<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Members Nearby - Alumni Connect</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    <!-- Leaflet CSS -->
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
            height: 600px;
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
            min-height: 600px;
            background-color: #e8f4f8;
        }
        /* Loading indicator */
        .map-loading {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            display: none;
            justify-content: center;
            align-items: center;
            background: rgba(255,255,255,0.9);
            z-index: 1000;
            font-size: 1.2rem;
            color: #333;
        }
        /* User location marker styles */
        .user-marker {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #5b1f1f;
            border: 2px solid white;
            box-shadow: 0 0 0 2px #5b1f1f;
            position: relative;
            overflow: hidden;
        }
        .user-marker img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .user-marker-inner {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <?php include '../sidebar.php'; ?>

    <div class="main-content" id="mainContent">
        <div style="background: linear-gradient(135deg, var(--primary-color) 0%, #7a2a2a 100%); color: var(--white); padding: 60px 40px; text-align: center;">
            <h1 style="font-size: 42px; margin-bottom: 15px;">Members Nearby</h1>
            <p>Discover and connect with alumni in your area</p>
        </div>

        <div class="map-section">
            <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 0 20px;">
                <div style="text-align: center; max-width: 800px; margin: 0 auto 40px;">
                    <h2 style="color: var(--primary-color); font-size: 32px; margin-bottom: 20px;">
                        <i class="fas fa-globe-americas"></i> Alumni Around the World
                    </h2>
                    <p style="color: var(--text-light); font-size: 16px; line-height: 1.8; margin-bottom: 30px;">
                        Discover and connect with fellow alumni across the globe. Click on markers to see alumni details and connect.
                    </p>
                </div>

                <!-- Map Container -->
                <div class="map-container">
                    <div id="map"></div>
                    <div class="map-loading">
                        <div style="text-align: center;">
                            <div class="spinner" style="font-size: 2rem; margin-bottom: 10px;">
                                <i class="fas fa-spinner fa-spin"></i>
                            </div>
                            <p>Loading map data...</p>
                        </div>
                    </div>
                </div>
                
                <div style="margin-top: 20px; text-align: center; color: #666; font-size: 0.9rem;">
                    <p><i class="fas fa-info-circle"></i> Click on any marker to see alumni details</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
            crossorigin=""></script>
    <!-- Map Initialization -->
    <script src="../assets/js/map-init.js"></script>
    
    <script>
    // Initialize map when the page loads
    document.addEventListener('DOMContentLoaded', function() {
        // Check if we're on a page with a map
        if (document.getElementById('map')) {
            // Initialize the map
            initializeMap();
            
            // Load user locations after a short delay to ensure map is fully initialized
            setTimeout(loadUserLocations, 500);
            
            // Show loading indicator
            const loadingIndicator = document.querySelector('.map-loading');
            if (loadingIndicator) {
                loadingIndicator.style.display = 'flex';
                
                // Hide loading indicator after map is loaded
                setTimeout(() => {
                    loadingIndicator.style.display = 'none';
                }, 2000);
            }
        }
    });
    </script>
</body>
</html>
