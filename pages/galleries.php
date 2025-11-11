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
                <?php if (!empty($galleries)): ?>
                    <?php foreach ($galleries as $gallery): ?>
                        <?php 
// Get the first image as cover if no cover image is set
                            $coverImage = '';
                            if (!empty($gallery['cover_image_path'])) {
                                $coverImage = '../' . ltrim($gallery['cover_image_path'], '/');
                            } elseif (!empty($gallery['cover_image'])) {
                                $coverImage = '../' . ltrim($gallery['cover_image'], '/');
                            }
                            
                            // Check if the file exists, if not use placeholder
                            if (!empty($coverImage) && !file_exists(ltrim($coverImage, '/')) && !file_exists($coverImage)) {
                                error_log("Image not found: " . $coverImage);
                                $coverImage = 'https://via.placeholder.com/800x600?text=No+Image';
                            } elseif (empty($coverImage)) {
                                $coverImage = 'https://via.placeholder.com/800x600?text=No+Image';
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
                    <?php endforeach; ?>
                <?php endif; ?>

                <div class="gallery-item">
                    <img src="https://images.unsplash.com/photo-1591115765373-5207764f72e7?w=800&h=600&fit=crop" alt="Tech Summit 2024">
                    <div class="gallery-overlay">
                        <div class="gallery-info">
                            <h3>Tech Leaders Summit 2024</h3>
                            <p>August 25, 2024 • 70 Photos</p>
                        </div>
                    </div>
                </div>

                <div class="gallery-item">
                    <img src="https://images.unsplash.com/photo-1523050854058-8df90110c9f1?w=800&h=600&fit=crop" alt="Graduation 2024">
                    <div class="gallery-overlay">
                        <div class="gallery-info">
                            <h3>Graduation Ceremony 2024</h3>
                            <p>June 30, 2024 • 200 Photos</p>
                        </div>
                    </div>
                </div>

                <div class="gallery-item">
                    <img src="https://images.unsplash.com/photo-1511578314322-379afb476865?w=800&h=600&fit=crop" alt="Mentorship Launch">
                    <div class="gallery-overlay">
                        <div class="gallery-info">
                            <h3>Mentorship Program Launch</h3>
                            <p>May 12, 2024 • 45 Photos</p>
                        </div>
                    </div>
                </div>

                <div class="gallery-item">
                    <img src="https://images.unsplash.com/photo-1469571486292-0ba58a3f068b?w=800&h=600&fit=crop" alt="Social Impact">
                    <div class="gallery-overlay">
                        <div class="gallery-info">
                            <h3>Social Impact Initiative 2024</h3>
                            <p>April 22, 2024 • 60 Photos</p>
                        </div>
                    </div>
                </div>

                <div class="gallery-item">
                    <img src="https://images.unsplash.com/photo-1559136555-9303baea8ebd?w=800&h=600&fit=crop" alt="Entrepreneurs Meet">
                    <div class="gallery-overlay">
                        <div class="gallery-info">
                            <h3>Entrepreneurs Meetup 2024</h3>
                            <p>March 18, 2024 • 55 Photos</p>
                        </div>
                    </div>
                </div>

                <div class="gallery-item">
                    <img src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=800&h=600&fit=crop" alt="Women Leaders">
                    <div class="gallery-overlay">
                        <div class="gallery-info">
                            <h3>Women Leaders Summit 2024</h3>
                            <p>December 5, 2023 • 65 Photos</p>
                        </div>
                    </div>
                </div>

                <div class="gallery-item">
                    <img src="https://images.unsplash.com/photo-1505373876331-ff89f46d510d?w=800&h=600&fit=crop" alt="Research Symposium">
                    <div class="gallery-overlay">
                        <div class="gallery-info">
                            <h3>Annual Research Symposium</h3>
                            <p>November 15, 2023 • 85 Photos</p>
                        </div>
                    </div>
                </div>

                <div class="gallery-item">
                    <img src="https://images.unsplash.com/photo-1522071820081-009c5fdc0a27?w=800&h=600&fit=crop" alt="Hackathon">
                    <div class="gallery-overlay">
                        <div class="gallery-info">
                            <h3>24-Hour Hackathon</h3>
                            <p>October 28, 2023 • 120 Photos</p>
                        </div>
                    </div>
                </div>

                <div class="gallery-item">
                    <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=800&h=600&fit=crop" alt="Campus Festival">
                    <div class="gallery-overlay">
                        <div class="gallery-info">
                            <h3>Annual Campus Festival</h3>
                            <p>September 30, 2023 • 150 Photos</p>
                        </div>
                    </div>
                </div>

                <div class="gallery-item">
                    <img src="https://images.unsplash.com/photo-1523240795612-9a054b0db644?w=800&h=600&fit=crop" alt="Alumni Meet">
                    <div class="gallery-overlay">
                        <div class="gallery-info">
                            <h3>Alumni Homecoming</h3>
                            <p>August 12, 2023 • 95 Photos</p>
                        </div>
                    </div>
                </div>

                <div class="gallery-item">
                    <img src="https://images.unsplash.com/photo-1523057530100-383d7fbc77a1?w=800&h=600&fit=crop" alt="Sports Day">
                    <div class="gallery-overlay">
                        <div class="gallery-info">
                            <h3>Annual Sports Day</h3>
                            <p>July 22, 2023 • 180 Photos</p>
                        </div>
                    </div>
                </div>

                <div class="gallery-item">
                    <img src="https://images.unsplash.com/photo-1532094349884-543bc11b234d?w=800&h=600&fit=crop" alt="Cultural Night">
                    <div class="gallery-overlay">
                        <div class="gallery-info">
                            <h3>Cultural Night Extravaganza</h3>
                            <p>June 15, 2023 • 110 Photos</p>
                        </div>
                    </div>
                </div>

                <div class="gallery-item">
                    <img src="https://images.unsplash.com/photo-1517048676732-d0bc0d4706db?w=800&h=600&fit=crop" alt="Startup Showcase">
                    <div class="gallery-overlay">
                        <div class="gallery-info">
                            <h3>Startup Showcase 2023</h3>
                            <p>May 5, 2023 • 75 Photos</p>
                        </div>
                    </div>
                </div>
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
