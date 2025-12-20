<?php
declare(strict_types=1);

// Configuration de session (AVANT session_start)
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0);

session_start();
header('Content-Type: text/html; charset=UTF-8');

require_once '../includes/language.php';
require_once '../includes/activities_functions.php';

$pageTitle = t('events.all_events') . " - AmiGo";
$pageDescription = t('events.all_events');
$assetsDepth = 1;
$customCSS = [
    "../assets/css/style.css",
    "css/events-list.css"
];

// Récupération des filtres depuis l'URL
$filters = [
    'search' => (string) ($_GET['search'] ?? ''),
    'category' => (string) ($_GET['category'] ?? ''),
    'time_filter' => (string) ($_GET['time'] ?? ''),
    'date_filter' => (string) ($_GET['date'] ?? '')
];

// Variables pour faciliter l'accès
$filterDate = $filters['date_filter'];

// Récupération des activités depuis la base de données avec filtres
$activitiesFromDB = getAllActivities($filters);

// Récupération de toutes les catégories pour les filtres
$categories = getAllCategories();

// Transformation des données pour l'affichage
$events = [];
$userFavorites = [];

// Récupérer les favoris de l'utilisateur si connecté
if (isset($_SESSION['user_id'])) {
    try {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT activity_id FROM user_favorites WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $userFavorites = array_column($stmt->fetchAll(), 'activity_id');
    } catch (PDOException $e) {
        $userFavorites = [];
    }
}

foreach ($activitiesFromDB as $act) {
    $eventDate = new DateTime($act['event_date']);
    $events[] = [
        'id' => $act['id'],
        'category' => $act['category_name'],
        'title' => $act['title'],
        'date' => $eventDate->format('d/m/Y'),
        'time' => formatEventTime($act['event_time']),
        'location' => $act['location'] . ', ' . $act['city'],
        'places' => (int)$act['max_participants'],
        'taken' => (int)$act['current_participants'],
        'organizer' => $act['creator_first_name'] ?? $act['creator_username'],
        'excerpt' => $act['excerpt'],
        'image' => $act['image'] ?? 'https://picsum.photos/800/600',
        'subscribed' => isset($_SESSION['user_id']) ? isUserRegistered($act['id'], $_SESSION['user_id']) : false,
        'is_favorite' => in_array($act['id'], $userFavorites)
    ];
}

