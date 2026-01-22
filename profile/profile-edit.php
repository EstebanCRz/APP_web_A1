<?php
require_once '../includes/session.php';
require_once '../includes/config.php';
require_once '../includes/language.php';

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$pdo = getDB();
$message = '';
$error = '';

// Récupérer les données actuelles de l'utilisateur
try {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        header('Location: ../auth/login.php');
        exit;
    }
} catch (Exception $e) {
    $error = "Erreur lors du chargement du profil";
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $bio = trim($_POST['bio'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        
        // Validation
        if (empty($username) || empty($email)) {
            throw new Exception("Le nom d'utilisateur et l'email sont requis");
        }
        
        // Vérifier si l'email est déjà utilisé par un autre utilisateur
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
        $stmt->execute([$email, $user_id]);
        if ($stmt->fetch()) {
            throw new Exception("Cet email est déjà utilisé");
        }
        
        // Vérifier si le username est déjà utilisé
        $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ? AND id != ?');
        $stmt->execute([$username, $user_id]);
        if ($stmt->fetch()) {
            throw new Exception("Ce nom d'utilisateur est déjà utilisé");
        }
        
        // Gestion de l'upload d'avatar
        $avatar_path = $user['avatar'] ?? null;
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../uploads/avatars/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            if (!in_array($file_extension, $allowed_extensions)) {
                throw new Exception("Format d'image non autorisé");
            }
            
            if ($_FILES['avatar']['size'] > 5 * 1024 * 1024) {
                throw new Exception("L'image est trop volumineuse (max 5MB)");
            }
            
            $new_filename = 'avatar_' . $user_id . '_' . time() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $upload_path)) {
                // Supprimer l'ancien avatar s'il existe
                if ($avatar_path && file_exists('../' . $avatar_path)) {
                    unlink('../' . $avatar_path);
                }
                $avatar_path = 'uploads/avatars/' . $new_filename;
            }
        }
        
        // Mettre à jour la base de données
        $stmt = $pdo->prepare('
            UPDATE users 
            SET username = ?, email = ?, first_name = ?, last_name = ?, 
                bio = ?, city = ?, phone = ?, avatar = ?
            WHERE id = ?
        ');
        $stmt->execute([
            $username, $email, $first_name, $last_name,
            $bio, $city, $phone, $avatar_path, $user_id
        ]);
        
        // Mettre à jour la session
        $_SESSION['user_first_name'] = $first_name;
        $_SESSION['user_last_name'] = $last_name;
        $_SESSION['username'] = $username;
        
        // Recharger les données
        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $message = "Profil mis à jour avec succès !";
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$pageTitle = "Modifier mon profil - AmiGo";
$pageDescription = "Modifiez vos informations personnelles";
$assetsDepth = 1;
$customCSS = ["css/profile-edit.css"];

include '../includes/header.php';
?>

<div class="edit-container">
    <div class="edit-header">
        <h1>✏️ Modifier mon profil</h1>
        <p>Personnalisez vos informations et votre avatar</p>
    </div>
    
    <?php if ($message): ?>
        <div class="alert alert-success">✅ <?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-error">❌ <?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <form method="POST" enctype="multipart/form-data" class="edit-form">
        <!-- Section Avatar -->
        <div class="form-section">
            <h2>📸 Photo de profil</h2>
            <div class="avatar-upload">
                <div class="avatar-preview">
                    <?php if (!empty($user['avatar']) && file_exists('../' . $user['avatar'])): ?>
                        <img src="../<?php echo htmlspecialchars($user['avatar']); ?>" alt="Avatar" id="avatar-img">
                    <?php else: ?>
                        <div class="avatar-placeholder" id="avatar-img">
                            <span><?php echo strtoupper(substr($user['username'], 0, 2)); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="avatar-controls">
                    <label for="avatar" class="btn-upload">
                        📁 Choisir une photo
                        <input type="file" id="avatar" name="avatar" accept="image/*" onchange="previewAvatar(this)">
                    </label>
                    <p class="help-text">JPG, PNG ou GIF - Max 5MB</p>
                </div>
            </div>
        </div>
        
        <!-- Section Informations personnelles -->
        <div class="form-section">
            <h2>👤 Informations personnelles</h2>
            <div class="form-grid">
                <div class="form-group">
                    <label for="username">Nom d'utilisateur *</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="first_name">Prénom</label>
                    <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="last_name">Nom</label>
                    <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="city">Ville</label>
                    <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="phone">Téléphone</label>
                    <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                </div>
            </div>
            
            <div class="form-group full-width">
                <label for="bio">Biographie</label>
                <textarea id="bio" name="bio" rows="4" placeholder="Parlez-nous de vous..."><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
            </div>
        </div>
        
        <!-- Section Actions -->
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <span>💾</span>
                <span>Enregistrer les modifications</span>
            </button>
            <a href="profile.php" class="btn btn-secondary">
                <span>↩️</span>
                <span>Retour au profil</span>
            </a>
            <a href="../auth/forgot-password.php" class="btn btn-link">
                <span>🔒</span>
                <span>Changer mon mot de passe</span>
            </a>
        </div>
    </form>
</div>

<script>
function previewAvatar(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('avatar-img');
            if (preview.tagName === 'IMG') {
                preview.src = e.target.result;
            } else {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.alt = 'Avatar preview';
                preview.parentNode.replaceChild(img, preview);
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php include '../includes/footer.php';
