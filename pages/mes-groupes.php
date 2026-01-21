<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');

require_once '../includes/language.php';

// Simuler des groupes et conversations pour l'exemple
$groupes = [
    [
        'id' => 1,
        'nom' => 'Running Paris 15ème',
        'avatar' => 'https://images.unsplash.com/photo-1476480862126-209bfaa8edc8?w=100',
        'dernierMessage' => 'Super session ce matin ! 🏃',
        'heure' => '10:32',
        'nonLus' => 3,
        'membres' => 12
    ],
    [
        'id' => 2,
        'nom' => 'Amateurs de Yoga',
        'avatar' => 'https://images.unsplash.com/photo-1506126613408-eca07ce68773?w=100',
        'dernierMessage' => 'RDV dimanche à 9h au parc ?',
        'heure' => '09:15',
        'nonLus' => 0,
        'membres' => 8
    ],
    [
        'id' => 3,
        'nom' => 'Photo Walk Bordeaux',
        'avatar' => 'https://images.unsplash.com/photo-1499750310107-5fef28a66643?w=100',
        'dernierMessage' => 'Les photos de samedi sont en ligne 📸',
        'heure' => 'Hier',
        'nonLus' => 1,
        'membres' => 15
    ],
    [
        'id' => 4,
        'nom' => 'Soirées Jeux de Société',
        'avatar' => 'https://images.unsplash.com/photo-1585504198199-2027774e50af?w=100',
        'dernierMessage' => 'Qui a le Catan ?',
        'heure' => 'Hier',
        'nonLus' => 0,
        'membres' => 20
    ],
    [
        'id' => 5,
        'nom' => 'Cuisine du Monde',
        'avatar' => 'https://images.unsplash.com/photo-1556910103-1c02745aae4d?w=100',
        'dernierMessage' => 'Recette de pad thaï partagée !',
        'heure' => 'Lun',
        'nonLus' => 0,
        'membres' => 10
    ]
];

$conversations = [
    [
        'id' => 1,
        'nom' => 'Camille Dupont',
        'avatar' => 'https://i.pravatar.cc/100?img=1',
        'dernierMessage' => 'À quelle heure on se rejoint ?',
        'heure' => '11:20',
        'nonLus' => 2,
        'enLigne' => true
    ],
    [
        'id' => 2,
        'nom' => 'Mathis Leroy',
        'avatar' => 'https://i.pravatar.cc/100?img=12',
        'dernierMessage' => 'Merci pour l\'organisation !',
        'heure' => '10:05',
        'nonLus' => 0,
        'enLigne' => false
    ],
    [
        'id' => 3,
        'nom' => 'Zoé Martin',
        'avatar' => 'https://i.pravatar.cc/100?img=5',
        'dernierMessage' => 'Tu as vu la nouvelle activité ?',
        'heure' => 'Hier',
        'nonLus' => 0,
        'enLigne' => true
    ],
    [
        'id' => 4,
        'nom' => 'Nora Benali',
        'avatar' => 'https://i.pravatar.cc/100?img=9',
        'dernierMessage' => 'Je confirme ma présence 👍',
        'heure' => 'Dim',
        'nonLus' => 0,
        'enLigne' => false
    ]
];

$pageTitle = t('pages.my_groups') . " - AmiGo";
$assetsDepth = 1;
$customCSS = ["../pages/css/mes-groupes.css"];

include '../includes/header.php';
?>

<div class="messages-container">
    <div class="messages-sidebar">
        <div class="sidebar-header">
            <h2>Messages</h2>
            <button class="btn-new-message" title="Nouveau message">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </button>
        </div>

        <div class="search-box">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"></circle>
                <path d="m21 21-4.35-4.35"></path>
            </svg>
            <input type="text" placeholder="Rechercher...">
        </div>

        <div class="tabs">
            <button class="tab-btn active" data-tab="groupes">Groupes</button>
            <button class="tab-btn" data-tab="messages">Direct</button>
        </div>

        <div class="conversations-list" id="groupes-list">
            <?php foreach ($groupes as $groupe): ?>
                <div class="conversation-item" data-conversation-id="<?php echo $groupe['id']; ?>">
                    <div class="avatar-wrapper">
                        <img src="<?php echo htmlspecialchars($groupe['avatar']); ?>" alt="<?php echo htmlspecialchars($groupe['nom']); ?>" class="avatar">
                    </div>
                    <div class="conversation-info">
                        <div class="conversation-header">
                            <h4><?php echo htmlspecialchars($groupe['nom']); ?></h4>
                            <span class="time"><?php echo htmlspecialchars($groupe['heure']); ?></span>
                        </div>
                        <div class="conversation-preview">
                            <p><?php echo htmlspecialchars($groupe['dernierMessage']); ?></p>
                            <?php if ($groupe['nonLus'] > 0): ?>
                                <span class="badge-unread"><?php echo $groupe['nonLus']; ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="groupe-membres">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                            <span><?php echo $groupe['membres']; ?> membres</span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="conversations-list hidden" id="messages-list">
            <?php foreach ($conversations as $conv): ?>
                <div class="conversation-item" data-conversation-id="<?php echo $conv['id']; ?>">
                    <div class="avatar-wrapper">
                        <img src="<?php echo htmlspecialchars($conv['avatar']); ?>" alt="<?php echo htmlspecialchars($conv['nom']); ?>" class="avatar">
                        <?php if ($conv['enLigne']): ?>
                            <span class="online-indicator"></span>
                        <?php endif; ?>
                    </div>
                    <div class="conversation-info">
                        <div class="conversation-header">
                            <h4><?php echo htmlspecialchars($conv['nom']); ?></h4>
                            <span class="time"><?php echo htmlspecialchars($conv['heure']); ?></span>
                        </div>
                        <div class="conversation-preview">
                            <p><?php echo htmlspecialchars($conv['dernierMessage']); ?></p>
                            <?php if ($conv['nonLus'] > 0): ?>
                                <span class="badge-unread"><?php echo $conv['nonLus']; ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="messages-main">
        <div class="empty-state">
            <svg width="120" height="120" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
            </svg>
            <h3>Sélectionner une conversation</h3>
            <p>Choisissez un groupe ou une conversation pour commencer à discuter</p>
        </div>
    </div>
</div>

<script>
// Gestion des onglets
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const tab = btn.dataset.tab;
        
        // Activer l'onglet
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        
        // Afficher la liste correspondante
        document.getElementById('groupes-list').classList.add('hidden');
        document.getElementById('messages-list').classList.add('hidden');
        document.getElementById(tab + '-list').classList.remove('hidden');
    });
});

// Simuler la sélection d'une conversation
document.querySelectorAll('.conversation-item').forEach(item => {
    item.addEventListener('click', () => {
        document.querySelectorAll('.conversation-item').forEach(i => i.classList.remove('active'));
        item.classList.add('active');
        
        // Ici vous pourriez charger la conversation en AJAX
        const emptyState = document.querySelector('.empty-state');
        if (emptyState) {
            emptyState.innerHTML = `
                <div class="chat-interface">
                    <div class="chat-header">
                        <h3>Conversation chargée</h3>
                        <p>Interface de chat à implémenter...</p>
                    </div>
                </div>
            `;
        }
    });
});
</script>

<?php include '../includes/footer.php';
