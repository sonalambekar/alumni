<?php
// Admin Photo Galleries Management
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once '../includes/db_config.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        if ($action === 'add_gallery') {
            // Add new gallery
            $title = $_POST['title'] ?? '';
            $description = $_POST['description'] ?? '';
            $event_date = !empty($_POST['event_date']) ? $_POST['event_date'] : null;
            $location = $_POST['location'] ?? '';

            if (!empty($title)) {
                try {
                    $stmt = $pdo->prepare("
                        INSERT INTO galleries (title, description, event_date, location, author_id)
                        VALUES (?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([$title, $description, $event_date, $location, $_SESSION['user_id']]);

                    $success = "Gallery created successfully!";
                } catch(PDOException $e) {
                    $error = "Error creating gallery: " . $e->getMessage();
                }
            } else {
                $error = "Please enter a gallery title";
            }
        } elseif ($action === 'edit_gallery') {
            // Update gallery
            $id = $_POST['gallery_id'] ?? 0;
            $title = $_POST['title'] ?? '';
            $description = $_POST['description'] ?? '';
            $event_date = !empty($_POST['event_date']) ? $_POST['event_date'] : null;
            $location = $_POST['location'] ?? '';
            $is_active = isset($_POST['is_active']) ? 1 : 0;

            if (!empty($title) && !empty($id)) {
                try {
                    $stmt = $pdo->prepare("
                        UPDATE galleries
                        SET title = ?, description = ?, event_date = ?, location = ?, is_active = ?, updated_at = CURRENT_TIMESTAMP
                        WHERE id = ?
                    ");
                    $stmt->execute([$title, $description, $event_date, $location, $is_active, $id]);

                    $success = "Gallery updated successfully!";
                } catch(PDOException $e) {
                    $error = "Error updating gallery: " . $e->getMessage();
                }
            } else {
                $error = "Please fill in all required fields";
            }
        } elseif ($action === 'delete_gallery') {
            // Delete gallery (this will cascade delete photos)
            $id = $_POST['gallery_id'] ?? 0;

            if (!empty($id)) {
                try {
                    $stmt = $pdo->delete("DELETE FROM galleries WHERE id = ?", [$id]);
                    $success = "Gallery deleted successfully!";
                } catch(PDOException $e) {
                    $error = "Error deleting gallery: " . $e->getMessage();
                }
            }
        } elseif ($action === 'add_photo') {
            // Add new photo to gallery
            $gallery_id = $_POST['gallery_id'] ?? 0;
            $title = $_POST['title'] ?? '';
            $description = $_POST['description'] ?? '';
            $image_path = $_POST['image_path'] ?? '';
            $caption = $_POST['caption'] ?? '';
            $is_featured = isset($_POST['is_featured']) ? 1 : 0;

            if (!empty($gallery_id) && !empty($image_path)) {
                try {
                    $stmt = $pdo->prepare("
                        INSERT INTO photos (gallery_id, title, description, image_path, caption, is_featured)
                        VALUES (?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([$gallery_id, $title, $description, $image_path, $caption, $is_featured]);

                    $success = "Photo added successfully!";
                } catch(PDOException $e) {
                    $error = "Error adding photo: " . $e->getMessage();
                }
            } else {
                $error = "Please select a gallery and provide an image URL";
            }
        } elseif ($action === 'edit_photo') {
            // Update photo
            $id = $_POST['photo_id'] ?? 0;
            $title = $_POST['title'] ?? '';
            $description = $_POST['description'] ?? '';
            $image_path = $_POST['image_path'] ?? '';
            $caption = $_POST['caption'] ?? '';
            $is_featured = isset($_POST['is_featured']) ? 1 : 0;

            if (!empty($id) && !empty($image_path)) {
                try {
                    $stmt = $pdo->prepare("
                        UPDATE photos
                        SET title = ?, description = ?, image_path = ?, caption = ?, is_featured = ?
                        WHERE id = ?
                    ");
                    $stmt->execute([$title, $description, $image_path, $caption, $is_featured, $id]);

                    $success = "Photo updated successfully!";
                } catch(PDOException $e) {
                    $error = "Error updating photo: " . $e->getMessage();
                }
            } else {
                $error = "Please provide an image URL";
            }
        } elseif ($action === 'delete_photo') {
            // Delete photo
            $id = $_POST['photo_id'] ?? 0;

            if (!empty($id)) {
                try {
                    $stmt = $pdo->delete("DELETE FROM photos WHERE id = ?", [$id]);
                    $success = "Photo deleted successfully!";
                } catch(PDOException $e) {
                    $error = "Error deleting photo: " . $e->getMessage();
                }
            }
        }
    }
}

// Initialize variables
$galleries = [];
$photos = [];
$success = '';
$error = '';

// Get all galleries with photo counts
try {
    // Check if tables exist before querying
    $tablesExist = true;
    try {
        $pdo->query("SELECT 1 FROM galleries LIMIT 1");
        $pdo->query("SELECT 1 FROM users LIMIT 1");
        $pdo->query("SELECT 1 FROM photos LIMIT 1");
    } catch(PDOException $e) {
        $tablesExist = false;
    }

    if ($tablesExist) {
        try {
            // Try to get author name, fallback to author_id if full_name doesn't exist
            $stmt = $pdo->query("
                SELECT g.*,
                       COALESCE(u.full_name, u.name, CONCAT('User #', g.author_id)) as author_name,
                       COUNT(p.id) as photo_count
                FROM galleries g
                LEFT JOIN users u ON g.author_id = u.id
                LEFT JOIN photos p ON g.id = p.gallery_id
                GROUP BY g.id
                ORDER BY g.created_at DESC
            ");
            $galleries = $stmt->fetchAll();

            // Get all photos for modal
            $stmt = $pdo->query("
                SELECT p.*, g.title as gallery_title
                FROM photos p
                LEFT JOIN galleries g ON p.gallery_id = g.id
                ORDER BY p.gallery_id, p.sort_order, p.upload_date DESC
            ");
            $photos = $stmt->fetchAll();

        } catch(PDOException $e) {
            // If the join fails, try without the author name
            $stmt = $pdo->query("
                SELECT g.*,
                       CONCAT('User #', g.author_id) as author_name,
                       COUNT(p.id) as photo_count
                FROM galleries g
                LEFT JOIN photos p ON g.id = p.gallery_id
                GROUP BY g.id
                ORDER BY g.created_at DESC
            ");
            $galleries = $stmt->fetchAll();

            // Get all photos for modal (simplified query)
            $stmt = $pdo->query("
                SELECT p.*, g.title as gallery_title
                FROM photos p
                LEFT JOIN galleries g ON p.gallery_id = g.id
                ORDER BY p.gallery_id, p.sort_order, p.upload_date DESC
            ");
            $photos = $stmt->fetchAll();
        }
    } else {
        $error = "Database tables not found. Please run the database setup first.";
    }
} catch(PDOException $e) {
    $error = "Error loading galleries: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Photo Galleries - Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #5b1f1f;
            --secondary-color: #ecc35c;
            --bg-light: #f5f7fb;
            --text-color: #333;
            --text-light: #6b7280;
            --border-color: #e5e7eb;
            --white: #ffffff;
            --shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.05);
            --shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.1);
            --border-radius: 8px;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-light);
            color: var(--text-color);
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }

        .admin-header {
            background: var(--white);
            border-bottom: 1px solid var(--border-color);
            padding: 20px 0;
        }

        .admin-nav {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
        }

        .admin-brand {
            font-size: 24px;
            font-weight: 700;
            color: var(--primary-color);
        }

        .back-btn {
            background: var(--text-light);
            color: var(--white);
            border: none;
            padding: 8px 16px;
            border-radius: var(--border-radius);
            font-size: 14px;
            cursor: pointer;
            transition: background 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .back-btn:hover {
            background: #4b5563;
        }

        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .dashboard-title {
            font-size: 28px;
            font-weight: 700;
            color: var(--text-color);
        }

        .add-btn {
            background: var(--primary-color);
            color: var(--white);
            border: none;
            padding: 12px 24px;
            border-radius: var(--border-radius);
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: background 0.3s ease;
        }

        .add-btn:hover {
            background: #4a1919;
        }

        .alert {
            padding: 15px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            font-weight: 500;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }

        .form-container {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 30px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border-color);
            margin-bottom: 30px;
        }

        .form-container h3 {
            font-size: 20px;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--primary-color);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-color);
        }

        .form-input, .form-textarea, .form-select {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid var(--border-color);
            border-radius: var(--border-radius);
            font-size: 14px;
            font-family: inherit;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .form-input:focus, .form-textarea:focus, .form-select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(91, 31, 31, 0.1);
        }

        .form-textarea {
            resize: vertical;
            min-height: 120px;
        }

        .form-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: var(--border-radius);
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: var(--primary-color);
            color: var(--white);
        }

        .btn-primary:hover {
            background: #4a1919;
        }

        .btn-secondary {
            background: var(--text-light);
            color: var(--white);
        }

        .btn-secondary:hover {
            background: #4b5563;
        }

        .btn-danger {
            background: #dc2626;
            color: var(--white);
        }

        .btn-danger:hover {
            background: #b91c1c;
        }

        .galleries-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .gallery-card {
            background: var(--white);
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            border: 1px solid var(--border-color);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .gallery-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .gallery-header {
            padding: 20px;
            border-bottom: 1px solid var(--border-color);
        }

        .gallery-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 8px;
        }

        .gallery-meta {
            font-size: 12px;
            color: var(--text-light);
            margin-bottom: 10px;
        }

        .gallery-description {
            color: var(--text-color);
            font-size: 14px;
            line-height: 1.5;
        }

        .gallery-footer {
            padding: 15px 20px;
            background: var(--bg-light);
            display: flex;
            justify-content: between;
            align-items: center;
        }

        .photo-count {
            font-size: 12px;
            color: var(--text-light);
        }

        .gallery-actions {
            display: flex;
            gap: 8px;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
        }

        .status-active {
            background: #d1fae5;
            color: #065f46;
        }

        .status-inactive {
            background: #fee2e2;
            color: #991b1b;
        }

        .photos-section {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 25px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border-color);
            margin-top: 30px;
        }

        .photos-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .photos-title {
            font-size: 20px;
            font-weight: 600;
            color: var(--text-color);
        }

        .photos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
        }

        .photo-card {
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            overflow: hidden;
            background: var(--white);
        }

        .photo-image {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-bottom: 1px solid var(--border-color);
        }

        .photo-info {
            padding: 12px;
        }

        .photo-title {
            font-weight: 500;
            font-size: 14px;
            color: var(--text-color);
            margin-bottom: 4px;
        }

        .photo-caption {
            font-size: 12px;
            color: var(--text-light);
            margin-bottom: 8px;
        }

        .photo-actions {
            display: flex;
            gap: 5px;
        }

        .featured-badge {
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 10px;
            font-weight: 500;
            text-transform: uppercase;
            background: #fbbf24;
            color: #92400e;
            margin-bottom: 8px;
            display: inline-block;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }

            .form-actions {
                flex-direction: column;
            }

            .dashboard-header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }

            .galleries-grid {
                grid-template-columns: 1fr;
            }

            .photos-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            }
        }
    </style>
