<?php
require_once __DIR__ . '/../core/database.php';
class assignment {
    public static function getTeacherAssignment($teacher_name) {
        $db = database::getConnection();
        $pr = $db->prepare("SELECT * FROM assignments WHERE teacher = ?");
        $pr->execute([$teacher_name]);
        return $pr->fetchAll();
    }

    /** Lấy một assignment theo tên và kiểm tra thuộc về giáo viên (cho trang chi tiết) */
    public static function getByNameAndTeacher($name, $teacher_name) {
        $db = database::getConnection();
        $pr = $db->prepare("SELECT * FROM assignments WHERE name = ? AND teacher = ?");
        $pr->execute([$name, $teacher_name]);
        return $pr->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    
    public static function getStudentAssignment() {
        $db = database::getConnection();
        $pr = $db->prepare("SELECT * FROM assignments WHERE exp >= NOW() ORDER BY exp ASC");
        $pr->execute();
        return $pr->fetchAll();
    }
    public static function createAssignment($name, $description, $exp, $fpath , $teacher){
        $db = database::getConnection();
        $pr = $db->prepare("INSERT INTO assignments (name, de, exp, upload, submiss , teacher) VALUES (?, ?, ?, ?, ?, ?)");
        $pr->execute([$name, $description, $exp, $fpath, '', $teacher]);
        return $pr->rowCount();
    }
    public static function submitAssignment($fpath, $username, $name){
        $db = database::getConnection();
        
        // Lấy submiss hiện tại
        $pr = $db->prepare("SELECT submiss FROM assignments WHERE name = ?");
        $pr->execute([$name]);
        $current = $pr->fetch(PDO::FETCH_COLUMN);
        
        // Debug
        error_log("Current submiss: " . $current);
        error_log("Username: " . $username);
        error_log("File path: " . $fpath);
        
        // Parse JSON hoặc tạo mới
        $submissions = $current ? json_decode($current, true) : [];
        if (!is_array($submissions)) {
            $submissions = [];
        }
        
        // Thêm submission mới với format 'username': 'file_path'
        $submissions[$username] = $fpath;
        
        // Update lại
        $jsonSubmissions = json_encode($submissions, JSON_UNESCAPED_UNICODE);
        error_log("New JSON submiss: " . $jsonSubmissions);
        
        $pr2 = $db->prepare("UPDATE assignments SET submiss = ? WHERE name = ?");
        $pr2->execute([$jsonSubmissions, $name]);
        
        return $pr2->rowCount();
    }
}