<?php
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('pages'));
foreach ($files as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $content = file_get_contents($file->getRealPath());
        
        $pattern = '/(<div class="main-content">\s*)<div class="topbar(?:-left)?">.*?<div class="topbar-title">(.*?)<\/div>(.*?)<div class="page-body">/is';
        
        $newContent = preg_replace_callback($pattern, function($matches) {
            $main = $matches[1];
            $title = trim($matches[2]);
            
            $between = $matches[3];
            $actions = '';
            
            if (preg_match('/<div class="topbar-actions">(.*?)<\/div>/is', $between, $mAct)) {
                $actions = trim($mAct[1]);
            }
            
            $replacement = $main . 
                   '<div class="topbar">' . "\n" .
                   '    <div class="topbar-left">' . "\n" .
                   '        <button class="hamburger" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>' . "\n" .
                   '        <div class="topbar-title">' . "\n" .
                   '            ' . $title . "\n" .
                   '        </div>' . "\n" .
                   '    </div>' . "\n";
                   
            if ($actions) {
                $replacement .= 
                   '    <div class="topbar-actions">' . "\n" .
                   '        ' . $actions . "\n" .
                   '    </div>' . "\n";
            }
            
            $replacement .= '</div>' . "\n" . '<div class="page-body">';
            
            return $replacement;
        }, $content);
        
        if ($newContent !== $content && $newContent !== null) {
            file_put_contents($file->getRealPath(), $newContent);
            echo "Rebuilt topbar for " . $file->getPathname() . "\n";
        }
    }
}
