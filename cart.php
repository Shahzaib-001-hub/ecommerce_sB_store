<?php
/**
 * Shopping Cart Page
 * BazarPK - Pakistani Custom eCommerce Platform
 */

$pageTitle = "Shopping Cart";
require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding: 32px 16px 60px 16px;">
    <h1 style="font-size: 24px; font-weight: 800; color: #0f172a; margin-bottom: 24px;">Shopping Cart</h1>

    <div style="display: grid; grid-template-columns: 1fr 360px; gap: 32px;" class="cart-page-layout">
        <!-- Cart Table Container -->
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px;" id="cartTableContainer">
            <!-- Rendered by JS -->
        </div>

        <!-- Order Summary & Coupon Card -->
        <div>
            <!-- Coupon Box -->
            <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin-bottom: 20px;">
                <h3 style="font-size: 15px; font-weight: 700; margin-bottom: 12px;">Have a Promo Code?</h3>
                <form id="couponForm" onsubmit="applyPromoCode(event)" style="display: flex; gap: 8px;">
                    <input type="text" id="couponInput" placeholder="Try WELCOME10 or PKFREESHIP" style="flex: 1; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; text-transform: uppercase;">
                    <button type="submit" class="btn-primary" style="padding: 8px 16px; font-size: 13px; border-radius: 6px;">Apply</button>
                </form>
                <div id="couponMessage" style="font-size: 12px; margin-top: 8px;"></div>
            </div>

            <!-- Order Summary -->
            <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px;">
                <h3 style="font-size: 17px; font-weight: 800; color: #0f172a; margin-bottom: 18px; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px;">Order Summary</h3>
                
                <div style="display: flex; justify-content: space-between; font-size: 14px; margin-bottom: 12px;">
                    <span style="color: #64748b;">Subtotal</span>
                    <span style="font-weight: 700;" id="pageCartSubtotal">Rs. 0</span>
                </div>

                <div style="display: flex; justify-content: space-between; font-size: 14px; margin-bottom: 12px;">
                    <span style="color: #64748b;">Estimated Shipping</span>
                    <span style="font-weight: 700; color: #16a34a;" id="pageCartShipping">Calculated at Checkout</span>
                </div>

                <div style="display: flex; justify-content: space-between; font-size: 14px; margin-bottom: 16px; color: #dc2626;" id="discountRow" style="display: none;">
                    <span>Discount</span>
                    <span style="font-weight: 700;" id="pageCartDiscount">- Rs. 0</span>
                </div>

                <div style="border-top: 2px solid #f1f5f9; padding-top: 14px; display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 24px;">
                    <span style="font-size: 16px; font-weight: 800; color: #0f172a;">Estimated Total</span>
                    <span style="font-size: 22px; font-weight: 800; color: #0f766e;" id="pageCartTotal">Rs. 0</span>
                </div>

                <a href="<?= BASE_URL ?>/checkout.php" class="btn-checkout-drawer" id="cartProceedBtn" style="text-align: center; font-size: 15px; padding: 14px;">
                    Proceed to Checkout &bull; Cash on Delivery
                </a>

                <div style="margin-top: 18px; text-align: center;">
                    <a href="<?= BASE_URL ?>/shop.php" style="font-size: 13px; color: #0f766e; text-decoration: underline;">&larr; Continue Shopping</a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media (max-width: 860px) {
    .cart-page-layout { grid-template-columns: 1fr !important; }
}
</style>

<script>
let appliedDiscount = 0;
let appliedCouponCode = '';

