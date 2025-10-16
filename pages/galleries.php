<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galleries - Alumni Connect</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .gallery-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        .gallery-title {
            text-align: center;
            color: var(--primary-color);
            font-size: 2.2rem;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .gallery-subtitle {
            text-align: center;
            color: var(--text-light);
            font-size: 1.1rem;
            margin-bottom: 40px;
        }

        /* Masonry Gallery Layout */
        .masonry-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            grid-auto-rows: 200px;
            gap: 15px;
            margin-bottom: 40px;
        }

        .gallery-item {
            position: relative;
            overflow: hidden;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.4s ease;
            cursor: pointer;
        }

        .gallery-item:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.2);
        }

        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }

        .gallery-item:hover img {
            transform: scale(1.1);
        }

        .gallery-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(
                45deg,
                rgba(91, 31, 31, 0.8) 0%,
                rgba(236, 195, 92, 0.6) 50%,
                rgba(91, 31, 31, 0.8) 100%
            );
            opacity: 0;
            transition: opacity 0.3s ease;
            display: flex;
            align-items: flex-end;
            padding: 20px;
        }

        .gallery-item:hover .gallery-overlay {
            opacity: 1;
        }

        .gallery-info {
            color: white;
            font-size: 0.9rem;
        }

        .gallery-info h3 {
            font-size: 1.1rem;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .gallery-info p {
            font-size: 0.8rem;
            opacity: 0.9;
        }

        /* Different sizes for masonry effect */
        .gallery-item:nth-child(3n) {
            grid-row: span 2;
        }

        .gallery-item:nth-child(5n) {
            grid-row: span 3;
        }

        .gallery-item:nth-child(7n) {
            grid-row: span 2;
        }

        .gallery-item:nth-child(8n) {
            grid-column: span 2;
        }

        /* Responsive adjustments */
        @media (max-width: 1200px) {
            .masonry-gallery {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                gap: 12px;
            }
        }

        @media (max-width: 768px) {
            .masonry-gallery {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                gap: 10px;
                grid-auto-rows: 150px;
            }

            .gallery-title {
                font-size: 1.8rem;
            }

            .gallery-subtitle {
                font-size: 1rem;
            }
        }

        @media (max-width: 480px) {
            .masonry-gallery {
                grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
                gap: 8px;
                grid-auto-rows: 120px;
            }
        }
    </style>
</head>
<body>
    <?php include '../sidebar.php'; ?>

    <div class="main-content" id="mainContent">
        <div class="gallery-container">
            <h2 class="gallery-title">Photo Galleries</h2>
            <p class="gallery-subtitle">Relive memorable moments from alumni events and gatherings</p>

            <div class="masonry-gallery">
                <div class="gallery-item">
                    <img src="https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=800&h=600&fit=crop" alt="Annual Reunion 2024">
                    <div class="gallery-overlay">
                        <div class="gallery-info">
                            <h3>Annual Reunion 2024</h3>
                            <p>December 15, 2024 • 120 Photos</p>
                        </div>
                    </div>
                </div>

                <div class="gallery-item">
                    <img src="https://images.unsplash.com/photo-1523580494863-6f3031224c94?w=800&h=600&fit=crop" alt="Career Fair 2024">
                    <div class="gallery-overlay">
                        <div class="gallery-info">
                            <h3>Career Fair 2024</h3>
                            <p>November 10, 2024 • 85 Photos</p>
                        </div>
                    </div>
                </div>

                <div class="gallery-item">
                    <img src="https://images.unsplash.com/photo-1511795409834-ef04bbd61622?w=800&h=600&fit=crop" alt="Sports Day 2024">
                    <div class="gallery-overlay">
                        <div class="gallery-info">
                            <h3>Alumni Sports Day 2024</h3>
                            <p>October 20, 2024 • 150 Photos</p>
                        </div>
                    </div>
                </div>

                <div class="gallery-item">
                    <img src="https://images.unsplash.com/photo-1475721027785-f74eccf877e2?w=800&h=600&fit=crop" alt="Gala Dinner 2024">
                    <div class="gallery-overlay">
                        <div class="gallery-info">
                            <h3>Annual Gala Dinner 2024</h3>
                            <p>September 15, 2024 • 95 Photos</p>
                        </div>
                    </div>
                </div>

                <div class="gallery-item">
                    <img src="https://images.unsplash.com/photo-1591115765373-5207764f72e7?w=800&h=600&fit=crop" alt="Tech Summit 2024">
                    <div class="gallery-overlay">
                        <div class="gallery-info">
                            <h3>Tech Leaders Summit 2024</h3>
                            <p>August 25, 2024 • 70 Photos</p>
                        </div>
                    </div>
                </div>

                <div class="gallery-item">
                    <img src="https://images.unsplash.com/photo-1523050854058-8df90110c9f1?w=800&h=600&fit=crop" alt="Graduation 2024">
                    <div class="gallery-overlay">
                        <div class="gallery-info">
                            <h3>Graduation Ceremony 2024</h3>
                            <p>June 30, 2024 • 200 Photos</p>
                        </div>
                    </div>
                </div>

                <div class="gallery-item">
                    <img src="https://images.unsplash.com/photo-1511578314322-379afb476865?w=800&h=600&fit=crop" alt="Mentorship Launch">
                    <div class="gallery-overlay">
                        <div class="gallery-info">
                            <h3>Mentorship Program Launch</h3>
                            <p>May 12, 2024 • 45 Photos</p>
                        </div>
                    </div>
                </div>

                <div class="gallery-item">
                    <img src="https://images.unsplash.com/photo-1469571486292-0ba58a3f068b?w=800&h=600&fit=crop" alt="Social Impact">
                    <div class="gallery-overlay">
                        <div class="gallery-info">
                            <h3>Social Impact Initiative 2024</h3>
                            <p>April 22, 2024 • 60 Photos</p>
                        </div>
                    </div>
                </div>

                <div class="gallery-item">
                    <img src="https://images.unsplash.com/photo-1559136555-9303baea8ebd?w=800&h=600&fit=crop" alt="Entrepreneurs Meet">
                    <div class="gallery-overlay">
                        <div class="gallery-info">
                            <h3>Entrepreneurs Meetup 2024</h3>
                            <p>March 18, 2024 • 55 Photos</p>
                        </div>
                    </div>
                </div>

                <div class="gallery-item">
                    <img src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=800&h=600&fit=crop" alt="Women Leaders">
                    <div class="gallery-overlay">
                        <div class="gallery-info">
                            <h3>Women Leaders Summit 2024</h3>
                            <p>February 28, 2024 • 75 Photos</p>
                        </div>
                    </div>
                </div>

                <div class="gallery-item">
                    <img src="https://images.unsplash.com/photo-1451187580459-43490279c0fa?w=800&h=600&fit=crop" alt="Innovation Hub">
                    <div class="gallery-overlay">
                        <div class="gallery-info">
                            <h3>Innovation Hub Launch</h3>
                            <p>January 20, 2024 • 90 Photos</p>
                        </div>
                    </div>
                </div>

                <div class="gallery-item">
                    <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=800&h=600&fit=crop" alt="Alumni Meet">
                    <div class="gallery-overlay">
                        <div class="gallery-info">
                            <h3>Regional Alumni Meet</h3>
                            <p>December 5, 2023 • 65 Photos</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/script.js"></script>
</body>
</html>
