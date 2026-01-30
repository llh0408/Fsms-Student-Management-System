<?php
    require_once __DIR__ . '/../core/database.php';
    class user{
        public static function login($username, $password, $role){
            try {
                $db = database::getConnection();
                
                // Choose table based on role
                $table = ($role === 'teacher') ? 'teachers' : 'students';
                
                $stmt = $db->prepare("SELECT * FROM " . $table . " WHERE name = ? LIMIT 1");
                $stmt->execute([$username]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user && password_verify($password, $user['pass'])) {
                    // Add role to user data for session
                    $user['role'] = $role;
                    return $user;
                }
                return false;
            } catch(PDOException $e) {
                return false;
            }
        }
        
        public static function updateProfile($username, $role, $fullname, $email, $phone, $newPassword = null) {
            try {
                $db = database::getConnection();
                
                if ($role === 'teacher') {
                    $table = 'teachers';
                } else {
                    $table = 'students';
                }
                
                // Check if email already exists for another user (across both tables)
                $checkQuery = "SELECT email FROM teachers WHERE email = ? UNION SELECT email FROM students WHERE email = ? LIMIT 1";
                $checkStmt = $db->prepare($checkQuery);
                $checkStmt->execute([$email, $email]);
                $existingUser = $checkStmt->fetch(PDO::FETCH_ASSOC);
                
                if ($existingUser) {
                    // Verify it's not the current user by checking username
                    $currentQuery = "SELECT name FROM $table WHERE name = ? LIMIT 1";
                    $currentStmt = $db->prepare($currentQuery);
                    $currentStmt->execute([$username]);
                    $currentUser = $currentStmt->fetch(PDO::FETCH_ASSOC);
                    
                    // If email belongs to a different user, reject
                    $emailOwnerQuery = "SELECT name FROM teachers WHERE email = ? UNION SELECT name FROM students WHERE email = ? LIMIT 1";
                    $emailOwnerStmt = $db->prepare($emailOwnerQuery);
                    $emailOwnerStmt->execute([$email, $email]);
                    $emailOwner = $emailOwnerStmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($emailOwner && $emailOwner['name'] !== $username) {
                        return false;
                    }
                }
                
                // Build update query
                $updateFields = "fullname = ?, email = ?, phone = ?";
                $params = [$fullname, $email, $phone];
                
                // Add password update if provided
                if ($newPassword) {
                    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                    $updateFields .= ", pass = ?";
                    $params[] = $hashedPassword;
                }
                
                // Update by username
                $params[] = $username; // username for WHERE clause
                $query = "UPDATE $table SET $updateFields WHERE name = ?";
                
                $stmt = $db->prepare($query);
                return $stmt->execute($params);
                
            } catch (PDOException $e) {
                error_log("Update profile error: " . $e->getMessage());
                return false;
            }
        }
        
        public static function register($fullname, $username, $password, $email, $phone, $role){
            try {
                $db = database::getConnection();
                
                // Choose table based on role
                $table = ($role === 'teacher') ? 'teachers' : 'students';
                
                // Check if username already exists in both tables
                $stmt = $db->prepare("SELECT name FROM teachers WHERE name = ? UNION SELECT name FROM students WHERE name = ? LIMIT 1");
                $stmt->execute([$username, $username]);
                if ($stmt->fetch()) {
                    return false; // Username already exists
                }
                
                // Check if email already exists in both tables
                $stmt = $db->prepare("SELECT email FROM teachers WHERE email = ? UNION SELECT email FROM students WHERE email = ? LIMIT 1");
                $stmt->execute([$email, $email]);
                if ($stmt->fetch()) {
                    return false; // Email already exists
                }
                
                // Hash password
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                
                // Insert new user into table
                if ($role === 'teacher') {
                    // `uploads` column removed from teachers table; do not reference it here.
                    $stmt = $db->prepare("INSERT INTO teachers (fullname, name, email, phone, pass, avatar, history) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    return $stmt->execute([$fullname, $username, $email, $phone, $hashedPassword, '', '']);
                } else {
                    $stmt = $db->prepare("INSERT INTO students (fullname, name, email, phone, pass, avatar, history) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    return $stmt->execute([$fullname, $username, $email, $phone, $hashedPassword, '', '']);
                }
            } catch(PDOException $e) {
                error_log("Registration error: " . $e->getMessage());
                return false;
            }
        }
        
        public static function findByUsername($username, $role = null){
            try {
                $db = database::getConnection();
                
                if ($role) {
                    // Search in specific table
                    $table = ($role === 'teacher') ? 'teachers' : 'students';
                    $stmt = $db->prepare("SELECT * FROM " . $table . " WHERE name = ? LIMIT 1");
                    $stmt->execute([$username]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($user) {
                        $user['role'] = $role;
                    }
                    return $user;
                } else {
                    // Search in both tables
                    $stmt = $db->prepare("SELECT *, 'teacher' as role FROM teachers WHERE name = ? UNION SELECT *, 'student' as role FROM students WHERE name = ? LIMIT 1");
                    $stmt->execute([$username, $username]);
                    return $stmt->fetch(PDO::FETCH_ASSOC);
                }
            } catch(PDOException $e) {
                return false;
            }
        }
        
        public static function findById($id, $role = null){
            try {
                $db = database::getConnection();
                
                if ($role) {
                    // Search in specific table
                    $table = ($role === 'teacher') ? 'teachers' : 'students';
                    $stmt = $db->prepare("SELECT * FROM " . $table . " WHERE id = ? LIMIT 1");
                    $stmt->execute([$id]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($user) {
                        $user['role'] = $role;
                    }
                    return $user;
                } else {
                    // Search in both tables
                    $stmt = $db->prepare("SELECT *, 'teacher' as role FROM teachers WHERE id = ? UNION SELECT *, 'student' as role FROM students WHERE id = ? LIMIT 1");
                    $stmt->execute([$id, $id]);
                    return $stmt->fetch(PDO::FETCH_ASSOC);
                }
            } catch(PDOException $e) {
                return false;
            }
        }
        
        public static function getAllTeachers(){
            try {
                $db = database::getConnection();
                $stmt = $db->query("SELECT fullname, name, email, phone FROM teachers");
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
        
        public static function getAllStudents(){
            try {
                $db = database::getConnection();
                $stmt = $db->query("SELECT fullname, name, email, phone FROM students");
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
        
        public static function updateAvatar($username, $role, $avatarPath) {
            try {
                $db = database::getConnection();
                
                if ($role === 'teacher') {
                    $table = 'teachers';
                } else {
                    $table = 'students';
                }
                
                // Update avatar path in database
                $stmt = $db->prepare("UPDATE $table SET avatar = ? WHERE name = ?");
                return $stmt->execute([$avatarPath, $username]);
                
            } catch (PDOException $e) {
                error_log("Update avatar error: " . $e->getMessage());
                return false;
            }
        }
        
        public static function findByEmail($email) {
            try {
                $db = database::getConnection();

                // Prefer students table when searching by email (used by teacher to edit students)
                $stmt = $db->prepare("SELECT *, 'student' as role FROM students WHERE email = ? LIMIT 1");
                $stmt->execute([$email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($user) return $user;

                // Fallback: check teachers table
                $stmt = $db->prepare("SELECT *, 'teacher' as role FROM teachers WHERE email = ? LIMIT 1");
                $stmt->execute([$email]);
                return $stmt->fetch(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                return false;
            }
        }
    }
?>