function renderFullCartPage() {
    const cart = BazarPK.getCart();
    const container = document.getElementById('cartTableContainer');
    const subtotalEl = document.getElementById('pageCartSubtotal');
    const totalEl = document.getElementById('pageCartTotal');
    const discountRow = document.getElementById('discountRow');
    const discountEl = document.getElementById('pageCartDiscount');
    const proceedBtn = document.getElementById('cartProceedBtn');

    if (cart.length === 0) {
        container.innerHTML = `
            <div style="text-align:center; padding: 60px 20px;">
                <div style="font-size:56px; margin-bottom:14px;">🛒</div>
                <h3 style="font-size:20px; font-weight:800; color:#0f172a; margin-bottom:8px;">Your cart is currently empty</h3>
                <p style="font-size:14px; color:#64748b; margin-bottom:24px;">Add your favorite Pakistani apparel, earbuds or fragrances to get started.</p>
                <a href="shop.php" class="btn-primary" style="display:inline-block; padding:12px 28px;">Start Shopping</a>
            </div>
        `;
        subtotalEl.textContent = 'Rs. 0';
        totalEl.textContent = 'Rs. 0';
        proceedBtn.style.opacity = '0.5';
        proceedBtn.style.pointerEvents = 'none';
        return;
    }

    proceedBtn.style.opacity = '1';
    proceedBtn.style.pointerEvents = 'auto';

    let subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    subtotalEl.textContent = 'Rs. ' + subtotal.toLocaleString();

    let grandTotal = Math.max(0, subtotal - appliedDiscount);
    totalEl.textContent = 'Rs. ' + grandTotal.toLocaleString();

    if (appliedDiscount > 0) {
        discountRow.style.display = 'flex';
        discountEl.textContent = '- Rs. ' + appliedDiscount.toLocaleString();
    } else {
        discountRow.style.display = 'none';
    }

    let html = `
        <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
            <thead>
                <tr style="border-bottom: 2px solid #f1f5f9; text-align: left; color: #64748b; font-size: 12px; text-transform: uppercase;">
                    <th style="padding-bottom: 12px;">Product</th>
                    <th style="padding-bottom: 12px;">Price</th>
                    <th style="padding-bottom: 12px; text-align: center;">Qty</th>
                    <th style="padding-bottom: 12px; text-align: right;">Total</th>
                    <th style="padding-bottom: 12px; text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
    `;

    cart.forEach((item, index) => {
        const itemTotal = item.price * item.quantity;
        html += `
            <tr style="border-bottom: 1px solid #f1f5f9;">
                <td style="padding: 16px 0; display: flex; align-items: center; gap: 14px;">
                    <img src="${item.image || 'assets/images/placeholder.jpg'}" alt="${item.name}" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px; border: 1px solid #e2e8f0;">
                    <div>
                        <a href="product.php?id=${item.id}" style="font-weight: 700; color: #0f172a; text-decoration: none;">${item.name}</a>
                        <div style="font-size: 12px; color: #64748b;">Variant: ${item.variant}</div>
                    </div>
                </td>
                <td style="padding: 16px 0; font-weight: 600;">Rs. ${item.price.toLocaleString()}</td>
                <td style="padding: 16px 0; text-align: center;">
                    <div class="quantity-stepper" style="margin: 0 auto; width: fit-content;">
                        <button type="button" class="qty-btn" onclick="BazarPK.updateQuantity(${index}, -1); renderFullCartPage();">&minus;</button>
                        <input type="text" class="qty-input" value="${item.quantity}" readonly>
                        <button type="button" class="qty-btn" onclick="BazarPK.updateQuantity(${index}, 1); renderFullCartPage();">&plus;</button>
                    </div>
                </td>
                <td style="padding: 16px 0; font-weight: 800; color: #0f766e; text-align: right;">Rs. ${itemTotal.toLocaleString()}</td>
                <td style="padding: 16px 0; text-align: right;">
                    <button type="button" onclick="BazarPK.removeFromCart(${index}); renderFullCartPage();" style="background: none; border: none; color: #ef4444; cursor: pointer; font-size: 18px;" title="Remove">&times;</button>
                </td>
            </tr>
        `;
    });

    html += `
            </tbody>
        </table>
    `;
    container.innerHTML = html;
}

function applyPromoCode(e) {
    e.preventDefault();
    const code = document.getElementById('couponInput').value.trim().toUpperCase();
    const msgEl = document.getElementById('couponMessage');
    const cart = BazarPK.getCart();
    const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);

    if (!code) return;

    fetch(`api/coupons.php?action=validate&code=${encodeURIComponent(code)}&subtotal=${subtotal}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                appliedDiscount = data.discount;
                appliedCouponCode = code;
                msgEl.style.color = '#15803d';
                msgEl.textContent = `✓ Coupon applied! Saved Rs. ${data.discount.toLocaleString()}`;
                renderFullCartPage();
            } else {
                appliedDiscount = 0;
                msgEl.style.color = '#dc2626';
                msgEl.textContent = data.message || 'Invalid coupon code';
                renderFullCartPage();
            }
        })
        .catch(() => {
            // Local fallback simulation for default seeds
            if (code === 'WELCOME10') {
                appliedDiscount = Math.round(subtotal * 0.10);
                appliedCouponCode = code;
                msgEl.style.color = '#15803d';
                msgEl.textContent = `✓ Coupon WELCOME10 applied! (10% off)`;
                renderFullCartPage();
            } else if (code === 'PKFREESHIP') {
                appliedDiscount = 250;
                appliedCouponCode = code;
                msgEl.style.color = '#15803d';
                msgEl.textContent = `✓ Free shipping voucher applied!`;
                renderFullCartPage();
            } else {
                msgEl.style.color = '#dc2626';
                msgEl.textContent = 'Invalid promo code.';
            }
        });
}

document.addEventListener('DOMContentLoaded', renderFullCartPage);
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
