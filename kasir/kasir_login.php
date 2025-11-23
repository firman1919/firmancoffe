<?php
session_start();

// kalau sudah login, langsung ke dashboard
if (isset($_SESSION['kasir_logged_in']) && $_SESSION['kasir_logged_in'] === true) {
  header("Location: kasir.php");
  exit;
}

$error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username'] ?? '');
  $password = trim($_POST['password'] ?? '');

  // username & password default
  $valid_user = "kasir";
  $valid_pass = "12345";

  if ($username === $valid_user && $password === $valid_pass) {
    $_SESSION['kasir_logged_in'] = true;
    $_SESSION['kasir_username'] = $username;
    header("Location: kasir.php");
    exit;
  } else {
    $error = "Username atau password salah!";
  }
}
?>
<!doctype html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <title>Login Kasir</title>
</head>

<body
  style="margin:0;font-family:'Segoe UI',Arial,sans-serif;height:100vh;display:flex;justify-content:center;align-items:center;background:#ECEFF1;">

  <div
    style="background:#fff;padding:30px;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,0.15);width:100%;max-width:360px;">
    <h2 style="text-align:center;color:#2F3640;margin-top:0;font-weight:600;">Login Kasir</h2>

    <?php if (!empty($error)): ?>
      <div style="color:#e74c3c;margin-bottom:10px;text-align:center;"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="post">
      <input type="text" name="username" placeholder="Username" required
        style="width:100%;padding:12px 10px;margin:8px 0;border-radius:6px;border:1px solid #ccc;font-size:14px;">
      <input type="password" name="password" placeholder="Password" required
        style="width:100%;padding:12px 10px;margin:8px 0;border-radius:6px;border:1px solid #ccc;font-size:14px;">
      <button type="submit"
        style="width:100%;padding:12px;background:#2F3640;color:#fff;border:none;border-radius:6px;cursor:pointer;font-size:15px;font-weight:500;transition:0.3s;">
        Login
      </button>
    </form>
  </div>

</body>

</html>
