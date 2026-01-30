<?php
    require_once __DIR__ . '/../../core/jwt.php';
    $currentUser = JWT::isLoggedIn();
    if ($currentUser){
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
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User List - FHC Student Management System</title>
    <link rel="stylesheet" href="/htdocs/assets/styles/custom-lib.css">
    <link rel="stylesheet" href="/htdocs/assets/styles/user-list.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="navbar-content">
            <div class="navbar-brand">FHC Student Management System</div>
            <div class="navbar-menu">
                <a href="/class" class="nav-link">
                    <i class="fas fa-tasks"></i>
                    Assignments
                </a>
                <div class="nav-user" onclick="window.location.href='/profile'">
                    <img src="<?= $currentUser ? $avatarPath : '/htdocs/uploads/avatars/default-avatar.jpg'; ?>" alt="User Avatar" class="user-avatar">
                    <span class="user-name"><?= $currentUser ? htmlspecialchars($currentUser['fullname']) : 'Anonymous'; ?></span>
                </div>
            </div>
        </div>
    </nav>
    
    <div class="user-list-container">
        <div class="user-list-header">
            <h1>Danh sách người dùng</h1>
        </div>
        
        <?php
            require_once __DIR__ . '/../../controllers/userController.php';
            $userController = new userController();
            $teachers = $userController->getAllTeachers();
            $students = $userController->getAllStudents();
            $allUsers = array_merge($teachers, $students);
            
            // Function to get user avatar from database
            function getUserAvatar($user) {
                $avatarPath = '/htdocs/uploads/avatars/default-avatar.jpg';

                if (!empty($user['avatar'])) {
                    // Check if file exists before using it
                    $fullPath = __DIR__ . '/../../..' . $user['avatar'];
                    if (file_exists($fullPath)) {
                        $avatarPath = $user['avatar'];
                    }
                }

                return $avatarPath;
            }
            
            if (empty($allUsers)) {
                echo '<div class="alert-message info">Chưa có người dùng nào trong hệ thống.</div>';
            }
        ?>        
        <?php if (!empty($allUsers)): ?>
            <table class="user-table">
                <thead>
                    <tr>
                        <th>Họ và tên</th>
                        <th>Email</th>
                        <th>Số điện thoại</th>
                        <th>Vai trò</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($allUsers as $user): ?>
                        <?php $rowClass = (isset($user['role']) && $user['role'] == 'teacher') ? 'teacher-row' : 'student-row'; ?>
                        <tr class="<?php echo $rowClass; ?>" data-email="<?php echo htmlspecialchars($user['email']); ?>">
                            <td>
                                <div class="user-info">
                                    <img src="<?php echo getUserAvatar($user); ?>" alt="Avatar" class="user-avatar">
                                    <div>
                                        <div class="user-name"><?php echo htmlspecialchars($user['fullname']); ?></div>
                                        <div class="user-email"><?php echo htmlspecialchars($user['email']); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['phone']); ?></td>
                            <td>
                                <span class="role-badge role-<?php echo $user['role']; ?>">
                                    <?php echo $user['role'] === 'teacher' ? 'Giáo viên' : 'Sinh viên'; ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-users">
                <i class="fas fa-users"></i>
                <p>Chưa có người dùng nào trong hệ thống.</p>
                <p><a href="/register">Đăng ký người dùng mới</a></p>
            </div>
        <?php endif; ?>
    </div>
    
    <script src="/htdocs/assets/js/user-list.js"></script>
    <script>
        // Make student rows clickable and navigate by email only
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('tr.student-row[data-email]').forEach(function(row) {
                row.style.cursor = 'pointer';
                row.addEventListener('click', function() {
                    var email = row.getAttribute('data-email') || '';
                    if (email) {
                        window.location.href = '/profile/edit?email=' + encodeURIComponent(email);
                    }
                });
            });
        });
    </script>
</body>
</html>