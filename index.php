<?php
session_start();
require_once 'config.php';
// Top 3 agents
$top3Stmt = $pdo->query("
    SELECT u.id, u.first_name, u.last_name, u.profile_photo,
           ROUND(AVG(ar.rating),1) AS avg_rating
    FROM users u
    JOIN agent_reviews ar ON ar.agent_id = u.id
    WHERE u.role = 'agent' AND u.status = 'approved'
    GROUP BY u.id
    HAVING avg_rating > 0
    ORDER BY avg_rating DESC
    LIMIT 3
");
$top3Agents = $top3Stmt->fetchAll(PDO::FETCH_ASSOC);
$agentCount = $pdo->query("
    SELECT COUNT(*) FROM users 
    WHERE role = 'agent' AND status = 'approved'
")->fetchColumn();
$showTrialBanner = false;
if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'agent') {
    $uStmt = $pdo->prepare("SELECT status, trial_used FROM users WHERE id = ?");
    $uStmt->execute([$_SESSION['user_id']]);
    $uRow = $uStmt->fetch();
    if ($uRow && $uRow['status'] === 'approved' && !$uRow['trial_used']) {
        $showTrialBanner = true;
    }
}
$stmt = $pdo->query("
    SELECT c.*,
           GROUP_CONCAT(p.photo_path ORDER BY p.id ASC SEPARATOR '|') AS photos,
           u.phone,
           u.first_name,
           u.last_name,
           ap.company_name
    FROM cars c
    LEFT JOIN car_photos p ON p.car_id = c.id
    LEFT JOIN users u ON u.id = c.agent_id
    LEFT JOIN agent_profiles ap ON ap.user_id = c.agent_id
    WHERE c.status = 'available'
    GROUP BY c.id
    ORDER BY c.id DESC
");
$cars = $stmt->fetchAll(PDO::FETCH_ASSOC);
$carsJson = json_encode(array_map(function($car) {
    return [
        'name'         => $car['title'],
        'brand'        => $car['brand'],
        'year'         => $car['year'],
        'price'        => number_format($car['price']) . ' DZD',
        'priceRaw'     => (int)$car['price'],
        'typeLabel'    => $car['body_type'] ?? '',
        'bodyType'     => $car['body_type'] ?? '',
        'fuel'         => $car['fuel_type'] ?? '',
        'wilaya'       => $car['wilaya'] ?? '',
        'kmRaw'        => (int)($car['mileage'] ?? 0),
        'transmission' => $car['transmission'] ?? '',
        'drive'        => $car['drive_type'] ?? '',
        'agentId'      => (int)$car['agent_id'],
        'seats'        => $car['seats'] ?? '',
        'colorExt'     => $car['color_ext'] ?? '',
        'colorInt'     => $car['color_int'] ?? '',
    ];
}, $cars));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>China2DZ — Chinese Cars in Algeria</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,400&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
<script src="theme.js"></script>
</head>
<body>
<?php if ($showTrialBanner): ?>
<div id="trialBanner" style="position:fixed;bottom:24px;left:24px;z-index:999;background:#141414;border:1px solid rgba(212,168,67,.4);border-radius:14px;padding:16px 18px;max-width:280px;box-shadow:0 8px 32px rgba(0,0,0,.6);">
    <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#d4a843" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        <span style="font-size:.78rem;color:#d4a843;font-weight:700;">Free Trial Available</span>
    </div>
    <p style="font-size:.78rem;color:rgba(255,255,255,.5);margin-bottom:14px;line-height:1.5;">Start your 7-day free trial to access the full dashboard.</p>
    <div style="display:flex;gap:8px;">
        <button onclick="claimTrial()" style="flex:1;display:flex;align-items:center;justify-content:center;gap:6px;padding:9px;background:rgba(212,168,67,.15);border:1px solid rgba(212,168,67,.4);border-radius:8px;color:#d4a843;font-size:.76rem;font-weight:700;cursor:pointer;font-family:inherit;">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            Start Trial
        </button>
        <button onclick="refuseTrial()" style="flex:1;display:flex;align-items:center;justify-content:center;gap:6px;padding:9px;background:rgba(231,76,60,.08);border:1px solid rgba(231,76,60,.3);border-radius:8px;color:#e74c3c;font-size:.76rem;font-weight:700;cursor:pointer;font-family:inherit;">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            Refuse
        </button>
    </div>
</div>

<div id="refuseOverlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.75);z-index:1000;align-items:center;justify-content:center;backdrop-filter:blur(6px);">
    <div style="background:#141414;border:1px solid rgba(255,255,255,.1);border-radius:16px;padding:32px 28px;max-width:340px;width:90%;text-align:center;">
        <div style="width:52px;height:52px;border-radius:50%;background:rgba(231,76,60,.1);border:1px solid rgba(231,76,60,.3);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#e74c3c" stroke-width="1.5"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        </div>
        <h3 style="color:#fff;font-size:1rem;font-weight:700;margin-bottom:8px;">Dashboard Locked</h3>
        <p style="color:rgba(255,255,255,.45);font-size:.8rem;line-height:1.6;margin-bottom:22px;">You refused the free trial. Subscribe to access the full dashboard.</p>
        <button onclick="goSubscribe()" style="width:100%;display:flex;align-items:center;justify-content:center;gap:8px;padding:12px;background:linear-gradient(135deg,#d4a843,#c4922f);border:none;border-radius:10px;color:#000;font-weight:700;font-size:.85rem;cursor:pointer;font-family:inherit;margin-bottom:10px;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
            View Subscription Plans
        </button>
        <button onclick="closeRefuse()" style="width:100%;display:flex;align-items:center;justify-content:center;gap:8px;padding:11px;background:none;border:1px solid rgba(255,255,255,.1);border-radius:10px;color:rgba(255,255,255,.35);font-size:.82rem;cursor:pointer;font-family:inherit;">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            Stay on Site
        </button>
    </div>
</div>

<script>
async function claimTrial() {
    const res = await fetch('start_trial.php', { method: 'POST' });
    const data = await res.json();
    if (data.success) {
        document.getElementById('trialBanner').style.display = 'none';
        window.location.href = 'agents.php';
    }
}
async function refuseTrial() {
    await fetch('refuse_trial.php', { method: 'POST' });
    document.getElementById('trialBanner').style.display = 'none';
    document.getElementById('refuseOverlay').style.display = 'flex';
}
function goSubscribe() {
    window.location.href = 'agents.php';
}
function closeRefuse() {
    document.getElementById('refuseOverlay').style.display = 'none';
}
</script>
<?php endif; ?>
<header id="mainHeader">
  <div class="logo">
    <a href="index.php"><span class="logo-china">China</span><span class="logo-2dz">2DZ</span></a>
  </div>
  <nav id="mainNav">
    <ul>
      <li><a href="#home">Home</a></li>
      <li><a href="#cars">Cars</a></li>
      <li><a href="#agents">Agents</a></li>
      <li><a href="#reviews">Reviews</a></li>
      <li><a href="javascript:void(0)" onclick="navWishlist()" class="nav-icon-link">
  Wishlist
  <span id="wishlistBadge" style="display:none;background:#d91f26;color:#fff;font-size:.68rem;padding:1px 6px;border-radius:10px;margin-right:4px;"></span>
</a></li>
<li><a href="javascript:void(0)" onclick="navnotifications()" class="nav-icon-link">
  Notifications
  <span id="alertsBadge" style="display:none;background:#d91f26;color:#fff;font-size:.68rem;padding:1px 6px;border-radius:10px;margin-right:4px;"></span>
</a></li>
<li id="nav-login-li">
  <a href="login.html" class="login-btn">Sign In</a>
</li>
<li id="nav-profile-li" style="display:none; position:relative;">
  <button id="navProfileBtn" onclick="toggleProfileDropdown()" style="background:none;border:1px solid rgba(255,255,255,0.12);cursor:pointer;display:flex;align-items:center;gap:8px;padding:6px 12px;border-radius:20px;">
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
  <a href="check_dashboard.php" id="agentDashLink" style="display:none;align-items:center;gap:10px;padding:12px 16px;color:rgba(255,255,255,0.8);text-decoration:none;font-size:.85rem;border-bottom:1px solid rgba(255,255,255,0.06);">
  🏢 My Dashboard
</a>
<a href="profile.php" style="display:flex;align-items:center;gap:10px;padding:12px 16px;color:rgba(255,255,255,0.8);text-decoration:none;font-size:.85rem;border-bottom:1px solid rgba(255,255,255,0.06);">
  👤 My Profile
</a>
    <a href="profile.php?tab=favorites" style="display:flex;align-items:center;gap:10px;padding:12px 16px;color:rgba(255,255,255,0.8);text-decoration:none;font-size:.85rem;border-bottom:1px solid rgba(255,255,255,0.06);">
      ❤️ Favorites
      <span id="ndFavBadge" style="display:none;margin-right:auto;background:#d91f26;color:#fff;font-size:.68rem;padding:1px 7px;border-radius:10px;"></span>
    </a>
    <a href="profile.php?tab=notifications" style="display:flex;align-items:center;gap:10px;padding:12px 16px;color:rgba(255,255,255,0.8);text-decoration:none;font-size:.85rem;border-bottom:1px solid rgba(255,255,255,0.06);">
      🔔 Notifications
      <span id="ndNotifBadge" style="display:none;margin-right:auto;background:#d91f26;color:#fff;font-size:.68rem;padding:1px 7px;border-radius:10px;"></span>
    </a>
    <button onclick="doLogout()" style="display:flex;align-items:center;gap:10px;padding:12px 16px;color:#ff5057;font-size:.85rem;background:none;border:none;cursor:pointer;width:100%;font-family:inherit;">
      🚪 Logout
    </button>
  </div>
</li>
<button id="themeBtn" onclick="toggleTheme()" 
  style="background:none;border:1px solid rgba(255,255,255,0.2);
  color:#fff;border-radius:50%;width:36px;height:36px;
  cursor:pointer;font-size:16px;">🌙</button>
    </ul>
  </nav>
  <div class="hamburger" id="hamburger"><span></span><span></span><span></span></div>
</header>

<!-- HERO -->
<section id="home" class="hero">
  <div class="hero-overlay"></div>
  <div class="hero-bg-pattern"></div>
  <img src="uploads/photo_2026-03-06_15-44-58.jpg" alt="Hero Car" class="hero-img" onerror="this.style.display='none'">
  <div class="hero-content">
    <div class="hero-badge">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
      #1 Chinese Car Platform in Algeria
    </div>
    <h1>Find Your Perfect <span class="highlight">Chinese Car</span></h1>
    <p class="hero-sub">Best deals on Chinese cars in Algeria — Prices in DZD, duty-free, delivered nationwide</p>

    <div class="search-wrapper">
      <div class="search-box">
        <svg class="search-icon" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
        <input type="text" id="searchInput" placeholder="search by brand or model">
        <button class="search-btn" id="searchBtn">Search</button>
      </div>
      <div class="search-dropdown" id="searchDropdown">
        <div id="dropdownContent"></div>
        <div class="no-results" id="noResults" style="display:none;">
          <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
          <p>No results found</p>
        </div>
      </div>
    </div>

    <!-- FILTER TRIGGER -->
    <div class="hero-filter-trigger-wrap">
      <button class="hero-filter-trigger-btn" id="filterTriggerBtn" onclick="toggleFilterPanel()">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="4" y1="6" x2="20" y2="6"/><line x1="8" y1="12" x2="16" y2="12"/><line x1="11" y1="18" x2="13" y2="18"/></svg>
        Advanced Filters
        <span class="filter-badge" id="filterTriggerBadge" style="display:none">0</span>
      </button>
    </div>
    <div class="hero-stats">
      <div class="stat"><span class="stat-num" id="stat-cars">...</span><span class="stat-label">Cars Listed</span></div>
      <div class="stat-divider"></div>
      <div class="stat"><span class="stat-num" id="stat-agents">...</span><span class="stat-label">Verified Agents</span></div>
      <div class="stat-divider"></div>
      <div class="stat"><span class="stat-num" id="stat-wilayas">...</span><span class="stat-label">Wilayas Covered</span></div>
    </div>
  </div>
</section>

<!-- INLINE FILTER PANEL -->
<div id="filterPanelContainer"></div>

<!-- OFFERS BUTTON (below filter panel) -->
<div class="offers-btn-wrap" id="offersBtnWrap" style="display:none;">
  <button class="offers-go-btn" id="offersGoBtn">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
    View Offers
    <span id="offersCountBadge" class="offers-count-badge">0</span>
    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
  </button>
  <button class="offers-reset-btn" id="offersResetBtn">
    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-4.92"/></svg>
    Reset
  </button>
</div>


<!-- CARS -->
<section id="cars">
  <div class="section-header">
    <h2 class="section-title">Available Cars</h2>
    <p class="section-subtitle">Prices in DZD — Duty-free — Delivered anywhere in Algeria</p>
  </div>
  <div id="carsGrid" class="cars-container">
    <?php if (empty($cars)): ?>
    <div style="text-align:center; padding:60px; color:#888;">
      <p>No cars available yet.</p>
    </div>
  <?php else: foreach ($cars as $car):
      $photos = $car['photos'] ? explode('|', $car['photos']) : [];
      $firstPhoto = $photos[0] ? 'http://localhost/' . $photos[0] : null;
      $brand = htmlspecialchars($car['brand'] ?? '');
      $title = htmlspecialchars($car['title'] ?? '');
      $year  = htmlspecialchars($car['year'] ?? '');
      $price = number_format($car['price']);
  ?>
    <?php
$resStatus = $car['reservation_status'] ?? 'available';
$reservedUntil = $car['reserved_until'] ?? null;
$isReserved = $resStatus === 'reserved' && $reservedUntil && strtotime($reservedUntil) > time();?>
<div class="car-card <?= $isReserved ? 'car-reserved' : '' ?>" data-brand="<?= $brand ?>" data-id="<?= $car['id'] ?>"onclick="window.location.href='listing.html?id=<?= $car['id'] ?>'" style="cursor:pointer;">
      <div class="car-img-wrapper" style="position:relative;">
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
          <img src="<?= $firstPhoto ? htmlspecialchars($firstPhoto): '' ?>"
               alt="<?= $title ?>"
               onerror="this.src='https://images.unsplash.com/photo-1549317661-bd32c8ce0db2?w=400'">
        <?php endif; ?>
        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $car['agent_id']): ?>
<div class="car-options-wrap" onclick="event.stopPropagation()">
  <button class="car-options-btn" onclick="toggleCarMenu(this)">⋮</button>
  <div class="car-options-menu">
    <button onclick="openEditModal(<?= $car['id'] ?>)">✏️ Edit</button>
    <button class="danger" onclick="deleteCar(<?= $car['id'] ?>, this)">🗑️ Delete</button>
  </div>
</div>
<?php endif; ?>
        <a href="javascript:void(0)" 
   class="favorite-btn" 
   id="fav-<?= $car['id'] ?>" onclick="event.stopPropagation(); toggleFavorite(<?= $car['id'] ?>, '<?= addslashes($title) ?>', '<?= addslashes($firstPhoto ?? '') ?>', '<?= $price ?>')">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
          </svg>
        </a>
      </div>
<div class="car-info">
    <div class="car-brand-tag"><?= $brand ?></div>
    <h3><?= $title ?></h3>
    <div class="car-quick-specs">
        <span><?= $year ?></span>
    </div>

    <?php if (!empty($car['description'])): ?>
    <div class="car-desc"><?= htmlspecialchars($car['description']) ?></div>
    <?php endif; ?>

    <div class="car-agent-info">
        <div class="agent-name">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            <?= htmlspecialchars($car['first_name'] . ' ' . $car['last_name']) ?>
        </div>
        <?php if (!empty($car['company_name'])): ?>
        <div class="agent-company">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
            <?= htmlspecialchars($car['company_name']) ?>
        </div>
        <?php endif; ?>
        <div class="agent-phone">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13.1 19.79 19.79 0 0 1 1.65 4.59 2 2 0 0 1 3.62 2.43h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l.81-.81a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 21.73 17z"/></svg>
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
  <a href="login.html" class="contact-agent-btn">Contact →</a>
  <?php endif; ?>
  <a href="listing.html?id=<?= $car['id'] ?>" class="more-details-btn">
    Details
    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
  </a>
</div>
</div>
    </div>
  <?php endforeach; endif; ?>
  </div>
</section>

<!-- AGENTS -->
<section id="agents">
  <div class="section-header">
    <h2 class="section-title light">Verified Agents</h2>
    <p class="section-subtitle light">Connect with certified Chinese car Agents across Algeria</p>
  </div>
  <div class="agents-container">
    <div class="agents-text">
      <div class="agents-icon-wrap">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      </div>
      <h2><?= $agentCount ?>+ Verified Agents</h2>
      <p>Our certified dealer network covers all 58 wilayas, offering you the best selection of Chinese cars with full after-sales support.</p>
      <ul class="agents-features">
        <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> Official authorised importers</li>
        <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> Manufacturer warranty included</li>
        <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> After-sales & spare parts service</li>
        <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> Nationwide delivery available</li>
      </ul>
      <a href="dealers.php" class="browse-btn">Browse All Agents <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
    </div>
    <div class="agents-images">
  <?php foreach($top3Agents as $i => $t):
    $fn = trim($t['first_name'].' '.$t['last_name']);
    $ini = mb_strtoupper(mb_substr($t['first_name'],0,1).mb_substr($t['last_name'],0,1));
  ?>
  <div class="agent-card-mini <?= $i===1?'featured':'' ?>">
    <?php if(!empty($t['profile_photo'])): ?>
      <img src="<?= htmlspecialchars($t['profile_photo']) ?>" alt="<?= htmlspecialchars($fn) ?>">
    <?php else: ?>
      <div style="width:60px;height:60px;border-radius:50%;background:linear-gradient(135deg,#1e40af,#0e7490);display:flex;align-items:center;justify-content:center;font-weight:700;color:#fff;font-size:1.1rem;margin:0 auto 8px;"><?= $ini ?></div>
    <?php endif; ?>
    <span><?= htmlspecialchars($fn) ?></span>
    <small>⭐ <?= number_format($t['avg_rating'],1) ?></small>
  </div>
  <?php endforeach; ?>
</div>
</section>

<!-- ============================================================
     REVIEWS — Real DB System
     ============================================================ -->
<section id="reviews">
  <div class="section-header">
    <h2 class="section-title">Customer Reviews</h2>
    <p class="section-subtitle">What our customers say about their Chinese car experience</p>
  </div>

  <!-- نموذج إضافة تعليق — للمسجلين فقط -->
  <?php if (isset($_SESSION['user_id'])): ?>
  <div class="add-review-form-wrap">
    <button class="add-review-btn" id="addReviewMainBtn">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Add a Review
    </button>
    <div id="reviewFormBox" style="display:none; background:#f5f5f7; border:1.5px solid #e4e4e7; border-radius:16px; padding:24px; margin-top:18px; text-align:left;">
  <p style="font-weight:700; color:#1a1a2e; font-size:13.5px; margin-bottom:12px;">Your Rating</p>
  <div id="starInput" style="display:flex; gap:4px; margin-bottom:12px;">
    <span data-val="1" style="font-size:2rem; cursor:pointer; color:#ccc;">★</span>
    <span data-val="2" style="font-size:2rem; cursor:pointer; color:#ccc;">★</span>
    <span data-val="3" style="font-size:2rem; cursor:pointer; color:#ccc;">★</span>
    <span data-val="4" style="font-size:2rem; cursor:pointer; color:#ccc;">★</span>
    <span data-val="5" style="font-size:2rem; cursor:pointer; color:#ccc;">★</span>
  </div>
  <input type="hidden" id="ratingVal" value="0">
  <textarea id="reviewContent" placeholder="Share your experience with China2DZ..." rows="4" style="width:100%; min-height:100px; padding:12px; border:1.5px solid #e4e4e7; border-radius:10px; font-family:inherit; font-size:13.5px; resize:vertical; outline:none; display:block; box-sizing:border-box; color:#1a1a2e; background:#fff;"></textarea>
  <button onclick="submitReview()" style="margin-top:14px; background:#0d0d0d; color:#fff; border:none; padding:11px 28px; border-radius:50px; cursor:pointer; font-weight:700; font-size:13.5px; font-family:inherit;">Submit Review</button>
</div>
  </div>
  <?php else: ?>
  <div style="text-align:center;margin-bottom:20px;">
    <a href="login.html" class="add-review-btn">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Sign in to Add a Review
    </a>
  </div>
  <?php endif; ?>

  <!-- قائمة التعليقات -->
  <div class="reviews-container" id="reviewsList"></div>

  <!-- زر تحميل المزيد -->
  <div class="reviews-more-wrap" id="moreWrap" style="display:none;">
    <button class="more-reviews-btn" id="moreBtn" onclick="toggleAllReviews()">
      💬 <span id="reviewsCountLabel">ALL Reviews</span>
    </button>
  </div>

  <!-- رسالة لا توجد تعليقات -->
  <div id="noReviewsMsg" style="display:none;">
    No reviews yet. Be the first to share your experience!
  </div>
</section>
<?php include 'footer.php'; ?>
<div id="toast" class="toast"></div>
<script src="script.js"></script>
<script>
ALL_CARS = <?= $carsJson ?>;
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  initCommon();
  initNav();

  buildFilterPanel('filterPanelContainer');

  var inp = document.getElementById('searchInput');
  var btn = document.getElementById('searchBtn');
  var dd  = document.getElementById('searchDropdown');
  var content = document.getElementById('dropdownContent');
  var noRes   = document.getElementById('noResults');
  if (inp) {
    inp.addEventListener('input', function() {
      var q = this.value.trim();
      if (!q) { dd.classList.remove('active'); return; }
      var hits = ALL_CARS.filter(function(c){ return fuzzyMatch(c, q); });
      dd.classList.add('active');
      if (!hits.length) { content.innerHTML = ''; noRes.style.display = 'flex'; }
      else {
        noRes.style.display = 'none';
        var carSvg = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13" rx="1"/><path d="M16 8l4 2v5h-4z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>';
        content.innerHTML = hits.slice(0, 8).map(function(c) {
          return '<div class="d-item" data-name="' + c.name + '">'
            + '<div class="d-icon">' + carSvg + '</div>'
            + '<div class="d-info"><strong>' + c.name + '</strong>'
            + '<small>' + c.brand + ' · ' + c.year + ' · ' + c.typeLabel + ' · ' + c.price + '</small></div>'
            + '<span class="d-tag">View</span></div>';
        }).join('');
        content.querySelectorAll('.d-item').forEach(function(el){
          el.addEventListener('click', function(){ goSearch(this.dataset.name); });
        });
      }
    });
    inp.addEventListener('keydown', function(e){
      if (e.key === 'Enter') goSearch(this.value);
      if (e.key === 'Escape') { dd.classList.remove('active'); this.value = ''; }
    });
  }
  if (btn) btn.addEventListener('click', function(){ goSearch(inp ? inp.value : ''); });
  document.addEventListener('click', function(e){
    var wrap = document.querySelector('.search-wrapper');
    if (wrap && !wrap.contains(e.target)) dd.classList.remove('active');
  });

  var pills = document.querySelectorAll('.filter-pill');
  var cards = document.querySelectorAll('.car-card');
  pills.forEach(function(pill){
    pill.addEventListener('click', function(){
      pills.forEach(function(p){ p.classList.remove('active'); });
      this.classList.add('active');
      var f = this.dataset.filter;
      cards.forEach(function(card){
        var b = (card.dataset.brand || '').toLowerCase();
        var t = (card.dataset.type  || '').toLowerCase();
        var show = f === 'all' ? true : f === 'electric' ? t === 'electric' : b === f.toLowerCase();
        card.style.display = show ? '' : 'none';
      });
    });
  });

  /* Offers button logic */
  window.onFiltersApplied = function() { updateOffersButton(); };

  function updateOffersButton() {
    var n = countActiveFilters(window.activeFilters);
    var wrap = document.getElementById('offersBtnWrap');
    var badge = document.getElementById('offersCountBadge');
    if (!wrap) return;
    if (n > 0) {
      wrap.style.display = 'flex';
      var results = filterCars(ALL_CARS, window.activeFilters, '');
      if (badge) badge.textContent = results.length + ' car' + (results.length !== 1 ? 's' : '');
    } else {
      wrap.style.display = 'none';
    }
  }

  var offersBtn = document.getElementById('offersGoBtn');
  if (offersBtn) {
    offersBtn.addEventListener('click', function(){
      var url = 'results.php?' + serializeFiltersToURL(window.activeFilters, inp ? inp.value : '');
      window.location.href = url;
    });
  }

  var offersResetBtn = document.getElementById('offersResetBtn');
  if (offersResetBtn) {
    offersResetBtn.addEventListener('click', function(){
      resetActiveFilters();
      syncSelectBoxes();
      updateSfpCount();
      updateOffersButton();
      toggleFilterPanel(false);
    });
  }

  /* ── تحميل التعليقات عند فتح الصفحة ── */
loadReviews(true);

var addRevBtn = document.querySelector('#addReviewMainBtn');
if (addRevBtn) {
  addRevBtn.addEventListener('click', function() {
    toggleAddForm();
    setTimeout(initStarInput, 50);
  });
}

});
</script>

