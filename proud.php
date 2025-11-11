<?php
session_start();
require_once 'includes/db_config.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$pageTitle = 'Proud Alumni';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Alumni Connect</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .page-header {
            background: var(--primary-color);
            color: white;
            padding: 60px 0 40px;
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
        }

        .page-header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            position: relative;
            z-index: 2;
        }

        .page-header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .page-header h1 i {
            color: #ecc35c;
        }

        .page-header p {
            font-size: 1.1rem;
            opacity: 0.9;
            max-width: 800px;
        }

        .alumni-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .alumni-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        @media (min-width: 768px) {
            .alumni-card {
                flex-direction: row;
                min-height: 300px;
            }
        }

        .alumni-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .alumni-image {
            flex: 0 0 300px;
            position: relative;
            overflow: hidden;
        }

        .alumni-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .alumni-card:hover .alumni-image img {
            transform: scale(1.05);
        }

        .alumni-content {
            flex: 1;
            padding: 30px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .alumni-name {
            font-size: 1.8rem;
            color: #2c3e50;
            margin-bottom: 10px;
            position: relative;
            display: inline-block;
        }

        .alumni-name::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 0;
            width: 60px;
            height: 3px;
            background-color: #e74c3c;
        }

        .alumni-year {
            display: inline-block;
            background-color: #e74c3c;
            color: white;
            padding: 3px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            margin-bottom: 15px;
            font-weight: 500;
        }

        .alumni-description {
            color: #555;
            line-height: 1.7;
            margin-bottom: 20px;
        }

        @media (max-width: 767px) {
            .alumni-card {
                flex-direction: column;
            }
            
            .alumni-image {
                height: 250px;
            }
            
            .page-header {
                padding: 40px 0 30px;
            }
            
            .page-header h1 {
                font-size: 2rem;
            }
            
            .alumni-content {
                padding: 20px;
            }
            
            .alumni-name {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="main-content" id="mainContent">
        <div class="page-header">
            <div class="page-header-content">
                <h1><i class="fas fa-trophy"></i> Proud Alumni</h1>
                <p>Celebrating the outstanding achievements of our distinguished alumni community</p>
            </div>
        </div>

        <div class="container">
            <div class="alumni-container">
                <div class="alumni-card">
                    <div class="alumni-image">
                        <img src="assets/images/proud/Milind.png" alt="Milind DM">
                    </div>
                    <div class="alumni-content">
                        <h2 class="alumni-name">Milind DM</h2>
                        <span class="alumni-year">2020 Output</span>
                        <p class="alumni-description">
                            Pursued his passion for science with a BE Biotechnology, a fusion of all sciences. Currently working in Biocon Biologics Ltd as an executive in the upstream production department for PEG-GCSF. Worked in Shilpa Biologicals Private ltd, Dharwad as a trainee in production department in microbial fermentation upstream process for Recombinant Human Albumin & Zycov-D corona vaccine. Worked as student intern in Karnataka Antibiotics and Pharmaceuticals limited, Banglore.
                        </p>
                    </div>
                </div>
                <div class="alumni-card">
                    <div class="alumni-image">
                        <img src="assets/images/proud/sindhu.png" alt="Sindhu S Patil">
                    </div>
                    <div class="alumni-content">
                        <h2 class="alumni-name">Sindhu S Patil</h2>
                        <span class="alumni-year">2016 Output</span>
                        <p class="alumni-description">
                            Dedicated Advocate enrolled with the Karnataka State Bar Council, adept in communication and administration. Skilled in trademark drafting and prosecution across India and the US. Proficient in conducting IP searches and ensuring compliance. Expert in managing prosecution proceedings and client IP profiles with meticulous attention to deadlines. Actively contributes to idea generation and innovation. Experienced in attending arbitration hearings and drafting legal contracts. Interned with various legal offices during LLB and LLM studies. Specialized in Intellectual Property Rights, Corporate Law, Criminal Law, Legal Research, and Drafting. Passionate about leveraging legal expertise to serve clients effectively and uphold justice.
                        </p>
                    </div>
                </div>
                <!-- Alumni 1 -->
                <div class="alumni-card">
                    <div class="alumni-image">
                        <img src="assets/images/proud/Manjunath.png" alt="Dr. Manjunatha Thondamal">
                    </div>
                    <div class="alumni-content">
                        <h2 class="alumni-name">Dr. Manjunatha Thondamal</h2>
                        <span class="alumni-year">2006 Output</span>
                        <p class="alumni-description">
                            Manjunatha Thondamal is an Associate Professor at Department of Biotechnology, GST. He received his Ph.D. from the ENS de Lyon, France in 2014. He was a postdoctoral research associate at University of Rochester, NY USA. Before joining GITAM, he served as DST INSPIRE Faculty at DRILS Hyderabad and at SRM University AP. He is a visiting scientist at Institute Pasteur, Paris. His research group at GITAM mainly focuses on the genetics of aging using C. elegans as model system.
                        </p>
                    </div>
                </div>

                <!-- Alumni 2 -->
                <div class="alumni-card">
                    <div class="alumni-image">
                        <img src="assets/images/proud/Anshu.png" alt="Dr. Anshu Alok">
                    </div>
                    <div class="alumni-content">
                        <h2 class="alumni-name">Dr. Anshu Alok</h2>
                        <span class="alumni-year">2009 Output</span>
                        <p class="alumni-description">
                            Anshu Alok is a postdoctoral researcher at the University of Minnesota, USA, working in plant molecular biology and biotechnology. His research focuses on CRISPR-based genome editing tools like Cas9 and Cpf1 to improve crop resilience and genetic engineering methods. He has published over 90 papers, with more than 2,300 citations and 21,000 reads on ResearchGate. His contributions include advances in soybean transformation, gene regulation, and plant stress response studies. He also serves in editorial roles for journals such as BMC Plant Methods and Frontiers in Genome Editing. Through his work, he is driving innovations in sustainable agriculture and crop improvement.
                        </p>
                    </div>
                </div>

                <!-- Alumni 3 -->

                
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
    <script>
        // Toggle sidebar
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
            document.getElementById('sidebarOverlay').classList.toggle('active');
        });

        // Close sidebar when clicking outside
        document.getElementById('sidebarOverlay').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.remove('active');
            this.classList.remove('active');
        });

        // Add active class to current page in sidebar
        document.addEventListener('DOMContentLoaded', function() {
            const currentPage = window.location.pathname.split('/').pop() || 'index.php';
            const navLinks = document.querySelectorAll('.nav-link');
            
            navLinks.forEach(link => {
                if (link.getAttribute('href').includes(currentPage)) {
                    link.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>
