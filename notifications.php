<!DOCTYPE html>
<html lang="en">
<head>
<script>
  if (!localStorage.getItem('c2dz_user')) {
    window.location.replace('login.html');
  }
</script>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Notifications – China2DZ</title>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&family=Playfair+Display:wght@700;900&display=swap" rel="stylesheet">
<style>
[data-theme="light"] body { background: #f5f5f7; color: #1a1a2e; }
[data-theme="light"] body::before { display: none; }
[data-theme="light"] .header { background: rgba(255,255,255,0.97); border-color: rgba(0,0,0,0.08); }
[data-theme="light"] .back-btn { background: rgba(0,0,0,0.05); border-color: rgba(0,0,0,0.1); }
[data-theme="light"] .back-btn svg { stroke: #1a1a2e; }
[data-theme="light"] .action-btn { color: #1a1a2e; border-color: rgba(0,0,0,0.15); background: rgba(0,0,0,0.04); }
[data-theme="light"] .tabs { border-color: rgba(0,0,0,0.1); }
[data-theme="light"] .tab-btn { color: #6b7280; }
[data-theme="light"] .tab-btn.active { color: #1a1a2e; }
[data-theme="light"] .notif-item { background: #fff; border-bottom-color: rgba(0,0,0,0.06); }
[data-theme="light"] .notif-item:hover { background: #f5f5f7; }
[data-theme="light"] .notif-item.unread { background: rgba(0,188,212,0.06); }
[data-theme="light"] .notif-title { color: #1a1a2e; }
[data-theme="light"] .notif-msg { color: #6b7280; }
[data-theme="light"] .notif-time { color: #9ca3af; }
[data-theme="light"] .notif-more-btn { color: #6b7280; }
[data-theme="light"] .notif-more-btn:hover { background: rgba(0,0,0,0.06); color: #1a1a2e; }
[data-theme="light"] .notif-avatar-placeholder { background: rgba(0,0,0,0.06); border-color: rgba(0,0,0,0.1); }
[data-theme="light"] .section-label { color: #9ca3af; }
[data-theme="light"] .empty-title { color: #1a1a2e; }
[data-theme="light"] .empty-sub { color: #6b7280; }
[data-theme="light"] .empty-icon { background: rgba(0,0,0,0.04); border-color: rgba(0,0,0,0.08); }
[data-theme="light"] .header { background: rgba(245,245,247,0.95); }
[data-theme="light"] .notif-item { background: rgba(0,0,0,0.02); }
[data-theme="light"] .notif-item.unread { background: rgba(0,188,212,0.05); }
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}

:root {
  --red: #00bcd4;
  --red-soft: rgba(0,188,212,0.12);
  --bg: #0d0d0d;
  --card: rgba(255,255,255,0.035);
  --border: rgba(255,255,255,0.08);
  --text: #fff;
  --text-muted: rgba(255,255,255,0.4);
}

body {
  min-height: 100vh;
  background: var(--bg);
  font-family: 'Montserrat', sans-serif;
  color: var(--text);
}
body::before {
  content: '';
  position: fixed;
  inset: 0;
  background:
    radial-gradient(ellipse 70% 50% at 15% 10%, rgba(0,188,212,0.09) 0%, transparent 60%),
    radial-gradient(ellipse 50% 40% at 85% 80%, rgba(0,188,212,0.06) 0%, transparent 60%);
  pointer-events: none;
  z-index: 0;
}

/* HEADER */
.header {
  position: sticky; top: 0; z-index: 100;
  position: relative;
  background: rgba(13,13,13,0.92);
  backdrop-filter: blur(20px);
  border-bottom: 1px solid var(--border);
  padding: 0 20px; height: 60px;
  display: flex; align-items: center; justify-content: space-between;
}
.header-left { display: flex; align-items: center; gap: 14px; }
.back-btn {
  width: 36px; height: 36px; border-radius: 50%;
  background: rgba(255,255,255,0.06);
  border: 1px solid var(--border);
  display: flex; align-items: center; justify-content: center;
  cursor: pointer; transition: background 0.2s; text-decoration: none;
}
.back-btn:hover { background: rgba(255,255,255,0.1); }
.back-btn svg { width: 16px; height: 16px; stroke: #fff; fill: none; stroke-width: 2.2; stroke-linecap: round; stroke-linejoin: round; }
.header-title { font-size: 18px; font-weight: 800; }
.action-btn {
  height: 34px; padding: 0 14px; border-radius: 20px;
  border: 1px solid var(--border); background: rgba(255,255,255,0.05);
  color: var(--text-muted); font-family: 'Montserrat', sans-serif;
  font-size: 11px; font-weight: 700; cursor: pointer;
  transition: all 0.2s; display: flex; align-items: center; gap: 6px;
}
.action-btn:hover { border-color: var(--red); color: var(--red); background: var(--red-soft); }
.action-btn svg { width: 13px; height: 13px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }

/* TABS */
.tabs {
  display: flex; gap: 4px; padding: 14px 20px 0;
  border-bottom: 1px solid var(--border);
  position: relative; z-index: 1;
  overflow-x: auto; scrollbar-width: none;
}
.tabs::-webkit-scrollbar { display: none; }
.tab-btn {
  padding: 8px 16px 12px; background: none; border: none;
  color: var(--text-muted); font-family: 'Montserrat', sans-serif;
  font-size: 12px; font-weight: 700; cursor: pointer;
  position: relative; transition: color 0.2s; white-space: nowrap;
}
.tab-btn.active { color: #fff; }
.tab-btn.active::after {
  content: ''; position: absolute; bottom: -1px; left: 0; right: 0;
  height: 2px; background: var(--red); border-radius: 2px 2px 0 0;
}
.tab-badge {
  display: inline-flex; align-items: center; justify-content: center;
  min-width: 18px; height: 18px; background: var(--red); color: #fff;
  font-size: 9px; font-weight: 800; border-radius: 9px;
  padding: 0 5px; margin-right: 5px;
}

/* CONTENT */
.content { position: relative; z-index: 1; max-width: 700px; margin: 0 auto; padding: 0 0 80px; }
.section-label {
  padding: 18px 20px 8px; font-size: 10px; font-weight: 800;
  color: var(--text-muted); text-transform: uppercase; letter-spacing: 1.2px;
}

/* NOTIFICATION ITEM */
.notif-item {
  display: flex; align-items: flex-start; gap: 13px;
  padding: 14px 20px; border-bottom: 1px solid rgba(255,255,255,0.04);
  cursor: pointer; transition: background 0.18s; position: relative;
}
.notif-item:hover { background: rgba(255,255,255,0.03); }
.notif-item.unread { background: rgba(0,188,212,0.04); }
.notif-item.unread:hover { background: rgba(0,188,212,0.07); }
.unread-dot {
  position: absolute; right: 54px; top: 50%; transform: translateY(-50%);
  width: 8px; height: 8px; border-radius: 50%; background: var(--red);
}

/* AVATAR */
.notif-avatar { position: relative; flex-shrink: 0; width: 48px; height: 48px; }
.notif-avatar-placeholder {
  width: 48px; height: 48px; border-radius: 50%;
  background: rgba(255,255,255,0.07); border: 2px solid var(--border);
  display: flex; align-items: center; justify-content: center; font-size: 20px;
}
.notif-type-icon {
  position: absolute; bottom: -2px; left: -2px;
  width: 20px; height: 20px; border-radius: 50%;
  border: 2px solid var(--bg);
  display: flex; align-items: center; justify-content: center; font-size: 9px;
}
.notif-type-icon.like    { background: #e1306c; }
.notif-type-icon.comment { background: #1877f2; }
.notif-type-icon.message { background: #25d366; }
.notif-type-icon.offer   { background: #f5a623; }
.notif-type-icon.info    { background: #6c757d; }
.notif-type-icon.success { background: #28a745; }
.notif-type-icon.warning { background: #ffc107; }

/* BODY */
.notif-body { flex: 1; min-width: 0; }
.notif-title { font-size: 13px; font-weight: 600; line-height: 1.4; margin-bottom: 3px; }
.notif-msg { font-size: 12px; color: var(--text-muted); line-height: 1.4; margin-bottom: 4px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.notif-time { font-size: 10.5px; color: var(--text-muted); font-weight: 600; }

.notif-more-btn {
  flex-shrink: 0; width: 32px; height: 32px; border-radius: 50%;
  background: none; border: none; color: var(--text-muted);
  cursor: pointer; display: flex; align-items: center; justify-content: center;
  transition: background 0.18s; margin-top: 6px;
}
.notif-more-btn:hover { background: rgba(255,255,255,0.07); color: #fff; }
.notif-more-btn svg { width: 16px; height: 16px; fill: currentColor; }

/* EMPTY */
.empty-state { text-align: center; padding: 80px 20px; display: none; }
.empty-state.show { display: block; }
.empty-icon {
  width: 70px; height: 70px; border-radius: 50%;
  background: rgba(255,255,255,0.05); border: 1px solid var(--border);
  display: flex; align-items: center; justify-content: center;
  margin: 0 auto 18px; font-size: 28px;
}
.empty-title { font-size: 16px; font-weight: 800; margin-bottom: 8px; }
.empty-sub { font-size: 13px; color: var(--text-muted); }

/* LOADING */
.loading-wrap { display: flex; align-items: center; justify-content: center; padding: 60px; }
.spinner {
  width: 32px; height: 32px; border: 3px solid rgba(255,255,255,0.08);
  border-top-color: var(--red); border-radius: 50%;
  animation: spin 0.7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* DROPDOWN */
.dropdown-menu {
  position: fixed; z-index: 999; background: #1a1a1a;
  border: 1px solid rgba(255,255,255,0.12); border-radius: 14px;
  box-shadow: 0 20px 50px rgba(0,0,0,0.7); overflow: hidden;
  min-width: 220px; display: none;
  animation: dropIn 0.15s ease;
}
.dropdown-menu.show { display: block; }
@keyframes dropIn { from{opacity:0;transform:translateY(-6px)} to{opacity:1;transform:translateY(0)} }
.dropdown-item {
  display: flex; align-items: center; gap: 12px;
  padding: 13px 16px; font-size: 13px; font-weight: 600;
  color: rgba(255,255,255,0.8); cursor: pointer; transition: background 0.15s;
}
.dropdown-item:hover { background: rgba(255,255,255,0.06); }
.dropdown-item.danger { color: #ff5057; }
.dropdown-item.danger:hover { background: rgba(255,80,87,0.08); }
.dropdown-item svg { width: 15px; height: 15px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
.dropdown-divider { height: 1px; background: rgba(255,255,255,0.07); margin: 4px 0; }

/* TOAST */
.toast {
  position: fixed; bottom: 30px; left: 50%;
  transform: translateX(-50%) translateY(20px);
  background: #1e1e1e; border: 1px solid rgba(255,255,255,0.1);
  color: #fff; padding: 12px 22px; border-radius: 30px;
  font-size: 13px; font-weight: 600; opacity: 0;
  transition: all 0.3s ease; z-index: 9999; pointer-events: none;
}
.toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }

/* TABS PANELS */
.tab-panel { display: none; }
.tab-panel.active { display: block; }
[data-theme="light"] #logo2dz { color: #1a1a2e !important; }
</style>
<script src="theme.js"></script>
</head>
<body>

<div class="header">
  <div class="header-left">
    <a href="index.php" class="back-btn">
      <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
    </a>
  </div>
  <a href="index.php" style="position:absolute;left:50%;transform:translateX(-50%);font-family:'Playfair Display',serif;font-size:22px;font-weight:900;text-decoration:none;">
    <span style="color:#00bcd4;">China</span><span id="logo2dz" style="color:#fff;">2DZ</span>
  </a>
  <button class="action-btn" onclick="markAllRead()">
    <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
    Mark all as read
  </button>
</div>

<div class="tabs">
  <button class="tab-btn active" onclick="switchTab('all',this)">
    All <span class="tab-badge" id="badge-all" style="display:none">0</span>
  </button>
  <button class="tab-btn" onclick="switchTab('unread',this)">
    Unread <span class="tab-badge" id="badge-unread" style="display:none">0</span>
  </button>
  <button class="tab-btn" onclick="switchTab('messages',this)">Messages</button>
  <button class="tab-btn" onclick="switchTab('offers',this)">Offers</button>
</div>

<div class="content">
  <div class="tab-panel active" id="panel-all">
    <div class="loading-wrap" id="loading-all"><div class="spinner"></div></div>
    <div id="list-today"></div>
    <div id="list-earlier"></div>
    <div class="empty-state" id="empty-all">
      <div class="empty-icon">🔔</div>
      <div class="empty-title">No notifications</div>
      <div class="empty-sub">Your notifications will appear here</div>
    </div>
  </div>
  <div class="tab-panel" id="panel-unread">
    <div id="list-unread"></div>
    <div class="empty-state" id="empty-unread">
      <div class="empty-icon">✅</div>
      <div class="empty-title">All caught up!</div>
      <div class="empty-sub">You have no unread notifications</div>
    </div>
  </div>
  <div class="tab-panel" id="panel-messages">
    <div id="list-messages"></div>
    <div class="empty-state" id="empty-messages">
      <div class="empty-icon">💬</div>
      <div class="empty-title">No messages</div>
      <div class="empty-sub">Message notifications will appear here</div>
    </div>
  </div>
  <div class="tab-panel" id="panel-offers">
    <div id="list-offers"></div>
    <div class="empty-state" id="empty-offers">
      <div class="empty-icon">🚗</div>
      <div class="empty-title">No offers</div>
      <div class="empty-sub">Offer notifications will appear here</div>
    </div>
  </div>
</div>

<!-- DROPDOWN -->
<div class="dropdown-menu" id="dropdownMenu">
  <div class="dropdown-item" onclick="markOneRead()">
    <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
    Mark as read
  </div>
  <div class="dropdown-divider"></div>
  <div class="dropdown-item danger" onclick="deleteOne()">
    <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>
    Delete notification
  </div>
</div>

<div class="toast" id="toastEl"></div>

<script>
(function(){
  var userRaw = localStorage.getItem('c2dz_user');
  var currentUser = userRaw ? JSON.parse(userRaw) : null;
  if (!currentUser || !currentUser.id) { window.location.href = 'login.html'; return; }
  var userId = currentUser.id;

  var allNotifs = [];
  var activeDropdownId = null;

  // ── LOAD ──
  function loadNotifications() {
    fetch('notification_action.php?action=get&user_id=' + userId)
      .then(function(r){ return r.json(); })
      .then(function(data){
        document.getElementById('loading-all').style.display = 'none';
        if (data.success) { allNotifs = data.notifications || []; renderAll(); }
        else { showEmpty('all'); }
      })
      .catch(function(){ document.getElementById('loading-all').style.display = 'none'; showEmpty('all'); });
  }

  // ── RENDER ──
  function renderAll() {
    var now = new Date();
    var todayStart = new Date(now.getFullYear(), now.getMonth(), now.getDate());
    var today    = allNotifs.filter(function(n){ return new Date(n.created_at) >= todayStart; });
    var earlier  = allNotifs.filter(function(n){ return new Date(n.created_at) < todayStart; });
    var unread   = allNotifs.filter(function(n){ return n.is_read == 0; });
    var messages = allNotifs.filter(function(n){ return n.type === 'message'; });
    var offers   = allNotifs.filter(function(n){ return n.type === 'offer'; });

    // Badges
    var ba = document.getElementById('badge-all');
    ba.textContent = allNotifs.length; ba.style.display = allNotifs.length ? 'inline-flex' : 'none';
    var bu = document.getElementById('badge-unread');
    bu.textContent = unread.length; bu.style.display = unread.length ? 'inline-flex' : 'none';

    // Lists
    renderSection('list-today',    today.length    ? '<div class="section-label">Today</div>'   + today.map(buildItem).join('') : '');
    renderSection('list-earlier',  earlier.length  ? '<div class="section-label">Earlier</div>' + earlier.map(buildItem).join('') : '');
    renderSection('list-unread',   unread.map(buildItem).join(''));
    renderSection('list-messages', messages.map(buildItem).join(''));
    renderSection('list-offers',   offers.map(buildItem).join(''));

    // Empties
    toggleEmpty('all',      !allNotifs.length);
    toggleEmpty('unread',   !unread.length);
    toggleEmpty('messages', !messages.length);
    toggleEmpty('offers',   !offers.length);
  }

  function renderSection(id, html) { document.getElementById(id).innerHTML = html; }

  function buildItem(n) {
    var link    = n.link || '#';
    var isUnread = n.is_read == 0;

    // ── Avatar ──
    var avatarHtml;
    if (n.sender_photo) {
        avatarHtml = '<img src="' + esc(n.sender_photo) + '" style="width:48px;height:48px;border-radius:50%;object-fit:cover;border:2px solid rgba(255,255,255,0.1)">';
    } else {
        avatarHtml = '<div class="notif-avatar-placeholder">👤</div>';
    }

    // ── Type icon + preview ──
    var typeIcons = { like:'❤️', comment:'💬', message:'✉️', offer:'🚗', info:'ℹ️', success:'✅', warning:'⚠️' };
    var icon = typeIcons[n.type] || 'ℹ️';

    // ── Title: اسم المرسل واضح ──
    var senderLabel = n.sender_name ? '<span style="color:#00bcd4;font-weight:700">' + esc(n.sender_name) + '</span> — ' : '';

    // ── Preview line حسب النوع ──
    var preview = n.message || '';
    if (n.type === 'comment')  preview = '💬 ' + preview;
    if (n.type === 'like')     preview = '❤️ ' + preview;
    if (n.type === 'message')  preview = '✉️ ' + preview;
    if (n.type === 'offer')    preview = '🚗 ' + preview;

    var time = formatTime(n.created_at);

    return '<div class="notif-item' + (isUnread?' unread':'') + '" data-id="' + n.id + '" onclick="handleClick(event,' + n.id + ',\'' + link + '\')" style="cursor:pointer">' +
        '<div class="notif-avatar">' +
            avatarHtml +
            '<div class="notif-type-icon ' + (n.type||'info') + '">' + icon + '</div>' +
        '</div>' +
        '<div class="notif-body">' +
            '<div class="notif-title">' + senderLabel + esc(n.title||'') + '</div>' +
            '<div class="notif-msg">' + esc(preview) + '</div>' +
            '<div class="notif-time">' + time + '</div>' +
        '</div>' +
        (isUnread ? '<div class="unread-dot"></div>' : '') +
        '<button class="notif-more-btn" onclick="openDropdown(event,' + n.id + ')">' +
            '<svg viewBox="0 0 24 24"><circle cx="12" cy="5" r="1.2"/><circle cx="12" cy="12" r="1.2"/><circle cx="12" cy="19" r="1.2"/></svg>' +
        '</button>' +
    '</div>';
}

  // ── CLICK ──
  window.handleClick = function(e, id, link) {
    if (e.target.closest('.notif-more-btn')) return;
    // mark read silently
    fetch('notification_action.php', {
      method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({ action:'mark_read', id:id, user_id:userId })
    });
    var n = allNotifs.find(function(x){ return x.id==id; });
    if (n) n.is_read = 1;
    // update DOM
    document.querySelectorAll('.notif-item[data-id="'+id+'"]').forEach(function(el){
      el.classList.remove('unread');
      var dot = el.querySelector('.unread-dot'); if(dot) dot.remove();
    });
    // refresh badges
    var unread = allNotifs.filter(function(x){ return x.is_read==0; });
    var bu = document.getElementById('badge-unread');
    bu.textContent = unread.length; bu.style.display = unread.length ? 'inline-flex' : 'none';
    // navigate
    // navigate — إذا link فاضي روحي لـ chat
    if (n && n.type === 'alert') {
    if (link && link !== '#' && link !== '') {
    window.location.href = link;
} else if (n.related_id) {
    window.location.href = 'listing.php?id=' + n.related_id;
    } else if (link && link !== '#' && link !== '') {
        window.location.href = link;
    } else {
        window.location.href = 'index.php';
    }
} else if (n && n.type === 'message') {
    window.location.href = 'profile.php?tab=chat';
} else if (link && link !== '#' && link !== '') {
    window.location.href = link;
} else {
    window.location.href = 'index.php';
}
  };

  // ── DROPDOWN ──
  window.openDropdown = function(e, id) {
    e.stopPropagation();
    activeDropdownId = id;
    var menu = document.getElementById('dropdownMenu');
    var rect = e.currentTarget.getBoundingClientRect();
    menu.style.top  = (rect.bottom + 6) + 'px';
    menu.style.left = Math.max(10, rect.left - 190) + 'px';
    menu.classList.add('show');
  };
  document.addEventListener('click', function(e){
    if (!e.target.closest('#dropdownMenu') && !e.target.closest('.notif-more-btn')) {
      document.getElementById('dropdownMenu').classList.remove('show');
    }
  });

  // ── MARK ONE READ ──
  window.markOneRead = function() {
    if (!activeDropdownId) return;
    document.getElementById('dropdownMenu').classList.remove('show');
    fetch('notification_action.php', {
      method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({ action:'mark_read', id:activeDropdownId, user_id:userId })
    }).then(function(r){ return r.json(); }).then(function(data){
      if (data.success) {
        var n = allNotifs.find(function(x){ return x.id==activeDropdownId; });
        if (n) n.is_read = 1;
        renderAll(); toast('Marked as read ✓');
      }
    });
  };

  // ── DELETE ──
  window.deleteOne = function() {
    if (!activeDropdownId) return;
    document.getElementById('dropdownMenu').classList.remove('show');
    var delId = activeDropdownId;
    fetch('notification_action.php', {
      method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({ action:'delete', id:delId, user_id:userId })
    }).then(function(r){ return r.json(); }).then(function(data){
      if (data.success) {
        allNotifs = allNotifs.filter(function(x){ return x.id != delId; });
        renderAll(); toast('Notification deleted');
      }
    });
  };

  // ── MARK ALL READ ──
  window.markAllRead = function() {
    fetch('notification_action.php', {
      method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({ action:'mark_all_read', user_id:userId })
    }).then(function(r){ return r.json(); }).then(function(data){
      if (data.success) {
        allNotifs.forEach(function(n){ n.is_read = 1; });
        renderAll(); toast('All notifications marked as read ✓');
      }
    });
  };

  // ── TABS ──
  window.switchTab = function(tab, btn) {
    document.querySelectorAll('.tab-btn').forEach(function(b){ b.classList.remove('active'); });
    btn.classList.add('active');
    document.querySelectorAll('.tab-panel').forEach(function(p){ p.classList.remove('active'); });
    document.getElementById('panel-'+tab).classList.add('active');
  };

  // ── HELPERS ──
  function toggleEmpty(tab, show) {
    var el = document.getElementById('empty-'+tab);
    if (el) el.classList[show?'add':'remove']('show');
  }
  function esc(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }
  function formatTime(ts) {
    if (!ts) return '';
    var d = new Date(ts), now = new Date();
    var diff = Math.floor((now - d) / 1000);
    if (diff < 60)     return 'Just now';
    if (diff < 3600)   return Math.floor(diff/60) + ' min ago';
    if (diff < 86400)  return Math.floor(diff/3600) + ' hr ago';
    if (diff < 604800) return Math.floor(diff/86400) + ' days ago';
    return d.toLocaleDateString('en-US', { day:'numeric', month:'short' });
  }
  function toast(msg) {
    var t = document.getElementById('toastEl');
    t.textContent = msg; t.classList.add('show');
    clearTimeout(t._t); t._t = setTimeout(function(){ t.classList.remove('show'); }, 3000);
  }

  // ── INIT ──
  loadNotifications();
  setInterval(loadNotifications, 30000);
})();
</script>
</body>
</html>