<?php
/**
 * Product & Inventory Management
 * BazarPK - Super Admin Dashboard
 */

$adminTitle = "Products & Inventory";
require_once __DIR__ . '/includes/header.php';

$pdo = Database::getConnection();
$categories = [];

if ($pdo) {
    try {
        $cStmt = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC");
        $categories = $cStmt->fetchAll();
    } catch (Exception $e) {}
}

// Handle Add / Edit / Delete Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pdo) {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Security check failed. Please refresh.');
    } else {
        $action = sanitize($_POST['action'] ?? '');

        if ($action === 'add_product') {
            $name        = sanitize($_POST['name'] ?? '');
            $categoryId  = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
            $sku         = sanitize($_POST['sku'] ?? '');
            $price       = (float)($_POST['price'] ?? 0);
            $comparePrice= !empty($_POST['compare_at_price']) ? (float)$_POST['compare_at_price'] : null;
            $costPrice   = !empty($_POST['cost_price']) ? (float)$_POST['cost_price'] : null;
            $stock       = max(0, (int)($_POST['stock_quantity'] ?? 0));
            $image       = sanitize($_POST['main_image'] ?? '');
            $shortDesc   = sanitize($_POST['short_description'] ?? '');
            $desc        = sanitize($_POST['description'] ?? '');
            $isFeatured  = isset($_POST['is_featured']) ? 1 : 0;
            $slug        = slugify($name) . '-' . rand(10, 99);

            try {
                $stmt = $pdo->prepare("
                    INSERT INTO `products` (
                        `category_id`, `name`, `slug`, `sku`, `short_description`, `description`,
                        `price`, `compare_at_price`, `cost_price`, `stock_quantity`, `is_featured`, `is_active`, `main_image`
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?)
                ");
                $stmt->execute([
                    $categoryId, $name, $slug, $sku, $shortDesc, $desc,
                    $price, $comparePrice, $costPrice, $stock, $isFeatured, $image
                ]);
                setFlash('success', "Product '{$name}' added successfully.");
            } catch (Exception $e) {
                setFlash('error', 'Error adding product: ' . $e->getMessage());
            }
        } elseif ($action === 'quick_stock') {
            $prodId = (int)($_POST['product_id'] ?? 0);
            $newStock = max(0, (int)($_POST['stock_quantity'] ?? 0));
            try {
                $stmt = $pdo->prepare("UPDATE `products` SET `stock_quantity` = ? WHERE `id` = ?");
                $stmt->execute([$newStock, $prodId]);
                setFlash('success', "Stock updated to {$newStock}.");
            } catch (Exception $e) {
                setFlash('error', 'Error updating stock: ' . $e->getMessage());
            }
        } elseif ($action === 'delete_product') {
            $prodId = (int)($_POST['product_id'] ?? 0);
            try {
                $stmt = $pdo->prepare("DELETE FROM `products` WHERE `id` = ?");
                $stmt->execute([$prodId]);
                setFlash('success', "Product deleted.");
            } catch (Exception $e) {
                setFlash('error', 'Error deleting product: ' . $e->getMessage());
            }
        }
    }
}

// Fetch products list
$products = [];
if ($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            ORDER BY p.id DESC
        ");
        $products = $stmt->fetchAll();
    } catch (Exception $e) {}
}
?>

