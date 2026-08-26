<?php
$replacements = [
    'Ã³' => 'ó',
    'Ã¡' => 'á',
    'Ã©' => 'é',
    'Ã­' => 'í',
    'Ã\xad' => 'í',
    'Ãº' => 'ú',
    'Ã±' => 'ñ',
    'Ã‘' => 'Ñ',
    'Â¿' => '¿',
    'Â¡' => '¡',
    'Ã“' => 'Ó',
    'Ã\x81' => 'Á',
    'Ã\x89' => 'É',
    'Ã\x8d' => 'Í',
    'Ã\x9a' => 'Ú'
];

function process_dir($dir) {
    global $replacements;
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $filepath = $file->getPathname();
            $content = file_get_contents($filepath);
            $new_content = $content;
            
            foreach ($replacements as $bad => $good) {
                $new_content = str_replace($bad, $good, $new_content);
            }
            
            if ($content !== $new_content) {
                file_put_contents($filepath, $new_content);
                echo "Fixed: $filepath\n";
            }
        }
    }
}

process_dir(__DIR__ . '/views');
?>