include '../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h2><?php echo t('events.all_events'); ?></h2>
        <a href="event-create.php" class="btn btn-primary"><?php echo t('events.create'); ?></a>
    </div>

    <div class="content-wrapper">
        <!-- Sidebar avec filtres -->
        <aside class="filters-sidebar">
            <div class="filter-group">
                <h3><?php echo t('events.category_filter'); ?></h3>
                <div class="filter-chips">
                    <a href="?<?php echo http_build_query(['search' => $filters['search'], 'time' => $filters['time_filter'], 'date' => $filters['date_filter']]); ?>" class="filter-chip <?php echo ($filters['category'] === '') ? 'active' : ''; ?>"><?php echo t('events.all_categories'); ?></a>
                    <?php foreach ($categories as $cat): ?>
                        <a href="?<?php echo http_build_query(['search' => $filters['search'], 'category' => $cat['name'], 'time' => $filters['time_filter'], 'date' => $filters['date_filter']]); ?>" class="filter-chip <?php echo ($filters['category'] === $cat['name']) ? 'active' : ''; ?>">
                            <?php echo htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8'); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="filter-group">
                <h3><?php echo t('events.time_filter'); ?></h3>
                <div class="filter-chips">
                    <a href="?<?php echo http_build_query(['search' => $filters['search'], 'category' => $filters['category'], 'date' => $filters['date_filter']]); ?>" class="filter-chip <?php echo ($filters['time_filter'] === '') ? 'active' : ''; ?>"><?php echo t('events.all_times'); ?></a>
                    <a href="?<?php echo http_build_query(['search' => $filters['search'], 'category' => $filters['category'], 'time' => 'morning', 'date' => $filters['date_filter']]); ?>" class="filter-chip <?php echo ($filters['time_filter'] === 'morning') ? 'active' : ''; ?>"><?php echo t('events.morning'); ?></a>
                    <a href="?<?php echo http_build_query(['search' => $filters['search'], 'category' => $filters['category'], 'time' => 'afternoon', 'date' => $filters['date_filter']]); ?>" class="filter-chip <?php echo ($filters['time_filter'] === 'afternoon') ? 'active' : ''; ?>"><?php echo t('events.afternoon'); ?></a>
                    <a href="?<?php echo http_build_query(['search' => $filters['search'], 'category' => $filters['category'], 'time' => 'evening', 'date' => $filters['date_filter']]); ?>" class="filter-chip <?php echo ($filters['time_filter'] === 'evening') ? 'active' : ''; ?>"><?php echo t('events.evening'); ?></a>
                </div>
            </div>

            <div class="filter-group">
                <h3><?php echo t('events.period_filter'); ?></h3>
                <div class="filter-chips">
                    <a href="?<?php echo http_build_query(['search' => $filters['search'], 'category' => $filters['category'], 'time' => $filters['time_filter']]); ?>" class="filter-chip <?php echo ($filters['date_filter'] === '') ? 'active' : ''; ?>"><?php echo t('events.all_periods'); ?></a>
                    <a href="?<?php echo http_build_query(['search' => $filters['search'], 'category' => $filters['category'], 'time' => $filters['time_filter'], 'date' => 'week']); ?>" class="filter-chip <?php echo ($filters['date_filter'] === 'week') ? 'active' : ''; ?>"><?php echo t('events.this_week'); ?></a>
                    <a href="?<?php echo http_build_query(['search' => $filters['search'], 'category' => $filters['category'], 'time' => $filters['time_filter'], 'date' => 'month']); ?>" class="filter-chip <?php echo ($filters['date_filter'] === 'month') ? 'active' : ''; ?>"><?php echo t('events.this_month'); ?></a>
                    <a href="?<?php echo http_build_query(['search' => $filters['search'], 'category' => $filters['category'], 'time' => $filters['time_filter'], 'date' => 'coming']); ?>" class="filter-chip <?php echo ($filters['date_filter'] === 'coming') ? 'active' : ''; ?>"><?php echo t('events.coming_soon'); ?></a>
                    <a href="?<?php echo http_build_query(['search' => $filters['search'], 'category' => $filters['category'], 'time' => $filters['time_filter'], 'date' => 'past']); ?>" class="filter-chip <?php echo ($filters['date_filter'] === 'past') ? 'active' : ''; ?>"><?php echo t('events.past'); ?></a>
                </div>
            </div>
        </aside>

        <!-- Contenu principal -->
        <div class="main-content">
            <div class="search-section">
                <form method="GET" class="search-form">
                    <input type="search" name="search" placeholder="<?php echo t('events.search_placeholder'); ?>" value="<?php echo htmlspecialchars($filters['search'], ENT_QUOTES, 'UTF-8'); ?>">
                    <button type="submit" class="btn btn-primary"><?php echo t('events.search_button'); ?></button>
                </form>
            </div>

            <!-- Carte Google Maps -->
            <div class="map-section">
                <div id="events-map" class="events-map"></div>
            </div>

    <?php if (empty($events)): ?>
        <p>Aucun événement trouvé.</p>
    <?php else: ?>
        <div class="events-grid">
            <?php foreach ($events as $event):
                $id = (int) $event['id'];
                $title = htmlspecialchars($event['title'], ENT_QUOTES, 'UTF-8');
                $excerpt = htmlspecialchars($event['excerpt'], ENT_QUOTES, 'UTF-8');
                $date = htmlspecialchars($event['date'], ENT_QUOTES, 'UTF-8');
                $time = htmlspecialchars($event['time'], ENT_QUOTES, 'UTF-8');
                $location = htmlspecialchars($event['location'], ENT_QUOTES, 'UTF-8');
                $category = htmlspecialchars($event['category'], ENT_QUOTES, 'UTF-8');
                $taken = (int) ($event['taken'] ?? 0);
                $places = (int) ($event['places'] ?? 0);
                $organizer = htmlspecialchars($event['organizer'] ?? '', ENT_QUOTES, 'UTF-8');
                $image = htmlspecialchars($event['image'] ?? '', ENT_QUOTES, 'UTF-8');
                $subscribed = !empty($event['subscribed']);
                $isFavorite = !empty($event['is_favorite']);
            ?>
                <a href="event-details.php?id=<?php echo $id; ?>" class="event-card">
                    <div class="card-media" style="background-image: url('<?php echo $image; ?>');">
                        <span class="badge"><?php echo $category; ?></span>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <button 
                                class="favorite-btn-card <?php echo $isFavorite ? 'active' : ''; ?>" 
                                data-activity-id="<?php echo $id; ?>"
                                onclick="event.preventDefault(); event.stopPropagation(); toggleFavorite(this);"
                                title="<?php echo $isFavorite ? 'Retirer des favoris' : 'Ajouter aux favoris'; ?>"
                            >
                                ❤️
                            </button>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <h3 class="card-title"><?php echo $title; ?></h3>
                        <p class="card-excerpt"><?php echo $excerpt; ?></p>

                        <div class="card-meta">
                            <span class="meta-item">📍 <?php echo $location; ?></span>
                            <span class="meta-item">📅 <?php echo $date; ?> <?php echo $time; ?></span>
                            <span class="meta-item">👤 <?php echo $organizer; ?></span>
                        </div>

                        <div class="card-footer">
                            <span class="places participant-count"><?php echo $taken; ?>/<?php echo $places; ?> inscrits</span>
                            <?php if ($subscribed): ?>
                                <button class="event-cta event-cta--subscribed btn-unsubscribe" data-activity-id="<?php echo $id; ?>">Se désinscrire</button>
                            <?php else: ?>
                                <button class="event-cta btn-subscribe" data-activity-id="<?php echo $id; ?>">S'inscrire</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
        </div><!-- fin main-content -->
    </div><!-- fin content-wrapper -->
