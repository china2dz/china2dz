<?php
session_start();
require_once 'config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.html'); exit;
}
$agents_data = $pdo->query("
    SELECT u.id, u.email, u.status, u.created_at,
           u.first_name, u.last_name, u.phone,
           ap.national_id, ap.id_card_name, ap.company_name,
           ap.rc_number, ap.rc_owner_name,
           ap.id_card_file, ap.rc_file,
           (SELECT COUNT(*) FROM cars WHERE agent_id = u.id AND status = 'available') AS listings,
           (SELECT ROUND(AVG(rating),1) FROM agent_reviews WHERE agent_id = u.id) AS rating,
           (SELECT COUNT(*) FROM agent_reports WHERE agent_id = u.id AND status = 'open') AS report_count
    FROM users u
    LEFT JOIN agent_profiles ap ON ap.user_id = u.id
    WHERE u.role = 'agent'
    ORDER BY u.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);
$reports_data = $pdo->query("
    SELECT r.*, 
           u.first_name AS agent_first, u.last_name AS agent_last,
           ru.first_name AS reporter_first, ru.last_name AS reporter_last
    FROM agent_reports r
    LEFT JOIN users u ON u.id = r.agent_id
    LEFT JOIN users ru ON ru.id = r.reporter_id
    WHERE r.status = 'open'
    ORDER BY r.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);
$payments_data = $pdo->query("
    SELECT s.*, u.email, u.first_name, u.last_name
    FROM subscriptions s
    JOIN users u ON u.id = s.user_id
    ORDER BY s.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);
$clients_data = $pdo->query("
    SELECT u.id, u.first_name, u.last_name, u.email, u.profile_photo, u.created_at,
           (SELECT COUNT(*) FROM reviews WHERE user_id = u.id) AS index_reviews,
           (SELECT COUNT(*) FROM car_reviews WHERE user_id = u.id AND parent_id IS NULL) AS car_reviews,
           (SELECT COUNT(*) FROM agent_reviews WHERE reviewer_id = u.id) AS dealer_reviews,
           (SELECT COUNT(*) FROM review_likes WHERE user_id = u.id) AS total_likes,
           (SELECT COUNT(*) FROM review_likes_index WHERE user_id = u.id) AS index_likes
    FROM users u
    WHERE u.role = 'client' OR u.role = 'user'
    ORDER BY u.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);
$admin_notifs = $pdo->query("
    SELECT n.*, u.first_name, u.last_name
    FROM notifications n
    LEFT JOIN users u ON u.id = n.sender_id
    WHERE n.title IN ('New Agent Registration', 'New Subscription Request', 'New Dealer Report', 'Reviews reported')
    ORDER BY n.created_at DESC
    LIMIT 30
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>AdminOS</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">
<style>
:root{
  --bg:#0d0f18;--surface:#141624;--surface2:#1c1f35;
  --border:#252840;--text:#e8eaf6;--muted:#7b7f9e;
  --accent:#5c6ef8;--blue:#4c9cf8;--green:#3ecf8e;
  --amber:#f5a623;--red:#f45b5b;
  --sw:240px;--r:12px;--tr:all .22s ease;
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth}
body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--text);display:flex;min-height:100vh;overflow-x:hidden;font-size:15px}
::-webkit-scrollbar{width:5px;height:5px}
::-webkit-scrollbar-track{background:transparent}
::-webkit-scrollbar-thumb{background:var(--border);border-radius:3px}

