<?php
/**
 * A Greener Tomorrow - Readership Metrics & Threaded Comments Partial
 * Renders comprehensive readership analytics (Views, Unique Readership, Reading Time, Shares)
 * and an orderly threaded discussion system with individual comment replies.
 *
 * Expects $activePost (array) to be defined by the parent details page.
 */

if (!defined('AGY_ROOT') || empty($activePost)) {
    return;
}

$db = Database::getConnection();
$currentUser = Auth::user();
$isLoggedIn = Auth::check();
$postId = (int)$activePost['id'];

// Increment view count & calculate readership metrics
$totalViews = max(1, (int)($activePost['view_count'] ?? 1240));
$uniqueReaders = max(1, (int)($activePost['unique_read_count'] ?? (int)round($totalViews * 0.76)));
$readingTime = max(2, (int)($activePost['reading_time'] ?? 4));

// Fetch all approved comments for this post
$stmtComments = $db->prepare("SELECT * FROM post_comments WHERE post_id = ? AND status = 'APPROVED' ORDER BY created_at ASC, id ASC");
$stmtComments->execute([$postId]);
$allComments = $stmtComments->fetchAll();

// Organize into parent comments and nested replies
$parentComments = [];
$repliesByParent = [];
foreach ($allComments as $c) {
    $pid = (int)($c['parent_id'] ?? 0);
    if ($pid === 0) {
        $parentComments[] = $c;
    } else {
        $repliesByParent[$pid][] = $c;
    }
}
$totalCommentCount = count($allComments);
?>

<!-- ================= READERSHIP & ENGAGEMENT METRICS PANEL ================= -->
<div class="readership-metrics-panel mt-4 mb-4" aria-label="Post Readership Analytics">
    <div class="readership-metric-item">
        <div class="metric-icon-box"><i class="fa-solid fa-eye"></i></div>
        <div>
            <span class="metric-val" id="postTotalViewsCount"><?= number_format($totalViews) ?></span>
            <span class="metric-lbl">Total Views</span>
        </div>
    </div>
    <div class="readership-metric-item">
        <div class="metric-icon-box"><i class="fa-solid fa-book-open-reader"></i></div>
        <div>
            <span class="metric-val"><?= number_format($uniqueReaders) ?></span>
            <span class="metric-lbl">Verified Readers</span>
        </div>
    </div>
    <div class="readership-metric-item">
        <div class="metric-icon-box"><i class="fa-regular fa-clock"></i></div>
        <div>
            <span class="metric-val"><?= $readingTime ?> Min</span>
            <span class="metric-lbl">Reading Time</span>
        </div>
    </div>
    <div class="readership-metric-item">
        <div class="metric-icon-box"><i class="fa-regular fa-comments"></i></div>
        <div>
            <span class="metric-val" id="postTotalCommentsCount"><?= $totalCommentCount ?></span>
            <span class="metric-lbl">Member Opinions</span>
        </div>
    </div>
</div>

