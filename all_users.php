<?php
session_start();
include 'includes/db.php';

// Check login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Check admin role
$stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if ($user['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$PageTitle = "Tutti gli Utenti (Admin)";
include 'includes/header.php';

// Filters
$verified_filter = $_GET['verified'] ?? '';
$search          = $_GET['search'] ?? '';
$order           = $_GET['order'] ?? 'az';
?>

<section class="auth">
<div class="auth-card" style="max-width:1400px;">
<h1>Tutti gli Utenti (Admin)</h1>

<!-- FILTRI -->
<form method="GET" class="filter-form" style="
    display:flex;
    align-items:flex-end;
    gap:12px;
    flex-wrap:wrap;
    margin-bottom:20px;
">

    <div>
        <label>Verifica Email:</label>
        <select name="verified">
            <option value="">-- Tutti --</option>
            <option value="1" <?= $verified_filter === '1' ? 'selected' : '' ?>>Verificati</option>
            <option value="0" <?= $verified_filter === '0' ? 'selected' : '' ?>>Non verificati</option>
        </select>
    </div>

    <div>
        <label>Ordine:</label>
        <select name="order">
            <option value="az" <?= $order === 'az' ? 'selected' : '' ?>>A → Z</option>
            <option value="za" <?= $order === 'za' ? 'selected' : '' ?>>Z → A</option>
        </select>
    </div>

    <div style="flex:1; min-width:260px;">
        <label>Cerca:</label>
        <input 
            type="text" 
            name="search" 
            placeholder="Email, nome o cognome"
            value="<?= htmlspecialchars($search) ?>"
            style="
                width:100%;
                padding:10px 12px;
                border-radius:8px;
                border:1px solid #ccc;
                font-size:14px;
            "
        >
    </div>

    <button class="btn primary">Filtra</button>
    <a href="all_users.php" class="btn secondary">Reset</a>
</form>

<?php
// Base query
$query = "
SELECT 
    u.id,
    u.username,
    u.name,
    u.surname,
    u.email_verified,
    COUNT(r.report_id) AS reports_made
FROM users u
LEFT JOIN reports r ON r.user_id = u.id
WHERE 1=1
";

$params = [];
$types  = "";

// Filter: verified
if ($verified_filter !== '') {
    $query .= " AND u.email_verified = ?";
    $params[] = $verified_filter;
    $types .= "i";
}

// Search
if (!empty($search)) {
    $query .= " AND (
        u.username LIKE ?
        OR u.name LIKE ?
        OR u.surname LIKE ?
    )";
    $like = "%$search%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $types .= "sss";
}

// Group + Order
$query .= " GROUP BY u.id ";
$query .= ($order === 'za') ? " ORDER BY u.username DESC" : " ORDER BY u.username ASC";

$stmt = $conn->prepare($query);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo '<table class="report-table">';
    echo '
    <thead>
        <tr>
            <th>ID</th>
            <th>Username (Email)</th>
            <th>Nome</th>
            <th>Cognome</th>
            <th>Email Verificata</th>
            <th>Report Creati</th>
        </tr>
    </thead>
    <tbody>';

    while ($row = $result->fetch_assoc()) {

        $verifiedBadge = $row['email_verified']
            ? '<span style="color:green;font-weight:700;">✔ Sì</span>'
            : '<span style="color:red;font-weight:700;">✖ No</span>';

        echo '<tr>';
        echo '<td>' . htmlspecialchars($row['id']) . '</td>';
        echo '<td>' . htmlspecialchars($row['username']) . '</td>';
        echo '<td>' . htmlspecialchars($row['name']) . '</td>';
        echo '<td>' . htmlspecialchars($row['surname']) . '</td>';
        echo '<td>' . $verifiedBadge . '</td>';
        echo '<td>' . htmlspecialchars($row['reports_made']) . '</td>';
        echo '</tr>';
    }

    echo '</tbody></table>';
} else {
    echo '<p>Nessun utente trovato.</p>';
}

$stmt->close();
?>

<div class="auth-footer">
    <a href="dashboard.php">← Torna al Dashboard</a>
</div>

</div>
</section>

<?php include 'includes/footer.php'; ?>
