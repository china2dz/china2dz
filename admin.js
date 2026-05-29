/* ═══════════════════════════════════════
   ADMIN OS — JS  (Full navigation + profiles)
═══════════════════════════════════════ */

// ── DATA ─────────────────────────────
let agents = [
  { id:'A-001', name:'Mohamed Salah',   email:'msalah@mail.com',    phone:'+213 555 001 001', wilaya:'Alger',    joined:'2024-12-01', status:'active',  plan:'Pro',     listings:12, rating:4.8 },
  { id:'A-002', name:'Karim Boukhalfa', email:'kbouk@mail.com',     phone:'+213 555 002 002', wilaya:'Oran',     joined:'2025-01-15', status:'pending', plan:'—',       listings:0,  rating:0   },
  { id:'A-003', name:'Yacine Hamidi',   email:'yhamidi@mail.com',   phone:'+213 555 003 003', wilaya:'Constantine',joined:'2025-02-08',status:'pending',plan:'—',       listings:0,  rating:0   },
  { id:'A-004', name:'Sara Benali',     email:'sbenali@mail.com',   phone:'+213 555 004 004', wilaya:'Annaba',   joined:'2025-02-20', status:'active',  plan:'Basic',   listings:7,  rating:4.5 },
  { id:'A-005', name:'Omar Djebbari',   email:'odjebbari@mail.com', phone:'+213 555 005 005', wilaya:'Sétif',    joined:'2025-03-01', status:'blocked', plan:'Basic',   listings:3,  rating:3.1 },
  { id:'A-006', name:'Nadia Khelil',    email:'nkhelil@mail.com',   phone:'+213 555 006 006', wilaya:'Blida',    joined:'2025-03-10', status:'active',  plan:'Premium', listings:21, rating:4.9 },
  { id:'A-007', name:'Riad Messaoudi',  email:'rmessaoudi@mail.com',phone:'+213 555 007 007', wilaya:'Béjaïa',   joined:'2025-03-22', status:'pending', plan:'—',       listings:0,  rating:0   },
  { id:'A-008', name:'Amina Zahaf',     email:'azahaf@mail.com',    phone:'+213 555 008 008', wilaya:'Tlemcen',  joined:'2025-04-01', status:'active',  plan:'Pro',     listings:9,  rating:4.7 },
];

let subscriptions = [
  { id:'S-001', agentId:'A-001', agentName:'Mohamed Salah',  plan:'Pro',     proof:'proof_A001.jpg', requested:'2025-04-05', status:'pending'  },
  { id:'S-002', agentId:'A-004', agentName:'Sara Benali',    plan:'Basic',   proof:'proof_A004.jpg', requested:'2025-04-06', status:'approved' },
  { id:'S-003', agentId:'A-006', agentName:'Nadia Khelil',   plan:'Premium', proof:'proof_A006.jpg', requested:'2025-04-07', status:'pending'  },
  { id:'S-004', agentId:'A-008', agentName:'Amina Zahaf',    plan:'Pro',     proof:'proof_A008.jpg', requested:'2025-04-08', status:'rejected' },
];

let reports = [
  { id:'R-001', reporter:'User #U-892', agentId:'A-007', agent:'A-007 · Riad Messaoudi',  reason:'Fake listings',           date:'2025-04-08' },
  { id:'R-002', reporter:'User #U-451', agentId:'A-005', agent:'A-005 · Omar Djebbari',   reason:'Inappropriate behavior',  date:'2025-04-07' },
  { id:'R-003', reporter:'User #U-201', agentId:'A-002', agent:'A-002 · Karim Boukhalfa', reason:'Spam messages',            date:'2025-04-05' },
];

let currentFilter = 'all';
let activeSubId   = null;
let previousPage  = 'agents'; // page to go back to from profile

// ── INIT ─────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  lucide.createIcons();
  setupSidebar();
  setupNavigation();
  setupGlobalSearch();
  renderAgents();
  renderSubscriptions();
  renderReports();
  setupModal();
  setupNotifications();
  setupSearch();
  setupFilters();
  updateAllStats();
});

