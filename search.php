<?php
require_once __DIR__.'/include/db.php';
require_once __DIR__.'/include/header.php';

$search_type = $_GET['type'] ?? 'threads';
$search_query = $_GET['q'] ?? '';
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$items_per_page = 20;

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

<div class="container mt-4">
    <?php if (empty($results)): ?>
        <p>No results found.</p>
    <?php else: ?>
        <div class="list-group mb-4">
            <?php if ($search_type === 'threads'): ?>
                <?php foreach ($results as $thread): ?>
                    <a href="thread.php?id=<?= $thread['thread_id'] ?>" class="list-group-item list-group-item-action py-3">
                        <div class="d-flex w-100 justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-1"><?= htmlspecialchars($thread['title']) ?></h5>
                                <small class="text-muted">
                                    Category: <?= htmlspecialchars($thread['category_name']) ?> |
                                    Author: <?= htmlspecialchars($thread['author_name']) ?> |
                                    Posts: <?= $thread['post_count'] ?>
                                </small>
                            </div>
                            <small class="text-muted">
                                <?= date('M d, Y', strtotime($thread['created_at'])) ?>
                            </small>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <?php foreach ($results as $user): ?>
                    <div class="list-group-item py-3">
                        <div class="d-flex w-100 justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-1"><?= htmlspecialchars($user['username']) ?></h5>
                                <small class="text-muted">
                                    Threads: <?= $user['thread_count'] ?> |
                                    Posts: <?= $user['post_count'] ?>
                                </small>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php if ($total_pages > 1): ?>
            <nav aria-label="Page navigation" class="mb-4">
                <ul class="pagination justify-content-center flex-wrap">
                    <?php if ($current_page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $current_page - 1])) ?>">Previous</a>
                        </li>
                    <?php endif; ?>

                    <?php
                    $start_page = max(1, $current_page - 2);
                    $end_page = min($total_pages, $current_page + 2);
                    
                    if ($start_page > 1) {
                        echo '<li class="page-item"><a class="page-link" href="?' . http_build_query(array_merge($_GET, ['page' => 1])) . '">1</a></li>';
                        if ($start_page > 2) {
                            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                        }
                    }
                    
                    for ($i = $start_page; $i <= $end_page; $i++): ?>
                        <li class="page-item <?= $i === $current_page ? 'active' : '' ?>">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                        </li>
                    <?php endfor;
                    
                    if ($end_page < $total_pages) {
                        if ($end_page < $total_pages - 1) {
                            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                        }
                        echo '<li class="page-item"><a class="page-link" href="?' . http_build_query(array_merge($_GET, ['page' => $total_pages])) . '">' . $total_pages . '</a></li>';
                    }
                    ?>

                    <?php if ($current_page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $current_page + 1])) ?>">Next</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__.'/include/footer.php'; ?>