import os
import re

# Palette de couleurs à remplacer
replacements = {
    # Violet -> Bleu Turquoise
    '#667eea': '#55D5E0',
    '#764ba2': '#335F8A',
    '#5568d3': '#4ab3bf',
    
    # Couleurs bleues diverses -> Turquoise
    '#007bff': '#55D5E0',
    '#0056b3': '#335F8A',
    '#17a2b8': '#55D5E0',
    '#138496': '#335F8A',
}

base_dir = r'c:\Users\esteb\Documents\GitHub\APP_web_A1\admin\css'

# Fichiers à modifier
files_to_update = [
    'admin-dashboard.css',
    'admin-forum.css',
    'admin-messages.css',
    'admin-content.css',
    'admin-events.css',
    'admin-users.css',
]

updated_count = 0
for filename in files_to_update:
    full_path = os.path.join(base_dir, filename)
    
    if not os.path.exists(full_path):
        print(f"❌ Fichier introuvable: {filename}")
        continue
    
    try:
        # Essayer plusieurs encodages
        content = None
        for encoding in ['utf-8', 'latin-1', 'cp1252']:
            try:
                with open(full_path, 'r', encoding=encoding) as f:
                    content = f.read()
                break
            except:
                continue
        
        if content is None:
            print(f"❌ Impossible de lire: {filename}")
            continue
        
        original_content = content
        for old_color, new_color in replacements.items():
            content = content.replace(old_color, new_color)
        
        if content != original_content:
            with open(full_path, 'w', encoding='utf-8') as f:
                f.write(content)
            print(f"✅ Mis à jour: {filename}")
            updated_count += 1
        else:
            print(f"⏭️ Aucun changement: {filename}")
    
    except Exception as e:
        print(f"❌ Erreur sur {filename}: {str(e)}")

print(f"\n✅ {updated_count} fichiers CSS admin mis à jour!")
print("Thème harmonisé: Bleu Turquoise (#55D5E0) et Or (#F6B12D)")
