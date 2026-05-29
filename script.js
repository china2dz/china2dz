/* =========================================
   CHINA2DZ — script.js
   ========================================= */

/* ============================================================
   CAR DATA
   ============================================================ */
var ALL_CARS = [];
/* ============================================================
   FILTER OPTION LISTS
   ============================================================ */
var BRANDS = [
  'Chery','BYD','Haval','MG','Geely','BAIC','Changan','JAC',
  'Dongfeng','Omoda','Jetour','Livan','Exeed','Forthing','Deepal',
  'Avatr','Nio','Xpeng','GWM','DFSK','Foton','Maxus','Roewe','Tank','Wey'
];

var YEARS = ['2026','2025','2024','2023','2022','2021','2020'];

var FUEL_TYPES = ['Essence','Diesel','Hybrid','Electric','Plug-in Hybrid'];

var BODY_TYPES = ['SUV','Sedan','Hatchback','Pickup','Van','Coupe','Crossover','MPV'];

var TRANSMISSION_TYPES = ['Automatic','Manual','CVT','DCT'];

var DRIVE_TYPES = ['FWD','RWD','AWD','4WD'];

var SEAT_OPTIONS = ['2','4','5','6','7','8','9'];

var WILAYAS = [
  'Adrar','Chlef','Laghouat','Oum El Bouaghi','Batna','Béjaïa','Biskra',
  'Béchar','Blida','Bouira','Tamanrasset','Tébessa','Tlemcen','Tiaret',
  'Tizi Ouzou','Alger','Djelfa','Jijel','Sétif','Saïda','Skikda',
  'Sidi Bel Abbès','Annaba','Guelma','Constantine','Médéa','Mostaganem',
  'M\'Sila','Mascara','Ouargla','Oran','El Bayadh','Illizi',
  'Bordj Bou Arréridj','Boumerdès','El Tarf','Tindouf','Tissemsilt',
  'El Oued','Khenchela','Souk Ahras','Tipaza','Mila','Aïn Defla',
  'Naâma','Aïn Témouchent','Ghardaïa','Relizane','Timimoun',
  'Bordj Badji Mokhtar','Ouled Djellal','Béni Abbès','In Salah',
  'In Guezzam','Touggourt','Djanet','El M\'Ghair','El Menia'
];

var KM_RANGES = [
  {label:'Brand New (0 km)', min:0, max:0},
  {label:'Under 10,000 km', min:1, max:10000},
  {label:'10,000 – 50,000 km', min:10000, max:50000},
  {label:'50,000 – 100,000 km', min:50000, max:100000},
  {label:'Over 100,000 km', min:100000, max:Infinity}
];

var PRICE_RANGES = [
  {label:'Under 2M DZD', min:0, max:2000000},
  {label:'2M – 4M DZD', min:2000000, max:4000000},
  {label:'4M – 6M DZD', min:4000000, max:6000000},
  {label:'6M – 10M DZD', min:6000000, max:10000000},
  {label:'Over 10M DZD', min:10000000, max:Infinity}
];

var EXT_COLORS = [
  {name:'White', hex:'#ffffff'},{name:'Pearl White', hex:'#f8f8f0'},
  {name:'Black', hex:'#1a1a1a'},{name:'Midnight Black', hex:'#0d0d0d'},
  {name:'Silver', hex:'#c0c0c0'},{name:'Gray', hex:'#6b6b6b'},
  {name:'Dark Grey', hex:'#404040'},{name:'Red', hex:'#d91f26'},
  {name:'Burgundy', hex:'#7c2d2d'},{name:'Blue', hex:'#2563eb'},
  {name:'Navy Blue', hex:'#1e3a5f'},{name:'Sky Blue', hex:'#7dd3fc'},
  {name:'Green', hex:'#16a34a'},{name:'Olive Green', hex:'#4a5e2c'},
  {name:'Brown', hex:'#78350f'},{name:'Gold', hex:'#d97706'},
  {name:'Champagne', hex:'#e8d5a3'},{name:'Orange', hex:'#ea580c'},
  {name:'Yellow', hex:'#eab308'},{name:'Purple', hex:'#7c3aed'}
];

var INT_COLORS = [
  {name:'Black', hex:'#1a1a1a'},{name:'Beige', hex:'#d4b896'},
  {name:'Grey', hex:'#9ca3af'},{name:'Brown', hex:'#78350f'},
  {name:'Red', hex:'#d91f26'},{name:'White', hex:'#f5f5f5'},
  {name:'Cream', hex:'#fffbeb'},{name:'Cognac', hex:'#9a4523'}
];

/* ============================================================
   FILTER STATE
   ============================================================ */
