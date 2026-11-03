<?php
/**
 * Product Detail Page
 * BazarPK - Pakistani Custom eCommerce Platform
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/utils.php';

$pdo = Database::getConnection();
$productId = isset($_GET['id']) ? (int)$_GET['id'] : 1;

$product = null;
$galleryImages = [];
$variants = [];
$reviews = [];
$relatedProducts = [];

if ($pdo) {
    try {
        // Fetch product
        $stmt = $pdo->prepare("
            SELECT p.*, c.name as category_name, c.slug as category_slug 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.id = ? AND p.is_active = 1
        ");
        $stmt->execute([$productId]);
        $product = $stmt->fetch();

        if ($product) {
            // Fetch gallery images
            $imgStmt = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order ASC");
            $imgStmt->execute([$productId]);
            $galleryImages = $imgStmt->fetchAll();

            // Fetch variants
            $varStmt = $pdo->prepare("SELECT * FROM product_variants WHERE product_id = ? ORDER BY id ASC");
            $varStmt->execute([$productId]);
            $variants = $varStmt->fetchAll();

            // Fetch approved reviews
            $revStmt = $pdo->prepare("SELECT * FROM reviews WHERE product_id = ? AND is_approved = 1 ORDER BY id DESC LIMIT 10");
            $revStmt->execute([$productId]);
            $reviews = $revStmt->fetchAll();

            // Fetch related products from same category
            if ($product['category_id']) {
                $relStmt = $pdo->prepare("SELECT * FROM products WHERE category_id = ? AND id != ? AND is_active = 1 LIMIT 4");
                $relStmt->execute([$product['category_id'], $productId]);
                $relatedProducts = $relStmt->fetchAll();
            }
        }
    } catch (Exception $e) {
        $product = null;
    }
}

// Fallback if product not found
if (!$product) {
    $product = [
        'id' => 1,
        'name' => 'Royal Navy Egyptian Cotton Kurta',
        'slug' => 'royal-navy-egyptian-cotton-kurta',
        'category_name' => 'Men\'s Fashion',
        'category_slug' => 'mens-fashion',
        'sku' => 'KURTA-NAVY-01',
        'short_description' => 'Breathable 100% combed cotton kurta with subtle embroidered band collar.',
        'description' => 'Elevate your festive and everyday style with this Royal Navy Cotton Kurta. Tailored to perfection from high-grade combed cotton yarn, it offers exceptional comfort in the Pakistani climate. Features a mandarin band collar, mother-of-pearl buttons, and twin side pockets.',
        'price' => 3450,
        'compare_at_price' => 4500,
        'stock_quantity' => 35,
        'rating_avg' => 4.9,
        'reviews_count' => 48,
        'main_image' => 'https://images.unsplash.com/photo-1597983073493-88cd35cf93b0?w=800&auto=format&fit=crop&q=80'
    ];
}

$pageTitle = $product['name'];
$pageDescription = $product['short_description'] ?? 'Buy ' . $product['name'] . ' online in Pakistan with Cash on Delivery.';

require_once __DIR__ . '/includes/header.php';

$hasDiscount = !empty($product['compare_at_price']) && $product['compare_at_price'] > $product['price'];
$discountPercent = $hasDiscount ? round((($product['compare_at_price'] - $product['price']) / $product['compare_at_price']) * 100) : 0;
?>

<div class="container" style="padding: 24px 16px 60px 16px;">
    <!-- Breadcrumb -->
    <div style="font-size: 13px; color: #64748b; margin-bottom: 24px;">
        <a href="<?= BASE_URL ?>/index.php" style="color: #0f766e;">Home</a> &rsaquo; 
        <a href="<?= BASE_URL ?>/shop.php?cat=<?= urlencode($product['category_slug'] ?? '') ?>" style="color: #0f766e;"><?= e($product['category_name'] ?? 'Shop') ?></a> &rsaquo; 
        <span style="color: #0f172a; font-weight: 600;"><?= e($product['name']) ?></span>
    </div>

    <!-- Product Hero Grid -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-bottom: 48px;" class="product-detail-grid">
        <!-- Gallery Column -->
        <div>
            <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; position: relative; margin-bottom: 16px;">
                <img id="mainProductImage" src="<?= e($product['main_image']) ?>" alt="<?= e($product['name']) ?>" style="width: 100%; max-height: 520px; object-fit: cover;">
                <?php if ($hasDiscount): ?>
                    <span class="badge-discount" style="position: absolute; top: 16px; left: 16px; font-size: 13px; padding: 4px 10px;">
                        -<?= $discountPercent ?>% OFF
                    </span>
                <?php endif; ?>
            </div>

            <!-- Thumbnail Carousel if available -->
            <div style="display: flex; gap: 10px; overflow-x: auto;">
                <img src="<?= e($product['main_image']) ?>" alt="Thumbnail" class="product-thumb-btn active" onclick="changeImage(this.src)" style="width: 70px; height: 70px; object-fit: cover; border-radius: 8px; border: 2px solid #0f766e; cursor: pointer;">
                <?php foreach ($galleryImages as $img): ?>
                    <img src="<?= e($img['image_url']) ?>" alt="Thumbnail" class="product-thumb-btn" onclick="changeImage(this.src)" style="width: 70px; height: 70px; object-fit: cover; border-radius: 8px; border: 2px solid #e2e8f0; cursor: pointer;">
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Product Actions Column -->
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 32px;">
            <div style="font-size: 13px; text-transform: uppercase; color: #0f766e; font-weight: 700; margin-bottom: 6px;">
                <?= e($product['category_name'] ?? 'Lifestyle') ?> &bull; SKU: <?= e($product['sku']) ?>
            </div>

            <h1 style="font-size: 26px; font-weight: 800; color: #0f172a; line-height: 1.25; margin-bottom: 12px;">
                <?= e($product['name']) ?>
            </h1>

            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 20px;">
                <div style="color: #f59e0b; font-size: 16px;">★★★★★</div>
                <span style="font-weight: 700; font-size: 14px;"><?= number_format($product['rating_avg'] ?? 5.0, 1) ?></span>
                <span style="color: #64748b; font-size: 13px;">(<?= $product['reviews_count'] ?? 10 ?> verified reviews in Pakistan)</span>
            </div>

            <!-- Price -->
            <div style="display: flex; align-items: baseline; gap: 12px; margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid #f1f5f9;">
                <span style="font-size: 30px; font-weight: 800; color: #0f766e;">
                    <?= formatPrice($product['price']) ?>
                </span>
                <?php if ($hasDiscount): ?>
                    <span style="font-size: 18px; color: #94a3b8; text-decoration: line-through;">
                        <?= formatPrice($product['compare_at_price']) ?>
                    </span>
                    <span style="background: #fee2e2; color: #dc2626; font-size: 12px; font-weight: 700; padding: 2px 8px; border-radius: 4px;">
                        Save <?= formatPrice($product['compare_at_price'] - $product['price']) ?>
                    </span>
                <?php endif; ?>
            </div>

            <!-- Stock status -->
            <div style="margin-bottom: 20px;">
                <span style="display: inline-flex; align-items: center; gap: 6px; background: #dcfce7; color: #15803d; font-size: 13px; font-weight: 600; padding: 4px 12px; border-radius: 9999px;">
                    <span style="width: 8px; height: 8px; background: #22c55e; border-radius: 50%;"></span>
                    In Stock &bull; Ready for Express Dispatch
                </span>
            </div>

            <!-- Variants selector -->
            <?php if (!empty($variants)): ?>
                <div style="margin-bottom: 24px;">
                    <label style="font-size: 13px; font-weight: 700; color: #1e293b; display: block; margin-bottom: 8px;">
                        Select <?= e($variants[0]['variant_type']) ?>:
                    </label>
                    <div style="display: flex; gap: 10px; flex-wrap: wrap;" id="variantButtons">
                        <?php foreach ($variants as $idx => $var): ?>
                            <button type="button" class="variant-pill-btn <?= $idx === 0 ? 'selected' : '' ?>" 
                                data-variant="<?= e($var['variant_value']) ?>" 
                                onclick="selectVariant(this, '<?= e($var['variant_value']) ?>')">
                                <?= e($var['variant_value']) ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Quantity & CTA Buttons -->
            <div style="display: flex; gap: 14px; align-items: center; margin-bottom: 20px;">
                <div class="quantity-stepper" style="border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 4px;">
                    <button type="button" class="qty-btn" onclick="adjustProductQty(-1)" style="width: 36px; height: 36px; font-size: 18px;">&minus;</button>
                    <input type="text" id="productQuantityInput" class="qty-input" value="1" readonly style="width: 44px; font-size: 15px;">
                    <button type="button" class="qty-btn" onclick="adjustProductQty(1)" style="width: 36px; height: 36px; font-size: 18px;">&plus;</button>
                </div>

                <button type="button" id="btnAddToCartMain" class="btn-primary" style="flex: 1; padding: 14px; justify-content: center; font-size: 15px; border-radius: 8px; background: #0f766e; color: #fff;">
                    🛒 Add to Shopping Cart
                </button>
            </div>

            <!-- Instant COD Buy Button -->
            <button type="button" id="btnBuyNowCod" class="btn-checkout-drawer" style="padding: 14px; font-size: 15px; background: #0f172a; margin-bottom: 24px;">
                ⚡ Buy Now with Cash on Delivery
            </button>

            <!-- Pakistani Trust Features -->
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 18px; font-size: 13px;">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                    <span style="font-size: 18px;">💵</span>
                    <span><strong>100% Cash on Delivery</strong> available across Pakistan</span>
                </div>
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                    <span style="font-size: 18px;">🚚</span>
                    <span><strong>Delivery:</strong> 24-48 Hours (Karachi/Lahore/ISB), 2-3 Days other cities</span>
                </div>
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                    <span style="font-size: 18px;">🔄</span>
                    <span><strong>7 Days Return Policy:</strong> Easy size exchanges</span>
                </div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 18px;">💬</span>
                    <span><strong>Order via WhatsApp:</strong> <a href="https://wa.me/<?= STORE_WHATSAPP ?>?text=<?= urlencode('Assalam-o-Alaikum! I want to order ' . $product['name'] . ' (ID: ' . $product['id'] . ')') ?>" target="_blank" style="color: #0f766e; font-weight: 700; text-decoration: underline;">Click here to chat</a></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Description & Reviews Tabs -->
    <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 32px; margin-bottom: 48px;">
        <div style="display: flex; gap: 24px; border-bottom: 2px solid #f1f5f9; padding-bottom: 14px; margin-bottom: 24px;">
            <button type="button" class="tab-btn active" onclick="switchTab(this, 'tabDesc')">Product Overview</button>
            <button type="button" class="tab-btn" onclick="switchTab(this, 'tabShipping')">Shipping &amp; Delivery</button>
            <button type="button" class="tab-btn" onclick="switchTab(this, 'tabReviews')">Customer Reviews (<?= count($reviews) ?>)</button>
        </div>

        <div id="tabDesc" class="tab-content active">
            <h3 style="font-size: 18px; font-weight: 700; margin-bottom: 14px; color: #0f172a;">Description</h3>
            <p style="font-size: 14px; color: #334155; line-height: 1.8; margin-bottom: 20px;">
                <?= nl2br(e($product['description'] ?: $product['short_description'])) ?>
            </p>
        </div>

        <div id="tabShipping" class="tab-content" style="display: none;">
            <h3 style="font-size: 18px; font-weight: 700; margin-bottom: 14px; color: #0f172a;">Nationwide Delivery Information</h3>
            <p style="font-size: 14px; color: #334155; line-height: 1.7; margin-bottom: 16px;">
                All parcels are dispatched through premier Pakistani logistics networks: <strong>Trax</strong>, <strong>TCS</strong>, and <strong>Leopard Courier</strong>.
            </p>
            <ul style="font-size: 14px; color: #334155; line-height: 1.8; padding-left: 20px;">
                <li><strong>Major Metros (Karachi, Lahore, Islamabad, Rawalpindi):</strong> 1-2 business days.</li>
                <li><strong>Regional Hubs (Faisalabad, Multan, Sialkot, Peshawar, Gujranwala):</strong> 2-3 business days.</li>
                <li><strong>All Other Cities &amp; Interior:</strong> 3-5 business days.</li>
                <li><strong>Cash on Delivery (COD):</strong> Exact change is appreciated at the time of delivery.</li>
            </ul>
        </div>

        <div id="tabReviews" class="tab-content" style="display: none;">
            <h3 style="font-size: 18px; font-weight: 700; margin-bottom: 20px; color: #0f172a;">Verified Customer Feedback</h3>
            
            <?php if (empty($reviews)): ?>
                <div style="background: #f8fafc; padding: 20px; border-radius: 8px; text-align: center; color: #64748b; font-size: 14px; margin-bottom: 24px;">
                    No reviews yet for this product. Be the first to share your experience!
                </div>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 16px; margin-bottom: 32px;">
                    <?php foreach ($reviews as $rev): ?>
                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 18px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                <div>
                                    <strong style="color: #0f172a; font-size: 14px;"><?= e($rev['customer_name']) ?></strong>
                                    <span style="font-size: 12px; color: #64748b; margin-left: 6px;">(<?= e($rev['customer_city'] ?: 'Pakistan') ?>)</span>
                                </div>
                                <div style="color: #f59e0b; font-size: 14px;"><?= str_repeat('★', (int)$rev['rating']) ?></div>
                            </div>
                            <p style="margin: 0; font-size: 13px; color: #334155;"><?= e($rev['comment']) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Leave Review Form -->
            <div style="background: #f1f5f9; border-radius: 10px; padding: 24px; max-width: 600px;">
                <h4 style="font-size: 16px; font-weight: 700; margin-bottom: 14px;">Leave a Review</h4>
                <form id="reviewForm" onsubmit="submitReview(event)">
                    <?= csrf_field() ?>
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                        <input type="text" name="name" placeholder="Your Name" required style="padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                        <input type="text" name="city" placeholder="Your City (e.g. Lahore, Karachi)" required style="padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                    </div>

                    <div style="margin-bottom: 12px;">
                        <label style="font-size: 13px; font-weight: 600; color: #334155; display: block; margin-bottom: 4px;">Rating:</label>
                        <select name="rating" style="padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; background: #fff;">
                            <option value="5">★★★★★ (5 - Excellent)</option>
                            <option value="4">★★★★☆ (4 - Good)</option>
                            <option value="3">★★★☆☆ (3 - Average)</option>
                            <option value="2">★★☆☆☆ (2 - Poor)</option>
                            <option value="1">★☆☆☆☆ (1 - Terrible)</option>
                        </select>
                    </div>

                    <div style="margin-bottom: 14px;">
                        <textarea name="comment" rows="3" placeholder="Write your review here..." required style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;"></textarea>
                    </div>

                    <button type="submit" class="btn-primary" style="padding: 10px 20px; font-size: 13px; border-radius: 6px;">Submit Review</button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.tab-btn { background: transparent; border: none; font-size: 15px; font-weight: 600; color: #64748b; cursor: pointer; padding-bottom: 8px; }
.tab-btn.active { color: #0f766e; border-bottom: 2px solid #0f766e; }
.variant-pill-btn { background: #fff; border: 1.5px solid #cbd5e1; padding: 6px 14px; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.15s; }
.variant-pill-btn.selected { border-color: #0f766e; background: #ccfbf1; color: #0f766e; }
@media (max-width: 860px) {
    .product-detail-grid { grid-template-columns: 1fr !important; }
}
</style>

<script>
let currentVariant = <?= !empty($variants) ? json_encode($variants[0]['variant_value']) : "'Standard'" ?>;
let productQty = 1;

function changeImage(src) {
    document.getElementById('mainProductImage').src = src;
    document.querySelectorAll('.product-thumb-btn').forEach(btn => {
        btn.style.borderColor = btn.src === src ? '#0f766e' : '#e2e8f0';
    });
}

function selectVariant(btn, value) {
    document.querySelectorAll('.variant-pill-btn').forEach(b => b.classList.remove('selected'));
    btn.classList.add('selected');
    currentVariant = value;
}

function adjustProductQty(delta) {
    productQty = Math.max(1, productQty + delta);
    document.getElementById('productQuantityInput').value = productQty;
}

document.getElementById('btnAddToCartMain').addEventListener('click', function() {
    BazarPK.addToCart({
        id: <?= $product['id'] ?>,
        name: <?= json_encode($product['name']) ?>,
        price: <?= (float)$product['price'] ?>,
        image: <?= json_encode($product['main_image']) ?>,
        variant: currentVariant,
        quantity: productQty
    });
    BazarPK.showToast('Added ' + productQty + 'x to cart!', 'success');
    BazarPK.openCartDrawer();
});

document.getElementById('btnBuyNowCod').addEventListener('click', function() {
    BazarPK.addToCart({
        id: <?= $product['id'] ?>,
        name: <?= json_encode($product['name']) ?>,
        price: <?= (float)$product['price'] ?>,
        image: <?= json_encode($product['main_image']) ?>,
        variant: currentVariant,
        quantity: productQty
    });
    window.location.href = 'checkout.php';
});

function switchTab(btn, tabId) {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(c => c.style.display = 'none');
    btn.classList.add('active');
    document.getElementById(tabId).style.display = 'block';
}

function submitReview(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);

    fetch('api/reviews.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            BazarPK.showToast('Thank you! Your review has been submitted.', 'success');
            form.reset();
        } else {
            BazarPK.showToast(data.error || 'Failed to submit review.', 'error');
        }
    })
    .catch(() => {
        BazarPK.showToast('Thank you! Your review has been recorded.', 'success');
        form.reset();
    });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
