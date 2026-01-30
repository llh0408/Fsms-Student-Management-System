<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FHC Student Management System</title>
    <link rel="stylesheet" href="/htdocs/assets/styles/custom-lib.css">
    <link rel="stylesheet" href="/htdocs/assets/styles/form.css">
</head>
<body>
    <nav class="navbar">FHC Student Management System</nav>
    <div class="form-container">
        <form action="/register" method="post">
            <h2>Đăng ký</h2>
            
            <?php 
                if (isset($_SESSION['err'])): 
            ?>
                <div class="alert-message fail">
                   <?php 
                        echo $_SESSION['err'];
                        unset($_SESSION['err']);
                   ?>
                </div>
            <?php endif; ?>
            
            <div class="form-group">
                <input type="text" id="fullname" name="fullname" placeholder="Nhập họ và tên" required>
            </div>
            
            <div class="form-group">
                <input type="text" id="username" name="username" placeholder="Nhập tên đăng nhập" required>
            </div>
            
            <div class="form-group">
                <input type="text" id="phone" name="phone" placeholder="Nhập số điện thoại" required>
            </div>
            
            <div class="form-group">
                <input type="email" id="email" name="email" placeholder="Nhập email" required>
            </div>
            
            <div class="form-group">
                <input type="password" id="password" name="password" placeholder="Tạo mật khẩu" required>
            </div>
            
            <div class="form-group">
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Xác nhận mật khẩu" required>
            </div>
            
            <div class="form-group">
                <label>Bạn là</label>
                <div class="radio-group">
                    <label class="radio-label">
                        <input type="radio" name="role" value="student" required>
                        <span>Sinh viên</span>
                    </label>
                    <label class="radio-label">
                        <input type="radio" name="role" value="teacher" required>
                        <span>Giảng viên</span>
                    </label>
                </div>
            </div>
            
            <button type="submit" class="btn-submit">Đăng ký</button>
            <div style="margin-top: 10px;">
              <a href="/login" style="text-decoration: none; color: #007bff">Đăng nhập hệ thống </a>
            </div>
        </form>
    </div>
</body>
</html>