window.activeFilters = {
  brand: '', year: '', wilaya: '', fuel: '', bodyType: '',
  transmission: '', drive: '', seats: '', colorExt: '', colorInt: '',
  kmRange: '', priceRange: '', priceMin: '', priceMax: '',
  regType: ''
};

/* ============================================================
   FUZZY MATCH
   ============================================================ */
   function checkBlocked() {
  if (localStorage.getItem('is_blocked') !== 'true') return false;
  var until = localStorage.getItem('blocked_until');
  if (until) {
    var now = new Date();
    var end = new Date(until);
    if (now > end) {
      localStorage.removeItem('is_blocked');
      localStorage.removeItem('blocked_until');
      localStorage.removeItem('block_reason');
      localStorage.removeItem('block_remaining');
      return false;
    }
  }
  return true;
}

function showBlockedToast(msg) {
  msg = msg || 'Your account is suspended. You cannot perform this action.';
  var existing = document.getElementById('blockedToastGlobal');
  if (existing) existing.remove();
  var div = document.createElement('div');
  div.id = 'blockedToastGlobal';
  div.style.cssText = 'position:fixed;top:80px;left:50%;transform:translateX(-50%);' +
    'background:#1a1a1a;border:1.5px solid #e53e3e;border-radius:10px;' +
    'padding:14px 24px;color:#fff;font-size:13px;font-weight:600;' +
    'z-index:9999;display:flex;align-items:center;gap:10px;' +
    'box-shadow:0 8px 32px rgba(229,62,62,0.3);animation:fadeInDown .3s ease;' +
    'max-width:90vw;text-align:center;';
  div.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#e53e3e" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>' + msg;
  document.body.appendChild(div);
  setTimeout(function(){ if(div.parentNode) div.remove(); }, 4000);
}
function fuzzyMatch(car, q) {
  var s = (car.brand + ' ' + car.name + ' ' + car.typeLabel + ' ' + car.fuel + ' ' + car.year).toLowerCase();
  return q.toLowerCase().split(' ').every(function(w){ return s.indexOf(w) > -1; });
}

/* ============================================================
   FILTER CARS
   ============================================================ */
function filterCars(cars, filters, query) {
  return cars.filter(function(car) {
    if (query && !fuzzyMatch(car, query)) return false;
    if (filters.brand        && car.brand        !== filters.brand)        return false;
if (filters.year         && car.year + ''    !== filters.year)         return false;
if (filters.wilaya       && car.wilaya       !== filters.wilaya)       return false;
if (filters.fuel         && car.fuel         !== filters.fuel)         return false;
if (filters.bodyType     && car.bodyType     !== filters.bodyType)     return false;
if (filters.transmission && car.transmission !== filters.transmission) return false;
if (filters.drive        && car.drive        !== filters.drive)        return false;
    if (filters.seats     && car.seats + ''   !== filters.seats)        return false;
    if (filters.colorExt  && car.colorExt     !== filters.colorExt)     return false;
    if (filters.colorInt  && car.colorInt     !== filters.colorInt)     return false;
    if (filters.regType   && car.regType      !== filters.regType)      return false;
    if (filters.priceMin  !== '' && car.priceRaw < parseInt(filters.priceMin)) return false;
    if (filters.priceMax  !== '' && car.priceRaw > parseInt(filters.priceMax)) return false;
    if (filters.priceRange) {
      var pr = PRICE_RANGES[parseInt(filters.priceRange)];
      if (pr && (car.priceRaw < pr.min || (pr.max !== Infinity && car.priceRaw > pr.max))) return false;
    }
    if (filters.kmRange !== '') {
      var kr = KM_RANGES[parseInt(filters.kmRange)];
      if (kr) {
        if (kr.max === 0 && car.kmRaw !== 0) return false;
        else if (kr.max !== 0) {
          if (car.kmRaw < kr.min) return false;
          if (kr.max !== Infinity && car.kmRaw > kr.max) return false;
        }
      }
    }
    return true;
  });
}

function countActiveFilters(filters) {
  var n = 0;
  var keys = ['brand','year','wilaya','fuel','bodyType','transmission','drive','seats','colorExt','colorInt','regType','kmRange','priceRange'];
  keys.forEach(function(k){ if (filters[k] && filters[k] !== '') n++; });
  if (filters.priceMin || filters.priceMax) n++;
  return n;
}

/* ============================================================
   FILTER URL SERIALIZATION
   ============================================================ */
function serializeFiltersToURL(filters, query) {
  var params = new URLSearchParams();
  if (query) params.set('q', query);
  var keys = ['brand','year','wilaya','fuel','bodyType','transmission','drive','seats','colorExt','colorInt','regType','kmRange','priceRange','priceMin','priceMax'];
  keys.forEach(function(k){ if (filters[k] && filters[k] !== '') params.set(k, filters[k]); });
  return params.toString();
}

