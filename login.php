<?php
session_start();
include "config.php";

// Jika sudah login, redirect ke dashboard
if (isset($_SESSION['user'])) {
    redirect('dashboard admin.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = clean($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        $q = mysqli_query($conn, "SELECT * FROM users WHERE username='$username' LIMIT 1");
        $user = mysqli_fetch_assoc($q);

        if ($user && password_verify($password, $user['password'])) {
            if ($user['role'] === 'admin') {
                $_SESSION['user'] = $user;
                redirect('dashboard admin.php');
            } else {
                $error = 'Akses ditolak. Hanya Admin yang bisa masuk di sini.';
            }
        } else {
            $error = 'Username atau password salah.';
        }
    } else {
        $error = 'Harap isi semua kolom.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login — Admin PKL</title>
  <link rel="stylesheet" href="style.css"/>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
  <style>
    body { display:flex; align-items:center; justify-content:center; min-height:100vh; background:#0f172a; }
    .login-box {
      width:100%; max-width:400px; padding:40px 36px;
      background:#1e293b; border:1px solid rgba(45,212,191,0.2);
      border-radius:20px; animation: fadeUp .5s ease;
    }
    .login-logo { text-align:center; margin-bottom:28px; }
    .login-logo i { font-size:2.5rem; color:#2dd4bf; }
    .login-logo h1 { font-size:1.3rem; margin-top:10px; color:#e2e8f0; }
    .login-logo p  { font-size:.8rem; color:#64748b; margin-top:4px; }
    .login-box .btn { width:100%; justify-content:center; margin-top:8px; }
    .toggle-pass {
      position:absolute; right:12px; top:50%; transform:translateY(-50%);
      color:#475569; cursor:pointer; font-size:.9rem;
    }
    .field-wrap { position:relative; }
  </style>
</head>
<body>
<div class="login-box">
  <div class="login-logo">
    <i class="fas fa-shield-alt"></i>
    <h1>Admin Panel PKL</h1>
    <p>Masuk untuk melanjutkan</p>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
  <?php endif; ?>

  <form method="POST" action="">
    <div class="form-group">
      <label><i class="fas fa-user" style="color:#2dd4bf;margin-right:6px;"></i> Username</label>
      <input type="text" name="username" class="form-control"
             placeholder="Masukkan username" required
             value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" autocomplete="username"/>
    </div>

    <div class="form-group">
      <label><i class="fas fa-lock" style="color:#2dd4bf;margin-right:6px;"></i> Password</label>
      <div class="field-wrap">
        <input type="password" name="password" id="passInput" class="form-control"
               placeholder="Masukkan password" required autocomplete="current-password"/>
        <span class="toggle-pass" onclick="togglePass()"><i class="fas fa-eye" id="eyeIcon"></i></span>
      </div>
    </div>

    <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;margin-top:8px;">
      <i class="fas fa-sign-in-alt"></i> Masuk
    </button>
  </form>
</div>
<script>
function togglePass() {
  const inp = document.getElementById('passInput');
  const ico = document.getElementById('eyeIcon');
  if (inp.type === 'password') { inp.type='text'; ico.className='fas fa-eye-slash'; }
  else { inp.type='password'; ico.className='fas fa-eye'; }
}
</script>
</body>
</html>
