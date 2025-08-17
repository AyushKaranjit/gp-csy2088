<?php
/**
 * Secure Remote Product Image Fetch & Cache
 * POST JSON: { "product_id":123, "url":"https://...", "force_primary":true? }
 * - Validates domain & content-type
 * - Downloads and stores under /uploads/products/
 * - Creates product_images record, optionally primary
 * - Updates products.image_url if primary
 */
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: http://localhost');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') { http_response_code(200); exit; }

session_start();
require_once '../../../config/database.php';
require_once '../../../src/Controllers/AuthController.php';

try {
    $auth = new AuthController();
    if (!$auth->isAdmin()) { http_response_code(403); echo json_encode(['success'=>false,'message'=>'Unauthorized']); return; }
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') { http_response_code(405); echo json_encode(['success'=>false,'message'=>'Method not allowed']); return; }

    $input = json_decode(file_get_contents('php://input'), true) ?: [];
    $productId = (int)($input['product_id'] ?? 0);
    $remoteUrl = trim($input['url'] ?? '');
    $forcePrimary = !empty($input['force_primary']);
    if ($productId <=0 || $remoteUrl==='') throw new Exception('product_id and url required');
    if (!preg_match('#^https?://#i',$remoteUrl)) throw new Exception('url must start with http/https');

    // Basic domain allowlist (expand or configure via env IMAGE_DOMAIN_ALLOWLIST comma separated)
    $envList = getenv('IMAGE_DOMAIN_ALLOWLIST');
    // By default do not allow external hosts. To enable, set IMAGE_DOMAIN_ALLOWLIST to a comma separated list.
    $allowedDomains = $envList ? array_filter(array_map('trim', explode(',', $envList))) : [];
    $host = parse_url($remoteUrl, PHP_URL_HOST) ?: '';
    if ($allowedDomains && !in_array($host, $allowedDomains, true)) {
        throw new Exception('Domain not allowed: ' . $host);
    }

    $pdo = Database::getInstance()->getConnection();
    // Verify product exists
    $ps = $pdo->prepare('SELECT product_id FROM products WHERE product_id=?');
    $ps->execute([$productId]);
    if (!$ps->fetch()) throw new Exception('Product not found');

    // HEAD request to validate
    $ch = curl_init($remoteUrl);
    curl_setopt_array($ch,[CURLOPT_NOBODY=>true,CURLOPT_RETURNTRANSFER=>true,CURLOPT_FOLLOWLOCATION=>true,CURLOPT_TIMEOUT=>10]);
    curl_exec($ch);
    $ct = curl_getinfo($ch,CURLINFO_CONTENT_TYPE) ?: '';
    $httpCode = curl_getinfo($ch,CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($httpCode >=400) throw new Exception('Remote resource not accessible (HTTP '.$httpCode.')');
    if (!preg_match('#^image/(jpeg|png|webp|gif)#i',$ct)) throw new Exception('Unsupported content-type: '.$ct);

    // GET body
    $ch2 = curl_init($remoteUrl);
    curl_setopt_array($ch2,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_FOLLOWLOCATION=>true,CURLOPT_TIMEOUT=>20]);
    $data = curl_exec($ch2);
    $err = curl_error($ch2);
    $http2 = curl_getinfo($ch2,CURLINFO_HTTP_CODE);
    curl_close($ch2);
    if ($data === false || $http2 >=400) throw new Exception('Download failed: '.($err ?: 'HTTP '.$http2));
    if (strlen($data) > 5*1024*1024) throw new Exception('Image exceeds 5MB limit');
    $contentHash = sha1($data);

    // De-dupe: if hash already stored for this product, reuse
    $pdo->exec("CREATE TABLE IF NOT EXISTS product_image_hashes (
        hash_id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT NOT NULL,
        content_hash CHAR(40) NOT NULL,
        image_url VARCHAR(1000) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uniq_prod_hash (product_id, content_hash),
        INDEX (content_hash)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $hs = $pdo->prepare('SELECT image_url FROM product_image_hashes WHERE product_id=? AND content_hash=? LIMIT 1');
    $hs->execute([$productId,$contentHash]);
    $existing = $hs->fetchColumn();

    if ($existing) {
        echo json_encode(['success'=>true,'message'=>'Image already exists (hash match)','product_id'=>$productId,'stored_url'=>$existing,'primary'=>false,'dedup'=>true]);
        return;
    }

    $extMap = ['image/jpeg'=>'jpg','image/jpg'=>'jpg','image/png'=>'png','image/webp'=>'webp','image/gif'=>'gif'];
    $ext = $extMap[strtolower($ct)] ?? 'jpg';
    $filename = 'product_'. $productId . '_' . substr(sha1($remoteUrl.microtime(true)),0,12) . '.' . $ext;
    $absBase = realpath(__DIR__ . '/../../'); // .../public
    $uploads = $absBase . '/uploads';
    if (!is_dir($uploads) && !mkdir($uploads,0755,true)) throw new Exception('Cannot create uploads base');
    $prodDir = $uploads . '/products';
    if (!is_dir($prodDir) && !mkdir($prodDir,0755,true)) throw new Exception('Cannot create products dir');
    $absPath = $prodDir . '/' . $filename;
    if (file_put_contents($absPath,$data) === false) throw new Exception('Failed saving image');

    // Ensure product_images
    $pdo->exec("CREATE TABLE IF NOT EXISTS product_images (
        image_id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT NOT NULL,
        image_url VARCHAR(1000) NOT NULL,
        is_primary TINYINT(1) DEFAULT 0,
        sort_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (product_id),
        CONSTRAINT fk_product_images_product FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $primaryNeeded = $forcePrimary;
    if (!$primaryNeeded) {
        $chk = $pdo->prepare('SELECT 1 FROM product_images WHERE product_id=? AND is_primary=1 LIMIT 1');
        $chk->execute([$productId]);
        $primaryNeeded = !$chk->fetch();
    }
    if ($primaryNeeded) {
        $pdo->prepare('UPDATE product_images SET is_primary=0 WHERE product_id=? AND is_primary=1')->execute([$productId]);
    }

    $relUrl = '/uploads/products/' . basename($absPath);
    $ins = $pdo->prepare('INSERT INTO product_images (product_id,image_url,is_primary,sort_order) VALUES (?,?,?,0)');
    $ins->execute([$productId,$relUrl,$primaryNeeded?1:0]);

    // Update shortcut column if exists and primary
    $col = $pdo->query("SHOW COLUMNS FROM products LIKE 'image_url'")->fetch();
    if ($col && $primaryNeeded) {
        $pdo->prepare('UPDATE products SET image_url=? WHERE product_id=?')->execute([$relUrl,$productId]);
    }

    // Record hash
    $pdo->prepare('INSERT INTO product_image_hashes (product_id,content_hash,image_url) VALUES (?,?,?)')->execute([$productId,$contentHash,$relUrl]);

    echo json_encode([
        'success'=>true,
        'message'=>'Image fetched & stored',
        'product_id'=>$productId,
        'stored_url'=>$relUrl,
        'primary'=>$primaryNeeded,
        'content_type'=>$ct,
        'size_bytes'=>strlen($data),
        'hash'=>$contentHash
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}
?>
