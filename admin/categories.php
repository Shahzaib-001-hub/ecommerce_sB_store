<?php
/**
 * Category Management
 * BazarPK - Super Admin Dashboard
 */

$adminTitle = "Categories";
require_once __DIR__ . '/includes/header.php';

$pdo = Database::getConnection();

// Handle Category Creation & Deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pdo) {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Security check failed. Please refresh.');
    } else {
        $action = sanitize($_POST['action'] ?? '');

        if ($action === 'add_category') {
            $name = sanitize($_POST['name'] ?? '');
            $desc = sanitize($_POST['description'] ?? '');
            $img  = sanitize($_POST['image'] ?? '');
            $feat = isset($_POST['is_featured']) ? 1 : 0;
            $slug = slugify($name);

            try {
                $stmt = $pdo->prepare("INSERT INTO `categories` (`name`, `slug`, `description`, `image`, `is_featured`) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$name, $slug, $desc, $img, $feat]);
                setFlash('success', "Category '{$name}' created.");
            } catch (Exception $e) {
                setFlash('error', 'Error creating category: ' . $e->getMessage());
            }
        } elseif ($action === 'delete_category') {
            $catId = (int)($_POST['category_id'] ?? 0);
            try {
                $stmt = $pdo->prepare("DELETE FROM `categories` WHERE id = ?");
                $stmt->execute([$catId]);
                setFlash('success', 'Category deleted.');
            } catch (Exception $e) {
                setFlash('error', 'Error deleting category: ' . $e->getMessage());
            }
        }
    }
}

$categories = [];
if ($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT c.*, COUNT(p.id) as product_count 
            FROM categories c 
            LEFT JOIN products p ON p.category_id = c.id 
            GROUP BY c.id 
            ORDER BY c.display_order ASC
        ");
        $categories = $stmt->fetchAll();
    } catch (Exception $e) {}
}
?>

<div class="admin-card">
    <div class="admin-card-header">
        <div>
            <h1 class="admin-card-title">Product Categories</h1>
            <span style="font-size: 13px; color: #64748b;">Organize store catalog and showcase collections</span>
        </div>
        <button type="button" class="btn-admin" onclick="openModal('addCatModal')">
            + Add Category
        </button>
    </div>

    <table class="admin-table">
        <thead>
            <tr>
                <th>Image</th>
                <th>Category Name</th>
                <th>Slug</th>
                <th>Products</th>
                <th>Featured</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $cat): ?>
                <tr>
                    <td>
                        <img src="<?= e($cat['image'] ?: BASE_URL . '/assets/images/placeholder.jpg') ?>" alt="Thumb" style="width: 42px; height: 42px; object-fit: cover; border-radius: 50%;">
                    </td>
                    <td style="font-weight: 700; color: #0f172a;"><?= e($cat['name']) ?></td>
                    <td style="font-size: 12px; color: #64748b;"><code><?= e($cat['slug']) ?></code></td>
                    <td style="font-weight: 600;"><?= $cat['product_count'] ?> items</td>
                    <td>
                        <span class="badge-status <?= $cat['is_featured'] ? 'badge-delivered' : 'badge-unpaid' ?>">
                            <?= $cat['is_featured'] ? 'Yes' : 'No' ?>
                        </span>
                    </td>
                    <td>
                        <form method="POST" onsubmit="return confirm('Delete this category?');" style="display: inline;">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="delete_category">
                            <input type="hidden" name="category_id" value="<?= $cat['id'] ?>">
                            <button type="submit" class="btn-admin-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Add Category Modal -->
<div id="addCatModal" class="modal-overlay">
    <div class="modal-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="margin: 0; font-size: 18px; color: #0f172a;">Add New Category</h3>
            <button type="button" onclick="closeModal('addCatModal')" style="background: none; border: none; font-size: 24px; cursor: pointer;">&times;</button>
        </div>

        <form method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="add_category">

            <div style="margin-bottom: 14px;">
                <label style="font-size: 12px; font-weight: 700; color: #334155; display: block; margin-bottom: 4px;">Category Name *</label>
                <input type="text" name="name" required placeholder="e.g. Traditional Footwear" class="admin-input">
            </div>

            <div style="margin-bottom: 14px;">
                <label style="font-size: 12px; font-weight: 700; color: #334155; display: block; margin-bottom: 4px;">Image URL</label>
                <input type="url" name="image" placeholder="https://..." class="admin-input">
            </div>

            <div style="margin-bottom: 14px;">
                <label style="font-size: 12px; font-weight: 700; color: #334155; display: block; margin-bottom: 4px;">Description</label>
                <textarea name="description" rows="2" class="admin-input"></textarea>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 600; color: #334155;">
                    <input type="checkbox" name="is_featured" value="1" checked style="accent-color: #0f766e;">
                    Feature on Homepage Nav &amp; Circles
                </label>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="closeModal('addCatModal')" class="btn-admin-secondary">Cancel</button>
                <button type="submit" class="btn-admin">Save Category</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
