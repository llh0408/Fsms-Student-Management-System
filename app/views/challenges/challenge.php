<?php
    require_once __DIR__ . '/../../core/jwt.php';
    $currentUser = JWT::isLoggedIn();

    if (!$currentUser) {
        header('Location: /login');
        exit();
    }

    // $challenge is provided by controller
    $flashError = $_SESSION['challenge_error'] ?? null;
    if (isset($_SESSION['challenge_error'])) {
        unset($_SESSION['challenge_error']);
    }

    $flashResult = $_SESSION['challenge_result'] ?? null;
    if (isset($_SESSION['challenge_result'])) {
        unset($_SESSION['challenge_result']);
    }

    // Get user data with avatar from database
    require_once __DIR__ . '/../../models/user.php';
    $userData = user::findByUsername($currentUser['username'], $currentUser['role']);

    $avatarPath = '/htdocs/uploads/avatars/default-avatar.jpg';
    if ($userData && !empty($userData['avatar'])) {
        $fullPath = __DIR__ . '/../../..' . $userData['avatar'];
        if (file_exists($fullPath)) {
            $avatarPath = $userData['avatar'];
        }
    }

    $challengeCode = $challenge['code'] ?? '';
    $challengeTitle = $challenge['title'] ?? '';
    $challengeHint = $challenge['hint'] ?? '';
    $challengeUpload = $challenge['upload'] ?? '';
    $challengeExp = $challenge['exp'] ?? '';
    // Format datetime for display
    $expDisplay = '';
    if ($challengeExp) {
        $dateTime = new DateTime($challengeExp);
        $expDisplay = $dateTime->format('d/m/Y H:i');
    }
    $challengeCreatedBy = $challenge['created_by'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Challenge - FHC Student Management System</title>
    <link rel="stylesheet" href="/htdocs/assets/styles/custom-lib.css" />
    <link rel="stylesheet" href="/htdocs/assets/styles/class.css" />
    <link rel="stylesheet" href="/htdocs/assets/styles/challenge.css" />
    <link rel="stylesheet" href="/htdocs/assets/styles/quiz.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
</head>
<body>
    <nav class="navbar">
        <div class="navbar-content">
            <div class="navbar-brand">FHC Student Management System</div>
            <div class="navbar-menu">
                <a href="/class" class="nav-link">Assignments</a>
                <div class="nav-user" onclick="window.location.href='/profile'">
                    <img src="<?php echo $avatarPath; ?>" alt="User Avatar" class="user-avatar" />
                    <span class="user-name"><?php echo htmlspecialchars($currentUser['fullname']); ?></span>
                </div>
            </div>
        </div>
    </nav>

    <div class="challenge-page">
        <div class="challenge-card">
            <?php if (isset($_SESSION['challenge_error'])): ?>
                <div class="toast toast-error" id="errorToast">
                    <div class="toast-icon">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <div class="toast-content">
                        <strong>Lỗi!</strong>
                        <span><?php echo htmlspecialchars($_SESSION['challenge_error']); ?></span>
                    </div>
                    <button class="toast-close" onclick="closeToast('errorToast')">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <?php unset($_SESSION['challenge_error']); ?>
            <?php endif; ?>

          <?php if ($flashResult !== null): ?> 
                <?php if (isset($flashResult['success']) && $flashResult['success']): ?>
                    <div class="toast toast-success" id="successToast">
                        <div class="toast-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="toast-content">
                            <strong>Thành công!</strong>
                            <span><?php echo htmlspecialchars($flashResult['message'] ?? 'Chúc mừng! Bạn đã trả lời đúng!'); ?></span>
                        </div>
                        <button class="toast-close" onclick="closeToast('successToast')">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                <?php else: ?>
                    <div class="toast toast-error" id="errorToast">
                        <div class="toast-icon">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                        <div class="toast-content">
                            <strong>Thất bại!</strong>
                            <span><?php echo htmlspecialchars($flashResult['message'] ?? 'Thất bại'); ?></span>
                        </div>
                        <button class="toast-close" onclick="closeToast('errorToast')">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                <?php endif; ?>
                <?php unset($_SESSION['challenge_result']); ?>
            <?php endif; ?>
            <div class="challenge-header">
                <div>
                    <h1><?php echo htmlspecialchars($challengeTitle); ?></h1>
                    <div class="challenge-meta">
                        <span class="badge"><i class="fas fa-hashtag"></i> <?php echo htmlspecialchars($challengeCode); ?></span>
                        <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($challengeCreatedBy); ?></span>
                        <span><i class="fas fa-hourglass-end"></i> <?php echo htmlspecialchars($expDisplay); ?></span>
                    </div>
                </div>
            </div>

            <div class="hint-box">
                <h3><i class="fas fa-lightbulb"></i> Gợi ý</h3>
                <p class="hint-text"><?php echo htmlspecialchars($challengeHint); ?></p>
            </div>

            <?php if ($currentUser['role'] === 'teacher'): ?>
                <div class="result-box" style="margin-top: 16px;">
                    <div><strong>Upload:</strong> <span class="upload-path"><?php echo htmlspecialchars($challengeUpload); ?></span></div>
                </div>
            <?php else: ?>
                <form class="answer-form" action="/challenges/check" method="POST">
                    <input type="hidden" name="code" value="<?php echo htmlspecialchars($challengeCode); ?>" />

                    <label for="answer">Nhập đáp án</label>
                    <div class="answer-row">
                        <input id="answer" name="answer" type="text" autocomplete="off" required />
                        <button type="submit"><i class="fas fa-check"></i> Submit</button>
                    </div>
                </form>

                <?php if (is_array($flashResult) && isset($flashResult['success']) && $flashResult['success'] && isset($flashResult['content'])): ?>
                    <div class="result-box">
                        <div><strong>Nội dung file:</strong></div>
                        <div class="result-content"><?php echo htmlspecialchars($flashResult['content']); ?></div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function closeToast(toastId) {
            const toast = document.getElementById(toastId);
            if (toast) {
                toast.classList.add('hiding');
                setTimeout(() => {
                    toast.remove();
                }, 300);
            }
        }

        // Auto-close toasts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const toasts = document.querySelectorAll('.toast');
            toasts.forEach(function(toast) {
                setTimeout(() => {
                    if (toast && !toast.classList.contains('hiding')) {
                        closeToast(toast.id);
                    }
                }, 5000);
            });
        });

        // Handle form submission with AJAX — show result on page without redirect
        document.querySelector('.answer-form')?.addEventListener('submit', function(e) {
            e.preventDefault();

            const form = this;
            const formData = new FormData(form);
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;

            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang kiểm tra...';
            submitBtn.disabled = true;

            fetch('/challenges/check', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(function(response) {
                const contentType = response.headers.get('Content-Type') || '';
                if (contentType.includes('application/json')) {
                    return response.text().then(function(text) {
                        var data;
                        try {
                            data = text ? JSON.parse(text) : null;
                        } catch (e) {
                            return { ok: false, data: { success: false, message: 'Máy chủ trả về không hợp lệ. Thử lại.' } };
                        }
                        return { ok: response.ok, data: data };
                    });
                }
                if (response.redirected && response.url) {
                    window.location.href = response.url;
                    return null;
                }
                if (!response.ok) {
                    return response.text().then(function() {
                        return { ok: false, data: { success: false, message: 'Máy chủ lỗi. Vui lòng thử lại.' } };
                    });
                }
                window.location.href = '/challenge/' + encodeURIComponent(formData.get('code'));
                return null;
            })
            .then(function(result) {
                if (result == null) return;

                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;

                var data = result.data;
                if (data && data.success) {
                    showToast('success', 'Thành công!', data.message || 'Chúc mừng! Bạn đã trả lời đúng!');
                    if (data.content) {
                        showResultBox(data.content);
                    }
                } else {
                    showToast('error', 'Thất bại!', (data && data.message) ? data.message : 'Không gửi được đáp án. Thử lại.');
                }
            })
            .catch(function(error) {
                console.error('Error:', error);
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
                showToast('error', 'Lỗi!', 'Không thể gửi đáp án. Thử lại.');
            });
        });

        function showToast(type, title, message) {
            const id = 'toast-' + Date.now();
            const toast = document.createElement('div');
            toast.id = id;
            toast.className = 'toast toast-' + type;
            toast.innerHTML = '<div class="toast-icon"><i class="fas fa-' + (type === 'success' ? 'check-circle' : 'exclamation-circle') + '"></i></div>' +
                '<div class="toast-content"><strong>' + escapeHtml(title) + '</strong><span>' + escapeHtml(message) + '</span></div>' +
                '<button class="toast-close" onclick="closeToast(\'' + id + '\')"><i class="fas fa-times"></i></button>';
            document.body.appendChild(toast);
            setTimeout(function() { if (document.getElementById(id)) closeToast(id); }, 5000);
        }

        function showResultBox(content) {
            const answerForm = document.querySelector('.answer-form');
            let box = document.querySelector('.result-box');
            if (!box) {
                box = document.createElement('div');
                box.className = 'result-box';
                box.innerHTML = '<div><strong>Nội dung file:</strong></div><div class="result-content"></div>';
                if (answerForm && answerForm.nextSibling) {
                    answerForm.parentNode.insertBefore(box, answerForm.nextSibling);
                } else if (answerForm) {
                    answerForm.parentNode.appendChild(box);
                }
            }
            box.querySelector('.result-content').textContent = content;
            box.style.display = '';
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>
</body>
</html>
