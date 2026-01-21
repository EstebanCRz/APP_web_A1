<?php
session_start();
require_once '../includes/language.php';
header('Content-Type: text/html; charset=UTF-8');

$pageTitle = "Paiement - AmiGo";
$pageDescription = "Processus de paiement pour les événements AmiGo";
$assetsDepth = 1;
$customCSS = "../assets/css/index.css";

include '../includes/header.php';
?>

<div class="container">
    <h2><?php echo t('pages.payment'); ?></h2>
    
    <section>
        <h3><?php echo t('pages.order_summary'); ?></h3>
        <p><strong><?php echo t('pages.event'); ?> :</strong> Concert Rock en plein air</p>
        <p><strong><?php echo t('pages.date'); ?> :</strong> 25/11/2025 - 20h00</p>
        <p><strong><?php echo t('pages.price'); ?> :</strong> 25€</p>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="card-number"><?php echo t('pages.card_number'); ?></label>
                <input type="text" id="card-number" name="card_number" placeholder="1234 5678 9012 3456" required>
            </div>
            
            <div class="form-group">
                <label for="card-name"><?php echo t('pages.card_name'); ?></label>
                <input type="text" id="card-name" name="card_name" required>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="expiry"><?php echo t('pages.expiry'); ?></label>
                    <input type="text" id="expiry" name="expiry" placeholder="MM/AA" required>
                </div>
                
                <div class="form-group">
                    <label for="cvv"><?php echo t('pages.cvv'); ?></label>
                    <input type="text" id="cvv" name="cvv" placeholder="123" required>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary"><?php echo t('pages.pay_button'); ?> 25€</button>
        </form>
    </section>
</div>

<?php include '../includes/footer.php';
