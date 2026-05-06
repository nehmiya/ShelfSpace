<?php
// add_book.php
require_once 'config.php';
require_once 'partials/auth_check.php';
require_admin();

$error = '';
$success = '';

// Fetch categories for dropdown
$categories = $pdo->query("SELECT * FROM categories ORDER BY category_name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $isbn = trim($_POST['isbn'] ?? '');
    $category_id = $_POST['category_id'] ?? '';
    $cover_image = trim($_POST['cover_image'] ?? '');
    
    if (empty($title) || empty($author) || empty($isbn) || empty($category_id)) {
        $error = "Title, author, ISBN, and category are required.";
    } else {
        // Check if ISBN exists
        $stmt = $pdo->prepare("SELECT id FROM books WHERE isbn = ?");
        $stmt->execute([$isbn]);
        if ($stmt->fetch()) {
            $error = "A book with this ISBN already exists.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO books (title, author, isbn, category_id, cover_image) VALUES (?, ?, ?, ?, ?)");
            if ($stmt->execute([$title, $author, $isbn, $category_id, empty($cover_image) ? null : $cover_image])) {
                $success = "Book added successfully!";
                // Clear post data to prevent resubmission display
                $_POST = array();
            } else {
                $error = "Failed to add book.";
            }
        }
    }
}
?>

<?php include 'partials/header.php'; ?>

<div style="max-width: 600px; margin: 0 auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h2>Add New Book</h2>
        <a href="admin.php" class="btn btn-outline">&larr; Back to Admin</a>
    </div>
    
    <div class="card">
        <div class="card-body">
            <?php if ($error): ?>
                <div class="alert alert-error"><?= sanitize($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?= sanitize($success) ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label" for="title">Book Title *</label>
                    <input type="text" id="title" name="title" class="form-control" required value="<?= sanitize($_POST['title'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="author">Author *</label>
                    <input type="text" id="author" name="author" class="form-control" required value="<?= sanitize($_POST['author'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="isbn">ISBN *</label>
                    <input type="text" id="isbn" name="isbn" class="form-control" required value="<?= sanitize($_POST['isbn'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="category_id">Category *</label>
                    <?php if (empty($categories)): ?>
                        <p style="color: var(--danger); font-size: 0.9rem;">Please create a category first.</p>
                    <?php else: ?>
                        <select id="category_id" name="category_id" class="form-control" required>
                            <option value="">Select Category...</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= (isset($_POST['category_id']) && $_POST['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                                    <?= sanitize($cat['category_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="cover_image">Cover Image URL (Optional)</label>
                    <input type="url" id="cover_image" name="cover_image" class="form-control" placeholder="https://example.com/image.jpg" value="<?= sanitize($_POST['cover_image'] ?? '') ?>">
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%;" <?= empty($categories) ? 'disabled' : '' ?>>Save Book</button>
            </form>
        </div>
    </div>
</div>

<?php include 'partials/footer.php'; ?>
