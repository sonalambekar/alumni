/**
 * Map Initialization Script
 * Handles the Leaflet map initialization and user location features
 */

// Global flag to track map initialization
let mapInitialized = false;
let mapInstance = null;

/**
 * Initialize the map with Leaflet
 */
function initializeMap() {
    console.log('Initializing map...');

    // Prevent multiple initializations
    if (mapInitialized || !document.getElementById('map')) {
        console.log('Map already initialized or container not found');
        return;
    }

    const mapContainer = document.getElementById('map');
    const loadingIndicator = document.querySelector('.map-loading');

    // Show loading indicator
    if (loadingIndicator) {
        loadingIndicator.style.display = 'flex';
    }

    try {
        // Center on India with zoom level 4 to show the entire country
        const indiaCoords = [20.5937, 78.9629];
        
        // Initialize the map
        mapInstance = L.map('map', {
            center: indiaCoords,
            zoom: 4,
            minZoom: 3,
            maxZoom: 18,
            zoomControl: true
        });

        // Add OpenStreetMap tiles with a more subtle style
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
            maxZoom: 18,
            minZoom: 3
        }).addTo(mapInstance);
        
        // Set the view to show all of India with some padding
        const southWest = L.latLng(6.5, 68.1);
        const northEast = L.latLng(35.7, 97.4);
        const bounds = L.latLngBounds(southWest, northEast);
        mapInstance.fitBounds(bounds, { padding: [50, 50] });

        console.log('Map tiles added successfully');

        // Major Indian cities with coordinates and alumni counts
        const cities = [
            { city: 'Mumbai', coords: [19.0760, 72.8777], alumni: 1250 },
            { city: 'Delhi', coords: [28.6139, 77.2090], alumni: 1180 },
            { city: 'Bangalore', coords: [12.9716, 77.5946], alumni: 980 },
            { city: 'Hyderabad', coords: [17.3850, 78.4867], alumni: 760 },
            { city: 'Chennai', coords: [13.0827, 80.2707], alumni: 720 },
            { city: 'Kolkata', coords: [22.5726, 88.3639], alumni: 680 },
            { city: 'Pune', coords: [18.5204, 73.8567], alumni: 540 },
            { city: 'Ahmedabad', coords: [23.0225, 72.5714], alumni: 490 },
            { city: 'Jaipur', coords: [26.9124, 75.7873], alumni: 320 },
            { city: 'Lucknow', coords: [26.8467, 80.9462], alumni: 280 },
            { city: 'Kochi', coords: [9.9312, 76.2673], alumni: 250 },
            { city: 'Chandigarh', coords: [30.7333, 76.7794], alumni: 220 },
            { city: 'Bhopal', coords: [23.2599, 77.4126], alumni: 210 },
            { city: 'Indore', coords: [22.7196, 75.8577], alumni: 190 },
            { city: 'Goa', coords: [15.2993, 74.1240], alumni: 180 }
        ];

        console.log('Adding circle markers for', cities.length, 'cities');
        
        // Add markers for each city
        cities.forEach(city => {
            const circle = L.circleMarker(city.coords, {
                radius: 8 + (city.alumni / 200), // Scale marker size based on alumni count
                fillColor: '#5b1f1f',
                color: '#fff',
                weight: 1,
                opacity: 1,
                fillOpacity: 0.8
            }).addTo(mapInstance);
            
            // Add popup with city info
            circle.bindPopup(`
                <div style="text-align: center;">
                    <strong>${city.city}</strong><br>
                    Alumni: ${city.alumni.toLocaleString()}+
                </div>
            `);
        });

        // Mark as initialized
        mapInitialized = true;
        
        // Load user locations
        loadUserLocations();
        
        // Hide loading indicator
        if (loadingIndicator) {
            loadingIndicator.style.display = 'none';
        }

        console.log('Map initialized successfully');
        
    } catch (error) {
        console.error('Error initializing map:', error);
        
        // Show error message
        if (loadingIndicator) {
            loadingIndicator.innerHTML = `
                <div style="text-align: center; color: #d32f2f;">
                    <i class="fas fa-exclamation-triangle" style="font-size: 2rem; margin-bottom: 10px;"></i>
                    <p>Failed to load map. Please refresh the page.</p>
                    <p style="font-size: 0.9rem; margin-top: 10px;">${error.message}</p>
                </div>
            `;
        }
    }
}

