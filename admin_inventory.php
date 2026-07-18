<?php
session_start();
require_once 'db_connect.php';

// Only Gehrman can access this page
if(!isset($_SESSION['hunter_id']) || $_SESSION['role'] !== 'gehrman') {
    header('Location: home.php');
    exit;
}

$error   = '';
$success = '';


// ===== ADD ITEM =====
if(isset($_POST['add_item'])) {
    $item_name       = $_POST['item_name'];
    $description     = $_POST['description'];
    $price           = $_POST['price'];
    $insight_required = (int)($_POST['insight_required'] ?? 0);
    $stock           = $_POST['stock'];
    $img_path        = '';

    if(empty($item_name) || empty($price) || empty($stock)) {
        $error = "Item name, price and stock are required.";
    } else {

        // Handle image upload
        if(!empty($_FILES['item_image']['name'])) {
            $upload_dir  = 'images/items/';
            $filename    = time() . '_' . basename($_FILES['item_image']['name']);
            $target      = $upload_dir . $filename;
            $file_type   = strtolower(pathinfo($target, PATHINFO_EXTENSION));
            $allowed     = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if(!in_array($file_type, $allowed)) {
                $error = "Only JPG, JPEG, PNG, GIF and WEBP files are allowed.";
            } elseif(move_uploaded_file($_FILES['item_image']['tmp_name'], $target)) {
                $img_path = $target;
            } else {
                $error = "Image upload failed. Make sure the images/items/ folder exists.";
            }
        }

        if(empty($error)) {
            $sql    = "INSERT INTO items (item_name, description, price, insight_required, stock, img_path, created_at)
                       VALUES ('$item_name', '$description', '$price', '$insight_required', '$stock', '$img_path', NOW())";
            $result = mysqli_query($conn, $sql);

            if($result) {
                $success = "Item added to the Workshop successfully.";
            } else {
                $error = "Something went wrong while adding the item.";
            }
        }
    }
}

// ===== DELETE ITEM =====
if(isset($_POST['delete_item'])) {
    $item_id = $_POST['item_id'];
    $sql     = "DELETE FROM items WHERE item_id = '$item_id'";
    $result  = mysqli_query($conn, $sql);

    if($result) {
        $success = "Item removed from the Workshop.";
    } else {
        $error = "Something went wrong while deleting the item.";
    }
}

// ===== EDIT ITEM =====
if(isset($_POST['edit_item'])) {
    $item_id         = $_POST['item_id'];
    $item_name       = $_POST['item_name'];
    $description     = $_POST['description'];
    $price           = $_POST['price'];
    $insight_required = (int)($_POST['insight_required'] ?? 0);
    $stock           = $_POST['stock'];
    $img_path        = $_POST['current_img']; // keep existing image by default

    // Handle new image upload if a new one was chosen
    if(!empty($_FILES['edit_image']['name'])) {
        $upload_dir = 'images/items/';
        $filename   = time() . '_' . basename($_FILES['edit_image']['name']);
        $target     = $upload_dir . $filename;
        $file_type  = strtolower(pathinfo($target, PATHINFO_EXTENSION));
        $allowed    = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if(!in_array($file_type, $allowed)) {
            $error = "Only JPG, JPEG, PNG, GIF and WEBP files are allowed.";
        } elseif(move_uploaded_file($_FILES['edit_image']['tmp_name'], $target)) {
            $img_path = $target;
        } else {
            $error = "Image upload failed. Make sure the images/items/ folder exists.";
        }
    }

    if(empty($error)) {
        $sql    = "UPDATE items SET item_name='$item_name', description='$description', price='$price', insight_required='$insight_required', stock='$stock', img_path='$img_path' WHERE item_id='$item_id'";
        $result = mysqli_query($conn, $sql);

        if($result) {
            $success = "Item updated successfully.";
        } else {
            $error = "Something went wrong while updating the item.";
        }
    }
}

