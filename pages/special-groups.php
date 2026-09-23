<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Special Interest Groups - Alumni Connect</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #5b1f1f;
            --secondary-color: #ecc35c;
            --bg-light: #f5f7fb;
            --text-color: #333;
            --text-light: #6b7280;
            --border-color: #e5e7eb;
            --white: #ffffff;
            --shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.05);
            --shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.1);
            --border-radius: 8px;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-light);
            color: var(--text-color);
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }

        .groups-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, #7a2a2a 100%);
            color: white;
            padding: 60px 40px;
            text-align: center;
        }

        .groups-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .main-content {
            margin-left: 250px;
            padding: 30px;
            min-height: 100vh;
            transition: margin 0.3s ease;
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 20px 15px;
            }
        }

        .groups-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .groups-title {
            font-size: 28px;
            color: var(--primary-color);
            margin: 0;
        }

        .btn {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn:hover {
            background-color: #4a1919;
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .btn-secondary {
            background-color: var(--secondary-color);
            color: var(--primary-color);
        }

        .groups-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }

        .group-card {
            background: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .group-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .group-header {
            padding: 20px;
            text-align: center;
            background: linear-gradient(135deg, var(--primary-color) 0%, #7a2a2a 100%);
            color: white;
        }

        .group-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            margin: 0 auto 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
        }

        .group-name {
            font-size: 20px;
            font-weight: 600;
            margin: 10px 0 5px;
        }

        .group-category {
            font-size: 14px;
            opacity: 0.9;
            margin: 0 0 10px;
        }

        .group-body {
            padding: 20px;
        }

        .group-members {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 15px;
        }

        .member-avatar {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            border: 2px solid var(--white);
            margin-left: -8px;
        }

        .member-avatar:first-child {
            margin-left: 0;
        }

        .member-count {
            font-size: 12px;
            color: var(--text-light);
            margin-left: 8px;
            align-self: center;
        }

        .group-description {
            color: var(--text-light);
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .group-stats {
            display: flex;
            justify-content: space-between;
            font-size: 13px;
            color: var(--text-light);
            border-top: 1px solid var(--border-color);
            padding-top: 15px;
            margin-top: 15px;
        }

        .stat {
            text-align: center;
        }

        .stat-value {
            font-weight: 700;
            color: var(--primary-color);
            display: block;
            font-size: 16px;
        }

        .group-actions {
            display: flex;
            gap: 10px;
        }

        .btn-outline {
            background: transparent;
            border: 1px solid var(--primary-color);
            color: var(--primary-color);
            flex: 1;
            text-align: center;
            padding: 8px 15px;
            font-size: 14px;
        }

        @media (max-width: 768px) {
            .groups-nav {
                flex-direction: column;
                align-items: flex-start;
            }

            .groups-actions {
                width: 100%;
                justify-content: space-between;
            }

            .btn {
                width: 100%;
                justify-content: center;
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body>
    <?php include '../sidebar.php'; ?>

    <div class="main-content" id="mainContent">
        <div class="groups-header">
            <h1>Special Interest Groups</h1>
            <p>Connect with alumni who share your passions and interests</p>
        </div>

        <div class="groups-container">
            <div class="groups-nav">
                <h2 class="groups-title">Active Groups</h2>
                <div class="groups-actions">
                    <a href="#create-group" class="btn">
                        <i class="fas fa-plus"></i> Create Interest Group
                    </a>
                </div>
            </div>

            <div class="groups-grid">
                <!-- Group 1: Tech Enthusiasts -->
                <div class="group-card">
                    <div class="group-header">
                        <div class="group-icon">
                            <i class="fas fa-laptop-code"></i>
                        </div>
                        <h3 class="group-name">Tech Enthusiasts</h3>
                        <p class="group-category">Technology & Innovation</p>
                    </div>
                    <div class="group-body">
                        <div class="group-members">
                            <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Member" class="member-avatar">
                            <img src="https://randomuser.me/api/portraits/women/44.jpg" alt="Member" class="member-avatar">
                            <img src="https://randomuser.me/api/portraits/men/67.jpg" alt="Member" class="member-avatar">
                            <img src="https://randomuser.me/api/portraits/women/22.jpg" alt="Member" class="member-avatar">
                            <span class="member-count">+15 more</span>
                        </div>
                        <p class="group-description">
                            A community for technology lovers to share ideas, discuss latest trends, and collaborate on innovative projects.
                        </p>
                        <div class="group-stats">
                            <div class="stat">
                                <span class="stat-value">47</span>
                                <span>Members</span>
                            </div>
                            <div class="stat">
                                <span class="stat-value">12</span>
                                <span>Events</span>
                            </div>
                            <div class="stat">
                                <span class="stat-value">3</span>
                                <span>Projects</span>
                            </div>
                        </div>
                        <div class="group-actions">
                            <a href="#" class="btn btn-outline">
                                <i class="fas fa-users"></i> View Members
                            </a>
                            <a href="#" class="btn">
                                <i class="fas fa-sign-in-alt"></i> Join Group
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Group 2: Entrepreneurs Network -->
                <div class="group-card">
                    <div class="group-header">
                        <div class="group-icon">
                            <i class="fas fa-handshake"></i>
                        </div>
                        <h3 class="group-name">Entrepreneurs Network</h3>
                        <p class="group-category">Business & Startups</p>
                    </div>
                    <div class="group-body">
                        <div class="group-members">
                            <img src="https://randomuser.me/api/portraits/men/12.jpg" alt="Member" class="member-avatar">
                            <img src="https://randomuser.me/api/portraits/women/31.jpg" alt="Member" class="member-avatar">
                            <img src="https://randomuser.me/api/portraits/men/45.jpg" alt="Member" class="member-avatar">
                            <span class="member-count">+8 more</span>
                        </div>
                        <p class="group-description">
                            Connect with fellow entrepreneurs, share business ideas, and get support for your startup journey.
                        </p>
                        <div class="group-stats">
                            <div class="stat">
                                <span class="stat-value">23</span>
                                <span>Members</span>
                            </div>
                            <div class="stat">
                                <span class="stat-value">8</span>
                                <span>Events</span>
                            </div>
                            <div class="stat">
                                <span class="stat-value">5</span>
                                <span>Startups</span>
                            </div>
                        </div>
                        <div class="group-actions">
                            <a href="#" class="btn btn-outline">
                                <i class="fas fa-users"></i> View Members
                            </a>
                            <a href="#" class="btn">
                                <i class="fas fa-sign-in-alt"></i> Join Group
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Group 3: Arts & Culture -->
                <div class="group-card">
                    <div class="group-header">
                        <div class="group-icon">
                            <i class="fas fa-palette"></i>
                        </div>
                        <h3 class="group-name">Arts & Culture</h3>
                        <p class="group-category">Creative Arts</p>
                    </div>
                    <div class="group-body">
                        <div class="group-members">
                            <img src="https://randomuser.me/api/portraits/women/28.jpg" alt="Member" class="member-avatar">
                            <img src="https://randomuser.me/api/portraits/men/55.jpg" alt="Member" class="member-avatar">
                            <img src="https://randomuser.me/api/portraits/women/63.jpg" alt="Member" class="member-avatar">
                            <img src="https://randomuser.me/api/portraits/men/78.jpg" alt="Member" class="member-avatar">
                            <span class="member-count">+12 more</span>
                        </div>
                        <p class="group-description">
                            Explore creativity through art exhibitions, cultural events, and collaborative artistic projects.
                        </p>
                        <div class="group-stats">
                            <div class="stat">
                                <span class="stat-value">34</span>
                                <span>Members</span>
                            </div>
                            <div class="stat">
                                <span class="stat-value">15</span>
                                <span>Events</span>
                            </div>
                            <div class="stat">
                                <span class="stat-value">7</span>
                                <span>Exhibitions</span>
                            </div>
                        </div>
                        <div class="group-actions">
                            <a href="#" class="btn btn-outline">
                                <i class="fas fa-users"></i> View Members
                            </a>
                            <a href="#" class="btn">
                                <i class="fas fa-sign-in-alt"></i> Join Group
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Group 4: Sports & Fitness -->
                <div class="group-card">
                    <div class="group-header">
                        <div class="group-icon">
                            <i class="fas fa-running"></i>
                        </div>
                        <h3 class="group-name">Sports & Fitness</h3>
                        <p class="group-category">Health & Wellness</p>
                    </div>
                    <div class="group-body">
                        <div class="group-members">
                            <img src="https://randomuser.me/api/portraits/men/41.jpg" alt="Member" class="member-avatar">
                            <img src="https://randomuser.me/api/portraits/women/39.jpg" alt="Member" class="member-avatar">
                            <img src="https://randomuser.me/api/portraits/men/52.jpg" alt="Member" class="member-avatar">
                            <span class="member-count">+6 more</span>
                        </div>
                        <p class="group-description">
                            Stay active with group workouts, sports events, and wellness activities. All fitness levels welcome!
                        </p>
                        <div class="group-stats">
                            <div class="stat">
                                <span class="stat-value">18</span>
                                <span>Members</span>
                            </div>
                            <div class="stat">
                                <span class="stat-value">22</span>
                                <span>Activities</span>
                            </div>
                            <div class="stat">
                                <span class="stat-value">4</span>
                                <span>Sports</span>
                            </div>
                        </div>
                        <div class="group-actions">
                            <a href="#" class="btn btn-outline">
                                <i class="fas fa-users"></i> View Members
                            </a>
                            <a href="#" class="btn">
                                <i class="fas fa-sign-in-alt"></i> Join Group
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Create Interest Group Section -->
            <div id="create-group" style="margin-top: 60px; padding-top: 40px; border-top: 1px solid var(--border-color);">
                <div style="max-width: 700px; margin: 0 auto; text-align: center;">
                    <h2 style="color: var(--primary-color); margin-bottom: 20px;">Create a Special Interest Group</h2>
                    <p style="color: var(--text-light); margin-bottom: 30px; line-height: 1.7;">
                        Start your own community around a shared interest or passion. Bring together alumni with similar hobbies, professional interests, or goals and create meaningful connections.
                    </p>
                    <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
                        <a href="#" class="btn" style="background-color: var(--primary-color);">
                            <i class="fas fa-plus"></i> Create New Group
                        </a>
                        <a href="#" class="btn" style="background-color: var(--secondary-color); color: var(--primary-color);">
                            <i class="fas fa-question-circle"></i> Learn More
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/script.js"></script>
    <script>
        // Add smooth scrolling for anchor links
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
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
    </script>
</body>
</html>
