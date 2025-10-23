<?php
session_start();
require_once '../includes/db_config.php';

// Require authentication
requireLogin();
?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alumni Directory - Alumni Connect</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #5b1f1f;
            --secondary-color: #ecc35c;
            --bg-light: #f8f9fa;
            --text-dark: #1a1a1a;
            --text-light: #6b7280;
            --white: #ffffff;
            --shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 25px rgba(0, 0, 0, 0.1);
            --shadow-xl: 0 20px 40px rgba(0, 0, 0, 0.1);
            --border-radius: 12px;
            --border-radius-lg: 20px;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-light);
            color: var(--text-dark);
            line-height: 1.6;
        }

        /* Professional Header Section */
        .directory-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, #7a2a2a 100%);
            color: white;
            padding: 60px 40px;
            position: relative;
            overflow: hidden;
        }

        .directory-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="20" cy="20" r="2" fill="rgba(255,255,255,0.1)"/><circle cx="80" cy="40" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="40" cy="80" r="1.5" fill="rgba(255,255,255,0.1)"/></svg>');
            opacity: 0.3;
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            position: relative;
            z-index: 2;
        }

        .header-title {
            font-size: 48px;
            font-weight: 700;
            margin-bottom: 15px;
            text-align: center;
        }

        .header-subtitle {
            font-size: 18px;
            opacity: 0.9;
            text-align: center;
            font-weight: 400;
            margin-bottom: 40px;
        }

        /* Enhanced Search Section */
        .search-section {
            background: white;
            padding: 40px;
            margin: -30px 40px 0;
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-xl);
            position: relative;
            z-index: 3;
        }

        .search-container {
            max-width: 800px;
            margin: 0 auto;
        }

        .search-form {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 20px;
            align-items: end;
        }

        .search-input-wrapper {
            position: relative;
        }

        .search-input {
            width: 100%;
            padding: 18px 25px 18px 55px;
            border: 2px solid #e5e7eb;
            border-radius: var(--border-radius);
            font-size: 16px;
            background: white;
            transition: all 0.3s ease;
            outline: none;
        }

        .search-input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(91, 31, 31, 0.1);
        }

        .search-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            width: 20px;
            height: 20px;
            color: var(--text-light);
        }

        .search-button {
            padding: 18px 32px;
            background: linear-gradient(135deg, var(--primary-color), #7a2a2a);
            color: white;
            border: none;
            border-radius: var(--border-radius);
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            white-space: nowrap;
        }

        .search-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(91, 31, 31, 0.3);
        }

        /* Filter Section */
        .filter-section {
            background: white;
            padding: 30px 40px;
            margin-bottom: 40px;
        }

        .filter-grid {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .filter-label {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 5px;
        }

        .filter-select {
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            background: white;
            font-size: 14px;
            color: var(--text-dark);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .filter-select:focus {
            border-color: var(--primary-color);
            outline: none;
        }

        /* Results Section */
        .results-section {
            padding: 0 40px 80px;
        }

        .results-header {
            max-width: 1200px;
            margin: 0 auto 40px;
            text-align: center;
        }

        .results-title {
            font-size: 32px;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 10px;
        }

        .results-count {
            color: var(--text-light);
            font-size: 16px;
        }

        /* Enhanced Alumni Grid */
        .alumni-grid {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
        }

        .alumni-card {
            background: white;
            border-radius: var(--border-radius-lg);
            padding: 30px;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            border: 1px solid #f3f4f6;
            position: relative;
            overflow: hidden;
        }

        .alumni-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-xl);
            border-color: var(--secondary-color);
        }

        .card-header {
            text-align: center;
            margin-bottom: 25px;
        }

        .alumni-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            margin: 0 auto 15px;
            overflow: hidden;
            border: 3px solid var(--secondary-color);
            box-shadow: var(--shadow);
        }

        .alumni-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .alumni-name {
            font-size: 20px;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 5px;
        }

        .alumni-batch {
            color: var(--secondary-color);
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .alumni-location {
            color: var(--text-light);
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            margin-bottom: 20px;
        }

        .alumni-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            justify-content: center;
            margin-bottom: 25px;
        }

        .tag {
            background: linear-gradient(135deg, rgba(236, 195, 92, 0.1), rgba(91, 31, 31, 0.05));
            color: var(--primary-color);
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            border: 1px solid rgba(236, 195, 92, 0.3);
        }

        .card-actions {
            display: flex;
            gap: 12px;
            justify-content: center;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), #7a2a2a);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(91, 31, 31, 0.3);
        }

        .btn-secondary {
            background: transparent;
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
        }

        .btn-secondary:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-2px);
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .directory-header {
                padding: 40px 20px;
            }

            .header-title {
                font-size: 36px;
            }

            .search-section {
                margin: -20px 20px 0;
                padding: 30px 20px;
            }

            .search-form {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .filter-section {
                padding: 20px;
            }

            .filter-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .results-section {
                padding: 0 20px 60px;
            }

            .alumni-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .alumni-card {
                padding: 25px 20px;
            }
        }

        @media (max-width: 480px) {
            .directory-header {
                padding: 30px 15px;
            }

            .header-title {
                font-size: 28px;
            }

            .search-section {
                margin: -15px 15px 0;
                padding: 25px 15px;
            }
        }
    </style>