// ── NAVIGATION ───────────────────────
function setupNavigation() {
  // Sidebar nav items
  document.querySelectorAll('.nav-item[data-page]').forEach(item => {
    item.addEventListener('click', e => {
      e.preventDefault();
      navigateTo(item.dataset.page);
    });
  });

  // Top bar bell → notifications
  document.querySelector('.topbar-right [data-page="notifications"]')
    ?.addEventListener('click', () => navigateTo('notifications'));

  // Breadcrumb root click → dashboard
  document.getElementById('bcRoot').addEventListener('click', () => navigateTo('dashboard'));

  // Stat cards
  document.querySelectorAll('.stat-card.clickable[data-page]').forEach(card => {
    card.addEventListener('click', () => {
      const page   = card.dataset.page;
      const filter = card.dataset.filter;
      navigateTo(page, { filter });
    });
  });

  // Quick action buttons
  document.querySelectorAll('.action-btn[data-page]').forEach(btn => {
    btn.addEventListener('click', () => {
      const page   = btn.dataset.page;
      const filter = btn.dataset.filter;
      navigateTo(page, { filter });
    });
  });

  // Activity list items
  document.querySelectorAll('.activity-item.clickable[data-page]').forEach(item => {
    item.addEventListener('click', () => handleActivityClick(item));
  });

  // Back to agents from profile
  document.getElementById('backToAgents').addEventListener('click', () => {
    navigateTo(previousPage);
  });
}

function navigateTo(page, opts = {}) {
  // Hide all pages
  document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));

  const target = document.getElementById(`page-${page}`);
  if (target) target.classList.add('active');

  // Update sidebar active state
  document.querySelectorAll('.nav-item[data-page]').forEach(item => {
    const navPage = page.startsWith('agent-') ? 'agents' : page;
    item.classList.toggle('active', item.dataset.page === navPage);
  });

  // Update breadcrumb
  const titles = {
    dashboard:      'Dashboard',
    notifications:  'Notifications',
    agents:         'Agents',
    'agent-profile':'Agents',
    subscriptions:  'Subscriptions',
    monitoring:     'Monitoring',
    reports:        'Reports',
  };
  document.getElementById('bcRoot').textContent = titles[page] || page;

  // Sub-breadcrumb (for profile)
  const bcSep   = document.getElementById('bcSep');
  const bcChild = document.getElementById('bcChild');
  if (page === 'agent-profile' && opts.agentName) {
    bcSep.style.display   = 'flex';
    bcChild.style.display = 'inline';
    bcChild.textContent   = opts.agentName;
    document.getElementById('bcRoot').textContent = 'Agents';
  } else {
    bcSep.style.display   = 'none';
    bcChild.style.display = 'none';
  }

  // Apply filter if provided
  if (opts.filter && page === 'agents') {
    setAgentFilter(opts.filter);
  }

  // Highlight specific row if provided
  if (opts.highlightSub && page === 'subscriptions') {
    setTimeout(() => highlightRow('subsBody', opts.highlightSub), 50);
  }
  if (opts.highlightReport && page === 'reports') {
    setTimeout(() => highlightRow('reportsBody', opts.highlightReport), 50);
  }

  // Scroll top
  document.getElementById('pagesContainer').scrollTo({ top:0, behavior:'smooth' });

  // Update monitoring live data when visiting that page
  if (page === 'monitoring') updateMonitoring();
}

function handleActivityClick(item) {
  const page       = item.dataset.page;
  const agentId    = item.dataset.agentId;
  const subId      = item.dataset.subId;
  const reportId   = item.dataset.reportId;

  if (page === 'agents' && agentId) {
    openAgentProfile(agentId, 'dashboard');
  } else if (page === 'subscriptions' && subId) {
    navigateTo('subscriptions', { highlightSub: subId });
  } else if (page === 'reports' && reportId) {
    navigateTo('reports', { highlightReport: reportId });
  } else {
    navigateTo(page);
  }
}

function highlightRow(tbodyId, id) {
  const tbody = document.getElementById(tbodyId);
  if (!tbody) return;
  const row = tbody.querySelector(`[data-highlight-id="${id}"]`);
  if (row) {
    row.style.background = 'rgba(92,110,248,.15)';
    row.scrollIntoView({ behavior:'smooth', block:'center' });
    setTimeout(() => { row.style.background = ''; }, 2000);
  }
}

