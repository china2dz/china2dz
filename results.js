(function () {
  'use strict';
  /* ── STATE ── */
  var S = {
    query:    '',
    brands:   [],
    bodyType: 'all',
    yearMin:  null, yearMax:  null,
    mileage:  null,
    fuels:    [],
    trans:    'all',
    drive:    'all',
    state:    'all',
    colorExt: 'all',
    colorInt: 'all',
    priceMin: null, priceMax: null
  };
  var sortMode    = 'default';
  var listView    = false;
  var alertFreq   = 'instant';
  function isLoggedIn() {
  return localStorage.getItem('c2dz_user') !== null;
}

  /* ── HELPERS ── */
  function $(id)    { return document.getElementById(id); }
  function cap(s)   { return s ? s.charAt(0).toUpperCase() + s.slice(1) : ''; }
  function toast(m) {
    var t = $('toast');
    if (!t) return;
    t.textContent = m;
    t.classList.add('show');
    clearTimeout(t._timer);
    t._timer = setTimeout(function () { t.classList.remove('show'); }, 2800);
  }

  /* ── DRAWER OPEN / CLOSE ── */
  function openDrawer() {
    $('filterDrawer').classList.add('open');
    $('drawerOverlay').classList.add('open');
    document.body.style.overflow = 'hidden';
    refreshViewCount();
  }
  function closeDrawer() {
    $('filterDrawer').classList.remove('open');
    $('drawerOverlay').classList.remove('open');
    if (!document.querySelector('.modal-bg.open')) document.body.style.overflow = '';
  }

  /* ── MODAL OPEN / CLOSE ── */
  window.openModal = function (id) {
    var el = $(id);
    if (!el) return;
    el.classList.add('open');
    document.body.style.overflow = 'hidden';
    el.addEventListener('click', function onBg(e) {
      if (e.target === el) { window.closeModal(id); el.removeEventListener('click', onBg); }
    });
  };
  window.closeModal = function (id) {
    var el = $(id);
    if (el) el.classList.remove('open');
    if (!document.querySelector('.modal-bg.open')) document.body.style.overflow = '';
  };

  /* ── PILL GROUP ── */
  function bindPillGroup(groupId, stateKey) {
    var group = $(groupId);
    if (!group) return;
    group.querySelectorAll('.df-pill').forEach(function (p) {
      p.addEventListener('click', function () {
        group.querySelectorAll('.df-pill').forEach(function (x) { x.classList.remove('active'); });
        p.classList.add('active');
        S[stateKey] = p.dataset.val;
        refreshViewCount();
      });
    });
  }

  function resetPillGroup(groupId, def) {
    var group = $(groupId);
    if (!group) return;
    group.querySelectorAll('.df-pill').forEach(function (p) {
      p.classList.toggle('active', p.dataset.val === def);
    });
  }

  /* ── COLOR GROUP ── */
  function bindColorGroup(groupId, stateKey) {
    var group = $(groupId);
    if (!group) return;
    group.querySelectorAll('.df-color').forEach(function (b) {
      b.addEventListener('click', function () {
        group.querySelectorAll('.df-color').forEach(function (x) { x.classList.remove('active'); });
        b.classList.add('active');
        S[stateKey] = b.dataset.val;
        refreshViewCount();
      });
    });
    var all = group.querySelector('[data-val="all"]');
    if (all) all.classList.add('active');
  }

  function resetColorGroup(groupId) {
    var group = $(groupId);
    if (!group) return;
    group.querySelectorAll('.df-color').forEach(function (b) {
      b.classList.toggle('active', b.dataset.val === 'all');
    });
  }

  /* ── CHECK GROUP ── */
  function bindCheckGroup(groupId, stateKey) {
    var group = $(groupId);
    if (!group) return;
    group.querySelectorAll('input[type="checkbox"]').forEach(function (cb) {
      cb.addEventListener('change', function () {
        S[stateKey] = [];
        group.querySelectorAll('input:checked').forEach(function (c) {
          S[stateKey].push(c.value);
        });
        refreshViewCount();
      });
    });
  }

  function resetCheckGroup(groupId) {
    var group = $(groupId);
    if (!group) return;
    group.querySelectorAll('input[type="checkbox"]').forEach(function (cb) { cb.checked = false; });
  }

  /* ── BIND ALL FILTER CONTROLS ── */
  function bindFilters() {
    bindCheckGroup('brandGroup', 'brands');
    bindPillGroup('bodyGroup',   'bodyType');
    bindPillGroup('transGroup',  'trans');
    bindPillGroup('driveGroup',  'drive');
    bindPillGroup('stateGroup',  'state');
    bindCheckGroup('fuelGroup',  'fuels');
    bindColorGroup('colorExtGroup', 'colorExt');
    bindColorGroup('colorIntGroup', 'colorInt');

    // Year
    var yn = $('yearMin'), yx = $('yearMax');
    if (yn) yn.addEventListener('input', function () { S.yearMin = this.value ? +this.value : null; refreshViewCount(); });
    if (yx) yx.addEventListener('input', function () { S.yearMax = this.value ? +this.value : null; refreshViewCount(); });

    // Mileage
    var ml = $('mileageSel');
    if (ml) ml.addEventListener('change', function () { S.mileage = this.value ? +this.value : null; refreshViewCount(); });

    // Price inputs
    var pn = $('priceMin'), px = $('priceMax');
    if (pn) pn.addEventListener('input', function () { S.priceMin = this.value ? +this.value : null; clearPresets(); refreshViewCount(); });
    if (px) px.addEventListener('input', function () { S.priceMax = this.value ? +this.value : null; clearPresets(); refreshViewCount(); });

    // Price presets
    document.querySelectorAll('.df-preset').forEach(function (p) {
      p.addEventListener('click', function () {
        document.querySelectorAll('.df-preset').forEach(function (x) { x.classList.remove('active'); });
        p.classList.add('active');
        S.priceMin = +p.dataset.min;
        S.priceMax = +p.dataset.max;
        var pnEl = $('priceMin'), pxEl = $('priceMax');
        if (pnEl) pnEl.value = S.priceMin || '';
        if (pxEl) pxEl.value = S.priceMax >= 99000000 ? '' : S.priceMax;
        refreshViewCount();
      });
    });
  }

  function clearPresets() {
    document.querySelectorAll('.df-preset').forEach(function (p) { p.classList.remove('active'); });
  }

  /* ── MATCHING LOGIC ── */
  function cardMatches(card) {
    var brand = (card.dataset.brand || '').toLowerCase();
    var type  = (card.dataset.type  || '').toLowerCase();
    var price = +(card.dataset.price  || 0);
    var year  = +(card.dataset.year   || 0);
    var mile  = +(card.dataset.mileage|| 0);
    var fuel  = (card.dataset.fuel    || '').toLowerCase();
    var trans = (card.dataset.transmission || '').toLowerCase();
    var drive = (card.dataset.drive   || '').toLowerCase();
    var state = (card.dataset.state   || '').toLowerCase();
    var cExt  = (card.dataset.colorExt|| '').toLowerCase();
    var cInt  = (card.dataset.colorInt|| '').toLowerCase();
    var title = (card.dataset.title || '').toLowerCase();
var h3text = ((card.querySelector('h3') || {}).textContent || '').toLowerCase();
var hay = brand + ' ' + title + ' ' + h3text + ' ' + type + ' ' + fuel + ' ' + (card.dataset.wilaya || '').toLowerCase();

    if (S.query && hay.indexOf(S.query) === -1) return false;
    if (S.brands.length && S.brands.indexOf(brand) === -1) return false;
    if (S.bodyType !== 'all' && type !== S.bodyType) return false;
    if (S.yearMin !== null && year < S.yearMin) return false;
    if (S.yearMax !== null && year > S.yearMax) return false;
    if (S.mileage !== null && mile > S.mileage) return false;
    if (S.fuels.length && S.fuels.indexOf(fuel) === -1) return false;
    if (S.trans !== 'all' && trans !== S.trans) return false;
    if (S.drive !== 'all' && drive !== S.drive) return false;
    if (S.state !== 'all' && state !== S.state) return false;
    if (S.colorExt !== 'all' && cExt !== S.colorExt) return false;
    if (S.colorInt !== 'all' && cInt !== S.colorInt) return false;
    if (S.priceMin !== null && price < S.priceMin) return false;
    if (S.priceMax !== null && price > S.priceMax) return false;
    return true;
  }

  function getCards() { return Array.from(document.querySelectorAll('.car-card')); }

  /* ── APPLY FILTERS & SORT ── */
  window.applyFilters = function applyFilters() {
    var cards   = getCards();
    var visible = [], hidden = [];

    cards.forEach(function (c) {
      if (cardMatches(c)) visible.push(c);
      else hidden.push(c);
    });

    // Sort
    if (sortMode === 'price-asc')  visible.sort(function (a,b) { return +(a.dataset.price||0) - +(b.dataset.price||0); });
    if (sortMode === 'price-desc') visible.sort(function (a,b) { return +(b.dataset.price||0) - +(a.dataset.price||0); });
    if (sortMode === 'year-desc')  visible.sort(function (a,b) { return +(b.dataset.year||0) - +(a.dataset.year||0); });
    if (sortMode === 'year-asc')   visible.sort(function (a,b) { return +(a.dataset.year||0) - +(b.dataset.year||0); });

    var grid = $('carsGrid');
    visible.forEach(function (c) { c.style.display = ''; grid.appendChild(c); });
    hidden.forEach(function (c)  { c.style.display = 'none'; });

    var n = visible.length;
    var countEl = $('resultsCount');
    if (countEl) countEl.textContent = n + ' car' + (n !== 1 ? 's' : '') + ' found';

    var noRes = $('noResults');
    if (noRes) noRes.style.display = n === 0 ? 'block' : 'none';

    renderTags();
    renderBadge();
  }

  /* ── LIVE COUNT (in drawer button) ── */
  window.refreshViewCount = function refreshViewCount() {
    var n = getCards().filter(cardMatches).length;
    var el = $('drawerCount');
    if (el) el.textContent = n;
  }

  /* ── ACTIVE TAG PILLS ── */
  function renderTags() {
    var wrap = $('activeTags');
    if (!wrap) return;
    var tags = [];

    if (S.query)          tags.push({ l: '"' + S.query + '"',           k: 'query' });
    S.brands.forEach(function (b)  { tags.push({ l: cap(b),              k: 'brand:' + b  }); });
    S.fuels.forEach(function  (f)  { tags.push({ l: cap(f),              k: 'fuel:'  + f  }); });
    if (S.bodyType !== 'all')       tags.push({ l: cap(S.bodyType),       k: 'bodyType'     });
    if (S.trans    !== 'all')       tags.push({ l: S.trans.toUpperCase(), k: 'trans'        });
    if (S.drive    !== 'all')       tags.push({ l: S.drive.toUpperCase(), k: 'drive'        });
    if (S.state    !== 'all')       tags.push({ l: cap(S.state),          k: 'state'        });
    if (S.colorExt !== 'all')       tags.push({ l: cap(S.colorExt) + ' ext.', k: 'colorExt'});
    if (S.colorInt !== 'all')       tags.push({ l: cap(S.colorInt) + ' int.', k: 'colorInt'});
    if (S.mileage  !== null)        tags.push({ l: 'Max ' + S.mileage.toLocaleString() + ' km', k: 'mileage' });
    if (S.yearMin  !== null || S.yearMax  !== null) tags.push({ l: (S.yearMin||'…') + '–' + (S.yearMax||'…'), k: 'year' });
    if (S.priceMin !== null || S.priceMax !== null) {
      var lo = S.priceMin ? (S.priceMin/1e6).toFixed(1)+'M' : '0';
      var hi = (S.priceMax && S.priceMax < 99e6) ? (S.priceMax/1e6).toFixed(1)+'M' : '∞';
      tags.push({ l: lo + '–' + hi + ' DZD', k: 'price' });
    }

    wrap.innerHTML = tags.map(function (t) {
      return '<span class="atag">' + t.l + '<button onclick="removeTag(\'' + t.k + '\')">×</button></span>';
    }).join('');
  }

  window.removeTag = function (key) {
    if (key === 'query')    { S.query = ''; var inp = $('searchInput'); if (inp) inp.value = ''; }
    else if (key === 'bodyType') { S.bodyType = 'all'; resetPillGroup('bodyGroup', 'all'); }
    else if (key === 'trans')    { S.trans    = 'all'; resetPillGroup('transGroup', 'all'); }
    else if (key === 'drive')    { S.drive    = 'all'; resetPillGroup('driveGroup', 'all'); }
    else if (key === 'state')    { S.state    = 'all'; resetPillGroup('stateGroup', 'all'); }
    else if (key === 'colorExt') { S.colorExt = 'all'; resetColorGroup('colorExtGroup'); }
    else if (key === 'colorInt') { S.colorInt = 'all'; resetColorGroup('colorIntGroup'); }
    else if (key === 'mileage')  { S.mileage = null; var mEl = $('mileageSel'); if (mEl) mEl.value = ''; }
    else if (key === 'year')  { S.yearMin = S.yearMax = null; ['yearMin','yearMax'].forEach(function(id){var e=$(id);if(e)e.value='';}); }
    else if (key === 'price') { S.priceMin = S.priceMax = null; ['priceMin','priceMax'].forEach(function(id){var e=$(id);if(e)e.value='';}); clearPresets(); }
    else if (key.startsWith('brand:')) {
      var b = key.slice(6); S.brands = S.brands.filter(function(x){return x!==b;});
      var bg = $('brandGroup'); if (bg) bg.querySelectorAll('input').forEach(function(cb){if(cb.value===b)cb.checked=false;});
    }
    else if (key.startsWith('fuel:')) {
      var f = key.slice(5); S.fuels = S.fuels.filter(function(x){return x!==f;});
      var fg = $('fuelGroup'); if (fg) fg.querySelectorAll('input').forEach(function(cb){if(cb.value===f)cb.checked=false;});
    }
    applyFilters();
  };

  /* ── BADGE ── */
  function renderBadge() {
    var n = 0;
    n += S.brands.length;
    n += S.fuels.length;
    if (S.bodyType !== 'all') n++;
    if (S.trans    !== 'all') n++;
    if (S.drive    !== 'all') n++;
    if (S.state    !== 'all') n++;
    if (S.colorExt !== 'all') n++;
    if (S.colorInt !== 'all') n++;
    if (S.mileage  !== null)  n++;
    if (S.yearMin !== null || S.yearMax !== null)   n++;
    if (S.priceMin !== null || S.priceMax !== null) n++;
    var badge = $('filterBadge');
    if (badge) { badge.textContent = n; badge.style.display = n > 0 ? 'inline-flex' : 'none'; }
  }

  /* ── RESET ALL ── */
  window.resetFilters = function () {
    S = { query:'', brands:[], bodyType:'all', yearMin:null, yearMax:null, mileage:null, fuels:[], trans:'all', drive:'all', state:'all', colorExt:'all', colorInt:'all', priceMin:null, priceMax:null };
    resetCheckGroup('brandGroup');
    resetCheckGroup('fuelGroup');
    ['bodyGroup','transGroup','driveGroup','stateGroup'].forEach(function(id){resetPillGroup(id,'all');});
    resetColorGroup('colorExtGroup');
    resetColorGroup('colorIntGroup');
    ['yearMin','yearMax','priceMin','priceMax'].forEach(function(id){var e=$(id);if(e)e.value='';});
    var ms=$('mileageSel');if(ms)ms.value='';
    clearPresets();
    var si=$('searchInput');if(si)si.value='';
    applyFilters();
    refreshViewCount();

  };

  /* ── SAVE / WISHLIST ── */
  window.toggleSave = function (btn) {
    btn.classList.toggle('saved');
    toast(btn.classList.contains('saved') ? '❤ Added to Wishlist' : 'Removed from Wishlist');
  };

  /* ── VIEW TOGGLE ── */
  function bindViewToggle() {
    var gBtn = $('gridBtn'), lBtn = $('listBtn'), grid = $('carsGrid');
    if (gBtn) gBtn.addEventListener('click', function () {
      listView = false;
      if (grid) grid.classList.remove('list-view');
      gBtn.classList.add('active');
      if (lBtn) lBtn.classList.remove('active');
    });
    if (lBtn) lBtn.addEventListener('click', function () {
      listView = true;
      if (grid) grid.classList.add('list-view');
      lBtn.classList.add('active');
      if (gBtn) gBtn.classList.remove('active');
    });
  }

  /* ── SORT ── */
  function bindSort() {
    var sel = $('sortSelect');
    if (sel) sel.addEventListener('change', function () { sortMode = this.value; applyFilters(); });
  }

  /* ── SEARCH BAR ── */
  function bindSearch() {
    var inp = $('searchInput'), btn = $('searchBtn');
    function doSearch() { S.query = ($('searchInput').value || '').trim().toLowerCase(); applyFilters(); }
    if (inp) inp.addEventListener('keydown', function (e) { if (e.key === 'Enter') doSearch(); });
    if (btn) btn.addEventListener('click', doSearch);
  }

  /* ── URL PARAMS ── */
  function parseURL() {
    var p = new URLSearchParams(window.location.search);
    var q = p.get('q') || '';
    if (q) {
        S.query = q.toLowerCase();
        var si = $('searchInput');
        if (si) si.value = q;
    }
    // الفلاتر الجديدة من index
    if (p.get('brand'))        { S.brands = [p.get('brand').toLowerCase()]; }
    if (p.get('year'))         { S.yearMin = +p.get('year'); S.yearMax = +p.get('year'); }
    if (p.get('fuel'))         { S.fuels = [p.get('fuel').toLowerCase()]; }
    if (p.get('bodyType'))     { S.bodyType = p.get('bodyType').toLowerCase(); }
    if (p.get('transmission')) { S.trans = p.get('transmission').toLowerCase(); }
    if (p.get('drive'))        { S.drive = p.get('drive').toLowerCase(); }
    if (p.get('priceMin'))     { S.priceMin = +p.get('priceMin'); }
    if (p.get('priceMax'))     { S.priceMax = +p.get('priceMax'); }
    if (p.get('wilaya'))       { S.query = (S.query + ' ' + p.get('wilaya').toLowerCase()).trim(); }
    if (p.get('colorExt'))     { S.colorExt = p.get('colorExt').toLowerCase(); }
    if (p.get('colorInt'))     { S.colorInt = p.get('colorInt').toLowerCase(); }
    if (p.get('kmRange')) {
        var km = +p.get('kmRange');
        if (km === 0) S.mileage = 0;
        else S.mileage = km;
    }
}

  /* ── SWITCH TAB (contact modal) ── */
  window.switchTab = function (tabId, btn) {
    document.querySelectorAll('.ctab').forEach(function (t) { t.classList.remove('active'); });
    document.querySelectorAll('.tab-pane').forEach(function (p) { p.classList.remove('active'); });
    if (btn) btn.classList.add('active');
    var p = $('tab-' + tabId);
    if (p) p.classList.add('active');
  };

  /* ── QUICK MSG ── */
  window.setMsg = function (el) {
    var m = $('cMsg');
    if (m) m.value = el.textContent;
  };

  /* ── SEND MESSAGE ── */
  window.sendMsg = function () {
    var name = $('cName') ? $('cName').value.trim() : '';
    var msg  = $('cMsg')  ? $('cMsg').value.trim()  : '';
    if (!name || !msg) { toast('Please fill in your name and message.'); return; }
    toast('✓ Message sent! The dealer will contact you shortly.');
    window.closeModal('contactModal');
    ['cName','cPhone','cMsg'].forEach(function(id){var e=$(id);if(e)e.value='';});
  };

  /* ── OPEN CONTACT ── */
  window.openContact = function (btn) {
    var card = btn.closest('.car-card');
    var name  = ((card.querySelector('.card-name') || {}).textContent || '').trim();
    var brand = cap(card.dataset.brand || '');
    var wilaya = cap(card.dataset.wilaya || 'Algeria');
    var el_n = $('contactCarName'), el_d = $('contactDealerLoc');
    if (el_n) el_n.textContent = brand + ' ' + name;
    if (el_d) el_d.textContent = 'Verified Dealer · ' + wilaya;
    var msgEl = $('cMsg');
    if (msgEl && !msgEl.value) msgEl.value = 'Hello, I\'m interested in the ' + brand + ' ' + name + '. Is it still available?';
    window.switchTab('message', document.querySelector('.ctab'));
    window.openModal('contactModal');
  };

  /* ── OPEN DETAILS ── */
  window.openDetails = function (btn) {
    var card  = btn.closest('.car-card');
    var brand = cap(card.dataset.brand || '');
    var type  = cap(card.dataset.type  || '');
    var name  = ((card.querySelector('.card-name') || {}).textContent || '').trim();
    var price = (+card.dataset.price || 0).toLocaleString();
    var year  = card.dataset.year  || '—';
    var fuel  = cap(card.dataset.fuel  || '—');
    var mile  = (+card.dataset.mileage || 0).toLocaleString();
    var trans = cap(card.dataset.transmission || '—');
    var drive = (card.dataset.drive || '—').toUpperCase();
    var state = cap(card.dataset.state || '—');
    var cExt  = cap(card.dataset.colorExt || '—');
    var cInt  = cap(card.dataset.colorInt || '—');
    var wilaya = cap(card.dataset.wilaya || '—');
    var imgSrc = (card.querySelector('.card-img-wrap img') || {}).src || '';

    var specs = [
      {k:'Brand', v:brand}, {k:'Model', v:name}, {k:'Year', v:year},
      {k:'Body Type', v:type}, {k:'Mileage', v:mile+' km'}, {k:'Fuel', v:fuel},
      {k:'Transmission', v:trans}, {k:'Drive', v:drive}, {k:'State', v:state},
      {k:'Ext. Color', v:cExt}, {k:'Int. Color', v:cInt}, {k:'Wilaya', v:wilaya},
      {k:'Warranty', v:'3 Years'}, {k:'Status', v:'Available'}
    ];

    var features = ['Air Conditioning','Leather Seats','GPS Navigation','Rear Camera',
      'Blind Spot Monitor','Lane Assist','Panoramic Roof','Apple CarPlay',
      'Android Auto','Keyless Entry','Heated Seats','Cruise Control'];

    var reviews = [
      {n:'Mohamed A.', s:'★★★★★', t:'Excellent car, very reliable. The dealer was professional and the delivery was smooth.', img:'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=80&q=80'},
      {n:'Fatima Z.',  s:'★★★★★', t:'Superb quality for the price. Highly recommended for anyone looking for a Chinese car in Algeria.', img:'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=80&q=80'}
    ];

    var content = $('detailsContent');
    if (!content) return;

    content.innerHTML =
      '<div class="dm-hero">' +
        '<img src="' + imgSrc + '" alt="' + brand + ' ' + name + '" onerror="this.src=\'https://images.unsplash.com/photo-1533473359331-0135ef1b58bf?w=800\'">' +
        '<div class="dm-hero-overlay"></div>' +
        '<div class="dm-hero-info">' +
          '<div class="dm-hero-brand">' + brand + '</div>' +
          '<h2 class="dm-hero-name">' + name + '</h2>' +
          '<div class="dm-hero-price">' + price + ' DZD</div>' +
        '</div>' +
      '</div>' +
      '<div class="dm-body">' +
        '<div class="dm-sec-title">Specifications</div>' +
        '<div class="dm-specs-grid">' +
          specs.map(function(s){return '<div class="dm-spec"><div class="dm-spec-k">'+s.k+'</div><div class="dm-spec-v">'+s.v+'</div></div>';}).join('') +
        '</div>' +
        '<div class="dm-sec-title">Standard Features</div>' +
        '<div class="dm-features">' +
          features.map(function(f){return '<div class="dm-feat"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>'+f+'</div>';}).join('') +
        '</div>' +
        '<div class="dm-sec-title">Customer Reviews</div>' +
        '<div>' +
          reviews.map(function(r){return '<div class="dm-review"><div class="dm-rev-head"><img class="dm-rev-avatar" src="'+r.img+'" alt="'+r.n+'"><div><div class="dm-rev-name">'+r.n+'</div><div class="dm-rev-stars">'+r.s+'</div></div></div><div class="dm-rev-text">'+r.t+'</div></div>';}).join('') +
          '<div class="dm-add-review">' +
            '<div class="dm-add-label">Leave a Review</div>' +
            '<div class="star-row"><span class="star-pick" onclick="pickStar(this,1)">★</span><span class="star-pick" onclick="pickStar(this,2)">★</span><span class="star-pick" onclick="pickStar(this,3)">★</span><span class="star-pick" onclick="pickStar(this,4)">★</span><span class="star-pick" onclick="pickStar(this,5)">★</span></div>' +
            '<textarea class="dm-textarea" rows="3" placeholder="Share your experience with this car…"></textarea>' +
            '<button class="dm-submit-review" onclick="submitReview(this)">Post Review</button>' +
          '</div>' +
        '</div>' +
        '<div class="dm-footer-row">' +
          '<button class="dm-cta-btn" onclick="closeModal(\'detailsModal\'); openContactByName(\'' + brand + ' ' + name + '\', \'' + wilaya + '\')">' +
            '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.65 3.18C1.51 2.84 1.7 2.41 2 2.17A2 2 0 0 1 3.36 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L7.91 9.4a16 16 0 0 0 6.29 6.29l.76-.76a2 2 0 0 1 2.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0 1 22 17z"/></svg>' +
            'Contact Dealer' +
          '</button>' +
          '<button class="dm-save-btn2" onclick="toggleSaveFromDetails(this)">' +
            '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>' +
            'Save' +
          '</button>' +
        '</div>' +
      '</div>';

    window.openModal('detailsModal');
  };

  window.pickStar = function (el, n) {
    var row = el.parentElement.querySelectorAll('.star-pick');
    row.forEach(function (s, i) { s.classList.toggle('on', i < n); });
  };

  window.submitReview = function (btn) {
    var wrap = btn.closest('.dm-add-review');
    var txt  = wrap.querySelector('.dm-textarea').value.trim();
    if (!txt) { toast('Please write a comment first.'); return; }
    toast('✓ Review submitted! Thank you.');
    wrap.querySelector('.dm-textarea').value = '';
    wrap.querySelectorAll('.star-pick').forEach(function (s) { s.classList.remove('on'); });
  };

  window.toggleSaveFromDetails = function (btn) {
    btn.classList.toggle('saved-detail');
    var saved = btn.classList.contains('saved-detail');
    btn.style.color = saved ? '#e63946' : '';
    btn.style.borderColor = saved ? 'rgba(230,57,70,0.4)' : '';
    toast(saved ? '❤ Added to Wishlist' : 'Removed from Wishlist');
  };

  window.openContactByName = function (carName, wilaya) {
    var el_n = $('contactCarName'), el_d = $('contactDealerLoc');
    if (el_n) el_n.textContent = carName;
    if (el_d) el_d.textContent = 'Verified Dealer · ' + (wilaya || 'Algeria');
    var msgEl = $('cMsg');
    if (msgEl) msgEl.value = 'Hello, I\'m interested in the ' + carName + '. Is it still available?';
    window.switchTab('message', document.querySelector('.ctab'));
    window.openModal('contactModal');
  };

function openAlertModal() {
  if (!isLoggedIn()) {
    window.location.href = 'login.html?redirect=alert';
    return;
  }

  // بناء الفلاتر
  var tags = [];
  if (S.query) tags.push('"' + S.query + '"');
  S.brands.forEach(function(b){ tags.push(cap(b)); });
  S.fuels.forEach(function(f){ tags.push(cap(f)); });
  if (S.bodyType !== 'all') tags.push(cap(S.bodyType));
  if (S.trans !== 'all') tags.push(S.trans.toUpperCase());
  if (S.drive !== 'all') tags.push(S.drive.toUpperCase());
  if (S.state !== 'all') tags.push(cap(S.state));
  if (S.colorExt !== 'all') tags.push(cap(S.colorExt) + ' ext.');
  if (S.colorInt !== 'all') tags.push(cap(S.colorInt) + ' int.');
  if (S.mileage !== null) tags.push('Max ' + S.mileage.toLocaleString() + ' km');
  if (S.yearMin !== null || S.yearMax !== null)
    tags.push((S.yearMin||'…')+'–'+(S.yearMax||'…'));
  if (S.priceMin !== null || S.priceMax !== null) {
    var lo = S.priceMin ? (S.priceMin/1e6).toFixed(1)+'M' : '0';
    var hi = (S.priceMax && S.priceMax < 99e6) ? (S.priceMax/1e6).toFixed(1)+'M' : '∞';
    tags.push(lo+'–'+hi+' DZD');
  }
  if (!tags.length) tags.push('All Cars');

  // حط الـ chips
  var chips = $('filterChips');
  if (chips) {
    chips.innerHTML = '';
    tags.forEach(function(t){
      var s = document.createElement('span');
      s.className = 'chip';
      s.textContent = t;
      chips.appendChild(s);
    });
  }

  // افتح الـ overlay
  var ov = $('alertOverlay');
  if (ov) {
    ov.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
  }

  // reset الـ steps
  alertNewShowStep('details');
  alertNewResetState();

  // bind الأزرار (مرة وحدة)
  if (!window._alertNewBound) {
    window._alertNewBound = true;

    $('cancelBtn1').addEventListener('click', closeAlertNew);
    $('doneBtn').addEventListener('click', closeAlertNew);
    $('backBtn').addEventListener('click', function(){ alertNewShowStep('details'); });
    $('alertCloseX').addEventListener('click', closeAlertNew);
    $('alertOverlay').addEventListener('click', function(e){
      if (e.target === $('alertOverlay')) closeAlertNew();
    });

    // channel cards
    document.querySelectorAll('.channel-card').forEach(function(btn){
      btn.addEventListener('click', function(){
        alertNewChannel = btn.dataset.channel;
        alertNewContactValue = '';
        $('contactInput').value = '';
        alertNewUpdateChannelUI();
      });
    });

    // freq buttons
    document.querySelectorAll('.freq-btn').forEach(function(btn){
      btn.addEventListener('click', function(){
        alertNewFreq = btn.dataset.freq;
        document.querySelectorAll('.freq-btn').forEach(function(b){ b.classList.remove('active'); });
        btn.classList.add('active');
      });
    });

    $('contactInput').addEventListener('input', function(e){
      alertNewContactValue = e.target.value;
    });

    $('createBtn').addEventListener('click', function(){
      if (!alertNewValidate()) return;
      alertNewPopulateConfirm(tags);
      alertNewShowStep('confirm');
    });

    $('activateBtn').addEventListener('click', function(){
      alertNewSubmit(tags);
    });
  }
}

// ── متغيرات الـ alert الجديد
var alertNewChannel = null;
var alertNewContactValue = '';
var alertNewFreq = 'instant';

var ALERT_CHANNELS = {
  whatsapp: { label:'WhatsApp', color:'#25D366', placeholder:'+213 6XX XXX XXX', fieldLabel:'WhatsApp number', fieldType:'tel' },
  email:    { label:'Email',    color:'#60a5fa', placeholder:'you@example.com',  fieldLabel:'Email address',    fieldType:'email' },
  site:     { label:'Site Notification', color:'#f59e0b' }
};

function closeAlertNew() {
  var ov = $('alertOverlay');
  if (ov) ov.classList.add('hidden');
  document.body.style.overflow = '';
  alertNewResetState();
}

function alertNewResetState() {
  alertNewChannel = null;
  alertNewContactValue = '';
  alertNewFreq = 'instant';
  if ($('contactInput')) $('contactInput').value = '';
  if ($('channelError')) $('channelError').classList.add('hidden');
  document.querySelectorAll('.channel-card').forEach(function(b){ b.className = 'channel-card'; });
  document.querySelectorAll('.freq-btn').forEach(function(b){
    b.classList.remove('active');
    if (b.dataset.freq === 'instant') b.classList.add('active');
  });
  if ($('contactField')) $('contactField').classList.add('hidden');
  if ($('siteNote')) $('siteNote').classList.add('hidden');
}

function alertNewShowStep(step) {
  ['step-details','step-confirm','step-success'].forEach(function(id){
    var el = $(id); if (el) el.classList.remove('active');
  });
  var target = $(  'step-' + step);
  if (target) target.classList.add('active');
}

function alertNewUpdateChannelUI() {
  document.querySelectorAll('.channel-card').forEach(function(b){
    b.className = 'channel-card';
    if (b.dataset.channel === alertNewChannel) b.classList.add('active-' + alertNewChannel);
  });
  var cf = $('contactField'), sn = $('siteNote');
  var inp = $('contactInput'), lbl = $('contactLabel');
  if (alertNewChannel === 'site') {
    if (cf) cf.classList.add('hidden');
    if (sn) sn.classList.remove('hidden');
  } else if (alertNewChannel) {
    var cfg = ALERT_CHANNELS[alertNewChannel];
    if (sn) sn.classList.add('hidden');
    if (cf) cf.classList.remove('hidden');
    if (lbl) lbl.textContent = cfg.fieldLabel;
    if (inp) { inp.placeholder = cfg.placeholder; inp.type = cfg.fieldType; }
  } else {
    if (cf) cf.classList.add('hidden');
    if (sn) sn.classList.add('hidden');
  }
}

function alertNewValidate() {
  var valid = true;
  var grid = $('channelGrid'), errEl = $('channelError'), input = $('contactInput');
  if (!alertNewChannel) {
    grid.classList.add('shake');
    errEl.classList.remove('hidden');
    setTimeout(function(){ grid.classList.remove('shake'); }, 500);
    valid = false;
  } else {
    errEl.classList.add('hidden');
  }
  if (alertNewChannel === 'email' && !alertNewContactValue.includes('@')) {
    input.classList.add('error'); setTimeout(function(){ input.classList.remove('error'); }, 500); valid = false;
  } else if (alertNewChannel === 'whatsapp' && alertNewContactValue.replace(/\D/g,'').length < 8) {
    input.classList.add('error'); setTimeout(function(){ input.classList.remove('error'); }, 500); valid = false;
  }
  return valid;
}

function alertNewPopulateConfirm(tags) {
  var ch = ALERT_CHANNELS[alertNewChannel];
  $('confirmFilters').textContent = tags.length ? tags.join(', ') : '—';
  $('confirmChannel').textContent = ch.label;
  $('confirmContact').textContent = alertNewChannel === 'site' ? 'Your account' : (alertNewContactValue || '—');
  $('confirmFreq').textContent    = alertNewFreq.charAt(0).toUpperCase() + alertNewFreq.slice(1);
}

function alertNewSubmit(tags) {
  var user = JSON.parse(localStorage.getItem('c2dz_user') || '{}');
  var payload = {
    client_id:    user.id || null,
    client_name:  ((user.first_name||'') + ' ' + (user.last_name||'')).trim(),
    client_email: user.email || '',
    client_phone: alertNewChannel === 'whatsapp' ? alertNewContactValue : '',
    notify_channel: alertNewChannel,
    notify_contact: alertNewContactValue,
    brand:        S.brands.join(', '),
    year_min:     S.yearMin,
    year_max:     S.yearMax,
    budget_min:   S.priceMin,
    budget_max:   S.priceMax,
    body_type:    S.bodyType !== 'all' ? S.bodyType : '',
    fuel_type:    S.fuels.join(', '),
    description:  S.query || '',
    frequency:    alertNewFreq
  };

  payload.action = 'create_alert';
fetch('notification_action.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload)
  })
  .then(function(r){ return r.json(); })
  .then(function(data){
    if (data.success) {
      // success chips
      var sc = $('successChips');
      if (sc) {
        sc.innerHTML = '';
        tags.forEach(function(t){
          var s = document.createElement('span');
          s.className = 'chip'; s.textContent = t;
          sc.appendChild(s);
        });
      }
      var ch = ALERT_CHANNELS[alertNewChannel];
      var txt = 'You\'ll receive a <strong>' + alertNewFreq + '</strong> notification via <strong style="color:' + ch.color + '">' + ch.label + '</strong>';
      if (alertNewChannel !== 'site') txt += ' at <strong>' + alertNewContactValue + '</strong>';
      txt += ' as soon as a matching listing appears.';
      $('successText').innerHTML = txt;
      alertNewShowStep('success');
    } else {
      toast('Error: ' + (data.error || 'Please try again.'));
    }
  })
  .catch(function(){ toast('Connection error. Please try again.'); });
}
function submitAlert() {
    var name  = $('alertName')  ? $('alertName').value.trim()  : '';
    var email = $('alertEmail') ? $('alertEmail').value.trim() : '';
    if (!name)  { toast('Please enter your name.'); return; }
    if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { 
        toast('Please enter a valid email.'); return; 
    }
    var user = JSON.parse(localStorage.getItem('c2dz_user') || '{}');
    var payload = {
        client_id:    user.id || null,
        client_name:  name,
        client_email: email,
        client_phone: $('alertPhone') ? $('alertPhone').value.trim() : '',
        brand:        S.brands.join(', '),
        year_min:     S.yearMin,
        year_max:     S.yearMax,
        budget_min:   S.priceMin,
        budget_max:   S.priceMax,
        body_type:    S.bodyType !== 'all' ? S.bodyType : '',
        fuel_type:    S.fuels.join(', '),
        description:  S.query || '',
        frequency:    alertFreq
    };
    payload.action = 'create_alert';
fetch('notification_action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(function(r){ return r.json(); })
    .then(function(data){
        if (data.success) {
            ['alertName','alertEmail','alertPhone'].forEach(function(id){
                var e=$(id); if(e) e.value='';
            });
            window.closeModal('alertModal');
            toast('✓ Alert created! Dealers will be notified.');
        } else {
            toast('Error: ' + (data.error || 'Please try again.'));
        }
    })
    .catch(function(){ toast('Connection error. Please try again.'); });
}

  /* ── HAMBURGER ── */
  function bindHamburger() {
    var btn = $('hamburger'), nav = $('mainNav');
    if (!btn || !nav) return;
    btn.addEventListener('click', function () {
      nav.style.display = nav.style.display === 'flex' ? 'none' : 'flex';
    });
  }

  /* ── HEADER SCROLL ── */
  function bindHeaderScroll() {
    var hdr = $('mainHeader');
    if (!hdr) return;
    window.addEventListener('scroll', function () {
      hdr.style.boxShadow = window.scrollY > 10 ? '0 2px 24px rgba(0,0,0,0.4)' : '';
    });
  }
function loadCarsFromDB() {
  applyFilters();
}
  /* ── INIT ── */
  document.addEventListener('DOMContentLoaded', function () {

    // Drawer triggers
    var trigger = $('filtersTrigger');
    if (trigger) trigger.addEventListener('click', openDrawer);

    var drawerClose = $('drawerClose');
    if (drawerClose) drawerClose.addEventListener('click', closeDrawer);

    var drawerOverlay = $('drawerOverlay');
    if (drawerOverlay) drawerOverlay.addEventListener('click', closeDrawer);

    var drawerReset = $('drawerReset');
    if (drawerReset) drawerReset.addEventListener('click', window.resetFilters);

    var drawerViewBtn = $('drawerViewBtn');
    if (drawerViewBtn) drawerViewBtn.addEventListener('click', function () {
      applyFilters();
      closeDrawer();
    });

    // No results alert
    var nrAlert = $('noResultsAlertBtn');
    if (nrAlert) nrAlert.addEventListener('click', openAlertModal);

    // Alert submit
    var alertSubmit = $('alertSubmit');
    if (alertSubmit) alertSubmit.addEventListener('click', submitAlert);

    // Escape key
    document.addEventListener('keydown', function (e) {
      if (e.key !== 'Escape') return;
      var drawer = $('filterDrawer');
      if (drawer && drawer.classList.contains('open')) { closeDrawer(); return; }
      var modal = document.querySelector('.modal-bg.open');
      if (modal) window.closeModal(modal.id);
    });

    bindFilters();
    bindSearch();
    bindSort();
    bindViewToggle();
    bindHamburger();
    bindHeaderScroll();
    parseURL();
    loadCarsFromDB();
    applyFilters();
    // في نهاية DOMContentLoaded، بعد applyFilters()
if (new URLSearchParams(window.location.search).get('openAlert') === '1' && isLoggedIn()) {
  openAlertModal();
}
  });

})();