<?php
/**
 * Return & Refund Policy
 * BazarPK - Pakistani Custom eCommerce Platform
 */

$pageTitle = "Return & Exchange Policy";
require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding: 40px 16px 80px 16px; max-width: 800px;">
    <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 36px 32px; line-height: 1.8; font-size: 14px; color: #334155;">
        <h1 style="font-size: 26px; font-weight: 800; color: #0f172a; margin-bottom: 20px;">Return, Exchange &amp; Refund Policy</h1>

        <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 14px 18px; margin-bottom: 20px; font-size: 14px; color: #166534;">
            <strong>✨ 7-Day Easy Exchange Guarantee</strong><br>
            If your size doesn't fit or you receive a damaged product, we offer a 100% free exchange across all cities in Pakistan.
        </div>

        <h3 style="font-size: 16px; font-weight: 700; color: #0f172a; margin-top: 20px;">Eligibility Criteria:</h3>
        <ul style="padding-left: 20px;">
            <li>Items must be unworn, unwashed, and in original packaging with intact product tags.</li>
            <li>Exchange request must be initiated within 7 calendar days of courier delivery.</li>
            <li>Tech gadgets must retain their original warranty seal and all enclosed accessories.</li>
        </ul>

        <h3 style="font-size: 16px; font-weight: 700; color: #0f172a; margin-top: 20px;">How to Initiate an Exchange:</h3>
        <p>Simply message our customer support team on WhatsApp at <strong>+<?= STORE_WHATSAPP ?></strong> with your Order Number and photos of the parcel. We will arrange reverse courier pickup from your doorstep.</p>

        <h3 style="font-size: 16px; font-weight: 700; color: #0f172a; margin-top: 20px;">Refunds:</h3>
        <p>For approved returns, refunds are transferred directly into your <strong>JazzCash</strong>, <strong>Easypaisa</strong>, or designated Pakistani Bank Account within 48-72 banking hours.</p>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
