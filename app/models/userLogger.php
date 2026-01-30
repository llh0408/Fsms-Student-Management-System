<?php
    require_once __DIR__ . '/../core/database.php';
    
    class userLogger {
        
        public static function logAction($username, $role, $action) {
            try {
                $db = database::getConnection();
                
                // Get current history
                $table = ($role === 'teacher') ? 'teachers' : 'students';
                $stmt = $db->prepare("SELECT history FROM $table WHERE name = ?");
                $stmt->execute([$username]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                $history = [];
                if ($result && !empty($result['history'])) {
                    $history = json_decode($result['history'], true);
                    if (!is_array($history)) {
                        $history = [];
                    }
                }
                
                // Add new action
                $newAction = [
                    'action' => $action,
                    'time' => date('Y-m-d H:i:s'),
                    'timestamp' => time()
                ];
                
                $history[] = $newAction;
                
                // Keep only last 50 actions
                if (count($history) > 50) {
                    $history = array_slice($history, -50);
                }
                
                // Update history in database
                $historyJson = json_encode($history);
                $updateStmt = $db->prepare("UPDATE $table SET history = ? WHERE name = ?");
                return $updateStmt->execute([$historyJson, $username]);
                
            } catch (PDOException $e) {
                error_log("Logger error: " . $e->getMessage());
                return false;
            }
        }
        
        public static function logLogin($username, $role) {
            return self::logAction($username, $role, 'Đăng nhập hệ thống');
        }
        
        public static function logLogout($username, $role) {
            return self::logAction($username, $role, 'Đăng xuất hệ thống');
        }
        
        public static function logProfileUpdate($username, $role) {
            return self::logAction($username, $role, 'Cập nhật thông tin cá nhân');
        }
        
        public static function logPasswordChange($username, $role) {
            return self::logAction($username, $role, 'Đổi mật khẩu');
        }
        
        public static function logAvatarUpload($username, $role) {
            return self::logAction($username, $role, 'Cập nhật ảnh đại diện');
        }
    }
?>
