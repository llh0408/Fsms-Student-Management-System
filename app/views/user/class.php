<?php
    require_once __DIR__ . '/../../core/jwt.php';
    $currentUser = JWT::isLoggedIn();
    
    if (!$currentUser) {
        header('Location: /login');
        exit();
    }
    
    // Get user data with avatar from database
    require_once __DIR__ . '/../../models/user.php';
    $userData = user::findByUsername($currentUser['username'], $currentUser['role']);
    
    // Avatar path from database or default
    $avatarPath = '/htdocs/uploads/avatars/default-avatar.jpg';
    if ($userData && !empty($userData['avatar'])) {
        // Check if file exists
        $fullPath = __DIR__ . '/../../..' . $userData['avatar'];
        if (file_exists($fullPath)) {
            $avatarPath = $userData['avatar'];
        }
    }  
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,1.0">
    <title>Class Assignments - FHC Student Management System</title>
    <link rel="stylesheet" href="/htdocs/assets/styles/custom-lib.css">
    <link rel="stylesheet" href="/htdocs/assets/styles/class.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="navbar-content">
            <div class="navbar-brand">FHC Student Management System</div>
            <div class="navbar-menu">
                <span>Assignments</span>
                <?php if ($currentUser['role'] === 'teacher'): ?>
                    <button class="btn-assignments" onclick="openCreateAssignmentModal()">
                        <i class="fas fa-file-alt"></i>
                        Tạo Bài Tập
                    </button>
                    <button class="btn-challenges" onclick="openCreateChallengeModal()">
                        <i class="fas fa-plus"></i>
                        Tạo Challenge
                    </button>
                <?php else: ?>
                    <button class="btn-challenges" onclick="openChallengeModal()">
                        <i class="fas fa-trophy"></i>
                        Join Challenges
                    </button>
                <?php endif; ?>
                <div class="nav-user" onclick="window.location.href='/profile'">
                    <img src="<?php echo $avatarPath; ?>" alt="User Avatar" class="user-avatar">
                    <span class="user-name"><?php echo htmlspecialchars($currentUser['fullname']); ?></span>
                </div>
            </div>
        </div>
    </nav>
        
    <div class="class-container">
        <div class="class-header">
            <h1 class="class-title">Class Assignments</h1>
        </div>

        <!-- Challenge Error Toast -->
        <?php if (isset($_SESSION['challenge_error'])): ?>
            <div class="toast toast-error" id="errorToast" style="position: fixed; top: 80px; right: 20px; z-index: 9999;">
                <div class="toast-icon">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <div class="toast-content">
                    <strong>Lỗi tạo challenge!</strong>
                    <span><?php echo htmlspecialchars($_SESSION['challenge_error']); ?></span>
                </div>
                <button class="toast-close" onclick="closeToast('errorToast')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <?php unset($_SESSION['challenge_error']); ?>
        <?php endif; ?>
        
        <div class="assignments-grid">
            <!-- Insert assignments here from db -->
         <?php
             require __DIR__ . '/../../controllers/assignmentController.php';
             $assignments = assignmentController::fetchAssignments();
             if ($assignments === null || empty($assignments)) {
                echo "<p>Hiện chưa có bài tập nào !!</p>";
             } else {
                foreach ($assignments as $am) {
                    require __DIR__ . '/../../views/assignments/assignment_tags.php';
                }
             }
         ?>
        </div>
    
    <!-- Create Challenge Modal (for teachers) -->
    <?php if ($currentUser['role'] === 'teacher'): ?>
    <div id="createChallengeModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-plus"></i> Tạo Quiz Challenge</h3>
                <span class="close" onclick="closeCreateChallengeModal()">&times;</span>
            </div>
            <div class="modal-body">
                <?php if (isset($_SESSION['challenge_error'])): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo htmlspecialchars($_SESSION['challenge_error']); ?></span>
                </div>
                <?php unset($_SESSION['challenge_error']); ?>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['challenge_success'])): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    <span><?php echo htmlspecialchars($_SESSION['challenge_success']); ?></span>
                </div>
                <?php unset($_SESSION['challenge_success']); ?>
                <?php endif; ?>
                
                <form id="createChallengeForm" action="/challenges/create" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="challengeTitle">Tiêu đề Challenge:</label>
                        <input type="text" id="challengeTitle" name="title" required placeholder="Ví dụ: Đố vui văn học">
                    </div>
                    
                    <div class="form-group">
                        <label for="challengeHint">Gợi ý (< 200 ký tự):</label>
                        <textarea id="challengeHint" name="hint" rows="3" required maxlength="200" placeholder="Nhập gợi ý về quyển sách... (tối đa 200 ký tự)" oninput="updateHintCharCount()"></textarea>
                        <small style="color: #6c757d; display: block; margin-top: 5px;">
                            <span id="hintCharCount">0</span>/200 ký tự
                        </small>
                    </div>

                    <div class="form-group">
                        <label for="challengeExp">Hết hạn (exp):</label>
                        <input type="datetime-local" id="challengeExp" name="exp" required>
                        <small style="color: #6c757d; display: block; margin-top: 5px;">
                            Chọn ngày giờ hết hạn cho challenge.
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <label for="challengeFile">Upload file nội dung (.txt):</label>
                        <input type="file" id="challengeFile" name="challenge_file" accept=".txt" required>
                        <small style="color: #6c757d; display: block; margin-top: 5px;">
                            <i class="fas fa-info-circle"></i>
                            Tên file phải không dấu, các từ cách nhau bởi 1 khoảng trắng, và kết thúc bằng .txt. Tối đa 50MB.
                        </small>
                    </div>
                    
                    <div class="modal-actions">
                        <button type="button" class="btn-cancel" onclick="closeCreateChallengeModal()">Hủy</button>
                        <button type="submit" class="btn-create">
                            <i class="fas fa-plus"></i>
                            Tạo Challenge
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Join Challenge Modal (for students) -->
    <?php if ($currentUser['role'] === 'student'): ?>
    <div id="challengeModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-trophy"></i> Tham gia Challenge</h3>
                <span class="close" onclick="closeChallengeModal()">&times;</span>
            </div>
            <div class="modal-body">
                <?php if (isset($_SESSION['challenge_error'])): ?>
                <div id="challengeError" class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <span id="errorText"><?php echo htmlspecialchars($_SESSION['challenge_error']); ?></span>
                </div>
                <?php unset($_SESSION['challenge_error']); ?>
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="challengeCode">Nhập mã challenge:</label>
                    <input type="text" id="challengeCode" placeholder="Ví dụ: CH1234ABCD" maxlength="20">
                    <small>Mã challenge được cung cấp bởi giảng viên</small>
                </div>
                
                <div class="modal-actions">
                    <button class="btn-cancel" onclick="closeChallengeModal()">Hủy</button>
                    <button class="btn-join" onclick="joinChallenge()">
                        <i class="fas fa-sign-in-alt"></i>
                        Tham gia
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Create Assignment Modal (for teachers) -->
    <?php if ($currentUser['role'] === 'teacher'): ?>
    <div id="createAssignmentModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-file-alt"></i> Tạo Bài Tập Mới</h3>
                <span class="close" onclick="closeCreateAssignmentModal()">&times;</span>
            </div>
            <div class="modal-body">
                <?php if (isset($_SESSION['assignment_error'])): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo htmlspecialchars($_SESSION['assignment_error']); ?></span>
                </div>
                <?php unset($_SESSION['assignment_error']); ?>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['assignment_success'])): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    <span><?php echo htmlspecialchars($_SESSION['assignment_success']); ?></span>
                </div>
                <?php unset($_SESSION['assignment_success']); ?>
                <?php endif; ?>
                
                <form id="createAssignmentForm" action="/assignments/create" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="assignmentName">Tên Bài Tập:</label>
                        <input type="text" id="assignmentName" name="name" required placeholder="Ví dụ: Lab Assignment 1: Basic Algorithms">
                    </div>
                    
                    <div class="form-group">
                        <label for="assignmentDescription">Mô Tả:</label>
                        <textarea id="assignmentDescription" name="description" required placeholder="Mô tả chi tiết về bài tập, yêu cầu, và nội dung cần hoàn thành..." rows="4"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="assignmentExp">Thời Gian Hết Hạn:</label>
                        <input type="datetime-local" id="assignmentExp" name="exp" required>
                        <small style="color: #6c757d; display: block; margin-top: 5px;">
                            <i class="fas fa-info-circle"></i>
                            Chọn ngày giờ hết hạn cho bài tập.
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <label for="assignmentFile">Upload File Bài Tập:</label>
                        <input type="file" id="assignmentFile" name="assignment_file" accept=".txt, .pdf,.doc,.docx,.zip,.rar, .xls, .xlsx" required>
                        <small style="color: #6c757d; display: block; margin-top: 5px;">
                            <i class="fas fa-info-circle"></i>
                            Chấp nhận các định dạng: TXT, PDF, DOC, DOCX, XLS, XLSX, ZIP, RAR Tối đa 36MB.
                        </small>
                    </div>
                    
                    <div class="modal-actions">
                        <button type="button" class="btn-cancel" onclick="closeCreateAssignmentModal()">Hủy</button>
                        <button type="submit" class="btn-create">
                            <i class="fas fa-plus"></i>
                            Tạo Bài Tập
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Assignment Error/Success Toasts -->
    <?php if (isset($_SESSION['assignment_error'])): ?>
        <div class="toast toast-error" id="assignmentErrorToast" style="position: fixed; top: 80px; right: 20px; z-index: 9999;">
            <div class="toast-icon">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <div class="toast-content">
                <strong>Lỗi bài tập!</strong>
                <span><?php echo htmlspecialchars($_SESSION['assignment_error']); ?></span>
            </div>
            <button class="toast-close" onclick="closeToast('assignmentErrorToast')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <?php unset($_SESSION['assignment_error']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['assignment_success'])): ?>
        <div class="toast toast-success" id="assignmentSuccessToast" style="position: fixed; top: 80px; right: 20px; z-index: 9999;">
            <div class="toast-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="toast-content">
                <strong>Thành công!</strong>
                <span><?php echo htmlspecialchars($_SESSION['assignment_success']); ?></span>
            </div>
            <button class="toast-close" onclick="closeToast('assignmentSuccessToast')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <?php unset($_SESSION['assignment_success']); ?>
    <?php endif; ?>
    
    <!-- Upload Modal -->
    <div id="uploadModal" class="modal">
        <form class="modal-content" action="/assignments/upload" method="POST" enctype="multipart/form-data" id="uploadForm">
            <div class="modal-header">
                <h3>Nộp bài tập<span id="assignment_Name" name="assignment_name"></span></h3>
                <span class="close-upload-modal">&times;</span>
            </div>
            <div class="modal-body">
                <div class="upload-area">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <p>Kéo file vào hoặc chọn file để nộp bài</p>
                    <input type="file" id="fileInput" name="assignment_file" accept=".pdf,.doc,.docx,.zip,.rar,.txt,.xls,.xlsx" required>
                    <button type="button" class="btn-browse">Chọn bài file</button>
                </div>
                <div class="upload-info">
                    <p><strong>Định dạng hỗ trợ:</strong> PDF, DOC, DOCX, ZIP, RAR, TXT, XLS, XLSX</p>
                    <p><strong>Kích thước file tối đa:</strong> 36MB</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel-upload">Hủy</button>
                <button type="submit" class="btn-submit-upload">Nộp bài</button>
            </div>
        </form>
    </div>
    
    <!-- Submissions Modal -->
    <div id="submissionsModal" class="modal">
        <div class="modal-content" style="max-width: 800px;">
            <div class="modal-header">
                <h3>Danh sách bài đã nộp</h3>
                <span class="close-submissions-modal">&times;</span>
            </div>
            <div class="modal-body">
                <div id="submissionsList">
                    <!-- Submissions will be loaded here -->
                </div>
            </div>
        </div>
    </div>
    
    <script src="/htdocs/assets/js/class.js"></script>
</body>
</html>
