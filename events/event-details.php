<?php
declare(strict_types=1);

/* ============================================================
   1. CONFIGURATION SESSION & ENCODAGE
   ============================================================ */

// Cookies de session non accessibles en JavaScript (limite XSS sur ID de session)
ini_set('session.cookie_httponly', '1');
// La session ne fonctionne que via les cookies (pas d'ID dans l'URL)
ini_set('session.use_only_cookies', '1');
// En prod HTTPS : mettre '1' pour forcer le cookie en HTTPS uniquement
ini_set('session.cookie_secure', '0');

session_start();

// Force la réponse en HTML UTF-8
header('Content-Type: text/html; charset=UTF-8');

// Fichiers d’internationalisation et fonctions liées aux activités
require_once '../includes/language.php';
require_once '../includes/activities_functions.php';

/* ============================================================
   2. RÉCUPÉRATION & VALIDATION DE L'ID D'ÉVÉNEMENT
   ============================================================ */

// On récupère l'id dans l'URL, on cast en entier pour éviter l'injection
$event_id = (int) ($_GET['id'] ?? 0);

// Si pas d'id valide → retour à la liste
if ($event_id === 0) {
    header('Location: events-list.php');
    exit;
}

// On charge l'événement correspondant
$event = getActivityById($event_id);

// Si l'événement n'existe pas → retour à la liste
if (!$event) {
    header('Location: events-list.php');
    exit;
}

/* ============================================================
   3. TRAITEMENT DU FORMULAIRE D'AVIS (POST)
   ============================================================ */

$reviewMessage    = '';    // Message de succès (GET ?review=success)
$reviewError      = '';    // Message d'erreur (validation serveur)
$userReview       = null;  // Avis déjà existant de l'utilisateur pour cet event
$isUserRegistered = false; // L'utilisateur est-il inscrit à cet événement ?
$isFavorite       = false; // L'événement est-il dans ses favoris ?

