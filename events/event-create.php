<?php
declare(strict_types=1);
session_start();
header('Content-Type: text/html; charset=UTF-8');

$pageTitle = "Créer une activité - AmiGo";
$pageDescription = "Créez et partagez une nouvelle activité avec la communauté";
$assetsDepth = 1;
$customCSS = "css/event-create.css";

// Handle form submission
$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim((string) ($_POST['title'] ?? ''));
    $description = trim((string) ($_POST['description'] ?? ''));
    $category = trim((string) ($_POST['category'] ?? ''));
    $location = trim((string) ($_POST['location'] ?? ''));
    $date = trim((string) ($_POST['date'] ?? ''));
    $time = trim((string) ($_POST['time'] ?? ''));
    $capacity = trim((string) ($_POST['capacity'] ?? ''));
    $image = trim((string) ($_POST['image'] ?? ''));

    // Validation
    if (empty($title) || empty($description) || empty($category) || empty($location) || empty($date) || empty($time) || empty($capacity)) {
        $error = 'Tous les champs obligatoires doivent être remplis.';
    } elseif ((int)$capacity < 1) {
        $error = 'La capacité doit être supérieure à 0.';
    } else {
        // TODO: Save to database
        $success = true;
    }
}

include '../includes/header.php';
?>

<div class="container">
    <div class="form-container">
        <div class="form-header">
            <h1>Créer une nouvelle activité</h1>
            <p class="form-subtitle">Partagez votre idée d'activité avec la communauté</p>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success">
                Votre activité a été créée avec succès ! <a href="events-list.php">Voir la liste des événements</a>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="event-form">
            <div class="form-group">
                <label for="title">Titre de l'activité <span class="required">*</span></label>
                <input 
                    type="text" 
                    id="title" 
                    name="title" 
                    placeholder="ex: Sortie Running au Parc"
                    value="<?php echo htmlspecialchars($_POST['title'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="description">Description <span class="required">*</span></label>
                <textarea 
                    id="description" 
                    name="description" 
                    placeholder="Décrivez votre activité..."
                    rows="4"
                    required
                ><?php echo htmlspecialchars($_POST['description'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>

            <div class="form-group">
                <label for="category">Catégorie <span class="required">*</span></label>
                <select id="category" name="category" required>
                    <option value="">Sélectionner une catégorie</option>
                    <option value="Musique" <?php echo ($_POST['category'] ?? '') === 'Musique' ? 'selected' : ''; ?>>Musique</option>
                    <option value="Sport" <?php echo ($_POST['category'] ?? '') === 'Sport' ? 'selected' : ''; ?>>Sport</option>
                    <option value="Cinéma" <?php echo ($_POST['category'] ?? '') === 'Cinéma' ? 'selected' : ''; ?>>Cinéma</option>
                    <option value="Autres" <?php echo ($_POST['category'] ?? '') === 'Autres' ? 'selected' : ''; ?>>Autres</option>
                </select>
            </div>

            <div class="form-group">
                <label for="location">Lieu <span class="required">*</span></label>
                <input 
                    type="text" 
                    id="location" 
                    name="location" 
                    placeholder="ex: Parc Monceau, Paris"
                    value="<?php echo htmlspecialchars($_POST['location'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                    required
                >
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="date">Date <span class="required">*</span></label>
                    <input 
                        type="date" 
                        id="date" 
                        name="date" 
                        value="<?php echo htmlspecialchars($_POST['date'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                        required
                    >
                </div>
                <div class="form-group">
                    <label for="time">Heure <span class="required">*</span></label>
                    <input 
                        type="time" 
                        id="time" 
                        name="time" 
                        value="<?php echo htmlspecialchars($_POST['time'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                        required
                    >
                </div>
            </div>

            <div class="form-group">
                <label for="capacity">Capacité maximale <span class="required">*</span></label>
                <input 
                    type="number" 
                    id="capacity" 
                    name="capacity" 
                    placeholder="ex: 12"
                    min="1"
                    value="<?php echo htmlspecialchars($_POST['capacity'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="image">URL de l'image <span class="optional">(optionnel)</span></label>
                <input 
                    type="url" 
                    id="image" 
                    name="image" 
                    placeholder="https://..."
                    value="<?php echo htmlspecialchars($_POST['image'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                >
            </div>

            <div class="form-actions">
                <a href="events-list.php" class="btn btn-secondary">Annuler</a>
                <button type="submit" class="btn btn-primary btn-lg">Créer l'activité</button>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