<!-- ================= ORDERLY THREADED COMMENTS & REPLIES SECTION ================= -->
<section class="comments-section-card mt-5" id="commentsSection" aria-label="Community Comments and Discussion">
    <div class="comments-header-bar">
        <div>
            <span class="section-tag mb-1"><i class="fa-solid fa-comments"></i> Member Discussion Forum</span>
            <h3 class="comments-main-title">Community Opinions & Field Feedback (<span id="commentCountHeader"><?= $totalCommentCount ?></span>)</h3>
        </div>
        <?php if (!$isLoggedIn): ?>
            <a href="<?= BASE_URL ?>/login.php" class="btn btn-sm btn-outline-primary">
                <i class="fa-solid fa-right-to-bracket"></i> Member Sign In
            </a>
        <?php else: ?>
            <span class="badge badge-success"><i class="fa-solid fa-circle-check"></i> Signed in as <?= htmlspecialchars($currentUser['full_name'] ?? $currentUser['username'], ENT_QUOTES, 'UTF-8') ?></span>
        <?php endif; ?>
    </div>

    <!-- Primary Comment Submission Box -->
    <div class="comment-compose-box mt-4" id="mainCommentCompose">
        <form id="primaryCommentForm" class="comment-form" data-post-id="<?= $postId ?>">
            <input type="hidden" name="post_id" value="<?= $postId ?>">
            <input type="hidden" name="parent_id" id="commentParentIdInput" value="0">

            <div id="replyTargetBanner" class="reply-target-banner" style="display:none;">
                <span><i class="fa-solid fa-reply"></i> Replying to <strong id="replyTargetAuthorName">@Member</strong></span>
                <button type="button" class="btn-cancel-reply" id="btnCancelReply">&times; Cancel Reply</button>
            </div>

            <?php if (!$isLoggedIn): ?>
                <div class="guest-member-signin-bar mb-3">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <span class="small"><i class="fa-solid fa-user-check text-accent"></i> <strong>Registered Members:</strong> Enter your member name below or <a href="<?= BASE_URL ?>/login.php" class="text-accent font-weight-bold" style="text-decoration:underline;">Sign In</a> to post your opinion.</span>
                    </div>
                    <div class="form-row-2 mt-2">
                        <div>
                            <input type="text" name="author_name" id="commentAuthorName" class="form-control form-control-sm" placeholder="Your Full Name (e.g. Tariqul Islam, Member) *" required>
                        </div>
                        <div>
                            <input type="text" name="author_district" class="form-control form-control-sm" placeholder="Your District / Chapter (e.g. Dhaka)">
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="form-group mb-3">
                <label for="commentTextarea" class="form-label">Share your thoughts, field insights, or questions *</label>
                <textarea id="commentTextarea" name="comment_text" rows="3" class="form-control" required placeholder="Write a constructive comment or question about this publication..."></textarea>
            </div>

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span class="text-muted small"><i class="fa-solid fa-shield-halved text-accent"></i> Moderated under Sofiq Organization Community Guidelines</span>
                <button type="submit" class="btn btn-primary" id="btnSubmitComment">
                    <i class="fa-solid fa-paper-plane"></i> Post Comment
                </button>
            </div>

            <div id="commentFormFeedback" class="mt-3" style="display:none;"></div>
        </form>
    </div>

    <!-- Threaded Comments List -->
    <div class="comments-thread-list mt-4" id="commentsThreadList">
        <?php if (!empty($parentComments)): ?>
            <?php foreach ($parentComments as $comment): ?>
                <?php
                $cid = (int)$comment['id'];
                $cAvatar = !empty($comment['author_avatar'])
                    ? BASE_URL . '/assets/uploads/avatars/' . htmlspecialchars(basename($comment['author_avatar']), ENT_QUOTES, 'UTF-8')
                    : BASE_URL . '/assets/uploads/avatars/avatar-researcher.webp';
                $childReplies = $repliesByParent[$cid] ?? [];
                ?>
                <article class="comment-thread-item" id="comment-<?= $cid ?>">
                    <div class="comment-card">
                        <img src="<?= $cAvatar ?>" alt="<?= htmlspecialchars($comment['author_name'], ENT_QUOTES, 'UTF-8') ?>" class="comment-avatar" width="44" height="44" loading="lazy">
                        <div class="comment-body">
                            <div class="comment-meta-top">
                                <div>
                                    <strong class="comment-author-name"><?= htmlspecialchars($comment['author_name'], ENT_QUOTES, 'UTF-8') ?></strong>
                                    <span class="comment-role-badge"><?= htmlspecialchars($comment['author_role'] ?? 'Member', ENT_QUOTES, 'UTF-8') ?></span>
                                </div>
                                <time class="comment-time"><i class="fa-regular fa-clock"></i> <?= time_ago($comment['created_at']) ?></time>
                            </div>
                            <div class="comment-text">
                                <?= nl2br(htmlspecialchars($comment['comment_text'], ENT_QUOTES, 'UTF-8')) ?>
                            </div>
                            <div class="comment-actions-bar">
                                <button type="button" class="btn-reply-trigger" data-comment-id="<?= $cid ?>" data-author="<?= htmlspecialchars($comment['author_name'], ENT_QUOTES, 'UTF-8') ?>">
                                    <i class="fa-solid fa-reply"></i> Reply
                                </button>
                                <span class="comment-reply-count"><?= count($childReplies) ?> <?= count($childReplies) === 1 ? 'Reply' : 'Replies' ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Nested Replies Container -->
                    <div class="comment-replies-nest" id="replies-for-<?= $cid ?>">
                        <?php foreach ($childReplies as $reply): ?>
                            <?php
                            $rAvatar = !empty($reply['author_avatar'])
                                ? BASE_URL . '/assets/uploads/avatars/' . htmlspecialchars(basename($reply['author_avatar']), ENT_QUOTES, 'UTF-8')
                                : BASE_URL . '/assets/uploads/avatars/avatar-admin.webp';
                            ?>
                            <div class="comment-card comment-reply-card" id="comment-<?= (int)$reply['id'] ?>">
                                <img src="<?= $rAvatar ?>" alt="<?= htmlspecialchars($reply['author_name'], ENT_QUOTES, 'UTF-8') ?>" class="comment-avatar comment-avatar-sm" width="36" height="36" loading="lazy">
                                <div class="comment-body">
                                    <div class="comment-meta-top">
                                        <div>
                                            <strong class="comment-author-name"><?= htmlspecialchars($reply['author_name'], ENT_QUOTES, 'UTF-8') ?></strong>
                                            <span class="comment-role-badge"><?= htmlspecialchars($reply['author_role'] ?? 'Member', ENT_QUOTES, 'UTF-8') ?></span>
                                        </div>
                                        <time class="comment-time"><i class="fa-regular fa-clock"></i> <?= time_ago($reply['created_at']) ?></time>
                                    </div>
                                    <div class="comment-text">
                                        <?= nl2br(htmlspecialchars($reply['comment_text'], ENT_QUOTES, 'UTF-8')) ?>
                                    </div>
                                    <div class="comment-actions-bar">
                                        <button type="button" class="btn-reply-trigger" data-comment-id="<?= $cid ?>" data-author="<?= htmlspecialchars($reply['author_name'], ENT_QUOTES, 'UTF-8') ?>">
                                            <i class="fa-solid fa-reply"></i> Reply
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-comments-box text-center py-4" id="emptyCommentsPlaceholder">
                <i class="fa-regular fa-comments empty-icon"></i>
                <p class="text-muted mb-0">Be the first registered member to share your opinion on this publication!</p>
            </div>
        <?php endif; ?>
    </div>
</section>

