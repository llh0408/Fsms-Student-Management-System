<?php
    require_once __DIR__ . '/../models/challenge.php';
    require_once __DIR__ . '/../core/jwt.php';
    
    class challengeController {
        
        // Create new challenge (for teachers)
        public function createChallenge() {
            $currentUser = JWT::isLoggedIn();

            
            if (!$currentUser || $currentUser['role'] !== 'teacher') {
                $_SESSION['challenge_error'] = 'Chỉ giáo viên mới được tạo challenge!';
                header('Location: /class');
                exit();
            }
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $title = trim($_POST['title'] ?? '');
                $hint = trim($_POST['hint'] ?? '');
                $exp = trim($_POST['exp'] ?? '');

                if ($title === '' || $hint === '' || $exp === '') {
                    $_SESSION['challenge_error'] = 'Vui lòng nhập đầy đủ tiêu đề, gợi ý và thời gian hết hạn!';
                    header('Location: /class');
                    exit();
                }

                if (strlen($hint) > 200) {
                    $_SESSION['challenge_error'] = 'Gợi ý không được quá 200 ký tự!';
                    header('Location: /class');
                    exit();
                }

                // Convert datetime-local to MySQL format
                $expDateTime = date('Y-m-d H:i:s', strtotime($exp));

                if (!isset($_FILES['challenge_file']) || $_FILES['challenge_file']['error'] !== UPLOAD_ERR_OK) {
                    $_SESSION['challenge_error'] = 'Vui lòng chọn file .txt để upload!';
                    header('Location: /class');
                    exit();
                }

                $file = $_FILES['challenge_file'];
                $fileName = $file['name'];
                $fileTmpName = $file['tmp_name'];
                $fileSize = $file['size'];

                $maxSize = 50 * 1024 * 1024; // 50MB
                if ($fileSize > $maxSize) {
                    $_SESSION['challenge_error'] = 'File không được quá 50MB!';
                    header('Location: /class');
                    exit();
                }

                // Rule: ASCII only (no dấu) + words separated by single spaces + .txt
                if (!preg_match('/^[A-Za-z0-9]+( [A-Za-z0-9]+)*\.txt$/i', $fileName) || preg_match('/[^\x00-\x7F]/', $fileName)) {
                    $_SESSION['challenge_error'] = 'Tên file phải không dấu, các từ cách nhau bởi 1 khoảng trắng và kết thúc bằng .txt';
                    header('Location: /class');
                    exit();
                }

                // Lưu file với tên lowercase, khoảng trắng hợp lệ (chỉ 1 space giữa các từ)
                $baseName = pathinfo($fileName, PATHINFO_FILENAME);
                $baseName = trim($baseName);
                $baseName = strtolower($baseName);
                $baseName = preg_replace('/\s+/', ' ', $baseName);
                $fileNameForSave = $baseName . '.txt';

                $code = Challenge::generateUniqueCode();
                $teacherFolder = preg_replace('/[^A-Za-z0-9_\-]/', '_', $currentUser['username']);
                $targetDir = __DIR__ . '/../../../htdocs/uploads/challenges/' . $teacherFolder . '/' . $code;
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0777, true);
                }

                $absoluteUploadPath = $targetDir . '/' . $fileNameForSave;
                $relativeUploadPath = 'htdocs/uploads/challenges/' . $teacherFolder . '/' . $code . '/' . $fileNameForSave;

                if (!move_uploaded_file($fileTmpName, $absoluteUploadPath)) {
                    $_SESSION['challenge_error'] = 'Không thể upload file! Lỗi: ' . (error_get_last()['message'] ?? 'Unknown error');
                    header('Location: /class');
                    exit();
                }

                $result = Challenge::createChallenge($title, $hint, $relativeUploadPath, $expDateTime, $currentUser['username'], $code);
                if ($result['success']) {
                    header('Location: /challenge/' . urlencode($result['code']));
                    exit();
                }

                $_SESSION['challenge_error'] = $result['message'];
                header('Location: /challenge/error');
                exit();
            }
        }

        public function viewChallengeByCode($code) {
            $currentUser = JWT::isLoggedIn();
            if (!$currentUser) {
                header('Location: /login');
                exit();
            }

            $challenge = Challenge::getByCode($code);
            if (!$challenge) {
                header('Location: /class');
                exit();
            }

            $result = null;
            require __DIR__ . '/../views/challenges/challenge.php';
        }

        public function checkAnswer() {
            $currentUser = JWT::isLoggedIn();
            if (!$currentUser) {
                header('Location: /login');
                exit();
            }

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                header('Location: /class');
                exit();
            }

            $code = $_POST['code'] ?? '';
            $answer = $_POST['answer'] ?? '';
            $code = trim($code);
            $answer = trim($answer);

            $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

            if ($code === '' || $answer === '') {
                if ($isAjax) {
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode(['success' => false, 'message' => 'Vui lòng nhập đáp án!']);
                    exit();
                }
                $_SESSION['challenge_error'] = 'Vui lòng nhập đáp án!';
                header('Location: /challenge/' . urlencode($code));
                exit();
            }

            $result = Challenge::checkAnswerByCode($code, $answer);

            if ($isAjax) {
                if (ob_get_length()) {
                    ob_clean();
                }
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode($result);
                exit();
            }

            $_SESSION['challenge_result'] = $result;
            header('Location: /challenge/' . urlencode($code));
            exit();
        }

        // Backward-compatible: join by code then redirect to /challenge/<code>
        public function joinChallenge() {
            $currentUser = JWT::isLoggedIn();
            if (!$currentUser) {
                header('Location: /login');
                exit();
            }

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                header('Location: /class');
                exit();
            }

            $code = trim($_POST['code'] ?? '');
            if ($code === '') {
                $_SESSION['challenge_error'] = 'Vui lòng nhập mã challenge!';
                header('Location: /class');
                exit();
            }

            header('Location: /challenge/' . urlencode($code));
            exit();
        }

        // Backward-compatible: old routes /challenges?id=... or /challenges/quiz?id=...
        public function getChallenge() {
            $currentUser = JWT::isLoggedIn();
            if (!$currentUser) {
                header('Location: /login');
                exit();
            }

            $code = trim($_GET['id'] ?? '');
            if ($code === '') {
                header('Location: /class');
                exit();
            }

            header('Location: /challenge/' . urlencode($code));
            exit();
        }
    }
?>