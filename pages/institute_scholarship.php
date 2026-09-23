<?php
session_start();
require_once '../includes/db_config.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$pageTitle = 'Institute Scholarship';
$success = false;
$error = '';

// Get current user info if logged in
$currentUser = null;
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $currentUser = $stmt->fetch();
    
    // Debug: Check user data
    if (!$currentUser) {
        error_log("No user found with ID: " . $_SESSION['user_id']);
    } else {
        error_log("User data: " . print_r($currentUser, true));
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contribute'])) {
    try {
        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
        $amount = filter_input(INPUT_POST, 'amount', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);
        
        // Basic validation
        if (empty($name) || empty($email) || empty($amount)) {
            throw new Exception('Name, email, and amount are required fields.');
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Please enter a valid email address.');
        }
        
        // Insert into database
        $stmt = $pdo->prepare("INSERT INTO contributors (name, email, phone, amount, designation, organization, message, user_id) 
                              VALUES (:name, :email, :phone, :amount, :designation, :organization, :message, :user_id)");
        
        $stmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':phone' => $phone,
            ':amount' => $amount,
            ':designation' => $currentUser['designation'] ?? null,
            ':organization' => $currentUser['organization'] ?? null,
            ':message' => $message,
            ':user_id' => $_SESSION['user_id'] ?? null
        ]);
        
        $success = true;
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Alumni Connect</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .scholarship-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .page-header {
            text-align: center;
            margin-bottom: 3rem;
            padding: 2rem 0;
            background: var(--primary-color);
            color: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .page-header h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        
        .page-header p {
            font-size: 1.1rem;
            max-width: 800px;
            margin: 0 auto;
            line-height: 1.6;
        }
        
        .scholarship-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }
        
        .scholarship-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .scholarship-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }
        
        .scholarship-header {
            background: var(--primary-color);
            color: white;
            padding: 1.5rem;
            text-align: center;
        }
        
        .scholarship-header h3 {
            margin: 0;
            font-size: 1.5rem;
        }
        
        .scholarship-body {
            padding: 1.5rem;
        }
        
        .scholarship-amount {
            font-size: 1.8rem;
            color: var(--accent-gold);
            font-weight: bold;
            text-align: center;
            margin: 1rem 0;
        }
        
        .scholarship-details {
            margin-bottom: 1.5rem;
        }
        
        .scholarship-details p {
            margin: 0.5rem 0;
            color: var(--text-dark);
            line-height: 1.6;
        }
        
        .scholarship-footer {
            padding: 1rem 1.5rem;
            background: #f9f9f9;
            border-top: 1px solid #eee;
            text-align: center;
        }
        
        .apply-btn {
            display: inline-block;
            padding: 0.8rem 2rem;
            background: var(--accent-gold);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 1rem;
        }
        
        .apply-btn:hover {
            background: #d4af37;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(212, 175, 55, 0.3);
        }
        
        .scholarship-features {
            margin: 1.5rem 0;
        }
        
        .feature-item {
            display: flex;
            align-items: center;
            margin-bottom: 0.8rem;
        }
        
        .feature-item i {
            color: var(--accent-gold);
            margin-right: 0.8rem;
            font-size: 1.2rem;
        }
        
        @media (max-width: 768px) {
            .scholarship-cards {
                grid-template-columns: 1fr;
            }
            
            .page-header h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <?php include '../sidebar.php'; ?>

    <div class="main-content" id="mainContent">
        <div class="scholarship-container">
            <div class="page-header">
                <h1><i class="fas fa-graduation-cap"></i> Institute Scholarships</h1>
                <p>Discover and apply for various scholarship opportunities offered by our institute to support your academic journey and future success.</p>
            </div>
            
            <?php if ($success): ?>
                <div class="alert alert-success" style="max-width: 600px; margin: 2rem auto; padding: 1rem; background: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 4px; text-align: center;">
                    Thank you for your contribution! We've received your details and will be in touch soon.
                </div>
            <?php elseif ($error): ?>
                <div class="alert alert-danger" style="max-width: 600px; margin: 2rem auto; padding: 1rem; background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 4px; text-align: center;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <div class="contribution-container" style="display: flex; justify-content: center; margin: 4rem 0;">
                <button class="contribute-btn" style="padding: 1.2rem 3rem; background: var(--primary-color); color: white; border: none; border-radius: 50px; font-size: 1.2rem; font-weight: 600; cursor: pointer; transition: all 0.3s ease; display: flex; align-items: center; justify-content: center; gap: 0.8rem; box-shadow: 0 4px 15px rgba(28, 6, 70, 0.2);" onclick="document.getElementById('contributionModal').style.display='block';">
                    <i class="fas fa-hand-holding-heart" style="font-size: 1.2rem;"></i>
                    Contribute Now
                </button>
            </div>
            
            <!-- Contribution Modal -->
            <div id="contributionModal" class="modal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); overflow: auto;">
                <div class="modal-content" style="background-color: #fefefe; margin: 5% auto; padding: 2rem; border-radius: 10px; max-width: 600px; position: relative;">
                    <span class="close" onclick="document.getElementById('contributionModal').style.display='none'" style="position: absolute; right: 20px; top: 10px; font-size: 28px; font-weight: bold; color: #aaa; cursor: pointer;">&times;</span>
                    
                    <h2 style="color: var(--primary-color); margin-bottom: 1.5rem; text-align: center;">Make a Contribution</h2>
                    
                    <form method="POST" action="" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <?php if (!$currentUser): ?>
                            <div class="form-group" style="grid-column: 1 / -1;">
                                <p style="color: #666; font-style: italic; text-align: center;">Please log in to have your information pre-filled.</p>
                            </div>
                        <?php endif; ?>
                        
                        <div class="form-group" style="grid-column: 1 / -1;">
                            <label for="name">Full Name <span style="color: red;">*</span></label>
                            <input type="text" id="name" name="name" required 
                                value="<?php echo htmlspecialchars($currentUser['full_name'] ?? ''); ?>"
                                style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px;"
                                <?php echo isset($currentUser) ? 'readonly' : ''; ?>>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email <span style="color: red;">*</span></label>
                            <input type="email" id="email" name="email" required 
                                value="<?php echo htmlspecialchars($currentUser['email'] ?? ''); ?>"
                                style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px;"
                                <?php echo isset($currentUser) ? 'readonly' : ''; ?>>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Phone</label>
                            <input type="tel" id="phone" name="phone" 
                                value="<?php echo htmlspecialchars($currentUser['phone'] ?? ''); ?>"
                                style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px;"
                                <?php echo (isset($currentUser) && !empty($currentUser['phone'])) ? 'readonly' : ''; ?>>
                        </div>
                        
                        <div class="form-group">
                            <label for="amount">Amount (₹) <span style="color: red;">*</span></label>
                            <input type="number" id="amount" name="amount" min="1" step="0.01" required 
                                style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px;">
                        </div>
                        
                        <input type="hidden" name="contribution_type" value="one_time">
                        
                        <?php if (isset($currentUser) && !empty($currentUser['designation'])): ?>
                            <input type="hidden" name="designation" value="<?php echo htmlspecialchars($currentUser['designation']); ?>">
                        <?php endif; ?>
                        
                        <?php if (isset($currentUser) && !empty($currentUser['organization'])): ?>
                            <input type="hidden" name="organization" value="<?php echo htmlspecialchars($currentUser['organization']); ?>">
                        <?php endif; ?>
                        
                        <div class="form-group" style="grid-column: 1 / -1;">
                            <label for="message">Message (Optional)</label>
                            <textarea id="message" name="message" rows="3" 
                                placeholder="Add a personal message (optional)"
                                style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px;"></textarea>
                        </div>
                        
                        <div class="form-actions" style="grid-column: 1 / -1; display: flex; justify-content: flex-end; gap: 1rem; margin-top: 1rem;">
                            <button type="button" onclick="document.getElementById('contributionModal').style.display='none'" style="padding: 0.5rem 1.5rem; background: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer;">Cancel</button>
                            <button type="submit" name="contribute" style="padding: 0.5rem 1.5rem; background: var(--primary-color); color: white; border: none; border-radius: 4px; cursor: pointer;">Submit Contribution</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <script>
                // Close modal when clicking outside of it
                window.onclick = function(event) {
                    var modal = document.getElementById('contributionModal');
                    if (event.target == modal) {
                        modal.style.display = "none";
                    }
                }
            </script>
            </div>
        </div>
    </div>

    <!-- Application Form Modal -->
    <div id="applicationModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal()">&times;</span>
            <h2>Apply for <span id="scholarshipTitle"></span></h2>
            <form id="scholarshipForm" onsubmit="submitApplication(event)">
                <div class="form-group">
                    <label for="fullName">Full Name</label>
                    <input type="text" id="fullName" name="fullName" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" required>
                </div>
                <div class="form-group">
                    <label for="currentEducation">Current Education Level</label>
                    <select id="currentEducation" name="currentEducation" required>
                        <option value="">Select Education Level</option>
                        <option value="high_school">High School</option>
                        <option value="undergraduate">Undergraduate</option>
                        <option value="postgraduate">Postgraduate</option>
                        <option value="phd">PhD/Doctoral</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="achievements">Achievements & Qualifications</label>
                    <textarea id="achievements" name="achievements" rows="4" required></textarea>
                </div>
                <div class="form-group">
                    <label for="documents">Upload Documents (PDF, max 5MB)</label>
                    <input type="file" id="documents" name="documents" accept=".pdf" required>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Application</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Modal functionality
        const modal = document.getElementById('applicationModal');
        
        function showApplicationForm(scholarshipName) {
            document.getElementById('scholarshipTitle').textContent = scholarshipName;
            modal.style.display = 'block';
        }
        
        function closeModal() {
            modal.style.display = 'none';
            document.getElementById('scholarshipForm').reset();
        }
        
        // Close modal when clicking outside of it
        window.onclick = function(event) {
            if (event.target === modal) {
                closeModal();
            }
        }
        
        // Form submission
        function submitApplication(event) {
            event.preventDefault();
            // Here you would typically send the form data to the server
            alert('Application submitted successfully! We will review your application and get back to you soon.');
            closeModal();
        }
    </script>
    
    <style>
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            overflow: auto;
        }
        
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 2rem;
            border-radius: 10px;
            max-width: 600px;
            width: 90%;
            position: relative;
            animation: modalFadeIn 0.3s;
        }
        
        @keyframes modalFadeIn {
            from {opacity: 0; transform: translateY(-50px);}
            to {opacity: 1; transform: translateY(0);}
        }
        
        .close-btn {
            position: absolute;
            right: 1.5rem;
            top: 1rem;
            font-size: 2rem;
            font-weight: bold;
            color: #aaa;
            cursor: pointer;
        }
        
        .close-btn:hover {
            color: #333;
        }
        
        /* Form Styles */
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--text-dark);
        }
        
        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="tel"],
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 2px rgba(28, 6, 70, 0.1);
        }
        
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 2rem;
        }
        
        .btn {
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: var(--accent-gold);
            color: white;
        }
        
        .btn-primary:hover {
            background: #d4af37;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: #f0f0f0;
            color: #333;
        }
        
        .btn-secondary:hover {
            background: #e0e0e0;
        }
        
        @media (max-width: 600px) {
            .modal-content {
                margin: 10% auto;
                width: 95%;
                padding: 1.5rem;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
        }
    </style>
</body>
</html>
