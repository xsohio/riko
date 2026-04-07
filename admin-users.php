<?php
session_start();
include "config.php";
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') redirect('login.php');

// Hapus user
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    mysqli_query($conn, "DELETE FROM users WHERE id=$id AND role != 'admin'");
    setFlash('success', 'User berhasil dihapus.');
    redirect('admin-users.php');
}

// Tambah / Edit user
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id       = (int)($_POST['id'] ?? 0);
    $username = clean($_POST['username']);
    $role     = clean($_POST['role']);
    $password = $_POST['password'] ?? '';

    if ($id) {
        // Edit
        if ($password) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            mysqli_query($conn, "UPDATE users SET username='$username', role='$role', password='$hash' WHERE id=$id");
        } else {
            mysqli_query($conn, "UPDATE users SET username='$username', role='$role' WHERE id=$id");
        }
        setFlash('success', 'User berhasil diperbarui.');
    } else {
        // Tambah
        if (!$password) { setFlash('error', 'Password wajib diisi untuk user baru.'); redirect('admin-users.php'); }
        $hash = password_hash($password, PASSWORD_DEFAULT);
        mysqli_query($conn, "INSERT INTO users (username, password, role) VALUES ('$username','$hash','$role')");
        setFlash('success', 'User baru berhasil ditambahkan.');
    }
    redirect('admin-users.php');
}

// Search & filter
$search = clean($_GET['search'] ?? '');
$filter = clean($_GET['role'] ?? '');
$where  = "WHERE 1=1";
if ($search) $where .= " AND username LIKE '%$search%'";
if ($filter) $where .= " AND role='$filter'";

