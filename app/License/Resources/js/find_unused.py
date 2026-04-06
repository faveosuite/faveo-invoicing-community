import os
import glob
import re

vue_files = glob.glob('**/*.vue', recursive=True)
all_files = glob.glob('**/*.vue', recursive=True) + glob.glob('**/*.js', recursive=True)

unused = []

for v in vue_files:
    if 'Layouts/LicenseManagerLayout.vue' in v or 'Layouts/LicenseManagerRenderer.vue' in v:
        continue # skip layouts as they might be entry points

    filename = os.path.basename(v)
    name_no_ext = os.path.splitext(filename)[0]
    
    found = False
    for f in all_files:
        if f == v:
            continue
        with open(f, 'r', encoding='utf-8', errors='ignore') as file_obj:
            content = file_obj.read()
            if filename in content or name_no_ext in content:
                found = True
                break
    
    if not found:
        unused.append(v)

for u in unused:
    print(u)
