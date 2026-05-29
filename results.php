<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Search Results — China2DZ</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,400&display=swap" rel="stylesheet">
<link rel="stylesheet" href="results.css">
<script src="theme.js"></script>
</head>
<body>

<!-- ═══════════════════════════════════════════
     HEADER
═══════════════════════════════════════════ -->
<header id="mainHeader">
  <div class="logo">
    <a href="index.php"><span class="logo-china">China</span><span class="logo-2dz">2DZ</span></a>
  </div>
  <nav id="mainNav">
    <ul>
      <li><a href="index.php">Home</a></li>
      <li><a href="index.php#cars">Cars</a></li>
      <li><a href="index.php#agents">Dealers</a></li>
      <li><a href="index.php#reviews">Reviews</a></li>
      <li>
        <a href="javascript:void(0)" onclick="navWishlist()" class="nav-icon-link">
          Wishlist
          <span id="wishlistBadge" style="display:none;background:#d91f26;color:#fff;font-size:.68rem;padding:1px 6px;border-radius:10px;"></span>
        </a>
      </li>
      <li>
        <a href="javascript:void(0)" onclick="navnotifications()" class="nav-icon-link">
          Notifications
          <span id="alertsBadge" style="display:none;background:#d91f26;color:#fff;font-size:.68rem;padding:1px 6px;border-radius:10px;"></span>
        </a>
      </li>
      <li id="nav-login-li"><a href="login.html" class="login-btn">Sign In</a></li>
      <li id="nav-profile-li" style="display:none;position:relative;">
        <button id="navProfileBtn" onclick="toggleProfileDropdown()"
          style="background:none;border:1px solid rgba(255,255,255,0.12);cursor:pointer;display:flex;align-items:center;gap:8px;padding:6px 12px;border-radius:20px;">
          <img id="navAvatar" src="" style="width:28px;height:28px;border-radius:50%;object-fit:cover;">
          <span id="navUserName" style="font-size:.85rem;font-weight:600;color:#f0f0f0;max-width:90px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"></span>
          <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
        </button>
        <div id="profileDropdown" style="display:none;position:absolute;top:calc(100% + 8px);right:0;min-width:220px;background:#161616;border:1px solid rgba(255,255,255,0.1);border-radius:14px;box-shadow:0 12px 40px rgba(0,0,0,0.5);z-index:999;overflow:hidden;">
          <div style="padding:14px 16px;border-bottom:1px solid rgba(255,255,255,0.08);display:flex;gap:10px;align-items:center;">
            <img id="ndAvatar" src="" style="width:38px;height:38px;border-radius:50%;object-fit:cover;">
            <div>
              <div id="ndName" style="font-weight:700;font-size:.88rem;color:#fff;"></div>
              <div id="ndRole" style="font-size:.75rem;color:rgba(255,255,255,0.4);"></div>
            </div>
          </div>
          <a href="agents.php" id="agentDashLink" style="display:none;align-items:center;gap:10px;padding:12px 16px;color:rgba(255,255,255,0.8);text-decoration:none;font-size:.85rem;border-bottom:1px solid rgba(255,255,255,0.06);">🏢 My Dashboard</a>
          <a href="profile.php" style="display:flex;align-items:center;gap:10px;padding:12px 16px;color:rgba(255,255,255,0.8);text-decoration:none;font-size:.85rem;border-bottom:1px solid rgba(255,255,255,0.06);">👤 My Profile</a>
          <a href="profile.php?tab=favorites" style="display:flex;align-items:center;gap:10px;padding:12px 16px;color:rgba(255,255,255,0.8);text-decoration:none;font-size:.85rem;border-bottom:1px solid rgba(255,255,255,0.06);">
            ❤️ Favorites
            <span id="ndFavBadge" style="display:none;margin-right:auto;background:#d91f26;color:#fff;font-size:.68rem;padding:1px 7px;border-radius:10px;"></span>
          </a>
          <a href="profile.php?tab=notifications" style="display:flex;align-items:center;gap:10px;padding:12px 16px;color:rgba(255,255,255,0.8);text-decoration:none;font-size:.85rem;border-bottom:1px solid rgba(255,255,255,0.06);">
            🔔 Notifications
            <span id="ndNotifBadge" style="display:none;margin-right:auto;background:#d91f26;color:#fff;font-size:.68rem;padding:1px 7px;border-radius:10px;"></span>
          </a>
          <button onclick="doLogout()" style="display:flex;align-items:center;gap:10px;padding:12px 16px;color:#ff5057;font-size:.85rem;background:none;border:none;cursor:pointer;width:100%;font-family:inherit;">🚪 Logout</button>
        </div>
      </li>
      <button id="themeBtn" onclick="toggleTheme()"
        style="background:none;border:1px solid rgba(255,255,255,0.2);color:#fff;border-radius:50%;width:36px;height:36px;cursor:pointer;font-size:16px;">🌙</button>
    </ul>
  </nav>
  <button class="hamburger" id="hamburger" aria-label="Menu">
    <span></span><span></span><span></span>
  </button>
