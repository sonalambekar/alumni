// Sidebar Toggle Functionality
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const toggleBtn = document.getElementById('sidebarToggle');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    const dropdownToggles = document.querySelectorAll('.dropdown-toggle');

    // Close sidebar when clicking overlay
    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', function() {
            sidebar.classList.remove('active');
            sidebarOverlay.classList.remove('active');
        });
    }

    // Dropdown menu functionality
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            const parent = this.parentElement;
            
            // Close other dropdowns
            document.querySelectorAll('.has-dropdown').forEach(item => {
                if (item !== parent) {
                    item.classList.remove('active');
                }
            });
            
            // Toggle current dropdown
            parent.classList.toggle('active');
        });
    });

    // Collapsible Sidebar Functionality
    const toggleBtnCollapsible = document.getElementById('sidebarToggle');
    
    // Check if toggle button exists
    if (toggleBtnCollapsible) {
        toggleBtnCollapsible.addEventListener('click', function() {
            // Check if mobile or desktop
            if (window.innerWidth <= 768) {
                // Mobile: slide in/out
                sidebar.classList.toggle('active');
                sidebarOverlay.classList.toggle('active');
            } else {
                // Desktop: collapse/expand
                sidebar.classList.toggle('collapsed');
                if (mainContent) {
                    mainContent.classList.toggle('expanded');
                }
                
                // Save state in localStorage
                const isCollapsed = sidebar.classList.contains('collapsed');
                localStorage.setItem('sidebarCollapsed', isCollapsed);
            }
        });
        
        // Load saved state for desktop
        if (window.innerWidth > 768) {
            const savedState = localStorage.getItem('sidebarCollapsed');
            if (savedState === 'true') {
                sidebar.classList.add('collapsed');
                if (mainContent) {
                    mainContent.classList.add('expanded');
                }
            }
        }
    }

    // Animated Counter for Stats
    const animateCounter = (element) => {
        const target = parseInt(element.getAttribute('data-target'));
        const duration = 2000; // 2 seconds
        const increment = target / (duration / 16); // 60fps
        let current = 0;

        const updateCounter = () => {
            current += increment;
            if (current < target) {
                element.textContent = Math.floor(current).toLocaleString();
                requestAnimationFrame(updateCounter);
            } else {
                element.textContent = target.toLocaleString() + '+';
            }
        };

        updateCounter();
    };

    // Intersection Observer for counter animation
    const observerOptions = {
        threshold: 0.5,
        rootMargin: '0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting && !entry.target.classList.contains('animated')) {
                animateCounter(entry.target);
                entry.target.classList.add('animated');
            }
        });
    }, observerOptions);

    // Observe all stat numbers
    document.querySelectorAll('.stat-number').forEach(stat => {
        observer.observe(stat);
    });

    // Initialize map when both DOM and Leaflet are ready
    function initMapWhenReady() {
        // Check if DOM is ready and Leaflet is loaded
        if (document.readyState === 'loading' || typeof L === 'undefined') {
            setTimeout(initMapWhenReady, 100);
            return;
        }
        
        // Check if we're on a page with a map
        if (document.getElementById('map')) {
            console.log('Initializing map...');
            initializeMap();
        }
    }

    // Start the initialization process
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        initMapWhenReady();
    } else {
        document.addEventListener('DOMContentLoaded', initMapWhenReady);
    }

    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
});

// Initialize Leaflet Map focused on India with alumni markers
let mapInitialized = false;

