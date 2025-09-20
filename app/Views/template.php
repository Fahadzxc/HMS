<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'City General Hospital' ?></title>

    <!-- Hospital Management System - Custom CSS -->
    <style>
        /* Reset and Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #2c3e50;
            background-color: #ecf0f1;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header */
        .header {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 1.5rem 0;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .header .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo-icon {
            font-size: 2.5rem;
            filter: drop-shadow(2px 2px 4px rgba(0,0,0,0.3));
        }

        .logo h1 {
            font-size: 2rem;
            font-weight: 700;
            letter-spacing: 1px;
        }

        .header-info {
            text-align: right;
            font-size: 0.9rem;
        }

        .header-info p {
            margin: 0;
            opacity: 0.9;
        }

        /* Navigation */
        .navbar {
            background: #2c3e50;
            padding: 0.75rem 0;
            border-bottom: 3px solid #3498db;
        }

        .navbar .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar-nav {
            display: flex;
            list-style: none;
            gap: 0;
        }

        .navbar-nav a {
            color: white;
            text-decoration: none;
            padding: 1rem 1.5rem;
            transition: all 0.3s ease;
            font-weight: 500;
            border-radius: 0;
            position: relative;
        }

        .navbar-nav a:hover {
            background-color: #3498db;
            transform: translateY(-2px);
        }

        .navbar-nav a.active {
            background-color: #3498db;
            border-bottom: 3px solid #2980b9;
        }

        /* Main Content */
        .main-content {
            min-height: calc(100vh - 200px);
            padding: 3rem 0;
        }

        /* Cards */
        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.1);
            padding: 2.5rem;
            margin-bottom: 2rem;
            border: 1px solid #e1e8ed;
            transition: transform 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(0,0,0,0.15);
        }

        .card-header {
            border-bottom: 2px solid #3498db;
            padding-bottom: 1rem;
            margin-bottom: 1.5rem;
        }

        .card-title {
            font-size: 1.8rem;
            color: #2c3e50;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .card-subtitle {
            color: #7f8c8d;
            font-size: 1rem;
            line-height: 1.5;
        }

        /* Buttons */
        .btn {
            display: inline-block;
            padding: 0.875rem 2rem;
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
            text-align: center;
            font-size: 0.95rem;
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(52, 152, 219, 0.4);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #95a5a6 0%, #7f8c8d 100%);
        }

        .btn-success {
            background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
        }

        .btn-danger {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
        }

        .btn-info {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            margin: 2rem 0;
        }

        .stat-card {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.1);
            text-align: center;
            border-left: 5px solid #3498db;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-number {
            font-size: 3rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #7f8c8d;
            font-size: 1.1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 500;
        }

        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.5rem;
            margin: 2rem 0;
        }

        .action-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 2rem 1.5rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.1);
            text-decoration: none;
            color: #2c3e50;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .action-btn:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 30px rgba(52, 152, 219, 0.2);
            border-color: #3498db;
        }

        .action-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .action-text {
            font-weight: 600;
            text-align: center;
            font-size: 1.1rem;
        }

        /* Footer */
        .footer {
            background: #2c3e50;
            color: white;
            text-align: center;
            padding: 3rem 0 1rem 0;
            margin-top: 4rem;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .footer-section h3 {
            margin-bottom: 1.5rem;
            color: #3498db;
            font-size: 1.3rem;
        }

        .footer-section p, .footer-section a {
            color: #bdc3c7;
            text-decoration: none;
            line-height: 1.8;
            font-size: 0.95rem;
        }

        .footer-section a:hover {
            color: #3498db;
        }

        .footer-bottom {
            border-top: 1px solid #34495e;
            padding-top: 2rem;
            color: #95a5a6;
            font-size: 0.9rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .header .container {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .navbar .container {
                flex-direction: column;
                gap: 1rem;
            }

            .navbar-nav {
                flex-direction: column;
                width: 100%;
            }

            .navbar-nav a {
                text-align: center;
                border-radius: 8px;
                margin: 0.25rem 0;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .quick-actions {
                grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            }

            .logo h1 {
                font-size: 1.5rem;
            }

            .header-info {
                text-align: center;
            }
        }

        /* Loading Animation - more human-like */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px) rotate(2deg);
            }
            to {
                opacity: 1;
                transform: translateY(0) rotate(0deg);
            }
        }

        @keyframes wobble {
            0% { transform: rotate(0deg); }
            25% { transform: rotate(0.5deg); }
            50% { transform: rotate(-0.3deg); }
            75% { transform: rotate(0.2deg); }
            100% { transform: rotate(0deg); }
        }

        .card {
            animation: fadeInUp 0.8s ease-out;
        }

        .stat-card, .action-btn {
            animation: fadeInUp 0.7s ease-out;
        }

        /* Add some personality to elements */
        .navbar-nav a:hover {
            animation: wobble 0.6s ease-in-out;
        }

        /* Handwritten feel for some text */
        .action-text {
            font-family: 'Comic Sans MS', cursive, sans-serif;
            font-weight: 600;
        }

        /* Add some texture */
        .header {
            background-image:
                radial-gradient(circle at 20% 50%, rgba(255,255,255,0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(255,255,255,0.1) 0%, transparent 50%),
                linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
        }

        /* Slightly imperfect borders */
        .card {
            border-radius: 12px 8px 12px 8px;
        }

        .stat-card {
            border-radius: 8px 12px 8px 12px;
        }

        /* Add some human touch to buttons */
        .btn:hover {
            transform: translateY(-2px) scale(1.02);
        }

        /* Personal signature style */
        .footer-bottom::after {
            content: " - Made with ❤️ by humans, for humans";
            font-size: 0.8rem;
            opacity: 0.7;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="logo">
                <span class="logo-icon">🏥</span>
                <h1>City General Hospital</h1>
            </div>
            <div class="header-info">
                <p>Hospital Management System</p>
                <p><?= date('l, F j, Y') ?></p>
            </div>
        </div>
    </header>

    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <ul class="navbar-nav">
                <li><a href="<?= base_url('home') ?>" class="active">Home</a></li>
                <li><a href="<?= base_url('about') ?>">About</a></li>
                <li><a href="<?= base_url('services') ?>">Services</a></li>
                <li><a href="<?= base_url('doctors') ?>">Doctors</a></li>
                <li><a href="<?= base_url('contact') ?>">Contact</a></li>
                <li><a href="<?= base_url('appointments') ?>">Appointments</a></li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <?= $this->renderSection('content') ?>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Contact Information</h3>
                    <p><strong>Address:</strong> 123 Healthcare Ave, Medical City, MC 12345</p>
                    <p><strong>Phone:</strong> (555) 123-4567</p>
                    <p><strong>Email:</strong> info@citygeneralhospital.com</p>
                    <p><strong>Emergency:</strong> (555) 911-0000</p>
                </div>
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <p><a href="<?= base_url('about') ?>">About Us</a></p>
                    <p><a href="<?= base_url('services') ?>">Our Services</a></p>
                    <p><a href="<?= base_url('doctors') ?>">Find a Doctor</a></p>
                    <p><a href="<?= base_url('appointments') ?>">Book Appointment</a></p>
                    <p><a href="<?= base_url('contact') ?>">Contact Us</a></p>
                </div>
                <div class="footer-section">
                    <h3>Hours of Operation</h3>
                    <p><strong>Emergency:</strong> 24/7</p>
                    <p><strong>Outpatient:</strong> 6:00 AM - 10:00 PM</p>
                    <p><strong>Visiting Hours:</strong> 8:00 AM - 8:00 PM</p>
                    <p><strong>Pharmacy:</strong> 24/7</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> City General Hospital. All rights reserved. | Licensed Healthcare Facility</p>
            </div>
        </div>
    </footer>
</body>
</html>