</header>

<!-- ═══════════════════════════════════════════
     HERO SEARCH
═══════════════════════════════════════════ -->
<section class="results-hero">
  <div class="results-hero-inner">
    <div class="breadcrumb">
      <a href="index.php">Home</a>
      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
      <span>Search Results</span>
    </div>
    <h1 class="results-title">Find Your <span>Chinese Car</span></h1>
    <div class="hero-search-wrap">
      <div class="hero-search-box">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
        <input type="text" id="searchInput" placeholder="Search brand, model, city…" autocomplete="off">
        <button class="hero-search-btn" id="searchBtn">Search</button>
      </div>
    </div>
  </div>
</section>

<!-- ═══════════════════════════════════════════
     RESULTS LAYOUT
═══════════════════════════════════════════ -->
<div class="page-wrap">

  <!-- TOP BAR -->
  <div class="topbar">
    <div class="topbar-left">
      <span class="results-count" id="resultsCount"></span>
      <div class="active-tags" id="activeTags"></div>
    </div>
    <div class="topbar-right">
      <!-- Clear All -->
      <button class="clear-all-btn" id="clearAllBtn" style="display:none;" onclick="clearAllFilters()">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-3.46"/></svg>
        Clear All
      </button>
      <!-- Sort -->
      <select class="sort-select" id="sortSelect">
        <option value="default">Sort: Default</option>
        <option value="price-asc">Price: Low → High</option>
        <option value="price-desc">Price: High → Low</option>
        <option value="year-desc">Year: Newest</option>
        <option value="year-asc">Year: Oldest</option>
      </select>
      <!-- View toggle -->
      <div class="view-btns">
        <button class="view-btn active" id="gridBtn" title="Grid">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
        </button>
        <button class="view-btn" id="listBtn" title="List">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
        </button>
      </div>
      <!-- Filters -->
      <button class="filters-trigger" id="filtersTrigger">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="4" y1="6" x2="20" y2="6"/><line x1="8" y1="12" x2="16" y2="12"/><line x1="11" y1="18" x2="13" y2="18"/></svg>
        Filters
        <span class="filter-badge" id="filterBadge" style="display:none">0</span>
      </button>
    </div>
  </div>

  <!-- CARS GRID -->
  <div class="cars-grid" id="carsGrid">
  <?php
  require_once 'config.php';

  /* ── Build WHERE from URL params ── */
  $params = [];
  $where  = ["c.status = 'available'"];

  $q = trim($_GET['q'] ?? '');
  if ($q !== '') {
    $where[] = "(c.title LIKE ? OR c.brand LIKE ? OR c.description LIKE ? OR c.wilaya LIKE ?)";
    $like     = '%' . $q . '%';
    $params   = [$like, $like, $like, $like];
  }

  /* Extra URL filters passed from index filter panel */
  if (!empty($_GET['brand'])) {
    $where[]  = "LOWER(c.brand) = ?";
    $params[] = strtolower($_GET['brand']);
  }
  if (!empty($_GET['year'])) {
    $where[]  = "c.year = ?";
    $params[] = (int)$_GET['year'];
  }
  if (!empty($_GET['fuel'])) {
    $where[]  = "LOWER(c.fuel_type) = ?";
    $params[] = strtolower($_GET['fuel']);
  }
  if (!empty($_GET['bodyType'])) {
    $where[]  = "LOWER(c.body_type) = ?";
    $params[] = strtolower($_GET['bodyType']);
  }
  if (!empty($_GET['transmission'])) {
    $where[]  = "LOWER(c.transmission) = ?";
    $params[] = strtolower($_GET['transmission']);
  }
  if (!empty($_GET['drive'])) {
    $where[]  = "LOWER(c.drive_type) = ?";
    $params[] = strtolower($_GET['drive']);
  }
  if (!empty($_GET['priceMin'])) {
    $where[]  = "c.price >= ?";
    $params[] = (int)$_GET['priceMin'];
  }
  if (!empty($_GET['priceMax'])) {
    $where[]  = "c.price <= ?";
    $params[] = (int)$_GET['priceMax'];
  }
  if (!empty($_GET['colorExt'])) {
    $where[]  = "LOWER(c.color_ext) = ?";
    $params[] = strtolower($_GET['colorExt']);
  }
  if (!empty($_GET['colorInt'])) {
    $where[]  = "LOWER(c.color_int) = ?";
    $params[] = strtolower($_GET['colorInt']);
  }
  if (isset($_GET['kmRange']) && $_GET['kmRange'] !== '') {
    $km = (int)$_GET['kmRange'];
    if ($km === 0) {
      $where[] = "c.mileage = 0";
    } else {
      $where[]  = "c.mileage <= ?";
      $params[] = $km;
    }
  }
  if (!empty($_GET['wilaya'])) {
    $where[]  = "LOWER(c.wilaya) = ?";
    $params[] = strtolower($_GET['wilaya']);
  }

  $sql = "
    SELECT c.*,
           GROUP_CONCAT(p.photo_path ORDER BY p.id ASC SEPARATOR '|') AS photos,
           u.phone, u.first_name, u.last_name, ap.company_name
    FROM cars c
    LEFT JOIN car_photos p ON p.car_id = c.id
    LEFT JOIN users u ON u.id = c.agent_id
    LEFT JOIN agent_profiles ap ON ap.user_id = c.agent_id
    WHERE " . implode(' AND ', $where) . "
    GROUP BY c.id
    ORDER BY c.id DESC
  ";
  $stmt = $pdo->prepare($sql);
  $stmt->execute($params);
  $cars = $stmt->fetchAll(PDO::FETCH_ASSOC);

  foreach ($cars as $car):
    $photos      = $car['photos'] ? explode('|', $car['photos']) : [];
    $firstPhoto  = !empty($photos[0]) ? 'http://localhost/' . $photos[0] : null;
    $brand       = htmlspecialchars($car['brand'] ?? '');
    $title       = htmlspecialchars($car['title'] ?? '');
    $year        = htmlspecialchars($car['year']  ?? '');
    $price       = number_format($car['price']);
    $resStatus   = $car['reservation_status'] ?? 'available';
    $reservedUntil = $car['reserved_until'] ?? null;
    $isReserved  = $resStatus === 'reserved' && $reservedUntil && strtotime($reservedUntil) > time();
  ?>
  <div class="car-card <?= $isReserved ? 'car-reserved' : '' ?>"
       data-brand="<?= strtolower($brand) ?>"
       data-price="<?= (int)$car['price'] ?>"
       data-year="<?= $year ?>"
       data-type="<?= strtolower(htmlspecialchars($car['body_type'] ?? '')) ?>"
       data-fuel="<?= strtolower(htmlspecialchars($car['fuel_type'] ?? '')) ?>"
       data-transmission="<?= strtolower(htmlspecialchars($car['transmission'] ?? '')) ?>"
       data-drive="<?= strtolower(htmlspecialchars($car['drive_type'] ?? '')) ?>"
       data-state="<?= (int)($car['mileage'] ?? 0) === 0 ? 'new' : 'used' ?>"
       data-mileage="<?= (int)($car['mileage'] ?? 0) ?>"
       data-color-ext="<?= strtolower(htmlspecialchars($car['color_ext'] ?? '')) ?>"
       data-color-int="<?= strtolower(htmlspecialchars($car['color_int'] ?? '')) ?>"
       data-wilaya="<?= strtolower(htmlspecialchars($car['wilaya'] ?? '')) ?>"
       data-title="<?= strtolower($title) ?>"
       data-id="<?= $car['id'] ?>"
       onclick="window.location.href='listing.html?id=<?= $car['id'] ?>'"
       style="cursor:pointer;">

    <!-- IMAGE / GALLERY -->
    <div class="car-img-wrapper">
      <?php if (count($photos) > 1): ?>
        <div class="car-gallery" data-index="0">
          <?php foreach ($photos as $i => $photo): ?>
            <img src="<?= htmlspecialchars($photo) ?>"
                 alt="<?= $title ?>"
                 class="gallery-img <?= $i === 0 ? 'active' : '' ?>"
                 onerror="this.src='https://images.unsplash.com/photo-1549317661-bd32c8ce0db2?w=400'">
          <?php endforeach; ?>
          <button class="gallery-prev" onclick="event.stopPropagation(); galleryNav(this,-1)">&#8249;</button>
          <button class="gallery-next" onclick="event.stopPropagation(); galleryNav(this,1)">&#8250;</button>
          <div class="gallery-dots">
            <?php foreach ($photos as $i => $p): ?>
              <span class="gdot <?= $i === 0 ? 'active' : '' ?>"></span>
            <?php endforeach; ?>
          </div>
        </div>
      <?php else: ?>
        <img src="<?= $firstPhoto ? htmlspecialchars($firstPhoto) : '' ?>"
             alt="<?= $title ?>"
             onerror="this.src='https://images.unsplash.com/photo-1549317661-bd32c8ce0db2?w=400'">
      <?php endif; ?>

      <a href="javascript:void(0)"
         class="favorite-btn"
         id="fav-<?= $car['id'] ?>"
         onclick="event.stopPropagation(); toggleFavorite(<?= $car['id'] ?>, '<?= addslashes($title) ?>', '<?= addslashes($firstPhoto ?? '') ?>', '<?= $price ?>')">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
        </svg>
      </a>
    </div>

    <!-- CAR INFO -->
    <div class="car-info">
      <div class="car-brand-tag"><?= $brand ?></div>
      <h3><?= $title ?></h3>

      <div class="car-quick-specs">
        <span><?= $year ?></span>
        <?php if (!empty($car['body_type'])): ?><span><?= htmlspecialchars($car['body_type']) ?></span><?php endif; ?>
        <?php if (!empty($car['fuel_type'])): ?><span><?= htmlspecialchars($car['fuel_type']) ?></span><?php endif; ?>
        <?php if (!empty($car['wilaya'])): ?><span><?= htmlspecialchars($car['wilaya']) ?></span><?php endif; ?>
      </div>

      <?php if (!empty($car['description'])): ?>
      <div class="car-desc"><?= htmlspecialchars($car['description']) ?></div>
      <?php endif; ?>

      <div class="car-agent-info">
        <div class="agent-name">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
            <circle cx="12" cy="7" r="4"/>
          </svg>
          <?= htmlspecialchars(trim($car['first_name'] . ' ' . $car['last_name'])) ?>
        </div>
        <?php if (!empty($car['company_name'])): ?>
        <div class="agent-company">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="2" y="7" width="20" height="14" rx="2"/>
            <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
          </svg>
          <?= htmlspecialchars($car['company_name']) ?>
        </div>
        <?php endif; ?>
        <div class="agent-phone">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13.1 19.79 19.79 0 0 1 1.65 4.59 2 2 0 0 1 3.62 2.43h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l.81-.81a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 21.73 17z"/>
          </svg>
          <?= htmlspecialchars($car['phone']) ?>
        </div>
      </div>

      <div class="car-price-block">
        <div class="car-price"><?= $price ?> DZD</div>
        <div class="car-nodouane">
          <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
          Duty-Free
        </div>
      </div>

      <div class="card-actions-row">
        <?php if (isset($_SESSION['user_id'])): ?>
          <a href="javascript:void(0)"
             onclick="event.stopPropagation(); openContactModal('<?= htmlspecialchars($car['phone']) ?>', '<?= addslashes($car['first_name'].' '.$car['last_name']) ?>', <?= $car['agent_id'] ?>, <?= $car['id'] ?>, '<?= addslashes($car['title'] ?? '') ?>')"
             class="contact-agent-btn">Contact →</a>
        <?php else: ?>
          <a href="login.html" class="contact-agent-btn" onclick="event.stopPropagation()">Contact →</a>
        <?php endif; ?>
        <a href="listing.html?id=<?= $car['id'] ?>" class="more-details-btn" onclick="event.stopPropagation()">
          Details
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
        </a>
      </div>
    </div>
  </div>
  <?php endforeach; ?>

  <!-- NO RESULTS (shown by JS when count = 0) -->
  <div class="no-results" id="noResults" style="display:none">
    <div class="no-results-icon">
      <svg width="52" height="52" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
        <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
      </svg>
    </div>
    <h3>No cars found</h3>
    <p>No listings match your search criteria. Adjust your filters or create an alert and we'll notify you when a matching car is listed.</p>
    <div class="no-results-btns">
      <button class="nr-clear-btn" onclick="clearAllFilters()">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-3.46"/></svg>
        Clear Filters
      </button>
      <button class="nr-alert-btn" id="noResultsAlertBtn">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
        Create Alert
      </button>
    </div>
  </div>

  </div><!-- /cars-grid -->
