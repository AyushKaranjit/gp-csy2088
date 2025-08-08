<?php
/**
 * User Addresses CRUD API
 * Endpoints:
 *  GET    /api/users/addresses.php              -> list current user's addresses
 *  POST   /api/users/addresses.php              -> create address (JSON body)
 *  PUT    /api/users/addresses.php?id={id}      -> update address
 *  PATCH  /api/users/addresses.php?id={id}      -> set default
 *  DELETE /api/users/addresses.php?id={id}      -> delete address
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204); exit;
}

session_start();
require_once __DIR__ . '/../../../config/database.php';

function current_user_id() {
    return $_SESSION['user_id'] ?? null;
}

function json_input() {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    $userId = current_user_id();
    if (!$userId) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Authentication required']);
        exit;
    }

    $method = $_SERVER['REQUEST_METHOD'];
    if ($method === 'GET') {
        $stmt = $conn->prepare("SELECT address_id, address_type, address_label, street_address, city, state, postal_code, country, landmark, is_default, created_at FROM user_addresses WHERE user_id = ? ORDER BY is_default DESC, created_at DESC");
        $stmt->execute([$userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'addresses' => $rows, 'count' => count($rows)]);
        exit;
    }

    if ($method === 'POST') {
        $data = json_input();
        $required = ['street_address','city','state','postal_code'];
        foreach ($required as $f) { if (empty($data[$f])) { http_response_code(422); echo json_encode(['success'=>false,'message'=>"Missing field: $f"]); exit; } }
        $isDefault = !empty($data['is_default']) ? 1 : 0;
        if ($isDefault) { $conn->prepare("UPDATE user_addresses SET is_default=0 WHERE user_id=?")->execute([$userId]); }
        $stmt = $conn->prepare("INSERT INTO user_addresses (user_id,address_type,address_label,street_address,city,state,postal_code,country,landmark,is_default) VALUES (?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute([
            $userId,
            $data['address_type'] ?? 'home',
            $data['address_label'] ?? null,
            $data['street_address'],
            $data['city'],
            $data['state'],
            $data['postal_code'],
            $data['country'] ?? 'Nepal',
            $data['landmark'] ?? null,
            $isDefault
        ]);
        echo json_encode(['success'=>true,'address_id'=>$conn->lastInsertId()]);
        exit;
    }

    if ($method === 'PUT') {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0; if ($id<=0){ http_response_code(400); echo json_encode(['success'=>false,'message'=>'Invalid id']); exit; }
        $data = json_input();
        $fields = ['address_type','address_label','street_address','city','state','postal_code','country','landmark','is_default'];
        $sets=[];$params=[];
        foreach($fields as $f){ if(array_key_exists($f,$data)){ $sets[]="$f = ?"; $params[]=$f==='is_default'?(int)!empty($data[$f]):$data[$f]; }}
        if (!$sets){ http_response_code(400); echo json_encode(['success'=>false,'message'=>'No fields to update']); exit; }
        if (isset($data['is_default']) && $data['is_default']) { $conn->prepare("UPDATE user_addresses SET is_default=0 WHERE user_id=?")->execute([$userId]); }
        $params[]=$userId; $params[]=$id;
        $sql = "UPDATE user_addresses SET ".implode(',', $sets)." WHERE user_id = ? AND address_id = ?";
        $stmt=$conn->prepare($sql); $stmt->execute($params);
        echo json_encode(['success'=>true,'updated'=>$stmt->rowCount()>0]);
        exit;
    }

    if ($method === 'PATCH') {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0; if ($id<=0){ http_response_code(400); echo json_encode(['success'=>false,'message'=>'Invalid id']); exit; }
        $conn->prepare("UPDATE user_addresses SET is_default=0 WHERE user_id=?")->execute([$userId]);
        $conn->prepare("UPDATE user_addresses SET is_default=1 WHERE user_id=? AND address_id=?")->execute([$userId,$id]);
        echo json_encode(['success'=>true]);
        exit;
    }

    if ($method === 'DELETE') {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0; if ($id<=0){ http_response_code(400); echo json_encode(['success'=>false,'message'=>'Invalid id']); exit; }
        $stmt = $conn->prepare("DELETE FROM user_addresses WHERE user_id=? AND address_id=?");
        $stmt->execute([$userId,$id]);
        echo json_encode(['success'=>true,'deleted'=>$stmt->rowCount()>0]);
        exit;
    }

    http_response_code(405);
    echo json_encode(['success'=>false,'message'=>'Method not allowed']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>'Server error','error'=>$e->getMessage()]);
}
?>
