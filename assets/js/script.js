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

    // Initialize Map (wait for Leaflet to load)
    console.log('DOM loaded, checking for Leaflet...');
    console.log('Leaflet available:', typeof L !== 'undefined');

    if (typeof L !== 'undefined') {
        console.log('Leaflet is loaded, initializing map...');
        initializeMap();
    } else {
        console.log('Leaflet not loaded yet, waiting...');
        // Wait for Leaflet to load
        const checkLeaflet = setInterval(() => {
            console.log('Checking Leaflet availability...');
            if (typeof L !== 'undefined') {
                console.log('Leaflet loaded, initializing map...');
                clearInterval(checkLeaflet);
                initializeMap();
            }
        }, 100);

        // Timeout after 5 seconds
        setTimeout(() => {
            if (typeof L === 'undefined') {
                console.log('ERROR: Leaflet failed to load after 5 seconds');
                clearInterval(checkLeaflet);
            }
        }, 5000);
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

// Initialize Leaflet Map (Free alternative to Mapbox)
function initializeMap() {
    console.log('initializeMap function called');

    // Check if map container exists
    const mapContainer = document.getElementById('map');
    if (!mapContainer) {
        console.log('ERROR: Map container not found');
        return;
    }

    console.log('Map container found, dimensions:', mapContainer.offsetWidth, 'x', mapContainer.offsetHeight);

    // Ensure the container has dimensions
    if (mapContainer.offsetWidth === 0 || mapContainer.offsetHeight === 0) {
        console.log('Map container has no dimensions, waiting...');
        setTimeout(initializeMap, 100);
        return;
    }

    try {
        console.log('Initializing Leaflet map...');

        // Initialize Leaflet map with OpenStreetMap tiles (completely free)
        const map = L.map('map').setView([20, 0], 2);

        // Add OpenStreetMap tiles (free, no API key required)
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 18
        }).addTo(map);

        console.log('Tiles added successfully');

        // Alumni locations data
        const alumniLocations = [
            { city: 'Bengaluru', country: 'India', coordinates: [12.9716, 77.5946], members: 2500 },
            { city: 'Delhi', country: 'India', coordinates: [28.7041, 77.1025], members: 3200 },
            { city: 'Mumbai', country: 'India', coordinates: [19.0760, 72.8777], members: 1800 },
            { city: 'New York', country: 'USA', coordinates: [40.7128, -74.0060], members: 1200 },
            { city: 'San Francisco', country: 'USA', coordinates: [37.7749, -122.4194], members: 900 },
            { city: 'London', country: 'UK', coordinates: [51.5074, -0.1278], members: 800 },
            { city: 'Singapore', country: 'Singapore', coordinates: [1.3521, 103.8198], members: 650 },
            { city: 'Dubai', country: 'UAE', coordinates: [25.2048, 55.2708], members: 550 },
            { city: 'Toronto', country: 'Canada', coordinates: [43.6532, -79.3832], members: 480 },
            { city: 'Sydney', country: 'Australia', coordinates: [-33.8688, 151.2093], members: 420 },
            { city: 'Berlin', country: 'Germany', coordinates: [52.5200, 13.4050], members: 380 },
            { city: 'Tokyo', country: 'Japan', coordinates: [35.6762, 139.6503], members: 350 }
        ];

        console.log('Adding markers for', alumniLocations.length, 'locations');

        // Add markers for each location
        alumniLocations.forEach((location, index) => {
            // Create a custom icon for alumni markers
            const alumniIcon = L.divIcon({
                className: 'custom-alumni-marker',
                html: `<div style="
                    width: 20px;
                    height: 20px;
                    border-radius: 50%;
                    background-color: #5b1f1f;
                    border: 2px solid #ecc35c;
                    box-shadow: 0 2px 8px rgba(0,0,0,0.3);
                    cursor: pointer;
                "></div>`,
                iconSize: [20, 20],
                iconAnchor: [10, 10]
            });

            // Create popup content
            const popupContent = `
                <div style="padding: 10px; font-family: 'Poppins', sans-serif; min-width: 150px;">
                    <h3 style="margin: 0 0 5px 0; color: #5b1f1f; font-size: 16px;">${location.city}</h3>
                    <p style="margin: 0; color: #666; font-size: 14px;">${location.country}</p>
                    <p style="margin: 5px 0 0 0; color: #ecc35c; font-weight: 600; font-size: 14px;">${location.members}+ Alumni</p>
                </div>
            `;

            // Add marker to map
            const marker = L.marker(location.coordinates, { icon: alumniIcon })
                .bindPopup(popupContent)
                .addTo(map);

            console.log('Added marker for', location.city);
        });

        // Add a scale control
        L.control.scale().addTo(map);

        console.log('Free Leaflet map initialized successfully with OpenStreetMap tiles');

    } catch (error) {
        console.log('Map initialization failed:', error);
        console.log('Error details:', error.message, error.stack);
        // Display a fallback message
        mapContainer.innerHTML = `
            <div style="display: flex; align-items: center; justify-content: center; height: 100%; background: linear-gradient(135deg, #5b1f1f 0%, #7a2a2a 100%); color: white; text-align: center; padding: 40px;">
                <div>
                    <h3 style="font-size: 24px; margin-bottom: 15px;">🌍 Global Alumni Network</h3>
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
