<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] <= 0) {
    // User is not logged in, redirect to login page
    header("Location: /alumni/login.php");
    exit();
}

// If user is logged in, check if they're a director and redirect to admin dashboard
if (isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0) {
    require_once 'includes/db_config.php';
    $stmt = $pdo->prepare("SELECT is_director FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if ($user && $user['is_director'] == 1) {
        // Set admin role if not already set
        if (!isset($_SESSION['role'])) {
            $_SESSION['role'] = 'admin';
        }
        header("Location: /alumni/admin/admin_dashboard.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alumni Connect - Home</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Leaflet CSS (Free OpenStreetMap library) -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
          crossorigin=""/>
</head>
<body>
    <?php
    // Note: User authentication is handled by the login requirement at the top
    ?>

    <!-- Fixed Logout Button -->
    <div class="fixed-logout" style="position: fixed; top: 20px; right: 80px; z-index: 1000; background: white; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
        <a href="/alumni/logout.php" style="background: #5b1f1f; color: white; border: none; padding: 10px 16px; border-radius: 8px; font-size: 14px; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; transition: background 0.3s ease;" onmouseover="this.style.background='#4a1919'" onmouseout="this.style.background='#5b1f1f'">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>

    <?php include 'sidebar.php'; ?>

    <div class="main-content" id="mainContent">
        <!-- Hero Section -->
        <section class="hero-section">
            <div class="hero-image">
                <img src="assets/images/Generated Image October 17, 2025 - 11_10AM.png" alt="Alumni Event">
            </div>
            <div class="hero-content">
                <h1>About Our <span>Alumni Network</span></h1>
                <p>Welcome to the Alumni Connect platform, where memories meet opportunities. Our vibrant community brings together graduates from across the globe, fostering connections that transcend time and distance.</p>
                <p>Whether you're looking to reconnect with old friends, mentor the next generation, or explore new career opportunities, our platform provides the tools and resources to help you stay engaged with your alma mater.</p>
                <p>Join thousands of alumni who are making a difference in their communities and industries worldwide.</p>
            </div>
        </section>

        <!-- Vision & Stats Section -->
        <section class="vision-section">
            <h2>Our Vision</h2>
            <p>To create a thriving global community of alumni who inspire, support, and empower each other while contributing to the growth and excellence of our institution.</p>
            
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-number" data-target="15000">0</div>
                    <div class="stat-label">Active Members</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" data-target="50">0</div>
                    <div class="stat-label">Graduating Batches</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" data-target="85">0</div>
                    <div class="stat-label">Cities Worldwide</div>
                </div>
            </div>
        </section>

        <!-- Alumni Highlights Section -->
        <section class="alumni-highlights">
            <div class="section-header">
                <h2>Meet Our Alumni</h2>
                <p>Celebrating the achievements of our distinguished alumni community</p>
            </div>
            
            <div class="alumni-grid">
                <div class="alumni-card" onclick="location.href='pages/directory.php'">
                    <div class="alumni-avatar">
                        <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=300&h=300&fit=crop" alt="Alumni 1">
                    </div>
                    <div class="alumni-name">John Anderson</div>
                </div>
                <div class="alumni-card" onclick="location.href='pages/directory.php'">
                    <div class="alumni-avatar">
                        <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=300&h=300&fit=crop" alt="Alumni 2">
                    </div>
                    <div class="alumni-name">Sarah Mitchell</div>
                </div>
                <div class="alumni-card" onclick="location.href='pages/directory.php'">
                    <div class="alumni-avatar">
                        <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=300&h=300&fit=crop" alt="Alumni 3">
                    </div>
                    <div class="alumni-name">Michael Chen</div>
                </div>
                <div class="alumni-card" onclick="location.href='pages/directory.php'">
                    <div class="alumni-avatar">
                        <img src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=300&h=300&fit=crop" alt="Alumni 4">
                    </div>
                    <div class="alumni-name">Emily Rodriguez</div>
                </div>
                <div class="alumni-card" onclick="location.href='pages/directory.php'">
                    <div class="alumni-avatar">
                        <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=300&h=300&fit=crop" alt="Alumni 5">
                    </div>
                    <div class="alumni-name">David Thompson</div>
                </div>
            </div>
        </section>

        <!-- Alumni Groups Section -->
        <section class="alumni-groups">
            <div class="section-header">
                <h2>Alumni Groups</h2>
                <p>Connect with like-minded alumni through our special interest groups</p>
            </div>
            
            <div class="groups-grid">
                <div class="group-card">
                    <div class="group-image">
                        <img src="https://images.unsplash.com/photo-1559136555-9303baea8ebd?w=600&h=400&fit=crop" alt="Entrepreneurs">
                    </div>
                    <div class="group-content">
                        <h3>Entrepreneurs</h3>
                        <p>A dynamic community of alumni who have ventured into entrepreneurship, sharing insights, challenges, and success stories.</p>
                        <button class="btn">Join Group</button>
                    </div>
                </div>
                
                <div class="group-card">
                    <div class="group-image">
                        <img src="https://images.unsplash.com/photo-1451187580459-43490279c0fa?w=600&h=400&fit=crop" alt="Innovators">
                    </div>
                    <div class="group-content">
                        <h3>Innovators</h3>
                        <p>Connect with alumni driving innovation in technology, science, and creative industries across the globe.</p>
                        <button class="btn">Join Group</button>
                    </div>
                </div>
                
                <div class="group-card">
                    <div class="group-image">
                        <img src="https://images.unsplash.com/photo-1469571486292-0ba58a3f068b?w=600&h=400&fit=crop" alt="Social Impact">
                    </div>
                    <div class="group-content">
                        <h3>Social Impact</h3>
                        <p>Join alumni dedicated to making a difference through social entrepreneurship, NGOs, and community development.</p>
                        <button class="btn">Join Group</button>
                    </div>
                </div>
                
                <div class="group-card">
                    <div class="group-image">
                        <img src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=600&h=400&fit=crop" alt="Women Leaders">
                    </div>
                    <div class="group-content">
                        <h3>Women Leaders</h3>
                        <p>Empowering women alumni through mentorship, networking, and leadership development opportunities.</p>
                        <button class="btn">Join Group</button>
                    </div>
                </div>

                <div class="group-card">
                    <div class="group-image">
                        <img src="https://images.unsplash.com/photo-1517077304055-6e89abbf09b0?w=600&h=400&fit=crop" alt="Technology & AI">
                    </div>
                    <div class="group-content">
                        <h3>Technology & AI</h3>
                        <p>Connect with alumni at the forefront of artificial intelligence, machine learning, and emerging technologies shaping the future.</p>
                        <button class="btn">Join Group</button>
                    </div>
                </div>

                <div class="group-card">
                    <div class="group-image">
                        <img src="https://images.unsplash.com/photo-1541961017774-22349e4a1262?w=600&h=400&fit=crop" alt="Arts & Culture">
                    </div>
                    <div class="group-content">
                        <h3>Arts & Culture</h3>
                        <p>Join creative alumni in literature, visual arts, music, theater, and cultural preservation initiatives worldwide.</p>
                        <button class="btn">Join Group</button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Chapters Section -->
        <section class="chapters-section">
            <div class="section-header">
                <h2>Regional Chapters</h2>
                <p>Connect with alumni in your city or region</p>
            </div>
            
            <div class="chapters-grid">
                <div class="chapter-card">
                    <div class="chapter-icon">🇮🇳</div>
                    <h3>Bengaluru Chapter</h3>
                    <p>2,500+ Members</p>
                    <button class="btn btn-secondary">View Members</button>
                </div>
                
                <div class="chapter-card">
                    <div class="chapter-icon">🇮🇳</div>
                    <h3>Delhi NCR Chapter</h3>
                    <p>3,200+ Members</p>
                    <button class="btn btn-secondary">View Members</button>
                </div>
                
                <div class="chapter-card">
                    <div class="chapter-icon">🇺🇸</div>
                    <h3>USA Chapter</h3>
                    <p>1,800+ Members</p>
                    <button class="btn btn-secondary">View Members</button>
                </div>
                
                <div class="chapter-card">
                    <div class="chapter-icon">🇬🇧</div>
                    <h3>UK & Europe Chapter</h3>
                    <p>1,200+ Members</p>
                    <button class="btn btn-secondary">View Members</button>
                </div>

                <div class="chapter-card">
                    <div class="chapter-icon">🇸🇬</div>
                    <h3>Asia-Pacific Chapter</h3>
                    <p>950+ Members</p>
                    <button class="btn btn-secondary">View Members</button>
                </div>

                <div class="chapter-card">
                    <div class="chapter-icon">🇦🇪</div>
                    <h3>Middle East & Africa Chapter</h3>
                    <p>680+ Members</p>
                    <button class="btn btn-secondary">View Members</button>
                </div>
            </div>
        </section>

        <!-- Worldwide Alumni Map -->
        <section class="map-section">
            <div class="section-header">
                <h2>Our Alumni Around the World</h2>
                <p>Explore where our alumni are making an impact globally</p>
            </div>
            
            <div class="map-container">
                <div id="map"></div>
            </div>
        </section>

        <!-- Alumni Services Cards Section -->
        <section class="services-section">
            <div class="section-header">
                <h2>Alumni Services</h2>
                <p>Access exclusive services and opportunities designed for our alumni community</p>
            </div>

            <div class="services-grid">
                <div class="service-card" onclick="location.href='pages/mentorship.php'">
                    <div class="service-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>Mentor</h3>
                    <p>Connect with experienced alumni mentors to guide your career journey and professional development.</p>
                    <div class="service-stats">
                        <span>500+ Mentors</span>
                        <span>1200+ Sessions</span>
                    </div>
                </div>

                <div class="service-card" onclick="location.href='pages/jobs.php?type=internship'">
                    <div class="service-icon">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <h3>Internships</h3>
                    <p>Discover exclusive internship opportunities shared by alumni and partner companies.</p>
                    <div class="service-stats">
                        <span>200+ Opportunities</span>
                        <span>50+ Companies</span>
                    </div>
                </div>

                <div class="service-card" onclick="location.href='pages/jobs.php?post=true'">
                    <div class="service-icon">
                        <i class="fas fa-plus-circle"></i>
                    </div>
                    <h3>Post Job Vacancies</h3>
                    <p>Share job openings from your company and help fellow alumni advance their careers.</p>
                    <div class="service-stats">
                        <span>Post Jobs</span>
                        <span>For Free</span>
                    </div>
                </div>

                <div class="service-card" onclick="toggleExperienceModal()">
                    <div class="service-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <h3>Share Your Experience</h3>
                    <p>Share your professional journey, insights, and advice with the alumni community.</p>
                    <div class="service-stats">
                        <span>300+ Stories</span>
                        <span>Inspiring</span>
                    </div>
                </div>

                <div class="service-card" onclick="location.href='pages/scholarship.php'">
                    <div class="service-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <h3>Institution Scholarship</h3>
                    <p>Support current students through scholarships and educational funding programs.</p>
                    <div class="service-stats">
                        <span>₹50L+ Donated</span>
                        <span>100+ Students</span>
                    </div>
                </div>

                <div class="service-card" onclick="location.href='pages/institute_medal.php'">
                    <div class="service-icon">
                        <i class="fas fa-medal"></i>
                    </div>
                    <h3>Institute Medal</h3>
                    <p>Nominate deserving alumni for institutional recognition and awards program.</p>
                    <div class="service-stats">
                        <span>Annual Awards</span>
                        <span>Recognition</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Experience Sharing Modal -->
        <div id="experienceModal" class="modal" style="display: none;">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Share Your Experience</h2>
                    <span class="close" onclick="closeExperienceModal()">&times;</span>
                </div>
                <form id="experienceForm">
                    <div class="form-group">
                        <label for="experienceTitle">Experience Title</label>
                        <input type="text" id="experienceTitle" name="title" required>
                    </div>
                    <div class="form-group">
                        <label for="experienceContent">Share your story, insights, or advice</label>
                        <textarea id="experienceContent" name="content" rows="6" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="experienceCategory">Category</label>
                        <select id="experienceCategory" name="category">
                            <option value="career">Career Advice</option>
                            <option value="entrepreneurship">Entrepreneurship</option>
                            <option value="leadership">Leadership</option>
                            <option value="personal">Personal Growth</option>
                            <option value="industry">Industry Insights</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeExperienceModal()">Cancel</button>
                        <button type="submit" class="btn">Share Experience</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Experience Stories Section -->
        <section class="experience-section" id="experienceSection">
            <div class="section-header">
                <h2>Alumni Experiences</h2>
                <p>Insights, stories, and wisdom shared by our community</p>
            </div>

            <div class="experience-grid" id="experienceGrid">
                <!-- Experience cards will be loaded here -->
            </div>
        </section>
            <div class="footer-content">
                <div class="footer-section">
                    <h3>About Alumni Connect</h3>
                    <p>Building bridges between past, present, and future generations of our institution. Stay connected, stay inspired.</p>
                    <div class="social-links">
                        <a href="#" class="social-icon">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                        </a>
                        <a href="#" class="social-icon">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                            </svg>
                        </a>
                        <a href="#" class="social-icon">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                            </svg>
                        </a>
                        <a href="#" class="social-icon">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 0C8.74 0 8.333.015 7.053.072 5.775.132 4.905.333 4.14.63c-.789.306-1.459.717-2.126 1.384S.935 3.35.63 4.14C.333 4.905.131 5.775.072 7.053.012 8.333 0 8.74 0 12s.015 3.667.072 4.947c.06 1.277.261 2.148.558 2.913.306.788.717 1.459 1.384 2.126.667.666 1.336 1.079 2.126 1.384.766.296 1.636.499 2.913.558C8.333 23.988 8.74 24 12 24s3.667-.015 4.947-.072c1.277-.06 2.148-.262 2.913-.558.788-.306 1.459-.718 2.126-1.384.666-.667 1.079-1.335 1.384-2.126.296-.765.499-1.636.558-2.913.06-1.28.072-1.687.072-4.947s-.015-3.667-.072-4.947c-.06-1.277-.262-2.149-.558-2.913-.306-.789-.718-1.459-1.384-2.126C21.319 1.347 20.651.935 19.86.63c-.765-.297-1.636-.499-2.913-.558C15.667.012 15.26 0 12 0zm0 2.16c3.203 0 3.585.016 4.85.071 1.17.055 1.805.249 2.227.415.562.217.96.477 1.382.896.419.42.679.819.896 1.381.164.422.36 1.057.413 2.227.057 1.266.07 1.646.07 4.85s-.015 3.585-.074 4.85c-.061 1.17-.256 1.805-.421 2.227-.224.562-.479.96-.899 1.382-.419.419-.824.679-1.38.896-.42.164-1.065.36-2.235.413-1.274.057-1.649.07-4.859.07-3.211 0-3.586-.015-4.859-.074-1.171-.061-1.816-.256-2.236-.421-.569-.224-.96-.479-1.379-.899-.421-.419-.69-.824-.9-1.38-.165-.42-.359-1.065-.42-2.235-.045-1.26-.061-1.649-.061-4.844 0-3.196.016-3.586.061-4.861.061-1.17.255-1.814.42-2.234.21-.57.479-.96.9-1.381.419-.419.81-.689 1.379-.898.42-.166 1.051-.361 2.221-.421 1.275-.045 1.65-.06 4.859-.06l.045.03zm0 3.678c-3.405 0-6.162 2.76-6.162 6.162 0 3.405 2.76 6.162 6.162 6.162 3.405 0 6.162-2.76 6.162-6.162 0-3.405-2.76-6.162-6.162-6.162zM12 16c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4zm7.846-10.405c0 .795-.646 1.44-1.44 1.44-.795 0-1.44-.646-1.44-1.44 0-.794.646-1.439 1.44-1.439.793-.001 1.44.645 1.44 1.439z"/>
                            </svg>
                        </a>
                    </div>
                </div>
                
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <div class="footer-links">
                        <a href="pages/directory.php">Alumni Directory</a>
                        <a href="pages/events.php">Upcoming Events</a>
                        <a href="pages/jobs.php">Job Board</a>
                        <a href="pages/news.php">News & Updates</a>
                    </div>
                </div>
                
                <div class="footer-section">
                    <h3>Resources</h3>
                    <div class="footer-links">
                        <a href="pages/mentorship.php">Mentorship Program</a>
                        <a href="pages/fundraising.php">Support the Fund</a>
                        <a href="pages/yearbook.php">Yearbook</a>
                        <a href="pages/galleries.php">Photo Galleries</a>
                    </div>
                </div>
                
                <div class="footer-section">
                    <h3>Contact Us</h3>
                    <p>Email: alumni@university.edu</p>
                    <p>Phone: +1 (555) 123-4567</p>
                    <p>Address: 123 University Ave, Campus City, ST 12345</p>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2025 Alumni Connect. All rights reserved. | Privacy Policy | Terms of Service</p>
            </div>
        </footer>
    </div>

    <script>
        // Modal functions
        function toggleExperienceModal() {
            const modal = document.getElementById('experienceModal');
            modal.classList.toggle('show');
        }

        function closeExperienceModal() {
            document.getElementById('experienceModal').classList.remove('show');
        }

        // Close modal when clicking outside
        document.getElementById('experienceModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeExperienceModal();
            }
        });

        // Handle experience form submission
        document.getElementById('experienceForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch('/alumni/submit_experience.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Experience shared successfully!');
                    closeExperienceModal();
                    loadExperiences();
                    this.reset();
                } else {
                    alert('Error sharing experience. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error sharing experience. Please try again.');
            });
        });

        // Load experiences on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadExperiences();
        });

        // Function to load experiences
        function loadExperiences() {
            fetch('/alumni/get_experiences.php')
            .then(response => response.json())
            .then(data => {
                const experienceGrid = document.getElementById('experienceGrid');
                experienceGrid.innerHTML = '';

                if (data.length === 0) {
                    experienceGrid.innerHTML = '<p style="text-align: center; color: var(--text-light); font-style: italic;">No experiences shared yet. Be the first to share yours!</p>';
                    return;
                }

                data.forEach(experience => {
                    const experienceCard = createExperienceCard(experience);
                    experienceGrid.appendChild(experienceCard);
                });
            })
            .catch(error => {
                console.error('Error loading experiences:', error);
            });
        }

        // Function to create experience card HTML
        function createExperienceCard(experience) {
            const card = document.createElement('div');
            card.className = 'experience-card';

            card.innerHTML = `
                <div class="experience-header">
                    <div class="experience-author">
                        <div class="experience-avatar">${experience.author_name.charAt(0).toUpperCase()}</div>
                        <div class="experience-meta">
                            <div class="experience-name">${experience.author_name}</div>
                            <div class="experience-date">${new Date(experience.created_at).toLocaleDateString()}</div>
                        </div>
                    </div>
                </div>
                <div class="experience-title">${experience.title}</div>
                <div class="experience-category">${experience.category}</div>
                <div class="experience-content">${experience.content}</div>
            `;

            return card;
        }
    </script>

        /* Services Cards Section */
        .services-section {
            padding: 80px 40px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }

        .services-grid {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
        }

        .service-card {
            background: white;
            border-radius: 20px;
            padding: 40px 30px;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            cursor: pointer;
            border: 2px solid transparent;
        }

        .service-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            border-color: var(--secondary-color);
        }

        .service-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary-color), #7a2a2a);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            font-size: 32px;
            color: white;
        }

        .service-card h3 {
            font-size: 24px;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 15px;
        }

        .service-card p {
            color: var(--text-light);
            line-height: 1.6;
            margin-bottom: 25px;
            font-size: 15px;
        }

        .service-stats {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: auto;
        }

        .service-stats span {
            background: rgba(236, 195, 92, 0.1);
            color: var(--primary-color);
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        /* Modal Styles */
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
            max-width: 600px;
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
            min-height: 120px;
        }

        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 30px;
        }

        /* Experience Section */
        .experience-section {
            padding: 80px 40px;
            background: white;
        }

        .experience-grid {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
        }

        .experience-card {
            background: var(--bg-light);
            border-radius: 15px;
            padding: 30px;
            border-left: 5px solid var(--secondary-color);
            transition: transform 0.3s ease;
        }

        .experience-card:hover {
            transform: translateY(-5px);
        }

        .experience-header {
            display: flex;
            justify-content: between;
            align-items: start;
            margin-bottom: 20px;
        }

        .experience-author {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .experience-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color), #7a2a2a);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 18px;
        }

        .experience-meta {
            flex: 1;
        }

        .experience-name {
            font-weight: 600;
            color: var(--text-dark);
            font-size: 16px;
        }

        .experience-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 10px;
        }

        .experience-category {
            background: rgba(91, 31, 31, 0.1);
            color: var(--primary-color);
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
            margin-bottom: 15px;
        }

        .experience-content {
            color: var(--text-light);
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .experience-date {
            color: var(--text-light);
            font-size: 13px;
        }

        @media (max-width: 768px) {
            .services-section,
            .experience-section {
                padding: 60px 20px;
            }

            .services-grid,
            .experience-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .service-card {
                padding: 30px 20px;
            }

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
        }
            .fixed-logout {
                top: 80px !important; /* Below sidebar toggle */
                right: 15px !important;
                left: 15px !important;
                display: flex !important;
                justify-content: flex-end !important;
            }

            .fixed-logout a {
                padding: 8px 14px !important;
                font-size: 13px !important;
            }
        }
    </style>
</body>
</html>
