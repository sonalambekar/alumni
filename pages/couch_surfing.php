<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] <= 0) {
    header("Location: /alumni/login.php");
    exit();
}

// Include database config
require_once '../includes/db_config.php';

// Handle form submission for new listing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $location = $_POST['location'];
    $available_from = $_POST['available_from'];
    $available_to = $_POST['available_to'];
    $max_guests = $_POST['max_guests'];
    $amenities = $_POST['amenities'] ?? '';
    $rules = $_POST['rules'] ?? '';
    $author_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("INSERT INTO couch_listings (title, description, location, available_from, available_to, max_guests, amenities, rules, author_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$title, $description, $location, $available_from, $available_to, $max_guests, $amenities, $rules, $author_id]);

    // Redirect or show success message
    header("Location: couch_surfing.php?success=1");
    exit();
}

// Fetch listings from database
$listings = [];
try {
    $stmt = $pdo->query("SELECT cl.*, u.full_name FROM couch_listings cl JOIN users u ON cl.author_id = u.id WHERE cl.is_active = 1 ORDER BY cl.created_at DESC");
    $listings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Fallback to sample data if table doesn't exist
    $listings = [
        [
            'title' => 'Cozy Apartment in Downtown',
            'location' => 'New York, USA',
            'available_from' => '2025-12-01',
            'available_to' => '2025-12-15',
            'description' => 'Comfortable 1-bedroom apartment with great views. Perfect for short stays.',
            'max_guests' => 2,
            'full_name' => 'John Doe'
        ],
        [
            'title' => 'Spacious Home Near Campus',
            'location' => 'Bengaluru, India',
            'available_from' => '2026-01-10',
            'available_to' => '2026-01-20',
            'description' => 'Large family home with garden. Close to university and amenities.',
            'max_guests' => 4,
            'full_name' => 'Jane Smith'
        ]
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Couch Surfing - Alumni Connect</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #5b1f1f;
            --secondary-color: #ecc35c;
            --bg-light: #f8f9fa;
            --text-color: #333;
            --light-gray: #f0f0f0;
            --border-color: #e0e0e0;
        }

        .couch-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            font-family: 'Poppins', sans-serif;
        }

        .page-header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px 0;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .page-header h1 {
            margin: 0;
            font-size: 2.5rem;
            font-weight: 700;
        }

        .page-header p {
            margin: 10px 0 0;
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .tabs {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 2px solid var(--border-color);
        }

        .tab {
            padding: 15px 25px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 500;
            color: var(--text-color);
            transition: all 0.3s ease;
            border-bottom: 3px solid transparent;
        }

        .tab.active {
            color: var(--primary-color);
            border-bottom-color: var(--primary-color);
            background: rgba(91, 31, 31, 0.05);
        }

        .tab:hover {
            background: var(--bg-light);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .search-section {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
            border: 1px solid var(--border-color);
        }

        .search-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            align-items: end;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--primary-color);
        }

        .form-group input, .form-group select {
            padding: 12px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(91, 31, 31, 0.1);
        }

        .btn {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn:hover {
            background: var(--secondary-color);
            color: var(--primary-color);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .listings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
        }

        .listing-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            border: 1px solid var(--border-color);
        }

        .listing-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
        }

        .listing-image {
            height: 200px;
            background: var(--light-gray);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #666;
            font-size: 3rem;
        }

        .listing-content {
            padding: 20px;
        }

        .listing-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 10px;
        }

        .listing-location {
            color: #666;
            margin-bottom: 10px;
            font-size: 0.9rem;
        }

        .listing-dates {
            font-size: 0.9rem;
            color: #555;
            margin-bottom: 15px;
        }

        .listing-description {
            color: #666;
            line-height: 1.5;
            margin-bottom: 15px;
        }

        .listing-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .listing-guests {
            font-size: 0.9rem;
            color: #666;
        }

        .post-form {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid var(--border-color);
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-full {
            grid-column: 1 / -1;
        }

        textarea {
            resize: vertical;
            min-height: 100px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .page-header h1 {
                font-size: 2rem;
            }

            .tabs {
                flex-direction: column;
            }

            .tab {
                text-align: center;
            }

            .search-form {
                grid-template-columns: 1fr;
            }

            .listings-grid {
                grid-template-columns: 1fr;
            }

            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include '../sidebar.php'; ?>

    <div class="main-content" id="mainContent">
        <div class="couch-container">
            <div class="page-header">
                <h1>Couch Surfing</h1>
                <p>Connect with fellow alumni for accommodation during your travels</p>
            </div>

            <div class="tabs">
                <button class="tab active" onclick="showTab('search')">Search & Browse</button>
                <button class="tab" onclick="showTab('offers')">My Offers</button>
                <button class="tab" onclick="showTab('requests')">My Requests</button>
                <button class="tab" onclick="showTab('post')">Post New</button>
            </div>

            <!-- Search Tab -->
            <div id="search" class="tab-content active">
                <div class="search-section">
                    <h2>Find Accommodation</h2>
                    <form class="search-form" method="GET" action="">
                        <div class="form-group">
                            <label for="location">Location</label>
                            <input type="text" id="location" name="location" placeholder="Enter city or region">
                        </div>
                        <div class="form-group">
                            <label for="checkin">Check-in Date</label>
                            <input type="date" id="checkin" name="checkin">
                        </div>
                        <div class="form-group">
                            <label for="checkout">Check-out Date</label>
                            <input type="date" id="checkout" name="checkout">
                        </div>
                        <div class="form-group">
                            <label for="guests">Guests</label>
                            <select id="guests" name="guests">
                                <option value="1">1 Guest</option>
                                <option value="2">2 Guests</option>
                                <option value="3">3 Guests</option>
                                <option value="4">4+ Guests</option>
                            </select>
                        </div>
                        <button type="submit" class="btn">Search</button>
                    </form>
                </div>

                <h2>Available Accommodations</h2>
                <div class="listings-grid" id="listingsGrid">
                    <?php
                    // Sample listings (in a real app, fetch from database)
                    $sampleListings = [
                        [
                            'title' => 'Cozy Apartment in Downtown',
                            'location' => 'New York, USA',
                            'dates' => 'Available: Dec 1-15, 2025',
                            'description' => 'Comfortable 1-bedroom apartment with great views. Perfect for short stays.',
                            'guests' => 'Max 2 guests',
                            'image' => '🏠'
                        ],
                        [
                            'title' => 'Spacious Home Near Campus',
                            'location' => 'Bengaluru, India',
                            'dates' => 'Available: Jan 10-20, 2026',
                            'description' => 'Large family home with garden. Close to university and amenities.',
                            'guests' => 'Max 4 guests',
                            'image' => '🏡'
                        ],
                        [
                            'title' => 'Modern Studio in City Center',
                            'location' => 'London, UK',
                            'dates' => 'Available: Feb 5-12, 2026',
                            'description' => 'Stylish studio apartment with all modern amenities.',
                            'guests' => 'Max 1 guest',
                            'image' => '🏢'
                        ]
                    ];

                    foreach ($sampleListings as $listing) {
                        echo "
                        <div class='listing-card'>
                            <div class='listing-image'>{$listing['image']}</div>
                            <div class='listing-content'>
                                <div class='listing-title'>{$listing['title']}</div>
                                <div class='listing-location'><i class='fas fa-map-marker-alt'></i> {$listing['location']}</div>
                                <div class='listing-dates'>{$listing['dates']}</div>
                                <div class='listing-description'>{$listing['description']}</div>
                                <div class='listing-footer'>
                                    <div class='listing-guests'>{$listing['guests']}</div>
                                    <button class='btn' onclick='contactHost()'>Contact Host</button>
                                </div>
                            </div>
                        </div>";
                    }
                    ?>
                </div>
            </div>

            <!-- Post New Tab -->
            <div id="post" class="tab-content">
                <div class="post-form">
                    <h2>Post New Accommodation Offer</h2>
                    <form method="POST" action="">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="title">Title</label>
                                <input type="text" id="title" name="title" required placeholder="e.g., Cozy Apartment Downtown">
                            </div>
                            <div class="form-group">
                                <label for="location">Location</label>
                                <input type="text" id="location" name="location" required placeholder="City, Country">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="available_from">Available From</label>
                                <input type="date" id="available_from" name="available_from" required>
                            </div>
                            <div class="form-group">
                                <label for="available_to">Available To</label>
                                <input type="date" id="available_to" name="available_to" required>
                            </div>
                            <div class="form-group">
                                <label for="max_guests">Max Guests</label>
                                <select id="max_guests" name="max_guests" required>
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                    <option value="4">4+</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row form-full">
                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea id="description" name="description" required placeholder="Describe your accommodation, amenities, and any rules..."></textarea>
                            </div>
                        </div>
                        <div class="form-row form-full">
                            <div class="form-group">
                                <label for="amenities">Amenities (optional)</label>
                                <input type="text" id="amenities" name="amenities" placeholder="WiFi, Kitchen, Parking, etc.">
                            </div>
                        </div>
                        <div class="form-row form-full">
                            <div class="form-group">
                                <label for="rules">House Rules (optional)</label>
                                <textarea id="rules" name="rules" placeholder="No smoking, quiet hours, etc."></textarea>
                            </div>
                        </div>
                        <button type="submit" class="btn">Post Listing</button>
                    </form>
                </div>
            </div>

            <!-- My Offers and Requests tabs can be populated with database queries -->
            <div id="offers" class="tab-content">
                <h2>My Accommodation Offers</h2>
                <p>You haven't posted any offers yet. <a href="#" onclick="showTab('post')">Post your first offer!</a></p>
            </div>

            <div id="requests" class="tab-content">
                <h2>My Accommodation Requests</h2>
                <p>You haven't made any requests yet. <a href="#" onclick="showTab('search')">Browse available options!</a></p>
            </div>
        </div>
    </div>

    <script>
        function showTab(tabName) {
            // Hide all tab contents
            const contents = document.querySelectorAll('.tab-content');
            contents.forEach(content => content.classList.remove('active'));

            // Remove active class from all tabs
            const tabs = document.querySelectorAll('.tab');
            tabs.forEach(tab => tab.classList.remove('active'));

            // Show selected tab content
            document.getElementById(tabName).classList.add('active');

            // Add active class to clicked tab
            event.target.classList.add('active');
        }

        function contactHost(listingId) {
            alert('Contact functionality would connect you with the host via email or messaging. Listing ID: ' + listingId);
        }
    </script>
</body>
</html>