<div class="admin-card">
    <div class="admin-card-header">
        <div>
            <h1 class="admin-card-title">Products &amp; Inventory</h1>
            <span style="font-size: 13px; color: #64748b;">Manage catalog, variants, prices in PKR, and inventory stock</span>
        </div>
        <button type="button" class="btn-admin" onclick="openModal('addProductModal')">
            + Add New Product
        </button>
    </div>

    <!-- Products Table -->
    <div style="overflow-x: auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Product Name &amp; SKU</th>
                    <th>Category</th>
                    <th>Price (PKR)</th>
                    <th>Compare Price</th>
                    <th>Stock Quantity</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products)): ?>
                    <tr>
                        <td colspan="8" style="text-align: center; color: #64748b; padding: 32px;">No products in database.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($products as $p): ?>
                        <tr>
                            <td>
                                <img src="<?= e($p['main_image'] ?: BASE_URL . '/assets/images/placeholder.jpg') ?>" alt="Thumb" style="width: 48px; height: 48px; object-fit: cover; border-radius: 6px; border: 1px solid #e2e8f0;">
                            </td>
                            <td>
                                <div style="font-weight: 700; color: #0f172a; max-width: 280px;"><?= e($p['name']) ?></div>
                                <div style="font-size: 11px; color: #64748b;">SKU: <?= e($p['sku']) ?></div>
                            </td>
                            <td><?= e($p['category_name'] ?? 'Uncategorized') ?></td>
                            <td style="font-weight: 800; color: #0f766e;"><?= formatPrice($p['price']) ?></td>
                            <td style="color: #94a3b8; font-size: 12px;">
                                <?= $p['compare_at_price'] ? formatPrice($p['compare_at_price']) : '&mdash;' ?>
                            </td>
                            <td>
                                <form method="POST" style="display: flex; align-items: center; gap: 6px;">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="quick_stock">
                                    <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                    <input type="number" name="stock_quantity" value="<?= $p['stock_quantity'] ?>" style="width: 60px; padding: 4px 6px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 13px; font-weight: 700; text-align: center;">
                                    <button type="submit" title="Update Stock" style="background: none; border: none; cursor: pointer; font-size: 13px;">💾</button>
                                </form>
                                <?php if ($p['stock_quantity'] <= $p['low_stock_threshold']): ?>
                                    <span style="font-size: 10px; color: #dc2626; font-weight: 700;">Low Stock!</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge-status <?= $p['is_active'] ? 'badge-delivered' : 'badge-cancelled' ?>">
                                    <?= $p['is_active'] ? 'Active' : 'Draft' ?>
                                </span>
                            </td>
                            <td>
                                <form method="POST" onsubmit="return confirm('Are you sure you want to delete this product?');" style="display: inline;">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="delete_product">
                                    <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                    <button type="submit" class="btn-admin-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Product Modal -->
<div id="addProductModal" class="modal-overlay">
    <div class="modal-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="margin: 0; font-size: 18px; color: #0f172a;">Add New Product</h3>
            <button type="button" onclick="closeModal('addProductModal')" style="background: none; border: none; font-size: 24px; cursor: pointer;">&times;</button>
        </div>

        <form method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="add_product">

            <div style="margin-bottom: 14px;">
                <label style="font-size: 12px; font-weight: 700; color: #334155; display: block; margin-bottom: 4px;">Product Title *</label>
                <input type="text" name="name" required placeholder="e.g. Luxury Embroidered Kurta" class="admin-input">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                <div>
                    <label style="font-size: 12px; font-weight: 700; color: #334155; display: block; margin-bottom: 4px;">Category *</label>
                    <select name="category_id" required class="admin-input">
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label style="font-size: 12px; font-weight: 700; color: #334155; display: block; margin-bottom: 4px;">SKU / Product Code *</label>
                    <input type="text" name="sku" required placeholder="e.g. KURTA-EMB-01" class="admin-input">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                <div>
                    <label style="font-size: 12px; font-weight: 700; color: #334155; display: block; margin-bottom: 4px;">Selling Price (PKR) *</label>
                    <input type="number" step="0.01" name="price" required placeholder="3500" class="admin-input">
                </div>
                <div>
                    <label style="font-size: 12px; font-weight: 700; color: #334155; display: block; margin-bottom: 4px;">Compare Price (PKR)</label>
                    <input type="number" step="0.01" name="compare_at_price" placeholder="4500" class="admin-input">
                </div>
                <div>
                    <label style="font-size: 12px; font-weight: 700; color: #334155; display: block; margin-bottom: 4px;">Cost Price (PKR)</label>
                    <input type="number" step="0.01" name="cost_price" placeholder="1800" class="admin-input">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                <div>
                    <label style="font-size: 12px; font-weight: 700; color: #334155; display: block; margin-bottom: 4px;">Initial Stock Quantity *</label>
                    <input type="number" name="stock_quantity" value="20" required class="admin-input">
                </div>
                <div>
                    <label style="font-size: 12px; font-weight: 700; color: #334155; display: block; margin-bottom: 4px;">Image URL</label>
                    <input type="url" name="main_image" placeholder="https://..." class="admin-input">
                </div>
            </div>

            <div style="margin-bottom: 14px;">
                <label style="font-size: 12px; font-weight: 700; color: #334155; display: block; margin-bottom: 4px;">Short Description</label>
                <input type="text" name="short_description" placeholder="Brief summary of fabric, specs, or style..." class="admin-input">
            </div>

            <div style="margin-bottom: 14px;">
                <label style="font-size: 12px; font-weight: 700; color: #334155; display: block; margin-bottom: 4px;">Full Description</label>
                <textarea name="description" rows="3" class="admin-input"></textarea>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 600; color: #334155;">
                    <input type="checkbox" name="is_featured" value="1" style="accent-color: #0f766e;">
                    Feature on Homepage Trending Grid
                </label>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="closeModal('addProductModal')" class="btn-admin-secondary">Cancel</button>
                <button type="submit" class="btn-admin">Save Product</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
