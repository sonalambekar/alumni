<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>News Corner - Alumni Connect</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .news-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
            position: relative;
            min-height: 100vh;
        }

        .news-title {
            text-align: center;
            color: var(--primary-color);
            font-size: 2.2rem;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .news-subtitle {
            text-align: center;
            color: var(--text-light);
            font-size: 1.1rem;
            margin-bottom: 40px;
        }

        /* Bulletin Board Background */
        .bulletin-board {
            background: linear-gradient(135deg, #f4e4bc 0%, #e8d5a3 50%, #dcc88a 100%);
            min-height: calc(100vh - 200px);
            padding: 40px;
            position: relative;
            border-radius: 15px;
            box-shadow: inset 0 0 50px rgba(139, 115, 85, 0.3);
        }

        .bulletin-board::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image:
                radial-gradient(circle at 20% 30%, rgba(139, 115, 85, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 70%, rgba(139, 115, 85, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 40% 80%, rgba(139, 115, 85, 0.1) 0%, transparent 50%);
            pointer-events: none;
        }

        /* Bulletin Cards - Pinned Paper Style */
        .news-bulletin {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 25px;
            position: relative;
            z-index: 2;
        }

        .bulletin-card {
            background: #fefefe;
            border-radius: 8px;
            padding: 20px;
            box-shadow:
                0 4px 8px rgba(0, 0, 0, 0.15),
                inset 0 1px 0 rgba(255, 255, 255, 0.8);
            position: relative;
            transition: all 0.3s ease;
            transform-origin: center;
            animation: bulletinFloat 6s ease-in-out infinite;
            cursor: pointer;
        }

        .bulletin-card:nth-child(3n) {
            animation-delay: -2s;
            transform: rotate(-1deg);
        }

        .bulletin-card:nth-child(5n) {
            animation-delay: -4s;
            transform: rotate(0.5deg);
        }

        .bulletin-card:nth-child(7n) {
            animation-delay: -1s;
            transform: rotate(-0.5deg);
        }

        .bulletin-card:nth-child(8n) {
            animation-delay: -3s;
            transform: rotate(1deg);
        }

        .bulletin-card:hover {
            transform: rotate(0deg) translateY(-5px) scale(1.02);
            box-shadow:
                0 8px 25px rgba(0, 0, 0, 0.2),
                inset 0 1px 0 rgba(255, 255, 255, 0.9);
            animation-play-state: paused;
        }

        /* Pin Effect */
        .pin {
            position: absolute;
            top: -8px;
            left: 50%;
            transform: translateX(-50%);
            width: 16px;
            height: 16px;
            background: radial-gradient(circle, #c41e3a 0%, #8b1538 100%);
            border-radius: 50%;
            box-shadow:
                0 2px 4px rgba(0, 0, 0, 0.3),
                inset 0 1px 0 rgba(255, 255, 255, 0.3);
        }

        .pin::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 8px;
            height: 8px;
            background: radial-gradient(circle, #ff6b6b 0%, #d63031 100%);
            border-radius: 50%;
        }

        /* Card Content */
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px dashed #ddd;
        }

        .news-category {
            background: linear-gradient(135deg, var(--secondary-color) 0%, #f39c12 100%);
            color: var(--primary-color);
            padding: 4px 10px;
            border-radius: 15px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .news-date {
            color: #888;
            font-size: 12px;
            font-style: italic;
        }

        .news-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 10px;
            line-height: 1.3;
        }

        .news-excerpt {
            color: #666;
            font-size: 14px;
            line-height: 1.5;
            margin-bottom: 15px;
        }

        .read-more {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            color: var(--primary-color);
            font-weight: 600;
            font-size: 13px;
            text-decoration: none;
            transition: all 0.3s ease;
            padding: 5px 10px;
            border-radius: 20px;
            background: rgba(91, 31, 31, 0.1);
        }

        .read-more:hover {
            background: var(--primary-color);
            color: white;
            transform: translateX(2px);
        }

        /* Animations */
        @keyframes bulletinFloat {
            0%, 100% { transform: translateY(0px) rotate(var(--rotation, 0deg)); }
            25% { transform: translateY(-3px) rotate(calc(var(--rotation, 0deg) + 0.5deg)); }
            50% { transform: translateY(-1px) rotate(var(--rotation, 0deg)); }
            75% { transform: translateY(-4px) rotate(calc(var(--rotation, 0deg) - 0.5deg)); }
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .news-bulletin {
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
                gap: 20px;
            }
        }

        @media (max-width: 768px) {
            .bulletin-board {
                padding: 20px;
            }

            .news-bulletin {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .bulletin-card {
                padding: 15px;
            }

            .news-title {
                font-size: 16px;
            }

            .news-excerpt {
                font-size: 13px;
            }
        }

        @media (max-width: 480px) {
            .news-container {
                padding: 15px;
            }

            .news-title {
                font-size: 1.8rem;
            }

            .bulletin-card {
                padding: 12px;
            }
        }
    </style>
</head>
<body>
    <?php include '../sidebar.php'; ?>

    <div class="main-content" id="mainContent">
        <div class="news-container">
            <h2 class="news-title">News Corner</h2>
            <p class="news-subtitle">Stay informed with the latest news and updates from our alumni community</p>

            <div class="bulletin-board">
                <div class="news-bulletin">
                    <div class="bulletin-card">
                        <div class="pin"></div>
                        <div class="card-header">
                            <span class="news-category">Achievement</span>
                            <span class="news-date">Dec 10, 2025</span>
                        </div>
                        <h3 class="news-title">Alumni Wins Prestigious Innovation Award</h3>
                        <p class="news-excerpt">
                            Sarah Mitchell (Class of 2019) has been awarded the Global Innovation Award for her
                            groundbreaking work in sustainable technology solutions.
                        </p>
                        <a href="#" class="read-more">Read More →</a>
                    </div>

                    <div class="bulletin-card">
                        <div class="pin"></div>
                        <div class="card-header">
                            <span class="news-category">Business</span>
                            <span class="news-date">Dec 8, 2025</span>
                        </div>
                        <h3 class="news-title">Alumni Startup Raises $10M in Series A Funding</h3>
                        <p class="news-excerpt">
                            TechVentures, founded by alumni John Anderson, successfully closes Series A funding round
                            led by prominent venture capital firms.
                        </p>
                        <a href="#" class="read-more">Read More →</a>
                    </div>

                    <div class="bulletin-card">
                        <div class="pin"></div>
                        <div class="card-header">
                            <span class="news-category">Social Impact</span>
                            <span class="news-date">Dec 5, 2025</span>
                        </div>
                        <h3 class="news-title">Alumni-Led Initiative Impacts 10,000 Lives</h3>
                        <p class="news-excerpt">
                            The education initiative led by Lisa Patel (Class of 2021) has successfully provided
                            quality education to over 10,000 underprivileged children.
                        </p>
                        <a href="#" class="read-more">Read More →</a>
                    </div>

                    <div class="bulletin-card">
                        <div class="pin"></div>
                        <div class="card-header">
                            <span class="news-category">University</span>
                            <span class="news-date">Dec 3, 2025</span>
                        </div>
                        <h3 class="news-title">New Library Wing Construction Begins</h3>
                        <p class="news-excerpt">
                            Thanks to generous alumni contributions, construction of the new state-of-the-art library
                            wing has officially commenced on campus.
                        </p>
                        <a href="#" class="read-more">Read More →</a>
                    </div>

                    <div class="bulletin-card">
                        <div class="pin"></div>
                        <div class="card-header">
                            <span class="news-category">Research</span>
                            <span class="news-date">Nov 30, 2025</span>
                        </div>
                        <h3 class="news-title">Alumni Researcher Published in Nature Journal</h3>
                        <p class="news-excerpt">
                            Dr. Michael Chen's groundbreaking research on AI applications in healthcare has been
                            published in the prestigious Nature journal.
                        </p>
                        <a href="#" class="read-more">Read More →</a>
                    </div>

                    <div class="bulletin-card">
                        <div class="pin"></div>
                        <div class="card-header">
                            <span class="news-category">Event</span>
                            <span class="news-date">Nov 28, 2025</span>
                        </div>
                        <h3 class="news-title">Record Attendance at Tech Leaders Summit</h3>
                        <p class="news-excerpt">
                            Over 500 alumni attended the Tech Leaders Summit, making it the largest alumni networking
                            event in the technology sector to date.
                        </p>
                        <a href="#" class="read-more">Read More →</a>
                    </div>

                    <div class="bulletin-card">
                        <div class="pin"></div>
                        <div class="card-header">
                            <span class="news-category">Achievement</span>
                            <span class="news-date">Nov 25, 2025</span>
                        </div>
                        <h3 class="news-title">Alumni Appointed as Fortune 500 CEO</h3>
                        <p class="news-excerpt">
                            Emily Rodriguez (Class of 2020) has been appointed as the youngest CEO of a Fortune 500
                            company, breaking multiple glass ceilings.
                        </p>
                        <a href="#" class="read-more">Read More →</a>
                    </div>

                    <div class="bulletin-card">
                        <div class="pin"></div>
                        <div class="card-header">
                            <span class="news-category">Program</span>
                            <span class="news-date">Nov 22, 2025</span>
                        </div>
                        <h3 class="news-title">Mentorship Program Sees 95% Success Rate</h3>
                        <p class="news-excerpt">
                            The alumni mentorship program reports exceptional success with 95% of mentees achieving
                            their career goals within the first year.
                        </p>
                        <a href="#" class="read-more">Read More →</a>
                    </div>

                    <div class="bulletin-card">
                        <div class="pin"></div>
                        <div class="card-header">
                            <span class="news-category">Sports</span>
                            <span class="news-date">Nov 20, 2025</span>
                        </div>
                        <h3 class="news-title">Alumni Represents Country at Olympics</h3>
                        <p class="news-excerpt">
                            David Thompson (Class of 2016) will represent the country in athletics at the upcoming
                            Summer Olympics, making the entire alumni community proud.
                        </p>
                        <a href="#" class="read-more">Read More →</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/script.js"></script>
</body>
</html>
