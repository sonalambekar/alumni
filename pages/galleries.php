<?php
// Start session and include database configuration
session_start();
require_once '../includes/db_config.php';

// Function to get gallery image count
function getGalleryImageCount($pdo, $galleryId) {
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM gallery_images WHERE gallery_id = ?");
    $stmt->execute([$galleryId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? $result['count'] : 0;
}

// Fetch all active galleries with their first image as cover
try {
    $stmt = $pdo->query("
        SELECT 
            g.*,
            (SELECT cover_image FROM galleries WHERE id = g.id LIMIT 1) as cover_image_path,
            (SELECT COUNT(*) FROM gallery_images WHERE gallery_id = g.id) as image_count
        FROM galleries g
        WHERE g.is_active = 1
        ORDER BY g.created_at DESC
    ");
    $galleries = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Debug output (remove after testing)
    error_log("Fetched galleries: " . print_r($galleries, true));
    
} catch (PDOException $e) {
    $error = "Error fetching galleries: " . $e->getMessage();
    $galleries = [];
    error_log($error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galleries - Alumni Connect</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .gallery-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        .gallery-title {
            text-align: center;
            color: var(--primary-color);
            font-size: 2.2rem;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .gallery-subtitle {
            text-align: center;
            color: var(--text-light);
            font-size: 1.1rem;
            margin-bottom: 40px;
        }

        /* Masonry Gallery Layout */
        .masonry-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            grid-auto-rows: 200px;
            gap: 15px;
            margin-bottom: 40px;
        }

        .gallery-item {
            position: relative;
            overflow: hidden;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.4s ease;
            cursor: pointer;
        }

        .gallery-item:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.2);
        }

        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }

        .gallery-item:hover img {
            transform: scale(1.1);
        }

        .gallery-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(
                45deg,
                rgba(91, 31, 31, 0.8) 0%,
                rgba(236, 195, 92, 0.6) 50%,
                rgba(91, 31, 31, 0.8) 100%
            );
            opacity: 0;
            transition: opacity 0.3s ease;
            display: flex;
            align-items: flex-end;
            padding: 20px;
        }

        .gallery-item:hover .gallery-overlay {
            opacity: 1;
        }

        .gallery-info {
            color: white;
            font-size: 0.9rem;
        }

        .gallery-info h3 {
            font-size: 1.1rem;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .gallery-info p {
            font-size: 0.8rem;
            opacity: 0.9;
            margin: 2px 0;
        }
        
        .gallery-desc {
            font-size: 0.75rem !important;
            opacity: 0.8 !important;
            margin-top: 8px !important;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Different sizes for masonry effect */
        .gallery-item:nth-child(3n) {
            grid-row: span 2;
        }

        .gallery-item:nth-child(5n) {
            grid-row: span 3;
        }

        .gallery-item:nth-child(7n) {
            grid-row: span 2;
        }

        .gallery-item:nth-child(8n) {
            grid-column: span 2;
        }

        /* Responsive adjustments */
        @media (max-width: 1200px) {
            .masonry-gallery {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                gap: 12px;
            }
        }

        @media (max-width: 768px) {
            .masonry-gallery {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                gap: 10px;
                grid-auto-rows: 150px;
            }

            .gallery-title {
                font-size: 1.8rem;
            }

            .gallery-subtitle {
                font-size: 1rem;
            }
        }

        @media (max-width: 480px) {
            .masonry-gallery {
                grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
                gap: 8px;
                grid-auto-rows: 120px;
            }
        }
    </style>
</head>
<body>
    <?php include '../sidebar.php'; ?>

    <div class="main-content" id="mainContent">
        <div class="gallery-container">
            <h2 class="gallery-title">Photo Galleries</h2>
            <p class="gallery-subtitle">Relive memorable moments from alumni events and gatherings</p>

            <div class="masonry-gallery">
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
                
                // Display database galleries first
                if (!empty($galleries)): 
                    foreach ($galleries as $gallery): 
                        $coverImage = '';
                        if (!empty($gallery['cover_image_path'])) {
                            $coverImage = '../' . ltrim($gallery['cover_image_path'], '/');
                        } elseif (!empty($gallery['cover_image'])) {
                            $coverImage = '../' . ltrim($gallery['cover_image'], '/');
                        }
                        
                        // Check if the file exists, if not use first available gallery image
                        if (!empty($coverImage) && !file_exists(ltrim($coverImage, '/')) && !file_exists($coverImage)) {
                            if (!empty($galleryImages)) {
                                $coverImage = $galleryImagesDir . $galleryImages[0];
                            }
                        } elseif (empty($coverImage) && !empty($galleryImages)) {
                            $coverImage = $galleryImagesDir . $galleryImages[0];
                        }
                        
                        $galleryDate = date('F j, Y', strtotime($gallery['created_at']));
                        $imageCount = $gallery['image_count'] ?? getGalleryImageCount($pdo, $gallery['id']);
                ?>
                        <div class="gallery-item">
                            <a href="gallery-view.php?id=<?php echo $gallery['id']; ?>">
                                <img src="<?php echo htmlspecialchars($coverImage); ?>" 
                                     alt="<?php echo htmlspecialchars($gallery['name']); ?>"
                                     style="width: 100%; height: 100%; object-fit: cover;">
                                <div class="gallery-overlay">
                                    <div class="gallery-info">
                                        <h3><?php echo htmlspecialchars($gallery['name']); ?></h3>
                                        <p><?php echo $galleryDate; ?> • <?php echo $imageCount; ?> Photo<?php echo $imageCount != 1 ? 's' : ''; ?></p>
                                        <?php if (!empty($gallery['description'])): ?>
                                            <div class="gallery-desc"><?php echo nl2br(htmlspecialchars($gallery['description'])); ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </a>
                        </div>
                <?php 
                    endforeach; 
                endif;
                
                // Display individual gallery images
                foreach ($galleryImages as $index => $image): 
                    $imagePath = $galleryImagesDir . $image;
                    $imageNumber = $index + 1;
                ?>
                    <div class="gallery-item">
                        <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="Alumni Gallery Image <?php echo $imageNumber; ?>">
                        <div class="gallery-overlay">
                            <div class="gallery-info">
                                <h3>Alumni Memories</h3>
                                <p>Gallery Image <?php echo $imageNumber; ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script src="../assets/js/script.js"></script>
    <script>
        // Add any gallery-specific JavaScript here
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize any gallery functionality
            console.log('Gallery page loaded');
        });
    </script>
</body>
</html>
