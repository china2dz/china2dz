<?php
session_start();
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Profile — China2DZ</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,400&display=swap" rel="stylesheet">
<style>
:root {
  --bg: #0a0a0a;
  --card: #141414;
  --card2: #1c1c1c;
  --card3: #222;
  --border: rgba(255,255,255,0.07);
  --border2: rgba(255,255,255,0.12);
  --text: #f0f0f0;
  --muted: rgba(255,255,255,0.38);
  --red: #00bcd4;
  --red-hover: #b71c1c;
  --red2: rgba(0,188,212,0.12);
  --red3: rgba(0,188,212,0.06);
  --green: #22c55e;
  --blue: #3b82f6;
  --radius: 16px;
  --radius-sm: 10px;
  --shadow: 0 8px 40px rgba(0,0,0,0.5);
  --shadow-sm: 0 4px 16px rgba(0,0,0,0.3);
}
[data-theme="light"] {
  --bg: #f2f2f5;
  --card: #ffffff;
  --card2: #f7f7f9;
  --card3: #efefef;
  --border: rgba(0,0,0,0.07);
  --border2: rgba(0,0,0,0.13);
  --text: #0f0f0f;
  --muted: rgba(0,0,0,0.42);
  --red2: rgba(0,188,212,0.08);
  --red3: rgba(0,188,212,0.04);
  --shadow: 0 8px 40px rgba(0,0,0,0.1);
  --shadow-sm: 0 4px 16px rgba(0,0,0,0.07);
}
*,*::before,*::after { box-sizing:border-box; margin:0; padding:0; }
html { scroll-behavior:smooth; }
body {
  font-family:'DM Sans',sans-serif;
  background:var(--bg);
  color:var(--text);
  min-height:100vh;
  transition:background .3s,color .3s;
  -webkit-font-smoothing:antialiased;
}

/* ── NAV ── */
.pnav {
  display:flex; align-items:center; justify-content:space-between;
  padding:0 28px; height:62px;
  background:var(--card);
  border-bottom:1px solid var(--border);
  position:sticky; top:0; z-index:200;
  backdrop-filter:blur(12px);
}
.pnav-logo {
  font-family:'Syne',sans-serif; font-weight:800;
  font-size:1.25rem; text-decoration:none; letter-spacing:-.5px;
}
.pnav-logo .c { color:var(--red); }
.pnav-logo .d { color:var(--text); }
.pnav-back {
  display:flex; align-items:center; gap:7px;
  color:var(--muted); text-decoration:none;
  font-size:.85rem; font-weight:500;
  transition:color .2s; padding:6px 12px;
  border-radius:8px; border:1px solid transparent;
}
.pnav-back:hover { color:var(--text); border-color:var(--border2); }
.pnav-actions { display:flex; align-items:center; gap:8px; }
.theme-btn {
  width:36px; height:36px; border-radius:50%;
  background:var(--card2); border:1px solid var(--border);
  display:flex; align-items:center; justify-content:center;
  cursor:pointer; font-size:.95rem; transition:all .2s;
  color:var(--text);
}
.theme-btn:hover { border-color:var(--red); transform:scale(1.08); }

/* ── LAYOUT ── */
.container { max-width:1080px; margin:0 auto; padding:28px 18px 60px; }
.profile-grid {
  display:grid;
  grid-template-columns:268px 1fr;
  gap:20px;
  align-items:start;
}
@media(max-width:860px){ .profile-grid { grid-template-columns:1fr; } }

/* ── SIDEBAR ── */
.sidebar { display:flex; flex-direction:column; gap:14px; position:sticky; top:82px; }
@media(max-width:860px){ .sidebar { position:static; } }

