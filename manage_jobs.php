<?php
require_once 'includes/db_config.php';

// Check if user is admin
if (!isset($_SESSION)) {
    session_start();
}
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    die('Access denied. Only administrators can access this page.');
}

// Handle job deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM jobs WHERE id = ?");
        $stmt->execute([$_GET['delete']]);
        $message = "Job deleted successfully!";
    } catch (PDOException $e) {
        $error = "Error deleting job: " . $e->getMessage();
    }
}

// Fetch all jobs
try {
    $jobs = $pdo->query("SELECT * FROM jobs ORDER BY posted_at DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching jobs: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Jobs - Admin</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { padding: 20px; font-family: Arial, sans-serif; }
        h1 { color: #333; }
        .job-list { margin-top: 20px; }
        .job-item { 
            background: #f9f9f9; 
            border: 1px solid #ddd; 
            padding: 15px; 
            margin-bottom: 10px; 
            border-radius: 5px;
        }
        .job-item h3 { margin-top: 0; }
        .job-meta { color: #666; font-size: 0.9em; margin: 5px 0; }
        .actions { margin-top: 10px; }
        .btn { 
            padding: 5px 10px; 
            margin-right: 5px; 
            text-decoration: none; 
            border-radius: 3px; 
            font-size: 0.9em;
        }
        .btn-delete { background: #dc3545; color: white; }
        .btn-edit { background: #28a745; color: white; }
        .message { 
            padding: 10px; 
            margin: 10px 0; 
            border-radius: 4px; 
        }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
    <h1>Manage Job Postings</h1>
    
    <?php if (isset($message)): ?>
        <div class="message success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
        <div class="message error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <div class="job-list">
        <?php if (empty($jobs)): ?>
            <p>No jobs found in the database.</p>
        <?php else: ?>
            <?php foreach ($jobs as $job): ?>
                <div class="job-item">
                    <h3><?php echo htmlspecialchars($job['title']); ?></h3>
                    <div class="job-meta">
                        <strong>Company:</strong> <?php echo htmlspecialchars($job['company']); ?> |
                        <strong>Location:</strong> <?php echo htmlspecialchars($job['location'] ?? 'N/A'); ?> |
                        <strong>Posted:</strong> <?php echo date('M j, Y', strtotime($job['posted_at'])); ?>
                    </div>
                    <div class="actions">
                        <a href="#" class="btn btn-edit" onclick="return confirmEdit(<?php echo $job['id']; ?>)">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="?delete=<?php echo $job['id']; ?>" class="btn btn-delete" 
                           onclick="return confirm('Are you sure you want to delete this job?')">
                            <i class="fas fa-trash"></i> Delete
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <script>
        function confirmEdit(id) {
            if (confirm('This will take you to the edit page. Continue?')) {
                window.location.href = 'edit_job.php?id=' + id;
            }
            return false;
        }
    </script>
</body>
</html>
