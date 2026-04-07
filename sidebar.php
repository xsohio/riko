<?php
// ============================================================
//  sidebar.php — Komponen Sidebar (Include di setiap halaman)
//  Penggunaan: include 'sidebar.php';
//  Set $active_page sebelum include, contoh: $active_page = 'siswa';
// ============================================================
if (!isset($active_page)) $active_page = '';

function sidebarItem($href, $icon, $label, $page, $activePage, $badge = 0, $danger = false) {
    $isActive = ($page === $activePage) ? 'active' : '';
    $isDanger = $danger ? 'danger' : '';
    echo "<a href='{$href}' class='sidebar-item {$isActive} {$isDanger}' data-tooltip='{$label}'>
            <i class='{$icon}'></i>
            <span>{$label}</span>";
    if ($badge > 0) echo "<span class='badge'>{$badge}</span>";
    echo "</a>";
}
?>

<!-- Toggle Button -->
<button class="sidebar-toggle" id="sidebarToggle" title="Toggle Sidebar">
    <i class="fas fa-chevron-left"></i>
</button>

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">

    <!-- Menu Utama -->
    <div class="sidebar-section">
        <div class="sidebar-section-title">Menu Utama</div>
        <?php sidebarItem('dashboard admin.php', 'fas fa-th-large', 'Dashboard',   'dashboard',   $active_page); ?>
        <?php sidebarItem('admin-users.php',     'fas fa-users-cog','Manajemen User','users',     $active_page); ?>
        <?php sidebarItem('admin-siswa.php',     'fas fa-user-graduate','Data Siswa','siswa',     $active_page); ?>
        <?php sidebarItem('admin-pembimbing.php','fas fa-chalkboard-teacher','Pembimbing','pembimbing',$active_page); ?>
        <?php sidebarItem('admin-perusahaan.php','fas fa-building', 'Perusahaan',  'perusahaan',  $active_page); ?>
    </div>

    <div class="sidebar-divider"></div>

    <!-- PKL -->
    <div class="sidebar-section">
        <div class="sidebar-section-title">Manajemen PKL</div>
        <?php sidebarItem('pkl-penempatan.php', 'fas fa-map-marker-alt','Penempatan PKL','penempatan',$active_page); ?>
        <?php sidebarItem('pkl-absensi.php',    'fas fa-calendar-check','Absensi',  'absensi',     $active_page); ?>
        <?php sidebarItem('pkl-jurnal.php',     'fas fa-book-open',    'Jurnal Harian','jurnal',   $active_page); ?>
        <?php sidebarItem('pkl-nilai.php',      'fas fa-star',         'Penilaian', 'nilai',       $active_page); ?>
        <?php sidebarItem('pkl-laporan.php',    'fas fa-file-alt',     'Laporan PKL','laporan',    $active_page, 3); ?>
    </div>

    <div class="sidebar-divider"></div>

    <!-- Sistem -->
    <div class="sidebar-section">
        <div class="sidebar-section-title">Sistem</div>
        <?php sidebarItem('notifikasi.php',    'fas fa-bell',    'Notifikasi',    'notifikasi',  $active_page, 5); ?>
        <?php sidebarItem('pengaturan.php',    'fas fa-cog',     'Pengaturan',    'pengaturan',  $active_page); ?>
        <?php sidebarItem('backup.php',        'fas fa-database','Backup Data',   'backup',      $active_page); ?>
        <?php sidebarItem('log-aktivitas.php', 'fas fa-history', 'Log Aktivitas', 'log',         $active_page); ?>
    </div>

    <div class="sidebar-divider"></div>

    <!-- Akun -->
    <div class="sidebar-section">
        <?php sidebarItem('profil.php', 'fas fa-user-circle','Profil Saya','profil',$active_page); ?>
        <?php sidebarItem('login.php',  'fas fa-sign-out-alt','Logout',    'logout',$active_page, 0, true); ?>
    </div>

    <!-- Profile Box -->
    <div class="sidebar-profile">
        <div class="avatar"><?php echo strtoupper(substr($_SESSION['user']['username'], 0, 1)); ?></div>
        <div class="profile-info">
            <div class="name"><?php echo htmlspecialchars($_SESSION['user']['username']); ?></div>
            <div class="role-label">Administrator</div>
        </div>
    </div>

</aside>

<!-- Overlay Mobile -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>
