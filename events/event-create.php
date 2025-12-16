<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');

$pageTitle = "Créer un événement - AmiGo";
$pageDescription = "Créez votre propre événement sur AmiGo";
$assetsDepth = 1;
$customCSS = "../assets/css/index.css";

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // TODO: Enregistrer l'événement dans la base de données
    header('Location: events-list.php');
    exit;
}

include '../includes/header.php';
?>

<div class="container">
    <h2>Créer un événement</h2>
    
    <form method="POST" action="">
        <div class="form-group">
            <label for="title">Titre de l'événement</label>
            <input type="text" id="title" name="title" required>
        </div>
        
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="5" required></textarea>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="date">Date</label>
                <input type="date" id="date" name="date" required>
            </div>
            
            <div class="form-group">
                <label for="time">Heure</label>
                <input type="time" id="time" name="time" required>
            </div>
        </div>
        
        <div class="form-group">
            <label for="location">Lieu</label>
            <input type="text" id="location" name="location" required>
        </div>
        
        <div class="form-group">
            <label for="address">Adresse complète</label>
            <input type="text" id="address" name="address" required>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="places">Nombre de places</label>
                <input type="number" id="places" name="places" min="1" required>
            </div>
            
            <div class="form-group">
                <label for="price">Prix (€)</label>
                <input type="number" id="price" name="price" min="0" step="0.01" value="0">
            </div>
        </div>
        
        <div class="form-group">
            <label for="category">Catégorie</label>
            <select id="category" name="category" required>
                <option value="">Choisir...</option>
                <option value="sport">Sport</option>
                <option value="culture">Culture</option>
                <option value="musique">Musique</option>
                <option value="loisirs">Loisirs</option>
                <option value="autre">Autre</option>
            </select>
        </div>
        
        <button type="submit" class="btn btn-primary">Créer l'événement</button>
    </form>
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