</div><!-- /page-wrap -->


<!-- ═══════════════════════════════════════════
     FILTER DRAWER
═══════════════════════════════════════════ -->
<div class="drawer-overlay" id="drawerOverlay"></div>
<div class="filter-drawer" id="filterDrawer" role="dialog" aria-modal="true" aria-label="Filters">

  <div class="drawer-head">
    <div class="drawer-head-left">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
        <line x1="4" y1="6" x2="20" y2="6"/><line x1="8" y1="12" x2="16" y2="12"/><line x1="11" y1="18" x2="13" y2="18"/>
      </svg>
      Filters
    </div>
    <div class="drawer-head-right">
      <button class="drawer-reset" id="drawerReset">Reset All</button>
      <button class="drawer-close" id="drawerClose" aria-label="Close">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
          <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
        </svg>
      </button>
    </div>
  </div>

  <div class="drawer-body">

    <!-- Brand -->
    <div class="df-section">
      <div class="df-label">Brand</div>
      <div class="df-checks" id="brandGroup">
        <label class="df-check"><input type="checkbox" value="livan"><span>Livan</span></label>
        <label class="df-check"><input type="checkbox" value="chery"><span>Chery</span></label>
        <label class="df-check"><input type="checkbox" value="mg"><span>MG</span></label>
        <label class="df-check"><input type="checkbox" value="byd"><span>BYD</span></label>
        <label class="df-check"><input type="checkbox" value="geely"><span>Geely</span></label>
        <label class="df-check"><input type="checkbox" value="haval"><span>Haval</span></label>
        <label class="df-check"><input type="checkbox" value="jac"><span>JAC</span></label>
        <label class="df-check"><input type="checkbox" value="changan"><span>Changan</span></label>
      </div>
    </div>

    <!-- Body Type -->
    <div class="df-section">
      <div class="df-label">Body Type</div>
      <div class="df-pills" id="bodyGroup">
        <button class="df-pill active" data-val="all">All</button>
        <button class="df-pill" data-val="suv">SUV</button>
        <button class="df-pill" data-val="sedan">Sedan</button>
        <button class="df-pill" data-val="electric">⚡ EV</button>
        <button class="df-pill" data-val="hybrid">🌿 Hybrid</button>
        <button class="df-pill" data-val="hatchback">Hatchback</button>
        <button class="df-pill" data-val="pickup">Pickup</button>
        <button class="df-pill" data-val="minivan">Minivan</button>
      </div>
    </div>

    <!-- Year -->
    <div class="df-section">
      <div class="df-label">Year</div>
      <div class="df-range">
        <div class="df-range-field"><label>From</label><input type="number" id="yearMin" placeholder="2018" min="2010" max="2026"></div>
        <span class="df-range-sep">—</span>
        <div class="df-range-field"><label>To</label><input type="number" id="yearMax" placeholder="2026" min="2010" max="2026"></div>
      </div>
    </div>

    <!-- Mileage -->
    <div class="df-section">
      <div class="df-label">Max Mileage (km)</div>
      <select class="df-select" id="mileageSel">
        <option value="">Any</option>
        <option value="0">Brand New (0 km)</option>
        <option value="10000">Under 10,000 km</option>
        <option value="30000">Under 30,000 km</option>
        <option value="50000">Under 50,000 km</option>
        <option value="100000">Under 100,000 km</option>
        <option value="200000">Under 200,000 km</option>
      </select>
    </div>

    <!-- Fuel Type -->
    <div class="df-section">
      <div class="df-label">Fuel Type</div>
      <div class="df-checks" id="fuelGroup">
        <label class="df-check"><input type="checkbox" value="essence"><span>Essence</span></label>
        <label class="df-check"><input type="checkbox" value="diesel"><span>Diesel</span></label>
        <label class="df-check"><input type="checkbox" value="electric"><span>Electric</span></label>
        <label class="df-check"><input type="checkbox" value="hybrid"><span>Hybrid</span></label>
        <label class="df-check"><input type="checkbox" value="plugin hybrid"><span>Plug-in Hybrid</span></label>
      </div>
    </div>

    <!-- Transmission -->
    <div class="df-section">
      <div class="df-label">Transmission</div>
      <div class="df-pills" id="transGroup">
        <button class="df-pill active" data-val="all">All</button>
        <button class="df-pill" data-val="automatic">Automatic</button>
        <button class="df-pill" data-val="manual">Manual</button>
        <button class="df-pill" data-val="cvt">CVT</button>
        <button class="df-pill" data-val="dct">DCT</button>
      </div>
    </div>

    <!-- Drive -->
    <div class="df-section">
      <div class="df-label">Drive</div>
      <div class="df-pills" id="driveGroup">
        <button class="df-pill active" data-val="all">All</button>
        <button class="df-pill" data-val="fwd">FWD</button>
        <button class="df-pill" data-val="rwd">RWD</button>
        <button class="df-pill" data-val="awd">AWD</button>
        <button class="df-pill" data-val="4wd">4WD</button>
      </div>
    </div>

    <!-- State -->
    <div class="df-section">
      <div class="df-label">Vehicle State</div>
      <div class="df-pills" id="stateGroup">
        <button class="df-pill active" data-val="all">All</button>
        <button class="df-pill" data-val="new">New</button>
        <button class="df-pill" data-val="used">Used</button>
      </div>
    </div>

    <!-- Exterior Color -->
    <div class="df-section">
      <div class="df-label">Exterior Color</div>
      <div class="df-colors" id="colorExtGroup">
        <button class="df-color active" data-val="all"><span class="swatch swatch-all"></span><span>All</span></button>
        <button class="df-color" data-val="white"><span class="swatch" style="background:#f0f0f0;border:1px solid #555"></span><span>White</span></button>
        <button class="df-color" data-val="black"><span class="swatch" style="background:#111"></span><span>Black</span></button>
        <button class="df-color" data-val="silver"><span class="swatch" style="background:#a8a8b3"></span><span>Silver</span></button>
        <button class="df-color" data-val="grey"><span class="swatch" style="background:#5a6272"></span><span>Grey</span></button>
        <button class="df-color" data-val="blue"><span class="swatch" style="background:#3b82f6"></span><span>Blue</span></button>
        <button class="df-color" data-val="red"><span class="swatch" style="background:#e63946"></span><span>Red</span></button>
        <button class="df-color" data-val="green"><span class="swatch" style="background:#16a34a"></span><span>Green</span></button>
        <button class="df-color" data-val="orange"><span class="swatch" style="background:#f97316"></span><span>Orange</span></button>
        <button class="df-color" data-val="brown"><span class="swatch" style="background:#7c4a1e"></span><span>Brown</span></button>
      </div>
    </div>

    <!-- Interior Color -->
    <div class="df-section">
      <div class="df-label">Interior Color</div>
      <div class="df-colors" id="colorIntGroup">
        <button class="df-color active" data-val="all"><span class="swatch swatch-all"></span><span>All</span></button>
        <button class="df-color" data-val="black"><span class="swatch" style="background:#111"></span><span>Black</span></button>
        <button class="df-color" data-val="beige"><span class="swatch" style="background:#d4b896"></span><span>Beige</span></button>
        <button class="df-color" data-val="grey"><span class="swatch" style="background:#5a6272"></span><span>Grey</span></button>
        <button class="df-color" data-val="white"><span class="swatch" style="background:#f0f0f0;border:1px solid #555"></span><span>White</span></button>
        <button class="df-color" data-val="brown"><span class="swatch" style="background:#7c4a1e"></span><span>Brown</span></button>
        <button class="df-color" data-val="red"><span class="swatch" style="background:#e63946"></span><span>Red</span></button>
      </div>
    </div>

    <!-- Price -->
    <div class="df-section">
      <div class="df-label">Price Range (DZD)</div>
      <div class="df-range">
        <div class="df-range-field"><label>Min</label><input type="number" id="priceMin" placeholder="0" step="100000"></div>
        <span class="df-range-sep">—</span>
        <div class="df-range-field"><label>Max</label><input type="number" id="priceMax" placeholder="Any" step="100000"></div>
      </div>
      <div class="df-price-presets">
        <button class="df-preset" data-min="0"       data-max="3000000">Under 3M</button>
        <button class="df-preset" data-min="3000000"  data-max="5000000">3M – 5M</button>
        <button class="df-preset" data-min="5000000"  data-max="99000000">5M+</button>
      </div>
    </div>

  </div><!-- /drawer-body -->

  <div class="drawer-foot">
    <button class="drawer-view-btn" id="drawerViewBtn">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
      View Offers
      <span class="drawer-count" id="drawerCount">0</span>
    </button>
  </div>

