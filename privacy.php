<?php
session_start();
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Privacy Policy — China2DZ</title>
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
.sp-highlight-box {
  background: rgba(0,188,212,0.07);
  border: 1px solid rgba(0,188,212,0.2);
  border-radius: 12px;
  padding: 18px 22px;
  margin-bottom: 44px;
  font-size: .9rem;
  line-height: 1.7;
  opacity: .85;
}
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
    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
    Privacy
  </div>
  <h1>Privacy <span>Policy</span></h1>
  <span class="sp-updated">Last updated: January 2026</span>

  <div class="sp-highlight-box">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#00bcd4" stroke-width="2.5" style="vertical-align:middle"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg> Your privacy matters to us. China2DZ collects only the information necessary to provide our services and never sells your personal data to third parties.
  </div>
  <div class="sp-article">
    <div class="sp-art-section">
      <h2><span class="sp-art-num">1</span> Information We Collect</h2>
      <p>We collect the following types of information when you use China2DZ:</p>
      <ul>
        <li><strong>Account information:</strong> name, email address, phone number</li>
        <li><strong>Profile information:</strong> profile photo, wilaya, preferences</li>
        <li><strong>Usage data:</strong> pages visited, searches performed, cars viewed</li>
        <li><strong>Communication data:</strong> messages sent between buyers and Agents</li>
      </ul>
    </div>

    <div class="sp-art-section">
      <h2><span class="sp-art-num">2</span> How We Use Your Information</h2>
      <p>We use the information we collect to:</p>
      <ul>
        <li>Provide and improve our platform services</li>
        <li>Facilitate communication between buyers and Agents</li>
        <li>Send you relevant notifications about listings and activity</li>
        <li>Ensure platform security and prevent fraudulent activity</li>
        <li>Comply with legal obligations</li>
      </ul>
    </div>

    <div class="sp-art-section">
      <h2><span class="sp-art-num">3</span> Data Sharing</h2>
      <p>China2DZ does not sell your personal data. We may share your information with:</p>
      <ul>
        <li><strong>Agents:</strong> when you initiate contact or make an inquiry about a listing</li>
        <li><strong>Service providers:</strong> who help us operate the platform (hosting, email services)</li>
        <li><strong>Legal authorities:</strong> if required by Algerian law or court order</li>
      </ul>
    </div>

    <div class="sp-art-section">
      <h2><span class="sp-art-num">4</span> Data Security</h2>
      <p>We implement industry-standard security measures to protect your personal data, including encrypted connections (HTTPS), secure password storage, and regular security audits. However, no system is 100% secure, and we encourage you to use a strong, unique password for your account.</p>
    </div>

    <div class="sp-art-section">
      <h2><span class="sp-art-num">5</span> Your Rights</h2>
      <p>You have the right to access, correct, or delete your personal data at any time through your profile settings. You may also request a copy of your data or ask us to stop processing it by contacting us at <a href="mailto:chinadz563@gmail.com" style="color:#00bcd4;">chinadz563@gmail.com</a>.</p>
    </div>

    <div class="sp-art-section">
      <h2><span class="sp-art-num">6</span> Contact</h2>
      <p>For any privacy-related questions or requests, contact our team at <a href="mailto:chinadz563@gmail.com" style="color:#00bcd4;">chinadz563@gmail.com</a>. We will respond within 5 business days.</p>
    </div>
  </div>
</main>

<?php include 'footer.php'; ?>
<script src="script.js"></script>
<script>document.addEventListener('DOMContentLoaded', function(){ initCommon(); initNav(); });</script>
</body>
</html>