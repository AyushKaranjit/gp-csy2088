<?php
/**
 * Activity Logs API - Admin Only
 * DOKO Grocery E-commerce
 */

require_once '../ApiConfig.php';

try {
    // Require admin access
    ApiAuth::requireAdmin();
    
    $database = Database::getInstance();
    $conn = $database->getConnection();
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'GET':
            handleGetLogs($conn);
            break;
        case 'DELETE':
            handleDeleteLogs($conn);
            break;
        default:
            ApiResponse::error('Method not allowed', 405);
            break;
    }
    
} catch (Exception $e) {
    ApiLogger::error("Activity logs API error: " . $e->getMessage());
    ApiResponse::error('An error occurred: ' . $e->getMessage());
}

function handleGetLogs($conn) {
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? min(100, max(1, (int)$_GET['limit'])) : 20;
    $userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : null;
    $action = isset($_GET['action']) ? trim($_GET['action']) : null;
    $entityType = isset($_GET['entity_type']) ? trim($_GET['entity_type']) : null;
    $dateFrom = isset($_GET['date_from']) ? $_GET['date_from'] : null;
    $dateTo = isset($_GET['date_to']) ? $_GET['date_to'] : null;
    
    $offset = ($page - 1) * $limit;
    
    // Build query
    $whereConditions = [];
    $params = [];
    
    if ($userId) {
        $whereConditions[] = "al.user_id = :user_id";
        $params[':user_id'] = $userId;
    }
    
    if ($action) {
        $whereConditions[] = "al.action = :action";
        $params[':action'] = $action;
    }
    
    if ($entityType) {
        $whereConditions[] = "al.entity_type = :entity_type";
        $params[':entity_type'] = $entityType;
    }
    
    if ($dateFrom && ApiUtils::validateDate($dateFrom)) {
        $whereConditions[] = "DATE(al.created_at) >= :date_from";
        $params[':date_from'] = $dateFrom;
    }
    
    if ($dateTo && ApiUtils::validateDate($dateTo)) {
        $whereConditions[] = "DATE(al.created_at) <= :date_to";
        $params[':date_to'] = $dateTo;
    }
    
    $whereClause = $whereConditions ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
    
    $query = "SELECT al.log_id, al.user_id, al.action, al.entity_type, al.entity_id,
                     al.old_values, al.new_values, al.ip_address, al.user_agent, al.created_at,
                     u.username, u.first_name, u.last_name, u.role
              FROM activity_logs al
              LEFT JOIN users u ON al.user_id = u.user_id
              {$whereClause}
              ORDER BY al.created_at DESC
              LIMIT :limit OFFSET :offset";
    
    $stmt = $conn->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $logs = $stmt->fetchAll();
    
    // Get total count
    $countQuery = "SELECT COUNT(*) as total FROM activity_logs al {$whereClause}";
    $countStmt = $conn->prepare($countQuery);
    foreach ($params as $key => $value) {
        $countStmt->bindValue($key, $value);
    }
    $countStmt->execute();
    $total = $countStmt->fetchColumn();
    
    // Format log data
    $formattedLogs = array_map(function($log) {
        return [
            'log_id' => (int)$log['log_id'],
            'user' => [
                'user_id' => (int)$log['user_id'],
                'username' => $log['username'],
                'full_name' => trim($log['first_name'] . ' ' . $log['last_name']),
                'role' => $log['role']
            ],
            'action' => $log['action'],
            'entity_type' => $log['entity_type'],
            'entity_id' => $log['entity_id'] ? (int)$log['entity_id'] : null,
            'changes' => [
                'old_values' => json_decode($log['old_values'], true),
                'new_values' => json_decode($log['new_values'], true)
            ],
            'metadata' => [
                'ip_address' => $log['ip_address'],
                'user_agent' => $log['user_agent']
            ],
            'created_at' => $log['created_at'],
            'formatted_date' => date('M j, Y g:i A', strtotime($log['created_at']))
        ];
    }, $logs);
    
    // Get activity statistics
    $statsQuery = "SELECT 
                       COUNT(*) as total_activities,
                       COUNT(DISTINCT user_id) as active_users,
                       COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR) THEN 1 END) as last_24h,
                       COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as last_7d
                   FROM activity_logs al
                   {$whereClause}";
    
    $statsStmt = $conn->prepare($statsQuery);
    foreach ($params as $key => $value) {
        $statsStmt->bindValue($key, $value);
    }
    $statsStmt->execute();
    $stats = $statsStmt->fetch();
    
    $response = [
        'logs' => $formattedLogs,
        'statistics' => [
            'total_activities' => (int)$stats['total_activities'],
            'active_users' => (int)$stats['active_users'],
            'last_24_hours' => (int)$stats['last_24h'],
            'last_7_days' => (int)$stats['last_7d']
        ]
    ];
    
    ApiResponse::paginated($response, $page, $total, $limit, 'Activity logs retrieved successfully');
}

