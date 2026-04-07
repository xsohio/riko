<?php
session_start();
include "config.php";
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') redirect('login.php');
$active_page = 'pengaturan';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Pengaturan Sistem — Admin PKL</title>
  <link rel="stylesheet" href="style.css"/>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
  <style>
    body{display:flex;flex-direction:column;}
    header{position:fixed;top:0;left:0;right:0;z-index:100;background:rgba(15,23,42,.95);backdrop-filter:blur(10px);border-bottom:1px solid rgba(45,212,191,.2);height:64px;}
    header nav{display:flex;align-items:center;justify-content:space-between;height:100%;padding:0 24px;}
    .logo{font-weight:700;font-size:1.1rem;color:#2dd4bf;letter-spacing:2px;}
    .nav-links{display:flex;gap:8px;align-items:center;}
    .nav-links a{color:#94a3b8;font-size:.85rem;padding:6px 14px;border-radius:8px;transition:all .2s;display:flex;align-items:center;gap:6px;}
    .nav-links a:hover,.nav-links a.active{color:#2dd4bf;background:rgba(45,212,191,.1);}
    .layout-wrapper{display:flex;margin-top:64px;min-height:calc(100vh - 64px);}
    .sidebar{width:260px;min-height:calc(100vh - 64px);background:#0f172a;border-right:1px solid rgba(45,212,191,.15);padding:24px 0;position:fixed;top:64px;left:0;bottom:0;overflow-y:auto;transition:width .3s,transform .3s;z-index:90;}
    .sidebar.collapsed{width:68px;}
    .sidebar-section{padding:0 16px;margin-bottom:8px;}
    .sidebar-section-title{font-size:.65rem;font-weight:600;letter-spacing:2px;color:#475569;text-transform:uppercase;padding:12px 8px 6px;white-space:nowrap;overflow:hidden;}
    .sidebar.collapsed .sidebar-section-title{opacity:0;pointer-events:none;}
    .sidebar-item{display:flex;align-items:center;gap:12px;padding:10px 12px;border-radius:10px;text-decoration:none;color:#94a3b8;font-size:.875rem;font-weight:500;transition:all .2s;white-space:nowrap;overflow:hidden;position:relative;margin-bottom:2px;}
    .sidebar-item i{width:20px;text-align:center;font-size:1rem;flex-shrink:0;}
    .sidebar.collapsed .sidebar-item span{opacity:0;width:0;overflow:hidden;}
    .sidebar-item:hover{color:#e2e8f0;background:rgba(255,255,255,.06);}
    .sidebar-item.active{color:#2dd4bf;background:rgba(45,212,191,.12);border:1px solid rgba(45,212,191,.2);}
    .sidebar-item.danger:hover{color:#ef4444;background:rgba(239,68,68,.1);}
    .badge{margin-left:auto;background:#ef4444;color:#fff;font-size:.65rem;font-weight:700;padding:2px 7px;border-radius:99px;flex-shrink:0;}
    .sidebar.collapsed .badge{opacity:0;}
    .sidebar.collapsed .sidebar-item:hover::after{content:attr(data-tooltip);position:absolute;left:68px;top:50%;transform:translateY(-50%);background:#1e293b;color:#e2e8f0;padding:6px 12px;border-radius:8px;font-size:.8rem;white-space:nowrap;border:1px solid rgba(45,212,191,.2);pointer-events:none;z-index:200;}
    .sidebar-toggle{position:fixed;top:74px;left:246px;z-index:110;background:#1e293b;border:1px solid rgba(45,212,191,.3);color:#2dd4bf;width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:.75rem;transition:left .3s,transform .3s;}
    .sidebar-toggle.collapsed{left:54px;transform:rotate(180deg);}
    .sidebar-divider{height:1px;background:rgba(45,212,191,.1);margin:12px 16px;}
    .sidebar-profile{padding:16px;border-top:1px solid rgba(45,212,191,.15);display:flex;align-items:center;gap:10px;overflow:hidden;}
    .sidebar-profile .avatar{width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#2dd4bf,#0ea5e9);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.9rem;color:#0f172a;flex-shrink:0;}
    .sidebar.collapsed .profile-info{opacity:0;width:0;}
    .profile-info .name{font-size:.8rem;font-weight:600;color:#e2e8f0;}
    .profile-info .role-label{font-size:.7rem;color:#2dd4bf;}
    .main-content{margin-left:260px;flex:1;padding:32px;transition:margin-left .3s;}
    .main-content.expanded{margin-left:68px;}
    .mobile-menu-btn{display:none;align-items:center;justify-content:center;background:none;border:1px solid rgba(45,212,191,.3);color:#2dd4bf;width:34px;height:34px;border-radius:8px;cursor:pointer;font-size:.9rem;}
    .sidebar-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:88;}
    @media(max-width:768px){.sidebar{transform:translateX(-100%);width:260px!important;}.sidebar.mobile-open{transform:translateX(0);}.sidebar-overlay.active{display:block;}.sidebar-toggle{display:none;}.main-content{margin-left:0!important;padding:20px 16px;}.mobile-menu-btn{display:flex!important;}}
  </style>
</head>
<body>
<header>
  <nav>
    <div style="display:flex;align-items:center;gap:12px;">
      <button class="mobile-menu-btn" id="mobileMenuBtn"><i class="fas fa-bars"></i></button>
      <div class="logo"><i class="fas fa-shield-alt" style="margin-right:8px;"></i>ADMIN PANEL</div>
    </div>
    <ul class="nav-links">
      <li><a href="dashboard admin.php"><i class="fa fa-th-large"></i> Dashboard</a></li>
      <li><a href="login.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>
  </nav>
</header>
<button class="sidebar-toggle" id="sidebarToggle"><i class="fas fa-chevron-left"></i></button>
<?php include 'sidebar.php'; ?>
<div class="layout-wrapper">
<main class="main-content" id="mainContent">
  <?php getFlash(); ?>
  <div class="breadcrumb">
    <i class="fas fa-home"></i>
    <i class="fas fa-chevron-right" style="font-size:.6rem;"></i>
    <a href="dashboard admin.php" style="color:#94a3b8;">Dashboard</a>
    <i class="fas fa-chevron-right" style="font-size:.6rem;"></i>
    <span>Pengaturan Sistem</span>
  </div>
  <div class="page-header">
    <h2><i class="fas fa-cog" style="color:#0ea5e9;"></i> Pengaturan Sistem</h2>
    
  </div>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;flex-wrap:wrap;">
    <div class="card">
      <h3 style="margin-bottom:20px;"><i class="fas fa-school" style="color:#2dd4bf;margin-right:8px;"></i>Informasi Sekolah</h3>
      <div class="form-group"><label>Nama Sekolah</label><input type="text" class="form-control" value="SMK Negeri 1 Contoh"/></div>
      <div class="form-group"><label>Alamat</label><textarea class="form-control" rows="2">Jl. Pendidikan No.1, Kota Contoh</textarea></div>
      <div class="form-group"><label>Tahun Ajaran</label><input type="text" class="form-control" value="2024/2025"/></div>
      <button class="btn btn-primary btn-sm"><i class="fas fa-save"></i> Simpan</button>
    </div>
    <div class="card">
      <h3 style="margin-bottom:20px;"><i class="fas fa-calendar-alt" style="color:#f59e0b;margin-right:8px;"></i>Periode PKL</h3>
      <div class="form-group"><label>Tanggal Mulai PKL</label><input type="date" class="form-control" value="2025-01-01"/></div>
      <div class="form-group"><label>Tanggal Selesai PKL</label><input type="date" class="form-control" value="2025-03-31"/></div>
      <div class="form-group"><label>Durasi (Bulan)</label><input type="number" class="form-control" value="3"/></div>
      <button class="btn btn-primary btn-sm"><i class="fas fa-save"></i> Simpan</button>
    </div>
    <div class="card">
      <h3 style="margin-bottom:20px;"><i class="fas fa-bell" style="color:#8b5cf6;margin-right:8px;"></i>Notifikasi</h3>
      <div class="form-group" style="display:flex;align-items:center;justify-content:space-between;">
        <label style="margin:0;">Email Notifikasi</label>
        <input type="checkbox" checked style="width:18px;height:18px;cursor:pointer;accent-color:#2dd4bf;"/>
      </div>
      <div class="form-group" style="display:flex;align-items:center;justify-content:space-between;">
        <label style="margin:0;">Notifikasi Laporan Masuk</label>
        <input type="checkbox" checked style="width:18px;height:18px;cursor:pointer;accent-color:#2dd4bf;"/>
      </div>
      <div class="form-group" style="display:flex;align-items:center;justify-content:space-between;">
        <label style="margin:0;">Backup Otomatis</label>
        <input type="checkbox" checked style="width:18px;height:18px;cursor:pointer;accent-color:#2dd4bf;"/>
      </div>
      <button class="btn btn-primary btn-sm"><i class="fas fa-save"></i> Simpan</button>
    </div>
    <div class="card">
      <h3 style="margin-bottom:20px;"><i class="fas fa-lock" style="color:#ef4444;margin-right:8px;"></i>Keamanan</h3>
      <div class="form-group"><label>Password Baru</label><input type="password" class="form-control" placeholder="••••••••"/></div>
      <div class="form-group"><label>Konfirmasi Password</label><input type="password" class="form-control" placeholder="••••••••"/></div>
      <button class="btn btn-danger btn-sm"><i class="fas fa-key"></i> Ubah Password</button>
    </div>
  </div>
</main>
</div>
<script>
const sidebar=document.getElementById('sidebar'),main=document.getElementById('mainContent'),
      toggleBtn=document.getElementById('sidebarToggle'),mobileBtn=document.getElementById('mobileMenuBtn'),
      overlay=document.getElementById('sidebarOverlay');
toggleBtn.addEventListener('click',()=>{sidebar.classList.toggle('collapsed');main.classList.toggle('expanded');toggleBtn.classList.toggle('collapsed');});
mobileBtn.addEventListener('click',()=>{sidebar.classList.toggle('mobile-open');overlay.classList.toggle('active');});
overlay.addEventListener('click',()=>{sidebar.classList.remove('mobile-open');overlay.classList.remove('active');});
</script>
</body>
</html>
