<?php
// Test API response
ob_start();
$_GET['id'] = '1';
include 'public/api/products/product-detail.php';
$output = ob_get_clean();
echo 'Raw output: ' . $output . PHP_EOL;
echo 'Raw output length: ' . strlen($output) . PHP_EOL;
echo 'Raw output bytes: ';
for ($i = 0; $i < strlen($output); $i++) {
    echo ord($output[$i]) . ' ';
}
echo PHP_EOL;

$response = json_decode($output, true);
echo 'Decoded: ' . print_r($response, true) . PHP_EOL;
echo 'Success type: ' . gettype($response['success']) . PHP_EOL;
echo 'Success value: ' . var_export($response['success'], true) . PHP_EOL;
