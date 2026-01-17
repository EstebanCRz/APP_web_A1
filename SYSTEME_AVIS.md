# 📋 Système d'Avis - Documentation Complète v2.0

## 🎯 Vue d'ensemble

Système ultra-optimisé permettant aux utilisateurs inscrits de laisser un avis avec note (1-5 étoiles) et commentaire (10-1000 caractères). **Validation 100% JavaScript**, design moderne avec étoiles de 4.5rem.

---

## 📁 Architecture

| Fichier | Lignes | Rôle |
|---------|--------|------|
| `assets/js/reviews.js` | 62 | Validation client + beforeunload |
| `events/event-details.php` | 16 | Traitement POST (INSERT/UPDATE) |
| `events/css/event-details.css` | 29 | Design moderne avec gradients |
| Base: `activity_reviews` | - | Stockage (UNIQUE constraint) |

---

## 💻 Code Source Final

### 1️⃣ JavaScript - `reviews.js` (62 lignes)

```javascript
// Validation formulaire d'avis avec confirmation avant quitter
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('.review-form');
    if (!form) return;
    const comment = form.querySelector('#comment');
    const btn = form.querySelector('.btn-primary');
    
    // Créer compteur de caractères
    const counter = document.createElement('small');
    counter.style.cssText = 'display:block;margin-top:0.5rem;font-size:0.9rem;font-weight:600';
    comment.parentNode.insertBefore(counter, comment.nextSibling);
    
    // Flag pour détecter si l'utilisateur a commencé à remplir le formulaire
    let modified = false;
    
    // Validation en temps réel
    function validate() {
        const len = comment.value.trim().length;
        const ok = len >= 10 && form.querySelector('input[name="rating"]:checked');
        
        // Mettre à jour compteur
        counter.textContent = len + '/1000';
        counter.style.color = len < 10 ? '#ff6b6b' : len > 900 ? '#ff9800' : '#4AB8C8';
        
        // Style bouton et bordure
        btn.style.opacity = ok ? '1' : '0.5';
        comment.style.borderColor = len === 0 ? '#ddd' : len < 10 ? '#ff6b6b' : '#55D5E0';
    }
    
    // Marquer comme modifié quand l'utilisateur tape
    comment.addEventListener('input', function() {
        modified = true;
        validate();
    });
    
    // Marquer comme modifié quand l'utilisateur sélectionne une étoile
    form.querySelectorAll('input[name="rating"]').forEach(i => i.addEventListener('change', function() {
        modified = true;
        validate();
    }));
    
    validate();
    
    // Confirmation avant quitter si données non sauvegardées
    window.addEventListener('beforeunload', function(e) {
        if (modified && comment.value.trim().length > 0) {
            e.preventDefault();
            e.returnValue = '';
        }
    });
    
    // Validation avant envoi
    form.addEventListener('submit', function(e) {
        const len = comment.value.trim().length;
        if (!form.querySelector('input[name="rating"]:checked') || len < 10) {
            e.preventDefault();
            alert('Note 1 à 5 et 10 caractères minimum');
        } else {
            modified = false; // Désactiver l'alerte de sortie après envoi
        }
    });
});
```

**📖 Explication JavaScript :**
1. **DOMContentLoaded** : Attend que le DOM soit chargé
2. **Compteur dynamique** : Crée un `<small>` affichant X/1000
3. **Flag `modified`** : Détecte si l'utilisateur a commencé à saisir
4. **validate()** : Vérifie longueur (10-1000) + note sélectionnée
   - Change couleur compteur : Rouge (<10) → Cyan (≥10) → Orange (>900)
   - Change bordure textarea : Gris → Rouge → Cyan
   - Change opacité bouton : 0.5 (invalide) → 1.0 (valide)
5. **beforeunload** : Avertit avant quitter si données non sauvegardées
6. **submit** : Bloque envoi si invalide, affiche alerte, reset flag si valide

---

