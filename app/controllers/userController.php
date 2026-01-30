<?php
    require_once __DIR__ . '/../models/user.php';
    require_once __DIR__ . '/../core/database.php';
    
    class userController{
        
        public function listUsers(){
            try {
                $teachers = $this->getAllTeachers();
                $students = $this->getAllStudents();
                
                // Combine all users
                $allUsers = array_merge($teachers ?: [], $students ?: []);
                
                return $allUsers;
            } catch(Exception $e) {
                return [];
            }
        }
        
        public function getAllTeachers(){
            require_once __DIR__ . '/../models/user.php';
            try {
                $db = database::getConnection();
                $stmt = $db->query("SELECT fullname, name, email, phone, avatar FROM teachers");
                $teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Add role to each teacher
                foreach ($teachers as &$teacher) {
                    $teacher['role'] = 'teacher';
                    $teacher['id'] = count($teachers) + 1; // Temporary ID
                }
                
                return $teachers;
            } catch(PDOException $e) {
                error_log("Get teachers error: " . $e->getMessage());
                return false;
            }
        }
        
        public function getAllStudents(){
            require_once __DIR__ . '/../models/user.php';
            try {
                $db = database::getConnection();
                $stmt = $db->query("SELECT fullname, name, email, phone, avatar FROM students");
                $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Add role to each student
                foreach ($students as &$student) {
                    $student['role'] = 'student';
                    $student['id'] = count($students) + 1; // Temporary ID
                }
                
                return $students;
            } catch(PDOException $e) {
                error_log("Get students error: " . $e->getMessage());
                return false;
            }
        }
        
    }
?>
