<?php
// $assignment, $currentUser từ assignmentController::showDetail()
$submissions = !empty($assignment['submiss']) ? json_decode($assignment['submiss'], true) : [];
if (!is_array($submissions)) {
    $submissions = [];
}

require_once __DIR__ . '/../../models/user.php';
$userData = user::findByUsername($currentUser['username'], $currentUser['role']);
$avatarPath = '/htdocs/uploads/avatars/default-avatar.jpg';
if ($userData && !empty($userData['avatar'])) {
    $fullPath = __DIR__ . '/../../..' . $userData['avatar'];
    if (file_exists($fullPath)) {
        $avatarPath = $userData['avatar'];
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết bài tập - <?= htmlspecialchars($assignment['name']) ?></title>
    <link rel="stylesheet" href="/htdocs/assets/styles/custom-lib.css">
    <link rel="stylesheet" href="/htdocs/assets/styles/class.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/htdocs/assets/styles/assignment-detail.css">
</head>
<body>
    <nav class="navbar">
        <div class="navbar-content">
            <div class="navbar-brand">FHC Student Management System</div>
            <div class="navbar-menu">
                <a href="/class" class="nav-link">Assignments</a>
                <button class="btn-assignments" onclick="window.location.href='/class'">
                    <i class="fas fa-list"></i> Danh sách bài tập
                </button>
                <div class="nav-user" onclick="window.location.href='/profile'">
                    <img src="<?= htmlspecialchars($avatarPath) ?>" alt="Avatar" class="user-avatar">
                    <span class="user-name"><?= htmlspecialchars($currentUser['fullname']) ?></span>
                </div>
            </div>
        </div>
    </nav>

    <div class="detail-container">
        <div class="detail-header">
            <a href="/class" class="detail-back">
                <i class="fas fa-arrow-left"></i> Quay lại danh sách bài tập
            </a>
            <h1 class="detail-title"><?= htmlspecialchars($assignment['name']) ?></h1>
            <div class="detail-meta">
                <span><i class="fas fa-user-tie"></i> Giáo viên: <?= htmlspecialchars($assignment['teacher']) ?></span>
                <span><i class="fas fa-clock"></i> Hết hạn: <?= date('d/m/Y H:i', strtotime($assignment['exp'])) ?></span>
            </div>
        </div>

        <div class="detail-content-wrapper">
            <div class="detail-section">
                <h3><i class="fas fa-align-left"></i> Mô tả</h3>
                <div class="detail-description"><?= nl2br(htmlspecialchars($assignment['de'] ?? '')) ?></div>
                <a href="<?= htmlspecialchars($assignment['upload']) ?>" download class="detail-download-assignment">
                    <i class="fas fa-download"></i> Tải file đề bài tập
                </a>
            </div>

            <div class="detail-section">
                <h3><i class="fas fa-users"></i> Danh sách đã nộp (<?= count($submissions) ?>)</h3>
                <?php if (empty($submissions)): ?>
                    <p class="empty-submissions">Chưa có học sinh nào nộp bài.</p>
                <?php else: ?>
                    <table class="submissions-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Học sinh</th>
                                <th>Bài làm</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; foreach ($submissions as $username => $filePath): ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td><?= htmlspecialchars($username) ?></td>
                                <td>
                                    <a href="<?= htmlspecialchars($filePath) ?>" download class="submission-download">
                                        <i class="fas fa-file-download"></i> Tải bài làm
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