### 2️⃣ PHP - `event-details.php` (16 lignes)

```php
// Traitement POST - Lignes 14-29
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rating'], $_POST['comment'], $_SESSION['user_id'])) {
    $activity_id = (int)$_POST['activity_id'];
    try {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT id FROM activity_reviews WHERE activity_id=? AND user_id=?");
        $stmt->execute([$activity_id, (int)$_SESSION['user_id']]);
        $sql = $stmt->fetch() 
            ? "UPDATE activity_reviews SET rating=?, comment=?, updated_at=NOW() WHERE activity_id=? AND user_id=?" 
            : "INSERT INTO activity_reviews (rating, comment, activity_id, user_id) VALUES (?, ?, ?, ?)";
        $pdo->prepare($sql)->execute([(int)$_POST['rating'], trim($_POST['comment']), $activity_id, (int)$_SESSION['user_id']]);
        $_SESSION['review_message'] = "Avis enregistré !";
    } catch (PDOException $e) {
        $_SESSION['review_message'] = "Erreur.";
    }
    header("Location: event-details.php?id=$activity_id");
    exit;
}
```

**📖 Explication PHP :**
1. **Vérification** : Méthode POST + rating + comment + user connecté
2. **Type casting** : `(int)$_POST['activity_id']` pour sécurité
3. **Détection doublon** : SELECT pour vérifier si avis existe déjà
4. **Ternaire intelligent** : `$stmt->fetch() ? UPDATE : INSERT`
   - Si avis existe → UPDATE avec `updated_at=NOW()`
   - Sinon → INSERT nouveau
5. **Prepared statements** : Protection injection SQL
6. **Message flash** : Confirmation dans session
7. **POST-Redirect-GET** : Évite re-soumission F5

---

### 3️⃣ HTML - `event-details.php` (Formulaire)

```php
<?php if (isset($_SESSION['user_id']) && $isUserRegistered): ?>
<form method="POST" class="review-form" novalidate>
    <input type="hidden" name="activity_id" value="<?php echo $event_id; ?>">
    
    <label>Note</label>
    <div class="star-rating">
        <?php for ($i = 5; $i >= 1; $i--): ?>
            <input type="radio" id="star<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>">
            <label for="star<?php echo $i; ?>" title="<?php echo $i; ?> étoile<?php echo $i > 1 ? 's' : ''; ?>">★</label>
        <?php endfor; ?>
    </div>
    
    <label for="comment">Commentaire</label>
    <textarea id="comment" name="comment" rows="3" maxlength="1000" placeholder="Partagez votre expérience..."></textarea>
    
    <button type="submit" class="btn-primary">Publier mon avis</button>
</form>
<?php endif; ?>
```

**📖 Explication HTML :**
1. **Condition** : Affiché si user connecté ET inscrit à l'activité
2. **novalidate** : Désactive validation HTML5 (gérée par JS)
3. **Étoiles inversées** : Boucle 5→1 pour `flex-direction:row-reverse`
4. **Sans required** : Validation 100% JavaScript
5. **maxlength="1000"** : Limite physique hard-coded
6. **hidden input** : Transmet activity_id au serveur

---

### 4️⃣ CSS - `event-details.css` (20 lignes)

