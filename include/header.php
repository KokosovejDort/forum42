<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <title><?php echo (!empty($pageTitle)?$pageTitle.' - ':'')?>Forum 42</title>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="css/custom.css" rel="stylesheet">
  </head>
  <body>
    <header class="site-header">
      <div class="container header-flex">
        <h1>Forum 42</h1>
      </div>
    </header>

    <div class="top-bar">
      <div class="top-bar-inner">
        <nav class="main-nav">
            <a href="/~dudt05/semestralka/index.php" class="nav-link">
                <i class="bi bi-house"></i> Home
            </a>
            <a href="/~dudt05/semestralka/create-thread.php" class="nav-link">
                <i class="bi bi-plus-circle"></i> New Thread
            </a>
            <a href="/~dudt05/semestralka/profile.php" class="nav-link">
                <i class="bi bi-person"></i> My Profile
            </a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="/~dudt05/semestralka/actions/user-management/logout.php" class="nav-link">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
                <?php if (isset($_SESSION['admin']) && $_SESSION['admin']): ?>
                    <a href="/~dudt05/semestralka/admin.php" class="nav-link">
                        <i class="bi bi-shield-lock"></i> Admin
                    </a>
                <?php endif; ?>
            <?php else: ?>
                <a href="/~dudt05/semestralka/login.php" class="nav-link">
                    <i class="bi bi-box-arrow-in-right"></i> Login
                </a>
                <a href="/~dudt05/semestralka/register.php" class="nav-link">
                    <i class="bi bi-person-plus"></i> Register
                </a>
            <?php endif; ?>
        </nav>
        <?php
        require_once __DIR__.'/db.php';
        $search_query = isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '';
        $search_type = isset($_GET['type']) ? $_GET['type'] : 'threads';

        $categories = fetchAllCategories();
        $selected_category = isset($_GET['category']) ? $_GET['category'] : '';
        ?>
        <form method="GET" action="/~dudt05/semestralka/search.php" class="search-form">
            <input type="text" name="q" class="form-input wide-input" placeholder="Search..." value="<?= $search_query ?>" required>
            <select name="type" class="form-input select-wide">
                <option value="threads" <?= $search_type === 'threads' ? 'selected' : '' ?>>Threads</option>
                <option value="users" <?= $search_type === 'users' ? 'selected' : '' ?>>Users</option>
            </select>
            <button type="submit" class="btn-action btn-primary">
                <i class="bi bi-search"></i> Search
            </button>
        </form>
        <form method="GET" action="/~dudt05/semestralka/index.php" class="filter-form">
            <select name="category" class="form-input wide-input">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['category_id'] ?>" <?= $selected_category == $cat['category_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn-action btn-outline-primary">
                <i class="bi bi-funnel"></i> Filter
            </button>
        </form>
      </div>
    </div>

    <div class="container">