</head>
<body>
    <?php include '../sidebar.php'; ?>

    <div class="main-content" id="mainContent">
        <!-- Professional Directory Header -->
        <div class="directory-header">
            <div class="header-content">
                <h1 class="header-title">Alumni Directory</h1>
                <p class="header-subtitle">Connect with graduates from around the world</p>
            </div>
        </div>

        <!-- Enhanced Search Interface -->
        <div class="search-section">
            <div class="search-container">
                <form class="search-form">
                    <div class="search-input-wrapper">
                        <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="M21 21l-4.35-4.35"></path>
                        </svg>
                        <input type="text" class="search-input" placeholder="Search by name, location, or field..." id="alumniSearch">
                    </div>
                    <button type="submit" class="search-button">Search Alumni</button>
                </form>
            </div>
        </div>

        <!-- Filter Controls -->
        <div class="filter-section">
            <div class="filter-grid">
                <div class="filter-group">
                    <label class="filter-label">Graduation Year</label>
                    <select class="filter-select" id="batchFilter">
                        <option value="">All Years</option>
                        <option value="2023">2023</option>
                        <option value="2022">2022</option>
                        <option value="2021">2021</option>
                        <option value="2020">2020</option>
                        <option value="2019">2019</option>
                        <option value="2018">2018</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label">Location</label>
                    <select class="filter-select" id="locationFilter">
                        <option value="">All Locations</option>
                        <option value="bengaluru">Bengaluru</option>
                        <option value="delhi">Delhi</option>
                        <option value="mumbai">Mumbai</option>
                        <option value="usa">USA</option>
                        <option value="uk">UK</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label">Field of Work</label>
                    <select class="filter-select" id="fieldFilter">
                        <option value="">All Fields</option>
                        <option value="technology">Technology</option>
                        <option value="finance">Finance</option>
                        <option value="healthcare">Healthcare</option>
                        <option value="education">Education</option>
                        <option value="entrepreneur">Entrepreneur</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Search Results -->
        <div class="results-section">
            <div class="results-header">
                <h2 class="results-title">Alumni Profiles</h2>
                <p class="results-count" id="resultsCount">Showing all alumni</p>
            </div>

            <div class="alumni-grid">
                <div class="alumni-card">
                    <div class="card-header">
                        <div class="alumni-avatar">
                            <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=300&h=300&fit=crop" alt="John Anderson">
                        </div>
                        <div class="alumni-name">John Anderson</div>
                        <div class="alumni-batch">Class of 2018</div>
                        <div class="alumni-location">📍 San Francisco, USA</div>
                        <div class="alumni-tags">
                            <span class="tag">Technology</span>
                            <span class="tag">Entrepreneur</span>
                        </div>
                    </div>
                    <div class="card-actions">
                        <button class="btn btn-primary">Connect</button>
                        <button class="btn btn-secondary">Message</button>
                    </div>
                </div>

                <div class="alumni-card">
                    <div class="card-header">
                        <div class="alumni-avatar">
                            <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=300&h=300&fit=crop" alt="Sarah Mitchell">
                        </div>
                        <div class="alumni-name">Sarah Mitchell</div>
                        <div class="alumni-batch">Class of 2019</div>
                        <div class="alumni-location">📍 London, UK</div>
                        <div class="alumni-tags">
                            <span class="tag">Finance</span>
                            <span class="tag">Mentor</span>
                        </div>
                    </div>
                    <div class="card-actions">
                        <button class="btn btn-primary">Connect</button>
                        <button class="btn btn-secondary">Message</button>
                    </div>
                </div>

                <div class="alumni-card">
                    <div class="card-header">
                        <div class="alumni-avatar">
                            <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=300&h=300&fit=crop" alt="Michael Chen">
                        </div>
                        <div class="alumni-name">Michael Chen</div>
                        <div class="alumni-batch">Class of 2017</div>
                        <div class="alumni-location">📍 Singapore</div>
                        <div class="alumni-tags">
                            <span class="tag">AI/ML</span>
                            <span class="tag">Research</span>
                        </div>
                    </div>
                    <div class="card-actions">
                        <button class="btn btn-primary">Connect</button>
                        <button class="btn btn-secondary">Message</button>
                    </div>
                </div>

                <div class="alumni-card">
                    <div class="card-header">
                        <div class="alumni-avatar">
                            <img src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=300&h=300&fit=crop" alt="Emily Rodriguez">
                        </div>
                        <div class="alumni-name">Emily Rodriguez</div>
                        <div class="alumni-batch">Class of 2020</div>
                        <div class="alumni-location">📍 Bengaluru, India</div>
                        <div class="alumni-tags">
                            <span class="tag">Product Design</span>
                            <span class="tag">UX</span>
                        </div>
                    </div>
                    <div class="card-actions">
                        <button class="btn btn-primary">Connect</button>
                        <button class="btn btn-secondary">Message</button>
                    </div>
                </div>

                <div class="alumni-card">
                    <div class="card-header">
                        <div class="alumni-avatar">
                            <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=300&h=300&fit=crop" alt="David Thompson">
                        </div>
                        <div class="alumni-name">David Thompson</div>
                        <div class="alumni-batch">Class of 2016</div>
                        <div class="alumni-location">📍 New York, USA</div>
                        <div class="alumni-tags">
                            <span class="tag">Marketing</span>
                            <span class="tag">Strategy</span>
                        </div>
                    </div>
                    <div class="card-actions">
                        <button class="btn btn-primary">Connect</button>
                        <button class="btn btn-secondary">Message</button>
                    </div>
                </div>

                <div class="alumni-card">
                    <div class="card-header">
                        <div class="alumni-avatar">
                            <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=300&h=300&fit=crop" alt="Lisa Patel">
                        </div>
                        <div class="alumni-name">Lisa Patel</div>
                        <div class="alumni-batch">Class of 2021</div>
                        <div class="alumni-location">📍 Mumbai, India</div>
                        <div class="alumni-tags">
                            <span class="tag">Healthcare</span>
                            <span class="tag">Social Impact</span>
                        </div>
                    </div>
                    <div class="card-actions">
                        <button class="btn btn-primary">Connect</button>
                        <button class="btn btn-secondary">Message</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Include main JavaScript file -->
    <script src="../assets/js/script.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize dropdown toggles for the alumni menu
            const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
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

            const alumniSearch = document.getElementById('alumniSearch');
            const batchFilter = document.getElementById('batchFilter');
            const locationFilter = document.getElementById('locationFilter');
            const fieldFilter = document.getElementById('fieldFilter');
            const profileCards = document.querySelectorAll('.alumni-card');
            const resultsCount = document.getElementById('resultsCount');

            // Enhanced search functionality
            function searchAlumni() {
                const searchTerm = alumniSearch.value.toLowerCase();
                const batchValue = batchFilter.value;
                const locationValue = locationFilter.value.toLowerCase();
                const fieldValue = fieldFilter.value.toLowerCase();

                let visibleCount = 0;

                profileCards.forEach(card => {
                    const profileName = card.querySelector('.alumni-name').textContent.toLowerCase();
                    const profileBatch = card.querySelector('.alumni-batch').textContent.toLowerCase();
                    const profileLocation = card.querySelector('.alumni-location').textContent.toLowerCase();
                    const profileTags = Array.from(card.querySelectorAll('.tag')).map(tag => tag.textContent.toLowerCase());

                    const matchesSearch = searchTerm === '' ||
                        profileName.includes(searchTerm) ||
                        profileTags.some(tag => tag.includes(searchTerm));

                    const matchesBatch = batchValue === '' ||
                        profileBatch.includes(batchValue);

                    const matchesLocation = locationValue === '' ||
                        profileLocation.includes(locationValue);

                    const matchesField = fieldValue === '' ||
                        profileTags.some(tag => tag.includes(fieldValue));

                    if (matchesSearch && matchesBatch && matchesLocation && matchesField) {
                        card.style.display = 'block';
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0)';
                        visibleCount++;
                    } else {
                        card.style.display = 'none';
                    }
                });

                // Update results count
                if (visibleCount === profileCards.length) {
                    resultsCount.textContent = 'Showing all alumni';
                } else {
                    resultsCount.textContent = `Showing ${visibleCount} alumni`;
                }
            }

            // Event listeners
            if (alumniSearch) {
                alumniSearch.addEventListener('input', searchAlumni);
            }

            if (batchFilter) {
                batchFilter.addEventListener('change', searchAlumni);
            }

            if (locationFilter) {
                locationFilter.addEventListener('change', searchAlumni);
            }

            if (fieldFilter) {
                fieldFilter.addEventListener('change', searchAlumni);
            }

            // Initialize
            searchAlumni();
        });
    </script>
</body>
</html>
