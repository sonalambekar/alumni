<?php
require_once '../includes/db_config.php';
requireLogin();

// Get all alumni users from the database
try {
    $stmt = $pdo->query("SELECT id, name, email_id, profile_picture, batch_year, current_position, company, location, bio FROM users ORDER BY name ASC");
    $alumni = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching alumni data: " . $e->getMessage();
    error_log($error);
    $alumni = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alumni Directory - Alumni Connect</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .alumni-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .alumni-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .alumni-card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .alumni-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        .alumni-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .alumni-details {
            padding: 20px;
        }
        .alumni-name {
            font-size: 1.2rem;
            font-weight: 600;
            margin: 0 0 5px 0;
            color: #333;
        }
        .alumni-position, .alumni-company, .alumni-location, .alumni-batch {
            color: #666;
            margin: 5px 0;
            font-size: 0.9rem;
        }
        .alumni-email {
            color: #4a6baf;
            text-decoration: none;
            font-size: 0.9rem;
            display: block;
            margin: 10px 0;
            word-break: break-all;
        }
        .alumni-email:hover {
            text-decoration: underline;
        }
        .alumni-bio {
            margin-top: 10px;
            color: #555;
            font-size: 0.9rem;
            line-height: 1.5;
        }
        .page-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .page-header h1 {
            color: #333;
            margin-bottom: 10px;
        }
        .no-alumni {
            text-align: center;
            padding: 40px 20px;
            color: #666;
            grid-column: 1 / -1;
        }
        @media (max-width: 768px) {
            .alumni-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }
        }
    </style>
</head>
<body>
    <?php include '../sidebar.php'; ?>

    <div class="main-content" id="mainContent">
        <div class="alumni-container">
            <div class="page-header">
                <h1>Alumni Directory</h1>
                <p>Connect with your fellow alumni from around the world</p>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if (empty($alumni)): ?>
                <div class="no-alumni">
                    <i class="fas fa-users fa-3x" style="color: #ddd; margin-bottom: 15px;"></i>
                    <p>No alumni found in the database.</p>
                </div>
            <?php else: ?>
                <div class="alumni-grid">
                    <?php foreach ($alumni as $alumnus): ?>
                        <div class="alumni-card">
                            <img src="<?php 
                                if (!empty($alumnus['profile_picture'])) {
                                    echo htmlspecialchars($alumnus['profile_picture']);
                                } else {
                                    echo 'https://ui-avatars.com/api/?name=' . urlencode($alumnus['name']) . '&size=200&background=5b1f1f&color=fff';
                                }
                            ?>" alt="<?php echo htmlspecialchars($alumnus['name']); ?>" class="alumni-image">
                            <div class="alumni-details">
                                <h3 class="alumni-name"><?php echo htmlspecialchars($alumnus['name']); ?></h3>
                                
                                <?php if (!empty($alumnus['current_position'])): ?>
                                    <p class="alumni-position"><?php echo htmlspecialchars($alumnus['current_position']); ?></p>
                                <?php endif; ?>
                                
                                <?php if (!empty($alumnus['company'])): ?>
                                    <p class="alumni-company"><?php echo htmlspecialchars($alumnus['company']); ?></p>
                                <?php endif; ?>
                                
                                <?php if (!empty($alumnus['location'])): ?>
                                    <p class="alumni-location">
                                        <i class="fas fa-map-marker-alt"></i> 
                                        <?php echo htmlspecialchars($alumnus['location']); ?>
                                    </p>
                                <?php endif; ?>
                                
                                <?php if (!empty($alumnus['batch_year'])): ?>
                                    <p class="alumni-batch">
                                        <i class="fas fa-graduation-cap"></i> 
                                        Batch of <?php echo htmlspecialchars($alumnus['batch_year']); ?>
                                    </p>
                                <?php endif; ?>
                                
                                <?php if (!empty($alumnus['email_id'])): ?>
                                    <a href="mailto:<?php echo htmlspecialchars($alumnus['email_id']); ?>" class="alumni-email">
                                        <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($alumnus['email_id']); ?>
                                    </a>
                                <?php endif; ?>
                                
                                <?php if (!empty($alumnus['bio'])): ?>
                                    <div class="alumni-bio">
                                        <?php echo nl2br(htmlspecialchars(substr($alumnus['bio'], 0, 150) . (strlen($alumnus['bio']) > 150 ? '...' : ''))); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Add any JavaScript functionality here if needed
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize any interactive elements
        });
    </script>
</body>
</html>