function deserializeFiltersFromURL() {
  var params = new URLSearchParams(window.location.search);
  var f = {
    brand: '', year: '', wilaya: '', fuel: '', bodyType: '',
    transmission: '', drive: '', seats: '', colorExt: '', colorInt: '',
    kmRange: '', priceRange: '', priceMin: '', priceMax: '', regType: ''
  };
  var keys = ['brand','year','wilaya','fuel','bodyType','transmission','drive','seats','colorExt','colorInt','regType','kmRange','priceRange','priceMin','priceMax'];
  keys.forEach(function(k){ var v = params.get(k); if (v) f[k] = v; });
  return f;
}

function resetActiveFilters() {
  window.activeFilters = {
    brand: '', year: '', wilaya: '', fuel: '', bodyType: '',
    transmission: '', drive: '', seats: '', colorExt: '', colorInt: '',
    kmRange: '', priceRange: '', priceMin: '', priceMax: '', regType: ''
  };
}

/* ============================================================
   FILTER LABELS FOR ACTIVE TAGS
   ============================================================ */
function getFilterLabels() {
  var f = window.activeFilters;
  var tags = [];
  if (f.brand)        tags.push({ label: f.brand, key: 'brand' });
  if (f.year)         tags.push({ label: 'Year: ' + f.year, key: 'year' });
  if (f.wilaya)       tags.push({ label: f.wilaya, key: 'wilaya' });
  if (f.fuel)         tags.push({ label: f.fuel, key: 'fuel' });
  if (f.bodyType)     tags.push({ label: f.bodyType, key: 'bodyType' });
  if (f.transmission) tags.push({ label: f.transmission, key: 'transmission' });
  if (f.drive)        tags.push({ label: f.drive, key: 'drive' });
  if (f.seats)        tags.push({ label: f.seats + ' Seats', key: 'seats' });
  if (f.colorExt)     tags.push({ label: 'Ext: ' + f.colorExt, key: 'colorExt' });
  if (f.colorInt)     tags.push({ label: 'Int: ' + f.colorInt, key: 'colorInt' });
  if (f.regType)      tags.push({ label: 'Reg: ' + f.regType, key: 'regType' });
  if (f.kmRange !== '') {
    var kr = KM_RANGES[parseInt(f.kmRange)];
    if (kr) tags.push({ label: kr.label, key: 'kmRange' });
  }
  if (f.priceRange !== '') {
    var pr = PRICE_RANGES[parseInt(f.priceRange)];
    if (pr) tags.push({ label: pr.label, key: 'priceRange' });
  }
  if (f.priceMin || f.priceMax) {
    var pl = '';
    if (f.priceMin && f.priceMax) pl = Number(f.priceMin).toLocaleString() + ' – ' + Number(f.priceMax).toLocaleString() + ' DZD';
    else if (f.priceMin) pl = '≥ ' + Number(f.priceMin).toLocaleString();
    else pl = '≤ ' + Number(f.priceMax).toLocaleString();
    tags.push({ label: pl, key: 'customPrice' });
  }
  return tags;
}

function removeFilterTag(key) {
  if (key === 'customPrice') { window.activeFilters.priceMin = ''; window.activeFilters.priceMax = ''; }
  else window.activeFilters[key] = '';
  if (typeof window.onFiltersApplied === 'function') window.onFiltersApplied();
  syncSelectBoxes();
}

/* ============================================================
   SELECT BOX FILTER PANEL BUILDER
   ============================================================ */
function buildSelectOption(val, label) {
  return '<option value="' + val + '">' + label + '</option>';
}

function buildSelectGroup(id, label, icon, options, placeholder) {
  var opts = '<option value="">' + placeholder + '</option>';
  options.forEach(function(o) {
    if (typeof o === 'object') opts += buildSelectOption(o.value, o.label);
    else opts += buildSelectOption(o, o);
  });
  return '<div class="sf-group">'
    + '<label class="sf-label" for="' + id + '">'
    + '<span class="sf-icon">' + icon + '</span>' + label
    + '</label>'
    + '<div class="sf-select-wrap">'
    + '<select id="' + id + '" class="sf-select" data-filter="' + id + '">' + opts + '</select>'
    + '<span class="sf-arrow"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg></span>'
    + '</div>'
    + '</div>';
}

