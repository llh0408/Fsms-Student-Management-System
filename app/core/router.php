<?php
    session_start();
    require_once __DIR__ . '/../core/env.php';
    require_once __DIR__ . '/../core/jwt.php';
    require_once __DIR__ . '/../controllers/authController.php';
    require_once __DIR__ . '/../controllers/userController.php';
    require_once __DIR__ . '/../controllers/profileController.php';
    
    // Helper function for upload error messages
    function getUploadErrorMessage($errorCode) {
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
                return 'File quá lớn theo cấu hình PHP';
            case UPLOAD_ERR_FORM_SIZE:
                return 'File quá_large theo giới hạn form';
            case UPLOAD_ERR_PARTIAL:
                return 'File chỉ được upload một phần';
            case UPLOAD_ERR_NO_FILE:
                return 'Không có file được upload';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Thiếu thư mục tạm';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Không thể ghi file vào disk';
            case UPLOAD_ERR_EXTENSION:
                return 'Upload bị dừng bởi PHP extension';
            default:
                return 'Lỗi upload không xác định';
        }
    }
    
    $route =  parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    
    // Check if user is logged in via JWT
    $currentUser = JWT::isLoggedIn();
    
        // All User route allow anounymous view
      if ($route === '/list') {
            require __DIR__ . '/../views/user/list.php';
            exit();
      }
    if (!$currentUser) {
     switch ($route){
        case '/login':
            if(isset($_POST['username']) && isset($_POST['password'])){
                $auth = new authController();
                $auth->signin();
            }else{
                require __DIR__ . '/../views/auth/login.php';
            }
            break;
        case '/register':
            // Redirect if already logged in
            if ($currentUser) {
                header('Location: /');
                exit();
            }
            
            if(isset($_POST['fullname']) && isset($_POST['username'])){
                $auth = new authController();
                $auth->register();
            }else{
                require __DIR__ . '/../views/auth/signup.php';
            }
            break;
        default:
            require __DIR__ . '/../views/auth/welcome.php';
            break;
     }
    }else{
        if (preg_match('#^/challenge/([^/]+)$#', $route, $matches)) {
        $_GET['code'] = urldecode($matches[1]);
        $route = '/challenge/view';
        }
    //App route define
    switch ($route){
        case '/profile':
            require __DIR__ . '/../views/user/profile.php';
            break;
        case '/profile/edit':
            require __DIR__ . '/../views/user/profile_edit.php';
            break;
        case '/profile_edit.php':
            // Backward-compatible redirect from old URL format
            $qs = isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] !== '' ? ('?' . $_SERVER['QUERY_STRING']) : '';
            header('Location: /profile/edit' . $qs, true, 302);
            exit();
            break;
        case '/challenge':
            header('Location: /class');
            exit();
        break;
        // Phần để gv tạo bài tập
        case '/assignments/create':
            if(isset($_POST['name']) && isset($_POST['description']) && isset($_POST['exp']) && isset($_FILES['assignment_file'])){
            if ($_FILES['assignment_file']['size'] > 1024 * 1024 * 36.5) {
                $_SESSION['assignment_error'] = 'Kích thước file giới hạn là 36MB';
                header('Location: /class');
                exit();
            }
            require_once __DIR__ . '/../controllers/assignmentController.php';
            assignmentController::createAssignment($_POST['name'], $_POST['description'], $_POST['exp'],  $_FILES['assignment_file'], $currentUser['username']);
             
            }
           header('Location: /class');
            exit();
        break;

        // Phần để nộp bài tập
        case '/assignments/detail':
            require_once __DIR__ . '/../controllers/assignmentController.php';
            assignmentController::showDetail();
            exit();
        break;

        case '/assignments/upload':
            $hasFile = isset($_FILES['assignment_file']) && $_FILES['assignment_file']['error'] === UPLOAD_ERR_OK;
            $hasName = isset($_POST['assignment_name']) && trim((string)$_POST['assignment_name']) !== '';

            if ($hasFile && $hasName) {
                if ($_FILES['assignment_file']['size'] > 1024 * 1024 * 36.5) {
                    $_SESSION['assignment_error'] = 'Kích thước file giới hạn là 36MB';
                    header('Location: /class');
                    exit();
                }
                require_once __DIR__ . '/../controllers/assignmentController.php';
                assignmentController::uploadAssignment($_FILES['assignment_file'], trim($_POST['assignment_name']));
            } elseif (isset($_FILES['assignment_file']) && $_FILES['assignment_file']['error'] !== UPLOAD_ERR_OK) {
                $_SESSION['assignment_error'] = 'Lỗi upload file: ' . getUploadErrorMessage($_FILES['assignment_file']['error']);
            } elseif ($hasFile && !$hasName) {
                $_SESSION['assignment_error'] = 'Vui lòng chọn bài tập cần nộp.';
            }
            header('Location: /class');
            exit();
        break;

        case '/challenge/view':
            if (!isset($_GET['code']) || $_GET['code'] === '') {
                header('Location: /class');
                exit();
            }
            require_once __DIR__ . '/../controllers/challengeController.php';
            $challengeController = new challengeController();
            $challengeController->viewChallengeByCode($_GET['code']);
        break;
        case '/challenges':
            if (isset($_GET['id'])) {
                require_once __DIR__ . '/../controllers/challengeController.php';
                $challengeController = new challengeController();
                $challengeController->getChallenge();
            } else {
                header('Location: /');
                exit();
            }
        break;
        case '/challenges/quiz':
            require_once __DIR__ . '/../controllers/challengeController.php';
            $challengeController = new challengeController();
            $challengeController->getChallenge();
        break;
        case '/challenges/create':
            require_once __DIR__ . '/../controllers/challengeController.php';
            $challengeController = new challengeController();
            $challengeController->createChallenge();
        break;
        case '/challenges/join':
            require_once __DIR__ . '/../controllers/challengeController.php';
            $challengeController = new challengeController();
            $challengeController->joinChallenge();
        break;
        case '/challenges/check':
            require_once __DIR__ . '/../controllers/challengeController.php';
            $challengeController = new challengeController();
            $challengeController->checkAnswer();
        break;
        case '/logout':
            $auth = new authController();
            $auth->logout();
        break;
        case '/profile/upload-avatar':
            $profileController = new profileController();
            $profileController->uploadAvatar();
        break;
        default:
            require __DIR__ . '/../views/user/class.php';
        }
    }
    
?>