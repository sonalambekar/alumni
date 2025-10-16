<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Members Nearby - Alumni Connect</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <!-- Leaflet CSS (Free OpenStreetMap library) -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
          crossorigin=""/>
</head>
<body>
    <?php include '../sidebar.php'; ?>

    <div class="main-content" id="mainContent">
        <div style="background: linear-gradient(135deg, var(--primary-color) 0%, #7a2a2a 100%); color: var(--white); padding: 60px 40px; text-align: center;">
            <h1 style="font-size: 42px; margin-bottom: 15px;">Members Nearby</h1>
            <p>Discover and connect with alumni in your area</p>
        </div>

        <div style="padding: 60px 40px; background-color: var(--bg-light); min-height: 60vh;">
            <div style="text-align: center; max-width: 600px; margin: 0 auto 40px auto;">
                <h2 style="color: var(--primary-color); font-size: 32px; margin-bottom: 20px;">🗺️ Alumni Around the World</h2>
                <p style="color: var(--text-light); font-size: 16px; line-height: 1.8; margin-bottom: 30px;">
                    Discover and connect with fellow alumni across the globe. Click on markers to see alumni counts by city.
                </p>
            </div>

            <!-- Map Container -->
            <div id="map" style="width: 100%; height: 500px; border-radius: 10px; box-shadow: 0 4px 20px rgba(0,0,0,0.1);"></div>
        </div>
    </div>

    <!-- Leaflet JS (Free OpenStreetMap library) -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
            crossorigin=""></script>
    <script src="../assets/js/script.js?v=2"></script>
</body>
</html>
