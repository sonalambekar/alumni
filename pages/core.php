<?php
session_start();
require_once '../includes/db_config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Core Team - Alumni Connect</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #5b1f1f;
            --secondary-color: #ecc35c;
            --bg-light: #f5f7fb;
            --text-color: #333;
        }

        .core-team-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .page-header {
            text-align: center;
            margin-bottom: 50px;
        }

        .page-header h1 {
            color: var(--primary-color);
            font-size: 2.8rem;
            font-weight: 700;
            margin-bottom: 15px;
        }

        .page-header p {
            color: #666;
            font-size: 1.2rem;
            max-width: 700px;
            margin: 0 auto;
        }

        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 40px;
            margin-top: 50px;
        }

        .team-member {
            text-align: center;
            transition: transform 0.3s ease;
        }

        .team-member:hover {
            transform: translateY(-10px);
        }

        .member-image-wrapper {
            position: relative;
            width: 200px;
            height: 200px;
            margin: 0 auto 20px;
        }

        .member-image {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid var(--secondary-color);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
        }

        .team-member:hover .member-image {
            border-color: var(--primary-color);
            box-shadow: 0 15px 40px rgba(91, 31, 31, 0.3);
            transform: scale(1.05);
        }

        .member-info {
            padding: 0 15px;
        }

        .member-name {
            font-size: 1.4rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 8px;
        }

        .member-position {
            font-size: 1rem;
            color: var(--secondary-color);
            font-weight: 500;
            margin-bottom: 10px;
        }

        .member-description {
            font-size: 0.9rem;
            color: #666;
            line-height: 1.6;
        }

        @media (max-width: 768px) {
            .page-header h1 {
                font-size: 2rem;
            }

            .team-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                gap: 30px;
            }

            .member-image-wrapper {
                width: 150px;
                height: 150px;
            }

            .member-name {
                font-size: 1.2rem;
            }
        }
    </style>
</head>
<body>
    <?php include '../sidebar.php'; ?>

    <div class="main-content" id="mainContent">
        <div class="core-team-container">
            <div class="page-header">
                <h1>Our Core Team</h1>
                <p>Meet the dedicated individuals leading our alumni community</p>
            </div>

            <div class="team-grid">
                <?php
                // Team member data with their actual images from assets/images/core
                $teamMembers = [
                    ['name' => 'Ganesh G. Tilve', 'position' => 'Core Team Member', 'image' => 'ganesh.jpeg'],
                    ['name' => 'Dr. Santoshkumar M', 'position' => 'Core Team Member', 'image' => 'Dr. Santoshkumar M.jpg'],
                    ['name' => 'Dr. Kiran Kumar H S', 'position' => 'Core Team Member', 'image' => 'Dr. Kiran Kumar H S.jpg'],
                ];

                // Display team members with their images
                foreach ($teamMembers as $member) {
                    $imagePath = '../assets/images/core/' . $member['image'];
                    ?>
                    <div class="team-member">
                        <div class="member-image-wrapper">
                            <img src="<?php echo htmlspecialchars($imagePath); ?>" 
                                 alt="<?php echo htmlspecialchars($member['name']); ?>" 
                                 class="member-image"
                                 onerror="this.src='../assets/images/default-avatar.jpg'">
                        </div>
                        <div class="member-info">
                            <h3 class="member-name"><?php echo htmlspecialchars($member['name']); ?></h3>
                            <p class="member-position"><?php echo htmlspecialchars($member['position']); ?></p>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>
    </div>

    <script src="../assets/js/script.js"></script>
</body>
</html>