// ── SIDEBAR ──────────────────────────
function setupSidebar() {
  const sidebar = document.getElementById('sidebar');
  const main    = document.getElementById('main');

  function toggleSidebar() {
    sidebar.classList.toggle('collapsed');
    main.classList.toggle('expanded');
  }

  document.getElementById('collapseBtn').addEventListener('click', toggleSidebar);
  document.getElementById('menuBtn').addEventListener('click', toggleSidebar);
}

// ── AGENT PROFILE ─────────────────────
function openAgentProfile(agentId, from = 'agents') {
  const agent = agents.find(a => a.id === agentId);
  if (!agent) return;

  previousPage = from;
  const initials = agent.name.split(' ').map(w => w[0]).join('').slice(0,2).toUpperCase();

  const agentSubs = subscriptions.filter(s => s.agentId === agentId);
  const agentReports = reports.filter(r => r.agentId === agentId);

  const subsHtml = agentSubs.length
    ? agentSubs.map(s => `
        <div class="info-row">
          <span class="label">${s.plan} Plan</span>
          <span class="value"><span class="status ${s.status}">${capitalize(s.status)}</span></span>
        </div>`).join('')
    : `<p style="color:var(--muted);font-size:.83rem">No subscription requests</p>`;

  const reportsHtml = agentReports.length
    ? agentReports.map(r => `
        <div class="info-row">
          <span class="label">${r.reason}</span>
          <span class="value" style="color:var(--muted);font-size:.78rem">${formatDate(r.date)}</span>
        </div>`).join('')
    : `<p style="color:var(--muted);font-size:.83rem">No reports against this agent</p>`;

  const actionBtns = getAgentActionBtns(agent, 'profile');

  document.getElementById('agentProfileContent').innerHTML = `
    <div class="profile-hero">
      <div class="profile-avatar-lg">${initials}</div>
      <div class="profile-info">
        <h2>${agent.name}</h2>
        <div class="profile-meta">
          <span><i data-lucide="mail"></i>${agent.email}</span>
          <span><i data-lucide="phone"></i>${agent.phone}</span>
          <span><i data-lucide="map-pin"></i>${agent.wilaya}</span>
        </div>
        <div style="display:flex;align-items:center;gap:12px">
          <span class="status ${agent.status}">${capitalize(agent.status)}</span>
          <span style="font-size:.8rem;color:var(--muted)">${agent.id}</span>
        </div>
      </div>
      <div class="profile-actions">${actionBtns}</div>
    </div>

    <div class="profile-grid">
      <div class="profile-card">
        <div class="profile-card-head"><i data-lucide="info"></i> Agent Info</div>
        <div class="profile-card-body">
          <div class="info-row"><span class="label">Full Name</span><span class="value">${agent.name}</span></div>
          <div class="info-row"><span class="label">Agent ID</span><span class="value" style="font-family:monospace;color:var(--accent)">${agent.id}</span></div>
          <div class="info-row"><span class="label">Email</span><span class="value">${agent.email}</span></div>
          <div class="info-row"><span class="label">Phone</span><span class="value">${agent.phone}</span></div>
          <div class="info-row"><span class="label">Wilaya</span><span class="value">${agent.wilaya}</span></div>
          <div class="info-row"><span class="label">Joined</span><span class="value">${formatDate(agent.joined)}</span></div>
        </div>
      </div>

      <div class="profile-card">
        <div class="profile-card-head"><i data-lucide="bar-chart-2"></i> Activity</div>
        <div class="profile-card-body">
          <div class="info-row"><span class="label">Active Plan</span><span class="value">${agent.plan}</span></div>
          <div class="info-row"><span class="label">Listings</span><span class="value">${agent.listings}</span></div>
          <div class="info-row">
            <span class="label">Rating</span>
            <span class="value" style="color:var(--amber)">
              ${agent.rating > 0 ? '★ ' + agent.rating : '—'}
            </span>
          </div>
          <div class="info-row"><span class="label">Status</span><span class="value"><span class="status ${agent.status}">${capitalize(agent.status)}</span></span></div>
          <div class="info-row"><span class="label">Reports</span><span class="value" style="color:${agentReports.length>0?'var(--red)':'var(--green)'}">${agentReports.length}</span></div>
        </div>
      </div>

      <div class="profile-card">
        <div class="profile-card-head"><i data-lucide="credit-card"></i> Subscriptions</div>
        <div class="profile-card-body">${subsHtml}</div>
      </div>

      <div class="profile-card">
        <div class="profile-card-head"><i data-lucide="flag"></i> Reports</div>
        <div class="profile-card-body">${reportsHtml}</div>
      </div>
    </div>
  `;

  navigateTo('agent-profile', { agentName: agent.name });
  lucide.createIcons();

  // Wire profile action buttons
  document.querySelectorAll('[data-profile-action]').forEach(btn => {
    btn.addEventListener('click', () => {
      handleAgentAction(btn.dataset.profileAction, agent.id, true);
    });
  });
}

