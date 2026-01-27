<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
header('Content-Type: text/html; charset=UTF-8');
require_once '../includes/language.php';

$pageTitle = t('auth.reset_password') . " - AmiGo";
$pageDescription = t('auth.reset_password');
$assetsDepth = 1;
$customCSS = ["../assets/css/style.css", "../assets/css/reset-password.css"];

$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    
    if (!empty($email)) {
        // Générer un token unique
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+6 hours'));
        
        // Enregistrer le token et l'email dans la table password_resets
        require_once '../includes/config.php';
        $pdo = getDB();
        $stmt = $pdo->prepare('INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE token=?, expires_at=?');
        $stmt->execute([$email, $token, $expires, $token, $expires]);
        
        // Envoyer l'email de réinitialisation
        require_once '../includes/send_zoho_mail.php';
        $resetLink = 'http://localhost/auth/reset-password.php?token=' . $token;
        $subject = 'Réinitialisation de votre mot de passe AmiGo';
        $body = '<h2>Réinitialisation de mot de passe</h2>'
              . '<p>Pour réinitialiser votre mot de passe, cliquez sur le lien ci-dessous&nbsp;:</p>'
              . '<p><a href="' . $resetLink . '" style="display:inline-block; padding:12px 24px; background:#55D5E0; color:#fff; text-decoration:none; border-radius:6px; margin:1em 0;">Réinitialiser mon mot de passe</a></p>'
              . '<p>Ce lien expirera dans 6 heures.</p>'
              . '<p>Si vous n\'avez pas demandé cette réinitialisation, ignorez cet email.</p>'
              . '<br><div style="color:#888; font-size:0.95em; margin-top:2em;">© 2026 AmiGo - Tous droits réservés</div>';
        
        try {
            sendZohoMail($email, $subject, $body, 'AmiGo', 'amigocontact@zohomail.eu');
        } catch (Throwable $e) {
            error_log("Erreur envoi email reset password: " . $e->getMessage());
        }
        
        $success = true;
    }
}

include '../includes/header.php';
?>

<div class="container">
    <div class="form-container">
        <h2><?php echo t('auth.reset_password'); ?></h2>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <?php echo t('auth.reset_email_sent'); ?>
            </div>
        <?php else: ?>
            <p><?php echo t('auth.forgot_password_desc'); ?></p>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="email"><?php echo t('auth.email'); ?></label>
                    <input type="email" id="email" name="email" required placeholder="votre@email.com">
                </div>
                
                <button type="submit" class="btn btn-primary btn-block"><?php echo t('auth.send_link'); ?></button>
            </form>
        <?php endif; ?>
        
        <div class="form-links">
            <p><a href="login.php"><?php echo t('auth.back_to_login'); ?></a></p>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

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