var ICONS = {
  brand:        '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13" rx="1"/><path d="M16 8l4 2v5h-4z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>',
  year:         '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>',
  km:           '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>',
  fuel:         '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 22V8l9-6 9 6v14"/><path d="M10 22v-6.5a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 .5.5V22"/></svg>',
  body:         '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 17H2v-5l5-6h12l2 5v6h-2"/><circle cx="7" cy="17" r="2"/><circle cx="17" cy="17" r="2"/></svg>',
  transmission: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="6" cy="6" r="2"/><circle cx="18" cy="6" r="2"/><circle cx="6" cy="18" r="2"/><path d="M6 8v4m0 2v2m12-10v10M8 6h8"/></svg>',
  drive:        '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M12 1v4m0 14v4M1 12h4m14 0h4"/></svg>',
  seats:        '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
  color:        '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="13.5" cy="6.5" r=".5"/><circle cx="17.5" cy="10.5" r=".5"/><circle cx="8.5" cy="7.5" r=".5"/><circle cx="6.5" cy="12.5" r=".5"/><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.555-2.503 5.555-5.554C21.965 6.012 17.461 2 12 2z"/></svg>',
  wilaya:       '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>',
  price:        '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>'
};

function buildFilterPanel(containerId) {
  var kmOpts = KM_RANGES.map(function(r, i){ return { value: i, label: r.label }; });
  var priceOpts = PRICE_RANGES.map(function(r, i){ return { value: i, label: r.label }; });
  var colorExtOpts = EXT_COLORS.map(function(c){ return { value: c.name, label: c.name }; });
  var colorIntOpts = INT_COLORS.map(function(c){ return { value: c.name, label: c.name }; });

  var html = '<div class="select-filter-panel" id="selectFilterPanel">'
    + '<div class="sfp-header">'
    +   '<div class="sfp-title"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="4" y1="6" x2="20" y2="6"/><line x1="8" y1="12" x2="16" y2="12"/><line x1="11" y1="18" x2="13" y2="18"/></svg> Filters</div>'
    +   '<div class="sfp-header-actions">'
    +     '<span class="sfp-count" id="sfpCount" style="display:none">0 active</span>'
    +     '<button class="sfp-reset" id="sfpResetBtn">Reset</button>'
    +   '</div>'
    + '</div>'
    + '<div class="sfp-grid">'
    +   buildSelectGroup('brand',        'Brand',           ICONS.brand,        BRANDS,              'All Brands')
    +   buildSelectGroup('year',         'Year',            ICONS.year,         YEARS,               'All Years')
    +   buildSelectGroup('kmRange',      'Mileage',         ICONS.km,           kmOpts,              'Any Mileage')
    +   buildSelectGroup('fuel',         'Fuel Type',       ICONS.fuel,         FUEL_TYPES,          'All Fuels')
    +   buildSelectGroup('bodyType',     'Body Type',       ICONS.body,         BODY_TYPES,          'All Types')
    +   buildSelectGroup('transmission', 'Transmission',    ICONS.transmission, TRANSMISSION_TYPES,  'Any Gearbox')
    +   buildSelectGroup('drive',        'Drive',           ICONS.drive,        DRIVE_TYPES,         'Any Drive')
    +   buildSelectGroup('seats',        'Seats',           ICONS.seats,        SEAT_OPTIONS,        'Any Seats')
    +   buildSelectGroup('colorExt',     'Exterior Color',  ICONS.color,        colorExtOpts,        'Any Color')
    +   buildSelectGroup('colorInt',     'Interior Color',  ICONS.color,        colorIntOpts,        'Any Interior')
    +   buildSelectGroup('wilaya',       'Wilaya',          ICONS.wilaya,       WILAYAS,             'All Wilayas')
    +   buildSelectGroup('priceRange',   'Price Range',     ICONS.price,        priceOpts,           'Any Price')
    + '</div>'
    + '<div class="sfp-custom-price" id="sfpCustomPrice">'
    +   '<div class="sfp-price-label"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg> Custom Price (DZD)</div>'
    +   '<div class="sfp-price-row">'
    +     '<input type="text" id="sfpPriceMin" class="sfp-price-input" placeholder="Min (e.g. 2500000)">'
    +     '<span class="sfp-price-sep">—</span>'
    +     '<input type="text" id="sfpPriceMax" class="sfp-price-input" placeholder="Max (e.g. 6000000)">'
    +   '</div>'
    + '</div>'
    + '</div>';

  var container = document.getElementById(containerId);
  if (container) container.innerHTML = html;

  bindSelectFilterEvents(containerId);
}