function getAgentActionBtns(agent, ctx = 'table') {
  const attr = ctx === 'profile' ? 'data-profile-action' : 'data-action';
  let btns = '';
  if (agent.status === 'pending') {
    btns += `<button class="tbl-btn approve" ${attr}="approve" data-id="${agent.id}"><i data-lucide="check"></i> Approve</button>`;
    btns += `<button class="tbl-btn reject"  ${attr}="reject"  data-id="${agent.id}"><i data-lucide="x"></i> Reject</button>`;
  }
  if (agent.status === 'active') {
    btns += `<button class="tbl-btn block" ${attr}="block" data-id="${agent.id}"><i data-lucide="ban"></i> Block</button>`;
  }
  if (agent.status === 'blocked') {
    btns += `<button class="tbl-btn unblock" ${attr}="unblock" data-id="${agent.id}"><i data-lucide="unlock"></i> Unblock</button>`;
  }
  return btns;
}

// ── RENDER AGENTS ────────────────────
function renderAgents(filter = currentFilter, search = '') {
  const tbody = document.getElementById('agentsBody');
  tbody.innerHTML = '';

  const filtered = agents.filter(a => {
    const matchFilter = filter === 'all' || a.status === filter;
    const matchSearch = a.name.toLowerCase().includes(search.toLowerCase()) ||
                        a.email.toLowerCase().includes(search.toLowerCase()) ||
                        a.id.toLowerCase().includes(search.toLowerCase());
    return matchFilter && matchSearch;
  });

  if (!filtered.length) {
    tbody.innerHTML = `<tr><td colspan="5"><div class="empty-state">
      <i data-lucide="search-x"></i><p>No agents found</p>
    </div></td></tr>`;
    lucide.createIcons(); return;
  }

  filtered.forEach(agent => {
    const initials = agent.name.split(' ').map(w=>w[0]).join('').slice(0,2).toUpperCase();
    const tr = document.createElement('tr');
    tr.dataset.highlightId = agent.id;

    tr.innerHTML = `
      <td>
        <div class="agent-cell">
          <div class="agent-avatar">${initials}</div>
          <div>
            <div style="font-weight:600">${agent.name}</div>
            <div style="font-size:.73rem;color:var(--muted)">${agent.id}</div>
          </div>
        </div>
      </td>
      <td style="color:var(--muted)">${agent.email}</td>
      <td style="color:var(--muted)">${formatDate(agent.joined)}</td>
      <td><span class="status ${agent.status}">${capitalize(agent.status)}</span></td>
      <td>
        <div class="tbl-actions">
          ${getAgentActionBtns(agent)}
          <button class="view-profile-btn" data-id="${agent.id}" title="View profile">
            <i data-lucide="eye"></i> Profile
          </button>
        </div>
      </td>
    `;
    tbody.appendChild(tr);
  });

  // Action buttons
  tbody.querySelectorAll('[data-action]').forEach(btn => {
    btn.addEventListener('click', e => {
      e.stopPropagation();
      handleAgentAction(btn.dataset.action, btn.dataset.id);
    });
  });

  // Profile buttons
  tbody.querySelectorAll('.view-profile-btn').forEach(btn => {
    btn.addEventListener('click', e => {
      e.stopPropagation();
      openAgentProfile(btn.dataset.id, 'agents');
    });
  });

  // Row click → profile
  tbody.querySelectorAll('tr').forEach(row => {
    row.style.cursor = 'pointer';
    row.addEventListener('click', e => {
      if (e.target.closest('button')) return;
      const id = row.querySelector('.view-profile-btn')?.dataset.id;
      if (id) openAgentProfile(id, 'agents');
    });
  });

  lucide.createIcons();
}

