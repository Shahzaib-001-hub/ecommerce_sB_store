<?php
/**
 * Contact Us & Support
 * BazarPK - Pakistani Custom eCommerce Platform
 */

$pageTitle = "Contact Us & Support";
require_once __DIR__ . '/includes/header.php';

$sent = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sent = true;
}
?>

<div class="container" style="padding: 40px 16px 80px 16px; max-width: 800px;">
    <div style="text-align: center; margin-bottom: 36px;">
        <h1 style="font-size: 28px; font-weight: 800; color: #0f172a; margin-bottom: 8px;">Customer Support</h1>
        <p style="font-size: 14px; color: #64748b; margin: 0;">We're here to assist you with order tracking, size advice, or general questions.</p>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 32px;" class="contact-layout">
        <!-- Direct Channels -->
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 28px;">
            <h3 style="font-size: 17px; font-weight: 800; color: #0f172a; margin-bottom: 16px;">Direct Channels</h3>
            
            <div style="margin-bottom: 18px;">
                <div style="font-size: 12px; color: #64748b; font-weight: 700; text-transform: uppercase;">WhatsApp Support (Fastest)</div>
                <a href="https://wa.me/<?= STORE_WHATSAPP ?>" target="_blank" style="font-size: 16px; font-weight: 700; color: #16a34a; text-decoration: none;">
                    💬 +<?= STORE_WHATSAPP ?>
                </a>
                <div style="font-size: 12px; color: #64748b; margin-top: 2px;">Available Mon - Sat (10:00 AM - 9:00 PM PKT)</div>
            </div>

            <div style="margin-bottom: 18px;">
                <div style="font-size: 12px; color: #64748b; font-weight: 700; text-transform: uppercase;">Phone Helpline</div>
                <div style="font-size: 15px; font-weight: 700; color: #0f172a;"><?= STORE_PHONE ?></div>
            </div>

            <div style="margin-bottom: 18px;">
                <div style="font-size: 12px; color: #64748b; font-weight: 700; text-transform: uppercase;">Email Assistance</div>
                <div style="font-size: 14px; font-weight: 600; color: #0f766e;"><?= STORE_EMAIL ?></div>
            </div>

            <div>
                <div style="font-size: 12px; color: #64748b; font-weight: 700; text-transform: uppercase;">Warehouse / Office Address</div>
                <div style="font-size: 13px; color: #475569; margin-top: 2px; line-height: 1.5;"><?= STORE_ADDRESS ?></div>
            </div>
        </div>

        <!-- Contact Form -->
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 28px;">
            <h3 style="font-size: 17px; font-weight: 800; color: #0f172a; margin-bottom: 16px;">Send Us a Message</h3>

            <?php if ($sent): ?>
                <div style="background: #dcfce7; color: #166534; padding: 14px; border-radius: 8px; font-size: 13.5px; border: 1px solid #86efac;">
                    ✓ Thank you! Your message has been received. Our team will contact you shortly on WhatsApp or phone.
                </div>
            <?php else: ?>
                <form method="POST">
                    <?= csrf_field() ?>
                    <div style="margin-bottom: 12px;">
                        <input type="text" name="name" required placeholder="Your Name" style="width: 100%; padding: 10px 12px; border: 1.5px solid #cbd5e1; border-radius: 6px; font-size: 13px; box-sizing: border-box;">
                    </div>
                    <div style="margin-bottom: 12px;">
                        <input type="tel" name="phone" required placeholder="Mobile Number (e.g. 03001234567)" style="width: 100%; padding: 10px 12px; border: 1.5px solid #cbd5e1; border-radius: 6px; font-size: 13px; box-sizing: border-box;">
                    </div>
                    <div style="margin-bottom: 12px;">
                        <input type="text" name="order_no" placeholder="Order Number (if applicable)" style="width: 100%; padding: 10px 12px; border: 1.5px solid #cbd5e1; border-radius: 6px; font-size: 13px; box-sizing: border-box;">
                    </div>
                    <div style="margin-bottom: 16px;">
                        <textarea name="message" rows="4" required placeholder="How can we help you?" style="width: 100%; padding: 10px 12px; border: 1.5px solid #cbd5e1; border-radius: 6px; font-size: 13px; box-sizing: border-box;"></textarea>
                    </div>
                    <button type="submit" class="btn-primary" style="width: 100%; justify-content: center; padding: 12px; font-size: 14px; border-radius: 6px;">
                        Send Inquiry
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
@media (max-width: 680px) {
    .contact-layout { grid-template-columns: 1fr !important; }
}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
