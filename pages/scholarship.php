<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Institution Scholarship - Alumni Connect</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .page-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, #7a2a2a 100%);
            color: var(--white);
            padding: 60px 40px;
            text-align: center;
        }

        .page-header h1 {
            font-size: 42px;
            margin-bottom: 15px;
        }

        .page-header p {
            font-size: 18px;
            opacity: 0.9;
        }

        .scholarship-content {
            padding: 60px 40px;
            background: var(--bg-light);
        }

        .scholarship-intro {
            max-width: 800px;
            margin: 0 auto 60px;
            text-align: center;
        }

        .scholarship-intro h2 {
            color: var(--primary-color);
            font-size: 32px;
            margin-bottom: 20px;
        }

        .scholarship-intro p {
            font-size: 18px;
            line-height: 1.6;
            color: var(--text-light);
            margin-bottom: 40px;
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
            margin-bottom: 60px;
        }

        .stat-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            box-shadow: var(--shadow);
        }

        .stat-number {
            font-size: 36px;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 10px;
        }

        .stat-label {
            color: var(--text-light);
            font-size: 16px;
        }

        .application-form {
            background: white;
            border-radius: 20px;
            padding: 50px;
            box-shadow: var(--shadow-lg);
            max-width: 800px;
            margin: 0 auto;
        }

        .form-section {
            margin-bottom: 40px;
        }

        .form-section h3 {
            color: var(--primary-color);
            font-size: 24px;
            margin-bottom: 25px;
            border-bottom: 2px solid var(--secondary-color);
            padding-bottom: 10px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin-bottom: 25px;
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
            min-height: 100px;
        }

        .amount-display {
            background: rgba(236, 195, 92, 0.1);
            border: 2px solid var(--secondary-color);
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            margin: 20px 0;
        }

        .amount-display h4 {
            color: var(--primary-color);
            font-size: 18px;
            margin-bottom: 10px;
        }

        .amount-display .amount {
            font-size: 32px;
            font-weight: 700;
            color: var(--secondary-color);
        }

        .form-actions {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin-top: 40px;
        }

        .btn {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 15px 40px;
            border-radius: 25px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #6c757d;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        @media (max-width: 768px) {
            .page-header {
                padding: 40px 20px;
            }

            .page-header h1 {
                font-size: 32px;
            }

            .scholarship-content {
                padding: 40px 20px;
            }

            .scholarship-intro h2 {
                font-size: 28px;
            }

            .form-row {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .application-form {
                padding: 30px 20px;
            }

            .form-actions {
                flex-direction: column;
                gap: 15px;
            }

            .btn {
                width: 100%;
                padding: 15px 20px;
            }
        }
    </style>
</head>
<body>
    <?php include '../sidebar.php'; ?>

    <div class="main-content" id="mainContent">
        <div class="page-header">
            <h1>Institution Scholarship Program</h1>
            <p>Support the next generation of leaders through educational funding</p>
        </div>

        <div class="scholarship-content">
            <div class="scholarship-intro">
                <h2>Make a Difference in a Student's Life</h2>
                <p>Our scholarship program connects generous alumni donors with deserving students who need financial assistance to pursue their education. Your contribution can help shape the future of our institution and create lasting impact.</p>

                <div class="stats-container">
                    <div class="stat-card">
                        <div class="stat-number">₹50L+</div>
                        <div class="stat-label">Total Donated</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">100+</div>
                        <div class="stat-label">Students Supported</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">25+</div>
                        <div class="stat-label">Active Scholarships</div>
                    </div>
                </div>
            </div>

            <div class="application-form">
                <form id="scholarshipForm">
                    <div class="form-section">
                        <h3>Scholarship Details</h3>

                        <div class="form-group">
                            <label for="scholarshipTitle">Scholarship Title</label>
                            <input type="text" id="scholarshipTitle" name="title" required placeholder="e.g., John Doe Memorial Scholarship">
                        </div>

                        <div class="form-group">
                            <label for="scholarshipDescription">Description</label>
                            <textarea id="scholarshipDescription" name="description" required placeholder="Describe the purpose and goals of this scholarship..."></textarea>
                        </div>

                        <div class="form-group">
                            <label for="scholarshipAmount">Scholarship Amount (₹)</label>
                            <input type="number" id="scholarshipAmount" name="amount" min="1000" required placeholder="50000">
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="studentDetails">Target Students</label>
                                <input type="text" id="studentDetails" name="student_details" required placeholder="e.g., Undergraduate students from low-income families">
                            </div>
                            <div class="form-group">
                                <label for="scholarshipPurpose">Purpose</label>
                                <select id="scholarshipPurpose" name="purpose" required>
                                    <option value="">Select Purpose</option>
                                    <option value="tuition">Tuition Support</option>
                                    <option value="books">Books & Materials</option>
                                    <option value="living">Living Expenses</option>
                                    <option value="research">Research Support</option>
                                    <option value="travel">Travel & Conference</option>
                                    <option value="general">General Education</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3>Your Information</h3>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="applicantName">Your Full Name</label>
                                <input type="text" id="applicantName" name="applicant_name" required>
                            </div>
                            <div class="form-group">
                                <label for="applicantEmail">Email Address</label>
                                <input type="email" id="applicantEmail" name="applicant_email" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="applicantPhone">Phone Number</label>
                            <input type="tel" id="applicantPhone" name="applicant_phone" placeholder="+91 9876543210">
                        </div>
                    </div>

                    <div class="form-section">
                        <h3>Payment Information</h3>
                        <p style="color: var(--text-light); margin-bottom: 20px;">Note: Payment processing will be handled after admin approval of your scholarship application.</p>

                        <div class="form-group">
                            <label for="paymentMethod">Preferred Payment Method</label>
                            <select id="paymentMethod" name="payment_method">
                                <option value="bank">Bank Transfer</option>
                                <option value="cheque">Cheque</option>
                                <option value="online">Online Payment</option>
                                <option value="installment">Monthly Installment</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="paymentSchedule">Payment Schedule</label>
                            <select id="paymentSchedule" name="payment_schedule">
                                <option value="lump_sum">Lump Sum</option>
                                <option value="annual">Annual</option>
                                <option value="semester">Per Semester</option>
                                <option value="monthly">Monthly</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="history.back()">Cancel</button>
                        <button type="submit" class="btn">Submit Application</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Handle form submission
        document.getElementById('scholarshipForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.textContent = 'Submitting...';
            submitBtn.disabled = true;

            fetch('/alumni/submit_scholarship.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Scholarship application submitted successfully! You will receive a confirmation email shortly.');
                    this.reset();
                } else {
                    alert('Error submitting application. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error submitting application. Please try again.');
            })
            .finally(() => {
                // Restore button state
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            });
        });

        // Update amount display in real-time
        document.getElementById('scholarshipAmount').addEventListener('input', function() {
            const amount = this.value;
            const display = document.querySelector('.amount-display .amount');
            if (display) {
                display.textContent = '₹' + (amount ? new Intl.NumberFormat('en-IN').format(amount) : '0');
            }
        });
    </script>
</body>
</html>
