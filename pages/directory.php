<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alumni Directory - Alumni Connect</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .search-hero {
            background: linear-gradient(135deg, var(--primary-color) 0%, #7a2a2a 100%);
            color: white;
            padding: 80px 40px;
            position: relative;
            overflow: hidden;
        }

        .search-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="20" cy="20" r="2" fill="rgba(255,255,255,0.1)"/><circle cx="80" cy="40" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="40" cy="80" r="1.5" fill="rgba(255,255,255,0.1)"/></svg>');
            opacity: 0.3;
        }

        .search-container-enhanced {
            max-width: 1200px;
            margin: 0 auto;
            position: relative;
            z-index: 2;
        }

        .search-header {
            text-align: center;
            margin-bottom: 50px;
        }

        .search-header h2 {
            font-size: 48px;
            font-weight: 700;
            margin-bottom: 15px;
            background: linear-gradient(45deg, #ffffff, #f0f0f0);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .search-header p {
            font-size: 18px;
            opacity: 0.9;
            font-weight: 300;
        }

        .search-controls {
            display: flex;
            flex-direction: column;
            gap: 30px;
            align-items: center;
        }

        .search-input-group {
            position: relative;
            width: 100%;
            max-width: 600px;
        }

        .search-input-enhanced {
            width: 100%;
            padding: 20px 25px 20px 60px;
            border: none;
            border-radius: 50px;
            font-size: 18px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            outline: none;
        }

        .search-input-enhanced:focus {
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            transform: translateY(-2px);
        }

        .search-input-enhanced::placeholder {
            color: #666;
            font-style: italic;
        }

        .search-icon {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            width: 24px;
            height: 24px;
            color: var(--primary-color);
            z-index: 3;
        }

        .filter-controls {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            justify-content: center;
            width: 100%;
            max-width: 800px;
        }

        .filter-select {
            padding: 15px 25px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 25px;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            min-width: 180px;
        }

        .filter-select:focus {
            outline: none;
            border-color: rgba(255, 255, 255, 0.6);
            background: rgba(255, 255, 255, 0.2);
        }

        .filter-select option {
            background: var(--primary-color);
            color: white;
        }

        .search-btn {
            padding: 15px 35px;
            background: linear-gradient(135deg, var(--secondary-color) 0%, #ffd700 100%);
            color: var(--primary-color);
            border: none;
            border-radius: 30px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(255, 215, 0, 0.3);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .search-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(255, 215, 0, 0.4);
            background: linear-gradient(135deg, #ffd700 0%, var(--secondary-color) 100%);
        }

        .directory-content {
            padding: 80px 40px;
            background: linear-gradient(180deg, var(--bg-light) 0%, #f8f9fa 100%);
        }

        .alumni-directory-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 25px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .alumni-profile-card {
            background: linear-gradient(135deg, white 0%, #f8f9fa 100%);
            border-radius: 20px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            transition: all 0.4s ease;
            border: 1px solid rgba(255, 255, 255, 0.8);
            position: relative;
            overflow: hidden;
        }

        .alumni-profile-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(91, 31, 31, 0.05) 0%, rgba(255, 215, 0, 0.05) 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .alumni-profile-card:hover::before {
            opacity: 1;
        }

        .alumni-profile-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            border-color: var(--secondary-color);
        }

        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin: 0 auto 20px;
            overflow: hidden;
            border: 3px solid linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            position: relative;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .alumni-profile-card:hover .profile-avatar img {
            transform: scale(1.1);
        }

        .profile-name {
            font-size: 18px;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 6px;
            letter-spacing: -0.5px;
        }

        .profile-batch {
            color: var(--secondary-color);
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .profile-location {
            color: var(--text-light);
            font-size: 13px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .profile-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            justify-content: center;
            margin-bottom: 20px;
        }

        .tag {
            background: linear-gradient(135deg, rgba(255, 215, 0, 0.2), rgba(91, 31, 31, 0.1));
            color: var(--primary-color);
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            border: 1px solid rgba(255, 215, 0, 0.3);
            transition: all 0.3s ease;
        }

        .tag:hover {
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
            color: white;
            transform: translateY(-2px);
        }

        .profile-actions {
            display: flex;
            gap: 10px;
            justify-content: center;
        }

        .btn-small {
            padding: 8px 16px;
            font-size: 12px;
            border-radius: 20px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-small:first-child {
            background: linear-gradient(135deg, var(--primary-color), #7a2a2a);
            border: 2px solid transparent;
        }

        .btn-small:first-child:hover {
            background: linear-gradient(135deg, #7a2a2a, var(--primary-color));
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(91, 31, 31, 0.3);
        }

        .btn-small:last-child {
            background: transparent;
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
        }

        .btn-small:last-child:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-2px);
        }

        @media (max-width: 1200px) {
            .alumni-directory-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 20px;
            }
        }

        @media (max-width: 900px) {
            .alumni-directory-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 20px;
            }
        }

        @media (max-width: 768px) {
            .search-hero {
                padding: 60px 20px;
            }

            .search-header h2 {
                font-size: 36px;
            }

            .search-input-enhanced {
                font-size: 16px;
                padding: 18px 20px 18px 50px;
            }

            .search-icon {
                left: 15px;
                width: 20px;
                height: 20px;
            }

            .filter-controls {
                flex-direction: column;
                gap: 15px;
            }

            .filter-select {
                min-width: auto;
                width: 100%;
            }

            .directory-content {
                padding: 60px 20px;
            }

            .alumni-directory-grid {
                grid-template-columns: 1fr;
                gap: 25px;
            }

            .alumni-profile-card {
                padding: 25px 20px;
            }

            .profile-avatar {
                width: 90px;
                height: 90px;
            }
        }

        @media (max-width: 480px) {
            .search-hero {
                padding: 40px 15px;
            }

            .search-header h2 {
                font-size: 28px;
            }

            .search-input-enhanced {
                font-size: 15px;
                padding: 15px 18px 15px 45px;
            }

            .search-btn {
                padding: 12px 25px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <?php include '../sidebar.php'; ?>

    <div class="main-content" id="mainContent">
        <!-- Enhanced Search Section -->
        <div class="search-hero">
            <div class="search-container-enhanced">
                <div class="search-header">
                    <h2>Find Alumni</h2>
                    <p>Connect with graduates from around the world</p>
                </div>
                <div class="search-controls">
                    <div class="search-input-group">
                        <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="M21 21l-4.35-4.35"></path>
                        </svg>
                        <input type="text" class="search-input-enhanced" placeholder="Search by name, location, or field..." id="alumniSearch">
                    </div>
                    <div class="filter-controls">
                        <select class="filter-select" id="batchFilter">
                            <option value="">All Years</option>
                            <option value="2023">2023</option>
                            <option value="2022">2022</option>
                            <option value="2021">2021</option>
                            <option value="2020">2020</option>
                            <option value="2019">2019</option>
                            <option value="2018">2018</option>
                        </select>
                        <select class="filter-select" id="locationFilter">
                            <option value="">All Locations</option>
                            <option value="bengaluru">Bengaluru</option>
                            <option value="delhi">Delhi</option>
                            <option value="mumbai">Mumbai</option>
                            <option value="usa">USA</option>
                            <option value="uk">UK</option>
                        </select>
                        <button class="search-btn" id="searchBtn">Search</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="directory-content">
            <div class="alumni-directory-grid">
                <div class="alumni-profile-card">
                    <div class="profile-avatar">
                        <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=300&h=300&fit=crop" alt="John Anderson">
                    </div>
                    <div class="profile-name">John Anderson</div>
                    <div class="profile-batch">Class of 2018</div>
                    <div class="profile-location">📍 San Francisco, USA</div>
                    <div class="profile-tags">
                        <span class="tag">Technology</span>
                        <span class="tag">Entrepreneur</span>
                    </div>
                    <div class="profile-actions">
                        <button class="btn btn-small">Connect</button>
                        <button class="btn btn-secondary btn-small">Message</button>
                    </div>
                </div>

                <div class="alumni-profile-card">
                    <div class="profile-avatar">
                        <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=300&h=300&fit=crop" alt="Sarah Mitchell">
                    </div>
                    <div class="profile-name">Sarah Mitchell</div>
                    <div class="profile-batch">Class of 2019</div>
                    <div class="profile-location">📍 London, UK</div>
                    <div class="profile-tags">
                        <span class="tag">Finance</span>
                        <span class="tag">Mentor</span>
                    </div>
                    <div class="profile-actions">
                        <button class="btn btn-small">Connect</button>
                        <button class="btn btn-secondary btn-small">Message</button>
                    </div>
                </div>

                <div class="alumni-profile-card">
                    <div class="profile-avatar">
                        <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=300&h=300&fit=crop" alt="Michael Chen">
                    </div>
                    <div class="profile-name">Michael Chen</div>
                    <div class="profile-batch">Class of 2017</div>
                    <div class="profile-location">📍 Singapore</div>
                    <div class="profile-tags">
                        <span class="tag">AI/ML</span>
                        <span class="tag">Research</span>
                    </div>
                    <div class="profile-actions">
                        <button class="btn btn-small">Connect</button>
                        <button class="btn btn-secondary btn-small">Message</button>
                    </div>
                </div>

                <div class="alumni-profile-card">
                    <div class="profile-avatar">
                        <img src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=300&h=300&fit=crop" alt="Emily Rodriguez">
                    </div>
                    <div class="profile-name">Emily Rodriguez</div>
                    <div class="profile-batch">Class of 2020</div>
                    <div class="profile-location">📍 Bengaluru, India</div>
                    <div class="profile-tags">
                        <span class="tag">Product Design</span>
                        <span class="tag">UX</span>
                    </div>
                    <div class="profile-actions">
                        <button class="btn btn-small">Connect</button>
                        <button class="btn btn-secondary btn-small">Message</button>
                    </div>
                </div>

                <div class="alumni-profile-card">
                    <div class="profile-avatar">
                        <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=300&h=300&fit=crop" alt="David Thompson">
                    </div>
                    <div class="profile-name">David Thompson</div>
                    <div class="profile-batch">Class of 2016</div>
                    <div class="profile-location">📍 New York, USA</div>
                    <div class="profile-tags">
                        <span class="tag">Marketing</span>
                        <span class="tag">Strategy</span>
                    </div>
                    <div class="profile-actions">
                        <button class="btn btn-small">Connect</button>
                        <button class="btn btn-secondary btn-small">Message</button>
                    </div>
                </div>

                <div class="alumni-profile-card">
                    <div class="profile-avatar">
                        <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=300&h=300&fit=crop" alt="Lisa Patel">
                    </div>
                    <div class="profile-name">Lisa Patel</div>
                    <div class="profile-batch">Class of 2021</div>
                    <div class="profile-location">📍 Mumbai, India</div>
                    <div class="profile-tags">
                        <span class="tag">Healthcare</span>
                        <span class="tag">Social Impact</span>
                    </div>
                    <div class="profile-actions">
                        <button class="btn btn-small">Connect</button>
                        <button class="btn btn-secondary btn-small">Message</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const alumniSearch = document.getElementById('alumniSearch');
            const batchFilter = document.getElementById('batchFilter');
            const locationFilter = document.getElementById('locationFilter');
            const searchBtn = document.getElementById('searchBtn');
            const profileCards = document.querySelectorAll('.alumni-profile-card');

            // Alumni search functionality
            function searchAlumni() {
                const searchTerm = alumniSearch.value.toLowerCase();
                const batchValue = batchFilter.value;
                const locationValue = locationFilter.value.toLowerCase();

                profileCards.forEach(card => {
                    const profileName = card.querySelector('.profile-name').textContent.toLowerCase();
                    const profileBatch = card.querySelector('.profile-batch').textContent.toLowerCase();
                    const profileLocation = card.querySelector('.profile-location').textContent.toLowerCase();
                    const profileTags = Array.from(card.querySelectorAll('.tag')).map(tag => tag.textContent.toLowerCase());

                    const matchesSearch = searchTerm === '' ||
                        profileName.includes(searchTerm) ||
                        profileTags.some(tag => tag.includes(searchTerm));

                    const matchesBatch = batchValue === '' ||
                        profileBatch.includes(batchValue);

                    const matchesLocation = locationValue === '' ||
                        profileLocation.includes(locationValue);

                    if (matchesSearch && matchesBatch && matchesLocation) {
                        card.style.display = 'block';
                        card.style.animation = 'fadeInUp 0.5s ease-out';
                    } else {
                        card.style.display = 'none';
                    }
                });
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

            if (searchBtn) {
                searchBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    searchAlumni();
                });
            }

            // Add CSS animation for fade in effect
            const style = document.createElement('style');
            style.textContent = `
                @keyframes fadeInUp {
                    from {
                        opacity: 0;
                        transform: translateY(20px);
                    }
                    to {
                        opacity: 1;
                        transform: translateY(0);
                    }
                }
            `;
            document.head.appendChild(style);

            // Initialize search
            searchAlumni();
        });
    </script>
</body>
</html>