// Fetch all items for the table
$items_sql    = "SELECT * FROM items ORDER BY created_at DESC";
$items_result = mysqli_query($conn, $items_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Inventory | Gehrman's Workshop</title>

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

  /* ===== Cards ===== */
  .bb-card {
    background: rgba(8,6,5,0.7) !important;
    border-color: rgba(156,28,28,0.35) !important;
  }

  .bb-card .card-header {
    background: rgba(107,15,16,0.2);
    border-bottom-color: rgba(156,28,28,0.35);
    font-family: 'Cinzel', serif;
    font-size: 0.85rem;
    letter-spacing: 0.1em;
    color: var(--bb-bone);
    text-transform: uppercase;
  }

  /* ===== Form controls ===== */
  .form-label {
    font-family: 'Cinzel', serif;
    font-size: 0.7rem;
    letter-spacing: 0.15em;
    color: var(--bb-bone-dim);
    text-transform: uppercase;
  }

  .form-control {
    background: rgba(0,0,0,0.4) !important;
    border-color: rgba(156,28,28,0.35) !important;
    color: var(--bb-bone) !important;
    font-family: 'EB Garamond', serif;
  }

  .form-control::placeholder {
    color: #5c564c !important;
  }

  .form-control:focus {
    border-color: var(--bb-glow) !important;
    box-shadow: 0 0 0 0.15rem rgba(244,211,94,0.25) !important;
  }

  /* file input styling */
  .form-control[type="file"] {
    color: var(--bb-bone-dim) !important;
    padding: 0.5rem;
  }

  /* image preview */
  .img-preview {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border: 1px solid rgba(156,28,28,0.4);
    display: none;
    margin-top: 0.5rem;
  }

  /* ===== Buttons ===== */
  .btn-bb-add {
    --bs-btn-color: var(--bb-bone-dim);
    --bs-btn-border-color: rgba(156,28,28,0.5);
    --bs-btn-hover-color: var(--bb-glow);
    --bs-btn-hover-border-color: var(--bb-glow);
    --bs-btn-hover-bg: transparent;
    font-family: 'Cinzel', serif;
    font-size: 0.8rem;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    transition: color .25s, text-shadow .25s, border-color .25s;
  }

  .btn-bb-add:hover {
    text-shadow: 0 0 8px var(--bb-glow);
  }

  .btn-bb-delete {
    --bs-btn-color: var(--bb-blood-bright);
    --bs-btn-border-color: rgba(156,28,28,0.5);
    --bs-btn-hover-color: #ff4444;
    --bs-btn-hover-border-color: #ff4444;
    --bs-btn-hover-bg: transparent;
    font-family: 'Cinzel', serif;
    font-size: 0.75rem;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    transition: color .25s, border-color .25s;
  }

  .btn-bb-edit {
    --bs-btn-color: var(--bb-bone-dim);
    --bs-btn-border-color: rgba(156,28,28,0.4);
    --bs-btn-hover-color: var(--bb-glow);
    --bs-btn-hover-border-color: var(--bb-glow);
    --bs-btn-hover-bg: transparent;
    font-family: 'Cinzel', serif;
    font-size: 0.75rem;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    transition: color .25s, border-color .25s;
  }

  /* ===== Table ===== */
  .bb-table {
    --bs-table-color:var(--bb-bone);
    color: var(--bb-bone);
    font-size: 0.89rem;
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
  }

  .bb-table tbody tr:hover {
    background: rgba(156,28,28,0.08);
  }

  .bb-table .item-img {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border: 1px solid rgba(156,28,28,0.3);
  }

  /* ===== Edit modal ===== */
  .modal-content {
    background: #0d0b0a;
    border-color: rgba(156,28,28,0.4);
    color: var(--bb-bone);
  }

  .modal-header {
    border-bottom-color: rgba(156,28,28,0.35);
    font-family: 'Cinzel', serif;
    letter-spacing: 0.1em;
  }

  .modal-footer {
    border-top-color: rgba(156,28,28,0.35);
  }

  .btn-close {
    filter: invert(1);
  }

  /* ===== Footer ===== */
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

  .container.pb-5 { 
    animation: fadeInUp .7s ease both; 
  }

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
  <img class="bg-art" src="images/workshop2.jpg" alt="">
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
      <span class="value stat-value" data-target="<?php echo (int)($_SESSION['insight'] ?? 0); ?>">0</span>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- ===== Navbar ===== -->
<nav class="navbar navbar-expand-lg bb-navbar sticky-top">
  <div class="container">
    <a class="navbar-brand" href="home.php">Gehrman's Workshop</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navMenu">
      <ul class="navbar-nav ms-auto gap-2">
        <li class="nav-item"><a class="nav-link" href="shop.php">Workshop</a></li>
        <li class="nav-item"><a class="nav-link active" href="admin_inventory.php">Inventory</a></li>
        <li class="nav-item"><a class="nav-link" href="logout.php">Leave</a></li>
      </ul>
    </div>
  </div>
</nav>

<!-- ===== Page header ===== -->
<div class="page-header">
  <h1>INVENTORY</h1>
  <p>Manage the relics of the Workshop</p>
  <div class="ornament-line"></div>
</div>

<div class="container pb-5">

  <?php if($error): ?>
    <div class="alert text-center small mb-4" style="background: rgba(107,15,16,0.2); border-color: rgba(156,28,28,0.5); color: var(--bb-blood-bright);">
      <?php echo $error; ?>
    </div>
  <?php endif; ?>

  <?php if($success): ?>
    <div class="alert text-center small mb-4" style="background: rgba(15,50,16,0.3); border-color: rgba(28,100,30,0.5); color: #7ec87f;">
      <?php echo $success; ?>
    </div>
  <?php endif; ?>

  <!-- ===== Add Item Form ===== -->
  <div class="card bb-card mb-5">
    <div class="card-header">Add New Item</div>
    <div class="card-body p-4">
      <!-- enctype required for file uploads -->
      <form action="admin_inventory.php" method="POST" enctype="multipart/form-data">
        <div class="row g-3">

          <div class="col-12 col-md-6">
            <label class="form-label">Item Name</label>
            <input type="text" class="form-control" name="item_name" placeholder="e.g. Saw Cleaver" required>
          </div>

          <div class="col-12 col-md-6">
            <label class="form-label">Item Image</label>
            <input type="file" class="form-control" name="item_image" accept="image/*" id="add_image_input">
            <img id="add_image_preview" class="img-preview" src="" alt="Preview">
          </div>

          <div class="col-12 col-md-4">
            <label class="form-label">Price (Blood Echoes)</label>
            <input type="number" step="0.01" class="form-control" name="price" placeholder="0.00" required>
          </div>

          <div class="col-12 col-md-4">
            <label class="form-label">Insight Required</label>
            <input type="number" class="form-control" name="insight_required" placeholder="0" min="0" value="0">
          </div>

          <div class="col-12 col-md-4">
            <label class="form-label">Stock</label>
            <input type="number" class="form-control" name="stock" placeholder="0" required>
          </div>

          <div class="col-12">
            <label class="form-label">Description</label>
            <textarea class="form-control" name="description" rows="2" placeholder="A short lore-friendly description..."></textarea>
          </div>

          <div class="col-12">
            <button type="submit" name="add_item" class="btn btn-outline-secondary btn-bb-add px-4">
              Add to Workshop
            </button>
          </div>

        </div>
      </form>
    </div>
  </div>

  <!-- ===== Items Table ===== -->
  <div class="card bb-card">
    <div class="card-header" style="color: var(--bb-bone);">Current Inventory</div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table bb-table mb-0">
          <thead>
            <tr>
              <th>ID</th>
              <th>Image</th>
              <th>Item Name</th>
              <th>Description</th>
              <th>Cost</th>
              <th>Insight Req.</th>
              <th>Stock</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if(mysqli_num_rows($items_result) > 0): ?>
              <?php while($item = mysqli_fetch_assoc($items_result)): ?>
                <tr>
                  <td><?php echo $item['item_id']; ?></td>
                  <td>
                    <img
                      src="<?php echo !empty($item['img_path']) ? $item['img_path'] : 'images/items/placeholder.jpg'; ?>"
                      class="item-img"
                      alt="<?php echo $item['item_name']; ?>"
                    >
                  </td>
                  <td><?php echo $item['item_name']; ?></td>
                  <td><?php echo $item['description']; ?></td>
                  <td><?php echo number_format($item['price']); ?> Blood Echoes</td>
                  <td>
                    <?php if($item['insight_required'] > 0): ?>
                      <span style="color: var(--bb-select);"> <?php echo (int)$item['insight_required']; ?></span>
                    <?php else: ?>
                      <span style="color: var(--bb-bone-dim);">—</span>
                    <?php endif; ?>
                  </td>
                  <td><?php echo $item['stock']; ?></td>
                  <td>
                    <div class="d-flex gap-2">

                      <!-- Edit button triggers modal -->
                      <button
                        class="btn btn-outline-secondary btn-bb-edit btn-sm"
                        data-bs-toggle="modal"
                        data-bs-target="#editModal"
                        data-id="<?php echo $item['item_id']; ?>"
                        data-name="<?php echo htmlspecialchars($item['item_name']); ?>"
                        data-description="<?php echo htmlspecialchars($item['description']); ?>"
                        data-price="<?php echo $item['price']; ?>"
                        data-insight="<?php echo (int)$item['insight_required']; ?>"
                        data-stock="<?php echo $item['stock']; ?>"
                        data-img="<?php echo htmlspecialchars($item['img_path']); ?>"
                      >
                        Edit
                      </button>

                      <!-- Delete button -->
                      <form action="admin_inventory.php" method="POST" onsubmit="return confirm('Remove this item from the Workshop?');">
                        <input type="hidden" name="item_id" value="<?php echo $item['item_id']; ?>">
                        <button type="submit" name="delete_item" class="btn btn-outline-secondary btn-bb-delete btn-sm">
                          Delete
                        </button>
                      </form>

                    </div>
                  </td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="8" class="text-center fst-italic py-4" style="color: var(--bb-bone-dim);">
                  The Workshop is empty. Add your first item above.
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

</div>

<!-- ===== Edit Item Modal ===== -->
<div class="modal fade" id="editModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" style="font-family:'Cinzel',serif; letter-spacing:0.1em;">Edit Item</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <!-- enctype required for file uploads in edit form too -->
      <form action="admin_inventory.php" method="POST" enctype="multipart/form-data">
        <div class="modal-body">
          <input type="hidden" name="item_id" id="edit_item_id">
          <input type="hidden" name="current_img" id="edit_current_img">
          <div class="row g-3">

            <div class="col-12 col-md-6">
              <label class="form-label">Item Name</label>
              <input type="text" class="form-control" name="item_name" id="edit_item_name" required>
            </div>

            <div class="col-12 col-md-6">
              <label class="form-label">Item Image</label>
              <img id="edit_image_preview" class="img-preview d-block" src="" alt="Current Image" style="display:block !important; margin-bottom:0.5rem;">
              <input type="file" class="form-control" name="edit_image" id="edit_image_input" accept="image/*">
              <small style="color: var(--bb-bone-dim); font-style:italic;">Leave blank to keep current image</small>
            </div>

            <div class="col-12 col-md-4">
              <label class="form-label">Price (Blood Echoes)</label>
              <input type="number" step="0.01" class="form-control" name="price" id="edit_price" required>
            </div>

            <div class="col-12 col-md-4">
              <label class="form-label">Insight Required</label>
              <input type="number" class="form-control" name="insight_required" id="edit_insight" min="0">
            </div>

            <div class="col-12 col-md-4">
              <label class="form-label">Stock</label>
              <input type="number" class="form-control" name="stock" id="edit_stock" required>
            </div>

            <div class="col-12">
              <label class="form-label">Description</label>
              <textarea class="form-control" name="description" id="edit_description" rows="2"></textarea>
            </div>

          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary btn-bb-edit" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" name="edit_item" class="btn btn-outline-secondary btn-bb-add">Save Changes</button>
        </div>
      </form>
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

  // Preview image before upload on Add form
  document.getElementById('add_image_input').addEventListener('change', function() {
    const preview = document.getElementById('add_image_preview');
    if(this.files && this.files[0]) {
      preview.src = URL.createObjectURL(this.files[0]);
      preview.style.display = 'block';
    }
  });

  // Preview image before upload on Edit modal
  document.getElementById('edit_image_input').addEventListener('change', function() {
    const preview = document.getElementById('edit_image_preview');
    if(this.files && this.files[0]) {
      preview.src = URL.createObjectURL(this.files[0]);
    }
  });

  // Populate edit modal with current item data
  const editModal = document.getElementById('editModal');
  editModal.addEventListener('show.bs.modal', function(e) {
    const btn = e.relatedTarget;
    document.getElementById('edit_item_id').value      = btn.getAttribute('data-id');
    document.getElementById('edit_item_name').value    = btn.getAttribute('data-name');
    document.getElementById('edit_description').value  = btn.getAttribute('data-description');
    document.getElementById('edit_price').value        = btn.getAttribute('data-price');
    document.getElementById('edit_insight').value       = btn.getAttribute('data-insight');
    document.getElementById('edit_stock').value        = btn.getAttribute('data-stock');
    document.getElementById('edit_current_img').value  = btn.getAttribute('data-img');

    // Show current image in modal preview
    const imgSrc = btn.getAttribute('data-img');
    const preview = document.getElementById('edit_image_preview');
    if(imgSrc) {
      preview.src = imgSrc;
    } else {
      preview.src = 'images/items/placeholder.jpg';
    }
  });
</script>

</body>
</html>