<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');

$pageTitle = "Paramètres - AmiGo";
$pageDescription = "Gérez vos paramètres AmiGo";
$assetsDepth = 1;
$customCSS = "../assets/css/index.css";

include '../includes/header.php';
?>

<div class="container">
    <h2>Paramètres</h2>
    
    <section>
        <h3>Préférences de compte</h3>
        <form method="POST" action="">
            <div class="form-group">
                <label for="language">Langue</label>
                <select id="language" name="language" class="language-selector">
                    <option value="fr">Français</option>
                    <option value="en">English</option>
                    <option value="es">Español</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="notifications">Notifications</label>
                <input type="checkbox" id="notifications" name="notifications" checked>
                <label for="notifications">Recevoir les notifications par email</label>
            </div>
            
            <button type="submit" class="btn btn-primary">Enregistrer</button>
        </form>
    </section>
</div>

<?php include '../includes/footer.php'; ?>
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
