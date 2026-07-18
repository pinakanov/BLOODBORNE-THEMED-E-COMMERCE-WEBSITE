<?php
session_start();
require_once 'db_connect.php';

// Redirect to login if not logged in
if(!isset($_SESSION['hunter_id'])) {
    header('Location: login.php');
    exit;
}

$hunter_id = $_SESSION['hunter_id'];

// Fetch this hunter's orders joined with item details
$sql = "SELECT orders.order_id, orders.quantity, orders.total_price, orders.status, orders.created,
               items.item_name, items.img_path
        FROM orders
        JOIN items ON orders.item_id = items.item_id
        WHERE orders.hunter_id = ?
        ORDER BY orders.created DESC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $hunter_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if(!$result) {
    die("Query failed: " . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Order History | Gehrman's Workshop</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700&family=EB+Garamond:ital,wght@0,400;0,500;1,400&display=swap" rel="stylesheet">

<style>
  :root{
    --bb-blood: #6b0f10;
    --bb-blood-bright: #9c1c1c;
    --bb-glow: #f4d35e;
    --bb-bone: #c9bfa8;
    --bb-bone-dim: #8c8473;
    --bb-ink: #050403;

    --bs-body-bg: var(--bb-ink);
    --bs-body-color: var(--bb-bone);
    --bs-border-color: rgba(156,28,28,0.4);
  }

  body {
    font-family: 'EB Garamond', serif;
    background: var(--bb-ink);
  }

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

  .page-header {
    text-align: center;
    padding: 3rem 0 1.5rem;
  }

  .page-header h1 {
    font-family: 'Cinzel', serif;
    font-size: clamp(1.8rem, 4vw, 2.8rem);
    color: var(--bb-bone);
    letter-spacing: 0.12em;
    text-shadow: 0 0 18px rgba(156,28,28,0.45);
  }

  .page-header p {
    font-style: italic;
    color: var(--bb-bone-dim);
  }

  .ornament-line {
    height: 1px;
    background: linear-gradient(to right, transparent, var(--bb-blood-bright), transparent);
    max-width: 400px;
    margin: 1rem auto 2.5rem;
  }

  .bb-card {
    background: rgba(8,6,5,0.7) !important;
    border-color: rgba(156,28,28,0.35) !important;
  }

  .bb-table {
    color: var(--bb-bone);
    font-size: 0.95rem;
    margin-bottom: 0;
  }

  .bb-table thead th {
    font-family: 'Cinzel', serif;
    font-size: 0.7rem;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    color: var(--bb-bone-dim);
    border-color: rgba(156,28,28,0.35);
    background: rgba(107,15,16,0.15);
  }

  .bb-table td {
    border-color: rgba(156,28,28,0.2);
    vertical-align: middle;
     color: var(--bb-bone);
  }

  .bb-table tbody tr:hover {
    background: rgba(156,28,28,0.08);
  }

  .bb-table .item-img {
    width: 48px;
    height: 48px;
    object-fit: cover;
    border: 1px solid rgba(156,28,28,0.3);
  }

  ::selection {
  background: var(--bb-blood-bright);
  color: var(--bb-bone);
}

.bb-table ::selection {
  background: var(--bb-blood-bright);
  color: var(--bb-bone);
}

  .order-total-row td {
    font-family: 'Cinzel', serif;
    letter-spacing: 0.08em;
    color: var(--bb-glow);
    text-shadow: 0 0 6px rgba(244,211,94,0.25);
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

  .shop-header h1, .page-header h1 { animation: titleFlicker 4.5s ease-in-out infinite; }
</style>
</head>
<body>

<div class="page-bg">
  <img class="bg-art" src="images/maxresdefault.jpg" alt="">
  <div class="vignette"></div>
  <div class="grain"></div>
</div>
<div class="embers"></div>

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
      <span class="value stat-value" data-target="<?php echo (int)($_SESSION['insight'] ?? 0); ?>">0</span>
    </div>
  </div>
</div>
<?php endif; ?>

<nav class="navbar navbar-expand-lg bb-navbar sticky-top">
  <div class="container">
    <a class="navbar-brand" href="home.php">Gehrman's Workshop</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navMenu">
      <ul class="navbar-nav ms-auto gap-2">
        <li class="nav-item"><a class="nav-link" href="shop.php">Workshop</a></li>
        <li class="nav-item"><a class="nav-link" href="cart.php">Cart</a></li>
        <li class="nav-item"><a class="nav-link active" href="orders.php">Orders</a></li>
        <?php if(($_SESSION['role'] ?? '') === 'gehrman'): ?>
          <li class="nav-item"><a class="nav-link" href="admin_inventory.php">Inventory</a></li>
        <?php endif; ?>
        <li class="nav-item"><a class="nav-link" href="logout.php">Leave</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="page-header">
  <h1>ORDER HISTORY</h1>
  <p>I am... Gehrman, friend to you hunters. You're sure to be in a fine haze about now, but don't think too hard about all of this. Just go out and kill a few beasts. It's for your own good.</p>
  <div class="ornament-line"></div>
</div>

<div class="container pb-5">
  <div class="card bb-card">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table bb-table">
          <thead>
            <tr>
              <th>Order #</th>
              <th>Item</th>
              <th>Qty</th>
              <th>Total</th>
              <th>Status</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>
            <?php if(mysqli_num_rows($result) > 0): ?>
              <?php
                $grand_total = 0;
                while($order = mysqli_fetch_assoc($result)):
                  $grand_total += $order['total_price'];
                  $status_label = $order['status'] == 1 ? 'Completed' : 'Pending';
              ?>
                <tr>
                  <td>#<?php echo $order['order_id']; ?></td>
                  <td class="d-flex align-items-center gap-2">
                    <img
                      src="<?php echo !empty($order['img_path']) ? htmlspecialchars($order['img_path']) : 'assets/img/placeholder.jpg'; ?>"
                      class="item-img"
                      alt="<?php echo htmlspecialchars($order['item_name']); ?>"
                    >
                    <?php echo htmlspecialchars($order['item_name']); ?>
                  </td>
                  <td><?php echo (int)$order['quantity']; ?></td>
                  <td><?php echo number_format($order['total_price']); ?> Blood Echoes</td>
                  <td><?php echo $status_label; ?></td>
                  <td><?php echo date('M j, Y g:ia', strtotime($order['created'])); ?></td>
                </tr>
              <?php endwhile; ?>
              <tr class="order-total-row">
                <td colspan="3" class="text-end">Lifetime Total</td>
                <td colspan="3"><?php echo number_format($grand_total); ?> Blood Echoes</td>
              </tr>
            <?php else: ?>
              <tr>
                <td colspan="6" class="empty-state">
                  No hunts completed yet. Visit the <a href="shop.php" style="color: var(--bb-glow);">Workshop</a> to begin.
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<footer class="site-footer text-center py-3">
  &copy; <?php echo date('Y'); ?> Gehrman's Workshop — A Bloodborne-inspired E-commerce Project
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>

  // Count up the Hunter's Ledger stats on load
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