$result = mysqli_query($conn, "SELECT * FROM users $where ORDER BY id DESC");
$active_page = 'users';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Manajemen User — Admin PKL</title>
  <link rel="stylesheet" href="style.css"/>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
  <style>
    <?php include_once 'sidebar-style.php'; // referensi style sidebar dari dashboard ?>
    /* Inline sidebar style agar standalone */
    body{display:flex;flex-direction:column;}
    header{position:fixed;top:0;left:0;right:0;z-index:100;background:rgba(15,23,42,.95);
      backdrop-filter:blur(10px);border-bottom:1px solid rgba(45,212,191,.2);height:64px;}
    header nav{display:flex;align-items:center;justify-content:space-between;height:100%;padding:0 24px;}
    .logo{font-weight:700;font-size:1.1rem;color:#2dd4bf;letter-spacing:2px;}
    .nav-links{display:flex;gap:8px;align-items:center;}
    .nav-links a{color:#94a3b8;font-size:.85rem;padding:6px 14px;border-radius:8px;
      transition:all .2s;display:flex;align-items:center;gap:6px;}
    .nav-links a:hover,.nav-links a.active{color:#2dd4bf;background:rgba(45,212,191,.1);}
    .layout-wrapper{display:flex;margin-top:64px;min-height:calc(100vh - 64px);}
    .sidebar{width:260px;min-height:calc(100vh - 64px);background:#0f172a;
      border-right:1px solid rgba(45,212,191,.15);padding:24px 0;position:fixed;
      top:64px;left:0;bottom:0;overflow-y:auto;transition:width .3s,transform .3s;z-index:90;}
    .sidebar.collapsed{width:68px;}
    .sidebar-section{padding:0 16px;margin-bottom:8px;}
    .sidebar-section-title{font-size:.65rem;font-weight:600;letter-spacing:2px;color:#475569;
      text-transform:uppercase;padding:12px 8px 6px;white-space:nowrap;overflow:hidden;}
    .sidebar.collapsed .sidebar-section-title{opacity:0;pointer-events:none;}
    .sidebar-item{display:flex;align-items:center;gap:12px;padding:10px 12px;border-radius:10px;
      text-decoration:none;color:#94a3b8;font-size:.875rem;font-weight:500;transition:all .2s;
      white-space:nowrap;overflow:hidden;position:relative;margin-bottom:2px;}
    .sidebar-item i{width:20px;text-align:center;font-size:1rem;flex-shrink:0;}
    .sidebar.collapsed .sidebar-item span{opacity:0;width:0;overflow:hidden;}
    .sidebar-item:hover{color:#e2e8f0;background:rgba(255,255,255,.06);}
    .sidebar-item.active{color:#2dd4bf;background:rgba(45,212,191,.12);border:1px solid rgba(45,212,191,.2);}
    .sidebar-item.danger:hover{color:#ef4444;background:rgba(239,68,68,.1);}
    .badge{margin-left:auto;background:#ef4444;color:#fff;font-size:.65rem;font-weight:700;
      padding:2px 7px;border-radius:99px;flex-shrink:0;}
    .sidebar.collapsed .badge{opacity:0;}
    .sidebar-toggle{position:fixed;top:74px;left:246px;z-index:110;background:#1e293b;
      border:1px solid rgba(45,212,191,.3);color:#2dd4bf;width:28px;height:28px;border-radius:50%;
      display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:.75rem;
      transition:left .3s,transform .3s;}
    .sidebar-toggle.collapsed{left:54px;transform:rotate(180deg);}
    .sidebar-divider{height:1px;background:rgba(45,212,191,.1);margin:12px 16px;}
    .sidebar-profile{padding:16px;border-top:1px solid rgba(45,212,191,.15);
      display:flex;align-items:center;gap:10px;overflow:hidden;}
    .sidebar-profile .avatar{width:36px;height:36px;border-radius:50%;
      background:linear-gradient(135deg,#2dd4bf,#0ea5e9);display:flex;align-items:center;
      justify-content:center;font-weight:700;font-size:.9rem;color:#0f172a;flex-shrink:0;}
    .sidebar.collapsed .profile-info{opacity:0;width:0;}
    .profile-info .name{font-size:.8rem;font-weight:600;color:#e2e8f0;}
    .profile-info .role-label{font-size:.7rem;color:#2dd4bf;}
    .main-content{margin-left:260px;flex:1;padding:32px;transition:margin-left .3s;}
    .main-content.expanded{margin-left:68px;}
    .mobile-menu-btn{display:none;align-items:center;justify-content:center;background:none;
      border:1px solid rgba(45,212,191,.3);color:#2dd4bf;width:34px;height:34px;
      border-radius:8px;cursor:pointer;font-size:.9rem;}
    .sidebar-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:88;}
    @media(max-width:768px){
      .sidebar{transform:translateX(-100%);width:260px!important;}
      .sidebar.mobile-open{transform:translateX(0);}
      .sidebar-overlay.active{display:block;}
      .sidebar-toggle{display:none;}
      .main-content{margin-left:0!important;padding:20px 16px;}
      .mobile-menu-btn{display:flex!important;}
    }
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

  <div class="breadcrumb">
    <i class="fas fa-home"></i> <i class="fas fa-chevron-right" style="font-size:.6rem;"></i>
    <a href="dashboard admin.php" style="color:#94a3b8;">Dashboard</a>
    <i class="fas fa-chevron-right" style="font-size:.6rem;"></i> <span>Manajemen User</span>
  </div>

  <?php getFlash(); ?>

  <div class="page-header">
    <h2><i class="fas fa-users-cog"></i> Manajemen User</h2>
    <button class="btn btn-primary btn-sm" onclick="openModal()">
      <i class="fas fa-user-plus"></i> Tambah User
    </button>
  </div>

  <!-- Filter & Search -->
  <div style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:20px;align-items:center;">
    <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
      <div class="search-bar">
        <i class="fas fa-search"></i>
        <input type="text" name="search" placeholder="Cari username..." value="<?php echo htmlspecialchars($search); ?>"/>
      </div>
      <select name="role" class="form-control" style="width:auto;padding:9px 14px;">
        <option value="">Semua Role</option>
        <option value="admin"      <?php if($filter==='admin')      echo 'selected'; ?>>Admin</option>
        <option value="siswa"      <?php if($filter==='siswa')      echo 'selected'; ?>>Siswa</option>
        <option value="pembimbing" <?php if($filter==='pembimbing') echo 'selected'; ?>>Pembimbing</option>
      </select>
      <button type="submit" class="btn btn-secondary btn-sm"><i class="fas fa-filter"></i> Filter</button>
      <a href="admin-users.php" class="btn btn-sm" style="background:rgba(255,255,255,.06);color:#94a3b8;">Reset</a>
    </form>
  </div>

  <!-- Tabel -->
  <div class="card">
    <div class="table-wrapper">
      <table>
        <thead>
          <tr>
            <th>#</th><th>Username</th><th>Role</th><th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php $no=1; while($row = mysqli_fetch_assoc($result)): ?>
          <tr>
            <td><?php echo $no++; ?></td>
            <td><i class="fas fa-user" style="color:#2dd4bf;margin-right:8px;"></i><?php echo htmlspecialchars($row['username']); ?></td>
            <td>
              <?php
              $pillMap = ['admin'=>'pill-green','siswa'=>'pill-blue','pembimbing'=>'pill-yellow'];
              $p = $pillMap[$row['role']] ?? 'pill-purple';
              echo "<span class='pill {$p}'>" . ucfirst($row['role']) . "</span>";
              ?>
            </td>
            <td style="display:flex;gap:8px;">
              <button class="btn btn-warning btn-sm"
                onclick="editUser(<?php echo $row['id'].',\''.htmlspecialchars($row['username']).'\',\''.$row['role'].'\''; ?>)">
                <i class="fas fa-edit"></i> Edit
              </button>
              <?php if ($row['role'] !== 'admin'): ?>
              <a href="?hapus=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm"
                 onclick="return confirm('Hapus user ini?')">
                <i class="fas fa-trash"></i>
              </a>
              <?php endif; ?>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>

</main>
</div>

<!-- Modal Tambah/Edit -->
<div class="modal-overlay" id="userModal">
  <div class="modal">
    <div class="modal-header">
      <h3 id="modalTitle"><i class="fas fa-user-plus" style="color:#2dd4bf;margin-right:8px;"></i> Tambah User</h3>
      <button class="modal-close" onclick="closeModal()"><i class="fas fa-times"></i></button>
    </div>
    <form method="POST">
      <input type="hidden" name="id" id="userId" value="0"/>
      <div class="form-group">
        <label>Username</label>
        <input type="text" name="username" id="userUsername" class="form-control" required placeholder="Masukkan username"/>
      </div>
      <div class="form-group">
        <label>Role</label>
        <select name="role" id="userRole" class="form-control">
          <option value="siswa">Siswa</option>
          <option value="pembimbing">Pembimbing</option>
          <option value="admin">Admin</option>
        </select>
      </div>
      <div class="form-group">
        <label>Password <span id="passNote" style="color:#64748b;font-size:.75rem;">(kosongkan jika tidak diubah)</span></label>
        <input type="password" name="password" class="form-control" placeholder="••••••••"/>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" onclick="closeModal()">Batal</button>
        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save"></i> Simpan</button>
      </div>
    </form>
  </div>
</div>

<script>
function openModal(){ document.getElementById('userModal').classList.add('active'); }
function closeModal(){ document.getElementById('userModal').classList.remove('active'); }
function editUser(id,username,role){
  document.getElementById('userId').value=id;
  document.getElementById('userUsername').value=username;
  document.getElementById('userRole').value=role;
  document.getElementById('modalTitle').innerHTML='<i class="fas fa-edit" style="color:#f59e0b;margin-right:8px;"></i> Edit User';
  document.getElementById('passNote').style.display='inline';
  openModal();
}
// Sidebar toggle
const sidebar=document.getElementById('sidebar'),main=document.getElementById('mainContent'),
      toggleBtn=document.getElementById('sidebarToggle'),mobileBtn=document.getElementById('mobileMenuBtn'),
      overlay=document.getElementById('sidebarOverlay');
toggleBtn.addEventListener('click',()=>{sidebar.classList.toggle('collapsed');main.classList.toggle('expanded');toggleBtn.classList.toggle('collapsed');});
mobileBtn.addEventListener('click',()=>{sidebar.classList.toggle('mobile-open');overlay.classList.toggle('active');});
overlay.addEventListener('click',()=>{sidebar.classList.remove('mobile-open');overlay.classList.remove('active');});
</script>
</body>
</html>
