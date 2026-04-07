<?php
session_start();
include "config.php"; // Pastikan koneksi disertakan

// Cek Login
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

// --- LOGIKA HITUNG STATISTIK ---
// 1. Hitung Total Siswa (dari tabel siswa biar akurat datanya real)
$query_siswa = mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role='siswa'");
$data_siswa = mysqli_fetch_assoc($query_siswa);
$total_siswa = $data_siswa['total'];

// 2. Hitung Pembimbing (ambil dari tabel users yang rolenya pembimbing)
$query_pembimbing = mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role='pembimbing'");
$data_pembimbing = mysqli_fetch_assoc($query_pembimbing);
$total_pembimbing = $data_pembimbing['total'];

// 3. Hitung Mitra Industri (dari tabel perusahaan)
$query_mitra = mysqli_query($conn, "SELECT COUNT(*) as total FROM perusahaan");
$data_mitra = mysqli_fetch_assoc($query_mitra);
$total_mitra = $data_mitra['total'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard Admin</title>
  <link rel="stylesheet" href="style.css" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

  <style>
    /* ===== RESET & BASE ===== */
    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'Poppins', sans-serif;
      background: #0f172a;
      color: #e2e8f0;
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }

    /* ===== HEADER ===== */
    header {
      position: fixed;
      top: 0; left: 0; right: 0;
      z-index: 100;
      background: rgba(15, 23, 42, 0.95);
      backdrop-filter: blur(10px);
      border-bottom: 1px solid rgba(45, 212, 191, 0.2);
      height: 64px;
    }

    header nav {
      display: flex;
      align-items: center;
      justify-content: space-between;
      height: 100%;
      padding: 0 24px;
    }

    .logo {
      font-weight: 700;
      font-size: 1.1rem;
      color: #2dd4bf;
      letter-spacing: 2px;
    }

    .nav-links {
      list-style: none;
      display: flex;
      gap: 8px;
      align-items: center;
    }

    .nav-links a {
      text-decoration: none;
      color: #94a3b8;
      font-size: 0.85rem;
      padding: 6px 14px;
      border-radius: 8px;
      transition: all 0.2s;
      display: flex;
      align-items: center;
      gap: 6px;
    }

    .nav-links a:hover,
    .nav-links a.active {
      color: #2dd4bf;
      background: rgba(45, 212, 191, 0.1);
    }

    /* ===== LAYOUT WRAPPER ===== */
    .layout-wrapper {
      display: flex;
      margin-top: 64px;
      min-height: calc(100vh - 64px);
    }

    /* ===== SIDEBAR ===== */
    .sidebar {
      width: 260px;
      min-height: calc(100vh - 64px);
      background: #0f172a;
      border-right: 1px solid rgba(45, 212, 191, 0.15);
      padding: 24px 0;
      position: fixed;
      top: 64px;
      left: 0;
      bottom: 0;
      overflow-y: auto;
      transition: width 0.3s ease, transform 0.3s ease;
      z-index: 90;
    }

    .sidebar.collapsed {
      width: 68px;
    }

    .sidebar-section {
      padding: 0 16px;
      margin-bottom: 8px;
    }

    .sidebar-section-title {
      font-size: 0.65rem;
      font-weight: 600;
      letter-spacing: 2px;
      color: #475569;
      text-transform: uppercase;
      padding: 12px 8px 6px;
      white-space: nowrap;
      overflow: hidden;
    }

    .sidebar.collapsed .sidebar-section-title {
      opacity: 0;
      pointer-events: none;
    }

    .sidebar-item {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 10px 12px;
      border-radius: 10px;
      text-decoration: none;
      color: #94a3b8;
      font-size: 0.875rem;
      font-weight: 500;
      transition: all 0.2s;
      white-space: nowrap;
      overflow: hidden;
      position: relative;
      margin-bottom: 2px;
    }

    .sidebar-item i {
      width: 20px;
      text-align: center;
      font-size: 1rem;
      flex-shrink: 0;
      transition: color 0.2s;
    }

    .sidebar-item span {
      transition: opacity 0.2s;
    }

    .sidebar.collapsed .sidebar-item span {
      opacity: 0;
      width: 0;
      overflow: hidden;
    }

    .sidebar-item:hover {
      color: #e2e8f0;
      background: rgba(255,255,255,0.06);
    }

    .sidebar-item.active {
      color: #2dd4bf;
      background: rgba(45, 212, 191, 0.12);
      border: 1px solid rgba(45, 212, 191, 0.2);
    }

    .sidebar-item.active i { color: #2dd4bf; }

    .sidebar-item.danger { color: #94a3b8; }
    .sidebar-item.danger:hover { color: #ef4444; background: rgba(239,68,68,0.1); }

    /* Badge notif */
    .badge {
      margin-left: auto;
      background: #ef4444;
      color: #fff;
      font-size: 0.65rem;
      font-weight: 700;
      padding: 2px 7px;
      border-radius: 99px;
      flex-shrink: 0;
      transition: opacity 0.2s;
    }

    .sidebar.collapsed .badge { opacity: 0; }

    /* Tooltip saat collapsed */
    .sidebar.collapsed .sidebar-item:hover::after {
      content: attr(data-tooltip);
      position: absolute;
      left: 68px;
      top: 50%;
      transform: translateY(-50%);
      background: #1e293b;
      color: #e2e8f0;
      padding: 6px 12px;
      border-radius: 8px;
      font-size: 0.8rem;
      white-space: nowrap;
      border: 1px solid rgba(45,212,191,0.2);
      pointer-events: none;
      z-index: 200;
    }

    /* Toggle button */
    .sidebar-toggle {
      position: fixed;
      top: 74px;
      left: 246px;
      z-index: 110;
      background: #1e293b;
      border: 1px solid rgba(45,212,191,0.3);
      color: #2dd4bf;
      width: 28px;
      height: 28px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      font-size: 0.75rem;
      transition: left 0.3s ease, transform 0.3s ease;
    }

    .sidebar-toggle.collapsed {
      left: 54px;
      transform: rotate(180deg);
    }

    /* Divider */
    .sidebar-divider {
      height: 1px;
      background: rgba(45,212,191,0.1);
      margin: 12px 16px;
    }

    /* Profile box di bawah sidebar */
    .sidebar-profile {
      padding: 16px;
      margin-top: auto;
      border-top: 1px solid rgba(45,212,191,0.15);
      display: flex;
      align-items: center;
      gap: 10px;
      overflow: hidden;
    }

    .sidebar-profile .avatar {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      background: linear-gradient(135deg, #2dd4bf, #0ea5e9);
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 700;
      font-size: 0.9rem;
      color: #0f172a;
      flex-shrink: 0;
    }

    .sidebar-profile .profile-info {
      overflow: hidden;
      transition: opacity 0.2s;
    }

    .sidebar.collapsed .profile-info { opacity: 0; width: 0; }

    .profile-info .name {
      font-size: 0.8rem;
      font-weight: 600;
      color: #e2e8f0;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .profile-info .role-label {
      font-size: 0.7rem;
      color: #2dd4bf;
    }

    /* ===== MAIN CONTENT ===== */
    .main-content {
      margin-left: 260px;
      flex: 1;
      padding: 32px;
      transition: margin-left 0.3s ease;
    }

    .main-content.expanded {
      margin-left: 68px;
    }

    /* ===== BREADCRUMB ===== */
    .breadcrumb {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 0.8rem;
      color: #475569;
      margin-bottom: 24px;
    }

    .breadcrumb span { color: #2dd4bf; }

    /* ===== WELCOME SECTION ===== */
    .welcome-section {
      background: linear-gradient(135deg, rgba(45,212,191,0.12), rgba(14,165,233,0.08));
      border: 1px solid rgba(45,212,191,0.2);
      border-radius: 16px;
      padding: 28px 32px;
      margin-bottom: 28px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
      animation: fadeUp 0.5s ease both;
    }

    .welcome-section h1 {
      font-size: 1.5rem;
      font-weight: 700;
      color: #e2e8f0;
    }

    .welcome-section p {
      color: #94a3b8;
      font-size: 0.875rem;
      margin-top: 4px;
    }

    .welcome-section .status-badge {
      display: flex;
      align-items: center;
      gap: 8px;
      background: rgba(45,212,191,0.1);
      border: 1px solid rgba(45,212,191,0.25);
      padding: 8px 16px;
      border-radius: 99px;
      font-size: 0.8rem;
      color: #2dd4bf;
      white-space: nowrap;
    }

    .status-dot {
      width: 8px; height: 8px;
      border-radius: 50%;
      background: #2dd4bf;
      animation: pulse 2s infinite;
    }

    @keyframes pulse {
      0%, 100% { opacity: 1; transform: scale(1); }
      50% { opacity: 0.5; transform: scale(0.85); }
    }

    /* ===== STAT GRID ===== */
    .section-title {
      font-size: 0.75rem;
      font-weight: 600;
      letter-spacing: 2px;
      text-transform: uppercase;
      color: #475569;
      margin-bottom: 16px;
    }

    .stat-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
      gap: 16px;
      margin-bottom: 28px;
    }

    .stat-card {
      background: #1e293b;
      border: 1px solid rgba(45,212,191,0.2);
      border-radius: 14px;
      padding: 20px;
      transition: transform 0.2s, box-shadow 0.2s;
      animation: fadeUp 0.5s ease both;
    }

    .stat-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 24px rgba(0,0,0,0.3);
    }

    .stat-card .card-icon {
      width: 42px; height: 42px;
      border-radius: 10px;
      display: flex; align-items: center; justify-content: center;
      font-size: 1.1rem;
      margin-bottom: 14px;
    }

    .stat-card h3 {
      font-size: 0.8rem;
      font-weight: 500;
      color: #94a3b8;
      margin-bottom: 6px;
    }

    .stat-card .number {
      font-size: 2rem;
      font-weight: 700;
      line-height: 1;
    }

    .stat-card .trend {
      font-size: 0.72rem;
      color: #64748b;
      margin-top: 6px;
    }

    /* ===== QUICK ACTION GRID ===== */
    .quick-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
      gap: 14px;
      margin-bottom: 28px;
    }

    .quick-card {
      background: #1e293b;
      border: 1px solid rgba(255,255,255,0.06);
      border-radius: 12px;
      padding: 18px 20px;
      text-decoration: none;
      color: #94a3b8;
      font-size: 0.875rem;
      font-weight: 500;
      display: flex;
      align-items: center;
      gap: 12px;
      transition: all 0.2s;
      animation: fadeUp 0.5s ease both;
    }

    .quick-card i {
      width: 36px; height: 36px;
      border-radius: 9px;
      display: flex; align-items: center; justify-content: center;
      font-size: 1rem;
      flex-shrink: 0;
    }

    .quick-card:hover {
      color: #e2e8f0;
      background: #263348;
      transform: translateY(-2px);
      border-color: rgba(45,212,191,0.2);
    }

    /* ===== RECENT ACTIVITY ===== */
    .two-col {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
      margin-bottom: 28px;
    }

    @media (max-width: 900px) { .two-col { grid-template-columns: 1fr; } }

    .panel {
      background: #1e293b;
      border: 1px solid rgba(255,255,255,0.06);
      border-radius: 14px;
      padding: 20px;
      animation: fadeUp 0.5s ease both;
    }

    .panel-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 16px;
    }

    .panel-header h3 {
      font-size: 0.9rem;
      font-weight: 600;
      color: #e2e8f0;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .panel-header a {
      font-size: 0.75rem;
      color: #2dd4bf;
      text-decoration: none;
    }

    .activity-item {
      display: flex;
      align-items: flex-start;
      gap: 12px;
      padding: 10px 0;
      border-bottom: 1px solid rgba(255,255,255,0.05);
    }

    .activity-item:last-child { border-bottom: none; }

    .activity-icon {
      width: 32px; height: 32px;
      border-radius: 8px;
      display: flex; align-items: center; justify-content: center;
      font-size: 0.8rem;
      flex-shrink: 0;
      margin-top: 2px;
    }

    .activity-text { flex: 1; }
    .activity-text p { font-size: 0.82rem; color: #cbd5e1; line-height: 1.4; }
    .activity-text span { font-size: 0.72rem; color: #475569; }

    /* Notif list */
    .notif-item {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 10px 0;
      border-bottom: 1px solid rgba(255,255,255,0.05);
    }

    .notif-item:last-child { border-bottom: none; }

    .notif-dot {
      width: 8px; height: 8px;
      border-radius: 50%;
      flex-shrink: 0;
    }

    .notif-item p { font-size: 0.82rem; color: #cbd5e1; }
    .notif-item span { font-size: 0.72rem; color: #475569; margin-left: auto; white-space: nowrap; }

    /* ===== SYSTEM STATUS ===== */
    .status-list { display: flex; flex-direction: column; gap: 12px; }

    .status-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
    }

    .status-row span { font-size: 0.8rem; color: #94a3b8; }

    .progress-bar {
      flex: 1;
      height: 6px;
      background: rgba(255,255,255,0.08);
      border-radius: 99px;
      overflow: hidden;
    }

    .progress-fill {
      height: 100%;
      border-radius: 99px;
      transition: width 1s ease;
    }

    .status-row .val { font-size: 0.75rem; color: #64748b; white-space: nowrap; }

    /* ===== ANIMATIONS ===== */
    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(18px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    .delay-1 { animation-delay: 0.08s; }
    .delay-2 { animation-delay: 0.16s; }
    .delay-3 { animation-delay: 0.24s; }
    .delay-4 { animation-delay: 0.32s; }
    .delay-5 { animation-delay: 0.40s; }

    /* ===== MOBILE ===== */
    .sidebar-overlay {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.5);
      z-index: 88;
    }

    @media (max-width: 768px) {
      .sidebar {
        transform: translateX(-100%);
        width: 260px !important;
      }

      .sidebar.mobile-open {
        transform: translateX(0);
      }

      .sidebar-overlay.active { display: block; }

      .sidebar-toggle { display: none; }

      .main-content { margin-left: 0 !important; padding: 20px 16px; }

      .mobile-menu-btn {
        display: flex !important;
      }

      .two-col { grid-template-columns: 1fr; }
      .welcome-section { flex-direction: column; align-items: flex-start; }
    }

    .mobile-menu-btn {
      display: none;
      align-items: center;
      justify-content: center;
      background: none;
      border: 1px solid rgba(45,212,191,0.3);
      color: #2dd4bf;
      width: 34px; height: 34px;
      border-radius: 8px;
      cursor: pointer;
      font-size: 0.9rem;
    }
  </style>
</head>
<body>

<!-- ===== HEADER ===== -->
<header>
  <nav>
    <div style="display:flex; align-items:center; gap:12px;">
      <button class="mobile-menu-btn" id="mobileMenuBtn"><i class="fas fa-bars"></i></button>
      <div class="logo"><i class="fas fa-shield-alt" style="margin-right:8px;"></i>ADMIN PANEL</div>
    </div>
    <ul class="nav-links">
      <li>
        <a href="dashboard admin.php" class="active">
          <i class="fa fa-th-large"></i> Dashboard
        </a>
      </li>
      <li>
        <a href="#" style="position:relative;">
          <i class="fas fa-bell"></i>
          <span style="position:absolute;top:2px;right:2px;width:8px;height:8px;background:#ef4444;border-radius:50%;"></span>
        </a>
      </li>
      <li>
        <a href="login.php" id="logout-link">
          <i class="fas fa-sign-out-alt"></i> Logout
        </a>
      </li>
    </ul>
  </nav>
</header>

<!-- ===== SIDEBAR TOGGLE ===== -->
<button class="sidebar-toggle" id="sidebarToggle" title="Toggle Sidebar">
  <i class="fas fa-chevron-left"></i>
</button>

<!-- ===== SIDEBAR ===== -->
<aside class="sidebar" id="sidebar">

  <!-- MENU UTAMA -->
  <div class="sidebar-section">
    <div class="sidebar-section-title">Menu Utama</div>

    <a href="dashboard admin.php" class="sidebar-item active" data-tooltip="Dashboard">
      <i class="fas fa-th-large"></i>
      <span>Dashboard</span>
    </a>

    <a href="admin-users.php" class="sidebar-item" data-tooltip="Manajemen User">
      <i class="fas fa-users-cog"></i>
      <span>Manajemen User</span>
    </a>

    <a href="admin-siswa.php" class="sidebar-item" data-tooltip="Data Siswa">
      <i class="fas fa-user-graduate"></i>
      <span>Data Siswa</span>
    </a>

    <a href="admin-pembimbing.php" class="sidebar-item" data-tooltip="Pembimbing">
      <i class="fas fa-chalkboard-teacher"></i>
      <span>Pembimbing</span>
    </a>

    <a href="admin-perusahaan.php" class="sidebar-item" data-tooltip="Perusahaan">
      <i class="fas fa-building"></i>
      <span>Perusahaan</span>
    </a>
  </div>

  <div class="sidebar-divider"></div>

  <!-- PKL -->
  <div class="sidebar-section">
    <div class="sidebar-section-title">Manajemen PKL</div>

    <a href="pkl-penempatan.php" class="sidebar-item" data-tooltip="Penempatan PKL">
      <i class="fas fa-map-marker-alt"></i>
      <span>Penempatan PKL</span>
    </a>

    <a href="pkl-absensi.php" class="sidebar-item" data-tooltip="Absensi">
      <i class="fas fa-calendar-check"></i>
      <span>Absensi</span>
    </a>


    <a href="pkl-nilai.php" class="sidebar-item" data-tooltip="Penilaian">
      <i class="fas fa-star"></i>
      <span>Penilaian</span>
    </a>

    <a href="pkl-laporan.php" class="sidebar-item" data-tooltip="Laporan PKL">
      <i class="fas fa-file-alt"></i>
      <span>Laporan PKL</span>
      <span class="badge">3</span>
    </a>
  </div>

  <div class="sidebar-divider"></div>

  <!-- SISTEM -->
  <div class="sidebar-section">
    <div class="sidebar-section-title">Sistem</div>

    <a href="log-aktivitas.php" class="sidebar-item" data-tooltip="Log Aktivitas">
      <i class="fas fa-history"></i>
      <span>Log Aktivitas</span>
    </a>
  </div>

  <div class="sidebar-divider"></div>

  <!-- AKUN -->
  <div class="sidebar-section">
    <a href="profil.php" class="sidebar-item" data-tooltip="Profil Saya">
      <i class="fas fa-user-circle"></i>
      <span>Profil Saya</span>
    </a>
    <a href="login.php" class="sidebar-item danger" data-tooltip="Logout">
      <i class="fas fa-sign-out-alt"></i>
      <span>Logout</span>
    </a>
  </div>

  <!-- PROFILE BOX -->
  <div class="sidebar-profile">
    <div class="avatar"><?php echo strtoupper(substr($_SESSION['user']['username'], 0, 1)); ?></div>
    <div class="profile-info">
      <div class="name"><?php echo $_SESSION['user']['username']; ?></div>
      <div class="role-label">Administrator</div>
    </div>
  </div>

</aside>

<!-- Overlay mobile -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- ===== MAIN CONTENT ===== -->
<div class="layout-wrapper">
  <main class="main-content" id="mainContent">

    <!-- Breadcrumb -->
    <div class="breadcrumb">
      <i class="fas fa-home"></i>
      <i class="fas fa-chevron-right" style="font-size:0.6rem;"></i>
      <span>Dashboard</span>
    </div>

    <!-- Welcome -->
    <div class="welcome-section">
      <div>
        <h1>Selamat Datang, <?php echo $_SESSION['user']['username']; ?> 👋</h1>
        <p>Pusat pengelolaan data master dan konfigurasi sistem PKL.</p>
      </div>
      <div class="status-badge">
        <div class="status-dot"></div>
        Online
      </div>
    </div>

    <!-- Stat Cards -->
    <div class="section-title">Data Master</div>
    <div class="stat-grid">

      <div class="stat-card delay-1">
        <div class="card-icon" style="background:rgba(45,212,191,0.12);">
          <i class="fas fa-user-graduate" style="color:#2dd4bf;"></i>
        </div>
        <h3>Total Siswa</h3>
        <div class="number" style="color:#2dd4bf;"><?php echo $total_siswa; ?></div>
        <div class="trend"><i class="fas fa-circle" style="font-size:0.5rem; color:#2dd4bf;"></i> Siswa aktif terdaftar</div>
      </div>

      <div class="stat-card delay-2">
        <div class="card-icon" style="background:rgba(245,158,11,0.12);">
          <i class="fas fa-chalkboard-teacher" style="color:#f59e0b;"></i>
        </div>
        <h3>Pembimbing</h3>
        <div class="number" style="color:#f59e0b;"><?php echo $total_pembimbing; ?></div>
        <div class="trend"><i class="fas fa-circle" style="font-size:0.5rem; color:#f59e0b;"></i> Guru pembimbing PKL</div>
      </div>

      <div class="stat-card delay-3">
        <div class="card-icon" style="background:rgba(239,68,68,0.12);">
          <i class="fas fa-building" style="color:#ef4444;"></i>
        </div>
        <h3>Perusahaan</h3>
        <div class="number" style="color:#ef4444;"><?php echo $total_mitra; ?></div>
        <div class="trend"><i class="fas fa-circle" style="font-size:0.5rem; color:#ef4444;"></i> Mitra industri PKL</div>
      </div>

      <div class="stat-card delay-4">
        <div class="card-icon" style="background:rgba(139,92,246,0.12);">
          <i class="fas fa-file-alt" style="color:#8b5cf6;"></i>
        </div>
        <h3>Laporan Masuk</h3>
        <div class="number" style="color:#8b5cf6;">3</div>
        <div class="trend"><i class="fas fa-circle" style="font-size:0.5rem; color:#8b5cf6;"></i> Menunggu persetujuan</div>
      </div>

    </div>
<script>
  const sidebar      = document.getElementById('sidebar');
  const mainContent  = document.getElementById('mainContent');
  const toggleBtn    = document.getElementById('sidebarToggle');
  const mobileBtn    = document.getElementById('mobileMenuBtn');
  const overlay      = document.getElementById('sidebarOverlay');

  // Desktop toggle collapse
  toggleBtn.addEventListener('click', () => {
    sidebar.classList.toggle('collapsed');
    mainContent.classList.toggle('expanded');
    toggleBtn.classList.toggle('collapsed');
  });

  // Mobile toggle open
  mobileBtn.addEventListener('click', () => {
    sidebar.classList.toggle('mobile-open');
    overlay.classList.toggle('active');
  });

  overlay.addEventListener('click', () => {
    sidebar.classList.remove('mobile-open');
    overlay.classList.remove('active');
  });
</script>

</body>
</html>
