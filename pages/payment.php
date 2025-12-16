<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');

$pageTitle = "Paiement - AmiGo";
$pageDescription = "Processus de paiement pour les événements AmiGo";
$assetsDepth = 1;
$customCSS = "../assets/css/index.css";

include '../includes/header.php';
?>

<div class="container">
    <h2>Paiement</h2>
    
    <section>
        <h3>Récapitulatif de votre commande</h3>
        <p><strong>Événement :</strong> Concert Rock en plein air</p>
        <p><strong>Date :</strong> 25/11/2025 - 20h00</p>
        <p><strong>Prix :</strong> 25€</p>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="card-number">Numéro de carte</label>
                <input type="text" id="card-number" name="card_number" placeholder="1234 5678 9012 3456" required>
            </div>
            
            <div class="form-group">
                <label for="card-name">Nom sur la carte</label>
                <input type="text" id="card-name" name="card_name" required>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="expiry">Date d'expiration</label>
                    <input type="text" id="expiry" name="expiry" placeholder="MM/AA" required>
                </div>
                
                <div class="form-group">
                    <label for="cvv">CVV</label>
                    <input type="text" id="cvv" name="cvv" placeholder="123" required>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary">Payer 25€</button>
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