</div><!-- /filter-drawer -->


<!-- ═══════════════════════════════════════════
     ALERT MODAL
═══════════════════════════════════════════ -->
<div class="overlay hidden" id="alertOverlay">
  <div class="modal" id="alertModal">
    <button class="close-x" id="alertCloseX">✕</button>

    <div class="step active" id="step-details">
      <div class="m-head">
        <div class="bell-wrap">🔔</div>
        <h3 class="m-title">Create Search Alert</h3>
        <p class="m-sub">Choose how you want to be notified when a matching car appears.</p>
      </div>
      <div class="summary-box">
        <span class="summary-label">Alert for:</span>
        <div class="summary-tags" id="filterChips"></div>
      </div>
      <div style="margin-bottom:18px">
        <label class="label">Notify me via</label>
        <div class="channel-grid" id="channelGrid">
          <button class="channel-card" data-channel="whatsapp">
            <span class="ch-icon">
              <svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413zm-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
              </svg>
            </span>
            <span class="ch-label">WhatsApp</span>
            <span class="ch-desc">Message on WhatsApp</span>
          </button>
          <button class="channel-card" data-channel="email">
            <span class="ch-icon">
              <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8">
                <rect x="2" y="4" width="20" height="16" rx="2"/><path d="M2 7l10 7 10-7"/>
              </svg>
            </span>
            <span class="ch-label">Email</span>
            <span class="ch-desc">Send to your inbox</span>
          </button>
          <button class="channel-card" data-channel="site">
            <span class="ch-icon">
              <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/>
              </svg>
            </span>
            <span class="ch-label">Site Notification</span>
            <span class="ch-desc">Alert in your account</span>
          </button>
        </div>
        <p class="error-text hidden" id="channelError">Please select a notification method.</p>
      </div>
      <div class="field-group contact-field hidden" id="contactField">
        <label class="label" id="contactLabel">Contact</label>
        <input class="input" id="contactInput" type="text" placeholder=""/>
      </div>
      <div class="site-note hidden" id="siteNote">
        <span class="site-note-icon">🔔</span>
        <p class="site-note-text">You'll see a notification badge in your account the moment a matching car is listed.</p>
      </div>
      <div class="field-group">
        <label class="label">Notification frequency</label>
        <div class="freq-row">
          <button class="freq-btn active" data-freq="instant">Instant</button>
          <button class="freq-btn" data-freq="daily">Daily</button>
          <button class="freq-btn" data-freq="weekly">Weekly</button>
        </div>
      </div>
      <div class="actions">
        <button class="cancel-btn" id="cancelBtn1">Cancel</button>
        <button class="confirm-btn" id="createBtn">Create Alert →</button>
      </div>
    </div><!-- /step-details -->

    <div class="step" id="step-confirm">
      <div class="m-head">
        <div class="bell-wrap">📋</div>
        <h3 class="m-title">Confirm your alert</h3>
        <p class="m-sub">Review the details before activating.</p>
      </div>
      <div class="confirm-box">
        <div class="confirm-row"><span class="confirm-key">Filters</span><span class="confirm-val" id="confirmFilters">—</span></div>
        <div class="confirm-row"><span class="confirm-key">Channel</span><span class="confirm-val" id="confirmChannel">—</span></div>
        <div class="confirm-row"><span class="confirm-key">Contact</span><span class="confirm-val" id="confirmContact">—</span></div>
        <div class="confirm-row"><span class="confirm-key">Frequency</span><span class="confirm-val" id="confirmFreq">—</span></div>
      </div>
      <div class="actions">
        <button class="cancel-btn" id="backBtn">← Back</button>
        <button class="confirm-btn" id="activateBtn">Confirm & Activate</button>
      </div>
    </div><!-- /step-confirm -->

    <div class="step" id="step-success">
      <div class="success-panel">
        <div class="success-circle">✓</div>
        <h3 class="success-title">Alert Activated!</h3>
        <p class="success-text" id="successText"></p>
        <div class="summary-tags" id="successChips" style="justify-content:center;margin-bottom:16px"></div>
        <button class="done-btn" id="doneBtn">Done</button>
      </div>
    </div><!-- /step-success -->

  </div>
</div><!-- /alertOverlay -->
<?php include'footer.php'; ?>
<div class="toast" id="toast"></div>

<!-- Gallery nav (shared) -->
<script>
function galleryNav(btn, dir) {
  var gallery = btn.closest('.car-gallery');
  var imgs = gallery.querySelectorAll('.gallery-img');
  var dots = gallery.querySelectorAll('.gdot');
  var idx  = parseInt(gallery.dataset.index) || 0;
  imgs[idx].classList.remove('active');
  if (dots[idx]) dots[idx].classList.remove('active');
  idx = (idx + dir + imgs.length) % imgs.length;
  imgs[idx].classList.add('active');
  if (dots[idx]) dots[idx].classList.add('active');
  gallery.dataset.index = idx;
}

/* Clear all filters + reload page without params */
function clearAllFilters() {
  var base = window.location.pathname;
  window.location.href = base;
}
</script>

<script src="script.js"></script>
<script src="results.js?v=6"></script>
</body>
</html>