<!-- ============================================================
     GALLERY NAV
     ============================================================ -->
<script>
function galleryNav(btn, dir) {
  var gallery = btn.closest('.car-gallery');
  var imgs = gallery.querySelectorAll('.gallery-img');
  var dots = gallery.querySelectorAll('.gdot');
  var idx = parseInt(gallery.dataset.index) || 0;
  imgs[idx].classList.remove('active');
  dots[idx].classList.remove('active');
  idx = (idx + dir + imgs.length) % imgs.length;
  imgs[idx].classList.add('active');
  dots[idx].classList.add('active');
  gallery.dataset.index = idx;
}
</script>

<!-- ============================================================
     CHAT
     ============================================================ -->
<script>
async function startChat(agentId, carName, carId) {
  if (checkBlocked()) { showBlockedToast('Account suspended — you cannot contact agents.'); return; }
  var token = localStorage.getItem('c2dz_token');
  if (!token) { window.location.href = 'login.html'; return; }
  try {
    var res = await fetch('api.php?action=start_conversation', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token },
      body: JSON.stringify({ agent_id: parseInt(agentId), car_id: String(carId), car_name: carName })
    });
    var data = JSON.parse(await res.text());
    if (data.success) {
      window.location.href = 'profile.php?tab=chat&conv=' + data.data.conversation_id;
    } else { alert(data.message); }
  } catch(e) { alert('Error: ' + e.message); }
}
</script>

