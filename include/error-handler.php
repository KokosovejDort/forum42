<?php

function render_error(string $message, int $statusCode = 400) {
    http_response_code($statusCode);
    echo '<div class="page-container">';
    echo '<div class="content-card">';
    echo '<div class="content-card-header"><h2>Error: Status Code ' . $statusCode . '</h2></div>';
    echo '<div class="content-card-body" style="text-align: center; padding: 1rem;">';
    echo '<p style="color: #d00; font-weight: bold;">' . htmlspecialchars($message) . '</p>';
    echo '<p><a href="javascript:history.back()" style="margin-right: 1rem;">Go Back</a> <a href="/~dudt05/semestralka/index.php">Home</a></p>';
    echo '</div></div></div>';
    include __DIR__ . '/footer.php';
    exit;
} 