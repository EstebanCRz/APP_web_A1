// Initialiser la carte au chargement
document.addEventListener('DOMContentLoaded', function() {
    // Centre par défaut (France)
    const centreParDefaut = [46.603354, 1.888334];
    
    // Création de la carte Leaflet
    const carte = L.map('events-map').setView(centreParDefaut, 6);
    
    // Ajouter la couche de tuiles OpenStreetMap
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 19,
        minZoom: 3
    }).addTo(carte);
    
    // Données des événements (passées depuis PHP)
    const evenements = window.evenementsData || [];
    
    // Groupe de marqueurs pour ajuster les bounds
    const marqueurs = [];
    
    // Icône personnalisée ROUGE pour les marqueurs
    const iconeRouge = L.icon({
        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34],
        shadowSize: [41, 41]
    });
    
    // Coordonnées par défaut des villes françaises
    const coordonneesVilles = {
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
    function obtenirCoordonneesVille(localisation) {
        for (const [ville, coordonnees] of Object.entries(coordonneesVilles)) {
            if (localisation.includes(ville)) {
                return coordonnees;
            }
        }
        return null;
    }
    
    // Fonction pour géocoder une adresse avec Nominatim (avec fallback)
    async function geocoderAdresse(adresse) {
        // D'abord essayer de trouver la ville dans notre mapping
        const coordonneesVille = obtenirCoordonneesVille(adresse);
        if (coordonneesVille) {
            return coordonneesVille;
        }
        
        // Sinon essayer le géocodage Nominatim
        try {
            const reponse = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(adresse)}, France&limit=1`);
            const donnees = await reponse.json();
            if (donnees && donnees.length > 0) {
                return [parseFloat(donnees[0].lat), parseFloat(donnees[0].lon)];
            }
        } catch (erreur) {
            console.error('Erreur de géocodage:', erreur);
        }
        
        return null;
    }
    
    // Créer les marqueurs pour chaque événement
    let compteurTraites = 0;
    
    evenements.forEach(async (evenement) => {
        const coordonnees = await geocoderAdresse(evenement.location);
        
        if (coordonnees) {
            // Créer le marqueur ROUGE
            const marqueur = L.marker(coordonnees, { icon: iconeRouge }).addTo(carte);
            
            // Contenu du popup
            const contenuPopup = `
                <div class="leaflet-popup-custom" style="min-width: 200px;">
                    <h3 style="margin: 0 0 10px 0; color: #2F4558; font-size: 16px; font-weight: 600;">${evenement.title}</h3>
                    <div style="margin: 8px 0; color: #666; font-size: 14px; line-height: 1.8;">
                        <div style="margin: 5px 0;"><strong>📍</strong> ${evenement.location}</div>
                        <div style="margin: 5px 0;"><strong>📅</strong> ${evenement.date} ${evenement.time}</div>
                        <div style="margin: 5px 0;"><strong>👥</strong> ${evenement.taken}/${evenement.places} inscrits</div>
                        <div style="margin: 5px 0;"><strong>🎯</strong> ${evenement.category}</div>
                    </div>
                    <a href="event-details.php?id=${evenement.id}" 
                       style="display: inline-block; margin-top: 10px; padding: 8px 16px; background: linear-gradient(135deg, #FF6B6B 0%, #EE5A5A 100%); color: white; text-decoration: none; border-radius: 20px; font-size: 14px; font-weight: 500; text-align: center; box-shadow: 0 2px 8px rgba(255, 107, 107, 0.3); transition: all 0.3s ease;">
                        Voir les détails →
                    </a>
                </div>
            `;
            
            marqueur.bindPopup(contenuPopup, {
                maxWidth: 300,
                className: 'custom-popup'
            });
            
            marqueurs.push(marqueur);
        }
        
        compteurTraites++;
        
        // Ajuster la vue quand tous les marqueurs sont ajoutés
        if (compteurTraites === evenements.length && marqueurs.length > 0) {
            const groupe = L.featureGroup(marqueurs);
            carte.fitBounds(groupe.getBounds().pad(0.1));
        }
    });
    
    // Si aucun événement, garder la vue par défaut
    if (evenements.length === 0) {
        carte.setView(centreParDefaut, 6);
    }
});

// Gestion des favoris
function toggleFavorite(bouton) {
    const idActivite = bouton.dataset.activityId;
    const estActif = bouton.classList.contains('active');
    const action = estActif ? 'remove' : 'add';
    
    fetch('api/favorite-toggle.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `activity_id=${idActivite}&action=${action}`
    })
    .then(reponse => reponse.json())
    .then(donnees => {
        if (donnees.success) {
            bouton.classList.toggle('active');
            bouton.title = bouton.classList.contains('active') ? 'Retirer des favoris' : 'Ajouter aux favoris';
            
            // Animation
            if (bouton.classList.contains('active')) {
                bouton.style.animation = 'heartBeat 0.3s ease';
                setTimeout(() => {
                    bouton.style.animation = '';
                }, 300);
            }
        } else {
            alert('Erreur: ' + donnees.message);
        }
    })
    .catch(erreur => {
        console.error('Erreur:', erreur);
        alert('Erreur de connexion. Vérifiez la console pour plus de détails.');
    });
}

// Système de filtres collapsibles pour mobile
document.addEventListener('DOMContentLoaded', function() {
    // Fonction pour initialiser les filtres collapsibles
    function initCollapsibleFilters() {
        const filterGroups = document.querySelectorAll('.filter-group');
        
        // Déterminer si on est sur mobile
        const isMobile = window.innerWidth <= 560;
        
        if (isMobile) {
            filterGroups.forEach((group, index) => {
                const title = group.querySelector('h3');
                if (title) {
                    // Garder le premier groupe ouvert, replier les autres
                    if (index !== 0) {
                        group.classList.add('collapsed');
                    }
                    
                    // Ajouter l'événement click sur le titre
                    title.addEventListener('click', function() {
                        group.classList.toggle('collapsed');
                    });
                }
            });
        } else {
            // Sur desktop, tout ouvrir
            filterGroups.forEach(group => {
                group.classList.remove('collapsed');
                const title = group.querySelector('h3');
                if (title) {
                    // Retirer l'événement click
                    title.style.cursor = 'default';
                }
            });
        }
    }
    
    // Initialiser au chargement
    initCollapsibleFilters();
    
    // Réinitialiser au redimensionnement
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            initCollapsibleFilters();
        }, 250);
    });
});

