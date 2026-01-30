<?php
    require_once __DIR__ . '/../core/jwt.php';
    
    class profileController {
        
        public function uploadAvatar() {
            $currentUser = JWT::isLoggedIn();
            
            if (!$currentUser) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'Unauthorized'
                ]);
                exit();
            }
            
            if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['avatar'])) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid request'
                ]);
                exit();
            }
            
            $file = $_FILES['avatar'];
            
            // Validate file
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $maxSize = 5 * 1024 * 1024; // 5MB
            
            if ($file['error'] !== UPLOAD_ERR_OK) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'Lỗi khi tải lên ảnh!'
                ]);
                exit();
            }
            
            if (!in_array($file['type'], $allowedTypes)) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'Chỉ chấp nhận file ảnh (JPG, PNG, GIF, WebP)!'
                ]);
                exit();
            }
            
            if ($file['size'] > $maxSize) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'Kích thước file không được vượt quá 5MB!'
                ]);
                exit();
            }
            
            // Create unique filename based on username
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = $currentUser['username'] . '.' . $extension;
            $uploadPath = __DIR__ . '/../../htdocs/uploads/avatars/' . $filename;
            
            // Get old avatar path from database to delete old file
            require_once __DIR__ . '/../models/user.php';
            $userData = user::findByUsername($currentUser['username'], $currentUser['role']);
            $oldAvatarPath = null;
            if ($userData && !empty($userData['avatar'])) {
                $oldAvatarPath = $userData['avatar'];
                $oldBaseName = basename($oldAvatarPath);
                // Never delete shared default avatar assets
                if (strpos($oldBaseName, 'default-avatar') !== 0) {
                    // Convert web path to file system path
                    $oldFilePath = __DIR__ . '/../../..' . $oldAvatarPath;
                    if (file_exists($oldFilePath)) {
                        unlink($oldFilePath); // Delete old avatar file
                    }
                }
            }
            
            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                // Update user avatar in database with new file path
                $avatarPath = '/htdocs/uploads/avatars/' . $filename;
                $result = user::updateAvatar($currentUser['username'], $currentUser['role'], $avatarPath);
                
                if ($result) {
                    // Log avatar upload action
                    require_once __DIR__ . '/../models/userLogger.php';
                    userLogger::logAvatarUpload($currentUser['username'], $currentUser['role']);
                    
                    // Return JSON response with avatar path
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => true,
                        'avatarPath' => $avatarPath,
                        'message' => 'Avatar uploaded successfully'
                    ]);
                } else {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => false,
                        'message' => 'Lỗi khi cập nhật avatar trong database!'
                    ]);
                }
            } else {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'Lỗi khi tải lên file!'
                ]);
            }
            
            exit();
        }
    }
?>
