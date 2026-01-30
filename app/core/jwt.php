<?php
    class JWT {
        private static $secret_key = 'FHC_Student_Management_THIS_GAME_MAY_BE_HARD_833jJHJ83rhjhf84hjh4itj483ymfn';
        private static $algorithm = 'HS256';
        private static $expiration = 1209600; // 14 days in seconds - Lưu cookie 14 ngày
        
        /**
         * Create JWT token
         */
        public static function encode($payload) {
            $header = json_encode(['typ' => 'JWT', 'alg' => self::$algorithm]);
            $payload['exp'] = time() + self::$expiration;
            $payload['iat'] = time();
            
            $header_encoded = self::base64url_encode($header);
            $payload_encoded = self::base64url_encode(json_encode($payload));
            
            $signature = hash_hmac('sha256', "$header_encoded.$payload_encoded", self::$secret_key, true);
            $signature_encoded = self::base64url_encode($signature);
            
            return "$header_encoded.$payload_encoded.$signature_encoded";
        }
        
        /**
         * Decode and verify JWT token
         */
        public static function decode($jwt) {
            $parts = explode('.', $jwt);
            
            if (count($parts) !== 3) {
                return false;
            }
            
            list($header_encoded, $payload_encoded, $signature_encoded) = $parts;
            
            // Verify signature
            $signature = hash_hmac('sha256', "$header_encoded.$payload_encoded", self::$secret_key, true);
            $expected_signature = self::base64url_encode($signature);
            
            if (!hash_equals($expected_signature, $signature_encoded)) {
                return false;
            }
            
            // Decode payload
            $payload = json_decode(self::base64url_decode($payload_encoded), true);
            
            // Check expiration
            if (isset($payload['exp']) && $payload['exp'] < time()) {
                return false;
            }
            
            return $payload;
        }
        
        /**
         * Set JWT token in cookie
         */
        public static function setCookie($payload) {
            $token = self::encode($payload);
            setcookie('auth_token', $token, [
                'expires' => time() + self::$expiration,
                'path' => '/',
                'httponly' => true,
                'samesite' => 'Strict'
            ]);
            $_SESSION['user'] = $payload;
        }
        
        /**
         * Get user from JWT cookie
         */
        public static function getUserFromCookie() {
            if (isset($_COOKIE['auth_token'])) {
                $payload = self::decode($_COOKIE['auth_token']);
                if ($payload) {
                    $_SESSION['user'] = $payload;
                    return $payload;
                }
            }
            return false;
        }
        
        /**
         * Clear JWT cookie
         */
        public static function clearCookie() {
            setcookie('auth_token', '', [
                'expires' => time() - 3600,
                'path' => '/',
                'httponly' => true,
                'samesite' => 'Strict'
            ]);
            unset($_COOKIE['auth_token']);
            unset($_SESSION['user']);
        }
        
        /**
         * Check if user is logged in
         */
        public static function isLoggedIn() {
            if (isset($_SESSION['user'])) {
                return $_SESSION['user'];
            }
            
            return self::getUserFromCookie();
        }
        
        /**
         * Base64 URL encode
         */
        private static function base64url_encode($data) {
            return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
        }
        
        /**
         * Base64 URL decode
         */
        private static function base64url_decode($data) {
            return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
        }
    }
?>
