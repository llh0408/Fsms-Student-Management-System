<?php
    require_once __DIR__ . '/../../core/jwt.php';
    $currentUser = JWT::isLoggedIn();
    
    if (!$currentUser) {
        header('Location: /login');
        exit();
    }
    
    // Get user data with avatar from database
    require_once __DIR__ . '/../../models/user.php';

    // Support teacher editing a student via ?email (removed ?user lookup)
    $isEditingOther = false;
    $formUser = $currentUser;
    if ($currentUser['role'] === 'teacher' && !empty($_GET['email'])) {
        $editEmail = filter_var(trim($_GET['email']), FILTER_SANITIZE_EMAIL);
        if ($editEmail !== '') {
            $editData = user::findByEmail($editEmail);
            if ($editData && isset($editData['role']) && $editData['role'] === 'student') {
                $formUser = $editData;
                $isEditingOther = true;
            } else {
                // Either not found or not a student (do not allow editing teachers)
                $_SESSION['errors'][] = 'Không tìm thấy sinh viên để chỉnh sửa.';
            }
        }
    } else {
        $userData = user::findByUsername($currentUser['username'], $currentUser['role']);
        $formUser = $userData ?: $currentUser;
    }

    // Avatar path from database or default
    $avatarPath = '/htdocs/uploads/avatars/default-avatar.jpg';
    if (!empty($formUser['avatar'])) {
        $avatar = $formUser['avatar'];
        if (preg_match('#^(https?://|/)#i', $avatar)) {
            $avatarPath = $avatar;
        } else {
            $avatarPath = '/htdocs/uploads/avatars/' . ltrim($avatar, '/');
        }
    }
    
    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // If teacher editing another user, target that username/role
        $targetUsername = $currentUser['username'];
        $targetRole = $currentUser['role'];
        if (!empty($_POST['editing_user']) && $currentUser['role'] === 'teacher') {
            $targetUsername = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', trim($_POST['editing_user']));
            $targetRole = 'student';
        }

        $fullname = trim($_POST['fullname']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $current_password = trim($_POST['current_password']);
        $new_password = trim($_POST['new_password']);
        $confirm_password = trim($_POST['confirm_password']);
        
        $errors = [];
        
        // Validation (same for teacher editing student)
        if (empty($fullname)) {
            $errors[] = 'Vui lòng nhập họ tên!';
        }
        
        if (empty($email)) {
            $errors[] = 'Vui lòng nhập email!';
        }
        
        if (empty($phone)) {
            $errors[] = 'Vui lòng nhập số điện thoại!';
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email không hợp lệ!';
        }
        
        if (!preg_match('/^0[0-9]{9,10}$/', $phone)) {
            $errors[] = 'Số điện thoại không hợp lệ!';
        }
        
        // Password handling:
        // - If teacher editing another user: allow setting new password directly (no current password verification)
        // - If editing own profile: keep existing checks
        if (!empty($new_password)) {
            if ($targetUsername === $currentUser['username']) {
                if (empty($current_password)) {
                    $errors[] = 'Vui lòng nhập mật khẩu hiện tại để đổi mật khẩu!';
                } else {
                    $user = user::login($currentUser['username'], $current_password, $currentUser['role']);
                    if (!$user) {
                        $errors[] = 'Mật khẩu hiện tại không chính xác!';
                    }
                }
            }
            if (strlen($new_password) < 6) {
                $errors[] = 'Mật khẩu mới phải có ít nhất 6 ký tự!';
            }
            if ($new_password !== $confirm_password) {
                $errors[] = 'Mật khẩu xác nhận không khớp!';
            }
        }
        
        if (empty($errors)) {
            // Update target user's information
            $result = user::updateProfile($targetUsername, $targetRole, $fullname, $email, $phone, $new_password);
            
            if ($result) {
                // If teacher edited another user, log and redirect back to users list
                require_once __DIR__ . '/../../models/userLogger.php';
                if ($targetUsername !== $currentUser['username']) {
                    userLogger::logUserUpdate($currentUser['username'], $targetUsername);
                    $_SESSION['success'] = 'Cập nhật sinh viên thành công!';
                    header('Location: /users');
                    exit();
                }
                
                // Otherwise update JWT for self
                $updatedPayload = [
                    'username' => $currentUser['username'],
                    'fullname' => $fullname,
                    'email' => $email,
                    'phone' => $phone,
                    'role' => $currentUser['role']
                ];
                JWT::setCookie($updatedPayload);
                
                userLogger::logProfileUpdate($currentUser['username'], $currentUser['role']);
                
                if (!empty($new_password)) {
                    userLogger::logPasswordChange($currentUser['username'], $currentUser['role']);
                }
                
                $_SESSION['success'] = 'Cập nhật thông tin thành công!';
                header('Location: /profile');
                exit();
            } else {
                $errors[] = 'Email đã tồn tại trong hệ thống!';
            }
        }
        
        $_SESSION['errors'] = $errors;
    }
    
    // Get current user data
    $currentUser = JWT::isLoggedIn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - FHC Student Management System</title>
    <link rel="stylesheet" href="/htdocs/assets/styles/custom-lib.css">
    <link rel="stylesheet" href="/htdocs/assets/styles/form.css">
    <link rel="stylesheet" href="/htdocs/assets/styles/profile-edit.css">
