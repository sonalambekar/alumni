<?php
// Notice Board - Ready for future announcements and notices
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notice Board - Alumni Connect</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #5b1f1f;
            --secondary-color: #ecc35c;
            --bg-light: #f8f9fa;
            --text-color: #333;
            --light-gray: #f0f0f0;
            --border-color: #e0e0e0;
        }

        .noticeboard-header {
            text-align: center;
            padding: 60px 40px 40px;
            background: linear-gradient(135deg, var(--primary-color) 0%, #7a2a2a 100%);
            color: white;
            margin-bottom: 40px;
            position: relative;
            overflow: hidden;
        }

        .noticeboard-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="20" cy="20" r="2" fill="rgba(255,255,255,0.1)"/><circle cx="80" cy="40" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="40" cy="80" r="1.5" fill="rgba(255,255,255,0.1)"/></svg>');
            opacity: 0.3;
        }

        .noticeboard-title {
            font-size: 48px;
            font-weight: 700;
            margin-bottom: 15px;
            position: relative;
            z-index: 2;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .noticeboard-subtitle {
            font-size: 18px;
            opacity: 0.9;
            position: relative;
            z-index: 2;
            font-weight: 300;
        }

        .noticeboard-content {
            max-width: 1000px;
            margin: 0 auto;
            padding: 0 40px;
        }

        .noticeboard-placeholder {
            text-align: center;
            padding: 80px 40px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            margin-bottom: 60px;
        }

        .placeholder-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            color: white;
        }

        .placeholder-icon svg {
            width: 40px;
            height: 40px;
        }

        .noticeboard-placeholder h3 {
            font-size: 28px;
            color: var(--primary-color);
            margin-bottom: 20px;
            font-weight: 600;
        }

        .noticeboard-placeholder p {
            font-size: 16px;
            color: var(--text-color);
            line-height: 1.6;
            max-width: 600px;
            margin: 0 auto;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .noticeboard-header {
                padding: 40px 20px 30px;
            }

            .noticeboard-title {
                font-size: 36px;
            }

            .noticeboard-subtitle {
                font-size: 16px;
            }

            .noticeboard-content {
                padding: 0 20px;
            }

            .noticeboard-placeholder {
                padding: 60px 30px;
            }

            .noticeboard-placeholder h3 {
                font-size: 24px;
            }

            .placeholder-icon {
                width: 60px;
                height: 60px;
            }

            .placeholder-icon svg {
                width: 30px;
                height: 30px;
            }
        }
    </style>
</head>
<body>
    <?php include '../sidebar.php'; ?>

    <div class="main-content" id="mainContent">
        <div class="noticeboard-container">
            <div class="noticeboard-header">
                <h1 class="noticeboard-title">Notice Board</h1>
                <p class="noticeboard-subtitle">Stay updated with latest announcements and important information</p>
            </div>

            <div class="noticeboard-content">
                <div class="noticeboard-placeholder">
                    <div class="placeholder-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14,2 14,8 20,8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                            <polyline points="10,9 9,9 8,9"></polyline>
                        </svg>
                    </div>
                    <h3>Notice Board Coming Soon</h3>
                    <p>We're working on bringing you the latest announcements, updates, and important information from the alumni community.</p>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/script.js"></script>
</body>
</html>
