<?php
/**
 * System Settings API
 * DOKO Grocery E-commerce
 */

require_once '../ApiConfig.php';

try {
    // Apply rate limiting
    ApiRateLimit::check();
    
    $database = Database::getInstance();
    $conn = $database->getConnection();
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'GET':
            handleGetSettings($conn);
            break;
        case 'PUT':
        case 'POST':
            // Require admin access for updates
            ApiAuth::requireAdmin();
            handleUpdateSettings($conn);
            break;
        default:
            ApiResponse::error('Method not allowed', 405);
            break;
    }
    
} catch (Exception $e) {
    ApiLogger::error("Settings API error: " . $e->getMessage());
    ApiResponse::error('An error occurred: ' . $e->getMessage());
}

function handleGetSettings($conn) {
    $isPublic = isset($_GET['public']) && $_GET['public'] === '1';
    $key = isset($_GET['key']) ? trim($_GET['key']) : null;
    
    // Build query based on parameters
    $whereConditions = [];
    $params = [];
    
    if ($isPublic) {
        $whereConditions[] = "is_public = TRUE";
    } else {
        // Require admin access for non-public settings
        ApiAuth::requireAdmin();
    }
    
    if ($key) {
        $whereConditions[] = "setting_key = :key";
        $params[':key'] = $key;
    }
    
    $whereClause = $whereConditions ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
    
    $query = "SELECT setting_key, setting_value, setting_type, description, is_public, updated_at
              FROM system_settings 
              {$whereClause}
              ORDER BY setting_key";
    
    $stmt = $conn->prepare($query);
    foreach ($params as $paramKey => $value) {
        $stmt->bindValue($paramKey, $value);
    }
    $stmt->execute();
    
    $settings = $stmt->fetchAll();
    
    // Format settings based on type
    $formattedSettings = [];
    foreach ($settings as $setting) {
        $value = $setting['setting_value'];
        
        // Convert value based on type
        switch ($setting['setting_type']) {
            case 'number':
                $value = is_numeric($value) ? (float)$value : 0;
                break;
            case 'boolean':
                $value = (bool)$value;
                break;
            case 'json':
                $value = json_decode($value, true);
                break;
            default:
                // Keep as string
                break;
        }
        
        if ($key) {
            // Return single setting value
            ApiResponse::success($value, 'Setting retrieved successfully');
            return;
        }
        
        $formattedSettings[$setting['setting_key']] = [
            'value' => $value,
            'type' => $setting['setting_type'],
            'description' => $setting['description'],
            'is_public' => (bool)$setting['is_public'],
            'updated_at' => $setting['updated_at']
        ];
    }
    
    if ($key && empty($formattedSettings)) {
        ApiResponse::error('Setting not found', 404);
    }
    
    ApiResponse::success($formattedSettings, 'Settings retrieved successfully');
}

function handleUpdateSettings($conn) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        ApiResponse::error('Invalid JSON data', 400);
    }
    
    $updatedSettings = [];
    $errors = [];
    
    foreach ($input as $key => $value) {
        try {
            // Get current setting info
            $getQuery = "SELECT setting_id, setting_type, setting_value FROM system_settings WHERE setting_key = :key";
            $getStmt = $conn->prepare($getQuery);
            $getStmt->bindParam(':key', $key);
            $getStmt->execute();
            
            if ($getStmt->rowCount() === 0) {
                $errors[$key] = 'Setting not found';
                continue;
            }
            
            $currentSetting = $getStmt->fetch();
            
            // Validate and format value based on type
            $formattedValue = validateAndFormatSettingValue($value, $currentSetting['setting_type']);
            
            // Update setting
            $updateQuery = "UPDATE system_settings 
                           SET setting_value = :value, updated_at = NOW() 
                           WHERE setting_key = :key";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bindParam(':value', $formattedValue);
            $updateStmt->bindParam(':key', $key);
            $updateStmt->execute();
            
            $updatedSettings[$key] = [
                'old_value' => $currentSetting['setting_value'],
                'new_value' => $formattedValue,
                'type' => $currentSetting['setting_type']
            ];
            
            // Log the change
            logSettingChange($conn, $key, $currentSetting['setting_value'], $formattedValue);
            
        } catch (Exception $e) {
            $errors[$key] = $e->getMessage();
        }
    }
    
    $response = [
        'updated_settings' => $updatedSettings
    ];
    
    if (!empty($errors)) {
        $response['errors'] = $errors;
    }
    
    $message = count($updatedSettings) > 0 
        ? count($updatedSettings) . ' settings updated successfully'
        : 'No settings were updated';
    
    ApiResponse::success($response, $message);
}

function validateAndFormatSettingValue($value, $type) {
    switch ($type) {
        case 'number':
            if (!is_numeric($value)) {
                throw new Exception('Value must be a number');
            }
            return (string)$value;
            
        case 'boolean':
            if (!is_bool($value) && !in_array($value, ['0', '1', 0, 1, 'true', 'false'])) {
                throw new Exception('Value must be a boolean');
            }
            return $value ? '1' : '0';
            
        case 'json':
            if (is_array($value) || is_object($value)) {
                return json_encode($value);
            } else {
                // Try to decode as JSON first
                $decoded = json_decode($value, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new Exception('Value must be valid JSON');
                }
                return $value;
            }
            
        case 'string':
        default:
            return (string)$value;
    }
}

function logSettingChange($conn, $key, $oldValue, $newValue) {
    try {
        $currentUser = ApiAuth::getCurrentUser();
        
        if (!$currentUser) return;
        
        $logQuery = "INSERT INTO activity_logs (user_id, action, entity_type, entity_id, old_values, new_values, ip_address, user_agent) 
                     VALUES (:user_id, 'update', 'setting', NULL, :old_values, :new_values, :ip_address, :user_agent)";
        
        $logStmt = $conn->prepare($logQuery);
        $logStmt->bindParam(':user_id', $currentUser['user_id']);
        $logStmt->bindValue(':old_values', json_encode([$key => $oldValue]));
        $logStmt->bindValue(':new_values', json_encode([$key => $newValue]));
        $logStmt->bindValue(':ip_address', $_SERVER['REMOTE_ADDR'] ?? '');
        $logStmt->bindValue(':user_agent', $_SERVER['HTTP_USER_AGENT'] ?? '');
        $logStmt->execute();
        
    } catch (Exception $e) {
        // Log error but don't fail the main operation
        ApiLogger::warning("Failed to log setting change: " . $e->getMessage());
    }
}
?>
