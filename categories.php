<?php
// categories.php
require_once 'config.php';
require_once 'partials/auth_check.php';
require_admin();

$error = '';
$success = '';

// Handle category addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $category_name = trim($_POST['category_name'] ?? '');
    
    if (empty($category_name)) {
        $error = "Category name is required.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM categories WHERE category_name = ?");
        $stmt->execute([$category_name]);
        if ($stmt->fetch()) {
            $error = "Category already exists.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO categories (category_name) VALUES (?)");
            if ($stmt->execute([$category_name])) {
                $success = "Category added successfully!";
            } else {
                $error = "Failed to add category.";
            }
        }
    }
}

// Handle category deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = (int)$_POST['category_id'];
    
    // Check if category has books
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM books WHERE category_id = ?");
    $stmt->execute([$id]);
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        $error = "Cannot delete category because it contains $count book(s). Delete the books first.";
    } else {
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        if ($stmt->execute([$id])) {
            $success = "Category deleted successfully!";
        } else {
            $error = "Failed to delete category.";
        }
    }
}

// Fetch all categories
$categories = $pdo->query("
    SELECT c.*, COUNT(b.id) as book_count 
    FROM categories c 
    LEFT JOIN books b ON c.id = b.category_id 
    GROUP BY c.id 
    ORDER BY c.category_name ASC
")->fetchAll();
?>

<?php include 'partials/header.php'; ?>

<div style="max-width: 800px; margin: 0 auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h2>Manage Categories</h2>
        <a href="admin.php" class="btn btn-outline">&larr; Back to Admin</a>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?= sanitize($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?= sanitize($success) ?></div>
    <?php endif; ?>
    
    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem;">
        <!-- Add Category Form -->
        <div class="card" style="align-self: start;">
            <div class="card-body">
                <h3 style="margin-bottom: 1rem;">Add Category</h3>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="add">
                    <div class="form-group">
                        <label class="form-label" for="category_name">Category Name</label>
                        <input type="text" id="category_name" name="category_name" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Add</button>
                </form>
            </div>
        </div>
        
        <!-- Category List -->
        <div class="card">
            <div class="card-body">
                <h3 style="margin-bottom: 1rem;">Existing Categories</h3>
                <?php if (empty($categories)): ?>
                    <p style="color: var(--text-secondary);">No categories found.</p>
                <?php else: ?>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Books Count</th>
                                    <th style="text-align: right;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($categories as $cat): ?>
                                    <tr>
                                        <td><strong><?= sanitize($cat['category_name']) ?></strong></td>
                                        <td><?= $cat['book_count'] ?></td>
                                        <td style="text-align: right;">
                                            <?php if ($cat['book_count'] == 0): ?>
                                                <form method="POST" style="margin: 0; display: inline-block;" onsubmit="return confirm('Are you sure you want to delete this category?');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="category_id" value="<?= $cat['id'] ?>">
                                                    <button type="submit" class="btn btn-danger" style="padding: 0.3rem 0.6rem; font-size: 0.8rem;">Delete</button>
                                                </form>
                                            <?php else: ?>
                                                <button class="btn btn-outline" style="padding: 0.3rem 0.6rem; font-size: 0.8rem;" disabled title="Cannot delete category with existing books">In Use</button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'partials/footer.php'; ?>
