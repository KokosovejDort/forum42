<?php
require_once __DIR__.'/include/db.php';
require_once __DIR__.'/include/header.php';

$category_filter = isset($_GET['category']) && $_GET['category'] !== '' ? $_GET['category'] : null;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$items_per_page = 10; // Changed to 20 items per page

if ($category_filter) {
    $query = $db->prepare("SELECT t.*, u.username, c.name AS category_name
                           FROM forum_threads t
                           JOIN forum_users u ON t.author_id = u.user_id
                           JOIN forum_categories c ON t.category_id = c.category_id
                           WHERE t.category_id = ?
                           ORDER BY t.created_at DESC");
    $query->execute([$category_filter]);
} 
else {
    $query = $db->query("SELECT t.*, u.username, c.name AS category_name
                         FROM forum_threads t
                         JOIN forum_users u ON t.author_id = u.user_id
                         JOIN forum_categories c ON t.category_id = c.category_id
                         ORDER BY t.created_at DESC");
	$query->execute();
}

$all_threads = $query->fetchAll(PDO::FETCH_ASSOC);


$total_items = count($all_threads);
$total_pages = ceil($total_items / $items_per_page);


$start_index = ($current_page - 1) * $items_per_page;
$threads = array_slice($all_threads, $start_index, $items_per_page);
?>

<div class="page-container">
    <div class="content-card">
        <div class="content-card-header">
            <h2>Discussion Threads</h2>
        </div>
        <div class="content-card-body">
            <?php if (empty($threads)): ?>
                <p class="text-center">No threads found.</p>
            <?php else: ?>
                <div class="thread-list">
                    <?php foreach ($threads as $thread): ?>
                        <div class="thread-item">
                            <div class="thread-main">
                                <a href="thread.php?id=<?= $thread['thread_id'] ?>" class="thread-title">
                                    <?= htmlspecialchars($thread['title']) ?>
                                </a>
                                <?php if ($thread['is_closed']): ?>
                                    <span class="status-badge status-badge-secondary">Closed</span>
                                <?php endif; ?>
                            </div>
                            <div class="thread-meta thread-meta-gap">
                                <span class="thread-author">
                                    <i class="bi bi-person"></i> <?= htmlspecialchars($thread['username']) ?>
                                </span>
                                <span class="thread-category">
                                    <i class="bi bi-folder"></i> <?= htmlspecialchars($thread['category_name']) ?>
                                </span>
                                <span class="thread-date">
                                    <i class="bi bi-calendar"></i> <?= date('M d, Y', strtotime($thread['created_at'])) ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($current_page > 1): ?>
                            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $current_page - 1])) ?>" class="pagination-link">
                                <i class="bi bi-chevron-left"></i> Previous
                            </a>
                        <?php endif; ?>

                        <?php
                        $start_page = max(1, $current_page - 2);
                        $end_page = min($total_pages, $current_page + 2);
                        
                        if ($start_page > 1): ?>
                            <a href="?<?= http_build_query(array_merge($_GET, ['page' => 1])) ?>" class="pagination-link">1</a>
                            <?php if ($start_page > 2): ?>
                                <span class="pagination-ellipsis">...</span>
                            <?php endif;
                        endif;
                        
                        for ($i = $start_page; $i <= $end_page; $i++): ?>
                            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" 
                               class="pagination-link <?= $i === $current_page ? 'active' : '' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor;
                        
                        if ($end_page < $total_pages): 
                            if ($end_page < $total_pages - 1): ?>
                                <span class="pagination-ellipsis">...</span>
                            <?php endif; ?>
                            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $total_pages])) ?>" class="pagination-link">
                                <?= $total_pages ?>
                            </a>
                        <?php endif; ?>

                        <?php if ($current_page < $total_pages): ?>
                            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $current_page + 1])) ?>" class="pagination-link">
                                Next <i class="bi bi-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__.'/include/footer.php'; ?>
