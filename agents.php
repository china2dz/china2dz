<?php
session_start();

require_once 'config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'agent') {
    header('Location: login.html'); exit;
}
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
if ($user['status'] === 'pending') {
    header('Location: agent_pending.php'); exit;
}
?>
<?php
// جيبي بيانات agent_profiles أيضاً
$stmt2 = $pdo->prepare("SELECT * FROM agent_profiles WHERE user_id = ?");
$stmt2->execute([$_SESSION['user_id']]);
$profile = $stmt2->fetch();
$stmt3 = $pdo->prepare("
    SELECT * FROM subscriptions 
    WHERE user_id = ? 
    ORDER BY created_at DESC 
    LIMIT 1
");
$stmt3->execute([$_SESSION['user_id']]);
$subscription = $stmt3->fetch();
// ✅ تحقق من الـ subscription
// جيب بيانات المستخدم الكاملة
$fullUser = $pdo->prepare("SELECT trial_used, trial_refused, trial_started_at FROM users WHERE id = ?");
$fullUser->execute([$_SESSION['user_id']]);
$userTrial = $fullUser->fetch();

// تحقق من الوصول: subscription OR trial active
$hasAccess = false;

// 1. subscription approved وما انتهت
if ($subscription && $subscription['status'] === 'approved' && 
    !empty($subscription['end_date']) && strtotime($subscription['end_date']) > time()) {
    $hasAccess = true;
}

// 2. trial active وما انتهى وما رفضش
if (!$hasAccess && $userTrial['trial_used'] && !$userTrial['trial_refused'] && 
    !empty($userTrial['trial_started_at'])) {
    $elapsed = time() - strtotime($userTrial['trial_started_at']);
    if ($elapsed < 7 * 24 * 3600) {
        $hasAccess = true;
    }
}

// 3. دفع وينتظر قبول (pending) → يدخل لكن بلوكد
// 3. دفع وينتظر قبول
if (!$hasAccess && $subscription && $subscription['status'] === 'pending') {
    header('Location: subscription_expired.php?status=pending'); exit;
}

if (!$hasAccess) {
    header('Location: subscription_expired.php'); exit;
}

// ✅ حدّث الـ session
$_SESSION['sub_status'] = $subscription['status'];
$_SESSION['sub_plan']   = $subscription['plan'];
// حدّث الـ session من DB
if ($subscription) {
    $_SESSION['sub_status'] = $subscription['status'];
    $_SESSION['sub_plan']   = $subscription['plan'];
}
$pdo->prepare("UPDATE users SET is_online = 1 WHERE id = ?")->execute([$_SESSION['user_id']]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>china2dz — Agent Panel</title>
<link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600;700;900&family=Barlow+Condensed:wght@600;700;800;900&family=Syne:wght@400;600;700;800&display=swap" rel="stylesheet"/>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --bg:#090910;--s1:#111119;--s2:#18181f;--s3:#22222c;--br:#2c2c38;
  --gold:#d4a843;--gold2:#c4922f;--gold-glow:rgba(212,168,67,.18);
  --text:#eeeef5;--muted:#6e6e8a;--green:#2ecc71;--red:#e74c3c;
  --blue:#3b82f6;--purple:#8b5cf6;--sw:252px;--r:12px;--r2:8px;
}
html{font-size:14.5px}
body{font-family:'Barlow',sans-serif;background:var(--bg);color:var(--text);display:flex;min-height:100vh;overflow-x:hidden}
.sidebar{width:var(--sw);position:fixed;top:0;left:0;height:100vh;background:var(--s1);border-right:1px solid var(--br);display:flex;flex-direction:column;z-index:100;transition:transform .3s ease}
.sb-inner{display:flex;flex-direction:column;height:100%;padding:1.25rem 1rem 1rem;gap:0}
.sb-logo{display:flex;align-items:center;gap:.65rem;font-family:'Barlow Condensed',sans-serif;font-size:1.35rem;font-weight:800;color:var(--gold);letter-spacing:.5px;padding:.25rem .5rem 1.1rem;border-bottom:1px solid var(--br);margin-bottom:1rem}
.sb-agent{display:flex;align-items:center;gap:.75rem;padding:.7rem .75rem;background:var(--s2);border-radius:var(--r);margin-bottom:1rem;cursor:pointer;border:1px solid transparent;transition:border-color .2s}
.sb-agent:hover{border-color:var(--gold)}
.av{width:38px;height:38px;border-radius:9px;flex-shrink:0;background:linear-gradient(135deg,var(--gold),var(--gold2));display:grid;place-items:center;color:#000;overflow:hidden}
.av.sm{width:30px;height:30px;border-radius:7px}
.av img{width:100%;height:100%;object-fit:cover}
.sb-name{font-weight:700;font-size:.88rem;line-height:1.2}
.status-tag{display:inline-block;font-size:.68rem;font-weight:700;padding:.2rem .55rem;border-radius:20px;margin-top:.25rem;letter-spacing:.3px}
.status-tag[data-s="trial"]{background:rgba(212,168,67,.15);color:var(--gold)}
.status-tag[data-s="active"]{background:rgba(46,204,113,.15);color:var(--green)}
.status-tag[data-s="pending"]{background:rgba(59,130,246,.15);color:var(--blue)}
.status-tag[data-s="blocked"]{background:rgba(231,76,60,.15);color:var(--red)}
nav{display:flex;flex-direction:column;gap:.2rem;flex:1;margin-bottom:.75rem}
.nl{display:flex;align-items:center;gap:.75rem;padding:.7rem .85rem;border-radius:var(--r2);cursor:pointer;color:var(--muted);font-size:.88rem;font-weight:500;text-decoration:none;transition:all .18s;position:relative;background:none;border:none;width:100%;font-family:'Barlow',sans-serif;text-align:left}
.nl svg{flex-shrink:0}
.nl:hover{background:var(--s2);color:var(--text)}
.nl.active{background:var(--gold-glow);color:var(--gold);border-left:2px solid var(--gold)}
.nl.locked-nav{opacity:.4;cursor:not-allowed;pointer-events:none}
.nl.locked-nav::after{content:'🔒';font-size:.7rem;margin-left:auto}
.nb{margin-left:auto;background:var(--gold);color:#000;font-size:.62rem;font-weight:800;padding:.12rem .42rem;border-radius:20px;min-width:17px;text-align:center}
.trial-box{background:var(--s2);border:1px solid var(--br);border-radius:var(--r);padding:.9rem 1rem;margin-bottom:.75rem}
.tbox-top{display:flex;align-items:center;gap:.4rem;font-size:.72rem;color:var(--muted);margin-bottom:.35rem}
.tbox-time{font-family:'Barlow Condensed',sans-serif;font-size:2rem;font-weight:800;color:var(--gold);line-height:1}
.tbox-bar{background:var(--br);border-radius:3px;height:3px;margin-top:.5rem;overflow:hidden}
.tbox-fill{height:100%;background:linear-gradient(90deg,var(--gold),var(--gold2));border-radius:3px;transition:width 1s linear;width:100%}
.tbox-sub{display:flex;justify-content:space-between;align-items:center;margin-top:.5rem;font-size:.75rem}
.tbox-sub span:first-child{color:var(--gold);font-weight:700}
.tbox-sub span:last-child{color:var(--muted)}
.logout-btn{display:flex;align-items:center;gap:.65rem;padding:.65rem .85rem;background:none;border:1px solid var(--br);border-radius:var(--r2);color:var(--muted);cursor:pointer;font-size:.88rem;font-family:'Barlow',sans-serif;transition:all .2s;width:100%}
.logout-btn:hover{border-color:var(--red);color:var(--red)}
.topbar{display:none;position:fixed;top:0;left:0;right:0;height:52px;background:var(--s1);border-bottom:1px solid var(--br);padding:0 1rem;align-items:center;justify-content:space-between;z-index:99}
.burger{background:none;border:none;color:var(--text);cursor:pointer}
.tb-logo{font-family:'Barlow Condensed',sans-serif;font-size:1.3rem;font-weight:800;color:var(--gold)}
.main{margin-left:var(--sw);flex:1;padding:2rem;min-height:100vh;max-width:calc(100vw - var(--sw))}
.page{display:none;animation:fup .35s ease}
.page.active{display:block}
@keyframes fup{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:translateY(0)}}
.page-lock-banner{background:linear-gradient(135deg,rgba(212,168,67,.12),rgba(212,168,67,.05));border:1px solid rgba(212,168,67,.35);border-radius:var(--r);padding:1rem 1.25rem;margin-bottom:1.5rem;display:flex;align-items:center;gap:.85rem}
.page-lock-banner svg{flex-shrink:0;color:var(--gold)}
.page-lock-banner .plb-text{flex:1}
.page-lock-banner .plb-title{font-weight:700;font-size:.9rem;color:var(--gold)}
.page-lock-banner .plb-sub{font-size:.8rem;color:var(--muted);margin-top:.2rem}
.page-lock-banner .btn-gold{flex-shrink:0;padding:.45rem 1rem;font-size:.8rem}
.locked-content{filter:blur(6px);pointer-events:none;user-select:none;opacity:.45;transition:filter .3s,opacity .3s}
.ph{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1.75rem;flex-wrap:wrap;gap:1rem}
.ph h1{font-family:'Barlow Condensed',sans-serif;font-size:2rem;font-weight:800;letter-spacing:.5px}
.sub{color:var(--muted);font-size:.88rem;margin-top:.2rem}
.stats-row{display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:1.75rem}
.sc{background:var(--s1);border:1px solid var(--br);border-radius:var(--r);padding:1.1rem 1.25rem;display:flex;align-items:center;gap:1rem;cursor:pointer;transition:all .22s;position:relative;overflow:hidden}
.sc:hover{border-color:var(--gold);transform:translateY(-2px);box-shadow:0 8px 28px rgba(0,0,0,.4)}
.sc-ico{width:42px;height:42px;border-radius:10px;display:grid;place-items:center;flex-shrink:0}
.c1{background:rgba(59,130,246,.15);color:var(--blue)}
.c2{background:rgba(212,168,67,.15);color:var(--gold)}
.c3{background:rgba(46,204,113,.15);color:var(--green)}
.c4{background:rgba(139,92,246,.15);color:var(--purple)}
.sc-n{font-family:'Barlow Condensed',sans-serif;font-size:1.9rem;font-weight:800;line-height:1}
.sc-l{color:var(--muted);font-size:.78rem;margin-top:.15rem}
.sc-arr{margin-left:auto;color:var(--muted);opacity:0;transition:opacity .2s}
.sc:hover .sc-arr{opacity:1}
.dash-grid{display:grid;grid-template-columns:1fr 1fr;gap:1.25rem}
.card{background:var(--s1);border:1px solid var(--br);border-radius:var(--r);padding:1.4rem}
.ch{display:flex;justify-content:space-between;align-items:center;margin-bottom:1.1rem}
.ch h3{font-family:'Barlow Condensed',sans-serif;font-weight:700;font-size:1.1rem;letter-spacing:.3px}
.sec-title{font-family:'Barlow Condensed',sans-serif;font-size:1.1rem;font-weight:700;letter-spacing:.3px;margin-bottom:1.4rem}
.dli{display:flex;align-items:center;gap:.9rem;padding:.65rem .75rem;background:var(--s2);border-radius:var(--r2);margin-bottom:.5rem;cursor:pointer;transition:background .18s}
.dli:hover{background:var(--s3)}
.dli-thumb{width:48px;height:38px;border-radius:7px;background:var(--s3);display:grid;place-items:center;flex-shrink:0;overflow:hidden}
.dli-thumb img{width:100%;height:100%;object-fit:cover}
.dli-thumb svg{color:var(--muted)}
.dli-info{flex:1;min-width:0}
.dli-title{font-weight:600;font-size:.88rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.dli-price{color:var(--gold);font-size:.82rem;font-weight:600}
.dli-sub{color:var(--muted);font-size:.78rem}
.fbar{display:flex;gap:.6rem;align-items:center;margin-bottom:1.5rem;flex-wrap:wrap}
.fb{padding:.4rem 1rem;background:var(--s1);border:1px solid var(--br);border-radius:20px;color:var(--muted);cursor:pointer;font-size:.83rem;font-family:'Barlow',sans-serif;font-weight:500;transition:all .18s}
.fb.active,.fb:hover{background:var(--gold);border-color:var(--gold);color:#000;font-weight:700}
.srch{margin-left:auto;display:flex;align-items:center;gap:.5rem;background:var(--s1);border:1px solid var(--br);border-radius:20px;padding:.4rem 1rem}
.srch input{background:none;border:none;color:var(--text);font-size:.83rem;outline:none;width:180px;font-family:'Barlow',sans-serif}
.srch input::placeholder{color:var(--muted)}
.cgrid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:1.1rem}
.ccard{background:var(--s1);border:1px solid var(--br);border-radius:var(--r);overflow:hidden;transition:transform .22s,box-shadow .22s;animation:fup .35s ease}
.ccard:hover{transform:translateY(-3px);box-shadow:0 12px 32px rgba(0,0,0,.45)}
.cimg{width:100%;height:165px;object-fit:cover;display:block;background:var(--s2)}
.cimg-ph{width:100%;height:165px;background:var(--s2);display:grid;place-items:center}
.cimg-ph svg{color:var(--s3)}
.cbody{padding:.9rem 1rem}
.ctitle{font-family:'Barlow Condensed',sans-serif;font-size:1.05rem;font-weight:700;letter-spacing:.3px;margin-bottom:.2rem}
.cprice{color:var(--gold);font-weight:700;font-size:1rem;margin-bottom:.4rem}
.cdesc{color:var(--muted);font-size:.78rem;line-height:1.5;margin-bottom:.65rem;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.cst{display:inline-block;padding:.18rem .6rem;border-radius:20px;font-size:.7rem;font-weight:700;margin-bottom:.65rem;letter-spacing:.3px}
.cst.available{background:rgba(46,204,113,.12);color:var(--green)}
.cst.sold{background:rgba(231,76,60,.12);color:var(--red)}
.cacts{display:flex;gap:.45rem;flex-wrap:wrap}
.req-card{background:var(--s1);border:1px solid var(--br);border-radius:var(--r);padding:1.1rem 1.3rem;display:flex;align-items:center;gap:1.1rem;flex-wrap:wrap;margin-bottom:.9rem;animation:fup .33s ease;transition:border-color .2s}
.req-card.unread{border-left:3px solid var(--gold)}
.req-av{width:42px;height:42px;border-radius:50%;flex-shrink:0;background:linear-gradient(135deg,var(--blue),#60a5fa);display:grid;place-items:center;font-weight:700;font-size:.88rem;color:#fff}
.req-info{flex:1;min-width:160px}
.req-name{font-weight:700;font-size:.92rem}
.req-car{color:var(--gold);font-size:.8rem;margin-top:.15rem}
.req-msg{color:var(--text);font-size:.82rem;margin-top:.3rem;line-height:1.4}
.req-time{color:var(--muted);font-size:.74rem;margin-top:.3rem}
.req-acts{display:flex;gap:.45rem;align-items:center;flex-wrap:wrap}
.req-st{padding:.2rem .6rem;border-radius:20px;font-size:.7rem;font-weight:700;letter-spacing:.3px}
.req-st.pending{background:rgba(212,168,67,.12);color:var(--gold)}
.req-st.accepted{background:rgba(46,204,113,.12);color:var(--green)}
.req-st.rejected{background:rgba(231,76,60,.12);color:var(--red)}
.ncard{background:var(--s1);border:1px solid var(--br);border-radius:var(--r);padding:1rem 1.25rem;display:flex;gap:.9rem;margin-bottom:.75rem;animation:fup .33s ease;transition:border-color .2s,background .2s}
.ncard.unread{border-left:3px solid var(--gold);background:rgba(212,168,67,.03)}
.n-ico{width:38px;height:38px;border-radius:9px;background:var(--s2);display:grid;place-items:center;flex-shrink:0}
.n-body{flex:1}
.n-title{font-weight:700;font-size:.9rem}
.n-txt{color:var(--muted);font-size:.82rem;margin-top:.2rem;line-height:1.4}
.n-time{color:var(--muted);font-size:.72rem;margin-top:.35rem}
.n-actions{display:flex;gap:.5rem;margin-top:.5rem;flex-wrap:wrap}
.n-btn{background:var(--s2);border:1px solid var(--br);border-radius:var(--r2);padding:.3rem .75rem;color:var(--text);font-size:.76rem;font-weight:600;cursor:pointer;font-family:'Barlow',sans-serif;transition:all .18s;display:inline-flex;align-items:center;gap:.35rem}
.n-btn:hover{border-color:var(--gold);color:var(--gold)}
.n-btn.primary{background:var(--gold-glow);border-color:var(--gold);color:var(--gold)}
.n-btn.primary:hover{background:var(--gold);color:#000}
.n-btn.danger{border-color:rgba(231,76,60,.4);color:var(--red)}
.n-btn.danger:hover{background:rgba(231,76,60,.15)}
.n-mark{background:none;border:none;color:var(--muted);cursor:pointer;font-size:.78rem;padding:.25rem .5rem;border-radius:6px;font-family:'Barlow',sans-serif;transition:color .18s;white-space:nowrap}
.n-mark:hover{color:var(--gold)}
.plans-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:1.25rem}
.plan{background:var(--s1);border:1px solid var(--br);border-radius:var(--r);padding:1.75rem 1.5rem;transition:transform .22s,box-shadow .22s;position:relative}
.plan:hover{transform:translateY(-3px);box-shadow:0 12px 30px rgba(0,0,0,.4)}
.plan.featured{border-color:var(--gold);background:linear-gradient(180deg,rgba(212,168,67,.07),var(--s1))}
.plan-badge{position:absolute;top:-1px;left:50%;transform:translateX(-50%);background:var(--gold);color:#000;font-size:.72rem;font-weight:800;padding:.22rem .85rem;border-radius:0 0 8px 8px;letter-spacing:.5px}
.plan-name{font-family:'Barlow Condensed',sans-serif;font-size:1.3rem;font-weight:800;letter-spacing:.5px;margin-bottom:.4rem}
.plan-price{font-family:'Barlow Condensed',sans-serif;font-size:2.5rem;font-weight:900;color:var(--gold);line-height:1}
.plan-price span{font-size:.9rem;color:var(--muted);font-family:'Barlow',sans-serif;font-weight:400}
.plan-per{color:var(--muted);font-size:.78rem;margin:.4rem 0 1.1rem}
.plan-feats{list-style:none;display:flex;flex-direction:column;gap:.55rem;margin-bottom:1.4rem}
.plan-feats li{display:flex;align-items:center;gap:.5rem;font-size:.85rem;color:var(--muted)}
.sub-rows{display:flex;flex-wrap:wrap;gap:1.25rem}
.sr{display:flex;flex-direction:column;gap:.2rem}
.sr span{font-size:.75rem;color:var(--muted)}
.sr strong{font-size:.95rem}
.duration-grid{display:flex;flex-direction:column;gap:.65rem}
.duration-btn{display:flex;align-items:center;gap:1rem;background:var(--s2);border:1px solid var(--br);border-radius:var(--r);padding:1rem 1.2rem;cursor:pointer;text-align:left;width:100%;font-family:'Barlow',sans-serif;color:var(--text);transition:all .2s;}
.duration-btn:hover,.duration-btn.selected{border-color:var(--gold);background:var(--gold-glow)}
.dur-info{flex:1}
.dur-days{font-family:'Barlow Condensed',sans-serif;font-size:1.1rem;font-weight:800;letter-spacing:.3px}
.dur-desc{color:var(--muted);font-size:.78rem;margin-top:.1rem}
.dur-price{font-family:'Barlow Condensed',sans-serif;font-size:1.3rem;font-weight:900;color:var(--gold)}
.dur-save{background:rgba(46,204,113,.15);color:var(--green);font-size:.68rem;font-weight:700;padding:.15rem .5rem;border-radius:20px;margin-top:.2rem;display:inline-block;letter-spacing:.3px}
.dur-radio{width:18px;height:18px;border-radius:50%;border:2px solid var(--br);flex-shrink:0;transition:all .2s;display:grid;place-items:center}
.duration-btn.selected .dur-radio,.duration-btn:hover .dur-radio{border-color:var(--gold)}
.duration-btn.selected .dur-radio::after{content:'';width:8px;height:8px;border-radius:50%;background:var(--gold);display:block}
.pay-summary-box{background:var(--s2);border:1px solid var(--br);border-radius:var(--r);padding:1rem 1.2rem;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.5rem}
.psb-plan{font-family:'Barlow Condensed',sans-serif;font-size:1.1rem;font-weight:800}
.psb-dur{color:var(--muted);font-size:.78rem;margin-top:.1rem}
.psb-price{font-family:'Barlow Condensed',sans-serif;font-size:1.5rem;font-weight:900;color:var(--gold)}
.method-grid{display:flex;flex-direction:column;gap:.6rem}
.method-btn{display:flex;align-items:center;gap:.9rem;background:var(--s2);border:1px solid var(--br);border-radius:var(--r);padding:1rem 1.1rem;cursor:pointer;width:100%;text-align:left;font-family:'Barlow',sans-serif;color:var(--text);transition:all .2s}
.method-btn:hover{border-color:var(--gold);background:var(--gold-glow);transform:translateX(3px)}
.method-ico{width:44px;height:44px;border-radius:10px;display:grid;place-items:center;flex-shrink:0}
.cib-ico{background:rgba(59,130,246,.15);color:var(--blue)}
.edahabia-ico{background:rgba(46,204,113,.15);color:var(--green)}
.vir-ico{background:rgba(212,168,67,.15);color:var(--gold)}
.method-info{flex:1}
.method-info strong{display:block;font-weight:700;font-size:.9rem}
.method-info span{color:var(--muted);font-size:.78rem;margin-top:.12rem;display:block}
.method-btn svg:last-child{color:var(--muted);flex-shrink:0;transition:transform .2s}
.method-btn:hover svg:last-child{transform:translateX(3px);color:var(--gold)}
.secure-badge{display:flex;align-items:center;gap:.45rem;font-size:.75rem;color:var(--green);margin-top:.75rem;padding:.5rem .75rem;background:rgba(46,204,113,.05);border:1px solid rgba(46,204,113,.2);border-radius:var(--r2)}
.pay-dets{background:var(--s2);border-radius:var(--r2);padding:.85rem 1rem;display:flex;flex-direction:column;gap:.45rem}
.pdr{display:flex;justify-content:space-between;font-size:.83rem}
.pdr span{color:var(--muted)}
.pay-note{color:var(--muted);font-size:.8rem}
#proofPrevVir img{max-height:110px;border-radius:8px;border:1px solid var(--br);margin-top:.6rem}
#proofPrevVir .pf-name{font-size:.78rem;color:var(--muted);margin-top:.3rem}
.prof-layout{display:grid;grid-template-columns:240px 1fr;gap:1.25rem}
.prof-left{text-align:center}
.pav-wrap{position:relative;display:inline-block;margin-bottom:1rem}
.pav{width:84px;height:84px;border-radius:18px;background:linear-gradient(135deg,var(--gold),var(--gold2));display:grid;place-items:center;color:#000;margin:0 auto;overflow:hidden}
.pav img{width:100%;height:100%;object-fit:cover}
.pav-cam{position:absolute;bottom:0;right:-4px;width:28px;height:28px;background:var(--gold);border-radius:50%;display:grid;place-items:center;cursor:pointer;color:#000}
.pn{font-family:'Barlow Condensed',sans-serif;font-size:1.25rem;font-weight:800;margin-bottom:.4rem}
.p-mini-stats{display:flex;justify-content:center;gap:1.5rem;margin-top:1.1rem;padding-top:1rem;border-top:1px solid var(--br)}
.pms-n{font-family:'Barlow Condensed',sans-serif;font-size:1.5rem;font-weight:800;color:var(--gold);line-height:1}
.pms-l{color:var(--muted);font-size:.75rem;margin-top:.15rem}
.fg{margin-bottom:1rem}
.fg label{display:block;font-size:.8rem;color:var(--muted);margin-bottom:.35rem;font-weight:500}
.fi{width:100%;background:var(--s2);border:1px solid var(--br);border-radius:var(--r2);padding:.6rem .9rem;color:var(--text);font-family:'Barlow',sans-serif;font-size:.88rem;outline:none;transition:border-color .18s;resize:vertical}
.fi:focus{border-color:var(--gold)}
.fi[readonly]{opacity:.55;cursor:not-allowed}
select.fi option{background:var(--s2)}
.frow{display:grid;grid-template-columns:1fr 1fr;gap:.9rem}
.upzone{border:2px dashed var(--br);border-radius:var(--r2);padding:1.3rem;text-align:center;cursor:pointer;color:var(--muted);transition:all .18s}
.upzone:hover{border-color:var(--gold);color:var(--text);background:var(--gold-glow)}
.upzone svg{margin-bottom:.4rem}
.upzone p{font-size:.83rem}
.iprev{display:flex;flex-wrap:wrap;gap:.6rem;margin-top:.7rem}
.iprev img{width:72px;height:58px;object-fit:cover;border-radius:7px;border:2px solid var(--br)}
.btn-gold{background:linear-gradient(135deg,var(--gold),var(--gold2));color:#000;border:none;border-radius:var(--r2);padding:.6rem 1.25rem;font-weight:700;font-size:.85rem;cursor:pointer;display:inline-flex;align-items:center;gap:.45rem;transition:transform .15s,box-shadow .15s;font-family:'Barlow',sans-serif}
.btn-gold:hover{transform:translateY(-1px);box-shadow:0 6px 18px rgba(212,168,67,.3)}
.btn-outline{background:none;border:1px solid var(--br);border-radius:var(--r2);padding:.6rem 1.25rem;color:var(--text);font-size:.85rem;cursor:pointer;display:inline-flex;align-items:center;gap:.45rem;transition:all .18s;font-family:'Barlow',sans-serif}
.btn-outline:hover{border-color:var(--gold);color:var(--gold)}
.btn-outline.full,.btn-gold.full{width:100%;justify-content:center;margin-top:.5rem}
.btn-lnk{background:none;border:none;color:var(--gold);cursor:pointer;font-size:.82rem;font-family:'Barlow',sans-serif}
.btn-lnk:hover{text-decoration:underline}
.btn-sm{padding:.38rem .8rem;font-size:.78rem;border-radius:7px}
.btn-success{background:rgba(46,204,113,.15);border:1px solid var(--green);color:var(--green);border-radius:var(--r2);padding:.55rem 1.1rem;cursor:pointer;font-weight:700;font-size:.82rem;display:inline-flex;align-items:center;gap:.4rem;transition:background .18s;font-family:'Barlow',sans-serif}
.btn-success:hover{background:rgba(46,204,113,.3)}
.btn-danger{background:rgba(231,76,60,.15);border:1px solid var(--red);color:var(--red);border-radius:var(--r2);padding:.55rem 1.1rem;cursor:pointer;font-weight:700;font-size:.82rem;display:inline-flex;align-items:center;gap:.4rem;transition:background .18s;font-family:'Barlow',sans-serif}
.btn-danger:hover{background:rgba(231,76,60,.3)}
.ov{position:fixed;inset:0;background:rgba(0,0,0,.72);backdrop-filter:blur(8px);z-index:200;display:grid;place-items:center;padding:1rem;opacity:0;pointer-events:none;transition:opacity .25s}
.ov.open{opacity:1;pointer-events:all}
.modal{background:var(--s1);border:1px solid var(--br);border-radius:16px;width:100%;max-width:510px;max-height:90vh;overflow-y:auto;transform:translateY(18px) scale(.97);transition:transform .28s;box-shadow:0 24px 60px rgba(0,0,0,.6)}
.ov.open .modal{transform:translateY(0) scale(1)}
.modal-sm{max-width:380px}
.mhd{display:flex;justify-content:space-between;align-items:center;padding:1.3rem 1.4rem 0}
.mhd h2{font-family:'Barlow Condensed',sans-serif;font-size:1.2rem;font-weight:800;letter-spacing:.4px}
.cls-btn{background:var(--s2);border:none;color:var(--text);width:30px;height:30px;border-radius:50%;cursor:pointer;display:grid;place-items:center;transition:background .18s}
.cls-btn:hover{background:var(--red)}
.mbody{padding:1.3rem 1.4rem}
.mft{padding:.9rem 1.4rem 1.4rem;display:flex;justify-content:flex-end;gap:.65rem}
.cli-box{background:var(--s2);border-radius:var(--r2);padding:.9rem 1.1rem}
.cli-name{font-weight:700;font-size:.95rem}
.cli-car{color:var(--gold);font-size:.82rem;margin-top:.18rem}
.cli-msg{color:var(--muted);font-size:.82rem;margin-top:.45rem;line-height:1.45}
.act-row{display:flex;gap:.75rem;margin-top:1rem}
.del-ico{margin-bottom:1rem}
.v-stats{display:flex;justify-content:space-around;margin-bottom:1.5rem;padding:1rem;background:var(--s2);border-radius:var(--r);text-align:center}
.vs-n{font-family:'Barlow Condensed',sans-serif;font-size:2rem;font-weight:800;color:var(--gold)}
.vs-l{color:var(--muted);font-size:.78rem;margin-top:.15rem}
.vchart{background:var(--s2);border-radius:var(--r);padding:1.1rem;margin-bottom:1.25rem}
.vc-lbl{font-size:.78rem;color:var(--muted);margin-bottom:.85rem}
.vc-bars{display:flex;align-items:flex-end;gap:.4rem;height:80px;margin-bottom:.4rem}
.vc-bar{flex:1;background:var(--gold);border-radius:4px 4px 0 0;opacity:.8;transition:opacity .2s;position:relative}
.vc-bar:hover{opacity:1}
.vc-bar-tip{position:absolute;top:-22px;left:50%;transform:translateX(-50%);font-size:.7rem;color:var(--gold);white-space:nowrap;font-weight:700}
.vc-days{display:flex;gap:.4rem}
.vc-day{flex:1;text-align:center;font-size:.7rem;color:var(--muted)}
.vt-title{font-family:'Barlow Condensed',sans-serif;font-size:1rem;font-weight:700;letter-spacing:.3px;margin-bottom:.75rem}
.vt-item{display:flex;align-items:center;gap:.85rem;padding:.6rem .75rem;background:var(--s2);border-radius:var(--r2);margin-bottom:.5rem}
.vt-n{margin-left:auto;font-family:'Barlow Condensed',sans-serif;font-size:1.1rem;font-weight:700;color:var(--gold)}
.vt-l{font-size:.75rem;color:var(--muted)}
.notif-detail{padding:.5rem 0}
.notif-detail-txt{color:var(--muted);font-size:.88rem;line-height:1.6;margin-bottom:.75rem}
.notif-detail-time{font-size:.78rem;color:var(--muted);border-top:1px solid var(--br);padding-top:.65rem}
.lock-overlay{position:fixed;inset:0;background:rgba(0,0,0,.88);backdrop-filter:blur(14px);z-index:500;display:grid;place-items:center}
.lock-overlay.hidden{display:none}
.lock-box{background:var(--s1);border:1px solid var(--gold);border-radius:18px;padding:2.5rem 2.25rem;text-align:center;max-width:380px;width:100%;box-shadow:0 0 60px rgba(212,168,67,.18)}
.lock-ico{margin-bottom:1.1rem}
.lock-box h2{font-family:'Barlow Condensed',sans-serif;font-size:1.7rem;font-weight:800;margin-bottom:.6rem}
.lock-box p{color:var(--muted);margin-bottom:1.4rem;line-height:1.5}
.toast-wrap{position:fixed;bottom:1.3rem;right:1.3rem;z-index:999;display:flex;flex-direction:column;gap:.45rem}
.toast{background:var(--s1);border:1px solid var(--br);border-radius:10px;padding:.75rem 1.1rem;font-size:.83rem;min-width:240px;display:flex;align-items:center;gap:.65rem;animation:tslide .3s ease;box-shadow:0 8px 24px rgba(0,0,0,.5)}
.toast.ok{border-left:3px solid var(--green)}
.toast.err{border-left:3px solid var(--red)}
.toast.inf{border-left:3px solid var(--blue)}
.toast-ico{width:26px;height:26px;border-radius:7px;display:grid;place-items:center;flex-shrink:0}
.toast.ok .toast-ico{background:rgba(46,204,113,.15);color:var(--green)}
.toast.err .toast-ico{background:rgba(231,76,60,.15);color:var(--red)}
.toast.inf .toast-ico{background:rgba(59,130,246,.15);color:var(--blue)}
@keyframes tslide{from{opacity:0;transform:translateX(24px)}to{opacity:1;transform:translateX(0)}}
.processing-overlay{position:fixed;inset:0;background:rgba(0,0,0,.85);backdrop-filter:blur(12px);z-index:600;display:grid;place-items:center}
.proc-box{background:var(--s1);border:1px solid var(--br);border-radius:18px;padding:2.5rem 2.25rem;text-align:center;max-width:360px;width:100%}
.proc-spinner{width:52px;height:52px;border:3px solid var(--br);border-top-color:var(--gold);border-radius:50%;animation:spin 0.8s linear infinite;margin:0 auto 1.2rem}
@keyframes spin{to{transform:rotate(360deg)}}
.proc-title{font-family:'Barlow Condensed',sans-serif;font-size:1.4rem;font-weight:800;margin-bottom:.5rem}
.proc-sub{color:var(--muted);font-size:.83rem;line-height:1.5}
.proc-steps{margin-top:1.2rem;display:flex;flex-direction:column;gap:.4rem}
.proc-step{display:flex;align-items:center;gap:.6rem;font-size:.8rem;padding:.4rem .6rem;border-radius:7px;background:var(--s2)}
.proc-step.done{color:var(--green)}
.proc-step.done svg{color:var(--green)}
.proc-step.active{color:var(--gold)}
.proc-step.active svg{color:var(--gold);animation:spin .8s linear infinite}
.proc-step.wait{color:var(--muted)}
@media(max-width:1100px){.stats-row{grid-template-columns:1fr 1fr}}
@media(max-width:900px){
  .sidebar{transform:translateX(-100%)}
  .sidebar.open{transform:translateX(0)}
  .topbar{display:flex}
  .main{margin-left:0;padding:4.5rem 1rem 1.5rem;max-width:100vw}
  .dash-grid{grid-template-columns:1fr}
  .prof-layout{grid-template-columns:1fr}
  .plans-grid{grid-template-columns:1fr}
  .frow{grid-template-columns:1fr}
}
@media(max-width:600px){
  .stats-row{grid-template-columns:1fr 1fr}
  .cgrid{grid-template-columns:1fr}
}
::-webkit-scrollbar{width:5px;height:5px}
::-webkit-scrollbar-track{background:transparent}
::-webkit-scrollbar-thumb{background:var(--br);border-radius:3px}
.cst.reserved{background:rgba(212,168,67,.12);color:var(--gold)}
</style>
<script src="theme.js"></script>
</head>
<body>

<script>
  window.addEventListener('load', function() {
  renderAll();
});
</script>
<!-- LOCK OVERLAY -->
<div id="lockOverlay" class="lock-overlay hidden">
  <div class="lock-box">
    <div class="lock-ico">
      <svg width="52" height="52" viewBox="0 0 24 24" fill="none" stroke="#d4a843" stroke-width="1.5"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
    </div>
    <h2>Trial Expired</h2>
    <p>Your free trial period has ended.<br/>Subscribe to continue using china2dz.</p>
    <button class="btn-gold" onclick="goSub()">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
      View Plans
    </button>
  </div>
</div>

<!-- PAYMENT CHOICE MODAL -->
<div class="ov" id="payChoiceOv">
  <div class="modal">
    <div class="mhd">
      <h2 id="payChoiceTitle">Choose Duration</h2>
      <button class="cls-btn" onclick="closeOv('payChoiceOv')"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
    </div>
    <div class="mbody">
      <p style="color:var(--muted);font-size:.85rem;margin-bottom:1.2rem">Choose the duration for your <strong id="payChoicePlanName" style="color:var(--gold)"></strong> subscription:</p>
      <div id="durationOptions" class="duration-grid"></div>
    </div>
  </div>
</div>

<!-- PAYMENT METHOD MODAL -->
<div class="ov" id="payMethodOv">
  <div class="modal">
    <div class="mhd">
      <h2 id="payMethodTitle">Payment Method</h2>
      <button class="cls-btn" onclick="closeOv('payMethodOv')"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
    </div>
    <div class="mbody">
      <div class="pay-summary-box" id="paySummaryBox"></div>
      <p style="color:var(--muted);font-size:.83rem;margin:1.2rem 0 .8rem">Choose how to pay:</p>
      <div class="method-grid">
        <!-- بعد -->
<button class="method-btn" style="opacity:.4;cursor:not-allowed;pointer-events:none;">
          <div class="method-ico cib-ico"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg></div>
          <div class="method-info"><strong>CIB Card</strong><span>Secure online payment</span></div>
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
        </button>
        <!-- بعد -->
<button class="method-btn" style="opacity:.4;cursor:not-allowed;pointer-events:none;">
          <div class="method-ico edahabia-ico"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><circle cx="12" cy="12" r="3"/><path d="M2 10h20"/></svg></div>
          <div class="method-info"><strong>Edahabia Card</strong><span>Algérie Poste postal card</span></div>
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
        </button>
        <button class="method-btn" onclick="selectPayMethod('virement')">
          <div class="method-ico vir-ico"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg></div>
          <div class="method-info"><strong>CCP Wire Transfer</strong><span>Classic bank transfer</span></div>
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
        </button>
      </div>
    </div>
  </div>
</div>

<!-- PAYMENT CARD MODAL -->
<div class="ov" id="payCardOv">
  <div class="modal">
    <div class="mhd">
      <h2 id="payCardTitle">Card Payment</h2>
      <button class="cls-btn" onclick="closeOv('payCardOv')"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
    </div>
    <div class="mbody">
      <div class="pay-summary-box" id="payCardSummary"></div>
      <div class="card-form" style="margin-top:1.2rem">
        <div class="fg"><label>Card Number</label><input type="text" class="fi" id="cardNumber" placeholder="0000 0000 0000 0000" maxlength="19" oninput="formatCard(this)"/></div>
        <div class="frow">
          <div class="fg"><label>Expiration Date</label><input type="text" class="fi" id="cardExpiry" placeholder="MM/YY" maxlength="5" oninput="formatExpiry(this)"/></div>
          <div class="fg"><label>CVV Code</label><input type="text" class="fi" id="cardCvv" placeholder="123" maxlength="3"/></div>
        </div>
        <div class="fg"><label>Name on Card</label><input type="text" class="fi" id="cardName" placeholder="<?= htmlspecialchars(strtoupper($user['first_name'].' '.$user['last_name'])) ?>"/></div>
        <div class="secure-badge">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#2ecc71" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
          <span>SSL 256-bit Secure Payment</span>
        </div>
      </div>
    </div>
    <div class="mft">
      <button class="btn-outline" onclick="closeOv('payCardOv')">Cancel</button>
      <button class="btn-gold" onclick="submitCardPay()"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>Pay Now</button>
    </div>
  </div>
</div>

<!-- PAYMENT WIRE TRANSFER MODAL -->
<div class="ov" id="payVirOv">
  <div class="modal">
    <div class="mhd">
      <h2>CCP Wire Transfer</h2>
      <button class="cls-btn" onclick="closeOv('payVirOv')"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
    </div>
    <div class="mbody">
      <div class="pay-summary-box" id="payVirSummary"></div>
      <div class="pay-dets" style="margin-top:1.2rem">
        <div class="pdr"><span>Bank</span><strong>CCP Algeria</strong></div>
        <div class="pdr"><span>Account No.</span><strong>0021 4567 8901 23</strong></div>
        <div class="pdr"><span>RIP</span><strong>00799999001234567820</strong></div>
        <div class="pdr"><span>Beneficiary</span><strong>china2dz SARL</strong></div>
      </div>
      <p class="pay-note" style="margin-top:.85rem">Complete the transfer, then upload your receipt below.</p>
      <div class="fg" style="margin-top:1rem">
        <label>Payment Receipt (Photo / PDF)</label>
        <div class="upzone" onclick="document.getElementById('proofFileVir').click()">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
          <p>Click to upload</p>
          <input type="file" id="proofFileVir" accept="image/*,application/pdf" onchange="previewProofVir(event)" style="display:none"/>
        </div>
        <div id="proofPrevVir"></div>
      </div>
      <div class="fg"><label>Transaction Reference (optional)</label><input type="text" class="fi" id="payRefVir" placeholder="e.g. TXN-20240410"/></div>
    </div>
    <div class="mft">
      <button class="btn-outline" onclick="closeOv('payVirOv')">Cancel</button>
      <button class="btn-gold" onclick="submitVirPay()"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>Submit Request</button>
    </div>
  </div>
</div>

<!-- TOASTS -->
<div id="toastWrap" class="toast-wrap"></div>

<!-- SIDEBAR -->
<aside id="sidebar" class="sidebar">
  <div class="sb-inner">
    <div class="sb-logo">
  <a href="/index.php" style="text-decoration:none; display:flex; align-items:center;">
    <span style="font-family:'Syne',sans-serif; font-size:23px; font-weight:800; color:#00bcd4; letter-spacing:-0.5px;">China</span><span style="font-family:'Syne',sans-serif; font-size:23px; font-weight:800; color:#ffffff; letter-spacing:-0.5px;">2DZ</span>
  </a>
</div>
    <div class="sb-agent" onclick="window.location.href='profile.php'">
      <div class="av" id="sbAv">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
      </div>
      <div>
        <div class="sb-name" id="sbName"><?= htmlspecialchars($user['first_name'].' '.$user['last_name']) ?></div>
        <div class="status-tag" id="sbStatusTag" data-s="trial">Trial</div>
      </div>
    </div>
    <nav>
      <button class="nl active" data-p="dashboard" onclick="showPage('dashboard')">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
        Dashboard
      </button>
      <button class="nl" data-p="cars" onclick="showPage('cars')">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 17H3a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11a2 2 0 0 1 2 2v3"/><rect x="9" y="11" width="14" height="10" rx="1"/><circle cx="12" cy="21" r="1"/><circle cx="20" cy="21" r="1"/></svg>
        My Cars
        <span class="nb" id="nbCars">6</span>
      </button>
      <button class="nl" data-p="messages" onclick="showPage('messages');loadAgentConversations()">
  <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
  messages
  <span class="nb" id="nbMsg">0</span>
</button>
      <button class="nl" data-p="notifications" onclick="showPage('notifications');loadNotificationsFromDB()">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
        Notifications
        <span class="nb" id="nbNotif">2</span>
      </button>
      <button class="nl" data-p="shipment" onclick="showPage('shipment');loadShipmentReservations()">
  <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13" rx="1"/><path d="M16 8h4l3 5v3h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
  Shipment
  <span class="nb" id="nbShipment">0</span>
</button>
      <button class="nl" data-p="alerts" onclick="showPage('alerts');loadAgentAlerts(<?= $_SESSION['user_id'] ?>)">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/><line x1="12" y1="2" x2="12" y2="2"/></svg>
        Client Alerts
        <span class="nb" id="nbAlerts">0</span>
      </button>
      <button class="nl" data-p="subscription" onclick="showPage('subscription')">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
        Subscription
      </button>
      <button class="nl" data-p="payment" onclick="showPage('payment')">
  <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
  Payment Info
</button>
      <a href="profile.php" class="nl" style="text-decoration:none;">
  <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
  Profile
</a>
    </nav>
    <div class="trial-box" id="trialBox">
      <div class="tbox-top">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        <span id="trialLabel">Free Trial</span>
      </div>
      <div class="tbox-time" id="trialTime">03:00</div>
      <div class="tbox-bar"><div id="trialFill" class="tbox-fill"></div></div>
      <div class="tbox-sub" id="tboxSub" style="display:none">
        <span id="tboxDaysLeft"></span>
        <span id="tboxPlan"></span>
      </div>
    </div>
    <a href="index.php" class="logout-btn" style="text-decoration:none;margin-bottom:8px;">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
    Back to China2DZ
</a>
    <button class="logout-btn" onclick="doLogout()">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
      Logout
    </button>
  </div>
</aside>

<!-- MOBILE TOPBAR -->
<header class="topbar">
  <button class="burger" onclick="toggleSB()">
    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
  </button>
  <span class="tb-logo"><span style="font-family:'Syne',sans-serif;color:#00bcd4;">China</span><span style="font-family:'Syne',sans-serif;color:#ffffff;">2DZ</span></span>
  <div class="av sm" id="topAv">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
  </div>
</header>

<!-- MAIN CONTENT -->
<main class="main" id="main">

  <!-- DASHBOARD -->
  <section class="page active" id="page-dashboard">
    <div class="page-lock-banner" id="dashBanner" style="display:none"></div>
    <div class="ph">
      <div>
        <h1>Dashboard</h1>
        <p class="sub" id="welcomeMsg">Welcome</p>
      </div>
      <button class="btn-gold" onclick="guardedAction(function(){showPage('cars');setTimeout(openCarModal,100)})">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Add Car
      </button>
    </div>
    <div class="stats-row">
      <div class="sc" onclick="guardedAction(function(){showPage('cars')})">
        <div class="sc-ico c1"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 17H3a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11a2 2 0 0 1 2 2v3"/><rect x="9" y="11" width="14" height="10" rx="1"/><circle cx="12" cy="21" r="1"/><circle cx="20" cy="21" r="1"/></svg></div>
        <div class="sc-info"><div class="sc-n" id="dCars">6</div><div class="sc-l">Cars</div></div>
        <svg class="sc-arr" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
      </div>
    </div>
    <div class="dash-grid">
      <div class="card">
        <div class="ch"><h3>Recent Cars</h3><button class="btn-lnk" onclick="guardedAction(function(){showPage('cars')})">View All →</button></div>
        <div id="dCarsList"></div>
      </div>
</div>
  </section>
<!-- MESSAGES -->
  <section class="page" id="page-messages">
    <div class="ph"><h1>Messages</h1></div>
  <div style="background:var(--s1);border:1px solid var(--br);border-radius:var(--r);overflow:hidden;display:grid;grid-template-columns:220px 1fr;height:calc(100vh - 160px);">
  <div style="border-right:1px solid var(--br);overflow-y:auto;" id="agentConvList">
    <div style="padding:14px;font-weight:700;border-bottom:1px solid var(--br);">Conversations</div>
  </div>
  <div id="agentChatMain" style="display:flex;flex-direction:column;overflow:hidden;min-height:0;height:100%;">
    <div style="flex:1;display:flex;align-items:center;justify-content:center;color:var(--muted);">Select a conversation</div>
  </div>
</div>
  </section>
  <!-- CARS -->
  <section class="page" id="page-cars">
    <div class="page-lock-banner" id="carsBanner" style="display:none"></div>
    <div class="ph">
      <div><h1>My Cars</h1><p class="sub">Manage your listings</p></div>
      <button class="btn-gold" onclick="guardedAction(openCarModal)">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Add
      </button>
    </div>
    <div class="fbar">
      <button class="fb active" onclick="filterCars('all',this)">All</button>
      <button class="fb" onclick="filterCars('available',this)">Available</button>
      <button class="fb" onclick="filterCars('sold',this)">Sold</button>
      <div class="srch">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" placeholder="Search..." oninput="searchCars(this.value)"/>
      </div>
    </div>
    <div class="cgrid" id="carsGrid"></div>
  </section>

  <!-- REQUESTS -->
  <section class="page" id="page-requests">
    <div class="page-lock-banner" id="reqBanner" style="display:none"></div>
    <div class="ph"><h1>Client Requests</h1></div>
    <div id="reqList"></div>
  </section>

  <!-- NOTIFICATIONS -->
  <section class="page" id="page-notifications">
    <div class="page-lock-banner" id="notifBanner" style="display:none"></div>
    <div class="ph">
      <h1>Notifications</h1>
      <button class="btn-outline" onclick="guardedAction(markAllRead)">Mark All Read</button>
    </div>
    <div id="notifList"></div>
  </section>
  <!-- SHIPMENT TRACKING -->
<section class="page" id="page-shipment">
  <div class="ph">
    <div>
      <h1>Shipment Tracking</h1>
      <p class="sub">Update car shipping status for confirmed reservations</p>
    </div>
  </div>
  <div id="shipmentList">
    <div style="color:var(--muted);text-align:center;padding:40px">Loading...</div>
  </div>
</section>
<!-- CLIENT ALERTS -->
  <section class="page" id="page-alerts">
    <div class="ph">
      <div><h1>Client Alerts</h1><p class="sub">Clients looking for specific cars</p></div>
    </div>
    <div id="agentAlertsList">
      <div style="color:var(--muted);text-align:center;padding:40px">Loading...</div>
    </div>
  </section>
  <!-- PAYMENT INFO -->
<section class="page" id="page-payment">
  <div class="ph">
    <div><h1>Payment Info</h1><p class="sub">Bank details shown to clients when reserving</p></div>
    <button class="btn-gold" onclick="savePaymentInfo()">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/></svg>
      Save
    </button>
  </div>

  <!-- Preview Card -->
  <div class="card" style="margin-bottom:1.25rem;background:linear-gradient(135deg,rgba(212,168,67,.08),var(--s1));border-color:rgba(212,168,67,.25);">
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:1.1rem;">
      <div style="width:38px;height:38px;border-radius:10px;background:rgba(212,168,67,.15);display:grid;place-items:center;color:var(--gold);">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
      </div>
      <div>
        <div style="font-family:'Barlow Condensed',sans-serif;font-weight:700;font-size:1rem;">Client Payment Preview</div>
        <div style="font-size:.75rem;color:var(--muted);">This is what clients will see</div>
      </div>
    </div>
    <div style="background:rgba(0,0,0,.2);border-radius:10px;padding:1rem 1.2rem;display:flex;flex-direction:column;gap:.6rem;" id="payPreview">
      <div style="font-size:.78rem;color:var(--muted);text-align:center;">Fill in the fields below to see preview</div>
    </div>
  </div>

  <!-- Form -->
  <div class="card">
    <h3 class="sec-title" style="display:flex;align-items:center;gap:8px;">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--gold)" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
      CCP Bank Details
    </h3>
    <div class="frow">
      <div class="fg">
        <label>Account Number</label>
        <input type="text" class="fi" id="piAccount" placeholder="e.g. 0021456789" oninput="updatePayPreview()"/>
      </div>
      <div class="fg">
        <label>RIP Number</label>
        <input type="text" class="fi" id="piRip" placeholder="e.g. 007999990012345678" oninput="updatePayPreview()"/>
      </div>
    </div>
    <div class="frow">
      <div class="fg">
        <label>Account Owner Name</label>
        <input type="text" class="fi" id="piOwner" placeholder="e.g. Ahmed Benali" oninput="updatePayPreview()"/>
      </div>
      <div class="fg">
        <label>Deposit Amount (DZD)</label>
        <input type="number" class="fi" id="piDeposit" placeholder="e.g. 50000" oninput="updatePayPreview()"/>
      </div>
    </div>
  </div>
</section>
  <!-- SUBSCRIPTION -->
  <section class="page" id="page-subscription">
    <div class="ph">
      <div><h1>Subscription</h1><p class="sub">All prices in Algerian Dinar (DZD)</p></div>
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
        <button class="btn-outline full" onclick="openPayChoiceModal('Starter',1900)">Subscribe</button>
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
        <button class="btn-gold full" onclick="openPayChoiceModal('Pro',4900)">Subscribe</button>
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
        <button class="btn-outline full" onclick="openPayChoiceModal('Business',9900)">Subscribe</button>
      </div>
    </div>
    <div class="card" style="margin-top:2rem">
      <div class="ch"><h3>Current Subscription Status</h3></div>
      <div class="sub-rows" id="subRows">
        <div class="sr"><span>Plan</span><strong id="siPlan">Trial</strong></div>
        <div class="sr"><span>Status</span><strong id="siStatus">Trial Active</strong></div>
        <div class="sr"><span>Days Remaining</span><strong id="siDaysLeft">—</strong></div>
        <div class="sr"><span>Start</span><strong id="siStart">—</strong></div>
        <div class="sr"><span>Expiry</span><strong id="siExpiry">—</strong></div>
      </div>
    </div>
  </section>

  <!-- PROFILE -->
  <section class="page" id="page-profile">
    <div class="ph"><h1>My Profile</h1></div>
    <div class="prof-layout">
      <div class="card prof-left">
        <div class="pav-wrap">
          <div class="pav" id="pavDisplay">
            <svg width="38" height="38" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
          </div>
          <label class="pav-cam" for="avatarFile">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
          </label>
          <input type="file" id="avatarFile" accept="image/*" onchange="uploadAvatar(event)" style="display:none"/>
        </div>
        <div class="pn" id="pNameDisp"><?= htmlspecialchars($user['first_name'].' '.$user['last_name']) ?></div>
        <div class="status-tag" id="pStatusTag" data-s="<?= $user['status'] ?>"><?= ucfirst($user['status']) ?> </div>
        <div class="p-mini-stats">
          <div><div class="pms-n" id="pmsCars">6</div><div class="pms-l">Cars</div></div>
          <div><div class="pms-n" id="pmsReq">3</div><div class="pms-l">Requests</div></div>
          <div><div class="pms-n">142</div><div class="pms-l">Views</div></div>
        </div>
      </div>
      <div class="card">
        <h3 class="sec-title">Account Information</h3>
        <div class="fg"><label>Full Name</label><input type="text" class="fi" id="pName"value="<?= htmlspecialchars($user['first_name'].' '.$user['last_name']) ?>" /></div>
        <div class="fg"><label>Phone</label><input type="tel" class="fi" id="pPhone"value="<?= htmlspecialchars($user['phone']) ?>" /></div>
        <div class="fg"><label>Email</label><input type="email" class="fi" id="pEmail"value="<?= htmlspecialchars($user['email']) ?>" /></div>
        <div class="fg"><label>Account Status</label><input type="text" class="fi" id="pStatInp" value="<?= htmlspecialchars($user['status']) ?>" readonly/></div>
        <button class="btn-gold" onclick="saveProfile()">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
          Save
        </button>
      </div>
    </div>
  </section>
</main>

<!-- CAR MODAL -->
<div class="ov" id="carOv">
  <div class="modal">
    <div class="mhd">
      <h2 id="carMTitle">Add Car</h2>
      <button class="cls-btn" onclick="closeOv('carOv')"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
    </div>
    <div class="mbody">
      <div class="fg"><label>Title</label><input type="text" class="fi" id="cTitle" placeholder="e.g. MG5 2023 — Chery Tiggo 8"/></div>
      <div class="frow">
        <div class="fg"><label>Price (DZD)</label><input type="number" class="fi" id="cPrice" placeholder="2,500,000"/></div>
        <div class="fg"><label>Status</label>
          <select class="fi" id="cStatus"><option value="available">Available</option><option value="sold">Sold</option></select>
        </div>
      </div>
      <div class="frow">
        <div class="fg"><label>Brand</label>
          <select class="fi" id="cBrand">
            <option>MG</option><option>Chery</option><option>Livan</option><option>BYD</option><option>Geely</option><option>Haval</option><option>JAC</option><option>BAIC</option><option>jetour</option>
          </select>
        </div>
        <div class="fg"><label>Year</label><input type="number" class="fi" id="cYear" placeholder="2023" min="2000" max="2026"/></div>
      </div>
      <div class="frow">
  <div class="fg"><label>Fuel Type</label>
    <select class="fi" id="cFuel">
      <option value="">-- Select --</option>
      <option>Essence</option><option>Diesel</option>
      <option>Hybrid</option><option>Electric</option>
      <option>Plug-in Hybrid</option>
    </select>
  </div>
  <div class="fg"><label>Body Type</label>
    <select class="fi" id="cBodyType">
      <option value="">-- Select --</option>
      <option>SUV</option><option>Sedan</option><option>Hatchback</option>
      <option>Pickup</option><option>Van</option><option>Crossover</option><option>MPV</option>
    </select>
  </div>
</div>

<div class="frow">
  <div class="fg"><label>Transmission</label>
    <select class="fi" id="cTransmission">
      <option value="">-- Select --</option>
      <option>Automatic</option><option>Manual</option><option>CVT</option><option>DCT</option>
    </select>
  </div>
  <div class="fg"><label>Drive</label>
    <select class="fi" id="cDrive">
      <option value="">-- Select --</option>
      <option>FWD</option><option>RWD</option><option>AWD</option><option>4WD</option>
    </select>
  </div>
</div>

<div class="frow">
  <div class="fg"><label>Seats</label>
    <select class="fi" id="cSeats">
      <option value="">-- Select --</option>
      <option>2</option><option>4</option><option>5</option>
      <option>6</option><option>7</option><option>8</option><option>9</option>
    </select>
  </div>
  <div class="fg"><label>Mileage (km)</label>
    <input type="number" class="fi" id="cMileage" placeholder="0 if new">
  </div>
</div>

<div class="frow">
  <div class="fg"><label>Exterior Color</label>
    <select class="fi" id="cColorExt">
      <option value="">-- Select --</option>
      <option>White</option><option>Black</option><option>Silver</option>
      <option>Gray</option><option>Red</option><option>Blue</option>
      <option>Navy Blue</option><option>Green</option><option>Gold</option>
      <option>Champagne</option><option>Brown</option><option>Orange</option>
    </select>
  </div>
  <div class="fg"><label>Interior Color</label>
    <select class="fi" id="cColorInt">
      <option value="">-- Select --</option>
      <option>Black</option><option>Beige</option><option>Gray</option>
      <option>Brown</option><option>Cream</option><option>Cognac</option>
    </select>
  </div>
</div>

<div class="fg"><label>Wilaya</label>
  <select class="fi" id="cWilaya">
    <option value="">-- Select --</option>
    <option>Alger</option><option>Oran</option><option>Constantine</option>
    <option>Annaba</option><option>Blida</option><option>Sétif</option>
    <option>Tizi Ouzou</option><option>Béjaïa</option><option>Batna</option>
    <option>Tlemcen</option><option>Adrar</option><option>Chlef</option>
    <option>Laghouat</option><option>Oum El Bouaghi</option><option>Biskra</option>
    <option>Béchar</option><option>Bouira</option><option>Tamanrasset</option>
    <option>Tébessa</option><option>Tiaret</option><option>Djelfa</option>
    <option>Jijel</option><option>Saïda</option><option>Skikda</option>
    <option>Sidi Bel Abbès</option><option>Guelma</option><option>Médéa</option>
    <option>Mostaganem</option><option>M'Sila</option><option>Mascara</option>
    <option>Ouargla</option><option>El Bayadh</option><option>Illizi</option>
    <option>Bordj Bou Arréridj</option><option>Boumerdès</option><option>El Tarf</option>
    <option>Tindouf</option><option>Tissemsilt</option><option>El Oued</option>
    <option>Khenchela</option><option>Souk Ahras</option><option>Tipaza</option>
    <option>Mila</option><option>Aïn Defla</option><option>Naâma</option>
    <option>Aïn Témouchent</option><option>Ghardaïa</option><option>Relizane</option>
    <option>Timimoun</option><option>Bordj Badji Mokhtar</option><option>Ouled Djellal</option>
    <option>Béni Abbès</option><option>In Salah</option><option>In Guezzam</option>
    <option>Touggourt</option><option>Djanet</option><option>El M'Ghair</option>
    <option>El Menia</option>
  </select>
</div>
      <div class="fg"><label>Description</label><textarea class="fi" id="cDesc" rows="3" placeholder="Details, condition, features..."></textarea></div>
      <div class="fg">
        <label>Photos</label>
        <div class="upzone" onclick="document.getElementById('cImgs').click()">
          <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
          <p>Click to upload</p>
          <input type="file" id="cImgs" accept="image/*" multiple onchange="previewCarImgs(event)" style="display:none"/>
        </div>
        <div class="iprev" id="imgPrev"></div>
      </div>
    </div>
    <div class="mft">
  <button class="btn-outline" onclick="closeOv('carOv')">Cancel</button>
  <button class="btn-gold" onclick="goToStep2()">Next →</button>
</div>
  </div>
</div>
<!-- CAR MODAL STEP 2 -->
<div class="ov" id="carOv2">
  <div class="modal">
    <div class="mhd">
      <h2>More Details</h2>
      <button class="cls-btn" onclick="closeOv('carOv2')">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
        </svg>
      </button>
    </div>
    <div class="mbody">

      <!-- معاينة الصور مع zoom -->
      <div class="fg">
        <label style="margin-bottom:8px;display:block">Photos Preview (tap to zoom)</label>
        <div id="step2PhotosPreview" style="display:flex;flex-wrap:wrap;gap:8px;"></div>
      </div>

      <!-- مواصفات حرة -->
      <div class="frow" style="margin-top:16px">
  <div class="fg">
    <label>Engine</label>
    <input type="text" class="fi" id="cEngine" placeholder="e.g. 1.5T Turbo"/>
  </div>
  <div class="fg">
    <label>Power</label>
    <input type="text" class="fi" id="cPower" placeholder="e.g. 169 HP"/>
  </div>
</div>

<div class="frow">
  <div class="fg">
    <label>Consumption</label>
    <input type="text" class="fi" id="cConsumption" placeholder="e.g. 7.5L/100km"/>
  </div>
  <div class="fg">
    <label>Delivery</label>
    <input type="text" class="fi" id="cDelivery" placeholder="e.g. 2-4 weeks"/>
  </div>
</div>

<div class="fg">
  <label>Other Specs (one per line: Label: Value)</label>
  <textarea class="fi" id="cSpecs" rows="4"
    placeholder="Warranty: 5 years&#10;..."></textarea>
</div>
      <!-- رقم الهاتف -->
      <div class="fg">
        <label>Contact Phone</label>
        <input type="text" class="fi" id="cPhone" placeholder="e.g. 0551234567"/>
        <small style="color:var(--muted);font-size:.75rem;margin-top:4px;display:block">
          If different from your registered number, it will update your profile too.
        </small>
      </div>

    </div>
    <div class="mft">
      <button class="btn-outline" onclick="backToStep1()">← Back</button>
      <button class="btn-gold" onclick="saveCar()">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
          <polyline points="17 21 17 13 7 13 7 21"/>
        </svg>
        Save All
      </button>
    </div>
  </div>
</div>

<!-- LIGHTBOX للـ zoom -->
<div id="step2Lightbox" onclick="this.style.display='none'"
  style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.95);z-index:999;align-items:center;justify-content:center;">
  <img id="step2LightboxImg" style="max-width:90vw;max-height:90vh;border-radius:8px;object-fit:contain;">
</div>
<!-- REPLY MODAL -->
<div class="ov" id="replyOv">
  <div class="modal">
    <div class="mhd">
      <h2>Reply to Client</h2>
      <button class="cls-btn" onclick="closeOv('replyOv')"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
    </div>
    <div class="mbody">
      <div class="cli-box" id="cliBox"></div>
      <div class="fg" style="margin-top:1.2rem"><label>Your Reply</label><textarea class="fi" id="replyTxt" rows="4" placeholder="Write your message..."></textarea></div>
      <div class="act-row">
        <button class="btn-success" onclick="handleReq('accept')"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>Accept</button>
        <button class="btn-danger" onclick="handleReq('reject')"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>Reject</button>
      </div>
    </div>
    <div class="mft">
      <button class="btn-outline" onclick="closeOv('replyOv')">Cancel</button>
      <button class="btn-gold" onclick="sendReply()"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>Send</button>
    </div>
  </div>
</div>

<!-- DELETE MODAL -->
<div class="ov" id="delOv">
  <div class="modal modal-sm">
    <div class="mhd">
      <h2>Delete Car</h2>
      <button class="cls-btn" onclick="closeOv('delOv')"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
    </div>
    <div class="mbody" style="text-align:center;padding:2rem 1.5rem">
      <div class="del-ico"><svg width="42" height="42" viewBox="0 0 24 24" fill="none" stroke="#e74c3c" stroke-width="1.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg></div>
      <p>Delete <strong id="delName"></strong>?<br/><span style="color:#666;font-size:.85rem">This action cannot be undone.</span></p>
    </div>
    <div class="mft"><button class="btn-outline" onclick="closeOv('delOv')">Cancel</button><button class="btn-danger" onclick="confirmDel()">Delete</button></div>
  </div>
</div>

<!-- VIEWS MODAL -->
<div class="ov" id="viewsOv">
  <div class="modal">
    <div class="mhd">
      <h2>Views Analytics</h2>
      <button class="cls-btn" onclick="closeOv('viewsOv')"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
    </div>
    <div class="mbody">
      <div class="v-stats">
        <div><div class="vs-n">142</div><div class="vs-l">Total</div></div>
        <div><div class="vs-n">38</div><div class="vs-l">This Week</div></div>
        <div><div class="vs-n">12</div><div class="vs-l">Today</div></div>
      </div>
      <div class="vchart">
        <div class="vc-lbl">Daily Views — Last 7 Days</div>
        <div class="vc-bars" id="vcBars"></div>
        <div class="vc-days" id="vcDays"></div>
      </div>
      <div class="vt-title">Most Viewed Listings</div>
      <div id="vtList"></div>
    </div>
  </div>
</div>

<!-- NOTIF DETAIL MODAL -->
<div class="ov" id="notifOv">
  <div class="modal modal-sm">
    <div class="mhd">
      <h2 id="notifOvTitle">Notification</h2>
      <button class="cls-btn" onclick="closeOv('notifOv')"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
    </div>
    <div class="mbody"><div id="notifOvBody"></div></div>
    <div class="mft"><button class="btn-gold" onclick="closeOv('notifOv')">OK</button></div>
  </div>
</div>

<script>
/* ===================== STATE ===================== */
const S = {
  agent: {
  name: '<?= htmlspecialchars($user['first_name'].' '.$user['last_name']) ?>',
  phone: '<?= htmlspecialchars($user['phone']) ?>',
  email: '<?= htmlspecialchars($user['email']) ?>',
  avatar: null,
  status: '<?= htmlspecialchars($user['status']) ?>'
},
  cars:[],
  requests:[],
  notifs:[],
  sub:{plan:'Trial',status:'Trial Active',start:'—',expiry:'—',daysLeft:null,totalDays:null},
  trial:{secs:180,active:true},
  lock:{expired:false},
  carFilter:'all',
  searchQ:'',
  editId:null,
  delId:null,
  replyId:null,
  payPlan:null,
  payDuration:null,
  payMethod:null,
  newImgs:[],
};

const DURATIONS = {
  Starter:[
    {days:30,price:1900,label:'1 Month',save:null},
    {days:90,price:4900,label:'3 Months',save:'Save 900 DZD'},
    {days:180,price:8500,label:'6 Months',save:'Save 2,900 DZD'},
  ],
  Pro:[
    {days:30,price:4900,label:'1 Month',save:null},
    {days:90,price:12900,label:'3 Months',save:'Save 1,800 DZD'},
    {days:180,price:22000,label:'6 Months',save:'Save 7,400 DZD'},
  ],
  Business:[
    {days:90,price:9900,label:'3 Months',save:null},
    {days:180,price:17900,label:'6 Months',save:'Save 1,900 DZD'},
    {days:365,price:32000,label:'1 Year',save:'Save 7,600 DZD'},
  ],
};
function timeAgo(dateStr) {
  var date = new Date(dateStr);
  var now = new Date();
  var diff = Math.floor((now - date) / 1000);
  if (diff < 60) return 'Just now';
  if (diff < 3600) return Math.floor(diff/60) + ' min ago';
  if (diff < 86400) return Math.floor(diff/3600) + ' hr ago';
  return Math.floor(diff/86400) + ' days ago';
}
async function loadNotificationsFromDB() {
  try {
    var res = await fetch('api.php?action=get_agent_notifications&agent_id=<?= $_SESSION['user_id'] ?>');
    var data = await res.json();
    if (data.success && data.data) {
      S.notifs = data.data.items.map(function(n) {
        return {
          id: n.id,
          type: n.type || 'info',
          title: n.title,
          txt: n.message,
          time: timeAgo(n.created_at),
          unread: !n.is_read,
          actions: []
        };
      });
      var unread = data.data.unread;
      document.getElementById('nbNotif').textContent = unread;
      renderNotifs();
    }
  } catch(e) {}
}
/* ===================== INIT ===================== */
function renderAll(){
  refreshBadges();
  renderDash();
  loadCarsFromDB();
  fetch('check_visit.php').then(function(r){ return r.json(); }).then(function(data){ if(data.checked > 0) loadNotificationsFromDB(); });
  checkExpiredReservations();
  loadRequestsFromDB();  
  loadNotificationsFromDB();
  syncSidebar();
  updateLockState();
  var hasActiveSub = <?= ($subscription && $subscription['status'] !== 'rejected') ? 'true' : 'false' ?>;
<?php
$daysLeft = 0; $totalDays = 30;
if($subscription && !empty($subscription['end_date'])){
  $daysLeft = max(0, ceil((strtotime($subscription['end_date']) - time()) / 86400));
  $start = strtotime($subscription['start_date'] ?? $subscription['created_at']);
  $end = strtotime($subscription['end_date']);
  $totalDays = max(1, ceil(($end - $start) / 86400));
}
?>
S.sub.daysLeft = <?= $daysLeft ?>;
S.sub.totalDays = <?= $totalDays ?>;
S.sub.plan = '<?= htmlspecialchars($subscription["plan"] ?? "Pro") ?>';
S.sub.start = '<?= !empty($subscription['start_date']) ? date('d/m/Y', strtotime($subscription['start_date'])) : '—' ?>';
S.sub.expiry = '<?= !empty($subscription['end_date']) ? date('d/m/Y', strtotime($subscription['end_date'])) : '—' ?>';
S.sub.status = 'Active';
<?php
$testRemaining = 0;
if ($subscription && $subscription['status'] === 'approved' && !empty($subscription['test_started_at'])) {
    $durations = [30=>60, 90=>180, 180=>360, 365=>600];
    $totalSecs = $durations[$daysLeft] ?? 60;
    $elapsed = time() - strtotime($subscription['test_started_at']);
    $testRemaining = max(0, $totalSecs - $elapsed);
}
?>
S.testRemaining = <?= (int)$testRemaining ?>;
S.agent.status = 'active';
if (!hasActiveSub) {
    <?php
$trialUsed = !empty($user['trial_used']);
$trialExpired = false;
$trialRemaining = 0;
if ($trialUsed && !empty($user['trial_started_at'])) {
    $elapsed = time() - strtotime($user['trial_started_at']);
   $trialRemaining = max(0, (7 * 24 * 3600) - $elapsed);
    $trialExpired = ($trialRemaining <= 0);
}
?>
    var trialUsed = <?= $trialUsed ? 'true' : 'false' ?>;
    var trialExpired = <?= $trialExpired ? 'true' : 'false' ?>;
    var trialRemaining = <?= (int)$trialRemaining ?>;
    
    if (!trialUsed) {
    S.lock.expired = true;
    updateLockState();
    document.getElementById('lockOverlay').classList.add('hidden');
} else if (trialExpired) {
    lockSystem();
} else {
    startTrialCountdown(trialRemaining);
}
} else {
    document.getElementById('trialBox').querySelector('.tbox-top span').textContent = S.sub.plan;
    updateSubscriptionUI();
    syncSidebar();
    if (S.testRemaining > 0) {
    document.getElementById('trialTime').style.display = 'block';
    document.getElementById('tboxSub').style.display = 'none';
    startTrialCountdown(S.testRemaining);
}
}
}
/* ===================== ACCESS GUARD ===================== */
const FREE_PAGES=['subscription','profile'];
function isLocked(){
    // إذا locked ومش في صفحة subscription → blocked
    return S.lock.expired;
}
function guardedAction(fn){
  if(isLocked()){showLockOverlay();return;}
  fn();
}
function showLockOverlay(){document.getElementById('lockOverlay').classList.remove('hidden');}
function updateLockState(){
  var locked=S.lock.expired;
  document.querySelectorAll('.nl').forEach(function(el){
    var p=el.dataset.p;
    if(locked&&!FREE_PAGES.includes(p))el.classList.add('locked-nav');
    else el.classList.remove('locked-nav');
  });
  ['dash','cars','req','notif'].forEach(function(prefix){
    var banner=document.getElementById(prefix+'Banner');
    if(!banner)return;
    if(locked){
      banner.style.display='flex';
      banner.innerHTML='<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg><div class="plb-text"><div class="plb-title">Restricted Access</div><div class="plb-sub">Your trial has expired. Subscribe to access this section.</div></div><button class="btn-gold" onclick="showPage(\'subscription\')">Subscribe</button>';
      blurPageContent(prefix);
    }else{
      banner.style.display='none';
      unblurPageContent(prefix);
    }
  });
  updateSubscriptionUI();
}
function blurPageContent(prefix){
  var pageMap={dash:'dashboard',cars:'cars',req:'requests',notif:'notifications'};
  var page=document.getElementById('page-'+pageMap[prefix]);
  if(!page)return;
  Array.from(page.children).forEach(function(child){
    if(!child.classList.contains('page-lock-banner')&&!child.classList.contains('ph'))
      if (!child.classList.contains('cgrid')) {
  child.classList.add('locked-content');
}
  });
}

 function unblurPageContent(prefix){
  var pageMap={dash:'dashboard',cars:'cars',req:'requests',notif:'notifications'};
  var page=document.getElementById('page-'+pageMap[prefix]);
  if(!page)return;
  Array.from(page.children).forEach(function(child){child.classList.remove('locked-content');});
}
function updateSubscriptionUI(){
  var days=S.sub.daysLeft;
  document.getElementById('siDaysLeft').textContent=days!==null?days+' days':'—';
  if(days!==null){
    document.getElementById('trialTime').style.display='none';
    var sub=document.getElementById('tboxSub');
    sub.style.display='flex';
    document.getElementById('tboxDaysLeft').textContent=days+' days remaining';
    document.getElementById('tboxPlan').textContent=S.sub.plan;
    document.getElementById('trialLabel').textContent=S.sub.plan;
    var pct=S.sub.totalDays?Math.min(100,(days/S.sub.totalDays)*100):100;
    var fill=document.getElementById('trialFill');
    fill.style.width=pct+'%';
    if(pct<=20){fill.style.background='linear-gradient(90deg,#e74c3c,#c0392b)';document.getElementById('tboxDaysLeft').style.color='var(--red)';}
    else if(pct<=40){fill.style.background='linear-gradient(90deg,var(--gold),var(--gold2))';}
    else{fill.style.background='linear-gradient(90deg,var(--green),#27ae60)';}
  }
}

/* ===================== NAV ===================== */
function showPage(p){
  if(isLocked()&&!FREE_PAGES.includes(p)){
    showPage('subscription');
    return;
  }
  document.querySelectorAll('.page').forEach(function(x){x.classList.remove('active');});
  document.querySelectorAll('.nl').forEach(function(x){x.classList.remove('active');});
  var pg=document.getElementById('page-'+p);
  if(pg)pg.classList.add('active');
  var nl=document.querySelector('.nl[data-p="'+p+'"]');
  if(nl)nl.classList.add('active');
  if(window.innerWidth<=900)document.getElementById('sidebar').classList.remove('open');
  if(p==='dashboard')renderDash();
  if(p==='cars')renderCars();
  if(p==='requests')renderRequests();
  if(p==='notifications')renderNotifs();
  if(p==='payment')loadPaymentInfo();
}
function toggleSB(){document.getElementById('sidebar').classList.toggle('open');}
document.addEventListener('click',function(e){
  var sb=document.getElementById('sidebar');
  if(window.innerWidth<=900&&sb.classList.contains('open')&&!sb.contains(e.target)&&!e.target.closest('.burger'))
    sb.classList.remove('open');
});

/* ===================== TRIAL ===================== */
async function checkTrialStatus() {
  const token = localStorage.getItem('c2dz_token');
  const res = await fetch('api.php?action=get_trial_status', {
    headers: { 'Authorization': 'Bearer ' + token }
  });
  const data = await res.json();
  if (!data.success) return;
  const t = data.data;

  if (!t.trial_used) {
    // أول مرة — اعرض عرض التجربة
    showTrialOffer();
  } else if (t.expired) {
    // انتهت التجربة
    lockSystem();
  } else {
    // التجربة شغّالة
    startTrialCountdown(t.remaining_seconds);
  }
}

function showTrialOffer() {
  document.getElementById('lockOverlay').classList.remove('hidden');
  document.getElementById('lockOverlay').innerHTML = `
    <div class="lock-box" style="max-width:440px;">
      <div style="font-size:2.5rem;margin-bottom:12px;">🎉</div>
      <h2 style="margin-bottom:10px;">Welcome to China2DZ!</h2>
      <p style="color:var(--muted);margin-bottom:8px;line-height:1.6;">
        As a new agent, you get a <strong style="color:var(--gold);">7-day free trial</strong> 
        to explore all features — no payment required.
      </p>
      <p style="color:var(--muted);font-size:.82rem;margin-bottom:24px;">
        Your trial starts the moment you accept. You can come back later if you're not ready yet.
      </p>
      <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
        <button class="btn-gold" onclick="acceptTrial()" style="padding:.75rem 2rem;font-size:.95rem;">
          ✅ Start My Free Trial
        </button>
        <button class="btn-outline" onclick="maybeLater()" 
                style="padding:.75rem 1.5rem;">
          Maybe Later
        </button>
      </div>
    </div>`;
}

async function acceptTrial() {
  const res = await fetch('start_trial.php', {
    method: 'POST'
  });
  const data = await res.json();
  if (data.success) {
    document.getElementById('lockOverlay').classList.add('hidden');
    S.lock.expired = false;
    // أزيلي locked-nav من كل الأزرار
    document.querySelectorAll('.nl').forEach(function(el) {
        el.classList.remove('locked-nav');
    });
    startTrialCountdown(data.remaining_seconds);
    toast('Your 7-day free trial has started! 🎉', 'ok');
  }
}

var trialInterval = null;

function startTrialCountdown(totalRemaining) {
  if (trialInterval) clearInterval(trialInterval);
  var secs = totalRemaining;
  var total = secs;

  function tick() {
    secs--;
    if (secs < 0) { lockSystem(); return; }
    var m = Math.floor(secs / 60), s = secs % 60;
    var el = document.getElementById('trialTime');
    if (el) el.textContent = String(m).padStart(2,'0') + ':' + String(s).padStart(2,'0');
    document.getElementById('trialFill').style.width = ((secs / total) * 100) + '%';
    if (secs <= 60) {
      document.getElementById('trialFill').style.background = 'linear-gradient(90deg,#e74c3c,#c0392b)';
      var t2 = document.getElementById('trialTime');
      if (t2) t2.style.color = '#e74c3c';
    }
  }

  tick();
  trialInterval = setInterval(tick, 1000);
}
function maybeLater() {
    document.getElementById('lockOverlay').classList.add('hidden');
    S.lock.expired = true;
    document.querySelectorAll('.nl').forEach(function(el) {
        var p = el.dataset.p;
        if (p !== 'subscription') {
            el.classList.add('locked-nav');
        }
    });
    // عطّلي guardedAction على كل شي إلا subscription
    showPage('subscription');
}
function lockSystem() {
    S.trial.active = false;
    S.agent.status = 'blocked';
    S.lock.expired = true;
    syncSidebar();
    updateLockState();
    document.getElementById('lockOverlay').classList.remove('hidden');
    document.getElementById('lockOverlay').innerHTML = `
    <div class="lock-box">
        <div class="lock-ico">
            <svg width="52" height="52" viewBox="0 0 24 24" fill="none" stroke="#d4a843" stroke-width="1.5">
                <rect x="3" y="11" width="18" height="11" rx="2"/>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            </svg>
        </div>
        <h2>Trial Expired</h2>
        <p>Your free trial has ended.<br/>Subscribe to continue using China2DZ.</p>
        <button class="btn-gold" onclick="goSub()" style="margin-top:8px;">
            View Plans
        </button>
    </div>`;
}
function goSub() {
    if (trialInterval) clearInterval(trialInterval);
    document.getElementById('lockOverlay').classList.add('hidden');
    S.lock.expired = false;
    showPage('subscription');
}

/* ===================== SIDEBAR SYNC ===================== */
function syncSidebar(){
  var name=S.agent.name,avatar=S.agent.avatar,status=S.agent.status;
  document.getElementById('sbName').textContent=name;
  document.getElementById('welcomeMsg').textContent='Welcome, '+name.split(' ')[0];
  document.getElementById('pNameDisp').textContent=name;
  if(avatar){
    var avImg='<img src="'+avatar+'" alt=""/>';
    ['sbAv','topAv','pavDisplay'].forEach(function(id){
      var el=document.getElementById(id);
      if(el)el.innerHTML=avImg;
    });
  }
  var stMap={trial:'Trial',active:'Active',pending:'Pending',blocked:'Blocked'};
  document.querySelectorAll('.status-tag').forEach(function(el){
    el.textContent=stMap[status]||'Trial';
    el.dataset.s=status;
  });
  document.getElementById('siPlan').textContent=S.sub.plan;
  document.getElementById('siStatus').textContent=S.sub.status;
  document.getElementById('siStart').textContent=S.sub.start;
  document.getElementById('siExpiry').textContent=S.sub.expiry;
  document.getElementById('pStatInp').value=stMap[status]||'Trial';
}

/* ===================== BADGES ===================== */
function refreshBadges(){
  var pend=S.requests.filter(function(r){return r.status==='pending';}).length;
  var unread=S.notifs.filter(function(n){return n.unread;}).length;
  var e=function(id){return document.getElementById(id);};
  if(e('nbCars')) e('nbCars').textContent=S.cars.length;
  if(e('nbReq')) e('nbReq').textContent=pend;
  if(e('nbNotif')) e('nbNotif').textContent=unread;
  if(e('dCars')) e('dCars').textContent=S.cars.length;
  if(e('dReq')) e('dReq').textContent=pend;
  if(e('dMsg')) e('dMsg').textContent=S.requests.length;
  if(e('pmsCars')) e('pmsCars').textContent=S.cars.length;
  if(e('pmsReq')) e('pmsReq').textContent=pend;
}

/* ===================== DASHBOARD ===================== */
function renderDash(){
  refreshBadges();
  var cl=document.getElementById('dCarsList');
  if(cl){
    cl.innerHTML=S.cars.slice(0,5).map(function(c){
      return '<div class="dli" onclick="guardedAction(function(){showPage(\'cars\')})">'
        +'<div class="dli-thumb">'+(c.imgs.length?'<img src="'+c.imgs[0]+'" alt=""/>':'<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M5 17H3a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11a2 2 0 0 1 2 2v3"/><rect x="9" y="11" width="14" height="10" rx="1"/><circle cx="12" cy="21" r="1"/><circle cx="20" cy="21" r="1"/></svg>')+'</div>'
        +'<div class="dli-info"><div class="dli-title">'+c.title+'</div><div class="dli-price">'+fmt(c.price)+' DZD</div></div>'
        +'<span class="cst '+c.status+'">'+(c.status==='available'?'Available':'Sold')+'</span>'
        +'</div>';
    }).join('');
  }
  var rl=document.getElementById('dReqList');
  if(rl){
    var pr=S.requests.filter(function(r){return r.status==='pending';}).slice(0,3);
    rl.innerHTML=pr.length?pr.map(function(r){
      return '<div class="dli" onclick="guardedAction(function(){openReplyOv('+r.id+')})">'
        +'<div class="req-av" style="width:38px;height:38px;border-radius:9px;font-size:.8rem;flex-shrink:0">'+r.initials+'</div>'
        +'<div class="dli-info"><div class="dli-title">'+r.client+'</div><div class="dli-sub">'+r.carTitle+'</div></div>'
        +'<span class="req-st pending">Pending</span>'
        +'</div>';
    }).join(''):'<p style="color:var(--muted);font-size:.83rem;padding:.5rem 0">No pending requests</p>';
  }
}
async function loadCarsFromDB() {
  try {
    var res = await fetch('save_car.php?action=get&agent_id=<?= $_SESSION['user_id'] ?>');
    var data = await res.json();
    if (data.success) {
      S.cars = (data.cars || []).map(function(c) {
  c.reservation_status = c.reservation_status || null;
  return c;
});
      refreshBadges();
      renderDash();
      renderCars();
    }
  } catch(e) {}
}
async function loadRequestsFromDB() {
  try {
    var res = await fetch('api.php?action=get_agent_requests&agent_id=<?= $_SESSION['user_id'] ?>');
    var data = await res.json();
    if (data.success && data.data) {
      S.requests = data.data.map(function(r) {
        return {
          id: r.id,
          reservation_id: r.reservation_id || null,
          client_id: r.client_id,
          client: r.client_name,
          carTitle: r.car_title || '—',
          msg: r.message || '',
          time: timeAgo(r.created_at),
          status: r.status || 'pending',
          unread: !r.is_read,
          initials: r.client_name ? r.client_name.charAt(0).toUpperCase() : '?'
        };
      });
      refreshBadges();
      renderRequests();
    }
  } catch(e) {}
}
/* ===================== CARS ===================== */
function renderCars(){
  var grid=document.getElementById('carsGrid');
  if(!grid)return;
  var cars=[].concat(S.cars);
  if(S.carFilter!=='all')cars=cars.filter(function(c){return c.status===S.carFilter;});
  if(S.searchQ)cars=cars.filter(function(c){return c.title.toLowerCase().includes(S.searchQ)||c.brand.toLowerCase().includes(S.searchQ);});
  if(!cars.length){grid.innerHTML='<div style="grid-column:1/-1;text-align:center;padding:3rem;color:var(--muted)"><p>No cars found</p></div>';return;}
  grid.innerHTML=cars.map(function(c){
    return '<div class="ccard">'
       +(c.imgs.length?'<img class="cimg" src="'+c.imgs[0]+'" alt="'+c.title+'" style="cursor:pointer;" onclick="window.location.href=\'index.php#car-'+c.id+'\'"  />':'<div class="cimg-ph" style="cursor:pointer;" onclick="window.location.href=\'car.php?id='+c.id+'\'"><svg width="52" height="52" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><path d="M5 17H3a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11a2 2 0 0 1 2 2v3"/><rect x="9" y="11" width="14" height="10" rx="1"/><circle cx="12" cy="21" r="1"/><circle cx="20" cy="21" r="1"/></svg></div>')
      +'<div class="cbody">'
      +'<div class="ctitle">'+c.title+'</div>'
      +'<div class="cprice">'+fmt(c.price)+' DZD</div>'
      +'<div class="cdesc">'+c.desc+'</div>'
      +'<span class="cst '+(c.reservation_status==='reserved'?'reserved':c.status)+'">'+(c.reservation_status==='reserved'?'Reserved':c.status==='available'?'Available':'Sold')
      +'<div class="cacts">'
      +'<button class="btn-outline btn-sm" onclick="guardedAction(function(){openCarModal('+c.id+')})">'
      +'<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>Edit</button>'
      +'<button class="btn-danger btn-sm" onclick="guardedAction(function(){openDelOv('+c.id+')})">'
      +'<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/></svg></button>'
      +'<button class="btn-gold btn-sm" onclick="guardedAction(function(){toggleSold('+c.id+')})">'
      +(c.status==='available'?'<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>Mark Sold':'<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-4"/></svg>Relist')
      +'</button>'
      +'</div></div></div>';
  }).join('');
}
function filterCars(f,btn){
  S.carFilter=f;
  document.querySelectorAll('.fb').forEach(function(b){b.classList.remove('active');});
  btn.classList.add('active');
  renderCars();
}
function searchCars(q){S.searchQ=q.toLowerCase();renderCars();}
function toggleSold(id){
  var c=S.cars.find(function(x){return x.id===id;});
  if(!c)return;
  c.status=c.status==='available'?'sold':'available';
  renderCars();renderDash();
  fetch('save_car.php?action=toggle_status', {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify({car_id: id, status: c.status})
  }).then(r => r.json()).then(res => {
    if(res.success) toast(c.title+' — '+(c.status==='sold'?'Marked as sold':'Relisted'),'ok');
    else toast('Status update failed','err');
  });
}

/* ===================== CAR MODAL ===================== */
function openCarModal(id){
  id=id||null;
  S.editId=id;S.newImgs=[];
  document.getElementById('imgPrev').innerHTML='';
  document.getElementById('cImgs').value='';
  if(id){
    var c=S.cars.find(function(x){return x.id===id;});
    document.getElementById('carMTitle').textContent='Edit Car';
    document.getElementById('cTitle').value=c.title;
    document.getElementById('cPrice').value=c.price;
    document.getElementById('cStatus').value=c.status;
    document.getElementById('cBrand').value=c.brand;
    document.getElementById('cYear').value=c.year;
    document.getElementById('cDesc').value=c.desc;
    if(c.imgs.length)document.getElementById('imgPrev').innerHTML=c.imgs.map(function(s){return '<img src="'+s+'"/>';}).join('');
  }else{
    document.getElementById('carMTitle').textContent='Add Car';
    ['cTitle','cPrice','cYear','cDesc'].forEach(function(i){document.getElementById(i).value='';});
    document.getElementById('cStatus').value='available';
  }
  openOv('carOv');
}
function previewCarImgs(ev){
  var files=Array.from(ev.target.files);
  var prev=document.getElementById('imgPrev');
  prev.innerHTML='';S.newImgs=[];
  files.forEach(function(f){
    var r=new FileReader();
    r.onload=function(e){
      S.newImgs.push(e.target.result);
      var img=document.createElement('img');
      img.src=e.target.result;
      prev.appendChild(img);
    };
    r.readAsDataURL(f);
  });
}
function goToStep2() {
  if (!document.getElementById('cTitle').value.trim()) { alert('Please enter a title'); return; }
  if (!document.getElementById('cPrice').value) { alert('Please enter a price'); return; }

  // اعرضي الصور من Step 1
  const prev = document.getElementById('step2PhotosPreview');
  prev.innerHTML = '';
  const files = document.getElementById('cImgs').files;
  if (files.length > 0) {
    Array.from(files).forEach(file => {
      const img = document.createElement('img');
      img.src = URL.createObjectURL(file);
      img.style.cssText = 'width:80px;height:64px;object-fit:cover;border-radius:7px;border:2px solid var(--br);cursor:zoom-in;';
      img.onclick = () => {
        document.getElementById('step2LightboxImg').src = img.src;
        document.getElementById('step2Lightbox').style.display = 'flex';
      };
      prev.appendChild(img);
    });
  } else {
    prev.innerHTML = '<span style="color:var(--muted);font-size:.8rem">No photos uploaded yet</span>';
  }

  // حطي رقم الهاتف الحالي
  document.getElementById('cPhone').value = '<?= htmlspecialchars($user["phone"] ?? "") ?>';

  closeOv('carOv');
  setTimeout(() => document.getElementById('carOv2').classList.add('open'), 200);
}

function backToStep1() {
  document.getElementById('carOv2').classList.remove('open');
  setTimeout(() => document.getElementById('carOv').classList.add('open'), 200);
}

function openOv(id) {
  document.getElementById(id).classList.add('open');
}

function previewCarImgs2(event) {
  const prev = document.getElementById('imgPrev2');
  prev.innerHTML = '';
  Array.from(event.target.files).forEach(file => {
    const img = document.createElement('img');
    img.src = URL.createObjectURL(file);
    prev.appendChild(img);
  });
}
function saveCar(){
  var title=document.getElementById('cTitle').value.trim();
  var price=parseFloat(document.getElementById('cPrice').value);
  if(!title||!price){toast('Please fill in the title and price','err');return;}
  var data={title:title,price:price,status:document.getElementById('cStatus').value,brand:document.getElementById('cBrand').value,year:parseInt(document.getElementById('cYear').value)||2023,desc:document.getElementById('cDesc').value.trim()};
  if(S.editId){
    var c=S.cars.find(function(x){return x.id===S.editId;});
    Object.assign(c,data);
    if(S.newImgs.length)c.imgs=S.newImgs;
    var formData = new FormData();
    formData.append('car_id', S.editId);
    formData.append('title', data.title);
    formData.append('price', data.price);
    formData.append('status', data.status);
    formData.append('brand', data.brand);
    formData.append('year', data.year);
    formData.append('desc', data.desc);
    formData.append('fuel_type', document.getElementById('cFuel').value);
    formData.append('body_type', document.getElementById('cBodyType').value);
    formData.append('transmission', document.getElementById('cTransmission').value);
    formData.append('drive_type', document.getElementById('cDrive').value);
    formData.append('seats', document.getElementById('cSeats').value);
    formData.append('mileage', document.getElementById('cMileage').value || 0);
    formData.append('color_ext', document.getElementById('cColorExt').value);
    formData.append('color_int', document.getElementById('cColorInt').value);
    formData.append('wilaya', document.getElementById('cWilaya').value);
    formData.append('specs_text', document.getElementById('cSpecs').value);
formData.append('contact_phone', document.getElementById('cPhone').value);
    formData.append('engine',      document.getElementById('cEngine')?.value || '');
formData.append('power',       document.getElementById('cPower')?.value || '');
formData.append('consumption', document.getElementById('cConsumption')?.value || '');
formData.append('delivery',    document.getElementById('cDelivery')?.value || '');
formData.append('duty_free',   document.getElementById('cDutyFree')?.value || '0');

// الصور الإضافية
const imgs2 = document.getElementById('cImgs2').files;
for (let i = 0; i < imgs2.length; i++) {
  formData.append('photos2[]', imgs2[i]);
}
    var files = document.getElementById('cImgs').files;
    for(var i = 0; i < files.length; i++){
        formData.append('photos[]', files[i]);
    }
    fetch('save_car.php?action=update', {
    method: 'POST',
    body: formData
}).then(r => r.json()).then(res => {
    if(res.success) {
        toast('Car updated!','ok');
        loadCarsFromDB();
    }
    else toast('Update failed','err');
});
}
  else{
    var formData = new FormData();
     formData.append('title', data.title);
     formData.append('price', data.price);
     formData.append('status', data.status);
     formData.append('brand', data.brand);
     formData.append('year', data.year);
     formData.append('desc', data.desc);
     formData.append('fuel_type', document.getElementById('cFuel').value);
     formData.append('body_type', document.getElementById('cBodyType').value);
     formData.append('transmission', document.getElementById('cTransmission').value);
     formData.append('drive_type', document.getElementById('cDrive').value);
     formData.append('seats', document.getElementById('cSeats').value);
     formData.append('mileage', document.getElementById('cMileage').value || 0);
     formData.append('color_ext', document.getElementById('cColorExt').value);
     formData.append('color_int', document.getElementById('cColorInt').value);
     formData.append('wilaya', document.getElementById('cWilaya').value);
     formData.append('specs_text', document.getElementById('cSpecs').value);
formData.append('contact_phone', document.getElementById('cPhone').value);
formData.append('engine',      document.getElementById('cEngine')?.value || '');
formData.append('power',       document.getElementById('cPower')?.value || '');
formData.append('consumption', document.getElementById('cConsumption')?.value || '');
formData.append('delivery',    document.getElementById('cDelivery')?.value || '');
formData.append('duty_free',   document.getElementById('cDutyFree')?.value || '0');
// أضيفي الصور
var files = document.getElementById('cImgs').files;
for(var i = 0; i < files.length; i++){
    formData.append('photos[]', files[i]);
}

fetch('save_car.php', {
    method: 'POST',
    body: formData
})
.then(r => r.json())
.then(res => {
    if(res.success) {
        toast('Saved to database!', 'ok');
        loadCarsFromDB();
    }
});
  }
  closeOv('carOv');renderCars();renderDash();refreshBadges();
}

/* ===================== DELETE ===================== */
function openDelOv(id){
  S.delId=id;
  var c=S.cars.find(function(x){return x.id===id;});
  document.getElementById('delName').textContent=c.title;
  openOv('delOv');
}
function confirmDel(){
  S.cars=S.cars.filter(function(x){return x.id!==S.delId;});
  closeOv('delOv');renderCars();renderDash();refreshBadges();
  fetch('save_car.php?action=delete&car_id='+S.delId)
    .then(r => r.json())
    .then(res => {
      if(res.success) {
          toast('Car deleted','inf');
          loadCarsFromDB();
      }
      else toast('Delete failed','err');
    });
}

/* ===================== REQUESTS ===================== */
function renderRequests(){
  var el=document.getElementById('reqList');
  if(!el)return;
  if(!S.requests.length){el.innerHTML='<div style="text-align:center;padding:3rem;color:var(--muted)"><p>No requests</p></div>';return;}
  el.innerHTML=S.requests.map(function(r){
    return '<div class="req-card '+(r.unread?'unread':'')+'">'
      +'<div class="req-av">'+r.initials+'</div>'
      +'<div class="req-info">'
      +'<div class="req-name">'+r.client+'</div>'
      +'<div class="req-car">'+r.carTitle+'</div>'
      +'<div class="req-msg">'+r.msg+'</div>'
      +'<div class="req-time">'+r.time+'</div>'
      +'</div>'
      +'<div class="req-acts">'
      +'<span class="req-st '+r.status+'">'+(r.status==='pending'?'Pending':r.status==='accepted'?'Accepted':'Rejected')+'</span>'
    +(r.status==='pending'?
  '<button class="btn-success btn-sm" onclick="quickAct('+r.id+',\'accept\')"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>Accept</button>'
  +'<button class="btn-danger btn-sm" onclick="quickAct('+r.id+',\'reject\')"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>Reject</button>'
  :'')
+'<button class="btn-outline btn-sm" style="border-color:var(--purple);color:var(--purple);" onclick="openReservationDetail('+r.id+')">'
+'<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>View Booking</button>'
      +'<button class="btn-outline btn-sm" onclick="openReplyOv('+r.id+')">'
      +'<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 17 4 12 9 7"/><path d="M20 18v-2a4 4 0 0 0-4-4H4"/></svg>Reply</button>'
      +'</div></div>';
  }).join('');
}
function quickAct(id,act){
  var r=S.requests.find(function(x){return x.id===id;});
  if(!r)return;
  r.status=act==='accept'?'accepted':'rejected';
  r.unread=false;
  toast(act==='accept'?'Request accepted!':'Request rejected.',act==='accept'?'ok':'inf');
  renderRequests();renderDash();refreshBadges();
}
function openReplyOv(id){
  S.replyId=id;
  var r=S.requests.find(function(x){return x.id===id;});
  if(!r)return;
  r.unread=false;
  document.getElementById('cliBox').innerHTML='<div class="cli-name">'+r.client+'</div><div class="cli-car">'+r.carTitle+'</div><div class="cli-msg">"'+r.msg+'"</div>';
  document.getElementById('replyTxt').value='';
  openOv('replyOv');
  renderRequests();refreshBadges();
}
function handleReq(act){
  var r=S.requests.find(function(x){return x.id===S.replyId;});
  if(!r)return;
  r.status=act==='accept'?'accepted':'rejected';
  toast(act==='accept'?'Request accepted!':'Request rejected.',act==='accept'?'ok':'inf');
  closeOv('replyOv');renderRequests();renderDash();refreshBadges();
}
function sendReply(){
  var txt=document.getElementById('replyTxt').value.trim();
  if(!txt){toast('Write a reply first','err');return;}
  toast('Reply sent!','ok');
  closeOv('replyOv');
}

/* ===================== NOTIFICATIONS ===================== */
var nIcons={
  success:'<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>',
  warn:'<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>',
  info:'<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>',
  car:'<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 17H3a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11a2 2 0 0 1 2 2v3"/><rect x="9" y="11" width="14" height="10" rx="1"/><circle cx="12" cy="21" r="1"/><circle cx="20" cy="21" r="1"/></svg>',
  alert:'<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>',
};
var nColors={
  success:'var(--green)',
  warn:'var(--gold)',
  info:'var(--blue)',
  car:'var(--purple)',
  alert:'var(--gold)',
};
var btnIcons={
  card:'<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>',
  star:'<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>',
  x:'<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>',
  user:'<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>',
  car:'<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 17H3a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11a2 2 0 0 1 2 2v3"/><rect x="9" y="11" width="14" height="10" rx="1"/><circle cx="12" cy="21" r="1"/><circle cx="20" cy="21" r="1"/></svg>',
  eye:'<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>',
};
function renderNotifs(){
  var el=document.getElementById('notifList');
  if(!el)return;
  if(!S.notifs.length){el.innerHTML='<p style="color:var(--muted);padding:1rem">No notifications</p>';return;}
  el.innerHTML=S.notifs.map(function(n){
    var actionsHtml='';
    if(n.actions&&n.actions.length){
      actionsHtml='<div class="n-actions">'+n.actions.map(function(a,i){
        return '<button class="n-btn '+(a.primary?'primary':'')+' '+(a.danger?'danger':'')+'" onclick="notifAction('+n.id+','+i+')">'+(a.icon?(btnIcons[a.icon]||''):'')+' '+a.label+'</button>';
      }).join('')
      +'<button class="n-btn" onclick="openNotifDetail('+n.id+')">'
      +'<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>'
      +' Details</button></div>';
    }
    return '<div class="ncard '+(n.unread?'unread':'')+'">'
      +'<div class="n-ico" style="color:'+(nColors[n.type]||'var(--gold)')+'">'+(nIcons[n.type]||nIcons.info)+'</div>'
      +'<div class="n-body">'
      +'<div class="n-title">'+n.title+'</div>'
      +'<div class="n-txt">'+n.txt+'</div>'
      +'<div class="n-time">'+n.time+'</div>'
      +actionsHtml
      +'</div>'
      +(n.unread?'<button class="n-mark" onclick="markRead('+n.id+')">Mark read</button>':'<span style="color:var(--muted);font-size:.72rem;white-space:nowrap">Read</span>')
      +'</div>';
  }).join('');
}
function notifAction(notifId,actionIdx){
  var n=S.notifs.find(function(x){return x.id===notifId;});
  if(!n||!n.actions||!n.actions[actionIdx])return;
  var action=n.actions[actionIdx];
  n.unread=false;
  refreshBadges();renderNotifs();
  if(typeof action.cb==='function')action.cb(notifId);
}
function openNotifDetail(id){
  var n=S.notifs.find(function(x){return x.id===id;});
  if(!n)return;
  n.unread=false;refreshBadges();renderNotifs();
  document.getElementById('notifOvTitle').textContent=n.title;
  document.getElementById('notifOvBody').innerHTML='<div class="notif-detail"><div class="notif-detail-txt">'+n.txt+'</div><div class="notif-detail-time">'+n.time+'</div></div>';
  openOv('notifOv');
}
function markRead(id){
  var n=S.notifs.find(function(x){return x.id===id;});
  if(n)n.unread=false;
  renderNotifs();refreshBadges();
}
function markAllRead(){
  S.notifs.forEach(function(n){n.unread=false;});
  renderNotifs();refreshBadges();
  toast('All marked as read','ok');
}

/* ===================== PAYMENT FLOW ===================== */
function openPayChoiceModal(planName,basePrice){
  S.payPlan={name:planName,basePrice:basePrice};
  S.payDuration=null;
  document.getElementById('payChoiceTitle').textContent='Choose Duration — '+planName;
  document.getElementById('payChoicePlanName').textContent=planName;
  var durations=DURATIONS[planName]||DURATIONS['Pro'];
  document.getElementById('durationOptions').innerHTML=durations.map(function(d,i){
    return '<button class="duration-btn'+(i===0?' selected':'')+'" onclick="selectDuration(this,'+i+',\''+planName+'\')">'
      +'<div class="dur-radio"></div>'
      +'<div class="dur-info"><div class="dur-days">'+d.label+'</div><div class="dur-desc">'+d.days+' days of full access</div>'+(d.save?'<span class="dur-save">'+d.save+'</span>':'')+'</div>'
      +'<div style="text-align:right"><div class="dur-price">'+fmt(d.price)+' DZD</div><div style="color:var(--muted);font-size:.72rem;margin-top:.1rem">'+Math.round(d.price/(d.days/30))+' DZD/mo</div></div>'
      +'</button>';
  }).join('');
  S.payDuration=durations[0];
  openOv('payChoiceOv');
  var existing=document.getElementById('durConfirmBtn');
  if(existing)existing.remove();
  var btn=document.createElement('div');
  btn.id='durConfirmBtn';
  btn.style.cssText='padding:.9rem 1.4rem 1.4rem;display:flex;justify-content:flex-end;gap:.65rem';
  btn.innerHTML='<button class="btn-outline" onclick="closeOv(\'payChoiceOv\')">Cancel</button><button class="btn-gold" onclick="confirmDuration()">Continue <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg></button>';
  document.querySelector('#payChoiceOv .modal').appendChild(btn);
}
function selectDuration(btn,idx,planName){
  document.querySelectorAll('#durationOptions .duration-btn').forEach(function(b){b.classList.remove('selected');});
  btn.classList.add('selected');
  S.payDuration=DURATIONS[planName][idx];
}
function confirmDuration(){
  if(!S.payDuration){toast('Please choose a duration','err');return;}
  closeOv('payChoiceOv');
  setTimeout(openPayMethodModal,200);
}
function openPayMethodModal(){
  document.getElementById('payMethodTitle').textContent='Payment — '+S.payPlan.name;
  document.getElementById('paySummaryBox').innerHTML=buildSummaryHTML();
  openOv('payMethodOv');
}
function buildSummaryHTML(){
  if(!S.payPlan||!S.payDuration)return'';
  return '<div><div class="psb-plan">'+S.payPlan.name+'</div><div class="psb-dur">'+S.payDuration.label+' · '+S.payDuration.days+' days</div></div><div class="psb-price">'+fmt(S.payDuration.price)+' DZD</div>';
}
function selectPayMethod(method){
  S.payMethod=method;
  closeOv('payMethodOv');
  setTimeout(function(){
    if(method==='virement')openVirModal();
    else openCardModal(method);
  },200);
}
function openCardModal(method){
  var isCib=method==='cib';
  document.getElementById('payCardTitle').textContent=isCib?'Pay by CIB Card':'Pay by Edahabia Card';
  document.getElementById('payCardSummary').innerHTML=buildSummaryHTML();
  document.getElementById('cardNumber').value='';
  document.getElementById('cardExpiry').value='';
  document.getElementById('cardCvv').value='';
  document.getElementById('cardName').value='';
  openOv('payCardOv');
}
function formatCard(el){
  var v=el.value.replace(/\D/g,'').substring(0,16);
  el.value=v.replace(/(.{4})/g,'$1 ').trim();
}
function formatExpiry(el){
  var v=el.value.replace(/\D/g,'').substring(0,4);
  if(v.length>=2)v=v.substring(0,2)+'/'+v.substring(2);
  el.value=v;
}
function submitCardPay(){
  var num=document.getElementById('cardNumber').value.replace(/\s/g,'');
  var exp=document.getElementById('cardExpiry').value;
  var cvv=document.getElementById('cardCvv').value;
  var name=document.getElementById('cardName').value.trim();
  if(num.length<16){toast('Invalid card number','err');return;}
  if(exp.length<5){toast('Invalid expiration date','err');return;}
  if(cvv.length<3){toast('Invalid CVV','err');return;}
  if(!name){toast('Name on card is required','err');return;}
  closeOv('payCardOv');
  showProcessingOverlay(false);
}
function openVirModal(){
  document.getElementById('payVirSummary').innerHTML=buildSummaryHTML();
  document.getElementById('payRefVir').value='';
  document.getElementById('proofPrevVir').innerHTML='';
  document.getElementById('proofFileVir').value='';
  openOv('payVirOv');
}
function previewProofVir(ev){
  var f=ev.target.files[0];
  if(!f)return;
  var prev=document.getElementById('proofPrevVir');
  if(f.type.startsWith('image/')){
    var r=new FileReader();
    r.onload=function(e){prev.innerHTML='<img src="'+e.target.result+'"/><div class="pf-name">'+f.name+'</div>';};
    r.readAsDataURL(f);
  }else{
    prev.innerHTML='<div style="display:flex;align-items:center;gap:.5rem;padding:.65rem .9rem;background:var(--s2);border-radius:8px;font-size:.83rem;margin-top:.6rem"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#e74c3c" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>'+f.name+'</div>';
  }
}
function submitVirPay(){
  var f=document.getElementById('proofFileVir').files[0];
  if(!f){toast('Please upload your payment receipt','err');return;}
  closeOv('payVirOv');
  var formData = new FormData();
formData.append('proof', document.getElementById('proofFileVir').files[0]);
formData.append('plan', S.payPlan.name);
formData.append('amount', S.payDuration.price);
formData.append('payment_method', 'CCP');
formData.append('payment_reference', document.getElementById('payRefVir').value);
fetch('save_payment.php', {method:'POST', body: formData});
  showProcessingOverlay(true);
}
function showProcessingOverlay(isVirement){
  var overlay=document.createElement('div');
  overlay.className='processing-overlay';
  overlay.id='procOverlay';
  if(isVirement){
    overlay.innerHTML='<div class="proc-box"><div class="proc-spinner"></div><div class="proc-title">Request Submitted</div><div class="proc-sub">Your receipt has been submitted. Awaiting validation by our team.</div><div class="proc-steps"><div class="proc-step done"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>Receipt uploaded</div><div class="proc-step active"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>Verification in progress...</div><div class="proc-step wait"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>Activating subscription</div></div></div>';
  }else{
    overlay.innerHTML='<div class="proc-box"><div class="proc-spinner"></div><div class="proc-title">Processing Payment</div><div class="proc-sub">Secure connection to payment server...</div><div class="proc-steps"><div class="proc-step done"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>Data verified</div><div class="proc-step active"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>Authorization in progress...</div><div class="proc-step wait"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>Activating subscription</div></div></div>';
  }
  document.body.appendChild(overlay);
  var name=S.payPlan.name;
  var days=S.payDuration.days;
  var price=S.payDuration.price;
  var now=new Date();
  var exp=new Date(now.getTime()+days*86400000);
  S.sub={plan:name,status:isVirement?'Pending Validation':'Active',start:now.toLocaleDateString('en-GB'),expiry:exp.toLocaleDateString('en-GB'),daysLeft:days,totalDays:days};
  S.agent.status=isVirement?'pending':'active';
  if(isVirement){
    setTimeout(function(){
      overlay.remove();
      S.notifs.unshift({id:Date.now(),type:'info',title:'Payment Submitted',txt:'Your '+name+' subscription request ('+fmt(price)+' DZD / '+days+' days) is being verified. You will be notified once approved.',time:'Just now',unread:true,actions:[{label:'View Status',icon:'card',cb:function(){showPage('subscription');}}]});
      syncSidebar();updateLockState();renderNotifs();refreshBadges();
      toast('Receipt submitted. Awaiting validation.','inf');
      showPage('subscription');
    },3000);
  }else{
    setTimeout(function(){
      var steps=overlay.querySelectorAll('.proc-step');
      if(steps[1]){steps[1].classList.remove('active');steps[1].classList.add('done');}
      if(steps[2]){steps[2].classList.remove('wait');steps[2].classList.add('active');}
      overlay.querySelector('.proc-sub').textContent='Activating your subscription...';
    },2000);
    setTimeout(function(){
      overlay.remove();
      var trialMins = {30:1, 90:3, 180:6, 365:10};
var testSecs = (trialMins[days] || 1) * 60;
fetch('api.php?action=start_sub_test', {method:'POST'});
startTrialCountdown(testSecs);
      activateSubscription(name,days,price);
    },4000);
  }
}
function activateSubscription(planName,days,price){
  S.agent.status='active';S.sub.status='Active';S.lock.expired=false;S.trial.active=false;
  var trialMins = {30:1, 90:3, 180:6, 365:10};
var testSecs = (trialMins[days] || 1) * 60;
document.getElementById('trialTime').style.display='block';
document.getElementById('tboxSub').style.display='none';
startTrialCountdown(testSecs);
  S.notifs.unshift({id:Date.now()+1,type:'success',title:'Subscription Activated!',txt:'Your '+planName+' plan is now active. You have '+days+' days of full access. Enjoy china2dz!',time:'Just now',unread:true,actions:[{label:'Go to Dashboard',icon:'car',cb:function(){showPage('dashboard');},primary:true}]});
  syncSidebar();renderNotifs();refreshBadges();
  document.getElementById('lockOverlay').classList.add('hidden');
  toast(planName+' plan activated! Welcome.','ok');
  showPage('dashboard');
}

/* ===================== PROFILE ===================== */
function saveProfile(){
  var name=document.getElementById('pName').value.trim();
  if(!name){toast('Name cannot be empty','err');return;}
  S.agent.name=name;
  S.agent.phone=document.getElementById('pPhone').value.trim();
  S.agent.email=document.getElementById('pEmail').value.trim();
  document.getElementById('pNameDisp').textContent=name;
  syncSidebar();
  toast('Profile saved!','ok');
}
function uploadAvatar(ev){
  var f=ev.target.files[0];
  if(!f)return;
  var r=new FileReader();
  r.onload=function(e){
    S.agent.avatar=e.target.result;
    var avImg='<img src="'+e.target.result+'" alt=""/>';
    ['sbAv','topAv','pavDisplay'].forEach(function(id){
      var el=document.getElementById(id);
      if(el)el.innerHTML=avImg;
    });
    toast('Photo updated!','ok');
  };
  r.readAsDataURL(f);
}

/* ===================== VIEWS MODAL ===================== */
function openViewsModal(){
  var days=['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
  var vals=[8,12,5,18,22,15,12];
  var max=Math.max.apply(null,vals);
  document.getElementById('vcBars').innerHTML=vals.map(function(v){
    return '<div class="vc-bar" style="height:'+(v/max*100)+'%"><div class="vc-bar-tip">'+v+'</div></div>';
  }).join('');
  document.getElementById('vcDays').innerHTML=days.map(function(d){return '<div class="vc-day">'+d+'</div>';}).join('');
  var top=S.cars.slice(0,3).map(function(c,i){return Object.assign({},c,{views:[42,28,18][i]||10});});
  document.getElementById('vtList').innerHTML=top.map(function(c){
    return '<div class="vt-item"><div class="dli-thumb"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M5 17H3a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11a2 2 0 0 1 2 2v3"/><rect x="9" y="11" width="14" height="10" rx="1"/><circle cx="12" cy="21" r="1"/><circle cx="20" cy="21" r="1"/></svg></div><div><div class="dli-title">'+c.title+'</div><div class="vt-l">'+c.brand+' · '+c.year+'</div></div><div><div class="vt-n">'+c.views+'</div><div class="vt-l">views</div></div></div>';
  }).join('');
  openOv('viewsOv');
}

/* ===================== LOGOUT ===================== */
function doLogout(){
  if(!confirm('Are you sure you want to log out?'))return;
  localStorage.clear();
  window.location.href = 'logout.php';
}

/* ===================== OVERLAY HELPERS ===================== */
function openOv(id){document.getElementById(id).classList.add('open');document.body.style.overflow='hidden';}
function closeOv(id){document.getElementById(id).classList.remove('open');document.body.style.overflow='';}
document.addEventListener('click',function(e){
  document.querySelectorAll('.ov.open').forEach(function(ov){
    if(e.target===ov){ov.classList.remove('open');document.body.style.overflow='';}
  });
});

/* ===================== TOAST ===================== */
var tIcons={
  ok:'<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>',
  err:'<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>',
  inf:'<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>',
};
function toast(msg,type){
  type=type||'inf';
  var wrap=document.getElementById('toastWrap');
  var t=document.createElement('div');
  t.className='toast '+type;
  t.innerHTML='<div class="toast-ico">'+(tIcons[type]||tIcons.inf)+'</div><span>'+msg+'</span>';
  wrap.appendChild(t);
  setTimeout(function(){
    t.style.opacity='0';t.style.transform='translateX(20px)';t.style.transition='all .28s';
    setTimeout(function(){t.remove();},300);
  },3500);
}
function updatePayPreview() {
  var acc     = document.getElementById('piAccount').value || '—';
  var rip     = document.getElementById('piRip').value || '—';
  var owner   = document.getElementById('piOwner').value || '—';
  var deposit = document.getElementById('piDeposit').value;
  var prev    = document.getElementById('payPreview');
  if (!prev) return;
  prev.innerHTML = [
    ['<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>', 'Bank', 'CCP Algeria'],
    ['<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>', 'Account No.', acc],
    ['<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>', 'RIP', rip],
    ['<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>', 'Beneficiary', owner],
    ['<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>', 'Deposit Amount', deposit ? Number(deposit).toLocaleString('fr-DZ') + ' DZD' : '—'],
  ].map(function(r) {
    return '<div style="display:flex;justify-content:space-between;align-items:center;font-size:.82rem;padding:.3rem 0;border-bottom:1px solid rgba(255,255,255,.05);">'
      + '<span style="display:flex;align-items:center;gap:6px;color:var(--muted);">' + r[0] + r[1] + '</span>'
      + '<strong style="color:var(--text);">' + r[2] + '</strong></div>';
  }).join('');
}

async function savePaymentInfo() {
  var acc     = document.getElementById('piAccount').value.trim();
  var rip     = document.getElementById('piRip').value.trim();
  var owner   = document.getElementById('piOwner').value.trim();
  var deposit = document.getElementById('piDeposit').value;
  if (!acc && !rip && !owner) { toast('Please fill at least one field', 'err'); return; }
  try {
    var res = await fetch('api.php?action=save_payment_info_session', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ ccp_account: acc, ccp_rip: rip, ccp_owner: owner, deposit_amount: deposit })
    });
    var data = await res.json();
    if (data.success) toast('Payment info saved!', 'ok');
    else toast(data.message || 'Error', 'err');
  } catch(e) { toast('Connection error', 'err'); }
}
async function loadPaymentInfo() {
  try {
    var res = await fetch('api.php?action=get_payment_info&agent_id=' + AGENT_ID);
    var data = await res.json();
    if (data.success && data.data) {
      var d = data.data;
      document.getElementById('piAccount').value = d.ccp_account || '';
      document.getElementById('piRip').value     = d.ccp_rip || '';
      document.getElementById('piOwner').value   = d.ccp_owner || '';
      document.getElementById('piDeposit').value = d.deposit_amount || '';
      updatePayPreview();
    }
  } catch(e) {}
}
/* ===================== UTILS ===================== */
function fmt(n){return Number(n).toLocaleString('en-DZ');}
const AGENT_ID = <?= $_SESSION['user_id'] ?>;

async function agentApiCall(action, method = 'GET', body = null) {
  const opts = { method, headers: { 'Content-Type': 'application/json' } };
  if (body) opts.body = JSON.stringify(body);
  const res = await fetch(`api.php?action=${action}`, opts);
  return res.json();
}
let agentConvsData = [];
async function loadAgentConversations() {
  const res = await fetch('api.php?action=get_agent_conversations&agent_id=' + AGENT_ID);
  const data = await res.json();
  agentConvsData = data.data || [];
  const list = document.getElementById('agentConvList');
  if (!data.success || !data.data.length) {
    list.innerHTML = '<div style="padding:14px;font-weight:700;border-bottom:1px solid var(--br);">Conversations</div><div style="padding:20px;color:var(--muted);font-size:.83rem;">No conversations yet</div>';
    return;
  }
  list.innerHTML = '<div style="padding:14px;font-weight:700;border-bottom:1px solid var(--br);">Conversations</div>' +
    agentConvsData.map(c => {
      const initials = c.client_name ? c.client_name.charAt(0).toUpperCase() : '?';
      const avatar = c.client_photo
        ? `<img src="${c.client_photo}" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">`
        : `<span style="font-weight:700;color:#000;font-size:.85rem;">${initials}</span>`;
      const isOnline = c.is_online == 1;
      const unreadDot = c.unread_count > 0
        ? `<span style="min-width:18px;height:18px;border-radius:50%;background:var(--gold);color:#000;font-size:.65rem;font-weight:800;display:flex;align-items:center;justify-content:center;padding:0 4px;">${c.unread_count}</span>`
        : '';
      return `<div onclick="openAgentConv(${c.id}, '${c.client_name}', ${c.client_id})"
           id="conv-item-${c.id}"
           style="padding:11px 14px;cursor:pointer;border-bottom:1px solid var(--br);transition:background .15s;display:flex;align-items:center;gap:10px;"
           onmouseover="this.style.background='var(--s2)'" onmouseout="this.style.background=''">
        <div style="position:relative;flex-shrink:0;">
          <div style="width:38px;height:38px;border-radius:50%;background:var(--gold);display:flex;align-items:center;justify-content:center;overflow:hidden;">
            ${avatar}
          </div>
          <div style="position:absolute;bottom:0;right:0;width:10px;height:10px;border-radius:50%;background:${isOnline ? '#2ecc71' : '#555'};border:2px solid var(--s1);"></div>
        </div>
        <div style="flex:1;min-width:0;">
          <div style="font-weight:600;font-size:.85rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${c.client_name}</div>
          <div style="font-size:.75rem;color:var(--muted);margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${c.last_message || 'Start chatting'}</div>
        </div>
        ${unreadDot}
      </div>`;
    }).join('');
    const totalUnread = agentConvsData.reduce((sum, c) => sum + (parseInt(c.unread_count) || 0), 0);
  document.getElementById('nbMsg').textContent = totalUnread;
  }

let agentCurrentConvId = null;
let agentPollInterval = null;
async function openAgentConv(convId, clientName, clientId) {
  agentCurrentConvId = convId;
  const main = document.getElementById('agentChatMain');
  
  const convData = agentConvsData.find(c => c.id == convId);
  const clientPhoto = convData?.client_photo || null;
  const isOnline = convData?.is_online == 1;
  const avatarHeader = clientPhoto
    ? `<img src="${clientPhoto}" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">`
    : `<span style="font-weight:700;color:#000;">${clientName.charAt(0).toUpperCase()}</span>`;

  main.innerHTML =
    '<div style="padding:13px 16px;border-bottom:1px solid var(--br);display:flex;align-items:center;gap:10px;justify-content:space-between;">' +
      '<div style="display:flex;align-items:center;gap:10px;">' +
        '<div style="position:relative;width:36px;height:36px;border-radius:50%;background:var(--gold);display:flex;align-items:center;justify-content:center;overflow:hidden;flex-shrink:0;">' +
          avatarHeader +
          '<div style="position:absolute;bottom:0;right:0;width:9px;height:9px;border-radius:50%;background:' + (isOnline ? '#2ecc71' : '#555') + ';border:2px solid var(--s1);"></div>' +
        '</div>' +
        '<div>' +
          '<div style="font-weight:700;font-size:.9rem;">' + clientName + '</div>' +
          '<div style="font-size:.72rem;color:' + (isOnline ? '#2ecc71' : 'var(--muted)') + ';margin-top:1px;">' + (isOnline ? '● Online' : '○ Offline') + '</div>' +
        '</div>' +
      '</div>' +
    '</div>' +
    '<div id="agentMsgArea" style="flex:1;overflow-y:auto;padding:14px;display:flex;flex-direction:column;gap:8px;min-height:0;"></div>' +
    '<div style="padding:11px 12px;border-top:1px solid var(--br);display:flex;gap:8px;">' +
      '<input type="text" id="agentMsgInput" placeholder="Type a message..." ' +
             'style="flex:1;padding:9px 14px;background:var(--s2);border:1px solid var(--br);border-radius:22px;color:var(--text);font-family:inherit;font-size:.87rem;outline:none;" ' +
             'onkeypress="if(event.key===\'Enter\')sendAgentMsg(' + clientId + ')">' +
      '<button onclick="sendAgentMsg(' + clientId + ')" style="width:38px;height:38px;border-radius:50%;background:var(--gold);border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;">' +
        '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#000" stroke-width="2.5"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>' +
      '</button>' +
    '</div>';

  await fetchAgentMsgs();
  if (agentPollInterval) clearInterval(agentPollInterval);
  agentPollInterval = setInterval(fetchAgentMsgs, 4000);
}

async function fetchAgentMsgs() {
  if (!agentCurrentConvId) return;
  const res = await fetch('api.php?action=get_agent_messages&conversation_id=' + agentCurrentConvId + '&agent_id=' + AGENT_ID);
  const data = await res.json();
  const area = document.getElementById('agentMsgArea');
  if (!area || !data.success) return;
  const msgs = data.data;
  if (!msgs.length) { area.innerHTML = '<div style="text-align:center;color:var(--muted);font-size:.82rem;">Say hello! 👋</div>'; return; }
  area.innerHTML = msgs.map(m => {
    const mine = m.sender_id == AGENT_ID;

    // بطاقة الحجز
  if (m.message === 'reservation_request' && m.reservation_id) {
    const r = {
    first_name: m.res_first_name,
    last_name: m.res_last_name,
    phone: m.res_phone,
    payment_method: m.res_payment_method,
    payment_file: m.res_payment_file,
    status: m.res_status,
    car_id: m.res_car_id,
    car_name: m.res_car_name
};
    const isPending = r.status === 'pending';
        const statusColor = r.status === 'accepted' ? 'var(--green)' : r.status === 'refused' ? 'var(--red)' : 'var(--gold)';
        const statusLabel = r.status === 'accepted' ? 'Confirmed' : r.status === 'refused' ? 'Refused' : 'Pending';
        return `<div style="max-width:85%;align-self:flex-start;">
          <div style="background:var(--s2);border:1px solid var(--br);border-radius:14px;overflow:hidden;border-left:3px solid var(--gold);">
            <div style="padding:10px 14px;border-bottom:1px solid var(--br);display:flex;align-items:center;gap:8px;">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--gold)" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
              <span style="font-weight:700;font-size:.85rem;">Reservation Request</span>
${r.car_name ? `<a href="index.php#car-${r.car_id}" target="_blank" style="margin-left:auto;font-size:.78rem;font-weight:700;color:var(--red);text-decoration:none;padding:2px 8px;border-radius:6px;background:rgba(0,188,212,.1);display:flex;align-items:center;gap:4px;">
  <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 17H3a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11a2 2 0 0 1 2 2v3"/><rect x="9" y="11" width="14" height="10" rx="1"/><circle cx="12" cy="21" r="1"/><circle cx="20" cy="21" r="1"/></svg>
  ${r.car_name}
</a>` : ''}
              <span style="margin-left:auto;padding:2px 9px;border-radius:20px;font-size:.7rem;font-weight:700;background:${r.status==='accepted'?'rgba(46,204,113,.15)':r.status==='refused'?'rgba(231,76,60,.15)':'rgba(212,168,67,.15)'};color:${statusColor};">${statusLabel}</span>
            </div>
            <div style="padding:12px 14px;display:flex;flex-direction:column;gap:7px;">
              <div style="display:flex;justify-content:space-between;font-size:.82rem;">
                <span style="color:var(--muted);">Client</span>
                <strong style="color:var(--text);">${r.first_name} ${r.last_name}</strong>
              </div>
              <div style="display:flex;justify-content:space-between;font-size:.82rem;">
                <span style="color:var(--muted);">Phone</span>
                <strong style="color:var(--text);">${r.phone}</strong>
              </div>
              <div style="display:flex;justify-content:space-between;font-size:.82rem;">
                <span style="color:var(--muted);">Payment</span>
                <strong style="color:var(--text);">${r.payment_method === 'cheque' ? 'Bank Cheque' : 'Golden Card'}</strong>
              </div>
              ${r.payment_file ? `<div style="display:flex;justify-content:space-between;align-items:center;font-size:.82rem;">
                <span style="color:var(--muted);">Proof</span>
                <a href="${r.payment_file}" target="_blank" style="color:var(--gold);font-weight:700;text-decoration:none;display:flex;align-items:center;gap:4px;">
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                  View File
                </a>
              </div>` : ''}
              ${isPending ? `<div style="display:flex;gap:8px;margin-top:6px;">
                <button onclick="agentDecideFromChat(${m.reservation_id},'accepted')"
                  style="flex:1;padding:8px;background:rgba(46,204,113,.15);border:1px solid var(--green);border-radius:8px;color:var(--green);font-family:'Barlow',sans-serif;font-weight:700;font-size:.8rem;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:5px;transition:background .18s;"
                  onmouseover="this.style.background='rgba(46,204,113,.3)'" onmouseout="this.style.background='rgba(46,204,113,.15)'">
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                  Accept
                </button>
                <button onclick="agentDecideFromChat(${m.reservation_id},'refused')"
                  style="flex:1;padding:8px;background:rgba(231,76,60,.15);border:1px solid var(--red);border-radius:8px;color:var(--red);font-family:'Barlow',sans-serif;font-weight:700;font-size:.8rem;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:5px;transition:background .18s;"
                  onmouseover="this.style.background='rgba(231,76,60,.3)'" onmouseout="this.style.background='rgba(231,76,60,.15)'">
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                  Refuse
                </button>
              </div>` : ''}
            </div>
          </div>
          <div style="font-size:.69rem;color:var(--muted);margin-top:3px;">${new Date(m.sent_at).toLocaleTimeString('en',{hour:'2-digit',minute:'2-digit'})}</div>
        </div>`;
    }

    return `<div style="max-width:70%;align-self:${mine?'flex-end':'flex-start'};">
      <div style="padding:9px 13px;border-radius:14px;font-size:.86rem;background:${mine?'var(--gold)':'var(--s2)'};color:${mine?'#000':'var(--text)'};">${m.message}</div>
      <div style="font-size:.69rem;color:var(--muted);margin-top:3px;text-align:${mine?'right':'left'};">${new Date(m.sent_at).toLocaleTimeString('en',{hour:'2-digit',minute:'2-digit'})}</div>
    </div>`;
  }).join('');
  area.scrollTop = area.scrollHeight;
}

async function sendAgentMsg(clientId) {
  const input = document.getElementById('agentMsgInput');
  const msg = input?.value?.trim(); if (!msg) return;
  input.value = '';
  await fetch('api.php?action=send_agent_message', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({ conversation_id: agentCurrentConvId, message: msg, agent_id: AGENT_ID, client_id: clientId })
  });
  await fetchAgentMsgs();
}
function loadAgentAlerts(agentId) {
    fetch('notification_action.php?action=get&user_id=' + agentId)
    .then(function(r){ return r.json(); })
    .then(function(data){
        var alerts = (data.notifications || []).filter(function(n){ return n.type === 'offer'; });
        var container = document.getElementById('agentAlertsList');
        if (!container) return;

        var badge = document.getElementById('nbAlerts');
        if (badge) badge.textContent = alerts.length;

        if (!alerts.length) {
            container.innerHTML = '<div style="color:var(--muted);text-align:center;padding:40px">No client alerts yet</div>';
            return;
        }
        container.innerHTML = alerts.map(function(a) {
            return '<div style="background:var(--s1);border:1px solid var(--br);border-radius:12px;padding:14px 18px;margin-bottom:10px;">' +
                '<div style="font-weight:700;color:var(--gold);margin-bottom:6px;font-size:.95rem">' + (a.title || '') + '</div>' +
                '<div style="font-size:.85rem;color:var(--text);margin-bottom:4px">' + (a.message || '') + '</div>' +
                '<div style="font-size:.75rem;color:var(--muted)">' + (a.created_at || '') + '</div>' +
            '</div>';
        }).join('');
    })
    .catch(function(){ 
        var container = document.getElementById('agentAlertsList');
        if (container) container.innerHTML = '<div style="color:var(--muted);text-align:center;padding:40px">Error loading alerts</div>';
    });
}
/* ===================== SHIPMENT TRACKING ===================== */
const SHIPMENT_STAGES = [
  { key: 'purchased', label: 'Order Confirmed in China',
    icon: '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>' },
  { key: 'shipped',   label: 'Shipped — On the Way',
    icon: '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13" rx="1"/><path d="M16 8h4l3 5v3h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>' },
  { key: 'customs',   label: 'Customs Clearance',
    icon: '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>' },
  { key: 'warehouse', label: 'In Warehouse',
    icon: '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>' },
  { key: 'delivery',  label: 'Ready for Delivery',
    icon: '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>' },
  { key: 'delivered', label: 'Delivered',
    icon: '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>' },
];

var shipmentReservationId = null;
var shipmentSelectedStage = null;

async function loadShipmentReservations() {
  var container = document.getElementById('shipmentList');
  container.innerHTML = '<div style="color:var(--muted);text-align:center;padding:40px">Loading...</div>';
  try {
    var res = await fetch('api.php?action=get_agent_reservations_for_tracking&agent_id=' + AGENT_ID);
    var data = await res.json();
    if (!data.success || !data.data || !data.data.length) {
      container.innerHTML = '<div style="text-align:center;padding:40px;color:var(--muted);">No confirmed reservations yet</div>';
      return;
    }
    document.getElementById('nbShipment').textContent = data.data.length;
    container.innerHTML = data.data.map(function(r) {
      var stageInfo = SHIPMENT_STAGES.find(function(s){ return s.key === r.stage; });
      var stageBadge = r.stage
        ? '<span style="padding:3px 10px;border-radius:20px;font-size:.72rem;font-weight:700;background:rgba(212,168,67,.15);color:var(--gold);">' + (stageInfo ? stageInfo.label : r.stage_label) + '</span>'
        : '<span style="padding:3px 10px;border-radius:20px;font-size:.72rem;font-weight:700;background:rgba(59,130,246,.12);color:var(--blue);">Not Started</span>';
      return '<div style="background:var(--s1);border:1px solid var(--br);border-radius:12px;padding:16px 18px;margin-bottom:10px;display:flex;align-items:center;gap:14px;flex-wrap:wrap;">'
        + '<div style="width:44px;height:44px;border-radius:10px;background:rgba(212,168,67,.1);display:grid;place-items:center;flex-shrink:0;color:var(--gold);">'
        + '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13" rx="1"/><path d="M16 8h4l3 5v3h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>'
        + '</div>'
        + '<div style="flex:1;min-width:180px;">'
        + '<div style="font-weight:700;font-size:.92rem;">' + (r.car_title || 'Car') + '</div>'
        + '<div style="font-size:.78rem;color:var(--muted);margin-top:3px;">Client: ' + r.client_name + '</div>'
        + '<div style="margin-top:8px;">' + stageBadge + '</div>'
        + '</div>'
        + '<button onclick="openShipmentModal(' + r.reservation_id + ',\'' + (r.car_title || '').replace(/'/g, '') + '\',\'' + r.client_name.replace(/'/g, '') + '\',\'' + (r.stage || '') + '\')" '
        + 'class="btn-gold btn-sm" style="display:flex;align-items:center;gap:6px;white-space:nowrap;">'
        + '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>'
        + 'Update Status</button>'
        + '</div>';
    }).join('');
  } catch(e) {
    container.innerHTML = '<div style="text-align:center;padding:40px;color:var(--muted);">Error loading data</div>';
  }
}

function openShipmentModal(reservationId, carTitle, clientName, currentStage) {
  shipmentReservationId = reservationId;
  shipmentSelectedStage = currentStage || null;
  document.getElementById('shipmentNote').value = '';
  document.getElementById('shipmentCarInfo').innerHTML =
    '<div style="display:flex;align-items:center;gap:10px;">'
    + '<div style="width:38px;height:38px;border-radius:9px;background:rgba(212,168,67,.12);display:grid;place-items:center;flex-shrink:0;color:var(--gold);">'
    + '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 17H3a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11a2 2 0 0 1 2 2v3"/><rect x="9" y="11" width="14" height="10" rx="1"/><circle cx="12" cy="21" r="1"/><circle cx="20" cy="21" r="1"/></svg>'
    + '</div>'
    + '<div><div style="font-weight:700;font-size:.9rem;">' + carTitle + '</div>'
    + '<div style="font-size:.78rem;color:var(--muted);margin-top:2px;">Client: ' + clientName + '</div></div>'
    + '</div>';

  var currentIdx = SHIPMENT_STAGES.findIndex(function(s){ return s.key === currentStage; });
  document.getElementById('shipmentStagesGrid').innerHTML = SHIPMENT_STAGES.map(function(s, idx) {
    var isCurrent = s.key === currentStage;
    var isPast    = currentIdx >= 0 && idx < currentIdx;
    var borderColor  = isCurrent ? 'var(--gold)' : isPast ? 'rgba(46,204,113,.4)' : 'var(--br)';
    var bgColor      = isCurrent ? 'rgba(212,168,67,.08)' : isPast ? 'rgba(46,204,113,.06)' : 'var(--s2)';
    var iconBg       = isCurrent ? 'rgba(212,168,67,.2)' : isPast ? 'rgba(46,204,113,.15)' : 'var(--s3)';
    var iconColor    = isCurrent ? 'var(--gold)' : isPast ? 'var(--green)' : 'var(--muted)';
    var labelColor   = isCurrent ? 'var(--gold)' : isPast ? 'var(--green)' : 'var(--text)';
    var checkIcon    = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>';
    return '<button onclick="selectShipmentStage(this,\'' + s.key + '\')" '
      + 'data-stage="' + s.key + '" '
      + 'style="display:flex;align-items:center;gap:12px;padding:11px 14px;border-radius:10px;'
      + 'border:2px solid ' + borderColor + ';background:' + bgColor + ';'
      + 'width:100%;text-align:left;font-family:\'Barlow\',sans-serif;cursor:pointer;transition:all .18s;">'
      + '<div style="width:32px;height:32px;border-radius:50%;display:grid;place-items:center;flex-shrink:0;background:' + iconBg + ';color:' + iconColor + ';">'
      + (isPast ? checkIcon : s.icon)
      + '</div>'
      + '<span style="font-size:.86rem;font-weight:' + (isCurrent ? '700' : '500') + ';color:' + labelColor + ';">' + s.label + '</span>'
      + (isCurrent ? '<span style="margin-left:auto;font-size:.7rem;color:var(--gold);font-weight:700;padding:2px 8px;background:rgba(212,168,67,.12);border-radius:20px;">Current</span>' : '')
      + '</button>';
  }).join('');
  openOv('shipmentOv');
}

function selectShipmentStage(btn, stageKey) {
  document.querySelectorAll('#shipmentStagesGrid button').forEach(function(b) {
    b.style.borderColor = 'var(--br)';
    b.style.background  = 'var(--s2)';
  });
  btn.style.borderColor = 'var(--gold)';
  btn.style.background  = 'rgba(212,168,67,.08)';
  shipmentSelectedStage = stageKey;
}

async function submitShipmentUpdate() {
  if (!shipmentSelectedStage) { toast('Please select a stage', 'err'); return; }
  var note = document.getElementById('shipmentNote').value.trim();
  try {
    var res = await fetch('api.php?action=update_shipment_stage', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({
        reservation_id: shipmentReservationId,
        stage: shipmentSelectedStage,
        stage_note: note,
        agent_id: AGENT_ID
      })
    });
    var data = await res.json();
    if (data.success) {
      closeOv('shipmentOv');
      toast('Status updated — client notified', 'ok');
      loadShipmentReservations();
    } else {
      toast(data.message || 'Error', 'err');
    }
  } catch(e) {
    toast('Connection error', 'err');
  }
}
/* ===================== RESERVATION ===================== */
var rCurrentClientId = null;
var rCurrentCarId = null;
var rPayMethod = 'cheque';

function openReserveModal(clientId, carId) {
  rCurrentClientId = clientId;
  // جيبي أول سيارة متوفرة أو اتركيها يختار — هنا نعرض أول سيارة available
  var car = carId ? S.cars.find(function(c){ return c.id == carId; }) : S.cars.find(function(c){ return c.status === 'available'; });
if (!car) { toast('No available cars to reserve', 'err'); return; }

if (car.reservation_status === 'reserved') {
  toast('This car is already reserved by another client', 'err');
  return;
}
  rCurrentCarId = car.id;
  document.getElementById('reserveCarTitle').textContent = car.title;
  document.getElementById('reserveCarPrice').textContent = fmt(car.price) + ' DZD';
  document.getElementById('rFirstName').value = '';
  document.getElementById('rLastName').value = '';
  document.getElementById('rPhone').value = '';
  document.getElementById('rFilePrev').innerHTML = '';
  document.getElementById('rPayFile').value = '';
  selectRPayMethod('cheque');
  document.querySelector('input[name="rPayMethod"][value="cheque"]').checked = true;
  openOv('reserveOv');
}

function selectRPayMethod(method) {
  rPayMethod = method;
  var cheque = document.getElementById('rPayCheque');
  var gold = document.getElementById('rPayGold');
  if (method === 'cheque') {
    cheque.style.border = '2px solid var(--gold)';
    cheque.style.background = 'rgba(212,168,67,.08)';
    gold.style.border = '2px solid var(--br)';
    gold.style.background = 'var(--s2)';
  } else {
    gold.style.border = '2px solid var(--gold)';
    gold.style.background = 'rgba(212,168,67,.08)';
    cheque.style.border = '2px solid var(--br)';
    cheque.style.background = 'var(--s2)';
  }
}

function previewRFile(event) {
  var f = event.target.files[0];
  if (!f) return;
  var prev = document.getElementById('rFilePrev');
  var zone = document.getElementById('rUploadZone');
  zone.style.borderColor = 'var(--green)';
  if (f.type.startsWith('image/')) {
    var r = new FileReader();
    r.onload = function(e) {
      prev.innerHTML = '<div style="display:flex;align-items:center;gap:10px;margin-top:10px;padding:10px;background:var(--s2);border-radius:8px;">'
        + '<img src="'+e.target.result+'" style="width:56px;height:44px;object-fit:cover;border-radius:6px;border:1px solid var(--br);">'
        + '<div><div style="font-size:.82rem;font-weight:600;">'+f.name+'</div>'
        + '<div style="font-size:.75rem;color:var(--green);margin-top:2px;">✓ Ready to upload</div></div></div>';
    };
    r.readAsDataURL(f);
  } else {
    prev.innerHTML = '<div style="display:flex;align-items:center;gap:10px;margin-top:10px;padding:10px;background:var(--s2);border-radius:8px;">'
      + '<div style="width:36px;height:36px;border-radius:7px;background:rgba(231,76,60,.15);display:grid;place-items:center;flex-shrink:0;">'
      + '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#e74c3c" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg></div>'
      + '<div><div style="font-size:.82rem;font-weight:600;">'+f.name+'</div>'
      + '<div style="font-size:.75rem;color:var(--green);margin-top:2px;">✓ Ready to upload</div></div></div>';
  }
}

async function submitReservation() {
  var firstName = document.getElementById('rFirstName').value.trim();
  var lastName  = document.getElementById('rLastName').value.trim();
  var phone     = document.getElementById('rPhone').value.trim();
  var file      = document.getElementById('rPayFile').files[0];

  if (!firstName || !lastName) { toast('Please enter your full name', 'err'); return; }
  if (!phone || phone.length < 9) { toast('Please enter a valid phone number', 'err'); return; }
  if (!file) { toast('Please upload your deposit proof', 'err'); return; }

  var btn = document.getElementById('rSubmitBtn');
  btn.disabled = true;
  btn.innerHTML = '<div style="width:14px;height:14px;border:2px solid #000;border-top-color:transparent;border-radius:50%;animation:spin .7s linear infinite;"></div> Sending...';

  var formData = new FormData();
  formData.append('car_id', rCurrentCarId);
  formData.append('client_id', rCurrentClientId);
  formData.append('agent_id', AGENT_ID);
  formData.append('conversation_id', agentCurrentConvId || 0);
  formData.append('first_name', firstName);
  formData.append('last_name', lastName);
  formData.append('phone', phone);
  formData.append('payment_method', rPayMethod);
  formData.append('payment_file', file);

  try {
    var res = await fetch('reserve_car.php', { method: 'POST', body: formData });
    var data = await res.json();
    if (data.success) {
      closeOv('reserveOv');
      toast('Reservation request sent! The agent will confirm within 24h.', 'ok');
      // أرسلي رسالة تلقائية في الشات
      await fetch('api.php?action=send_agent_message', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({
          conversation_id: agentCurrentConvId,
          message: '🔒 I have submitted a reservation request for this car. Please review and confirm.',
          agent_id: AGENT_ID,
          client_id: rCurrentClientId
        })
      });
      await fetchAgentMsgs();
    } else {
      toast(data.message || 'Failed to submit reservation', 'err');
    }
  } catch(e) {
    toast('Connection error. Please try again.', 'err');
  }

  btn.disabled = false;
  btn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg> Send Reservation Request';
}
var rDecisionId = null;
var rVisited = 0;

function setVisited(val) {
  rVisited = val;
  var yes = document.getElementById('visitedYes');
  var no  = document.getElementById('visitedNo');
  if (val === 1) {
    yes.style.borderColor = 'var(--green)'; yes.style.color = 'var(--green)'; yes.style.background = 'rgba(46,204,113,.1)';
    no.style.borderColor  = 'var(--br)';    no.style.color  = 'var(--muted)'; no.style.background  = 'var(--s2)';
  } else {
    no.style.borderColor  = 'var(--red)';  no.style.color  = 'var(--red)';   no.style.background  = 'rgba(231,76,60,.1)';
    yes.style.borderColor = 'var(--br)';   yes.style.color = 'var(--muted)'; yes.style.background = 'var(--s2)';
  }
}

async function openReservationDetail(requestId) {
  // جيبي تفاصيل الحجز من DB
  try {
    var res = await fetch('api.php?action=get_reservation&request_id=' + requestId);
    var data = await res.json();
    if (!data.success || !data.data) { toast('Reservation not found', 'err'); return; }
    var r = data.data;
    rDecisionId = r.id;
    rVisited = r.visited || 0;
    document.getElementById('reserveDecisionBody').innerHTML =
      '<div style="background:var(--s2);border-radius:10px;padding:14px 16px;display:flex;flex-direction:column;gap:9px;">'
      + '<div style="display:flex;justify-content:space-between;"><span style="color:var(--muted);font-size:.82rem;">Client</span><strong>' + r.first_name + ' ' + r.last_name + '</strong></div>'
      + '<div style="display:flex;justify-content:space-between;"><span style="color:var(--muted);font-size:.82rem;">Phone</span><strong>' + r.phone + '</strong></div>'
      + '<div style="display:flex;justify-content:space-between;"><span style="color:var(--muted);font-size:.82rem;">Car</span><strong>' + (r.car_title || '—') + '</strong></div>'
      + '<div style="display:flex;justify-content:space-between;"><span style="color:var(--muted);font-size:.82rem;">Payment</span><strong>' + (r.payment_method === 'cheque' ? '🏦 Bank Cheque' : '💳 Golden Card') + '</strong></div>'
      + '<div style="display:flex;justify-content:space-between;align-items:center;"><span style="color:var(--muted);font-size:.82rem;">Proof</span>'
      + (r.payment_file ? '<a href="' + r.payment_file + '" target="_blank" style="color:var(--gold);font-size:.82rem;font-weight:700;text-decoration:none;">📎 View File</a>' : '<span style="color:var(--muted);font-size:.8rem;">—</span>')
      + '</div>'
      + '<div style="display:flex;justify-content:space-between;"><span style="color:var(--muted);font-size:.82rem;">Status</span>'
      + '<span style="padding:2px 10px;border-radius:20px;font-size:.75rem;font-weight:700;background:' + (r.status==='accepted'?'rgba(46,204,113,.15)':r.status==='refused'?'rgba(231,76,60,.15)':'rgba(212,168,67,.15)') + ';color:' + (r.status==='accepted'?'var(--green)':r.status==='refused'?'var(--red)':'var(--gold)') + ';">' + r.status.toUpperCase() + '</span>'
      + '</div></div>';
    setVisited(rVisited);
    openOv('reserveDecisionOv');
  } catch(e) {
    toast('Failed to load reservation', 'err');
  }
}
async function agentDecideFromChat(reservationId, decision) {
  try {
    const res = await fetch('api.php?action=decide_reservation', {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({ reservation_id: reservationId, status: decision, visited: 0 })
    });
    const data = await res.json();
    if (data.success) {
      toast(decision === 'accepted' ? 'Reservation confirmed. Car reserved for 4 days.' : 'Reservation refused.', decision === 'accepted' ? 'ok' : 'inf');
      if (decision === 'accepted') {
  setTimeout(async function() {
    var res = await fetch('api.php?action=get_reservation&request_id=' + reservationId);
    var data = await res.json();
    if (data.success) {
      rDecisionId = data.data.id;
      rVisited = 0;
      setVisited(0);
      document.getElementById('reserveDecisionBody').innerHTML =
        '<div style="background:var(--s2);border-radius:10px;padding:14px;color:var(--text);font-size:.85rem;line-height:1.6;">'
        + '✅ Reservation confirmed for <strong>' + data.data.first_name + ' ' + data.data.last_name + '</strong>.<br>'
        + 'The client has <strong style="color:var(--gold);">4 days</strong> to visit. Update the visit status below when ready.'
        + '</div>';
      openOv('reserveDecisionOv');
    }
  }, 800);
}
      await fetchAgentMsgs();
    } else {
      toast(data.message || 'Error', 'err');
    }
  } catch(e) {
    toast('Connection error', 'err');
  }
}
async function agentDecideReservation(decision) {
  if (!rDecisionId) return;
  try {
    var res = await fetch('api.php?action=decide_reservation', {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({ reservation_id: rDecisionId, status: decision, visited: rVisited })
    });
    var data = await res.json();
    if (data.success) {
      closeOv('reserveDecisionOv');
      if (decision === 'accepted' && rVisited === 1) {
        toast('Client visited — car remains reserved.', 'ok');
      } else if (decision === 'accepted' && rVisited === 0) {
        toast('Reservation confirmed. Client has 4 days to visit.', 'ok');
      } else {
        toast('Reservation refused — car is now available.', 'inf');
      }
      loadRequestsFromDB();
      loadCarsFromDB();
      loadNotificationsFromDB();
    } else {
      toast(data.message || 'Error', 'err');
    }
  } catch(e) {
    toast('Connection error', 'err');
  }
}
async function checkExpiredReservations() {
  try {
    var res = await fetch('api.php?action=get_pending_visit_reservations&agent_id=' + AGENT_ID);
    var data = await res.json();
    if (data.success && data.data && data.data.length > 0) {
      var r = data.data[0];
      rDecisionId = r.id;
      rVisited = 0;
      setVisited(0);

      var acceptedAt = new Date(r.updated_at);
      var now = new Date();
      var daysPassed = (now - acceptedAt) / (1000 * 60 * 60 * 24);
      var isExpired = daysPassed >= 4;

      document.getElementById('reserveDecisionBody').innerHTML =
        '<div style="background:' + (isExpired ? 'rgba(231,76,60,.08)' : 'rgba(212,168,67,.08)') + ';border:1px solid ' + (isExpired ? 'rgba(231,76,60,.25)' : 'rgba(212,168,67,.25)') + ';border-radius:10px;padding:14px;font-size:.85rem;line-height:1.6;">'
        + (isExpired
          ? '⚠️ <strong style="color:var(--red);">4 days have passed!</strong><br>Did <strong>' + r.first_name + ' ' + r.last_name + '</strong> visit? You must respond now.'
          : '⏳ Reservation confirmed for <strong>' + r.first_name + ' ' + r.last_name + '</strong>.<br>Did the client visit to complete the purchase?')
        + '</div>';

      var closeBtn = document.querySelector('#reserveDecisionOv .cls-btn');
      if (closeBtn) {
        closeBtn.style.display = isExpired ? 'none' : 'flex';
        closeBtn.onclick = function() {
    document.getElementById('reserveDecisionOv').style.display = 'none';
};
      }

      openOv('reserveDecisionOv');
    }
  } catch(e) {}
}
setInterval(loadAgentConversations, 10000);
loadAgentConversations();
</script>
<!-- RESERVATION MODAL -->
<div class="ov" id="reserveOv">
  <div class="modal" style="max-width:520px;">
    <div class="mhd">
      <h2>🔒 Reserve This Car</h2>
      <button class="cls-btn" onclick="closeOv('reserveOv')">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <div class="mbody">
      <!-- Info Banner -->
      <div style="background:rgba(212,168,67,.08);border:1px solid rgba(212,168,67,.25);border-radius:10px;padding:12px 14px;margin-bottom:18px;display:flex;gap:10px;align-items:flex-start;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#d4a843" stroke-width="2" style="flex-shrink:0;margin-top:1px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <div style="font-size:.82rem;color:var(--muted);line-height:1.5;">
          Fill in your details and upload proof of <strong style="color:var(--gold);">initial deposit</strong>. 
          The agent will review and confirm your reservation within <strong style="color:var(--text);">24 hours</strong>. 
          Car stays reserved for <strong style="color:var(--text);">4 days</strong> once confirmed.
        </div>
      </div>

      <!-- Car being reserved -->
      <div id="reserveCarInfo" style="background:var(--s2);border-radius:10px;padding:12px 14px;margin-bottom:16px;display:flex;align-items:center;gap:12px;">
        <div style="width:44px;height:44px;border-radius:9px;background:rgba(212,168,67,.15);display:grid;place-items:center;flex-shrink:0;">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#d4a843" stroke-width="2"><path d="M5 17H3a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11a2 2 0 0 1 2 2v3"/><rect x="9" y="11" width="14" height="10" rx="1"/><circle cx="12" cy="21" r="1"/><circle cx="20" cy="21" r="1"/></svg>
        </div>
        <div>
          <div id="reserveCarTitle" style="font-weight:700;font-size:.9rem;">—</div>
          <div id="reserveCarPrice" style="color:var(--gold);font-size:.82rem;margin-top:2px;">—</div>
        </div>
      </div>

      <!-- Form -->
      <div class="frow">
        <div class="fg"><label>First Name</label><input type="text" class="fi" id="rFirstName" placeholder="Your first name"/></div>
        <div class="fg"><label>Last Name</label><input type="text" class="fi" id="rLastName" placeholder="Your last name"/></div>
      </div>
      <div class="fg"><label>Phone Number</label><input type="tel" class="fi" id="rPhone" placeholder="e.g. 0551234567"/></div>

      <div class="fg">
        <label>Payment Method for Deposit</label>
        <div style="display:flex;gap:10px;margin-top:6px;">
          <label style="flex:1;cursor:pointer;">
            <input type="radio" name="rPayMethod" value="cheque" style="display:none;" onchange="selectRPayMethod('cheque')" checked>
            <div id="rPayCheque" style="padding:11px 14px;border:2px solid var(--gold);border-radius:9px;background:rgba(212,168,67,.08);text-align:center;">
              <div style="font-size:1.1rem;margin-bottom:3px;">🏦</div>
              <div style="font-weight:700;font-size:.83rem;">Bank Cheque</div>
            </div>
          </label>
          <label style="flex:1;cursor:pointer;">
            <input type="radio" name="rPayMethod" value="golden_card" style="display:none;" onchange="selectRPayMethod('golden_card')">
            <div id="rPayGold" style="padding:11px 14px;border:2px solid var(--br);border-radius:9px;background:var(--s2);text-align:center;">
              <div style="font-size:1.1rem;margin-bottom:3px;">💳</div>
              <div style="font-weight:700;font-size:.83rem;">Golden Card</div>
            </div>
          </label>
        </div>
      </div>

      <div class="fg">
        <label>Upload Deposit Proof <span style="color:var(--gold);">*</span></label>
        <div class="upzone" onclick="document.getElementById('rPayFile').click()" id="rUploadZone">
          <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
          <p style="margin-top:6px;font-size:.82rem;">Click to upload photo or PDF</p>
          <input type="file" id="rPayFile" accept="image/*,application/pdf" style="display:none;" onchange="previewRFile(event)"/>
        </div>
        <div id="rFilePrev"></div>
      </div>
    </div>
    <div class="mft">
      <button class="btn-outline" onclick="closeOv('reserveOv')">Cancel</button>
      <button class="btn-gold" onclick="submitReservation()" id="rSubmitBtn">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
        Send Reservation Request
      </button>
    </div>
  </div>
</div>
<!-- SHIPMENT UPDATE MODAL -->
<div class="ov" id="shipmentOv">
  <div class="modal" style="max-width:500px;">
    <div class="mhd">
      <h2>Update Shipment Status</h2>
      <button class="cls-btn" onclick="closeOv('shipmentOv')">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <div class="mbody">
      <div id="shipmentCarInfo" style="background:var(--s2);border-radius:10px;padding:12px 14px;margin-bottom:18px;"></div>
      <label style="font-size:.78rem;color:var(--muted);font-weight:600;display:block;margin-bottom:10px;text-transform:uppercase;letter-spacing:.5px;">Select Stage</label>
      <div id="shipmentStagesGrid" style="display:flex;flex-direction:column;gap:8px;margin-bottom:16px;"></div>
      <div class="fg" style="margin-top:4px;">
        <label>Note (optional)</label>
        <textarea class="fi" id="shipmentNote" rows="2" placeholder="e.g. Expected arrival in 5 days..."></textarea>
      </div>
    </div>
    <div class="mft">
      <button class="btn-outline" onclick="closeOv('shipmentOv')">Cancel</button>
      <button class="btn-gold" onclick="submitShipmentUpdate()">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
        Send Update
      </button>
    </div>
  </div>
</div>
<!-- AGENT RESERVATION DECISION MODAL -->
<div class="ov" id="reserveDecisionOv">
  <div class="modal" style="max-width:480px;">
    <div class="mhd">
      <h2>🔒 Reservation Request</h2>
      <button class="cls-btn" onclick="closeOv('reserveDecisionOv')">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <div class="mbody">
      <div id="reserveDecisionBody"></div>

      <div style="margin-top:18px;">
        <label style="font-size:.8rem;color:var(--muted);margin-bottom:6px;display:block;">Client visited in person?</label>
        <div style="display:flex;gap:10px;">
          <button id="visitedYes" onclick="setVisited(1)"
  style="flex:1;padding:9px;border:2px solid var(--br);border-radius:9px;background:var(--s2);color:var(--muted);font-family:'Barlow',sans-serif;font-weight:600;cursor:pointer;transition:all .2s;display:flex;align-items:center;justify-content:center;gap:6px;">
  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
  Yes, visited
</button>
<button id="visitedNo" onclick="setVisited(0)"
  style="flex:1;padding:9px;border:2px solid var(--br);border-radius:9px;background:var(--s2);color:var(--muted);font-family:'Barlow',sans-serif;font-weight:600;cursor:pointer;transition:all .2s;display:flex;align-items:center;justify-content:center;gap:6px;">
  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
  Not yet
</button>
        </div>
      </div>
    </div>
    <div class="mft">
      <button class="btn-danger" onclick="agentDecideReservation('refused')">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        Refuse
      </button>
      <button class="btn-success" onclick="agentDecideReservation('accepted')">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
        Confirm Reservation
      </button>
    </div>
  </div>
</div>
</body>
</html>