<!-- ============================================================
     REVIEWS SYSTEM
     ============================================================ -->
<script>
var reviewsPage  = 1;
var isLoggedIn   = <?= isset($_SESSION['user_id']) ? 'true' : 'false' ?>;

/* ── Escape HTML ── */
function escHtml(str) {
  return String(str || '')
    .replace(/&/g,'&amp;').replace(/</g,'&lt;')
    .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

/* ── Build star string ── */
function buildStars(rating) {
  var s = '';
  for (var i = 1; i <= 5; i++) s += (i <= rating) ? '★' : '☆';
  return s;
}

/* ── Build one review card HTML ── */
 var CURRENT_USER_ID = <?= isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0 ?>;
function buildReviewCard(r) {
  var name  = escHtml((r.first_name || '') + ' ' + (r.last_name || ''));
  var liked = parseInt(r.user_liked) === 1;
  var avatar = r.profile_photo
    ? '<img src="' + escHtml(r.profile_photo) + '" style="width:44px;height:44px;border-radius:50%;object-fit:cover;border:2px solid #00bcd4;">'
    : '<div style="width:44px;height:44px;border-radius:50%;background:#00bcd4;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:1rem;flex-shrink:0;">' + escHtml(String(r.first_name||'').charAt(0).toUpperCase()) + '</div>';

  var repliesHtml = '';
  if (r.replies && r.replies.length > 0) {
    repliesHtml = r.replies.map(function(rep) {
      var repOwner = rep.user_id && CURRENT_USER_ID == rep.user_id;
      var repAvatar = rep.profile_photo
        ? '<img src="' + escHtml(rep.profile_photo) + '" style="width:28px;height:28px;border-radius:50%;object-fit:cover;border:1.5px solid #00bcd4;flex-shrink:0;">'
        : '<div style="width:28px;height:28px;border-radius:50%;background:#00bcd4;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:11px;flex-shrink:0;">' + escHtml(String(rep.first_name||'').charAt(0).toUpperCase()) + '</div>';
      var repBtns = repOwner
        ? '<span style="margin-left:auto;display:flex;gap:6px;">'
          + '<button onclick="deleteReply(' + rep.id + ',this)" style="background:none;border:none;color:#e53e3e;cursor:pointer;font-size:11px;font-weight:600;font-family:inherit;">🗑</button>'
          + '<button onclick="editReply(' + rep.id + ',this)" style="background:none;border:none;color:#6b7280;cursor:pointer;font-size:11px;font-weight:600;font-family:inherit;">✏️</button>'
          + '</span>'
        : '';
    return '<div class="reply-item" id="replyItem_' + rep.id + '" style="display:flex;align-items:center;gap:8px;">'
        + repAvatar
        + '<div style="flex:1;">'
          + '<strong style="font-size:12px;">' + escHtml((rep.first_name||'') + ' ' + (rep.last_name||'')) + '</strong>'
          + '<div id="replyText_' + rep.id + '" style="font-size:12px;color:#6b7280;">' + escHtml(rep.content) + '</div>'
        + '</div>'
        + repBtns
        + '</div>';
    }).join('');
  }

  var replyForm = isLoggedIn
    ? '<div class="reply-input-row" style="margin-top:10px;">'
        + '<input type="text" placeholder="Write a reply..." id="replyInp_' + r.id + '">'
        + '<button onclick="submitReply(' + r.id + ')">Reply</button>'
      + '</div>'
    : '';

  var date = r.created_at ? r.created_at.substring(0,10) : ''
  var isOwner = (r.uid && CURRENT_USER_ID == r.uid);
var ownerBtns = isOwner
  ? '<button onclick="deleteReview(' + r.id + ')" style="background:none;border:none;color:#e53e3e;cursor:pointer;font-size:12px;font-weight:600;font-family:inherit;padding:0 8px;">🗑 Delete</button>'
  + '<button onclick="editReview(' + r.id + ')" style="background:none;border:none;color:#6b7280;cursor:pointer;font-size:12px;font-weight:600;font-family:inherit;padding:0 8px;">✏️ Edit</button>'
  : '';

  return '<div class="review-card" id="rev_' + r.id + '">'
    + '<div class="review-header">'
      + '<a href="profile.php?id=' + r.uid + '" style="display:flex;align-items:center;gap:10px;text-decoration:none;">'
        + avatar
        + '<div>'
          + '<h3 style="margin:0;">' + name + '</h3>'
          + '<div class="review-stars-display">' + buildStars(r.rating) + '</div>'
        + '</div>'
      + '</a>'
      + '<span style="margin-left:auto;font-size:11px;color:#999;display:flex;align-items:center;gap:6px;">' + ownerBtns + date + '</span>'
    + '</div>'
    + '<p class="review-body">' + escHtml(r.content) + '</p>'
    + '<div style="display:flex;align-items:center;gap:10px;">'
      + '<button class="heart-btn' + (liked ? ' liked' : '') + '" id="likeBtn_' + r.id + '" onclick="toggleLike(' + r.id + ')">'
        + '❤ <span id="likeCount_' + r.id + '">' + (r.likes || 0) + '</span>'
      + '</button>'
      + '<button onclick="toggleReplies(' + r.id + ')" style="background:none;border:1.5px solid #e4e4e7;padding:6px 13px;border-radius:20px;font-size:12px;font-weight:600;color:#6b7280;cursor:pointer;font-family:inherit;">'
        + '💬 Replies (' + (r.replies ? r.replies.length : 0) + ')'
      + '</button>'
      + '<button onclick="openReportComment(\'index_review\',' + r.id + ',\'' + escHtml(r.content||'').substring(0,80).replace(/'/g,"\\'") + '\')" style="background:none;border:1px solid rgba(239,68,68,0.2);color:rgba(239,68,68,0.6);font-size:.72rem;padding:5px 10px;border-radius:6px;cursor:pointer;font-family:inherit;margin-left:4px;">⚑ Report</button>'
    + '</div>'
    + '<div id="repliesBox_' + r.id + '" style="display:none;margin-top:10px;">'
      + (repliesHtml ? '<div class="review-reply-box">' + repliesHtml + '</div>' : '')
      + replyForm
    + '</div>'
  + '</div>';
}
function toggleReplies(reviewId) {
  var box = document.getElementById('repliesBox_' + reviewId);
  if (!box) return;
  box.style.display = box.style.display === 'none' ? 'block' : 'none';
}
var _reviewsExpanded = false;
function toggleAllReviews() {
  var list  = document.getElementById('reviewsList');
  var label = document.getElementById('reviewsCountLabel');

  if (!_reviewsExpanded) {
    _reviewsExpanded = true;
    if (label) label.textContent = 'Hide Comments (' + (window._totalReviews || '') + ')';

    function loadAll() {
      if (window._allLoaded) return;
      fetch('reviews_api.php?action=get&page=' + reviewsPage, { credentials: 'include' })
        .then(function(r) { return r.json(); })
        .then(function(data) {
          data.reviews.forEach(function(r) {
            list.insertAdjacentHTML('beforeend', buildReviewCard(r));
          });
          reviewsPage++;
          window._allLoaded = !data.has_more;
          if (!window._allLoaded) loadAll();
        });
    }
    loadAll();

  } else {
    _reviewsExpanded = false;
    reviewsPage = 1;
    window._allLoaded = false;
    loadReviews(true);
  }
}
function deleteReview(reviewId) {
  if (!confirm('Delete this review?')) return;
  var fd = new FormData();
  fd.append('review_id', reviewId);
  fetch('reviews_api.php?action=delete', { method: 'POST', body: fd })
    .then(function(r) { return r.json(); })
    .then(function(data) {
      if (data.success) {
        var el = document.getElementById('rev_' + reviewId);
        if (el) {
        el.remove();
        var label = document.getElementById('reviewsCountLabel');
        if (label) {
          if (window._totalReviews) window._totalReviews--;
          var t = window._totalReviews || 0;
          label.textContent = (_reviewsExpanded ? 'Hide Comments' : 'Comments') + ' (' + t + ')';
        }
      }
      } else { alert(data.message || 'Error'); }
    });
}

function editReview(reviewId) {
  var card = document.getElementById('rev_' + reviewId);
  if (!card) return;
  var body = card.querySelector('.review-body');
  var currentText = body ? body.textContent : '';
  var newText = prompt('Edit your review:', currentText);
  if (!newText || newText.trim() === currentText.trim()) return;
  fetch('reviews_api.php?action=edit', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ review_id: reviewId, content: newText.trim(), rating: 5 })
  })
  .then(function(r) { return r.json(); })
  .then(function(data) {
    if (data.success) {
      if (body) body.textContent = newText.trim();
    } else { alert(data.message || 'Error'); }
  });
}
function deleteReply(replyId, btn) {
  if (!confirm('Delete this reply?')) return;
  var fd = new FormData();
  fd.append('reply_id', replyId);
  fetch('reviews_api.php?action=delete_reply', { method:'POST', body:fd })
    .then(function(r) { return r.json(); })
    .then(function(data) {
      if (data.success) {
        var el = document.getElementById('replyItem_' + replyId);
        if (el) el.remove();
      } else { alert(data.message || 'Error'); }
    });
}

