import os
import re

files_to_patch = [
    'views/layouts/sidebar_admin.php',
    'views/layouts/sidebar_cliente.php',
    'includes/nav_barbero.php',
    'includes/nav_admin.php',
    'includes/nav_cliente.php'
]

fouc_script = """?>
<!-- Script para evitar FOUC (parpadeo visual) al cargar colapsado -->
<script>
    if (localStorage.getItem('sidebar_collapsed') === 'true') {
        document.body.classList.add('sidebar-collapsed');
    }
</script>"""

toggle_btn = """        <div class="sidebar-brand-text">
            <span class="sidebar-brand-name">MC BARBER</span>
            <span class="sidebar-brand-sub">\\1</span>
        </div>
        <button class="desktop-toggle" onclick="toggleDesktopSidebar()" title="Colapsar menú">
            <i class="bi bi-chevron-left"></i>
        </button>
    </div>"""

js_func = """    };
}

function toggleDesktopSidebar() {
    document.body.classList.toggle('sidebar-collapsed');
    if (document.body.classList.contains('sidebar-collapsed')) {
        localStorage.setItem('sidebar_collapsed', 'true');
    } else {
        localStorage.setItem('sidebar_collapsed', 'false');
    }
}
</script>"""

for file_path in files_to_patch:
    if not os.path.exists(file_path):
        print(f"Skipping {file_path}, does not exist")
        continue

    with open(file_path, 'r', encoding='utf-8') as f:
        content = f.read()

    # 1. Replace first ?>
    if 'localStorage.getItem(\'sidebar_collapsed\')' not in content:
        content = re.sub(r'\?>', fouc_script, content, count=1)

    # 2. Add button
    if 'desktop-toggle' not in content:
        content = re.sub(r'        <div class="sidebar-brand-text">\s*<span class="sidebar-brand-name">MC BARBER</span>\s*<span class="sidebar-brand-sub">([^<]+)</span>\s*</div>\s*</div>', toggle_btn, content)

    # 3. Add JS function
    if 'function toggleDesktopSidebar' not in content:
        content = re.sub(r'    };\n}\n</script>', js_func, content)

    with open(file_path, 'w', encoding='utf-8') as f:
        f.write(content)

    print(f"Patched {file_path}")
