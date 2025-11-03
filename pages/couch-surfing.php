<?php
session_start();
require_once '../includes/db_config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php?redirect=couch-surfing");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_request'])) {
    $user_id = $_SESSION['user_id'];
    $destination = trim($_POST['destination']);
    $check_in = $_POST['check_in'];
    $check_out = $_POST['check_out'];
    $guests = (int)$_POST['guests'];
    $message = trim($_POST['message']);
    
    if (!empty($destination) && !empty($check_in) && !empty($check_out) && $guests > 0) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO couch_surfing_requests 
                (user_id, destination, check_in, check_out, guests, message, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())
            ")->execute([$user_id, $destination, $check_in, $check_out, $guests, $message]);
            
            $success = "Your couch surfing request has been submitted successfully!";
        } catch (PDOException $e) {
            $error = "Error submitting request: " . $e->getMessage();
        }
    } else {
        $error = "Please fill in all required fields.";
    }
}

// Get user's couch surfing requests
try {
    $stmt = $pdo->prepare("
        SELECT * FROM couch_surfing_requests 
        WHERE user_id = ? 
        ORDER BY created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error loading your requests: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Couch Surfing - Alumni Connect</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e8ecf1 100%);
            min-height: 100vh;
        }

        .main-content {
            padding: 40px 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .page-header {
            text-align: center;
            margin-bottom: 50px;
            animation: fadeInDown 0.6s ease;
        }

        .page-header h1 {
            color: #5b1f1f;
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }

        .page-header p {
            color: #666;
            font-size: 16px;
            max-width: 700px;
            margin: 0 auto;
            line-height: 1.6;
        }

        .couch-container {
            display: grid;
            grid-template-columns: 1fr 1.5fr;
            gap: 30px;
            margin-bottom: 50px;
        }

        .couch-form {
            background: white;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(91, 31, 31, 0.08);
            height: fit-content;
            position: sticky;
            top: 30px;
            border: 1px solid rgba(91, 31, 31, 0.05);
        }

        .couch-form h2 {
            color: #5b1f1f;
            margin-bottom: 25px;
            font-size: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }

        .form-group input[type="text"],
        .form-group input[type="date"],
        .form-group input[type="number"],
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-family: inherit;
            font-size: 15px;
            transition: all 0.3s ease;
            background: #fafafa;
        }

        .form-group textarea {
            min-height: 120px;
            resize: vertical;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #5b1f1f;
            background: white;
            box-shadow: 0 0 0 4px rgba(91, 31, 31, 0.1);
        }

        .btn-submit {
            background: linear-gradient(135deg, #5b1f1f 0%, #7a2828 100%);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 30px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            width: 100%;
            font-size: 16px;
            margin-top: 10px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(91, 31, 31, 0.3);
        }

        .requests-container {
            display: flex;
            flex-direction: column;
            gap: 25px;
        }

        .requests-container h2 {
            color: #5b1f1f;
            font-size: 24px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .request-card {
            background: white;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
            transition: all 0.3s ease;
            border: 1px solid rgba(0, 0, 0, 0.04);
            position: relative;
            overflow: hidden;
        }

        .request-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(180deg, #5b1f1f 0%, #7a2828 100%);
        }

        .request-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #f0f0f0;
        }

        .request-destination {
            font-size: 20px;
            font-weight: 600;
            color: #333;
        }

        .request-dates {
            display: flex;
            gap: 15px;
            color: #666;
            font-size: 14px;
        }

        .request-meta {
            display: flex;
            gap: 20px;
            margin-top: 15px;
            color: #666;
            font-size: 14px;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .request-message {
            margin-top: 15px;
            color: #555;
            line-height: 1.6;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 8px;
        }

        .request-status {
            position: absolute;
            top: 20px;
            right: 20px;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-approved {
            background: #d4edda;
            color: #155724;
        }

        .status-rejected {
            background: #f8d7da;
            color: #721c24;
        }

        .no-requests {
            text-align: center;
            padding: 50px 20px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
        }

        .no-requests i {
            font-size: 64px;
            color: #ddd;
            margin-bottom: 20px;
        }

        .no-requests p {
            color: #888;
            font-size: 16px;
            margin: 0;
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 900px) {
            .couch-container {
                grid-template-columns: 1fr;
            }

            .couch-form {
                position: static;
                margin-bottom: 30px;
            }

            .page-header h1 {
                font-size: 28px;
            }
        }

        /* Animation for new requests */
        @keyframes fadeInRight {
            from {
                opacity: 0;
                transform: translateX(30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .fade-in {
            animation: fadeInRight 0.5s ease-out;
        }
    </style>
</head>
<body>
    <?php include '../sidebar.php'; ?>

    <div class="main-content" id="mainContent">
        <div class="page-header">
            <h1><i class="fas fa-couch"></i> Couch Surfing</h1>
            <p>Connect with alumni around the world and find a place to stay during your travels. Experience local hospitality and make meaningful connections with fellow alumni.</p>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-error" style="margin-bottom: 30px;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($success)): ?>
            <div class="alert alert-success" style="margin-bottom: 30px;">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <div class="couch-container">
            <!-- Request Form -->
            <div class="couch-form">
                <h2><i class="fas fa-paper-plane"></i> New Couch Request</h2>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="destination">Destination City</label>
                        <input type="text" id="destination" name="destination" required 
                               placeholder="Where would you like to stay?">
                    </div>

                    <div class="form-group">
                        <label for="check_in">Check-in Date</label>
                        <input type="date" id="check_in" name="check_in" required 
                               min="<?php echo date('Y-m-d'); ?>">
                    </div>

                    <div class="form-group">
                        <label for="check_out">Check-out Date</label>
                        <input type="date" id="check_out" name="check_out" required 
                               min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                    </div>

                    <div class="form-group">
                        <label for="guests">Number of Guests</label>
                        <input type="number" id="guests" name="guests" min="1" max="10" value="1" required>
                    </div>

                    <div class="form-group">
                        <label for="message">Message to Host (Optional)</label>
                        <textarea id="message" name="message" 
                                 placeholder="Tell your potential host a bit about yourself and your trip..."></textarea>
                    </div>

                    <button type="submit" name="submit_request" class="btn-submit">
                        <i class="fas fa-paper-plane"></i> Submit Request
                    </button>
                </form>
            </div>

            <!-- Requests List -->
            <div class="requests-container">
                <h2><i class="fas fa-list"></i> My Couch Requests</h2>
                
                <?php if (!empty($requests)): ?>
                    <?php foreach ($requests as $request): 
                        $statusClass = 'status-' . strtolower($request['status']);
                        $checkIn = new DateTime($request['check_in']);
                        $checkOut = new DateTime($request['check_out']);
                        $now = new DateTime();
                        $isUpcoming = $checkIn > $now;
                    ?>
                        <div class="request-card fade-in">
                            <div class="request-header">
                                <div class="request-destination">
                                    <i class="fas fa-map-marker-alt"></i> 
                                    <?php echo htmlspecialchars($request['destination']); ?>
                                </div>
                                <span class="request-status <?php echo $statusClass; ?>">
                                    <?php echo ucfirst($request['status']); ?>
                                </span>
                            </div>
                            
                            <div class="request-dates">
                                <div class="meta-item">
                                    <i class="far fa-calendar-check"></i>
                                    <?php echo $checkIn->format('M j, Y'); ?>
                                </div>
                                <div class="meta-item">
                                    <i class="far fa-calendar-times"></i>
                                    <?php echo $checkOut->format('M j, Y'); ?>
                                </div>
                            </div>
                            
                            <div class="request-meta">
                                <div class="meta-item">
                                    <i class="fas fa-user-friends"></i>
                                    <?php echo $request['guests'] . ($request['guests'] > 1 ? ' guests' : ' guest'); ?>
                                </div>
                                <div class="meta-item">
                                    <i class="far fa-clock"></i>
                                    <?php 
                                    $interval = $checkIn->diff($checkOut);
                                    echo $interval->days . ($interval->days > 1 ? ' nights' : ' night');
                                    ?>
                                </div>
                                <div class="meta-item">
                                    <i class="far fa-calendar"></i>
                                    <?php 
                                    $created = new DateTime($request['created_at']);
                                    echo 'Requested ' . $created->format('M j, Y');
                                    ?>
                                </div>
                            </div>
                            
                            <?php if (!empty($request['message'])): ?>
                                <div class="request-message">
                                    <p><?php echo nl2br(htmlspecialchars($request['message'])); ?></p>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($isUpcoming && $request['status'] === 'approved'): ?>
                                <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #f0f0f0;">
                                    <a href="#" class="btn-submit" style="display: inline-block; width: auto; padding: 8px 20px;">
                                        <i class="fas fa-envelope"></i> Contact Host
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-requests">
                        <i class="fas fa-couch"></i>
                        <p>You haven't made any couch surfing requests yet.<br>Fill out the form to get started!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Set minimum check-out date based on check-in date
        document.getElementById('check_in').addEventListener('change', function() {
            const checkInDate = this.value;
            const checkOutField = document.getElementById('check_out');
            
            if (checkInDate) {
                // Set min date to day after check-in
                const nextDay = new Date(checkInDate);
                nextDay.setDate(nextDay.getDate() + 1);
                const nextDayStr = nextDay.toISOString().split('T')[0];
                
                checkOutField.min = nextDayStr;
                
                // If current check-out is before new min date, update it
                if (checkOutField.value && checkOutField.value <= checkInDate) {
                    checkOutField.value = nextDayStr;
                }
            }
        });

        // Add fade-in animation to new requests
        document.addEventListener('DOMContentLoaded', function() {
            const requests = document.querySelectorAll('.request-card');
            requests.forEach((request, index) => {
                // Stagger the animations
                request.style.animationDelay = `${index * 0.1}s`;
            });
        });
    </script>
</body>
</html>
