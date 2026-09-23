<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Internship Opportunities - Alumni Connect</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .page-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, #7a2a2a 100%);
            color: var(--white);
            padding: 60px 40px;
            text-align: center;
        }

        .page-header h1 {
            font-size: 42px;
            margin-bottom: 15px;
        }

        .search-container {
            position: sticky;
            top: 20px;
            z-index: 100;
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin: 20px auto;
            max-width: 1000px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .search-wrapper {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .search-input-container {
            display: flex;
            align-items: center;
            background: var(--bg-light);
            border: 2px solid #e0e0e0;
            border-radius: 50px;
            padding: 5px;
            transition: border-color 0.3s ease;
        }

        .search-input-container:focus-within {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(91, 31, 31, 0.1);
        }

        .search-icon {
            width: 20px;
            height: 20px;
            color: var(--text-light);
            margin: 0 15px;
            flex-shrink: 0;
        }

        .search-input {
            flex: 1;
            border: none;
            outline: none;
            background: transparent;
            padding: 15px 10px;
            font-size: 16px;
            color: var(--text-dark);
        }

        .search-input::placeholder {
            color: var(--text-light);
        }

        .search-btn {
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 25px;
            padding: 12px 25px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin: 0 10px;
        }

        .search-btn:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
        }

        .search-filters {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .filter-select {
            padding: 12px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 25px;
            background: white;
            font-size: 14px;
            color: var(--text-dark);
            cursor: pointer;
            transition: border-color 0.3s ease;
            min-width: 150px;
        }

        .filter-select:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        .internships-content {
            padding: 20px 40px 60px;
            background-color: var(--bg-light);
        }

        .internships-list {
            max-width: 1000px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 25px;
        }

        .internship-card {
            background-color: var(--white);
            border-radius: 15px;
            padding: 30px;
            box-shadow: var(--shadow);
            transition: var(--transition);
            border: 2px solid transparent;
            display: grid;
            grid-template-columns: 80px 1fr auto;
            gap: 25px;
            align-items: center;
        }

        .internship-card:hover {
            border-color: var(--secondary-color);
            box-shadow: var(--shadow-lg);
        }

        .company-logo {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary-color), #7a2a2a);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-size: 28px;
            font-weight: 700;
            flex-shrink: 0;
        }

        .internship-details h3 {
            font-size: 22px;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 8px;
        }

        .company-name {
            color: var(--secondary-color);
            font-weight: 500;
            margin-bottom: 12px;
        }

        .internship-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 12px;
        }

        .internship-meta-item {
            display: flex;
            align-items: center;
            gap: 6px;
            color: var(--text-light);
            font-size: 14px;
        }

        .internship-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .internship-tag {
            background-color: rgba(236, 195, 92, 0.2);
            color: var(--primary-color);
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .internship-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
            align-items: flex-end;
        }

        .internship-posted {
            color: var(--text-light);
            font-size: 13px;
        }

        @media (max-width: 768px) {
            .internship-card {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .company-logo {
                margin: 0 auto;
            }

            .internship-meta {
                justify-content: center;
            }

            .internship-actions {
                align-items: center;
            }

            .page-header h1 {
                font-size: 32px;
            }

            .search-container {
                margin: 10px;
                padding: 15px;
            }

            .search-input-container {
                flex-direction: column;
                gap: 10px;
                padding: 10px;
            }

            .search-icon {
                margin: 0;
                align-self: flex-start;
            }

            .search-input {
                width: 100%;
                padding: 10px;
            }

            .search-btn {
                width: 100%;
                margin: 0;
                padding: 15px;
            }

            .search-filters {
                flex-direction: column;
                gap: 10px;
            }

            .filter-select {
                width: 100%;
                min-width: auto;
            }
        }
    </style>
</head>
<body>
    <?php include '../sidebar.php'; ?>

    <div class="main-content" id="mainContent">
        <!-- Floating Search Bar -->
        <div class="search-container">
            <div class="search-wrapper">
                <div class="search-input-container">
                    <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="M21 21l-4.35-4.35"></path>
                    </svg>
                    <input type="text" id="internshipSearch" placeholder="Search internships by title, company, location, or skills..." class="search-input">
                    <button class="search-btn">Search</button>
                </div>
                <div class="search-filters">
                    <select class="filter-select" id="locationFilter">
                        <option value="">All Locations</option>
                        <option value="remote">Remote</option>
                        <option value="san-francisco">San Francisco</option>
                        <option value="boston">Boston</option>
                        <option value="new-york">New York</option>
                        <option value="chicago">Chicago</option>
                    </select>
                    <select class="filter-select" id="durationFilter">
                        <option value="">All Durations</option>
                        <option value="summer">Summer</option>
                        <option value="fall">Fall</option>
                        <option value="spring">Spring</option>
                        <option value="winter">Winter</option>
                        <option value="year-round">Year-round</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="internships-content">
            <div class="internships-list">
                <div class="internship-card">
                    <div class="company-logo">TC</div>
                    <div class="internship-details">
                        <h3>Software Engineering Intern</h3>
                        <div class="company-name">TechCorp Inc.</div>
                        <div class="internship-meta">
                            <span class="internship-meta-item">📍 San Francisco, CA</span>
                            <span class="internship-meta-item">⏰ Summer 2024</span>
                            <span class="internship-meta-item">💰 $7,000 - $9,000/month</span>
                        </div>
                        <div class="internship-tags">
                            <span class="internship-tag">Full-time</span>
                            <span class="internship-tag">On-site</span>
                            <span class="internship-tag">Paid</span>
                        </div>
                    </div>
                    <div class="internship-actions">
                        <button class="btn">Apply Now</button>
                        <span class="internship-posted">Posted 2 days ago</span>
                    </div>
                </div>

                <div class="internship-card">
                    <div class="company-logo">DG</div>
                    <div class="internship-details">
                        <h3>Marketing Intern</h3>
                        <div class="company-name">DesignGuru</div>
                        <div class="internship-meta">
                            <span class="internship-meta-item">📍 Remote</span>
                            <span class="internship-meta-item">⏰ Fall 2024</span>
                            <span class="internship-meta-item">💰 $20 - $25/hour</span>
                        </div>
                        <div class="internship-tags">
                            <span class="internship-tag">Part-time</span>
                            <span class="internship-tag">Remote</span>
                            <span class="internship-tag">Paid</span>
                        </div>
                    </div>
                    <div class="internship-actions">
                        <button class="btn">Apply Now</button>
                        <span class="internship-posted">Posted 5 days ago</span>
                    </div>
                </div>

                <div class="internship-card">
                    <div class="company-logo">AI</div>
                    <div class="internship-details">
                        <h3>AI Research Intern</h3>
                        <div class="company-name">AI Innovations</div>
                        <div class="internship-meta">
                            <span class="internship-meta-item">📍 Boston, MA</span>
                            <span class="internship-meta-item">⏰ Spring 2025</span>
                            <span class="internship-meta-item">💰 $8,500/month</span>
                        </div>
                        <div class="internship-tags">
                            <span class="internship-tag">Full-time</span>
                            <span class="internship-tag">On-site</span>
                            <span class="internship-tag">Housing Stipend</span>
                        </div>
                    </div>
                    <div class="internship-actions">
                        <button class="btn">Apply Now</button>
                        <span class="internship-posted">Posted 1 week ago</span>
                    </div>
                </div>

                <div class="internship-card">
                    <div class="company-logo">FB</div>
                    <div class="internship-details">
                        <h3>Finance Intern</h3>
                        <div class="company-name">Finance Bros LLC</div>
                        <div class="internship-meta">
                            <span class="internship-meta-item">📍 New York, NY</span>
                            <span class="internship-meta-item">⏰ Summer 2024</span>
                            <span class="internship-meta-item">💰 $6,500/month</span>
                        </div>
                        <div class="internship-tags">
                            <span class="internship-tag">Full-time</span>
                            <span class="internship-tag">On-site</span>
                            <span class="internship-tag">Paid</span>
                        </div>
                    </div>
                    <div class="internship-actions">
                        <button class="btn">Apply Now</button>
                        <span class="internship-posted">Posted 1 week ago</span>
                    </div>
                </div>

                <div class="internship-card">
                    <div class="company-logo">CD</div>
                    <div class="internship-details">
                        <h3>UX Design Intern</h3>
                        <div class="company-name">Creative Digital</div>
                        <div class="internship-meta">
                            <span class="internship-meta-item">📍 Chicago, IL</span>
                            <span class="internship-meta-item">⏰ Fall 2024</span>
                            <span class="internship-meta-item">💰 $22/hour</span>
                        </div>
                        <div class="internship-tags">
                            <span class="internship-tag">Part-time</span>
                            <span class="internship-tag">Hybrid</span>
                            <span class="internship-tag">Paid</span>
                        </div>
                    </div>
                    <div class="internship-actions">
                        <button class="btn">Apply Now</button>
                        <span class="internship-posted">Posted 2 weeks ago</span>
                    </div>
                </div>

                <div class="internship-card">
                    <div class="company-logo">DS</div>
                    <div class="internship-details">
                        <h3>Data Science Intern</h3>
                        <div class="company-name">DataSmart Solutions</div>
                        <div class="internship-meta">
                            <span class="internship-meta-item">📍 Remote</span>
                            <span class="internship-meta-item">⏰ Year-round</span>
                            <span class="internship-meta-item">💰 $5,000 - $7,000/month</span>
                        </div>
                        <div class="internship-tags">
                            <span class="internship-tag">Full-time</span>
                            <span class="internship-tag">Remote</span>
                            <span class="internship-tag">Flexible</span>
                        </div>
                    </div>
                    <div class="internship-actions">
                        <button class="btn">Apply Now</button>
                        <span class="internship-posted">Posted 2 weeks ago</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const internshipSearch = document.getElementById('internshipSearch');
            const locationFilter = document.getElementById('locationFilter');
            const durationFilter = document.getElementById('durationFilter');
            const searchBtn = document.querySelector('.search-btn');
            const internshipCards = document.querySelectorAll('.internship-card');

            // Internship search functionality
            function searchInternships() {
                const searchTerm = internshipSearch.value.toLowerCase();
                const locationValue = locationFilter.value.toLowerCase();
                const durationValue = durationFilter.value.toLowerCase();

                internshipCards.forEach(card => {
                    const internshipTitle = card.querySelector('h3').textContent.toLowerCase();
                    const companyName = card.querySelector('.company-name').textContent.toLowerCase();
                    const internshipLocation = card.querySelector('.internship-meta-item').textContent.toLowerCase();
                    const internshipDuration = card.querySelectorAll('.internship-meta-item')[1].textContent.toLowerCase();
                    const internshipTags = Array.from(card.querySelectorAll('.internship-tag')).map(tag => tag.textContent.toLowerCase());

                    const matchesSearch = searchTerm === '' ||
                        internshipTitle.includes(searchTerm) ||
                        companyName.includes(searchTerm) ||
                        internshipTags.some(tag => tag.includes(searchTerm));

                    const matchesLocation = locationValue === '' ||
                        internshipLocation.includes(locationValue);

                    const matchesDuration = durationValue === '' ||
                        internshipDuration.includes(durationValue);

                    if (matchesSearch && matchesLocation && matchesDuration) {
                        card.style.display = 'grid';
                    } else {
                        card.style.display = 'none';
                    }
                });
            }

            // Event listeners
            if (internshipSearch) {
                internshipSearch.addEventListener('input', searchInternships);
            }

            if (locationFilter) {
                locationFilter.addEventListener('change', searchInternships);
            }

            if (durationFilter) {
                durationFilter.addEventListener('change', searchInternships);
            }

            if (searchBtn) {
                searchBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    searchInternships();
                });
            }

            // Initialize search with current filters
            searchInternships();
        });
    </script>
</body>
</html>