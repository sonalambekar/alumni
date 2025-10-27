<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Institute Medal - Alumni Connect</title>
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

        .medal-content {
            padding: 60px 40px;
            background: var(--bg-light);
        }

        .medal-intro {
            max-width: 800px;
            margin: 0 auto 60px;
            text-align: center;
        }

        .medal-intro h2 {
            color: var(--primary-color);
            font-size: 32px;
            margin-bottom: 20px;
        }

        .medal-intro p {
            font-size: 18px;
            line-height: 1.6;
            color: var(--text-light);
            margin-bottom: 40px;
        }

        .medal-categories {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 60px;
        }

        .medal-category {
            background: white;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            box-shadow: var(--shadow);
            transition: transform 0.3s ease;
        }

        .medal-category:hover {
            transform: translateY(-5px);
        }

        .medal-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary-color), #7a2a2a);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 32px;
            color: white;
        }

        .medal-category h3 {
            color: var(--primary-color);
            font-size: 24px;
            margin-bottom: 15px;
        }

        .medal-category p {
            color: var(--text-light);
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .medal-stats {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: auto;
        }

        .medal-stat {
            background: rgba(236, 195, 92, 0.1);
            color: var(--primary-color);
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .nomination-form {
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

        .past-recipients {
            background: white;
            border-radius: 20px;
            padding: 50px;
            box-shadow: var(--shadow-lg);
            max-width: 1000px;
            margin: 60px auto 0;
        }

        .past-recipients h3 {
            color: var(--primary-color);
            font-size: 32px;
            text-align: center;
            margin-bottom: 40px;
        }

        .recipients-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }

        .recipient-card {
            background: var(--bg-light);
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            border-left: 5px solid var(--secondary-color);
        }

        .recipient-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color), #7a2a2a);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: white;
            font-size: 32px;
            font-weight: 600;
        }

        .recipient-name {
            font-size: 20px;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 10px;
        }

        .recipient-year {
            color: var(--secondary-color);
            font-weight: 500;
            margin-bottom: 15px;
        }

        .recipient-category {
            background: rgba(91, 31, 31, 0.1);
            color: var(--primary-color);
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
            margin-bottom: 15px;
        }

        .recipient-achievement {
            color: var(--text-light);
            line-height: 1.6;
            font-size: 14px;
        }

        @media (max-width: 768px) {
            .page-header {
                padding: 40px 20px;
            }

            .page-header h1 {
                font-size: 32px;
            }

            .medal-content {
                padding: 40px 20px;
            }

            .medal-intro h2 {
                font-size: 28px;
            }

            .medal-categories {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .form-row {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .nomination-form,
            .past-recipients {
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

            .recipients-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
        }
    </style>
</head>
<body>
    <?php include '../sidebar.php'; ?>

    <div class="main-content" id="mainContent">
        <div class="page-header">
            <h1>Institute Medal Program</h1>
            <p>Recognizing excellence and celebrating outstanding alumni achievements</p>
        </div>

        <div class="medal-content">
            <div class="medal-intro">
                <h2>Honor Our Distinguished Alumni</h2>
                <p>The Institute Medal Program recognizes alumni who have made exceptional contributions in their fields and demonstrated outstanding leadership, innovation, and service to society. Nominate deserving alumni for this prestigious recognition.</p>
            </div>

            <div class="medal-categories">
                <div class="medal-category">
                    <div class="medal-icon">🎓</div>
                    <h3>Academic Excellence</h3>
                    <p>Recognizing alumni who have made significant contributions to research, education, and academic advancement.</p>
                    <div class="medal-stats">
                        <span class="medal-stat">Research Impact</span>
                        <span class="medal-stat">Publications</span>
                    </div>
                </div>

                <div class="medal-category">
                    <div class="medal-icon">💼</div>
                    <h3>Professional Achievement</h3>
                    <p>Honoring alumni who have achieved remarkable success in their professional careers and leadership roles.</p>
                    <div class="medal-stats">
                        <span class="medal-stat">Career Success</span>
                        <span class="medal-stat">Leadership</span>
                    </div>
                </div>

                <div class="medal-category">
                    <div class="medal-icon">🌍</div>
                    <h3>Community Service</h3>
                    <p>Celebrating alumni who have made significant contributions to community development and social impact.</p>
                    <div class="medal-stats">
                        <span class="medal-stat">Social Impact</span>
                        <span class="medal-stat">Philanthropy</span>
                    </div>
                </div>

                <div class="medal-category">
                    <div class="medal-icon">🚀</div>
                    <h3>Entrepreneurship</h3>
                    <p>Recognizing alumni who have founded successful ventures and contributed to economic growth and innovation.</p>
                    <div class="medal-stats">
                        <span class="medal-stat">Innovation</span>
                        <span class="medal-stat">Startups</span>
                    </div>
                </div>

                <div class="medal-category">
                    <div class="medal-icon">🔬</div>
                    <h3>Innovation</h3>
                    <p>Honoring alumni who have pioneered new technologies, processes, or solutions that benefit society.</p>
                    <div class="medal-stats">
                        <span class="medal-stat">Technology</span>
                        <span class="medal-stat">Patents</span>
                    </div>
                </div>

                <div class="medal-category">
                    <div class="medal-icon">🏆</div>
                    <h3>Lifetime Achievement</h3>
                    <p>Celebrating alumni with exceptional careers spanning decades of dedication and outstanding contributions.</p>
                    <div class="medal-stats">
                        <span class="medal-stat">Legacy</span>
                        <span class="medal-stat">Impact</span>
                    </div>
                </div>
            </div>

            <div class="nomination-form">
                <form id="medalNominationForm">
                    <div class="form-section">
                        <h3>Nominee Information</h3>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="nomineeName">Full Name</label>
                                <input type="text" id="nomineeName" name="nominee_name" required>
                            </div>
                            <div class="form-group">
                                <label for="nomineeEmail">Email Address</label>
                                <input type="email" id="nomineeEmail" name="nominee_email" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="nomineeGraduationYear">Graduation Year</label>
                                <input type="number" id="nomineeGraduationYear" name="nominee_graduation_year" min="1950" max="2025">
                            </div>
                            <div class="form-group">
                                <label for="nomineeDegree">Degree/Program</label>
                                <input type="text" id="nomineeDegree" name="nominee_degree" placeholder="e.g., B.Tech Computer Science">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="medalCategory">Award Category</label>
                            <select id="medalCategory" name="category" required>
                                <option value="">Select Category</option>
                                <option value="academic">Academic Excellence</option>
                                <option value="professional">Professional Achievement</option>
                                <option value="community">Community Service</option>
                                <option value="entrepreneurship">Entrepreneurship</option>
                                <option value="innovation">Innovation</option>
                                <option value="lifetime">Lifetime Achievement</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="nominationReason">Reason for Nomination</label>
                            <textarea id="nominationReason" name="reason" required placeholder="Describe why this person deserves the Institute Medal..."></textarea>
                        </div>

                        <div class="form-group">
                            <label for="nomineeAchievements">Key Achievements</label>
                            <textarea id="nomineeAchievements" name="achievements" placeholder="List major achievements, awards, publications, or milestones..."></textarea>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3>Your Information</h3>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="nominatorName">Your Full Name</label>
                                <input type="text" id="nominatorName" name="nominator_name" required>
                            </div>
                            <div class="form-group">
                                <label for="nominatorEmail">Your Email Address</label>
                                <input type="email" id="nominatorEmail" name="nominator_email" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="nominatorPhone">Your Phone Number</label>
                                <input type="tel" id="nominatorPhone" name="nominator_phone" placeholder="+91 9876543210">
                            </div>
                            <div class="form-group">
                                <label for="nominatorRelationship">Your Relationship to Nominee</label>
                                <input type="text" id="nominatorRelationship" name="nominator_relationship" required placeholder="e.g., Colleague, Former Student, Friend">
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="history.back()">Cancel</button>
                        <button type="submit" class="btn">Submit Nomination</button>
                    </div>
                </form>
            </div>

            <div class="past-recipients">
                <h3>Past Recipients</h3>
                <div class="recipients-grid">
                    <div class="recipient-card">
                        <div class="recipient-avatar">DR</div>
                        <div class="recipient-name">Dr. Rajesh Kumar</div>
                        <div class="recipient-year">Class of 1985</div>
                        <div class="recipient-category">Academic Excellence</div>
                        <div class="recipient-achievement">Pioneering research in artificial intelligence with 200+ publications and mentorship of 50+ PhD students.</div>
                    </div>

                    <div class="recipient-card">
                        <div class="recipient-avatar">PS</div>
                        <div class="recipient-name">Priya Sharma</div>
                        <div class="recipient-year">Class of 1992</div>
                        <div class="recipient-category">Professional Achievement</div>
                        <div class="recipient-achievement">CEO of Fortune 500 company, recognized for leadership excellence and corporate governance.</div>
                    </div>

                    <div class="recipient-card">
                        <div class="recipient-avatar">AM</div>
                        <div class="recipient-name">Arun Mehta</div>
                        <div class="recipient-year">Class of 1988</div>
                        <div class="recipient-category">Entrepreneurship</div>
                        <div class="recipient-achievement">Founded 3 successful startups, created 1000+ jobs, and contributed to India's tech ecosystem growth.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Handle form submission
        document.getElementById('medalNominationForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.textContent = 'Submitting...';
            submitBtn.disabled = true;

            fetch('/alumni/submit_medal_nomination.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Nomination submitted successfully! The review committee will evaluate your nomination.');
                    this.reset();
                } else {
                    alert('Error submitting nomination. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error submitting nomination. Please try again.');
            })
            .finally(() => {
                // Restore button state
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            });
        });
    </script>
</body>
</html>
