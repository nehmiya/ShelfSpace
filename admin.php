<?php
// admin.php
require_once 'config.php';
require_once 'partials/auth_check.php';

// Ensure user is admin
require_admin();

// Fetch stats
$usersCount = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();
$booksCount = $pdo->query("SELECT COUNT(*) FROM books")->fetchColumn();
$borrowedCount = $pdo->query("SELECT COUNT(*) FROM books WHERE status = 'Borrowed'")->fetchColumn();
$categoriesCount = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();

// Fetch recent active borrowings
$recentBorrows = $pdo->query("
    SELECT br.borrowed_at, b.title, u.name as user_name 
    FROM borrowings br 
    JOIN books b ON br.book_id = b.id 
    JOIN users u ON br.user_id = u.id 
    WHERE br.returned_at IS NULL 
    ORDER BY br.borrowed_at DESC 
    LIMIT 5
")->fetchAll();
?>

<?php include 'partials/header.php'; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <h2>Admin Dashboard</h2>
    <div style="display: flex; gap: 1rem;">
        <a href="categories.php" class="btn btn-outline">Manage Categories</a>
        <a href="add_book.php" class="btn btn-primary">Add New Book</a>
    </div>
</div>

<div class="stats-grid">
    <div class="card stat-card">
        <div class="stat-value"><?= $booksCount ?></div>
        <div class="stat-label">Total Books</div>
    </div>
    <div class="card stat-card">
        <div class="stat-value" style="background: var(--success); -webkit-background-clip: text;"><?= $usersCount ?></div>
        <div class="stat-label">Active Users</div>
    </div>
    <div class="card stat-card">
        <div class="stat-value" style="background: var(--danger); -webkit-background-clip: text;"><?= $borrowedCount ?></div>
        <div class="stat-label">Books Borrowed</div>
    </div>
    <div class="card stat-card">
        <div class="stat-value" style="background: #eab308; -webkit-background-clip: text;"><?= $categoriesCount ?></div>
        <div class="stat-label">Categories</div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <h3 style="margin-bottom: 1rem;">Recent Borrowings (Active)</h3>
        <?php if (empty($recentBorrows)): ?>
            <p style="color: var(--text-secondary);">No active borrowings right now.</p>
        <?php else: ?>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Book Title</th>
                            <th>Borrowed By</th>
                            <th>Date Borrowed</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentBorrows as $borrow): ?>
                            <tr>
                                <td><strong><?= sanitize($borrow['title']) ?></strong></td>
                                <td><?= sanitize($borrow['user_name']) ?></td>
                                <td><?= date('M j, Y g:i A', strtotime($borrow['borrowed_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'partials/footer.php'; ?>
