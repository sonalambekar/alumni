<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mobile App - Alumni Connect</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <!-- Font Awesome for icons -->
    <script src="https://kit.fontawesome.com/b99e675b6e.js" crossorigin="anonymous"></script>
    <!-- Leaflet CSS (Free OpenStreetMap library) -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
          crossorigin=""/>
    <style>
        .app-showcase {
            background: linear-gradient(135deg, var(--primary-color) 0%, #7a2a2a 100%);
            color: white;
            padding: 25px 40px;
            text-align: center;
        }

        .app-showcase h1 {
            font-size: 24px;
            margin-bottom: 8px;
            color: white;
        }

        .app-showcase p {
            font-size: 14px;
            opacity: 0.9;
            margin-bottom: 15px;
        }

        .app-preview {
            background: white;
            border-radius: 20px;
            padding: 20px;
            margin: 10px auto;
            max-width: 1000px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        .phone-mockup {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 25px;
            margin-bottom: 25px;
        }

        .phone-frame {
            background: #333;
            border-radius: 25px;
            padding: 20px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
            position: relative;
        }

        .phone-screen {
            width: 160px;
            height: 320px;
            background: linear-gradient(135deg, #5b1f1f 0%, #7a2a2a 100%);
            border-radius: 15px;
            position: relative;
            overflow: hidden;
        }

        .phone-screen::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 4px;
            background: #666;
            border-radius: 2px;
        }

        .app-icon-large {
            width: 60px;
            height: 60px;
            background: var(--secondary-color);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            font-weight: bold;
            color: var(--primary-color);
            margin: 30px auto 15px;
        }

        .app-features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin: 60px 0;
        }

        .feature-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-5px);
        }

        .feature-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary-color), #7a2a2a);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 24px;
            color: white;
        }

        .feature-card h3 {
            color: var(--primary-color);
            font-size: 20px;
            margin-bottom: 15px;
        }

        .feature-card p {
            color: var(--text-light);
            line-height: 1.6;
        }

        .download-section {
            background: var(--bg-light);
            padding: 60px 40px;
            text-align: center;
        }

        .download-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin: 40px 0;
            flex-wrap: wrap;
        }

        .download-btn {
            display: inline-flex;
            align-items: center;
            gap: 15px;
            padding: 20px 40px;
            background: var(--primary-color);
            color: white;
            text-decoration: none;
            border-radius: 15px;
            font-size: 18px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(91, 31, 31, 0.3);
        }

        .download-btn:hover {
            background: var(--secondary-color);
            color: var(--primary-color);
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(91, 31, 31, 0.4);
        }

        .store-badge {
            width: 30px;
            height: 30px;
        }

        .stats-section-unique {
            background: linear-gradient(135deg, #f8f9fa 0%, white 50%, #f8f9fa 100%);
            padding: 80px 40px;
            position: relative;
            overflow: hidden;
        }

        .stats-section-unique::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 30% 50%, rgba(91, 31, 31, 0.05) 0%, transparent 50%),
                        radial-gradient(circle at 70% 50%, rgba(255, 215, 0, 0.05) 0%, transparent 50%);
        }

        .stats-container {
            max-width: 1200px;
            margin: 0 auto;
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .stats-header h2 {
            font-size: 36px;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 10px;
            position: relative;
        }

        .stats-header h2::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 2px;
        }

        .stats-header p {
            font-size: 16px;
            color: var(--text-light);
            font-style: italic;
        }

        .stats-line {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 40px;
            flex-wrap: wrap;
            width: 100%;
            max-width: 1000px;
        }

        .stat-card {
            display: flex;
            align-items: center;
            gap: 20px;
            background: white;
            padding: 25px 30px;
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border: 2px solid transparent;
            min-width: 220px;
        }

        .stat-card:hover {
            transform: translateY(-5px) scale(1.05);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
            border-color: var(--secondary-color);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            flex-shrink: 0;
            transition: all 0.3s ease;
        }

        .stat-card:hover .stat-icon {
            transform: rotate(10deg) scale(1.1);
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
        }

        .stat-content {
            text-align: left;
        }

        .stat-number {
            font-size: 28px;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 5px;
            font-variant-numeric: tabular-nums;
        }

        .stat-label {
            font-size: 14px;
            color: var(--text-light);
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        @media (max-width: 768px) {
            .stats-section-unique {
                padding: 60px 20px;
            }

            .stats-header h2 {
                font-size: 28px;
            }

            .stats-line {
                flex-direction: column;
                gap: 25px;
            }

            .stat-card {
                width: 100%;
                max-width: 300px;
                justify-content: center;
                text-align: center;
            }

            .stat-content {
                text-align: center;
            }
        }

        .faq-item {
            margin-bottom: 15px;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            overflow: hidden;
            background: white;
        }

        .faq-question {
            display: flex;
            align-items: center;
            padding: 20px;
            cursor: pointer;
            background: white;
            transition: background-color 0.3s ease;
        }

        .faq-question:hover {
            background-color: #f8f9fa;
        }

        .faq-icon {
            width: 30px;
            height: 30px;
            background: var(--primary-color);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-weight: bold;
            font-size: 18px;
            transition: transform 0.3s ease, background-color 0.3s ease;
        }

        .faq-question.active .faq-icon {
            background: var(--secondary-color);
            transform: rotate(45deg);
        }

        .faq-question h3 {
            margin: 0;
            color: var(--primary-color);
            font-size: 18px;
            font-weight: 600;
        }

        .faq-answer {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
            background: #f8f9fa;
        }

        .faq-answer p {
            margin: 0;
            padding: 20px;
            color: var(--text-light);
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <?php include '../sidebar.php'; ?>

    <div class="main-content" id="mainContent">
        <!-- App Showcase Section -->
        <div class="app-showcase">
            <h1>Alumni Connect Mobile App</h1>
            <p>Stay connected with your alumni network wherever you go</p>

            <div class="app-preview">
                <div class="phone-mockup">
                    <div class="phone-frame">
                        <div class="phone-screen">
                            <div class="app-icon-large">AC</div>
                            <h2 style="color: white; margin: 15px 0 8px; font-size: 18px;">Alumni Connect</h2>
                            <p style="color: rgba(255,255,255,0.8); font-size: 12px; padding: 0 15px;">
                                Connect • Network • Grow
                            </p>
                            <div style="margin-top: 40px; padding: 0 15px;">
                                <div style="background: rgba(255,255,255,0.1); border-radius: 8px; padding: 10px; margin-bottom: 10px;">
                                    <p style="color: white; margin: 0; font-size: 14px;">📍 Find Alumni Nearby</p>
                                </div>
                                <div style="background: rgba(255,255,255,0.1); border-radius: 8px; padding: 10px; margin-bottom: 10px;">
                                    <p style="color: white; margin: 0; font-size: 14px;">🎯 Quick Networking</p>
                                </div>
                                <div style="background: rgba(255,255,255,0.1); border-radius: 8px; padding: 10px;">
                                    <p style="color: white; margin: 0; font-size: 14px;">📅 Event Updates</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div style="text-align: left; max-width: 320px;">
                        <h2 style="color: var(--primary-color); margin-bottom: 8px; font-size: 20px;">Experience Alumni Connect on Mobile</h2>
                        <p style="color: var(--text-light); line-height: 1.3; margin-bottom: 12px; font-size: 13px;">
                            Our mobile app brings the full alumni network experience to your smartphone.
                            Connect with fellow alumni, discover opportunities, and stay updated.
                        </p>
                        <div class="download-buttons" style="margin-top: 12px;">
                            <a href="https://play.google.com/store" class="download-btn" target="_blank">
                                <svg class="store-badge" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M17.523 15.3414c-.5511 0-.9993-.4482-.9993-.9993s.4482-.9993.9993-.9993.9993.4482.9993.9993-.4482.9993-.9993.9993zm-11.046 0c-.5511 0-.9993-.4482-.9993-.9993s.4482-.9993.9993-.9993.9993.4482.9993.9993-.4482.9993-.9993.9993z"/>
                                    <path d="M21 8v8c0 1.1046-.8954 2-2 2H5c-1.1046 0-2-.8954-2-2V8c0-1.1046.8954-2 2-2h14c1.1046 0 2 .8954 2 2zM8 2h8v2H8V2z"/>
                                </svg>
                                Download on Play Store
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- App Features Section - FAQ Style -->
        <div style="padding: 40px 40px; background-color: var(--bg-light);">
            <div style="text-align: center; margin-bottom: 40px;">
                <h2 style="color: var(--primary-color); font-size: 32px; margin-bottom: 15px;">App Features</h2>
                <p style="color: var(--text-light); font-size: 16px;">Discover what makes our mobile app powerful and user-friendly</p>
            </div>

            <div style="max-width: 800px; margin: 0 auto;">
                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFAQ(this)">
                        <div class="faq-icon">+</div>
                        <h3>Mobile-First Design</h3>
                    </div>
                    <div class="faq-answer">
                        <p>Optimized interface for smartphones and tablets with intuitive navigation and touch-friendly controls. Every element is designed for mobile interaction.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFAQ(this)">
                        <div class="faq-icon">+</div>
                        <h3>Location Services</h3>
                    </div>
                    <div class="faq-answer">
                        <p>Find alumni in your area with real-time location tracking and nearby member discovery features. Never miss networking opportunities around you.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFAQ(this)">
                        <div class="faq-icon">+</div>
                        <h3>Instant Notifications</h3>
                    </div>
                    <div class="faq-answer">
                        <p>Get notified about new messages, event updates, job opportunities, and alumni activities instantly. Stay connected with what's happening in your network.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFAQ(this)">
                        <div class="faq-icon">+</div>
                        <h3>Direct Messaging</h3>
                    </div>
                    <div class="faq-answer">
                        <p>Connect with alumni through private messaging, group chats, and networking conversations. Build meaningful professional relationships.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFAQ(this)">
                        <div class="faq-icon">+</div>
                        <h3>Event Management</h3>
                    </div>
                    <div class="faq-answer">
                        <p>Browse upcoming events, RSVP to gatherings, and get directions to alumni meetups and reunions. Never miss important alumni events.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFAQ(this)">
                        <div class="faq-icon">+</div>
                        <h3>Smart Matching</h3>
                    </div>
                    <div class="faq-answer">
                        <p>AI-powered recommendations to connect you with alumni based on your interests, location, and career goals. Discover relevant connections effortlessly.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- App Stats Section - Unique One Line Design -->
        <div class="stats-section-unique">
            <div class="stats-container">
                <div class="stats-header">
                    <h2>App Statistics</h2>
                    <p>Trusted by alumni worldwide</p>
                </div>
                <div class="stats-line">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 21V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14"></path>
                                <path d="M3 21h18"></path>
                                <path d="M7 12v9"></path>
                                <path d="M11 12v9"></path>
                                <path d="M15 12v9"></path>
                                <path d="M19 12v9"></path>
                            </svg>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number" data-target="50000">0</div>
                            <div class="stat-label">Downloads</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path>
                            </svg>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number" data-target="4.8">0</div>
                            <div class="stat-label">Rating</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                <circle cx="12" cy="10" r="3"></circle>
                            </svg>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number" data-target="25">0</div>
                            <div class="stat-label">Countries</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number" data-target="15000">0</div>
                            <div class="stat-label">Active Users</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Download Section -->
        <div class="download-section">
            <h2>Ready to Get Started?</h2>
            <p style="font-size: 18px; margin-bottom: 30px;">Join thousands of alumni already using our mobile app</p>

            <div class="download-buttons">
                <a href="https://play.google.com/store" class="download-btn" target="_blank">
                    <svg class="store-badge" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M17.523 15.3414c-.5511 0-.9993-.4482-.9993-.9993s.4482-.9993.9993-.9993.9993.4482.9993.9993-.4482.9993-.9993.9993zm-11.046 0c-.5511 0-.9993-.4482-.9993-.9993s.4482-.9993.9993-.9993.9993.4482.9993.9993-.4482.9993-.9993.9993z"/>
                        <path d="M21 8v8c0 1.1046-.8954 2-2 2H5c-1.1046 0-2-.8954-2-2V8c0-1.1046.8954-2 2-2h14c1.1046 0 2 .8954 2 2zM8 2h8v2H8V2z"/>
                    </svg>
                    Download on Google Play Store
                </a>
            </div>

            <p style="color: var(--text-light); margin-top: 30px;">
                Available for Android devices • Requires Android 8.0 or higher • Free to download
            </p>
        </div>
    </div>

    <!-- Leaflet JS (Free OpenStreetMap library) -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
            crossorigin=""></script>
    <script src="../assets/js/script.js?v=2"></script>

    <script>
        function toggleFAQ(element) {
            const faqItem = element.parentElement;
            const faqAnswer = faqItem.querySelector('.faq-answer');
            const isActive = element.classList.contains('active');

            // Close all other FAQ items
            document.querySelectorAll('.faq-question').forEach(q => {
                q.classList.remove('active');
                q.parentElement.querySelector('.faq-answer').style.maxHeight = '0';
            });

            // Toggle current item
            if (!isActive) {
                element.classList.add('active');
                faqAnswer.style.maxHeight = faqAnswer.scrollHeight + 'px';
            }
        }

        // Optional: Open first FAQ item by default
        document.addEventListener('DOMContentLoaded', function() {
            const firstFAQ = document.querySelector('.faq-item:first-child .faq-question');
            if (firstFAQ) {
                toggleFAQ(firstFAQ);
            }
        });
    </script>
</body>
</html>
