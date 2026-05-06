<?php
// index.php
require_once 'config.php';
require_once 'partials/auth_check.php';

// Handle Borrow Request
if (isset($_POST['borrow_book_id'])) {
    $book_id = (int)$_POST['borrow_book_id'];
    $user_id = $_SESSION['user_id'];
    
    // Check if book is available
    $stmt = $pdo->prepare("SELECT status FROM books WHERE id = ?");
    $stmt->execute([$book_id]);
    $book = $stmt->fetch();
    
    if ($book && $book['status'] === 'Available') {
        // Begin transaction
        $pdo->beginTransaction();
        try {
            // Update book status
            $updateStmt = $pdo->prepare("UPDATE books SET status = 'Borrowed' WHERE id = ?");
            $updateStmt->execute([$book_id]);
            
            // Insert into borrowings
            $insertStmt = $pdo->prepare("INSERT INTO borrowings (book_id, user_id) VALUES (?, ?)");
            $insertStmt->execute([$book_id, $user_id]);
            
            $pdo->commit();
            $success = "Book borrowed successfully!";
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Failed to borrow book.";
        }
    } else {
        $error = "Book is not available.";
    }
}

// Fetch Categories for Filter
$catStmt = $pdo->query("SELECT * FROM categories ORDER BY category_name ASC");
$categories = $catStmt->fetchAll();

// Fetch all books with their categories
$booksStmt = $pdo->query("
    SELECT b.*, c.category_name 
    FROM books b 
    JOIN categories c ON b.category_id = c.id 
    ORDER BY b.added_on DESC
");
$books = $booksStmt->fetchAll();
?>

<?php include 'partials/header.php'; ?>

<div class="search-bar">
    <input type="text" id="searchInput" class="form-control search-input" placeholder="Search by title or author...">
    <select id="categoryFilter" class="form-control" style="width: auto;">
        <option value="">All Categories</option>
        <?php foreach ($categories as $cat): ?>
            <option value="<?= sanitize($cat['category_name']) ?>"><?= sanitize($cat['category_name']) ?></option>
        <?php endforeach; ?>
    </select>
</div>

<?php if (isset($success)): ?>
    <div class="alert alert-success"><?= sanitize($success) ?></div>
<?php endif; ?>
<?php if (isset($error)): ?>
    <div class="alert alert-error"><?= sanitize($error) ?></div>
<?php endif; ?>
<?php if (isset($_GET['error']) && $_GET['error'] == 'access_denied'): ?>
    <div class="alert alert-error">Access Denied: You must be an admin to view that page.</div>
<?php endif; ?>

<div class="book-grid">
    <?php foreach ($books as $book): ?>
        <div class="card book-card" 
             data-title="<?= sanitize($book['title']) ?>" 
             data-author="<?= sanitize($book['author']) ?>"
             data-category="<?= sanitize($book['category_name']) ?>">
             
            <div class="book-cover-container">
                <img src="<?= sanitize($book['cover_image'] ?? 'https://ui-avatars.com/api/?name='.urlencode($book['title']).'&background=2a3b52&color=fff&size=512') ?>" 
                     alt="<?= sanitize($book['title']) ?>" 
                     class="book-cover"
                     onerror="this.src='https://ui-avatars.com/api/?name=Book&background=2a3b52&color=fff&size=512'">
            </div>
            
            <div class="book-info">
                <h3 class="book-title"><?= sanitize($book['title']) ?></h3>
                <p class="book-author">by <?= sanitize($book['author']) ?></p>
                <div style="margin-bottom: 1rem; margin-top: 0.5rem;">
                    <span class="badge" style="background: rgba(255,255,255,0.1);"><?= sanitize($book['category_name']) ?></span>
                </div>
                
                <div class="book-meta">
                    <?php if ($book['status'] === 'Available'): ?>
                        <span class="badge badge-available">Available</span>
                        <form method="POST" style="margin: 0;">
                            <input type="hidden" name="borrow_book_id" value="<?= $book['id'] ?>">
                            <button type="submit" class="btn btn-primary" style="padding: 0.3rem 0.8rem; font-size: 0.85rem;">Borrow</button>
                        </form>
                    <?php else: ?>
                        <span class="badge badge-borrowed">Borrowed</span>
                        <button class="btn btn-outline" style="padding: 0.3rem 0.8rem; font-size: 0.85rem;" disabled>Unavailable</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    
    <?php if (empty($books)): ?>
        <p style="grid-column: 1 / -1; text-align: center; color: var(--text-secondary); padding: 3rem;">No books found in the library yet.</p>
    <?php endif; ?>
</div>

<?php include 'partials/footer.php'; ?>
