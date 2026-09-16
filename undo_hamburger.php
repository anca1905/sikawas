<?php
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('pages'));
foreach ($files as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $content = file_get_contents($file->getRealPath());
        
        $newContent = preg_replace(
            '/(<div class="main-content">\s*)<div class="topbar-left">\\\\s\*<button class="hamburger"<\/button>/is',
            '$1<div class="topbar">' . "\n" . '                <div class="topbar-left">' . "\n" . '                    <button class="hamburger" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>',
            $content
        );

        // if the previous regex didn't match because the <div class="main-content"> is on a different line, try another way
        if ($newContent === $content) {
            $newContent = preg_replace(
                '/<div class="topbar-left">\\\\s\*<button class="hamburger"<\/button>/is',
                '<div class="topbar">' . "\n" . '                <div class="topbar-left">' . "\n" . '                    <button class="hamburger" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>',
                $content
            );
        }

        if ($newContent !== $content && $newContent !== null) {
            file_put_contents($file->getRealPath(), $newContent);
            echo "Fixed " . $file->getPathname() . "\n";
        }
    }
}