</head>
<body>
    <nav class="navbar">FHC Student Management System</nav>
    
    <div class="profile-edit-container">
        <div class="form-container">
            <?php if (isset($_SESSION['errors'])): ?>
                <div class="alert-message error">
                    <?php foreach ($_SESSION['errors'] as $error): ?>
                        <p><?php echo $error; ?></p>
                    <?php endforeach; ?>
                </div>
                <?php unset($_SESSION['errors']); ?>
            <?php endif; ?>
            
            <form method="POST" action="/profile/edit">
                <div class="profile-layout">
                    <!-- Left Section: Personal Information -->
                    <div class="left-section">
                        <div class="section-title"><?php echo $isEditingOther ? 'Chỉnh sửa sinh viên' : 'Thông tin cá nhân'; ?></div>
                        
                        <div class="form-group">
                            <label for="fullname">Họ và tên</label>
                            <input type="text" id="fullname" name="fullname" value="<?php echo htmlspecialchars($formUser['fullname'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($formUser['email'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Số điện thoại</label>
                            <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($formUser['phone'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="username">Tên đăng nhập</label>
                            <?php
                                // Prefer student's `username` or legacy `name` field when teacher edits another user.
                                $displayUsername = $currentUser['username'];
                                if (!empty($formUser)) {
                                    if (!empty($formUser['username'])) {
                                        $displayUsername = $formUser['username'];
                                    } elseif (!empty($formUser['name'])) {
                                        $displayUsername = $formUser['name'];
                                    }
                                }
                            ?>
                            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($displayUsername); ?>" disabled>
                            <small style="color: #6c757d;"><?php echo $isEditingOther ? 'Bạn đang chỉnh sửa tài khoản sinh viên' : 'Tên đăng nhập không thể thay đổi'; ?></small>
                        </div>
                    </div>
                    
                    <!-- Right Section: Password & Avatar -->
                    <div class="right-section">
                        <div class="section-title">Cập nhật mật khẩu</div>
                        
                        <div class="form-group">
                            <label for="current_password">Mật khẩu hiện tại</label>
                            <input type="password" id="current_password" name="current_password">
                        </div>
                        
                        <div class="form-group">
                            <label for="new_password">Mật khẩu mới</label>
                            <input type="password" id="new_password" name="new_password">
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Xác nhận mật khẩu mới</label>
                            <input type="password" id="confirm_password" name="confirm_password">
                        </div>
                        
                        <div class="section-title" style="margin-top: 30px;">Cập nhật ảnh đại diện</div>
                        
                        <div class="avatar-section">
                            <div class="avatar-container">
                                <img src="<?php echo htmlspecialchars($avatarPath ?? '/htdocs/uploads/avatars/default-avatar.jpg', ENT_QUOTES, 'UTF-8'); ?>" alt="Avatar" class="avatar-img" id="avatar-preview">
                                <div class="avatar-overlay">
                                    <div class="avatar-text">Upload ảnh</div>
                                </div>
                            </div>
                            <small style="color: #6c757d;">Click để thay đổi avatar</small>
                        </div>
                    </div>
                </div>
                
                <div class="btn-group">
                    <?php if ($isEditingOther): ?>
                        <?php $hiddenEditingUser = $formUser['username'] ?? $formUser['name'] ?? ''; ?>
                        <input type="hidden" name="editing_user" value="<?php echo htmlspecialchars($hiddenEditingUser); ?>">
                    <?php endif; ?>
                    <button type="submit" class="btn-save">Lưu thay đổi</button>
                    <a href="<?php echo $isEditingOther ? '/users' : '/profile'; ?>" class="btn-cancel">Hủy</a>
                </div>
            </form>
        </div>
    </div>
	
	<!-- Hidden file input for avatar upload -->
	<input type="file" id="avatar-upload" accept="image/*" style="display: none;">

    <script src="/htdocs/assets/js/profile.js"></script>
</body>
</html>
