<?php
require_once '../functions/loadTemplate.php';
$template = loadTemplate('home.html.php');
assert(strpos($template, 'Welcome to Grocery Website') !== false);
