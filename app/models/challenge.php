<?php
    require_once __DIR__ . '/../core/database.php';
    
    class Challenge {
        public static function createChallenge($title, $hint, $uploadPath, $exp, $createdBy, $code = null) {
            try {
                $db = database::getConnection();

                if ($code === null) {
                    $code = self::generateUniqueCode();
                }

                $stmt = $db->prepare(
                    "INSERT INTO challenges (title, code, hint, upload, exp, created_by) VALUES (?, ?, ?, ?, ?, ?)"
                );

                $result = $stmt->execute([$title, $code, $hint, $uploadPath, $exp, $createdBy]);
                if (!$result) {
                    return ['success' => false, 'message' => 'Không thể tạo challenge!'];
                }

                return ['success' => true, 'code' => $code];
            } catch(PDOException $e) {
                error_log("Challenge create error: " . $e->getMessage());
                return ['success' => false, 'message' => 'Lỗi hệ thống!'];
            }
        }
        
        public static function getByCode($code) {
            try {
                $db = database::getConnection();
                $stmt = $db->prepare("SELECT * FROM challenges WHERE code = ?");
                $stmt->execute([$code]);
                return $stmt->fetch(PDO::FETCH_ASSOC);
            } catch(PDOException $e) {
                error_log("Challenge getByCode error: " . $e->getMessage());
                return false;
            }
        }

        public static function checkAnswerByCode($code, $userAnswer) {
            try {
                $challenge = self::getByCode($code);
                if (!$challenge) {
                    return ['success' => false, 'message' => 'Challenge không tồn tại!'];
                }

                $createdBy = isset($challenge['created_by']) ? trim((string) $challenge['created_by']) : '';
                $challengeCode = isset($challenge['code']) ? trim((string) $challenge['code']) : '';
                if ($createdBy === '' || $challengeCode === '') {
                    return ['success' => false, 'message' => 'Đáp án chưa chính xác!'];
                }

                $teacherFolder = preg_replace('/[^A-Za-z0-9_\-]/', '_', $createdBy);
                $folderRelative = 'htdocs/uploads/challenges/' . $teacherFolder . '/' . $challengeCode;
                $base = realpath(__DIR__ . '/../../../');
                if ($base === false) {
                    return ['success' => false, 'message' => 'Lỗi hệ thống!'];
                }
                $folderPath = $base . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $folderRelative);

                if (!is_dir($folderPath)) {
                    return ['success' => false, 'message' => 'Đáp án chưa chính xác!'];
                }

                // Đáp án = tên file (không đuôi). Chuẩn hóa: lowercase, chỉ khoảng trắng hợp lệ (1 space giữa các từ), rồi ghép .txt
                $normalized = self::normalizeFileName($userAnswer);
                if ($normalized === '') {
                    return ['success' => false, 'message' => 'Đáp án chưa chính xác!'];
                }

                $fileName = $normalized . '.txt';
                $sep = DIRECTORY_SEPARATOR;
                $fullPath = $folderPath . $sep . $fileName;

                if (!is_file($fullPath)) {
                    $fullPath = null;
                    $entries = @scandir($folderPath);
                    if ($entries !== false) {
                        foreach ($entries as $entry) {
                            if ($entry === '.' || $entry === '..' || is_dir($folderPath . $sep . $entry)) {
                                continue;
                            }
                            $baseName = pathinfo($entry, PATHINFO_FILENAME);
                            if (self::normalizeFileName($baseName) === $normalized) {
                                $fullPath = $folderPath . $sep . $entry;
                                break;
                            }
                        }
                    }
                }

                if ($fullPath === null || !is_file($fullPath)) {
                    return ['success' => false, 'message' => 'Đáp án chưa chính xác!'];
                }

                $content = @file_get_contents($fullPath);
                if ($content === false) {
                    return ['success' => false, 'message' => 'Không đọc được nội dung file!'];
                }
                return ['success' => true, 'content' => $content, 'message' => 'Chúc mừng! Bạn đã trả lời đúng!'];
            } catch (\Throwable $e) {
                error_log("Challenge checkAnswerByCode error: " . $e->getMessage());
                return ['success' => false, 'message' => 'Lỗi hệ thống!'];
            }
        }
        
        public static function generateUniqueCode() {
            $db = database::getConnection();

            while (true) {
                $code = 'CH' . strtoupper(bin2hex(random_bytes(4)));
                $stmt = $db->prepare("SELECT code FROM challenges WHERE code = ? LIMIT 1");
                $stmt->execute([$code]);
                if (!$stmt->fetch()) {
                    return $code;
                }
            }
        }

        /** Chuẩn hóa tên file / đáp án: trim, lowercase, chỉ 1 khoảng trắng giữa các từ (không dấu). */
        private static function normalizeFileName($value) {
            $value = trim((string) $value);
            $value = strtolower($value);
            $value = preg_replace('/\s+/', ' ', $value);
            return $value;
        }
    }
?>
