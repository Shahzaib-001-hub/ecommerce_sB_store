<?php
/**
 * Checkout Page
 * BazarPK - Pakistani Custom eCommerce Platform
 */

$pageTitle = "Checkout";
$pageDescription = "Complete your order with Cash on Delivery, JazzCash, Easypaisa, or Card.";

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/config/constants.php';

$customer = getCurrentCustomer();
?>

<div class="container" style="padding: 32px 16px 60px 16px;">
    <!-- Checkout Progress Bar -->
    <div style="display: flex; justify-content: center; align-items: center; gap: 20px; margin-bottom: 32px; font-size: 14px; font-weight: 600;">
        <span style="color: #64748b;">1. Shopping Cart</span>
        <span style="color: #cbd5e1;">&rarr;</span>
        <span style="color: #0f766e; font-weight: 800;">2. Shipping &amp; Payment</span>
        <span style="color: #cbd5e1;">&rarr;</span>
        <span style="color: #cbd5e1;">3. Order Confirmation</span>
    </div>

    <form id="checkoutForm" onsubmit="handleCheckoutSubmit(event)">
        <?= csrf_field() ?>
        
        <div style="display: grid; grid-template-columns: 1fr 400px; gap: 36px;" class="checkout-layout">
            <!-- Left: Shipping & Payment Details -->
            <div>
                <!-- Contact & Shipping Address -->
                <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 28px; margin-bottom: 24px;">
                    <h2 style="font-size: 18px; font-weight: 800; color: #0f172a; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
                        <span>📍</span> Delivery Address (Pakistan)
                    </h2>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                        <div>
                            <label style="font-size: 13px; font-weight: 700; color: #334155; display: block; margin-bottom: 6px;">Full Name *</label>
                            <input type="text" name="customer_name" id="customer_name" value="<?= e($customer['name'] ?? '') ?>" placeholder="e.g. Muhammad Bilal" required style="width: 100%; padding: 11px 14px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 14px;">
                        </div>
                        <div>
                            <label style="font-size: 13px; font-weight: 700; color: #334155; display: block; margin-bottom: 6px;">Active Mobile Phone * (For Courier SMS &amp; Call)</label>
                            <input type="tel" name="customer_phone" id="customer_phone" value="<?= e($customer['phone'] ?? '') ?>" placeholder="03001234567" required pattern="^(03[0-9]{9}|\+923[0-9]{9})$" style="width: 100%; padding: 11px 14px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 14px;">
                            <span style="font-size: 11px; color: #64748b;">Format: 03XXXXXXXXX (11 digits)</span>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                        <div>
                            <label style="font-size: 13px; font-weight: 700; color: #334155; display: block; margin-bottom: 6px;">Email Address * (For Order Invoice)</label>
                            <input type="email" name="customer_email" id="customer_email" value="<?= e($customer['email'] ?? '') ?>" placeholder="name@domain.com" required style="width: 100%; padding: 11px 14px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 14px;">
                        </div>
                        <div>
                            <label style="font-size: 13px; font-weight: 700; color: #334155; display: block; margin-bottom: 6px;">Province / Region *</label>
                            <select name="shipping_province" id="shipping_province" onchange="handleProvinceChange()" required style="width: 100%; padding: 11px 14px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 14px; background: #fff;">
                                <option value="">Select Province</option>
                                <?php foreach (PK_PROVINCES as $k => $p): ?>
                                    <option value="<?= e($k) ?>"><?= e($p) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                        <div>
                            <label style="font-size: 13px; font-weight: 700; color: #334155; display: block; margin-bottom: 6px;">City / District *</label>
                            <select name="shipping_city" id="shipping_city" onchange="calculateShippingByCity()" required style="width: 100%; padding: 11px 14px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 14px; background: #fff;">
                                <option value="">Select City</option>
                                <?php foreach (PK_MAJOR_CITIES as $cityName => $cityInfo): ?>
                                    <option value="<?= e($cityName) ?>" data-province="<?= e($cityInfo['province']) ?>" data-rate="<?= $cityInfo['shipping'] ?>">
                                        <?= e($cityName) ?> (<?= e($cityInfo['days']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label style="font-size: 13px; font-weight: 700; color: #334155; display: block; margin-bottom: 6px;">Postal / Zip Code (Optional)</label>
                            <input type="text" name="shipping_postal_code" placeholder="e.g. 74000" style="width: 100%; padding: 11px 14px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 14px;">
                        </div>
                    </div>

                    <div style="margin-bottom: 16px;">
                        <label style="font-size: 13px; font-weight: 700; color: #334155; display: block; margin-bottom: 6px;">Complete Delivery Address (House/Apartment #, Street, Block, Area) *</label>
                        <textarea name="shipping_address" id="shipping_address" rows="3" placeholder="House 12-B, Street 4, Phase 5 DHA" required style="width: 100%; padding: 11px 14px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 14px;"></textarea>
                    </div>

                    <div>
                        <label style="font-size: 13px; font-weight: 700; color: #334155; display: block; margin-bottom: 6px;">Delivery Instructions (Optional)</label>
                        <input type="text" name="delivery_notes" placeholder="e.g. Call before arrival, leave with security guard, etc." style="width: 100%; padding: 11px 14px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 14px;">
                    </div>
                </div>

                <!-- Payment Method Selection -->
                <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 28px;">
                    <h2 style="font-size: 18px; font-weight: 800; color: #0f172a; margin-bottom: 8px; display: flex; align-items: center; gap: 8px;">
                        <span>💳</span> Select Payment Method
                    </h2>
                    <p style="font-size: 13px; color: #64748b; margin-bottom: 20px;">Choose how you would like to pay for your order</p>

                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        <!-- Cash on Delivery -->
                        <label class="payment-option-card selected" onclick="selectPaymentMethod('cod')">
                            <input type="radio" name="payment_method" value="cod" checked style="accent-color: #0f766e; width: 18px; height: 18px;">
                            <div style="flex: 1;">
                                <div style="display: flex; align-items: center; justify-content: space-between;">
                                    <strong style="color: #0f172a; font-size: 15px;">💵 Cash on Delivery (COD)</strong>
                                    <span style="background: #dcfce7; color: #166534; font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 4px;">Recommended</span>
                                </div>
                                <div style="font-size: 12px; color: #64748b; margin-top: 4px;">Pay with cash to the courier representative when the parcel arrives at your address.</div>
                            </div>
                        </label>

                        <!-- JazzCash -->
                        <label class="payment-option-card" onclick="selectPaymentMethod('jazzcash')">
                            <input type="radio" name="payment_method" value="jazzcash" style="accent-color: #0f766e; width: 18px; height: 18px;">
                            <div style="flex: 1;">
                                <div style="display: flex; align-items: center; justify-content: space-between;">
                                    <strong style="color: #0f172a; font-size: 15px;">📱 JazzCash (Mobile Account / Voucher)</strong>
                                    <span style="background: #fee2e2; color: #dc2626; font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 4px;">Instant</span>
                                </div>
                                <div style="font-size: 12px; color: #64748b; margin-top: 4px;">Pay securely via your JazzCash mobile account MPIN or generated voucher.</div>
                            </div>
                        </label>

                        <!-- Easypaisa -->
                        <label class="payment-option-card" onclick="selectPaymentMethod('easypaisa')">
                            <input type="radio" name="payment_method" value="easypaisa" style="accent-color: #0f766e; width: 18px; height: 18px;">
                            <div style="flex: 1;">
                                <div style="display: flex; align-items: center; justify-content: space-between;">
                                    <strong style="color: #0f172a; font-size: 15px;">📱 Easypaisa (Wallet / Direct OTP)</strong>
                                    <span style="background: #dcfce7; color: #15803d; font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 4px;">Instant</span>
                                </div>
                                <div style="font-size: 12px; color: #64748b; margin-top: 4px;">Debit directly from your Easypaisa wallet with OTP verification.</div>
                            </div>
                        </label>

                        <!-- Debit / Credit Card -->
                        <label class="payment-option-card" onclick="selectPaymentMethod('card')">
                            <input type="radio" name="payment_method" value="card" style="accent-color: #0f766e; width: 18px; height: 18px;">
                            <div style="flex: 1;">
                                <div style="display: flex; align-items: center; justify-content: space-between;">
                                    <strong style="color: #0f172a; font-size: 15px;">💳 Debit / Credit Card (Visa, Mastercard, PayPak)</strong>
                                    <span style="background: #e0f2fe; color: #0284c7; font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 4px;">3D Secure</span>
                                </div>
                                <div style="font-size: 12px; color: #64748b; margin-top: 4px;">256-bit encrypted card checkout backed by 3D-Secure bank OTP.</div>
                            </div>
                        </label>
                    </div>

                    <!-- Online Payment Notice -->
                    <div id="onlinePaymentNotice" style="display: none; margin-top: 16px; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 12px 16px; font-size: 13px; color: #1e40af;">
                        🔒 <em>Note: For online transactions, your order will be created as "Pending Verification" and securely verified with the payment gateway server before dispatch.</em>
                    </div>
                </div>
            </div>

            <!-- Right: Order Summary Sidebar -->
            <div>
                <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; position: sticky; top: 96px;">
                    <h3 style="font-size: 17px; font-weight: 800; color: #0f172a; margin-bottom: 16px; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px;">Your Order</h3>

                    <!-- Mini Cart Items List -->
                    <div id="checkoutItemsList" style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 20px; max-height: 240px; overflow-y: auto; padding-right: 4px;"></div>

                    <!-- Pricing Breakdown -->
                    <div style="border-top: 1px solid #f1f5f9; padding-top: 14px; margin-bottom: 16px;">
                        <div style="display: flex; justify-content: space-between; font-size: 14px; margin-bottom: 8px;">
                            <span style="color: #64748b;">Subtotal</span>
                            <span style="font-weight: 700;" id="checkoutSubtotal">Rs. 0</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 14px; margin-bottom: 8px;">
                            <span style="color: #64748b;">Shipping Fee</span>
                            <span style="font-weight: 700; color: #0f766e;" id="checkoutShippingFee">Rs. 200</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 14px; margin-bottom: 8px; color: #dc2626;" id="checkoutDiscountRow" style="display: none;">
                            <span>Discount</span>
                            <span style="font-weight: 700;" id="checkoutDiscountAmount">- Rs. 0</span>
                        </div>
                        <div style="border-top: 2px solid #f1f5f9; padding-top: 12px; display: flex; justify-content: space-between; align-items: baseline;">
                            <span style="font-size: 16px; font-weight: 800; color: #0f172a;">Grand Total</span>
                            <span style="font-size: 22px; font-weight: 800; color: #0f766e;" id="checkoutGrandTotal">Rs. 0</span>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" id="btnPlaceOrder" class="btn-checkout-drawer" style="padding: 15px; font-size: 16px; width: 100%;">
                        Place Order (Cash on Delivery)
                    </button>

                    <div style="margin-top: 14px; text-align: center; font-size: 12px; color: #64748b; line-height: 1.4;">
                        🛡️ 100% Risk-Free Guarantee &bull; Inspect before acceptance &bull; 7 Days Exchange
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
.payment-option-card {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    padding: 16px;
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.2s;
}
.payment-option-card.selected {
    border-color: #0f766e;
    background: #f0fdfa;
}
@media (max-width: 960px) {
    .checkout-layout { grid-template-columns: 1fr !important; }
}
</style>

<script>
let currentShippingFee = 200;
let appliedDiscount = 0;
let selectedPayment = 'cod';

function initCheckout() {
    const cart = BazarPK.getCart();
    if (cart.length === 0) {
        window.location.href = 'cart.php';
        return;
    }
    renderCheckoutSummary();
}

function renderCheckoutSummary() {
    const cart = BazarPK.getCart();
    const container = document.getElementById('checkoutItemsList');
    const subtotalEl = document.getElementById('checkoutSubtotal');
    const shippingEl = document.getElementById('checkoutShippingFee');
    const totalEl = document.getElementById('checkoutGrandTotal');
    const btn = document.getElementById('btnPlaceOrder');

    let subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    subtotalEl.textContent = 'Rs. ' + subtotal.toLocaleString();

    // Check free shipping threshold (Rs. 3500)
    if (subtotal >= 3500) {
        currentShippingFee = 0;
        shippingEl.innerHTML = '<span style="color:#16a34a; font-weight:800;">FREE</span>';
    } else {
        shippingEl.textContent = 'Rs. ' + currentShippingFee.toLocaleString();
    }

    let grandTotal = Math.max(0, subtotal + currentShippingFee - appliedDiscount);
    totalEl.textContent = 'Rs. ' + grandTotal.toLocaleString();

    let methodLabel = selectedPayment === 'cod' ? 'Cash on Delivery' : (selectedPayment.toUpperCase());
    btn.textContent = `Place Order (Rs. ${grandTotal.toLocaleString()}) • ${methodLabel}`;

    let html = '';
    cart.forEach(item => {
        html += `
            <div style="display:flex; align-items:center; gap:10px; font-size:13px;">
                <img src="${item.image || 'assets/images/placeholder.jpg'}" alt="${item.name}" style="width:44px; height:44px; object-fit:cover; border-radius:6px; border:1px solid #e2e8f0;">
                <div style="flex:1;">
                    <div style="font-weight:600; color:#0f172a; line-height:1.2;">${item.name}</div>
                    <div style="font-size:11px; color:#64748b;">Qty: ${item.quantity} &bull; ${item.variant}</div>
                </div>
                <div style="font-weight:700; color:#0f766e;">Rs. ${(item.price * item.quantity).toLocaleString()}</div>
            </div>
        `;
    });
    container.innerHTML = html;
}

function calculateShippingByCity() {
    const select = document.getElementById('shipping_city');
    const selectedOption = select.options[select.selectedIndex];
    const rate = selectedOption.dataset.rate;
    const province = selectedOption.dataset.province;

    if (province) {
        document.getElementById('shipping_province').value = province;
    }

    if (rate) {
        currentShippingFee = parseInt(rate, 10);
    }
    renderCheckoutSummary();
}

function handleProvinceChange() {
    // Keep city selection or trigger shipping refresh
    renderCheckoutSummary();
}

function selectPaymentMethod(method) {
    selectedPayment = method;
    document.querySelectorAll('.payment-option-card').forEach(card => card.classList.remove('selected'));
    const targetInput = document.querySelector(`input[name="payment_method"][value="${method}"]`);
    if (targetInput) {
        targetInput.checked = true;
        targetInput.closest('.payment-option-card').classList.add('selected');
    }

    const notice = document.getElementById('onlinePaymentNotice');
    if (notice) {
        notice.style.display = method === 'cod' ? 'none' : 'block';
    }

    renderCheckoutSummary();
}

function handleCheckoutSubmit(e) {
    e.preventDefault();
    const btn = document.getElementById('btnPlaceOrder');
    btn.disabled = true;
    btn.textContent = 'Processing your order... Please wait...';

    const cart = BazarPK.getCart();
    const formData = new FormData(document.getElementById('checkoutForm'));
    
    const payload = {
        customer_name: formData.get('customer_name'),
        customer_phone: formData.get('customer_phone'),
        customer_email: formData.get('customer_email'),
        shipping_province: formData.get('shipping_province'),
        shipping_city: formData.get('shipping_city'),
        shipping_postal_code: formData.get('shipping_postal_code'),
        shipping_address: formData.get('shipping_address'),
        delivery_notes: formData.get('delivery_notes'),
        payment_method: selectedPayment,
        shipping_fee: currentShippingFee,
        items: cart,
        csrf_token: formData.get('csrf_token')
    };

    fetch('api/checkout.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Clear local cart
            localStorage.removeItem('bazarpk_cart');
            
            if (data.payment_url) {
                // Redirect to Online Payment Gateway (JazzCash / Easypaisa / Card)
                window.location.href = data.payment_url;
            } else {
                // Direct COD Order Success
                window.location.href = `order-confirmation.php?order_id=${data.order_id}&order_number=${data.order_number}`;
            }
        } else {
            alert(data.error || 'Failed to place order. Please review your details.');
            btn.disabled = false;
            renderCheckoutSummary();
        }
    })
    .catch(err => {
        alert('An unexpected network error occurred. Please try again.');
        btn.disabled = false;
        renderCheckoutSummary();
    });
}

document.addEventListener('DOMContentLoaded', initCheckout);
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
