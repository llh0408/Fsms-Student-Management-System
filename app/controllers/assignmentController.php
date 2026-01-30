<?php
    require_once __DIR__ . '/../models/assignment.php';
    require_once __DIR__ . '/../core/jwt.php';
    class assignmentController{
        public static function allowedExt($a_file){
            $ext = strtolower(pathinfo($a_file['name'], PATHINFO_EXTENSION));
            $allo_ext = ['zip', 'txt', 'pdf', 'doc', 'docx', 'xlsx', 'xls' ];
            if (!in_array($ext, $allo_ext)) {
                return false;
            }
            return true;
        }
        public static function fetchAssignments(){
            $currentUser = JWT::isLoggedIn();
            if ($currentUser['role'] == "teacher") {
                $assignmentlist = assignment::getTeacherAssignment($currentUser['username']);

            }else {
                $assignmentlist = assignment::getStudentAssignment();
            }
            return $assignmentlist;
        }
        public static function createAssignment($name, $description, $exp, $a_file, $teacher){
            $currentUser = JWT::isLoggedIn();
            if (!self::allowedExt($a_file)) {
                $_SESSION['assignment_error'] = 'Định dạng file không được hỗ trợ!!';
                return;
            }
            $fname = $a_file['name'];
            $fpath = '/htdocs/uploads/assignments/' . $currentUser['username'] . '/' . $fname;
            $fullPath = __DIR__ . '/../..' . $fpath;
            
            // Fix path for Windows
            $fullPath = str_replace('/', DIRECTORY_SEPARATOR, $fullPath);
            
            // Tạo thư mục nếu chưa tồn tại
            $uploadDir = dirname($fullPath);
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            if (move_uploaded_file($a_file['tmp_name'], $fullPath)) {
                if ($currentUser['role'] == "teacher") {
                    assignment::createAssignment($name, $description, $exp, $fpath, $teacher);
                    $_SESSION['assignment_success'] = 'Tạo bài tập thành công!';
                }
            } else {
                $_SESSION['assignment_error'] = 'Không thể upload file. Vui lòng kiểm tra lại quyền thư mục!';
            }
        }
        public static function uploadAssignment($a_file, $name){
            $currentUser = JWT::isLoggedIn();
            if (!self::allowedExt($a_file)) {
                $_SESSION['assignment_error'] = 'Định dạng file không được hỗ trợ!!';
                return;
            }
            
            $fname = $a_file['name'];
            $fpath = '/htdocs/uploads/submissions/' . $currentUser['username'] . '/' . $fname;
            $fullPath = __DIR__ . '/../..' . $fpath;
            
            // Fix path for Windows
            $fullPath = str_replace('/', DIRECTORY_SEPARATOR, $fullPath);
            
            // Debug path
            error_log("Upload path: " . $fullPath);
            error_log("Relative path: " . $fpath);
            error_log("__DIR__: " . __DIR__);
            
            // Tạo thư mục nếu chưa tồn tại
            $uploadDir = dirname($fullPath);
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
                error_log("Created directory: " . $uploadDir);
            }
            
            if (move_uploaded_file($a_file['tmp_name'], $fullPath)) {
                error_log("File moved successfully to: " . $fullPath);
                error_log("About to call submitAssignment with:");
                error_log("- fpath: " . $fpath);
                error_log("- username: " . $currentUser['username']);
                error_log("- assignment name: " . $name);
                
                $result = assignment::submitAssignment($fpath, $currentUser['username'], $name);
                error_log("submitAssignment returned: " . $result);
                
                if ($result > 0) {
                    $_SESSION['assignment_success'] = 'Nộp bài tập thành công!';
                } else {
                    $_SESSION['assignment_error'] = 'Có lỗi xảy ra khi lưu vào database!';
                }
            } else {
                error_log("Failed to move file to: " . $fullPath);
                $_SESSION['assignment_error'] = 'Không thể upload file. Vui lòng kiểm tra lại quyền thư mục!';
            }
        }

        /** Hiển thị trang chi tiết assignment cho giáo viên */
        public static function showDetail() {
            $currentUser = JWT::isLoggedIn();
            if (!$currentUser || $currentUser['role'] !== 'teacher') {
                header('Location: /class');
                exit();
            }
            $name = isset($_GET['name']) ? trim((string) $_GET['name']) : '';
            if ($name === '') {
                header('Location: /class');
                exit();
            }
            $assignment = assignment::getByNameAndTeacher($name, $currentUser['username']);
            if (!$assignment) {
                $_SESSION['assignment_error'] = 'Không tìm thấy bài tập hoặc bạn không có quyền xem.';
                header('Location: /class');
                exit();
            }
            require __DIR__ . '/../views/assignments/detail.php';
        }
    }
?>