function bindSelectFilterEvents(containerId) {
  var container = document.getElementById(containerId) || document;

  container.querySelectorAll('.sf-select').forEach(function(sel) {
    sel.addEventListener('change', function() {
      var key = this.dataset.filter;
      window.activeFilters[key] = this.value;
      updateSfpCount();
      if (typeof window.onFiltersApplied === 'function') window.onFiltersApplied();
    });
  });

  var resetBtn = document.getElementById('sfpResetBtn');
  if (resetBtn) resetBtn.addEventListener('click', function() {
    resetActiveFilters();
    syncSelectBoxes();
    updateSfpCount();
    if (typeof window.onFiltersApplied === 'function') window.onFiltersApplied();
  });

  var pMin = document.getElementById('sfpPriceMin');
  var pMax = document.getElementById('sfpPriceMax');
  if (pMin) pMin.addEventListener('input', function() {
    window.activeFilters.priceMin = this.value.replace(/\D/g,'');
    updateSfpCount();
    if (typeof window.onFiltersApplied === 'function') window.onFiltersApplied();
  });
  if (pMax) pMax.addEventListener('input', function() {
    window.activeFilters.priceMax = this.value.replace(/\D/g,'');
    updateSfpCount();
    if (typeof window.onFiltersApplied === 'function') window.onFiltersApplied();
  });
}

function syncSelectBoxes() {
  var f = window.activeFilters;
  var keys = ['brand','year','wilaya','fuel','bodyType','transmission','drive','seats','colorExt','colorInt','regType','kmRange','priceRange'];
  keys.forEach(function(k) {
    var el = document.getElementById(k);
    if (el) el.value = f[k] || '';
  });
  var pMin = document.getElementById('sfpPriceMin');
  var pMax = document.getElementById('sfpPriceMax');
  if (pMin) pMin.value = f.priceMin || '';
  if (pMax) pMax.value = f.priceMax || '';
}

function updateSfpCount() {
  var n = countActiveFilters(window.activeFilters);
  var el = document.getElementById('sfpCount');
  if (el) { el.textContent = n + ' active'; el.style.display = n > 0 ? 'inline-flex' : 'none'; }
  var tb = document.getElementById('filterTriggerBadge');
  if (tb) { tb.textContent = n; tb.style.display = n > 0 ? 'inline-flex' : 'none'; }
  var triggerBtn = document.getElementById('filterTriggerBtn');
  if (triggerBtn) triggerBtn.classList.toggle('has-filters', n > 0);
}

/* ============================================================
   INLINE PANEL TOGGLE (index.html)
   ============================================================ */
   var filterPanelOpen = false;
function toggleFilterPanel(forceState) {
  var container = document.getElementById('filterPanelContainer');
  if (!container) return;
  filterPanelOpen = (typeof forceState === 'boolean') ? forceState : !filterPanelOpen;
  container.classList.toggle('sfp-open', filterPanelOpen);
  if (filterPanelOpen) {
    setTimeout(function() {
      container.scrollIntoView({ behavior: 'smooth', block: 'center' });
      // تأثير التلوين
      container.style.transition = 'background 0.3s';
      container.style.background = 'rgba(4, 25, 61, 0.5)';
      setTimeout(function() {
        container.style.background = '';
      }, 1500);
    }, 50);
  }
}

/* ============================================================
   CARD BUILDER
   ============================================================ */
function buildCard(car, i, animate) {
  var animClass = animate ? ' result-animate' : '';
  var delay     = animate ? ' style="animation-delay:' + (i * 60) + 'ms"' : '';
  var eClass    = car.isElectric ? ' electric-card' : '';
  var eGlow     = car.isElectric ? '<div class="electric-glow"></div>' : '';
  var eBadge    = car.isElectric
    ? '<span class="car-badge-electric"><svg width="10" height="10" viewBox="0 0 24 24" fill="currentColor"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg> Electric</span>'
    : '';
  var specs = car.specs.map(function(s) {
    return '<div class="spec-row"><span class="spec-k">' + s[0] + '</span><span class="spec-v">' + s[1] + '</span></div>';
  }).join('');
  return '<div class="car-card' + animClass + eClass + '"' + delay + '>'
    + eGlow
    + '<div class="car-img-wrapper">'
    +   '<img src="' + car.img + '" alt="' + car.name + '" onerror="this.src=\'' + car.imgFb + '\'">'
    +   eBadge
    +   '<a href="login.html" class="favorite-btn"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg></a>'
    + '</div>'
    + '<div class="car-info">'
    +   '<div class="car-brand-tag">' + car.brand + '</div>'
    +   '<h3>' + car.name + '</h3>'
    +   '<div class="car-quick-specs"><span>' + car.year + '</span><span>' + car.typeLabel + '</span><span>' + car.km + '</span><span>' + car.engine + '</span></div>'
    +   '<div class="car-specs-table">' + specs + '</div>'
    +   '<div class="car-price-block">'
    +     '<div class="car-price">' + car.price + '</div>'
    +     '<div class="car-nodouane"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> Duty-Free</div>'
    +   '</div>'
    +   '<a href="login.html" class="details-btn">View Details <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>'
    + '</div>'
    + '</div>';
}

