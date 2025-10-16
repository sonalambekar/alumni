<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Board - Alumni Connect</title>
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

        .jobs-content {
            padding: 20px 40px 60px;
            background-color: var(--bg-light);
        }

        .jobs-list {
            max-width: 1000px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 25px;
        }

        .job-card {
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

        .job-card:hover {
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

        .job-details h3 {
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

        .job-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 12px;
        }

        .job-meta-item {
            display: flex;
            align-items: center;
            gap: 6px;
            color: var(--text-light);
            font-size: 14px;
        }

        .job-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .job-tag {
            background-color: rgba(236, 195, 92, 0.2);
            color: var(--primary-color);
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .job-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
            align-items: flex-end;
        }

        .job-posted {
            color: var(--text-light);
            font-size: 13px;
        }

        @media (max-width: 768px) {
            .job-card {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .company-logo {
                margin: 0 auto;
            }

            .job-meta {
                justify-content: center;
            }

            .job-actions {
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
                    <input type="text" id="jobSearch" placeholder="Search jobs by title, company, location, or skills..." class="search-input">
                    <button class="search-btn">Search</button>
                </div>
                <div class="search-filters">
                    <select class="filter-select" id="locationFilter">
                        <option value="">All Locations</option>
                        <option value="bengaluru">Bengaluru</option>
                        <option value="mumbai">Mumbai</option>
                        <option value="delhi">Delhi</option>
                        <option value="hyderabad">Hyderabad</option>
                        <option value="pune">Pune</option>
                    </select>
                    <select class="filter-select" id="typeFilter">
                        <option value="">All Types</option>
                        <option value="full-time">Full-time</option>
                        <option value="part-time">Part-time</option>
                        <option value="contract">Contract</option>
                        <option value="internship">Internship</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="jobs-content">
            <div class="jobs-list">
                <div class="job-card">
                    <div class="company-logo">TI</div>
                    <div class="job-details">
                        <h3>Senior Software Engineer</h3>
                        <div class="company-name">Tech Innovations Inc.</div>
                        <div class="job-meta">
                            <span class="job-meta-item">📍 Bengaluru, India</span>
                            <span class="job-meta-item">💼 Full-time</span>
                            <span class="job-meta-item">💰 ₹20-30 LPA</span>
                        </div>
                        <div class="job-tags">
                            <span class="job-tag">React</span>
                            <span class="job-tag">Node.js</span>
                            <span class="job-tag">AWS</span>
                        </div>
                    </div>
                    <div class="job-actions">
                        <button class="btn">Apply Now</button>
                        <span class="job-posted">Posted 2 days ago</span>
                    </div>
                </div>

                <div class="job-card">
                    <div class="company-logo">FG</div>
                    <div class="job-details">
                        <h3>Product Manager</h3>
                        <div class="company-name">FinTech Global</div>
                        <div class="job-meta">
                            <span class="job-meta-item">📍 Mumbai, India</span>
                            <span class="job-meta-item">💼 Full-time</span>
                            <span class="job-meta-item">💰 ₹25-35 LPA</span>
                        </div>
                        <div class="job-tags">
                            <span class="job-tag">Product Strategy</span>
                            <span class="job-tag">Agile</span>
                            <span class="job-tag">FinTech</span>
                        </div>
                    </div>
                    <div class="job-actions">
                        <button class="btn">Apply Now</button>
                        <span class="job-posted">Posted 5 days ago</span>
                    </div>
                </div>

                <div class="job-card">
                    <div class="company-logo">DS</div>
                    <div class="job-details">
                        <h3>Data Scientist</h3>
                        <div class="company-name">DataSmart Analytics</div>
                        <div class="job-meta">
                            <span class="job-meta-item">📍 Hyderabad, India</span>
                            <span class="job-meta-item">💼 Full-time</span>
                            <span class="job-meta-item">💰 ₹18-28 LPA</span>
                        </div>
                        <div class="job-tags">
                            <span class="job-tag">Python</span>
                            <span class="job-tag">Machine Learning</span>
                            <span class="job-tag">SQL</span>
                        </div>
                    </div>
                    <div class="job-actions">
                        <button class="btn">Apply Now</button>
                        <span class="job-posted">Posted 1 week ago</span>
                    </div>
                </div>

                <div class="job-card">
                    <div class="company-logo">MC</div>
                    <div class="job-details">
                        <h3>Marketing Manager</h3>
                        <div class="company-name">Marketing Creatives Ltd.</div>
                        <div class="job-meta">
                            <span class="job-meta-item">📍 Delhi, India</span>
                            <span class="job-meta-item">💼 Full-time</span>
                            <span class="job-meta-item">💰 ₹15-22 LPA</span>
                        </div>
                        <div class="job-tags">
                            <span class="job-tag">Digital Marketing</span>
                            <span class="job-tag">SEO</span>
                            <span class="job-tag">Content Strategy</span>
                        </div>
                    </div>
                    <div class="job-actions">
                        <button class="btn">Apply Now</button>
                        <span class="job-posted">Posted 1 week ago</span>
                    </div>
                </div>

                <div class="job-card">
                    <div class="company-logo">UX</div>
                    <div class="job-details">
                        <h3>UX/UI Designer</h3>
                        <div class="company-name">UX Design Studio</div>
                        <div class="job-meta">
                            <span class="job-meta-item">📍 Pune, India</span>
                            <span class="job-meta-item">💼 Full-time</span>
                            <span class="job-meta-item">💰 ₹12-18 LPA</span>
                        </div>
                        <div class="job-tags">
                            <span class="job-tag">Figma</span>
                            <span class="job-tag">User Research</span>
                            <span class="job-tag">Prototyping</span>
                        </div>
                    </div>
                    <div class="job-actions">
                        <button class="btn">Apply Now</button>
                        <span class="job-posted">Posted 2 weeks ago</span>
                    </div>
                </div>

                <div class="job-card">
                    <div class="company-logo">BC</div>
                    <div class="job-details">
                        <h3>Business Analyst</h3>
                        <div class="company-name">Business Consulting Group</div>
                        <div class="job-meta">
                            <span class="job-meta-item">📍 Gurgaon, India</span>
                            <span class="job-meta-item">💼 Full-time</span>
                            <span class="job-meta-item">💰 ₹16-24 LPA</span>
                        </div>
                        <div class="job-tags">
                            <span class="job-tag">Business Intelligence</span>
                            <span class="job-tag">Excel</span>
                            <span class="job-tag">Tableau</span>
                        </div>
                    </div>
                    <div class="job-actions">
                        <button class="btn">Apply Now</button>
                        <span class="job-posted">Posted 2 weeks ago</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const jobSearch = document.getElementById('jobSearch');
            const locationFilter = document.getElementById('locationFilter');
            const typeFilter = document.getElementById('typeFilter');
            const searchBtn = document.querySelector('.search-btn');
            const jobCards = document.querySelectorAll('.job-card');

            // Job search functionality
            function searchJobs() {
                const searchTerm = jobSearch.value.toLowerCase();
                const locationValue = locationFilter.value.toLowerCase();
                const typeValue = typeFilter.value.toLowerCase();

                jobCards.forEach(card => {
                    const jobTitle = card.querySelector('h3').textContent.toLowerCase();
                    const companyName = card.querySelector('.company-name').textContent.toLowerCase();
                    const jobLocation = card.querySelector('.job-meta-item').textContent.toLowerCase();
                    const jobTags = Array.from(card.querySelectorAll('.job-tag')).map(tag => tag.textContent.toLowerCase());

                    const matchesSearch = searchTerm === '' ||
                        jobTitle.includes(searchTerm) ||
                        companyName.includes(searchTerm) ||
                        jobTags.some(tag => tag.includes(searchTerm));

                    const matchesLocation = locationValue === '' ||
                        jobLocation.includes(locationValue);

                    const matchesType = typeValue === '' ||
                        card.querySelector('.job-meta').textContent.toLowerCase().includes(typeValue);

                    if (matchesSearch && matchesLocation && matchesType) {
                        card.style.display = 'grid';
                    } else {
                        card.style.display = 'none';
                    }
                });
            }

            // Event listeners
            if (jobSearch) {
                jobSearch.addEventListener('input', searchJobs);
            }

            if (locationFilter) {
                locationFilter.addEventListener('change', searchJobs);
            }

            if (typeFilter) {
                typeFilter.addEventListener('change', searchJobs);
            }

            if (searchBtn) {
                searchBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    searchJobs();
                });
            }

            // Initialize search with current filters
            searchJobs();
        });
    </script>
</body>
</html>
