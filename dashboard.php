<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) 
{
    header('Location: login.php');
    exit();
}

$PageTitle = "Dashboard";
include 'includes/header.php';

// Fetch user data
$stmt = $conn->prepare("SELECT surname, name, role FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$statuses = [
    'pending'   => 'In Attesa',
    'reviewing' => 'In Revisione',
    'approved'  => 'Approvati',
    'rejected'  => 'Rifiutati'
];
?>

<section class="dashboard">
    <div class="dashboard-header">
        <h1>Ciao, <?= htmlspecialchars($user['surname']) ?> Benvenuto 👋</h1>
        <span class="role-badge <?= $user['role'] ?>">
            <?= strtoupper($user['role']) ?>
        </span>
    </div>

    <div class="cards">
        <?php
        foreach ($statuses as $key => $label) 
        {
            if ($user['role'] === 'admin') 
            {
                $stmt = $conn->prepare("SELECT COUNT(*) FROM reports WHERE status = ?");
                $stmt->bind_param("s", $key);
            } 
            else 
            {
                $stmt = $conn->prepare("SELECT COUNT(*) FROM reports WHERE status = ? AND user_id = ?");
                $stmt->bind_param("si", $key, $_SESSION['user_id']);
            }
            $stmt->execute();
            $count = $stmt->get_result()->fetch_row()[0];
            ?>
            <div class="card">
                <h3><?= $count ?></h3>
                <p><?= $label ?></p>
            </div>
        <?php } ?>
    </div>

    <div class="dashboard-actions">
        <?php if ($user['role'] === 'admin'): ?>
            <a href="all_reports.php" class="btn primary">📋 Tutti i Report</a>
        	<a href="all_users.php" class="btn secondary">👤 Mostra tutti Utenti</a>
        <?php else: ?>
            <a href="my_reports.php" class="btn primary">📋 Miei Report</a>
            <a href="make_report.php" class="btn secondary">➕ Crea Report</a>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
