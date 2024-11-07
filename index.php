<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIEM Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container-fluid">
        <header class="d-flex justify-content-between align-items-center py-3">
            <h1>SIEM Dashboard</h1>
            <nav>
                <a href="addResources.php"><button type="button" class="btn btn-success">Add Resources</button></a>
            </nav>
        </header>

        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                    <?php
                        // Database connection
                        $host = 'localhost';
                        $db = 'siem';
                        $user = 'root';
                        $pass = '';

                        try {
                            $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
                            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        } catch (PDOException $e) {
                            die("Database connection failed: " . $e->getMessage());
                        }

                        // Fetch all targets for dropdown
                        $targets = $pdo->query("SELECT id, target_name, log_path FROM monitored_targets")->fetchAll(PDO::FETCH_ASSOC);

                        // Handle target selection to show logs
                        $selectedTarget = null;
                        $logs = [];
                        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['target_id'])) {
                            $targetId = $_POST['target_id'];
                            $stmt = $pdo->prepare("SELECT id, target_name, log_path FROM monitored_targets WHERE id = ?");
                            $stmt->execute([$targetId]);
                            $selectedTarget = $stmt->fetch(PDO::FETCH_ASSOC);

                            // Read log file if exists
                            if ($selectedTarget && file_exists($selectedTarget['log_path'])) {
                                $logs = file($selectedTarget['log_path'], FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                            } else {
                                echo "<p>Log file not found for this target.</p>";
                            }
                        }
                    ?>

                    <h2>Select Target to View Activity Logs</h2>
                    <form method="POST" action="">
                        <label for="target_id">Target:</label>
                        <select name="target_id" class="form-control w-25 d-inline" required onchange="checkForDosAttack()">
                            <option value="">Select a target</option>
                            <?php foreach ($targets as $target): ?>
                                <option value="<?php echo htmlspecialchars($target['id']); ?>" <?php echo ($selectedTarget && $selectedTarget['id'] == $target['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($target['target_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-primary ml-2">View Logs</button>
                    </form>

                    <?php if (!empty($logs) && isset($selectedTarget['target_name'])): ?>
                        <h3 class="mt-4">Activity Logs for <?php echo htmlspecialchars($selectedTarget['target_name']); ?></h3>
                        <table class="table table-bordered mt-3">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Timestamp</th>
                                    <th>Log Entry</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($logs as $logEntry): ?>
                                    <tr>
                                        <?php
                                        $logParts = explode(" ", $logEntry, 2);
                                        ?>
                                        <td><?php echo htmlspecialchars($logParts[0]); ?></td>
                                        <td><?php echo htmlspecialchars($logParts[1] ?? ''); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php elseif ($selectedTarget): ?>
                        <p>No log entries found for the selected target.</p>
                    <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Dashboard Components -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Total Alerts</h5>
                        <div class="single-value" id="total-alerts">45</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Active Incidents</h5>
                        <div class="single-value" id="active-incidents">7</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">System Status</h5>
                        <div class="single-value" id="system-status">Operational</div>
                    </div>
                </div>
            </div>
        </div>

        <footer class="text-center py-3">
            <p>&copy; 2024 SIEM Dashboard</p>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        function checkForDosAttack() {
            const targetId = document.querySelector('select[name="target_id"]').value;

            if (!targetId) return;

            fetch(`monitor.php?target_id=${targetId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.alert) {
                        alert("Potential DoS/DDoS attack detected from IP(s): " + data.suspiciousIps.join(", "));
                    }
                })
                .catch(error => console.error("Error checking for DoS/DDoS attack:", error));
        }

        // Check every 5 seconds
        setInterval(checkForDosAttack, 5000);
    </script>
</body>
</html>
