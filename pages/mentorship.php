<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mentorship Program - Alumni Connect</title>
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

        .mentorship-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, #7a2a2a 100%);
            color: white;
            padding: 60px 40px;
            text-align: center;
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

        .mentors-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .mentors-title {
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

        .mentors-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }

        .mentor-card {
            background: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .mentor-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .mentor-header {
            padding: 20px;
            text-align: center;
            background: linear-gradient(135deg, var(--primary-color) 0%, #7a2a2a 100%);
            color: white;
        }

        .mentor-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 4px solid var(--white);
            margin: 0 auto 15px;
            overflow: hidden;
        }

        .mentor-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .mentor-name {
            font-size: 20px;
            font-weight: 600;
            margin: 10px 0 5px;
        }

        .mentor-title {
            font-size: 14px;
            opacity: 0.9;
            margin: 0 0 10px;
        }

        .mentor-body {
            padding: 20px;
        }

        .mentor-expertise {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 15px;
        }

        .expertise-tag {
            background-color: var(--secondary-color);
            color: var(--primary-color);
            font-size: 12px;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 12px;
        }

        .mentor-bio {
            color: var(--text-light);
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .mentor-actions {
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
            .mentors-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .mentors-actions {
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
        <div class="mentorship-header">
            <h1>Mentorship Program</h1>
            <p>Connect with experienced alumni mentors or share your knowledge by becoming a mentor yourself</p>
        </div>

        <div class="mentors-container">
            <div class="mentors-header">
                <h2 class="mentors-title">Our Mentors</h2>
                <div class="mentors-actions">
                    <a href="#become-mentor" class="btn">
                        <i class="fas fa-user-plus"></i> Become a Mentor
                    </a>
                </div>
            </div>

            <div class="mentors-grid">
                <!-- Mentor 1 -->
                <div class="mentor-card">
                    <div class="mentor-header">
                        <div class="mentor-avatar">
                            <img src="https://randomuser.me/api/portraits/women/44.jpg" alt="Sarah Johnson">
                        </div>
                        <h3 class="mentor-name">Sarah Johnson</h3>
                        <p class="mentor-title">Senior Product Manager at TechCorp</p>
                    </div>
                    <div class="mentor-body">
                        <div class="mentor-expertise">
                            <span class="expertise-tag">Product Management</span>
                            <span class="expertise-tag">Agile</span>
                            <span class="expertise-tag">Startups</span>
                        </div>
                        <p class="mentor-bio">
                            10+ years of experience in product management. Passionate about helping early-career PMs navigate their career paths.
                        </p>
                        <div class="mentor-actions">
                            <a href="#" class="btn btn-outline">
                                <i class="far fa-envelope"></i> Message
                            </a>
                            <a href="#" class="btn">
                                <i class="fas fa-calendar-alt"></i> Book Session
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Mentor 2 -->
                <div class="mentor-card">
                    <div class="mentor-header">
                        <div class="mentor-avatar">
                            <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Michael Chen">
                        </div>
                        <h3 class="mentor-name">Michael Chen</h3>
                        <p class="mentor-title">Lead Software Engineer at DataSystems</p>
                    </div>
                    <div class="mentor-body">
                        <div class="mentor-expertise">
                            <span class="expertise-tag">Machine Learning</span>
                            <span class="expertise-tag">Python</span>
                            <span class="expertise-tag">AI</span>
                        </div>
                        <p class="mentor-bio">
                            AI/ML specialist with 8 years of experience. Love helping students and early-career professionals break into the field.
                        </p>
                        <div class="mentor-actions">
                            <a href="#" class="btn btn-outline">
                                <i class="far fa-envelope"></i> Message
                            </a>
                            <a href="#" class="btn">
                                <i class="fas fa-calendar-alt"></i> Book Session
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Mentor 3 -->
                <div class="mentor-card">
                    <div class="mentor-header">
                        <div class="mentor-avatar">
                            <img src="https://randomuser.me/api/portraits/women/68.jpg" alt="Priya Patel">
                        </div>
                        <h3 class="mentor-name">Priya Patel</h3>
                        <p class="mentor-title">UX Design Lead at CreativeMinds</p>
                    </div>
                    <div class="mentor-body">
                        <div class="mentor-expertise">
                            <span class="expertise-tag">UX/UI Design</span>
                            <span class="expertise-tag">Figma</span>
                            <span class="expertise-tag">Design Thinking</span>
                        </div>
                        <p class="mentor-bio">
                            Passionate about creating intuitive user experiences. 7 years in the industry with experience in both startups and enterprises.
                        </p>
                        <div class="mentor-actions">
                            <a href="#" class="btn btn-outline">
                                <i class="far fa-envelope"></i> Message
                            </a>
                            <a href="#" class="btn">
                                <i class="fas fa-calendar-alt"></i> Book Session
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Become a Mentor Section -->
            <div id="become-mentor" style="margin-top: 60px; padding-top: 40px; border-top: 1px solid var(--border-color);">
                <div style="max-width: 700px; margin: 0 auto; text-align: center;">
                    <h2 style="color: var(--primary-color); margin-bottom: 20px;">Become a Mentor</h2>
                    <p style="color: var(--text-light); margin-bottom: 30px; line-height: 1.7;">
                        Share your knowledge and experience with fellow alumni and students. As a mentor, you'll help shape the next generation of professionals while expanding your own network and leadership skills.
                    </p>
                    <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
                        <a href="#" class="btn" style="background-color: var(--primary-color);">
                            <i class="fas fa-user-plus"></i> Sign Up as Mentor
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
