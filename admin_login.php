<?php
session_start();

// kalau sudah login, langsung ke dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: admin_dashboard.php");
    exit;
}

$error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // username & password default
    $valid_user = "admin";
    $valid_pass = "12345";

    if ($username === $valid_user && $password === $valid_pass) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        header("Location: admin_dashboard.php");
        exit;
    } else {
        $error = "Username atau password salah!";
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Login Admin</title>
</head>
<body style="margin:0;font-family:Arial,sans-serif;height:100vh;display:flex;justify-content:center;align-items:center;background:#f2f2f2;">

  <div style="background:#fff;padding:30px;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.2);width:100%;max-width:350px;">
    <h2 style="text-align:center;color:#2F3640;margin-top:0;">Login Admin</h2>

    <?php if (!empty($error)): ?>
      <div style="color:red;margin-bottom:10px;text-align:center;"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="post">
      <input type="text" name="username" placeholder="Username" required
             style="width:100%;padding:10px;margin:8px 0;border-radius:6px;border:1px solid #ccc;">
      <input type="password" name="password" placeholder="Password" required
             style="width:100%;padding:10px;margin:8px 0;border-radius:6px;border:1px solid #ccc;">
      <button type="submit"
              style="width:100%;padding:12px;background:#2F3640;color:#fff;border:none;border-radius:6px;cursor:pointer;">
        Login
      </button>
    </form>
  </div>

</body>
</html>
