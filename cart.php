<?php
session_start();
require_once 'db_connect.php';

// Redirect to login if not logged in
if(!isset($_SESSION['hunter_id'])) {
    header('Location: login.php');
    exit;
}

$hunter_id = $_SESSION['hunter_id'];

if(!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = []; // item_id => quantity
}

$error   = '';
$success = '';

// ===== ADD TO CART (posted from shop.php) =====
if(isset($_POST['add_to_cart'])) {
    $item_id = (int)$_POST['item_id'];

    // Re-check the Insight gate server-side, don't just trust the shop page UI
    $gate_sql  = "SELECT insight_required FROM items WHERE item_id = ?";
    $gate_stmt = mysqli_prepare($conn, $gate_sql);
    mysqli_stmt_bind_param($gate_stmt, "i", $item_id);
    mysqli_stmt_execute($gate_stmt);
    $gate_res = mysqli_stmt_get_result($gate_stmt);
    $gate_row = mysqli_fetch_assoc($gate_res);

    $insight_sql  = "SELECT insight FROM hunters WHERE hunter_id = ?";
    $insight_stmt = mysqli_prepare($conn, $insight_sql);
    mysqli_stmt_bind_param($insight_stmt, "i", $hunter_id);
    mysqli_stmt_execute($insight_stmt);
    $insight_res = mysqli_stmt_get_result($insight_stmt);
    $insight_row = mysqli_fetch_assoc($insight_res);
    $hunter_insight = $insight_row ? (int)$insight_row['insight'] : 0;

    if($gate_row && $gate_row['insight_required'] > $hunter_insight) {
        $_SESSION['cart_error'] = "You lack the Insight to perceive that item.";
    } else {
        if(isset($_SESSION['cart'][$item_id])) {
            $_SESSION['cart'][$item_id]++;
        } else {
            $_SESSION['cart'][$item_id] = 1;
        }
    }

    header('Location: cart.php');
    exit;
}

// ===== UPDATE QUANTITY =====
if(isset($_POST['update_qty'])) {
    $item_id = (int)$_POST['item_id'];
    $qty     = (int)$_POST['qty'];

    if($qty <= 0) {
        unset($_SESSION['cart'][$item_id]);
    } else {
        $_SESSION['cart'][$item_id] = $qty;
    }

    header('Location: cart.php');
    exit;
}

// ===== REMOVE ITEM =====
if(isset($_POST['remove_item'])) {
    $item_id = (int)$_POST['item_id'];
    unset($_SESSION['cart'][$item_id]);

    header('Location: cart.php');
    exit;
}

