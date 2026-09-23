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
            $cover_image = '';

            // Handle file upload
            if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = '../attachments/galleries/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $file_extension = pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION);
                $filename = uniqid('gallery_') . '.' . $file_extension;
                $target_path = $upload_dir . $filename;
                
                if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $target_path)) {
                    $cover_image = 'attachments/galleries/' . $filename;
                } else {
                    $error = "Error uploading cover image";
                }
            }

            if (!empty($title)) {
                try {
                    $stmt = $pdo->prepare("
                        INSERT INTO galleries (name, description, cover_image, created_by, is_active)
                        VALUES (?, ?, ?, ?, 1)
                    ");
                    $stmt->execute([
                        $title, 
                        $description, 
                        $cover_image,
                        $_SESSION['user_id']
                    ]);

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
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            $current_cover = $_POST['current_cover'] ?? '';
            $cover_image = $current_cover;

            // Handle file upload if a new file is provided
            if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = '../attachments/galleries/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                // Delete old cover image if it exists
                if (!empty($current_cover) && file_exists('../' . $current_cover)) {
                    @unlink('../' . $current_cover);
                }
                
                $file_extension = pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION);
                $filename = uniqid('gallery_') . '.' . $file_extension;
                $target_path = $upload_dir . $filename;
                
                if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $target_path)) {
                    $cover_image = 'attachments/galleries/' . $filename;
                } else {
                    $error = "Error uploading cover image";
                }
            }

            if (!empty($title) && !empty($id)) {
                try {
                    $stmt = $pdo->prepare("
                        UPDATE galleries
                        SET name = ?, 
                            description = ?, 
                            cover_image = ?,
                            is_active = ?, 
                            updated_at = CURRENT_TIMESTAMP
                        WHERE id = ?
                    ");
                    $stmt->execute([
                        $title, 
                        $description, 
                        $cover_image,
                        $is_active, 
                        $id
                    ]);

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
                    $stmt = $pdo->prepare("DELETE FROM galleries WHERE id = ?");
                    $stmt->execute([$id]);
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
                    $stmt = $pdo->prepare("DELETE FROM photos WHERE id = ?");
                    $stmt->execute([$id]);
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
            // Get galleries with photo count
            $stmt = $pdo->query("
                SELECT g.id, g.name, g.description, g.is_active, g.created_at, g.updated_at, g.cover_image,
                       COUNT(p.id) as photo_count
                FROM galleries g
                LEFT JOIN photos p ON g.id = p.gallery_id
                GROUP BY g.id
                ORDER BY g.created_at DESC
            ");
            $galleries = $stmt->fetchAll();

            // Get all photos for modal
            $stmt = $pdo->query("
                SELECT p.*, g.name as gallery_name
                FROM photos p
                LEFT JOIN galleries g ON p.gallery_id = g.id
                ORDER BY p.gallery_id, p.upload_date DESC
            ");
            $photos = $stmt->fetchAll();

        } catch(PDOException $e) {
            // Get galleries with photo count (simplified query)
            $stmt = $pdo->query("
                SELECT g.id, g.name, g.description, g.is_active, g.created_at, g.updated_at, g.cover_image,
                       COUNT(p.id) as photo_count
                FROM galleries g
                LEFT JOIN photos p ON g.id = p.gallery_id
                GROUP BY g.id
                ORDER BY g.created_at DESC
            ");
            $galleries = $stmt->fetchAll();

            // Get all photos for modal (simplified query)
            $stmt = $pdo->query("
                SELECT p.*, g.name as gallery_name
                FROM photos p
                LEFT JOIN galleries g ON p.gallery_id = g.id
                ORDER BY p.gallery_id, p.upload_date DESC
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --primary-light: #818cf8;
            --secondary: #ec4899;
            --accent: #f59e0b;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --info: #3b82f6;
            
            --bg-main: #f8fafc;
            --bg-card: #ffffff;
            --bg-hover: #f1f5f9;
            
            --text-primary: #0f172a;
            --text-secondary: #475569;
            --text-muted: #94a3b8;
            
            --border: #e2e8f0;
            --border-light: #f1f5f9;
            
            --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.05);
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-md: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            --shadow-xl: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            
            --radius: 12px;
            --radius-sm: 8px;
            --radius-lg: 16px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #ffffff;
            min-height: 100vh;
            color: #000000;
            line-height: 1.6;
        }

        /* Header Styles */
        .admin-header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--border);
            padding: 1.25rem 0;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: var(--shadow-sm);
        }

        .admin-nav {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 2rem;
        }

        .admin-brand {
            font-size: 1.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .admin-brand i {
            -webkit-text-fill-color: var(--primary);
        }

        .back-btn {
            background: var(--text-secondary);
            color: white;
            border: none;
            padding: 0.625rem 1.25rem;
            border-radius: var(--radius-sm);
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .back-btn:hover {
            background: var(--text-primary);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        /* Container */
        .dashboard-container {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 2rem 2rem;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .dashboard-title {
            font-size: 2rem;
            font-weight: 800;
            color: white;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        /* Alert Styles */
        .alert {
            padding: 1rem 1.25rem;
            border-radius: var(--radius);
            margin-bottom: 1.5rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border-left: 4px solid var(--success);
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border-left: 4px solid var(--danger);
        }

        /* Form Container */
        .form-container {
            background: var(--bg-card);
            border-radius: var(--radius-lg);
            padding: 2rem;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--border);
            margin-bottom: 2rem;
            transition: all 0.3s ease;
        }

        .form-container:hover {
            box-shadow: var(--shadow-xl);
        }

        .form-container h3 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 3px solid var(--primary);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .form-container h3 i {
            color: var(--primary);
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--text-primary);
            font-size: 0.875rem;
        }

        .form-input, .form-textarea, .form-select, input[type="file"] {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid var(--border);
            border-radius: var(--radius-sm);
            font-size: 0.9375rem;
            font-family: inherit;
            transition: all 0.3s ease;
            background: var(--bg-card);
            color: var(--text-primary);
        }

        .form-input:focus, .form-textarea:focus, .form-select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        }

        .form-textarea {
            resize: vertical;
            min-height: 120px;
        }

        .form-text {
            display: block;
            margin-top: 0.5rem;
            font-size: 0.8125rem;
            color: var(--text-muted);
        }

        /* Buttons */
        .btn {
            padding: 0.625rem 1.25rem;
            border: none;
            border-radius: var(--radius-sm);
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            white-space: nowrap;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            box-shadow: var(--shadow);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .btn-secondary {
            background: var(--text-secondary);
            color: white;
        }

        .btn-secondary:hover {
            background: var(--text-primary);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .btn-danger {
            background: var(--danger);
            color: white;
        }

        .btn-danger:hover {
            background: #dc2626;
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .btn-success {
            background: var(--success);
            color: white;
        }

        .btn-success:hover {
            background: #059669;
        }

        .form-actions {
            display: flex;
            gap: 0.75rem;
            justify-content: flex-end;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border-light);
        }

        /* Galleries Grid */
        .galleries-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .gallery-card {
            background: var(--bg-card);
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            transition: all 0.3s ease;
            position: relative;
        }

        .gallery-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-xl);
        }

        .gallery-cover {
            width: 100%;
            height: 220px;
            overflow: hidden;
            position: relative;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .gallery-cover::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to bottom, transparent 0%, rgba(0,0,0,0.4) 100%);
        }

        .gallery-cover img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .gallery-card:hover .gallery-cover img {
            transform: scale(1.1);
        }

        .gallery-header {
            padding: 1.5rem;
        }

        .gallery-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }

        .gallery-meta {
            font-size: 0.8125rem;
            color: var(--text-muted);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .gallery-status {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .gallery-status[data-status='1'] {
            background: #d1fae5;
            color: #065f46;
        }

        .gallery-status[data-status='0'] {
            background: #fee2e2;
            color: #991b1b;
        }

        .gallery-description {
            color: var(--text-secondary);
            font-size: 0.875rem;
            line-height: 1.6;
        }

        .gallery-footer {
            padding: 1rem 1.5rem;
            background: var(--bg-hover);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .photo-count {
            font-size: 0.8125rem;
            color: var(--text-muted);
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .photo-count i {
            color: var(--primary);
        }

        .gallery-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .gallery-actions .btn {
            padding: 0.5rem 0.75rem;
            font-size: 0.8125rem;
        }

        /* Photos Section */
        .photos-section {
            background: var(--bg-card);
            border-radius: var(--radius-lg);
            padding: 2rem;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--border);
            margin-top: 2rem;
        }

        .photos-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--border-light);
        }

        .photos-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .photos-title i {
            color: var(--primary);
        }

        .photos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 1.25rem;
        }

        .photo-card {
            border: 1px solid var(--border);
            border-radius: var(--radius);
            overflow: hidden;
            background: var(--bg-card);
            transition: all 0.3s ease;
        }

        .photo-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-md);
        }

        .photo-image {
            width: 100%;
            height: 180px;
            object-fit: cover;
            border-bottom: 1px solid var(--border);
        }

        .photo-info {
            padding: 1rem;
        }

        .photo-title {
            font-weight: 600;
            font-size: 0.9375rem;
            color: var(--text-primary);
            margin-bottom: 0.375rem;
        }

        .photo-caption {
            font-size: 0.8125rem;
            color: var(--text-muted);
            margin-bottom: 0.75rem;
            line-height: 1.5;
        }

        .photo-actions {
            display: flex;
            gap: 0.5rem;
        }

        .photo-actions .btn {
            flex: 1;
            padding: 0.5rem;
            font-size: 0.8125rem;
        }

        .featured-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.25rem 0.625rem;
            border-radius: 12px;
            font-size: 0.6875rem;
            font-weight: 700;
            text-transform: uppercase;
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            color: white;
            margin-bottom: 0.75rem;
            letter-spacing: 0.5px;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(4px);
            overflow: auto;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal-content {
            background: var(--bg-card);
            margin: 3% auto;
            padding: 2rem;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-xl);
            width: 90%;
            max-width: 700px;
            animation: slideUp 0.3s ease;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .close {
            color: var(--text-muted);
            float: right;
            font-size: 2rem;
            font-weight: 300;
            cursor: pointer;
            transition: all 0.3s ease;
            line-height: 1;
        }

        .close:hover {
            color: var(--danger);
            transform: rotate(90deg);
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 0.625rem;
            cursor: pointer;
            user-select: none;
        }

        .checkbox-label input[type="checkbox"] {
            width: 1.25rem;
            height: 1.25rem;
            cursor: pointer;
            accent-color: var(--primary);
        }

        .empty-state {
            grid-column: 1 / -1;
            text-align: center;
            padding: 3rem;
            color: var(--text-muted);
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            display: block;
            opacity: 0.5;
        }

        .empty-state p {
            font-size: 1.125rem;
            font-weight: 500;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .admin-nav {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .dashboard-header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .form-actions {
                flex-direction: column;
            }

            .galleries-grid {
                grid-template-columns: 1fr;
            }

            .photos-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            }

            .gallery-footer {
                flex-direction: column;
                align-items: flex-start;
            }

            .gallery-actions {
                width: 100%;
            }

            .gallery-actions .btn {
                flex: 1;
            }
        }
    </style>
</head>
<body>
    <!-- Admin Header -->
    <div class="admin-header">
        <div class="admin-nav">
            <div class="admin-brand">
                <i class="fas fa-images"></i>
                Photo Galleries
            </div>
            <a href="admin_dashboard.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Dashboard
            </a>
        </div>
    </div>

    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1 class="dashboard-title">Gallery Management</h1>
        </div>

        <!-- Success/Error Messages -->
        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- Add Gallery Form -->
        <div class="form-container">
            <h3><i class="fas fa-plus-circle"></i> Create New Gallery</h3>
            <form method="POST" action="" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add_gallery">
                <input type="hidden" name="MAX_FILE_SIZE" value="5242880">

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="galleryTitle">Gallery Title *</label>
                        <input type="text" id="galleryTitle" name="title" class="form-input" required placeholder="Enter gallery name">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="coverImage">Cover Image *</label>
                        <input type="file" id="coverImage" name="cover_image" accept="image/*" required>
                        <small class="form-text">Max 5MB. Formats: JPG, PNG, GIF</small>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="event_date">Event Date</label>
                        <input type="date" id="event_date" name="event_date" class="form-input">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="location">Location</label>
                        <input type="text" id="location" name="location" class="form-input" placeholder="Event location">
                    </div>
                </div>

                <div class="form-group full-width">
                    <label class="form-label" for="description">Description</label>
                    <textarea id="description" name="description" class="form-textarea" placeholder="Describe the gallery..."></textarea>
                </div>

                <div class="form-actions">
                    <button type="reset" class="btn btn-secondary">
                        <i class="fas fa-undo"></i> Reset
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Create Gallery
                    </button>
                </div>
            </form>
        </div>

        <!-- Galleries Grid -->
        <div class="galleries-grid">
            <?php if (isset($galleries) && !empty($galleries)): ?>
                <?php foreach ($galleries as $gallery): ?>
                    <div class="gallery-card">
                        <?php if (!empty($gallery['cover_image'])): ?>
                            <div class="gallery-cover">
                                <img src="../<?php echo htmlspecialchars($gallery['cover_image']); ?>" alt="<?php echo htmlspecialchars($gallery['name']); ?>">
                            </div>
                        <?php endif; ?>
                        
                        <div class="gallery-header">
                            <h3 class="gallery-title"><?php echo htmlspecialchars($gallery['name']); ?></h3>
                            <div class="gallery-meta">
                                <span class="gallery-status" data-status="<?php echo $gallery['is_active']; ?>">
                                    <?php echo $gallery['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </div>
                            <?php if (!empty($gallery['description'])): ?>
                                <p class="gallery-description">
                                    <?php echo htmlspecialchars(substr($gallery['description'], 0, 120)); ?>
                                    <?php if (strlen($gallery['description']) > 120): ?>...<?php endif; ?>
                                </p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="gallery-footer">
                            <div class="photo-count">
                                <i class="fas fa-images"></i>
                                <span><?php echo $gallery['photo_count']; ?> photos</span>
                            </div>
                            <div class="gallery-actions">
                                <button class="btn btn-primary" onclick="managePhotos(<?php echo $gallery['id']; ?>)" title="Manage Photos">
                                    <i class="fas fa-images"></i> Photos
                                </button>
                                <button class="btn btn-secondary" onclick="editGallery(<?php echo $gallery['id']; ?>)" title="Edit Gallery">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Delete this gallery and all its photos?');">
                                    <input type="hidden" name="action" value="delete_gallery">
                                    <input type="hidden" name="gallery_id" value="<?php echo $gallery['id']; ?>">
                                    <button type="submit" class="btn btn-danger" title="Delete Gallery">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-images"></i>
                    <p>No galleries yet. Create your first gallery above!</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Photos Management Section -->
        <div class="photos-section" id="photosSection" style="display: none;">
            <div class="photos-header">
                <h3 class="photos-title" id="photosGalleryTitle">
                    <i class="fas fa-image"></i>
                    Gallery Photos
                </h3>
                <button class="btn btn-primary" onclick="showAddPhotoForm()">
                    <i class="fas fa-plus"></i> Add Photo
                </button>
            </div>

            <!-- Add Photo Form -->
            <div class="form-container" id="addPhotoForm" style="display: none; margin-bottom: 1.5rem;">
                <h3><i class="fas fa-plus-circle"></i> Add New Photo</h3>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="add_photo">
                    <input type="hidden" name="gallery_id" id="add_photo_gallery_id">

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="photo_title">Photo Title</label>
                            <input type="text" id="photo_title" name="title" class="form-input" placeholder="Photo title">
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="photo_image_path">Image URL *</label>
                            <input type="url" id="photo_image_path" name="image_path" class="form-input" required placeholder="https://example.com/photo.jpg">
                        </div>
                    </div>

                    <div class="form-group full-width">
                        <label class="form-label" for="photo_caption">Caption</label>
                        <input type="text" id="photo_caption" name="caption" class="form-input" placeholder="Brief caption">
                    </div>

                    <div class="form-group full-width">
                        <label class="form-label" for="photo_description">Description</label>
                        <textarea id="photo_description" name="description" class="form-textarea" rows="2" placeholder="Detailed description"></textarea>
                    </div>

                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" id="photo_is_featured" name="is_featured">
                            <span>Featured Photo</span>
                        </label>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="hideAddPhotoForm()">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Add Photo
                        </button>
                    </div>
                </form>
            </div>

            <!-- Photos Grid -->
            <div class="photos-grid" id="photosGrid"></div>
        </div>
    </div>

    <!-- Edit Gallery Modal -->
    <div id="editGalleryModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('editGalleryModal').style.display='none'">&times;</span>
            <h3 style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem;">
                <i class="fas fa-edit" style="color: var(--primary);"></i>
                Edit Gallery
            </h3>
            <form id="editGalleryForm" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="edit_gallery">
                <input type="hidden" name="gallery_id" id="editGalleryId">
                <input type="hidden" name="current_cover" id="currentCover">
                <input type="hidden" name="MAX_FILE_SIZE" value="5242880">
                
                <div class="form-group">
                    <label class="form-label" for="editGalleryTitle">Gallery Title *</label>
                    <input type="text" id="editGalleryTitle" name="title" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="editDescription">Description</label>
                    <textarea id="editDescription" name="description" class="form-textarea" rows="3"></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="editEventDate">Event Date</label>
                        <input type="date" id="editEventDate" name="event_date" class="form-input">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="editLocation">Location</label>
                        <input type="text" id="editLocation" name="location" class="form-input">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Current Cover Image</label>
                    <div id="editCoverPreview" style="margin-bottom: 1rem;"></div>
                    
                    <label class="form-label" for="editCoverImage">Change Cover Image</label>
                    <input type="file" id="editCoverImage" name="cover_image" accept="image/*">
                    <small class="form-text">Leave empty to keep current. Max 5MB.</small>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" id="editIsActive" name="is_active" value="1" checked>
                        <span>Active</span>
                    </label>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('editGalleryModal').style.display='none'">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let currentGalleryId = null;

        function managePhotos(galleryId) {
            currentGalleryId = galleryId;
            const galleries = <?php echo json_encode($galleries); ?>;
            const gallery = galleries.find(g => g.id == galleryId);

            if (gallery) {
                document.getElementById('photosGalleryTitle').innerHTML = `<i class="fas fa-image"></i>${gallery.name} - Photos`;
                document.getElementById('add_photo_gallery_id').value = galleryId;
                document.getElementById('photosSection').style.display = 'block';
                document.getElementById('photosSection').scrollIntoView({ behavior: 'smooth' });

                const photos = <?php echo json_encode($photos); ?>;
                const galleryPhotos = photos.filter(p => p.gallery_id == galleryId);
                displayPhotos(galleryPhotos);
            }
        }

        function displayPhotos(photos) {
            const photosGrid = document.getElementById('photosGrid');

            if (photos.length === 0) {
                photosGrid.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-image"></i>
                        <p>No photos yet. Add some photos above!</p>
                    </div>
                `;
                return;
            }

            photosGrid.innerHTML = photos.map(photo => `
                <div class="photo-card">
                    <img src="${photo.image_path}" alt="${photo.title || 'Photo'}" class="photo-image" onerror="this.src='https://via.placeholder.com/220x180?text=No+Image'">
                    <div class="photo-info">
                        ${photo.is_featured ? '<span class="featured-badge"><i class="fas fa-star"></i> Featured</span>' : ''}
                        <div class="photo-title">${photo.title || 'Untitled'}</div>
                        ${photo.caption ? `<div class="photo-caption">${photo.caption}</div>` : ''}
                        <div class="photo-actions">
                            <button class="btn btn-secondary" onclick="editPhoto(${photo.id})" title="Edit Photo">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Delete this photo?');">
                                <input type="hidden" name="action" value="delete_photo">
                                <input type="hidden" name="photo_id" value="${photo.id}">
                                <button type="submit" class="btn btn-danger" title="Delete Photo">
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
            fetch(`get_gallery.php?id=${galleryId}`)
                .then(response => response.json())
                .then(gallery => {
                    document.getElementById('editGalleryId').value = gallery.id;
                    document.getElementById('editGalleryTitle').value = gallery.name;
                    document.getElementById('editDescription').value = gallery.description || '';
                    document.getElementById('editEventDate').value = gallery.event_date || '';
                    document.getElementById('editLocation').value = gallery.location || '';
                    document.getElementById('editIsActive').checked = gallery.is_active == 1;
                    document.getElementById('currentCover').value = gallery.cover_image || '';
                    
                    const coverPreview = document.getElementById('editCoverPreview');
                    if (gallery.cover_image) {
                        coverPreview.innerHTML = `
                            <img src="../${gallery.cover_image}" alt="Current cover" style="max-width: 100%; border-radius: var(--radius-sm); box-shadow: var(--shadow);">
                        `;
                    } else {
                        coverPreview.innerHTML = '<div style="color: var(--text-muted); font-size: 0.875rem;">No cover image</div>';
                    }
                    
                    document.getElementById('editGalleryModal').style.display = 'block';
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading gallery data');
                });
        }

        function editPhoto(photoId) {
            const photos = <?php echo json_encode($photos); ?>;
            const photo = photos.find(p => p.id == photoId);

            if (photo) {
                // Create edit form dynamically or populate existing modal
                alert('Photo editing feature - ID: ' + photoId);
            }
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('editGalleryModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>