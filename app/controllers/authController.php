<?php
    require_once __DIR__ . '/../models/user.php';
    require_once __DIR__ . '/../core/jwt.php';
    
    class authController{
        
        public function signin(){
            // Check if form is submitted
            if (!isset($_POST['username']) || !isset($_POST['password']) || !isset($_POST['role'])) {
                $_SESSION['err'] = 'Vui lòng điền đầy đủ thông tin!';
                header('Location: /login');
                exit();
            }
            
            $username = trim($_POST['username']);
            $password = trim($_POST['password']);
            $role = trim($_POST['role']);
            
            // Validation
            if (empty($username)) {
                $_SESSION['err'] = 'Vui lòng nhập tên đăng nhập!';
                header('Location: /login');
                exit();
            }
            
            if (empty($password)) {
                $_SESSION['err'] = 'Vui lòng nhập mật khẩu!';
                header('Location: /login');
                exit();
            }
            
            if (empty($role)) {
                $_SESSION['err'] = 'Vui lòng chọn vai trò!';
                header('Location: /login');
                exit();
            }
            
            // Length validation
            if (strlen($username) < 3) {
                $_SESSION['err'] = 'Tên đăng nhập phải có ít nhất 3 ký tự!';
                header('Location: /login');
                exit();
            }
            
            if (strlen($password) < 6) {
                $_SESSION['err'] = 'Mật khẩu phải có ít nhất 6 ký tự!';
                header('Location: /login');
                exit();
            }
            
            // Attempt login
            $user = user::login($username, $password, $role);
            
            if ($user && password_verify($password, $user['pass'])) {
                // Log login action
                require_once __DIR__ . '/../models/userLogger.php';
                userLogger::logLogin($username, $role);
                
                // Create JWT token
                $payload = [
                    'username' => $username,
                    'fullname' => $user['fullname'],
                    'email' => $user['email'],
                    'phone' => $user['phone'] ?? '',
                    'role' => $role
                ];
                
                JWT::setCookie($payload);
                header('Location: /profile');
                exit();
            } else {
                // Failed login
                $_SESSION['err'] = 'Tên đăng nhập hoặc mật khẩu không chính xác!';
                header('Location: /login');
                exit();
            }
        }
        
        public function register(){
            // Check if form is submitted
            if (!isset($_POST['fullname']) || !isset($_POST['username']) || !isset($_POST['phone']) || 
                !isset($_POST['email']) || !isset($_POST['password']) || !isset($_POST['confirm_password']) || !isset($_POST['role'])) {
                $_SESSION['err'] = 'Vui lòng điền đầy đủ thông tin!';
                header('Location: /register');
                exit();
            }
            
            $fullname = trim($_POST['fullname']);
            $username = trim($_POST['username']);
            $phone = trim($_POST['phone']);
            $email = trim($_POST['email']);
            $password = trim($_POST['password']);
            $confirm_password = trim($_POST['confirm_password']);
            $role = trim($_POST['role']);
            
            // Validation
            if (empty($fullname)) {
                $_SESSION['err'] = 'Vui lòng nhập họ tên!';
                header('Location: /register');
                exit();
            }
            
            if (empty($username)) {
                $_SESSION['err'] = 'Vui lòng nhập tên đăng nhập!';
                header('Location: /register');
                exit();
            }
            
            if (empty($phone)) {
                $_SESSION['err'] = 'Vui lòng nhập số điện thoại!';
                header('Location: /register');
                exit();
            }
            
            if (empty($email)) {
                $_SESSION['err'] = 'Vui lòng nhập email!';
                header('Location: /register');
                exit();
            }
            
            if (empty($password)) {
                $_SESSION['err'] = 'Vui lòng nhập mật khẩu!';
                header('Location: /register');
                exit();
            }
            
            if (empty($confirm_password)) {
                $_SESSION['err'] = 'Vui lòng xác nhận mật khẩu!';
                header('Location: /register');
                exit();
            }
            
            if (empty($role)) {
                $_SESSION['err'] = 'Vui lòng chọn vai trò!';
                header('Location: /register');
                exit();
            }
            
            // Length validation
            if (strlen($fullname) < 2) {
                $_SESSION['err'] = 'Họ tên phải có ít nhất 2 ký tự!';
                header('Location: /register');
                exit();
            }
            
            if (strlen($username) < 3) {
                $_SESSION['err'] = 'Tên đăng nhập phải có ít nhất 3 ký tự!';
                header('Location: /register');
                exit();
            }
            
            if (strlen($password) < 6) {
                $_SESSION['err'] = 'Mật khẩu phải có ít nhất 6 ký tự!';
                header('Location: /register');
                exit();
            }
            
            // Password confirmation
            if ($password !== $confirm_password) {
                $_SESSION['err'] = 'Mật khẩu xác nhận không khớp!';
                header('Location: /register');
                exit();
            }
            
            // Email validation
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $_SESSION['err'] = 'Email không hợp lệ!';
                header('Location: /register');
                exit();
            }
            
            // Phone validation (Vietnamese phone number format)
            if (!preg_match('/^0[0-9]{9,10}$/', $phone)) {
                $_SESSION['err'] = 'Số điện thoại không hợp lệ!';
                header('Location: /register');
                exit();
            }
            
            // Username validation (alphanumeric and underscore)
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
                $_SESSION['err'] = 'Tên đăng nhập chỉ được chứa chữ cái, số và dấu gạch dưới!';
                header('Location: /register');
                exit();
            }
            
            // Attempt registration
            $result = user::register($fullname, $username, $password, $email, $phone, $role);
            
            if ($result) {
                // Log registration action
                require_once __DIR__ . '/../models/userLogger.php';
                userLogger::logAction($username, $role, 'Đăng ký tài khoản mới');
                
                $_SESSION['success'] = 'Đăng ký thành công! Vui lòng đăng nhập.';
                header('Location: /login');
                exit();
            } else {
                $_SESSION['err'] = 'Tên đăng nhập hoặc email đã tồn tại!';
                header('Location: /register');
                exit();
            }
        }
        
        public function logout(){
            // Get current user before logout for logging
            $currentUser = JWT::isLoggedIn();
            
            JWT::clearCookie();
            
            // Log logout action if user was logged in
            if ($currentUser) {
                require_once __DIR__ . '/../models/userLogger.php';
                userLogger::logLogout($currentUser['username'], $currentUser['role']);
            }
            
            $_SESSION['success'] = 'Đăng xuất thành công!';
            header('Location: /login');
            exit();
        }
    }
?>