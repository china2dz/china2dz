<?php
session_start();
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Terms of Use — China2DZ</title>
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
  font-size: clamp(1.8rem, 4vw, 2.6rem);
  font-weight: 800;
  margin: 0 0 12px;
}
.static-page h1 span { color: #00bcd4; }
.sp-updated {
  font-size: .8rem;
  opacity: .45;
  margin-bottom: 44px;
  display: block;
}
.sp-toc {
  background: rgba(255,255,255,0.03);
  border: 1px solid rgba(255,255,255,0.07);
  border-radius: 14px;
  padding: 20px 24px;
  margin-bottom: 44px;
}
.sp-toc h3 { font-size: .85rem; font-weight: 700; margin: 0 0 12px; opacity: .5; text-transform: uppercase; letter-spacing: 1.5px; }
.sp-toc ol { margin: 0; padding-left: 18px; display: flex; flex-direction: column; gap: 6px; }
.sp-toc ol li a { font-size: .88rem; color: #00bcd4; text-decoration: none; }
.sp-toc ol li a:hover { text-decoration: underline; }
.sp-article { display: flex; flex-direction: column; gap: 40px; }
.sp-art-section h2 {
  font-size: 1.05rem;
  font-weight: 700;
  margin: 0 0 12px;
  display: flex;
  align-items: center;
  gap: 10px;
}
.sp-art-num {
  width: 28px; height: 28px;
  border-radius: 7px;
  background: rgba(0,188,212,0.12);
  border: 1px solid rgba(0,188,212,0.25);
  color: #00bcd4;
  font-size: .78rem;
  font-weight: 800;
  display: grid;
  place-items: center;
  flex-shrink: 0;
}
.sp-art-section p, .sp-art-section li {
  font-size: .92rem;
  line-height: 1.85;
  opacity: .7;
}
.sp-art-section ul { padding-left: 20px; display: flex; flex-direction: column; gap: 6px; }
</style>
</head>
<body>
<main class="static-page">
  <div class="sp-badge">
    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
    Legal
  </div>
  <h1>Terms of <span>Use</span></h1>
  <span class="sp-updated">Last updated: January 2026</span>

  <div class="sp-toc">
    <h3>Table of Contents</h3>
    <ol>
      <li><a href="#acceptance">Acceptance of Terms</a></li>
      <li><a href="#use">Use of the Platform</a></li>
      <li><a href="#accounts">User Accounts</a></li>
      <li><a href="#listings">Listings & Content</a></li>
      <li><a href="#liability">Limitation of Liability</a></li>
      <li><a href="#contact">Contact</a></li>
    </ol>
  </div>

  <div class="sp-article">
    <div class="sp-art-section" id="acceptance">
      <h2><span class="sp-art-num">1</span> Acceptance of Terms</h2>
      <p>By accessing or using China2DZ, you agree to be bound by these Terms of Use. If you do not agree to these terms, please do not use our platform. We reserve the right to update these terms at any time, and continued use of the platform constitutes acceptance of any changes.</p>
    </div>

    <div class="sp-art-section" id="use">
      <h2><span class="sp-art-num">2</span> Use of the Platform</h2>
      <p>China2DZ is a marketplace platform connecting car buyers with verified Agents in Algeria. You agree to use the platform only for lawful purposes and in a manner that does not infringe the rights of others. Prohibited uses include:</p>
      <ul>
        <li>Posting false, misleading, or fraudulent listings</li>
        <li>Harassing or threatening other users or Agents</li>
        <li>Attempting to bypass our security measures</li>
        <li>Using automated bots or scrapers without permission</li>
      </ul>
    </div>

    <div class="sp-art-section" id="accounts">
      <h2><span class="sp-art-num">3</span> User Accounts</h2>
      <p>You are responsible for maintaining the confidentiality of your account credentials and for all activities that occur under your account. You must notify us immediately of any unauthorized use of your account. China2DZ reserves the right to suspend or terminate accounts that violate these terms.</p>
    </div>

    <div class="sp-art-section" id="listings">
      <h2><span class="sp-art-num">4</span> Listings & Content</h2>
      <p>Agents are responsible for the accuracy of their listings, including vehicle descriptions, prices, and photos. China2DZ does not guarantee the accuracy of any listing and is not responsible for transactions between buyers and Agents. We reserve the right to remove any listing that violates our policies.</p>
    </div>

    <div class="sp-art-section" id="liability">
      <h2><span class="sp-art-num">5</span> Limitation of Liability</h2>
      <p>China2DZ provides this platform "as is" without warranties of any kind. We are not liable for any damages arising from your use of the platform, including disputes between buyers and Agents. Our role is limited to facilitating connections; all transactions are between buyers and Agents directly.</p>
    </div>

    <div class="sp-art-section" id="contact">
      <h2><span class="sp-art-num">6</span> Contact</h2>
      <p>For any questions about these Terms of Use, please contact us at <a href="mailto:chinadz563@gmail.com" style="color:#00bcd4;">chinadz563@gmail.com</a>.</p>
    </div>
  </div>
</main>

<?php include 'footer.php'; ?>
<script src="script.js"></script>
<script>document.addEventListener('DOMContentLoaded', function(){ initCommon(); initNav(); });</script>
</body>
</html>