function editReply(replyId, btn) {
  var textEl = document.getElementById('replyText_' + replyId);
  if (!textEl) return;
  var newText = prompt('Edit reply:', textEl.textContent);
  if (!newText || newText.trim() === textEl.textContent.trim()) return;
  fetch('reviews_api.php?action=edit_reply', {
    method:'POST',
    headers:{'Content-Type':'application/json'},
    body: JSON.stringify({ reply_id: replyId, content: newText.trim() })
  })
  .then(function(r) { return r.json(); })
  .then(function(data) {
    if (data.success) { textEl.textContent = newText.trim(); }
    else { alert(data.message || 'Error'); }
  });
}
/* ── Load reviews from API ── */
function loadReviews(reset) {
  if (reset) {
    reviewsPage = 1;
    document.getElementById('reviewsList').innerHTML = '';
  }

  var btn = document.getElementById('moreBtn');
  if (btn) btn.innerHTML = '💬 <span id="reviewsCountLabel">All Reviews</span>';

  fetch('reviews_api.php?action=get&page=' + reviewsPage, { credentials: 'include' })
    .then(function(r) { return r.json(); })
    .then(function(data) {
      if (!data.success) return;

      var list     = document.getElementById('reviewsList');
      var noMsg    = document.getElementById('noReviewsMsg');
      var moreWrap = document.getElementById('moreWrap');

      if (data.total === 0) {
        noMsg.style.display = 'block';
        moreWrap.style.display = 'none';
        return;
      }
      noMsg.style.display = 'none';

      data.reviews.forEach(function(r) {
        list.insertAdjacentHTML('beforeend', buildReviewCard(r));
      });

      reviewsPage++;
      moreWrap.style.display = 'flex';
      var label = document.getElementById('reviewsCountLabel');
      if (label) label.textContent = 'ALL Reviews (' + data.total + ')';
      window._totalReviews = data.total;
      window._allLoaded = !data.has_more;
    })
    .catch(function() {
      if (btn) btn.textContent = 'Load More Reviews';
    });
}

