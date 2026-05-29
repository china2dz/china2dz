<?php
session_start();
error_reporting(0);
ini_set('display_errors', 0);
require_once 'config.php';

// Fetch all approved agents
$stmt = $pdo->prepare("
    SELECT 
        u.id, u.first_name, u.last_name, u.full_name, u.profile_photo,
        u.email, u.phone, u.wilaya, u.bio, u.is_online, u.last_seen,
        u.created_at,
        ap.company_name, ap.national_id, ap.id_card_name, ap.rc_number,
        ap.rc_owner_name, ap.whatsapp, ap.id_card_file,
        (SELECT COUNT(*) FROM cars WHERE cars.agent_id = u.id AND cars.status = 'available') AS car_count,
        (SELECT ROUND(AVG(r.rating),1) FROM agent_reviews r WHERE r.agent_id = u.id) AS avg_rating,
        (SELECT COUNT(*) FROM agent_reviews r WHERE r.agent_id = u.id) AS review_count
    FROM users u
    LEFT JOIN agent_profiles ap ON ap.user_id = u.id
    WHERE u.role = 'agent' AND u.status = 'approved'
    ORDER BY u.created_at DESC
");
$stmt->execute();
$agents = $stmt->fetchAll(PDO::FETCH_ASSOC);

// AJAX handlers
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');

    if ($_POST['action'] === 'add_review') {
        $agent_id = intval($_POST['agent_id']);
        $rating   = intval($_POST['rating']);
        $comment  = trim($_POST['comment']);
        // Use logged-in user name if available, else fallback
        if (isset($_SESSION['user_id'])) {
            $us = $pdo->prepare("SELECT first_name, last_name, profile_photo FROM users WHERE id = ?");
            $us->execute([$_SESSION['user_id']]);
            $urow = $us->fetch(PDO::FETCH_ASSOC);
            $reviewer = trim(($urow['first_name']??'') . ' ' . ($urow['last_name']??''));
            $reviewer_photo = $urow['profile_photo'] ?? '';
        } else {
            $reviewer = trim($_POST['reviewer_name']) ?: 'Anonymous';
            $reviewer_photo = '';
        }
        if ($agent_id && $rating >= 1 && $rating <= 5 && $comment !== '') {
        $s = $pdo->prepare("INSERT INTO agent_reviews (agent_id, reviewer_id, reviewer_name, rating, comment, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
$s->execute([$agent_id, $_SESSION['user_id'] ?? null, $reviewer, $rating, $comment]);
            echo json_encode(['success' => true, 'id' => $pdo->lastInsertId(), 'reviewer_name' => $reviewer, 'reviewer_photo' => $reviewer_photo]);
        } else {
            echo json_encode(['success' => false, 'msg' => 'Invalid data']);
        }
        exit;
    }
if ($_POST['action'] === 'get_replies') {
        $rid = intval($_POST['review_id']);
        $s = $pdo->prepare("SELECT * FROM dealer_review_replies WHERE review_id = ? ORDER BY created_at ASC");
        $s->execute([$rid]);
        echo json_encode($s->fetchAll(PDO::FETCH_ASSOC));
        exit;
    }

    if ($_POST['action'] === 'reply_review') {
        $rid = intval($_POST['review_id']);
        $content = trim($_POST['reply']);
        $uid = $_SESSION['user_id'] ?? null;
        $uname = ''; $uphoto = '';
        if ($uid) {
            $u = $pdo->prepare("SELECT first_name, last_name, profile_photo FROM users WHERE id=?");
            $u->execute([$uid]);
            $ur = $u->fetch(PDO::FETCH_ASSOC);
            $uname = trim(($ur['first_name']??'').' '.($ur['last_name']??''));
            $uphoto = $ur['profile_photo'] ?? '';
        }
        $s = $pdo->prepare("INSERT INTO dealer_review_replies (review_id,user_id,user_name,user_photo,content) VALUES (?,?,?,?,?)");
        $s->execute([$rid,$uid,$uname,$uphoto,$content]);
        $count = $pdo->prepare("SELECT COUNT(*) FROM dealer_review_replies WHERE review_id=?");
        $count->execute([$rid]);
        echo json_encode(['success'=>true,'count'=>$count->fetchColumn()]);
        exit;
    }

    if ($_POST['action'] === 'delete_reply') {
        $id = intval($_POST['reply_id']);
        $pdo->prepare("DELETE FROM dealer_review_replies WHERE id=?")->execute([$id]);
        echo json_encode(['success'=>true]);
        exit;
    }

    if ($_POST['action'] === 'like_review') {
        $id = intval($_POST['review_id']);
        $pdo->prepare("UPDATE agent_reviews SET likes = likes+1 WHERE id=?")->execute([$id]);
        $s = $pdo->prepare("SELECT likes FROM agent_reviews WHERE id=?");
        $s->execute([$id]);
        echo json_encode(['likes'=>$s->fetchColumn()]);
        exit;
    }
    if ($_POST['action'] === 'delete_review') {
        $id = intval($_POST['review_id']);
        $pdo->prepare("DELETE FROM agent_reviews WHERE id = ?")->execute([$id]);
        echo json_encode(['success' => true]);
        exit;
    }

    if ($_POST['action'] === 'edit_review') {
        $id      = intval($_POST['review_id']);
        $comment = trim($_POST['comment']);
        $rating  = intval($_POST['rating']);
        $pdo->prepare("UPDATE agent_reviews SET comment = ?, rating = ? WHERE id = ?")->execute([$comment, $rating, $id]);
        echo json_encode(['success' => true]);
        exit;
    }
    if ($_POST['action'] === 'edit_reply') {
        $id = intval($_POST['reply_id']);
        $content = trim($_POST['content']);
        $pdo->prepare("UPDATE dealer_review_replies SET content=? WHERE id=?")->execute([$content, $id]);
        echo json_encode(['success'=>true]);
        exit;
    }

    if ($_POST['action'] === 'reply_review') {
        $id    = intval($_POST['review_id']);
        $reply = trim($_POST['reply']);
        $pdo->prepare("UPDATE agent_reviews SET reply = ? WHERE id = ?")->execute([$reply, $id]);
        echo json_encode(['success' => true]);
        exit;
    }

    if ($_POST['action'] === 'get_reviews') {
        $agent_id = intval($_POST['agent_id']);
        $s = $pdo->prepare("SELECT ar.*, u.profile_photo as reviewer_photo, (SELECT COUNT(*) FROM dealer_review_replies WHERE review_id = ar.id) as reply_count FROM agent_reviews ar LEFT JOIN users u ON u.id = ar.reviewer_id WHERE ar.agent_id = ? ORDER BY ar.created_at DESC");
        $s->execute([$agent_id]);
        echo json_encode($s->fetchAll(PDO::FETCH_ASSOC));
        exit;
    }

    if ($_POST['action'] === 'get_cars') {
        $agent_id = intval($_POST['agent_id']);
        $s = $pdo->prepare("SELECT id, title, brand, year, price, status, wilaya, fuel_type, mileage FROM cars WHERE agent_id = ? ORDER BY created_at DESC");
        $s->execute([$agent_id]);
        echo json_encode($s->fetchAll(PDO::FETCH_ASSOC));
        exit;
    }
}

// Current logged-in user info for review form
$currentUser = null;
$currentUserPhoto = '';
if (isset($_SESSION['user_id'])) {
    $cu = $pdo->prepare("SELECT first_name, last_name, profile_photo FROM users WHERE id = ?");
    $cu->execute([$_SESSION['user_id']]);
    $currentUser = $cu->fetch(PDO::FETCH_ASSOC);
    $currentUserPhoto = $currentUser['profile_photo'] ?? '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Verified Agents | China2DZ</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,400&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
<script src="theme.js"></script>
<style>
/* ── Agents page extra styles ── */
.Agents-hero {
  background: linear-gradient(135deg, #0a0a1a 0%, #0d1b2a 60%, #0a0a1a 100%);
  padding: 100px 2rem 60px;
  text-align: center;
  position: relative;
  overflow: hidden;
  border-bottom: 1px solid rgba(255,255,255,0.06);
}
.Agents-hero::before {
  content: '';
  position: absolute; inset: 0;
  background: radial-gradient(ellipse at 50% 100%, rgba(0,90,180,.13) 0%, transparent 70%);
}
.Agents-hero-badge {
  display: inline-flex; align-items: center; gap: 6px;
  background: rgba(0,120,255,0.1); border: 1px solid rgba(0,120,255,0.25);
  color: #60a5fa; font-size: .75rem; font-weight: 600;
  padding: 5px 14px; border-radius: 50px; margin-bottom: 1.2rem;
  position: relative;
}
.Agents-hero h1 {
  font-family: 'Syne', sans-serif;
  font-size: clamp(2rem, 5vw, 3.2rem);
  font-weight: 800; color: #fff;
  position: relative; margin-bottom: .6rem;
}
.Agents-hero h1 .highlight {
  background: linear-gradient(135deg, #3b82f6, #06b6d4);
  -webkit-background-clip: text; -webkit-text-fill-color: transparent;
  background-clip: text;
}
.Agents-hero p {
  color: rgba(255,255,255,0.5); font-size: 1rem;
  max-width: 500px; margin: 0 auto 2rem; position: relative;
}
.Agents-search-wrap {
  max-width: 560px; margin: 0 auto; position: relative;
}
.Agents-search-wrap input {
  width: 100%;
  background: rgba(255,255,255,0.05);
  border: 1px solid rgba(255,255,255,0.1);
  border-radius: 50px;
  padding: .85rem 1.5rem .85rem 3.2rem;
  color: #fff; font-size: .95rem;
  font-family: 'DM Sans', sans-serif;
  outline: none; transition: border-color .2s;
}
.Agents-search-wrap input:focus { border-color: rgba(59,130,246,0.5); }
.Agents-search-wrap input::placeholder { color: rgba(255,255,255,0.3); }
.Agents-search-icon {
  position: absolute; left: 1.3rem; top: 50%; transform: translateY(-50%);
  color: rgba(255,255,255,0.3);
}

/* wilaya filters */
.Agents-filters {
  display: flex; gap: .5rem; justify-content: center; flex-wrap: wrap;
  padding: 1.5rem 2rem 1rem;
  max-width: 1300px; margin: 0 auto;
}
.Agents-filter-btn {
  background: rgba(255,255,255,0.04);
  border: 1px solid rgba(255,255,255,0.08);
  color: rgba(255,255,255,0.5);
  padding: .4rem 1rem; border-radius: 50px;
  font-size: .8rem; font-family: 'DM Sans', sans-serif;
  cursor: pointer; transition: all .2s;
}
.Agents-filter-btn:hover, .Agents-filter-btn.active {
  background: rgba(59,130,246,0.15);
  border-color: rgba(59,130,246,0.4);
  color: #60a5fa;
}

/* grid */
.Agents-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
  gap: 1.4rem;
  max-width: 1300px; margin: 0 auto; padding: 1rem 2rem 5rem;
  align-items: start;
}

/* agent card */
.dealer-card {
  background: rgba(255,255,255,0.03);
  border: 1px solid rgba(255,255,255,0.07);
  border-radius: 18px; overflow: hidden;
  transition: transform .25s, border-color .25s, box-shadow .25s;
}
.dealer-card:hover {
  transform: translateY(-4px);
  border-color: rgba(59,130,246,0.25);
  box-shadow: 0 16px 48px rgba(0,0,0,0.3);
}

/* card top */
.dc-top {
  padding: 1.4rem 1.4rem 1rem;
  display: flex; gap: 1rem; align-items: flex-start;
}
.dc-avatar-wrap { position: relative; flex-shrink: 0; }
.dc-avatar {
  width: 68px; height: 68px; border-radius: 50%;
  object-fit: cover;
  border: 2px solid rgba(255,255,255,0.1);
}
.dc-avatar-placeholder {
  width: 68px; height: 68px; border-radius: 50%;
  background: linear-gradient(135deg, #1e40af 0%, #0e7490 100%);
  display: flex; align-items: center; justify-content: center;
  font-size: 1.5rem; font-weight: 700; color: #fff;
  border: 2px solid rgba(255,255,255,0.1);
  font-family: 'Syne', sans-serif;
}
.dc-online-dot {
  width: 11px; height: 11px; border-radius: 50%;
  background: #22c55e; border: 2px solid #0d1117;
  position: absolute; bottom: 3px; right: 3px;
}
.dc-info { flex: 1; min-width: 0; }
.dc-name {
  font-family: 'Syne', sans-serif;
  font-size: 1rem; font-weight: 700; color: #fff; line-height: 1.3;
}
.dc-company { font-size: .78rem; color: #60a5fa; margin-top: .15rem; }
.dc-wilaya {
  display: inline-flex; align-items: center; gap: 4px;
  font-size: .73rem; color: rgba(255,255,255,0.4); margin-top: .4rem;
}
.dc-stars-row { display: flex; align-items: center; gap: .4rem; margin-top: .5rem; }
.dc-stars { display: flex; gap: 2px; font-size: .85rem; }
.dc-star-on { color: #f59e0b; }
.dc-star-off { color: rgba(255,255,255,0.12); }
.dc-rating-text { font-size: .73rem; color: rgba(255,255,255,0.4); }

/* stats row */
.dc-stats {
  display: flex;
  border-top: 1px solid rgba(255,255,255,0.06);
  border-bottom: 1px solid rgba(255,255,255,0.06);
}
.dc-stat {
  flex: 1; text-align: center;
  padding: .75rem .5rem;
  border-right: 1px solid rgba(255,255,255,0.06);
}
.dc-stat:last-child { border-right: none; }
.dc-stat-num { font-size: 1.05rem; font-weight: 700; color: #60a5fa; font-family: 'Syne', sans-serif; }
.dc-stat-lbl { font-size: .65rem; color: rgba(255,255,255,0.35); margin-top: 1px; text-transform: uppercase; letter-spacing: .04em; }

/* bio */
.dc-bio {
  padding: .75rem 1.4rem;
  font-size: .82rem; color: rgba(255,255,255,0.45); line-height: 1.7;
  display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
}

/* toggle btns */
.dc-toggles {
  padding: .7rem 1.4rem;
  display: flex; gap: .5rem; flex-wrap: wrap;
}
.dc-toggle-btn {
  background: rgba(255,255,255,0.04);
  border: 1px solid rgba(255,255,255,0.08);
  color: rgba(255,255,255,0.5);
  font-family: 'DM Sans', sans-serif;
  font-size: .78rem; padding: .35rem .85rem; border-radius: 8px;
  cursor: pointer; transition: all .2s;
  display: flex; align-items: center; gap: 5px;
}
.dc-toggle-btn:hover { border-color: rgba(59,130,246,0.35); color: #60a5fa; }
.dc-toggle-btn.open {
  background: rgba(59,130,246,0.1);
  border-color: rgba(59,130,246,0.35); color: #60a5fa;
}
.dc-toggle-btn svg { width: 12px; height: 12px; transition: transform .25s; }
.dc-toggle-btn.open svg.chevron { transform: rotate(180deg); }

/* expandable */
.dc-expand {
  max-height: 0; overflow: hidden;
  transition: max-height .35s ease;
}
.dc-expand.open {
  max-height: 700px;
  border-top: 1px solid rgba(255,255,255,0.06);
}

/* cars list */
.dc-cars-list { padding: .8rem 1.2rem; display: flex; flex-direction: column; gap: .4rem; }
.dc-car-item {
  display: flex; align-items: center; justify-content: space-between;
  background: rgba(255,255,255,0.03); border-radius: 10px;
  padding: .55rem 1rem; font-size: .82rem;
  cursor: pointer; transition: background .15s;
  text-decoration: none; color: #fff;
  border: 1px solid transparent;
}
.dc-car-item:hover { background: rgba(59,130,246,0.08); border-color: rgba(59,130,246,0.2); }
.dc-car-name { font-weight: 600; font-size: .83rem; }
.dc-car-meta { color: rgba(255,255,255,0.35); font-size: .73rem; margin-top: 2px; }
.dc-car-price { color: #f59e0b; font-weight: 700; font-size: .82rem; flex-shrink: 0; }

/* reviews panel */
.dc-reviews-panel { padding: 1rem 1.2rem; }
.dc-review-form { margin-bottom: 1rem; }

/* current user row in form */
.dc-review-user-row {
  display: flex; align-items: center; gap: 10px;
  background: rgba(255,255,255,0.04);
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 10px; padding: .6rem .9rem;
  margin-bottom: .7rem;
}
.dc-review-user-avatar {
  width: 34px; height: 34px; border-radius: 50%;
  object-fit: cover; border: 1.5px solid rgba(255,255,255,0.1);
}
.dc-review-user-initials {
  width: 34px; height: 34px; border-radius: 50%;
  background: linear-gradient(135deg, #1e40af, #0e7490);
  display: flex; align-items: center; justify-content: center;
  font-size: .8rem; font-weight: 700; color: #fff; flex-shrink: 0;
}
.dc-review-user-name { font-size: .83rem; font-weight: 600; color: #fff; }
.dc-review-user-sub { font-size: .72rem; color: rgba(255,255,255,0.4); }

.dc-review-form textarea {
  width: 100%; background: rgba(255,255,255,0.04);
  border: 1px solid rgba(255,255,255,0.08); border-radius: 10px;
  padding: .65rem .9rem; color: #fff;
  font-family: 'DM Sans', sans-serif; font-size: .83rem;
  outline: none; margin-bottom: .5rem;
  transition: border-color .2s; resize: none;
}
.dc-review-form textarea:focus { border-color: rgba(59,130,246,0.4); }
.dc-review-form textarea::placeholder { color: rgba(255,255,255,0.25); }
.dc-star-picker { display: flex; gap: 4px; margin-bottom: .6rem; cursor: pointer; }
.dc-star-picker span { font-size: 1.4rem; color: rgba(255,255,255,0.15); transition: color .15s; }
.dc-star-picker span.sel { color: #f59e0b; }
.dc-btn-submit {
  background: linear-gradient(135deg, #1e40af, #0e7490);
  color: #fff; border: none;
  padding: .55rem 1.3rem; border-radius: 9px;
  font-family: 'DM Sans', sans-serif; font-weight: 600; font-size: .83rem;
  cursor: pointer; transition: opacity .2s;
}
.dc-btn-submit:hover { opacity: .85; }
.dc-login-prompt {
  background: rgba(59,130,246,0.06);
  border: 1px solid rgba(59,130,246,0.15);
  border-radius: 10px; padding: .7rem 1rem;
  font-size: .82rem; color: rgba(255,255,255,0.5);
  margin-bottom: .8rem; text-align: center;
}
.dc-login-prompt a { color: #60a5fa; text-decoration: none; font-weight: 600; }

/* review item */
.dc-review-item {
  background: rgba(255,255,255,0.03); border-radius: 12px;
  padding: .9rem 1rem; margin-bottom: .6rem;
  border: 1px solid rgba(255,255,255,0.06);
}
.dc-rev-head { display: flex; justify-content: space-between; align-items: flex-start; gap: .5rem; }
.dc-rev-user { display: flex; align-items: center; gap: 8px; }
.dc-rev-avatar { width: 30px; height: 30px; border-radius: 50%; object-fit: cover; border: 1.5px solid rgba(255,255,255,0.1); }
.dc-rev-initials-sm {
  width: 30px; height: 30px; border-radius: 50%;
  background: linear-gradient(135deg, #1e40af, #0e7490);
  display: flex; align-items: center; justify-content: center;
  font-size: .72rem; font-weight: 700; color: #fff; flex-shrink: 0;
}
.dc-rev-name { font-weight: 600; font-size: .83rem; color: #fff; }
.dc-rev-date { font-size: .7rem; color: rgba(255,255,255,0.3); }
.dc-rev-stars { display: flex; gap: 1px; font-size: .8rem; margin-top: 2px; }
.dc-rev-body { font-size: .82rem; margin-top: .5rem; color: rgba(255,255,255,0.6); line-height: 1.6; }
.dc-rev-actions { display: flex; gap: .4rem; margin-top: .5rem; }
.dc-rev-btn {
  background: none;
  border: 1px solid rgba(255,255,255,0.08);
  color: rgba(255,255,255,0.4);
  font-family: 'DM Sans', sans-serif;
  font-size: .72rem; padding: .2rem .65rem; border-radius: 6px;
  cursor: pointer; transition: all .15s;
}
.dc-rev-btn:hover { border-color: rgba(59,130,246,0.35); color: #60a5fa; }
.dc-rev-btn.danger:hover { border-color: rgba(239,68,68,0.4); color: #f87171; }
.dc-reply-block {
  margin-top: .5rem; padding: .5rem .8rem;
  background: rgba(245,158,11,0.05); border-radius: 7px;
  border-left: 2px solid #f59e0b;
  font-size: .78rem; color: rgba(255,255,255,0.4);
}
.dc-reply-form { margin-top: .5rem; display: none; }
.dc-reply-form textarea {
  width: 100%; background: rgba(255,255,255,0.03);
  border: 1px solid rgba(255,255,255,0.08); border-radius: 8px;
  padding: .45rem .8rem; color: #fff;
  font-family: 'DM Sans', sans-serif; font-size: .79rem;
  outline: none; resize: none;
}
.dc-no-reviews { text-align: center; color: rgba(255,255,255,0.3); font-size: .83rem; padding: .5rem 0 1rem; }

/* edit area */
.dc-edit-area {
  width: 100%; background: rgba(255,255,255,0.03);
  border: 1px solid rgba(59,130,246,0.35); border-radius: 8px;
  padding: .5rem .8rem; color: #fff;
  font-family: 'DM Sans', sans-serif; font-size: .82rem;
  resize: none; outline: none; margin-top: .4rem;
}

/* card actions */
.dc-actions {
  padding: .9rem 1.2rem;
  display: flex; gap: .6rem;
  border-top: 1px solid rgba(255,255,255,0.06);
}
.dc-btn-action {
  flex: 1; display: flex; align-items: center; justify-content: center; gap: 5px;
  padding: .6rem; border-radius: 10px;
  font-family: 'DM Sans', sans-serif; font-weight: 600; font-size: .8rem;
  text-decoration: none; cursor: pointer; border: none; transition: all .2s;
}
.dc-btn-call {
  background: linear-gradient(135deg, #1e40af, #1d4ed8);
  color: #fff;
}
.dc-btn-call:hover { opacity: .85; }
.dc-btn-wa { background: #166534; color: #fff; }
.dc-btn-wa:hover { background: #16a34a; }
.dc-btn-mail {
  background: rgba(255,255,255,0.05);
  color: rgba(255,255,255,0.6);
  border: 1px solid rgba(255,255,255,0.08) !important;
  flex: 0 0 42px;
}
.dc-btn-mail:hover { border-color: rgba(59,130,246,0.35) !important; color: #60a5fa; }
.dc-btn-report {
  flex: 0 0 42px;
  background: rgba(239,68,68,0.08);
  color: rgba(239,68,68,0.7);
  border: 1px solid rgba(239,68,68,0.2) !important;
  border-radius: 10px;
  padding: 0;
  display: flex; align-items: center; justify-content: center;
}
.dc-btn-report:hover {
  background: rgba(239,68,68,0.15);
  border-color: rgba(239,68,68,0.5) !important;
  color: #f87171;
}
/* no results */
.dc-no-results {
  text-align: center; color: rgba(255,255,255,0.3);
  padding: 4rem 2rem; font-size: 1rem; grid-column: 1/-1;
}

/* spinner */
.dc-spinner {
  display: inline-block; width: 16px; height: 16px;
  border: 2px solid rgba(255,255,255,0.1);
  border-top-color: #60a5fa;
  border-radius: 50%; animation: dcSpin .7s linear infinite;
}
@keyframes dcSpin { to { transform: rotate(360deg); } }

@media (max-width: 640px) {
  .Agents-grid { padding: 1rem; }
  .Agents-hero h1 { font-size: 1.8rem; }
  .dc-actions { flex-wrap: wrap; }
}
[data-theme="light"] .dealer-card { background: #fff !important; border-color: #ddd !important; }
[data-theme="light"] .dc-name, [data-theme="light"] .dc-car-name, [data-theme="light"] .dc-rev-name, [data-theme="light"] .dc-review-user-name { color: #000 !important; }
[data-theme="light"] .dc-company { color: #1d4ed8 !important; }
[data-theme="light"] .dc-wilaya, [data-theme="light"] .dc-stat-lbl, [data-theme="light"] .dc-rating-text, [data-theme="light"] .dc-bio, [data-theme="light"] .dc-rev-body, [data-theme="light"] .dc-rev-date, [data-theme="light"] .dc-car-meta, [data-theme="light"] .dc-review-user-sub, [data-theme="light"] .dc-no-reviews { color: #555 !important; }
[data-theme="light"] .dc-stat-num { color: #1d4ed8 !important; }
[data-theme="light"] .dc-stats { background: #f5f5f5 !important; border-color: #ddd !important; }
[data-theme="light"] .dc-stat { border-color: #ddd !important; }
[data-theme="light"] .dc-toggle-btn { background: #eee !important; color: #222 !important; border-color: #ccc !important; }
[data-theme="light"] .dc-expand { background: #f9f9f9 !important; border-color: #ddd !important; }
[data-theme="light"] .dc-review-item { background: #fff !important; border-color: #ddd !important; }
[data-theme="light"] .dc-review-form textarea, [data-theme="light"] .dc-review-user-row { background: #eee !important; color: #000 !important; border-color: #ccc !important; }
[data-theme="light"] .dc-car-item { background: #f0f0f0 !important; color: #000 !important; border-color: #ddd !important; }
[data-theme="light"] .Agents-filter-btn { background: #eee !important; color: #333 !important; border-color: #ccc !important; }
[data-theme="light"] .Agents-filter-btn.active { background: #dbeafe !important; color: #1d4ed8 !important; border-color: #3b82f6 !important; }
[data-theme="light"] .dc-rev-btn { color: #444 !important; border-color: #ccc !important; }
[data-theme="light"] .dc-actions { background: #fff !important; border-top-color: #ddd !important; }
[data-theme="light"] .dc-star-picker span { color: #ccc !important; }
[data-theme="light"] .dc-star-picker span.sel { color: #f59e0b !important; }
[data-theme="light"] .dc-review-form textarea::placeholder { color: #888 !important; }
.dc-report-reason {
  display: flex; align-items: center; gap: 10px;
  background: rgba(255,255,255,0.03);
  border: 1px solid rgba(255,255,255,0.07);
  border-radius: 10px; padding: .6rem .9rem;
  cursor: pointer; transition: all .2s;
  color: rgba(255,255,255,0.6); font-size: .84rem;
  font-family: 'DM Sans', sans-serif;
}
.dc-report-reason:hover {
  background: rgba(239,68,68,0.06);
  border-color: rgba(239,68,68,0.25);
  color: #fff;
}
.dc-report-reason input[type="radio"] { display: none; }
.dc-report-reason:has(input:checked) {
  background: rgba(239,68,68,0.1);
  border-color: rgba(239,68,68,0.4);
  color: #fff;
}
.dc-report-reason-icon {
  width: 30px; height: 30px; border-radius: 8px;
  border: 1px solid;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
}
</style>
</head>
<body id="Agentspage">

<!-- ══════════════════════════════════════════
     HEADER — same as index.php
══════════════════════════════════════════ -->
<header id="mainHeader">
  <div class="logo">
    <a href="index.php"><span class="logo-china">China</span><span class="logo-2dz">2DZ</span></a>
  </div>
  <nav id="mainNav">
    <ul>
      <li><a href="index.php#home">Home</a></li>
      <li><a href="index.php#cars">Cars</a></li>
      <li><a href="Agents.php" class="active">Agents</a></li>
      <li><a href="index.php#reviews">Reviews</a></li>
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

<!-- ══════════════════════════════════════════
     HERO
══════════════════════════════════════════ -->
<section class="Agents-hero">
  <div class="Agents-hero-badge">
    <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
    <?= count($agents) ?>+ Verified Agents
  </div>
  <h1>Our <span class="highlight">Verified</span> Agents</h1>
  <p>Connect with the best certified Chinese car Agents across Algeria</p>
  <div class="Agents-search-wrap">
    <svg class="Agents-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
    <input type="text" id="AgentsSearch" placeholder="Search by name, wilaya, or company…" oninput="filterAgents()">
  </div>
</section>
<?php
// جيبي top 3 بالتقييم
$top3 = array_filter($agents, fn($a) => $a['avg_rating'] > 0);
usort($top3, fn($a, $b) => $b['avg_rating'] <=> $a['avg_rating']);
$top3 = array_slice($top3, 0, 3);
?>

<?php if (!empty($top3)): ?>
<div style="max-width:900px;margin:0 auto;padding:1.5rem 2rem .5rem;text-align:center;">
  <div style="font-size:.75rem;color:#60a5fa;font-weight:600;letter-spacing:.08em;text-transform:uppercase;margin-bottom:.5rem;">⭐ Top Rated</div>
  <div style="display:flex;justify-content:center;gap:1.5rem;flex-wrap:wrap;">
    <?php foreach($top3 as $t):
      $fn = trim(($t['first_name']??'').' '.($t['last_name']??''));
      $ini = mb_strtoupper(mb_substr($fn,0,1).mb_substr($t['last_name']??'',0,1));
      $stars = round($t['avg_rating']);
    ?>
    <div style="display:flex;flex-direction:column;align-items:center;gap:.4rem;cursor:pointer;" onclick="document.querySelector('[data-name*=\'<?= strtolower(addslashes($fn)) ?>\']')?.scrollIntoView({behavior:'smooth',block:'center'})">
      <?php if(!empty($t['profile_photo'])): ?>
        <img src="<?= htmlspecialchars($t['profile_photo']) ?>" style="width:56px;height:56px;border-radius:50%;object-fit:cover;border:2px solid #f59e0b;">
      <?php else: ?>
        <div style="width:56px;height:56px;border-radius:50%;background:linear-gradient(135deg,#1e40af,#0e7490);display:flex;align-items:center;justify-content:center;font-weight:700;color:#fff;font-size:1.1rem;border:2px solid #f59e0b;"><?= $ini ?></div>
      <?php endif; ?>
      <div style="font-size:.82rem;font-weight:700;color:#fff;"><?= htmlspecialchars($fn) ?></div>
      <div style="font-size:.75rem;color:#f59e0b;"><?= str_repeat('★', $stars) ?><?= str_repeat('☆', 5-$stars) ?> <?= number_format($t['avg_rating'],1) ?></div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>
<!-- wilaya filters -->
<div class="Agents-filters" id="AgentsFilters">
  <button class="Agents-filter-btn active" onclick="setDealerWilaya('', this)">All</button>
  <?php
  $wilayas = array_unique(array_filter(array_column($agents, 'wilaya')));
  sort($wilayas);
  foreach ($wilayas as $w):
  ?>
  <button class="Agents-filter-btn" onclick="setDealerWilaya('<?= htmlspecialchars($w) ?>', this)"><?= htmlspecialchars($w) ?></button>
  <?php endforeach; ?>
</div>

<!-- ══════════════════════════════════════════
     Agents GRID
══════════════════════════════════════════ -->
<div class="Agents-grid" id="AgentsGrid">

<?php if (empty($agents)): ?>
  <div class="dc-no-results">No verified Agents available yet.</div>
<?php endif; ?>

<?php foreach ($agents as $a):
  $firstName = $a['first_name'] ?? '';
  $lastName  = $a['last_name']  ?? '';
  $fullName  = trim("$firstName $lastName") ?: ($a['full_name'] ?? 'Dealer');
  $initials  = mb_strtoupper(mb_substr($firstName ?: ($a['full_name']??'D'), 0, 1) . mb_substr($lastName, 0, 1));
  $rating    = (float)($a['avg_rating'] ?? 0);
  $fullStars = (int)floor($rating);
  $wa        = preg_replace('/[^0-9]/', '', $a['whatsapp'] ?? '');
  $photoSrc  = '';
  if (!empty($a['profile_photo'])) {
      $photoSrc = htmlspecialchars($a['profile_photo']);
  }
?>
<div class="dealer-card"
     data-name="<?= htmlspecialchars(strtolower($fullName . ' ' . ($a['company_name']??''))) ?>"
     data-wilaya="<?= htmlspecialchars($a['wilaya'] ?? '') ?>">

  <!-- TOP -->
  <div class="dc-top">
    <div class="dc-avatar-wrap">
      <?php if ($photoSrc): ?>
        <img class="dc-avatar" src="<?= $photoSrc ?>" alt="<?= htmlspecialchars($fullName) ?>" onerror="this.style.display='none';if(this.nextElementSibling)this.nextElementSibling.style.display='flex'">
      <?php else: ?>
        <div class="dc-avatar-placeholder"><?= htmlspecialchars($initials) ?></div>
      <?php endif; ?>
      <?php if ($a['is_online']): ?><div class="dc-online-dot" title="Online now"></div><?php endif; ?>
    </div>
    <div class="dc-info">
      <div class="dc-name"><?= htmlspecialchars($fullName) ?></div>
      <?php if (!empty($a['company_name'])): ?>
        <div class="dc-company">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline;vertical-align:middle;margin-right:3px"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
          <?= htmlspecialchars($a['company_name']) ?>
        </div>
      <?php endif; ?>
      <?php if (!empty($a['wilaya'])): ?>
        <div class="dc-wilaya">
          <svg width="11" height="11" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5S10.62 6.5 12 6.5s2.5 1.12 2.5 2.5S13.38 11.5 12 11.5z"/></svg>
          <?= htmlspecialchars($a['wilaya']) ?>
        </div>
      <?php endif; ?>
      <div class="dc-stars-row">
        <div class="dc-stars">
          <?php for ($i=1;$i<=5;$i++): ?>
            <span class="<?= $i<=$fullStars ? 'dc-star-on' : 'dc-star-off' ?>">★</span>
          <?php endfor; ?>
        </div>
        <span class="dc-rating-text">
          <?= $rating ? number_format($rating,1) : 'No rating' ?>
          (<?= intval($a['review_count']) ?>)
        </span>
      </div>
    </div>
  </div>
  <!-- STATS -->
  <div class="dc-stats">
    <div class="dc-stat">
      <div class="dc-stat-num"><?= intval($a['car_count']) ?></div>
      <div class="dc-stat-lbl">Cars Listed</div>
    </div>
    <div class="dc-stat">
      <div class="dc-stat-num"><?= intval($a['review_count']) ?></div>
      <div class="dc-stat-lbl">Reviews</div>
    </div>
    <div class="dc-stat">
      <?php if ($a['is_online']): ?>
        <div class="dc-stat-num" style="color:#22c55e;font-size:.8rem;">● Online</div>
      <?php else: ?>
        <div class="dc-stat-num" style="font-size:.75rem;color:rgba(255,255,255,0.3);">Offline</div>
      <?php endif; ?>
      <div class="dc-stat-lbl">Status</div>
    </div>
  </div>

  <!-- BIO -->
  <?php if (!empty($a['bio'])): ?>
  <div class="dc-bio"><?= htmlspecialchars($a['bio']) ?></div>
  <?php endif; ?>

  <!-- TOGGLE BUTTONS -->
  <div class="dc-toggles">
    <?php if ($a['car_count'] > 0): ?>
    <button class="dc-toggle-btn" onclick="dcToggle(this, 'dc-cars-<?= $a['id'] ?>', <?= $a['id'] ?>, 'cars')">
      <svg viewBox="0 0 24 24" fill="currentColor"><rect x="1" y="3" width="15" height="13" rx="1"/><path d="M16 8l4 2v5h-4z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
      Cars (<?= $a['car_count'] ?>)
      <svg class="chevron" viewBox="0 0 24 24" fill="currentColor"><path d="M7 10l5 5 5-5z"/></svg>
    </button>
    <?php endif; ?>
    <button class="dc-toggle-btn" onclick="dcToggle(this, 'dc-reviews-<?= $a['id'] ?>', <?= $a['id'] ?>, 'reviews')">
      <svg viewBox="0 0 24 24" fill="currentColor"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"/></svg>
      Reviews (<?= intval($a['review_count']) ?>)
      <svg class="chevron" viewBox="0 0 24 24" fill="currentColor"><path d="M7 10l5 5 5-5z"/></svg>
    </button>
  </div>

  <!-- CARS PANEL -->
  <div class="dc-expand" id="dc-cars-<?= $a['id'] ?>">
    <div class="dc-cars-list" id="dc-cars-list-<?= $a['id'] ?>">
      <div style="text-align:center;padding:.8rem"><div class="dc-spinner"></div></div>
    </div>
  </div>

  <!-- REVIEWS PANEL -->
  <div class="dc-expand" id="dc-reviews-<?= $a['id'] ?>">
    <div class="dc-reviews-panel">

      <?php if (isset($_SESSION['user_id']) && $currentUser): ?>
      <!-- Logged-in: show user info + form -->
      <div class="dc-review-form">
        <div class="dc-star-picker" id="dcpicker-<?= $a['id'] ?>" data-val="0">
          <?php for($si=1;$si<=5;$si++): ?>
            <span onclick="dcSetStar(<?= $a['id'] ?>,<?= $si ?>)">★</span>
          <?php endfor; ?>
        </div>
        <textarea rows="3" placeholder="Share your experience with this dealer…" id="dctext-<?= $a['id'] ?>"></textarea>
        <button class="dc-btn-submit" onclick="dcSubmitReview(<?= $a['id'] ?>)">Submit Review</button>
      </div>
      <?php else: ?>
      <!-- Not logged in -->
      <div class="dc-login-prompt">
        <a href="login.html">Sign in</a> to leave a review
      </div>
      <?php endif; ?>

      <!-- reviews list -->
      <div id="dc-reviews-list-<?= $a['id'] ?>">
        <div style="text-align:center;padding:.8rem"><div class="dc-spinner"></div></div>
      </div>
    </div>
  </div>

<!-- ACTIONS -->
<div class="dc-actions">
  <button class="dc-btn-action dc-btn-call"
    onclick="dcOpenContact(
      <?= $a['id'] ?>,
      '<?= addslashes(htmlspecialchars($fullName)) ?>',
      '<?= addslashes($a['phone']??'') ?>',
      '<?= addslashes($wa) ?>',
      '<?= addslashes($a['email']??'') ?>'
    )">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
    </svg>
    Contact Dealer
  </button>
  <button class="dc-btn-action dc-btn-report"
    onclick="dcOpenReport(<?= $a['id'] ?>, '<?= addslashes(htmlspecialchars($fullName)) ?>')"
    title="Report Dealer">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
      <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/>
      <line x1="4" y1="22" x2="4" y2="15"/>
    </svg>
  </button>
</div>
</div><!-- /dealer-card -->
<?php endforeach; ?>
</div><!-- /Agents-grid -->
<?php include 'footer.php'; ?>
<div id="toast" class="toast"></div>
<script src="script.js"></script>
<script>
  const CURRENT_USER_ID = <?= isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 'null' ?>;
document.addEventListener('DOMContentLoaded', function() {
  initCommon();
  initNav();
});

/* ── Search & filter ── */
let _dealerWilaya = '';
function filterAgents() {
  const q = document.getElementById('AgentsSearch').value.toLowerCase().trim();
  document.querySelectorAll('.dealer-card').forEach(card => {
    const name   = card.dataset.name || '';
    const wilaya = card.dataset.wilaya || '';
    const matchQ = !q || name.includes(q) || wilaya.toLowerCase().includes(q);
    const matchW = !_dealerWilaya || wilaya === _dealerWilaya;
    card.style.display = (matchQ && matchW) ? '' : 'none';
  });
}
function setDealerWilaya(w, el) {
  _dealerWilaya = w;
  document.querySelectorAll('.Agents-filter-btn').forEach(b => b.classList.remove('active'));
  el.classList.add('active');
  filterAgents();
}

/* ── Toggle expand panels (one at a time per card) ── */
const _dcLoaded = {};
function dcToggle(btn, panelId, agentId, type) {
  const panel = document.getElementById(panelId);
  const isOpen = panel.classList.contains('open');
  
  // أغلق فقط panels الكرت نفسه
  const card = btn.closest('.dealer-card');
  card.querySelectorAll('.dc-expand').forEach(p => p.classList.remove('open'));
  card.querySelectorAll('.dc-toggle-btn').forEach(b => b.classList.remove('open'));
  
  if (!isOpen) {
    panel.classList.add('open');
    btn.classList.add('open');
    if (!_dcLoaded[panelId]) {
      _dcLoaded[panelId] = true;
      type === 'reviews' ? dcLoadReviews(agentId) : dcLoadCars(agentId);
    }
  }
}

/* ── Load cars ── */
function dcLoadCars(agentId) {
  fetch('dealers.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'action=get_cars&agent_id=' + agentId
  }).then(r => r.json()).then(cars => {
    const el = document.getElementById('dc-cars-list-' + agentId);
    if (!cars.length) {
      el.innerHTML = '<div class="dc-no-reviews">No cars listed yet.</div>';
      return;
    }
    el.innerHTML = cars.map(c => `
      <a class="dc-car-item" href="index.php#car-${c.id}">
        <div>
          <div class="dc-car-name">${dcEsc(c.title)}</div>
          <div class="dc-car-meta">${dcEsc(c.brand||'')} ${c.year||''} · ${dcEsc(c.wilaya||'')} · ${dcEsc(c.fuel_type||'')}${c.mileage?' · '+Number(c.mileage).toLocaleString()+' km':''}</div>
        </div>
        <div class="dc-car-price">${c.price ? Number(c.price).toLocaleString('fr-DZ')+' DZD' : ''}</div>
      </a>`).join('');
  });
}

/* ── Load reviews ── */
function dcLoadReviews(agentId) {
  fetch('dealers.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'action=get_reviews&agent_id=' + agentId
  }).then(r => r.json()).then(reviews => dcRenderReviews(agentId, reviews));
}
function dcRenderReviews(agentId, reviews) {
  const el = document.getElementById('dc-reviews-list-' + agentId);
  if (!reviews.length) {
    el.innerHTML = '<div class="dc-no-reviews">No reviews yet — be the first!</div>';
    return;
  }
  el.innerHTML = reviews.map(rv => {
    const initials = dcEsc(String(rv.reviewer_name||'?').charAt(0).toUpperCase());
    const avatarHtml = (rv.reviewer_photo && rv.reviewer_photo !== '' && rv.reviewer_photo !== null)
    ? `<img class="dc-rev-avatar" src="${dcEsc(rv.reviewer_photo)}" alt="" onerror="this.style.display='none';if(this.nextElementSibling)this.nextElementSibling.style.display='flex'"><div class="dc-rev-initials-sm" style="display:none">${initials}</div>`
      : `<div class="dc-rev-initials-sm">${initials}</div>`;
    const isOwner = rv.reviewer_id && parseInt(rv.reviewer_id) === parseInt(CURRENT_USER_ID);
    const replyCount = rv.reply_count || 0;
    return `
    <div class="dc-review-item" id="dcrv-${rv.id}">
      <div class="dc-rev-head">
        <div class="dc-rev-user">
          ${avatarHtml}
          <div>
            <div class="dc-rev-name">${dcEsc(rv.reviewer_name||'Anonymous')}</div>
            <div class="dc-rev-stars">${dcStars(rv.rating)}</div>
          </div>
        </div>
        <div style="display:flex;align-items:center;gap:.5rem">
          ${isOwner ? `
            <button class="dc-rev-btn danger" onclick="dcDeleteReview(${rv.id},${agentId})" style="color:#f87171;border-color:rgba(239,68,68,0.3);font-size:.7rem;padding:.15rem .5rem">🗑</button>
              <button class="dc-rev-btn" onclick="dcStartEdit(${rv.id},'${dcEsc(rv.comment).replace(/'/g,"\\'")}',${rv.rating},${agentId})" style="color:#fbbf24;border-color:rgba(251,191,36,0.3);font-size:.7rem;padding:.15rem .5rem">✏️</button>
          ` : ''}
          <span class="dc-rev-date">${dcFmtDate(rv.created_at)}</span>
        </div>
      </div>
      <div class="dc-rev-body" id="dcrv-body-${rv.id}">${dcEsc(rv.comment)}</div>
      ${rv.reply ? `<div class="dc-reply-block">↩ Dealer reply: ${dcEsc(rv.reply)}</div>` : ''}
      <div class="dc-rev-actions" style="display:flex;gap:.5rem;margin-top:.6rem;align-items:center">
        <button class="dc-rev-btn" onclick="dcLikeReview(${rv.id},${agentId})" style="display:flex;align-items:center;gap:4px;border-radius:50px">
          ❤️ <span id="dc-likes-${rv.id}">${rv.likes||0}</span>
        </button>
        <button class="dc-rev-btn" onclick="dcToggleReplies(${rv.id},${agentId})" style="border-radius:50px">
          💬 Replies (<span id="dc-replycount-${rv.id}">${replyCount}</span>)
        </button>
        <button onclick="dcOpenReportReview('dealer_review',${rv.id},'${dcEsc(rv.comment||'').substring(0,80).replace(/'/g,"\\'")}')" style="background:none;border:1px solid rgba(239,68,68,0.2);color:rgba(239,68,68,0.6);font-size:.72rem;padding:.2rem .65rem;border-radius:6px;cursor:pointer;font-family:'DM Sans',sans-serif;">⚑ Report</button>
      </div>
      <div id="dc-replies-section-${rv.id}" style="display:none;margin-top:.6rem">
        <div id="dc-replies-list-${rv.id}"></div>
        <div style="display:flex;gap:.5rem;margin-top:.5rem">
          <textarea rows="2" placeholder="Write a reply…" id="dcrft-${rv.id}"
            style="flex:1;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.1);border-radius:8px;padding:.4rem .7rem;color:#fff;font-family:'DM Sans',sans-serif;font-size:.79rem;outline:none;resize:none"></textarea>
          <button class="dc-btn-submit" style="font-size:.76rem;align-self:flex-end" onclick="dcSubmitReply(${rv.id},${agentId})">Reply</button>
        </div>
      </div>
    </div>`;
  }).join('');
}

function dcToggleReplies(reviewId, agentId) {
  const sec = document.getElementById('dc-replies-section-' + reviewId);
  const isOpen = sec.style.display !== 'none';
  if (isOpen) { sec.style.display = 'none'; return; }
  sec.style.display = 'block';
  dcLoadReplies(reviewId, agentId);
}

function dcLoadReplies(reviewId, agentId) {
  fetch('dealers.php', {
    method: 'POST',
    headers: {'Content-Type':'application/x-www-form-urlencoded'},
    body: `action=get_replies&review_id=${reviewId}`
  }).then(r=>r.json()).then(replies => {
    const el = document.getElementById('dc-replies-list-' + reviewId);
    if (!replies.length) { el.innerHTML = '<div style="font-size:.78rem;color:rgba(255,255,255,0.3);padding:.3rem 0">No replies yet</div>'; return; }
    el.innerHTML = replies.map(rp => {
      const ini = dcEsc(String(rp.user_name||'?').charAt(0).toUpperCase());
      const ava = rp.user_photo
        ? `<img src="${dcEsc(rp.user_photo)}" style="width:26px;height:26px;border-radius:50%;object-fit:cover">`
        : `<div style="width:26px;height:26px;border-radius:50%;background:linear-gradient(135deg,#1e40af,#0e7490);display:flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:700;color:#fff">${ini}</div>`;
      const canDelete = rp.user_id && rp.user_id == CURRENT_USER_ID;
      return `<div style="display:flex;gap:.5rem;align-items:flex-start;padding:.4rem 0;border-bottom:1px solid rgba(255,255,255,0.05)">
  ${ava}
  <div style="flex:1">
    <div style="display:flex;justify-content:space-between;align-items:center">
      <div style="font-size:.78rem;font-weight:600;color:#fff">${dcEsc(rp.user_name||'Anonymous')}</div>
      ${canDelete ? `
        <div style="display:flex;gap:.3rem">
          <button onclick="dcEditReply(${rp.id},'${dcEsc(rp.content).replace(/'/g,"\\'")}',${reviewId},${agentId})"
            style="background:none;border:none;color:#fbbf24;font-size:.75rem;cursor:pointer;padding:.1rem .2rem">✏️</button>
          <button onclick="dcDeleteReply(${rp.id},${reviewId},${agentId})"
            style="background:none;border:none;color:#f87171;font-size:.75rem;cursor:pointer;padding:.1rem .2rem">🗑</button>
        </div>` : ''}
    </div>
    <div id="rp-body-${rp.id}" style="font-size:.78rem;color:rgba(255,255,255,0.6)">${dcEsc(rp.content)}</div>
  </div>
</div>`;
    }).join('');
  });
}

function dcLikeReview(reviewId, agentId) {
  fetch('dealers.php', {
    method: 'POST',
    headers: {'Content-Type':'application/x-www-form-urlencoded'},
    body: `action=like_review&review_id=${reviewId}`
  }).then(r=>r.json()).then(res => {
    if (res.likes !== undefined) document.getElementById('dc-likes-' + reviewId).textContent = res.likes;
  });
}

function dcDeleteReply(replyId, reviewId, agentId) {
  fetch('dealers.php', {
    method: 'POST',
    headers: {'Content-Type':'application/x-www-form-urlencoded'},
    body: `action=delete_reply&reply_id=${replyId}`
  }).then(r=>r.json()).then(() => dcLoadReplies(reviewId, agentId));
}
function dcEditReply(replyId, oldText, reviewId, agentId) {
  const bodyEl = document.getElementById('rp-body-' + replyId);
  if (!bodyEl) return;
  bodyEl.innerHTML = `
    <textarea id="rpedit-${replyId}" rows="2"
      style="width:100%;background:rgba(255,255,255,0.05);border:1px solid rgba(59,130,246,0.4);
             border-radius:7px;padding:.4rem .6rem;color:#fff;font-family:'DM Sans',sans-serif;
             font-size:.78rem;resize:none;outline:none;box-sizing:border-box">${oldText}</textarea>
    <div style="display:flex;gap:.4rem;margin-top:.3rem">
      <button onclick="dcSaveReplyEdit(${replyId},${reviewId},${agentId})"
        style="background:linear-gradient(135deg,#1e40af,#0e7490);color:#fff;border:none;
               border-radius:6px;font-size:.75rem;padding:.25rem .7rem;cursor:pointer">Save</button>
      <button onclick="dcLoadReplies(${reviewId},${agentId})"
        style="background:none;border:1px solid rgba(255,255,255,0.15);color:rgba(255,255,255,0.5);
               border-radius:6px;font-size:.75rem;padding:.25rem .7rem;cursor:pointer">Cancel</button>
    </div>`;
}

function dcSaveReplyEdit(replyId, reviewId, agentId) {
  const text = document.getElementById('rpedit-' + replyId)?.value.trim();
  if (!text) return;
  fetch('dealers.php', {
    method: 'POST',
    headers: {'Content-Type':'application/x-www-form-urlencoded'},
    body: `action=edit_reply&reply_id=${replyId}&content=${encodeURIComponent(text)}`
  }).then(r=>r.json()).then(() => dcLoadReplies(reviewId, agentId));
}
function dcStars(n) {
  let s = '';
  for (let i=1;i<=5;i++) s += `<span style="color:${i<=n?'#f59e0b':'rgba(255,255,255,0.12)'}">★</span>`;
  return s;
}
function dcFmtDate(d) {
  if (!d) return '';
  return new Date(d).toLocaleDateString('en-GB', { year:'numeric', month:'short', day:'numeric' });
}
function dcEsc(s) {
  return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

/* ── Star picker ── */
function dcSetStar(agentId, val) {
  const picker = document.getElementById('dcpicker-' + agentId);
  picker.dataset.val = val;
  picker.querySelectorAll('span').forEach((s,i) => s.classList.toggle('sel', i < val));
}

/* ── Submit review ── */
function dcSubmitReview(agentId) {
  const rating  = parseInt(document.getElementById('dcpicker-' + agentId).dataset.val) || 0;
  const comment = document.getElementById('dctext-' + agentId).value.trim();
  if (!rating)  { showToast('Please select a star rating'); return; }
  if (!comment) { showToast('Please write your review'); return; }
  fetch('dealers.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `action=add_review&agent_id=${agentId}&rating=${rating}&comment=${encodeURIComponent(comment)}`
  }).then(r => r.json()).then(res => {
    if (res.success) {
      document.getElementById('dctext-' + agentId).value = '';
      dcSetStar(agentId, 0);
      _dcLoaded['dc-reviews-' + agentId] = false;
      dcLoadReviews(agentId);
      showToast('Review submitted!');
    } else {
      showToast(res.msg || 'Error submitting review');
    }
  });
}

/* ── Delete review ── */
function dcDeleteReview(reviewId, agentId) {
  if (!confirm('Delete this review?')) return;
  fetch('dealers.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `action=delete_review&review_id=${reviewId}`
  }).then(r => r.json()).then(res => {
    if (res.success) document.getElementById('dcrv-' + reviewId)?.remove();
  });
}

/* ── Edit review ── */
function dcStartEdit(reviewId, oldText, oldRating, agentId) {
  const bodyEl = document.getElementById('dcrv-body-' + reviewId);
  if (!bodyEl) return;
  bodyEl.innerHTML = `
    <div class="dc-star-picker" id="dcedit-picker-${reviewId}" data-val="${oldRating}" style="margin-bottom:.3rem">
      ${[1,2,3,4,5].map(i=>`<span style="font-size:1.3rem;cursor:pointer;color:${i<=oldRating?'#f59e0b':'rgba(255,255,255,0.15)'}" onclick="dcSetEditStar(${reviewId},${i})">★</span>`).join('')}
    </div>
    <textarea id="dcedit-text-${reviewId}" rows="3" style="width:100%;background:rgba(255,255,255,0.05);border:1px solid rgba(59,130,246,0.4);border-radius:8px;padding:.5rem .8rem;color:#fff;font-family:'DM Sans',sans-serif;font-size:.82rem;resize:none;outline:none;box-sizing:border-box">${oldText}</textarea>
    <div style="display:flex;gap:.5rem;margin-top:.4rem">
      <button class="dc-btn-submit" style="font-size:.76rem" onclick="dcSaveEdit(${reviewId},${agentId})">Save</button>
      <button class="dc-rev-btn" style="font-size:.76rem" onclick="dcLoadReviews(${agentId})">Cancel</button>
    </div>`;
}

function dcSetEditStar(reviewId, val) {
  const p = document.getElementById('dcedit-picker-' + reviewId);
  if (!p) return;
  p.dataset.val = val;
  p.querySelectorAll('span').forEach((s,i) => s.style.color = i < val ? '#f59e0b' : 'rgba(255,255,255,0.15)');
}

function dcSaveEdit(reviewId, agentId) {
  const comment = document.getElementById('dcedit-text-' + reviewId)?.value.trim();
  const rating = parseInt(document.getElementById('dcedit-picker-' + reviewId)?.dataset.val) || 0;
  if (!comment || !rating) { showToast('Please fill all fields'); return; }
  fetch('dealers.php', {
    method: 'POST',
    headers: {'Content-Type':'application/x-www-form-urlencoded'},
    body: `action=edit_review&review_id=${reviewId}&comment=${encodeURIComponent(comment)}&rating=${rating}`
  }).then(r=>r.json()).then(() => {
    _dcLoaded['dc-reviews-' + agentId] = false;
    dcLoadReviews(agentId);
    showToast('Review updated!');
  });
}

/* ── Reply ── */
function dcToggleReply(reviewId) {
  const rf = document.getElementById('dcrf-' + reviewId);
  rf.style.display = rf.style.display === 'block' ? 'none' : 'block';
}
function dcSubmitReply(reviewId, agentId) {
  const reply = document.getElementById('dcrft-' + reviewId).value.trim();
  if (!reply) return;
  fetch('dealers.php', {
    method: 'POST',
    headers: {'Content-Type':'application/x-www-form-urlencoded'},
    body: `action=reply_review&review_id=${reviewId}&reply=${encodeURIComponent(reply)}`
  }).then(r=>r.json()).then(res => {
    document.getElementById('dcrft-' + reviewId).value = '';
    if (res.count !== undefined) document.getElementById('dc-replycount-' + reviewId).textContent = res.count;
    dcLoadReplies(reviewId, agentId);
  });
}
/* ── Toast helper ── */
function showToast(msg) {
  const t = document.getElementById('toast');
  if (!t) return;
  t.textContent = msg; t.classList.add('show');
  setTimeout(() => t.classList.remove('show'), 3000);
}
/* ══ CONTACT MODAL ══ */
let _dcAgentId = null;

function dcOpenContact(agentId, name, phone, wa, email) {
  _dcAgentId = agentId;

  document.getElementById('dcContactName').textContent = '📞 Contact: ' + name;

  // Phone
  const phoneDiv = document.getElementById('dcContactPhoneDiv');
  if (phone) {
    phoneDiv.style.display = 'block';
    document.getElementById('dcContactPhoneLink').href = 'tel:' + phone;
    document.getElementById('dcContactPhoneNum').textContent = phone;
  } else {
    phoneDiv.style.display = 'none';
  }

  // WhatsApp
  const waDiv = document.getElementById('dcContactWADiv');
  if (wa) {
    waDiv.style.display = 'block';
    const waNum = wa.replace(/^0/, '');
    document.getElementById('dcContactWALink').href = 'https://wa.me/213' + waNum;
  } else {
    waDiv.style.display = 'none';
  }

  // Email
  const emailDiv = document.getElementById('dcContactEmailDiv');
  if (email) {
    emailDiv.style.display = 'block';
    document.getElementById('dcContactEmailLink').href = 'mailto:' + email;
    document.getElementById('dcContactEmailAddr').textContent = email;
  } else {
    emailDiv.style.display = 'none';
  }

  // Clear msg
  const msgEl = document.getElementById('dcSiteMsg');
  if (msgEl) msgEl.value = '';

  document.getElementById('dcContactModal').style.display = 'flex';
  document.body.style.overflow = 'hidden';
}

function dcCloseContact() {
  document.getElementById('dcContactModal').style.display = 'none';
  document.body.style.overflow = '';
}

// إغلاق بالضغط خارج الـ modal
document.addEventListener('DOMContentLoaded', function() {
  var m = document.getElementById('dcContactModal');
  if (m) m.addEventListener('click', function(e) {
    if (e.target === this) dcCloseContact();
  });
});

async function dcSendSiteMsg() {
  const msg = document.getElementById('dcSiteMsg')?.value?.trim();
  if (!msg) { showToast('Please write a message'); return; }
  if (!_dcAgentId) return;

  try {
    const res = await fetch('send_message.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        agent_id: _dcAgentId,
        car_id: 0,          // مافيش car محدد من صفحة Agents
        message: msg
      })
    });
    const data = await res.json();
    if (data.success) {
  dcCloseContact();
  // روحي لصفحة الشات
  window.location.href = 'profile.php?tab=messages&conv=' + data.conv_id;
}
    else {
      showToast(data.error || 'Error sending message');
    }
  } catch(e) {
    showToast('Connection error');
  }
}
</script>
<!-- ══ CONTACT MODAL ══ -->
<div id="dcContactModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.75);
     z-index:9999;align-items:center;justify-content:center;backdrop-filter:blur(6px);">
  <div style="background:#161616;border:1px solid rgba(255,255,255,0.1);border-radius:20px;
              padding:2rem;width:92%;max-width:430px;position:relative;max-height:90vh;overflow-y:auto;">

    <!-- Close -->
    <button onclick="dcCloseContact()"
      style="position:absolute;top:1rem;right:1rem;background:rgba(255,255,255,0.06);
             border:none;color:#fff;width:30px;height:30px;border-radius:50%;
             cursor:pointer;font-size:1rem;display:flex;align-items:center;justify-content:center;">✕</button>

    <!-- Dealer name -->
    <h3 id="dcContactName"
      style="color:#fff;font-family:'Syne',sans-serif;margin-bottom:1.5rem;font-size:1.1rem;padding-right:2rem;"></h3>

    <!-- Phone -->
    <div id="dcContactPhoneDiv" style="display:none;margin-bottom:.75rem;">
      <a id="dcContactPhoneLink" href="#"
        style="display:flex;align-items:center;gap:10px;
               background:linear-gradient(135deg,#1e40af,#1d4ed8);
               color:#fff;padding:.85rem 1.2rem;border-radius:12px;
               text-decoration:none;font-weight:600;font-size:.9rem;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07
                   A19.5 19.5 0 0 1 4.69 13.1 19.79 19.79 0 0 1 1.65 4.59
                   A2 2 0 0 1 3.62 2.43h3a2 2 0 0 1 2 1.72
                   12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91
                   a16 16 0 0 0 6 6l.81-.81a2 2 0 0 1 2.11-.45
                   12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 21.73 17z"/>
        </svg>
        Call: <span id="dcContactPhoneNum"></span>
      </a>
    </div>

    <!-- WhatsApp -->
    <div id="dcContactWADiv" style="display:none;margin-bottom:.75rem;">
      <a id="dcContactWALink" href="#" target="_blank" rel="noopener"
        style="display:flex;align-items:center;gap:10px;
               background:#166534;color:#fff;padding:.85rem 1.2rem;
               border-radius:12px;text-decoration:none;font-weight:600;font-size:.9rem;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
          <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15
                   -.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075
                   -.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059
                   -.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52
                   .149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52
                   -.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51
                   -.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372
                   -.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074
                   .149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625
                   .712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413
                   .248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347
                   m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214
                   -3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26
                   c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898
                   a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884
                   m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892
                   c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654
                   a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893
                   a11.821 11.821 0 0 0-3.48-8.413z"/>
        </svg>
        WhatsApp
      </a>
    </div>

    <!-- Email -->
    <div id="dcContactEmailDiv" style="display:none;margin-bottom:.75rem;">
      <a id="dcContactEmailLink" href="#"
        style="display:flex;align-items:center;gap:10px;
               background:rgba(255,255,255,0.05);
               border:1px solid rgba(255,255,255,0.12);
               color:#fff;padding:.85rem 1.2rem;border-radius:12px;
               text-decoration:none;font-weight:600;font-size:.9rem;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
          <polyline points="22,6 12,13 2,6"/>
        </svg>
        Email: <span id="dcContactEmailAddr" style="color:#60a5fa;font-size:.85rem;"></span>
      </a>
    </div>

    <!-- Divider -->
    <div style="border-top:1px solid rgba(255,255,255,0.08);margin:1.1rem 0;"></div>

    <!-- Site message -->
    <?php if (isset($_SESSION['user_id'])): ?>
    <p style="color:rgba(255,255,255,0.5);font-size:.82rem;margin-bottom:.6rem;">
      Or send a message directly from the site:
    </p>
    <textarea id="dcSiteMsg" rows="3"
      placeholder="Write your message to the dealer..."
      style="width:100%;background:rgba(255,255,255,0.04);
             border:1px solid rgba(255,255,255,0.1);border-radius:10px;
             padding:.65rem .9rem;color:#fff;
             font-family:'DM Sans',sans-serif;font-size:.83rem;
             outline:none;resize:none;box-sizing:border-box;
             transition:border-color .2s;">
    </textarea>
    <button onclick="dcSendSiteMsg()"
      style="margin-top:.6rem;width:100%;
             background:linear-gradient(135deg,#1e40af,#0e7490);
             color:#fff;border:none;padding:.65rem;border-radius:10px;
             font-family:'DM Sans',sans-serif;font-weight:600;
             font-size:.85rem;cursor:pointer;transition:opacity .2s;">
      Send Message
    </button>
    <?php else: ?>
    <div style="text-align:center;padding:.75rem;background:rgba(59,130,246,0.06);
                border:1px solid rgba(59,130,246,0.15);border-radius:10px;
                font-size:.83rem;color:rgba(255,255,255,0.5);">
      <a href="login.html" style="color:#60a5fa;font-weight:600;">Sign in</a> to send a message
    </div>
    <?php endif; ?>

  </div>
</div>
<style id="lm-fix">
</style>
<script>
function applyLightModeFix() {
  const style = document.getElementById('lm-fix');
  if (document.body.classList.contains('light-mode')) {
    style.textContent = `
      .dealer-card { background: #fff !important; border-color: #ccc !important; }
      .dc-name { color: #000 !important; }
      .dc-company { color: #1d4ed8 !important; }
      .dc-wilaya { color: #444 !important; }
      .dc-rating-text { color: #555 !important; }
      .dc-bio { color: #333 !important; }
      .dc-stat-num { color: #1d4ed8 !important; }
      .dc-stat-lbl { color: #555 !important; }
      .dc-stats { background: #f5f5f5 !important; border-color: #ddd !important; }
      .dc-stat { border-color: #ddd !important; }
      .dc-toggle-btn { background: #eee !important; color: #222 !important; border-color: #ccc !important; }
      .dc-expand { background: #f9f9f9 !important; border-color: #ddd !important; }
      .dc-car-item { background: #f0f0f0 !important; color: #000 !important; border-color: #ddd !important; }
      .dc-car-name { color: #000 !important; }
      .dc-car-meta { color: #555 !important; }
      .dc-review-item { background: #fff !important; border-color: #ddd !important; }
      .dc-rev-name { color: #000 !important; }
      .dc-rev-body { color: #333 !important; }
      .dc-rev-date { color: #777 !important; }
      .dc-rev-btn { color: #444 !important; border-color: #ccc !important; }
      .dc-review-user-row { background: #eee !important; border-color: #ccc !important; }
      .dc-review-user-name { color: #000 !important; }
      .dc-review-user-sub { color: #555 !important; }
      .dc-review-form textarea { background: #eee !important; color: #000 !important; border-color: #ccc !important; }
      .dc-no-reviews { color: #555 !important; }
      .dc-actions { background: #fff !important; border-color: #ddd !important; }
      .Agents-filter-btn { background: #eee !important; color: #333 !important; border-color: #ccc !important; }
      .Agents-filter-btn.active { background: #dbeafe !important; color: #1d4ed8 !important; border-color: #3b82f6 !important; }
    `;
  } else {
    style.textContent = '';
  }
}

// شغّل عند كل تغيير في الـ theme
const _origToggle = window.toggleTheme;
window.toggleTheme = function() {
  _origToggle && _origToggle();
  setTimeout(applyLightModeFix, 50);
};

// شغّل عند تحميل الصفحة
document.addEventListener('DOMContentLoaded', applyLightModeFix);
/* ══ REPORT MODAL ══ */
let _dcReportAgentId = null;

function dcOpenReport(agentId, name) {
  _dcReportAgentId = agentId;
  document.getElementById('dcReportDealerName').textContent = 'Dealer: ' + name;
  document.querySelectorAll('input[name="dcReportReason"]').forEach(r => r.checked = false);
  document.getElementById('dcReportDetails').value = '';
  document.getElementById('dcReportModal').style.display = 'flex';
  document.body.style.overflow = 'hidden';
}

function dcCloseReport() {
  document.getElementById('dcReportModal').style.display = 'none';
  document.body.style.overflow = '';
}

document.addEventListener('DOMContentLoaded', function() {
  var modal = document.getElementById('dcReportModal');
  if (modal) {
    modal.addEventListener('click', function(e) {
      if (e.target === this) dcCloseReport();
    });
  }
});

async function dcSubmitReport() {
  const reason = document.querySelector('input[name="dcReportReason"]:checked')?.value;
  const details = document.getElementById('dcReportDetails').value.trim();
  if (!reason) { showToast('Please select a reason'); return; }
  if (!_dcReportAgentId) return;

  try {
    const res = await fetch('report_dealer.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        agent_id: _dcReportAgentId,
        reason: reason,
        details: details
      })
    });
    const data = await res.json();
    if (data.success) {
      dcCloseReport();
      showToast('Report submitted. Thank you!');
    } else {
      showToast(data.error || 'Error submitting report');
    }
  } catch(e) {
    showToast('Connection error');
  }
}
</script>
<!-- ══ REPORT MODAL ══ -->
<div id="dcReportModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.75);
     z-index:9999;align-items:center;justify-content:center;backdrop-filter:blur(6px);">
  <div style="background:#161616;border:1px solid rgba(255,255,255,0.1);border-radius:20px;
              padding:2rem;width:92%;max-width:420px;position:relative;">

    <button onclick="dcCloseReport()"
      style="position:absolute;top:1rem;right:1rem;background:rgba(255,255,255,0.06);
             border:none;color:#fff;width:30px;height:30px;border-radius:50%;
             cursor:pointer;font-size:1rem;display:flex;align-items:center;justify-content:center;">✕</button>

    <div style="display:flex;align-items:center;gap:10px;margin-bottom:1.4rem;">
      <div style="width:36px;height:36px;border-radius:10px;background:rgba(239,68,68,0.12);
                  border:1px solid rgba(239,68,68,0.3);display:flex;align-items:center;justify-content:center;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#f87171" stroke-width="2.2">
          <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/>
          <line x1="4" y1="22" x2="4" y2="15"/>
        </svg>
      </div>
      <div>
        <h3 style="color:#fff;font-family:'Syne',sans-serif;font-size:1.05rem;margin:0;">Report Dealer</h3>
        <p id="dcReportDealerName" style="color:rgba(255,255,255,0.4);font-size:.78rem;margin:2px 0 0;"></p>
      </div>
    </div>

    <p style="color:rgba(255,255,255,0.45);font-size:.82rem;margin-bottom:.9rem;">Select the reason for your report:</p>

    <div style="display:flex;flex-direction:column;gap:.5rem;margin-bottom:1rem;" id="dcReportReasons">

      <label class="dc-report-reason">
        <input type="radio" name="dcReportReason" value="Fraud or scam">
        <div class="dc-report-reason-icon" style="background:rgba(239,68,68,0.1);border-color:rgba(239,68,68,0.25);">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#f87171" stroke-width="2">
            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
            <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
          </svg>
        </div>
        <span>Fraud or scam</span>
      </label>

      <label class="dc-report-reason">
        <input type="radio" name="dcReportReason" value="Fake car listings">
        <div class="dc-report-reason-icon" style="background:rgba(251,191,36,0.08);border-color:rgba(251,191,36,0.25);">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#fbbf24" stroke-width="2">
            <rect x="1" y="3" width="15" height="13" rx="1"/><path d="M16 8l4 2v5h-4z"/>
            <circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>
          </svg>
        </div>
        <span>Fake car listings</span>
      </label>

      <label class="dc-report-reason">
        <input type="radio" name="dcReportReason" value="Abusive or inappropriate behavior">
        <div class="dc-report-reason-icon" style="background:rgba(168,85,247,0.08);border-color:rgba(168,85,247,0.25);">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#c084fc" stroke-width="2">
            <circle cx="12" cy="12" r="10"/>
            <line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/>
          </svg>
        </div>
        <span>Abusive or inappropriate behavior</span>
      </label>

      <label class="dc-report-reason">
        <input type="radio" name="dcReportReason" value="Wrong or misleading information">
        <div class="dc-report-reason-icon" style="background:rgba(59,130,246,0.08);border-color:rgba(59,130,246,0.25);">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#60a5fa" stroke-width="2">
            <circle cx="12" cy="12" r="10"/>
            <line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
          </svg>
        </div>
        <span>Wrong or misleading information</span>
      </label>

      <label class="dc-report-reason">
        <input type="radio" name="dcReportReason" value="Other">
        <div class="dc-report-reason-icon" style="background:rgba(255,255,255,0.05);border-color:rgba(255,255,255,0.12);">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.5)" stroke-width="2">
            <circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/><circle cx="5" cy="12" r="1"/>
          </svg>
        </div>
        <span>Other</span>
      </label>

    </div>

    <textarea id="dcReportDetails" rows="3" placeholder="Add more details (optional)..."
      style="width:100%;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.1);
             border-radius:10px;padding:.65rem .9rem;color:#fff;font-family:'DM Sans',sans-serif;
             font-size:.83rem;outline:none;resize:none;box-sizing:border-box;
             margin-bottom:.9rem;transition:border-color .2s;"></textarea>

    <button onclick="dcSubmitReport()"
      style="width:100%;background:linear-gradient(135deg,#dc2626,#b91c1c);
             color:#fff;border:none;padding:.7rem;border-radius:10px;
             font-family:'DM Sans',sans-serif;font-weight:600;
             font-size:.88rem;cursor:pointer;transition:opacity .2s;
             display:flex;align-items:center;justify-content:center;gap:8px;">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
        <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/>
        <line x1="4" y1="22" x2="4" y2="15"/>
      </svg>
      Submit Report
    </button>
  </div>
</div>
<!-- REPORT REVIEW MODAL -->
<div id="dcReportReviewModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.75);z-index:9999;align-items:center;justify-content:center;backdrop-filter:blur(6px);">
  <div style="background:#161616;border:1px solid rgba(255,255,255,0.1);border-radius:20px;padding:2rem;width:92%;max-width:420px;position:relative;">
    <button onclick="dcCloseReportReview()" style="position:absolute;top:1rem;right:1rem;background:rgba(255,255,255,0.06);border:none;color:#fff;width:30px;height:30px;border-radius:50%;cursor:pointer;font-size:1rem;">✕</button>
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:1.4rem;">
      <div style="width:36px;height:36px;border-radius:10px;background:rgba(239,68,68,0.12);border:1px solid rgba(239,68,68,0.3);display:flex;align-items:center;justify-content:center;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#f87171" stroke-width="2"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/></svg>
      </div>
      <h3 style="color:#fff;font-family:'Syne',sans-serif;font-size:1.05rem;margin:0;">Report Review</h3>
    </div>
    <p style="color:rgba(255,255,255,0.45);font-size:.82rem;margin-bottom:.9rem;">Select the reason:</p>
    <div style="display:flex;flex-direction:column;gap:.5rem;margin-bottom:1rem;">
      <label style="display:flex;align-items:center;gap:10px;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.07);border-radius:10px;padding:.6rem .9rem;cursor:pointer;color:rgba(255,255,255,0.6);font-size:.84rem;font-family:'DM Sans',sans-serif;">
        <input type="radio" name="dcReportReviewReason" value="Inappropriate content"> Inappropriate content
      </label>
      <label style="display:flex;align-items:center;gap:10px;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.07);border-radius:10px;padding:.6rem .9rem;cursor:pointer;color:rgba(255,255,255,0.6);font-size:.84rem;font-family:'DM Sans',sans-serif;">
        <input type="radio" name="dcReportReviewReason" value="Spam"> Spam
      </label>
      <label style="display:flex;align-items:center;gap:10px;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.07);border-radius:10px;padding:.6rem .9rem;cursor:pointer;color:rgba(255,255,255,0.6);font-size:.84rem;font-family:'DM Sans',sans-serif;">
        <input type="radio" name="dcReportReviewReason" value="Harassment"> Harassment
      </label>
      <label style="display:flex;align-items:center;gap:10px;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.07);border-radius:10px;padding:.6rem .9rem;cursor:pointer;color:rgba(255,255,255,0.6);font-size:.84rem;font-family:'DM Sans',sans-serif;">
        <input type="radio" name="dcReportReviewReason" value="False information"> False information
      </label>
      <label style="display:flex;align-items:center;gap:10px;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.07);border-radius:10px;padding:.6rem .9rem;cursor:pointer;color:rgba(255,255,255,0.6);font-size:.84rem;font-family:'DM Sans',sans-serif;">
        <input type="radio" name="dcReportReviewReason" value="Other"> Other
      </label>
    </div>
    <button onclick="dcSubmitReportReview()" style="width:100%;background:linear-gradient(135deg,#dc2626,#b91c1c);color:#fff;border:none;padding:.7rem;border-radius:10px;font-family:'DM Sans',sans-serif;font-weight:600;font-size:.88rem;cursor:pointer;">
      Submit Report
    </button>
  </div>
</div>

<script>
let _dcRepType = null, _dcRepId = null, _dcRepText = '';

function dcOpenReportReview(type, id, text) {
  if (!CURRENT_USER_ID) { window.location.href = 'login.html'; return; }
  _dcRepType = type;
  _dcRepId = id;
  _dcRepText = text;
  document.querySelectorAll('input[name="dcReportReviewReason"]').forEach(r => r.checked = false);
  document.getElementById('dcReportReviewModal').style.display = 'flex';
}

function dcCloseReportReview() {
  document.getElementById('dcReportReviewModal').style.display = 'none';
}

async function dcSubmitReportReview() {
  const reason = document.querySelector('input[name="dcReportReviewReason"]:checked')?.value;
  if (!reason) { showToast('Please select a reason'); return; }
  try {
    const res = await fetch('report_comment.php', {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({
        comment_type: _dcRepType,
        comment_id: _dcRepId,
        comment_text: _dcRepText,
        reason: reason,
        page_url: window.location.href
      })
    });
    const data = await res.json();
    if (data.success) {
      dcCloseReportReview();
      showToast('Report submitted. Thank you!');
    } else {
      showToast(data.error || 'Error');
    }
  } catch(e) {
    showToast('Connection error');
  }
}
</script>
</body>
</html>