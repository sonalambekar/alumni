<?php
require_once '../includes/db_config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Photo Gallery - Alumni Connect</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #5b1f1f;
            --secondary-color: #ecc35c;
            --bg-light: #f5f7fb;
            --text-color: #333;
            --light-gray: #f0f0f0;
            --border-color: #e0e0e0;
            --transition-speed: 0.3s;
        }

        .gallery-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        .page-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .page-header h1 {
            color: var(--primary-color);
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .page-header p {
            color: #666;
            font-size: 1.1rem;
            max-width: 700px;
            margin: 0 auto;
        }

        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            margin-top: 30px;
        }

        .gallery-item {
            position: relative;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            transition: all var(--transition-speed) ease;
            aspect-ratio: 4/3;
            background: #fff;
        }

        .gallery-item:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            z-index: 2;
        }

        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .gallery-item:hover img {
            transform: scale(1.05);
        }

        .gallery-caption {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0, 0, 0, 0.8));
            color: white;
            padding: 25px 20px;
            transform: translateY(100%);
            transition: transform var(--transition-speed) ease;
            backdrop-filter: blur(5px);
        }

        .gallery-item:hover .gallery-caption {
            transform: translateY(0);
        }

        .gallery-caption h3 {
            margin: 0 0 8px 0;
            font-size: 1.2rem;
            font-weight: 600;
            text-shadow: 0 1px 3px rgba(0,0,0,0.3);
        }

        .gallery-caption p {
            margin: 0;
            font-size: 0.95rem;
            opacity: 0.95;
            line-height: 1.4;
            text-shadow: 0 1px 2px rgba(0,0,0,0.3);
        }

        .gallery-category {
            margin-bottom: 60px;
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .gallery-category h2 {
            color: var(--primary-color);
            margin-bottom: 25px;
            font-size: 2rem;
            position: relative;
            padding-bottom: 15px;
            display: inline-block;
        }

        .gallery-category h2:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 3px;
            background: var(--secondary-color);
            border-radius: 3px;
        }

        @media (max-width: 768px) {
            .gallery-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                gap: 15px;
            }

            .page-header h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <?php include '../sidebar.php'; ?>

    <div class="main-content">
        <div class="gallery-container">
            <div class="page-header">
                <h1>Alumni Memories Gallery</h1>
                <p>Relive the cherished moments and create new memories with our alumni community</p>
            </div>

            <?php
            // Get all images from the gallery folder
            $galleryImagesDir = '../assets/images/gallery/';
            $galleryImages = [];
            if (is_dir($galleryImagesDir)) {
                $files = scandir($galleryImagesDir);
                foreach ($files as $file) {
                    if ($file !== '.' && $file !== '..' && preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $file)) {
                        $galleryImages[] = $file;
                    }
                }
            }
            
            // Split images into categories
            $imagesPerCategory = ceil(count($galleryImages) / 3);
            $graduationImages = array_slice($galleryImages, 0, $imagesPerCategory);
            $campusImages = array_slice($galleryImages, $imagesPerCategory, $imagesPerCategory);
            $sportsImages = array_slice($galleryImages, $imagesPerCategory * 2);
            ?>

            <?php if (!empty($graduationImages)): ?>
            <div class="gallery-category">
                <h2><i class="fas fa-graduation-cap"></i> Graduation Ceremonies</h2>
                <div class="gallery-grid">
                    <?php foreach ($graduationImages as $index => $image): ?>
                    <div class="gallery-item">
                        <img src="<?php echo htmlspecialchars($galleryImagesDir . $image); ?>" alt="Graduation Event">
                        <div class="gallery-caption">
                            <h3>Graduation Ceremony</h3>
                            <p>Alumni celebration moments</p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($campusImages)): ?>
            <div class="gallery-category">
                <h2><i class="fas fa-users"></i> Campus Life</h2>
                <div class="gallery-grid">
                    <?php foreach ($campusImages as $index => $image): ?>
                    <div class="gallery-item">
                        <img src="<?php echo htmlspecialchars($galleryImagesDir . $image); ?>" alt="Campus Life">
                        <div class="gallery-caption">
                            <h3>Campus Memories</h3>
                            <p>Life at our university</p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($sportsImages)): ?>
            <div class="gallery-category">
                <h2><i class="fas fa-trophy"></i> Sports & Events</h2>
                <div class="gallery-grid">
                    <?php foreach ($sportsImages as $index => $image): ?>
                    <div class="gallery-item">
                        <img src="<?php echo htmlspecialchars($galleryImagesDir . $image); ?>" alt="Sports & Events">
                        <div class="gallery-caption">
                            <h3>Alumni Events</h3>
                            <p>Sports and special occasions</p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Add any interactive functionality here if needed
        document.addEventListener('DOMContentLoaded', function() {
            // Smooth scroll for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth'
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>