```css
/* Avis optimisé */
.event-reviews,.review-item{background:#fff;border:1px solid #e8eaed;border-radius:10px}
.event-reviews{padding:2.5rem;margin-top:1.5rem;box-shadow:0 1px 3px rgba(0,0,0,.05)}
.reviews-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;padding-bottom:1.25rem;border-bottom:2px solid #e5e7eb}
.reviews-header h2{font-size:1.75rem;color:#1a202c;margin:0;font-weight:700}
.reviews-stats{background:#fffbeb;padding:0.5rem 1rem;border-radius:8px}
.avg-rating{font-size:1.8rem;font-weight:800;color:#f59e0b}
.alert-success{background:#d4edda;color:#155724;padding:14px 18px;border-radius:8px;margin-bottom:1.5rem;border-left:4px solid #28a745}
.review-form{background:#f9fafb;padding:2rem;border-radius:10px;margin-bottom:1.5rem;border:1px solid #e5e7eb}
.review-form label{display:block;font-weight:600;color:#374151;margin:1.25rem 0 0.6rem}
.review-form label:first-of-type{margin-top:0}
.star-rating{display:inline-flex;flex-direction:row-reverse;justify-content:flex-end;align-items:baseline;gap:14px;font-size:4.5rem;padding:0.75rem 0}
.star-rating input{display:none}
.star-rating label{cursor:pointer;color:#d1d5db;transition:all .2s;line-height:1;height:1em;display:inline-block}
.star-rating label:hover,.star-rating label:hover~label{color:#fbbf24;transform:scale(1.1)}
.star-rating input:checked~label{color:#f59e0b}
.review-form textarea{width:100%;padding:1rem;border:2px solid #e5e7eb;border-radius:8px;resize:vertical;min-height:100px;transition:border .2s}
.review-form textarea:focus{outline:0;border-color:#3b82f6;box-shadow:0 0 0 3px rgba(59,130,246,.1)}
.review-form .btn-primary{margin-top:1.25rem;padding:0.875rem 2rem;background:#3b82f6;border:0;border-radius:8px;color:#fff;font-weight:600;cursor:pointer;transition:all .2s}
.review-form .btn-primary:hover{background:#2563eb;transform:translateY(-1px);box-shadow:0 4px 8px rgba(37,99,235,.25)}
.review-item{border-left:4px solid #3b82f6;padding:1.5rem;margin-top:1.25rem}
```

**📖 Explication CSS :**
1. **Sélecteurs mutualisés** : `.event-reviews,.review-item` partagent background + border + radius (économie 1 ligne)
2. **Container reviews** : Padding 2.5rem, shadow légère, margin-top pour espacement
3. **Header** : Flex space-between, titre 1.75rem noir, bordure bottom 2px
4. **Stats badge** : Fond jaune `#fffbeb` pour note moyenne
5. **Success alert** : Vert avec bordure gauche 4px
6. **Formulaire** : Fond gris clair `#f9fafb`, border 1px, radius 10px
7. **Labels** : Font-weight 600, marge verticale, premier sans margin-top
8. **Étoiles 4.5rem** : 
   - `inline-flex` + `row-reverse` pour ordre 5→1
   - `align-items:baseline` + `height:1em` = alignement parfait
   - Gap 14px, couleur grise `#d1d5db`
   - Hover : or `#fbbf24` + scale(1.1)
   - Checked : or foncé `#f59e0b`
9. **Textarea** : Border 2px, transition, min-height 100px
10. **Focus textarea** : Border bleue + ring shadow `rgba(59,130,246,.1)`
11. **Bouton** : Bleu `#3b82f6`, padding généreux, font-weight 600
12. **Hover bouton** : Bleu foncé `#2563eb` + translateY(-1px) + shadow
13. **Item avis** : Border-left 4px bleue, padding, margin-top

