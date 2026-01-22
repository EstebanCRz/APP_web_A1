<?php
require_once '../includes/session.php';
require_once '../includes/config.php';
require_once '../includes/admin_functions.php';

// Vérifier que l'utilisateur est admin
requireAdmin();

// Gérer la suppression d'une activité
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_activity'])) {
    try {
        $stmt = $pdo->prepare('DELETE FROM activities WHERE id = ?');
        $stmt->execute([$_POST['activity_id']]);
        $successMsg = "Activité supprimée avec succès.";
    } catch (Exception $e) {
        $errorMsg = "Erreur lors de la suppression: " . $e->getMessage();
    }
}

// Récupérer les statistiques et activités
$search = $_GET['search'] ?? '';
try {
    // Statistiques par catégorie
    $stmt = $pdo->prepare('
        SELECT ac.name as category, COUNT(a.id) as total
        FROM activity_categories ac
        LEFT JOIN activities a ON ac.id = a.category_id
        GROUP BY ac.id, ac.name
        ORDER BY total DESC
    ');
    $stmt->execute();
    $statsByCategory = $stmt->fetchAll();

    // Statistiques par date (30 derniers jours)
    $stmt = $pdo->prepare('
        SELECT DATE(created_at) as date, COUNT(*) as total
        FROM activities
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY DATE(created_at)
        ORDER BY date DESC
    ');
    $stmt->execute();
    $statsByDate = $stmt->fetchAll();

    // Liste des activités
    if ($search) {
        $stmt = $pdo->prepare('
            SELECT a.*, ac.name as category_name, u.username as creator_name
            FROM activities a
            LEFT JOIN activity_categories ac ON a.category_id = ac.id
            LEFT JOIN users u ON a.creator_id = u.id
            WHERE a.title LIKE ? OR a.description LIKE ?
            ORDER BY a.created_at DESC
        ');
        $searchTerm = "%$search%";
        $stmt->execute([$searchTerm, $searchTerm]);
    } else {
        $stmt = $pdo->prepare('
            SELECT a.*, ac.name as category_name, u.username as creator_name
            FROM activities a
            LEFT JOIN activity_categories ac ON a.category_id = ac.id
            LEFT JOIN users u ON a.creator_id = u.id
            ORDER BY a.created_at DESC
        ');
        $stmt->execute();
    }
    $activities = $stmt->fetchAll();
} catch (Exception $e) {
    $activities = [];
    $statsByCategory = [];
    $statsByDate = [];
    $errorMsg = "Erreur de chargement: " . $e->getMessage();
}

$pageTitle = "Gestion des Événements - Admin";
$assetsDepth = 1;
$customCSS = ["css/admin-dashboard.css"];

include '../includes/header.php';
            </select>
        </div>
    </header>

    <nav>
        <ul>
            <li><a href="index.php">Accueil</a></li>
            <li><a href="../events/events-list.php">Événements</a></li>
            <li><a href="../auth/login.php">Connexion</a></li>
            <li><a href="../auth/register.php">Inscription</a></li>
        </ul>
    </nav>

    <main>
        <div class="container">
            <section>
                <h2>Bienvenue sur AmiGo</h2>
                <p>Découvrez et participez à des événements proches de vous. Rencontrez de nouvelles personnes et partagez des moments inoubliables !</p>
                
                <div style="margin: 2rem 0;">
                    <a href="../auth/register.php" class="btn btn-primary">S'inscrire</a>
                    <a href="../auth/login.php" class="btn btn-secondary">Se connecter</a>
                </div>
            </section>

            <section>
                <h3>Événements tendance</h3>
                <!-- TODO: Charger les événements depuis la base de données avec PHP -->
                <div class="grid">
                    <div class="event-card">
                        <div class="event-banner" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);"></div>
                        <div class="event-info">
                            <h4 class="event-title">Concert Rock en plein air</h4>
                            <p class="event-details">📅 25/11/2025 - 20h00</p>
                            <p class="event-details">📍 Paris, France</p>
                            <p class="event-details">👥 50 places disponibles</p>
                            <a href="../events/event-details.php" class="btn btn-primary">Voir plus</a>
                        </div>
                    </div>

                    <div class="event-card">
                        <div class="event-banner" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);"></div>
                        <div class="event-info">
                            <h4 class="event-title">Match de Football</h4>
                            <p class="event-details">📅 28/11/2025 - 15h00</p>
                            <p class="event-details">📍 Lyon, France</p>
                            <p class="event-details">👥 20 places disponibles</p>
                            <a href="../events/event-details.php" class="btn btn-primary">Voir plus</a>
                        </div>
                    </div>

                    <div class="event-card">
                        <div class="event-banner" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);"></div>
                        <div class="event-info">
                            <h4 class="event-title">Soirée Cinéma</h4>
                            <p class="event-details">📅 30/11/2025 - 19h30</p>
                            <p class="event-details">📍 Marseille, France</p>
                            <p class="event-details">👥 30 places disponibles</p>
                            <a href="../events/event-details.php" class="btn btn-primary">Voir plus</a>
                        </div>
                    </div>
                </div>
            </section>

            <section>
                <h3>Rechercher un événement</h3>
                <form action="events-list.php" method="get">
                    <!-- TODO: Implémenter la recherche avec PHP/MySQL -->
                    <div class="form-group">
                        <input type="text" name="search" placeholder="Rechercher par mots-clés..." aria-label="Rechercher un événement">
                    </div>
                    <button type="submit" class="btn btn-primary">Rechercher</button>
                </form>
            </section>
        </div>
    </main>

    <footer>
        <ul class="footer-links">
            <li><a href="../pages/contact.php">Contact</a></li>
            <li><a href="../pages/faq.php">FAQ</a></li>
            <li><a href="../pages/cgu.php">CGU</a></li>
            <li><a href="../pages/mentions-legales.php">Mentions légales</a></li>
        </ul>
        <p>&copy; 2025 AmiGo - Tous droits réservés</p>
    </footer>
</body>
</html>
