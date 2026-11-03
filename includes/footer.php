<?php
/**
 * Footer Template
 * BazarPK - Pakistani Custom eCommerce Platform
 */
?>
<!-- Main Site Footer -->
<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">
            <!-- Brand & Mission -->
            <div class="footer-col">
                <div class="logo-wrapper" style="margin-bottom: 16px;">
                    <div class="logo-icon" style="background: #14b8a6;">B</div>
                    <div>
                        <span class="logo-text" style="color: #fff;">Bazar<span style="color: #14b8a6;">PK</span></span>
                    </div>
                </div>
                <p style="font-size: 13px; line-height: 1.6; margin-bottom: 16px;">
                    Pakistan's trusted destination for modern lifestyle, traditional wear, and cutting-edge tech gadgets. We deliver right to your doorstep with 100% Cash on Delivery protection and 7-day hassle-free exchanges.
                </p>
                <div style="font-size: 13px;">
                    <strong>Customer Support:</strong><br>
                    📞 Phone: <?= STORE_PHONE ?><br>
                    💬 WhatsApp: <a href="https://wa.me/<?= STORE_WHATSAPP ?>" target="_blank" style="color: #25d366; font-weight: 600;">+<?= STORE_WHATSAPP ?></a><br>
                    ✉️ Email: <?= STORE_EMAIL ?>
                </div>
            </div>

            <!-- Customer Service -->
            <div class="footer-col">
                <h4>Customer Service</h4>
                <ul>
                    <li><a href="<?= BASE_URL ?>/track-order.php">Track Your Order</a></li>
                    <li><a href="<?= BASE_URL ?>/shop.php">Browse All Products</a></li>
                    <li><a href="<?= BASE_URL ?>/cart.php">Shopping Cart</a></li>
                    <li><a href="<?= BASE_URL ?>/account.php">My Account & Orders</a></li>
                    <li><a href="<?= BASE_URL ?>/contact.php">Help & Contact Us</a></li>
                </ul>
            </div>

            <!-- Nationwide Logistics -->
            <div class="footer-col">
                <h4>Nationwide Delivery</h4>
                <ul>
                    <li>Karachi (1-2 Working Days)</li>
                    <li>Lahore (1-2 Working Days)</li>
                    <li>Islamabad & Rawalpindi</li>
                    <li>Faisalabad, Multan, Sialkot</li>
                    <li>Peshawar, Quetta & Interior</li>
                    <li>Azad Kashmir & Gilgit</li>
                </ul>
            </div>

            <!-- Payment Methods & Security -->
            <div class="footer-col">
                <h4>Payment & Security</h4>
                <p style="font-size: 13px; margin-bottom: 12px;">
                    Pay safely at your convenience via Cash on Delivery, instant Mobile Wallets, or Debit/Credit cards.
                </p>
                <div class="payment-badges-row">
                    <span class="payment-badge">💵 Cash on Delivery</span>
                    <span class="payment-badge" style="background:#dc2626;">📱 JazzCash</span>
                    <span class="payment-badge" style="background:#16a34a;">📱 Easypaisa</span>
                    <span class="payment-badge" style="background:#1e40af;">💳 Visa / Mastercard</span>
                </div>
                <div style="margin-top: 18px; font-size: 12px; color: #64748b;">
                    Official Logistics Partners: Trax, TCS, CallCourier, Leopard.
                </div>
            </div>
        </div>

        <!-- Bottom Copyright -->
        <div class="footer-bottom">
            <div>
                &copy; <?= date('Y') ?> <strong><?= STORE_NAME ?></strong>. All Rights Reserved. Built for Pakistan with PHP &amp; Vanilla JS.
            </div>
            <div style="display: flex; gap: 16px;">
                <a href="<?= BASE_URL ?>/terms.php" style="color: #64748b;">Terms of Service</a>
                <a href="<?= BASE_URL ?>/privacy.php" style="color: #64748b;">Privacy Policy</a>
                <a href="<?= BASE_URL ?>/refund-policy.php" style="color: #64748b;">Return &amp; Refund Policy</a>
            </div>
        </div>
    </div>
</footer>

<!-- Floating WhatsApp Button -->
<a href="https://wa.me/<?= STORE_WHATSAPP ?>?text=<?= urlencode('Assalam-o-Alaikum! I have a question about an order on BazarPK.') ?>" class="whatsapp-float" target="_blank" title="Chat with us on WhatsApp" aria-label="WhatsApp Support">
    <svg width="30" height="30" viewBox="0 0 24 24" fill="currentColor"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
</a>

<!-- Toast notification container -->
<div class="toast-container"></div>

<!-- Core JavaScript Engine -->
<script src="<?= BASE_URL ?>/assets/js/app.js"></script>

</body>
</html>
