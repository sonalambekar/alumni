<?php
require_once '../includes/db_config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Photo Gallery - Alumni Connect</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #5b1f1f;
            --secondary-color: #ecc35c;
            --bg-light: #f5f7fb;
            --text-color: #333;
            --light-gray: #f0f0f0;
            --border-color: #e0e0e0;
            --transition-speed: 0.3s;
        }

        .gallery-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        .page-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .page-header h1 {
            color: var(--primary-color);
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .page-header p {
            color: #666;
            font-size: 1.1rem;
            max-width: 700px;
            margin: 0 auto;
        }

        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            margin-top: 30px;
        }

        .gallery-item {
            position: relative;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            transition: all var(--transition-speed) ease;
            aspect-ratio: 4/3;
            background: #fff;
        }

        .gallery-item:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            z-index: 2;
        }

        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .gallery-item:hover img {
            transform: scale(1.05);
        }

        .gallery-caption {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0, 0, 0, 0.8));
            color: white;
            padding: 25px 20px;
            transform: translateY(100%);
            transition: transform var(--transition-speed) ease;
            backdrop-filter: blur(5px);
        }

        .gallery-item:hover .gallery-caption {
            transform: translateY(0);
        }

        .gallery-caption h3 {
            margin: 0 0 8px 0;
            font-size: 1.2rem;
            font-weight: 600;
            text-shadow: 0 1px 3px rgba(0,0,0,0.3);
        }

        .gallery-caption p {
            margin: 0;
            font-size: 0.95rem;
            opacity: 0.95;
            line-height: 1.4;
            text-shadow: 0 1px 2px rgba(0,0,0,0.3);
        }

        .gallery-category {
            margin-bottom: 60px;
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .gallery-category h2 {
            color: var(--primary-color);
            margin-bottom: 25px;
            font-size: 2rem;
            position: relative;
            padding-bottom: 15px;
            display: inline-block;
        }

        .gallery-category h2:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 3px;
            background: var(--secondary-color);
            border-radius: 3px;
        }

        @media (max-width: 768px) {
            .gallery-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                gap: 15px;
            }

            .page-header h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <?php include '../sidebar.php'; ?>

    <div class="main-content">
        <div class="gallery-container">
            <div class="page-header">
                <h1>Alumni Memories Gallery</h1>
                <p>Relive the cherished moments and create new memories with our alumni community</p>
            </div>

            <div class="gallery-category">
                <h2><i class="fas fa-graduation-cap"></i> Graduation Ceremonies</h2>
                <div class="gallery-grid">
                    <div class="gallery-item">
                        <img src="https://images.unsplash.com/photo-1541339907198-e08756dedf3f?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1170&q=80" alt="Graduation Day">
                        <div class="gallery-caption">
                            <h3>Class of 2023</h3>
                            <p>Graduation ceremony at the main auditorium</p>
                        </div>
                    </div>

                    <div class="gallery-item">
                        <img src="https://images.unsplash.com/photo-1541178735493-479c1a27ed24?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1171&q=80" alt="Caps in the Air">
                        <div class="gallery-caption">
                            <h3>Moment of Achievement</h3>
                            <p>Celebrating academic success</p>
                        </div>
                    </div>

                    <div class="gallery-item">
                        <img src="https://images.unsplash.com/photo-1523240795612-9a054b0db644?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1170&q=80" alt="Graduation Portrait">
                        <div class="gallery-caption">
                            <h3>Alumni Portraits</h3>
                            <p>Class of 2023 graduates</p>
                        </div>
                    </div>

                    <div class="gallery-item">
                        <img src="https://images.unsplash.com/photo-1523050853548-9860ac796eb3?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1170&q=80" alt="Diploma Ceremony">
                        <div class="gallery-caption">
                            <h3>Diploma Ceremony</h3>
                            <p>Receiving the hard-earned degrees</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="gallery-category">
                <h2><i class="fas fa-users"></i> Campus Life</h2>
                <div class="gallery-grid">
                    <div class="gallery-item">
                        <img src="https://images.unsplash.com/photo-1523240795612-9a054b0db644?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1170&q=80" alt="Campus Building">
                        <div class="gallery-caption">
                            <h3>Historic Campus</h3>
                            <p>Our beautiful campus in spring</p>
                        </div>
                    </div>

                    <div class="gallery-item">
                        <img src="https://images.unsplash.com/photo-1588072432836-e10032774350?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1170&q=80" alt="Library">
                        <div class="gallery-caption">
                            <h3>University Library</h3>
                            <p>Where knowledge meets inspiration</p>
                        </div>
                    </div>

                    <div class="gallery-item">
                        <img src="https://images.unsplash.com/photo-1523240795612-9a054b0db644?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1170&q=80" alt="Cafeteria">
                        <div class="gallery-caption">
                            <h3>Student Center</h3>
                            <p>Social hub of the university</p>
                        </div>
                    </div>

                    <div class="gallery-item">
                        <img src="https://images.unsplash.com/photo-1541339907198-e08756dedf3f?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1170&q=80" alt="Research Lab">
                        <div class="gallery-caption">
                            <h3>Research Facilities</h3>
                            <p>State-of-the-art laboratories</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="gallery-category">
                <h2><i class="fas fa-trophy"></i> Sports & Events</h2>
                <div class="gallery-grid">
                    <div class="gallery-item">
                        <img src="https://images.unsplash.com/photo-1540747913346-19e32dc3e97e?ixlib=rb-4.0.3&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1105&q=80" alt="Basketball Game">
                        <div class="gallery-caption">
                            <h3>Basketball Finals</h3>
                            <p>Intense match against rival college</p>
                        </div>
                    </div>

                    <div class="gallery-item">
                        <img src="https://images.unsplash.com/photo-1543351611-58f69d7c1781?ixlib=rb-4.0.3&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1074&q=80" alt="Soccer Match">
                        <div class="gallery-caption">
                            <h3>Soccer Championship</h3>
                            <p>Victory celebration with the team</p>
                        </div>
                    </div>

                    <div class="gallery-item">
                        <img src="https://images.unsplash.com/photo-1543351611-58f69d7c1781?ixlib=rb-4.0.3&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1074&q=80" alt="Track & Field">
                        <div class="gallery-caption">
                            <h3>Annual Sports Meet</h3>
                            <p>Celebrating athletic excellence</p>
                        </div>
                    </div>

                    <div class="gallery-item">
                        <img src="https://images.unsplash.com/photo-1543351611-58f69d7c1781?ixlib=rb-4.0.3&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1074&q=80" alt="Cultural Fest">
                        <div class="gallery-caption">
                            <h3>Cultural Festival</h3>
                            <p>Showcasing talent and diversity</p>
                        </div>
                    </div>
                    <div class="gallery-item">
                        <img src="https://images.unsplash.com/photo-1540747913346-19e32dc3e97e?ixlib=rb-4.0.3&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1005&q=80" alt="Basketball Game">
                        <img src="https://images.unsplash.com/photo-1540747913346-19e32dc3e97e?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1005&q=80" alt="Basketball Game">
                        <div class="gallery-caption">
                            <h3>Basketball Finals</h3>
                            <p>2023 Inter-University Championship</p>
                        </div>
                    </div>

                    <div class="gallery-item">
                        <img src="https://images.unsplash.com/photo-1574629810360-7efbbe195d86?ixlib=rb-4.0.3&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1200&q=80" alt="Soccer Match">
                        <div class="gallery-caption">
                            <h3>Soccer Tournament</h3>
                            <p>Annual inter-college competition</p>
                        </div>
                    </div>

                    <div class="gallery-item">
                        <img src="https://images.unsplash.com/photo-1517649763962-0c2a416d70e3?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1170&q=80" alt="Track & Field">
                        <div class="gallery-caption">
                            <h3>Track & Field</h3>
                            <p>Annual sports meet 2023</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Add any interactive functionality here if needed
        document.addEventListener('DOMContentLoaded', function() {
            // Smooth scroll for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth'
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>
