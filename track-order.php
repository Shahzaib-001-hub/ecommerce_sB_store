<?php
/**
 * Live Order Tracking Page
 * BazarPK - Pakistani Custom eCommerce Platform
 */

$pageTitle = "Track Order";
$pageDescription = "Track your parcel delivery across Pakistan in real-time with your Order ID or Mobile Number.";

require_once __DIR__ . '/includes/header.php';

$initialQuery = sanitize($_GET['q'] ?? '');
?>

<div class="container" style="padding: 40px 16px 80px 16px; max-width: 780px;">
    <!-- Title & Search Form -->
    <div style="text-align: center; margin-bottom: 36px;">
        <h1 style="font-size: 28px; font-weight: 800; color: #0f172a; margin-bottom: 8px;">Track Your Parcel</h1>
        <p style="font-size: 14px; color: #64748b; max-width: 500px; margin: 0 auto 24px auto;">
            Enter your <strong>Order Number</strong> (e.g. <code>PK-2611-1001</code>) or your <strong>11-digit Pakistani Mobile Number</strong>.
        </p>

        <form id="trackingForm" onsubmit="handleTrackSubmit(event)" style="display: flex; gap: 10px; max-width: 520px; margin: 0 auto;">
            <input type="text" id="trackInput" value="<?= e($initialQuery) ?>" placeholder="e.g. PK-2611-1001 or 03001234567" required style="flex: 1; padding: 12px 18px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 14px; outline: none;">
            <button type="submit" class="btn-primary" style="padding: 12px 24px; font-size: 14px; border-radius: 8px;">
                Track Now
            </button>
        </form>
    </div>

    <!-- Tracking Results Container -->
    <div id="trackingResult" style="display: none;"></div>

    <!-- Logistics Network Info -->
    <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; margin-top: 36px;">
        <h3 style="font-size: 15px; font-weight: 700; color: #0f172a; margin-bottom: 12px;">Official Nationwide Logistics Network</h3>
        <p style="font-size: 13px; color: #64748b; line-height: 1.6; margin-bottom: 14px;">
            All parcels are picked and dispatched daily from our Karachi and Lahore distribution centers via licensed couriers (Trax, TCS, and Leopard). You will receive an SMS and WhatsApp notification with your delivery agent's contact details when the parcel is out for delivery.
        </p>
        <div style="display: flex; gap: 16px; flex-wrap: wrap; font-size: 12px; font-weight: 600; color: #0f766e;">
            <span>✓ Trax Logistics Integrated</span>
            <span>✓ TCS Express Tracking</span>
            <span>✓ CallCourier Network</span>
            <span>✓ Open Parcel Policy</span>
        </div>
    </div>
</div>

<style>
.stepper {
    display: flex;
    justify-content: space-between;
    position: relative;
    margin: 32px 0 24px 0;
}
.stepper::before {
    content: "";
    position: absolute;
    top: 18px;
    left: 20px;
    right: 20px;
    height: 3px;
    background: #e2e8f0;
    z-index: 1;
}
.step-item {
    position: relative;
    z-index: 2;
    text-align: center;
    flex: 1;
}
.step-circle {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: #fff;
    border: 3px solid #cbd5e1;
    color: #64748b;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 8px auto;
    font-size: 13px;
    font-weight: 700;
}
.step-item.active .step-circle {
    border-color: #0f766e;
    background: #0f766e;
    color: #fff;
}
.step-label {
    font-size: 12px;
    font-weight: 600;
    color: #475569;
}
.step-time {
    font-size: 11px;
    color: #94a3b8;
    margin-top: 2px;
}
@media (max-width: 640px) {
    .stepper { flex-direction: column; gap: 20px; }
    .stepper::before { display: none; }
    .step-item { display: flex; align-items: center; gap: 14px; text-align: left; }
    .step-circle { margin: 0; }
}
</style>