// Function to create a custom icon with profile image
function createUserIcon(profileImage) {
    // Ensure we have a valid image URL
    let imageUrl = profileImage || 'assets/images/default-avatar.png';
    
    // Handle relative URLs
    if (!imageUrl.startsWith('http') && !imageUrl.startsWith('/')) {
        // If it's a relative path, make sure it's relative to the root
        imageUrl = '/alumni/' + imageUrl.replace(/^\/+/, '');
    }
    
    // Add cache buster to prevent caching issues
    const cacheBuster = '?t=' + new Date().getTime();
    
    return L.divIcon({
        className: 'user-location-marker',
        html: `
            <div class="user-marker">
                <img src="${imageUrl}${cacheBuster}" 
                     alt="Profile"
                     onerror="this.onerror=null; this.src='/alumni/assets/images/default-avatar.png'"
                     style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
            </div>
        `,
        iconSize: [40, 40],
        iconAnchor: [20, 40],
        popupAnchor: [0, -40]
    });
}

// Function to load user locations
function loadUserLocations() {
    if (!mapInstance) {
        console.error('Map instance not available');
        return;
    }
    
    console.log('Loading user locations...');
    
    fetch('/alumni/api/get_user_locations.php')
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Received user locations:', data);
            
            if (data.success && data.users && Array.isArray(data.users)) {
                let addedMarkers = 0;
                
                data.users.forEach(user => {
                    try {
                        if (user.latitude !== undefined && user.longitude !== undefined) {
                            const lat = parseFloat(user.latitude);
                            const lng = parseFloat(user.longitude);
                            
                            if (isNaN(lat) || isNaN(lng)) {
                                console.warn('Invalid coordinates for user:', user.id, lat, lng);
                                return;
                            }
                            
                            const marker = L.marker(
                                [lat, lng],
                                {
                                    icon: createUserIcon(user.profile_picture || '')
                                }
                            ).addTo(mapInstance);
                            
                            // Process profile picture URL for popup
                            let popupImageUrl = user.profile_picture || '/alumni/assets/images/default-avatar.png';
                            if (!popupImageUrl.startsWith('http') && !popupImageUrl.startsWith('/')) {
                                popupImageUrl = '/alumni/' + popupImageUrl.replace(/^\/+/, '');
                            }
                            
                            // Add popup with user info
                            const popupContent = `
                                <div class="user-popup">
                                    <div class="user-popup-header">
                                        <img src="${popupImageUrl}" 
                                             alt="${user.name || 'User'}" 
                                             class="user-popup-avatar"
                                             onerror="this.onerror=null; this.src='/alumni/assets/images/default-avatar.png'">
                                        <div>
                                            <h4>${user.name || 'Alumni Member'}</h4>
                                            ${user.usn ? `<p class="user-usn">${user.usn}</p>` : ''}
                                        </div>
                                    </div>
                                    <div class="user-popup-actions">
                                        <a href="/alumni/profile.php?id=${user.id}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-user"></i> View Profile
                                        </a>
                                    </div>
                                </div>
                            `;
                            
                            marker.bindPopup(popupContent);
                            addedMarkers++;
                        }
                    } catch (error) {
                        console.error('Error adding user marker:', error, user);
                    }
                });
                
                console.log(`Successfully added ${addedMarkers} user markers to the map`);
                
                // If we have users, fit the map to show all markers
                if (addedMarkers > 0 && data.users.length > 1) {
                    const group = new L.featureGroup(data.users.map(user => 
                        L.marker([parseFloat(user.latitude), parseFloat(user.longitude)])
                    ));
                    mapInstance.fitBounds(group.getBounds().pad(0.1));
                }
            } else {
                console.warn('No user data or invalid format:', data);
            }
        })
        .catch(error => {
            console.error('Error loading user locations:', error);
            
            // Show error message on the map
            if (mapInstance) {
                L.popup()
                    .setLatLng(mapInstance.getCenter())
                    .setContent(`
                        <div style="text-align: center; padding: 10px;">
                            <i class="fas fa-exclamation-triangle" style="color: #e74c3c; font-size: 24px; margin-bottom: 10px;"></i>
                            <p>Could not load user locations. Please try again later.</p>
                            <small>${error.message}</small>
                        </div>
                    `)
                    .openOn(mapInstance);
            }
        });
}

// Initialize map when the page loads
document.addEventListener('DOMContentLoaded', function() {
    // Check if we're on a page with a map
    if (document.getElementById('map')) {
        // Wait for Leaflet to be loaded
        const checkLeaflet = setInterval(() => {
            if (typeof L !== 'undefined') {
                clearInterval(checkLeaflet);
                initializeMap();
            }
        }, 100);
        
        // Timeout after 5 seconds
        setTimeout(() => {
            clearInterval(checkLeaflet);
            if (!mapInitialized) {
                const loadingIndicator = document.querySelector('.map-loading');
                if (loadingIndicator) {
                    loadingIndicator.innerHTML = `
                        <div style="text-align: center; color: #d32f2f;">
                            <i class="fas fa-exclamation-triangle"></i>
                            <p>Map failed to load. Please check your internet connection.</p>
                        </div>
                    `;
                }
            }
        }, 5000);
    }
});
