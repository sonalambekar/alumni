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

        .page-header p {
            font-size: 18px;
            opacity: 0.9;
        }
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 10000;
        }

        .modal.show {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 20px;
            padding: 40px;
            max-width: 700px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #eee;
        }

        .modal-header h2 {
            color: var(--primary-color);
            font-size: 28px;
            margin: 0;
        }

        .close {
            font-size: 30px;
            color: var(--text-light);
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .close:hover {
            color: var(--primary-color);
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-dark);
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(91, 31, 31, 0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }

        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 30px;
        }

        @media (max-width: 768px) {
            .modal-content {
                padding: 30px 20px;
                width: 95%;
            }

            .form-actions {
                flex-direction: column;
            }

            .form-actions button {
                width: 100%;
            }

            .page-header h1 {
                font-size: 32px;
            }
        }
    </style>
</head>
    <?php
    // Check if specific job type filter is requested
    $jobTypeFilter = '';
    if (isset($_GET['type']) && $_GET['type'] === 'internship') {
        $jobTypeFilter = 'internship';
    }

    // Check if post job mode is requested
    $showPostJob = isset($_GET['post']) && $_GET['post'] === 'true';
    ?>

    <div class="main-content" id="mainContent">
        <!-- Page Header -->
        <div class="page-header">
            <h1>
                <?php
                if ($jobTypeFilter === 'internship') {
                    echo 'Internship Opportunities';
                } elseif ($showPostJob) {
                    echo 'Post Job Vacancies';
                } else {
                    echo 'Job Board';
                }
                ?>
            </h1>
            <p>
                <?php
                if ($jobTypeFilter === 'internship') {
                    echo 'Discover exciting internship opportunities shared by alumni and partner companies';
                } elseif ($showPostJob) {
                    echo 'Share job openings from your company and help fellow alumni advance their careers';
                } else {
                    echo 'Find your next career opportunity in our exclusive alumni job board';
                }
                ?>
            </p>
        </div>

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

        <!-- Post Job Button -->
        <?php if ($showPostJob): ?>
        <div style="text-align: right; margin: 20px auto; max-width: 1000px;">
            <button class="btn" onclick="togglePostJobModal()" style="background: var(--secondary-color);">
                <i class="fas fa-plus"></i> Post Job
            </button>
        </div>
        <?php endif; ?>

        <!-- Post Job Modal -->
        <div id="postJobModal" class="modal" style="display: none;">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Post a Job</h2>
                    <span class="close" onclick="closePostJobModal()">&times;</span>
                </div>
                <form id="postJobForm">
                    <div class="form-group">
                        <label for="jobTitle">Job Title</label>
                        <input type="text" id="jobTitle" name="title" required>
                    </div>
                    <div class="form-group">
                        <label for="companyName">Company Name</label>
                        <input type="text" id="companyName" name="company" required>
                    </div>
                    <div class="form-group">
                        <label for="jobDescription">Job Description</label>
                        <textarea id="jobDescription" name="description" rows="4" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="jobRequirements">Requirements</label>
                        <textarea id="jobRequirements" name="requirements" rows="3"></textarea>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label for="jobLocation">Location</label>
                            <input type="text" id="jobLocation" name="location" required>
                        </div>
                        <div class="form-group">
                            <label for="jobType">Job Type</label>
                            <select id="jobType" name="job_type" required>
                                <option value="full-time">Full-time</option>
                                <option value="part-time">Part-time</option>
                                <option value="contract">Contract</option>
                                <option value="internship">Internship</option>
                                <option value="freelance">Freelance</option>
                            </select>
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label for="experienceLevel">Experience Level</label>
                            <select id="experienceLevel" name="experience_level">
                                <option value="entry">Entry Level</option>
                                <option value="mid">Mid Level</option>
                                <option value="senior">Senior Level</option>
                                <option value="executive">Executive</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="salaryRange">Salary Range</label>
                            <input type="text" id="salaryRange" name="salary_range" placeholder="e.g., ₹10-15 LPA">
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label for="applicationDeadline">Application Deadline</label>
                            <input type="date" id="applicationDeadline" name="application_deadline">
                        </div>
                        <div class="form-group">
                            <label for="contactEmail">Contact Email</label>
                            <input type="email" id="contactEmail" name="contact_email" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="applicationUrl">Application URL (optional)</label>
                        <input type="url" id="applicationUrl" name="application_url" placeholder="https://company.com/careers/job">
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="closePostJobModal()">Cancel</button>
                        <button type="submit" class="btn">Post Job</button>
                    </div>
                </form>
            </div>
        </div>
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
        // Modal functions
        function togglePostJobModal() {
            const modal = document.getElementById('postJobModal');
            modal.classList.toggle('show');
        }

        function closePostJobModal() {
            document.getElementById('postJobModal').classList.remove('show');
        }

        // Close modal when clicking outside
        document.getElementById('postJobModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closePostJobModal();
            }
        });

        // Handle post job form submission
        document.getElementById('postJobForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch('/alumni/submit_job.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Job posted successfully!');
                    closePostJobModal();
                    this.reset();
                    // Optionally reload the page or add the job to the list
                } else {
                    alert('Error posting job. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error posting job. Please try again.');
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            const jobSearch = document.getElementById('jobSearch');
            const locationFilter = document.getElementById('locationFilter');
            const typeFilter = document.getElementById('typeFilter');
            const searchBtn = document.querySelector('.search-btn');
            const jobCards = document.querySelectorAll('.job-card');

            // Set initial filter based on URL parameter
            <?php if ($jobTypeFilter === 'internship'): ?>
                if (typeFilter) {
                    typeFilter.value = 'internship';
                }
            <?php endif; ?>

            // Job search functionality
            function searchJobs() {
                const searchTerm = jobSearch ? jobSearch.value.toLowerCase() : '';
                const locationValue = locationFilter ? locationFilter.value.toLowerCase() : '';
                const typeValue = typeFilter ? typeFilter.value.toLowerCase() : '';

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