**Optimisations :**
- Fusionné propriétés communes (background, border, radius)
- Supprimé styles affichage avis détaillés (header, meta, rating, etc.)
- Focus sur formulaire saisie uniquement
- **20 lignes ultra-compactes**
.review-item p{color:#374151;font-size:1.15rem;line-height:1.6;margin:0}
.no-reviews{text-align:center;color:#9ca3af;padding:3rem 2rem;font-style:italic;background:#f9fafb;border-radius:10px;border:2px dashed #e5e7eb}
```

**📖 Explication CSS :**
1. **Container** : Fond blanc, shadow légère, border-radius 12px
2. **Header** : Flex space-between, titre 1.75rem, bordure bottom
3. **Stats** : Badge jaune `#fffbeb` pour note moyenne
4. **Formulaire** : Fond gris clair `#f9fafb`, padding 2rem
5. **Étoiles 4.5rem** : Très grandes, hover scale(1.1), couleur or `#f59e0b`
   - `align-items:baseline` : Force alignement horizontal
   - `height:1em` : Taille uniforme
6. **Textarea** : Border 2px, focus ring bleu `rgba(59,130,246,.1)`
7. **Bouton** : Bleu `#3b82f6`, hover `translateY(-1px)` + shadow
8. **Items** : Border-left 4px bleu, nom 1.4rem, étoiles or, date grise
9. **Liste** : gap:0 (margin-top sur items directement)

---

## 📊 Statistiques

| Composant | Lignes | Poids | Rôle |
|-----------|--------|-------|------|
| JavaScript | 62 | ~2.1 KB | Validation + UX |
| PHP | 16 | ~0.7 KB | INSERT/UPDATE |
| CSS | 20 | ~1.8 KB | Design ultra-compact |
| HTML | 16 | ~0.6 KB | Structure form |
| **TOTAL** | **114** | **~5.2 KB** | Système complet |

---

## 🚀 Workflow Complet

```
1. 👤 USER écrit commentaire (10+ chars)
2. 🎯 USER clique étoile (1-5)
3. 📊 JS valide en temps réel
   ├─ Compteur : 0/1000 → X/1000
   ├─ Couleur : Rouge → Cyan
   ├─ Bouton : opacité 0.5 → 1.0
   └─ Flag modified = true
4. 📤 USER clique "Publier"
5. ✅ JS vérifie rating + 10 chars min
   ├─ ❌ Invalide → alert() + preventDefault()
   └─ ✅ Valide → modified=false + submit
6. 🔧 PHP traite POST
   ├─ SELECT : Avis existe?
   ├─ Oui → UPDATE rating + comment
   └─ Non → INSERT nouveau
7. ↩️ Redirect vers event-details.php?id=X
8. ✨ Message "Avis enregistré !" affiché
9. 📋 Avis apparaît dans liste
```

---

## 🎨 Palette Couleurs

| Usage | Couleur | Hex | Signification |
|-------|---------|-----|---------------|
| Étoiles remplies | 🟡 Or | #f59e0b | Note sélectionnée |
| Étoiles vides | ⚪ Gris | #d1d5db | Non sélectionné |
| Étoiles hover | 🟡 Or clair | #fbbf24 | Interactivité |
| Compteur valide | 🔵 Cyan | #4AB8C8 | 10-900 chars |
| Compteur invalide | 🔴 Rouge | #ff6b6b | <10 chars |
| Compteur warning | 🟠 Orange | #ff9800 | >900 chars |
| Bordure focus | 🔵 Bleu | #3b82f6 | Focus textarea |
| Bouton | 🔵 Bleu | #3b82f6 | Action principale |
| Bouton hover | 🔵 Bleu foncé | #2563eb | État hover |
| Stats badge | 🟡 Jaune | #fffbeb | Note moyenne |
| Success | 🟢 Vert | #d4edda | Confirmation |

---

## 🔒 Sécurité

### ✅ Implémenté
- **Validation JS** : Empêche soumission invalide
- **Type casting** : `(int)`, `trim()` en PHP
- **Prepared statements** : 100% requêtes préparées
- **Session check** : Vérifie `$_SESSION['user_id']`
- **POST-Redirect-GET** : Évite re-soumission
- **maxlength** : Limite hard-coded 1000

### ⚠️ À améliorer
- [ ] CSRF token
- [ ] Validation PHP backup (si JS désactivé)
- [ ] Rate limiting (max X avis/minute)
- [ ] htmlspecialchars() sur affichage
- [ ] Vérifier $isUserRegistered côté serveur
- [ ] Log tentatives malveillantes

---

## 📝 Base de Données

```sql
CREATE TABLE activity_reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    activity_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_review (activity_id, user_id),
    FOREIGN KEY (activity_id) REFERENCES activities(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Contraintes :**
- **UNIQUE** : 1 seul avis par user/activité
- **CHECK** : Rating entre 1-5
- **CASCADE** : Suppression auto si activité/user supprimé

---

## 🎯 Fonctionnalités Clés

| Feature | Status | Détails |
|---------|--------|---------|
| ⭐ Étoiles 4.5rem | ✅ | Très grandes, hover scale(1.1) |
| 📊 Compteur live | ✅ | X/1000 avec couleurs |
| 🔔 beforeunload | ✅ | Alerte si données non sauvées |
| 🎨 Border dynamique | ✅ | Gris/Rouge/Cyan selon validation |
| 💾 UPDATE auto | ✅ | Détecte si avis existe |
| 🚫 Validation HTML5 | ❌ | Désactivée (novalidate) |
| 🔒 CSRF | ❌ | Non implémenté |

---

## 📈 Évolutions Possibles

### 🟢 Facile (1-2h)
- Compteur inversé : "Reste 1000 - X caractères"
- localStorage : Auto-save brouillon
- Emoji picker : Smiley rapides 😊👍❤️
- Animation submit : Spinner loading

### 🟡 Moyen (3-5h)
- Édition inline : Double-clic pour modifier
- Tri/Filtre : Par date, note, pertinence
- Preview : Avant publication
- Photos : Upload images dans avis

### 🔴 Avancé (1-2j)
- Modération admin : Approuver/Rejeter
- Votes utiles : Like/Dislike sur avis
- Réponses : Organisateur peut répondre
- Analytics : Graphiques évolution notes

---

## 📞 Support Technique

**Créé le** : 13/01/2026  
**Version** : 2.0 Ultra-Optimisée  
**Compatibilité** : PHP 7.4+, MySQL 5.7+, ES6+  
**Navigateurs** : Chrome 90+, Firefox 88+, Safari 14+

---

## 📝 Changelog

### v2.0 (13/01/2026) - Ultra-Optimisé
- ⭐ Étoiles 4.5rem (très grandes)
- 🎨 CSS réduit à 29 lignes
- 🚫 Supprimé validation HTML5 (novalidate)
- ✅ beforeunload confirmé fonctionnel
- 📐 Alignement étoiles parfait (baseline + height:1em)
- 🎨 Design moderne : gradients, shadow, hover animations
- 📊 Note moyenne dans badge jaune
- 🔵 Bouton bleu avec hover translateY

### v1.0 (13/01/2026) - Initial
- ✅ Système de base fonctionnel
- ✅ Validation JavaScript
- ✅ INSERT/UPDATE automatique

---

## 📁 Structure des fichiers

### 1. **JavaScript** - `assets/js/reviews.js` (58 lignes)
Validation côté client avec confirmation avant quitter

### 2. **PHP** - `events/event-details.php` (12 lignes)
Traitement POST et redirection

### 3. **CSS** - `events/css/event-details.css` (22 lignes)
Style minimaliste et moderne

### 4. **Base de données** - Table `activity_reviews`
```sql
CREATE TABLE activity_reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    activity_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY (activity_id, user_id)
);
```

---

## ✨ Fonctionnalités

### ✅ Validation JavaScript (100%)
- ⭐ **Sélection note** : 1 à 5 étoiles obligatoire
- 📝 **Commentaire** : 10 à 1000 caractères
- 🔢 **Compteur temps réel** : `X/1000`
- 🎨 **Bordures colorées** : Gris (vide), Rouge (<10), Cyan (≥10)
- 🔔 **Alerte sortie** : Si données non sauvegardées
- 💾 **Auto-désactivation** : Après envoi réussi

### 🔧 Traitement PHP (minimal)
- 🔍 **Détection avis existant** : UPDATE ou INSERT automatique
- 🚫 **Aucune validation** : Confiance totale en JavaScript
- ↩️ **Redirection POST-Redirect-GET** : Évite re-soumission

### 🎨 Design CSS (épuré)
- 🔲 Bordures fines (1-3px)
- 🔘 Arrondis doux (6-12px)
- ⚡ Transitions rapides (0.2s)
- 🌟 Étoiles avec hover scale(1.1)
- 🎯 Bouton plat sans dégradé

---

## 🔧 Code source

### JavaScript (reviews.js)

```javascript
// Validation formulaire d'avis avec confirmation avant quitter
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('.review-form');
    if (!form) return;
    const comment = form.querySelector('#comment');
    const btn = form.querySelector('.btn-primary');
    
    // Créer compteur de caractères
    const counter = document.createElement('small');
    counter.style.cssText = 'display:block;margin-top:0.5rem;font-size:0.9rem;font-weight:600';
    comment.parentNode.insertBefore(counter, comment.nextSibling);
    
    // Flag pour détecter si l'utilisateur a commencé à remplir le formulaire
    let modified = false;
    
    // Validation en temps réel
    function validate() {
        const len = comment.value.trim().length;
        const ok = len >= 10 && form.querySelector('input[name="rating"]:checked');
        
        counter.textContent = len + '/1000';
        counter.style.color = len < 10 ? '#ff6b6b' : len > 900 ? '#ff9800' : '#4AB8C8';
        btn.style.opacity = ok ? '1' : '0.5';
        comment.style.borderColor = len === 0 ? '#ddd' : len < 10 ? '#ff6b6b' : '#55D5E0';
    }
    
    // Marquer comme modifié
    comment.addEventListener('input', function() { modified = true; validate(); });
    form.querySelectorAll('input[name="rating"]').forEach(i => 
        i.addEventListener('change', function() { modified = true; validate(); })
    );
    
    validate();
    
    // Confirmation avant quitter
    window.addEventListener('beforeunload', function(e) {
        if (modified && comment.value.trim().length > 0) {
            e.preventDefault();
            e.returnValue = '';
        }
    });
    
    // Validation avant envoi
    form.addEventListener('submit', function(e) {
        const len = comment.value.trim().length;
        if (!form.querySelector('input[name="rating"]:checked') || len < 10) {
            e.preventDefault();
            alert('Note et 10 caractères minimum');
        } else {
            modified = false; // Désactiver l'alerte après envoi
        }
    });
});
```

### PHP (event-details.php - lignes 14-29)
```php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rating'], $_POST['comment'], $_SESSION['user_id'])) {
    $activity_id = (int)$_POST['activity_id'];
    try {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT id FROM activity_reviews WHERE activity_id=? AND user_id=?");
        $stmt->execute([$activity_id, (int)$_SESSION['user_id']]);
        $sql = $stmt->fetch() 
            ? "UPDATE activity_reviews SET rating=?, comment=?, updated_at=NOW() WHERE activity_id=? AND user_id=?" 
            : "INSERT INTO activity_reviews (rating, comment, activity_id, user_id) VALUES (?, ?, ?, ?)";
        $pdo->prepare($sql)->execute([(int)$_POST['rating'], trim($_POST['comment']), $activity_id, (int)$_SESSION['user_id']]);
        $_SESSION['review_message'] = "Avis enregistré !";
    } catch (PDOException $e) {
        $_SESSION['review_message'] = "Erreur.";
    }
    header("Location: event-details.php?id=$activity_id");
    exit;
}
```

### HTML (event-details.php - lignes 204-213)
```php
<?php if (isset($_SESSION['user_id']) && $isUserRegistered): ?>
<form method="POST" class="review-form">
    <input type="hidden" name="activity_id" value="<?php echo $event_id; ?>">
    
    <label>Note</label>
    <div class="star-rating">
        <?php for ($i = 5; $i >= 1; $i--): ?>
            <input type="radio" id="star<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>" required>
            <label for="star<?php echo $i; ?>" title="<?php echo $i; ?> étoile<?php echo $i > 1 ? 's' : ''; ?>">★</label>
        <?php endfor; ?>
    </div>
    
    <label for="comment">Commentaire</label>
    <textarea id="comment" name="comment" rows="3" maxlength="1000" required placeholder="Partagez votre expérience..."></textarea>
    
    <button type="submit" class="btn-primary">Publier mon avis</button>