/* ============================================================
   SEARCH HELPERS
   ============================================================ */
function goSearch(q) {
  if (!q || !q.trim()) return;
  window.location.href = 'results.php?q=' + encodeURIComponent(q.trim());
}

window.pickSearch = function(name) {
  var inp = document.getElementById('searchInput');
  if (inp) { inp.value = name; inp.dispatchEvent(new Event('input')); inp.focus(); }
};

/* ============================================================
   SHARED INIT
   ============================================================ */
function initCommon() {
  var ham = document.getElementById('hamburger');
  var nav = document.getElementById('mainNav');
  if (ham && nav) {
    ham.addEventListener('click', function() { nav.classList.toggle('open'); });
    nav.querySelectorAll('a').forEach(function(a) { a.addEventListener('click', function() { nav.classList.remove('open'); }); });
  }
  window.addEventListener('scroll', function() {
    var h = document.getElementById('mainHeader');
    if (h) h.style.boxShadow = window.scrollY > 10 ? '0 4px 28px rgba(0,0,0,0.14)' : '0 2px 16px rgba(0,0,0,0.06)';
  });
}
const API = 'api.php';

async function initNav() {
  const token = localStorage.getItem('c2dz_token');
  const loginLi = document.getElementById('nav-login-li');
  const profileLi = document.getElementById('nav-profile-li');
  if (!token) { if(loginLi) loginLi.style.display=''; if(profileLi) profileLi.style.display='none'; return; }

  try {
    const res = await fetch(API + '?action=get_profile', {
      headers: { 'Authorization': 'Bearer ' + token }
    });
    const data = await res.json();
    if (!data.success) { localStorage.removeItem('c2dz_token'); return; }
    const u = data.data;
    loginLi.style.display = 'none';
    profileLi.style.display = '';

    const defaultAvatar = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 40 40'%3E%3Crect fill='%23333' width='40' height='40' rx='20'/%3E%3Ctext fill='%23999' font-size='18' x='20' y='27' text-anchor='middle'%3E%F0%9F%91%A4%3C/text%3E%3C/svg%3E";
    const photo = u.profile_photo || defaultAvatar;
document.getElementById('navAvatar').src = photo;
document.getElementById('ndAvatar').src = photo;
const firstName = u.first_name || (u.full_name || 'Profile').split(' ')[0];
document.getElementById('navUserName').textContent = firstName;
document.getElementById('ndName').textContent = u.full_name || (u.first_name + ' ' + u.last_name).trim() || '';
document.getElementById('ndRole').textContent = u.role === 'agent' ? 'Agent' : 'Client';

// زر Dashboard للـ agent فقط إذا كان approved
var dashLink = document.getElementById('agentDashLink');
if (dashLink) {
  // تحقق من API أولاً، وإذا ما وجد اقرأ من localStorage
  var localUser = JSON.parse(localStorage.getItem('c2dz_user') || '{}');
  var agentStatus = u.agent_status || u.status || localUser.agent_status || localUser.status || '';
if (u.role === 'agent' && (agentStatus === 'approved' || agentStatus === 'active')) {
    dashLink.style.display = 'flex';
  } else {
    dashLink.style.display = 'none';
  }
}

    // Load badges
    const nRes = await fetch(API + '?action=get_notifications', { headers: { 'Authorization': 'Bearer ' + token } });
    const nData = await nRes.json();
    if (nData.success && nData.data.unread > 0) {
      document.getElementById('ndNotifBadge').textContent = nData.data.unread;
      document.getElementById('ndNotifBadge').style.display = 'inline';
      document.getElementById('alertsBadge').textContent = nData.data.unread;
      document.getElementById('alertsBadge').style.display = 'inline';
    }

    const fRes = await fetch(API + '?action=get_favorites', { headers: { 'Authorization': 'Bearer ' + token } });
    const fData = await fRes.json();
    if (fData.success && fData.data.length > 0) {
      document.getElementById('ndFavBadge').textContent = fData.data.length;
      document.getElementById('ndFavBadge').style.display = 'inline';
      document.getElementById('wishlistBadge').textContent = fData.data.length;
      document.getElementById('wishlistBadge').style.display = 'inline';
    }
  } catch(e) {}
}

function toggleProfileDropdown() {
  var dd = document.getElementById('profileDropdown');
dd.style.display = dd.style.display === 'none' ? 'block' : 'none';
}
document.addEventListener('click', function(e) {
  var btn = document.getElementById('navProfileBtn');
  var dd = document.getElementById('profileDropdown');
  if (btn && dd && !btn.contains(e.target)) {
    dd.style.display = 'none';
  }
});

