<?php
// User profile update endpoint
require __DIR__ . '/../_bootstrap.php';
use Doko\Http\ApiResponse;

require_method('POST');

try {
    ensure_session();
    if (empty($_SESSION['user_id'])) {
        ApiResponse::error('Authentication required', 401);
    }

    // Accept multipart/form-data or JSON
    $input = [];
    if (isset($_SERVER['CONTENT_TYPE']) && stripos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
        $decoded = json_input();
        if (is_array($decoded)) { $input = $decoded; }
    } else {
        foreach ($_POST as $k => $v) { $input[$k] = trim((string)$v); }
    }

    // Determine schema type (new extended users table vs legacy simple one)
    $pdoProbe = db()->getConnection();
    $columns = $pdoProbe->query("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_COLUMN);
    $isExtended = in_array('first_name',$columns) && in_array('last_name',$columns);
    $hasAddress = in_array('address',$columns);
    $hasEmail = in_array('email',$columns);
    $hasProfileImage = in_array('profile_image',$columns);
    // Build allowed list dynamically
    $allowed = [];
    foreach(['first_name','last_name','phone','date_of_birth','gender','email','address'] as $f){
        if (($f==='email' && !$hasEmail) || ($f==='address' && !$hasAddress)) continue;
        if (!$isExtended && in_array($f,['first_name','last_name','date_of_birth','gender'])) continue; // legacy table lacks these
        $allowed[] = $f;
    }
    $updates = [];
    $params = [];
    foreach ($allowed as $field) {
        if (array_key_exists($field, $input) && $input[$field] !== '') {
            // Basic validation
            if (in_array($field, ['first_name','last_name']) && strlen($input[$field]) > 50) {
                ApiResponse::error('Field '.$field.' too long', 400);
            }
            if ($field === 'gender' && !in_array($input[$field], ['male','female','other'], true)) {
                ApiResponse::error('Invalid gender', 400);
            }
            if ($field === 'date_of_birth' && $input[$field] && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $input[$field])) {
                ApiResponse::error('Invalid date format (YYYY-MM-DD)', 400);
            }
            $updates[] = "$field = ?";
            $params[] = $input[$field];
        }
    }

    if (empty($updates) && (empty($_FILES['profile_image']) || !is_uploaded_file($_FILES['profile_image']['tmp_name']))) {
        ApiResponse::error('No valid fields provided', 400);
    }

    // Handle optional profile image upload
    $imagePathClause = '';
    if ($hasProfileImage && !empty($_FILES['profile_image']) && is_uploaded_file($_FILES['profile_image']['tmp_name'])) {
        $file = $_FILES['profile_image'];
        if ($file['error'] === UPLOAD_ERR_OK) {
            $maxSize = 2 * 1024 * 1024; // 2MB
            if ($file['size'] > $maxSize) {
                ApiResponse::error('Profile image exceeds 2MB limit', 400);
            }
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            $allowedMimes = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
            if (!isset($allowedMimes[$mime])) {
                ApiResponse::error('Unsupported image type', 400);
            }
            $ext = $allowedMimes[$mime];
            $hash = substr(sha1_file($file['tmp_name']).uniqid('', true),0,32);
            $filename = $hash.'.'.$ext;
            // Ensure files are saved inside the public directory so they are web-accessible
            // Try to resolve public directory robustly
            $publicBase = realpath(__DIR__ . '/../../'); // typically repository/.../public
            if (!$publicBase) {
                // Fallback: try __DIR__ one level up
                $possible = realpath(__DIR__ . '/..');
                $publicBase = $possible ?: (__DIR__ . '/../../');
            }
            // Normalize and ensure uploads directory exists
            $profileDir = rtrim($publicBase, "\/\\") . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'profiles';
            if (!is_dir($profileDir)) { if (!@mkdir($profileDir, 0775, true)) { ApiResponse::error('Unable to create upload directory', 500); } }
            $dest = $profileDir . DIRECTORY_SEPARATOR . $filename;
            // Try move_uploaded_file, but as a fallback use copy + unlink
            $moved = false;
            if (is_uploaded_file($file['tmp_name'])) {
                $moved = @move_uploaded_file($file['tmp_name'], $dest);
            }
            if (!$moved) {
                // fallback copy
                if (@copy($file['tmp_name'], $dest)) {
                    $moved = true; @unlink($file['tmp_name']);
                }
            }
            if (!$moved) {
                ApiResponse::error('Failed to store profile image', 500);
            }
            // Remove previous file (best effort)
            $pdoPrev = db()->getConnection();
            $prevStmt = $pdoPrev->prepare('SELECT profile_image FROM users WHERE user_id = ?');
            $prevStmt->execute([$_SESSION['user_id']]);
            $prev = $prevStmt->fetch(PDO::FETCH_ASSOC);
            if ($prev && !empty($prev['profile_image']) && strpos($prev['profile_image'], 'profiles/') !== false) {
                // Try both legacy (project root/uploads) and new (public/uploads) locations
                $candidates = [
                    realpath(__DIR__.'/../../../'.ltrim($prev['profile_image'],'/')),
                    realpath($publicBase.'/'.ltrim($prev['profile_image'],'/'))
                ];
                foreach($candidates as $oldPath){ if ($oldPath && file_exists($oldPath)) { @unlink($oldPath); } }
            }
            $relative = '/uploads/profiles/'.$filename; // web path
            if ($hasProfileImage){
                $updates[] = 'profile_image = ?';
                $params[] = $relative;
            }
        } else {
            ApiResponse::error('Image upload error code '.$file['error'], 400);
        }
    }

    $userId = $_SESSION['user_id'] ?? null;
    if (!$userId) {
        ApiResponse::error('User ID not found in session', 401);
    }
    
    $params[] = $userId;
    // Choose primary key column name
    $pk = in_array('user_id',$columns) ? 'user_id' : (in_array('id',$columns)?'id':'user_id');
    $setClause = implode(', ', $updates);
    if ($setClause) { $setClause .= ', updated_at = NOW()'; } else { $setClause = 'updated_at = NOW()'; }
    $sql = 'UPDATE users SET '.$setClause.' WHERE '.$pk.' = ?';
    $pdo = db()->getConnection();
    $stmt = $pdo->prepare($sql);
    if (!$stmt->execute($params)) {
        ApiResponse::error('Failed to update profile', 500);
    }

    // Return updated row snippet & refresh session cache
    $selectCols = [];
    foreach(['user_id','id','username','email','first_name','last_name','phone','address','date_of_birth','gender','profile_image','role'] as $c){ if(in_array($c,$columns)) $selectCols[]=$c; }
    $sel = $pdo->prepare('SELECT '.implode(',', $selectCols).' FROM users WHERE '.$pk.' = ?');
    $sel->execute([$userId]);
    $user = $sel->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        // Update session values (only those we allow to change / expose)
        foreach (['first_name','last_name','phone','address','email','date_of_birth','gender','profile_image'] as $k) {
            if (array_key_exists($k, $user)) {
                $_SESSION[$k] = $user[$k];
            }
        }
    }
    ApiResponse::success(['message' => 'Profile updated', 'user' => $user]);
} catch (Throwable $e) {
    error_log('profile-update error: '.$e->getMessage());
    ApiResponse::error('Failed to update profile', 500, ['exception' => $e->getMessage()]);
}