/* ── Toggle like ── */
function toggleLike(reviewId) {
  if (checkBlocked()) { showBlockedToast('Account suspended — likes are disabled.'); return; }
  if (!isLoggedIn) { window.location.href = 'login.html'; return; }
  var fd = new FormData();
  fd.append('review_id', reviewId);
  fd.append('token', localStorage.getItem('c2dz_token') || '');
  fetch('reviews_api.php?action=like', {
    method: 'POST', body: fd, credentials: 'include'
  })
  .then(function(r) { return r.json(); })
  .then(function(data) {
    if (!data.success) return;
    var btn = document.getElementById('likeBtn_' + reviewId);
    var cnt = document.getElementById('likeCount_' + reviewId);
    if (data.liked) btn.classList.add('liked');
    else btn.classList.remove('liked');
    cnt.textContent = data.count;
  });
}

/* ── Submit reply ── */
function submitReply(reviewId) {
  if (checkBlocked()) { showBlockedToast('Account suspended — replies are disabled.'); return; }
  if (!isLoggedIn) { window.location.href = 'login.html'; return; }
  var inp = document.getElementById('replyInp_' + reviewId);
  var content = inp ? inp.value.trim() : '';
  if (!content) return;

  fetch('reviews_api.php?action=reply', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ 
      review_id: reviewId, 
      content: content,
      token: localStorage.getItem('c2dz_token') || ''
    })
  })
  .then(function(r) { return r.json(); })
  .then(function(data) {
    if (!data.success) return;
    inp.value = '';
    var box = document.getElementById('repliesBox_' + reviewId);
    var newItem = '<div class="reply-item"><strong>' + escHtml(data.name) + ':</strong> ' + escHtml(data.content) + '</div>';
    var existBox = box.querySelector('.review-reply-box');
    if (existBox) existBox.insertAdjacentHTML('beforeend', newItem);
    else box.innerHTML = '<div class="review-reply-box">' + newItem + '</div>';
  });
}


/* ── Toggle add review form ── */
function toggleAddForm() {
  var box = document.getElementById('reviewFormBox');
  if (!box) return;
  box.style.display = (box.style.display === 'none' || box.style.display === '') ? 'block' : 'none';
}

/* ── Star input init ── */
function initStarInput() {
  var stars = document.querySelectorAll('#starInput span');
  if (!stars.length) return;
  stars.forEach(function(star) {
    star.addEventListener('click', function() {
      var val = parseInt(this.dataset.val);
      document.getElementById('ratingVal').value = val;
      stars.forEach(function(s) {
        s.style.color = parseInt(s.dataset.val) <= val ? '#f5a623' : '#ccc';
      });
    });
    star.addEventListener('mouseover', function() {
      var val = parseInt(this.dataset.val);
      stars.forEach(function(s) {
        s.style.color = parseInt(s.dataset.val) <= val ? '#f5a623' : '#ccc';
      });
    });
    star.addEventListener('mouseout', function() {
      var chosen = parseInt(document.getElementById('ratingVal').value) || 0;
      stars.forEach(function(s) {
        s.style.color = parseInt(s.dataset.val) <= chosen ? '#f5a623' : '#ccc';
      });
    });
  });
}