function handleAgentAction(action, id, fromProfile = false) {
  const agent = agents.find(a => a.id === id);
  if (!agent) return;

  if (action === 'approve') agent.status = 'active';
  if (action === 'reject')  agent.status = 'blocked';
  if (action === 'block')   agent.status = 'blocked';
  if (action === 'unblock') agent.status = 'active';

  const msgs  = { approve:`✔ Agent ${id} approved`, reject:`✖ Agent ${id} rejected`, block:`🚫 Agent ${id} blocked`, unblock:`🔓 Agent ${id} unblocked` };
  const types = { approve:'success', reject:'error', block:'error', unblock:'success' };
  showToast(msgs[action], types[action]);

  renderAgents(currentFilter, document.getElementById('agentSearch').value);
  updateAllStats();

  if (fromProfile) openAgentProfile(id, previousPage);
}

function setAgentFilter(filter) {
  currentFilter = filter;
  document.querySelectorAll('.filter-tab').forEach(t => {
    t.classList.toggle('active', t.dataset.filter === filter);
  });
  renderAgents(filter, document.getElementById('agentSearch').value);
}

// ── RENDER SUBSCRIPTIONS ─────────────
function renderSubscriptions() {
  const tbody = document.getElementById('subsBody');
  tbody.innerHTML = '';

  if (!subscriptions.length) {
    tbody.innerHTML = `<tr><td colspan="6"><div class="empty-state"><i data-lucide="inbox"></i><p>No subscriptions</p></div></td></tr>`;
    lucide.createIcons(); return;
  }

  subscriptions.forEach(sub => {
    const initials = sub.agentName.split(' ').map(w=>w[0]).join('').slice(0,2).toUpperCase();
    const tr = document.createElement('tr');
    tr.dataset.highlightId = sub.id;

    tr.innerHTML = `
      <td>
        <div class="agent-cell" style="cursor:pointer" data-open-agent="${sub.agentId}">
          <div class="agent-avatar">${initials}</div>
          <div>
            <div style="font-weight:600">${sub.agentName}</div>
            <div style="font-size:.73rem;color:var(--muted)">${sub.agentId}</div>
          </div>
        </div>
      </td>
      <td><strong>${sub.plan}</strong></td>
      <td>
        <span class="proof-link" data-sub="${sub.id}">
          <i data-lucide="file-image"></i> ${sub.proof}
        </span>
      </td>
      <td style="color:var(--muted)">${formatDate(sub.requested)}</td>
      <td><span class="status ${sub.status}">${capitalize(sub.status)}</span></td>
      <td>
        <div class="tbl-actions">
          ${sub.status === 'pending'
            ? `<button class="tbl-btn approve" data-action="approveSub" data-id="${sub.id}"><i data-lucide="check"></i> Approve</button>
               <button class="tbl-btn reject"  data-action="rejectSub"  data-id="${sub.id}"><i data-lucide="x"></i> Reject</button>`
            : `<span style="color:var(--muted);font-size:.8rem">—</span>`}
        </div>
      </td>
    `;
    tbody.appendChild(tr);
  });

  // Agent name click → profile
  tbody.querySelectorAll('[data-open-agent]').forEach(el => {
    el.addEventListener('click', () => openAgentProfile(el.dataset.openAgent, 'subscriptions'));
  });

  // Proof modal
  tbody.querySelectorAll('.proof-link').forEach(link => {
    link.addEventListener('click', () => {
      activeSubId = link.dataset.sub;
      const sub = subscriptions.find(s => s.id === activeSubId);
      if (sub) document.getElementById('proofFileName').textContent = sub.proof;
      document.getElementById('proofModal').classList.add('open');
    });
  });

  // Action btns
  tbody.querySelectorAll('[data-action]').forEach(btn => {
    btn.addEventListener('click', () => handleSubAction(btn.dataset.action, btn.dataset.id));
  });

  lucide.createIcons();
}

function handleSubAction(action, id) {
  const sub = subscriptions.find(s => s.id === id);
  if (!sub) return;
  if (action === 'approveSub') { sub.status = 'approved'; showToast(`✔ Subscription ${id} approved`, 'success'); }
  if (action === 'rejectSub')  { sub.status = 'rejected'; showToast(`✖ Subscription ${id} rejected`, 'error'); }
  renderSubscriptions();
  updateAllStats();
}