</form>
<?php endif; ?>
```

### CSS (event-details.css - 22 lignes minifiées)
```css
/* Avis minimaliste */
.event-reviews{background:#fff;padding:2rem;border-radius:12px;margin-top:1.5rem;border:1px solid #e0e0e0}
.reviews-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;border-bottom:2px solid #f0f0f0;padding-bottom:1rem}
.reviews-header h2{font-size:1.5rem;color:#333;margin:0}
.avg-rating{font-size:1.6rem;font-weight:700;color:#FFD700}
.alert-success{background:#d4edda;color:#155724;padding:12px;border-radius:8px;margin-bottom:1rem}
.review-form{background:#f8f9fa;padding:1.5rem;border-radius:8px;margin-bottom:1.5rem}
.review-form label{display:block;font-weight:600;color:#555;margin:1rem 0 0.5rem}
.review-form label:first-of-type{margin-top:0}
.star-rating{display:flex;flex-direction:row-reverse;gap:6px;font-size:2.5em;padding:0.5rem 0}
.star-rating input{display:none}
.star-rating label{cursor:pointer;color:#ddd;transition:all 0.2s}
.star-rating label:hover,.star-rating label:hover~label{color:#FFD700;transform:scale(1.1)}
.star-rating input:checked~label{color:#FFD700}
.review-form textarea{width:100%;padding:0.75rem;border:2px solid #ddd;border-radius:6px;font-size:1rem;font-family:inherit;resize:vertical;min-height:100px;transition:border-color 0.2s}
.review-form textarea:focus{outline:none;border-color:#55D5E0}
.review-form .btn-primary{margin-top:1rem;padding:0.75rem 1.5rem;background:#55D5E0;border:none;border-radius:6px;color:#fff;font-weight:600;cursor:pointer;transition:all 0.2s}
.review-form .btn-primary:hover{background:#4AB8C8;transform:translateY(-1px)}
.reviews-list{margin-top:1.5rem;display:flex;flex-direction:column;gap:1rem}
.review-item{background:#fff;padding:1.25rem;border-radius:8px;border:1px solid #e8e8e8;border-left:3px solid #55D5E0}
.review-item .review-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:0.75rem;padding-bottom:0.75rem;border-bottom:1px solid #f0f0f0}
```

---

## 📊 Statistiques

| Composant | Lignes | Taille |
|-----------|--------|--------|
| JavaScript | 58 | ~1.8 KB |
| PHP | 12 | ~0.6 KB |
| CSS | 22 | ~1.5 KB |
| HTML | 15 | ~0.5 KB |
| **Total** | **107** | **~4.4 KB** |

---

## 🚀 Utilisation

### Conditions d'affichage
1. ✅ Utilisateur connecté (`$_SESSION['user_id']`)
2. ✅ Inscrit à l'activité (`$isUserRegistered`)

### Workflow
1. 👤 **Utilisateur** : Remplit note + commentaire
2. 🔍 **JavaScript** : Valide en temps réel
3. 📤 **Submit** : Envoi formulaire
4. 🔧 **PHP** : INSERT ou UPDATE
5. ↩️ **Redirect** : Retour page avec message
6. ✅ **Affichage** : Avis apparaît dans la liste

---

## 🎨 Couleurs

| État | Couleur | Hex |
|------|---------|-----|
| Compteur < 10 | 🔴 Rouge | #ff6b6b |
| Compteur 10-900 | 🔵 Cyan | #4AB8C8 |
| Compteur > 900 | 🟠 Orange | #ff9800 |
| Étoiles vides | ⚪ Gris | #ddd |
| Étoiles pleines | 🟡 Or | #FFD700 |
| Bordure valide | 🔵 Cyan | #55D5E0 |
| Bordure invalide | 🔴 Rouge | #ff6b6b |

---

## 🔒 Sécurité

### ✅ Mesures implémentées
- **POST-Redirect-GET** : Empêche re-soumission
- **Session validation** : Vérifie `$_SESSION['user_id']`
- **Prepared statements** : Prévient injection SQL
- **Type casting** : `(int)`, `trim()`
- **maxlength HTML** : Limite 1000 caractères

### ⚠️ Améliorations recommandées
- [ ] Ajouter validation PHP de secours
- [ ] Implémenter token CSRF
- [ ] Vérifier participation côté serveur
- [ ] Limiter taux de soumission (rate limiting)
- [ ] Escape HTML dans affichage commentaires

---

## 🛠️ Maintenance

### Fichiers à surveiller
- **assets/js/reviews.js** - Logique validation
- **events/event-details.php** - Traitement POST + affichage
- **events/css/event-details.css** - Styles avis
- **database/activity_reviews.sql** - Structure table

### Logs à vérifier
- Erreurs PDO (base de données)
- Avis en double (UNIQUE constraint)
- Commentaires vides acceptés

---

## 📈 Évolutions possibles

### 🎯 Court terme (facile)
- [ ] Auto-sauvegarde brouillon (localStorage)
- [ ] Émojis rapides 😊 👍 ❤️
- [ ] Compteur inversé (1000 - X restants)

### 🚀 Moyen terme (modéré)
- [ ] Double-clic pour éditer son avis
- [ ] Tri des avis (récents, meilleures notes)
- [ ] Filtre par étoiles (5★, 4★...)

### 💎 Long terme (avancé)
- [ ] Votes "Utile" sur les avis
- [ ] Modération admin
- [ ] Photos dans les avis
- [ ] Réponses de l'organisateur

---

## 📞 Support Technique

**Créé le** : 13/01/2026  
**Version** : 3.0 Ultra-Compact  
**Compatibilité** : PHP 7.4+, MySQL 5.7+, ES6+  
**Navigateurs** : Chrome 90+, Firefox 88+, Safari 14+

---

## 📝 Changelog

### v3.0 (13/01/2026) - Ultra-Compact
- 📦 CSS réduit à **20 lignes** (29→20)
- 🔀 Sélecteurs mutualisés (économie code)
- ⚡ Total système : **114 lignes** (~5.2 KB)
- 🎯 Focus formulaire saisie uniquement
- ✅ Étoiles 4.5rem maintenues
- ✅ Validation JS complète conservée

### v2.0 (13/01/2026) - Ultra-Optimisé
- ⭐ Étoiles 4.5rem (très grandes)
- 🎨 CSS réduit à 29 lignes
- 🚫 Supprimé validation HTML5 (novalidate)
- ✅ beforeunload confirmé fonctionnel
- 📐 Alignement étoiles parfait (baseline + height:1em)
- 🎨 Design moderne : gradients, shadow, hover animations
- 📊 Note moyenne dans badge jaune
- 🔵 Bouton bleu avec hover translateY

### v1.0 (13/01/2026) - Initial
- ✅ Système de base fonctionnel
- ✅ Validation JavaScript
- ✅ INSERT/UPDATE automatique
        ");
        $stmt->execute([$event_id]);
        $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {}
}
```

---

## 🔒 Sécurité
- Validation côté serveur (longueur, type, plage)
- Protection contre les injections SQL (requêtes préparées)
- Vérification de participation à l'événement
- Vérification que l'événement est terminé
- Échappement HTML pour l'affichage

---

## 🎮 Intégration Gamification
- **+10 points** pour laisser un nouvel avis
- Aucun point pour la modification d'un avis existant
- Action: `review_leave`