</head>
<body>
    <!-- Admin Header -->
    <div class="admin-header">
        <div class="admin-nav">
            <div class="admin-brand">Photo Galleries Management</div>
            <a href="admin_dashboard.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <div class="dashboard-container">
        <?php if (isset($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- Add Gallery Form -->
        <div class="form-container">
            <h3><i class="fas fa-plus-circle"></i> Create New Gallery</h3>
            <form method="POST" action="">
                <input type="hidden" name="action" value="add_gallery">

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="title">Gallery Title *</label>
                        <input type="text" id="title" name="title" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="event_date">Event Date</label>
                        <input type="date" id="event_date" name="event_date" class="form-input">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="location">Location</label>
                        <input type="text" id="location" name="location" class="form-input" placeholder="Event location or venue">
                    </div>
                </div>

                <div class="form-group full-width">
                    <label class="form-label" for="description">Description</label>
                    <textarea id="description" name="description" class="form-textarea" rows="3" placeholder="Describe the gallery and what photos it contains..."></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Create Gallery
                    </button>
                    <button type="reset" class="btn btn-secondary">
                        <i class="fas fa-undo"></i> Reset
                    </button>
                </div>
            </form>
        </div>

        <!-- Galleries Grid -->
        <div class="galleries-grid">
            <?php if (isset($galleries) && !empty($galleries)): ?>
                <?php foreach ($galleries as $gallery): ?>
                    <div class="gallery-card">
                        <div class="gallery-header">
                            <div class="gallery-title"><?php echo htmlspecialchars($gallery['title']); ?></div>
                            <div class="gallery-meta">
                                by <?php echo htmlspecialchars($gallery['author_name']); ?> •
                                <?php echo isset($gallery['photo_count']) ? $gallery['photo_count'] : 0; ?> photos
                            </div>
                            <?php if (isset($gallery['description']) && !empty($gallery['description'])): ?>
                                <div class="gallery-description">
                                    <?php echo htmlspecialchars(substr($gallery['description'], 0, 100)); ?>
                                    <?php if (strlen($gallery['description']) > 100): ?>...<?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="gallery-footer">
                            <div class="photo-count">
                                <?php echo isset($gallery['photo_count']) ? $gallery['photo_count'] : 0; ?> photos
                                <?php if (isset($gallery['event_date']) && !empty($gallery['event_date'])): ?>
                                    • <?php echo date('M j, Y', strtotime($gallery['event_date'])); ?>
                                <?php endif; ?>
                            </div>
                            <div class="gallery-actions">
                                <span class="status-badge <?php echo (isset($gallery['is_active']) && $gallery['is_active']) ? 'status-active' : 'status-inactive'; ?>">
                                    <?php echo (isset($gallery['is_active']) && $gallery['is_active']) ? 'Active' : 'Inactive'; ?>
                                </span>
                                <button class="btn btn-secondary" onclick="managePhotos(<?php echo $gallery['id']; ?>)">
                                    <i class="fas fa-images"></i> Manage
                                </button>
                                <button class="btn btn-secondary" onclick="editGallery(<?php echo $gallery['id']; ?>)">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this gallery? All photos will be deleted too.');">
                                    <input type="hidden" name="action" value="delete_gallery">
                                    <input type="hidden" name="gallery_id" value="<?php echo $gallery['id']; ?>">
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: var(--text-light);">
                    <i class="fas fa-images" style="font-size: 48px; margin-bottom: 15px; display: block;"></i>
                    No galleries found. Create your first gallery above.
                </div>
            <?php endif; ?>
        </div>

        <!-- Photos Management Section (Hidden by default) -->
        <div class="photos-section" id="photosSection" style="display: none;">
            <div class="photos-header">
                <h3 class="photos-title" id="photosGalleryTitle">Gallery Photos</h3>
                <div>
                    <button class="btn btn-primary" onclick="showAddPhotoForm()">
                        <i class="fas fa-plus"></i> Add Photo
                    </button>
                </div>
            </div>

            <!-- Add Photo Form -->
            <div class="form-container" id="addPhotoForm" style="display: none; margin-top: 20px; margin-bottom: 20px;">
                <h3><i class="fas fa-plus-circle"></i> Add New Photo</h3>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="add_photo">
                    <input type="hidden" name="gallery_id" id="add_photo_gallery_id">

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="photo_title">Photo Title</label>
                            <input type="text" id="photo_title" name="title" class="form-input">
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="photo_image_path">Image URL *</label>
                            <input type="url" id="photo_image_path" name="image_path" class="form-input" required placeholder="https://example.com/photo.jpg">
                        </div>
                    </div>

                    <div class="form-group full-width">
                        <label class="form-label" for="photo_caption">Caption</label>
                        <input type="text" id="photo_caption" name="caption" class="form-input" placeholder="Brief description of the photo...">
                    </div>

                    <div class="form-group full-width">
                        <label class="form-label" for="photo_description">Description</label>
                        <textarea id="photo_description" name="description" class="form-textarea" rows="2" placeholder="Detailed description of the photo..."></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <input type="checkbox" id="photo_is_featured" name="is_featured"> Featured Photo
                        </label>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Add Photo
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="hideAddPhotoForm()">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                    </div>
                </form>
            </div>

            <!-- Photos Grid -->
            <div class="photos-grid" id="photosGrid">
                <!-- Photos will be populated here by JavaScript -->
            </div>
        </div>
    </div>

    <script>
        let currentGalleryId = null;

        function managePhotos(galleryId) {
            currentGalleryId = galleryId;
            const galleries = <?php echo json_encode($galleries); ?>;
            const gallery = galleries.find(g => g.id == galleryId);

            if (gallery) {
                document.getElementById('photosGalleryTitle').textContent = gallery.title + ' - Photos';
                document.getElementById('add_photo_gallery_id').value = galleryId;
                document.getElementById('photosSection').style.display = 'block';

                // Filter and display photos for this gallery
                const photos = <?php echo json_encode($photos); ?>;
                const galleryPhotos = photos.filter(p => p.gallery_id == galleryId);

                displayPhotos(galleryPhotos);
            }
        }

        function displayPhotos(photos) {
            const photosGrid = document.getElementById('photosGrid');

            if (photos.length === 0) {
                photosGrid.innerHTML = '<div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: var(--text-light);"><i class="fas fa-image" style="font-size: 48px; margin-bottom: 15px; display: block;"></i>No photos in this gallery yet. Add some photos above.</div>';
                return;
            }

            photosGrid.innerHTML = photos.map(photo => `
                <div class="photo-card">
                    <img src="${photo.image_path}" alt="${photo.title || 'Photo'}" class="photo-image" onerror="this.src='https://via.placeholder.com/200x150?text=No+Image'">
                    <div class="photo-info">
                        ${photo.is_featured ? '<span class="featured-badge">Featured</span>' : ''}
                        <div class="photo-title">${photo.title || 'Untitled'}</div>
                        <div class="photo-caption">${photo.caption || ''}</div>
                        <div class="photo-actions">
                            <button class="btn btn-secondary" onclick="editPhoto(${photo.id})" style="padding: 5px 10px; font-size: 12px;">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this photo?');">
                                <input type="hidden" name="action" value="delete_photo">
                                <input type="hidden" name="photo_id" value="${photo.id}">
                                <button type="submit" class="btn btn-danger" style="padding: 5px 10px; font-size: 12px;">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        function showAddPhotoForm() {
            document.getElementById('addPhotoForm').style.display = 'block';
        }

        function hideAddPhotoForm() {
            document.getElementById('addPhotoForm').style.display = 'none';
        }

        function editGallery(galleryId) {
            // Find the gallery data and populate the modal
            const galleries = <?php echo json_encode($galleries); ?>;
            const gallery = galleries.find(g => g.id == galleryId);

            if (gallery) {
                document.getElementById('edit_gallery_id').value = gallery.id;
                document.getElementById('edit_title').value = gallery.title || '';
                document.getElementById('edit_description').value = gallery.description || '';
                document.getElementById('edit_event_date').value = gallery.event_date || '';
                document.getElementById('edit_location').value = gallery.location || '';
                document.getElementById('edit_is_active').checked = (gallery.is_active == 1);

                document.getElementById('editModal').classList.add('show');
            }
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.remove('show');
        }

        function editPhoto(photoId) {
            // Find the photo data and populate the modal
            const photos = <?php echo json_encode($photos); ?>;
            const photo = photos.find(p => p.id == photoId);

            if (photo) {
                document.getElementById('edit_photo_id').value = photo.id;
                document.getElementById('edit_photo_title').value = photo.title || '';
                document.getElementById('edit_photo_image_path').value = photo.image_path;
                document.getElementById('edit_photo_caption').value = photo.caption || '';
                document.getElementById('edit_photo_description').value = photo.description || '';
                document.getElementById('edit_photo_is_featured').checked = (photo.is_featured == 1);

                document.getElementById('editPhotoModal').classList.add('show');
            }
        }

        function closeEditPhotoModal() {
            document.getElementById('editPhotoModal').classList.remove('show');
        }
    </script>
</body>
</html>
