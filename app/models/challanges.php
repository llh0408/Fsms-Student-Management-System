<?php
    require_once __DIR__ . '/../../core/jwt.php';
    require_once __DIR__ . '/../../core/database.php';
    class challenge {
        public function joinChallenge($id){
            $db = database::getConnection();
            $stmt = $db->prepare("SELECT * FROM challanges WHERE code = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
    }    
?>