// ===== CHECKOUT =====
if(isset($_POST['checkout'])) {

    if(empty($_SESSION['cart'])) {
        $error = "Your cart is empty, Hunter.";
    } else {

        // Fresh balance check straight from the DB (don't trust stale session values)
        $balance_sql  = "SELECT blood_echoes, insight FROM hunters WHERE hunter_id = ?";
        $balance_stmt = mysqli_prepare($conn, $balance_sql);
        mysqli_stmt_bind_param($balance_stmt, "i", $hunter_id);
        mysqli_stmt_execute($balance_stmt);
        $balance_res = mysqli_stmt_get_result($balance_stmt);
        $balance_row = mysqli_fetch_assoc($balance_res);
        $hunter_echoes  = $balance_row ? (int)$balance_row['blood_echoes'] : 0;
        $hunter_insight = $balance_row ? (int)$balance_row['insight'] : 0;

        $stock_ok    = true;
        $echoes_needed = 0;

        // Verify stock, Insight gate, and total cost for every item first
        foreach($_SESSION['cart'] as $item_id => $qty) {
            $item_id = (int)$item_id;
            $check_sql    = "SELECT stock, price, insight_required FROM items WHERE item_id = ?";
            $check_stmt   = mysqli_prepare($conn, $check_sql);
            mysqli_stmt_bind_param($check_stmt, "i", $item_id);
            mysqli_stmt_execute($check_stmt);
            $check_result = mysqli_stmt_get_result($check_stmt);
            $check_item   = mysqli_fetch_assoc($check_result);

            if(!$check_item || $check_item['stock'] < $qty) {
                $stock_ok = false;
                $error    = "Not enough stock for one of the items in your cart.";
                break;
            }

            if($check_item['insight_required'] > $hunter_insight) {
                $stock_ok = false;
                $error    = "You no longer meet the Insight requirement for one of the items in your cart.";
                break;
            }

            $echoes_needed += $check_item['price'] * $qty;
        }

        if($stock_ok && $hunter_echoes < $echoes_needed) {
            $stock_ok = false;
            $error    = "You don't have enough Blood Echoes for this hunt.";
        }

        if($stock_ok) {
            foreach($_SESSION['cart'] as $item_id => $qty) {
                $item_id = (int)$item_id;

                // Look up current price to compute total_price for this line
                $price_sql    = "SELECT price FROM items WHERE item_id = ?";
                $price_stmt   = mysqli_prepare($conn, $price_sql);
                mysqli_stmt_bind_param($price_stmt, "i", $item_id);
                mysqli_stmt_execute($price_stmt);
                $price_result = mysqli_stmt_get_result($price_stmt);
                $price_row    = mysqli_fetch_assoc($price_result);
                $total_price  = round($price_row['price'] * $qty);

                // status: 1 = completed
                $order_sql  = "INSERT INTO orders (hunter_id, item_id, quantity, total_price, status)
                               VALUES (?, ?, ?, ?, 1)";
                $order_stmt = mysqli_prepare($conn, $order_sql);
                mysqli_stmt_bind_param($order_stmt, "iiii", $hunter_id, $item_id, $qty, $total_price);

                if(!mysqli_stmt_execute($order_stmt)) {
                    $error = "Order failed: " . mysqli_error($conn);
                    break;
                }

                $update_stock_sql  = "UPDATE items SET stock = stock - ? WHERE item_id = ?";
                $update_stock_stmt = mysqli_prepare($conn, $update_stock_sql);
                mysqli_stmt_bind_param($update_stock_stmt, "ii", $qty, $item_id);
                mysqli_stmt_execute($update_stock_stmt);
            }

            if(empty($error)) {
                // Deduct Blood Echoes spent and sync the session
                $new_balance = $hunter_echoes - $echoes_needed;
                $deduct_sql  = "UPDATE hunters SET blood_echoes = ? WHERE hunter_id = ?";
                $deduct_stmt = mysqli_prepare($conn, $deduct_sql);
                mysqli_stmt_bind_param($deduct_stmt, "ii", $new_balance, $hunter_id);
                mysqli_stmt_execute($deduct_stmt);
                $_SESSION['blood_echoes'] = $new_balance;

                $_SESSION['cart'] = [];
                $success = "Your echoes have been exchanged. The Workshop thanks you, Hunter.";
            }
        }
    }
}

// Pick up any flash error from the Insight gate check on add_to_cart
if(isset($_SESSION['cart_error'])) {
    $error = $_SESSION['cart_error'];
    unset($_SESSION['cart_error']);
}

// ===== FETCH CART ITEM DETAILS =====
$cart_items = [];
$grand_total = 0;

