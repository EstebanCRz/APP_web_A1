<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');

$pageTitle = "Mot de passe oublié - AmiGo";
$pageDescription = "Réinitialisez votre mot de passe AmiGo";
$assetsDepth = 1;
$customCSS = "../assets/css/index.css";

$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    
    if (!empty($email)) {
        // TODO: Envoyer l'email de réinitialisation
        $success = true;
    }
}

include '../includes/header.php';
?>

<div class="container">
    <div class="form-container">
        <h2>Mot de passe oublié</h2>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                Un email de réinitialisation a été envoyé à votre adresse email.
            </div>
        <?php else: ?>
            <p>Entrez votre adresse email pour recevoir un lien de réinitialisation.</p>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required placeholder="votre@email.com">
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Envoyer le lien</button>
            </form>
        <?php endif; ?>
        
        <div class="form-links">
            <p><a href="login.php">Retour à la connexion</a></p>
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