/* ── Submit review ── */
function submitReview() {
  if (checkBlocked()) { showBlockedToast('Account suspended — reviews are disabled.'); return; }
  var rating  = parseInt(document.getElementById('ratingVal').value || '0');
  var content = (document.getElementById('reviewContent').value || '').trim();
  if (rating < 1) { alert('Please select a rating (1-5 stars)'); return; }
  if (!content)   { alert('Please write your review'); return; }

  fetch('reviews_api.php?action=add', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ rating: rating, content: content })
  })
  .then(function(r) { return r.json(); })
  .then(function(data) {
    if (data.success) {
      document.getElementById('reviewFormBox').style.display = 'none';
      document.getElementById('reviewContent').value = '';
      document.getElementById('ratingVal').value = '0';
      document.querySelectorAll('#starInput span').forEach(function(s){ s.classList.remove('active'); s.style.color = ''; });
      loadReviews(true);
    } else {
      alert(data.message || 'Error submitting review');
    }
  })
  .catch(function() { alert('Connection error. Please try again.'); });
}
</script>
<script>
window.addEventListener('load', function() {
    var hash = window.location.hash;
    var params = new URLSearchParams(window.location.search);
    var scrollReply = params.get('scrollreply');
    
    if (hash && hash.startsWith('#rev_')) {
        var id = hash.replace('#rev_', '');
        var tryScroll = setInterval(function() {
            var el = document.getElementById('rev_' + id);
            if (el) {
                clearInterval(tryScroll);
                el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                el.style.boxShadow = '0 0 0 2px #00bcd4';
                setTimeout(function() { el.style.boxShadow = ''; }, 3000);
                
                if (scrollReply) {
    setTimeout(function() {
        toggleReplies(id);
        setTimeout(function() {
            var repliesBox = document.getElementById('repliesBox_' + id);
            if (repliesBox) {
                repliesBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
                var allReplies = repliesBox.querySelectorAll('.reply-item');
                if (allReplies.length > 0) {
                    var lastReply = allReplies[allReplies.length - 1];
                    lastReply.style.background = 'rgba(0,188,212,0.15)';
                    lastReply.style.borderRadius = '8px';
                    lastReply.style.transition = 'background 1s';
                    setTimeout(function() { lastReply.style.background = ''; }, 3000);
                }
            }
        }, 400);
    }, 1500);
}
            }
        }, 300);
        setTimeout(function() { clearInterval(tryScroll); }, 5000);
    }
});
</script>
<script>
window.addEventListener('load', function() {
  var hash = window.location.hash;
  if (hash && hash.startsWith('#car-')) {
    var carId = hash.replace('#car-', '');
    // انتظر تحميل الصفحة ثم اسكرول
    setTimeout(function() {
      var el = document.querySelector('.car-card[data-id="' + carId + '"]');
      if (el) {
        el.scrollIntoView({ behavior: 'smooth', block: 'center' });
        el.style.outline = '2px solid #00bcd4';
        el.style.boxShadow = '0 0 24px rgba(0,188,212,0.4)';
        setTimeout(function() {
          el.style.outline = '';
          el.style.boxShadow = '';
        }, 3000);
      }
    }, 800);
  }
 }); </script>
<!-- ══ EDIT CAR MODAL ══ -->
<div id="editCarModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.75);z-index:9999;align-items:center;justify-content:center;backdrop-filter:blur(6px);">
  <div style="background:#0d1b35;border:1px solid rgba(0,210,200,.25);border-radius:18px;width:90%;max-width:560px;max-height:90vh;overflow:hidden;display:flex;flex-direction:column;">
    
    <!-- Header -->
    <div style="display:flex;justify-content:space-between;align-items:center;padding:20px 24px;border-bottom:1px solid rgba(0,210,200,.15);">
      <h3 style="color:#fff;font-size:1.1rem;margin:0;">✏️ Edit Car</h3>
      <button onclick="closeEditModal()" style="background:none;border:none;color:#aaa;font-size:1.4rem;cursor:pointer;">×</button>
    </div>

    <!-- Tabs -->
    <div style="display:flex;border-bottom:1px solid rgba(0,210,200,.15);">
      <button id="editTab1" onclick="switchEditTab(1)" style="flex:1;padding:12px;background:rgba(0,210,200,.1);border:none;color:#00d2c8;font-weight:700;font-size:13px;cursor:pointer;border-bottom:2px solid #00d2c8;">
        🏷️ Card Info
      </button>
      <button id="editTab2" onclick="switchEditTab(2)" style="flex:1;padding:12px;background:none;border:none;color:#aab8d0;font-weight:700;font-size:13px;cursor:pointer;border-bottom:2px solid transparent;">
        📋 Details Page
      </button>
    </div>

    <input type="hidden" id="editCarId">
    <div style="overflow-y:auto;padding:20px 24px;flex:1;">

      <!-- TAB 1: ما يظهر في البطاقة -->
      <div id="editTabContent1">
        <div style="display:grid;gap:14px;">
          <div>
            <label style="color:#aab8d0;font-size:12px;display:block;margin-bottom:5px;">Title</label>
            <input id="editTitle" style="width:100%;padding:10px 14px;background:#0a1528;border:1px solid rgba(0,210,200,.2);border-radius:10px;color:#fff;font-family:inherit;font-size:13px;box-sizing:border-box;">
          </div>
          <div>
            <label style="color:#aab8d0;font-size:12px;display:block;margin-bottom:5px;">Price (DZD)</label>
            <input id="editPrice" type="number" style="width:100%;padding:10px 14px;background:#0a1528;border:1px solid rgba(0,210,200,.2);border-radius:10px;color:#fff;font-family:inherit;font-size:13px;box-sizing:border-box;">
          </div>
          <div>
            <label style="color:#aab8d0;font-size:12px;display:block;margin-bottom:5px;">Year</label>
            <input id="editYear" type="number" style="width:100%;padding:10px 14px;background:#0a1528;border:1px solid rgba(0,210,200,.2);border-radius:10px;color:#fff;font-family:inherit;font-size:13px;box-sizing:border-box;">
          </div>
          <div>
            <label style="color:#aab8d0;font-size:12px;display:block;margin-bottom:5px;">Short Description (shown on card)</label>
            <textarea id="editDesc" rows="3" style="width:100%;padding:10px 14px;background:#0a1528;border:1px solid rgba(0,210,200,.2);border-radius:10px;color:#fff;font-family:inherit;font-size:13px;resize:vertical;box-sizing:border-box;"></textarea>
          </div>
          <div>
            <label style="color:#aab8d0;font-size:12px;display:block;margin-bottom:5px;">Current Photos</label>
            <div id="currentPhotosWrap" style="display:flex;flex-wrap:wrap;gap:4px;"></div>
          </div>
          <div>
            <label style="color:#aab8d0;font-size:12px;display:block;margin-bottom:5px;">Add New Photos</label>
            <input id="editPhotos" type="file" multiple accept="image/*" style="width:100%;padding:10px 14px;background:#0a1528;border:1px solid rgba(0,210,200,.2);border-radius:10px;color:#aab8d0;font-family:inherit;font-size:12px;box-sizing:border-box;">
          </div>
        </div>
      </div>

      <!-- TAB 2: ما يظهر في صفحة التفاصيل -->
      <div id="editTabContent2" style="display:none;">
        <div style="display:grid;gap:14px;">
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
            <div>
              <label style="color:#aab8d0;font-size:12px;display:block;margin-bottom:5px;">Engine</label>
              <input id="editEngine" placeholder="e.g. 1.5T Turbo" style="width:100%;padding:10px 14px;background:#0a1528;border:1px solid rgba(0,210,200,.2);border-radius:10px;color:#fff;font-family:inherit;font-size:13px;box-sizing:border-box;">
            </div>
            <div>
              <label style="color:#aab8d0;font-size:12px;display:block;margin-bottom:5px;">Power</label>
              <input id="editPower" placeholder="e.g. 169 HP" style="width:100%;padding:10px 14px;background:#0a1528;border:1px solid rgba(0,210,200,.2);border-radius:10px;color:#fff;font-family:inherit;font-size:13px;box-sizing:border-box;">
            </div>
          </div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
            <div>
              <label style="color:#aab8d0;font-size:12px;display:block;margin-bottom:5px;">Consumption</label>
              <input id="editConsumption" placeholder="e.g. 7.5L/100km" style="width:100%;padding:10px 14px;background:#0a1528;border:1px solid rgba(0,210,200,.2);border-radius:10px;color:#fff;font-family:inherit;font-size:13px;box-sizing:border-box;">
            </div>
            <div>
              <label style="color:#aab8d0;font-size:12px;display:block;margin-bottom:5px;">Delivery</label>
              <input id="editDelivery" placeholder="e.g. 2-4 weeks" style="width:100%;padding:10px 14px;background:#0a1528;border:1px solid rgba(0,210,200,.2);border-radius:10px;color:#fff;font-family:inherit;font-size:13px;box-sizing:border-box;">
            </div>
          </div>
          <div>
            <label style="color:#aab8d0;font-size:12px;display:block;margin-bottom:5px;">Other Specs (one per line: Label: Value)</label>
            <textarea id="editSpecs" rows="4" placeholder="Warranty: 5 years&#10;Seats: 7&#10;Drive: AWD" style="width:100%;padding:10px 14px;background:#0a1528;border:1px solid rgba(0,210,200,.2);border-radius:10px;color:#fff;font-family:inherit;font-size:13px;resize:vertical;box-sizing:border-box;"></textarea>
          </div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
            <div>
              <label style="color:#aab8d0;font-size:12px;display:block;margin-bottom:5px;">Transmission</label>
              <select id="editTransmission" style="width:100%;padding:10px 14px;background:#0a1528;border:1px solid rgba(0,210,200,.2);border-radius:10px;color:#fff;font-family:inherit;font-size:13px;box-sizing:border-box;">
                <option value="">Select...</option>
                <option value="Automatic">Automatic</option>
                <option value="Manual">Manual</option>
                <option value="CVT">CVT</option>
                <option value="DCT">DCT</option>
              </select>
            </div>
            <div>
              <label style="color:#aab8d0;font-size:12px;display:block;margin-bottom:5px;">Fuel Type</label>
              <select id="editFuel" style="width:100%;padding:10px 14px;background:#0a1528;border:1px solid rgba(0,210,200,.2);border-radius:10px;color:#fff;font-family:inherit;font-size:13px;box-sizing:border-box;">
                <option value="">Select...</option>
                <option value="Essence">Essence</option>
                <option value="Diesel">Diesel</option>
                <option value="Hybrid">Hybrid</option>
                <option value="Electric">Electric</option>
              </select>
            </div>
          </div>
          <div>
            <label style="color:#aab8d0;font-size:12px;display:block;margin-bottom:5px;">Contact Phone</label>
            <input id="editPhone" placeholder="e.g. 0551234567" style="width:100%;padding:10px 14px;background:#0a1528;border:1px solid rgba(0,210,200,.2);border-radius:10px;color:#fff;font-family:inherit;font-size:13px;box-sizing:border-box;">
          </div>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <div style="display:flex;gap:10px;padding:16px 24px;border-top:1px solid rgba(0,210,200,.15);">
      <button onclick="saveCarEdit()" style="flex:1;padding:12px;background:#00d2c8;border:none;border-radius:10px;color:#0a0f1e;font-weight:700;font-size:14px;cursor:pointer;font-family:inherit;">Save Changes</button>
      <button onclick="closeEditModal()" style="flex:1;padding:12px;background:transparent;border:1px solid rgba(255,255,255,.15);border-radius:10px;color:#aaa;font-size:14px;cursor:pointer;font-family:inherit;">Cancel</button>
    </div>
  </div>
