<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) 
{
    header('Location: login.php');
    exit();
}

$PageTitle = "My Reports";
include 'includes/header.php';
$status_filter   = $_GET['stato'] ?? '';
$category_filter = $_GET['categoria'] ?? '';

// Helper function for status badges
function status_badge($status) 
{
    $colors = [
        'pending'   => 'rgba(150, 158, 165, 0.65)', // brighter gray
        'reviewing' => 'rgba(255, 175, 50, 0.65)',  // bright orange
        'approved'  => 'rgba(60, 200, 100, 0.65)',  // brighter green
        'rejected'  => 'rgba(240, 80, 90, 0.65)',   // brighter red
    ];
    $color = $colors[$status] ?? '#000000';
    $text = strtoupper($status);
    return "<span class='status-badge' style='background-color: $color;'>$text</span>";
}
?>

<section class="auth">
    <div class="auth-card" style="max-width:1200px;">
        <h1>📋 I Miei Report</h1>

        <!-- Filters -->
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

            <button class="btn primary">Filtra</button>
            <a href="my_reports.php" class="btn secondary">Reset</a>
        </form>

        <?php
        $filter_query = "
            SELECT report_id, title, category, status, created_at, last_update, location, description, admin_feedback
            FROM reports
            WHERE user_id = ?
        ";

        $params = [$_SESSION['user_id']];
        $types  = "i";

        if ($status_filter) 
        {
            $filter_query .= " AND status = ?";
            $params[] = $status_filter;
            $types .= "s";
        }

        if ($category_filter) 
        {
            $filter_query .= " AND category = ?";
            $params[] = $category_filter;
            $types .= "s";
        }

        $stmt = $conn->prepare($filter_query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo '<table class="report-table">';
            echo '<thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Data Invio</th>
                        <th>Ultimo Aggiornamento</th>
                        <th>Dettagli</th>
                    </tr>
                  </thead><tbody>';

            while ($row = $result->fetch_assoc()) 
            {
                $rid = (int)$row['report_id'];

                echo '<tr>';
                echo '<td>' . $rid . '</td>';
                echo '<td>' . htmlspecialchars($row['title']) . '</td>';
                echo '<td>' . htmlspecialchars($row['category']) . '</td>';
                echo '<td>' . status_badge($row['status']) . '</td>';
                echo '<td>' . htmlspecialchars($row['created_at']) . '</td>';
                echo '<td>' . htmlspecialchars($row['last_update'] ?? '-') . '</td>';
                echo '<td>
                        <button class="details-toggle" onclick="toggleDetails(' . $rid . ', this)">
                            Dettagli <span class="arrow">▼</span>
                        </button>
                      </td>';
                echo '</tr>';

                echo '<tr class="details-row" id="details-' . $rid . '">
                        <td colspan="7">
                            <div class="details-content">
                                <p><strong>Posizione:</strong> ' . htmlspecialchars($row['location']) . '</p>
                                <p><strong>Descrizione:</strong><br>' . nl2br(htmlspecialchars($row['description'])) . '</p>';

                // Show admin feedback if exists
                if (!empty($row['admin_feedback'])) {
                    echo '<p><strong>Motivazione:</strong> ' . nl2br(htmlspecialchars($row['admin_feedback'])) . '</p>';
                }

                echo '    </div>
                        </td>
                      </tr>';
            }

            echo '</tbody></table>';
        } 
        else 
        {
            echo '<p>Nessun report trovato.</p>';
        }

        $stmt->close();
        ?>

        <div class="auth-footer">
            <a href="dashboard.php">← Torna al Dashboard</a>
        </div>
    </div>
</section>

<script>
    function toggleDetails(id, btn) {
        const row = document.getElementById('details-' + id);
        const arrow = btn.querySelector('.arrow');

        if (row.classList.contains('open')) {
            row.classList.remove('open');
            arrow.textContent = '▼';
        } else {
            row.classList.add('open');
            arrow.textContent = '▲';
        }
    }
</script>

<?php include 'includes/footer.php'; ?>
