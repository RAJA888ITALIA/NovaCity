<?php
session_start();
include 'includes/db.php';

/* ---------- AUTH ---------- */
if (!isset($_SESSION['user_id'])) 
{
    header('Location: login.php');
    exit();
}

// Check if admin
$stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($user['role'] !== 'admin')
{
    http_response_code(403);
    exit('Access denied');
}

/* ---------- PAGE SETUP ---------- */
$PageTitle = "Aggiorna Report";
include 'includes/header.php';

$message = "";
$message_type = "";

/* ---------- VALIDATE REPORT ---------- */
if (!isset($_GET['report_id'])) 
{
    echo "<p class='error-box'>Report non trovato.</p>";
    echo '<p><a href="all_reports.php" class="btn secondary">Torna a Tutti i Report</a></p>';
    include 'includes/footer.php';
    exit();
}

$report_id = (int)$_GET['report_id'];

/* ---------- HANDLE POST ---------- */
if ($_SERVER["REQUEST_METHOD"] === "POST") 
{
    // --- DELETE REPORT ---
    if (isset($_POST['delete'])) 
    {
        $stmt_delete = $conn->prepare("DELETE FROM reports WHERE report_id = ?");
        $stmt_delete->bind_param("i", $report_id);
        $stmt_delete->execute();

        if ($stmt_delete->affected_rows > 0) 
        {
            echo "<p class='success-box'>Report eliminato con successo!</p>";
            echo '<p><a href="all_reports.php" class="btn secondary">Torna a Tutti i Report</a></p>';
            $stmt_delete->close();
            include 'includes/footer.php';
            exit();
        }
        else 
        {
            $message = "Errore: report non eliminato.";
            $message_type = "error";
        }
        $stmt_delete->close();
    }

    // --- UPDATE STATUS + FEEDBACK ---
    elseif (isset($_POST['update'])) 
    {
        $new_status = $_POST['status'];
        $feedback   = trim($_POST['admin_feedback']);

        $stmt_update = $conn->prepare("UPDATE reports SET status = ?, last_update = NOW(), admin_feedback=? WHERE report_id = ?");
        $stmt_update->bind_param("ssi", $new_status, $feedback, $report_id);
        
        if ($stmt_update->execute()) 
        {
            $message = "Status e feedback aggiornati con successo!";
            $message_type = "success";
        } 
        else 
        {
            $message = "Errore nell'aggiornamento dello status.";
            $message_type = "error";
        }

        $stmt_update->close();
    }
}

/* ---------- FETCH REPORT ---------- */
$stmt = $conn->prepare("SELECT * FROM reports WHERE report_id = ?");
$stmt->bind_param("i", $report_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) 
{
    echo "<p class='error-box'>Report non trovato.</p>";
    echo '<p><a href="all_reports.php" class="btn secondary">Torna a Tutti i Report</a></p>';
    include 'includes/footer.php';
    exit();
}

$row = $result->fetch_assoc();
$stmt->close();

/* ---------- HELPER FOR STATUS BADGES ---------- */
function status_badge($status)
{
    $colors = [
        'pending'   => '#969ea5',
        'reviewing' => '#ffaf32',
        'approved'  => '#3cc864',
        'rejected'  => '#f0505a'
    ];
    $color = $colors[$status] ?? '#000000';
    return "<span style='background-color:$color;padding:3px 8px;border-radius:5px;color:#fff;font-weight:bold;'>".strtoupper($status)."</span>";
}
?>

<section class="report-update" style="display:flex;justify-content:center;align-items:center;min-height:100vh;">
	<div class="auth-card" style="max-width:900px;margin:auto;">
        <h1>📌 Aggiorna Report</h1>

        <?php if (!empty($message)): ?>
            <div class="message <?= $message_type ?>"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <div class="report-details">
            <h3><?= htmlspecialchars($row['title']) ?></h3>
            <p><strong>User ID:</strong> <?= htmlspecialchars($row['user_id']) ?></p>
            <p><strong>Report ID:</strong> <?= htmlspecialchars($row['report_id']) ?></p>
            <p><strong>Categoria:</strong> <?= htmlspecialchars($row['category']) ?></p>
            <p><strong>Descrizione:</strong><br><?= nl2br(htmlspecialchars($row['description'])) ?></p>
            <p><strong>Luogo:</strong> <?= htmlspecialchars($row['location']) ?></p>
            <p><strong>Stato:</strong> <?= status_badge($row['status']) ?></p>
            <p><strong>Inviato il:</strong> <?= htmlspecialchars($row['created_at']) ?></p>
            <p><strong>Ultimo aggiornamento:</strong> <?= htmlspecialchars($row['last_update'] ?? '-') ?></p>

            <?php if (!empty($row['image'])): ?>
                <div style="margin-top:10px;">
                    <img src="uploads/<?= htmlspecialchars($row['image']) ?>" alt="Report Image" style="max-width:100%;border:1px solid #ccc;border-radius:5px;">
                </div>
            <?php endif; ?>
        </div>

        <!-- Update / Delete Form -->
        <form method="post" class="status-form" style="margin-top:20px;">
            <div class="form-field">
                <label for="status">Aggiorna Stato:</label>
                <select name="status" id="status" required>
                    <option value="pending"   <?= $row['status'] === "pending" ? "selected" : "" ?>>IN ATTESA</option>
                    <option value="reviewing" <?= $row['status'] === "reviewing" ? "selected" : "" ?>>IN REVISIONE</option>
                    <option value="approved"  <?= $row['status'] === "approved" ? "selected" : "" ?>>APPROVATO</option>
                    <option value="rejected"  <?= $row['status'] === "rejected" ? "selected" : "" ?>>RIFIUTATO</option>
                </select>
            </div>

            <div class="form-field">
                <label for="admin_feedback">Motivo / Feedback per l'utente:</label>
                <textarea name="admin_feedback" id="admin_feedback" rows="3"><?= htmlspecialchars($row['admin_feedback'] ?? '') ?></textarea>
            </div>

            <button name="update" class="btn primary">Aggiorna Stato</button>
            <button name="delete" class="btn danger" onclick="return confirm('Sei sicuro di voler eliminare questo report?');">Elimina Report</button>
        </form>

        <div class="auth-footer" style="margin-top:15px;">
            <a href="all_reports.php">← Torna a Tutti i Report</a>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>