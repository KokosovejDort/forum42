<?php
require_once __DIR__.'/include/db.php';
require_once __DIR__.'/include/header.php';

$search_type = $_GET['type'] ?? 'threads';
$search_query = $_GET['q'] ?? '';
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$items_per_page = 10;

$results = [];

if ($search_type === 'threads') {
    if (!empty($search_query)) {
        $sql = "
            SELECT t.*, u.username AS author_name, c.name AS category_name,
                   (SELECT COUNT(*) FROM forum_posts WHERE thread_id = t.thread_id) AS post_count
            FROM forum_threads t
            JOIN forum_users u ON t.author_id = u.user_id
            JOIN forum_categories c ON t.category_id = c.category_id
            WHERE t.title LIKE ?
            ORDER BY t.created_at DESC";
    
        $query = $db->prepare($sql);
        $query->execute(["%{$search_query}%"]);
        $all_results = $query->fetchAll(PDO::FETCH_ASSOC);
    }
} else {
    $sql = "SELECT u.*, 
                   (SELECT COUNT(*) FROM forum_threads WHERE author_id = u.user_id) AS thread_count,
                   (SELECT COUNT(*) FROM forum_posts WHERE author_id = u.user_id) AS post_count
            FROM forum_users u
            WHERE u.username LIKE ?
            ORDER BY u.username ASC";
    
    $query = $db->prepare($sql);
    $query->execute(["%{$search_query}%"]);
    $all_results = $query->fetchAll(PDO::FETCH_ASSOC);
}


$total_items = count($all_results);
$total_pages = ceil($total_items / $items_per_page);
$start_index = ($current_page - 1) * $items_per_page;
$results = array_slice($all_results, $start_index, $items_per_page);
?>

<div class="page-container">
    <div class="content-card">
        <div class="content-card-header">
            <h2>Search Results</h2>
        </div>
        <div class="content-card-body">
            <?php if (empty($results)): ?>
                <p class="text-center">No results found.</p>
            <?php else: ?>
                <div class="thread-list">
                    <?php if ($search_type === 'threads'): ?>
                        <?php foreach ($results as $thread): ?>
                            <div class="thread-item">
                                <div class="thread-main">
                                    <a href="thread.php?id=<?= $thread['thread_id'] ?>" class="thread-title">
                                        <?= htmlspecialchars($thread['title']) ?>
                                    </a>
                                    <?php if (!empty($thread['is_closed'])): ?>
                                        <span class="status-badge status-badge-secondary">Closed</span>
                                    <?php endif; ?>
                                </div>
                                <div class="thread-meta thread-meta-gap">
                                    <span class="thread-author">
                                        <i class="bi bi-person"></i> <?= htmlspecialchars($thread['author_name']) ?>
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
                    <?php else: ?>
                        <?php foreach ($results as $user): ?>
                            <div class="thread-item">
                                <div class="thread-main">
                                    <span class="thread-title">
                                        <i class="bi bi-person"></i> <?= htmlspecialchars($user['username']) ?>
                                    </span>
                                </div>
                                <div class="thread-meta thread-meta-gap">
                                    <span>
                                        Threads: <?= $user['thread_count'] ?>
                                    </span>
                                    <span>
                                        Posts: <?= $user['post_count'] ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
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