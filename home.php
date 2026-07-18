<?php
session_start();
$isLoggedIn = isset($_SESSION['hunter_id']);
$username = $isLoggedIn ? $_SESSION['username'] : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Gehrman's Workshop | Hunter's Dream</title>

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
    overflow-x: hidden;
  }

  .menu-bg {
    position: relative;
    min-height: 100vh;
    background-color: #000;
  }

  .menu-bg img.bg-art {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    filter: grayscale(15%) brightness(0.5) contrast(1.1);
    z-index: 0;
  }

  .vignette {
    position: absolute;
    inset: 0;
    z-index: 1;
    background: radial-gradient(ellipse at center, rgba(0,0,0,0) 30%, rgba(0,0,0,0.9) 100%);
    pointer-events: none;
  }

  .grain {
    position: absolute;
    inset: 0;
    z-index: 2;
    opacity: 0.05;
    pointer-events: none;
  }


  .game-title {
    font-family: 'Cinzel', serif;
    font-weight: 700;
    letter-spacing: 0.12em;
    font-size: clamp(2.2rem, 5vw, 4rem);
    color: var(--bb-bone);
    text-shadow: 0 0 18px rgba(156,28,28,0.45), 0 2px 4px #000;
  }

  .game-subtitle {
    font-family: 'Cinzel', serif;
    font-size: clamp(0.85rem, 1.4vw, 1.1rem);
    letter-spacing: 0.3em;
    color: var(--bb-blood-bright);
    text-transform: uppercase;
  }

  .ornament-line {
    height: 1px;
    background: linear-gradient(to right, transparent, var(--bb-blood-bright), transparent);
  }

  .main-menu .list-group-item {
    background: transparent;
    border: none;
    border-radius: 0 !important;
    font-family: 'Cinzel', serif;
    font-size: clamp(1.1rem, 1.8vw, 1.5rem);
    letter-spacing: 0.08em;
    color: var(--bb-bone-dim);
    padding: 0.55em 1em;
    transition: color .25s ease, text-shadow .25s ease, letter-spacing .25s ease;
  }

  .main-menu .list-group-item:hover,
  .main-menu .list-group-item:focus {
    color: var(--bb-glow);
    letter-spacing: 0.14em;
    text-shadow: 0 0 8px var(--bb-glow), 0 0 18px rgba(244,211,94,.7);
  }

  .main-menu .list-group-item.disabled {
    color: #4a4540;
  }

  /*bootstrap*/
  .ledger-card {
    background: rgba(8,6,5,0.55) !important;
    border-color: rgba(156,28,28,0.4) !important;
    backdrop-filter: blur(2px);
  }

  .ledger-card .card-header {
    background: transparent;
    border-bottom-color: rgba(156,28,28,0.35);
    font-family: 'Cinzel', serif;
    font-size: 0.62rem;
    letter-spacing: 0.25em;
    text-transform: uppercase;
    color: var(--bb-bone-dim);
    text-align: center;
  }

  .ledger-card .stat-row {
    font-family: 'Cinzel', serif;
  }

  .ledger-card .stat-row .value {
    color: var(--bb-blood-bright);
    text-shadow: 0 0 6px rgba(156,28,28,0.5);
  }

  /* ===== Footer ===== */
  .site-footer {
    background: linear-gradient(to top, #000, transparent);
    font-size: 0.75rem;
    letter-spacing: 0.1em;
    color: var(--bb-bone-dim);
  }
</style>
</head>
<body>

<div class="menu-bg">


  <img class="bg-art" src="images/bgwallapaper.jpg " alt="">
  <div class="vignette"></div>
  <div class="grain"></div>

  <?php if ($isLoggedIn): ?>

  <div class="position-absolute top-0 end-0 m-4 z-3" style="z-index:3;">
    <div class="card ledger-card" style="min-width: 220px;">
      <div class="card-header">Hunter <?php echo htmlspecialchars($username); ?></div>
      <div class="card-body py-2">
        <div class="d-flex justify-content-between stat-row mb-1">
          <span class="small text-uppercase">Blood Echoes</span>
          <span class="value"><?php echo htmlspecialchars($_SESSION['blood_echoes'] ?? 0); ?></span>
        </div>
        <div class="d-flex justify-content-between stat-row">
          <span class="small text-uppercase">Insight</span>
          <span class="value"><?php echo htmlspecialchars($_SESSION['insight'] ?? 0); ?></span>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>
  <div class="container position-relative" style="z-index:3; min-height:100vh;">
    <div class="row min-vh-100 justify-content-center align-items-center text-center">
      <div class="col-12 col-md-8 col-lg-6">

        <h1 class="game-title mb-1">GEHRMAN</h1>
        <div class="game-subtitle mb-3">The First Hunter's Workshop</div>

        <div class="ornament-line mx-auto mb-4" style="max-width: 280px;"></div>

        <div class="list-group main-menu mx-auto" style="max-width: 360px;">
          <?php if ($isLoggedIn): ?>
            <a href="shop.php" class="list-group-item">Enter the Workshop</a>
            <a href="cart.php" class="list-group-item">Hunter's Cart</a>
            <a href="orders.php" class="list-group-item">Order History</a>
            
            <?php if (($_SESSION['role'] ?? '') === 'gehrman'): ?>
              <a href="admin_inventory.php" class="list-group-item">Manage Inventory</a>
            <?php endif; ?>
          <?php else: ?>
            <a href="login.php" class="list-group-item">Awaken (Login)</a>
            <a href="register.php" class="list-group-item">Become a Hunter (Register)</a>
            <a href="shop.php" class="list-group-item">Browse the Workshop</a>

                      <?php endif; ?>
            <a href ="logout.php" class="list-group-item">Leave the Dream</a>
        </div>

        <p class="fst-italic mt-4 mb-0" style="color: var(--bb-bone-dim);">
          <?php echo $isLoggedIn
            ? 'Welcome back, Hunter ' . htmlspecialchars($username) . '.'
            : 'Fear the old blood.'; ?>
        </p>

      </div>
    </div>
  </div>

</div>

<footer class="site-footer text-center py-3 position-relative" style="z-index:3;">
  &copy; <?php echo date('Y'); ?> Gehrman's Workshop — A Bloodborne-inspired E-commerce Project
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>