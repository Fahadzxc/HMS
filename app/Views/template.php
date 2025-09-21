<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'MediCare Hospital' ?></title>
    <style>
        /* MediCare Hospital - Clean Professional Design */

        /* Reset and base styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #2c3e50;
            background-color: #fafbfc;
            height: 100vh;
            overflow: hidden;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header */
        .header {
            background: #ffffff;
            padding: 1.5rem 0;
            border-bottom: 1px solid #e8f4f8;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .header .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #74b9ff, #0984e3);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
        }

        .logo h1 {
            font-size: 1.8rem;
            font-weight: 600;
            color: #2c3e50;
            margin: 0;
        }

        /* Navigation */
        .navbar {
            display: flex;
            align-items: center;
        }

        .navbar-nav {
            display: flex;
            list-style: none;
            gap: 0;
        }

        .nav-link {
            color: #5a6c7d;
            text-decoration: none;
            padding: 0.75rem 1.5rem;
            transition: all 0.3s ease;
            font-weight: 500;
            font-size: 0.95rem;
            border-radius: 6px;
        }

        .nav-link:hover {
            color: #74b9ff;
            background: #f8f9fa;
        }

        .nav-link.active {
            color: #74b9ff;
            background: #e3f2fd;
        }

        /* Main content */
        .main-content {
            padding: 2rem 0;
            height: calc(100vh - 140px);
            overflow-y: auto;
        }

        /* Homepage styles */
        .hero-section {
            text-align: center;
            max-width: 900px;
            margin: 0 auto;
        }

        .hero-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 1rem;
            line-height: 1.2;
        }

        .hero-subtitle {
            font-size: 1rem;
            color: #7f8c8d;
            margin-bottom: 2rem;
            line-height: 1.5;
        }

        .services-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .service-card {
            background: #ffffff;
            padding: 1.5rem 1.2rem;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            border: 1px solid #f1f3f4;
        }

        .service-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 30px rgba(0,0,0,0.12);
        }

        .service-icon {
            width: 50px;
            height: 50px;
            margin: 0 auto 1rem;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            color: white;
        }

        .emergency-icon {
            background: linear-gradient(135deg, #ff7675, #d63031);
        }

        .treatment-icon {
            background: linear-gradient(135deg, #74b9ff, #0984e3);
        }

        .care-icon {
            background: linear-gradient(135deg, #00b894, #00a085);
        }

        .service-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.8rem;
        }

        .service-description {
            font-size: 0.85rem;
            color: #7f8c8d;
            line-height: 1.4;
        }

        /* About page styles */
        .about-section {
            max-width: 1000px;
            margin: 0 auto;
        }

        .about-title {
            font-size: 2.2rem;
            font-weight: 700;
            color: #2c3e50;
            text-align: center;
            margin-bottom: 0.8rem;
        }

        .about-subtitle {
            font-size: 0.95rem;
            color: #7f8c8d;
            text-align: center;
            margin-bottom: 2rem;
            line-height: 1.5;
        }

        .story-vision-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .story-section, .vision-section {
            padding: 1.5rem;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border: 1px solid #f1f3f4;
        }

        .story-section {
            background: linear-gradient(135deg, #e8f5e8 0%, #ffffff 100%);
        }

        .vision-section {
            background: linear-gradient(135deg, #e3f2fd 0%, #ffffff 100%);
        }

        .section-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.8rem;
        }

        .section-text {
            font-size: 0.85rem;
            color: #7f8c8d;
            line-height: 1.5;
        }

        .departments-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .department-card {
            background: #ffffff;
            padding: 1.2rem;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            text-align: center;
            transition: all 0.3s ease;
            border: 1px solid #f1f3f4;
        }

        .department-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        }

        .department-icon {
            width: 40px;
            height: 40px;
            margin: 0 auto 0.8rem;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: white;
        }

        .cardiology-icon {
            background: linear-gradient(135deg, #ff7675, #d63031);
        }

        .neurology-icon {
            background: linear-gradient(135deg, #74b9ff, #0984e3);
        }

        .pediatrics-icon {
            background: linear-gradient(135deg, #00b894, #00a085);
        }

        .surgery-icon {
            background: linear-gradient(135deg, #a29bfe, #6c5ce7);
        }

        .pharmacy-icon {
            background: linear-gradient(135deg, #fd79a8, #e84393);
        }

        .laboratory-icon {
            background: linear-gradient(135deg, #fdcb6e, #e17055);
        }

        .department-title {
            font-size: 0.9rem;
            font-weight: 600;
            color: #2c3e50;
            margin: 0;
        }

        .stats-section {
            background: linear-gradient(135deg, #74b9ff, #0984e3);
            padding: 1.5rem;
            border-radius: 12px;
            text-align: center;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
        }

        .stat-item {
            color: white;
        }

        .stat-number {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0.3rem;
        }

        .stat-label {
            font-size: 0.8rem;
            opacity: 0.9;
        }

        /* Contact page styles */
        .contact-section {
            max-width: 1000px;
            margin: 0 auto;
        }

        .contact-title {
            font-size: 2.2rem;
            font-weight: 700;
            color: #2c3e50;
            text-align: center;
            margin-bottom: 0.8rem;
        }

        .contact-subtitle {
            font-size: 0.95rem;
            color: #7f8c8d;
            text-align: center;
            margin-bottom: 2rem;
            line-height: 1.5;
        }

        .contact-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .contact-info {
            background: #ffffff;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border: 1px solid #f1f3f4;
        }

        .contact-info h2 {
            font-size: 1.2rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 1.2rem;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            margin-bottom: 1.2rem;
        }

        .contact-icon {
            width: 35px;
            height: 35px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            color: white;
        }

        .contact-icon.address {
            background: linear-gradient(135deg, #74b9ff, #0984e3);
        }

        .contact-icon.phone {
            background: linear-gradient(135deg, #00b894, #00a085);
        }

        .contact-icon.email {
            background: linear-gradient(135deg, #fd79a8, #e84393);
        }

        .contact-icon.emergency {
            background: linear-gradient(135deg, #ff7675, #d63031);
        }

        .contact-details h4 {
            font-size: 0.9rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.2rem;
        }

        .contact-details p {
            font-size: 0.8rem;
            color: #7f8c8d;
            margin: 0;
            line-height: 1.4;
        }

        .contact-form {
            background: #ffffff;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border: 1px solid #f1f3f4;
        }

        .contact-form h2 {
            font-size: 1.2rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 1.2rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            font-size: 0.85rem;
            font-weight: 500;
            color: #2c3e50;
            margin-bottom: 0.4rem;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 0.7rem;
            border: 2px solid #e8f4f8;
            border-radius: 6px;
            font-size: 0.85rem;
            font-family: inherit;
            transition: border-color 0.3s ease;
            background: #fafbfc;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #74b9ff;
            background: #ffffff;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }

        .btn-submit {
            background: linear-gradient(135deg, #74b9ff, #0984e3);
            color: white;
            padding: 0.7rem 1.5rem;
            border: none;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(116, 185, 255, 0.3);
        }

        .emergency-section {
            background: linear-gradient(135deg, #ff7675, #d63031);
            color: white;
            padding: 1.2rem;
            border-radius: 12px;
            text-align: center;
        }

        .emergency-section h2 {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 0.6rem;
        }

        .emergency-section p {
            font-size: 0.9rem;
            margin-bottom: 0.8rem;
            opacity: 0.9;
        }

        .emergency-number {
            font-size: 1.4rem;
            font-weight: 700;
            margin: 0.6rem 0;
        }

        /* Footer */
        .footer {
            background: #2c3e50;
            color: #bdc3c7;
            text-align: center;
            padding: 1rem 0;
            margin-top: 0;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .header .container {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .navbar-nav {
                flex-direction: row;
                gap: 0.5rem;
            }

            .hero-title {
                font-size: 2.5rem;
            }

            .hero-subtitle {
                font-size: 1.1rem;
            }

            .services-grid {
                grid-template-columns: 1fr;
                gap: 2rem;
            }

            .story-vision-grid {
                grid-template-columns: 1fr;
                gap: 2rem;
            }

            .departments-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .contact-content {
                grid-template-columns: 1fr;
                gap: 2rem;
            }

            .about-title, .contact-title {
                font-size: 2.2rem;
            }
        }

        @media (max-width: 480px) {
            .hero-title {
                font-size: 2rem;
            }

            .service-card, .department-card {
                padding: 1.5rem;
            }

            .contact-info, .contact-form {
                padding: 1.5rem;
            }

            .about-title, .contact-title {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="logo">
                <div class="logo-icon">🏥</div>
                <h1>MediCare Hospital</h1>
            </div>
            <nav class="navbar">
                <ul class="navbar-nav">
                    <li><a href="<?= base_url('home') ?>" class="nav-link">Home</a></li>
                    <li><a href="<?= base_url('about') ?>" class="nav-link">About</a></li>
                    <li><a href="<?= base_url('contact') ?>" class="nav-link">Contact</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <?= $this->renderSection('content') ?>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 MediCare Hospital. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
