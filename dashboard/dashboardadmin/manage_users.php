<?php
// Cek admin login
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: /PJBL-main/halamanWeb/loginpage/signin.php');
    exit;
}

require_once '../../koneksi.php';

$action = $_GET['action'] ?? '';
$user_id = $_GET['id'] ?? '';

// Update user level
if ($action === 'update_level' && $user_id) {
    $level = $_POST['level'] ?? 'user';
    $user_id = intval($user_id);

    $stmt = mysqli_prepare($koneksi, "UPDATE users SET level = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'si', $level, $user_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success_message'] = 'Level user berhasil diperbarui.';
    } else {
        $_SESSION['error_message'] = 'Gagal memperbarui level user.';
    }
    mysqli_stmt_close($stmt);
    header('Location: manage_users.php');
    exit;
}

// Delete user
if ($action === 'delete' && $user_id) {
    $user_id = intval($user_id);
    
    $stmt = mysqli_prepare($koneksi, "DELETE FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success_message'] = 'User berhasil dihapus.';
    } else {
        $_SESSION['error_message'] = 'Gagal menghapus user.';
    }
    mysqli_stmt_close($stmt);
    header('Location: manage_users.php');
    exit;
}

// Get all users
$query = "SELECT id, nama, email, level, created_at FROM users ORDER BY created_at DESC";
$result = mysqli_query($koneksi, $query);
$users = [];

while ($row = mysqli_fetch_assoc($result)) {
    $users[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Users - Admin Dashboard</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Inria Sans', 'Inika', sans-serif;
      background: #f5f7fa;
      padding: 20px;
    }

    .container {
      max-width: 1200px;
      margin: 0 auto;
    }

    h1 {
      color: #1e5a7d;
      margin-bottom: 30px;
      font-size: 32px;
    }

    .alert {
      padding: 15px 20px;
      border-radius: 8px;
      margin-bottom: 20px;
      font-weight: 500;
    }

    .alert.success {
      background: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }

    .alert.error {
      background: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
    }

    .table-wrapper {
      background: white;
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
      overflow: hidden;
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    th {
      background: #1e5a7d;
      color: white;
      padding: 15px;
      text-align: left;
      font-weight: 600;
    }

    td {
      padding: 15px;
      border-bottom: 1px solid #e9ecef;
    }

    tr:hover {
      background: #f8f9fa;
    }

    .level-badge {
      display: inline-block;
      padding: 6px 12px;
      border-radius: 20px;
      font-size: 13px;
      font-weight: 600;
    }

    .level-badge.user {
      background: #e7f3ff;
      color: #0066cc;
    }

    .level-badge.admin {
      background: #fff3cd;
      color: #856404;
    }

    .actions {
      display: flex;
      gap: 8px;
    }

    .btn {
      padding: 8px 12px;
      border: none;
      border-radius: 6px;
      font-size: 13px;
      cursor: pointer;
      font-weight: 500;
      transition: all 0.3s ease;
      text-decoration: none;
      display: inline-block;
    }

    .btn-edit {
      background: #0d6efd;
      color: white;
    }

    .btn-edit:hover {
      background: #0b5ed7;
    }

    .btn-delete {
      background: #dc3545;
      color: white;
    }

    .btn-delete:hover {
      background: #c82333;
    }

    .no-data {
      text-align: center;
      padding: 40px;
      color: #6c757d;
    }

    .modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(0, 0, 0, 0.5);
      z-index: 1000;
      align-items: center;
      justify-content: center;
    }

    .modal.show {
      display: flex;
    }

    .modal-content {
      background: white;
      padding: 30px;
      border-radius: 12px;
      max-width: 400px;
      width: 100%;
    }

    .modal-content h2 {
      color: #1e5a7d;
      margin-bottom: 20px;
    }

    .form-group {
      margin-bottom: 15px;
    }

    .form-group label {
      display: block;
      margin-bottom: 8px;
      font-weight: 600;
      color: #333;
    }

    .form-group select {
      width: 100%;
      padding: 10px;
      border: 1px solid #dee2e6;
      border-radius: 6px;
      font-size: 14px;
    }

    .modal-footer {
      display: flex;
      gap: 10px;
      justify-content: flex-end;
      margin-top: 20px;
    }

    .btn-cancel {
      background: #6c757d;
      color: white;
      padding: 10px 16px;
    }

    .btn-cancel:hover {
      background: #5a6268;
    }

    .btn-save {
      background: #28a745;
      color: white;
      padding: 10px 16px;
    }

    .btn-save:hover {
      background: #218838;
    }

    .back-link {
      display: inline-block;
      margin-bottom: 20px;
      color: #0d6efd;
      text-decoration: none;
      font-weight: 500;
    }

    .back-link:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <div class="container">
    <a href="/PJBL-main/dashboard/dashboardadmin/dashboard.php" class="back-link">← Kembali ke Dashboard</a>

    <h1>📋 Kelola User</h1>

    <?php if (isset($_SESSION['success_message'])): ?>
      <div class="alert success"><?= $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
      <div class="alert error"><?= $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
    <?php endif; ?>

    <div class="table-wrapper">
      <?php if (empty($users)): ?>
        <div class="no-data">
          <p>Belum ada user terdaftar.</p>
        </div>
      <?php else: ?>
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Nama</th>
              <th>Email</th>
              <th>Level</th>
              <th>Terdaftar</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($users as $user): ?>
              <tr>
                <td><?= $user['id']; ?></td>
                <td><?= htmlspecialchars($user['nama']); ?></td>
                <td><?= htmlspecialchars($user['email']); ?></td>
                <td>
                  <span class="level-badge <?= $user['level']; ?>">
                    <?= ucfirst($user['level']); ?>
                  </span>
                </td>
                <td><?= date('d M Y H:i', strtotime($user['created_at'])); ?></td>
                <td>
                  <div class="actions">
                    <button class="btn btn-edit" onclick="openEditModal(<?= $user['id']; ?>, '<?= $user['level']; ?>')">
                      Edit Level
                    </button>
                    <button class="btn btn-delete" onclick="if(confirm('Yakin hapus user ini?')) window.location.href='manage_users.php?action=delete&id=<?= $user['id']; ?>'">
                      Hapus
                    </button>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </div>

  <!-- Modal Edit Level -->
  <div class="modal" id="editModal">
    <div class="modal-content">
      <h2>Ubah Level User</h2>
      <form id="editForm" method="POST">
        <div class="form-group">
          <label for="levelSelect">Level</label>
          <select id="levelSelect" name="level" required>
            <option value="user">User</option>
            <option value="admin">Admin</option>
          </select>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-cancel" onclick="closeEditModal()">Batal</button>
          <button type="submit" class="btn btn-save">Simpan</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    let currentUserId = null;

    function openEditModal(userId, currentLevel) {
      currentUserId = userId;
      document.getElementById('levelSelect').value = currentLevel;
      document.getElementById('editModal').classList.add('show');
      document.getElementById('editForm').action = `manage_users.php?action=update_level&id=${userId}`;
    }

    function closeEditModal() {
      document.getElementById('editModal').classList.remove('show');
      currentUserId = null;
    }

    // Close modal jika klik di luar
    document.getElementById('editModal').addEventListener('click', function(e) {
      if (e.target === this) {
        closeEditModal();
      }
    });
  </script>
</body>
</html>
