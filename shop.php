<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>The Workshop | Gehrman's Workshop</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700&family=EB+Garamond:ital,wght@0,400;0,500;1,400&display=swap" rel="stylesheet">

<?php
session_start();
require_once 'db_connect.php';

if(!isset($_SESSION['hunter_id'])) {
    header('Location: login.php');
    exit;
}

$sql    = "SELECT * FROM items WHERE stock > 0";
$result = mysqli_query($conn, $sql);

// Fetch this hunter's current Insight to check against item gates
$hunter_id   = $_SESSION['hunter_id'];
$insight_sql = "SELECT insight FROM hunters WHERE hunter_id = ?";
$insight_stmt = mysqli_prepare($conn, $insight_sql);
mysqli_stmt_bind_param($insight_stmt, "i", $hunter_id);
mysqli_stmt_execute($insight_stmt);
$insight_res = mysqli_stmt_get_result($insight_stmt);
$insight_row = mysqli_fetch_assoc($insight_res);
$hunter_insight = $insight_row ? (int)$insight_row['insight'] : 0;
?>

<style>
  :root{
    --bb-blood: #6b0f10;
    --bb-blood-bright: #9c1c1c;
    --bb-glow: #f4d35e;
    --bb-bone: #c9bfa8;
    --bb-bone-dim: #8c8473;
    --bb-ink: #050403;
    --bb-select: #5f86ab;

    --bs-body-bg: var(--bb-ink);
    --bs-body-color: var(--bb-bone);
    --bs-border-color: rgba(156,28,28,0.4);
  }

  body {
    font-family: 'EB Garamond', serif;
    background: var(--bb-ink);
    overflow-x: hidden;
  }

  /* ===== Navbar ===== */
  .bb-navbar {
    background: rgba(5,4,3,0.95);
    border-bottom: 1px solid rgba(156,28,28,0.4);
    font-family: 'Cinzel', serif;
  }

  .bb-navbar .navbar-brand {
    color: var(--bb-bone);
    letter-spacing: 0.1em;
    font-size: 1rem;
  }

  .bb-navbar .nav-link {
    color: var(--bb-bone-dim);
    letter-spacing: 0.08em;
    font-size: 0.85rem;
    transition: color .25s ease, text-shadow .25s ease;
  }

  .bb-navbar .nav-link:hover {
    color: var(--bb-glow);
    text-shadow: 0 0 8px var(--bb-glow);
  }

  .bb-navbar .nav-link.active {
    color: var(--bb-glow);
  }

  /* ===== Page header ===== */
  .shop-header {
    text-align: center;
    padding: 3rem 0 1.5rem;
  }

  .shop-header h1 {
    font-family: 'Cinzel', serif;
    font-size: clamp(1.8rem, 4vw, 3rem);
    color: var(--bb-bone);
    letter-spacing: 0.12em;
    text-shadow: 0 0 18px rgba(156,28,28,0.45);
  }

  .shop-header p {
    font-style: italic;
    color: var(--bb-bone-dim);
  }

  .ornament-line {
    height: 1px;
    background: linear-gradient(to right, transparent, var(--bb-blood-bright), transparent);
    max-width: 400px;
    margin: 1rem auto 2.5rem;
  }

  /* ===== Item list ===== */
  .item-list-wrap {
    max-width: 760px;
    margin: 0 auto;
  }

  .item-list {
    background: rgba(8,6,5,0.85);
    border: 1px solid rgba(156,28,28,0.4);
    box-shadow: inset 0 0 40px rgba(0,0,0,0.6);
  }

  .item-list::before,
  .item-list::after {
    content: "";
    display: block;
    height: 1px;
    background: linear-gradient(to right, transparent, var(--bb-glow), transparent);
    opacity: 0.5;
  }

  /* ===== Item row — outer wrapper div ===== */
  .item-row {
    position: relative;
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.65rem 1.25rem;
    border-bottom: 1px solid rgba(156,28,28,0.2);
    background: transparent;
    transition: background .2s ease, box-shadow .2s ease;
  }

  .item-row:last-of-type {
    border-bottom: none;
  }

  .item-row:hover,
  .item-row:focus-within {
    background: linear-gradient(to right, rgba(95,134,171,0.28), rgba(95,134,171,0.05) 70%);
    box-shadow: inset 0 0 22px rgba(95,134,171,0.35);
  }

  /* form is invisible to layout — display:contents makes it a ghost wrapper */
  .item-row form {
    display: contents;
  }

  /*  Icon box  */
  .item-icon-box {
    position: relative;
    flex: 0 0 auto;
    width: 46px;
    height: 46px;
    border: 1px solid rgba(201,191,168,0.35);
    background: #0c0a08 center/cover no-repeat;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: visible; /* let the stock badge peek outside the box */
  }

  .item-row:hover .item-icon-box,
  .item-row:focus-within .item-icon-box {
    border-color: var(--bb-glow);
  }

  .item-icon-box img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    filter: grayscale(15%) brightness(0.85);
    position: relative;
    z-index: 1; /* image sits below the badge */
  }

  .item-stock-badge {
    position: absolute;
    top: -6px;
    left: -6px;
    min-width: 16px;
    height: 16px;
    padding: 0 3px;
    background: var(--bb-ink);
    border: 1px solid var(--bb-glow);
    color: var(--bb-glow);
    font-family: 'Cinzel', serif;
    font-size: 0.65rem;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 2; /* badge always paints above the image */
  }

  .item-text {
    flex: 1 1 auto;
    min-width: 0;
  }

  .item-name {
    font-family: 'Cinzel', serif;
    font-size: 0.95rem;
    letter-spacing: 0.05em;
    color: var(--bb-glow);
    text-shadow: 0 0 6px rgba(244,211,94,0.25);
    margin: 0;
  }

  .item-description {
    font-size: 0.85rem;
    color: var(--bb-bone-dim);
    font-style: italic;
    margin: 0.1rem 0 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .item-row:hover .item-description,
  .item-row:focus-within .item-description {
    color: var(--bb-bone);
  }

  /* price */
  .item-price {
    flex: 0 0 auto;
    font-family: 'Cinzel', serif;
    font-size: 0.85rem;
    color: var(--bb-bone-dim);
    margin-right: 0.5rem;
  }

  .item-insight-tag {
    font-size: 0.75rem;
    color: var(--bb-select);
    font-style: italic;
    margin: 0.1rem 0 0;
  }

  .item-row-locked {
    opacity: 0.55;
  }

  .item-row-locked .item-name {
    color: var(--bb-bone-dim);
    text-shadow: none;
  }

  .item-locked-label {
    flex: 0 0 auto;
    font-family: 'Cinzel', serif;
    font-size: 0.65rem;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: var(--bb-select);
    padding: 0.25rem 0.7rem;
  }

  .item-acquire-btn {
    flex: 0 0 auto;
    background: transparent;
    border: 1px solid transparent;
    color: transparent;
    font-family: 'Cinzel', serif;
    font-size: 0.7rem;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    padding: 0.25rem 0.7rem;
    transition: color .2s ease, border-color .2s ease;
    text-decoration: none;
  }

  .item-row:hover .item-acquire-btn,
  .item-row:focus-within .item-acquire-btn {
    color: var(--bb-glow);
    border-color: rgba(244,211,94,0.6);
  }

  .item-acquire-btn::after {
    content: "\25B6";
    margin-left: 0.5em;
    font-size: 0.6rem;
  }

  /* pang edit ng admins */
  .item-edit-btn {
    flex: 0 0 auto;
    background: transparent;
    border: 1px solid transparent;
    color: transparent;
    font-family: 'Cinzel', serif;
    font-size: 0.7rem;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    padding: 0.25rem 0.7rem;
    transition: color .2s ease, border-color .2s ease;
    text-decoration: none;
  }

  .item-row:hover .item-edit-btn,
  .item-row:focus-within .item-edit-btn {
    color: var(--bb-glow);
    border-color: rgba(244,211,94,0.6);
  }

  .item-edit-btn::after {
    content: " ✎";
    font-size: 0.75rem;
  }

  .empty-state {
    text-align: center;
    padding: 4rem 0;
    font-style: italic;
    color: var(--bb-bone-dim);
  }
  .site-footer {
    border-top: 1px solid rgba(156,28,28,0.2);
    font-size: 0.75rem;
    letter-spacing: 0.1em;
    color: var(--bb-bone-dim);
    margin-top: 4rem;
  }

  @media (max-width: 576px) {
    .item-description { display: none; }
    .item-price { display: none; }
  }

  /* ===== Atmosphere: fixed background, dimmed for readability ===== */
  .page-bg {
    position: fixed;
    inset: 0;
    z-index: -2;
    overflow: hidden;
    background: #000;
  }

  .page-bg img.bg-art {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    filter: grayscale(15%) brightness(1) contrast(1.1);
  }

  .page-bg .vignette {
    position: absolute;
    inset: 0;
    background: radial-gradient(ellipse at 50% 10%, rgba(0,0,0,0.35) 0%, rgba(0,0,0,0.78) 55%, rgba(0,0,0,0.96) 100%);
  }

  .page-bg .grain {
    position: absolute;
    inset: 0;
    opacity: 0.05;
    pointer-events: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='2'/%3E%3C/filter%3E%3Crect width='100' height='100' filter='url(%23n)' opacity='0.4'/%3E%3C/svg%3E");
  }

  /* drifting embers for a little life */
  .embers {
    position: fixed;
    inset: 0;
    z-index: -1;
    overflow: hidden;
    pointer-events: none;
  }

  .ember {
    position: absolute;
    bottom: -10px;
    width: 3px;
    height: 3px;
    border-radius: 50%;
    background: var(--bb-glow);
    box-shadow: 0 0 6px 2px rgba(244,211,94,0.55);
    opacity: 0;
    animation: emberRise linear infinite;
  }

  @keyframes emberRise {
    0%   { transform: translateY(0) translateX(0); opacity: 0; }
    8%   { opacity: .8; }
    100% { transform: translateY(-105vh) translateX(24px); opacity: 0; }
  }

  /* floating Hunter's Ledger, always in view */
  .hunter-ledger {
    position: fixed;
    top: 5.25rem;
    right: 1.25rem;
    z-index: 1030;
    min-width: 190px;
    background: rgba(8,6,5,0.72) !important;
    border-color: rgba(156,28,28,0.4) !important;
    backdrop-filter: blur(3px);
    animation: ledgerGlow 5s ease-in-out infinite;
  }

  @keyframes ledgerGlow {
    0%, 100% { box-shadow: 0 0 18px rgba(0,0,0,0.6); }
    50%      { box-shadow: 0 0 22px rgba(156,28,28,0.35); }
  }

  .hunter-ledger .card-header {
    background: transparent;
    border-bottom-color: rgba(156,28,28,0.35);
    font-family: 'Cinzel', serif;
    font-size: 0.62rem;
    letter-spacing: 0.25em;
    text-transform: uppercase;
    color: var(--bb-bone-dim);
    text-align: center;
  }

  .hunter-ledger .stat-row {
    font-family: 'Cinzel', serif;
  }

  .hunter-ledger .stat-row .value {
    color: var(--bb-blood-bright);
    text-shadow: 0 0 6px rgba(156,28,28,0.5);
  }

  @media (max-width: 767px) {
    .hunter-ledger { top: auto; bottom: 1rem; right: 1rem; left: 1rem; min-width: 0; }
  }

  /* gentle rise-in for main content, subtle title flicker */
  @keyframes fadeInUp {
    from { opacity: 0; transform: translateY(12px); }
    to   { opacity: 1; transform: translateY(0); }
  }

  .container.pb-5 { animation: fadeInUp .7s ease both; }

  @keyframes titleFlicker {
    0%, 100% { text-shadow: 0 0 18px rgba(156,28,28,0.45); }
    50%      { text-shadow: 0 0 26px rgba(156,28,28,0.7), 0 0 14px rgba(244,211,94,0.25); }
  }

  .shop-header h1, .page-header h1 { 
    animation: titleFlicker 4.5s ease-in-out infinite; 
    }
</style>
</head>
<body>

<div class="page-bg">
  <img class="bg-art" src="ladymaria.jpg" alt="">
  <div class="vignette"></div>
  <div class="grain"></div>
</div>

<?php if(isset($_SESSION['hunter_id'])): ?>
<div class="card hunter-ledger">
  <div class="card-header">Hunter's Ledger</div>
  <div class="card-body py-2">
    <div class="d-flex justify-content-between stat-row mb-1">
      <span class="small text-uppercase">Blood Echoes</span>
      <span class="value stat-value" data-target="<?php echo (int)($_SESSION['blood_echoes'] ?? 0); ?>">0</span>
    </div>
    <div class="d-flex justify-content-between stat-row">
      <span class="small text-uppercase">Insight</span>
      <span class="value stat-value" data-target="<?php echo $hunter_insight; ?>">0</span>
    </div>
  </div>
</div>
<?php endif; ?>

<!--       N avbar-->
<nav class="navbar navbar-expand-lg bb-navbar sticky-top">
  <div class="container">
    <a class="navbar-brand" href="home.php">Gehrman's Workshop</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navMenu">
      <ul class="navbar-nav ms-auto gap-2">
        <li class="nav-item"><a class="nav-link active" href="shop.php">Workshop</a></li>
        <li class="nav-item"><a class="nav-link" href="cart.php">Cart</a></li>
        <li class="nav-item"><a class="nav-link" href="orders.php">Orders</a></li>
        <?php if(($_SESSION['role'] ?? '') === 'gehrman'): ?>
          <li class="nav-item"><a class="nav-link" href="admin_inventory.php">Inventory</a></li>
        <?php endif; ?>
        <li class="nav-item"><a class="nav-link" href="logout.php">Leave</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="shop-header">
  <h1>THE WORKSHOP</h1>
  <p>I am... Gehrman, friend to you hunters. You're sure to be in a fine haze about now, but don't think too hard about all of this. Just go out and kill a few beasts. It's for your own good.</p>
  <div class="ornament-line"></div>
</div>

<div class="container pb-5">
  <div class="item-list-wrap">
    <div class="item-list">

      <?php if(mysqli_num_rows($result) > 0): ?>
        <?php while($item = mysqli_fetch_assoc($result)): ?>
          <?php
            $is_locked = ($item['insight_required'] > 0 && $hunter_insight < $item['insight_required']);
          ?>

          <div class="item-row<?php echo $is_locked ? ' item-row-locked' : ''; ?>">

            <form action="cart.php" method="POST">
              <input type="hidden" name="item_id" value="<?php echo $item['item_id']; ?>">

              <div class="item-icon-box">
                <span class="item-stock-badge"><?php echo (int)$item['stock']; ?></span>
                <img
                  src="<?php echo !empty($item['img_path']) ? htmlspecialchars($item['img_path']) : 'assets/img/placeholder.jpg'; ?>"
                  alt="<?php echo htmlspecialchars($item['item_name']); ?>"
                >
              </div>

              <div class="item-text">
                <p class="item-name"><?php echo htmlspecialchars($item['item_name']); ?></p>
                <p class="item-description"><?php echo htmlspecialchars($item['description']); ?></p>
                <?php if($item['insight_required'] > 0): ?>
                  <p class="item-insight-tag"> Requires <?php echo (int)$item['insight_required']; ?> Insight</p>
                <?php endif; ?>
              </div>

              <span class="item-price"><?php echo number_format($item['price']); ?> Blood Echoes</span>

              <?php if(($_SESSION['role'] ?? '') !== 'gehrman'): ?>
                <?php if($is_locked): ?>
                  <span class="item-locked-label">Insight Required</span>
                <?php else: ?>
                  <button type="submit" name="add_to_cart" class="item-acquire-btn">Acquire</button>
                <?php endif; ?>
              <?php endif; ?>

            </form>
            <?php if(($_SESSION['role'] ?? '') === 'gehrman'): ?>
              <a href="admin_inventory.php" class="item-edit-btn">Edit</a>
            <?php endif; ?>

          </div>

        <?php endwhile; ?>
      <?php else: ?>

        <div class="empty-state">
          <p>The Workshop is bare. Check back soon, Hunter.</p>
        </div>

      <?php endif; ?>

    </div>
  </div>
</div>

<footer class="site-footer text-center py-3">
  &copy; <?php echo date('Y'); ?> Gehrman's Workshop — A Bloodborne-inspired E-commerce Project
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>

  // Count up the Hunter's Ledger stats on load instead of just snapping in
  document.querySelectorAll('.hunter-ledger .stat-value').forEach(function(el) {
    const target = parseInt(el.dataset.target, 10) || 0;
    let current = 0;
    const step = Math.max(1, Math.ceil(target / 30));
    const timer = setInterval(function() {
      current += step;
      if (current >= target) { current = target; clearInterval(timer); }
      el.textContent = current.toLocaleString();
    }, 20);
  });
</script>

</body>
</html>