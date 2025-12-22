<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/language.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$assetsDepth = 1;
$pageTitle = "Messages et Groupes - AmiGo";
$customCSS = ['css/messages.css'];
require_once '../includes/header.php';

$user_id = $_SESSION['user_id'];
?>

<div class="messages-container">
    <div class="messages-header">
        <h1>Messages et Groupes</h1>
    </div>

    <!-- Navigation par onglets -->
    <div class="tabs">
        <button class="tab-btn active" data-tab="groups">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
            </svg>
            Groupes
            <span id="groups-badge" class="tab-badge" style="display: none;"></span>
        </button>
        <button class="tab-btn" data-tab="private">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
            </svg>
            Messages privés
        </button>
    </div>

    <!-- Contenu des onglets -->
    <div class="tab-content active" id="groups-content">
        <div class="section-header">
            <h2>Mes Groupes</h2>
            <button class="btn-primary" id="btn-create-group">+ Créer un groupe</button>
        </div>
        
        <!-- Invitations en attente -->
        <div id="invitations-section" style="margin-bottom: 20px;"></div>
        
        <div id="groups-list" class="groups-list"></div>
    </div>

    <div class="tab-content" id="private-content">
        <div class="section-header">
            <h2>Conversations privées</h2>
            <div class="search-users">
                <input type="text" id="search-users" placeholder="Rechercher un utilisateur..." />
                <div id="search-results" class="search-results"></div>
            </div>
        </div>
        <div id="conversations-list" class="conversations-list"></div>
    </div>
</div>

<!-- Modal pour les détails du groupe -->
<div id="group-modal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <div id="group-details"></div>
    </div>
</div>

<!-- Modal pour la conversation privée -->
<div id="conversation-modal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <div id="conversation-details"></div>
    </div>
</div>

<script src="../assets/js/messages.js"></script>

<?php require_once '../includes/footer.php'; ?>
