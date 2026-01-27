import os
import glob

# Palette de couleurs
replacements = {
    '#667eea': '#55D5E0',  # Violet -> Bleu Turquoise
    '#764ba2': '#335F8A',  # Violet foncé -> Bleu foncé
    '#6ee0eb': '#55D5E0',  # Autre bleu -> Bleu Turquoise
    '#4a7fad': '#335F8A',  # Autre bleu foncé -> Bleu foncé
    '#f6b12d': '#F6B12D',  # Normaliser l'or (majuscules)
}

# Fichiers à modifier
files_to_update = [
    'pages/css/legal.css',
    'pages/css/friends.css',
    'pages/css/leaderboard.css',
    'pages/css/forum.css',
    'auth/reset-password.php',
    'auth/forgot-password.php',
    'auth/css/reset-password.css',
    'auth/css/verify-email.css',
    'events/css/event-create.css',
    'events/css/event-details.css',
    'profile/css/profile-edit.css',
]

base_dir = r'c:\Users\esteb\Documents\GitHub\APP_web_A1'

updated_count = 0
for file_path in files_to_update:
    full_path = os.path.join(base_dir, file_path)
    
    if not os.path.exists(full_path):
        print(f"❌ Fichier introuvable: {file_path}")
        continue
    
    try:
        with open(full_path, 'r', encoding='utf-8') as f:
            content = f.read()
        
        original_content = content
        for old_color, new_color in replacements.items():
            content = content.replace(old_color, new_color)
        
        if content != original_content:
            with open(full_path, 'w', encoding='utf-8') as f:
                f.write(content)
            print(f"✅ Mis à jour: {file_path}")
            updated_count += 1
        else:
            print(f"⏭️ Aucun changement: {file_path}")
    
    except Exception as e:
        print(f"❌ Erreur sur {file_path}: {str(e)}")

print(f"\n✅ {updated_count} fichiers mis à jour avec succès!")
print("Couleurs harmonisées: Bleu Turquoise (#55D5E0) et Or (#F6B12D)")
