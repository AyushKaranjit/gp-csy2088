<?php
function loadTemplate($title, $content) {
    // Use a default HTML layout instead of loading a file
    echo "<!DOCTYPE html>\n";
    echo "<html lang='en'>\n<head>\n<meta charset='UTF-8'>\n<meta name='viewport' content='width=device-width, initial-scale=1.0'>\n<title>" . htmlspecialchars($title) . "</title>\n<link rel='stylesheet' href='/websites/Doko/assets/css/style.css'>\n</head>\n<body>\n";
    echo $content;
    echo "</body>\n</html>";
}
