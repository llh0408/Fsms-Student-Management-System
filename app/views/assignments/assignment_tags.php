   <div class="assignment-card">
                <div class="assignment-header">
                    <h3 class="assignment-title"><?= $am["name"] ?></h3>
                    <div class="assignment-meta">
                        <span class="teacher-name">
                            <i class="fas fa-user-tie"></i>
                            <?= $am["teacher"] ?>
                        </span>
                        <span class="deadline">
                            <i class="fas fa-clock"></i>
                            Hết hạn: <?= date("d/m/Y H:i", strtotime($am["exp"])) ?>
                        </span>
                    </div>
                </div>
                
                <div class="assignment-body">
                    <p class="assignment-description">
                        <?= $am["de"] ?>
                    </p>
                    <div class="assignment-status">
                        <?php 
                        $currentUser = JWT::isLoggedIn();
                        $submissions = json_decode($am["submiss"], true) ?: [];
                        $hasSubmitted = isset($submissions[$currentUser['username']]);
                        $isExpired = strtotime($am["exp"]) < time();
                        $submissionCount = count($submissions);
                        ?>
                        
                        <?php if ($currentUser['role'] === 'student'): ?>
                            <?php if ($hasSubmitted): ?>
                                <span class="status-badge submitted">Đã nộp</span>
                            <?php else: ?>
                                <span class="status-badge pending">Chưa làm</span>
                            <?php endif; ?>
                            <span class="submission-count" onclick="showSubmissions(<?= json_encode($am['name']) ?>, <?= json_encode($am['submiss']) ?>)"><?= $submissionCount ?> bài</span>
                        <?php else: ?>
                            <?php if ($isExpired): ?>
                                <span class="status-badge expired">Kết thúc</span>
                            <?php else: ?>
                                <span class="status-badge active">Đang làm</span>
                            <?php endif; ?>
                            <a href="/assignments/detail?name=<?= urlencode($am['name']) ?>" class="submission-count submission-detail-link" title="Xem chi tiết bài tập">
                                <i class="fas fa-list-alt"></i> <?= $submissionCount ?> bài đã nộp
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="assignment-actions">
                    <a href="<?= htmlspecialchars($am["upload"]) ?>" download>
                    <button class="btn-download">
                        <i class="fas fa-download"></i>
                        Tải về
                    </button>
                    </a>
                    <?php if ($currentUser['role'] === 'teacher'): ?>
                    <a href="/assignments/detail?name=<?= urlencode($am['name']) ?>" class="btn-detail">
                        <i class="fas fa-info-circle"></i>
                        Chi tiết
                    </a>
                    <?php elseif ($currentUser['role'] === 'student' && !$hasSubmitted && !$isExpired): ?>
                    <button class="btn-upload">
                        <i class="fas fa-upload"></i>
                        Nộp bài
                    </button>
                    <?php endif; ?>
                </div>
            </div>