/* SIDEBAR */
.sidebar{width:var(--sw);background:var(--surface);border-right:1px solid var(--border);display:flex;flex-direction:column;position:fixed;top:0;left:0;height:100vh;z-index:100;transition:width .25s ease;overflow:hidden}
.sidebar.collapsed{width:64px}
.sidebar.collapsed .logo-text,.sidebar.collapsed .nav-item span:not(.badge),.sidebar.collapsed .nav-label,.sidebar.collapsed .admin-info{opacity:0;width:0;overflow:hidden;white-space:nowrap}
.sidebar.collapsed .nav-item{justify-content:center;padding:0 18px}
.sidebar.collapsed .badge{display:none}
.sid-head{display:flex;align-items:center;justify-content:space-between;padding:20px 16px;border-bottom:1px solid var(--border)}
.logo{display:flex;align-items:center;gap:10px}
.logo-icon{width:36px;height:36px;background:linear-gradient(135deg,var(--accent),#9b7ff8);border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;color:#fff}
.logo-icon svg{width:18px;height:18px}
.logo-text{font-family:'Syne',sans-serif;font-weight:800;font-size:1.15rem;letter-spacing:.5px;transition:var(--tr);white-space:nowrap}
.collapse-btn{background:none;border:none;color:var(--muted);cursor:pointer;padding:6px;border-radius:8px;transition:var(--tr);display:flex;flex-shrink:0}
.collapse-btn:hover{background:var(--surface2);color:var(--text)}
.collapse-btn svg{width:18px;height:18px}
.sid-nav{flex:1;padding:16px 10px;display:flex;flex-direction:column;gap:2px;overflow-y:auto}
.nav-label{font-size:.68rem;text-transform:uppercase;letter-spacing:1.2px;color:var(--muted);padding:10px 10px 4px;transition:var(--tr);white-space:nowrap}
.nav-item{display:flex;align-items:center;gap:12px;padding:10px 14px;border-radius:10px;text-decoration:none;color:var(--muted);font-weight:500;transition:var(--tr);position:relative;white-space:nowrap;cursor:pointer;border:none;background:none;font-family:'DM Sans',sans-serif;font-size:.9rem;width:100%;text-align:left}
.nav-item svg{width:18px;height:18px;flex-shrink:0}
.nav-item:hover{background:var(--surface2);color:var(--text)}
.nav-item.active{background:linear-gradient(90deg,rgba(92,110,248,.18),rgba(92,110,248,.06));color:#fff;border-left:3px solid var(--accent)}
.badge{margin-left:auto;background:var(--accent);color:#fff;font-size:.65rem;font-weight:700;padding:2px 7px;border-radius:20px;flex-shrink:0}
.badge.danger{background:var(--red)}
.sid-foot{padding:16px;border-top:1px solid var(--border)}
.admin-profile{display:flex;align-items:center;gap:10px}
.avatar{width:38px;height:38px;border-radius:50%;background:linear-gradient(135deg,var(--accent),#9b7ff8);display:flex;align-items:center;justify-content:center;font-family:'Syne',sans-serif;font-weight:700;color:#fff;font-size:.9rem;flex-shrink:0}
.avatar.sm{width:32px;height:32px;font-size:.78rem}
.admin-info{display:flex;flex-direction:column;transition:var(--tr);overflow:hidden;white-space:nowrap}
.admin-name{font-weight:600;font-size:.85rem}
.admin-role{font-size:.72rem;color:var(--muted)}

/* MAIN */
.main{margin-left:var(--sw);flex:1;display:flex;flex-direction:column;min-height:100vh;transition:margin-left .25s ease}
.main.expanded{margin-left:64px}
.topbar{height:60px;background:var(--surface);border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;padding:0 24px;position:sticky;top:0;z-index:90}
.topbar-left{display:flex;align-items:center;gap:14px}
.topbar-right{display:flex;align-items:center;gap:10px}
.icon-btn{background:none;border:none;color:var(--muted);cursor:pointer;padding:8px;border-radius:9px;transition:var(--tr);display:flex;position:relative}
.icon-btn:hover{background:var(--surface2);color:var(--text)}
.icon-btn svg{width:20px;height:20px}
.breadcrumb{display:flex;align-items:center;gap:6px;font-family:'Syne',sans-serif;font-weight:700;font-size:1rem}
.bc-root{cursor:pointer;color:var(--muted);transition:var(--tr)}
.bc-root:hover{color:var(--text)}
.bc-sep{color:var(--border);display:none;align-items:center}
.bc-sep svg{width:14px;height:14px}
.bc-child{color:var(--text);display:none}
.notif-dot{width:7px;height:7px;background:var(--red);border-radius:50%;position:absolute;top:6px;right:6px;border:2px solid var(--surface);display:none}

/* SEARCH OVERLAY */
.search-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:300;align-items:flex-start;justify-content:center;padding-top:80px;backdrop-filter:blur(6px)}
.search-overlay.open{display:flex}
.search-modal{background:var(--surface);border:1px solid var(--border);border-radius:16px;width:560px;max-width:95vw;overflow:hidden;animation:fadeSlide .2s ease}
.search-input-wrap{display:flex;align-items:center;gap:12px;padding:16px 20px;border-bottom:1px solid var(--border)}
.search-input-wrap svg{width:18px;height:18px;color:var(--muted);flex-shrink:0}
.search-input-wrap input{flex:1;background:none;border:none;outline:none;color:var(--text);font-family:'DM Sans',sans-serif;font-size:1rem}
.search-input-wrap input::placeholder{color:var(--muted)}
.search-input-wrap kbd{background:var(--surface2);border:1px solid var(--border);border-radius:6px;padding:2px 8px;font-size:.72rem;color:var(--muted)}
.search-results{max-height:360px;overflow-y:auto}
.sr-item{display:flex;align-items:center;gap:12px;padding:12px 20px;cursor:pointer;transition:var(--tr);border-bottom:1px solid var(--border)}
.sr-item:last-child{border-bottom:none}
.sr-item:hover{background:var(--surface2)}
.sr-icon{width:34px;height:34px;border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.sr-icon svg{width:15px;height:15px}
.sr-icon.blue{background:rgba(76,156,248,.15);color:var(--blue)}
.sr-icon.green{background:rgba(62,207,142,.15);color:var(--green)}
.sr-icon.red{background:rgba(244,91,91,.15);color:var(--red)}
.sr-body{flex:1}
.sr-body strong{font-size:.85rem;font-weight:600;display:block}
.sr-body span{font-size:.75rem;color:var(--muted)}
.sr-type{font-size:.68rem;text-transform:uppercase;letter-spacing:.8px;color:var(--muted);background:var(--surface2);padding:2px 8px;border-radius:20px}
.search-empty{padding:32px;text-align:center;color:var(--muted);font-size:.875rem}

/* PAGES */
.pages-wrap{padding:28px;flex:1}
.page{display:none;animation:fadeSlide .3s ease}
.page.active{display:block}
@keyframes fadeSlide{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}
.page-hd{margin-bottom:28px}
.page-hd h1{font-family:'Syne',sans-serif;font-size:1.7rem;font-weight:800;margin-bottom:4px}
.page-hd p{color:var(--muted);font-size:.9rem}

/* STAT CARDS */
.stats-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:16px;margin-bottom:24px}
.stat-card{background:var(--surface);border:1px solid var(--border);border-radius:var(--r);padding:20px;display:flex;align-items:center;gap:14px;transition:var(--tr)}
.stat-card.clickable{cursor:pointer}
.stat-card.clickable:hover{border-color:var(--accent);transform:translateY(-2px);box-shadow:0 8px 24px rgba(0,0,0,.25)}
.stat-icon{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.stat-icon svg{width:20px;height:20px}
.stat-icon.blue{background:rgba(76,156,248,.15);color:var(--blue)}
.stat-icon.green{background:rgba(62,207,142,.15);color:var(--green)}
.stat-icon.amber{background:rgba(245,166,35,.15);color:var(--amber)}
.stat-icon.red{background:rgba(244,91,91,.15);color:var(--red)}
.stat-info{flex:1;display:flex;flex-direction:column}
.stat-value{font-family:'Syne',sans-serif;font-size:1.8rem;font-weight:800;line-height:1}
.stat-label{font-size:.75rem;color:var(--muted);margin-top:2px}
.stat-trend{font-size:.72rem;font-weight:600;display:flex;align-items:center;gap:3px}
.stat-trend svg{width:13px;height:13px}
.stat-trend.up{color:var(--green)}
.stat-trend.warn{color:var(--amber)}
.stat-trend.down{color:var(--red)}

/* CARD */
.card{background:var(--surface);border:1px solid var(--border);border-radius:var(--r);overflow:hidden}
.card-head{display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid var(--border)}
.card-head h2{font-family:'Syne',sans-serif;font-size:.95rem;font-weight:700;display:flex;align-items:center;gap:8px}
.card-head h2 svg{width:16px;height:16px;color:var(--accent)}
.card-sub{font-size:.73rem;color:var(--muted)}

/* DASHBOARD BOTTOM */
.dash-bot{display:grid;grid-template-columns:1fr 300px;gap:20px}
@media(max-width:900px){.dash-bot{grid-template-columns:1fr}}

/* ACTIVITY */
.act-list{list-style:none}
.act-item{display:flex;align-items:center;gap:14px;padding:14px 20px;border-bottom:1px solid var(--border);transition:var(--tr);cursor:pointer}
.act-item:last-child{border-bottom:none}
.act-item:hover{background:var(--surface2)}
.act-ic{width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.act-ic svg{width:16px;height:16px}
.act-ic.blue{background:rgba(76,156,248,.15);color:var(--blue)}
.act-ic.green{background:rgba(62,207,142,.15);color:var(--green)}
.act-ic.red{background:rgba(244,91,91,.15);color:var(--red)}
.act-ic.amber{background:rgba(245,166,35,.15);color:var(--amber)}
.act-body{flex:1}
.act-body strong{display:block;font-size:.875rem;font-weight:600}
.act-body span{font-size:.78rem;color:var(--muted)}
.act-right{display:flex;flex-direction:column;align-items:flex-end;gap:4px;flex-shrink:0}
.act-right time{font-size:.72rem;color:var(--muted);white-space:nowrap}
.act-arr{width:14px;height:14px;color:var(--border);transition:var(--tr)}
.act-item:hover .act-arr{color:var(--accent);transform:translateX(2px)}

/* QUICK ACTIONS */
.qa-btns{padding:16px;display:grid;grid-template-columns:1fr 1fr;gap:10px}
.qa-btn{background:var(--surface2);border:1px solid var(--border);border-radius:10px;color:var(--text);padding:14px 10px;display:flex;flex-direction:column;align-items:center;gap:8px;cursor:pointer;font-family:'DM Sans',sans-serif;font-size:.78rem;font-weight:500;transition:var(--tr)}
.qa-btn svg{width:20px;height:20px;color:var(--accent)}
.qa-btn:hover{background:rgba(92,110,248,.12);border-color:var(--accent);color:#fff}

/* NOTIFICATIONS */
.btn-ghost{background:none;border:1px solid var(--border);color:var(--muted);padding:6px 14px;border-radius:8px;cursor:pointer;font-size:.8rem;transition:var(--tr);font-family:'DM Sans',sans-serif}
.btn-ghost:hover{border-color:var(--accent);color:var(--text)}
.notif-list{list-style:none}
.notif-item{display:flex;align-items:flex-start;gap:14px;padding:16px 20px;border-bottom:1px solid var(--border);transition:var(--tr);position:relative;cursor:pointer}
.notif-item:last-child{border-bottom:none}
.notif-item.unread{background:rgba(92,110,248,.05)}
.notif-item.unread::before{content:'';width:6px;height:6px;background:var(--accent);border-radius:50%;position:absolute;top:20px;left:8px}
.notif-item:hover{background:var(--surface2)}
.notif-ic{width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:2px}
.notif-ic svg{width:17px;height:17px}
.notif-ic.blue{background:rgba(76,156,248,.15);color:var(--blue)}
.notif-ic.green{background:rgba(62,207,142,.15);color:var(--green)}
.notif-ic.red{background:rgba(244,91,91,.15);color:var(--red)}
.notif-ic.amber{background:rgba(245,166,35,.15);color:var(--amber)}
.notif-body{flex:1}
.notif-body strong{font-size:.875rem;font-weight:600;display:block}
.notif-body p{font-size:.8rem;color:var(--muted);margin-top:2px}
.notif-body time{font-size:.72rem;color:var(--muted);margin-top:4px;display:block}
.notif-x{background:none;border:none;color:var(--muted);cursor:pointer;padding:4px;border-radius:6px;transition:var(--tr);display:flex;flex-shrink:0;z-index:2}
.notif-x:hover{background:var(--surface2);color:var(--red)}
.notif-x svg{width:15px;height:15px}

/* FILTER BAR */
.filter-bar{display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;gap:14px;flex-wrap:wrap}
.search-box{display:flex;align-items:center;gap:10px;background:var(--surface);border:1px solid var(--border);border-radius:10px;padding:9px 16px;flex:1;max-width:340px}
.search-box svg{width:16px;height:16px;color:var(--muted)}
.search-box input{background:none;border:none;outline:none;color:var(--text);font-family:'DM Sans',sans-serif;font-size:.875rem;width:100%}
.search-box input::placeholder{color:var(--muted)}
.filter-tabs{display:flex;gap:4px;background:var(--surface);border:1px solid var(--border);border-radius:10px;padding:4px}
.f-tab{background:none;border:none;color:var(--muted);padding:6px 16px;border-radius:7px;cursor:pointer;font-family:'DM Sans',sans-serif;font-size:.82rem;font-weight:500;transition:var(--tr)}
.f-tab.active{background:var(--accent);color:#fff}
.f-tab:hover:not(.active){background:var(--surface2);color:var(--text)}

/* TABLE */
.data-table{width:100%;border-collapse:collapse}
.data-table thead{background:var(--surface2)}
.data-table th{text-align:left;padding:13px 20px;font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--muted);border-bottom:1px solid var(--border)}
.data-table td{padding:14px 20px;font-size:.875rem;border-bottom:1px solid var(--border);vertical-align:middle}
.data-table tr:last-child td{border-bottom:none}
.data-table tbody tr{transition:var(--tr);cursor:pointer}
.data-table tbody tr:hover{background:var(--surface2)}
.status{display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:20px;font-size:.73rem;font-weight:600}
.status::before{content:'';width:6px;height:6px;border-radius:50%}
.status.active{background:rgba(62,207,142,.12);color:var(--green)}
.status.active::before{background:var(--green)}
.status.pending{background:rgba(245,166,35,.12);color:var(--amber)}
.status.pending::before{background:var(--amber)}
.status.blocked{background:rgba(244,91,91,.12);color:var(--red)}
.status.blocked::before{background:var(--red)}
.status.approved{background:rgba(62,207,142,.12);color:var(--green)}
.status.approved::before{background:var(--green)}
.status.rejected{background:rgba(244,91,91,.12);color:var(--red)}
.status.rejected::before{background:var(--red)}
.status.inactive{background:rgba(123,127,158,.12);color:var(--muted)}
.status.inactive::before{background:var(--muted)}
.agent-cell{display:flex;align-items:center;gap:10px}
.agent-av{width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,var(--accent),#9b7ff8);display:flex;align-items:center;justify-content:center;font-size:.72rem;font-weight:700;color:#fff;flex-shrink:0}
.tbl-actions{display:flex;align-items:center;gap:6px;flex-wrap:wrap}
.tbl-btn{border:none;border-radius:8px;padding:6px 10px;cursor:pointer;font-size:.75rem;font-weight:600;display:flex;align-items:center;gap:5px;transition:var(--tr);font-family:'DM Sans',sans-serif}
.tbl-btn svg{width:13px;height:13px}
.tbl-btn.approve{background:rgba(62,207,142,.15);color:var(--green)}
.tbl-btn.approve:hover{background:rgba(62,207,142,.3)}
.tbl-btn.reject{background:rgba(244,91,91,.15);color:var(--red)}
.tbl-btn.reject:hover{background:rgba(244,91,91,.3)}
.tbl-btn.block{background:rgba(244,91,91,.15);color:var(--red)}
.tbl-btn.block:hover{background:rgba(244,91,91,.3)}
.tbl-btn.unblock{background:rgba(62,207,142,.15);color:var(--green)}
.tbl-btn.unblock:hover{background:rgba(62,207,142,.3)}
.tbl-btn.view{background:rgba(76,156,248,.15);color:var(--blue)}
.tbl-btn.view:hover{background:rgba(76,156,248,.3)}
.tbl-btn.ignore{background:rgba(123,127,158,.15);color:var(--muted)}
.tbl-btn.ignore:hover{background:rgba(123,127,158,.25)}
.proof-link{display:inline-flex;align-items:center;gap:6px;color:var(--blue);font-size:.8rem;cursor:pointer;padding:4px 8px;border-radius:6px;transition:var(--tr)}
.proof-link:hover{background:rgba(76,156,248,.12)}
.proof-link svg{width:14px;height:14px}
.empty-state{text-align:center;padding:48px 20px;color:var(--muted)}
.empty-state svg{width:40px;height:40px;margin-bottom:12px;opacity:.4;display:block;margin-left:auto;margin-right:auto}
.empty-state p{font-size:.875rem}

/* AGENT PROFILE */
.back-btn{background:none;border:1px solid var(--border);color:var(--muted);padding:8px 16px;border-radius:10px;cursor:pointer;font-family:'DM Sans',sans-serif;font-size:.85rem;display:inline-flex;align-items:center;gap:8px;transition:var(--tr);margin-bottom:20px}
.back-btn:hover{border-color:var(--accent);color:var(--text)}
.back-btn svg{width:16px;height:16px}
.profile-hero{background:var(--surface);border:1px solid var(--border);border-radius:var(--r);padding:28px;display:flex;align-items:center;gap:24px;margin-bottom:20px;flex-wrap:wrap}
.profile-av-lg{width:72px;height:72px;border-radius:50%;background:linear-gradient(135deg,var(--accent),#9b7ff8);display:flex;align-items:center;justify-content:center;font-family:'Syne',sans-serif;font-size:1.6rem;font-weight:800;color:#fff;flex-shrink:0}
.profile-info{flex:1;min-width:200px}
.profile-info h2{font-family:'Syne',sans-serif;font-size:1.4rem;font-weight:800;margin-bottom:4px}
.profile-meta{display:flex;gap:16px;flex-wrap:wrap;font-size:.82rem;color:var(--muted);margin-bottom:10px}
.profile-meta span{display:flex;align-items:center;gap:5px}
.profile-meta svg{width:13px;height:13px}
.profile-actions{display:flex;gap:8px;flex-wrap:wrap}
.profile-grid{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px}
@media(max-width:700px){.profile-grid{grid-template-columns:1fr}}
.profile-card{background:var(--surface);border:1px solid var(--border);border-radius:var(--r);overflow:hidden}
.profile-card-head{padding:14px 20px;border-bottom:1px solid var(--border);font-family:'Syne',sans-serif;font-weight:700;font-size:.9rem;display:flex;align-items:center;gap:8px}
.profile-card-head svg{width:15px;height:15px;color:var(--accent)}
.profile-card-body{padding:20px}
.info-row{display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid var(--border);font-size:.85rem}
.info-row:last-child{border-bottom:none}
.info-row .lbl{color:var(--muted)}
.info-row .val{font-weight:500}

/* MODAL */
.modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.65);z-index:200;align-items:center;justify-content:center;backdrop-filter:blur(4px)}
.modal-overlay.open{display:flex}
.modal{background:var(--surface);border:1px solid var(--border);border-radius:16px;width:420px;max-width:95vw;animation:fadeSlide .2s ease}
.modal-head{display:flex;align-items:center;justify-content:space-between;padding:18px 20px;border-bottom:1px solid var(--border)}
.modal-head h3{font-family:'Syne',sans-serif;font-weight:700}
.modal-close{background:none;border:none;color:var(--muted);cursor:pointer;padding:6px;border-radius:8px;display:flex;transition:var(--tr)}
.modal-close:hover{background:var(--surface2);color:var(--red)}
.modal-close svg{width:17px;height:17px}
.modal-body{padding:20px}
.proof-ph{width:100%;height:180px;background:var(--surface2);border:2px dashed var(--border);border-radius:12px;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:10px;color:var(--muted);margin-bottom:20px}
.proof-ph svg{width:36px;height:36px}
.modal-actions{display:flex;gap:10px}
.btn{flex:1;padding:11px 20px;border-radius:10px;border:none;cursor:pointer;font-family:'DM Sans',sans-serif;font-weight:600;font-size:.875rem;display:flex;align-items:center;justify-content:center;gap:7px;transition:var(--tr)}
.btn svg{width:16px;height:16px}
.btn.green{background:rgba(62,207,142,.15);color:var(--green)}
.btn.green:hover{background:rgba(62,207,142,.3)}
.btn.red{background:rgba(244,91,91,.15);color:var(--red)}
.btn.red:hover{background:rgba(244,91,91,.3)}

/* MONITORING */
.monitor-grid{display:grid;grid-template-columns:1fr 1fr;gap:20px}
@media(max-width:700px){.monitor-grid{grid-template-columns:1fr}}
.plan-bars{padding:20px;display:flex;flex-direction:column;gap:18px}
.plan-bar-row{display:flex;align-items:center;gap:12px;font-size:.83rem}
.plan-bar-row>span:first-child{width:55px;color:var(--muted);font-weight:500}
.plan-bar-row>span:last-child{width:24px;text-align:right;font-weight:700}
.bar-wrap{flex:1;height:8px;background:var(--surface2);border-radius:4px;overflow:hidden}
.bar{height:100%;border-radius:4px;transition:width .6s ease}
.donut-wrap{padding:20px;display:flex;align-items:center;justify-content:center;gap:28px}
.donut-chart{width:120px;height:120px;transform:rotate(-90deg)}
.donut-legend{display:flex;flex-direction:column;gap:10px;font-size:.82rem}
.dot-lg{display:inline-block;width:10px;height:10px;border-radius:50%;margin-right:7px}

/* TOAST */
.toast-wrap{position:fixed;bottom:24px;right:24px;display:flex;flex-direction:column;gap:10px;z-index:999}
.toast{background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:14px 18px;display:flex;align-items:center;gap:12px;font-size:.85rem;box-shadow:0 8px 32px rgba(0,0,0,.35);animation:slideIn .3s ease;min-width:260px;max-width:360px}
.toast svg{width:16px;height:16px;flex-shrink:0}
.toast.success{border-left:4px solid var(--green)}
.toast.success svg{color:var(--green)}
.toast.error{border-left:4px solid var(--red)}
.toast.error svg{color:var(--red)}
.toast.info{border-left:4px solid var(--blue)}
.toast.info svg{color:var(--blue)}
@keyframes slideIn{from{opacity:0;transform:translateX(30px)}to{opacity:1;transform:translateX(0)}}
@keyframes slideOut{to{opacity:0;transform:translateX(40px)}}
@media(max-width:640px){.pages-wrap{padding:16px}.stats-grid{grid-template-columns:1fr 1fr}.stat-trend{display:none}}
</style>
</head>
<body>

<!-- SVG ICON DEFINITIONS (inline, no external deps) -->
<svg style="display:none" xmlns="http://www.w3.org/2000/svg">
  <symbol id="ic-shield-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></symbol>
  <symbol id="ic-panel-left-close" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="9" y1="3" x2="9" y2="21"/><polyline points="15 8 11 12 15 16"/></symbol>
  <symbol id="ic-layout-dashboard" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></symbol>
  <symbol id="ic-bell" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></symbol>
  <symbol id="ic-users" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></symbol>
  <symbol id="ic-credit-card" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></symbol>
  <symbol id="ic-activity" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></symbol>
  <symbol id="ic-flag" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/></symbol>
  <symbol id="ic-menu" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></symbol>
  <symbol id="ic-chevron-right" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></symbol>
  <symbol id="ic-search" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></symbol>
  <symbol id="ic-trending-up" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></symbol>
  <symbol id="ic-clock" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></symbol>
  <symbol id="ic-alert-circle" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></symbol>
  <symbol id="ic-alert-triangle" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></symbol>
  <symbol id="ic-zap" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></symbol>
  <symbol id="ic-user-plus" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="17" y1="11" x2="23" y2="11"/></symbol>
  <symbol id="ic-receipt" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></symbol>
  <symbol id="ic-check-circle" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></symbol>
  <symbol id="ic-user-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><polyline points="17 11 19 13 23 9"/></symbol>
  <symbol id="ic-x" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></symbol>
  <symbol id="ic-arrow-left" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></symbol>
  <symbol id="ic-image" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></symbol>
  <symbol id="ic-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></symbol>
  <symbol id="ic-ban" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></symbol>
  <symbol id="ic-unlock" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 9.9-1"/></symbol>
  <symbol id="ic-eye" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></symbol>
  <symbol id="ic-mail" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></symbol>
  <symbol id="ic-phone" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.41 2 2 0 0 1 3.6 1.24h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.91a16 16 0 0 0 6 6l.92-.92a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 21.73 16.92z"/></symbol>
  <symbol id="ic-map-pin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></symbol>
  <symbol id="ic-info" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></symbol>
  <symbol id="ic-bar-chart-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></symbol>
  <symbol id="ic-file-image" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><circle cx="10" cy="13" r="2"/><path d="m20 17-1.09-1.09a2 2 0 0 0-2.82 0L10 22"/></symbol>
  <symbol id="ic-inbox" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></symbol>
  <symbol id="ic-search-x" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="8" y1="8" x2="14" y2="14"/><line x1="14" y1="8" x2="8" y2="14"/></symbol>
  <symbol id="ic-x-circle" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></symbol>
</svg>

<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">
  <div class="sid-head">
    <div class="logo">
      <div class="logo-icon"><svg><use href="#ic-shield-check"/></svg></div>
      <span class="logo-text">AdminOS</span>
    </div>
    <button class="collapse-btn" id="collapseBtn"><svg><use href="#ic-panel-left-close"/></svg></button>
  </div>
  <nav class="sid-nav">
    <span class="nav-label">Overview</span>
    <button class="nav-item active" data-page="dashboard"><svg><use href="#ic-layout-dashboard"/></svg><span>Dashboard</span></button>
    <button class="nav-item" data-page="notifications"><svg><use href="#ic-bell"/></svg><span>Notifications</span><span class="badge" id="notifBadge">4</span></button>
    <span class="nav-label">Management</span>
    <button class="nav-item" data-page="agents"><svg><use href="#ic-users"/></svg><span>Agents</span></button>
    <button class="nav-item" data-page="clients"><svg><use href="#ic-users"/></svg><span>Clients</span></button>
    <button class="nav-item" data-page="subscriptions"><svg><use href="#ic-credit-card"/></svg><span>Subscriptions</span></button>
    <span class="nav-label">System</span>
    <button class="nav-item" data-page="monitoring"><svg><use href="#ic-activity"/></svg><span>Monitoring</span></button>
    <button class="nav-item" data-page="reports"><svg><use href="#ic-flag"/></svg><span>Reports</span><span class="badge danger" id="reportBadge">3</span></button>
  </nav>
  <div class="sid-foot">
    <div class="admin-profile">
      <div class="avatar">A</div>
      <div class="admin-info"><span class="admin-name">Super Admin</span><span class="admin-role">System Controller</span></div>
    </div>
  </div>
</aside>

<!-- MAIN -->
<main class="main" id="main">
  <header class="topbar">
    <div class="topbar-left">
      <button class="icon-btn" id="menuBtn"><svg><use href="#ic-menu"/></svg></button>
      <div class="breadcrumb">
        <span class="bc-root" id="bcRoot">Dashboard</span>
        <span class="bc-sep" id="bcSep"><svg><use href="#ic-chevron-right"/></svg></span>
        <span class="bc-child" id="bcChild"></span>
      </div>
    </div>
    <div class="topbar-right">
      <button class="icon-btn" id="searchBtn"><svg><use href="#ic-search"/></svg></button>
      <button class="icon-btn" id="notifBtn"><svg><use href="#ic-bell"/></svg><span class="notif-dot" id="notifDot"></span></button>
      <div class="avatar sm">A</div>
    </div>
  </header>

  <!-- SEARCH OVERLAY -->
  <div class="search-overlay" id="searchOverlay">
    <div class="search-modal">
      <div class="search-input-wrap">
        <svg><use href="#ic-search"/></svg>
        <input type="text" id="globalSearch" placeholder="Search agents, subscriptions, reports…" autocomplete="off"/>
        <kbd>ESC</kbd>
      </div>
      <div class="search-results" id="searchResults"></div>
    </div>
  </div>

  <div class="pages-wrap" id="pagesWrap">

    <!-- DASHBOARD -->
    <section class="page active" id="page-dashboard">
      <div class="page-hd"><h1>Welcome back, Admin 👋</h1><p>Here's what's happening across your system today.</p></div>
      <div class="stats-grid">
        <div class="stat-card clickable" id="sc-agents">
          <div class="stat-icon blue"><svg><use href="#ic-users"/></svg></div>
          <div class="stat-info"><span class="stat-value" id="statAgents">8</span><span class="stat-label">Total Agents</span></div>
          <div class="stat-trend up"><svg><use href="#ic-trending-up"/></svg> +3</div>
        </div>
        <div class="stat-card clickable" id="sc-pending">
          <div class="stat-icon amber"><svg><use href="#ic-clock"/></svg></div>
          <div class="stat-info"><span class="stat-value" id="statPending">3</span><span class="stat-label">Pending Agents</span></div>
          <div class="stat-trend warn"><svg><use href="#ic-alert-circle"/></svg> Review</div>
        </div>
        <div class="stat-card clickable" id="sc-subs">
          <div class="stat-icon green"><svg><use href="#ic-credit-card"/></svg></div>
          <div class="stat-info"><span class="stat-value" id="statSubs">1</span><span class="stat-label">Active Subs</span></div>
          <div class="stat-trend up"><svg><use href="#ic-trending-up"/></svg> +5</div>
        </div>
        <div class="stat-card clickable" id="sc-reports">
          <div class="stat-icon red"><svg><use href="#ic-flag"/></svg></div>
          <div class="stat-info"><span class="stat-value" id="statReports">3</span><span class="stat-label">Open Reports</span></div>
          <div class="stat-trend down"><svg><use href="#ic-alert-triangle"/></svg> Urgent</div>
        </div>
      </div>
      <div class="dash-bot">
        <div class="card">
          <div class="card-head"><h2><svg><use href="#ic-zap"/></svg> Quick Actions</h2></div>
          <div class="qa-btns">
            <button class="qa-btn" id="qa-pending"><svg><use href="#ic-user-check"/></svg><span>Review Agents</span></button>
            <button class="qa-btn" id="qa-subs"><svg><use href="#ic-credit-card"/></svg><span>Check Subs</span></button>
            <button class="qa-btn" id="qa-reports"><svg><use href="#ic-flag"/></svg><span>Open Reports</span></button>
            <button class="qa-btn" id="qa-monitor"><svg><use href="#ic-activity"/></svg><span>Monitoring</span></button>
          </div>
        </div>
      </div>
    </section>

    <!-- NOTIFICATIONS -->
    <section class="page" id="page-notifications">
      <div class="page-hd"><h1>Notifications</h1><p>All system alerts and updates in one place.</p></div>
      <div class="card">
        <div class="card-head">
          <h2><svg><use href="#ic-bell"/></svg> Inbox</h2>
          <button class="btn-ghost" id="markAllRead">Mark all read</button>
        </div>
        <ul class="notif-list" id="notifList">
          <?php if(empty($admin_notifs)): ?>
<li style="padding:24px;text-align:center;color:var(--muted)">No notifications yet</li>
<?php else: foreach($admin_notifs as $n): ?>
<li class="notif-item <?= $n['is_read'] ? '' : 'unread' ?>">
  <div class="notif-ic <?= $n['type']==='success' ? 'green' : ($n['type']==='warning' ? 'amber' : 'blue') ?>">
    <svg><use href="#ic-bell"/></svg>
  </div>
  <div class="notif-body">
    <strong><?= htmlspecialchars($n['title']) ?></strong>
    <p><?= htmlspecialchars($n['message']) ?></p>
    <time><?= $n['created_at'] ?></time>
  </div>
  <button class="notif-x" data-nid="<?= $n['id'] ?>"><svg><use href="#ic-x"/></svg></button>
</li>
<?php endforeach; endif; ?>
        </ul>
      </div>
    </section>

    <!-- AGENTS -->
    <section class="page" id="page-agents">
      <div class="page-hd"><h1>Agents</h1><p>Manage agent accounts — approve, block, or reject.</p></div>
      <div class="filter-bar">
        <div class="search-box"><svg><use href="#ic-search"/></svg><input type="text" placeholder="Search agents…" id="agentSearch"/></div>
        <div class="filter-tabs">
          <button class="f-tab active" data-filter="all">All</button>
          <button class="f-tab" data-filter="pending">Pending</button>
          <button class="f-tab" data-filter="active">Active</button>
          <button class="f-tab" data-filter="blocked">Blocked</button>
        </div>
      </div>
      <div class="card">
        <table class="data-table">
          <thead><tr><th>Agent</th><th>Email</th><th>Joined</th><th>Status</th><th>Actions</th></tr></thead>
          <tbody id="agentsBody"></tbody>
        </table>
      </div>
    </section>

    <!-- AGENT PROFILE -->
    <section class="page" id="page-agent-profile">
      <button class="back-btn" id="backBtn"><svg><use href="#ic-arrow-left"/></svg> Back</button>
      <div id="profileContent"></div>
    </section>
<!-- BLOCK MODAL -->
<div class="modal-overlay" id="blockModal">
  <div class="modal">
    <div class="modal-head">
      <h3><svg width="16" height="16"><use href="#ic-ban"/></svg> Suspend Agent</h3>
      <button class="modal-close" id="closeBlockModal"><svg><use href="#ic-x"/></svg></button>
    </div>
    <div class="modal-body">
      <p style="color:var(--muted);font-size:.85rem;margin-bottom:16px">Select suspension duration:</p>
      <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:20px">
        <label style="display:flex;align-items:center;gap:10px;cursor:pointer;padding:10px;border:1px solid var(--border);border-radius:10px">
          <input type="radio" name="blockDays" value="3" checked> <span>Warning — 3 days</span>
        </label>
        <label style="display:flex;align-items:center;gap:10px;cursor:pointer;padding:10px;border:1px solid var(--border);border-radius:10px">
          <input type="radio" name="blockDays" value="7"> <span>Medium — 7 days</span>
        </label>
        <label style="display:flex;align-items:center;gap:10px;cursor:pointer;padding:10px;border:1px solid var(--border);border-radius:10px">
          <input type="radio" name="blockDays" value="30"> <span>Severe — 30 days</span>
        </label>
        <label style="display:flex;align-items:center;gap:10px;cursor:pointer;padding:10px;border:1px solid var(--border);border-radius:10px">
          <input type="radio" name="blockDays" value="0"> <span>Permanent</span>
        </label>
      </div>
      <input type="text" id="blockReason" placeholder="Reason (optional)" 
             style="width:100%;background:var(--surface2);border:1px solid var(--border);border-radius:10px;padding:10px 14px;color:var(--text);font-family:'DM Sans',sans-serif;font-size:.875rem;outline:none;margin-bottom:16px">
      <div class="modal-actions">
        <button class="btn red" id="confirmBlock"><svg><use href="#ic-ban"/></svg> Confirm Suspend</button>
        <button class="btn" style="background:var(--surface2);color:var(--muted)" id="cancelBlock">Cancel</button>
      </div>
    </div>
  </div>
</div>
    <!-- SUBSCRIPTIONS -->
    <section class="page" id="page-subscriptions">
      <div class="page-hd"><h1>Subscriptions</h1><p>Review payment proofs and manage subscription requests.</p></div>
      <div class="card">
        <table class="data-table">
          <thead><tr><th>Agent</th><th>Plan</th><th>Payment Proof</th><th>Requested</th><th>Status</th><th>Actions</th></tr></thead>
          <tbody id="subsBody"></tbody>
        </table>
      </div>
      <div class="modal-overlay" id="proofModal">
        <div class="modal">
          <div class="modal-head"><h3>Payment Proof</h3><button class="modal-close" id="closeProofModal"><svg><use href="#ic-x"/></svg></button></div>
          <div class="modal-body">
            <div class="proof-ph"><svg><use href="#ic-image"/></svg><span id="proofFileName">proof_payment.jpg</span></div>
            <div class="modal-actions">
              <button class="btn green" id="modalApprove"><svg><use href="#ic-check"/></svg> Approve</button>
              <button class="btn red" id="modalReject"><svg><use href="#ic-x"/></svg> Reject</button>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- MONITORING -->
    <section class="page" id="page-monitoring">
      <div class="page-hd"><h1>System Monitoring</h1><p>Read-only overview of platform activity.</p></div>
      <div class="stats-grid">
        <div class="stat-card"><div class="stat-icon blue"><svg><use href="#ic-users"/></svg></div><div class="stat-info"><span class="stat-value" id="monTotal">0</span><span class="stat-label">Total Agents</span></div></div>
        <div class="stat-card"><div class="stat-icon green"><svg><use href="#ic-check-circle"/></svg></div><div class="stat-info"><span class="stat-value" id="monActive">0</span><span class="stat-label">Active</span></div></div>
        <div class="stat-card"><div class="stat-icon amber"><svg><use href="#ic-clock"/></svg></div><div class="stat-info"><span class="stat-value" id="monPending">0</span><span class="stat-label">Pending</span></div></div>
        <div class="stat-card"><div class="stat-icon red"><svg><use href="#ic-ban"/></svg></div><div class="stat-info"><span class="stat-value" id="monBlocked">0</span><span class="stat-label">Blocked</span></div></div>
      </div>
      <div class="monitor-grid">
        <div class="card"><div class="card-head"><h2><svg><use href="#ic-bar-chart-2"/></svg> Subscription Plans</h2></div><div class="plan-bars" id="planBars"></div></div>
        <div class="card">
          <div class="card-head"><h2><svg><use href="#ic-activity"/></svg> Status Breakdown</h2></div>
          <div class="donut-wrap">
            <svg viewBox="0 0 36 36" class="donut-chart">
              <circle cx="18" cy="18" r="15.9" fill="none" stroke="#1a1a2e" stroke-width="3"/>
              <circle id="donutActive"  cx="18" cy="18" r="15.9" fill="none" stroke="var(--green)" stroke-width="3" stroke-dasharray="0 100" stroke-dashoffset="25"/>
              <circle id="donutPending" cx="18" cy="18" r="15.9" fill="none" stroke="var(--amber)" stroke-width="3" stroke-dasharray="0 100" stroke-dashoffset="25"/>
              <circle id="donutBlocked" cx="18" cy="18" r="15.9" fill="none" stroke="var(--red)"   stroke-width="3" stroke-dasharray="0 100" stroke-dashoffset="25"/>
            </svg>
            <div class="donut-legend" id="donutLegend"></div>
          </div>
        </div>
      </div>
    </section>
<!-- CLIENTS -->
<section class="page" id="page-clients">
  <div class="page-hd"><h1>Clients</h1><p>All registered clients on the platform.</p></div>
  <div class="filter-bar">
    <div class="search-box"><svg><use href="#ic-search"/></svg><input type="text" placeholder="Search clients…" id="clientSearch"/></div>
  </div>
  <div class="card">
    <table class="data-table">
      <thead><tr><th>Client</th><th>Email</th><th>Joined</th><th>Reviews</th><th>Likes</th><th>Actions</th></tr></thead>
      <tbody id="clientsBody">
      <?php foreach($clients_data as $c):
        $name = htmlspecialchars($c['first_name'].' '.$c['last_name']);
        $initials = strtoupper(substr($c['first_name'],0,1).substr($c['last_name'],0,1));
        $totalReviews = $c['index_reviews'] + $c['car_reviews'] + $c['dealer_reviews'];
        $totalLikes = $c['total_likes'] + $c['index_likes'];
      ?>
      <tr onclick="openClientProfile('<?= $c['id'] ?>')" style="cursor:pointer">
        <td>
          <div class="agent-cell">
            <?php if(!empty($c['profile_photo'])): ?>
              <img src="<?= htmlspecialchars($c['profile_photo']) ?>" style="width:32px;height:32px;border-radius:50%;object-fit:cover;flex-shrink:0;">
            <?php else: ?>
              <div class="agent-av"><?= $initials ?></div>
            <?php endif; ?>
            <div>
              <div style="font-weight:600"><?= $name ?></div>
              <div style="font-size:.73rem;color:var(--muted)">#<?= $c['id'] ?></div>
            </div>
          </div>
        </td>
        <td style="color:var(--muted)"><?= htmlspecialchars($c['email']) ?></td>
        <td style="color:var(--muted)"><?= date('d M Y', strtotime($c['created_at'])) ?></td>
        <td><span style="color:var(--blue);font-weight:600"><?= $totalReviews ?></span></td>
        <td><span style="color:var(--amber);font-weight:600"><?= $totalLikes ?></span></td>
        <td>
          <button class="tbl-btn view" onclick="event.stopPropagation();openClientProfile('<?= $c['id'] ?>')">
            <svg><use href="#ic-eye"/></svg> View
          </button>
        </td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>

<!-- CLIENT PROFILE -->
<section class="page" id="page-client-profile">
  <button class="back-btn" id="clientBackBtn"><svg><use href="#ic-arrow-left"/></svg> Back</button>
  <div id="clientProfileContent"></div>
</section>
    <!-- REPORTS -->
    <section class="page" id="page-reports">
      <div class="page-hd"><h1>Reports</h1><p>Handle complaints and take action on violations.</p></div>
      <div class="card">
        <table class="data-table">
          <thead><tr><th>Reporter</th><th>Agent Reported</th><th>Reason</th><th>Date</th><th>Actions</th></tr></thead>
          <tbody id="reportsBody"></tbody>
        </table>
      </div>
    </section>

  </div><!-- /pages-wrap -->
</main>

<div class="toast-wrap" id="toastWrap"></div>

<script>
// ═══════════════════════════ ICON HELPER ═══════════════════════════
// Map old lucide names -> our symbol ids
const ICONS = {
  'check-circle':'ic-check-circle','x-circle':'ic-x-circle','info':'ic-info',
  'users':'ic-users','credit-card':'ic-credit-card','flag':'ic-flag',
  'check':'ic-check','x':'ic-x','ban':'ic-ban','unlock':'ic-unlock',
  'eye':'ic-eye','search-x':'ic-search-x','inbox':'ic-inbox',
  'file-image':'ic-file-image','receipt':'ic-receipt','user-plus':'ic-user-plus',
  'mail':'ic-mail','phone':'ic-phone','map-pin':'ic-map-pin',
  'info':'ic-info','bar-chart-2':'ic-bar-chart-2','credit-card':'ic-credit-card'
};
function svgIcon(name){
  const id=ICONS[name]||('ic-'+name);
  return '<svg><use href="#'+id+'"/></svg>';
}

// ═══════════════════════════ DATA ═══════════════════════════
let agents = [];
let subscriptions = [];
let reports = [];

async function loadData() {
  agents = <?php echo json_encode($agents_data); ?>.map(a => ({
    id:       String(a.id),
    name:     a.first_name + ' ' + a.last_name,
    email:    a.email,
    phone:    a.phone,
    wilaya:   '—',
    status:   a.status,
    joined:   a.created_at,
    plan:     '—',
    listings: a.listings || 0,
    rating:   a.rating || 0,
    report_count: a.report_count || 0,
    national_id:   a.national_id,
    id_card_name:  a.id_card_name,
    company_name:  a.company_name,
    rc_number:     a.rc_number,
    rc_owner_name: a.rc_owner_name,
    id_card_file:  a.id_card_file,
    rc_file:       a.rc_file,
}));

reports = <?php echo json_encode($reports_data); ?>.map(r => ({
    id:        String(r.id),
    agentId:   String(r.agent_id),
    agentName: r.agent_first + ' ' + r.agent_last,
    reporter:  r.reporter_first ? r.reporter_first + ' ' + r.reporter_last : 'Anonymous',
    reason:    r.reason,
    details:   r.details || '',
    date:      r.created_at,
    status:    r.status
}));

  subscriptions = <?php echo json_encode($payments_data); ?>.map(p => ({
    id:        String(p.id),
    agentId:   String(p.user_id),
    agentName: p.first_name + ' ' + p.last_name,
    plan:      p.plan_type || 'Basic',
    proof:     p.proof_file || 'proof.jpg',
    requested: p.created_at,
    status:    p.status
  }));

  renderAgents(curFilter, '');
  renderSubscriptions();
  renderReports();
  updateStats();
}
document.addEventListener('DOMContentLoaded', () =>loadData());

let curFilter='all', activeSubId=null, prevPage='agents';
let activeBlockAgentId = null, activeBlockFromReport = false, activeBlockReportId = null;
// ═══════════════════════════ UTILS ═══════════════════════════
const fmtDate=d=>new Date(d).toLocaleDateString('en-GB',{day:'2-digit',month:'short',year:'numeric'});
const cap=s=>s?s[0].toUpperCase()+s.slice(1):'';
const ini=n=>n.split(' ').map(w=>w[0]).join('').slice(0,2).toUpperCase();
const G=id=>document.getElementById(id);

function toast(msg,type='info'){
  const icons={success:'check-circle',error:'x-circle',info:'info'};
  const t=document.createElement('div');
  t.className='toast '+type;
  t.innerHTML=svgIcon(icons[type])+'<span>'+msg+'</span>';
  G('toastWrap').appendChild(t);
  setTimeout(()=>{t.style.animation='slideOut .3s ease forwards';setTimeout(()=>t.remove(),300);},3000);
}

// ═══════════════════════════ NAVIGATION ═══════════════════════════
const TITLES={dashboard:'Dashboard',notifications:'Notifications',agents:'Agents','agent-profile':'Agents',clients:'Clients','client-profile':'Clients',subscriptions:'Subscriptions',monitoring:'Monitoring',reports:'Reports'};
function goTo(page,opts={}){
  document.querySelectorAll('.page').forEach(p=>p.classList.remove('active'));
  const target=G('page-'+page);
  if(target) target.classList.add('active');

  document.querySelectorAll('.nav-item[data-page]').forEach(b=>{
    b.classList.toggle('active',b.dataset.page===(page.startsWith('agent-')?'agents':page));
  });

  const bcRoot=G('bcRoot'),bcSep=G('bcSep'),bcChild=G('bcChild');
  if(page==='agent-profile'&&opts.name){
    bcRoot.textContent='Agents';
    bcSep.style.cssText='display:flex';bcChild.style.cssText='display:inline';
    bcChild.textContent=opts.name;
  } else {
    bcRoot.textContent=TITLES[page]||page;
    bcSep.style.cssText='display:none';bcChild.style.cssText='display:none';
  }

  if(opts.filter&&page==='agents') setFilter(opts.filter);
  if(opts.hl&&page==='subscriptions') setTimeout(()=>hlRow('subsBody',opts.hl),60);
  if(opts.hl&&page==='reports') setTimeout(()=>hlRow('reportsBody',opts.hl),60);
  if(page==='monitoring') renderMonitoring();
  G('pagesWrap').scrollTo({top:0,behavior:'smooth'});
}

function hlRow(tbody,id){
  const row=document.querySelector('#'+tbody+' [data-hlid="'+id+'"]');
  if(!row)return;
  row.style.background='rgba(92,110,248,.18)';
  row.scrollIntoView({behavior:'smooth',block:'center'});
  setTimeout(()=>row.style.background='',2200);
}

// ═══════════════════════════ SIDEBAR ═══════════════════════════
G('collapseBtn').addEventListener('click',()=>{
  G('sidebar').classList.toggle('collapsed');
  G('main').classList.toggle('expanded');
});
G('menuBtn').addEventListener('click',()=>{
  G('sidebar').classList.toggle('collapsed');
  G('main').classList.toggle('expanded');
});

// ═══════════════════════════ NAV WIRING ═══════════════════════════
document.querySelectorAll('.nav-item[data-page]').forEach(btn=>{
  btn.addEventListener('click',()=>goTo(btn.dataset.page));
});
G('bcRoot').addEventListener('click',()=>goTo('dashboard'));
G('notifBtn').addEventListener('click',()=>goTo('notifications'));

// Dashboard stat cards
G('sc-agents').addEventListener('click',()=>goTo('agents',{filter:'all'}));
G('sc-pending').addEventListener('click',()=>goTo('agents',{filter:'pending'}));
G('sc-subs').addEventListener('click',()=>goTo('subscriptions'));
G('sc-reports').addEventListener('click',()=>goTo('reports'));

// Quick action buttons
G('qa-pending').addEventListener('click',()=>goTo('agents',{filter:'pending'}));
G('qa-subs').addEventListener('click',()=>goTo('subscriptions'));
G('qa-reports').addEventListener('click',()=>goTo('reports'));
G('qa-monitor').addEventListener('click',()=>goTo('monitoring'));
// Back button
G('backBtn').addEventListener('click',()=>goTo(prevPage));
G('clientBackBtn').addEventListener('click',()=>goTo('clients'));

// ═══════════════════════════ CLIENTS ═══════════════════════════
const clientsData = <?php echo json_encode($clients_data); ?>;

G('clientSearch').addEventListener('input', function(){
  const q = this.value.toLowerCase();
  document.querySelectorAll('#clientsBody tr').forEach(tr=>{
    const text = tr.innerText.toLowerCase();
    tr.style.display = text.includes(q) ? '' : 'none';
  });
});

function openClientProfile(clientId) {
  const c = clientsData.find(x => String(x.id) === String(clientId));
  if (!c) return;
  prevPage = 'clients';

  const name = (c.first_name + ' ' + c.last_name).trim();
  const initials = (c.first_name[0] + (c.last_name[0]||'')).toUpperCase();
  const totalReviews = (parseInt(c.index_reviews)||0) + (parseInt(c.car_reviews)||0) + (parseInt(c.dealer_reviews)||0);
  const totalLikes = (parseInt(c.total_likes)||0) + (parseInt(c.index_likes)||0);

  const avatarHtml = c.profile_photo
    ? `<img src="${c.profile_photo}" style="width:72px;height:72px;border-radius:50%;object-fit:cover;border:2px solid var(--border)">`
    : `<div class="profile-av-lg">${initials}</div>`;

  G('clientProfileContent').innerHTML = `
    <div class="profile-hero">
      ${avatarHtml}
      <div class="profile-info">
        <h2>${name}</h2>
        <div class="profile-meta">
          <span>${svgIcon('mail')}${c.email}</span>
          <span>${svgIcon('clock')}Joined ${fmtDate(c.created_at)}</span>
        </div>
        <div style="display:flex;gap:12px;margin-top:8px">
          <span style="background:rgba(76,156,248,.12);color:var(--blue);padding:4px 12px;border-radius:20px;font-size:.78rem;font-weight:600">
            ${svgIcon('users')} ${totalReviews} Reviews
          </span>
          <span style="background:rgba(245,166,35,.12);color:var(--amber);padding:4px 12px;border-radius:20px;font-size:.78rem;font-weight:600">
            ${svgIcon('activity')} ${totalLikes} Likes
          </span>
        </div>
      </div>
    </div>

    <div class="profile-grid">
      <div class="profile-card">
        <div class="profile-card-head">${svgIcon('info')} Personal Info</div>
        <div class="profile-card-body">
          <div class="info-row"><span class="lbl">Full Name</span><span class="val">${name}</span></div>
          <div class="info-row"><span class="lbl">Client ID</span><span class="val" style="font-family:monospace;color:var(--accent)">#${c.id}</span></div>
          <div class="info-row"><span class="lbl">Email</span><span class="val">${c.email}</span></div>
          <div class="info-row"><span class="lbl">Joined</span><span class="val">${fmtDate(c.created_at)}</span></div>
        </div>
      </div>

      <div class="profile-card">
        <div class="profile-card-head">${svgIcon('activity')} Activity</div>
        <div class="profile-card-body">
          <div class="info-row"><span class="lbl">Index Reviews</span><span class="val" style="color:var(--blue)">${c.index_reviews}</span></div>
          <div class="info-row"><span class="lbl">Car Reviews</span><span class="val" style="color:var(--blue)">${c.car_reviews}</span></div>
          <div class="info-row"><span class="lbl">Dealer Reviews</span><span class="val" style="color:var(--blue)">${c.dealer_reviews}</span></div>
          <div class="info-row"><span class="lbl">Total Likes Given</span><span class="val" style="color:var(--amber)">${totalLikes}</span></div>
        </div>
      </div>

      <div class="profile-card" style="grid-column:1/-1">
        <div class="profile-card-head">${svgIcon('flag')} Comments — <span style="font-size:.78rem;color:var(--muted)">Admin can delete</span></div>
        <div class="profile-card-body" id="clientCommentsBox-${c.id}">
          <div style="text-align:center;color:var(--muted);padding:20px">
            <div class="dc-spinner"></div>
          </div>
        </div>
      </div>
    </div>`;

  goTo('client-profile', {name: name});
  loadClientComments(c.id);
}

function loadClientComments(clientId) {
  fetch('admin_action.php', {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify({type:'get_client_comments', id: clientId})
  }).then(r=>r.json()).then(data=>{
    const box = document.getElementById('clientCommentsBox-' + clientId);
    if (!box) return;
    if (!data.comments || !data.comments.length) {
      box.innerHTML = '<p style="color:var(--muted);font-size:.83rem;text-align:center;padding:20px">No comments yet</p>';
      return;
    }
    const active  = data.comments.filter(cm => !cm.deleted);
const deleted = data.comments.filter(cm => cm.deleted);

const renderActive = active.map(cm => `
  <div class="info-row" id="cm-${cm.type}-${cm.id}" style="flex-direction:column;align-items:flex-start;gap:6px;padding:12px 0">
    <div style="display:flex;width:100%;justify-content:space-between;align-items:center">
      <span style="font-size:.72rem;background:rgba(76,156,248,.12);color:var(--blue);padding:2px 8px;border-radius:10px">${cm.source}</span>
      <span style="font-size:.72rem;color:var(--muted)">${fmtDate(cm.created_at)}</span>
    </div>
    <div style="font-size:.85rem;color:var(--text);line-height:1.5">${cm.text}</div>
    <button class="tbl-btn reject" onclick="adminDeleteComment('${cm.type}','${cm.id}','${clientId}')">
      ${svgIcon('x')} Delete Comment
    </button>
  </div>
  <hr style="border-color:var(--border);margin:0">`).join('');

const renderDeleted = deleted.length ? `
  <div style="margin-top:20px;padding:10px 0">
    <div style="font-size:.75rem;text-transform:uppercase;letter-spacing:1px;color:var(--muted);margin-bottom:10px">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg> Deleted Comments (${deleted.length})
    </div>
    ${deleted.map(cm => `
      <div style="padding:12px;margin-bottom:8px;background:rgba(244,91,91,.05);border:1px solid rgba(244,91,91,.15);border-radius:10px;opacity:.7">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
          <span style="font-size:.72rem;background:rgba(244,91,91,.12);color:var(--red);padding:2px 8px;border-radius:10px">${cm.source}</span>
          <span style="font-size:.7rem;color:var(--red);font-weight:600">
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> Deleted ${fmtDate(cm.deleted_at)}
          </span>
        </div>
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px">
  ${cm.profile_photo 
    ? `<img src="${cm.profile_photo}" style="width:24px;height:24px;border-radius:50%;object-fit:cover">` 
    : `<div style="width:24px;height:24px;border-radius:50%;background:var(--accent);display:flex;align-items:center;justify-content:center;font-size:.65rem;font-weight:700;color:#fff">${cm.user_name ? cm.user_name[0].toUpperCase() : '?'}</div>`}
  <span style="font-size:.78rem;font-weight:600;color:var(--muted)">${cm.user_name || 'Client'}</span>
</div>
        <div style="font-size:.83rem;color:var(--muted);line-height:1.5;text-decoration:line-through">${cm.text}</div>
      </div>`).join('')}
  </div>` : '';

box.innerHTML = renderActive + renderDeleted;
 });
}
async function adminDeleteComment(type, commentId, clientId) {
  if (!confirm('Delete this comment? The client will be notified.')) return;
  const res = await fetch('admin_action.php', {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify({type:'delete_comment', comment_type: type, comment_id: commentId, client_id: clientId})
  });
  const data = await res.json();
  if (data.success) {
    const el = document.getElementById('cm-' + type + '-' + commentId);
    const hr = el?.nextElementSibling || el?.previousElementSibling;
    if (hr && hr.tagName === 'HR') hr.remove();
    el?.remove();
    toast('Comment deleted and client notified', 'success');
    loadClientComments(clientId);
  } else {
    toast('Error deleting comment', 'error');
  }
}
// ═══════════════════════════ SEARCH OVERLAY ═══════════════════════════
(function(){
  const overlay=G('searchOverlay'),input=G('globalSearch'),results=G('searchResults');
  G('searchBtn').addEventListener('click',()=>{overlay.classList.add('open');setTimeout(()=>input.focus(),50);});
  overlay.addEventListener('click',e=>{if(e.target===overlay)overlay.classList.remove('open');});
  document.addEventListener('keydown',e=>{if(e.key==='Escape')overlay.classList.remove('open');});
  input.addEventListener('input',()=>{
    const q=input.value.trim().toLowerCase();
    if(!q){results.innerHTML='';return;}
    const hits=[];
    agents.forEach(a=>{
      if(a.name.toLowerCase().includes(q)||a.email.toLowerCase().includes(q)||a.id.toLowerCase().includes(q))
        hits.push({icon:'users',color:'blue',title:a.name,sub:a.id+' · '+cap(a.status),
          go(){overlay.classList.remove('open');openProfile(a.id,'agents');}});
    });
    subscriptions.forEach(s=>{
      if(s.agentName.toLowerCase().includes(q)||s.id.toLowerCase().includes(q)||s.plan.toLowerCase().includes(q))
        hits.push({icon:'credit-card',color:'green',title:s.agentName+' — '+s.plan,sub:s.id+' · '+cap(s.status),
          go(){overlay.classList.remove('open');goTo('subscriptions',{hl:s.id});}});
    });
    reports.forEach(r=>{
      if(r.agentName.toLowerCase().includes(q)||r.reason.toLowerCase().includes(q)||r.id.toLowerCase().includes(q))
        hits.push({icon:'flag',color:'red',title:r.reason,sub:r.id+' · '+r.agentName,
          go(){overlay.classList.remove('open');goTo('reports',{hl:r.id});}});
    });
    if(!hits.length){results.innerHTML='<div class="search-empty">No results for "'+q+'"</div>';return;}
    results.innerHTML='';
    hits.forEach(h=>{
      const el=document.createElement('div');
      el.className='sr-item';
      el.innerHTML='<div class="sr-icon '+h.color+'">'+svgIcon(h.icon)+'</div><div class="sr-body"><strong>'+h.title+'</strong><span>'+h.sub+'</span></div><span class="sr-type">'+h.icon+'</span>';
      el.addEventListener('click',()=>h.go());
      results.appendChild(el);
    });
  });
})();

// ═══════════════════════════ AGENTS ═══════════════════════════
function renderAgents(filter,search){
  filter=filter||curFilter; search=search||'';
  const tbody=G('agentsBody');
  tbody.innerHTML='';
  const list=agents.filter(a=>{
    const mf=filter==='all'||(filter==='active'&&(a.status==='active'||a.status==='approved'))||a.status===filter;
    const ms=a.name.toLowerCase().includes(search.toLowerCase())||
             a.email.toLowerCase().includes(search.toLowerCase())||
             a.id.toLowerCase().includes(search.toLowerCase());
    return mf&&ms;
  });
  if(!list.length){
    tbody.innerHTML='<tr><td colspan="5"><div class="empty-state">'+svgIcon('search-x')+'<p>No agents found</p></div></td></tr>';
    return;
  }
  list.forEach(a=>{
    const tr=document.createElement('tr');
    tr.dataset.hlid=a.id;
    let btns='';
    if(a.status==='pending'){
  btns+='<button class="tbl-btn approve" data-act="approve" data-id="'+a.id+'">'+svgIcon('check')+' Approve</button>';
  btns+='<button class="tbl-btn reject"  data-act="reject"  data-id="'+a.id+'">'+svgIcon('x')+' Reject</button>';
}
    if(a.status==='active') btns+='<button class="tbl-btn block" onclick="openBlockModal(\''+a.id+'\')">'+svgIcon('ban')+' Block</button>';
    if(a.status==='blocked') btns+='<button class="tbl-btn unblock" data-act="unblock" data-id="'+a.id+'">'+svgIcon('unlock')+' Unblock</button>';
    btns+='<button class="tbl-btn view" data-act="profile" data-id="'+a.id+'">'+svgIcon('eye')+' Profile</button>';
    tr.innerHTML=
      '<td><div class="agent-cell"><div class="agent-av">'+ini(a.name)+'</div><div><div style="font-weight:600">'+a.name+'</div><div style="font-size:.73rem;color:var(--muted)">'+a.id+'</div></div></div></td>'+
      '<td style="color:var(--muted)">'+a.email+'</td>'+
      '<td style="color:var(--muted)">'+fmtDate(a.joined)+'</td>'+
      '<td><span class="status '+a.status+'">'+cap(a.status)+'</span></td>'+
      '<td><div class="tbl-actions">'+btns+'</div></td>';
    tbody.appendChild(tr);
  });
  tbody.querySelectorAll('[data-act]').forEach(btn=>{
    btn.addEventListener('click',e=>{
      e.stopPropagation();
      if(btn.dataset.act==='profile'){openProfile(btn.dataset.id,'agents');return;}
      agentAction(btn.dataset.act,btn.dataset.id,false);
    });
  });
  tbody.querySelectorAll('tr').forEach(tr=>{
    tr.addEventListener('click',e=>{
      if(e.target.closest('button'))return;
      const profBtn=tr.querySelector('[data-act="profile"]');
      if(profBtn)openProfile(profBtn.dataset.id,'agents');
    });
  });
}

async function agentAction(act, id, fromProfile) {
  const a = agents.find(x => x.id === id); if (!a) return;
  const map = {approve:'approved', reject:'rejected', block:'blocked', unblock:'approved', later:'active'};
  
await fetch('admin_action.php', {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify({type:'agent', id: id, action: map[act], trial: act==='later'})
  });

  a.status = map[act];
// إذا كان temp_blocked غيره لـ blocked في الـ array
if (act === 'temp_blocked') a.status = 'blocked';
  toast({approve:'✔ Agent approved', reject:'✖ Agent rejected', 
         block:'🚫 Agent blocked', unblock:'🔓 Agent unblocked'}[act],
        {approve:'success', reject:'error', block:'error', unblock:'success'}[act]);
  renderAgents(curFilter, G('agentSearch').value);
  updateStats();
  if (fromProfile) openProfile(id, prevPage);
}

function setFilter(f){
  curFilter=f;
  document.querySelectorAll('.f-tab').forEach(t=>t.classList.toggle('active',t.dataset.filter===f));
  renderAgents(f,G('agentSearch').value);
}
G('agentSearch').addEventListener('input',e=>renderAgents(curFilter,e.target.value));
document.querySelectorAll('.f-tab').forEach(t=>t.addEventListener('click',()=>setFilter(t.dataset.filter)));

// ═══════════════════════════ AGENT PROFILE ═══════════════════════════
function openProfile(agentId,from){
  const a=agents.find(x=>x.id===agentId);if(!a)return;
  prevPage=from||'agents';
  const agSubs=subscriptions.filter(s=>s.agentId===agentId);
  const agReps=reports.filter(r=>r.agentId===agentId);
  const subsHtml=agSubs.length
    ?agSubs.map(s=>'<div class="info-row"><span class="lbl">'+s.plan+' Plan</span><span class="val"><span class="status '+s.status+'">'+cap(s.status)+'</span></span></div>').join('')
    :'<p style="color:var(--muted);font-size:.83rem">No subscription requests</p>';
  const repHtml=agReps.length
    ?agReps.map(r=>'<div class="info-row"><span class="lbl">'+r.reason+'</span><span class="val" style="color:var(--muted);font-size:.78rem">'+fmtDate(r.date)+'</span></div>').join('')
    :'<p style="color:var(--muted);font-size:.83rem">No reports against this agent</p>';
  let actBtns='';
  if(a.status==='pending'){actBtns+='<button class="tbl-btn approve" data-pact="approve" data-id="'+a.id+'">'+svgIcon('check')+' Approve</button><button class="tbl-btn reject" data-pact="reject" data-id="'+a.id+'">'+svgIcon('x')+' Reject</button>';}
if(a.status==='active') actBtns+='<button class="tbl-btn block" onclick="openBlockModal(\''+a.id+'\')">'+svgIcon('ban')+' Block</button>';
  if(a.status==='blocked') actBtns+='<button class="tbl-btn unblock" data-pact="unblock" data-id="'+a.id+'">'+svgIcon('unlock')+' Unblock</button>';

  G('profileContent').innerHTML=
    '<div class="profile-hero">'+
      '<div class="profile-av-lg">'+ini(a.name)+'</div>'+
      '<div class="profile-info">'+
        '<h2>'+a.name+'</h2>'+
        '<div class="profile-meta">'+
          '<span>'+svgIcon('mail')+a.email+'</span>'+
          '<span>'+svgIcon('phone')+a.phone+'</span>'+
          '<span>'+svgIcon('map-pin')+a.wilaya+'</span>'+
        '</div>'+
        '<div style="display:flex;align-items:center;gap:12px"><span class="status '+a.status+'">'+cap(a.status)+'</span><span style="font-size:.8rem;color:var(--muted)">'+a.id+'</span></div>'+
      '</div>'+
      '<div class="profile-actions">'+actBtns+'</div>'+
    '</div>'+
    '<div class="profile-grid">'+
      '<div class="profile-card"><div class="profile-card-head">'+svgIcon('info')+' Agent Info</div><div class="profile-card-body">'+
        '<div class="info-row"><span class="lbl">Full Name</span><span class="val">'+a.name+'</span></div>'+
        '<div class="info-row"><span class="lbl">Agent ID</span><span class="val" style="font-family:monospace;color:var(--accent)">'+a.id+'</span></div>'+
        '<div class="info-row"><span class="lbl">Email</span><span class="val">'+a.email+'</span></div>'+
        '<div class="info-row"><span class="lbl">Phone</span><span class="val">'+a.phone+'</span></div>'+
        '<div class="info-row"><span class="lbl">Wilaya</span><span class="val">'+a.wilaya+'</span></div>'+
        '<div class="info-row"><span class="lbl">Joined</span><span class="val">'+fmtDate(a.joined)+'</span></div>'+
        '<div class="info-row"><span class="lbl">National ID</span><span class="val">'+( a.national_id||'—')+'</span></div>'+
        '<div class="info-row"><span class="lbl">Full Name on ID</span><span class="val">'+(a.id_card_name||'—')+'</span></div>'+
        '<div class="info-row"><span class="lbl">Company</span><span class="val">'+(a.company_name||'—')+'</span></div>'+
        '<div class="info-row"><span class="lbl">RC Number</span><span class="val">'+(a.rc_number||'—')+'</span></div>'+
        '<div class="info-row"><span class="lbl">RC Owner</span><span class="val">'+(a.rc_owner_name||'—')+'</span></div>'+
        '<div class="info-row"><span class="lbl">ID Card File</span><span class="val">'+(a.id_card_file 
    ? '<a href="#" onclick="showImg(\'http://localhost/'+a.id_card_file+'\')" style="color:var(--accent)">📄 View File</a>' : '—')+'</span></div>'+
        '<div class="info-row"><span class="lbl">RC File</span><span class="val">'+(a.rc_file 
    ? '<a href="#" onclick="showImg(\'http://localhost/'+a.rc_file+'\')" style="color:var(--accent)">📄 View File</a>' : '—')+'</span></div>'+
      '</div></div>'+
      '<div class="profile-card"><div class="profile-card-head">'+svgIcon('bar-chart-2')+' Activity</div><div class="profile-card-body">'+
        '<div class="info-row"><span class="lbl">Plan</span><span class="val">'+a.plan+'</span></div>'+
        '<div class="info-row"><span class="lbl">Listings</span><span class="val">'+a.listings+'</span></div>'+
        '<div class="info-row"><span class="lbl">Rating</span><span class="val" style="color:var(--amber)">'+(a.rating>0?'★ '+a.rating:'—')+'</span></div>'+
        '<div class="info-row"><span class="lbl">Reports</span><span class="val" style="color:'+(agReps.length?'var(--red)':'var(--green)')+'">'+agReps.length+'</span></div>'+
      '</div></div>'+
      '<div class="profile-card"><div class="profile-card-head">'+svgIcon('credit-card')+' Subscriptions</div><div class="profile-card-body">'+subsHtml+'</div></div>'+
      '<div class="profile-card"><div class="profile-card-head">'+svgIcon('flag')+' Reports</div><div class="profile-card-body">'+repHtml+'</div></div>'+
    '</div>';

  goTo('agent-profile',{name:a.name});

  document.querySelectorAll('[data-pact]').forEach(btn=>{
    btn.addEventListener('click',e=>{e.stopPropagation();agentAction(btn.dataset.pact,btn.dataset.id,true);});
  });
}

      //════════════════════════ SUBSCRIPTIONS ═══════════════════════════//
function renderSubscriptions(){
  const tbody=G('subsBody');tbody.innerHTML='';
  if(!subscriptions.length){
    tbody.innerHTML='<tr><td colspan="6"><div class="empty-state">'+svgIcon('inbox')+'<p>No subscriptions</p></div></td></tr>';
    return;
  }
  subscriptions.forEach(s=>{
    const tr=document.createElement('tr');tr.dataset.hlid=s.id;
    tr.innerHTML=
      '<td><div class="agent-cell" data-open-agent="'+s.agentId+'" style="cursor:pointer">'+
        '<div class="agent-av">'+ini(s.agentName)+'</div>'+
        '<div><div style="font-weight:600">'+s.agentName+'</div><div style="font-size:.73rem;color:var(--muted)">'+s.agentId+'</div></div>'+
      '</div></td>'+
      '<td><strong>'+s.plan+'</strong></td>'+
      '<td><span class="proof-link" data-subid="'+s.id+'">'+svgIcon('file-image')+' '+s.proof+'</span></td>'+
      '<td style="color:var(--muted)">'+fmtDate(s.requested)+'</td>'+
      '<td><span class="status '+s.status+'">'+cap(s.status)+'</span></td>'+
      '<td><div class="tbl-actions">'+
        (s.status==='pending'
          ?'<button class="tbl-btn approve" data-sact="approve" data-sid="'+s.id+'">'+svgIcon('check')+' Approve</button>'+
            '<button class="tbl-btn reject"  data-sact="reject"  data-sid="'+s.id+'">'+svgIcon('x')+' Reject</button>'
          :'<span style="color:var(--muted);font-size:.8rem">—</span>')+
      '</div></td>';
    tbody.appendChild(tr);
  });
  tbody.querySelectorAll('[data-open-agent]').forEach(el=>el.addEventListener('click',()=>openProfile(el.dataset.openAgent,'subscriptions')));
  tbody.querySelectorAll('.proof-link').forEach(link=>{
    link.addEventListener('click',e=>{
      e.stopPropagation();
      activeSubId=link.dataset.subid;
      const sub=subscriptions.find(s=>s.id===activeSubId);
      if(sub)G('proofFileName').textContent=sub.proof;
      G('proofModal').classList.add('open');
    });
  });
  tbody.querySelectorAll('[data-sact]').forEach(btn=>{
    btn.addEventListener('click',e=>{e.stopPropagation();subAction(btn.dataset.sact,btn.dataset.sid);});
  });
}

async function subAction(act,id){
  const s=subscriptions.find(x=>x.id===id);if(!s)return;
await fetch('admin_action.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({type: 'payment', id: parseInt(id), action: act==='approve'?'approved':'rejected'})
});
  if(act==='approve'){s.status='approved';toast('✔ Subscription '+id+' approved','success');}
  if(act==='reject') {s.status='rejected';toast('✖ Subscription '+id+' rejected','error');}
  renderSubscriptions();updateStats();
}

// Proof modal
G('closeProofModal').addEventListener('click',()=>{G('proofModal').classList.remove('open');activeSubId=null;});
G('proofModal').addEventListener('click',e=>{if(e.target===G('proofModal')){G('proofModal').classList.remove('open');activeSubId=null;}});
G('modalApprove').addEventListener('click',()=>{if(activeSubId){subAction('approve',activeSubId);G('proofModal').classList.remove('open');activeSubId=null;}});
G('modalReject').addEventListener('click', ()=>{if(activeSubId){subAction('reject', activeSubId);G('proofModal').classList.remove('open');activeSubId=null;}});
// BLOCK MODAL
G('closeBlockModal').addEventListener('click', () => G('blockModal').classList.remove('open'));
G('cancelBlock').addEventListener('click', () => G('blockModal').classList.remove('open'));
G('confirmBlock').addEventListener('click', async () => {
  const days = parseInt(document.querySelector('input[name="blockDays"]:checked').value);
  const reason = G('blockReason').value.trim();
  const isPermanent = days === 0;

  await fetch('admin_action.php', {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify({
      type: 'agent',
      id: activeBlockAgentId,
      action: isPermanent ? 'blocked' : 'temp_blocked',
      days: days,
      reason: reason
    })
  });

  const a = agents.find(x => x.id == activeBlockAgentId);
  if (a) a.status = 'blocked';

  if (activeBlockFromReport && activeBlockReportId) {
    await fetch('admin_action.php', {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({type:'report', id: activeBlockReportId, action:'resolved'})
    });
    reports = reports.filter(r => r.id !== activeBlockReportId);
    renderReports();
  }

  G('blockModal').classList.remove('open');
  toast(isPermanent ? 'Agent permanently blocked' : 'Agent suspended for '+days+' days', 'error');
  renderAgents(curFilter, G('agentSearch').value);
  updateStats();
});

function openBlockModal(agentId, fromReport=false, reportId=null) {
  activeBlockAgentId = agentId;
  activeBlockFromReport = fromReport;
  activeBlockReportId = reportId;
  G('blockReason').value = '';
  document.querySelector('input[name="blockDays"][value="3"]').checked = true;
  G('blockModal').classList.add('open');
}
// ═══════════════════════════ REPORTS ═══════════════════════════
function renderReports(){
  const tbody=G('reportsBody');tbody.innerHTML='';
  if(!reports.length){
    tbody.innerHTML='<tr><td colspan="5"><div class="empty-state">'+svgIcon('check-circle')+'<p>No open reports</p></div></td></tr>';
    return;
  }
  reports.forEach(r=>{
  const agent = agents.find(x => x.id === r.agentId);
  if(agent && agent.status === 'blocked') return;
    const tr=document.createElement('tr');tr.dataset.hlid=r.id;
    tr.innerHTML=
      '<td><div class="agent-cell"><div class="agent-av">'+r.reporter[0].toUpperCase()+'</div>'+
      '<div><div style="font-weight:600">'+r.reporter+'</div></div></div></td>'+
      '<td><div class="agent-cell" style="cursor:pointer" onclick="openProfile(\''+r.agentId+'\',\'reports\')">'+
      '<div class="agent-av">'+r.agentName[0].toUpperCase()+'</div>'+
      '<div><div style="font-weight:600">'+r.agentName+'</div></div></div></td>'+
      '<td><span style="background:rgba(244,91,91,.12);color:#f45b5b;padding:4px 10px;border-radius:20px;font-size:.78rem;font-weight:600">'+r.reason+'</span></td>'+
      '<td style="color:var(--muted)">'+fmtDate(r.date)+'</td>'+
       '<td><div class="tbl-actions">'+
  '<button class="tbl-btn approve" onclick="resolveReport(\''+r.id+'\')">'+svgIcon('check')+' Resolve</button>'+
  (agent && (agent.status==='blocked') 
  ? '<button class="tbl-btn unblock" data-act="unblock" data-id="'+r.agentId+'">'+svgIcon('unlock')+' Unblock</button>'
  : '<button class="tbl-btn block" onclick="openBlockModal(\''+r.agentId+'\', true, \''+r.id+'\')">'+svgIcon('ban')+' Block</button>'
)+
  '<button class="tbl-btn view" onclick="openProfile(\''+r.agentId+'\',\'reports\')">'+svgIcon('eye')+' View Agent</button>'+
'</div></td>';
      '</div></td>';
    tbody.appendChild(tr);
  });
}

async function resolveReport(id) {
  await fetch('admin_action.php', {
    method:'POST',
    headers:{'Content-Type':'application/json'},
    body:JSON.stringify({type:'report', id:parseInt(id), action:'resolved'})
  });
  reports = reports.filter(r => r.id !== id);
  renderReports();
  updateStats();
  toast('Report resolved','success');
}
// ═══════════════════════════ NOTIFICATIONS ═══════════════════════════
G('markAllRead').addEventListener('click',()=>{
  document.querySelectorAll('#notifList .notif-item.unread').forEach(n=>n.classList.remove('unread'));
  updateNotifBadge();
  toast('All notifications marked as read','info');
});

G('notifList').addEventListener('click',e=>{
  const xBtn=e.target.closest('.notif-x');
  const item=e.target.closest('.notif-item');
  if(!item)return;
  if(xBtn){
    item.style.animation='slideOut .2s ease forwards';
    setTimeout(()=>{item.remove();updateNotifBadge();},200);
    return;
  }
  item.classList.remove('unread');
  updateNotifBadge();
  const page=item.dataset.page,aid=item.dataset.agentid,sid=item.dataset.subid,rid=item.dataset.reportid;
  if(page==='agents'&&aid)          openProfile(aid,'notifications');
  else if(page==='subscriptions'&&sid) goTo('subscriptions',{hl:sid});
  else if(page==='reports'&&rid)       goTo('reports',{hl:rid});
  else                                 goTo(page);
});

function updateNotifBadge(){
  const n=document.querySelectorAll('#notifList .notif-item.unread').length;
  G('notifBadge').textContent=n;
  G('notifDot').style.display=n>0?'inline-block':'none';
}

// ═══════════════════════════ STATS ═══════════════════════════
function updateStats(){
  const total  =agents.length;
  const pending=agents.filter(a=>a.status==='pending').length;
  const active =agents.filter(a=>a.status==='active').length;
  const blocked=agents.filter(a=>a.status==='blocked').length;
  const asubs  =subscriptions.filter(s=>s.status==='approved').length;
  G('statAgents').textContent =total;
  G('statPending').textContent=pending;
  G('statSubs').textContent   =asubs;
  G('statReports').textContent=reports.length;
  G('reportBadge').textContent=reports.length;
  G('monTotal').textContent   =total;
  G('monActive').textContent  =active;
  G('monPending').textContent =pending;
  G('monBlocked').textContent =blocked;
  updateNotifBadge();
}

function renderMonitoring(){
  updateStats();
  const total  =agents.length||1;
  const active =agents.filter(a=>a.status==='active').length;
  const pending=agents.filter(a=>a.status==='pending').length;
  const blocked=agents.filter(a=>a.status==='blocked').length;
  const basic  =subscriptions.filter(s=>s.plan==='Basic').length;
  const pro    =subscriptions.filter(s=>s.plan==='Pro').length;
  const premium=subscriptions.filter(s=>s.plan==='Premium').length;
  const maxS   =Math.max(basic,pro,premium,1);
  G('planBars').innerHTML=[
    {l:'Basic',v:basic,c:'var(--blue)'},{l:'Pro',v:pro,c:'var(--green)'},{l:'Premium',v:premium,c:'var(--amber)'}
  ].map(p=>'<div class="plan-bar-row"><span>'+p.l+'</span><div class="bar-wrap"><div class="bar" style="width:'+Math.round(p.v/maxS*100)+'%;background:'+p.c+'"></div></div><span>'+p.v+'</span></div>').join('');
  const ap=Math.round(active/total*100),pp=Math.round(pending/total*100),bp=Math.max(100-ap-pp,0);
  G('donutActive').setAttribute('stroke-dasharray',ap+' '+(100-ap));
  G('donutActive').setAttribute('stroke-dashoffset',25);
  G('donutPending').setAttribute('stroke-dasharray',pp+' '+(100-pp));
  G('donutPending').setAttribute('stroke-dashoffset',-ap+25);
  G('donutBlocked').setAttribute('stroke-dasharray',bp+' '+(100-bp));
  G('donutBlocked').setAttribute('stroke-dashoffset',-(ap+pp)+25);
  G('donutLegend').innerHTML=
    '<div><span class="dot-lg" style="background:var(--green)"></span>Active '+ap+'%</div>'+
    '<div><span class="dot-lg" style="background:var(--amber)"></span>Pending '+pp+'%</div>'+
    '<div><span class="dot-lg" style="background:var(--red)"></span>Blocked '+bp+'%</div>';
}

// ═══════════════════════════ BOOT ═══════════════════════════
renderAgents();
renderSubscriptions();
updateStats();
renderMonitoring();
updateNotifBadge();
loadData();
function showImg(src) {
  const overlay = document.createElement('div');
  overlay.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,.85);z-index:999;display:flex;align-items:center;justify-content:center;cursor:pointer';
  overlay.innerHTML = '<img src="'+src+'" style="max-width:90%;max-height:90%;border-radius:12px;">';
  overlay.addEventListener('click', () => overlay.remove());
  document.body.appendChild(overlay);
}
</script>
</body>
</html>