function initializeMap() {
    console.log('initializeMap function called');

    // Prevent multiple initializations
    if (mapInitialized) {
        console.log('Map already initialized, skipping...');
        return;
    }

    // Check if map container exists
    const mapContainer = document.getElementById('map');
    const loadingIndicator = document.querySelector('.map-loading');
    
    if (!mapContainer) {
        console.error('Map container not found on this page');
        return;
    }

    // Check if Leaflet is available
    if (typeof L === 'undefined') {
        console.error('Leaflet not loaded');
        if (loadingIndicator) {
            loadingIndicator.innerHTML = `
                <div style="text-align: center; color: #d32f2f;">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>Map library failed to load. Please refresh the page.</p>
                </div>
            `;
        }
        return;
    }

    // Show loading indicator
    if (loadingIndicator) {
        loadingIndicator.style.display = 'flex';
    }

    console.log('Map container found, dimensions:', mapContainer.offsetWidth, 'x', mapContainer.offsetHeight);

    // Ensure the container has dimensions
    if (mapContainer.offsetWidth === 0 || mapContainer.offsetHeight === 0) {
        console.log('Map container has no dimensions, retrying...');
        setTimeout(initializeMap, 100);
        return;
    }

    try {
        console.log('Initializing India map...');

        // Center on India with zoom level 4 to show the entire country
        const indiaCoords = [20.5937, 78.9629];
        const map = L.map('map', {
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
        }).addTo(map);
        
        // Set the view to show all of India with some padding
        const southWest = L.latLng(6.5, 68.1);
        const northEast = L.latLng(35.7, 97.4);
        const bounds = L.latLngBounds(southWest, northEast);
        map.fitBounds(bounds, { padding: [50, 50] });

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
        
        // Mark as initialized
        mapInitialized = true;
        
        // Add markers for each city
        cities.forEach(city => {
            const circle = L.circleMarker(city.coords, {
                radius: 8 + (city.alumni / 200), // Scale marker size based on alumni count
                fillColor: '#5b1f1f',
                color: '#fff',
                weight: 1,
                opacity: 1,
                fillOpacity: 0.8
            }).addTo(map);
            
            // Add popup with city info
            circle.bindPopup(`
                <div style="text-align: center;">
                    <strong>${city.city}</strong><br>
                    Alumni: ${city.alumni.toLocaleString()}+
                </div>
            `);
        });

        // Hide loading indicator when map is ready
        if (loadingIndicator) {
            loadingIndicator.style.display = 'none';
        }
        
        console.log('Map initialized successfully');

        // Add custom CSS for perfect circles and remove square backgrounds
        const style = document.createElement('style');
        style.textContent = `
            /* Remove square backgrounds */
            .leaflet-tile-pane, .leaflet-objects-pane, .leaflet-overlay-pane {
                -webkit-backface-visibility: hidden;
                -webkit-transform: translateZ(0);
                backface-visibility: hidden;
                transform: translateZ(0);
            }
            
            /* Perfect circle rendering */
            .leaflet-zoom-animated, 
            .leaflet-marker-icon, 
            .leaflet-marker-shadow, 
            .leaflet-image-layer, 
            .leaflet-pane > svg path, 
            .leaflet-tile,
            .leaflet-circle-marker {
                shape-rendering: geometricPrecision !important;
                -webkit-backface-visibility: hidden;
                -webkit-transform: translateZ(0);
                backface-visibility: hidden;
                transform: translateZ(0);
                outline: none !important;
            }
            
            /* Remove any potential square backgrounds */
            .leaflet-container {
                background: transparent !important;
            }
            
            /* Circle marker specific styles */
            .alumni-circle-marker {
                background: transparent !important;
                border: none !important;
                box-shadow: none !important;
            }
        `;
        document.head.appendChild(style);

        // Function to create a custom icon with profile image
        function createUserIcon(profileImage) {
            return L.divIcon({
                className: 'user-location-marker',
                html: `<div class="user-marker"><img src="${profileImage}" alt="Profile" onerror="this.src='/alumni/assets/images/default-avatar.png'"></div>`,
                iconSize: [40, 40],
                iconAnchor: [20, 40],
                popupAnchor: [0, -40]
            });
        }

        // Fetch and display user locations
        fetch('/alumni/api/get_user_locations.php')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.users && data.users.length > 0) {
                    data.users.forEach(user => {
                        if (user.latitude && user.longitude) {
                            const marker = L.marker(
                                [parseFloat(user.latitude), parseFloat(user.longitude)],
                                { icon: createUserIcon(user.profile_picture) }
                            ).addTo(map);

                            // Create popup content
                            let popupContent = `
                                <div class="user-popup">
                                    <div class="user-popup-header">
                                        <img src="${user.profile_picture}" alt="${user.name}" 
                                             onerror="this.src='/alumni/assets/images/default-avatar.png'"
                                             class="user-popup-avatar">
                                        <div class="user-popup-info">
                                            <h4>${user.name || 'Alumni'}</h4>
                                            ${user.usn ? `<p class="usn">${user.usn}</p>` : ''}
                                        </div>
                                    </div>
                                </div>
                            `;

                            marker.bindPopup(popupContent, {
                                maxWidth: 300,
                                minWidth: 200,
                                className: 'user-location-popup'
                            });
                        }
                    });
                }
            })
            .catch(error => {
                console.error('Error fetching user locations:', error);
            });

        // Add a subtle title to the map
        L.control.attribution({
            position: 'bottomleft',
            prefix: 'Alumni Network Across India'
        }).addTo(map);

        // Add a scale control
        L.control.scale({
            position: 'bottomright',
            metric: true,
            imperial: false,
            maxWidth: 150

    console.log('India map initialized successfully');

} catch (error) {
    console.error('Error initializing map:', error);
    // Hide loading indicator on error
    if (loadingIndicator) {
        loadingIndicator.innerHTML = `
            <div style="text-align: center; color: #d32f2f;">
                <i class="fas fa-exclamation-triangle" style="font-size: 2rem; margin-bottom: 10px;"></i>
                <p>Failed to load map. Please refresh the page or try again later.</p>
                    <p style="font-size: 16px; opacity: 0.9;">Our alumni are spread across 85+ cities worldwide</p>
                    <p style="font-size: 14px; margin-top: 20px; opacity: 0.7;">Map loading failed. Please check your internet connection.</p>
                    <p style="font-size: 12px; margin-top: 10px; opacity: 0.5;">Error: ${error.message}</p>
                    <p style="font-size: 10px; margin-top: 10px; opacity: 0.3;">Check browser console for more details</p>
                </div>
            </div>
        `;
    }
}

// Add animation on scroll
const animateOnScroll = () => {
    const elements = document.querySelectorAll('.group-card, .chapter-card, .alumni-card');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, {
        threshold: 0.1
    });

    elements.forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';
        el.style.transition = 'all 0.6s ease';
        observer.observe(el);
    });
};

