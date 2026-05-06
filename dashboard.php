<?php
// dashboard.php
require_once 'config.php';
require_once 'partials/auth_check.php';

$user_id = $_SESSION['user_id'];

// Handle Return Request
if (isset($_POST['return_borrow_id'])) {
    $borrow_id = (int)$_POST['return_borrow_id'];
    
    // Fetch borrow record to ensure it belongs to this user and isn't returned
    $stmt = $pdo->prepare("SELECT book_id FROM borrowings WHERE id = ? AND user_id = ? AND returned_at IS NULL");
    $stmt->execute([$borrow_id, $user_id]);
    $borrowRecord = $stmt->fetch();
    
    if ($borrowRecord) {
        $book_id = $borrowRecord['book_id'];
        
        $pdo->beginTransaction();
        try {
            // Mark as returned
            $updateBorrow = $pdo->prepare("UPDATE borrowings SET returned_at = CURRENT_TIMESTAMP WHERE id = ?");
            $updateBorrow->execute([$borrow_id]);
            
            // Update book status
            $updateBook = $pdo->prepare("UPDATE books SET status = 'Available' WHERE id = ?");
            $updateBook->execute([$book_id]);
            
            $pdo->commit();
            $success = "Book returned successfully!";
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Failed to return book.";
        }
    }
}

// Fetch currently borrowed books
$stmt = $pdo->prepare("
    SELECT br.id as borrow_id, br.borrowed_at, b.title, b.author, b.cover_image, c.category_name 
    FROM borrowings br 
    JOIN books b ON br.book_id = b.id 
    JOIN categories c ON b.category_id = c.id
    WHERE br.user_id = ? AND br.returned_at IS NULL
    ORDER BY br.borrowed_at DESC
");
$stmt->execute([$user_id]);
$borrowed_books = $stmt->fetchAll();

// Fetch return history
$historyStmt = $pdo->prepare("
    SELECT br.borrowed_at, br.returned_at, b.title, b.author 
    FROM borrowings br 
    JOIN books b ON br.book_id = b.id 
    WHERE br.user_id = ? AND br.returned_at IS NOT NULL
    ORDER BY br.returned_at DESC
    LIMIT 10
");
$historyStmt->execute([$user_id]);
$history = $historyStmt->fetchAll();
?>

<?php include 'partials/header.php'; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <h2>Welcome, <?= sanitize($_SESSION['user_name']) ?>!</h2>
    <span class="badge" style="background: var(--accent-gradient); color: white; padding: 0.5rem 1rem; font-size: 0.9rem;">
        Role: <?= ucfirst(sanitize($_SESSION['role'])) ?>
    </span>
</div>

<?php if (isset($success)): ?>
    <div class="alert alert-success"><?= sanitize($success) ?></div>
<?php endif; ?>
<?php if (isset($error)): ?>
    <div class="alert alert-error"><?= sanitize($error) ?></div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 1fr; gap: 2rem;">
    <!-- Currently Borrowed -->
    <div>
        <h3 style="margin-bottom: 1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">Currently Borrowed</h3>
        
        <?php if (empty($borrowed_books)): ?>
            <div class="card" style="padding: 2rem; text-align: center; color: var(--text-secondary);">
                You haven't borrowed any books right now. <a href="index.php" style="color: var(--accent-primary);">Browse library</a>
            </div>
        <?php else: ?>
            <div class="book-grid" style="margin-top: 0;">
                <?php foreach ($borrowed_books as $book): ?>
                    <div class="card book-card">
                        <div class="book-cover-container" style="height: 200px;">
                            <img src="<?= sanitize($book['cover_image'] ?? 'https://ui-avatars.com/api/?name='.urlencode($book['title']).'&background=2a3b52&color=fff&size=512') ?>" alt="<?= sanitize($book['title']) ?>" class="book-cover" style="height: 200px;">
                        </div>
                        <div class="book-info">
                            <h3 class="book-title" style="font-size: 1rem;"><?= sanitize($book['title']) ?></h3>
                            <p class="book-author" style="font-size: 0.8rem;">by <?= sanitize($book['author']) ?></p>
                            <p style="font-size: 0.75rem; color: var(--text-secondary); margin-bottom: 1rem;">Borrowed: <?= date('M j, Y', strtotime($book['borrowed_at'])) ?></p>
                            
                            <form method="POST" style="margin-top: auto;">
                                <input type="hidden" name="return_borrow_id" value="<?= $book['borrow_id'] ?>">
                                <button type="submit" class="btn btn-outline" style="width: 100%; font-size: 0.85rem; border-color: var(--success); color: var(--success);">Return Book</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Borrow History -->
    <div>
        <h3 style="margin-bottom: 1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">Recent History</h3>
        
        <?php if (empty($history)): ?>
            <p style="color: var(--text-secondary);">No past borrowing history.</p>
        <?php else: ?>
            <div class="card table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Book Title</th>
                            <th>Author</th>
                            <th>Borrowed On</th>
                            <th>Returned On</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($history as $record): ?>
                            <tr>
                                <td><?= sanitize($record['title']) ?></td>
                                <td><?= sanitize($record['author']) ?></td>
                                <td><?= date('M j, Y', strtotime($record['borrowed_at'])) ?></td>
                                <td style="color: var(--success);"><?= date('M j, Y', strtotime($record['returned_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'partials/footer.php'; ?>