function navWishlist() {
  const token = localStorage.getItem('c2dz_token');
  if (!token) { window.location.href = 'login.html'; return; }
  window.location.href = 'profile.php?tab=favorites';
}
function navnotifications() {
  var user = localStorage.getItem('c2dz_user');
  window.location.href = user ? 'notifications.php' : 'login.html';
}
async function checkAndRedirectAfterLogin() {
  const token = localStorage.getItem('c2dz_token');
  if (!token) return;
  try {
    const res = await fetch(API + '?action=get_profile', {
      headers: { 'Authorization': 'Bearer ' + token }
    });
    const data = await res.json();
    if (!data.success) return;
    const u = data.data;
    if (u.role === 'agent' && u.agent_status === 'approved') {
      // يبقى في index مع ظهور زر dashboard — لا يحتاج redirect
    } else if (u.role === 'agent' && u.agent_status === 'pending') {
      window.location.href = 'agent_pending.php';
    }
  } catch(e) {}
}
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
  localStorage.removeItem('loggedIn');
  localStorage.removeItem('userRole');
  localStorage.removeItem('userId');
  localStorage.removeItem('userStatus');
  localStorage.removeItem('is_blocked');
localStorage.removeItem('blocked_until');
localStorage.removeItem('block_reason');
localStorage.removeItem('block_remaining');
  window.location.href = 'index.php';
}

document.addEventListener('DOMContentLoaded', initNav);
function opennotifications() {
  var user = localStorage.getItem('c2dz_user');
  if (user) {
    window.location.href = 'notifications.php';
  } else {
    window.location.href = 'login.html';
  }
}
function toggleFavorite(carId, carName, carImage, carPrice) {
  if (checkBlocked()) {
    showBlockedToast('Account suspended — you cannot save cars to wishlist.');
    return;
  }
  var userRaw = localStorage.getItem('c2dz_user');
  if (!userRaw) { window.location.href = 'login.html'; return; }
  var user = JSON.parse(userRaw);

  var btn = document.getElementById('fav-' + carId);
  var isSaved = btn && btn.classList.contains('saved');

  if (isSaved) {
    fetch('wishlist_action.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'remove', user_id: user.id, car_id: carId })
    }).then(function(r){ return r.json(); }).then(function(data){
      if (data.success) {
        if (btn) btn.classList.remove('saved');
        showWishlistToast('Removed from wishlist');
      }
    });
  } else {
    fetch('wishlist_action.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        action: 'save', user_id: user.id,
        car_id: carId, car_name: carName,
        car_image: carImage, car_price: carPrice,
        car_link: 'index.php#car-' + carId
      })
    }).then(function(r){ return r.json(); }).then(function(data){
      if (data.success) {
        if (btn) btn.classList.add('saved');
        showWishlistToast('Saved to wishlist ❤️');
      }
    });
  }
}

function loadFavoriteStates() {
  var userRaw = localStorage.getItem('c2dz_user');
  if (!userRaw) return;
  var user = JSON.parse(userRaw);
  fetch('wishlist_action.php?action=get&user_id=' + user.id)
    .then(function(r){ return r.json(); })
    .then(function(data){
      if (data.success && data.favorites) {
        data.favorites.forEach(function(fav){
          var btn = document.getElementById('fav-' + fav.car_id);
          if (btn) btn.classList.add('saved');
        });
      }
    });
}

function showWishlistToast(msg) {
  var t = document.getElementById('toast') || document.getElementById('toastEl');
  if (!t) return;
  t.textContent = msg;
  t.classList.add('show');
  clearTimeout(t._wt);
  t._wt = setTimeout(function(){ t.classList.remove('show'); }, 2500);
}

document.addEventListener('DOMContentLoaded', loadFavoriteStates);
function navWishlist() {
  var user = localStorage.getItem('c2dz_user');
  window.location.href = user ? 'wishlist.html' : 'login.html';
}
/* ============================================================
   CONTACT MODAL — موحد لكل الصفحات
   ============================================================ */
