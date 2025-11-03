<?php
// Start output buffering at the highest level
if (!headers_sent() && !in_array('ob_gzhandler', ob_list_handlers()) && ob_get_level() == 0) {
    ob_start('ob_gzhandler');
}

try {
    // Start session if not already started and headers not sent
    if (!headers_sent() && session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Include database configuration if not already included
    if (!function_exists('isLoggedIn')) {
        require_once __DIR__ . '/includes/db_config.php';
    }
} catch (Exception $e) {
    // Log error but don't output anything to prevent further header issues
    error_log('Sidebar initialization error: ' . $e->getMessage());
}
?>
<!-- Sidebar Navigation -->
<div class="sidebar" id="sidebar">
    <!-- Mobile Menu Toggle Button -->
    <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle navigation">
        <span class="hamburger-line"></span>
        <span class="hamburger-line"></span>
        <span class="hamburger-line"></span>
    </button>

    <div class="sidebar-header">
        <div class="logo-container">
            <span class="logo-text">Alumni Connect</span>
        </div>
    </div>

    <style>
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            height: 100vh;
            background: linear-gradient(135deg, var(--primary-color) 0%, #7a2a2a 100%);
            color: white;
            transform: translateX(0);
            transition: transform 0.3s ease;
            z-index: 1000;
            overflow-y: auto;
        }

        .sidebar-toggle {
            position: fixed;
            top: 15px;
            right: 15px;
            z-index: 1001;
            background: #5b1f1f;
            border: 2px solid #ecc35c;
            border-radius: 8px;
            padding: 12px;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease;
            width: 50px;
            height: 50px;
        }

        .sidebar-toggle:hover {
            background: #4a1919;
            border-color: #d4a847;
            transform: scale(1.05);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.4);
        }

        .sidebar-toggle:active {
            transform: scale(0.95);
        }

        .hamburger-line {
            display: block;
            width: 26px;
            height: 3px;
            background: white;
            margin: 5px 0;
            transition: all 0.3s ease;
            border-radius: 2px;
        }

        .sidebar.active .hamburger-line:nth-child(1) {
            transform: rotate(45deg) translate(5px, 5px);
        }

        .sidebar.active .hamburger-line:nth-child(2) {
            opacity: 0;
        }

        .sidebar.active .hamburger-line:nth-child(3) {
            transform: rotate(-45deg) translate(7px, -6px);
        }

        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .logo-container {
            margin-bottom: 15px;
        }

        .logo-text {
            font-size: 20px;
            font-weight: 700;
            color: white;
        }

        /* Navigation Styles */
        .sidebar-nav {
            padding: 15px 0;
        }

        .nav-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .nav-item {
            margin: 8px 12px;
            position: relative;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            color: rgba(255, 255, 255, 0.85);
            text-decoration: none;
            border-radius: 12px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .nav-link::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 0;
            background: #ecc35c;
            border-radius: 0 2px 2px 0;
            transition: height 0.3s ease;
        }

        .nav-link:hover {
            background: rgba(236, 195, 92, 0.15);
            color: #ecc35c;
            padding-left: 20px;
            transform: translateX(4px);
        }

        .nav-link:hover::before {
            height: 60%;
        }

        .nav-link.active {
            background: rgba(236, 195, 92, 0.2);
            color: #ecc35c;
            box-shadow: inset 0 0 10px rgba(236, 195, 92, 0.1);
        }

        .nav-link.active::before {
            height: 100%;
        }

        .nav-icon {
            width: 20px;
            height: 20px;
            margin-right: 12px;
            flex-shrink: 0;
            transition: transform 0.3s ease;
        }

        .nav-link:hover .nav-icon {
            transform: scale(1.1) rotate(5deg);
        }

        .nav-text {
            font-size: 14px;
            font-weight: 500;
            white-space: nowrap;
        }

        /* Dropdown Styles */
        .has-dropdown > .nav-link {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .dropdown-arrow {
            width: 18px;
            height: 18px;
            margin-left: auto;
            transition: transform 0.3s ease;
        }

        .has-dropdown.active .dropdown-arrow {
            transform: rotate(180deg);
        }

        .dropdown-menu {
            list-style: none;
            padding: 0;
            margin: 0;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }

        .has-dropdown.active .dropdown-menu {
            max-height: 500px;
        }

        .dropdown-menu li {
            margin: 0;
        }

        .dropdown-menu a {
            display: block;
            padding: 10px 16px 10px 48px;
            color: rgba(255, 255, 255, 0.75);
            text-decoration: none;
            font-size: 13px;
            transition: all 0.2s ease;
            position: relative;
            border-left: 2px solid transparent;
            margin-left: 12px;
        }

        .dropdown-menu a::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 2px;
            height: 0;
            background: #ecc35c;
            transition: height 0.3s ease;
        }

        .dropdown-menu a:hover {
            color: #ecc35c;
            padding-left: 52px;
        }

        .dropdown-menu a:hover::before {
            height: 60%;
        }

        /* Mobile Styles */
        @media (min-width: 769px) {
            .sidebar-toggle {
                display: none;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.active {
                transform: translateX(0);
            }
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        .sidebar-overlay.active {
            display: block;
        }
    </style>

    <nav class="sidebar-nav">
        <ul class="nav-list">
            <li class="nav-item">
                <a href="/alumni/index.php" class="nav-link active">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                        <polyline points="9 22 9 12 15 12 15 22"></polyline>
                    </svg>
                    <span class="nav-text">Home</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="/alumni/pages/noticeboard_new.php" class="nav-link">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                        <polyline points="10 9 9 9 8 9"></polyline>
                    </svg>
                    <span class="nav-text">Noticeboard</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="/alumni/pages/news.php" class="nav-link">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                    </svg>
                    <span class="nav-text">News Corner</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="/alumni/pages/galleries.php" class="nav-link">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                        <circle cx="8.5" cy="8.5" r="1.5"></circle>
                        <polyline points="21 15 16 10 5 21"></polyline>
                    </svg>
                    <span class="nav-text">Galleries</span>
                </a>
            </li>

            <li class="nav-item has-dropdown">
                <a href="#" class="nav-link dropdown-toggle">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                    <span class="nav-text">Alumni</span>
                    <svg class="dropdown-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </a>
                <ul class="dropdown-menu">
                    <li><a href="/alumni/pages/announcements.php">Announcements</a></li>
                    <li><a href="/alumni/pages/directory.php">Directory</a></li>
                    <li><a href="/alumni/pages/members-nearby.php">Members Nearby</a></li>
                    <li><a href="/alumni/pages/yearbook.php">Yearbook</a></li>
                </ul>
            </li>

            <li class="nav-item">
                <a href="/alumni/pages/mobile-app.php" class="nav-link">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="5" y="2" width="14" height="20" rx="2" ry="2"></rect>
                        <path d="M12 18h.01"></path>
                    </svg>
                    <span class="nav-text">Mobile App</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="/alumni/pages/events.php" class="nav-link">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                    <span class="nav-text">Events</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="/alumni/pages/jobs.php" class="nav-link">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                        <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                    </svg>
                    <span class="nav-text">Jobs</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="/alumni/pages/internships.php" class="nav-link">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M12 16h.01M16 16h.01M20 16h.01M4 12h16a2 2 0 002-2V8a2 2 0 00-2-2H4a2 2 0 00-2 2v4a2 2 0 002 2z"></path>
                    </svg>
                    <span class="nav-text">Internships</span>
                </a>
            </li>

            <li class="nav-item has-dropdown">
                <a href="#" class="nav-link dropdown-toggle">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                    </svg>
                    <span class="nav-text">Enterprise</span>
                    <svg class="dropdown-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </a>
                <ul class="dropdown-menu">
                    <li><a href="/alumni/pages/business-connect.php">Business Connect</a></li>
                    <li><a href="/alumni/pages/member-support.php">Member Support</a></li>
                    <li><a href="/alumni/pages/couch_surfing_enterprises.php">Enterprises Couch Surfing</a></li>
                </ul>
            </li>

            <li class="nav-item has-dropdown">
                <a href="#" class="nav-link dropdown-toggle">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <span class="nav-text">PRO</span>
                    <svg class="dropdown-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </a>
                <ul class="dropdown-menu">
                    <li><a href="/alumni/pages/fundraising.php">Fund Raising</a></li>
                    <li><a href="/alumni/pages/mentorship.php">Mentorship</a></li>
                    <li><a href="/alumni/pages/special-groups.php">Special Interest Groups</a></li>
                </ul>
            </li>

            <li class="nav-item has-dropdown">
                <a href="#" class="nav-link dropdown-toggle">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                    </svg>
                    <span class="nav-text">Enterprise</span>
                    <svg class="dropdown-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </a>
                <ul class="dropdown-menu">
                    <li><a href="/alumni/pages/business-connect.php">Business Connect</a></li>
                    <li><a href="/alumni/pages/member-support.php">Member Support</a></li>
                </ul>
            </li>

            <?php
            // Show admin panel only if user is logged in as admin
            if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin'):
            ?>
            <li class="nav-item has-dropdown">
                <a href="#" class="nav-link dropdown-toggle">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 1l3 6 6 3-6 3-3 6-3-6-6-3 6-3z"></path>
                    </svg>
                    <span class="nav-text">Admin Panel</span>
                    <svg class="dropdown-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </a>
                <ul class="dropdown-menu">
                    <li><a href="/alumni/admin/admin_dashboard.php">Dashboard</a></li>
                    <li><a href="/alumni/admin/manage_noticeboard.php">Noticeboard</a></li>
                    <li><a href="/alumni/admin/manage_news.php">News</a></li>
                    <li><a href="/alumni/admin/manage_events.php">Events</a></li>
                    <li><a href="/alumni/admin/manage_jobs.php">Jobs</a></li>
                    <li><a href="/alumni/admin/manage_galleries.php">Photo Galleries</a></li>
                    <li><a href="/alumni/admin/manage_groups.php">Interest Groups</a></li>
                    <li><a href="/alumni/admin/manage_users.php">User Management</a></li>
                    <li><a href="/alumni/logout.php">Logout</a></li>
                </ul>
            </li>
            <?php endif; ?>
        </ul>
        
        <!-- Profile Section -->
        <?php if (isLoggedIn()): ?>
        <?php $user = getCurrentUser(); ?>
        <div class="profile-section" style="margin-top: auto; padding: 15px; border-top: 1px solid rgba(255, 255, 255, 0.1);">
            <a href="/alumni/pages/profile.php" class="nav-link" style="padding: 10px 16px;">
                <div style="display: flex; align-items: center;">
                    <div style="width: 36px; height: 36px; border-radius: 50%; overflow: hidden; margin-right: 12px; flex-shrink: 0;">
                        <img src="<?php 
                            $userName = $user['name'] ?? 'User';
                            echo !empty($user['profile_picture']) ? 
                                '../uploads/profile_pictures/' . htmlspecialchars($user['profile_picture']) : 
                                'https://ui-avatars.com/api/?name=' . urlencode($userName) . '&size=200&background=5b1f1f&color=fff'; 
                        ?>" 
                             alt="Profile" 
                             style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                    <div style="overflow: hidden;">
                        <div style="font-weight: 500; color: white; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo htmlspecialchars($user['name'] ?? 'User'); ?></div>
                        <div style="font-size: 12px; color: rgba(255, 255, 255, 0.7); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">View Profile</div>
                    </div>
                </div>
            </a>
        </div>
        <?php endif; ?>
    </nav>
</div>

<!-- Mobile Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<script>
    // Immediate logging to verify script loads
    console.log('🔧 Sidebar script loaded!');
    console.log('📍 Current page:', window.location.href);

    document.addEventListener('DOMContentLoaded', function() {
        console.log('🎯 DOM Content Loaded - Sidebar initializing');

        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const dropdownToggles = document.querySelectorAll('.dropdown-toggle');

        console.log('🔍 Elements found:');
        console.log('- sidebarToggle:', sidebarToggle ? '✅' : '❌');
        console.log('- sidebar:', sidebar ? '✅' : '❌');
        console.log('- sidebarOverlay:', sidebarOverlay ? '✅' : '❌');
        console.log('- dropdownToggles:', dropdownToggles.length);

        // Toggle sidebar when hamburger is clicked
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('active');
                if (sidebarOverlay) {
                    sidebarOverlay.classList.toggle('active');
                }
            });
        }

        // Close sidebar when overlay is clicked
        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', function() {
                sidebar.classList.remove('active');
                this.classList.remove('active');
            });
        }

        // Handle dropdown toggles
        dropdownToggles.forEach(toggle => {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                const parentItem = this.closest('.has-dropdown');
                parentItem.classList.toggle('active');
            });
        });

        console.log('✅ Sidebar initialization complete');
    });
</script>