if(!empty($_SESSION['cart'])) {
    foreach($_SESSION['cart'] as $item_id => $qty) {
        $item_id = (int)$item_id;
        $sql    = "SELECT * FROM items WHERE item_id = ?";
        $stmt   = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $item_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $item   = mysqli_fetch_assoc($result);

        if($item) {
            $item['qty']      = $qty;
            $item['subtotal'] = $item['price'] * $qty;
            $grand_total     += $item['subtotal'];
            $cart_items[]     = $item;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Hunter's Cart | Gehrman's Workshop</title>

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
    overflow-x: hidden;
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

  .item-list-wrap {
    max-width: 780px;
    margin: 0 auto;
  }

  .item-list {
    background: rgba(8,6,5,0.85);
    border: 1px solid rgba(156,28,28,0.4);
    box-shadow: inset 0 0 40px rgba(0,0,0,0.6);
  }

  .item-row {
    position: relative;
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.65rem 1.25rem;
    border-bottom: 1px solid rgba(156,28,28,0.2);
    transition: background .2s ease, box-shadow .2s ease;
  }

  .item-row:last-of-type {
    border-bottom: none;
  }

  .item-row:hover {
    background: linear-gradient(to right, rgba(95,134,171,0.28), rgba(95,134,171,0.05) 70%);
    box-shadow: inset 0 0 22px rgba(95,134,171,0.35);
  }

  .item-row form {
    display: contents;
  }

  .item-icon-box {
    flex: 0 0 auto;
    width: 46px;
    height: 46px;
    border: 1px solid rgba(201,191,168,0.35);
    background: #0c0a08 center/cover no-repeat;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .item-icon-box img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    filter: grayscale(15%) brightness(0.85);
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

  .item-subtext {
    font-size: 0.8rem;
    color: var(--bb-bone-dim);
    font-style: italic;
    margin: 0.1rem 0 0;
  }

  .qty-input {
    width: 60px;
    background: rgba(0,0,0,0.4) !important;
    border-color: rgba(156,28,28,0.35) !important;
    color: var(--bb-bone) !important;
    font-family: 'Cinzel', serif;
    text-align: center;
  }

  .qty-input:focus {
    border-color: var(--bb-glow) !important;
    box-shadow: 0 0 0 0.15rem rgba(244,211,94,0.25) !important;
  }

  .item-subtotal {
    flex: 0 0 auto;
    width: 120px;
    text-align: right;
    font-family: 'Cinzel', serif;
    font-size: 0.85rem;
    color: var(--bb-bone-dim);
  }

  .item-row:hover .item-subtotal {
    color: var(--bb-glow);
  }

  .btn-bb-remove {
    --bs-btn-color: var(--bb-blood-bright);
    --bs-btn-border-color: rgba(156,28,28,0.5);
    --bs-btn-hover-color: #ff4444;
    --bs-btn-hover-border-color: #ff4444;
    --bs-btn-hover-bg: transparent;
    font-family: 'Cinzel', serif;
    font-size: 0.7rem;
    letter-spacing: 0.05em;
    text-transform: uppercase;
  }

  .btn-bb-update {
    --bs-btn-color: var(--bb-bone-dim);
    --bs-btn-border-color: rgba(156,28,28,0.4);
    --bs-btn-hover-color: var(--bb-glow);
    --bs-btn-hover-border-color: var(--bb-glow);
    --bs-btn-hover-bg: transparent;
    font-family: 'Cinzel', serif;
    font-size: 0.7rem;
    letter-spacing: 0.05em;
    text-transform: uppercase;
  }

  .cart-summary {
    max-width: 780px;
    margin: 1.5rem auto 0;
    background: rgba(8,6,5,0.7);
    border: 1px solid rgba(156,28,28,0.35);
    padding: 1.25rem 1.5rem;
  }

  .cart-summary .total-label {
    font-family: 'Cinzel', serif;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: var(--bb-bone-dim);
    font-size: 0.85rem;
  }

  .cart-summary .total-value {
    font-family: 'Cinzel', serif;
    font-size: 1.4rem;
    color: var(--bb-blood-bright);
    text-shadow: 0 0 8px rgba(156,28,28,0.5);
  }

  .btn-checkout {
    --bs-btn-color: var(--bb-bone-dim);
    --bs-btn-border-color: rgba(156,28,28,0.5);
    --bs-btn-hover-color: var(--bb-glow);
    --bs-btn-hover-border-color: var(--bb-glow);
    --bs-btn-hover-bg: transparent;
    font-family: 'Cinzel', serif;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    transition: color .25s ease, text-shadow .25s ease;
  }

  .btn-checkout:hover {
    text-shadow: 0 0 8px var(--bb-glow), 0 0 18px rgba(244,211,94,.7);
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
  <img class="bg-art" src="bloodborne.png" alt="">
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
        <li class="nav-item"><a class="nav-link active" href="cart.php">Cart</a></li>
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
  <h1>HUNTER'S CART</h1>
    <p>I am... Gehrman, friend to you hunters. You're sure to be in a fine haze about now, but don't think too hard about all of this. Just go out and kill a few beasts. It's for your own good.</p>

  <div class="ornament-line"></div>
</div>

<div class="container pb-5">

  <?php if($error): ?>
    <div class="alert text-center small mx-auto mb-4" style="max-width:780px; background: rgba(107,15,16,0.2); border-color: rgba(156,28,28,0.5); color: var(--bb-blood-bright);">
      <?php echo $error; ?>
    </div>
  <?php endif; ?>

  <?php if($success): ?>
    <div class="alert text-center small mx-auto mb-4" style="max-width:780px; background: rgba(15,50,16,0.3); border-color: rgba(28,100,30,0.5); color: #7ec87f;">
      <?php echo $success; ?>
      <br>
      <a href="orders.php" class="alt-link mt-1 d-inline-block" style="color:#7ec87f;">View your order history</a>
    </div>
  <?php endif; ?>

  <div class="item-list-wrap">
    <div class="item-list">

      <?php if(count($cart_items) > 0): ?>
        <?php foreach($cart_items as $item): ?>

          <div class="item-row">

            <div class="item-icon-box">
              <img
                src="<?php echo !empty($item['img_path']) ? htmlspecialchars($item['img_path']) : 'assets/img/placeholder.jpg'; ?>"
                alt="<?php echo htmlspecialchars($item['item_name']); ?>"
              >
            </div>

            <div class="item-text">
              <p class="item-name"><?php echo htmlspecialchars($item['item_name']); ?></p>
              <p class="item-subtext"><?php echo number_format($item['price']); ?> Blood Echoes each &middot; <?php echo (int)$item['stock']; ?> in stock<?php echo $item['insight_required'] > 0 ? ' &middot; requires ' . (int)$item['insight_required'] . ' Insight' : ''; ?></p>
            </div>

            <form action="cart.php" method="POST" class="d-flex align-items-center gap-2">
              <input type="hidden" name="item_id" value="<?php echo $item['item_id']; ?>">
              <input
                type="number"
                name="qty"
                class="form-control form-control-sm qty-input"
                value="<?php echo (int)$item['qty']; ?>"
                min="1"
                max="<?php echo (int)$item['stock']; ?>"
              >
              <button type="submit" name="update_qty" class="btn btn-outline-secondary btn-sm btn-bb-update">Update</button>
            </form>

            <span class="item-subtotal"><?php echo number_format($item['subtotal']); ?> Echoes</span>

            <form action="cart.php" method="POST">
              <input type="hidden" name="item_id" value="<?php echo $item['item_id']; ?>">
              <button type="submit" name="remove_item" class="btn btn-outline-secondary btn-sm btn-bb-remove">Remove</button>
            </form>

          </div>

        <?php endforeach; ?>
      <?php else: ?>

        <div class="empty-state">
          <p>Your cart is empty. The Workshop awaits, Hunter.</p>
        </div>

      <?php endif; ?>

    </div>
  </div>

  <?php if(count($cart_items) > 0): ?>
    <div class="cart-summary d-flex justify-content-between align-items-center flex-wrap gap-3">
      <div>
        <div class="total-label">Total Due</div>
        <div class="total-value"><?php echo number_format($grand_total); ?> Blood Echoes</div>
      </div>
      <form action="cart.php" method="POST">
        <button type="submit" name="checkout" class="btn btn-outline-secondary btn-checkout px-4">Complete the Hunt</button>
      </form>
    </div>
  <?php endif; ?>

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