// ── RENDER REPORTS ─────────────────────
function renderReports() {
  const tbody = document.getElementById('reportsBody');
  tbody.innerHTML = '';

  if (!reports.length) {
    tbody.innerHTML = `<tr><td colspan="5"><div class="empty-state"><i data-lucide="check-circle"></i><p>No open reports</p></div></td></tr>`;
    lucide.createIcons(); return;
  }

  reports.forEach(rep => {
    const tr = document.createElement('tr');
    tr.dataset.highlightId = rep.id;

    tr.innerHTML = `
      <td style="color:var(--muted)">${rep.reporter}</td>
      <td>
        <span style="cursor:pointer;color:var(--text);font-weight:600" data-open-agent="${rep.agentId}">
          ${rep.agent}
        </span>
      </td>
      <td>${rep.reason}</td>
      <td style="color:var(--muted)">${formatDate(rep.date)}</td>
      <td>
        <div class="tbl-actions">
          <button class="tbl-btn block"  data-action="blockReport"  data-id="${rep.id}"><i data-lucide="ban"></i> Block Agent</button>
          <button class="tbl-btn ignore" data-action="ignoreReport" data-id="${rep.id}"><i data-lucide="check"></i> Ignore</button>
          <button class="tbl-btn view"   data-action="viewAgent"    data-id="${rep.agentId}"><i data-lucide="eye"></i> Profile</button>
        </div>
      </td>
    `;
    tbody.appendChild(tr);
  });

  tbody.querySelectorAll('[data-open-agent]').forEach(el => {
    el.addEventListener('click', () => openAgentProfile(el.dataset.openAgent, 'reports'));
  });

  tbody.querySelectorAll('[data-action]').forEach(btn => {
    btn.addEventListener('click', () => {
      const { action, id } = btn.dataset;
      if (action === 'viewAgent') { openAgentProfile(id, 'reports'); return; }
      if (action === 'blockReport') {
        const rep = reports.find(r => r.id === id);
        if (rep) {
          const agent = agents.find(a => a.id === rep.agentId);
          if (agent) { agent.status = 'blocked'; renderAgents(); }
        }
        showToast(`🚫 Agent blocked via report ${id}`, 'error');
      } else {
        showToast(`Report ${id} dismissed`, 'info');
      }
      const idx = reports.findIndex(r => r.id === id);
      if (idx !== -1) reports.splice(idx, 1);
      renderReports();
      updateAllStats();
    });
  });

  lucide.createIcons();
}

// ── MODAL ─────────────────────────────
function setupModal() {
  const modal      = document.getElementById('proofModal');
  const closeBtn   = document.getElementById('closeModal');
  const approveBtn = document.getElementById('modalApprove');
  const rejectBtn  = document.getElementById('modalReject');

  closeBtn.addEventListener('click', () => modal.classList.remove('open'));
  modal.addEventListener('click', e => { if (e.target === modal) modal.classList.remove('open'); });
  approveBtn.addEventListener('click', () => {
    if (activeSubId) { handleSubAction('approveSub', activeSubId); modal.classList.remove('open'); }
  });
  rejectBtn.addEventListener('click', () => {
    if (activeSubId) { handleSubAction('rejectSub', activeSubId); modal.classList.remove('open'); }
  });
}

// ── NOTIFICATIONS ────────────────────
function setupNotifications() {
  document.getElementById('markAllRead').addEventListener('click', () => {
    document.querySelectorAll('.notif-item.unread').forEach(n => n.classList.remove('unread'));
    document.getElementById('notifBadge').textContent = '0';
    document.getElementById('notifDot').style.display = 'none';
    showToast('All notifications marked as read', 'info');
  });

  // Clickable notification items
  document.querySelectorAll('.notif-item.clickable').forEach(item => {
    item.addEventListener('click', e => {
      if (e.target.closest('.notif-close')) return;
      const page     = item.dataset.page;
      const agentId  = item.dataset.agentId;
      const subId    = item.dataset.subId;
      const reportId = item.dataset.reportId;

      item.classList.remove('unread');
      updateNotifBadge();

      if (page === 'agents' && agentId) {
        openAgentProfile(agentId, 'notifications');
      } else if (page === 'subscriptions' && subId) {
        navigateTo('subscriptions', { highlightSub: subId });
      } else if (page === 'reports' && reportId) {
        navigateTo('reports', { highlightReport: reportId });
      } else {
        navigateTo(page);
      }
    });
  });

  // Dismiss buttons
  document.querySelectorAll('.notif-close').forEach(btn => {
    btn.addEventListener('click', e => {
      e.stopPropagation();
      const item = btn.closest('.notif-item');
      item.style.animation = 'slideOut .2s ease forwards';
      setTimeout(() => { item.remove(); updateNotifBadge(); }, 200);
    });
  });
}

