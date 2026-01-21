<?php
declare(strict_types=1);

require_once '../includes/session.php';

require_once '../includes/language.php';
require_once '../includes/activities_functions.php';

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$pageTitle = t('events.create') . " - AmiGo";
$pageDescription = t('events.create');
$assetsDepth = 1;
$customCSS = [
    "../assets/css/style.css",
    "css/event-create.css"
];

// Récupérer les catégories
$categories = getAllCategories();

// Récupérer les villes
$cities = getAllCities();

// Handle form submission
$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim((string) ($_POST['title'] ?? ''));
    $description = trim((string) ($_POST['description'] ?? ''));
    $category_id = (int) ($_POST['category'] ?? 0);
    $location = trim((string) ($_POST['location'] ?? ''));
    $city = trim((string) ($_POST['city'] ?? ''));
    $date = trim((string) ($_POST['date'] ?? ''));
    $time = trim((string) ($_POST['time'] ?? ''));
    $capacity = (int) ($_POST['capacity'] ?? 0);
    $image = trim((string) ($_POST['image'] ?? ''));

    // Validation
    if (empty($title) || empty($description) || $category_id === 0 || empty($location) || empty($city) || empty($date) || empty($time) || $capacity < 1) {
        $error = 'Tous les champs obligatoires doivent être remplis.';
    } else {
        try {
            // Créer l'activité
            $activityData = [
                'title' => $title,
                'description' => $description,
                'excerpt' => substr($description, 0, 200),
                'category_id' => $category_id,
                'creator_id' => $_SESSION['user_id'],
                'location' => $location,
                'city' => $city,
                'event_date' => $date,
                'event_time' => $time,
                'max_participants' => $capacity,
                'image' => !empty($image) ? $image : 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?q=80&w=800'
            ];
            
            $activityId = createActivity($activityData);
            
            if ($activityId) {
                $success = true;
                // Rediriger vers le profil après 2 secondes
                header("Refresh: 2; url=../profile/profile-created.php");
            } else {
                $error = 'Erreur lors de la création de l\'activité.';
            }
        } catch (Exception $e) {
            $error = 'Erreur: ' . $e->getMessage();
        }
    }
}

include '../includes/header.php';
?>

<div class="container">
    <div class="form-container">
        <div class="form-header">
            <h1><?php echo t('events.create_new_activity'); ?></h1>
            <p class="form-subtitle"><?php echo t('events.share_activity_idea'); ?></p>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <?php echo t('events.activity_created'); ?> <a href="events-list.php"><?php echo t('events.view_events'); ?></a>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="event-form">
            <div class="form-group">
                <label for="title"><?php echo t('events.activity_title'); ?> <span class="required">*</span></label>
                <input 
                    type="text" 
                    id="title" 
                    name="title" 
                    placeholder="<?php echo t('events.activity_title_placeholder'); ?>"
                    value="<?php echo htmlspecialchars($_POST['title'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="description"><?php echo t('events.description_field'); ?> <span class="required">*</span></label>
                <textarea 
                    id="description"
                    name="description" 
                    placeholder="Décrivez votre activité..."
                    rows="4"
                    required
                ><?php echo htmlspecialchars($_POST['description'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>

            <div class="form-group">
                <label for="category"><?php echo t('events.category_field'); ?> <span class="required">*</span></label>
                <select id="category" name="category" required>
                    <option value=""><?php echo t('events.select_category'); ?></option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo (($_POST['category'] ?? 0) == $cat['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="location"><?php echo t('events.location_field'); ?> <span class="required">*</span></label>
                <input 
                    type="text" 
                    id="location" 
                    name="location" 
                    placeholder="ex: Parc Monceau"
                    value="<?php echo htmlspecialchars($_POST['location'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="city"><?php echo t('events.city_field'); ?> <span class="required">*</span></label>
                <input 
                    type="text" 
                    id="city" 
                    name="city" 
                    list="cities-list"
                    placeholder="ex: Paris"
                    value="<?php echo htmlspecialchars($_POST['city'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                    autocomplete="off"
                    required
                >
                <datalist id="cities-list">
                    <?php foreach ($cities as $city): ?>
                        <option value="<?php echo htmlspecialchars($city['name'], ENT_QUOTES, 'UTF-8'); ?>">
                    <?php endforeach; ?>
                </datalist>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="date"><?php echo t('events.date_field'); ?> <span class="required">*</span></label>
                    <input 
                        type="date" 
                        id="date" 
                        name="date" 
                        value="<?php echo htmlspecialchars($_POST['date'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                        required
                    >
                </div>
                <div class="form-group">
                    <label for="time"><?php echo t('events.time_field'); ?> <span class="required">*</span></label>
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
                <label for="capacity"><?php echo t('events.max_participants_field'); ?> <span class="required">*</span></label>
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
                <a href="events-list.php" class="btn btn-secondary"><?php echo t('common.cancel'); ?></a>
                <button type="submit" class="btn btn-primary btn-lg"><?php echo t('events.submit_event'); ?></button>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
