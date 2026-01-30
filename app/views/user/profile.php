<?php
    require_once __DIR__ . '/../../core/jwt.php';
    $currentUser = JWT::isLoggedIn();
    
    if (!$currentUser) {
        header('Location: /login');
        exit();
    }
    
    // Get user data with avatar from database
    require_once __DIR__ . '/../../models/user.php';
    $userData = user::findByUsername($currentUser['username'], $currentUser['role']);
    
    // Avatar path from database or default
    $avatarPath = '/htdocs/uploads/avatars/default-avatar.jpg';
    if ($userData && !empty($userData['avatar'])) {
        // Check if file exists
        $fullPath = __DIR__ . '/../../..' . $userData['avatar'];
        if (file_exists($fullPath)) {
            $avatarPath = $userData['avatar'];
        }
    }
    
    // Get user history from database
    $userHistory = [];
    if ($userData && !empty($userData['history'])) {
        $historyData = json_decode($userData['history'], true);
        if (is_array($historyData)) {
            // Sort by timestamp descending (newest first)
            usort($historyData, function($a, $b) {
                return $b['timestamp'] - $a['timestamp'];
            });
            $userHistory = array_slice($historyData, 0, 10); // Get last 10 actions (newest first)
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile - FHC Student Management System</title>
    <link rel="stylesheet" href="/htdocs/assets/styles/custom-lib.css">
    <link rel="stylesheet" href="/htdocs/assets/styles/profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="navbar-content">
            <span class="navbar-brand">FHC Student Management System</span>
            <div class="navbar-actions">
                <button class="nav-btn" onclick="window.location.href='/class'">
                    <i class="fas fa-tasks"></i>
                    Assignments
                </button>
                <button class="nav-btn" onclick="window.location.href='/profile/edit'">
                    <i class="fas fa-edit"></i>
                   Chỉnh sửa trang cá nhân
                </button>
                <button class="nav-btn logout" onclick="window.location.href='/logout'">
                    <i class="fas fa-sign-out-alt"></i>
                    Đăng xuất
                </button>
            </div>
        </div>
    </nav>
        
    <div class="profile-container">
        <div class="profile-card">
            <div class="profile-header">
                <div class="avatar-container">
                    <img src="<?php echo $avatarPath; ?>" alt="Avatar" class="avatar-img">
                </div>
                <h2 class="profile-name"><?php echo htmlspecialchars($currentUser['fullname']); ?></h2>
            </div>
            
            <div class="profile-body">
                <div class="profile-item">
                    <span class="profile-label">Username</span>
                    <span class="profile-value"><?php echo htmlspecialchars($currentUser['username']); ?></span>
                </div>
                
                <div class="profile-item">
                    <span class="profile-label">Email</span>
                    <span class="profile-value"><?php echo htmlspecialchars($currentUser['email']); ?></span>
                </div>
                
                <div class="profile-item">
                    <span class="profile-label">Phone</span>
                    <span class="profile-value"><?php echo htmlspecialchars($currentUser['phone'] ?? ''); ?></span>
                </div>
                
                <div class="profile-item">
                    <span class="profile-label">Role</span>
                    <span class="profile-value role-badge"><?php echo ucfirst($currentUser['role']); ?></span>
                </div>
            </div>
            
            <div class="profile-footer">
                <button class="btn-edit" onclick="window.location.href='/profile/edit'">Edit Profile</button>
                <button class="btn-logout" onclick="window.location.href='/logout'">Logout</button>
            </div>
        </div>
        
        <div class="action-history">
            <div class="history-header">
                <h3>Nhật ký hoạt động  </h3>
            </div>
            
            <div class="history-body">
                <?php if (!empty($userHistory)): ?>
                    <?php foreach ($userHistory as $history): ?>
                        <div class="history-item">
                            <div class="history-action"><?php echo htmlspecialchars($history['action']); ?></div>
                            <div class="history-time"><?php echo htmlspecialchars($history['time']); ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="history-item">
                        <div class="history-action">Chưa có hoạt động nào</div>
                        <div class="history-time">-</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