function updateNotifBadge() {
  const unread = document.querySelectorAll('.notif-item.unread').length;
  document.getElementById('notifBadge').textContent = unread;
  document.getElementById('notifDot').style.display = unread > 0 ? 'block' : 'none';
}

// ── SEARCH (AGENTS PAGE) ──────────────
function setupSearch() {
  document.getElementById('agentSearch').addEventListener('input', e => {
    renderAgents(currentFilter, e.target.value);
  });
}

// ── FILTERS ──────────────────────────
function setupFilters() {
  document.querySelectorAll('.filter-tab').forEach(tab => {
    tab.addEventListener('click', () => setAgentFilter(tab.dataset.filter));
  });
}

// ── GLOBAL SEARCH ────────────────────
function setupGlobalSearch() {
  const btn     = document.getElementById('topSearchBtn');
  const overlay = document.getElementById('searchOverlay');
  const input   = document.getElementById('globalSearch');
  const results = document.getElementById('searchResults');

  btn.addEventListener('click', () => {
    overlay.classList.add('open');
    setTimeout(() => input.focus(), 50);
  });

  overlay.addEventListener('click', e => {
    if (e.target === overlay) overlay.classList.remove('open');
  });

  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') overlay.classList.remove('open');
  });

  input.addEventListener('input', () => {
    const q = input.value.trim().toLowerCase();
    if (!q) { results.innerHTML = ''; return; }
    doGlobalSearch(q, results);
  });
}

function doGlobalSearch(q, container) {
  const hits = [];

  agents.forEach(a => {
    if (a.name.toLowerCase().includes(q) || a.email.toLowerCase().includes(q) || a.id.toLowerCase().includes(q)) {
      hits.push({
        type:'Agent', icon:'users', color:'blue',
        title: a.name, sub: `${a.id} · ${capitalize(a.status)}`,
        action: () => { document.getElementById('searchOverlay').classList.remove('open'); openAgentProfile(a.id, 'agents'); }
      });
    }
  });

  subscriptions.forEach(s => {
    if (s.agentName.toLowerCase().includes(q) || s.id.toLowerCase().includes(q) || s.plan.toLowerCase().includes(q)) {
      hits.push({
        type:'Subscription', icon:'credit-card', color:'green',
        title: `${s.agentName} — ${s.plan}`, sub: `${s.id} · ${capitalize(s.status)}`,
        action: () => { document.getElementById('searchOverlay').classList.remove('open'); navigateTo('subscriptions', { highlightSub: s.id }); }
      });
    }
  });

  reports.forEach(r => {
    if (r.agent.toLowerCase().includes(q) || r.reason.toLowerCase().includes(q) || r.id.toLowerCase().includes(q)) {
      hits.push({
        type:'Report', icon:'flag', color:'red',
        title: r.reason, sub: `${r.id} · ${r.agent}`,
        action: () => { document.getElementById('searchOverlay').classList.remove('open'); navigateTo('reports', { highlightReport: r.id }); }
      });
    }
  });

  if (!hits.length) {
    container.innerHTML = `<div class="search-empty">No results for "${q}"</div>`;
    return;
  }

  container.innerHTML = '';
  hits.forEach(hit => {
    const el = document.createElement('div');
    el.className = 'search-result-item';
    el.innerHTML = `
      <div class="sr-icon ${hit.color}"><i data-lucide="${hit.icon}"></i></div>
      <div class="sr-body">
        <strong>${hit.title}</strong>
        <span>${hit.sub}</span>
      </div>
      <span class="sr-type">${hit.type}</span>
    `;
    el.addEventListener('click', hit.action);
    container.appendChild(el);
  });
  lucide.createIcons();
}

