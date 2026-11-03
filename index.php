<?php
/**
 * Homepage - BazarPK
 * Pakistani Custom eCommerce Storefront
 */

$pageTitle = "Online Shopping in Pakistan - Cash on Delivery";
$pageDescription = "Shop latest fashion, accessories, tech gadgets & perfumes in Pakistan with Cash on Delivery, JazzCash & Easypaisa.";

require_once __DIR__ . '/includes/header.php';

// Fetch products & categories from Database with safe fallback
$featuredProducts = [];
$featuredCategories = [];
$dbInstalled = Database::isConnected();

if ($dbInstalled) {
    try {
        // Fetch categories
        $catStmt = $pdo->query("SELECT * FROM categories WHERE is_featured = 1 ORDER BY display_order ASC LIMIT 6");
        $featuredCategories = $catStmt->fetchAll();

        // Fetch featured products
        $prodStmt = $pdo->query("
            SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.is_active = 1 
            ORDER BY p.is_featured DESC, p.id DESC 
            LIMIT 8
        ");
        $featuredProducts = $prodStmt->fetchAll();
    } catch (Exception $e) {
        $dbInstalled = false;
    }
}

// Fallback dummy products if database hasn't been migrated yet
if (!$dbInstalled || empty($featuredProducts)) {
    $featuredProducts = [
        [
            'id' => 1,
            'name' => 'Royal Navy Egyptian Cotton Kurta',
            'slug' => 'royal-navy-egyptian-cotton-kurta',
            'category_name' => 'Men\'s Fashion',
            'price' => 3450,
            'compare_at_price' => 4500,
            'rating_avg' => 4.9,
            'reviews_count' => 48,
            'main_image' => 'https://images.unsplash.com/photo-1597983073493-88cd35cf93b0?w=800&auto=format&fit=crop&q=80'
        ],
        [
            'id' => 2,
            'name' => 'Classic Charcoal Waistcoat',
            'slug' => 'classic-charcoal-waistcoat',
            'category_name' => 'Men\'s Fashion',
            'price' => 4200,
            'compare_at_price' => 5600,
            'rating_avg' => 4.85,
            'reviews_count' => 32,
            'main_image' => 'https://images.unsplash.com/photo-1507679799987-c73779587ccf?w=800&auto=format&fit=crop&q=80'
        ],
        [
            'id' => 3,
            'name' => 'ProBass ANC Wireless Earbuds (v5.3)',
            'slug' => 'probass-anc-wireless-earbuds-v53',
            'category_name' => 'Tech & Gadgets',
            'price' => 4999,
            'compare_at_price' => 6999,
            'rating_avg' => 4.92,
            'reviews_count' => 114,
            'main_image' => 'https://images.unsplash.com/photo-1590658268037-6bf12165a8df?w=800&auto=format&fit=crop&q=80'
        ],
        [
            'id' => 4,
            'name' => 'UltraSlim 20,000mAh 65W Fast Power Bank',
            'slug' => 'ultraslim-20000mah-65w-fast-power-bank',
            'category_name' => 'Tech & Gadgets',
            'price' => 5800,
            'compare_at_price' => 7500,
            'rating_avg' => 4.88,
            'reviews_count' => 86,
            'main_image' => 'https://images.unsplash.com/photo-1609592426860-26210f0f4a34?w=800&auto=format&fit=crop&q=80'
        ],
        [
            'id' => 5,
            'name' => 'Authentic Kaptaan Chappal (Double Sole)',
            'slug' => 'authentic-kaptaan-chappal-double-sole',
            'category_name' => 'Traditional Footwear',
            'price' => 3950,
            'compare_at_price' => 5200,
            'rating_avg' => 4.95,
            'reviews_count' => 67,
            'main_image' => 'https://images.unsplash.com/photo-1560769629-975ec94e6a86?w=800&auto=format&fit=crop&q=80'
        ],
        [
            'id' => 6,
            'name' => 'Imperial Oudh & Amber Luxury Attar (12ml)',
            'slug' => 'imperial-oudh-amber-luxury-attar-12ml',
            'category_name' => 'Fragrances & Attar',
            'price' => 2800,
            'compare_at_price' => 3800,
            'rating_avg' => 4.9,
            'reviews_count' => 53,
            'main_image' => 'https://images.unsplash.com/photo-1592945403244-b3fbafd7f539?w=800&auto=format&fit=crop&q=80'
        ],
        [
            'id' => 7,
            'name' => 'Pastel Mint Embroidered 3-Piece Lawn Suit',
            'slug' => 'pastel-mint-embroidered-3-piece-lawn-suit',
            'category_name' => 'Women\'s Collection',
            'price' => 6499,
            'compare_at_price' => 8500,
            'rating_avg' => 4.93,
            'reviews_count' => 42,
            'main_image' => 'https://images.unsplash.com/photo-1610030469983-98e550d6193c?w=800&auto=format&fit=crop&q=80'
        ],
        [
            'id' => 8,
            'name' => 'SmartWatch Ultra (AMOLED Always-On Display)',
            'slug' => 'smartwatch-ultra-amoled-display',
            'category_name' => 'Tech & Gadgets',
            'price' => 6200,
            'compare_at_price' => 8900,
            'rating_avg' => 4.8,
            'reviews_count' => 29,
            'main_image' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=800&auto=format&fit=crop&q=80'
        ]
    ];
}
?>

<!-- Setup helper alert if DB not initialized yet -->
<?php if (!$dbInstalled): ?>
<div class="container" style="margin-top: 16px;">
    <div style="background: #eff6ff; border: 1.5px solid #60a5fa; border-radius: 8px; padding: 14px 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
        <div>
            <strong style="color: #1e40af;">🚀 Ready to connect Hostinger Database?</strong>
            <p style="margin: 0; font-size: 13px; color: #1e3a8a;">Configure your database in <code>config/config.php</code> or click the setup installer to initialize tables and admin account.</p>
        </div>
        <a href="<?= BASE_URL ?>/install.php" style="background: #2563eb; color: #fff; padding: 8px 16px; border-radius: 6px; font-size: 13px; font-weight: 600; text-decoration: none;">Launch Database Setup &rarr;</a>
    </div>
</div>
<?php endif; ?>

<!-- Value Proposition Features Bar -->
<section class="features-bar">
    <div class="container">
        <div class="features-grid">
            <div class="feature-box">
                <div class="feature-icon-wrapper">💵</div>
                <div>
                    <div class="feature-title">Cash on Delivery</div>
                    <div class="feature-subtitle">Pay securely when parcel arrives at your door</div>
                </div>
            </div>
            <div class="feature-box">
                <div class="feature-icon-wrapper">🚚</div>
                <div>
                    <div class="feature-title">Express Shipping</div>
                    <div class="feature-subtitle">Fast delivery via Trax &amp; TCS across Pakistan</div>
                </div>
            </div>
            <div class="feature-box">
                <div class="feature-icon-wrapper">🔄</div>
                <div>
                    <div class="feature-title">7-Day Easy Returns</div>
                    <div class="feature-subtitle">Hassle-free size &amp; product exchange policy</div>
                </div>
            </div>
            <div class="feature-box">
                <div class="feature-icon-wrapper">💬</div>
                <div>
                    <div class="feature-title">WhatsApp Support</div>
                    <div class="feature-subtitle">Instant assistance on +92 300 1234567</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="hero-grid">
            <!-- Main Hero Card -->
            <div class="hero-main-card">
                <span class="hero-pill">✨ Pakistan's Top Fashion &amp; Tech Trends</span>
                <h1 class="hero-title">Elevate Your Everyday Style &amp; Tech</h1>
                <p class="hero-desc">Discover premium stitched Egyptian cotton kurtas, handcrafted Peshawari chappals, and cutting-edge audio gadgets with verified nationwide express delivery.</p>
                <div class="hero-actions">
                    <a href="<?= BASE_URL ?>/shop.php" class="btn-primary">
                        <span>Shop All Collection</span>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                    </a>
                    <a href="<?= BASE_URL ?>/shop.php?cat=mens-fashion" class="btn-secondary">Men's Kurta &amp; Waistcoats</a>
                </div>
            </div>

            <!-- Secondary Hero Feature Box -->
            <div class="hero-side-card">
                <div>
                    <span style="background: #fee2e2; color: #dc2626; font-size: 11px; font-weight: 700; padding: 4px 8px; border-radius: 4px; text-transform: uppercase;">🔥 Flash Deal of the Week</span>
                    <h3 style="font-size: 20px; font-weight: 800; color: #0f172a; margin: 12px 0 6px 0;">ProBass ANC Wireless Earbuds</h3>
                    <p style="font-size: 13px; color: #64748b; margin-bottom: 14px;">Active Noise Cancellation &bull; 36hr Battery &bull; Low-latency gaming</p>
                    <div style="font-size: 22px; font-weight: 800; color: #0f766e; margin-bottom: 12px;">
                        Rs. 4,999 <span style="font-size: 14px; color: #94a3b8; text-decoration: line-through; font-weight: 500;">Rs. 6,999</span>
                    </div>
                </div>
                <div>
                    <button type="button" class="btn-add-cart" style="width: 100%; justify-content: center; padding: 12px;" data-action="add-to-cart" data-id="3" data-name="ProBass ANC Wireless Earbuds (v5.3)" data-price="4999" data-image="https://images.unsplash.com/photo-1590658268037-6bf12165a8df?w=800&auto=format&fit=crop&q=80">
                        ⚡ Quick Buy with COD
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Categories Section -->
<section style="padding: 24px 0;">
    <div class="container">
        <div class="section-header">
            <div>
                <h2 class="section-title">Shop by Category</h2>
                <p class="section-subtitle">Curated collections crafted for every occasion</p>
            </div>
            <a href="<?= BASE_URL ?>/shop.php" class="view-all-link">View All Categories &rarr;</a>
        </div>

        <div class="categories-grid">
            <a href="<?= BASE_URL ?>/shop.php?cat=mens-fashion" class="category-card">
                <img src="https://images.unsplash.com/photo-1617137984095-74e4e5e3613f?w=600&auto=format&fit=crop&q=80" alt="Men's Fashion" class="category-img">
                <div class="category-name">Men's Fashion</div>
            </a>
            <a href="<?= BASE_URL ?>/shop.php?cat=womens-collection" class="category-card">
                <img src="https://images.unsplash.com/photo-1539109136881-3be0616acf4b?w=600&auto=format&fit=crop&q=80" alt="Women's Collection" class="category-img">
                <div class="category-name">Women's Lawn</div>
            </a>
            <a href="<?= BASE_URL ?>/shop.php?cat=tech-gadgets" class="category-card">
                <img src="https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=600&auto=format&fit=crop&q=80" alt="Tech & Gadgets" class="category-img">
                <div class="category-name">Tech &amp; Audio</div>
            </a>
            <a href="<?= BASE_URL ?>/shop.php?cat=traditional-footwear" class="category-card">
                <img src="https://images.unsplash.com/photo-1549298916-b41d501d3772?w=600&auto=format&fit=crop&q=80" alt="Traditional Footwear" class="category-img">
                <div class="category-name">Peshawari Chappal</div>
            </a>
            <a href="<?= BASE_URL ?>/shop.php?cat=fragrances-attar" class="category-card">
                <img src="https://images.unsplash.com/photo-1547887537-6158d64c35b3?w=600&auto=format&fit=crop&q=80" alt="Fragrances & Attar" class="category-img">
                <div class="category-name">Pure Attar &amp; Oudh</div>
            </a>
            <a href="<?= BASE_URL ?>/shop.php" class="category-card">
                <img src="https://images.unsplash.com/photo-1513694203232-719a280e022f?w=600&auto=format&fit=crop&q=80" alt="Home & Living" class="category-img">
                <div class="category-name">Home &amp; Living</div>
            </a>
        </div>
    </div>
</section>

<!-- Featured Products Section -->
<section style="padding: 24px 0 48px 0;">
    <div class="container">
        <div class="section-header">
            <div>
                <h2 class="section-title">Trending Products in Pakistan</h2>
                <p class="section-subtitle">Top customer favorites with high ratings and verified reviews</p>
            </div>
            <a href="<?= BASE_URL ?>/shop.php" class="view-all-link">Browse Full Store &rarr;</a>
        </div>

        <div class="products-grid">
            <?php foreach ($featuredProducts as $product): 
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
                        <span class="product-category-name"><?= e($product['category_name'] ?? 'Lifestyle') ?></span>
                        <a href="<?= BASE_URL ?>/product.php?id=<?= $product['id'] ?>" class="product-title">
                            <?= e($product['name']) ?>
                        </a>

                        <div class="product-rating">
                            <span>★</span>
                            <span style="font-weight: 700; color: #0f172a;"><?= number_format($product['rating_avg'] ?? 5.0, 1) ?></span>
                            <span class="rating-count">(<?= $product['reviews_count'] ?? 10 ?> reviews)</span>
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
    </div>
</section>

<!-- Promotional Banner (Pakistan Delivery & Trust) -->
<section style="background: #094742; color: #fff; padding: 48px 0; margin-bottom: 48px;">
    <div class="container" style="text-align: center; max-width: 780px;">
        <span style="background: #14b8a6; color: #042f2e; font-weight: 800; font-size: 12px; padding: 4px 12px; border-radius: 9999px; text-transform: uppercase;">Pakistan Verified Store</span>
        <h2 style="font-size: 32px; font-weight: 800; margin: 16px 0 12px 0;">Shop Confidently with 100% Cash on Delivery</h2>
        <p style="font-size: 15px; opacity: 0.9; margin-bottom: 24px; line-height: 1.6;">
            We know trust matters. Open your parcel and inspect your items with peace of mind. Fast dispatch across Karachi, Lahore, Islamabad, Rawalpindi, Peshawar, Quetta, and all 400+ cities and tehsils nationwide.
        </p>
        <div style="display: flex; justify-content: center; gap: 14px; flex-wrap: wrap;">
            <a href="<?= BASE_URL ?>/shop.php" class="btn-primary">Browse All Products</a>
            <a href="https://wa.me/<?= STORE_WHATSAPP ?>" target="_blank" class="btn-secondary" style="border-color: #25d366; color: #25d366;">Order via WhatsApp</a>
        </div>
    </div>
</section>

<!-- Customer Reviews Section -->
<section style="padding: 20px 0 60px 0;">
    <div class="container">
        <div class="section-header" style="text-align: center; display: block; margin-bottom: 36px;">
            <h2 class="section-title">What Pakistani Shoppers Say</h2>
            <p class="section-subtitle">Real experiences from customers across Pakistan</p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;">
            <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; box-shadow: var(--shadow-sm);">
                <div style="color: #f59e0b; margin-bottom: 10px;">★★★★★</div>
                <p style="font-size: 14px; color: #334155; line-height: 1.6; margin-bottom: 16px;">
                    "Received my Egyptian cotton kurta in Karachi within 24 hours of placing the order. Stitching and finishing is top notch. Truly impressed by the COD service!"
                </p>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 38px; height: 38px; border-radius: 50%; background: #0f766e; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700;">U</div>
                    <div>
                        <div style="font-size: 14px; font-weight: 700; color: #0f172a;">Usman Tariq</div>
                        <div style="font-size: 12px; color: #64748b;">DHA Phase 6, Karachi &bull; Verified Buyer</div>
                    </div>
                </div>
            </div>

            <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; box-shadow: var(--shadow-sm);">
                <div style="color: #f59e0b; margin-bottom: 10px;">★★★★★</div>
                <p style="font-size: 14px; color: #334155; line-height: 1.6; margin-bottom: 16px;">
                    "The ProBass wireless earbuds have incredible sound clarity and noise cancelling. Great battery backup on my daily commute in Lahore. 10/10 purchase."
                </p>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 38px; height: 38px; border-radius: 50%; background: #0f766e; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700;">D</div>
                    <div>
                        <div style="font-size: 14px; font-weight: 700; color: #0f172a;">Danial Sheikh</div>
                        <div style="font-size: 12px; color: #64748b;">Gulberg III, Lahore &bull; Verified Buyer</div>
                    </div>
                </div>
            </div>

            <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; box-shadow: var(--shadow-sm);">
                <div style="color: #f59e0b; margin-bottom: 10px;">★★★★★</div>
                <p style="font-size: 14px; color: #334155; line-height: 1.6; margin-bottom: 16px;">
                    "Ordered an authentic Kaptaan Chappal for my father. The sole is original truck tire rubber and leather is genuinely soft. Arrived safely in Islamabad."
                </p>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 38px; height: 38px; border-radius: 50%; background: #0f766e; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700;">F</div>
                    <div>
                        <div style="font-size: 14px; font-weight: 700; color: #0f172a;">Fatima Zahra</div>
                        <div style="font-size: 12px; color: #64748b;">F-8, Islamabad &bull; Verified Buyer</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
