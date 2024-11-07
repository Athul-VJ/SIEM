<?php
// Database credentials
$host = 'localhost';
$db = 'siem';
$user = 'root';
$pass = '';

// Establish database connection
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Handle form submission to add or update a target
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_target'])) {
    $name = $_POST['name'];
    $ip = $_POST['ip'];
    $type = $_POST['type'];
    $logPath = $_POST['log_path'];
    $id = $_POST['id'] ?? null;

    if ($id) {
        // Update existing target
        $stmt = $pdo->prepare("UPDATE monitored_targets SET target_name = ?, ip_address = ?, type = ?, log_path = ? WHERE id = ?");
        $stmt->execute([$name, $ip, $type, $logPath, $id]);
    } else {
        // Add new target
        $stmt = $pdo->prepare("INSERT INTO monitored_targets (target_name, ip_address, type, log_path) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $ip, $type, $logPath]);
    }

    header("Location: {$_SERVER['PHP_SELF']}");
    exit;
}

// Handle delete action
if (isset($_GET['delete_id'])) {
    $stmt = $pdo->prepare("DELETE FROM monitored_targets WHERE id = ?");
    $stmt->execute([$_GET['delete_id']]);
    header("Location: {$_SERVER['PHP_SELF']}");
    exit;
}

// Fetch all monitored targets
$targets = $pdo->query("SELECT * FROM monitored_targets")->fetchAll(PDO::FETCH_ASSOC);

// Fetch target for updating
$updateTarget = null;
if (isset($_GET['update_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM monitored_targets WHERE id = ?");
    $stmt->execute([$_GET['update_id']]);
    $updateTarget = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SIEM Dashboard</title>
</head>
<body>

<h2><?php echo $updateTarget ? "Update Target" : "Add Networking Device or Website"; ?></h2>
<form method="POST" action="">
    <input type="hidden" name="id" value="<?php echo $updateTarget['id'] ?? ''; ?>">

    <label for="name">Target Name:</label>
    <input type="text" name="name" value="<?php echo htmlspecialchars($updateTarget['target_name'] ?? ''); ?>" required><br><br>

    <label for="ip">IP Address:</label>
    <input type="text" name="ip" value="<?php echo htmlspecialchars($updateTarget['ip_address'] ?? ''); ?>" required><br><br>

    <label for="type">Type:</label>
    <select name="type" required>
        <option value="device" <?php echo (isset($updateTarget) && $updateTarget['type'] == 'device') ? 'selected' : ''; ?>>Device</option>
        <option value="website" <?php echo (isset($updateTarget) && $updateTarget['type'] == 'website') ? 'selected' : ''; ?>>Website</option>
    </select><br><br>

    <label for="log_path">Log File Path:</label>
    <input type="text" name="log_path" value="<?php echo htmlspecialchars($updateTarget['log_path'] ?? ''); ?>" required><br><br>

    <button type="submit" name="save_target"><?php echo $updateTarget ? "Update Target" : "Add Target"; ?></button>
</form>

<h2>Monitored Targets</h2>
<table border="1">
    <tr>
        <th>ID</th>
        <th>Target Name</th>
        <th>IP Address</th>
        <th>Type</th>
        <th>Log File Path</th>
        <th>Actions</th>
    </tr>
    <?php foreach ($targets as $target): ?>
    <tr>
        <td><?php echo htmlspecialchars($target['id']); ?></td>
        <td><?php echo htmlspecialchars($target['target_name']); ?></td>
        <td><?php echo htmlspecialchars($target['ip_address']); ?></td>
        <td><?php echo htmlspecialchars($target['type']); ?></td>
        <td><?php echo htmlspecialchars($target['log_path']); ?></td>
        <td>
            <a href="?update_id=<?php echo $target['id']; ?>">Update</a> |
            <a href="?delete_id=<?php echo $target['id']; ?>" onclick="return confirm('Are you sure you want to delete this target?');">Delete</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

</body>
</html>