</div>

<script src="../assets/js/activity-registration.js"></script>

<!-- Leaflet CSS et JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
// Initialiser la carte au chargement
document.addEventListener('DOMContentLoaded', function() {
    // Centre par défaut (France)
    const defaultCenter = [46.603354, 1.888334];
    
    // Création de la carte Leaflet
    const map = L.map('events-map').setView(defaultCenter, 6);
    
    // Ajouter la couche de tuiles OpenStreetMap
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 19,
        minZoom: 3
    }).addTo(map);
    
    // Données des événements
    const events = <?php echo json_encode($events, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
    
    // Groupe de marqueurs pour ajuster les bounds
    const markers = [];
    
    // Icône personnalisée ROUGE pour les marqueurs
    const redIcon = L.icon({
        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34],
        shadowSize: [41, 41]
    });
    
    // Coordonnées par défaut des villes françaises
    const cityCoordinates = {
        'Paris': [48.8566, 2.3522],
        'Lyon': [45.7640, 4.8357],
        'Marseille': [43.2965, 5.3698],
        'Toulouse': [43.6047, 1.4442],
        'Nice': [43.7102, 7.2620],
        'Nantes': [47.2184, -1.5536],
        'Strasbourg': [48.5734, 7.7521],
        'Montpellier': [43.6108, 3.8767],
        'Bordeaux': [44.8378, -0.5792],
        'Lille': [50.6292, 3.0573],
        'Rennes': [48.1173, -1.6778],
        'Reims': [49.2583, 4.0317],
        'Le Havre': [49.4944, 0.1079],
        'Saint-Étienne': [45.4397, 4.3872],
        'Toulon': [43.1242, 5.9280],
        'Grenoble': [45.1885, 5.7245],
        'Dijon': [47.3220, 5.0415],
        'Angers': [47.4784, -0.5632],
        'Nîmes': [43.8367, 4.3601],
        'Villeurbanne': [45.7667, 4.8833],
        'Clermont-Ferrand': [45.7772, 3.0870],
        'Le Mans': [48.0077, 0.1984],
        'Aix-en-Provence': [43.5297, 5.4474],
        'Brest': [48.3904, -4.4861],
        'Tours': [47.3941, 0.6848],
        'Amiens': [49.8942, 2.2957],
        'Limoges': [45.8336, 1.2611],
        'Annecy': [45.8992, 6.1294],
        'Perpignan': [42.6886, 2.8948],
        'Besançon': [47.2380, 6.0243],
        'Orléans': [47.9029, 1.9093],
        'Metz': [49.1193, 6.1757],
        'Rouen': [49.4431, 1.0993],
        'Mulhouse': [47.7508, 7.3359],
        'Caen': [49.1829, -0.3707],
        'Nancy': [48.6921, 6.1844],
        'Argenteuil': [48.9475, 2.2466],
        'Saint-Denis': [48.9362, 2.3574],
        'Montreuil': [48.8636, 2.4436],
        'Roubaix': [50.6942, 3.1746],
        'Tourcoing': [50.7236, 3.1609],
        'Nanterre': [48.8925, 2.2069],
        'Avignon': [43.9493, 4.8055],
        'Poitiers': [46.5802, 0.3404],
        'Versailles': [48.8014, 2.1301],
        'Courbevoie': [48.8976, 2.2532],
        'Créteil': [48.7903, 2.4555],
        'Pau': [43.2951, -0.3708],
        'Vitry-sur-Seine': [48.7873, 2.3937],
        'Calais': [50.9513, 1.8587],
        'La Rochelle': [46.1591, -1.1520],
        'Cannes': [43.5528, 7.0174],
        'Antibes': [43.5808, 7.1239],
        'Ajaccio': [41.9270, 8.7369],
        'Bastia': [42.7028, 9.4503]
    };
    
    // Fonction pour obtenir les coordonnées d'une ville depuis le mapping
    function getCityCoordinates(location) {
        for (const [city, coords] of Object.entries(cityCoordinates)) {
            if (location.includes(city)) {
                return coords;
            }
        }
        return null;
    }
    
    // Fonction pour géocoder une adresse avec Nominatim (avec fallback)
    async function geocodeAddress(address) {
        // D'abord essayer de trouver la ville dans notre mapping
        const cityCoords = getCityCoordinates(address);
        if (cityCoords) {
            return cityCoords;
        }
        
        // Sinon essayer le géocodage Nominatim
        try {
            const response = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}, France&limit=1`);
            const data = await response.json();
            if (data && data.length > 0) {
                return [parseFloat(data[0].lat), parseFloat(data[0].lon)];
            }
        } catch (error) {
            console.error('Erreur de géocodage:', error);
        }
        
        return null;
    }
    
    // Créer les marqueurs pour chaque événement
    let processedCount = 0;
    
    events.forEach(async (event) => {
        const coords = await geocodeAddress(event.location);
        
        if (coords) {
            // Créer le marqueur ROUGE
            const marker = L.marker(coords, { icon: redIcon }).addTo(map);
            
            // Contenu du popup
            const popupContent = `
                <div class="leaflet-popup-custom" style="min-width: 200px;">
                    <h3 style="margin: 0 0 10px 0; color: #2F4558; font-size: 16px; font-weight: 600;">${event.title}</h3>
                    <div style="margin: 8px 0; color: #666; font-size: 14px; line-height: 1.8;">
                        <div style="margin: 5px 0;"><strong>📍</strong> ${event.location}</div>
                        <div style="margin: 5px 0;"><strong>📅</strong> ${event.date} ${event.time}</div>
                        <div style="margin: 5px 0;"><strong>👥</strong> ${event.taken}/${event.places} inscrits</div>
                        <div style="margin: 5px 0;"><strong>🎯</strong> ${event.category}</div>
                    </div>
                    <a href="event-details.php?id=${event.id}" 
                       style="display: inline-block; margin-top: 10px; padding: 8px 16px; background: linear-gradient(135deg, #FF6B6B 0%, #EE5A5A 100%); color: white; text-decoration: none; border-radius: 20px; font-size: 14px; font-weight: 500; text-align: center; box-shadow: 0 2px 8px rgba(255, 107, 107, 0.3); transition: all 0.3s ease;">
                        Voir les détails →
                    </a>
                </div>
            `;
            
            marker.bindPopup(popupContent, {
                maxWidth: 300,
                className: 'custom-popup'
            });
            
            markers.push(marker);
        }
        
        processedCount++;
        
        // Ajuster la vue quand tous les marqueurs sont ajoutés
        if (processedCount === events.length && markers.length > 0) {
            const group = L.featureGroup(markers);
            map.fitBounds(group.getBounds().pad(0.1));
        }
    });
    
    // Si aucun événement, garder la vue par défaut
    if (events.length === 0) {
        map.setView(defaultCenter, 6);
    }
});

// Gestion des favoris
function toggleFavorite(button) {
    const activityId = button.dataset.activityId;
    const isActive = button.classList.contains('active');
    const action = isActive ? 'remove' : 'add';
    
    fetch('api/favorite-toggle.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `activity_id=${activityId}&action=${action}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            button.classList.toggle('active');
            button.title = button.classList.contains('active') ? 'Retirer des favoris' : 'Ajouter aux favoris';
            
            // Animation
            if (button.classList.contains('active')) {
                button.style.animation = 'heartBeat 0.3s ease';
                setTimeout(() => {
                    button.style.animation = '';
                }, 300);
            }
        } else {
            alert('Erreur: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        alert('Erreur de connexion. Vérifiez la console pour plus de détails.');
    });
}
</script>

<?php include '../includes/footer.php'; ?>