// On ne traite avis + favoris que si un utilisateur est connecté
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // Vérifie si l'utilisateur est inscrit à cet événement
    $isUserRegistered = isUserRegistered($event_id, $user_id);

    // Connexion BDD
    $pdo = getDB();

    /* --- Vérification du statut "favori" --- */
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM user_favorites
        WHERE user_id = ? AND activity_id = ?
    ");
    $stmt->execute([$user_id, $event_id]);
    $isFavorite = $stmt->fetchColumn() > 0;

    /* --- Récupération d'un éventuel avis existant --- */
    $stmt = $pdo->prepare("
        SELECT *
        FROM activity_reviews
        WHERE activity_id = ? AND user_id = ?
    ");
    $stmt->execute([$event_id, $user_id]);
    $userReview = $stmt->fetch();

    /* --- Soumission du formulaire d'avis --- */
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {

        // On n'autorise l'avis que si :
        // - l'utilisateur est inscrit
        // - l'événement est déjà passé
        if ($isUserRegistered && strtotime($event['event_date']) < time()) {

            // Note (1 à 5) et commentaire trimé
            $rating  = (int)($_POST['rating'] ?? 0);
            $comment = trim($_POST['comment'] ?? '');

            // 1) Note obligatoire entre 1 et 5
            if ($rating < 1 || $rating > 5) {
                $reviewError = 'Veuillez sélectionner une note.';

            // 2) Longueur minimale du commentaire
            } elseif (strlen($comment) < 10) {
                $reviewError = '10 caractères minimum requis.';

            // 3) Vérification des caractères non autorisés
            //
            // Regex en "whitelist" : on définit ce qui est PERMIS,
            // tout le reste est refusé.
            //   \p{L}  : toutes les lettres Unicode (y compris accentuées)
            //   0-9   : chiffres
            //   espace + . , ; : ! ? ( ) [ ] { } " ' - : ponctuation autorisée
            //   \n \r : retours à la ligne
            // Le ^ au début de la classe [^...] signifie "tout ce qui n'est PAS dans la liste".
            // Si au moins un caractère interdit est trouvé → preg_match() retourne 1.
            } elseif (preg_match('/[^\p{L}0-9 .,;:!?()\[\]{}"\'\-\n\r]/u', $comment)) {
                $reviewError = 'Caractères spéciaux interdits.';

            } else {
                // À ce stade : note OK, longueur OK, caractères autorisés OK

                if ($userReview) {
                    // Mise à jour d'un avis existant
                    $stmt = $pdo->prepare("
                        UPDATE activity_reviews
                        SET rating = ?, comment = ?, updated_at = NOW()
                        WHERE activity_id = ? AND user_id = ?
                    ");
                    $stmt->execute([$rating, $comment, $event_id, $user_id]);
                } else {
                    // Création d'un nouvel avis
                    $stmt = $pdo->prepare("
                        INSERT INTO activity_reviews (activity_id, user_id, rating, comment)
                        VALUES (?, ?, ?, ?)
                    ");
                    $stmt->execute([$event_id, $user_id, $rating, $comment]);
                }

                // PRG Pattern : redirection après POST pour éviter la double soumission
                header('Location: event-details.php?id=' . $event_id . '&review=success');
                exit;
            }
        }
    }
}

// Message de succès passé en GET
if (isset($_GET['review']) && $_GET['review'] === 'success') {
    $reviewMessage = 'Votre avis a été enregistré !';
}

/* ============================================================
   4. CHARGEMENT DES LISTES ANNEXES
   - Participants
   - Autres activités de la même catégorie
   - Avis + note moyenne
   ============================================================ */

// Liste des participants à l'événement
$participants = getActivityParticipants($event_id);

// Suggestions d'autres activités de la même catégorie
$otherActivities = getAllActivities([
    'category' => $event['category_name'],
    'limit'    => 3
]);

// Récupération des avis + calcul de la moyenne
$reviews       = [];
$averageRating = 0;

$pdo  = getDB();
$stmt = $pdo->prepare("
    SELECT r.*, u.first_name, u.last_name
    FROM activity_reviews r
    JOIN users u ON r.user_id = u.id
    WHERE r.activity_id = ?
    ORDER BY r.created_at DESC
");
$stmt->execute([$event_id]);
$reviews = $stmt->fetchAll();

if (count($reviews) > 0) {
    $averageRating = round(
        array_sum(array_column($reviews, 'rating')) / count($reviews),
        1
    );
}

/* ============================================================
   5. CONFIGURATION PAGE & HEADER
   ============================================================ */

// Titre de la page (échappé pour éviter XSS dans le <title>)
$pageTitle       = htmlspecialchars($event['title']) . " - AmiGo";
$pageDescription = t('event_details.title');
$assetsDepth     = 1;

// CSS spécifiques à cette page
$customCSS = [
    "../assets/css/style.css",
    'css/event-details.css',
    '../assets/css/message-images.css'
];

// Inclusion du header commun
include '../includes/header.php';
?>

<div class="container">
    <div class="event-details-wrapper">
        <main class="event-main">

            <!-- ===================== Bloc infos principales ===================== -->
            <section class="event-info card">
                <div class="event-title-row">
                    <!-- Titre de l'événement protégé XSS -->
                    <h1><?= htmlspecialchars($event['title']) ?></h1>

                    <!-- Bouton favoris uniquement si utilisateur connecté -->
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <button
                            class="favorite-btn-large <?= $isFavorite ? 'active' : '' ?>"
                            data-activity-id="<?= $event_id ?>"
                        >
                            <span class="heart-icon">❤️</span>
                        </button>
                    <?php endif; ?>
                </div>

                <!-- Localisation + date -->
                <p>
                    📍 <?= htmlspecialchars($event['location']) ?>
                    | 📅 <?= formatEventDate($event['event_date']) ?>
                </p>

                <!-- Description (nl2br + htmlspecialchars pour éviter XSS) -->
                <div class="event-description">
                    <?= nl2br(htmlspecialchars($event['description'])) ?>
                </div>
            </section>

            <!-- ===================== Discussion (chat) ===================== -->
            <section
                class="event-discussion card"
                id="activity-chat"
                data-activity-id="<?= $event_id ?>"
            >
                <h2 class="discussion-title">💬 Discussion</h2>

                <?php if (!isset($_SESSION['user_id'])): ?>
                    <!-- Utilisateur non connecté : incitation à se connecter -->
                    <p><a href="../auth/login.php">Connectez-vous</a> pour discuter.</p>
                <?php else: ?>
                    <!-- Messages du chat, chargés via JS (activity-chat.js) -->
                    <div class="discussion-messages" id="chat-messages">
                        <div class="loading-messages">
                            <?= t('event_details.loading_messages') ?>
                        </div>
                    </div>

                    <!-- Formulaire de chat, soumis côté JS (pas de POST PHP classique) -->
                    <form class="discussion-form" id="chat-form" onsubmit="return false;">
                        <input
                            type="text"
                            id="chat-input"
                            class="message-input"
                            placeholder="Votre message..."
                        >
                        <button type="submit" class="btn btn-primary">
                            <?= t('event_details.send_button') ?>
                        </button>
                    </form>
                <?php endif; ?>
            </section>

            <!-- ===================== Section Avis (si événement passé) ===================== -->
            <?php if (strtotime($event['event_date']) < time()): ?>
            <section class="event-reviews card">
                <h2><span style="margin-right:0.5rem;">⭐</span> Avis (<?= $averageRating ?>/5)</h2>

                <!-- Message de succès (GET &review=success) -->
                <?php if ($reviewMessage): ?>
                    <div class="alert alert-success"><?= $reviewMessage ?></div>
                <?php endif; ?>

                <!-- Message d'erreur serveur (validation PHP) -->
                <?php if ($reviewError): ?>
                    <div class="alert alert-error"><?= $reviewError ?></div>
                <?php endif; ?>

                <!-- Formulaire d'avis uniquement si l'utilisateur est inscrit -->
                <?php if ($isUserRegistered): ?>
                    <form method="POST" class="review-form">
                        <!-- Notation par étoiles -->
                        <div class="star-rating-inline">
                            <?php for ($i = 5; $i >= 1; $i--): ?>
                                <input
                                    type="radio"
                                    id="star<?= $i ?>"
                                    name="rating"
                                    value="<?= $i ?>"
                                    <?php
                                        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $reviewError) {
                                            
                                            echo (isset($_POST['rating']) && (int)$_POST['rating'] == $i) ? 'checked' : '';
                                        }
                                        
                                    ?> required>
                                <label for="star<?= $i ?>">★</label>
                            <?php endfor; ?>
                        </div>
                        
                        <!-- Commentaire-->
                        <textarea
                            id="comment"
                            name="comment"
                            required
                            minlength="10"
                            placeholder="Donner votre avis... "
                        ><?php
                            if ($_SERVER['REQUEST_METHOD'] === 'POST' && $reviewError) {
                                echo isset($_POST['comment'])
                                    ? trim(htmlspecialchars($_POST['comment']))
                                    : '';
                            }
                        ?></textarea>
                        
                        <!-- Compteur + bouton (JS gère le disabled) -->
                        <div class="review-actions-row">
                            <small class="review-counter"></small>
                            <button
                                type="submit"
                                name="submit_review"
                                class="btn btn-primary"
                                disabled
                            >
                                Publier l'avis
                            </button>
                        </div>
                        
                        <!-- Erreur inline gérée côté JS -->
                        <div class="review-error" style="display:none;"></div>
                    </form>
                <?php endif; ?>

                <!-- Liste des avis existants -->
                <div class="reviews-list">
                    <?php foreach ($reviews as $rev): ?>
                        <div
                            class="review-item"
                            style="
                                border-left:4px solid #f5f5f5;
                                background:#fff;
                                border-radius:10px;
                                margin-bottom:1.2rem;
                                padding:1.2rem 1.5rem;
                                box-shadow:0 2px 8px rgba(0,0,0,0.04);
                            "
                        >
                            <div
                                style="
                                    display:flex;
                                    align-items:center;
                                    gap:1rem;
                                    margin-bottom:0.5rem;
                                "
                            >
                                <div>
                                    <!-- Nom complet de l'auteur de l'avis -->
                                    <span style="font-weight:700; font-size:1.08rem; color:#222;">
                                        <?= htmlspecialchars($rev['first_name'] . ' ' . $rev['last_name']) ?>
                                    </span>

                                    <!-- Note numérique + étoiles -->
                                    <span style="color:#888; font-size:0.98rem; margin-left:0.5rem;">
                                        (<?= $rev['rating'] ?>/5)
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <span
                                                style="
                                                    color:<?= $i <= $rev['rating'] ? '#deb514':'#ccc' ?>;
                                                    font-size:1.1em;
                                                "
                                            >
                                                ★
                                            </span>
                                        <?php endfor; ?>
                                    </span>

                                    <!-- Date de l'avis -->
                                    <span style="color:#aaa; font-size:0.92rem; margin-left:0.7rem;">
                                        <?= date('d/m/Y', strtotime($rev['created_at'] ?? 'now')) ?>
                                    </span>
                                </div>
                            </div>

                            <!-- Commentaire (affiché multi-lignes, protégé XSS) -->
                            <div
                                style="
                                    font-size:1.05rem;
                                    color:#222;
                                    margin-left:2.5rem;
                                    white-space:pre-line;
                                "
                            >
                                <?= nl2br(htmlspecialchars($rev['comment'])) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php endif; ?> 
        </main>

        <!-- ===================== Sidebar (participants + suggestions) ===================== -->
        <aside class="event-sidebar">
            <!-- Bloc participants -->
            <div class="card">
                <h3>Participants (<?= count($participants) ?>/<?= $event['max_participants'] ?>)</h3>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <button class="btn <?= $isUserRegistered ? 'btn-danger' : 'btn-primary' ?> btn-block">
                        <?= $isUserRegistered ? 'Se désinscrire' : 'S\'inscrire' ?>
                    </button>
                <?php endif; ?>
            </div>

            <!-- Suggestions d'activités similaires -->
            <?php if (!empty($otherActivities)): ?>
            <div class="card" style="margin-top:2rem;">
                <h3 style="margin-bottom:1rem;">Autres activités à découvrir</h3>

                <div class="events-grid" style="grid-template-columns:1fr; gap:1rem;">
                <?php foreach ($otherActivities as $act): ?>
                    <a
                        href="event-details.php?id=<?= (int)$act['id'] ?>"
                        class="event-card"
                        style="text-decoration:none; color:inherit;"
                    >
                        <!-- Illustration / image de l'activité -->
                        <div
                            class="card-media"
                            style="
                                background-image:url('<?= htmlspecialchars($act['image'] ?? 'https://picsum.photos/800/600', ENT_QUOTES) ?>');
                                height:80px;
                                background-size:cover;
                                background-position:center;
                                border-radius:12px 12px 0 0;
                                display:flex;
                                align-items:flex-start;
                                justify-content:space-between;
                                padding:0.75rem;
                                position:relative;
                            "
                        >
                            <!-- Badge catégorie -->
                            <span
                                class="badge"
                                style="
                                    background:rgba(255,255,255,0.95);
                                    color:var(--accent-structure);
                                    padding:0.4rem 0.75rem;
                                    border-radius:999px;
                                    font-weight:600;
                                    font-size:0.8rem;
                                    box-shadow:0 2px 6px rgba(47,69,88,0.1);
                                    display:inline-block;
                                "
                            >
                                <?= htmlspecialchars($act['category_name']) ?>
                            </span>
                        </div>

                        <!-- Contenu texte de la carte -->
                        <div
                            class="card-body"
                            style="padding:1rem; display:flex; flex-direction:column; gap:0.6rem;"
                        >
                            <h3
                                class="card-title"
                                style="
                                    font-size:1.05rem;
                                    margin:0;
                                    color:var(--accent-structure);
                                    font-weight:700;
                                    line-height:1.3;
                                "
                            >
                                <?= htmlspecialchars($act['title']) ?>
                            </h3>

                            <p
                                class="card-excerpt"
                                style="margin:0; color:#666; font-size:0.9rem; line-height:1.4;"
                            >
                                <?= htmlspecialchars($act['excerpt']) ?>
                            </p>

                            <div
                                class="card-meta"
                                style="
                                    font-size:0.85rem;
                                    color:var(--muted-color);
                                    display:flex;
                                    flex-direction:column;
                                    gap:0.3rem;
                                    margin-top:0.5rem;
                                "
                            >
                                <span
                                    class="meta-item"
                                    style="display:flex; align-items:center; gap:0.4rem;"
                                >
                                    📍 <?= htmlspecialchars($act['location']) ?>
                                </span>
                                <span
                                    class="meta-item"
                                    style="display:flex; align-items:center; gap:0.4rem;"
                                >
                                    📅 <?= formatEventDate($act['event_date']) ?>
                                </span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </aside>
    </div>
</div>

<!-- Scripts JS de la page -->
<script src="../assets/js/activity-registration.js"></script>
<script src="../assets/js/activity-chat.js"></script>
<script src="../assets/js/review-form-validation.js"></script>

<!-- Fichier JavaScript séparé -->
<script src="js/event-details.js"></script>

<?php include '../includes/footer.php'; ?>
