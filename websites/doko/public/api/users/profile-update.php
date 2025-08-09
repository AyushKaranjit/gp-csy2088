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

    $allowed = ['first_name','last_name','phone','date_of_birth','gender'];
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

    if (empty($updates)) {
        ApiResponse::error('No valid fields provided', 400);
    }

    // Handle optional profile image upload
    $imagePathClause = '';
    if (!empty($_FILES['profile_image']) && is_uploaded_file($_FILES['profile_image']['tmp_name'])) {
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
            $targetDir = realpath(__DIR__.'/../../../uploads');
            if (!$targetDir) { $targetDir = __DIR__.'/../../../uploads'; }
            $profileDir = $targetDir.'/profiles';
            if (!is_dir($profileDir)) { @mkdir($profileDir, 0775, true); }
            $dest = $profileDir.'/'.$filename;
            if (!move_uploaded_file($file['tmp_name'], $dest)) {
                ApiResponse::error('Failed to store profile image', 500);
            }
            // Remove previous file (best effort)
            $pdoPrev = db()->getConnection();
            $prevStmt = $pdoPrev->prepare('SELECT profile_image FROM users WHERE user_id = ?');
            $prevStmt->execute([$_SESSION['user_id']]);
            $prev = $prevStmt->fetch(PDO::FETCH_ASSOC);
            if ($prev && !empty($prev['profile_image']) && strpos($prev['profile_image'], 'profiles/') !== false) {
                $oldPath = realpath(__DIR__.'/../../../'.ltrim($prev['profile_image'],'/')); // assume stored relative like /uploads/profiles/...
                if ($oldPath && file_exists($oldPath)) { @unlink($oldPath); }
            }
            $relative = '/uploads/profiles/'.$filename;
            $updates[] = 'profile_image = ?';
            $params[] = $relative;
        } else {
            ApiResponse::error('Image upload error code '.$file['error'], 400);
        }
    }

    $params[] = $_SESSION['user_id'];
    $sql = 'UPDATE users SET '.implode(', ', $updates).', updated_at = NOW() WHERE user_id = ?';
    $pdo = db()->getConnection();
    $stmt = $pdo->prepare($sql);
    if (!$stmt->execute($params)) {
        ApiResponse::error('Failed to update profile', 500);
    }

    // Return updated row snippet & refresh session cache so UI reflects changes immediately
    $sel = $pdo->prepare('SELECT user_id, username, email, first_name, last_name, phone, date_of_birth, gender, profile_image, role FROM users WHERE user_id = ?');
    $sel->execute([$_SESSION['user_id']]);
    $user = $sel->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        // Update session values (only those we allow to change / expose)
        foreach (['first_name','last_name','phone','date_of_birth','gender','profile_image'] as $k) {
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
