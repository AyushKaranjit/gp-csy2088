<?php
// Register a function that will be called whenever PHP needs to load a class
spl_autoload_register(function ($className) {
    // Convert namespace separators to directory separators
    $className = str_replace('\\', '/', $className);
    
    // Build the full file path
    // __DIR__ is the directory containing this file
    $file = __DIR__ . '/src/' . $className . '.php';
    
    // Only include the file if it exists
    if (file_exists($file)) {
        require $file;
    }
});
?>