function handleDeleteLogs($conn) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        ApiResponse::error('Invalid JSON data', 400);
    }
    
    // Options for deletion
    $olderThan = isset($input['older_than_days']) ? (int)$input['older_than_days'] : null;
    $logIds = isset($input['log_ids']) && is_array($input['log_ids']) ? $input['log_ids'] : null;
    $userId = isset($input['user_id']) ? (int)$input['user_id'] : null;
    $action = isset($input['action']) ? trim($input['action']) : null;
    
    if (!$olderThan && !$logIds && !$userId && !$action) {
        ApiResponse::error('At least one deletion criteria must be specified', 400);
    }
    
    $whereConditions = [];
    $params = [];
    
    if ($olderThan) {
        $whereConditions[] = "created_at < DATE_SUB(NOW(), INTERVAL :older_than DAY)";
        $params[':older_than'] = $olderThan;
    }
    
    if ($logIds) {
        $placeholders = str_repeat('?,', count($logIds) - 1) . '?';
        $whereConditions[] = "log_id IN ($placeholders)";
        $params = array_merge($params, $logIds);
    }
    
    if ($userId) {
        $whereConditions[] = "user_id = :user_id";
        $params[':user_id'] = $userId;
    }
    
    if ($action) {
        $whereConditions[] = "action = :action";
        $params[':action'] = $action;
    }
    
    $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
    
    // Get count before deletion
    $countQuery = "SELECT COUNT(*) FROM activity_logs {$whereClause}";
    $countStmt = $conn->prepare($countQuery);
    
    $paramIndex = 1;
    foreach ($params as $param) {
        if (is_string($param) && strpos($param, ':') === 0) {
            $countStmt->bindValue($param, $params[$param]);
        } else {
            $countStmt->bindValue($paramIndex, $param);
            $paramIndex++;
        }
    }
    
    $countStmt->execute();
    $deleteCount = $countStmt->fetchColumn();
    
    if ($deleteCount === 0) {
        ApiResponse::error('No logs found matching the criteria', 404);
    }
    
    // Perform deletion
    $deleteQuery = "DELETE FROM activity_logs {$whereClause}";
    $deleteStmt = $conn->prepare($deleteQuery);
    
    $paramIndex = 1;
    foreach ($params as $param) {
        if (is_string($param) && strpos($param, ':') === 0) {
            $deleteStmt->bindValue($param, $params[$param]);
        } else {
            $deleteStmt->bindValue($paramIndex, $param);
            $paramIndex++;
        }
    }
    
    $deleteStmt->execute();
    $actualDeleted = $deleteStmt->rowCount();
    
    // Log this deletion action
    try {
        $currentUser = ApiAuth::getCurrentUser();
        
        $logQuery = "INSERT INTO activity_logs (user_id, action, entity_type, new_values, ip_address, user_agent) 
                     VALUES (:user_id, 'bulk_delete', 'activity_logs', :details, :ip_address, :user_agent)";
        
        $logStmt = $conn->prepare($logQuery);
        $logStmt->bindParam(':user_id', $currentUser['user_id']);
        $logStmt->bindValue(':details', json_encode([
            'deleted_count' => $actualDeleted,
            'criteria' => array_filter([
                'older_than_days' => $olderThan,
                'log_ids_count' => $logIds ? count($logIds) : null,
                'user_id' => $userId,
                'action' => $action
            ])
        ]));
        $logStmt->bindValue(':ip_address', $_SERVER['REMOTE_ADDR'] ?? '');
        $logStmt->bindValue(':user_agent', $_SERVER['HTTP_USER_AGENT'] ?? '');
        $logStmt->execute();
        
    } catch (Exception $e) {
        ApiLogger::warning("Failed to log bulk deletion: " . $e->getMessage());
    }
    
    ApiResponse::success([
        'deleted_count' => $actualDeleted
    ], "$actualDeleted activity logs deleted successfully");
}
?>