// Initialize animations
window.addEventListener('load', animateOnScroll);

// Dropdown functionality for sidebar
document.addEventListener('DOMContentLoaded', function() {
    // Handle dropdown toggles in sidebar
    const dropdownToggles = document.querySelectorAll('.dropdown-toggle');

    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();

            // Close all other dropdowns first
            dropdownToggles.forEach(otherToggle => {
                if (otherToggle !== toggle) {
                    const otherDropdown = otherToggle.parentElement.querySelector('.dropdown-menu');
                    if (otherDropdown) {
                        otherDropdown.classList.remove('show');
                        otherToggle.classList.remove('active');
                    }
                }
            });

            // Toggle current dropdown
            const dropdown = toggle.parentElement.querySelector('.dropdown-menu');
            const isActive = toggle.classList.contains('active');

            if (isActive) {
                // Hide dropdown
                dropdown.classList.remove('show');
                toggle.classList.remove('active');
            } else {
                // Show dropdown
                dropdown.classList.add('show');
                toggle.classList.add('active');
            }
        });
    });

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.has-dropdown')) {
            dropdownToggles.forEach(toggle => {
                const dropdown = toggle.parentElement.querySelector('.dropdown-menu');
                dropdown.classList.remove('show');
                toggle.classList.remove('active');
            });
        }
    });

    // Handle mobile sidebar overlay clicks
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.remove('active');
            sidebarOverlay.classList.remove('active');

            // Close all dropdowns when closing mobile sidebar
            dropdownToggles.forEach(toggle => {
                const dropdown = toggle.parentElement.querySelector('.dropdown-menu');
                if (dropdown) {
                    dropdown.classList.remove('show');
                }
                toggle.classList.remove('active');
            });
        });
    }
});

// Sidebar toggle functionality
document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const leftArrow = sidebarToggle?.querySelector('.fa-long-arrow-alt-left');
    const rightArrow = sidebarToggle?.querySelector('.fa-long-arrow-alt-right');

    if (sidebarToggle && sidebar && leftArrow && rightArrow) {
        // Ensure initial state is correct (default: expanded with left arrow)
        leftArrow.style.display = 'block';
        rightArrow.style.display = 'none';

        // Load saved state
        const savedState = localStorage.getItem('sidebarCollapsed');
        if (savedState === 'true') {
            sidebar.classList.add('collapsed');
            if (mainContent) {
                mainContent.classList.add('expanded');
            }
            leftArrow.style.display = 'none';
            rightArrow.style.display = 'block';
        }

        sidebarToggle.addEventListener('click', function() {
            const isCollapsed = sidebar.classList.contains('collapsed');

            if (isCollapsed) {
                // Expand sidebar
                sidebar.classList.remove('collapsed');
                if (mainContent) {
                    mainContent.classList.remove('expanded');
                }
                leftArrow.style.display = 'block';
                rightArrow.style.display = 'none';
                localStorage.setItem('sidebarCollapsed', 'false');
            } else {
                // Collapse sidebar
                sidebar.classList.add('collapsed');
                if (mainContent) {
                    mainContent.classList.add('expanded');
                }
                leftArrow.style.display = 'none';
                rightArrow.style.display = 'block';
                localStorage.setItem('sidebarCollapsed', 'true');
            }
        });
    }
});

// Handle window resize
let resizeTimer;
window.addEventListener('resize', function() {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(function() {
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const mainContent = document.getElementById('mainContent');

        if (window.innerWidth > 768) {
            // Desktop view
            sidebar?.classList.remove('active');
            sidebarOverlay?.classList.remove('active');

            // Restore collapsed state if it was saved
            const savedState = localStorage.getItem('sidebarCollapsed');
            if (savedState === 'true') {
                sidebar?.classList.add('collapsed');
                if (mainContent) {
                    mainContent.classList.add('expanded');
                }
            } else {
                sidebar?.classList.remove('collapsed');
                if (mainContent) {
                    mainContent.classList.remove('expanded');
                }
            }
        } else {
            // Mobile view
            sidebar?.classList.remove('collapsed');
            if (mainContent) {
                mainContent.classList.remove('expanded');
            }
        }
    }, 250);
});