// ── STATS ─────────────────────────────
function updateAllStats() {
  const total   = agents.length;
  const pending = agents.filter(a => a.status === 'pending').length;
  const active  = agents.filter(a => a.status === 'active').length;
  const blocked = agents.filter(a => a.status === 'blocked').length;
  const activeSubs = subscriptions.filter(s => s.status === 'approved').length;

  document.getElementById('statAgents').textContent  = total;
  document.getElementById('statPending').textContent = pending;
  document.getElementById('statSubs').textContent    = activeSubs;
  document.getElementById('statReports').textContent = reports.length;

  document.getElementById('reportBadge').textContent = reports.length;
  document.getElementById('notifBadge').textContent  = document.querySelectorAll('.notif-item.unread').length;

  // Monitoring
  document.getElementById('monTotal').textContent   = total;
  document.getElementById('monActive').textContent  = active;
  document.getElementById('monPending').textContent = pending;
  document.getElementById('monBlocked').textContent = blocked;
}

function updateMonitoring() {
  updateAllStats();

  const total   = agents.length || 1;
  const active  = agents.filter(a => a.status === 'active').length;
  const pending = agents.filter(a => a.status === 'pending').length;
  const blocked = agents.filter(a => a.status === 'blocked').length;

  // Plan bars
  const basic   = subscriptions.filter(s => s.plan === 'Basic').length;
  const pro     = subscriptions.filter(s => s.plan === 'Pro').length;
  const premium = subscriptions.filter(s => s.plan === 'Premium').length;
  const maxSub  = Math.max(basic, pro, premium, 1);

  document.getElementById('planBars').innerHTML = [
    { label:'Basic',   val:basic,   color:'var(--blue)' },
    { label:'Pro',     val:pro,     color:'var(--green)' },
    { label:'Premium', val:premium, color:'var(--amber)' },
  ].map(p => `
    <div class="plan-bar-row">
      <span>${p.label}</span>
      <div class="bar-wrap"><div class="bar" style="width:${Math.round(p.val/maxSub*100)}%;background:${p.color}"></div></div>
      <span>${p.val}</span>
    </div>
  `).join('');

  // Donut
  const ap = Math.round(active/total*100);
  const pp = Math.round(pending/total*100);
  const bp = 100 - ap - pp;

  const apOffset = 25;
  const ppOffset = -(ap) + 25;
  const bpOffset = -(ap + pp) + 25;

  document.getElementById('donutActive').setAttribute('stroke-dasharray', `${ap} ${100-ap}`);
  document.getElementById('donutActive').setAttribute('stroke-dashoffset', apOffset);
  document.getElementById('donutPending').setAttribute('stroke-dasharray', `${pp} ${100-pp}`);
  document.getElementById('donutPending').setAttribute('stroke-dashoffset', ppOffset);
  document.getElementById('donutBlocked').setAttribute('stroke-dasharray', `${Math.max(bp,0)} ${100-Math.max(bp,0)}`);
  document.getElementById('donutBlocked').setAttribute('stroke-dashoffset', bpOffset);

  document.getElementById('donutLegend').innerHTML = `
    <div><span class="dot-legend" style="background:var(--green)"></span>Active ${ap}%</div>
    <div><span class="dot-legend" style="background:var(--amber)"></span>Pending ${pp}%</div>
    <div><span class="dot-legend" style="background:var(--red)"></span>Blocked ${bp}%</div>
  `;
}

// ── TOAST ─────────────────────────────
function showToast(message, type = 'info') {
  const icons = { success:'check-circle', error:'x-circle', info:'info' };
  const toast = document.createElement('div');
  toast.className = `toast ${type}`;
  toast.innerHTML = `<i data-lucide="${icons[type]}"></i><span>${message}</span>`;
  document.getElementById('toastContainer').appendChild(toast);
  lucide.createIcons();
  setTimeout(() => {
    toast.style.animation = 'slideOut .3s ease forwards';
    setTimeout(() => toast.remove(), 300);
  }, 3000);
}

// ── HELPERS ──────────────────────────
function formatDate(dateStr) {
  return new Date(dateStr).toLocaleDateString('en-GB', { day:'2-digit', month:'short', year:'numeric' });
}
function capitalize(str) {
  return str.charAt(0).toUpperCase() + str.slice(1);
}