</div>
        

<style>
.car-options-wrap { position:absolute; top:10px; left:10px; z-index:20; }
.car-options-btn {
  width:30px; height:30px; border-radius:50%;
  background:rgba(0,210,200,.18); border:1px solid rgba(0,210,200,.4);
  color:#00d2c8; font-size:18px; font-weight:700; line-height:1;
  cursor:pointer; display:flex; align-items:center; justify-content:center;
  transition:background .2s;
}
.car-options-btn:hover { background:rgba(0,210,200,.32); }
.car-options-menu {
  position:absolute; top:36px; left:0;
  background:#0d1b35; border:1px solid rgba(0,210,200,.25);
  border-radius:10px; overflow:hidden; min-width:120px;
  box-shadow:0 8px 24px rgba(0,0,0,.5);
  opacity:0; transform:scale(.9) translateY(-6px);
  transform-origin:top left; pointer-events:none;
  transition:opacity .18s, transform .18s;
}
.car-options-menu.open { opacity:1; transform:scale(1) translateY(0); pointer-events:all; }
.car-options-menu button {
  display:flex; align-items:center; gap:8px; width:100%;
  padding:10px 14px; background:none; border:none;
  cursor:pointer; font-family:inherit; font-size:13px; font-weight:600;
  color:#cdd9f0; transition:background .15s;
}
.car-options-menu button:hover { background:rgba(0,210,200,.1); color:#fff; }
.car-options-menu button.danger { color:#ff5555; }
.car-options-menu button.danger:hover { background:rgba(255,85,85,.1); }
</style>

<script>
/* ── Toggle menu ── */
function toggleCarMenu(btn) {
  var menu = btn.nextElementSibling;
  var isOpen = menu.classList.toggle('open');
  document.querySelectorAll('.car-options-menu.open').forEach(function(m){
    if (m !== menu) m.classList.remove('open');
  });
}
document.addEventListener('click', function(e){
  if (!e.target.closest('.car-options-wrap')) {
    document.querySelectorAll('.car-options-menu.open').forEach(function(m){ m.classList.remove('open'); });
  }
});

/* ── Delete car ── */
function deleteCar(carId, btn) {
  if (!confirm('Delete this car? This cannot be undone.')) return;
  var fd = new FormData();
  fd.append('car_id', carId);
  fetch('car_actions.php?action=delete', { method:'POST', body:fd })
    .then(function(r){ return r.json(); })
    .then(function(data){
      if (data.success) {
        var card = btn.closest('.car-card');
        if (card) { card.style.opacity='0'; card.style.transition='opacity .3s'; setTimeout(function(){ card.remove(); }, 300); }
      } else { alert(data.message || 'Error deleting car'); }
    });
}

/* ── Open edit modal ── */
function deletePhoto(photoId, btn) {
  if (!confirm('Delete this photo?')) return;
  var fd = new FormData();
  fd.append('photo_id', photoId);
  fetch('car_actions.php?action=delete_photo', { method:'POST', body:fd })
    .then(function(r){ return r.json(); })
    .then(function(data){
      if (data.success) { btn.closest('div').remove(); }
      else { alert(data.message || 'Error'); }
    });
}
function closeEditModal() {
  document.getElementById('editCarModal').style.display = 'none';
}
/* ── Switch Tab ── */
function switchEditTab(tab) {
  document.getElementById('editTabContent1').style.display = tab === 1 ? 'block' : 'none';
  document.getElementById('editTabContent2').style.display = tab === 2 ? 'block' : 'none';
  document.getElementById('editTab1').style.background = tab === 1 ? 'rgba(0,210,200,.1)' : 'none';
  document.getElementById('editTab1').style.color = tab === 1 ? '#00d2c8' : '#aab8d0';
  document.getElementById('editTab1').style.borderBottom = tab === 1 ? '2px solid #00d2c8' : '2px solid transparent';
  document.getElementById('editTab2').style.background = tab === 2 ? 'rgba(0,210,200,.1)' : 'none';
  document.getElementById('editTab2').style.color = tab === 2 ? '#00d2c8' : '#aab8d0';
  document.getElementById('editTab2').style.borderBottom = tab === 2 ? '2px solid #00d2c8' : '2px solid transparent';
}

/* ── Open edit modal ── */
function openEditModal(carId) {
  fetch('car_actions.php?action=get&car_id=' + carId)
    .then(function(r){ return r.json(); })
    .then(function(data){
      if (!data.success) { alert('Could not load car data'); return; }
      var c = data.car;
      document.getElementById('editCarId').value = carId;
      document.getElementById('editTitle').value       = c.title || '';
      document.getElementById('editPrice').value       = c.price || '';
      document.getElementById('editYear').value        = c.year  || '';
      document.getElementById('editDesc').value        = c.description || '';
      document.getElementById('editEngine').value      = c.engine || '';
      document.getElementById('editPower').value       = c.power || '';
      document.getElementById('editConsumption').value = c.consumption || '';
      document.getElementById('editDelivery').value    = c.delivery || '';
      document.getElementById('editSpecs').value       = c.specs_text || '';
      document.getElementById('editTransmission').value = c.transmission || '';
      document.getElementById('editFuel').value        = c.fuel_type || '';
      document.getElementById('editPhone').value       = c.contact_phone || '';
      var photosWrap = document.getElementById('currentPhotosWrap');
      photosWrap.innerHTML = '';
      if (c.photos && c.photos.length) {
        c.photos.forEach(function(p) {
          photosWrap.innerHTML +=
            '<div style="position:relative;display:inline-block;margin:4px;">'
            + '<img src="http://localhost/' + p.path + '" style="width:80px;height:60px;object-fit:cover;border-radius:6px;">'
            + '<button onclick="deletePhoto(' + p.id + ',this)" style="position:absolute;top:-6px;right:-6px;background:#e74c3c;border:none;color:#fff;border-radius:50%;width:20px;height:20px;cursor:pointer;font-size:12px;line-height:1;">×</button>'
            + '</div>';
        });
      }
      switchEditTab(1);
      document.getElementById('editCarModal').style.display = 'flex';
    });
}

/* ── Save edits ── */
function saveCarEdit() {
  var carId = document.getElementById('editCarId').value;
  var fd = new FormData();
  fd.append('car_id',        carId);
  fd.append('title',         document.getElementById('editTitle').value.trim());
  fd.append('price',         document.getElementById('editPrice').value.trim());
  fd.append('year',          document.getElementById('editYear').value.trim());
  fd.append('description',   document.getElementById('editDesc').value.trim());
  fd.append('engine',        document.getElementById('editEngine').value.trim());
  fd.append('power',         document.getElementById('editPower').value.trim());
  fd.append('consumption',   document.getElementById('editConsumption').value.trim());
  fd.append('delivery',      document.getElementById('editDelivery').value.trim());
  fd.append('specs_text',    document.getElementById('editSpecs').value.trim());
  fd.append('transmission',  document.getElementById('editTransmission').value);
  fd.append('fuel_type',     document.getElementById('editFuel').value);
  fd.append('contact_phone', document.getElementById('editPhone').value.trim());
  var photos = document.getElementById('editPhotos').files;
  for (var i = 0; i < photos.length; i++) fd.append('photos[]', photos[i]);
  fetch('car_actions.php?action=edit', { method:'POST', body:fd })
    .then(function(r){ return r.json(); })
    .then(function(data){
      if (data.success) { closeEditModal(); location.reload(); }
      else { alert(data.message || 'Error saving'); }
    });
}
// ══ REPORT COMMENT POPUP ══
let _reportType = null, _reportCommentId = null, _reportText = '';

function openReportComment(type, id, text) {
  if (!isLoggedIn) { window.location.href = 'login.html'; return; }
  _reportType = type;
  _reportCommentId = id;
  _reportText = text;
  document.getElementById('reportCommentModal').style.display = 'flex';
  document.querySelectorAll('input[name="reportReason"]').forEach(r => r.checked = false);
}

function closeReportComment() {
  document.getElementById('reportCommentModal').style.display = 'none';
}

async function submitReportComment() {
  const reason = document.querySelector('input[name="reportReason"]:checked')?.value;
  if (!reason) { showToast('Please select a reason'); return; }
  try {
    const res = await fetch('report_comment.php', {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({
        comment_type: _reportType,
        comment_id: _reportCommentId,
        comment_text: _reportText,
        reason: reason,
        page_url: window.location.href
      })
    });
    const data = await res.json();
    if (data.success) {
      closeReportComment();
      showToast('Report submitted. Thank you!');
    } else {
      showToast(data.error || 'Error submitting report');
    }
  } catch(e) {
    showToast('Connection error');
  }
}
</script>
<!-- REPORT COMMENT MODAL -->
<div id="reportCommentModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.75);z-index:9999;align-items:center;justify-content:center;backdrop-filter:blur(6px);">
  <div style="background:#161616;border:1px solid rgba(255,255,255,0.1);border-radius:20px;padding:2rem;width:92%;max-width:420px;position:relative;">
    <button onclick="closeReportComment()" style="position:absolute;top:1rem;right:1rem;background:rgba(255,255,255,0.06);border:none;color:#fff;width:30px;height:30px;border-radius:50%;cursor:pointer;font-size:1rem;">✕</button>
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:1.4rem;">
      <div style="width:36px;height:36px;border-radius:10px;background:rgba(239,68,68,0.12);border:1px solid rgba(239,68,68,0.3);display:flex;align-items:center;justify-content:center;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#f87171" stroke-width="2"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/></svg>
      </div>
      <h3 style="color:#fff;font-family:'Syne',sans-serif;font-size:1.05rem;margin:0;">Report Comment</h3>
    </div>
    <p style="color:rgba(255,255,255,0.45);font-size:.82rem;margin-bottom:.9rem;">Select the reason:</p>
    <div style="display:flex;flex-direction:column;gap:.5rem;margin-bottom:1rem;">
      <label style="display:flex;align-items:center;gap:10px;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.07);border-radius:10px;padding:.6rem .9rem;cursor:pointer;color:rgba(255,255,255,0.6);font-size:.84rem;font-family:'DM Sans',sans-serif;">
        <input type="radio" name="reportReason" value="Inappropriate content"> Inappropriate content
      </label>
      <label style="display:flex;align-items:center;gap:10px;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.07);border-radius:10px;padding:.6rem .9rem;cursor:pointer;color:rgba(255,255,255,0.6);font-size:.84rem;font-family:'DM Sans',sans-serif;">
        <input type="radio" name="reportReason" value="threats"> threats
      </label>
      <label style="display:flex;align-items:center;gap:10px;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.07);border-radius:10px;padding:.6rem .9rem;cursor:pointer;color:rgba(255,255,255,0.6);font-size:.84rem;font-family:'DM Sans',sans-serif;">
        <input type="radio" name="reportReason" value="Harassment"> Harassment
      </label>
      <label style="display:flex;align-items:center;gap:10px;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.07);border-radius:10px;padding:.6rem .9rem;cursor:pointer;color:rgba(255,255,255,0.6);font-size:.84rem;font-family:'DM Sans',sans-serif;">
        <input type="radio" name="reportReason" value="False information"> racism/discrimination
      </label>
      <label style="display:flex;align-items:center;gap:10px;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.07);border-radius:10px;padding:.6rem .9rem;cursor:pointer;color:rgba(255,255,255,0.6);font-size:.84rem;font-family:'DM Sans',sans-serif;">
        <input type="radio" name="reportReason" value="Other"> Other
      </label>
    </div>
    <button onclick="submitReportComment()" style="width:100%;background:linear-gradient(135deg,#dc2626,#b91c1c);color:#fff;border:none;padding:.7rem;border-radius:10px;font-family:'DM Sans',sans-serif;font-weight:600;font-size:.88rem;cursor:pointer;">
      Submit Report
    </button>
  </div>
</div>
<script>
fetch('api.php?action=get_stats')
  .then(r => r.json())
  .then(res => {
    if (!res.success) return;
    const d = res.data;
    document.getElementById('stat-cars').textContent    = d.cars > 0    ? d.cars + '+'    : '0';
    document.getElementById('stat-agents').textContent  = d.agents > 0  ? d.agents + '+'  : '0';
    document.getElementById('stat-wilayas').textContent = d.wilayas > 0 ? d.wilayas       : '0';
  })
  .catch(() => {
    document.getElementById('stat-cars').textContent    = '200+';
    document.getElementById('stat-agents').textContent  = '50+';
    document.getElementById('stat-wilayas').textContent = '58';
  });
</script>
<script>
var highlightId = new URLSearchParams(window.location.search).get('highlight');
if (highlightId) {
    // انتظر تحميل الصفحة ثم روح للسيارة
    setTimeout(function() {
        var el = document.getElementById('car-' + highlightId);
        if (el) {
            el.scrollIntoView({ behavior: 'smooth', block: 'center' });
            el.style.outline = '3px solid #00bcd4';
            el.style.borderRadius = '12px';
            setTimeout(function(){ el.style.outline = ''; }, 3000);
        }
    }, 800);
}
</script>
</body>
</html>