<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login | Gehrman's Workshop</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700&family=EB+Garamond:ital,wght@0,400;0,500;1,400&display=swap" rel="stylesheet">

<?php


session_start();
require_once 'db_connect.php';
////
if(isset($_SESSION['hunter_id'])) {
    header('Location: home.php');
    exit;
}

$error = '';

if(isset($_POST['login'])) {

    $email    = trim($_POST['email']);
    $password = md5($_POST['password']); 

    $sql    = "SELECT * FROM hunters WHERE email = ? AND password = ?";
    $stmt   = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $email, $password);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if(mysqli_num_rows($result) == 1) {
        $hunter = mysqli_fetch_assoc($result);

        $_SESSION['hunter_id'] = $hunter['hunter_id'];
        $_SESSION['username']= $hunter['username'];
        $_SESSION['role'] = $hunter['role'];
        $_SESSION['blood_echoes'] = $hunter['blood_echoes'];
        $_SESSION['insight'] = $hunter['insight'];

        header('Location: home.php');
        exit;
    } else {
        $error = "That Hunter does not match our records. Try again.";
    }
}
?>
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
    filter: grayscale(15%) brightness(0.6) contrast(1.1);
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
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='2'/%3E%3C/filter%3E%3Crect width='100' height='100' filter='url(%23n)' opacity='0.4'/%3E%3C/svg%3E");
  }

  .game-title {
    font-family: 'Cinzel', serif;
    font-weight: 700;
    letter-spacing: 0.12em;
    font-size: clamp(1.8rem, 4vw, 3rem);
    color: var(--bb-bone);
    text-shadow: 0 0 18px rgba(156,28,28,0.45), 0 2px 4px #000;
  }

  .game-subtitle {
    font-family: 'Cinzel', serif;
    font-size: clamp(0.75rem, 1.2vw, 0.95rem);
    letter-spacing: 0.3em;
    color: var(--bb-blood-bright);
    text-transform: uppercase;
  }

  .ornament-line {
    height: 1px;
    background: linear-gradient(to right, transparent, var(--bb-blood-bright), transparent);
  }

  .ritual-card {
    background: rgba(8,6,5,0.6) !important;
    border-color: rgba(156,28,28,0.4) !important;
    backdrop-filter: blur(2px);
  }

  .ritual-card .form-label {
    font-family: 'Cinzel', serif;
    font-size: 0.7rem;
    letter-spacing: 0.15em;
    color: var(--bb-bone-dim);
    text-transform: uppercase;
  }

  .ritual-card .form-control {
    background: rgba(0,0,0,0.4);
    border-color: rgba(156,28,28,0.35);
    color: var(--bb-bone);
    font-family: 'EB Garamond', serif;
  }

  .ritual-card .form-control::placeholder {
    color: #5c564c;
  }

  .ritual-card .form-control:focus {
    background: rgba(0,0,0,0.55);
    border-color: var(--bb-glow);
    color: var(--bb-bone);
    box-shadow: 0 0 0 0.15rem rgba(244,211,94,0.25);
  }

  /* yung bootstrap outline button with yellow hover */
  .btn-awaken {
    --bs-btn-color: var(--bb-bone-dim);
    --bs-btn-border-color: rgba(156,28,28,0.5);
    --bs-btn-hover-color: var(--bb-glow);
    --bs-btn-hover-border-color: var(--bb-glow);
    --bs-btn-hover-bg: transparent;
    --bs-btn-active-bg: transparent;
    --bs-btn-active-color: var(--bb-glow);
    --bs-btn-active-border-color: var(--bb-glow);
    font-family: 'Cinzel', serif;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    transition: color .25s ease, text-shadow .25s ease, letter-spacing .25s ease, border-color .25s ease;
  }

  .btn-awaken:hover,
  .btn-awaken:focus {
    letter-spacing: 0.16em;
    text-shadow: 0 0 8px var(--bb-glow), 0 0 18px rgba(244,211,94,.7);
  }

  .alt-link {
    font-family: 'EB Garamond', serif;
    font-style: italic;
    color: var(--bb-bone-dim);
    text-decoration: none;
    transition: color .25s ease;
  }

  .alt-link:hover {
    color: var(--bb-glow);
  }

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

  <img class="bg-art" src="loginandregister.jpg" alt="">
  <div class="vignette"></div>
  <div class="grain"></div>

  <div class="container position-relative" style="z-index:3; min-height:100vh;">
    <div class="row min-vh-100 justify-content-center align-items-center text-center">
      <div class="col-12 col-md-8 col-lg-5">

        <h1 class="game-title mb-1">LOGIN</h1>
        <div class="game-subtitle mb-3">Awaken Dear Hunter</div>

        <div class="ornament-line mx-auto mb-4" style="max-width: 240px;"></div>

        <div class="card ritual-card mx-auto text-start">
          <div class="card-body p-4">

            <?php if($error): ?>
              <div class="alert text-center small mb-3" style="background: rgba(107,15,16,0.2); border-color: rgba(156,28,28,0.5); color: var(--bb-blood-bright);">
                <?php echo $error; ?>
              </div>
            <?php endif; ?>

            <form action="login.php" method="POST">

              <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type = "email" class = "form-control" id = "email" name = "email" placeholder = "gascoigne@hunters.com"
                value = "<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
      
                
              </div>

              <div class="mb-4">
                <label for="password" class="form-label">Password</label>
                <input type = "password" class="form-control" id="password" name="password" placeholder="password" required>
                
              </div>

              <button type="submit" name="login" class="btn btn-outline-secondary btn-awaken w-100">Login</button>

            </form>

            <div class="text-center mt-3">
              <a href="register.php" class="alt-link">Begin your contract.</a>
            </div>

          </div>
        </div>

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