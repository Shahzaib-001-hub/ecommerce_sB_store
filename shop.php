<?php
/**
 * Shop / Catalog Page
 * BazarPK - Pakistani Custom eCommerce Platform
 */

$pageTitle = "Shop Catalog";
$pageDescription = "Browse all collections, fashion wear, tech gadgets, and authentic Pakistani lifestyle products.";

require_once __DIR__ . '/includes/header.php';

$pdo = Database::getConnection();

// Filters from Query Parameters
$catSlug = sanitize($_GET['cat'] ?? '');
$searchQuery = sanitize($_GET['q'] ?? '');
$sort = sanitize($_GET['sort'] ?? 'newest');
$minPrice = isset($_GET['min_price']) && is_numeric($_GET['min_price']) ? (float)$_GET['min_price'] : 0;
$maxPrice = isset($_GET['max_price']) && is_numeric($_GET['max_price']) ? (float)$_GET['max_price'] : 20000;
$onSale = isset($_GET['sale']) && $_GET['sale'] == 1;

$products = [];
$allCategories = [];
$selectedCatName = 'All Products';

if ($pdo) {
    try {
        // Fetch all categories for filter sidebar
        $cStmt = $pdo->query("SELECT * FROM categories ORDER BY display_order ASC");
        $allCategories = $cStmt->fetchAll();

        // Build dynamic SQL query
        $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.is_active = 1";
        $params = [];

        if (!empty($catSlug)) {
            $sql .= " AND c.slug = ?";
            $params[] = $catSlug;
            foreach ($allCategories as $c) {
                if ($c['slug'] === $catSlug) {
                    $selectedCatName = $c['name'];
                    break;
                }
            }
        }

        if (!empty($searchQuery)) {
            $sql .= " AND (p.name LIKE ? OR p.short_description LIKE ? OR p.sku LIKE ?)";
            $searchTerm = '%' . $searchQuery . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $selectedCatName = 'Search results for "' . $searchQuery . '"';
        }

        if ($minPrice > 0) {
            $sql .= " AND p.price >= ?";
            $params[] = $minPrice;
        }

        if ($maxPrice < 20000) {
            $sql .= " AND p.price <= ?";
            $params[] = $maxPrice;
        }

        if ($onSale) {
            $sql .= " AND p.compare_at_price > p.price";
            $selectedCatName = 'Flash Deals & Discounts';
        }

        // Sorting
        switch ($sort) {
            case 'price_asc':
                $sql .= " ORDER BY p.price ASC";
                break;
            case 'price_desc':
                $sql .= " ORDER BY p.price DESC";
                break;
            case 'popular':
                $sql .= " ORDER BY p.reviews_count DESC, p.rating_avg DESC";
                break;
            case 'newest':
            default:
                $sql .= " ORDER BY p.id DESC";
                break;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $products = $stmt->fetchAll();
    } catch (Exception $e) {
        $products = [];
    }
}
?>

<div class="container" style="padding: 24px 16px 60px 16px;">
    <!-- Breadcrumb -->
    <div style="font-size: 13px; color: #64748b; margin-bottom: 20px;">
        <a href="<?= BASE_URL ?>/index.php" style="color: #0f766e;">Home</a> &rsaquo; 
        <span style="color: #0f172a; font-weight: 600;"><?= e($selectedCatName) ?></span>
    </div>

    <div style="display: grid; grid-template-columns: 260px 1fr; gap: 32px;" class="shop-layout">
        <!-- Filter Sidebar -->
        <aside style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; height: fit-content;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px;">
                <h3 style="font-size: 16px; font-weight: 700; margin: 0; color: #0f172a;">Filters</h3>
                <a href="<?= BASE_URL ?>/shop.php" style="font-size: 12px; color: #ef4444; font-weight: 600;">Reset All</a>
            </div>

            <!-- Categories Filter -->
            <div style="margin-bottom: 24px;">
                <h4 style="font-size: 14px; font-weight: 700; color: #1e293b; margin-bottom: 10px;">Categories</h4>
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <a href="<?= BASE_URL ?>/shop.php" style="font-size: 13px; font-weight: <?= empty($catSlug) ? '700; color: #0f766e;' : '500; color: #475569;' ?>">
                        &bull; All Categories
                    </a>
                    <?php foreach ($allCategories as $c): ?>
                        <a href="<?= BASE_URL ?>/shop.php?cat=<?= urlencode($c['slug']) ?>" style="font-size: 13px; font-weight: <?= ($catSlug === $c['slug']) ? '700; color: #0f766e;' : '500; color: #475569;' ?>">
                            &bull; <?= e($c['name']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Price Range Filter -->
            <form method="GET" action="<?= BASE_URL ?>/shop.php">
                <?php if (!empty($catSlug)): ?>
                    <input type="hidden" name="cat" value="<?= e($catSlug) ?>">
                <?php endif; ?>
                <?php if (!empty($searchQuery)): ?>
                    <input type="hidden" name="q" value="<?= e($searchQuery) ?>">
                <?php endif; ?>

                <div style="margin-bottom: 24px;">
                    <h4 style="font-size: 14px; font-weight: 700; color: #1e293b; margin-bottom: 10px;">Price Range (PKR)</h4>
                    <div style="display: flex; gap: 8px; align-items: center; margin-bottom: 10px;">
                        <input type="number" name="min_price" value="<?= (int)$minPrice ?>" placeholder="Min" style="width: 100%; padding: 6px 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                        <span style="color: #94a3b8;">-</span>
                        <input type="number" name="max_price" value="<?= (int)$maxPrice ?>" placeholder="Max" style="width: 100%; padding: 6px 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                    </div>
                    <button type="submit" class="btn-primary" style="width: 100%; padding: 8px; font-size: 13px; justify-content: center; border-radius: 6px;">Apply Price Filter</button>
                </div>
            </form>

            <!-- Delivery Trust Box -->
            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 14px; font-size: 12px; color: #166534;">
                <strong>🚚 Fast Delivery in Pakistan</strong><br>
                Karachi: 1-2 Days<br>
                Lahore &amp; Islamabad: 1-2 Days<br>
                Other Cities: 2-3 Days<br>
                <strong>Cash on Delivery Available</strong>
            </div>
        </aside>

        <!-- Product Listing Grid -->
        <main>
            <!-- Top Controls Row -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px; background: #fff; padding: 14px 20px; border-radius: 10px; border: 1px solid #e2e8f0;">
                <div>
                    <h1 style="font-size: 20px; font-weight: 800; color: #0f172a; margin: 0;"><?= e($selectedCatName) ?></h1>
                    <span style="font-size: 13px; color: #64748b;">Showing <?= count($products) ?> items</span>
                </div>

                <!-- Sort Dropdown -->
                <div style="display: flex; align-items: center; gap: 10px;">
                    <label for="sortSelect" style="font-size: 13px; color: #64748b; font-weight: 600;">Sort by:</label>
                    <select id="sortSelect" onchange="location = this.value;" style="padding: 6px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; background: #fff;">
                        <?php
                        $baseSortUrl = BASE_URL . '/shop.php?' . http_build_query(array_merge($_GET, ['sort' => '']));
                        $baseSortUrl = preg_replace('/&?sort=$/', '', $baseSortUrl);
                        $sep = str_contains($baseSortUrl, '?') ? '&' : '?';
                        ?>
                        <option value="<?= $baseSortUrl . $sep ?>sort=newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest First</option>
                        <option value="<?= $baseSortUrl . $sep ?>sort=popular" <?= $sort === 'popular' ? 'selected' : '' ?>>Most Popular</option>
                        <option value="<?= $baseSortUrl . $sep ?>sort=price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
                        <option value="<?= $baseSortUrl . $sep ?>sort=price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
                    </select>
                </div>
            </div>

            <!-- Products Grid -->
            <?php if (empty($products)): ?>
                <div style="text-align: center; background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 60px 20px;">
                    <div style="font-size: 48px; margin-bottom: 12px;">🔍</div>
                    <h3 style="font-size: 18px; color: #0f172a; margin-bottom: 8px;">No products match your criteria</h3>
                    <p style="font-size: 14px; color: #64748b; margin-bottom: 20px;">Try clearing filters or searching for something else like "Kurta", "Earbuds", or "Waistcoat".</p>
                    <a href="<?= BASE_URL ?>/shop.php" class="btn-primary" style="display: inline-block;">Clear All Filters</a>
                </div>
            <?php else: ?>
                <div class="products-grid" style="grid-template-columns: repeat(3, 1fr); margin-bottom: 0;">
                    <?php foreach ($products as $product): 
                        $hasDiscount = !empty($product['compare_at_price']) && $product['compare_at_price'] > $product['price'];
                        $discountPercent = $hasDiscount ? round((($product['compare_at_price'] - $product['price']) / $product['compare_at_price']) * 100) : 0;
                    ?>
                        <div class="product-card">
                            <div class="product-thumb-wrapper">
                                <a href="<?= BASE_URL ?>/product.php?id=<?= $product['id'] ?>">
                                    <img src="<?= e($product['main_image']) ?>" alt="<?= e($product['name']) ?>" class="product-thumb" loading="lazy">
                                </a>

                                <div class="product-badges">
                                    <?php if ($hasDiscount): ?>
                                        <span class="badge-discount">-<?= $discountPercent ?>% OFF</span>
                                    <?php endif; ?>
                                </div>

                                <button type="button" class="btn-wishlist" data-action="toggle-wishlist" data-id="<?= $product['id'] ?>" title="Add to Wishlist" aria-label="Add to Wishlist">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                                </button>
                            </div>

                            <div class="product-info">
                                <span class="product-category-name"><?= e($product['category_name'] ?? 'General') ?></span>
                                <a href="<?= BASE_URL ?>/product.php?id=<?= $product['id'] ?>" class="product-title">
                                    <?= e($product['name']) ?>
                                </a>

                                <div class="product-rating">
                                    <span>★</span>
                                    <span style="font-weight: 700; color: #0f172a;"><?= number_format($product['rating_avg'] ?? 5.0, 1) ?></span>
                                    <span class="rating-count">(<?= $product['reviews_count'] ?? 10 ?>)</span>
                                </div>

                                <div class="product-bottom-row">
                                    <div class="product-pricing">
                                        <span class="price-current"><?= formatPrice($product['price']) ?></span>
                                        <?php if ($hasDiscount): ?>
                                            <span class="price-compare"><?= formatPrice($product['compare_at_price']) ?></span>
                                        <?php endif; ?>
                                    </div>

                                    <button type="button" class="btn-add-cart" 
                                        data-action="add-to-cart" 
                                        data-id="<?= $product['id'] ?>" 
                                        data-name="<?= e($product['name']) ?>" 
                                        data-price="<?= $product['price'] ?>" 
                                        data-image="<?= e($product['main_image']) ?>">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                        <span>Add</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<style>
@media (max-width: 860px) {
    .shop-layout { grid-template-columns: 1fr !important; }
}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
