<?php
session_start();
require_once '../conn/conn.php';

// ---------- Guard ----------
if (empty($_SESSION['user_id'])) { header('Location: ../auth/login.php'); exit(); }
if (($_SESSION['user_role'] ?? '') === 'admin') { header('Location: ../admin/admin.php'); exit(); }

function h($s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$uid = (int)$_SESSION['user_id'];
$st  = $conn->prepare("SELECT name, email, status, is_seller FROM users WHERE id = ? AND role = 'user'");
$st->execute([$uid]);
$me = $st->fetch(PDO::FETCH_ASSOC);
if (!$me || $me['status'] !== 'active') { header('Location: ../admin/logout.php'); exit(); }

$_SESSION['csrf'] = $_SESSION['csrf'] ?? bin2hex(random_bytes(16));
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

// ---------- Seller application ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_seller'])) {
    $err = null;
    $full  = trim($_POST['full_name'] ?? '');
    $farm  = trim($_POST['farm_name'] ?? '');
    $addr  = trim($_POST['farm_address'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $plan  = trim($_POST['produce_plan'] ?? '');

    if (!hash_equals($_SESSION['csrf'], (string)($_POST['csrf'] ?? ''))) {
        $err = 'Your session expired. Please try again.';
    } elseif ($me['is_seller']) {
        $err = 'You are already a seller.';
    } elseif ($full === '' || $farm === '' || $addr === '' || $plan === '' || empty($_POST['agree'])) {
        $err = 'Please fill in every field and confirm the details are true.';
    } elseif (!preg_match('/^[0-9+()\-\s]{7,20}$/', $phone)) {
        $err = 'Please enter a valid contact number.';
    } elseif (mb_strlen($full) > 100 || mb_strlen($farm) > 100 || mb_strlen($addr) > 160 || mb_strlen($plan) > 255) {
        $err = 'One of your answers is too long.';
    } else {
        $p = $conn->prepare("SELECT 1 FROM seller_applications WHERE user_id = ? AND status = 'pending'");
        $p->execute([$uid]);
        if ($p->fetchColumn()) {
            $err = 'You already have an application waiting for review.';
        } else {
            $conn->prepare("INSERT INTO seller_applications (user_id, full_name, farm_name, farm_address, phone, produce_plan)
                            VALUES (?, ?, ?, ?, ?, ?)")->execute([$uid, $full, $farm, $addr, $phone, $plan]);
        }
    }
    $_SESSION['flash'] = $err ? ['error', $err] : ['success', 'Application sent! An admin will review it. You can keep browsing in the meantime.'];
    header('Location: buyer.php');
    exit();
}

// Sidebar state for selling
$a = $conn->prepare("SELECT status, reject_reason FROM seller_applications WHERE user_id = ? ORDER BY id DESC LIMIT 1");
$a->execute([$uid]);
$app = $a->fetch(PDO::FETCH_ASSOC) ?: ['status' => '', 'reject_reason' => ''];
$isSeller = (bool)$me['is_seller'];
$pending  = !$isSeller && $app['status'] === 'pending';
$rejected = !$isSeller && $app['status'] === 'rejected';

$welcomeSeller = $isSeller && empty($_SESSION['seller_welcome_seen']);
if ($welcomeSeller) $_SESSION['seller_welcome_seen'] = true;

// Dashboard numbers: replace with real queries once the listings/offers/orders tables exist
$activeListings = 0;
$pendingOffers  = 0;
$contractValue  = 0;

// Recent activity (real data from login_activity)
$ra = $conn->prepare("SELECT action, created_at FROM login_activity WHERE user_id = ? ORDER BY created_at DESC, id DESC LIMIT 6");
$ra->execute([$uid]);
$activity = $ra->fetchAll(PDO::FETCH_ASSOC);

$first   = explode(' ', trim($me['name']))[0];
$initial = strtoupper(mb_substr($me['name'], 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>FarmLinked | Buyer Dashboard</title>
<link rel="icon" href="../assets/images/logo.png">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@500;600;700&family=Nunito+Sans:wght@400;600;700&display=swap" rel="stylesheet">
<style>
:root{--g:#2d6e3f;--gd:#245a33;--band:#6a6767;--card:#dcdcdc;--ink:#1f2a1f;--mut:#6b756b}
*{box-sizing:border-box}
body{margin:0;font-family:'Nunito Sans',system-ui,sans-serif;color:var(--ink)}
.app{display:grid;grid-template-columns:240px 1fr;min-height:100vh}
.side{background:var(--g);color:#fff;display:flex;flex-direction:column;position:sticky;top:0;height:100vh;overflow-y:auto}
.logo{display:flex;align-items:center;gap:.6rem;padding:1.1rem 1.2rem;font:700 1.15rem Outfit,sans-serif}
.logo img{width:38px;height:38px;object-fit:contain}
.side a,.side .lnk{display:block;width:100%;padding:.85rem 1.4rem;color:#fff;text-decoration:none;font:600 1rem 'Nunito Sans',sans-serif;background:none;border:0;text-align:left;cursor:pointer}
.side a:hover,.side .lnk:hover{background:rgba(255,255,255,.12)}
.side a.active{background:rgba(255,255,255,.24)}
.side .off{display:block;padding:.85rem 1.4rem;opacity:.75;font-weight:600}
.sell{margin-top:auto;padding-top:1rem}
.sell small{display:block;padding:0 1.4rem .3rem;font-size:.7rem;text-transform:uppercase;letter-spacing:.08em;opacity:.7}
.sell .cta{margin:.2rem 1.2rem;width:calc(100% - 2.4rem);text-align:center;border:1.5px solid #fff;border-radius:8px;padding:.7rem}
.me{display:flex;align-items:center;gap:.7rem;padding:1rem 1.2rem;margin-top:.5rem;border-top:1px solid rgba(255,255,255,.2)}
.av{width:38px;height:38px;border-radius:50%;background:#d9d9d9;color:var(--g);display:grid;place-items:center;font-weight:800;flex:none}
.me b{display:block;font-size:.9rem;line-height:1.2}
.me a{display:inline;padding:0;font-size:.8rem;opacity:.85;text-decoration:underline}
.me a:hover{background:none}
.main{display:flex;flex-direction:column;min-width:0}
.band{background:var(--band);color:#fff;display:flex;align-items:center;gap:1.1rem;padding:1.1rem 2rem}
.band .av{width:56px;height:56px;background:#aaa;color:#fff;font-size:1.3rem}
.band h1{margin:0;font:500 1.7rem Outfit,sans-serif}
.add{margin-left:auto;border:1.5px solid #fff;background:transparent;color:#fff;padding:.5rem 1.3rem;border-radius:6px;font:700 .85rem 'Nunito Sans';text-decoration:none}
.add:hover{background:rgba(255,255,255,.15)}
.body{background:#fff;flex:1;padding:1.5rem 2rem}
.view{display:none}.view.active{display:block}
.stats{display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin-bottom:1.25rem}
.card{background:var(--card);border-radius:14px;padding:1rem 1.2rem;min-height:110px}
.card.hi{background:var(--g);color:#fff}
.card h3{margin:0 0 .4rem;font-size:.9rem}
.num{font:700 2rem Outfit,sans-serif}
.two{display:grid;grid-template-columns:1.1fr 1fr;gap:1rem}
.panel{background:var(--card);border-radius:14px;padding:1rem 1.2rem}
.panel h2{margin:0 0 .8rem;font-size:1rem}
.row{background:#ececec;border-radius:6px;padding:.6rem .8rem;margin-bottom:.45rem;display:flex;justify-content:space-between;font-size:.9rem}
.row span:last-child{color:var(--mut)}
.empty{color:var(--mut);text-align:center;padding:1.6rem .5rem;font-size:.92rem}
.btn{display:inline-block;border:0;background:var(--g);color:#fff;padding:.6rem 1.1rem;border-radius:9px;font:700 .9rem 'Nunito Sans';cursor:pointer;text-decoration:none}
.btn:hover{background:var(--gd)}.btn.ghost{background:#fff;color:var(--ink);border:1px solid #cfcfcf}
.tools{display:flex;gap:.6rem;margin-bottom:1rem;flex-wrap:wrap}
.tools input,.tools select{padding:.6rem .8rem;border:1px solid #cfcfcf;border-radius:9px;font:inherit}
.tools input{flex:1;min-width:200px}
table{width:100%;border-collapse:collapse;font-size:.9rem}
th{text-align:left;padding:.6rem;background:#ececec}
.chat{display:grid;grid-template-columns:240px 1fr;gap:1rem;min-height:320px}
.chat>div{background:#ececec;border-radius:10px;padding:1rem}
.note{padding:.8rem 1rem;border-radius:10px;margin-bottom:1rem;font-size:.92rem}
.note.success,.note.ok{background:#e8f6e8;border:1px solid #b9deb9;color:#1d5a20}
.note.error{background:#fdecea;border:1px solid #f3b8b3;color:#8a1c14}
.note.warn{background:#fff6e0;border:1px solid #f0d58a;color:#7a5a00}
dl{margin:0;display:grid;grid-template-columns:140px 1fr;gap:.6rem 1rem}dt{color:var(--mut)}dd{margin:0;font-weight:600}
dialog{border:0;border-radius:16px;padding:1.5rem;width:min(520px,92vw);box-shadow:0 20px 60px rgba(0,0,0,.25)}
dialog::backdrop{background:rgba(0,0,0,.45)}
dialog h2{margin:0 0 .25rem;font:700 1.3rem Outfit,sans-serif}
.sub{margin:0 0 1rem;color:var(--mut);font-size:.9rem}
label{display:block;font-weight:700;font-size:.85rem;margin:.7rem 0 .25rem}
dialog input[type=text],dialog input[type=tel],dialog textarea{width:100%;padding:.6rem .7rem;border:1px solid #cfcfcf;border-radius:9px;font:inherit}
.check{display:flex;gap:.5rem;align-items:flex-start;font-weight:400;margin-top:1rem}
.actions{display:flex;gap:.6rem;justify-content:flex-end;margin-top:1.25rem}
@media(max-width:860px){.app{grid-template-columns:1fr}.side{position:static;height:auto}.stats,.two,.chat{grid-template-columns:1fr}.band,.body{padding:1rem}}
</style>
</head>
<body>
<div class="app">

  <!-- ============ SIDEBAR ============ -->
  <aside class="side">
    <div class="logo"><img src="../assets/images/logo.png" alt=""><span>FarmLinked</span></div>

    <nav>
      <a href="#dashboard" data-view="dashboard" class="active">Dashboard</a>
      <a href="#browse" data-view="browse">Browse Listing</a>
      <a href="#orders" data-view="orders">My Orders</a>
      <a href="#messages" data-view="messages">Messages</a>
      <a href="#payments" data-view="payments">Payments</a>
      <a href="#settings" data-view="settings">Settings</a>
    </nav>

    <div class="sell">
      <small>Selling</small>
      <?php if ($isSeller): ?>
        <a href="../farmer/farmer.php">Seller Dashboard</a>
        <a href="../farmer/farmer.php#listings">Produce Listings</a>
        <a href="../farmer/farmer.php#requests">Buyer Requests</a>
        <a href="../farmer/farmer.php#orders">Orders &amp; Contracts</a>
      <?php elseif ($pending): ?>
        <span class="off">Seller application pending</span>
      <?php else: ?>
        <button type="button" class="lnk cta" id="openApply"><?php echo $rejected ? 'Apply again' : 'Become a seller'; ?></button>
      <?php endif; ?>

      <div class="me">
        <div class="av"><?php echo h($initial); ?></div>
        <div><b><?php echo h($me['name']); ?></b><a href="../admin/logout.php">Log Out</a></div>
      </div>
    </div>
  </aside>

  <!-- ============ MAIN ============ -->
  <div class="main">
    <header class="band">
      <div class="av"><?php echo h($initial); ?></div>
      <h1 id="pageTitle">Greetings, <?php echo h($first); ?></h1>
      <a href="#browse" class="add" data-view="browse">Add Order</a>
    </header>

    <div class="body">
      <?php if ($flash): ?><div class="note <?php echo h($flash[0]); ?>"><?php echo h($flash[1]); ?></div><?php endif; ?>
      <?php if ($welcomeSeller): ?>
        <div class="note ok"><strong>You're approved as a seller!</strong> Your seller tools are now in the sidebar.
          <a href="../farmer/farmer.php" style="font-weight:700;color:inherit">Open seller dashboard &rarr;</a></div>
      <?php elseif ($pending): ?>
        <div class="note warn"><strong>Your seller application is under review.</strong> You can keep browsing while you wait.</div>
      <?php elseif ($rejected): ?>
        <div class="note error"><strong>Your seller application was not approved.</strong>
          <?php echo $app['reject_reason'] ? 'Reason: ' . h($app['reject_reason']) : ''; ?> You can apply again.</div>
      <?php endif; ?>

      <!-- Dashboard -->
      <section class="view active" id="view-dashboard">
        <div class="stats">
          <article class="card hi"><h3>Active Pre-harvesting Listings</h3><div class="num"><?php echo $activeListings; ?></div></article>
          <article class="card"><h3>Pending Offers</h3><div class="num"><?php echo $pendingOffers; ?></div></article>
          <article class="card"><h3>Contract Value</h3><div class="num">₱<?php echo number_format($contractValue, 2); ?></div></article>
        </div>
        <div class="two">
          <section class="panel">
            <h2>Your Listings</h2>
            <div class="empty">You haven't pre-ordered any listings yet.<br><br><a href="#browse" class="btn" data-view="browse">Browse Listing</a></div>
          </section>
          <section class="panel">
            <h2>Recent Activity</h2>
            <?php if (!$activity): ?><div class="empty">No activity yet.</div><?php endif; ?>
            <?php foreach ($activity as $r): ?>
              <div class="row"><span><?php echo $r['action'] === 'login' ? 'Logged in' : 'Logged out'; ?></span><span><?php echo h(date('M j, g:i A', strtotime($r['created_at']))); ?></span></div>
            <?php endforeach; ?>
          </section>
        </div>
      </section>

      <!-- Browse Listing -->
      <section class="view" id="view-browse">
        <div class="tools">
          <input type="search" placeholder="Search crop or location" aria-label="Search listings">
          <select aria-label="Crop stage"><option>All stages</option><option>Planted</option><option>Growing</option><option>Ready to harvest</option></select>
        </div>
        <section class="panel"><div class="empty">No produce listings yet. Listings from approved farmers will appear here.</div></section>
      </section>

      <!-- My Orders -->
      <section class="view" id="view-orders">
        <section class="panel">
          <h2>My Orders</h2>
          <table><thead><tr><th>Order</th><th>Farmer</th><th>Crop</th><th>Quantity</th><th>Total</th><th>Status</th></tr></thead>
            <tbody><tr><td colspan="6" class="empty">You have no orders yet.</td></tr></tbody></table>
        </section>
      </section>

      <!-- Messages -->
      <section class="view" id="view-messages">
        <div class="chat">
          <div><strong>Conversations</strong><p class="empty">No conversations yet.</p></div>
          <div class="empty">Select a conversation to start messaging a farmer.</div>
        </div>
      </section>

      <!-- Payments -->
      <section class="view" id="view-payments">
        <section class="panel">
          <h2>Payments</h2>
          <table><thead><tr><th>Date</th><th>Order</th><th>Farmer</th><th>Amount</th><th>Status</th></tr></thead>
            <tbody><tr><td colspan="5" class="empty">No payments yet.</td></tr></tbody></table>
        </section>
      </section>

      <!-- Settings -->
      <section class="view" id="view-settings">
        <section class="panel">
          <h2>Account</h2>
          <dl><dt>Name</dt><dd><?php echo h($me['name']); ?></dd><dt>Email</dt><dd><?php echo h($me['email']); ?></dd></dl>
        </section>
      </section>
    </div>
  </div>
</div>

<?php if (!$isSeller && !$pending): ?>
<dialog id="applyDialog">
  <h2>Become a seller</h2>
  <p class="sub">Tell us about your farm. An admin reviews every application before you can sell.</p>
  <form method="post" action="buyer.php">
    <input type="hidden" name="csrf" value="<?php echo h($_SESSION['csrf']); ?>">
    <label for="f1">Full name</label>
    <input type="text" id="f1" name="full_name" required maxlength="100" value="<?php echo h($me['name']); ?>">
    <label for="f2">Farm name</label>
    <input type="text" id="f2" name="farm_name" required maxlength="100" placeholder="e.g. Dela Cruz Family Farm">
    <label for="f3">Farm address</label>
    <input type="text" id="f3" name="farm_address" required maxlength="160" placeholder="Barangay, town, province">
    <label for="f4">Contact number</label>
    <input type="tel" id="f4" name="phone" required maxlength="20" placeholder="09XX XXX XXXX">
    <label for="f5">What do you plan to sell?</label>
    <textarea id="f5" name="produce_plan" rows="3" required maxlength="255" placeholder="e.g. Tomato, eggplant, okra"></textarea>
    <label class="check"><input type="checkbox" name="agree" value="1" required> I confirm these details are true.</label>
    <div class="actions">
      <button type="button" class="btn ghost" id="closeApply">Cancel</button>
      <button type="submit" name="apply_seller" class="btn">Submit application</button>
    </div>
  </form>
</dialog>
<?php endif; ?>

<script>
const titles = {dashboard: <?php echo json_encode('Greetings, ' . $first); ?>, browse:'Browse Listing', orders:'My Orders', messages:'Messages', payments:'Payments', settings:'Settings'};
function show(v) {
  if (!titles[v]) v = 'dashboard';
  document.querySelectorAll('.view').forEach(s => s.classList.toggle('active', s.id === 'view-' + v));
  document.querySelectorAll('.side nav a').forEach(a => a.classList.toggle('active', a.dataset.view === v));
  document.getElementById('pageTitle').textContent = titles[v];
}
window.addEventListener('hashchange', () => show(location.hash.slice(1)));
show(location.hash.slice(1));

const d = document.getElementById('applyDialog');
document.getElementById('openApply')?.addEventListener('click', () => d.showModal());
document.getElementById('closeApply')?.addEventListener('click', () => d.close());
</script>
</body>
</html>