<script>
function handleTrackSubmit(e) {
    if (e) e.preventDefault();
    const query = document.getElementById('trackInput').value.trim();
    if (!query) return;

    const resultBox = document.getElementById('trackingResult');
    resultBox.style.display = 'block';
    resultBox.innerHTML = '<div style="text-align:center; padding:30px; color:#64748b;">Checking courier network and database...</div>';

    fetch(`api/tracking.php?q=${encodeURIComponent(query)}`)
        .then(res => res.json())
        .then(data => {
            if (data.success && data.order) {
                renderTrackingDetails(data.order);
            } else {
                resultBox.innerHTML = `
                    <div style="background:#fee2e2; border:1px solid #fca5a5; color:#991b1b; padding:20px; border-radius:12px; text-align:center; font-size:14px;">
                        <strong>Order Not Found</strong><br>
                        ${data.error || 'Please double-check your Order ID or Mobile Number.'}
                    </div>
                `;
            }
        })
        .catch(() => {
            resultBox.innerHTML = `
                <div style="background:#fee2e2; border:1px solid #fca5a5; color:#991b1b; padding:20px; border-radius:12px; text-align:center; font-size:14px;">
                    An unexpected connection error occurred. Please try again.
                </div>
            `;
        });
}

function renderTrackingDetails(order) {
    const resultBox = document.getElementById('trackingResult');

    let stepperHtml = '<div class="stepper">';
    order.timeline.forEach((step, idx) => {
        stepperHtml += `
            <div class="step-item ${step.done ? 'active' : ''}">
                <div class="step-circle">${step.done ? '✓' : (idx + 1)}</div>
                <div>
                    <div class="step-label">${step.status}</div>
                    <div class="step-time">${step.time}</div>
                </div>
            </div>
        `;
    });
    stepperHtml += '</div>';

    resultBox.innerHTML = `
        <div style="background:#fff; border:1px solid #e2e8f0; border-radius:16px; padding:28px; box-shadow:var(--shadow-sm);">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; border-bottom:1px solid #f1f5f9; padding-bottom:16px; margin-bottom:20px;">
                <div>
                    <span style="font-size:12px; color:#64748b; text-transform:uppercase; font-weight:700;">Order Status</span>
                    <h2 style="font-size:22px; font-weight:800; color:#0f766e; margin:2px 0 0 0; text-transform:capitalize;">${order.order_status.replace('_', ' ')}</h2>
                </div>
                <div style="text-align:right;">
                    <div style="font-size:13px; color:#64748b;">Order: <strong>${order.order_number}</strong></div>
                    <div style="font-size:12px; color:#0f766e;">Courier: <strong>${order.courier_name || 'Trax Logistics'}</strong> (${order.tracking_code})</div>
                </div>
            </div>

            <!-- Visual Stepper Progress -->
            ${stepperHtml}

            <!-- Details Grid -->
            <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:20px; display:grid; grid-template-columns:1fr 1fr; gap:16px; font-size:13px; margin-top:24px;">
                <div>
                    <div style="color:#64748b;">Customer Name:</div>
                    <strong style="color:#0f172a;">${order.customer_name}</strong>
                    <div style="color:#64748b; margin-top:8px;">Destination City:</div>
                    <strong style="color:#0f172a;">${order.shipping_city}, ${order.shipping_province || 'Pakistan'}</strong>
                </div>
                <div>
                    <div style="color:#64748b;">Payment Method:</div>
                    <strong style="color:#0f172a; text-transform:uppercase;">${order.payment_method} (${order.payment_status})</strong>
                    <div style="color:#64748b; margin-top:8px;">Total Payable:</div>
                    <strong style="color:#0f766e; font-size:15px;">${order.formatted_total || ('Rs. ' + Number(order.total_amount).toLocaleString())}</strong>
                </div>
            </div>

            <div style="display:flex; justify-content:center; gap:14px; margin-top:24px; flex-wrap:wrap;">
                <a href="https://wa.me/<?= STORE_WHATSAPP ?>?text=Assalam-o-Alaikum! Inquiring about order status of ${order.order_number}" target="_blank" class="btn-primary" style="background:#25d366; color:#fff; border-radius:8px; padding:10px 20px; font-size:13px;">
                    💬 Inquire on WhatsApp
                </a>
                <button type="button" onclick="window.print()" class="btn-secondary" style="color:#0f172a; border-color:#cbd5e1; border-radius:8px; padding:10px 20px; font-size:13px;">
                    🖨️ Print Receipt
                </button>
            </div>
        </div>
    `;
}

// Auto-run if query param provided in URL
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('trackInput').value.trim()) {
        handleTrackSubmit();
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
