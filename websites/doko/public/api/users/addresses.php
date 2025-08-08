<?php
// Refactored user addresses endpoint using bootstrap & ApiResponse
require __DIR__ . '/../_bootstrap.php';
use Doko\Http\ApiResponse;

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

try {
    ensure_session();
    if (empty($_SESSION['user_id'])) { ApiResponse::error('Authentication required', 401); }
    $db = db();
    $conn = $db->getConnection();
    $userId = (int)$_SESSION['user_id'];
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET') {
        $stmt = $conn->prepare("SELECT address_id, address_type, address_label, street_address, city, state, postal_code, country, landmark, is_default, created_at FROM user_addresses WHERE user_id = ? ORDER BY is_default DESC, created_at DESC");
        $stmt->execute([$userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        ApiResponse::success(['addresses' => $rows, 'count' => count($rows)]);
    }
    elseif ($method === 'POST') {
        $data = json_input();
        foreach (['street_address','city','state','postal_code'] as $f) { if (empty($data[$f])) { ApiResponse::error("Missing field: $f", 422); } }
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
        ApiResponse::success(['address_id' => $conn->lastInsertId()], 201);
    }
    elseif ($method === 'PUT') {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0; if ($id<=0) { ApiResponse::error('Invalid id', 400); }
        $data = json_input();
        $fields = ['address_type','address_label','street_address','city','state','postal_code','country','landmark','is_default'];
        $sets=[];$params=[];
        foreach ($fields as $f) { if (array_key_exists($f,$data)) { $sets[]="$f = ?"; $params[] = $f==='is_default'?(int)!empty($data[$f]):$data[$f]; } }
        if (!$sets) { ApiResponse::error('No fields to update', 400); }
        if (!empty($data['is_default'])) { $conn->prepare("UPDATE user_addresses SET is_default=0 WHERE user_id=?")->execute([$userId]); }
        $params[] = $userId; $params[] = $id;
        $sql = 'UPDATE user_addresses SET '.implode(',', $sets).' WHERE user_id = ? AND address_id = ?';
        $stmt = $conn->prepare($sql); $stmt->execute($params);
        ApiResponse::success(['updated' => $stmt->rowCount() > 0]);
    }
    elseif ($method === 'PATCH') {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0; if ($id<=0) { ApiResponse::error('Invalid id', 400); }
        $conn->prepare("UPDATE user_addresses SET is_default=0 WHERE user_id=?")->execute([$userId]);
        $conn->prepare("UPDATE user_addresses SET is_default=1 WHERE user_id=? AND address_id=?")->execute([$userId,$id]);
        ApiResponse::success(['message' => 'Default address updated']);
    }
    elseif ($method === 'DELETE') {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0; if ($id<=0) { ApiResponse::error('Invalid id', 400); }
        $stmt = $conn->prepare("DELETE FROM user_addresses WHERE user_id=? AND address_id=?");
        $stmt->execute([$userId,$id]);
        ApiResponse::success(['deleted' => $stmt->rowCount() > 0]);
    }
    else {
        ApiResponse::error('Method not allowed', 405);
    }
} catch (Throwable $e) {
    error_log('addresses error: '.$e->getMessage());
    ApiResponse::error('Server error', 500, ['exception' => $e->getMessage()]);
}
