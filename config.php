<?php
// ============================================================
//  config.php — Konfigurasi Koneksi Database
//  Sistem Informasi PKL
// ============================================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // Ganti sesuai username MySQL Anda
define('DB_PASS', '');           // Ganti sesuai password MySQL Anda
define('DB_NAME', 'pkl_system'); // Ganti sesuai nama database Anda

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    die('<div style="font-family:Poppins,sans-serif;background:#0f172a;color:#ef4444;padding:40px;text-align:center;">
        <h2>❌ Koneksi Database Gagal</h2>
        <p>' . mysqli_connect_error() . '</p>
        <p style="color:#64748b;font-size:.85rem;">Periksa konfigurasi di config.php</p>
    </div>');
}

mysqli_set_charset($conn, 'utf8mb4');

// Timezone
date_default_timezone_set('Asia/Jakarta');

// Helper: Escape input
function clean($data) {
    global $conn;
    return mysqli_real_escape_string($conn, trim(htmlspecialchars($data)));
}

// Helper: Redirect
function redirect($url) {
    header("Location: $url");
    exit;
}

// Helper: Flash message (session)
function setFlash($type, $msg) {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        $color = $f['type'] === 'success' ? '#2dd4bf' : '#ef4444';
        echo "<div style='background:rgba(".($f['type']==='success'?'45,212,191':'239,68,68').",0.12);
              border:1px solid {$color};border-radius:10px;padding:12px 20px;
              color:{$color};font-size:.85rem;margin-bottom:20px;'>
              {$f['msg']}</div>";
    }
}
?>
