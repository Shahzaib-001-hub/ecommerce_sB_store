<?php
/**
 * Wishlist Page
 * BazarPK - Pakistani Custom eCommerce Platform
 */

$pageTitle = "My Wishlist";
require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding: 32px 16px 80px 16px;">
    <h1 style="font-size: 24px; font-weight: 800; color: #0f172a; margin-bottom: 8px;">My Wishlist ❤️</h1>
    <p style="font-size: 14px; color: #64748b; margin-bottom: 28px;">Your saved Pakistani fashion, tech gadgets, and favorite items.</p>

    <div id="wishlistContainer">
        <!-- Rendered by JS -->
    </div>
</div>

<script>
function renderWishlistPage() {
    const container = document.getElementById('wishlistContainer');
    let wishlist = [];
    try {
        wishlist = JSON.parse(localStorage.getItem('bazarpk_wishlist')) || [];
    } catch(e) { wishlist = []; }

    if (wishlist.length === 0) {
        container.innerHTML = `
            <div style="background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:60px 20px; text-align:center;">
                <div style="font-size:48px; margin-bottom:12px;">❤️</div>
                <h3 style="font-size:18px; color:#0f172a; margin-bottom:6px;">Your Wishlist is Empty</h3>
                <p style="font-size:14px; color:#64748b; margin-bottom:20px;">Click the heart icon on any product to save it here for later.</p>
                <a href="shop.php" class="btn-primary" style="display:inline-block; padding:10px 24px;">Explore Catalog</a>
            </div>
        `;
        return;
    }

    container.innerHTML = '<div style="text-align:center; padding:40px; color:#64748b;">Loading saved items...</div>';

    // Fetch product details for wishlist items
    fetch('api/products.php?action=search&q=')
        .then(res => res.json())
        .then(data => {
            const products = data.products || [];
            const savedProducts = products.filter(p => wishlist.includes(p.id));

            if (savedProducts.length === 0) {
                // If items aren't in search sample, render generic cards
                renderMockWishlistItems(wishlist);
                return;
            }

            let html = '<div class="products-grid">';
            savedProducts.forEach(prod => {
                html += `
                    <div class="product-card">
                        <div class="product-thumb-wrapper">
                            <a href="product.php?id=${prod.id}">
                                <img src="${prod.main_image || 'assets/images/placeholder.jpg'}" alt="${prod.name}" class="product-thumb">
                            </a>
                            <button type="button" class="btn-wishlist active" onclick="removeWishlistItem(${prod.id})" title="Remove from Wishlist">&times;</button>
                        </div>
                        <div class="product-info">
                            <a href="product.php?id=${prod.id}" class="product-title">${prod.name}</a>
                            <div class="product-bottom-row">
                                <span class="price-current">Rs. ${Number(prod.price).toLocaleString()}</span>
                                <button type="button" class="btn-add-cart" onclick="moveToCart(${prod.id}, '${prod.name}', ${prod.price}, '${prod.main_image}')">
                                    🛒 Add
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            });
            html += '</div>';
            container.innerHTML = html;
        })
        .catch(() => {
            renderMockWishlistItems(wishlist);
        });
}

function renderMockWishlistItems(ids) {
    const container = document.getElementById('wishlistContainer');
    let html = '<div class="products-grid">';
    ids.forEach(id => {
        html += `
            <div class="product-card">
                <div class="product-thumb-wrapper">
                    <img src="https://images.unsplash.com/photo-1597983073493-88cd35cf93b0?w=800&auto=format&fit=crop&q=80" class="product-thumb">
                    <button type="button" class="btn-wishlist active" onclick="removeWishlistItem(${id})" title="Remove from Wishlist">&times;</button>
                </div>
                <div class="product-info">
                    <a href="product.php?id=${id}" class="product-title">Saved Product #${id}</a>
                    <div class="product-bottom-row">
                        <span class="price-current">In Stock</span>
                        <a href="product.php?id=${id}" class="btn-add-cart">View</a>
                    </div>
                </div>
            </div>
        `;
    });
    html += '</div>';
    container.innerHTML = html;
}

function removeWishlistItem(id) {
    BazarPK.toggleWishlist(id);
    renderWishlistPage();
}

function moveToCart(id, name, price, image) {
    BazarPK.addToCart({ id, name, price, image, variant: 'Standard', quantity: 1 });
    BazarPK.showToast(`Moved "${name}" to your cart!`, 'success');
    BazarPK.openCartDrawer();
}

document.addEventListener('DOMContentLoaded', renderWishlistPage);
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
