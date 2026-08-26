<?php
$files = [
    'views/layouts/sidebar_admin.php',
    'views/layouts/sidebar_cliente.php',
    'includes/nav_barbero.php',
    'includes/nav_admin.php',
    'includes/nav_cliente.php'
];

$fouc = "?>\n<!-- Script para evitar FOUC (parpadeo visual) al cargar colapsado -->\n<script>\n    if (localStorage.getItem('sidebar_collapsed') === 'true') {\n        document.body.classList.add('sidebar-collapsed');\n    }\n</script>";

$toggle = <<<HTML
        <div class="sidebar-brand-text">
            <span class="sidebar-brand-name">MC BARBER</span>
            <span class="sidebar-brand-sub">$1</span>
        </div>
        <button class="desktop-toggle" onclick="toggleDesktopSidebar()" title="Colapsar menú">
            <i class="bi bi-chevron-left"></i>
        </button>
    </div>
HTML;

$js = <<<JS
    };
}

function toggleDesktopSidebar() {
    document.body.classList.toggle('sidebar-collapsed');
    if (document.body.classList.contains('sidebar-collapsed')) {
        localStorage.setItem('sidebar_collapsed', 'true');
    } else {
        localStorage.setItem('sidebar_collapsed', 'false');
    }
}
</script>
JS;

foreach ($files as $file) {
    if (!file_exists($file)) {
        echo "Skipping $file\n";
        continue;
    }
    
    $content = file_get_contents($file);
    
    if (strpos($content, "localStorage.getItem('sidebar_collapsed')") === false) {
        $content = preg_replace('/\?>/', $fouc, $content, 1);
    }
    
    if (strpos($content, "desktop-toggle") === false) {
        $content = preg_replace('/<div class="sidebar-brand-text">\s*<span class="sidebar-brand-name">MC BARBER<\/span>\s*<span class="sidebar-brand-sub">([^<]+)<\/span>\s*<\/div>\s*<\/div>/s', $toggle, $content);
    }
    
    if (strpos($content, "toggleDesktopSidebar") === false) {
        $content = preg_replace('/    };\n}\n<\/script>/', $js, $content);
    }
    
    file_put_contents($file, $content);
    echo "Patched $file\n";
}
