<?php
session_start();
require_once 'config.php';
$r1 = $pdo->query("SELECT COUNT(*) FROM cars WHERE status IN ('available', 'reserved')");
$total_cars = $r1->fetchColumn();

$r2 = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'agent' AND status = 'approved'");
$total_agents = $r2->fetchColumn();

$r3 = $pdo->query("SELECT COUNT(DISTINCT wilaya) FROM users WHERE role = 'agent' AND status = 'approved' AND wilaya IS NOT NULL AND wilaya != ''");
$total_wilayas = $r3->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>About Us — China2DZ</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
<script src="theme.js"></script>
<style>
.static-page {
  max-width: 820px;
  margin: 0 auto;
  padding: 100px 28px 80px;
}
.static-page .sp-badge {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  background: rgba(0,188,212,0.1);
  border: 1px solid rgba(0,188,212,0.25);
  color: #00bcd4;
  font-size: .75rem;
  font-weight: 700;
  padding: 5px 14px;
  border-radius: 20px;
  margin-bottom: 20px;
  letter-spacing: .5px;
}
.static-page h1 {
  font-size: clamp(2rem, 5vw, 3rem);
  font-weight: 800;
  margin: 0 0 16px;
  line-height: 1.15;
}
.static-page h1 span { color: #00bcd4; }
.static-page .sp-lead {
  font-size: 1.05rem;
  line-height: 1.8;
  opacity: .7;
  margin-bottom: 52px;
  max-width: 640px;
}
.sp-divider {
  width: 48px; height: 3px;
  background: linear-gradient(90deg, #00bcd4, transparent);
  border-radius: 2px;
  margin-bottom: 48px;
}
.sp-section { margin-bottom: 48px; }
.sp-section h2 {
  font-size: 1.2rem;
  font-weight: 700;
  margin-bottom: 14px;
  display: flex;
  align-items: center;
  gap: 10px;
}
.sp-section h2 .sp-icon {
  width: 32px; height: 32px;
  border-radius: 8px;
  background: rgba(0,188,212,0.12);
  border: 1px solid rgba(0,188,212,0.25);
  display: grid;
  place-items: center;
  color: #00bcd4;
  flex-shrink: 0;
}
.sp-section p {
  font-size: .95rem;
  line-height: 1.85;
  opacity: .7;
}
.sp-stats {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px;
  margin-bottom: 52px;
}
.sp-stat {
  background: rgba(255,255,255,0.03);
  border: 1px solid rgba(255,255,255,0.07);
  border-radius: 14px;
  padding: 24px 20px;
  text-align: center;
}
.sp-stat-num {
  font-size: 2rem;
  font-weight: 800;
  color: #00bcd4;
  display: block;
}
.sp-stat-label {
  font-size: .8rem;
  opacity: .55;
  margin-top: 4px;
  display: block;
}
@media(max-width:560px){ .sp-stats{ grid-template-columns:1fr; } }
</style>
</head>
<body>
<main class="static-page">
  <div class="sp-badge">
    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    About China2DZ
  </div>
  <h1>Algeria's #1 Platform for <span>Chinese Cars</span></h1>
  <p class="sp-lead">China2DZ was born from a simple idea: make importing Chinese cars to Algeria easy, transparent, and accessible to everyone — whether you're a buyer or a professional dealer.</p>

  <div class="sp-divider"></div>

  <div class="sp-stats">
    <div class="sp-stat">
      <span class="sp-stat-num"><?= $total_cars ?>+</span>
      <span class="sp-stat-label">Cars Listed</span>
    </div>
    <div class="sp-stat">
      <span class="sp-stat-num"><?= $total_agents ?>+</span>
      <span class="sp-stat-label">Verified Agents</span>
    </div>
    <div class="sp-stat">
      <span class="sp-stat-num"><?= $total_wilayas ?></span>
      <span class="sp-stat-label">Wilayas Covered</span>
    </div>
  </div>

  <div class="sp-section">
    <h2>
      <span class="sp-icon"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg></span>
      Our Story
    </h2>
    <p>China2DZ was founded in 2024 with one mission: to bridge the gap between Chinese car manufacturers and Algerian buyers. We noticed the growing demand for high-quality, affordable Chinese vehicles in Algeria, yet there was no dedicated, trustworthy platform to facilitate these transactions. We built China2DZ to change that.</p>
  </div>

  <div class="sp-section">
    <h2>
      <span class="sp-icon"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></span>
      Our Mission
    </h2>
    <p>We connect verified Algerian Agents with buyers across all 58 wilayas, offering transparent pricing in DZD, duty-free options, and nationwide delivery. Every dealer on our platform is vetted to ensure you get a genuine, safe transaction.</p>
  </div>

  <div class="sp-section">
    <h2>
      <span class="sp-icon"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></span>
      Why Choose Us
    </h2>
    <p>With China2DZ you get real photos, verified prices, direct contact with Agents, and a growing community of buyers who share honest reviews. We're committed to making your car buying experience as smooth and transparent as possible.</p>
  </div>
</main>

<?php include 'footer.php'; ?>
<script src="script.js"></script>
<script>document.addEventListener('DOMContentLoaded', function(){ initCommon(); initNav(); });</script>
</body>
</html>