<?php
session_start();
include 'includes/db.php';

// Check if user is admin
if (!isset($_SESSION['user_id'])) 
{
	header('Location: login.php');
	exit();
}

// Fetch role
$stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if ($user['role'] !== 'admin')
{
	header('Location: login.php');
	exit();
}

$PageTitle = "Tutti i Report (Admin)";
$PageHead  = "";
include 'includes/header.php';

// Filters
$status_filter   = $_GET['stato'] ?? '';
$category_filter = $_GET['categoria'] ?? '';
?>

<section class="auth">
	<div class="auth-card" style="max-width:1400px;">
		<h1>Tutti i Report (Admin)</h1>

		<!-- FILTRI -->
		<form method="GET" class="filter-form">
			<label>Stato:</label>
			<select name="stato">
				<option value="">-- Tutti --</option>
				<option value="pending" <?= $status_filter == 'pending' ? 'selected' : '' ?>>In attesa</option>
				<option value="reviewing" <?= $status_filter == 'reviewing' ? 'selected' : '' ?>>Accettati</option>
				<option value="approved" <?= $status_filter == 'approved' ? 'selected' : '' ?>>Approvati</option>
				<option value="rejected" <?= $status_filter == 'rejected' ? 'selected' : '' ?>>Rifiutati</option>
			</select>

			<label>Categoria:</label>
			<select name="categoria">
				<option value="">-- Tutti --</option>
				<option value="Strada" <?= $category_filter == 'Strada' ? 'selected' : '' ?>>Strada</option>
				<option value="Illuminazione" <?= $category_filter == 'Illuminazione' ? 'selected' : '' ?>>Illuminazione</option>
				<option value="Rifiuti" <?= $category_filter == 'Rifiuti' ? 'selected' : '' ?>>Rifiuti</option>
				<option value="Altro" <?= $category_filter == 'Altro' ? 'selected' : '' ?>>Altro</option>
			</select>

			<button type="submit" class="btn primary">Filtra</button>
			<a href="view_report.php" class="btn secondary">Reset</a>
		</form>

		<?php
		try {
			// Admin query: all reports
			$query = "SELECT report_id, user_id, title, category, status FROM reports WHERE 1=1";
			$params = [];
			$types  = "";

			if (!empty($status_filter)) 
            {
				$query .= " AND status = ?";
				$params[] = $status_filter;
				$types .= "s";
			}

			if (!empty($category_filter)) 
            {
				$query .= " AND category = ?";
				$params[] = $category_filter;
				$types .= "s";
			}

			$stmt = $conn->prepare($query);

			if (!empty($params)) 
            {
				$stmt->bind_param($types, ...$params);
			}

			$stmt->execute();
			$result = $stmt->get_result();

			if ($result->num_rows > 0) 
            {
				echo '<table class="report-table">';
				echo '<thead>
						<tr>
							<th>ID</th>
							<th>User ID</th>
							<th>Titolo</th>
							<th>Categoria</th>
							<th>Stato</th>
							<th>Azioni</th>
						</tr>
					  </thead>
					  <tbody>';
				while ($row = $result->fetch_assoc()) 
                {
					echo '<tr>';
					echo '<td>' . htmlspecialchars($row['report_id']) . '</td>';
					echo '<td>' . htmlspecialchars($row['user_id']) . '</td>';
					echo '<td>' . htmlspecialchars($row['title']) . '</td>';
					echo '<td>' . htmlspecialchars($row['category']) . '</td>';
					echo '<td>' . htmlspecialchars($row['status']) . '</td>';
					echo '<td><a href="update_status.php?report_id=' . htmlspecialchars($row['report_id']) . '" class="btn primary full">Aggiorna</a></td>';
					echo '</tr>';
				}
				echo '</tbody></table>';
			} 
            else 
            {
				echo '<p>Nessun report trovato.</p>';
			}

			$stmt->close();
		} 
        catch (\Exception $e) 
        {
			echo '<p class="error-box">Errore nel caricamento dei report.</p>';
		}
		?>

		<div class="auth-footer">
			<a href="admin_dashboard.php">← Torna al Dashboard Admin</a>
		</div>
	</div>
</section>

<?php include 'includes/footer.php'; ?>
