<?php
session_start();
require_once 'config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'agent') {
    header('Location: login.html'); exit;
}

$stmt = $pdo->prepare("SELECT status, end_date FROM subscriptions WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
$stmt->execute([$_SESSION['user_id']]);
$sub = $stmt->fetch();

// subscription approved وما انتهت → يرجع للداشبورد
if ($sub && $sub['status'] === 'approved' && 
    $sub['end_date'] && strtotime($sub['end_date']) > time()) {
    header('Location: agents.php'); exit;
}

// pending → يبقى هنا مع رسالة انتظار
$isPending = $sub && $sub['status'] === 'pending';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Renew Subscription — China2DZ</title>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{min-height:100vh;background:#0d0d0d;font-family:'Montserrat',sans-serif;color:#fff}
.topbar{background:rgba(255,255,255,0.04);border-bottom:1px solid rgba(255,255,255,0.08);padding:16px 28px;display:flex;align-items:center;justify-content:space-between}
.logo{font-size:20px;font-weight:700;color:#d91f26}
.logout{padding:8px 18px;background:rgba(217,31,38,0.15);color:#ff5057;border:1px solid rgba(217,31,38,0.3);border-radius:8px;text-decoration:none;font-size:13px;font-weight:600}
.container{max-width:960px;margin:40px auto;padding:0 20px}
.ph{margin-bottom:28px}
.ph h1{font-size:1.6rem;font-weight:700;margin-bottom:4px}
.ph p{color:rgba(255,255,255,0.45);font-size:.9rem}
.plans-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px;margin-bottom:32px}
@media(max-width:700px){.plans-grid{grid-template-columns:1fr}}
.plan{background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.1);border-radius:16px;padding:28px;position:relative}
.plan.featured{border-color:#c9a84c;background:rgba(201,168,76,0.06)}
.plan-badge{position:absolute;top:-12px;left:50%;transform:translateX(-50%);background:#c9a84c;color:#000;font-size:11px;font-weight:700;padding:4px 14px;border-radius:20px;white-space:nowrap}
.plan-name{font-size:1.1rem;font-weight:700;margin-bottom:10px}
.plan-price{font-size:2rem;font-weight:800;color:#c9a84c}
.plan-price span{font-size:.9rem;color:rgba(255,255,255,0.4);font-weight:400}
.plan-per{font-size:.8rem;color:rgba(255,255,255,0.4);margin-bottom:18px}
.plan-feats{list-style:none;margin-bottom:22px;display:flex;flex-direction:column;gap:8px}
.plan-feats li{font-size:.83rem;display:flex;align-items:center;gap:8px}
.btn-outline{background:none;border:1px solid rgba(255,255,255,0.2);color:#fff;padding:11px 20px;border-radius:10px;font-family:'Montserrat',sans-serif;font-size:.85rem;font-weight:600;cursor:pointer;width:100%;transition:all .2s}
.btn-outline:hover{border-color:#c9a84c;color:#c9a84c}
.btn-gold{background:#c9a84c;color:#000;border:none;padding:11px 20px;border-radius:10px;font-family:'Montserrat',sans-serif;font-size:.85rem;font-weight:700;cursor:pointer;width:100%;transition:all .2s}
.btn-gold:hover{background:#b8963d}
.full{width:100%}

/* Modal */
.modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,0.7);z-index:100;align-items:center;justify-content:center;backdrop-filter:blur(4px)}
.modal-overlay.open{display:flex}
.modal{background:#141414;border:1px solid rgba(255,255,255,0.1);border-radius:20px;padding:32px;width:100%;max-width:460px}
.modal h3{font-size:1.1rem;margin-bottom:20px;font-weight:700}
.field{margin-bottom:16px}
.field label{display:block;font-size:11px;font-weight:700;color:rgba(255,255,255,0.4);text-transform:uppercase;letter-spacing:.7px;margin-bottom:7px}
.field select,.field input{width:100%;background:rgba(255,255,255,0.05);border:1.5px solid rgba(255,255,255,0.09);border-radius:10px;padding:12px 14px;color:#fff;font-family:'Montserrat',sans-serif;font-size:13px;outline:none}
.field select option{background:#1a1a1a}
.dur-btns{display:flex;gap:8px;flex-wrap:wrap}
.dur-btn{flex:1;padding:10px;border:1px solid rgba(255,255,255,0.12);border-radius:8px;background:none;color:#fff;font-family:'Montserrat',sans-serif;font-size:.78rem;cursor:pointer;text-align:center;transition:all .2s;min-width:80px}
.dur-btn.active{border-color:#c9a84c;background:rgba(201,168,76,0.12);color:#c9a84c}
.msg{padding:12px;border-radius:10px;font-size:13px;margin-bottom:16px;display:none}
.msg.success{background:rgba(62,207,142,0.1);color:#3ecf8e;border:1px solid rgba(62,207,142,0.2);display:block}
.msg.error{background:rgba(244,91,91,0.1);color:#f45b5b;border:1px solid rgba(244,91,91,0.2);display:block}
.modal-actions{display:flex;gap:10px;margin-top:8px}
</style>
</head>
<body>
<div class="topbar">
    <a href="index.php" style="display:flex;align-items:center;gap:6px;color:rgba(255,255,255,0.6);text-decoration:none;font-size:13px;font-weight:600;padding:8px 14px;border:1px solid rgba(255,255,255,0.1);border-radius:8px;">
        ← Back to China2DZ
    </a>
    <div class="logo">China2DZ</div>
    <a href="logout.php" class="logout">Logout</a>
</div>

<div class="container">
    <?php if ($isPending): ?>
<div style="background:rgba(59,130,246,.1);border:1px solid rgba(59,130,246,.3);
border-radius:14px;padding:24px;margin-bottom:28px;display:flex;gap:16px;align-items:center;">
    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="1.5">
        <circle cx="12" cy="12" r="10"/>
        <polyline points="12 6 12 12 16 14"/>
    </svg>
    <div>
        <div style="font-weight:700;color:#3b82f6;margin-bottom:4px;">Payment Under Review</div>
        <div style="color:rgba(255,255,255,.5);font-size:.85rem;">
            Your payment has been submitted and is awaiting admin approval. 
            Your dashboard will open automatically once approved.
        </div>
    </div>
</div>
<?php endif; ?>
    <div class="ph">
        <h1>Subscription</h1>
        <p>All prices in Algerian Dinar (DZD)</p>
    </div>

    <div class="plans-grid">
        <div class="plan">
            <div class="plan-name">Starter</div>
            <div class="plan-price">1,900 <span>DZD</span></div>
            <div class="plan-per">30-day access</div>
            <ul class="plan-feats">
                <li><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#2ecc71" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>Up to 10 Cars</li>
                <li><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#2ecc71" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>Basic Analytics</li>
                <li><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#2ecc71" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>Client Requests</li>
                <li><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#555" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg><span style="color:#555">Priority Listing</span></li>
            </ul>
            <button class="btn-outline full" onclick="openModal('Starter',1900)">Subscribe</button>
        </div>
        <div class="plan featured">
            <div class="plan-badge">Most Popular</div>
            <div class="plan-name">Pro</div>
            <div class="plan-price">4,900 <span>DZD</span></div>
            <div class="plan-per">30-day access</div>
            <ul class="plan-feats">
                <li><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#2ecc71" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>Unlimited Cars</li>
                <li><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#2ecc71" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>Advanced Analytics</li>
                <li><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#2ecc71" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>Priority Listing</li>
                <li><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#2ecc71" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>Priority Support</li>
            </ul>
            <button class="btn-gold full" onclick="openModal('Pro',4900)">Subscribe</button>
        </div>
        <div class="plan">
            <div class="plan-name">Business</div>
            <div class="plan-price">9,900 <span>DZD</span></div>
            <div class="plan-per">90-day access</div>
            <ul class="plan-feats">
                <li><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#2ecc71" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>Everything in Pro</li>
                <li><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#2ecc71" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>Multi-Agent</li>
                <li><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#2ecc71" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>Dedicated Manager</li>
                <li><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#2ecc71" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>Custom Branding</li>
            </ul>
            <button class="btn-outline full" onclick="openModal('Business',9900)">Subscribe</button>
        </div>
    </div>
</div>

<!-- Payment Modal -->
<div class="modal-overlay" id="payModal">
    <div class="modal">
        <h3 id="modalTitle">Subscribe</h3>
        <div id="payMsg" class="msg"></div>
        <div class="field">
            <label>Duration</label>
            <div class="dur-btns" id="durBtns"></div>
        </div>
        <div class="field">
            <label>Payment Method</label>
            <select id="payMethod">
                <option value="">-- Choose --</option>
                <option value="CCP">CCP</option>
                <option value="BaridiMob">BaridiMob</option>
                <option value="Virement bancaire">Virement bancaire</option>
            </select>
        </div>
        <div class="field">
            <label>Reference / Transfer Number</label>
            <input type="text" id="payRef" placeholder="e.g. 1234567890">
        </div>
        <div class="field">
            <label>Proof (Cheque / Screenshot)</label>
            <input type="file" id="proofFile" accept="image/*,.pdf">
        </div>
        <div class="modal-actions">
            <button class="btn-outline" onclick="closeModal()">Cancel</button>
            <button class="btn-gold" onclick="sendPayment()">Send Payment</button>
        </div>
    </div>
</div>

<script>
const DURATIONS = {
    Starter:[{days:30,price:1900,label:'1 Month',save:null},{days:90,price:4900,label:'3 Months',save:'Save 900 DZD'},{days:180,price:8500,label:'6 Months',save:'Save 2,900 DZD'}],
    Pro:[{days:30,price:4900,label:'1 Month',save:null},{days:90,price:12900,label:'3 Months',save:'Save 1,800 DZD'},{days:180,price:22000,label:'6 Months',save:'Save 7,400 DZD'}],
    Business:[{days:90,price:9900,label:'3 Months',save:null},{days:180,price:17900,label:'6 Months',save:'Save 1,900 DZD'},{days:365,price:32000,label:'1 Year',save:'Save 7,600 DZD'}]
};

let selPlan='', selPrice=0, selDays=0;

function openModal(plan, basePrice) {
    selPlan = plan;
    document.getElementById('modalTitle').textContent = 'Subscribe — ' + plan;
    document.getElementById('payMsg').className = 'msg';
    document.getElementById('payMethod').value = '';
    document.getElementById('payRef').value = '';
    document.getElementById('proofFile').value = '';
    const btns = document.getElementById('durBtns');
    btns.innerHTML = '';
    DURATIONS[plan].forEach((d,i) => {
        const b = document.createElement('button');
        b.className = 'dur-btn' + (i===0?' active':'');
        b.innerHTML = d.label + '<br><strong>' + d.price.toLocaleString() + ' DZD</strong>' + (d.save ? '<br><small style="color:#2ecc71">'+d.save+'</small>' : '');
        b.onclick = () => { document.querySelectorAll('.dur-btn').forEach(x=>x.classList.remove('active')); b.classList.add('active'); selPrice=d.price; selDays=d.days; };
        btns.appendChild(b);
        if(i===0){selPrice=d.price; selDays=d.days;}
    });
    document.getElementById('payModal').classList.add('open');
}

function closeModal() {
    document.getElementById('payModal').classList.remove('open');
}

function sendPayment() {
    var method = document.getElementById('payMethod').value;
    var ref    = document.getElementById('payRef').value.trim();
    var file   = document.getElementById('proofFile').files[0];
    var msg    = document.getElementById('payMsg');

    if (!method || !ref || !file) {
        msg.className = 'msg error';
        msg.textContent = 'Please fill all fields and attach proof.';
        return;
    }

    var form = new FormData();
    form.append('plan', selPlan);
    form.append('amount', selPrice);
    form.append('days', selDays);
    form.append('payment_method', method);
    form.append('payment_reference', ref);
    form.append('proof', file);

    fetch('save_payment.php', { method:'POST', body:form })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            msg.className = 'msg success';
            msg.textContent = 'Request sent! Waiting for admin approval.';
            setTimeout(() => { closeModal(); location.href='subscription_expired.php'; }, 2000);
        } else {
            msg.className = 'msg error';
            msg.textContent = data.error || 'An error occurred.';
        }
    });
}
</script>
</body>
</html>