/* Profile Card */
.pcard {
  background:var(--card);
  border-radius:var(--radius);
  border:1px solid var(--border);
  overflow:hidden;
  box-shadow:var(--shadow-sm);
}
.pcard-cover {
  height:80px;
  background:linear-gradient(135deg,#7b0e12 0%,#00bcd4 60%,#ff5057 100%);
  position:relative;
}
.pcard-cover::after {
  content:'';
  position:absolute; inset:0;
  background:url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}
.pcard-body { padding:0 20px 22px; text-align:center; }
.avatar-wrap {
  position:relative; display:inline-block;
  margin-top:-38px; margin-bottom:10px;
}
.avatar-img {
  width:76px; height:76px; border-radius:50%;
  object-fit:cover;
  border:3px solid var(--card);
  background:var(--card2);
  display:block; cursor:pointer;
  transition:opacity .2s,transform .2s;
  box-shadow:0 4px 16px rgba(0,0,0,0.4);
}
.avatar-img:hover { opacity:.88; transform:scale(1.04); }
.avatar-edit-btn {
  position:absolute; bottom:2px; right:2px;
  width:24px; height:24px; border-radius:50%;
  background:var(--red);
  border:2px solid var(--card);
  display:flex; align-items:center; justify-content:center;
  cursor:pointer; transition:background .2s,transform .2s;
}
.avatar-edit-btn:hover { background:var(--red-hover); transform:scale(1.1); }
#photoInput { display:none; }
.pcard-name {
  font-family:'Syne',sans-serif;
  font-weight:700; font-size:1rem;
  margin-bottom:4px; letter-spacing:-.3px;
}
.pcard-role {
  display:inline-block; padding:3px 12px;
  border-radius:20px; font-size:.72rem; font-weight:700;
  background:var(--red2); color:var(--red);
  margin-bottom:14px; letter-spacing:.3px; text-transform:uppercase;
}
.pcard-info { font-size:.82rem; }
.pcard-row {
  display:flex; align-items:center; gap:9px;
  padding:8px 0; border-bottom:1px solid var(--border);
  color:var(--muted);
}
.pcard-row:last-child { border:none; }
.pcard-row svg { flex-shrink:0; opacity:.6; }

/* Sidebar Nav */
.snav {
  background:var(--card);
  border-radius:var(--radius);
  border:1px solid var(--border);
  overflow:hidden;
  box-shadow:var(--shadow-sm);
}
.snav-btn {
  display:flex; align-items:center; gap:10px;
  width:100%; padding:13px 16px;
  background:none; border:none;
  border-bottom:1px solid var(--border);
  color:var(--muted);
  font-family:'DM Sans',sans-serif; font-size:.88rem; font-weight:500;
  cursor:pointer; transition:all .18s;
  position:relative; text-align:left;
}
.snav-btn:last-child { border:none; }
.snav-btn:hover { background:var(--card2); color:var(--text); }
.snav-btn.active { background:var(--red3); color:var(--red); font-weight:600; }
.snav-btn.active::before {
  content:''; position:absolute; left:0; top:0; bottom:0;
  width:3px; background:var(--red); border-radius:0 3px 3px 0;
}
.snav-badge {
  margin-left:auto;
  background:var(--red); color:#fff;
  font-size:.65rem; font-weight:700;
  padding:2px 7px; border-radius:10px;
  min-width:20px; text-align:center;
}
.snav-logout { color:#ff5057 !important; }

/* ── MAIN ── */
.main { display:flex; flex-direction:column; gap:16px; }
.tab-panel { display:none; }
.tab-panel.active { display:flex; flex-direction:column; gap:16px; animation:fadeUp .22s ease; }
@keyframes fadeUp { from{opacity:0;transform:translateY(6px)} to{opacity:1;transform:translateY(0)} }

/* Box */
.box {
  background:var(--card);
  border-radius:var(--radius);
  border:1px solid var(--border);
  padding:22px;
  box-shadow:var(--shadow-sm);
}
.box-title {
  font-family:'Syne',sans-serif; font-weight:700; font-size:.95rem;
  margin-bottom:18px;
  display:flex; align-items:center; justify-content:space-between;
  gap:10px;
}
.box-title-icon {
  width:32px; height:32px; border-radius:9px;
  background:var(--red2); color:var(--red);
  display:flex; align-items:center; justify-content:center;
  flex-shrink:0;
}

/* Stats */
.stats-row { display:grid; grid-template-columns:repeat(3,1fr); gap:12px; }
.stat-box {
  background:var(--card);
  border:1px solid var(--border);
  border-radius:14px; padding:18px 14px;
  text-align:center;
  transition:border-color .2s,transform .2s;
  cursor:default;
}
.stat-box:hover { border-color:var(--red); transform:translateY(-2px); }
.stat-num {
  font-family:'Syne',sans-serif;
  font-size:1.8rem; font-weight:800; color:var(--red);
  line-height:1;
}
.stat-lbl { font-size:.73rem; color:var(--muted); margin-top:5px; font-weight:500; }

/* Form */
.form-row { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
@media(max-width:580px){ .form-row { grid-template-columns:1fr; } }
.fgroup { margin-bottom:14px; }
.fgroup:last-child { margin-bottom:0; }
.fgroup label {
  display:block; font-size:.75rem; font-weight:700;
  color:var(--muted); text-transform:uppercase;
  letter-spacing:.6px; margin-bottom:7px;
}
.fgroup input,.fgroup textarea,.fgroup select {
  width:100%; padding:10px 13px;
  background:var(--card2);
  border:1px solid var(--border);
  border-radius:var(--radius-sm);
  color:var(--text);
  font-family:'DM Sans',sans-serif; font-size:.9rem;
  transition:border .2s,box-shadow .2s; outline:none;
}
.fgroup input:focus,.fgroup textarea:focus,.fgroup select:focus {
  border-color:var(--red);
  box-shadow:0 0 0 3px rgba(0,188,212,0.1);
}
.fgroup select option { background:var(--card); }
.fgroup textarea { resize:vertical; min-height:78px; line-height:1.6; }

/* Buttons */
.btn {
  padding:9px 20px; border-radius:var(--radius-sm);
  font-family:'DM Sans',sans-serif; font-size:.88rem; font-weight:600;
  cursor:pointer; border:none; transition:all .18s;
  display:inline-flex; align-items:center; gap:7px;
}
.btn-primary { background:var(--red); color:#fff; }
.btn-primary:hover { background:var(--red-hover); transform:translateY(-1px); box-shadow:0 4px 14px rgba(0,188,212,.35); }
.btn-outline { background:none; border:1px solid var(--border2); color:var(--text); }
.btn-outline:hover { border-color:var(--red); color:var(--red); }
.btn-sm { padding:6px 14px; font-size:.8rem; }
.btn-danger { background:rgba(0,188,212,.1); color:#ff5057; border:1px solid rgba(0,188,212,.2); }
.btn-danger:hover { background:var(--red); color:#fff; }
.btn-ghost { background:var(--card2); border:1px solid var(--border); color:var(--muted); }
.btn-ghost:hover { color:var(--text); border-color:var(--border2); }

/* Notifications */
.notif-item {
  display:flex; gap:12px; padding:14px;
  border-radius:12px; background:var(--card2);
  border:1px solid var(--border); margin-bottom:9px;
  position:relative; transition:background .2s,border-color .2s;
}
.notif-item:last-child { margin-bottom:0; }
.notif-item.unread { border-color:rgba(0,188,212,.3); background:rgba(0,188,212,.05); }
.notif-dot {
  position:absolute; top:18px; right:14px;
  width:7px; height:7px; border-radius:50%;
  background:var(--red); display:none;
}
.notif-item.unread .notif-dot { display:block; }
.notif-icon {
  width:38px; height:38px; border-radius:50%; flex-shrink:0;
  display:flex; align-items:center; justify-content:center; font-size:.95rem;
}
.ni-success { background:rgba(34,197,94,.13); }
.ni-info    { background:rgba(59,130,246,.13); }
.ni-offer   { background:rgba(234,179,8,.13); }
.ni-warning { background:rgba(0,188,212,.13); }
.notif-title { font-weight:600; font-size:.88rem; margin-bottom:2px; }
.notif-msg { font-size:.81rem; color:var(--muted); line-height:1.5; }
.notif-time { font-size:.72rem; color:var(--muted); margin-top:5px; }

/* Favorites */
.fav-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(165px,1fr)); gap:13px; }
.fav-card {
  background:var(--card2);
  border-radius:12px; border:1px solid var(--border);
  overflow:hidden; transition:transform .2s,border-color .2s,box-shadow .2s;
}
.fav-card:hover { transform:translateY(-3px); border-color:var(--red); box-shadow:0 6px 24px rgba(0,188,212,.12); }
.fav-card img { width:100%; height:115px; object-fit:cover; display:block; }
.fav-card-body { padding:11px; }
.fav-card-name { font-weight:600; font-size:.84rem; margin-bottom:3px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.fav-card-price { color:var(--red); font-size:.79rem; margin-bottom:9px; font-weight:600; }
.fav-actions { display:flex; gap:6px; }

/* Empty state */
.empty-state {
  text-align:center; padding:44px 20px;
  color:var(--muted); display:flex; flex-direction:column;
  align-items:center; gap:10px;
}
.empty-state svg { opacity:.2; }
.empty-state p { font-size:.88rem; }

/* Spinner */
.spin {
  display:inline-block; width:18px; height:18px;
  border:2px solid rgba(255,255,255,.15);
  border-top-color:var(--red);
  border-radius:50%; animation:sp .7s linear infinite;
}
[data-theme="light"] .spin { border-color:rgba(0,0,0,.1); border-top-color:var(--red); }
@keyframes sp { to{transform:rotate(360deg)} }

/* ── CHAT ── */
.chat-wrap {
  display:grid; grid-template-columns:230px 1fr;
  height: calc(100vh - 130px);
   border-radius:var(--radius);
  border:1px solid var(--border); overflow:hidden;
  background:var(--card);
}
@media(max-width:640px){ .chat-wrap { grid-template-columns:1fr; height:auto; } }
.conv-list {
  background:var(--card2);
  border-right:1px solid var(--border);
  overflow-y:auto; display:flex; flex-direction:column;
}
.conv-list-header {
  padding:14px 15px;
  font-family:'Syne',sans-serif; font-weight:700; font-size:.85rem;
  border-bottom:1px solid var(--border);
  flex-shrink:0;
}
.conv-item {
  display:flex; align-items:center; gap:10px;
  padding:12px 14px; cursor:pointer;
  border-bottom:1px solid var(--border);
  transition:background .15s; position:relative;
}
.conv-item:hover { background:rgba(255,255,255,.04); }
.conv-item.active { background:var(--red3); }
[data-theme="light"] .conv-item:hover { background:rgba(0,0,0,.03); }
.conv-av {
  width:38px; height:38px; border-radius:50%;
  object-fit:cover; background:var(--card3);
  flex-shrink:0; display:flex; align-items:center;
  justify-content:center; font-size:.9rem; overflow:hidden;
}
.conv-av img { width:100%; height:100%; object-fit:cover; }
.conv-info { flex:1; min-width:0; }
.conv-name { font-size:.84rem; font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.conv-last { font-size:.74rem; color:var(--muted); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; margin-top:2px; }
.conv-unread {
  background:var(--red); color:#fff;
  font-size:.65rem; font-weight:700;
  padding:2px 6px; border-radius:10px; flex-shrink:0;
}
.conv-actions { display:flex; gap:4px; margin-left:auto; opacity:0; transition:opacity .15s; }
.conv-item:hover .conv-actions { opacity:1; }
.conv-action-btn {
  width:24px; height:24px; border-radius:6px;
  background:var(--card3); border:1px solid var(--border);
  display:flex; align-items:center; justify-content:center;
  cursor:pointer; font-size:.75rem; transition:all .15s; color:var(--muted);
}
.conv-action-btn:hover { background:var(--red); color:#fff; border-color:var(--red); }

.chat-main { display:flex; flex-direction:column; overflow:hidden; }
.chat-header {
  padding:13px 16px; border-bottom:1px solid var(--border);
  display:flex; align-items:center; gap:10px; flex-shrink:0;
}
.chat-header-av {
  width:36px; height:36px; border-radius:50%;
  object-fit:cover; background:var(--card2); flex-shrink:0;
  display:flex; align-items:center; justify-content:center; font-size:.9rem;
  overflow:hidden;
}
.chat-header-av img { width:100%; height:100%; object-fit:cover; }
.chat-header-name { font-weight:700; font-size:.9rem; }
.chat-header-status { font-size:.73rem; color:var(--green); display:flex; align-items:center; gap:4px; }
.chat-header-status::before { content:''; width:6px; height:6px; border-radius:50%; background:var(--green); display:inline-block; }
.chat-header-actions { margin-left:auto; display:flex; gap:6px; }
.chat-head-btn {
  width:30px; height:30px; border-radius:8px;
  background:var(--card2); border:1px solid var(--border);
  display:flex; align-items:center; justify-content:center;
  cursor:pointer; color:var(--muted); transition:all .15s;
}
.chat-head-btn:hover { background:rgba(0,188,212,.12); color:var(--red); border-color:rgba(0,188,212,.25); }
.chat-msgs {
  flex:1; overflow-y:auto; padding:14px;
  display:flex; flex-direction:column; gap:8px;
  min-height:0;
}
.chat-msgs::-webkit-scrollbar { width:4px; }
.chat-msgs::-webkit-scrollbar-thumb { background:var(--border2); border-radius:4px; }
.msg { max-width:70%; display:flex; flex-direction:column; }
.msg.mine { align-self:flex-end; }
.msg.theirs { align-self:flex-start; }
.msg-bubble {
  padding:9px 13px; border-radius:14px;
  font-size:.86rem; line-height:1.55;
  word-break:break-word;
}
.msg.mine .msg-bubble { background:var(--red); color:#fff; border-bottom-right-radius:4px; }
.msg.theirs .msg-bubble { background:var(--card2); border-bottom-left-radius:4px; }
.msg-meta { display:flex; align-items:center; gap:5px; margin-top:3px; padding:0 3px; }
.msg-time { font-size:.69rem; color:var(--muted); }
.msg-status { font-size:.69rem; color:var(--muted); }
.msg.mine .msg-meta { justify-content:flex-end; }
.chat-input {
  padding:11px 12px; border-top:1px solid var(--border);
  display:flex; gap:8px; flex-shrink:0;
}
.chat-input input {
  flex:1; padding:9px 14px;
  background:var(--card2); border:1px solid var(--border);
  border-radius:22px; color:var(--text);
  font-family:'DM Sans',sans-serif; font-size:.87rem; outline:none;
  transition:border .2s;
}
.chat-input input:focus { border-color:var(--red); }
.chat-send {
  width:38px; height:38px; border-radius:50%;
  background:var(--red); border:none; color:#fff;
  cursor:pointer; display:flex; align-items:center; justify-content:center;
  transition:background .2s,transform .2s; flex-shrink:0;
}
.chat-send:hover { background:var(--red-hover); transform:scale(1.06); }
.chat-placeholder {
  flex:1; display:flex; flex-direction:column;
  align-items:center; justify-content:center;
  color:var(--muted); gap:10px;
}
.chat-placeholder svg { opacity:.18; }
.chat-placeholder span { font-size:.85rem; }

/* ── SETTINGS ── */
.settings-section { margin-bottom:24px; }
.settings-section:last-child { margin-bottom:0; }
.settings-section h3 {
  font-size:.72rem; font-weight:700; color:var(--muted);
  text-transform:uppercase; letter-spacing:.7px; margin-bottom:10px;
}
.setting-row {
  display:flex; align-items:center; justify-content:space-between;
  padding:13px 16px;
  background:var(--card2);
  border-radius:12px; margin-bottom:7px;
  border:1px solid var(--border);
  transition:border-color .2s;
}
.setting-row:last-child { margin-bottom:0; }
.setting-row:hover { border-color:var(--border2); }
.setting-label { font-size:.88rem; font-weight:600; margin-bottom:2px; }
.setting-desc { font-size:.77rem; color:var(--muted); }
.toggle-switch { position:relative; width:44px; height:24px; flex-shrink:0; }
.toggle-switch input { opacity:0; width:0; height:0; position:absolute; }
.toggle-slider {
  position:absolute; inset:0;
  background:var(--card3); border-radius:24px;
  cursor:pointer; transition:.28s; border:1px solid var(--border);
}
.toggle-slider:before {
  content:''; position:absolute;
  width:18px; height:18px; border-radius:50%;
  background:#fff; left:2px; top:2px; transition:.28s;
  box-shadow:0 1px 4px rgba(0,0,0,.3);
}
input:checked + .toggle-slider { background:var(--red); border-color:var(--red); }
input:checked + .toggle-slider:before { transform:translateX(20px); }

.lang-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:9px; }
.lang-opt {
  padding:11px 8px; border-radius:10px; text-align:center;
  border:1.5px solid var(--border); cursor:pointer;
  font-size:.83rem; font-weight:600; transition:all .18s;
  background:var(--card2); color:var(--muted);
}
.lang-opt:hover { border-color:rgba(0,188,212,.4); color:var(--text); }
.lang-opt.selected { border-color:var(--red); background:var(--red2); color:var(--red); }

/* ── AVATAR MODAL ── */
.av-modal {
  display:none; position:fixed; inset:0; z-index:9999;
  background:rgba(0,0,0,.9); align-items:center; justify-content:center;
}
.av-modal.show { display:flex; animation:fadeIn .2s ease; }
@keyframes fadeIn { from{opacity:0} to{opacity:1} }
.av-modal-inner { position:relative; }
.av-modal-inner img {
  max-width:82vw; max-height:82vh; border-radius:50%;
  border:4px solid var(--red); box-shadow:0 0 60px rgba(0,188,212,.3);
}
.av-modal-close {
  position:absolute; top:-14px; right:-14px;
  width:32px; height:32px; border-radius:50%;
  background:var(--red); border:none; color:#fff;
  font-size:1.1rem; cursor:pointer;
  display:flex; align-items:center; justify-content:center;
  transition:background .2s;
}
.av-modal-close:hover { background:var(--red-hover); }

/* ── CONFIRM MODAL ── */
.confirm-modal {
  display:none; position:fixed; inset:0; z-index:9998;
  background:rgba(0,0,0,.7); align-items:center; justify-content:center;
  padding:20px;
}
.confirm-modal.show { display:flex; animation:fadeIn .2s ease; }
.confirm-box {
  background:var(--card); border-radius:var(--radius);
  border:1px solid var(--border); padding:26px;
  max-width:360px; width:100%; box-shadow:var(--shadow);
  text-align:center;
}
.confirm-box h3 { font-family:'Syne',sans-serif; font-size:1rem; margin-bottom:8px; }
.confirm-box p { font-size:.85rem; color:var(--muted); margin-bottom:20px; line-height:1.6; }
.confirm-actions { display:flex; gap:10px; justify-content:center; }

/* ── TOAST ── */
.toast {
  position:fixed; bottom:24px; left:50%;
  transform:translateX(-50%) translateY(70px);
  background:var(--card); color:var(--text); padding:11px 24px;
  border-radius:30px; border:1px solid var(--border2);
  font-size:.86rem; font-weight:500; z-index:9999;
  transition:transform .3s cubic-bezier(.34,1.56,.64,1);
  box-shadow:var(--shadow); pointer-events:none; white-space:nowrap;
}
.toast.show { transform:translateX(-50%) translateY(0); }
.toast.ok { border-color:var(--green); color:var(--green); }
.toast.err { border-color:var(--red); color:#ff5057; }
/* ── RESERVE BAR ── */
.reserve-bar {
  display: flex; align-items: center; justify-content: space-between;
  padding: 10px 16px;
  background: rgba(0,188,212,.04);
  border-bottom: 1px solid rgba(0,188,212,.14);
  gap: 10px;
}
.reserve-car-info {
  display: flex; align-items: center; gap: 7px;
  font-size: .8rem; color: var(--muted); font-weight: 500;
}
.reserve-trigger-btn {
  display: flex; align-items: center; gap: 7px;
  padding: 8px 18px;
  background: linear-gradient(135deg, #00bcd4, #0097a7);
  border: none; border-radius: 8px;
  color: #fff; font-family: 'DM Sans', sans-serif;
  font-size: .83rem; font-weight: 700;
  cursor: pointer; transition: opacity .18s, transform .18s;
  white-space: nowrap;
}
.reserve-trigger-btn:hover { opacity: .88; transform: translateY(-1px); }

/* ── RESERVE MODAL ── */
.rv-modal {
  display: none; position: fixed; inset: 0; z-index: 9997;
  background: rgba(0,0,0,.75); backdrop-filter: blur(8px);
  align-items: center; justify-content: center; padding: 20px;
}
.rv-modal.show { display: flex; animation: fadeIn .22s ease; }
.rv-box {
  background: var(--card); border-radius: 18px;
  border: 1px solid var(--border);
  width: 100%; max-width: 480px;
  max-height: 90vh; overflow-y: auto;
  box-shadow: 0 24px 60px rgba(0,0,0,.55);
  animation: fadeUp .25s ease;
}
.rv-header {
  display: flex; align-items: center; justify-content: space-between;
  padding: 20px 22px 0;
}
.rv-header h3 {
  font-family: 'Syne', sans-serif; font-size: 1rem; font-weight: 700;
  display: flex; align-items: center; gap: 9px;
}
.rv-header h3 svg { color: var(--red); }
.rv-close {
  width: 30px; height: 30px; border-radius: 50%;
  background: var(--card2); border: 1px solid var(--border);
  display: flex; align-items: center; justify-content: center;
  cursor: pointer; color: var(--muted); transition: all .18s;
}
.rv-close:hover { background: var(--red); color: #fff; border-color: var(--red); }
.rv-body { padding: 18px 22px 22px; }
.rv-info-banner {
  display: flex; gap: 10px; align-items: flex-start;
  background: rgba(0,188,212,.06);
  border: 1px solid rgba(0,188,212,.18);
  border-radius: 10px; padding: 12px 14px;
  margin-bottom: 18px;
  font-size: .8rem; color: var(--muted); line-height: 1.55;
}
.rv-info-banner svg { flex-shrink: 0; color: var(--red); margin-top: 1px; }
.rv-section-label {
  font-size: .72rem; font-weight: 700; text-transform: uppercase;
  letter-spacing: .6px; color: var(--muted); margin-bottom: 10px; margin-top: 16px;
}
.rv-method-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
.rv-method-card {
  display: flex; flex-direction: column; align-items: center;
  gap: 8px; padding: 14px 12px;
  border: 1.5px solid var(--border); border-radius: 12px;
  background: var(--card2); cursor: pointer;
  transition: all .18s; text-align: center;
}
.rv-method-card:hover { border-color: var(--red); }
.rv-method-card.selected {
  border-color: var(--red); background: var(--red2);
}
.rv-method-card svg { color: var(--red); }
.rv-method-card .rv-method-name {
  font-size: .84rem; font-weight: 700; color: var(--text);
}
.rv-method-card .rv-method-sub {
  font-size: .73rem; color: var(--muted); line-height: 1.4;
}
.rv-upload-zone {
  border: 2px dashed var(--border2); border-radius: 10px;
  padding: 18px; text-align: center; cursor: pointer;
  transition: all .18s; color: var(--muted);
}
.rv-upload-zone:hover { border-color: var(--red); color: var(--text); background: var(--red3); }
.rv-upload-zone svg { margin-bottom: 6px; }
.rv-upload-zone p { font-size: .8rem; }
.rv-preview {
  display: flex; align-items: center; gap: 10px;
  padding: 10px 12px; margin-top: 10px;
  background: var(--card2); border-radius: 9px;
  border: 1px solid var(--border);
}
.rv-preview img {
  width: 52px; height: 42px; object-fit: cover;
  border-radius: 6px; border: 1px solid var(--border);
}
.rv-preview-name { font-size: .8rem; font-weight: 600; }
.rv-preview-ok { font-size: .73rem; color: var(--green); margin-top: 2px; }
.rv-card-fields { display: flex; flex-direction: column; gap: 12px; margin-top: 12px; }
.rv-card-row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
.rv-footer {
  display: flex; gap: 10px; justify-content: flex-end;
  padding: 0 22px 22px;
}
.rv-btn-submit {
  display: flex; align-items: center; gap: 8px;
  padding: 10px 22px;
  background: var(--red); border: none; border-radius: 9px;
  color: #fff; font-family: 'DM Sans', sans-serif;
  font-size: .88rem; font-weight: 700;
  cursor: pointer; transition: all .18s;
}
.rv-btn-submit:hover { background: #0097a7; transform: translateY(-1px); }
.rv-btn-submit:disabled { opacity: .6; cursor: not-allowed; transform: none; }
</style>
<script src="theme.js"></script>
</head>
<body>

<!-- NAV -->
<nav class="pnav">
  <a href="index.php" class="pnav-back">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
    Back
  </a>
  <a href="index.php" class="pnav-logo"><span class="c">China</span><span class="d">2DZ</span></a>
  <div class="pnav-actions">
    <button class="theme-btn" id="themeBtnNav" onclick="toggleTheme()" title="Toggle theme">🌙</button>
  </div>
</nav>

<div class="container">
  <div class="profile-grid">

    <!-- ── SIDEBAR ── -->
    <div class="sidebar">
      <div class="pcard">
        <div class="pcard-cover"></div>
        <div class="pcard-body">
          <div class="avatar-wrap">
            <img id="avatarImg" src="" alt="" class="avatar-img" onclick="openAvatarModal()">
            <label class="avatar-edit-btn" for="photoInput" title="Change photo">
              <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
            </label>
            <input type="file" id="photoInput" accept="image/*" onchange="uploadPhoto(this)">
          </div>
          <div class="pcard-name" id="pcName">—</div>
          <div class="pcard-role" id="pcRole">Client</div>
          <div class="pcard-info">
            <div class="pcard-row">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
              <span id="pcEmail">—</span>
            </div>
            <div class="pcard-row">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.56 2h3a2 2 0 0 1 2 1.72 12 12 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12 12 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
              <span id="pcPhone">—</span>
            </div>
            <div class="pcard-row">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
              <span id="pcWilaya">—</span>
            </div>
            <div class="pcard-row">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
              <span id="pcJoined">—</span>
            </div>
          </div>
        </div>
      </div>

      <div class="snav">
        <button class="snav-btn active" onclick="showTab('overview',this)">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          <span data-i18n="My Profile">My Profile</span>
        </button>
        <button class="snav-btn" onclick="showTab('notifications',this)">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
          <span data-i18n="Notifications">Notifications</span>
          <span class="snav-badge" id="notifBadge" style="display:none">0</span>
        </button>
        <button class="snav-btn" onclick="showTab('favorites',this)">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
          <span data-i18n="Favorites">Favorites</span>
          <span class="snav-badge" id="favBadge" style="display:none">0</span>
        </button>
        <button class="snav-btn" onclick="showTab('chat',this)">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
          <span data-i18n="Messages">Messages</span>
          <span class="snav-badge" id="msgBadge" style="display:none">0</span>
        </button>
        <button class="snav-btn" onclick="showTab('tracking',this)">
  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13" rx="1"/><path d="M16 8h4l3 5v3h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
  Track Your Car
  <span class="snav-badge" id="trackingBadge" style="display:none">0</span>
</button>
        <button class="snav-btn" onclick="showTab('settings',this)">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
          Settings
        </button>
        <button class="snav-btn snav-logout" onclick="doLogout()">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#ff5057" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
          Logout
        </button>
      </div>
    </div>

    <!-- ── MAIN CONTENT ── -->
    <div class="main">

      <!-- OVERVIEW -->
      <div class="tab-panel active" id="tab-overview">
        <div class="stats-row">
          <div class="stat-box">
            <div class="stat-num" id="statFavs">0</div>
            <div class="stat-lbl">Favorites</div>
          </div>
          <div class="stat-box">
            <div class="stat-num" id="statChats">0</div>
            <div class="stat-lbl">Chats</div>
          </div>
          <div class="stat-box">
            <div class="stat-num" id="statNotifs">0</div>
            <div class="stat-lbl">Notifications</div>
          </div>
        </div>
        <div class="box">
          <div class="box-title">
            <span data-i18n="Edit Profile">Edit Prfile</span>
            <div class="box-title-icon">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </div>
          </div>
          <div class="form-row">
            <div class="fgroup">
              <label data-i18n="Full Namz">Full Name</label>
              <input type="text" id="editName" placeholder="Your full name">
            </div>
            <div class="fgroup">
              <label>Wilaya</label>
              <select id="editWilaya">
                <option value="">Select Wilaya</option>
                <option>Adrar</option><option>Chlef</option><option>Laghouat</option>
                <option>Oum El Bouaghi</option><option>Batna</option><option>Béjaïa</option>
                <option>Biskra</option><option>Béchar</option><option>Blida</option>
                <option>Bouira</option><option>Tamanrasset</option><option>Tébessa</option>
                <option>Tlemcen</option><option>Tiaret</option><option>Tizi Ouzou</option>
                <option>Algiers</option><option>Djelfa</option><option>Jijel</option>
                <option>Sétif</option><option>Saïda</option><option>Skikda</option>
                <option>Sidi Bel Abbès</option><option>Annaba</option><option>Guelma</option>
                <option>Constantine</option><option>Médéa</option><option>Mostaganem</option>
                <option>M'Sila</option><option>Mascara</option><option>Ouargla</option>
                <option>Oran</option><option>El Bayadh</option><option>Illizi</option>
                <option>Bordj Bou Arréridj</option><option>Boumerdès</option><option>El Tarf</option>
                <option>Tindouf</option><option>Tissemsilt</option><option>Djanet</option>
                <option>Khenchela</option><option>Souk Ahras</option><option>Tipaza</option>
                <option>Mila</option><option>Aïn Defla</option><option>Naâma</option>
                <option>Aïn Témouchent</option><option>Ghardaïa</option><option>Relizane</option>
              </select>
            </div>
          </div>
          <div class="fgroup">
            <label>Bio</label>
            <textarea id="editBio" placeholder="Tell us a bit about yourself..."></textarea>
          </div>
          <button class="btn btn-primary" onclick="saveProfile()">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
            Save Changes
          </button>
        </div>
      </div>

      <!-- NOTIFICATIONS -->
      <div class="tab-panel" id="tab-notifications">
        <div class="box">
          <div class="box-title">
            <span>Notifications</span>
            <button class="btn btn-outline btn-sm" onclick="markAllRead()">Mark all read</button>
          </div>
          <div id="notifList">
            <div class="empty-state"><div class="spin"></div></div>
          </div>
        </div>
      </div>

      <!-- FAVORITES -->
      <div class="tab-panel" id="tab-favorites">
        <div class="box">
          <div class="box-title">
            <span>My Favorites</span>
            <div class="box-title-icon">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
            </div>
          </div>
          <div class="fav-grid" id="favList">
            <div class="empty-state" style="grid-column:1/-1"><div class="spin"></div></div>
          </div>
        </div>
      </div>

      <!-- CHAT -->
      <div class="tab-panel" id="tab-chat">
        <div class="box" style="padding:0;overflow:hidden;">
          <div class="chat-wrap">
            <div class="conv-list">
              <div class="conv-list-header">Messages</div>
              <div id="convListInner">
                <div class="empty-state" style="padding:30px 20px;"><div class="spin"></div></div>
              </div>
            </div>
            <div class="chat-main" id="chatMain">
              <div class="chat-placeholder">
                <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                <span>Select a conversation</span>
              </div>
            </div>
          </div>
        </div>
      </div>
<!-- TRACKING -->
<div class="tab-panel" id="tab-tracking">
  <div class="box">
    <div class="box-title">
      Track Your Car
      <div class="box-title-icon">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13" rx="1"/><path d="M16 8h4l3 5v3h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
      </div>
    </div>
    <div id="trackingList">
      <div class="empty-state"><div class="spin"></div></div>
    </div>
  </div>
</div>
      <!-- SETTINGS -->
      <div class="tab-panel" id="tab-settings">
        <div class="box">
          <div class="box-title">Settings</div>

          <div class="settings-section">
            <h3>Appearance</h3>
            <div class="setting-row">
              <div>
                <div class="setting-label">Dark Mode</div>
                <div class="setting-desc">Switch between dark and light theme</div>
              </div>
              <label class="toggle-switch">
                <input type="checkbox" id="darkModeToggle" onchange="toggleTheme()">
                <span class="toggle-slider"></span>
              </label>
            </div>
          </div>

          <div class="settings-section">
            <h3>Language</h3>
            <div class="lang-grid">
              <div class="lang-opt" id="lang-en" onclick="setLang('en',this)">🇬🇧 English</div>
              <div class="lang-opt" id="lang-fr" onclick="setLang('fr',this)">🇫🇷 Français</div>
              <div class="lang-opt" id="lang-ar" onclick="setLang('ar',this)">🇩🇿 العربية</div>
            </div>
          </div>

          <div class="settings-section">
            <h3>Notifications</h3>
            <div class="setting-row">
              <div>
                <div class="setting-label">New Messages</div>
                <div class="setting-desc">Get notified when agents reply</div>
              </div>
              <label class="toggle-switch">
                <input type="checkbox" id="toggleMsgNotif" checked>
                <span class="toggle-slider"></span>
              </label>
            </div>
            <div class="setting-row">
              <div>
                <div class="setting-label">Price Alerts</div>
                <div class="setting-desc">Notify me when car prices drop</div>
              </div>
              <label class="toggle-switch">
                <input type="checkbox" id="togglePriceNotif" checked>
                <span class="toggle-slider"></span>
              </label>
            </div>
          </div>

          <div class="settings-section">
            <h3>Security</h3>
            <div class="fgroup">
  <label>Current Password</label>
  <input type="password" id="oldPass" placeholder="••••••••" 
         autocomplete="new-password" autocorrect="off">
</div>
<div class="form-row">
  <div class="fgroup">
    <label>New Password</label>
    <input type="password" id="newPass" placeholder="••••••••"
           autocomplete="new-password" autocorrect="off">
  </div>
  <div class="fgroup">
    <label>Confirm Password</label>
    <input type="password" id="confPass" placeholder="••••••••"
           autocomplete="new-password" autocorrect="off">
  </div>
</div>
            <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
  <button class="btn btn-primary btn-sm" onclick="changePassword()">Update Password</button>
  <a href="forgot_password.html" 
     style="font-size:.8rem;color:var(--red);text-decoration:none;font-weight:600;">
    Forgot current password?
  </a>
</div>
          </div>

          <div class="settings-section">
            <h3>Danger Zone</h3>
            <div class="setting-row" style="border-color:rgba(0,188,212,.18);">
              <div>
                <div class="setting-label" style="color:#ff5057;">Delete Account</div>
                <div class="setting-desc">Permanently delete your account and data</div>
              </div>
              <button class="btn btn-danger btn-sm" onclick="showConfirm('delete')">Delete</button>
            </div>
          </div>
        </div>
      </div>

    </div><!-- /main -->
  </div><!-- /profile-grid -->
</div><!-- /container -->

<!-- Avatar Modal -->
<div class="av-modal" id="avModal" onclick="this.classList.remove('show')">
  <div class="av-modal-inner" onclick="event.stopPropagation()">
    <button class="av-modal-close" onclick="document.getElementById('avModal').classList.remove('show')">×</button>
    <img id="avModalImg" src="" alt="Avatar">
  </div>
</div>

<!-- Confirm Modal -->
<div class="confirm-modal" id="confirmModal">
  <div class="confirm-box">
    <h3 id="confirmTitle">Are you sure?</h3>
    <p id="confirmMsg">This action cannot be undone.</p>
    <div class="confirm-actions">
      <button class="btn btn-ghost" onclick="hideConfirm()">Cancel</button>
      <button class="btn btn-danger" id="confirmOkBtn">Confirm</button>
    </div>
  </div>
</div>

<div class="toast" id="toast"></div>

<script>
const API = 'api.php';
let currentUser = null;
let currentConvId = null;
let currentOtherId = null;
let pollInterval = null;
const DEFAULT_AVATAR = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 80 80'%3E%3Crect fill='%23222' width='80' height='80' rx='40'/%3E%3Ctext fill='%23666' font-size='32' x='40' y='52' text-anchor='middle'%3E%F0%9F%91%A4%3C/text%3E%3C/svg%3E";

// ── INIT ──
document.addEventListener('DOMContentLoaded', () => {
  const token = localStorage.getItem('c2dz_token');
  if (!token) { window.location.href = 'login.html'; return; }

  // Theme
  const theme = localStorage.getItem('c2dz_theme') || 'dark';
  applyTheme(theme);

  // Language
  const lang = localStorage.getItem('c2dz_lang') || 'en';
  applyLang(lang);
  document.querySelectorAll('.lang-opt').forEach(el => el.classList.remove('selected'));
  const langEl = document.getElementById('lang-' + lang);
  if (langEl) langEl.classList.add('selected');

  // Notification prefs
  const msgPref = localStorage.getItem('c2dz_notif_msg');
  const pricePref = localStorage.getItem('c2dz_notif_price');
  if (msgPref !== null) document.getElementById('toggleMsgNotif').checked = msgPref === '1';
  if (pricePref !== null) document.getElementById('togglePriceNotif').checked = pricePref === '1';
  document.getElementById('toggleMsgNotif').addEventListener('change', function() {
    localStorage.setItem('c2dz_notif_msg', this.checked ? '1' : '0');
    toast(this.checked ? 'Message notifications on' : 'Message notifications off', 'ok');
  });
  document.getElementById('togglePriceNotif').addEventListener('change', function() {
    localStorage.setItem('c2dz_notif_price', this.checked ? '1' : '0');
    toast(this.checked ? 'Price alerts on' : 'Price alerts off', 'ok');
  });

  // Tab from URL
  const tab = new URLSearchParams(window.location.search).get('tab');
  if (tab) {
    const btn = document.querySelector(`.snav-btn[onclick*="'${tab}'"]`);
    if (btn) {
      showTab(tab, btn);
      if (tab === 'tracking') loadTracking();
    }
  }

  loadProfile();
});

// ── THEME ──
function applyTheme(theme) {
  document.documentElement.setAttribute('data-theme', theme);
  const isDark = theme === 'dark';
  const toggle = document.getElementById('darkModeToggle');
  const navBtn = document.getElementById('themeBtnNav');
  if (toggle) toggle.checked = isDark;
  if (navBtn) navBtn.textContent = isDark ? '🌙' : '☀️';
}
function toggleTheme() {
  const current = document.documentElement.getAttribute('data-theme');
  const next = current === 'dark' ? 'light' : 'dark';
  applyTheme(next);
  localStorage.setItem('c2dz_theme', next);
}

// ── API ──
async function apiCall(action, method = 'GET', body = null) {
  const token = localStorage.getItem('c2dz_token');
  const opts = {
    method,
    headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token }
  };
  if (body) opts.body = JSON.stringify(body);
  const res = await fetch(`${API}?action=${action}`, opts);
  return res.json();
}

// ── PROFILE ──
async function loadProfile() {
  const res = await apiCall('get_profile');
  if (!res.success) { window.location.href = 'login.html'; return; }
  currentUser = res.data;
  renderProfile(currentUser);
  loadNotifications();
  loadFavorites();
  loadConversations();
  loadTracking();
}

function renderProfile(u) {
  const photo = u.profile_photo_url || DEFAULT_AVATAR;
  document.getElementById('avatarImg').src = photo;
  document.getElementById('avModalImg').src = photo;
  document.getElementById('pcName').textContent = u.full_name || '—';
  document.getElementById('pcEmail').textContent = u.email || '—';
  document.getElementById('pcPhone').textContent = u.phone || '—';
  document.getElementById('pcWilaya').textContent = u.wilaya || 'Not set';
  document.getElementById('pcRole').textContent = u.role === 'agent' ? 'Agent' : 'Client';
  document.getElementById('pcJoined').textContent = 'Since ' +
    new Date(u.created_at).toLocaleDateString('en-GB', { month: 'short', year: 'numeric' });
  document.getElementById('editName').value = u.full_name || '';
  document.getElementById('editBio').value = u.bio || '';
  const ws = document.getElementById('editWilaya');
  for (let o of ws.options) if (o.value === u.wilaya) { o.selected = true; break; }
}

async function saveProfile() {
  const name = document.getElementById('editName').value.trim();
  const bio = document.getElementById('editBio').value.trim();
  const wilaya = document.getElementById('editWilaya').value;
  if (!name) { toast('Name is required', 'err'); return; }
  const res = await apiCall('update_profile', 'POST', { full_name: name, bio, wilaya });
  if (res.success) {
    document.getElementById('pcName').textContent = name;
    document.getElementById('pcWilaya').textContent = wilaya || 'Not set';
    const u = JSON.parse(localStorage.getItem('c2dz_user') || '{}');
    u.full_name = name;
    localStorage.setItem('c2dz_user', JSON.stringify(u));
    toast('Profile saved ✓', 'ok');
  } else {
    toast(res.message || 'Error saving', 'err');
  }
}

async function uploadPhoto(input) {
  const file = input.files[0]; if (!file) return;
  const token = localStorage.getItem('c2dz_token');
  const fd = new FormData(); fd.append('photo', file);
  toast('Uploading...', '');
  try {
    const res = await fetch(`${API}?action=upload_photo`, {
      method: 'POST',
      headers: { 'Authorization': 'Bearer ' + token },
      body: fd
    });
    const data = await res.json();
    if (data.success) {
      const url = data.data.url + '?t=' + Date.now();
      document.getElementById('avatarImg').src = url;
      document.getElementById('avModalImg').src = url;
      const u = JSON.parse(localStorage.getItem('c2dz_user') || '{}');
      u.profile_photo = url;
      localStorage.setItem('c2dz_user', JSON.stringify(u));
      toast('Photo updated ✓', 'ok');
    } else { toast(data.message || 'Upload failed', 'err'); }
  } catch(e) { toast('Upload failed', 'err'); }
}

function openAvatarModal() {
  document.getElementById('avModal').classList.add('show');
}

// ── NOTIFICATIONS ──
async function loadNotifications() {
  const res = await apiCall('get_notifications');
  if (!res.success) return;
  const { items, unread } = res.data;
  document.getElementById('statNotifs').textContent = items.length;
  if (unread > 0) {
    document.getElementById('notifBadge').textContent = unread;
    document.getElementById('notifBadge').style.display = 'inline';
  } else {
    document.getElementById('notifBadge').style.display = 'none';
  }
  const icons = { success: '✅', info: 'ℹ️', offer: '🏷️', warning: '⚠️' };
  const cls   = { success: 'ni-success', info: 'ni-info', offer: 'ni-offer', warning: 'ni-warning' };
  const list  = document.getElementById('notifList');
  if (!items.length) {
    list.innerHTML = `<div class="empty-state">
      <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
      <p>No notifications yet</p></div>`;
    return;
  }
  // فتح chat عند الضغط على notification الرسائل
  const openChat = () => {
    const chatBtn = document.querySelector('.snav-btn[onclick*="chat"]');
    if (chatBtn) chatBtn.click();
  };
  list.innerHTML = items.map(n => `
  <div class="notif-item ${n.is_read ? '' : 'unread'}" 
       onclick="handleNotifClick(event, '${n.type}', '${n.link || ''}')"
       style="${n.type === 'info' ? 'cursor:pointer;' : ''}">
      <div class="notif-dot"></div>
      <div class="notif-icon ${cls[n.type] || 'ni-info'}">${icons[n.type] || 'ℹ️'}</div>
      <div style="flex:1;min-width:0;">
        <div class="notif-title">${n.title}</div>
        <div class="notif-msg">${n.message}</div>
        <div class="notif-time">${timeAgo(n.created_at)}</div>
      </div>
    </div>`).join('');
}
function handleNotifClick(event, type, link) {
  if (type === 'message') {
    const chatBtn = document.querySelector('.snav-btn[onclick*="chat"]');
    if (chatBtn) chatBtn.click();
  } else if (link && link.includes('tracking')) {
    const trackBtn = document.querySelector('.snav-btn[onclick*="tracking"]');
    if (trackBtn) trackBtn.click();
    loadTracking();
  } else if(link && link !== '' && link !=='#'){
    window.location.href = link;
  }
}
async function markAllRead() {
  await apiCall('mark_notifications_read', 'POST');
  document.getElementById('notifBadge').style.display = 'none';
  document.querySelectorAll('.notif-item.unread').forEach(el => el.classList.remove('unread'));
  toast('All marked as read ✓', 'ok');
}

// ── FAVORITES ──
async function loadFavorites() {
  const res = await apiCall('get_favorites');
  if (!res.success) return;
  const items = res.data;
  document.getElementById('statFavs').textContent = items.length;
  if (items.length > 0) {
    document.getElementById('favBadge').textContent = items.length;
    document.getElementById('favBadge').style.display = 'inline';
  }
  const list = document.getElementById('favList');
  if (!items.length) {
    list.innerHTML = `<div class="empty-state" style="grid-column:1/-1">
      <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
      <p>No favorites yet</p></div>`;
    return;
  }
  list.innerHTML = items.map(f => `
    <div class="fav-card" id="fav-${f.car_id}">
      <img src="${f.car_image || ''}" alt="${f.car_name}"
           onerror="this.src='https://images.unsplash.com/photo-1549317661-bd32c8ce0db2?w=300'">
      <div class="fav-card-body">
        <div class="fav-card-name">${f.car_name}</div>
        <div class="fav-card-price">${f.car_price || ''}</div>
        <div class="fav-actions">
          <button class="btn btn-outline btn-sm" onclick="window.location.href='index.php'">View</button>
          <button class="btn btn-danger btn-sm" onclick="removeFav('${f.car_id}')">Remove</button>
        </div>
      </div>
    </div>`).join('');
}

async function removeFav(carId) {
  const res = await apiCall('remove_favorite', 'POST', { car_id: carId });
  if (res.success) {
    document.getElementById('fav-' + carId)?.remove();
    const s = document.getElementById('statFavs');
    const newCount = Math.max(0, parseInt(s.textContent) - 1);
    s.textContent = newCount;
    if (newCount === 0) document.getElementById('favBadge').style.display = 'none';
    else document.getElementById('favBadge').textContent = newCount;
    toast('Removed from favorites', '');
  }
}

// ── CHAT ──
async function loadConversations() {
  const res = await apiCall('get_conversations');
  if (!res.success) return;
  const convs = res.data;
  document.getElementById('statChats').textContent = convs.length;
  const totalUnread = convs.reduce((s, c) => s + parseInt(c.unread_count || 0), 0);
  if (totalUnread > 0) {
    document.getElementById('msgBadge').textContent = totalUnread;
    document.getElementById('msgBadge').style.display = 'inline';
  }
  const list = document.getElementById('convListInner');
  if (!convs.length) {
    list.innerHTML = '<div class="empty-state" style="padding:24px 16px;font-size:.82rem;">No conversations yet</div>';
    return;
  }
  list.innerHTML = convs.map(c => {
    const isClient = currentUser && c.client_id === currentUser.id;
    const name    = isClient ? c.agent_name  : c.client_name;
    const photo   = isClient ? c.agent_photo_url : c.client_photo_url;
    const otherId = isClient ? c.agent_id   : c.client_id;
    const av = photo
      ? `<div class="conv-av"><img src="${photo}" alt="${name}"></div>`
      : `<div class="conv-av">👤</div>`;
    return `<div class="conv-item" id="conv-${c.id}" data-car-id="${c.car_id || 0}" onclick="openConv(${c.id},'${name}','${photo||''}',${otherId})">
      ${av}
      <div class="conv-info">
        <div class="conv-name">${name}</div>
        <div class="conv-last">${c.last_message || 'Start chatting'}</div>
      </div>
      ${parseInt(c.unread_count) > 0 ? `<span class="conv-unread">${c.unread_count}</span>` : ''}
      <div class="conv-actions" onclick="event.stopPropagation()">
        <button class="conv-action-btn" title="Delete" onclick="deleteConv(${c.id})">🗑</button>
        <button class="conv-action-btn" title="Block" onclick="blockUser(${otherId})">🚫</button>
      </div>
    </div>`;
  }).join('');
  // فتح محادثة من URL
  var convParam = new URLSearchParams(window.location.search).get('conv');
if (convParam) {
  var convId = parseInt(convParam);
  // افتح tab chat أولاً
  var chatBtn = document.querySelector('.snav-btn[onclick*="chat"]');
  if (chatBtn) chatBtn.click();
  setTimeout(function() {
    var convEl = document.getElementById('conv-' + convId);
    if (convEl) convEl.click();
  }, 300);
}
}

async function openConv(convId, name, photo, otherId) {
  currentConvId = convId;
  currentOtherId = otherId;
  document.querySelectorAll('.conv-item').forEach(el => el.classList.remove('active'));
  document.getElementById('conv-' + convId)?.classList.add('active');

  const av = photo
    ? `<div class="chat-header-av"><img src="${photo}" alt="${name}"></div>`
    : `<div class="chat-header-av">👤</div>`;

  document.getElementById('chatMain').innerHTML = `
    <div class="chat-header">
      ${av}
      <div>
        <div class="chat-header-name">${name}</div>
        <div class="chat-header-status">Online</div>
      </div>
      <div class="chat-header-actions">
        <button class="chat-head-btn" title="Delete conversation" onclick="deleteConv(${convId})">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/></svg>
        </button>
        <button class="chat-head-btn" title="Block user" onclick="blockUser(${otherId})">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
        </button>
      </div>
    </div>
    <div class="reserve-bar">
  <div class="reserve-car-info" id="reserveCarInfoBar" style="display:none;">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 17H3a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11a2 2 0 0 1 2 2v3"/><rect x="9" y="11" width="14" height="10" rx="1"/><circle cx="12" cy="21" r="1"/><circle cx="20" cy="21" r="1"/></svg>
    <span id="reserveCarName">—</span>
  </div>
  <button class="reserve-trigger-btn" id="reserveTriggerBtn" onclick="openClientReserveModal()">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
    Reserve This Car
  </button>
</div>
    <div class="chat-msgs" id="msgArea" style="min-height:0;"></div>
    <div class="chat-input">
      <input type="text" id="msgInput" placeholder="Type a message..."
             onkeypress="if(event.key==='Enter')sendMsg(${otherId})">
      <button class="chat-send" onclick="sendMsg(${otherId})">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
      </button>
    </div>`;

  await fetchMsgs();
  if (pollInterval) clearInterval(pollInterval);
  pollInterval = setInterval(fetchMsgs, 4000);
}

async function fetchMsgs() {
  if (!currentConvId) return;
  console.log('fetching conv:', currentConvId);
const res = await apiCall('get_messages&conversation_id=' + currentConvId);
console.log('messages:', res);
  if (!res.success) return;
  const area = document.getElementById('msgArea'); if (!area) return;
  const msgs = res.data;
  if (!msgs.length) { area.innerHTML = '<div class="empty-state">Say hello! 👋</div>'; return; }
  area.innerHTML = msgs.map(m => {
    const mine = currentUser && m.sender_id === currentUser.id;
    const status = mine ? (m.seen_at ? '✓✓' : m.delivered ? '✓' : '') : '';

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
        const statusColor = r.status === 'accepted' ? 'var(--green)' : r.status === 'refused' ? '#ff5057' : 'var(--red)';
        const statusLabel = r.status === 'accepted' ? 'Confirmed' : r.status === 'refused' ? 'Refused' : 'Pending Review';
        return `<div class="msg ${mine ? 'mine' : 'theirs'}" style="max-width:85%;">
          <div style="background:var(--card2);border:1px solid var(--border2);border-radius:14px;overflow:hidden;border-left:3px solid var(--red);">
            <div style="padding:10px 14px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:8px;">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--red)" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
              <span style="font-weight:700;font-size:.85rem;">Reservation Request</span>
             ${r.car_name ? `<a href="index.php#car-${r.car_id}" target="_blank" style="margin-left:auto;font-size:.78rem;font-weight:700;color:var(--red);text-decoration:none;padding:2px 8px;border-radius:6px;background:rgba(0,188,212,.1);display:flex;align-items:center;gap:4px;">
  <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 17H3a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11a2 2 0 0 1 2 2v3"/><rect x="9" y="11" width="14" height="10" rx="1"/><circle cx="12" cy="21" r="1"/><circle cx="20" cy="21" r="1"/></svg>
  ${r.car_name}
</a>` : ''}
              <span style="margin-left:auto;padding:2px 9px;border-radius:20px;font-size:.7rem;font-weight:700;background:${r.status==='accepted'?'rgba(34,197,94,.15)':r.status==='refused'?'rgba(255,80,87,.15)':'rgba(0,188,212,.15)'};color:${statusColor};">${statusLabel}</span>
            </div>
            <div style="padding:12px 14px;display:flex;flex-direction:column;gap:7px;">
              <div style="display:flex;justify-content:space-between;font-size:.82rem;">
                <span style="color:var(--muted);">Client</span>
                <strong>${r.first_name} ${r.last_name}</strong>
              </div>
              <div style="display:flex;justify-content:space-between;font-size:.82rem;">
                <span style="color:var(--muted);">Phone</span>
                <strong>${r.phone}</strong>
              </div>
              <div style="display:flex;justify-content:space-between;font-size:.82rem;">
                <span style="color:var(--muted);">Payment</span>
                <strong>${r.payment_method === 'cheque' ? 'Payment recu' : 'Golden Card'}</strong>
              </div>
              ${r.payment_method === 'cheque' && r.payment_file ? `
  <div style="display:flex;justify-content:space-between;align-items:center;font-size:.82rem;">
    <span style="color:var(--muted);">Proof</span>
    <a href="${r.payment_file}" target="_blank" style="color:var(--red);font-weight:700;text-decoration:none;display:flex;align-items:center;gap:4px;">
      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
      View File
    </a>
  </div>` : r.payment_method === 'golden_card' ? `
  <div style="display:flex;justify-content:space-between;align-items:center;font-size:.82rem;">
    <span style="color:var(--muted);">Note</span>
    <span style="color:var(--green);font-weight:700;font-size:.78rem;">✔ Check your CCP/Baridimob for incoming transfer</span>
  </div>` : ''}
            </div>
          </div>
          ${r.status === 'pending' && currentUser?.role === 'agent' ? `
<div style="display:flex;gap:8px;margin-top:6px;">
  <button onclick="agentRespondReservation(${m.reservation_id},'accepted')" style="flex:1;padding:8px;background:rgba(34,197,94,.15);border:1px solid #22c55e;border-radius:8px;color:#22c55e;font-family:'DM Sans',sans-serif;font-weight:700;font-size:.8rem;cursor:pointer;">✔ Accept</button>
  <button onclick="agentRespondReservation(${m.reservation_id},'refused')" style="flex:1;padding:8px;background:rgba(255,80,87,.15);border:1px solid #ff5057;border-radius:8px;color:#ff5057;font-family:'DM Sans',sans-serif;font-weight:700;font-size:.8rem;cursor:pointer;">✖ Refuse</button>
</div>` : r.status === 'pending' && currentUser?.role !== 'agent' ? `
<div style="display:flex;gap:8px;margin-top:6px;">
  <button style="flex:1;padding:8px;background:rgba(0,188,212,.1);border:1px solid var(--red);border-radius:8px;color:var(--red);font-family:'DM Sans',sans-serif;font-weight:700;font-size:.8rem;cursor:default;">
    Pending — Waiting for agent
  </button>
</div>` : ''}
          <div class="msg-meta"><span class="msg-time">${fmtTime(m.sent_at)}</span></div>
        </div>`;
    }

    return `<div class="msg ${mine ? 'mine' : 'theirs'}">
      <div class="msg-bubble">${escapeHtml(m.message)}</div>
      <div class="msg-meta">
        <span class="msg-time">${fmtTime(m.sent_at)}</span>
        ${mine ? `<span class="msg-status" style="color:${m.seen_at?'#3b82f6':m.delivered?'var(--muted)':'var(--muted)'}">${status}</span>` : ''}
      </div>
    </div>`;
  }).join('');
  area.scrollTop = area.scrollHeight;
  // تحقق من حالة السيارة
var convEl = document.getElementById('conv-' + currentConvId);
var carId = convEl?.dataset?.carId;
window.currentCarId = carId;
if (carId && carId != '0') {
  fetch('api.php?action=get_car_status&car_id=' + carId, {
    headers: {'Authorization': 'Bearer ' + localStorage.getItem('c2dz_token')}
  }).then(r => r.json()).then(data => {
    var btn = document.getElementById('reserveTriggerBtn');
    if (!btn) return;
    if (data.reservation_status === 'reserved') {
      btn.disabled = true;
      btn.style.opacity = '0.5';
      btn.style.cursor = 'not-allowed';
      btn.innerHTML = '🔒 Already Reserved';
    } else {
      btn.disabled = false;
      btn.style.opacity = '1';
      btn.style.cursor = 'pointer';
      btn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg> Reserve This Car';
    }
  });
}
}

async function sendMsg(agentId) {
  const input = document.getElementById('msgInput');
  const msg = input?.value?.trim(); if (!msg) return;
  input.value = '';
  const res = await apiCall('send_message', 'POST', { agent_id: agentId, message: msg });
  if (res.success) await fetchMsgs();
}

async function deleteConv(convId) {
  showConfirm('deleteConv', async () => {
    const res = await apiCall('delete_conversation', 'POST', { conversation_id: convId });
    if (res.success) {
      document.getElementById('conv-' + convId)?.remove();
      document.getElementById('chatMain').innerHTML = `<div class="chat-placeholder">
        <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
        <span>Select a conversation</span></div>`;
      if (pollInterval) { clearInterval(pollInterval); pollInterval = null; }
      toast('Conversation deleted', '');
    }
  });
}

async function blockUser(userId) {
  showConfirm('blockUser', async () => {
    const res = await apiCall('block_user', 'POST', { blocked_id: userId });
    if (res.success) {
      toast('User blocked', '');
      await loadConversations();
      document.getElementById('chatMain').innerHTML = `<div class="chat-placeholder">
        <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
        <span>Select a conversation</span></div>`;
    }
  });
}

// ── SETTINGS ──
const TRANSLATIONS = {
  en: {
    'My Profile': 'My Profile', 'Notifications': 'Notifications',
    'Favorites': 'Favorites', 'Messages': 'Messages',
    'Settings': 'Settings', 'Logout': 'Logout',
    'Edit Profile': 'Edit Profile', 'Full Name': 'Full Name',
    'Wilaya': 'Wilaya', 'Bio': 'Bio', 'Save Changes': 'Save Changes',
    'Dark Mode': 'Dark Mode', 'Language': 'Language',
    'Back': 'Back'
  },
  fr: {
    'My Profile': 'Mon Profil', 'Notifications': 'Notifications',
    'Favorites': 'Favoris', 'Messages': 'Messages',
    'Settings': 'Paramètres', 'Logout': 'Déconnexion',
    'Edit Profile': 'Modifier le Profil', 'Full Name': 'Nom Complet',
    'Wilaya': 'Wilaya', 'Bio': 'Bio', 'Save Changes': 'Enregistrer',
    'Dark Mode': 'Mode Sombre', 'Language': 'Langue',
    'Back': 'Retour'
  },
  ar: {
    'My Profile': 'ملفي الشخصي', 'Notifications': 'الإشعارات',
    'Favorites': 'المفضلة', 'Messages': 'الرسائل',
    'Settings': 'الإعدادات', 'Logout': 'تسجيل الخروج',
    'Edit Profile': 'تعديل الملف', 'Full Name': 'الاسم الكامل',
    'Wilaya': 'الولاية', 'Bio': 'نبذة عني', 'Save Changes': 'حفظ',
    'Dark Mode': 'الوضع الداكن', 'Language': 'اللغة',
    'Back': 'رجوع'
  }
};

function applyLang(lang) {
  const t = TRANSLATIONS[lang] || TRANSLATIONS['en'];
  const isAr = lang === 'ar';
  document.documentElement.setAttribute('dir', isAr ? 'rtl' : 'ltr');
  document.documentElement.setAttribute('lang', lang);
  document.querySelectorAll('[data-i18n]').forEach(el => {
    const key = el.getAttribute('data-i18n');
    if (t[key]) el.textContent = t[key];
  });
}

function setLang(lang, el) {
  document.querySelectorAll('.lang-opt').forEach(o => o.classList.remove('selected'));
  el.classList.add('selected');
  localStorage.setItem('c2dz_lang', lang);
  applyLang(lang);
  toast('Language saved ✓', 'ok');
}
async function changePassword() {
  const oldP = document.getElementById('oldPass').value;
  const newP = document.getElementById('newPass').value;
  const cfP  = document.getElementById('confPass').value;
  if (!oldP || !newP) { toast('Fill all fields', 'err'); return; }
  if (newP !== cfP)   { toast('Passwords do not match', 'err'); return; }
  if (newP.length < 8){ toast('Minimum 8 characters', 'err'); return; }
  const res = await apiCall('change_password', 'POST', { old_password: oldP, new_password: newP });
  if (res.success) {
    toast('Password updated ✓', 'ok');
    document.getElementById('oldPass').value = '';
    document.getElementById('newPass').value = '';
    document.getElementById('confPass').value = '';
  } else {
    toast(res.message || 'Error', 'err');
  }
}

// ── CONFIRM MODAL ──
let confirmCallback = null;
function showConfirm(type, callback) {
  const titles = {
    delete: 'Delete Account',
    deleteConv: 'Delete Conversation',
    blockUser: 'Block User'
  };
  const msgs = {
    delete: 'Are you sure you want to permanently delete your account? This cannot be undone.',
    deleteConv: 'Delete this conversation? It will only be removed from your side.',
    blockUser: 'Block this user? You won\'t receive messages from them anymore.'
  };
  document.getElementById('confirmTitle').textContent = titles[type] || 'Are you sure?';
  document.getElementById('confirmMsg').textContent   = msgs[type]   || 'This action cannot be undone.';
  confirmCallback = callback || null;
  document.getElementById('confirmModal').classList.add('show');
  document.getElementById('confirmOkBtn').onclick = async () => {
    hideConfirm();
    if (confirmCallback) await confirmCallback();
    confirmCallback = null;
  };
}
function hideConfirm() {
  document.getElementById('confirmModal').classList.remove('show');
}
document.getElementById('confirmModal').addEventListener('click', function(e) {
  if (e.target === this) hideConfirm();
});

// ── LOGOUT ──
async function doLogout() {
  const token = localStorage.getItem('c2dz_token');
  if (token) {
    try {
      await fetch(API + '?action=logout', {
        method: 'POST',
        headers: { 'Authorization': 'Bearer ' + token }
      });
    } catch(e) {}
  }
  localStorage.removeItem('c2dz_token');
  localStorage.removeItem('c2dz_user');
  localStorage.setItem('loggedIn', 'false');
  window.location.href = 'index.php';
}

// ── TABS ──
function showTab(name, btn) {
  document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.snav-btn').forEach(b => b.classList.remove('active'));
  document.getElementById('tab-' + name)?.classList.add('active');
  if (btn) btn.classList.add('active');
  if (name !== 'chat' && pollInterval) { clearInterval(pollInterval); pollInterval = null; }
}

// ── HELPERS ──
function toast(msg, type = '') {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.className = 'toast show' + (type ? ' ' + type : '');
  clearTimeout(t._t);
  t._t = setTimeout(() => t.classList.remove('show'), 3000);
}
function timeAgo(d) {
  const s = (Date.now() - new Date(d)) / 1000;
  if (s < 60) return 'just now';
  if (s < 3600) return Math.floor(s / 60) + 'm ago';
  if (s < 86400) return Math.floor(s / 3600) + 'h ago';
  return Math.floor(s / 86400) + 'd ago';
}
function fmtTime(d) {
  return new Date(d).toLocaleTimeString('en', { hour: '2-digit', minute: '2-digit' });
}
function escapeHtml(str) {
  return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
// ── RESERVE MODAL (CLIENT) ──
let rvMethod = 'cheque';
let rvCurrentConvId = null;
let rvCurrentAgentId = null;

function openClientReserveModal() {
  var carId = window.currentCarId;
  if (carId && carId != '0') {
    fetch('api.php?action=get_car_status&conv_id=' + currentConvId, {
      headers: {'Authorization': 'Bearer ' + localStorage.getItem('c2dz_token')}
    }).then(r => r.json()).then(data => {
      if (data.data.reservation_status === 'reserved') {
        toast('🔒 This car is already reserved', 'err');
        return;
      }
      _openReserveModalNow();
    });
  } else {
    _openReserveModalNow();
  }
}

function _openReserveModalNow() {
  rvCurrentConvId = currentConvId;
  rvCurrentAgentId = currentOtherId;
  document.getElementById('rvFirstName').value = currentUser?.full_name?.split(' ')[0] || '';
  document.getElementById('rvLastName').value  = currentUser?.full_name?.split(' ').slice(1).join(' ') || '';
  document.getElementById('rvPhone').value     = currentUser?.phone || '';
  document.getElementById('rvFilePreview').innerHTML = '';
  document.getElementById('rvFile').value = '';
  selectRvMethod('cheque');
// جيب معلومات الدفع تع الوسيط
fetch('api.php?action=get_payment_info&agent_id=' + rvCurrentAgentId, {
  headers: { 'Authorization': 'Bearer ' + localStorage.getItem('c2dz_token') }
}).then(r => r.json()).then(data => {
  var box = document.getElementById('rvPaymentInfoBox');
  if (!box) return;
  if (data.success && data.data && (data.data.ccp_account || data.data.ccp_rip)) {
    var d = data.data;
    box.style.display = 'block';
    box.innerHTML = `
      <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px;">
        <div style="width:30px;height:30px;border-radius:8px;background:rgba(0,188,212,.12);display:grid;place-items:center;color:var(--red);">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
        </div>
        <div style="font-weight:700;font-size:.83rem;">Agent payment information</div>
      </div>
      <div style="display:flex;flex-direction:column;gap:6px;">
        <div style="display:flex;justify-content:space-between;font-size:.8rem;padding:5px 0;border-bottom:1px solid var(--border);">
          <span style="color:var(--muted);">Bank</span><strong>CCP Algeria</strong>
        </div>
        ${d.ccp_account ? `<div style="display:flex;justify-content:space-between;font-size:.8rem;padding:5px 0;border-bottom:1px solid var(--border);">
          <span style="color:var(--muted);">Account No.</span><strong>${d.ccp_account}</strong>
        </div>` : ''}
        ${d.ccp_rip ? `<div style="display:flex;justify-content:space-between;font-size:.8rem;padding:5px 0;border-bottom:1px solid var(--border);">
          <span style="color:var(--muted);">RIP</span><strong style="font-size:.75rem;">${d.ccp_rip}</strong>
        </div>` : ''}
        ${d.ccp_owner ? `<div style="display:flex;justify-content:space-between;font-size:.8rem;padding:5px 0;border-bottom:1px solid var(--border);">
          <span style="color:var(--muted);">Beneficiary</span><strong>${d.ccp_owner}</strong>
        </div>` : ''}
        ${d.deposit_amount ? `<div style="display:flex;justify-content:space-between;font-size:.8rem;padding:5px 0;">
          <span style="color:var(--muted);">Deposit Amount</span>
          <strong style="color:var(--red);">${Number(d.deposit_amount).toLocaleString('fr-DZ')} DZD</strong>
        </div>` : ''}
      </div>`;
  } else {
    box.style.display = 'none';
  }
});
document.getElementById('rvModal').classList.add('show');
}

function closeRvModal() {
  document.getElementById('rvModal').classList.remove('show');
}

function selectRvMethod(method) {
  rvMethod = method;
  const cheque = document.getElementById('rvMethodCheque');
  const gold   = document.getElementById('rvMethodGold');
  const chequeSection = document.getElementById('rvChequeSection');
  const cardSection   = document.getElementById('rvCardSection');
  if (method === 'cheque') {
    cheque.classList.add('selected');
    gold.classList.remove('selected');
    chequeSection.style.display = 'block';
    cardSection.style.display   = 'none';
  } else {
    gold.classList.add('selected');
    cheque.classList.remove('selected');
    cardSection.style.display   = 'block';
    chequeSection.style.display = 'none';
  }
}

function previewRvFile(event) {
  const f = event.target.files[0];
  if (!f) return;
  const prev = document.getElementById('rvFilePreview');
  if (f.type.startsWith('image/')) {
    const r = new FileReader();
    r.onload = e => {
      prev.innerHTML = `<div class="rv-preview">
        <img src="${e.target.result}" alt="">
        <div><div class="rv-preview-name">${f.name}</div><div class="rv-preview-ok">Ready to upload</div></div>
      </div>`;
    };
    r.readAsDataURL(f);
  } else {
    prev.innerHTML = `<div class="rv-preview">
      <div style="width:52px;height:42px;border-radius:6px;background:var(--card3);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--muted)" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
      </div>
      <div><div class="rv-preview-name">${f.name}</div><div class="rv-preview-ok">Ready to upload</div></div>
    </div>`;
  }
}

async function submitClientReservation() {
  const firstName = document.getElementById('rvFirstName').value.trim();
  const lastName  = document.getElementById('rvLastName').value.trim();
  const phone     = document.getElementById('rvPhone').value.trim();
  
  if (!firstName || !lastName) { toast('Please enter your full name', 'err'); return; }
  if (!phone || phone.length < 9) { toast('Please enter a valid phone number', 'err'); return; }

  if (rvMethod === 'cheque') {
    const file = document.getElementById('rvFile').files[0];
    if (!file) { toast('Please upload your deposit proof', 'err'); return; }
  } else {
    const cardNum  = document.getElementById('rvCardNumber').value.replace(/\s/g,'');
    const cardExp  = document.getElementById('rvCardExpiry').value;
    const cardCvv  = document.getElementById('rvCardCvv').value;
    const cardName = document.getElementById('rvCardName').value.trim();
    if (cardNum.length < 16) { toast('Invalid card number', 'err'); return; }
    if (cardExp.length < 5)  { toast('Invalid expiry date', 'err'); return; }
    if (cardCvv.length < 3)  { toast('Invalid CVV', 'err'); return; }
    if (!cardName)           { toast('Card holder name is required', 'err'); return; }
  }

  const btn = document.getElementById('rvSubmitBtn');
  btn.disabled = true;
  btn.innerHTML = '<div class="spin" style="width:14px;height:14px;border-width:2px;"></div> Sending...';

  const fd = new FormData();
  // جيب car_id من الـ conversation
const convEl = document.getElementById('conv-' + rvCurrentConvId);
const carIdFromConv = convEl?.dataset?.carId || 0;
fd.append('car_id', carIdFromConv);
  fd.append('client_id', currentUser.id);
  fd.append('agent_id', rvCurrentAgentId);
  fd.append('conversation_id', rvCurrentConvId);
  fd.append('first_name', firstName);
  fd.append('last_name', lastName);
  fd.append('phone', phone);
  fd.append('payment_method', rvMethod);
  
  if (rvMethod === 'cheque') {
    fd.append('payment_file', document.getElementById('rvFile').files[0]);
  } else {
    // Golden card — لا نرسل بيانات الكارت للسيرفر، فقط تأكيد الطريقة
    fd.append('card_last4', document.getElementById('rvCardNumber').value.replace(/\s/g,'').slice(-4));
    // نحتاج ملف وهمي — نرسل placeholder
    const placeholder = new Blob(['golden_card_payment'], {type: 'text/plain'});
    fd.append('payment_file', placeholder, 'golden_card_ref.txt');
  }

  try {
    const res = await fetch('reserve_car.php', { method: 'POST', body: fd });
    const data = await res.json();
    if (data.success) {
      closeRvModal();
      toast('Reservation request sent. Agent will confirm within 24h.', 'ok');
      // رسالة تلقائية في الشات
      await fetchMsgs();
    } else {
      toast(data.message || 'Failed to submit', 'err');
    }
  } catch(e) {
    toast('Connection error. Please try again.', 'err');
  }

  btn.disabled = false;
  btn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg> Send Reservation Request';
}

const rvModalEl = document.getElementById('rvModal');
if (rvModalEl) rvModalEl.addEventListener('click', function(e) {
  if (e.target === this) closeRvModal();
});
/* ─── TRACKING ─────────────────────────────── */
const TRACK_STAGES = [
  { key: 'purchased', label: 'Order Confirmed in China',
    icon: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>' },
  { key: 'shipped',   label: 'Shipped — On the Way',
    icon: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13" rx="1"/><path d="M16 8h4l3 5v3h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>' },
  { key: 'customs',   label: 'Customs Clearance',
    icon: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>' },
  { key: 'warehouse', label: 'In Warehouse',
    icon: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>' },
  { key: 'delivery',  label: 'Ready for Delivery',
    icon: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>' },
  { key: 'delivered', label: 'Delivered',
    icon: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>' },
];

async function loadTracking() {
  if (!currentUser) return;
  const list = document.getElementById('trackingList');
  try {
    const res = await fetch(`${API}?action=get_client_reservations&client_id=${currentUser.id}`, {
      headers: { 'Authorization': 'Bearer ' + localStorage.getItem('c2dz_token') }
    });
    const data = await res.json();
    if (!data.success || !data.data || !data.data.length) {
      list.innerHTML = `<div class="empty-state">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><rect x="1" y="3" width="15" height="13" rx="1"/><path d="M16 8h4l3 5v3h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
        <p>No reservations yet</p></div>`;
      return;
    }
    const badge = document.getElementById('trackingBadge');
    if (badge) { badge.textContent = data.data.length; badge.style.display = 'inline'; }
    list.innerHTML = data.data.map(r => buildTrackingCard(r)).join('');
  } catch(e) {
    list.innerHTML = '<div class="empty-state"><p>Error loading tracking data</p></div>';
  }
}

function buildTrackingCard(r) {
  const stageIdx = TRACK_STAGES.findIndex(s => s.key === r.stage);
  const hasStage = stageIdx >= 0;
  const statusColor = r.status === 'accepted' ? 'var(--green)' : r.status === 'refused' ? '#ff5057' : 'var(--red)';
  const statusLabel = r.status === 'accepted' ? 'Confirmed' : r.status === 'refused' ? 'Refused' : 'Pending';

  const stepsHtml = TRACK_STAGES.map((s, idx) => {
    const done    = hasStage && idx < stageIdx;
    const current = hasStage && idx === stageIdx;
    const color   = done ? 'var(--green)' : current ? 'var(--red)' : 'var(--muted)';
    const bg      = done ? 'rgba(34,197,94,.13)' : current ? 'rgba(0,188,212,.13)' : 'var(--card2)';
    const border  = done ? 'rgba(34,197,94,.35)' : current ? 'var(--red)' : 'var(--border)';
    return `<div style="display:flex;align-items:flex-start;gap:10px;padding:8px 0;">
      <div style="display:flex;flex-direction:column;align-items:center;flex-shrink:0;">
        <div style="width:34px;height:34px;border-radius:50%;border:2px solid ${border};
                    background:${bg};color:${color};
                    display:flex;align-items:center;justify-content:center;">
          ${done
            ? '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>'
            : s.icon}
        </div>
        ${idx < TRACK_STAGES.length - 1
          ? `<div style="width:2px;height:24px;background:${done ? 'rgba(34,197,94,.3)' : 'var(--border)'};margin-top:3px;border-radius:2px;"></div>`
          : ''}
      </div>
      <div style="padding-top:7px;flex:1;">
        <div style="font-size:.86rem;font-weight:${current ? '700' : '500'};color:${color};">${s.label}</div>
        ${current && r.stage_note ? `<div style="font-size:.76rem;color:var(--muted);margin-top:3px;">${escapeHtml(r.stage_note)}</div>` : ''}
        ${current && r.tracking_updated ? `<div style="font-size:.71rem;color:var(--muted);margin-top:2px;">${timeAgo(r.tracking_updated)}</div>` : ''}
      </div>
    </div>`;
  }).join('');

  return `<div style="border:1px solid var(--border);border-radius:14px;padding:18px;margin-bottom:14px;background:var(--card2);">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:8px;">
      <div>
        <div style="font-weight:700;font-size:.95rem;">${escapeHtml(r.car_title)}</div>
        <div style="font-size:.78rem;color:var(--muted);margin-top:3px;">
          Agent: ${escapeHtml(r.agent_name)} &bull; ${timeAgo(r.created_at)}
        </div>
      </div>
      <span style="padding:3px 12px;border-radius:20px;font-size:.74rem;font-weight:700;
                   background:${r.status === 'accepted' ? 'rgba(34,197,94,.13)' : r.status === 'refused' ? 'rgba(255,80,87,.13)' : 'rgba(0,188,212,.12)'};
                   color:${statusColor};">${statusLabel}</span>
    </div>
    ${r.status === 'accepted'
      ? hasStage
        ? stepsHtml
        : `<div style="text-align:center;padding:20px;color:var(--muted);font-size:.84rem;">
             <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom:8px;opacity:.35;display:block;margin-left:auto;margin-right:auto;"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
             Waiting for agent to start shipment tracking
           </div>`
      : `<div style="text-align:center;padding:16px;color:var(--muted);font-size:.84rem;">
           Tracking available once reservation is confirmed
         </div>`}
  </div>`;
}
async function agentRespondReservation(reservationId, action) {
  const res = await apiCall('respond_reservation', 'POST', { reservation_id: reservationId, action });
  if (res.success) {
    toast(action === 'accepted' ? 'Reservation accepted ✓' : 'Reservation refused', action === 'accepted' ? 'ok' : '');
    await fetchMsgs();
  } else {
    toast(res.message || 'Error', 'err');
  }
}
</script>
<!-- RESERVE MODAL (Client) -->
<div class="rv-modal" id="rvModal">
  <div class="rv-box">
    <div class="rv-header">
      <h3>
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        Reserve This Car
      </h3>
      <button class="rv-close" onclick="closeRvModal()">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <div class="rv-body">
      <div class="rv-info-banner">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <span>Submit your details and proof of initial deposit. The agent will review and confirm within <strong>24 hours</strong>. Car stays reserved for <strong>4 days</strong> once confirmed.</span>
      </div>

      <div class="rv-section-label">Your Information</div>
      <div class="form-row" style="margin-bottom:12px;">
        <div class="fgroup" style="margin-bottom:0;">
          <label>First Name</label>
          <input type="text" class="fgroup input" id="rvFirstName" placeholder="First name"
                 style="width:100%;padding:10px 13px;background:var(--card2);border:1px solid var(--border);border-radius:10px;color:var(--text);font-family:'DM Sans',sans-serif;font-size:.9rem;outline:none;">
        </div>
        <div class="fgroup" style="margin-bottom:0;">
          <label>Last Name</label>
          <input type="text" class="fgroup input" id="rvLastName" placeholder="Last name"
                 style="width:100%;padding:10px 13px;background:var(--card2);border:1px solid var(--border);border-radius:10px;color:var(--text);font-family:'DM Sans',sans-serif;font-size:.9rem;outline:none;">
        </div>
      </div>
      <div class="fgroup" style="margin-bottom:14px;">
        <label>Phone Number</label>
        <input type="tel" id="rvPhone" placeholder="e.g. 0551234567"
               style="width:100%;padding:10px 13px;background:var(--card2);border:1px solid var(--border);border-radius:10px;color:var(--text);font-family:'DM Sans',sans-serif;font-size:.9rem;outline:none;">
      </div>

      <div class="rv-section-label">Payment Method for Deposit</div>
      <div class="rv-method-grid">
        <div class="rv-method-card selected" id="rvMethodCheque" onclick="selectRvMethod('cheque')">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/><line x1="6" y1="15" x2="10" y2="15"/></svg>
          <div class="rv-method-name">Payment recu</div>
          <div class="rv-method-sub">Upload a photo or scan of your recu</div>
        </div>
        <!-- بعد -->
<div class="rv-method-card" id="rvMethodGold" style="opacity:.4;cursor:not-allowed;pointer-events:none;">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/><circle cx="17" cy="15" r="2"/></svg>
          <div class="rv-method-name">Golden Card</div>
          <div class="rv-method-sub">Enter your card details securely</div>
        </div>
      </div>
    <!-- Payment Info Box -->
      <div id="rvPaymentInfoBox" style="display:none;background:rgba(0,188,212,.05);border:1px solid rgba(0,188,212,.2);border-radius:10px;padding:12px 14px;margin-top:14px;"></div>

      <!-- Cheque upload -->
      <div id="rvChequeSection" style="margin-top:14px;">
        <div class="rv-section-label">Upload Deposit Proof</div>
        <div class="rv-upload-zone" onclick="document.getElementById('rvFile').click()">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
          <p>Click to upload photo or PDF</p>
          <input type="file" id="rvFile" accept="image/*,application/pdf" style="display:none;" onchange="previewRvFile(event)">
        </div>
        <div id="rvFilePreview"></div>
      </div>
      <!-- Golden card fields -->
      <div id="rvCardSection" style="display:none; margin-top:14px; pointer-events:none; opacity:.4">
        <div class="rv-section-label">Card Details</div>
        <div class="rv-card-fields">
          <div class="fgroup" style="margin-bottom:0;">
            <label>Card Number</label>
            <input type="text" id="rvCardNumber" placeholder="0000 0000 0000 0000" maxlength="19"
                   oninput="this.value=this.value.replace(/\D/g,'').replace(/(.{4})/g,'$1 ').trim().substring(0,19)"
                   style="width:100%;padding:10px 13px;background:var(--card2);border:1px solid var(--border);border-radius:10px;color:var(--text);font-family:'DM Sans',sans-serif;font-size:.9rem;outline:none;letter-spacing:2px;">
          </div>
          <div class="rv-card-row">
            <div class="fgroup" style="margin-bottom:0;">
              <label>Expiry Date</label>
              <input type="text" id="rvCardExpiry" placeholder="MM/YY" maxlength="5"
                     oninput="var v=this.value.replace(/\D/g,'').substring(0,4);if(v.length>=2)v=v.substring(0,2)+'/'+v.substring(2);this.value=v;"
                     style="width:100%;padding:10px 13px;background:var(--card2);border:1px solid var(--border);border-radius:10px;color:var(--text);font-family:'DM Sans',sans-serif;font-size:.9rem;outline:none;">
            </div>
            <div class="fgroup" style="margin-bottom:0;">
              <label>CVV</label>
              <input type="text" id="rvCardCvv" placeholder="123" maxlength="3"
                     style="width:100%;padding:10px 13px;background:var(--card2);border:1px solid var(--border);border-radius:10px;color:var(--text);font-family:'DM Sans',sans-serif;font-size:.9rem;outline:none;">
            </div>
          </div>
          <div class="fgroup" style="margin-bottom:0;">
            <label>Card Holder Name</label>
            <input type="text" id="rvCardName" placeholder="Name on card"
                   style="width:100%;padding:10px 13px;background:var(--card2);border:1px solid var(--border);border-radius:10px;color:var(--text);font-family:'DM Sans',sans-serif;font-size:.9rem;outline:none;">
          </div>
          <div style="display:flex;align-items:center;gap:7px;font-size:.75rem;color:var(--green);padding:8px 12px;background:rgba(34,197,94,.07);border:1px solid rgba(34,197,94,.2);border-radius:8px;">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            SSL 256-bit Secure — your card info is encrypted
          </div>
        </div>
      </div>
    </div>
    <div class="rv-footer">
      <button class="btn btn-outline" onclick="closeRvModal()">Cancel</button>
      <button class="rv-btn-submit" id="rvSubmitBtn" onclick="submitClientReservation()">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
        Send Reservation Request
      </button>
    </div>
  </div>
</div>
</body>
</html>