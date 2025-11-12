<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] <= 0) {
    // User is not logged in, redirect to login page
    header("Location: /alumni/login.php");
    exit();
}

// If user is logged in, check if they're a director and redirect to admin dashboard
if (isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0) {
    require_once 'includes/db_config.php';
    $stmt = $pdo->prepare("SELECT is_director FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if ($user && $user['is_director'] == 1) {
        // Set admin role if not already set
        if (!isset($_SESSION['role'])) {
            $_SESSION['role'] = 'admin';
        }
        header("Location: /alumni/admin/admin_dashboard.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alumni Connect - Home</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
            crossorigin=""></script>
    <!-- Map Initialization -->
    <script src="assets/js/map-init.js" defer></script>
    <style>
        /* Hover effects for feature cards */
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.12) !important;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .features-grid {
                grid-template-columns: 1fr !important;
                padding: 0 10px !important;
            }
            
            .feature-card {
                margin-bottom: 20px;
            }
        }
        
        @media (min-width: 769px) and (max-width: 1024px) {
            .features-grid {
                grid-template-columns: repeat(2, 1fr) !important;
            }
        }
        
        .groups-grid {
            display: flex;
            overflow-x: auto;
            gap: 25px;
            padding: 20px 0;
            scrollbar-width: none; /* Firefox */
            -ms-overflow-style: none; /* IE and Edge */
            scroll-behavior: smooth;
        }
        
        .groups-grid::-webkit-scrollbar {
            display: none; /* Hide scrollbar for Chrome, Safari and Opera */
        }
        
        .groups-grid .group-card {
            flex: 0 0 300px;
            margin-bottom: 10px;
        }
    </style>
    <!-- Leaflet CSS (Free OpenStreetMap library) -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
          crossorigin=""/>
    <style>
        /* Map container styles */
        .map-section {
            padding: 40px 0;
            background-color: #f9f9f9;
        }
        .map-container {
            width: 100%;
            height: 600px;  /* Increased height for better visibility */
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 6px 18px rgba(0,0,0,0.1);
            margin: 20px auto;
            max-width: 1200px;
            position: relative;
            border: 1px solid #e0e0e0;
        }
        #map {
            width: 100%;
            height: 100%;
            min-height: 600px;  /* Ensure minimum height */
            background-color: #e8f4f8;  /* Light blue background when loading */
        }
        /* Add loading indicator */
        .map-loading {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            background: rgba(255,255,255,0.9);
            z-index: 1000;
            font-size: 1.2rem;
            color: #333;
        }
    </style>
