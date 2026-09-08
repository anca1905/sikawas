<?php
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('pages'));
foreach ($files as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $content = file_get_contents($file->getRealPath());
        $newContent = preg_replace(
            '/(<div class="topbar">\s*)<button class="hamburger"(.*?)<\/button>\s*<div class="topbar-left">/is',
            '<div class="topbar-left">\s*<button class="hamburger"</button>',
            $content
        );
        if ($newContent !== $content && $newContent !== null) {
            file_put_contents($file->getRealPath(), $newContent);
            echo "Fixed " . $file->getPathname() . "\n";
        }
    }
}
