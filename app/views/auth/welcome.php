<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome</title>
    <link rel="stylesheet" href="/htdocs/assets/styles/custom-lib.css">
    <link rel="stylesheet" href="/htdocs/assets/styles/form.css">
</head>
<body>
    <nav class="navbar">FHC Student Management System</nav>
    <div class="form-container">
        <form>
            <h2>Welcome to FHC Student Management System</h2>

            <div class="form-group">
                <button type="button" class="btn-submit" onclick="window.location.href='/login'">Login</button>
            </div>

            <div class="form-group">
                <button type="button" class="btn-submit" onclick="window.location.href='/register'">Sign up</button>
            </div>
            <div class="form-group">
                <button type="button" class="btn-submit" onclick="window.location.href='/list'">Danh sách người dùng</button>
            </div>
        </form>
    </div>
</body>
</html>