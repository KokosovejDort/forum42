<?php
  $db = new PDO('mysql:host=127.0.0.1;dbname=dbname;charset=utf8', 'dbname', 'password');

  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

function fetchThreadById(int $thread_id) {
    global $db;
    $stmt = $db->prepare("
        SELECT t.*, u.username AS author_name, c.name AS category_name
        FROM forum_threads t
        JOIN forum_users u ON t.author_id = u.user_id
        JOIN forum_categories c ON t.category_id = c.category_id
        WHERE t.thread_id = ?
    ");
    $stmt->execute([$thread_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function fetchThreadsByCategory(?int $category_id = null) {
    global $db;
    if ($category_id) {
        $stmt = $db->prepare("
            SELECT t.*, u.username, c.name AS category_name
            FROM forum_threads t
            JOIN forum_users u ON t.author_id = u.user_id
            JOIN forum_categories c ON t.category_id = c.category_id
            WHERE t.category_id = ?
            ORDER BY t.created_at DESC
        ");
        $stmt->execute([$category_id]);
    } else {
        $stmt = $db->prepare("
            SELECT t.*, u.username, c.name AS category_name
            FROM forum_threads t
            JOIN forum_users u ON t.author_id = u.user_id
            JOIN forum_categories c ON t.category_id = c.category_id
            ORDER BY t.created_at DESC
        ");
        $stmt->execute();
    }
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function searchThreads(string $query) {
    global $db;
    $stmt = $db->prepare("
        SELECT t.*, u.username AS author_name, c.name AS category_name,
               (SELECT COUNT(*) FROM forum_posts WHERE thread_id = t.thread_id) AS post_count
        FROM forum_threads t
        JOIN forum_users u ON t.author_id = u.user_id
        JOIN forum_categories c ON t.category_id = c.category_id
        WHERE t.title LIKE ?
        ORDER BY t.created_at DESC
    ");
    $stmt->execute(["%{$query}%"]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function fetchPostById(int $post_id) {
    global $db;
    $stmt = $db->prepare("
        SELECT p.*, t.is_closed, t.thread_id
        FROM forum_posts p
        JOIN forum_threads t ON p.thread_id = t.thread_id
        WHERE p.post_id = ?
    ");
    $stmt->execute([$post_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function fetchPostsByThread(int $thread_id) {
    global $db;
    $stmt = $db->prepare("
        SELECT p.*, u.username AS author_name
        FROM forum_posts p
        LEFT JOIN forum_users u ON p.author_id = u.user_id
        WHERE p.thread_id = ?
        ORDER BY p.updated ASC
    ");
    $stmt->execute([$thread_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function fetchPostVotes(array $post_ids, ?int $user_id = null) {
    global $db;
    if (empty($post_ids)) return ['votes' => [], 'user_votes' => []];
    
    $placeholders = implode(',', array_fill(0, count($post_ids), '?'));
    $votes = [];
    $user_votes = [];
    
    $stmt = $db->prepare("
        SELECT post_id, SUM(vote_type) AS votes
        FROM forum_posts_votes
        WHERE post_id IN ($placeholders)
        GROUP BY post_id
    ");
    $stmt->execute($post_ids);
    $votes = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    if ($user_id) {
        $stmt = $db->prepare("
            SELECT post_id, vote_type
            FROM forum_posts_votes
            WHERE post_id IN ($placeholders) AND author_id = ?
        ");
        $user_vote_params = array_merge($post_ids, [$user_id]);
        $stmt->execute($user_vote_params);
        $user_votes = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }
    
    return ['votes' => $votes, 'user_votes' => $user_votes];
}

function fetchPostImages(array $post_ids) {
    global $db;
    if (empty($post_ids)) return [];
    
    $placeholders = implode(',', array_fill(0, count($post_ids), '?'));
    $stmt = $db->prepare("
        SELECT post_id, GROUP_CONCAT(image_path) as images
        FROM post_images
        WHERE post_id IN ($placeholders)
        GROUP BY post_id
    ");
    $stmt->execute($post_ids);
    return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
}

function searchUsers(string $query) {
    global $db;
    $stmt = $db->prepare("
        SELECT u.*, 
               (SELECT COUNT(*) FROM forum_threads WHERE author_id = u.user_id) AS thread_count,
               (SELECT COUNT(*) FROM forum_posts WHERE author_id = u.user_id) AS post_count
        FROM forum_users u
        WHERE u.username LIKE ?
        ORDER BY u.username ASC
    ");
    $stmt->execute(["%{$query}%"]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function fetchUserStats(int $user_id) {
    global $db;
    $stmt = $db->prepare("
        SELECT 
            (SELECT COUNT(*) FROM forum_threads WHERE author_id = ?) AS thread_count,
            (SELECT COUNT(*) FROM forum_posts WHERE author_id = ?) AS post_count,
            (SELECT COALESCE(SUM(vote_type), 0) FROM forum_posts_votes 
             JOIN forum_posts ON forum_posts_votes.post_id = forum_posts.post_id
             WHERE forum_posts.author_id = ?) AS total_votes
    ");
    $stmt->execute([$user_id, $user_id, $user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function fetchAllCategories() {
    global $db;
    $stmt = $db->query("
        SELECT c.*, COUNT(t.thread_id) AS thread_count
        FROM forum_categories c
        LEFT JOIN forum_threads t ON c.category_id = t.category_id
        GROUP BY c.category_id
        ORDER BY c.category_id
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function fetchCategoryById(int $category_id) {
    global $db;
    $stmt = $db->prepare("
        SELECT c.*, COUNT(t.thread_id) AS thread_count
        FROM forum_categories c
        LEFT JOIN forum_threads t ON c.category_id = t.category_id
        WHERE c.category_id = ?
        GROUP BY c.category_id
    ");
    $stmt->execute([$category_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function checkPostPermissions(int $post_id, ?int $user_id, bool $is_admin = false) {
    if (!$user_id) {
        return [
            'can_edit' => false,
            'can_delete' => false,
            'can_vote' => false,
            'error' => [
                'message' => "You must be logged in to perform this action.",
                'code' => 401
            ]
        ];
    }

    $post = fetchPostById($post_id);
    if (!$post) {
        return [
            'can_edit' => false,
            'can_delete' => false,
            'can_vote' => false,
            'error' => [
                'message' => "Post not found.",
                'code' => 404
            ]
        ];
    }

    if ($post['is_closed'] && !$is_admin) {
        return [
            'can_edit' => false,
            'can_delete' => false,
            'can_vote' => false,
            'error' => [
                'message' => "Cannot perform actions on posts in closed threads.",
                'code' => 403
            ]
        ];
    }

    $is_author = $post['author_id'] == $user_id;
    return [
        'can_edit' => $is_author || $is_admin,
        'can_delete' => $is_author || $is_admin,
        'can_vote' => true,
        'post' => $post,
        'error' => null
    ];
}

function canEditPost(int $post_id, ?int $user_id, bool $is_admin = false) {
    $result = checkPostPermissions($post_id, $user_id, $is_admin);
    if ($result['error']) {
        render_error($result['error']['message'], $result['error']['code']);
        return false;
    }
    return $result['can_edit'];
}

function canDeletePost(int $post_id, ?int $user_id, bool $is_admin = false) {
    $result = checkPostPermissions($post_id, $user_id, $is_admin);
    if ($result['error']) {
        render_error($result['error']['message'], $result['error']['code']);
        return false;
    }
    return $result['can_delete'];
}

function canVoteOnPost(int $post_id, ?int $user_id, bool $is_admin = false) {
    $result = checkPostPermissions($post_id, $user_id, $is_admin);
    if ($result['error']) {
        render_error($result['error']['message'], $result['error']['code']);
        return false;
    }
    return $result['can_vote'];
}

function getPostPermissions(int $post_id, ?int $user_id, bool $is_admin = false) {
    return checkPostPermissions($post_id, $user_id, $is_admin);
}

function fetchUserProfile(int $user_id) {
    global $db;
    $stmt = $db->prepare("
        SELECT username, email 
        FROM forum_users 
        WHERE user_id = ?
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function fetchUserRecentThreads(int $user_id) {
    global $db;
    $stmt = $db->prepare("
        SELECT t.*, c.name AS category_name, 
               (SELECT COUNT(*) FROM forum_posts WHERE thread_id = t.thread_id) AS reply_count
        FROM forum_threads t
        JOIN forum_categories c ON t.category_id = c.category_id
        WHERE t.author_id = ?
        ORDER BY t.created_at DESC
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function fetchUserRecentPosts(int $user_id) {
    global $db;
    $stmt = $db->prepare("
        SELECT p.*, t.title AS thread_title
        FROM forum_posts p 
        JOIN forum_threads t ON p.thread_id = t.thread_id
        WHERE p.author_id = ? AND (t.author_id != ? OR p.post_id NOT IN 
            (SELECT MIN(post_id) FROM forum_posts WHERE thread_id = p.thread_id))
        ORDER BY p.updated DESC
    ");
    $stmt->execute([$user_id, $user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function fetchAllUsers() {
    global $db;
    $stmt = $db->query("
        SELECT user_id, username, email, admin
        FROM forum_users
        ORDER BY user_id
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function fetchAllThreadsForAdmin() {
    global $db;
    $stmt = $db->query("
        SELECT t.*, u.username AS author_name, c.name AS category_name 
        FROM forum_threads t
        JOIN forum_users u ON t.author_id = u.user_id
        JOIN forum_categories c ON t.category_id = c.category_id
        ORDER BY t.thread_id
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
