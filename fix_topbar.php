<?php
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('pages'));
foreach ($files as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $content = file_get_contents($file->getRealPath());

        // Regex to find everything from <div class="topbar"> (or the broken one) up to <div class="page-body">
        // Since my previous script ran, it's currently `<div class="topbar">...` but there might be mismatched divs.
        
        // We can extract the title content and actions content.
        // Let's use preg_match to find topbar-title content
        preg_match('/<div class="topbar-title">(.*?)<\/div>\s*</is', $content, $titleMatch);
        $titleContent = $titleMatch ? trim($titleMatch[1]) : '';

        // Let's use preg_match to find topbar-actions content
        // The actions div might have inner divs, so this is tricky. But most actions are just spans and buttons/links.
        preg_match('/<div class="topbar-actions">(.*?)<\/div>\s*</is', $content, $actionMatch);
        // Wait, what if topbar-actions has nested elements? 
        // In dashboard.php: <span...></span> <a>...</a></div></div>
        
        // Let's just fix the HTML using string replacements.
        // What we want:
        // 1. Ensure <div class="topbar-title">...</div> is followed by </div> (to close topbar-left).
        // 2. Ensure <div class="topbar-actions">...</div> is followed by </div> (to close topbar).
        
        // Actually, since the contents vary and tags might be heavily unbalanced, the safest way is a PHP DOM parser, 
        // OR a very careful regex that replaces the block from `<div class="topbar">` to `<div class="page-body">`.
    }
}
