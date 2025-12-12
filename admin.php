<?php
include_once 'includes/header.php';

// Giriş yapmış mı ve rolü admin mi diye kontrol et
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    echo "<div class='message error'>Bu sayfaya erişim yetkiniz yok.</div>";
    include_once 'includes/footer.php';
    exit();
}

// Tüm kullanıcıları ve bitkileri çek
$all_users = supabase_api_request('GET', 'users');
$all_plants = supabase_api_request('GET', 'plants');
?>

<h2>Admin Paneli</h2>
<p>Sistemdeki tüm verileri buradan yönetebilirsiniz.</p>

<h3>Kullanıcılar (<?php echo count($all_users); ?>)</h3>
<table border="1" style="width:100%; border-collapse: collapse;">
    <thead>
        <tr>
            <th>ID</th>
            <th>Kullanıcı Adı</th>
            <th>Email</th>
            <th>Rol</th>
            <th>Kayıt Tarihi</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($all_users as $user): ?>
        <tr>
            <td><?php echo $user['id']; ?></td>
            <td><?php echo htmlspecialchars($user['username']); ?></td>
            <td><?php echo htmlspecialchars($user['email']); ?></td>
            <td><?php echo htmlspecialchars($user['role']); ?></td>
            <td><?php echo date('d M Y', strtotime($user['created_at'])); ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<h3 style="margin-top: 30px;">Tüm Bitkiler (<?php echo count($all_plants); ?>)</h3>
<table border="1" style="width:100%; border-collapse: collapse;">
    <thead>
        <tr>
            <th>Bitki ID</th>
            <th>Bitki Adı</th>
            <th>Sahip ID</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($all_plants as $plant): ?>
        <tr>
            <td><?php echo $plant['id']; ?></td>
            <td><?php echo htmlspecialchars($plant['plant_name']); ?></td>
            <td><?php echo $plant['user_id']; ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include_once 'includes/footer.php'; ?>