function openContactModal(agentPhone, agentName, agentId, carId, carName) {
  if (checkBlocked()) { showBlockedToast('Account suspended — you cannot contact agents.'); return; }
  
  var existing = document.getElementById('contactModalGlobal');
  if (existing) existing.remove();

  var modal = document.createElement('div');
  modal.id = 'contactModalGlobal';
  modal.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,0.7);backdrop-filter:blur(6px);z-index:9999;display:flex;align-items:center;justify-content:center;padding:20px;animation:fadeIn .2s ease;';
  
  modal.innerHTML = `
    <div style="background:#161616;border:1px solid rgba(255,255,255,0.1);border-radius:20px;width:100%;max-width:400px;padding:28px;position:relative;animation:slideUp .3s ease;">
      <button onclick="document.getElementById('contactModalGlobal').remove()" style="position:absolute;top:14px;right:14px;width:32px;height:32px;background:rgba(255,255,255,0.06);border:none;border-radius:50%;cursor:pointer;color:#888;font-size:16px;display:flex;align-items:center;justify-content:center;">✕</button>
      
      <div style="font-size:11px;color:#666;letter-spacing:1px;text-transform:uppercase;margin-bottom:8px;">China2DZ · Agent</div>
      <div style="font-family:'Syne',sans-serif;font-size:20px;font-weight:800;color:#fff;margin-bottom:4px;">Contact the Agent</div>
      <div style="font-size:13px;color:#666;margin-bottom:20px;">${carName || ''}</div>
      
      <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:20px;">
        <a href="https://wa.me/${agentPhone}" target="_blank" style="display:flex;align-items:center;gap:14px;background:#0d0d0d;border:1px solid rgba(255,255,255,0.08);border-radius:12px;padding:14px 16px;text-decoration:none;color:#fff;transition:border-color .2s;" onmouseover="this.style.borderColor='#00bcd4'" onmouseout="this.style.borderColor='rgba(255,255,255,0.08)'">
          <div style="width:40px;height:40px;border-radius:10px;background:#1a3a2a;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413z" fill="#25D366"/><path d="M12 2C6.48 2 2 6.48 2 12c0 1.76.46 3.41 1.27 4.84L2 22l5.33-1.27A9.94 9.94 0 0012 22c5.52 0 10-4.48 10-10S17.52 2 12 2zm0 18a8.24 8.24 0 01-4.19-1.14l-.3-.18-3.16.76.78-3.08-.2-.31A8.24 8.24 0 013.76 12C3.76 7.44 7.44 3.76 12 3.76S20.24 7.44 20.24 12 16.56 20 12 20z" fill="#25D366"/></svg>
          </div>
          <div>
            <div style="font-size:11px;color:#666;margin-bottom:2px;">WhatsApp</div>
            <div style="font-size:14px;font-weight:600;">${agentPhone}</div>
          </div>
        </a>
        
        <a href="tel:${agentPhone}" style="display:flex;align-items:center;gap:14px;background:#0d0d0d;border:1px solid rgba(255,255,255,0.08);border-radius:12px;padding:14px 16px;text-decoration:none;color:#fff;transition:border-color .2s;" onmouseover="this.style.borderColor='#00bcd4'" onmouseout="this.style.borderColor='rgba(255,255,255,0.08)'">
          <div style="width:40px;height:40px;border-radius:10px;background:#1a2a3a;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#4A9EE8" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2A19.79 19.79 0 014.69 12a19.79 19.79 0 01-3.07-8.67A2 2 0 013.6 1.27h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L7.91 8.91a16 16 0 006 6l.91-.91a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
          </div>
          <div>
            <div style="font-size:11px;color:#666;margin-bottom:2px;">Direct Call</div>
            <div style="font-size:14px;font-weight:600;">${agentPhone}</div>
          </div>
        </a>
      </div>
      
      <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;font-size:11px;color:#555;text-transform:uppercase;letter-spacing:1px;">
        <div style="flex:1;height:1px;background:rgba(255,255,255,0.08)"></div>
        or message via platform
        <div style="flex:1;height:1px;background:rgba(255,255,255,0.08)"></div>
      </div>
      
      <button onclick="openContactModal_startChat(${agentId},'${(carName||'').replace(/'/g,"\\'")}',${carId})" style="width:100%;background:#00bcd4;color:#fff;border:none;border-radius:12px;padding:14px;font-family:'Syne',sans-serif;font-weight:700;font-size:14px;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;transition:background .2s;" onmouseover="this.style.background='#0097a7'" onmouseout="this.style.background='#00bcd4'">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
        Message China2DZ
      </button>
    </div>`;
  
  modal.addEventListener('click', function(e) {
    if (e.target === modal) modal.remove();
  });
  
  document.body.appendChild(modal);
}

async function openContactModal_startChat(agentId, carName, carId) {
  document.getElementById('contactModalGlobal').remove();
  var token = localStorage.getItem('c2dz_token');
  if (!token) { window.location.href = 'login.html'; return; }
  try {
    var res = await fetch('api.php?action=start_conversation', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token },
      body: JSON.stringify({ agent_id: parseInt(agentId), car_id: String(carId), car_name: carName })
    });
    var data = await res.json();
    if (data.success) {
      window.location.href = 'profile.php?tab=chat&conv=' + data.data.conversation_id;
    } else { alert(data.message); }
  } catch(e) { alert('Error: ' + e.message); }
}