</head>
<body>
    <?php
    // Note: User authentication is handled by the login requirement at the top
    
    // Display success/error messages if any
    if (isset($_SESSION['success']) || isset($_SESSION['error'])) {
        echo '<div class="message-container" style="position: fixed; top: 20px; left: 50%; transform: translateX(-50%); z-index: 1000; width: 80%; max-width: 600px;">';
        if (isset($_SESSION['success'])) {
            echo '<div class="alert alert-success" style="background-color: #d4edda; color: #155724; padding: 15px; border: 1px solid #c3e6cb; border-radius: 4px; margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center;">';
            echo '<span>' . htmlspecialchars($_SESSION['success']) . '</span>';
            echo '<button type="button" class="close-message" style="background: none; border: none; font-size: 20px; cursor: pointer; color: #155724;">&times;</button>';
            echo '</div>';
            unset($_SESSION['success']);
        }
        if (isset($_SESSION['error'])) {
            echo '<div class="alert alert-danger" style="background-color: #f8d7da; color: #721c24; padding: 15px; border: 1px solid #f5c6cb; border-radius: 4px; margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center;">';
            echo '<span>' . htmlspecialchars($_SESSION['error']) . '</span>';
            echo '<button type="button" class="close-message" style="background: none; border: none; font-size: 20px; cursor: pointer; color: #721c24;">&times;</button>';
            echo '</div>';
            unset($_SESSION['error']);
        }
        echo '</div>';
        
        // Add JavaScript to handle message dismissal
        echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            const closeButtons = document.querySelectorAll(".close-message");
            closeButtons.forEach(button => {
                button.addEventListener("click", function() {
                    this.closest(".alert").style.display = "none";
                });
            });
            
            // Auto-hide messages after 5 seconds
            setTimeout(() => {
                const messages = document.querySelectorAll(".alert");
                messages.forEach(msg => {
                    msg.style.transition = "opacity 0.5s";
                    msg.style.opacity = "0";
                    setTimeout(() => {
                        msg.style.display = "none";
                    }, 500);
                });
            }, 5000);
        });
        </script>';
    }
    ?>

    <!-- Fixed Action Buttons -->
    <div class="fixed-actions" style="position: fixed; top: 20px; right: 20px; z-index: 1000; display: flex; gap: 10px; background: white; padding: 5px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
        <button id="locationBtn" style="background: #5b1f1f; color: white; border: none; padding: 10px 16px; border-radius: 8px; font-size: 14px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; transition: background 0.3s ease;" onmouseover="this.style.background='#4a1919'" onmouseout="this.style.background='#5b1f1f'">
            <i class="fas fa-map-marker-alt"></i> Share Location
        </button>
        <a href="/alumni/logout.php" style="background: #5b1f1f; color: white; border: none; padding: 10px 16px; border-radius: 8px; font-size: 14px; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; transition: background 0.3s ease;" onmouseover="this.style.background='#4a1919'" onmouseout="this.style.background='#5b1f1f'">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
    
    <script>
    document.getElementById('locationBtn').addEventListener('click', function() {
        const btn = this;
        const originalText = btn.innerHTML;
        
        // Show loading state
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Getting location...';
        
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    const { latitude, longitude } = position.coords;
                    
                    // Update button text
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
                    
                    // Send location to server
                    fetch('save_location.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `latitude=${encodeURIComponent(latitude)}&longitude=${encodeURIComponent(longitude)}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Show success message
                            btn.innerHTML = '<i class="fas fa-check-circle"></i> Location Saved';
                            setTimeout(() => {
                                btn.innerHTML = originalText;
                                btn.disabled = false;
                            }, 2000);
                            
                            console.log('Location saved:', data);
                            // Show a more user-friendly notification
                            const notification = document.createElement('div');
                            notification.className = 'location-notification';
                            
                            // Safely format coordinates
                            const lat = data.latitude || position.coords.latitude;
                            const lng = data.longitude || position.coords.longitude;
                            const formattedLat = typeof lat === 'number' ? lat.toFixed(4) : 'N/A';
                            const formattedLng = typeof lng === 'number' ? lng.toFixed(4) : 'N/A';
                            
                            notification.innerHTML = `
                                <i class="fas fa-check-circle"></i>
                                <span>Location updated successfully!</span>
                                <small>(${formattedLat}, ${formattedLng})</small>
                            `;
                            document.body.appendChild(notification);
                            
                            // Remove notification after 3 seconds
                            setTimeout(() => {
                                notification.style.opacity = '0';
                                setTimeout(() => notification.remove(), 300);
                            }, 5000);
                        } else {
                            throw new Error(data.message || 'Failed to save location');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        btn.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Error';
                        setTimeout(() => {
                            btn.innerHTML = originalText;
                            btn.disabled = false;
                        }, 2000);
                        
                        alert(`Error: ${error.message || 'Failed to save location. Please try again.'}`);
                    });
                },
                function(error) {
                    let errorMessage = 'Error getting location: ';
                    switch(error.code) {
                        case error.PERMISSION_DENIED:
                            errorMessage = 'Location access was denied. Please enable location services and try again.';
                            break;
                        case error.POSITION_UNAVAILABLE:
                            errorMessage = 'Location information is currently unavailable. Please check your connection and try again.';
                            break;
                        case error.TIMEOUT:
                            errorMessage = 'The request to get your location timed out. Please try again.';
                            break;
                        default:
                            errorMessage = 'An unknown error occurred while getting your location.';
                    }
                    
                    btn.innerHTML = '<i class="fas fa-exclamation-circle"></i> Error';
                    setTimeout(() => {
                        btn.innerHTML = originalText;
                        btn.disabled = false;
                    }, 2000);
                    
                    alert(errorMessage);
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,  // 10 seconds
                    maximumAge: 0     // Force fresh location
                }
            );
        } else {
            btn.innerHTML = originalText;
            btn.disabled = false;
            alert('Geolocation is not supported by your browser. Please try a different browser.');
        }
    });
    </script>

    <?php include 'sidebar.php'; ?>

    <div class="main-content" id="mainContent">
        <!-- Hero Section -->
        <section class="hero-section">
            <div class="hero-image">
                <img src="assets/images/Generated Image October 17, 2025 - 11_10AM.png" alt="Alumni Event">
            </div>
            <div class="hero-content">
                <h1 style="font-size: 36px;">Welcome to <span>Gems of GM </span>- Alumni Portal of GM Group of Institutions</h1>
                <p>Welcome to the Gems of GM platform, where memories meet opportunities. Our vibrant community brings together graduates from across the globe, fostering connections that transcend time and distance.</p>
                <p>Whether you're looking to reconnect with old friends, mentor the next generation, or explore new career opportunities, our platform provides the tools and resources to help you stay engaged with your alma mater.</p>
                <p>Join thousands of alumni who are making a difference in their communities and industries worldwide.</p>
            </div>
        </section>

        <!-- Vision & Stats Section -->
        <section class="vision-section">
            <h2>Our Vision</h2>
            <p>To create a thriving global community of alumni who inspire, support, and empower each other while contributing to the growth and excellence of GM Group of Institutions.</p>
            
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-number" data-target="15000" data-suffix="+">0</div>
                    <div class="stat-label">Active Members</div>
                </div>
                <div class="stat-card">
                    <div style="font-size: 2.5em; font-weight: bold; line-height: 1.2; color: var(--primary-color);">2002</div>
                    <div class="stat-label">We are since</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" data-target="85" data-suffix="+">0</div>
                    <div class="stat-label">Cities Worldwide</div>
                </div>
            </div>
        </section>

        <!-- Alumni Highlights Section -->
        <section class="alumni-highlights">
            <div class="section-header">
                <h2>Meet Our Recent Alumni</h2>
                <p>Celebrating the achievements of our distinguished alumni community</p>
            </div>
            
            <div class="alumni-grid">
                <div class="alumni-card" onclick="location.href='pages/directory.php'">
                    <div class="alumni-avatar">
                        <img src="assets/images/medals/WhatsApp%20Image%202025-11-07%20at%2010.09.45_92cc285f.jpg" alt="Alumni Achievement 1">
                    </div>
                    <div class="alumni-name">Pavan Varahad V</div>
                </div>
                <div class="alumni-card" onclick="location.href='pages/directory.php'">
                    <div class="alumni-avatar">
                        <img src="assets/images/medals/WhatsApp%20Image%202025-11-07%20at%2014.40.27_3afb5fa0.jpg" alt="Alumni Achievement 2">
                    </div>
                    <div class="alumni-name">Khushi Patil</div>
                </div>
                <div class="alumni-card" onclick="location.href='pages/directory.php'">
                    <div class="alumni-avatar">
                        <img src="assets/images/medals/WhatsApp%20Image%202025-11-07%20at%2014.40.47_5aec4de6.jpg" alt="Alumni Achievement 3">
                    </div>
                    <div class="alumni-name">Md Faizan Khan</div>
                </div>
                <div class="alumni-card" onclick="location.href='pages/directory.php'">
                    <div class="alumni-avatar">
                        <img src="assets/images/medals/Abhishek%20Belagavi.png" alt="Alumni Achievement 4">
                    </div>
                    <div class="alumni-name">Abhishek Belagavi</div>
                </div>
                <div class="alumni-card" onclick="location.href='pages/directory.php'">
                    <div class="alumni-avatar">
                        <img src="assets/images/medals/Asrar%20S.%20B.png" alt="Alumni Achievement 5">
                    </div>
                    <div class="alumni-name">Asrar S. B</div>
                </div>
                <div class="alumni-card" onclick="location.href='pages/directory.php'">
                    <div class="alumni-avatar">
                        <img src="assets/images/medals/pranav.png" alt="Alumni Achievement 6">
                    </div>
                    <div class="alumni-name">Pranav V</div>
                </div>
            </div>
        </section>

        <!-- Proud Alumni Section -->
        <section class="alumni-highlights" style="margin-top: -40px;">
            <div class="section-header">
                <h2>Meet Our Proud Alumni</h2>
                <p>Celebrating the outstanding achievements of our distinguished alumni community</p>
            </div>
            
            <div class="alumni-grid">
                <div class="alumni-card" onclick="location.href='pages/directory.php'">
                    <div class="alumni-avatar">
                        <img src="assets/images/medals/PRAJWAL%20NAYAK%20.png" alt="Prajwal Nayak">
                    </div>
                    <div class="alumni-name">Prajwal Nayak</div>
                </div>
                <div class="alumni-card" onclick="location.href='pages/directory.php'">
                    <div class="alumni-avatar">
                        <img src="assets/images/medals/UDAY%20V%20HARIHAR%20.png" alt="Uday V Harihar">
                    </div>
                    <div class="alumni-name">Uday V Harihar</div>
                </div>
                <div class="alumni-card" onclick="location.href='pages/directory.php'">
                    <div class="alumni-avatar">
                        <img src="assets/images/medals/SHREEGANESHA%20G%20J%20.png" alt="Shreeganesha G J">
                    </div>
                    <div class="alumni-name">Shreeganesha G J</div>
                </div>
                <div class="alumni-card" onclick="location.href='pages/directory.php'">
                    <div class="alumni-avatar">
                        <img src="assets/images/medals/KRUSHI%20D%20.png" alt="Krushi D">
                    </div>
                    <div class="alumni-name">Krushi D</div>
                </div>
                <div class="alumni-card" onclick="location.href='pages/directory.php'">
                    <div class="alumni-avatar">
                        <img src="assets/images/medals/SHEETAL%20S%20V%20.png" alt="Sheetal S V">
                    </div>
                    <div class="alumni-name">Sheetal S V</div>
                </div>
                <div class="alumni-card" onclick="location.href='pages/directory.php'">
                    <div class="alumni-avatar">
                        <img src="assets/images/medals/PRAVEEN%20D%20.png" alt="Praveen D">
                    </div>
                    <div class="alumni-name">Praveen D</div>
                </div>
            </div>
        </section>

        <!-- Alumni Groups Section -->
        <section class="alumni-groups">
            <div class="section-header">
                <h2>Alumni Groups</h2>
                <p>Connect with like-minded alumni through our special interest groups</p>
            </div>
            
            <div class="groups-scroll-container" style="position: relative;">
                <div class="groups-grid">
                <?php
                // Include database configuration
                require_once 'includes/db_config.php';
                
                try {
                    // Fetch all active interest groups
                    $stmt = $pdo->query("
                        SELECT * FROM interest_groups 
                        WHERE is_active = 1 
                        ORDER BY created_at DESC
                    ");
                    $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    // Default image if group_image is not set
                    $defaultImages = [
                        'https://images.unsplash.com/photo-1559136555-9303baea8ebd?w=600&h=400&fit=crop',
                        'https://images.unsplash.com/photo-1451187580459-43490279c0fa?w=600&h=400&fit=crop',
                        'https://images.unsplash.com/photo-1469571486292-0ba58a3f068b?w=600&h=400&fit=crop'
                    ];
                    $imageIndex = 0;
                    
                    foreach ($groups as $group):
                        $groupImage = !empty($group['group_image']) ? $group['group_image'] : $defaultImages[$imageIndex % count($defaultImages)];
                        $imageIndex++;
                ?>
                    <div class="group-card">
                        <div class="group-image">
                            <img src="<?php echo htmlspecialchars($groupImage); ?>" alt="<?php echo htmlspecialchars($group['name']); ?>">
                        </div>
                        <div class="group-content">
                            <h3><?php echo htmlspecialchars($group['name']); ?></h3>
                            <p><?php echo htmlspecialchars($group['description']); ?></p>
                            <button class="btn" onclick="event.stopPropagation(); showGroupMembers(<?php echo $group['id']; ?>, '<?php echo htmlspecialchars(addslashes($group['name'])); ?>')">View Members</button>
                        </div>
                    </div>
                <?php 
                    endforeach; 
                } catch (PDOException $e) {
                    // If there's an error, show a message but don't break the page
                    echo '<!-- Error loading groups: ' . htmlspecialchars($e->getMessage()) . ' -->';
                    // You might want to log this error in a real application
                }
                ?>
            </div>
        </section>

        <!-- Chapters Section -->
        <section class="chapters-section">
            <div class="section-header">
                <h2>Regional Chapters</h2>
                <p>Connect with alumni in your city or region</p>
            </div>
            
            <div class="chapters-grid">
                <div class="chapter-card">
                    <div class="chapter-icon">🇮🇳</div>
                    <h3>Bengaluru Chapter</h3>
                    <p>2,500+ Members</p>
                    <button class="btn btn-secondary">View Members</button>
                </div>
                
                <div class="chapter-card">
                    <div class="chapter-icon">🇮🇳</div>
                    <h3>Delhi NCR Chapter</h3>
                    <p>3,200+ Members</p>
                    <button class="btn btn-secondary">View Members</button>
                </div>
                
                <div class="chapter-card">
                    <div class="chapter-icon">🇺🇸</div>
                    <h3>USA Chapter</h3>
                    <p>1,800+ Members</p>
                    <button class="btn btn-secondary">View Members</button>
                </div>
                
                <div class="chapter-card">
                    <div class="chapter-icon">🇬🇧</div>
                    <h3>UK & Europe Chapter</h3>
                    <p>1,200+ Members</p>
                    <button class="btn btn-secondary">View Members</button>
                </div>

                <div class="chapter-card">
                    <div class="chapter-icon">🇸🇬</div>
                    <h3>Asia-Pacific Chapter</h3>
                    <p>950+ Members</p>
                    <button class="btn btn-secondary">View Members</button>
                </div>

                <div class="chapter-card">
                    <div class="chapter-icon">🇦🇪</div>
                    <h3>Middle East & Africa Chapter</h3>
                    <p>680+ Members</p>
                    <button class="btn btn-secondary">View Members</button>
                </div>
            </div>
        </section>

        <!-- Worldwide Alumni Map -->
        <section class="map-section">
            <div class="section-header">
                <h2>Our Alumni Around the World</h2>
                <p>Explore where our alumni are making an impact globally</p>
            </div>
            
            <div class="map-container">
                <div class="map-loading">
                    <div style="text-align: center;">
                        <div style="font-size: 2rem; margin-bottom: 10px;">
                            <i class="fas fa-spinner fa-spin"></i>
                        </div>
                        <p>Loading map...</p>
                    </div>
                </div>
                <div id="map"></div>
            </div>
        </section>

        <!-- Alumni Services Section -->
        <section class="services-section" style="padding: 40px 0; background-color: #f9f9f9; overflow: hidden;">
            <div class="container" style="max-width: 100%; margin: 0 auto; padding: 0 20px;">
                <div class="section-header" style="text-align: center; margin-bottom: 30px;">
                    <h2 style="color: #5b1f1f; font-size: 2rem; margin-bottom: 10px;">Alumni Services</h2>
                    <p style="color: #666; font-size: 1.1rem; max-width: 700px; margin: 0 auto;">Explore our range of services designed to support and connect our alumni community</p>
                </div>
                
                <div class="services-scroll-container" style="overflow-x: auto; padding: 20px 0 40px; -webkit-overflow-scrolling: touch; -ms-overflow-style: none; scrollbar-width: none;">
                    <div class="services-scroll" style="display: flex; gap: 25px; padding: 0 15px; width: max-content; min-width: 100%;">
                        <!-- Mentor Card -->
                        <div class="service-card" style="flex: 0 0 300px; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.08); transition: transform 0.3s ease, box-shadow 0.3s ease;">
                            <div class="card-icon" style="background-color: #f0f7ff; padding: 25px; text-align: center;">
                                <i class="fas fa-chalkboard-teacher" style="font-size: 2.5rem; color: #1e88e5;"></i>
                            </div>
                            <div class="card-content" style="padding: 25px;">
                                <h3 style="color: #333; margin-bottom: 15px; font-size: 1.4rem;">Mentor</h3>
                                <p style="color: #666; line-height: 1.6; margin-bottom: 20px;">Share your knowledge and guide current students or recent graduates in their career paths.</p>
                                <a href="pages/mentorship.php" class="card-link" style="color: #1e88e5; font-weight: 500; text-decoration: none; display: inline-flex; align-items: center; gap: 5px;">
                                    Learn more <i class="fas fa-arrow-right" style="font-size: 0.8rem;"></i>
                                </a>
                            </div>
                        </div>

                        <!-- Internships Card -->
                        <div class="service-card" style="flex: 0 0 300px; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.08); transition: transform 0.3s ease, box-shadow 0.3s ease; cursor: pointer;" onclick="window.location.href='pages/jobs.php'" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 8px 25px rgba(0,0,0,0.15)';" onmouseout="this.style.transform=''; this.style.boxShadow='0 4px 15px rgba(0,0,0,0.08)';">
                            <div class="card-icon" style="background-color: #e8f5e9; padding: 25px; text-align: center;">
                                <i class="fas fa-briefcase" style="font-size: 2.5rem; color: #43a047;"></i>
                            </div>
                            <div class="card-content" style="padding: 25px;">
                                <h3 style="color: #333; margin-bottom: 15px; font-size: 1.4rem;">Internships</h3>
                                <p style="color: #666; line-height: 1.6; margin-bottom: 20px;">Discover exciting internship opportunities from our network of industry partners.</p>
                                <a href="pages/jobs.php" class="card-link" style="color: #43a047; font-weight: 500; text-decoration: none; display: inline-flex; align-items: center; gap: 5px;">
                                    Browse internships <i class="fas fa-arrow-right" style="font-size: 0.8rem;"></i>
                                </a>
                            </div>
                        </div>

                        <!-- Job Vacancies Card -->
                        <div class="service-card" style="flex: 0 0 300px; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.08); transition: transform 0.3s ease, box-shadow 0.3s ease; cursor: pointer;" onclick="window.location.href='pages/jobs.php'" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 8px 25px rgba(0,0,0,0.15)';" onmouseout="this.style.transform=''; this.style.boxShadow='0 4px 15px rgba(0,0,0,0.08)';">
                            <div class="card-icon" style="background-color: #fff3e0; padding: 25px; text-align: center;">
                                <i class="fas fa-search-dollar" style="font-size: 2.5rem; color: #ff9800;"></i>
                            </div>
                            <div class="card-content" style="padding: 25px;">
                                <h3 style="color: #333; margin-bottom: 15px; font-size: 1.4rem;">Job Vacancies</h3>
                                <p style="color: #666; line-height: 1.6; margin-bottom: 20px;">Explore job opportunities posted by alumni and partner companies.</p>
                                <a href="pages/jobs.php" class="card-link" style="color: #ff9800; font-weight: 500; text-decoration: none; display: inline-flex; align-items: center; gap: 5px;">
                                    View jobs <i class="fas fa-arrow-right" style="font-size: 0.8rem;"></i>
                                </a>
                            </div>
                        </div>

                        <!-- Share Experience Card -->
                        

                        <!-- Institution Scholarship Card -->
                        <div class="service-card" style="flex: 0 0 300px; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.08); transition: transform 0.3s ease, box-shadow 0.3s ease;">
                            <div class="card-icon" style="background-color: #e8f5e9; padding: 25px; text-align: center;">
                                <i class="fas fa-graduation-cap" style="font-size: 2.5rem; color: #2e7d32;"></i>
                            </div>
                            <div class="card-content" style="padding: 25px;">
                                <h3 style="color: #333; margin-bottom: 15px; font-size: 1.4rem;">Institute Scholarship</h3>
                                <p style="color: #666; line-height: 1.6; margin-bottom: 20px;">Support the next generation of students by contributing to our scholarship fund.</p>
                                <a href="/alumni/pages/institute_scholarship.php" class="card-link" style="color: #2e7d32; font-weight: 500; text-decoration: none; display: inline-flex; align-items: center; gap: 5px;">
                                    Explore scholarships <i class="fas fa-arrow-right" style="font-size: 0.8rem;"></i>
                                </a>
                            </div>
                        </div>

                        <!-- Institute Medal Card -->
                        <div class="service-card" style="flex: 0 0 300px; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.08); transition: transform 0.3s ease, box-shadow 0.3s ease;">
                            <div class="card-icon" style="background-color: #fff3e0; padding: 25px; text-align: center;">
                                <i class="fas fa-medal" style="font-size: 2.5rem; color: #ff8f00;"></i>
                            </div>
                            <div class="card-content" style="padding: 25px;">
                                <h3 style="color: #333; margin-bottom: 15px; font-size: 1.4rem;">Institute Medal</h3>
                                <p style="color: #666; line-height: 1.6; margin-bottom: 20px;">Recognizing outstanding achievements and contributions of our alumni community.</p>
                                <a href="pages/institute_medal.php" class="card-link" style="color: #ff8f00; font-weight: 500; text-decoration: none; display: inline-flex; align-items: center; gap: 5px;">
                                    View Recipients <i class="fas fa-arrow-right" style="font-size: 0.8rem;"></i>
                                </a>
                            </div>
                        </div>
                        <div class="service-card" style="flex: 0 0 300px; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.08); transition: transform 0.3s ease, box-shadow 0.3s ease; cursor: pointer;" onclick="window.location.href='pages/feedback.php'" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 8px 25px rgba(0,0,0,0.15)';" onmouseout="this.style.transform=''; this.style.boxShadow='0 4px 15px rgba(0,0,0,0.08)';">
                            <div class="card-icon" style="background-color: #f3e5f5; padding: 25px; text-align: center;">
                                <i class="fas fa-comment-alt" style="font-size: 2.5rem; color: #9c27b0;"></i>
                            </div>
                            <div class="card-content" style="padding: 25px;">
                                <h3 style="color: #333; margin-bottom: 15px; font-size: 1.4rem;">Share Your Experience</h3>
                                <p style="color: #666; line-height: 1.6; margin-bottom: 20px;">Share your career journey and insights with fellow alumni and students.</p>
                                <a href="pages/feedback.php" class="card-link" style="color: #9c27b0; font-weight: 500; text-decoration: none; display: inline-flex; align-items: center; gap: 5px;">
                                    Share your story <i class="fas fa-arrow-right" style="font-size: 0.8rem;"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    
                </div>

                
                <!-- Scroll hint for desktop -->
                <div class="scroll-hint" style="text-align: center; margin-top: -25px; color: #888; font-size: 0.9rem; display: none;">
                    <i class="fas fa-arrow-left" style="margin-right: 10px;"></i>
                    Scroll to see more
                    <i class="fas fa-arrow-right" style="margin-left: 10px;"></i>
                </div>
            </div>
            
            <style>
                /* Hide scrollbar but keep functionality */
                /* Hide scrollbar for Chrome, Safari and Opera */
                .services-scroll-container::-webkit-scrollbar {
                    display: none;
                }
                
                /* Hide scrollbar for IE, Edge and Firefox */
                .services-scroll-container {
                    -ms-overflow-style: none;  /* IE and Edge */
                    scrollbar-width: none;  /* Firefox */
                }
                
                /* Hover effects */
                .service-card:hover {
                    transform: translateY(-5px);
                    box-shadow: 0 8px 25px rgba(0,0,0,0.12) !important;
                }
                
                /* Show scroll hint on desktop */
                @media (min-width: 1025px) {
                    .scroll-hint {
                        display: block !important;
                    }
                }
            </style>
        </section>

        <!-- Group Members Modal -->
        <div id="groupMembersModal" class="group-members-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.7); z-index: 1000; overflow-y: auto; padding: 20px;">
            <div class="group-members-content" style="background: white; margin: 50px auto; max-width: 800px; border-radius: 10px; box-shadow: 0 5px 30px rgba(0, 0, 0, 0.3); overflow: hidden; position: relative;">
                <div class="group-members-header" style="background: #5b1f1f; color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center;">
                    <h3 id="groupModalTitle" style="margin: 0; font-size: 1.2rem;">Group Members</h3>
                    <button class="close-modal" onclick="closeGroupModal()" style="background: none; border: none; color: white; font-size: 1.5rem; cursor: pointer; padding: 5px 10px;">&times;</button>
                </div>
                <div class="group-members-body" style="padding: 20px; max-height: 70vh; overflow-y: auto;">
                    <div id="membersLoading" class="loading-members" style="text-align: center; padding: 20px; color: #666;">
                        <p>Loading members...</p>
                    </div>
                    <div id="membersList" class="member-list" style="display: none; display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px; margin-top: 15px;">
                        <!-- Members will be inserted here by JavaScript -->
                    </div>
                </div>
            </div>
        </div>
        
        <style>
            .group-card {
                cursor: pointer;
                transition: all 0.3s ease;
            }
            
            .member-card {
                display: flex;
                align-items: center;
                padding: 10px;
                border: 1px solid #eee;
                border-radius: 8px;
                transition: all 0.3s ease;
            }
            
            .member-card:hover {
                transform: translateY(-3px);
                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            }
            
            .member-avatar {
                width: 40px;
                height: 40px;
                border-radius: 50%;
                object-fit: cover;
                margin-right: 12px;
            }
            
            .member-info h4 {
                margin: 0;
                font-size: 0.95rem;
                color: #333;
            }
            
            .member-info p {
                margin: 3px 0 0;
                font-size: 0.8rem;
                color: #777;
            }
            
            .no-members {
                text-align: center;
                padding: 20px;
                color: #666;
                grid-column: 1 / -1;
            }
        </style>

        <!-- Footer -->
        <footer class="footer">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>About Alumni Connect</h3>
                    <p>Building bridges between past, present, and future generations of our institution. Stay connected, stay inspired.</p>
                    <div class="social-links">
                        <a href="#" class="social-icon">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                        </a>
                        <a href="#" class="social-icon">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                            </svg>
                        </a>
                        <a href="#" class="social-icon">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                            </svg>
                        </a>
                        <a href="#" class="social-icon">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 0C8.74 0 8.333.015 7.053.072 5.775.132 4.905.333 4.14.63c-.789.306-1.459.717-2.126 1.384S.935 3.35.63 4.14C.333 4.905.131 5.775.072 7.053.012 8.333 0 8.74 0 12s.015 3.667.072 4.947c.06 1.277.261 2.148.558 2.913.306.788.717 1.459 1.384 2.126.667.666 1.336 1.079 2.126 1.384.766.296 1.636.499 2.913.558C8.333 23.988 8.74 24 12 24s3.667-.015 4.947-.072c1.277-.06 2.148-.262 2.913-.558.788-.306 1.459-.718 2.126-1.384.666-.667 1.079-1.335 1.384-2.126.296-.765.499-1.636.558-2.913.06-1.28.072-1.687.072-4.947s-.015-3.667-.072-4.947c-.06-1.277-.262-2.149-.558-2.913-.306-.789-.718-1.459-1.384-2.126C21.319 1.347 20.651.935 19.86.63c-.765-.297-1.636-.499-2.913-.558C15.667.012 15.26 0 12 0zm0 2.16c3.203 0 3.585.016 4.85.071 1.17.055 1.805.249 2.227.415.562.217.96.477 1.382.896.419.42.679.819.896 1.381.164.422.36 1.057.413 2.227.057 1.266.07 1.646.07 4.85s-.015 3.585-.074 4.85c-.061 1.17-.256 1.805-.421 2.227-.224.562-.479.96-.899 1.382-.419.419-.824.679-1.38.896-.42.164-1.065.36-2.235.413-1.274.057-1.649.07-4.859.07-3.211 0-3.586-.015-4.859-.074-1.171-.061-1.816-.256-2.236-.421-.569-.224-.96-.479-1.379-.899-.421-.419-.69-.824-.9-1.38-.165-.42-.359-1.065-.42-2.235-.045-1.26-.061-1.649-.061-4.844 0-3.196.016-3.586.061-4.861.061-1.17.255-1.814.42-2.234.21-.57.479-.96.9-1.381.419-.419.81-.689 1.379-.898.42-.166 1.051-.361 2.221-.421 1.275-.045 1.65-.06 4.859-.06l.045.03zm0 3.678c-3.405 0-6.162 2.76-6.162 6.162 0 3.405 2.76 6.162 6.162 6.162 3.405 0 6.162-2.76 6.162-6.162 0-3.405-2.76-6.162-6.162-6.162zM12 16c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4zm7.846-10.405c0 .795-.646 1.44-1.44 1.44-.795 0-1.44-.646-1.44-1.44 0-.794.646-1.439 1.44-1.439.793-.001 1.44.645 1.44 1.439z"/>
                            </svg>
                        </a>
                    </div>
                </div>
                
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <div class="footer-links">
                        <a href="pages/directory.php">Alumni Directory</a>
                        <a href="pages/events.php">Upcoming Events</a>
                        <a href="pages/jobs.php">Job Board</a>
                        <a href="pages/news.php">News & Updates</a>
                    </div>
                </div>
                
                <div class="footer-section">
                    <h3>Resources</h3>
                    <div class="footer-links">
                        <a href="pages/mentorship.php">Mentorship Program</a>
                        <a href="pages/fundraising.php">Support the Fund</a>
                        <a href="pages/yearbook.php">Yearbook</a>
                        <a href="pages/galleries.php">Photo Galleries</a>
                    </div>
                </div>
                
                <div class="footer-section">
                    <h3>Contact Us</h3>
                    <p>Email: alumni@university.edu</p>
                    <p>Phone: +1 (555) 123-4567</p>
                    <p>Address: 123 University Ave, Campus City, ST 12345</p>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2025 Alumni Connect. All rights reserved. | Privacy Policy | Terms of Service</p>
            </div>
        </footer>
    </div>

    <script>
        // Counter Animation
        document.addEventListener('DOMContentLoaded', function() {
            const statNumbers = document.querySelectorAll('.stat-number');
            
            // Function to check if element is in viewport
            function isInViewport(element) {
                const rect = element.getBoundingClientRect();
                return (
                    rect.top >= 0 &&
                    rect.left >= 0 &&
                    rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) * 1.5 &&
                    rect.right <= (window.innerWidth || document.documentElement.clientWidth) * 1.5
                );
            }

            // Function to animate counters
            function animateCounters() {
                statNumbers.forEach(stat => {
                    if (isInViewport(stat) && !stat.classList.contains('animated')) {
                        const target = parseInt(stat.getAttribute('data-target'));
                        const suffix = stat.getAttribute('data-suffix') || '';
                        
                        const counter = new countUp.CountUp(stat, target, {
                            startVal: 0,
                            duration: 2.5,
                            suffix: suffix,
                            useEasing: true,
                            useGrouping: true,
                            separator: ',',
                            decimal: '.',
                        });
                        
                        counter.start();
                        stat.classList.add('animated');
                    }
                });
            }

            // Initial check
            animateCounters();
            
            // Check on scroll
            window.addEventListener('scroll', animateCounters);
        });

        // Group members modal functionality
        function showGroupMembers(groupId, groupName) {
            const modal = document.getElementById('groupMembersModal');
            const modalTitle = document.getElementById('groupModalTitle');
            const membersList = document.getElementById('membersList');
            const membersLoading = document.getElementById('membersLoading');
            
            // Set group name in modal title
            modalTitle.textContent = groupName + ' - Members';
            
            // Show loading state
            membersLoading.style.display = 'block';
            membersList.style.display = 'none';
            
            // Clear previous members
            membersList.innerHTML = '';
            
            // Show modal
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden';
            
            // Fetch group members
            fetch(`/alumni/api/get_group_members.php?group_id=${groupId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Hide loading
                        membersLoading.style.display = 'none';
                        
                        if (data.members.length > 0) {
                            // Add members to the list
                            data.members.forEach(member => {
                                const memberCard = document.createElement('div');
                                memberCard.className = 'member-card';
                                memberCard.innerHTML = `
                                    <div class="member-avatar" style="width: 80px; height: 80px; border-radius: 50%; overflow: hidden; background-color: #f0f0f0; display: flex; align-items: center; justify-content: center; border: 2px solid #5b1f1f;">
                                        <img src="${member.profile_picture}" 
                                             alt="${member.name || 'Alumni Member'}" 
                                             style="width: 100%; height: 100%; object-fit: cover;"
                                             onerror="this.onerror=null; this.style.display='none'; this.parentNode.innerHTML='<div style=\'width:100%;height:100%;display:flex;align-items:center;justify-content:center;background-color:#5b1f1f;color:white;font-weight:bold;font-size:24px;\'>' + '${member.name ? member.name.charAt(0).toUpperCase() : 'A'}' + '</div>'">
                                    </div>
                                    <div class="member-info" style="flex: 1; margin-left: 15px;">
                                        <h4 style="margin: 0 0 5px 0; color: #333;">${member.name || 'Alumni Member'}</h4>
                                        ${member.usn ? `<p style="margin: 0; color: #666; font-size: 0.9em;">${member.usn}</p>` : ''}
                                        ${member.email ? `<p style="margin: 5px 0 0 0; color: #666; font-size: 0.9em;"><i class="fas fa-envelope" style="margin-right: 5px; color: #5b1f1f;"></i>${member.email}</p>` : ''}
                                `;
                                membersList.appendChild(memberCard);
                            });
                        } else {
                            membersList.innerHTML = `
                                <div class="no-members">
                                    <p>No members found in this group.</p>
                                </div>
                            `;
                        }
                        
                        // Show members list
                        membersList.style.display = 'grid';
                    } else {
                        throw new Error(data.error || 'Failed to load group members');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    membersLoading.innerHTML = `
                        <p style="color: #dc3545;">Error loading members: ${error.message}</p>
                        <button onclick="showGroupMembers(${groupId}, '${groupName.replace(/'/g, "\\'")}')" 
                                style="margin-top: 10px; padding: 5px 10px; background: #5b1f1f; color: white; border: none; border-radius: 4px; cursor: pointer;">
                            Retry
                        </button>
                    `;
                });
        }
        
        function closeGroupModal() {
            const modal = document.getElementById('groupMembersModal');
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
        
        // Close modal when clicking outside the content
        window.addEventListener('click', function(event) {
            const modal = document.getElementById('groupMembersModal');
            if (event.target === modal) {
                closeGroupModal();
            }
        });
        
        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            const modal = document.getElementById('groupMembersModal');
            if (event.key === 'Escape' && modal.style.display === 'block') {
                closeGroupModal();
            }
        });
    </script>
    
    <script src='https://unpkg.com/leaflet@1.9.4/dist/leaflet.js'
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
            crossorigin=""></script>
    <script src="assets/js/script.js?v=2"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/countup.js/2.3.2/countUp.umd.min.js"></script>

    <style>
        /* User location markers */
        .user-marker {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            overflow: hidden;
            border: 2px solid #5b1f1f;
            background: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        
        .user-marker img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        /* User popup styles */
        .user-location-popup .leaflet-popup-content-wrapper {
            border-radius: 8px;
            padding: 0;
            overflow: hidden;
        }
        
        .user-popup {
            width: 100%;
        }
        
        .user-popup-header {
            display: flex;
            align-items: center;
            padding: 12px;
            background: #f9f9f9;
            border-bottom: 1px solid #eee;
        }
        
        .user-popup-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 12px;
            border: 2px solid #5b1f1f;
        }
        
        .user-popup-info h4 {
            margin: 0 0 4px 0;
            color: #333;
            font-size: 15px;
        }
        
        .user-popup-info .usn {
            margin: 0;
            color: #666;
            font-size: 13px;
        }
        
        /* Location notification styles */
        .location-notification {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #4CAF50;
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 10000;
            opacity: 0;
            animation: slideIn 0.3s ease-out forwards;
            max-width: 350px;
        }
        
        .location-notification i {
            font-size: 20px;
        }
        
        .location-notification span {
            flex-grow: 1;
        }
        
        .location-notification small {
            display: block;
            font-size: 12px;
            opacity: 0.8;
            margin-top: 4px;
        }
        
        @keyframes slideIn {
            from {
                transform: translateY(20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        /* Mobile responsive styles for fixed actions */
        @media (max-width: 768px) {
            .fixed-actions {
                top: 80px !important; /* Below sidebar toggle */
                right: 15px !important;
                left: 15px !important;
                justify-content: flex-end !important;
                flex-wrap: wrap;
                gap: 8px !important;
                padding: 8px !important;
            }

            .fixed-actions a,
            .fixed-actions button {
                padding: 8px 12px !important;
                font-size: 13px !important;
                white-space: nowrap;
            }
            
            .fixed-actions i {
                margin-right: 4px;
            }
        }
    </style>
</body>
</html>
