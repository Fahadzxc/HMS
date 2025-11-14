<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'MediCare Hospital' ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* MediCare Hospital - Modern Hospital System Design */
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap');

        /* Reset and base styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
            color: #2c3e50;
            background: #f8fafc;
            overflow-x: hidden;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Sticky Navigation */
        .header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: #1a365d;
            padding: 1rem 0;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            z-index: 1000;
            backdrop-filter: blur(10px);
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
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, #4fc3f7, #29b6f6);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.4rem;
            box-shadow: 0 4px 15px rgba(79, 195, 247, 0.3);
        }

        .logo h1 {
            font-size: 1.8rem;
            font-weight: 700;
            color: #ffffff;
            margin: 0;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }

        /* Navigation with smooth hover effects */
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
            color: #b0c4de;
            text-decoration: none;
            padding: 0.8rem 1.5rem;
            transition: all 0.3s ease;
            font-weight: 500;
            font-size: 0.95rem;
            border-radius: 8px;
            position: relative;
            overflow: hidden;
        }

        .nav-link::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, #4fc3f7, #29b6f6);
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }

        .nav-link:hover {
            color: #4fc3f7;
            background: rgba(79, 195, 247, 0.1);
            transform: translateY(-2px);
        }

        .nav-link:hover::before {
            width: 80%;
        }

        .nav-link.active {
            color: #4fc3f7;
            background: rgba(79, 195, 247, 0.2);
            font-weight: 600;
        }

        .nav-link.active::before {
            width: 80%;
        }

        /* Main content with top padding for sticky nav */
        .main-content {
            padding-top: 80px;
            min-height: 100vh;
        }

        /* Hero Section with gradient and animations */
        .hero-section {
            text-align: center;
            padding: 4rem 0 6rem;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 50%, #3b82f6 100%);
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="1" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }

        .hero-section .container {
            position: relative;
            z-index: 2;
        }

        .hero-icon {
            width: 100px;
            height: 100px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px rgba(0,0,0,0.3);
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        .hero-icon i {
            font-size: 3rem;
            color: #ffffff;
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, #ffffff 0%, #4fc3f7 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1rem;
            line-height: 1.1;
            text-shadow: 0 4px 8px rgba(0,0,0,0.3);
            animation: fadeInUp 1s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .hero-subtitle {
            font-size: 1.2rem;
            color: #e2e8f0;
            margin-bottom: 2rem;
            line-height: 1.6;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
            animation: fadeInUp 1s ease-out 0.3s both;
        }

        /* Enhanced Cards Section */
        .services-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
            margin-top: 1rem;
            position: relative;
            z-index: 3;
            padding: 0 2rem;
        }

        .service-card {
            background: rgba(30, 60, 114, 0.9);
            padding: 2rem 1.5rem;
            border-radius: 20px;
            box-shadow: 0 15px 50px rgba(0,0,0,0.3);
            transition: all 0.4s ease;
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            position: relative;
            overflow: hidden;
        }

        .service-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #4fc3f7, #29b6f6);
        }

        .service-card:hover {
            transform: translateY(-15px) scale(1.02);
            box-shadow: 0 25px 70px rgba(0,0,0,0.4);
            background: rgba(30, 60, 114, 0.95);
        }

        .service-icon {
            width: 70px;
            height: 70px;
            margin: 0 auto 1rem;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: white;
            box-shadow: 0 10px 30px rgba(0,0,0,0.4);
            transition: all 0.3s ease;
        }

        .service-card:hover .service-icon {
            transform: scale(1.1);
        }

        .emergency-icon {
            background: linear-gradient(135deg, #ff6b6b, #ee5a52);
        }

        .treatment-icon {
            background: linear-gradient(135deg, #4fc3f7, #29b6f6);
        }

        .care-icon {
            background: linear-gradient(135deg, #66bb6a, #4caf50);
        }

        .service-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 0.8rem;
            text-align: center;
        }

        .service-description {
            font-size: 0.9rem;
            color: #e2e8f0;
            line-height: 1.5;
            text-align: center;
        }


        /* About Section with enhanced layout */
        .about-section {
            max-width: 100%;
            margin: 0;
            padding: 2rem 0;
            min-height: 100vh;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 50%, #3b82f6 100%);
            position: relative;
            overflow: hidden;
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.6s ease;
        }

        .about-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="1" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }

        .about-section .container {
            position: relative;
            z-index: 2;
        }

        .about-section.animate {
            opacity: 1;
            transform: translateY(0);
        }

        .about-title {
            font-size: 2.5rem;
            font-weight: 800;
            color: #ffffff;
            text-align: center;
            margin-bottom: 1rem;
            text-shadow: 0 4px 8px rgba(0,0,0,0.3);
        }

        .about-subtitle {
            font-size: 1rem;
            color: #e2e8f0;
            text-align: center;
            margin-bottom: 3rem;
            line-height: 1.6;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }

        /* Mission Section */
        .mission-section {
            margin: 3rem 0;
        }

        .mission-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            align-items: center;
        }

        .mission-text h3 {
            font-size: 2rem;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 1.5rem;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }

        .mission-text p {
            font-size: 1rem;
            color: #e2e8f0;
            line-height: 1.7;
            margin-bottom: 1rem;
            text-shadow: 0 1px 2px rgba(0,0,0,0.3);
        }

        .mission-image {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .placeholder-image {
            background: rgba(255, 255, 255, 0.1);
            border: 2px dashed rgba(255, 255, 255, 0.3);
            border-radius: 15px;
            padding: 3rem;
            text-align: center;
            backdrop-filter: blur(10px);
            width: 100%;
            max-width: 300px;
        }

        .placeholder-image i {
            font-size: 4rem;
            color: #ffffff;
            margin-bottom: 1rem;
            opacity: 0.7;
        }

        .placeholder-image p {
            color: #e2e8f0;
            font-size: 1rem;
            margin: 0;
        }

        .mission-photo {
            width: 100%;
            max-width: 400px;
            height: 300px;
            object-fit: cover;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            border: 3px solid rgba(255, 255, 255, 0.2);
        }

        /* Team Section */
        .team-section {
            margin: 4rem 0;
        }

        .team-title {
            font-size: 2.5rem;
            font-weight: 800;
            color: #ffffff;
            text-align: center;
            margin-bottom: 3rem;
            text-shadow: 0 4px 8px rgba(0,0,0,0.3);
        }

        .doctors-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 2rem;
        }

        .doctor-card {
            background: rgba(30, 60, 114, 0.9);
            border-radius: 20px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .doctor-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
            background: rgba(30, 60, 114, 0.95);
        }

        .doctor-image {
            margin-bottom: 1.5rem;
        }

        .doctor-photo {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            object-position: center top;
            margin: 0 auto;
            display: block;
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.3);
            border: 3px solid #ffffff;
        }

        .placeholder-photo {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, #3b82f6, #1e40af);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.3);
        }

        .placeholder-photo i {
            font-size: 3rem;
            color: #ffffff;
        }

        .doctor-info h4 {
            font-size: 1.3rem;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 0.5rem;
        }

        .doctor-title {
            font-size: 1rem;
            color: #60a5fa;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .doctor-description {
            font-size: 0.9rem;
            color: #e2e8f0;
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }

        .linkedin-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: linear-gradient(135deg, #0077b5, #005885);
            color: #ffffff;
            padding: 0.7rem 1.5rem;
            border-radius: 25px;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 119, 181, 0.3);
        }

        .linkedin-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 119, 181, 0.4);
            color: #ffffff;
            text-decoration: none;
        }

        .story-vision-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }

        .story-section, .vision-section {
            background: #ffffff;
            padding: 2.5rem;
            border-radius: 20px;
            box-shadow: 0 15px 50px rgba(0,0,0,0.1);
            text-align: center;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .story-section:hover, .vision-section:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 60px rgba(0,0,0,0.15);
        }

        .story-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #66bb6a, #4caf50);
        }

        .vision-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #4fc3f7, #29b6f6);
        }

        .section-icon {
            width: 60px;
            height: 60px;
            margin: 0 auto 1.5rem;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            color: white;
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        }

        .story-icon {
            background: linear-gradient(135deg, #66bb6a, #4caf50);
        }

        .vision-icon {
            background: linear-gradient(135deg, #4fc3f7, #29b6f6);
        }

        .section-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: #1a365d;
            margin-bottom: 1rem;
        }

        .section-text {
            font-size: 1rem;
            color: #64748b;
            line-height: 1.6;
        }

        /* Services Section with enhanced grid */
        .services-section {
            background: #f8fafc;
            padding: 4rem 0;
        }

        .departments-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .department-card {
            background: #ffffff;
            padding: 2rem;
            border-radius: 20px;
            box-shadow: 0 15px 50px rgba(0,0,0,0.1);
            text-align: center;
            transition: all 0.4s ease;
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            overflow: hidden;
        }

        .department-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #4fc3f7, #29b6f6);
        }

        .department-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 25px 70px rgba(0,0,0,0.2);
        }

        .department-icon {
            width: 60px;
            height: 60px;
            margin: 0 auto 1.5rem;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
            color: white;
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
        }

        .department-card:hover .department-icon {
            transform: scale(1.1);
        }

        .cardiology-icon {
            background: linear-gradient(135deg, #ff6b6b, #ee5a52);
        }

        .neurology-icon {
            background: linear-gradient(135deg, #4fc3f7, #29b6f6);
        }

        .pediatrics-icon {
            background: linear-gradient(135deg, #66bb6a, #4caf50);
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
            font-size: 1.1rem;
            font-weight: 700;
            color: #1a365d;
            margin: 0;
        }

        /* Contact Hero Section */
        .contact-hero {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 50%, #3b82f6 100%);
            padding: 6rem 0 4rem;
            text-align: center;
            position: relative;
            overflow: hidden;
            animation: fadeInUp 1s ease-out;
        }

        .contact-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="1" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }

        .contact-hero .container {
            position: relative;
            z-index: 2;
        }

        .contact-hero-title {
            font-size: 3.5rem;
            font-weight: 800;
            color: #ffffff;
            margin-bottom: 1rem;
            text-shadow: 0 4px 8px rgba(0,0,0,0.3);
        }

        .contact-hero-title .highlight {
            color: #60a5fa;
        }

        .contact-hero-subtitle {
            font-size: 1.2rem;
            color: #e2e8f0;
            max-width: 600px;
            margin: 0 auto;
            line-height: 1.6;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }

        /* Contact Cards Section */
        .contact-cards-section {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 50%, #3b82f6 100%);
            padding: 4rem 0;
            animation: fadeInUp 1s ease-out 0.3s both;
        }

        .info-cards-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 2rem;
            margin-top: 2rem;
        }

        .info-card {
            background: rgba(30, 60, 114, 0.9);
            padding: 2rem 1.5rem;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .info-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
            background: rgba(30, 60, 114, 0.95);
        }

        .card-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2rem;
            color: #ffffff;
        }

        .card-icon.email {
            background: linear-gradient(135deg, #ec4899, #be185d);
        }

        .card-icon.phone {
            background: linear-gradient(135deg, #10b981, #059669);
        }

        .card-icon.address {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
        }

        .card-icon.support {
            background: linear-gradient(135deg, #f59e0b, #d97706);
        }

        .info-card h3 {
            font-size: 1.3rem;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 0.5rem;
        }

        .card-detail {
            font-size: 1rem;
            color: #60a5fa;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .card-description {
            font-size: 0.9rem;
            color: #e2e8f0;
            line-height: 1.5;
        }

        /* Contact Form and Map Section */
        .contact-form-map-section {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 50%, #3b82f6 100%);
            padding: 1.5rem 0;
            animation: fadeInUp 1s ease-out 0.6s both;
        }

        .form-map-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            align-items: stretch;
        }

        .message-form-card {
            background: rgba(30, 60, 114, 0.9);
            padding: 2rem;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .map-card {
            background: transparent;
            padding: 0;
            border-radius: 0;
            box-shadow: none;
            border: none;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .message-form-card h3, .map-card h3 {
            font-size: 1.8rem;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 2rem;
            text-align: center;
        }

        .find-us-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 1.5rem;
            text-align: center;
            padding: 0 1rem;
        }

        .contact-form {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .contact-form .form-group {
            margin-bottom: 1.5rem;
        }

        .contact-form label {
            display: block;
            font-weight: 600;
            color: #ffffff;
            margin-bottom: 0.5rem;
        }

        .contact-form input,
        .contact-form select,
        .contact-form textarea {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.1);
            color: #000000;
        }

        .contact-form input::placeholder,
        .contact-form textarea::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        .contact-form input:focus,
        .contact-form select:focus,
        .contact-form textarea:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            color: #000000;
            background: rgba(255, 255, 255, 0.2);
        }

        .char-counter {
            text-align: right;
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.7);
            margin-top: 0.5rem;
        }

        .send-message-btn {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: #ffffff;
            padding: 1rem 2rem;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin: 0 auto;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
        }

        .send-message-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4);
        }


        .view-larger-map {
            display: block;
            text-align: center;
            margin-top: 1rem;
            color: #60a5fa;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .view-larger-map:hover {
            color: #93c5fd;
            text-decoration: none;
        }

        /* Inline FAQ Section */
        .faq-section-inline {
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            flex-shrink: 0;
        }

        .faq-title-inline {
            font-size: 1.3rem;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .faq-list-inline {
            display: flex;
            flex-direction: column;
            gap: 0.8rem;
        }

        .faq-item-inline {
            padding: 0.8rem;
            background: rgba(0, 0, 0, 0.2);
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .faq-question-inline {
            font-size: 0.9rem;
            font-weight: 600;
            color: #60a5fa;
            margin-bottom: 0.5rem;
        }

        .faq-answer-inline {
            color: #e2e8f0;
            line-height: 1.5;
            font-size: 0.8rem;
            margin: 0;
        }

        /* FAQ Section */
        .faq-section {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 50%, #3b82f6 100%);
            padding: 4rem 0;
            animation: fadeInUp 1s ease-out 0.9s both;
        }

        .faq-title {
            font-size: 2.5rem;
            font-weight: 800;
            color: #ffffff;
            text-align: center;
            margin-bottom: 3rem;
            text-shadow: 0 4px 8px rgba(0,0,0,0.3);
        }

        .faq-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
        }

        .faq-item {
            background: rgba(30, 60, 114, 0.9);
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .faq-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.4);
            background: rgba(30, 60, 114, 0.95);
        }

        .faq-item h4 {
            font-size: 1.2rem;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 1rem;
        }

        .faq-item p {
            color: #e2e8f0;
            line-height: 1.6;
        }

        /* Social Media Section */
        .social-section {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 50%, #3b82f6 100%);
            padding: 3rem 0;
            text-align: center;
            animation: fadeInUp 1s ease-out 1.2s both;
        }

        .social-title {
            font-size: 2rem;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 2rem;
        }

        .social-icons {
            display: flex;
            justify-content: center;
            gap: 1.5rem;
        }

        .social-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: #ffffff;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .social-icon.facebook {
            background: linear-gradient(135deg, #1877f2, #0d47a1);
        }

        .social-icon.twitter {
            background: linear-gradient(135deg, #1da1f2, #0d47a1);
        }

        .social-icon.linkedin {
            background: linear-gradient(135deg, #0077b5, #004182);
        }

        .social-icon.instagram {
            background: linear-gradient(135deg, #e4405f, #c13584);
        }

        .social-icon.youtube {
            background: linear-gradient(135deg, #ff0000, #cc0000);
        }

        .social-icon:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        }

        .contact-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="1" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }

        .contact-section .container {
            position: relative;
            z-index: 2;
        }

        .contact-title {
            font-size: 2.5rem;
            font-weight: 800;
            color: #ffffff;
            text-align: center;
            margin-bottom: 1rem;
            text-shadow: 0 4px 8px rgba(0,0,0,0.3);
        }

        .contact-subtitle {
            font-size: 1rem;
            color: #e2e8f0;
            text-align: center;
            margin-bottom: 2rem;
            line-height: 1.6;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }

        .contact-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }

        .contact-info {
            background: #ffffff;
            padding: 2.5rem;
            border-radius: 20px;
            box-shadow: 0 15px 50px rgba(0,0,0,0.1);
        }

        .contact-info h2 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1a365d;
            margin-bottom: 2rem;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .contact-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: white;
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        }

        .contact-icon.address {
            background: linear-gradient(135deg, #4fc3f7, #29b6f6);
        }

        .contact-icon.phone {
            background: linear-gradient(135deg, #66bb6a, #4caf50);
        }

        .contact-icon.email {
            background: linear-gradient(135deg, #fd79a8, #e84393);
        }

        .contact-icon.emergency {
            background: linear-gradient(135deg, #ff6b6b, #ee5a52);
        }

        .contact-details h4 {
            font-size: 1rem;
            font-weight: 600;
            color: #1a365d;
            margin-bottom: 0.5rem;
        }

        .contact-details p {
            font-size: 0.9rem;
            color: #64748b;
            margin: 0;
            line-height: 1.4;
        }

        .contact-form {
            background: #ffffff;
            padding: 2.5rem;
            border-radius: 20px;
            box-shadow: 0 15px 50px rgba(0,0,0,0.1);
        }

        .contact-form h2 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1a365d;
            margin-bottom: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            font-size: 0.9rem;
            font-weight: 600;
            color: #1a365d;
            margin-bottom: 0.5rem;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 1rem;
            font-family: inherit;
            transition: all 0.3s ease;
            background: #f8fafc;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #4fc3f7;
            background: #ffffff;
            box-shadow: 0 0 0 3px rgba(79, 195, 247, 0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }

        .btn-submit {
            background: linear-gradient(135deg, #4fc3f7, #29b6f6);
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            box-shadow: 0 5px 15px rgba(79, 195, 247, 0.3);
            position: relative;
            overflow: hidden;
        }

        .btn-submit::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(79, 195, 247, 0.4);
        }

        .btn-submit:hover::before {
            left: 100%;
        }

        .emergency-section {
            background: linear-gradient(135deg, #ff6b6b, #ee5a52);
            color: white;
            padding: 2rem;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 15px 50px rgba(255, 107, 107, 0.3);
        }

        .emergency-section h2 {
            font-size: 1.6rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .emergency-section p {
            font-size: 1rem;
            margin-bottom: 1rem;
            opacity: 0.9;
        }

        .emergency-number {
            font-size: 2rem;
            font-weight: 800;
            margin: 1rem 0;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }

        .emergency-note {
            font-size: 0.9rem;
            margin-top: 1rem;
            opacity: 0.8;
        }

        /* Google Maps placeholder */
        .map-container {
            background: #f8fafc;
            border: 2px dashed #cbd5e0;
            border-radius: 20px;
            padding: 3rem;
            text-align: center;
            margin-top: 2rem;
        }

        .map-placeholder {
            color: #64748b;
            font-size: 1.1rem;
        }

        /* Enhanced Login Page with glassmorphism */
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 50%, #3b82f6 100%);
            position: relative;
            overflow: hidden;
        }

        .login-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="1" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            padding: 3rem;
            border-radius: 25px;
            box-shadow: 0 25px 80px rgba(0,0,0,0.3);
            border: 1px solid rgba(255, 255, 255, 0.2);
            max-width: 450px;
            width: 100%;
            margin: 2rem;
            position: relative;
            z-index: 2;
        }

        .login-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 35px 100px rgba(0,0,0,0.4);
        }

        .login-form {
            width: 100%;
        }

        .login-form .form-group {
            margin-bottom: 2rem;
        }

        .login-form .form-group label {
            display: block;
            font-size: 1rem;
            font-weight: 600;
            color: #ffffff;
            margin-bottom: 0.8rem;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }

        .login-form .form-group input {
            width: 100%;
            padding: 1.2rem;
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 15px;
            font-size: 1rem;
            font-family: inherit;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.1);
            color: #ffffff;
            backdrop-filter: blur(10px);
        }

        .login-form .form-group input::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        .login-form .form-group input:focus {
            outline: none;
            border-color: #4fc3f7;
            background: rgba(255, 255, 255, 0.2);
            box-shadow: 0 0 0 3px rgba(79, 195, 247, 0.2);
        }

        .btn-login {
            width: 100%;
            background: linear-gradient(135deg, #4fc3f7, #29b6f6);
            color: white;
            padding: 1.2rem;
            border: none;
            border-radius: 15px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(79, 195, 247, 0.4);
            position: relative;
            overflow: hidden;
        }

        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(79, 195, 247, 0.5);
        }

        .btn-login:hover::before {
            left: 100%;
        }

        .login-footer {
            text-align: center;
        }

        .forgot-password {
            color: #4fc3f7;
            text-decoration: none;
            font-size: 1rem;
            font-weight: 500;
            transition: all 0.3s ease;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }

        .forgot-password:hover {
            color: #29b6f6;
            text-decoration: underline;
        }

        .alert {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 12px;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .alert-danger {
            background: rgba(255, 107, 107, 0.2);
            color: #ffffff;
            border: 1px solid rgba(255, 107, 107, 0.3);
            backdrop-filter: blur(10px);
        }

        .alert-success {
            background: rgba(102, 187, 106, 0.2);
            color: #ffffff;
            border: 1px solid rgba(102, 187, 106, 0.3);
            backdrop-filter: blur(10px);
        }


        /* Responsive Design */
        @media (max-width: 1024px) {
            .doctors-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 1.5rem;
            }

            .mission-content {
                grid-template-columns: 1fr;
                gap: 2rem;
            text-align: center;
            }
        }

        @media (max-width: 1024px) {
            .info-cards-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 1.5rem;
            }

            .form-map-grid {
                grid-template-columns: 1fr;
                gap: 2rem;
            }

            .faq-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 1.5rem;
            }
        }

        @media (max-width: 768px) {
            .contact-hero-title {
                font-size: 2.5rem;
            }

            .contact-hero-subtitle {
                font-size: 1rem;
            }

            .info-cards-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .info-card {
                padding: 1.5rem;
            }

            .card-icon {
                width: 60px;
                height: 60px;
                font-size: 1.5rem;
            }

            .form-map-grid {
                grid-template-columns: 1fr;
                gap: 2rem;
            }

            .message-form-card, .map-card {
                padding: 2rem;
            }

            .faq-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .faq-title {
                font-size: 2rem;
            }

            .social-icons {
                flex-wrap: wrap;
                gap: 1rem;
            }

            .social-icon {
                width: 50px;
                height: 50px;
                font-size: 1.2rem;
            }

            .mission-content {
                grid-template-columns: 1fr;
                gap: 2rem;
                text-align: center;
            }

            .mission-text h3 {
                font-size: 1.5rem;
            }

            .doctors-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .doctor-card {
                padding: 1.5rem;
            }

            .placeholder-photo {
                width: 100px;
                height: 100px;
            }

            .placeholder-photo i {
                font-size: 2.5rem;
            }
        }

        @media (max-width: 768px) {
            .header .container {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .navbar-nav {
                flex-direction: row;
                gap: 0.5rem;
                flex-wrap: wrap;
                justify-content: center;
            }

            .nav-link {
                padding: 0.6rem 1rem;
                font-size: 0.9rem;
            }

            .hero-section {
                padding: 4rem 0 6rem;
                min-height: 80vh;
            }

            .hero-icon {
                width: 80px;
                height: 80px;
                margin-bottom: 1.5rem;
            }

            .hero-icon i {
                font-size: 2.5rem;
            }

            .hero-title {
                font-size: 2.8rem;
            }

            .hero-subtitle {
                font-size: 1.1rem;
                margin-bottom: 2rem;
            }

            .services-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
                margin-top: -2rem;
                padding: 0 1rem;
            }

            .service-card {
                padding: 2rem;
            }

            .story-vision-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .contact-content {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .about-section, .contact-section {
                height: calc(100vh - 120px);
                padding: 1rem;
            }


            .about-title, .contact-title {
                font-size: 2rem;
            }

            .about-subtitle, .contact-subtitle {
                font-size: 0.9rem;
            }
        }

        @media (max-width: 480px) {
            .logo h1 {
                font-size: 1.5rem;
            }

            .logo-icon {
                width: 35px;
                height: 35px;
                font-size: 1.2rem;
            }

            .hero-title {
                font-size: 2.2rem;
            }

            .hero-subtitle {
                font-size: 1rem;
            }

            .service-card, .department-card {
                padding: 1.5rem;
            }

            .service-icon {
                width: 60px;
                height: 60px;
                font-size: 1.8rem;
            }

            .service-title {
                font-size: 1.2rem;
            }

            .service-description {
                font-size: 0.9rem;
            }

            .contact-info, .contact-form {
                padding: 2rem;
            }

            .about-title, .contact-title {
                font-size: 1.6rem;
            }

            .about-subtitle, .contact-subtitle {
                font-size: 0.85rem;
            }

            .story-section, .vision-section {
                padding: 1.5rem;
            }

            .contact-info, .contact-form {
                padding: 1.5rem;
            }

            .login-card {
                margin: 1rem;
                padding: 2rem;
            }
        }

        /* Scroll animations */
        .fade-in {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.6s ease;
        }

        .fade-in.animate {
            opacity: 1;
            transform: translateY(0);
        }

        /* Smooth scrolling */
        html {
            scroll-behavior: smooth;
        }
    </style>
</head>
<body>
<?php 
    $roleFromSession = session()->get('role');
    $segment = strtolower((string)($roleFromSession ?: service('uri')->getSegment(1)));
    $currentPath = strtolower(service('uri')->getPath());
    
    // Show sidebar for admin pages, not on login/logout/auth pages
    $validRoles = ['admin','doctor','nurse','receptionist','lab','pharmacist','accountant','it'];
    $isAdminPage = strpos($currentPath, 'admin') !== false || strpos($currentPath, 'patients') !== false || strpos($currentPath, 'appointments') !== false || strpos($currentPath, 'billing') !== false || strpos($currentPath, 'laboratory') !== false || strpos($currentPath, 'pharmacy') !== false || strpos($currentPath, 'reports') !== false || strpos($currentPath, 'users') !== false || strpos($currentPath, 'settings') !== false;
    $isNursePage = strpos($currentPath, 'nurse') !== false;
    $isDoctorPage = strpos($currentPath, 'doctor') !== false;
    $isReceptionPage = strpos($currentPath, 'reception') !== false;
    $isLabPage = strpos($currentPath, 'lab') !== false;
    $isPharmacyPage = strpos($currentPath, 'pharmacy') !== false;
    $isAccountsPage = strpos($currentPath, 'accounts') !== false;
    $isItPage = strpos($currentPath, 'it') !== false;
    $isDashboardPage = strpos($currentPath, 'dashboard') !== false;
    $isAuthPage = strpos($currentPath, 'login') !== false || strpos($currentPath, 'logout') !== false || strpos($currentPath, 'auth') !== false;
    
    $useSidebar = in_array($segment, $validRoles, true) && ($isDashboardPage || $isAdminPage || $isNursePage || $isDoctorPage || $isReceptionPage || $isLabPage || $isPharmacyPage || $isAccountsPage || $isItPage) && !$isAuthPage;
?>

<?php if ($useSidebar): ?>
    <style>
        /* Admin Dashboard Layout */
        .layout {
            display: flex;
            gap: 1rem;
        }

        .sidebar {
            width: 240px;
            background: #0f2747;
            color: #eaf2ff;
            border-radius: 12px;
            padding: 1rem;
            position: sticky;
            top: 0;
            height: 100vh;
        }

        .brand {
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .menu {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .menu a {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            color: #c9d7ee;
            padding: 0.65rem 0.75rem;
            border-radius: 8px;
        }

        .menu a:hover,
        .menu a.active {
            background: #16345d;
            color: #fff;
        }

        .content {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 1rem;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }

        /* Dashboard Components */
        .page-grid {
            display: grid;
            grid-template-columns: 2fr 1.3fr;
            gap: 1rem;
        }

        .panel {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .panel-header {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid #eef2f7;
        }

        .panel-header h2 {
            margin: 0;
            font-size: 1rem;
        }

        .panel-header p {
            margin: 0.25rem 0 0;
            color: #64748b;
            font-size: 0.9rem;
        }

        .stack {
            display: grid;
            gap: 0.75rem;
            padding: 1rem;
        }

        .card {
            padding: 1rem;
            border: 1px solid #eef2f7;
            border-radius: 10px;
            background: #f8fafc;
        }

        .row {
            display: flex;
            align-items: center;
        }

        .row.between {
            justify-content: space-between;
        }

        /* Badges */
        .badge {
            font-size: 0.75rem;
            padding: 0.2rem 0.5rem;
            border-radius: 999px;
            text-transform: capitalize;
        }

        .badge.high {
            background: #fee2e2;
            color: #b91c1c;
        }

        .badge.medium {
            background: #fef3c7;
            color: #a16207;
        }

        .badge.low {
            background: #dcfce7;
            color: #166534;
        }

        /* Action Grid */
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            padding: 1rem;
        }

        .action-tile {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
            background: #f8fafc;
            border: 1px solid #eef2f7;
            border-radius: 12px;
            padding: 1rem;
            text-decoration: none;
            color: #1a365d;
        }

        /* Lists */
        .list {
            list-style: none;
            margin: 0;
            padding: 1rem;
            display: grid;
            gap: 0.5rem;
        }

        .list-item {
            padding: 0.75rem;
            border: 1px solid #eef2f7;
            border-radius: 10px;
            background: #fff;
        }

        .list-item.info .dot {
            background: #60a5fa;
        }

        .list-item.success .dot {
            background: #22c55e;
        }

        .list-item.warn .dot {
            background: #f59e0b;
        }

        .list-item .dot {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 999px;
            margin-right: 0.5rem;
        }

        /* Status List */
        .status-list {
            padding: 1rem;
        }

        .status-row {
            display: flex;
            justify-content: space-between;
            padding: 0.6rem 0;
            border-bottom: 1px dashed #eef2f7;
        }

        .status-row:last-child {
            border-bottom: 0;
        }

        /* KPI Cards */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
        }

        .kpi-card {
            padding: 1.5rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .kpi-content {
            text-align: center;
        }

        .kpi-label {
            font-size: 0.9rem;
            color: #64748b;
            margin-bottom: 0.5rem;
        }

        .kpi-value {
            font-size: 2rem;
            font-weight: bold;
            color: #1e293b;
            margin-bottom: 0.25rem;
        }

        .kpi-change {
            font-size: 0.8rem;
        }

        .kpi-positive {
            color: #22c55e;
        }

        .kpi-negative {
            color: #ef4444;
        }

        .kpi-warning {
            color: #f59e0b;
        }

        /* Panel Spacing */
        .panel-spaced {
            margin-top: 1rem;
        }

        /* Modal (global styles) */
        .modal{position:fixed;inset:0;z-index:1000;display:flex;align-items:center;justify-content:center;padding:20px;background:rgba(0,0,0,.35);backdrop-filter:blur(2px)}
        .modal-backdrop{position:absolute;inset:0;background:rgba(0,0,0,.35);z-index:1}
        .modal-dialog{position:relative;z-index:2;margin:0 auto;background:#fff;border-radius:8px;max-width:860px;width:100%;max-height:90vh;overflow-y:auto;box-shadow:0 10px 30px rgba(0,0,0,.2)}
        .modal-header{padding:20px;border-bottom:1px solid var(--border,#e5e7eb);display:flex;align-items:center;justify-content:space-between}
        .modal-body{padding:20px}
        .modal-footer{display:flex;justify-content:flex-end;gap:12px;margin-top:16px}
        .modal-close{border:none;background:transparent;font-size:28px;cursor:pointer;line-height:1;color:#6b7280;padding:0;width:32px;height:32px;display:flex;align-items:center;justify-content:center;border-radius:4px;transition:all 0.2s}
        .modal-close:hover{background:#f3f4f6;color:#111827}
        .icon-button{border:none;background:transparent;font-size:22px;cursor:pointer;line-height:1}
        .form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}
        .form-field label{display:block;margin-bottom:6px;font-weight:600}
        .form-field input,.form-field select,.form-field textarea{width:100%;padding:10px 12px;border:1px solid var(--border,#e5e7eb);border-radius:6px;background:#fff}
        .form-field--full{grid-column:1/-1}

        /* Search Input */
        .search-input {
            padding: 0.5rem;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            width: 280px;
        }

        /* Primary Button */
        .btn-primary {
            padding: 0.6rem 1rem;
            background: #3b82f6;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            display: inline-block;
        }

        /* Table Styles */
        .table-header {
            background: #f8fafc;
            font-weight: 600;
            border-bottom: 2px solid #e2e8f0;
            margin-bottom: 0;
        }

        .table-row {
            margin-bottom: 0.5rem;
        }

        .table-row:last-child {
            margin-bottom: 0;
        }

        /* Table Columns */
        .col-id {
            flex: 1;
        }

        .col-name {
            flex: 2;
        }

        .col-age {
            flex: 1.5;
        }

        .col-contact {
            flex: 2;
        }

        .col-status {
            flex: 1;
        }

        .col-doctor {
            flex: 1.5;
        }

        .col-visit {
            flex: 1;
        }

        .col-actions {
            flex: 1;
        }

        .col-datetime {
            flex: 2;
        }

        .col-type {
            flex: 1.5;
        }

        .appointment-id {
            font-weight: 600;
        }

        .col-amount {
            flex: 1.5;
        }

        .col-date {
            flex: 1.5;
        }

        .col-payment {
            flex: 1.5;
        }

        .invoice-id {
            font-weight: 600;
        }

        /* Secondary Button */
        .btn-secondary {
            padding: 0.5rem 1rem;
            background: #f1f5f9;
            color: #475569;
            text-decoration: none;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            margin-left: 0.5rem;
        }


        /* Patient Data */
        .patient-id {
            font-weight: 600;
        }

        .blood-type {
            margin: 0;
            color: #64748b;
            font-size: 0.9rem;
        }

        .phone {
            margin: 0;
        }

        .email {
            margin: 0;
            color: #64748b;
            font-size: 0.9rem;
        }

        /* Badge Variants */
        .badge-gray {
            background: #6b7280;
            color: white;
        }

        .badge-blue {
            background: #3b82f6;
            color: white;
        }

        /* Action Links */
        .action-link {
            margin-right: 0.5rem;
            text-decoration: none;
            color: #3b82f6;
        }

        .action-delete {
            color: #ef4444;
        }

        .dot.ok {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: #22c55e;
            margin-right: 0.4rem;
        }

        /* Patient Info Layout */
        .patient-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .patient-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e0f2fe;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .patient-details {
            flex: 1;
        }

        .patient-details strong {
            display: block;
            margin-bottom: 0.25rem;
        }

        /* Age/Gender styling */
        .col-age div:first-child {
            font-weight: 500;
            color: #374151;
        }

        .col-age div:last-child {
            color: #6b7280;
            font-size: 0.9rem;
        }

        /* Badge Styles */
        .badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }

        .badge-green {
            background: #dcfce7;
            color: #166534;
        }

        .badge-red {
            background: #fecaca;
            color: #991b1b;
        }

        .badge-blue {
            background: #dbeafe;
            color: #1e40af;
        }

        .badge-gray {
            background: #f3f4f6;
            color: #374151;
        }

        /* Action Icons */
        .col-actions {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        .action-link svg {
            width: 16px;
            height: 16px;
        }

        .action-link:hover {
            opacity: 0.7;
        }

        /* Form Helper Text */
        .form-help {
            display: block;
            margin-top: 0.25rem;
            font-size: 0.75rem;
            color: #6b7280;
            font-style: italic;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .page-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Patient View Modal Styles */
        .patient-view-container {
            padding: 1rem;
        }

        .patient-view-header {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 2px solid #e2e8f0;
            margin-bottom: 1.5rem;
        }

        .patient-avatar-large {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: #e0f2fe;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .patient-view-title h3 {
            margin: 0 0 0.5rem 0;
            font-size: 1.5rem;
            color: #1e293b;
        }

        .patient-id-display {
            margin: 0;
            color: #64748b;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .patient-view-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .patient-view-section {
            background: #f8fafc;
            padding: 1.25rem;
            border-radius: 0.5rem;
            border: 1px solid #e2e8f0;
        }

        .patient-view-section h4 {
            margin: 0 0 1rem 0;
            font-size: 1rem;
            font-weight: 600;
            color: #1e293b;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #cbd5e1;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 0.75rem 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: #475569;
            min-width: 140px;
            font-size: 0.875rem;
        }

        .info-value {
            color: #1e293b;
            text-align: right;
            flex: 1;
            font-size: 0.875rem;
        }

        .modal-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
        }

        .btn-secondary {
            padding: 0.625rem 1.25rem;
            background: #e2e8f0;
            color: #475569;
            border: none;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-secondary:hover {
            background: #cbd5e1;
        }

        /* Patient View Modal scrollbar styling */
        #patientViewModal .modal-body {
            max-height: 70vh;
            overflow-y: auto;
            padding: 1.5rem;
        }

        #patientViewModal .modal-body::-webkit-scrollbar {
            width: 8px;
        }

        #patientViewModal .modal-body::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 4px;
        }

        #patientViewModal .modal-body::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        #patientViewModal .modal-body::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Vital Signs Display */
        .patient-view-section-full {
            grid-column: 1 / -1;
        }

        .vitals-display {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 0.5rem;
        }

        .vital-item-display {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
            padding: 0.75rem;
            background: white;
            border-radius: 0.375rem;
            border: 1px solid #e2e8f0;
        }

        .vital-label {
            font-size: 0.75rem;
            color: #64748b;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .vital-value {
            font-size: 1rem;
            color: #1e293b;
            font-weight: 600;
        }

        .vitals-history-table {
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            overflow: hidden;
        }

        .vitals-history-header {
            display: grid;
            grid-template-columns: 120px 1fr 1fr 1fr 1fr 150px;
            gap: 0.5rem;
            padding: 0.75rem;
            background: #f8fafc;
            font-weight: 600;
            font-size: 0.8rem;
            color: #475569;
            border-bottom: 2px solid #e2e8f0;
        }

        .vitals-history-row {
            display: grid;
            grid-template-columns: 120px 1fr 1fr 1fr 1fr 150px;
            gap: 0.5rem;
            padding: 0.75rem;
            border-bottom: 1px solid #e2e8f0;
            font-size: 0.875rem;
            color: #1e293b;
        }

        .vitals-history-row:last-child {
            border-bottom: none;
        }

        .vitals-history-row:hover {
            background: #f8fafc;
        }

        /* Prescriptions Table Styles */
        .prescriptions-table-wrapper {
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            overflow: hidden;
            margin-top: 1rem;
        }

        .prescriptions-table {
            width: 100%;
            border-collapse: collapse;
            background: #ffffff;
        }

        .prescriptions-table thead {
            background: #f8fafc;
        }

        .prescriptions-table th {
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            font-size: 0.875rem;
            color: #475569;
            border-bottom: 2px solid #e2e8f0;
        }

        .prescriptions-table td {
            padding: 0.875rem 1rem;
            border-bottom: 1px solid #e2e8f0;
            font-size: 0.875rem;
            color: #1e293b;
        }

        .prescriptions-table tbody tr:last-child td {
            border-bottom: none;
        }

        .prescriptions-table tbody tr:hover {
            background: #f8fafc;
        }

        /* Status Badges */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.25rem 0.75rem;
            border-radius: 999px;
            font-size: 0.8125rem;
            font-weight: 500;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-pending .status-dot {
            background: #f59e0b;
        }

        .status-completed {
            background: #d1fae5;
            color: #065f46;
        }

        .status-completed .status-dot {
            background: #10b981;
        }

        /* Mark as Given Button */
        .btn-mark-given {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: #3B82F6;
            color: #ffffff;
            border: none;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-mark-given:hover {
            background: #2563eb;
        }

        .btn-mark-given:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* Info Note */
        .info-note {
            margin-top: 1.5rem;
            padding: 1rem;
            background: #f0f9ff;
            border-left: 4px solid #3b82f6;
            border-radius: 0.5rem;
        }

        .info-note p {
            margin: 0 0 0.5rem 0;
            color: #1e293b;
            font-weight: 500;
        }

        .info-note ul {
            margin: 0.5rem 0 0 0;
            padding-left: 1.5rem;
            color: #475569;
        }

        .info-note li {
            margin-bottom: 0.25rem;
        }

        /* Wizard/Progress Line Styles */
        .wizard-progress {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            padding: 0.5rem 0;
        }

        .wizard-progress-inline {
            margin-top: 0.5rem;
        }

        .wizard-steps {
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .wizard-step {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 600;
            background: #eef2f7;
            color: #94a3b8;
            border: 2px solid #cbd5e1;
            position: relative;
            z-index: 2;
            transition: all 0.3s ease;
        }

        .wizard-step.completed {
            background: linear-gradient(135deg, #10b981, #059669);
            color: #ffffff;
            border-color: #10b981;
        }

        .wizard-step.active {
            background: linear-gradient(135deg, #3B82F6, #2563eb);
            color: #ffffff;
            border-color: #3B82F6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.2);
            transform: scale(1.1);
        }

        .wizard-line {
            flex: 1;
            height: 3px;
            background: #eef2f7;
            border-radius: 2px;
            position: relative;
            z-index: 1;
            transition: all 0.3s ease;
        }

        .wizard-line.completed {
            background: linear-gradient(90deg, #10b981, #059669);
        }

        .wizard-text {
            font-size: 0.8125rem;
            color: #475569;
            font-weight: 500;
            text-align: center;
        }

        .wizard-progress-inline .wizard-text {
            margin-top: 0.25rem;
            color: #3B82F6;
            font-weight: 600;
        }

        /* Responsive for Prescriptions */
        @media (max-width: 1024px) {
            .prescriptions-table {
                font-size: 0.8125rem;
            }
            
            .prescriptions-table th,
            .prescriptions-table td {
                padding: 0.75rem 0.5rem;
            }
            
            .btn-mark-given {
                padding: 0.4rem 0.75rem;
                font-size: 0.8125rem;
            }

            .wizard-step {
                width: 24px;
                height: 24px;
                font-size: 0.7rem;
            }
        }

        /* Treatment Updates - Clean Modern Design - Matches System Color Scheme */
        .treatment-card {
            margin-bottom: 1.5rem;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            border-left: 4px solid #3B82F6;
            overflow: hidden;
            transition: box-shadow 0.2s;
        }

        .treatment-card:hover {
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
        }

        .treatment-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 1.5rem;
            border-bottom: 1px solid #eef2f7;
            gap: 2rem;
            background: #f8fafc;
        }

        .patient-summary {
            display: flex;
            gap: 1rem;
            flex: 1;
            align-items: flex-start;
        }

        .patient-avatar-large {
            flex-shrink: 0;
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #3B82F6, #2563eb);
            border-radius: 10px;
        }

        .patient-avatar-large svg {
            filter: brightness(0) invert(1);
        }

        .patient-main-info h3 {
            margin: 0 0 0.5rem 0;
            color: #1a365d;
            font-size: 1.25rem;
            font-weight: 600;
        }

        .patient-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            color: #64748b;
            font-size: 0.875rem;
        }

        .meta-item strong {
            color: #475569;
            font-weight: 500;
        }

        .patient-status-info {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            min-width: 200px;
        }

        .info-row {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            color: #1a365d;
        }

        .info-row strong {
            color: #475569;
            min-width: 60px;
            font-weight: 500;
        }

        .room-badge {
            background: linear-gradient(135deg, #dbeafe, #bfdbfe);
            color: #1e40af;
            padding: 0.25rem 0.75rem;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.8125rem;
            border: 1px solid #93c5fd;
        }

        .treatment-body {
            padding: 1.5rem;
            background: #ffffff;
        }

        .treatment-section {
            margin-bottom: 2rem;
        }

        .treatment-section:last-child {
            margin-bottom: 0;
        }

        .treatment-section h4 {
            margin: 0 0 1rem 0;
            color: #1a365d;
            font-size: 1rem;
            font-weight: 600;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #eef2f7;
        }

        .vital-signs-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1rem;
        }

        .vital-item {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .vital-item label {
            color: #475569;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .vital-input {
            padding: 0.625rem 0.75rem;
            border: 1px solid #eef2f7;
            border-radius: 8px;
            font-size: 0.875rem;
            transition: all 0.2s;
            background: #ffffff;
            color: #1a365d;
        }

        .vital-input:focus {
            outline: none;
            border-color: #3B82F6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            background: #ffffff;
        }

        .vital-input::placeholder {
            color: #94a3b8;
        }

        .btn-small {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
        }

        .time-output {
            margin-top: 0.25rem;
            color: #475569;
            font-size: 0.85rem;
        }

        .time-history {
            margin-top: 0.5rem;
        }

        .time-history .time-row {
            color: #475569;
            font-size: 0.85rem;
            padding: 0.25rem 0;
        }

        .vital-actions-right {
            display: flex;
            align-items: flex-end;
        }

        /* Vital history grid */
        .vh-grid {
            display: grid;
            grid-template-columns: 200px 1fr 1fr 1fr 1fr 150px;
            gap: 0.75rem;
            align-items: center;
            padding: 0.75rem 1rem;
        }

        .vh-header {
            font-weight: 600;
            color: #1a365d;
            background: #f8fafc;
            border-radius: 8px;
        }

        .vh-grid-list {
            margin-top: 0.5rem;
        }

        .vh-grid-list .vh-row {
            display: grid;
            grid-template-columns: 200px 1fr 1fr 1fr 1fr 150px;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            border: 1px solid #eef2f7;
            border-radius: 8px;
            margin-bottom: 0.5rem;
            background: #ffffff;
            transition: all 0.2s;
        }

        .vh-grid-list .vh-row:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
        }

        .treatment-textarea {
            width: 100%;
            min-height: 100px;
            padding: 0.75rem;
            border: 1px solid #eef2f7;
            border-radius: 8px;
            font-size: 0.875rem;
            font-family: inherit;
            resize: vertical;
            transition: all 0.2s;
            background: #ffffff;
            color: #1a365d;
        }

        .treatment-textarea:focus {
            outline: none;
            border-color: #3B82F6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .treatment-textarea::placeholder {
            color: #94a3b8;
        }

        .treatment-actions {
            display: flex;
            gap: 0.75rem;
            padding-top: 1rem;
            border-top: 1px solid #eef2f7;
            margin-top: 1rem;
        }

        .search-input {
            padding: 0.625rem 0.75rem;
            border: 1px solid #eef2f7;
            border-radius: 8px;
            font-size: 0.875rem;
            width: 100%;
            max-width: 400px;
            transition: all 0.2s;
            background: #ffffff;
            color: #1a365d;
        }

        .search-input:focus {
            outline: none;
            border-color: #3B82F6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .search-input::placeholder {
            color: #94a3b8;
        }

        /* Responsive for Treatment Updates */
        @media (max-width: 768px) {
            .treatment-header {
                flex-direction: column;
                gap: 1rem;
            }
            
            .patient-status-info {
                width: 100%;
            }
            
            .vital-signs-grid {
                grid-template-columns: 1fr;
            }

            .vh-grid,
            .vh-grid-list .vh-row {
                grid-template-columns: 1fr;
                gap: 0.5rem;
            }

            .vh-grid > div,
            .vh-grid-list .vh-row > div {
                padding: 0.25rem 0;
            }
        }

        /* Basic Appointments Table Styles */
        .table-container {
            overflow-x: auto;
            margin-top: 1rem;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            background: #ffffff;
        }

        .data-table th,
        .data-table td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        .data-table th {
            background-color: #f8fafc;
            font-weight: 600;
            color: #475569;
            font-size: 0.875rem;
        }

        .data-table td {
            color: #1e293b;
            font-size: 0.875rem;
        }

        .data-table tbody tr:hover {
            background-color: #f8fafc;
        }

        .btn-xs {
            padding: 0.375rem 0.75rem;
            font-size: 0.8125rem;
            border-radius: 0.375rem;
            border: none;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.2s;
        }

        .btn-xs.btn-primary {
            background: #3B82F6;
            color: #ffffff;
        }

        .btn-xs.btn-primary:hover {
            background: #2563eb;
        }

        .btn-xs.btn-success {
            background: #10b981;
            color: #ffffff;
        }

        .btn-xs.btn-success:hover {
            background: #059669;
        }

        .badge {
            padding: 0.25rem 0.625rem;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
        }

        .badge-success {
            background-color: #d1fae5;
            color: #065f46;
        }

        .badge-warning {
            background-color: #fef3c7;
            color: #92400e;
        }

        .badge-info {
            background-color: #dbeafe;
            color: #1e40af;
        }

        .badge-secondary {
            background-color: #e2e8f0;
            color: #475569;
        }

        /* Unified Page Header Styles */
        .page-header-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .page-title {
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.5rem;
            font-weight: 600;
            color: #1a365d;
        }

        .page-subtitle {
            margin: 0.5rem 0 0 0;
            color: #64748b;
            font-size: 0.875rem;
        }

        .date-text {
            color: #f59e0b;
        }

        .text-center-empty {
            text-align: center;
            padding: 2rem;
        }

        /* Prescription Form Styles - Matches System Color Scheme */
        .prescription-form-card {
            padding: 2rem;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .form-section {
            margin-bottom: 2rem;
        }

        .form-section:last-of-type {
            margin-bottom: 1.5rem;
        }

        .section-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1a365d;
            margin: 0 0 1rem 0;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #eef2f7;
        }

        .form-row {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: 1rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-weight: 500;
            color: #475569;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
        }

        .req {
            color: #ef4444;
        }

        .form-input, .form-textarea {
            width: 100%;
            padding: 0.625rem 0.75rem;
            border: 1px solid #eef2f7;
            border-radius: 8px;
            font-size: 0.875rem;
            transition: all 0.2s;
            background: #ffffff;
            color: #1a365d;
        }

        .form-input:focus, .form-textarea:focus {
            outline: none;
            border-color: #3B82F6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .form-input::placeholder, .form-textarea::placeholder {
            color: #94a3b8;
        }

        .form-textarea {
            resize: vertical;
            min-height: 80px;
        }

        .form-input-sm {
            padding: 0.5rem 0.625rem;
            font-size: 0.8125rem;
        }

        /* Medication Table - Matches System Design */
        .medication-table-wrapper {
            border: 1px solid #eef2f7;
            border-radius: 8px;
            overflow: hidden;
            margin-top: 1rem;
            background: #ffffff;
        }

        .medication-table {
            width: 100%;
            border-collapse: collapse;
            background: #ffffff;
        }

        .medication-table thead {
            background: #f8fafc;
        }

        .medication-table th {
            padding: 0.875rem;
            text-align: left;
            font-weight: 600;
            font-size: 0.875rem;
            color: #1a365d;
            border-bottom: 2px solid #eef2f7;
        }

        .medication-table td {
            padding: 0.75rem;
            border-bottom: 1px solid #eef2f7;
            vertical-align: middle;
        }

        .medication-table tbody tr:last-child td {
            border-bottom: none;
        }

        .medication-table tbody tr:hover {
            background: #f8fafc;
        }

        .btn-add-medication {
            width: 100%;
            padding: 0.75rem;
            background: #f8fafc;
            border: 2px dashed #cbd5e1;
            border-top: none;
            color: #64748b;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            border-radius: 0 0 8px 8px;
        }

        .btn-add-medication:hover {
            background: #f1f5f9;
            border-color: #3B82F6;
            color: #1a365d;
        }

        .btn-remove {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1.1rem;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            transition: background 0.2s;
        }

        .btn-remove:hover {
            background: #fee2e2;
        }

        /* Form Actions */
        .form-actions {
            display: flex;
            gap: 1rem;
            padding-top: 1.5rem;
            border-top: 1px solid #eef2f7;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3B82F6, #2563eb);
            color: #ffffff;
            box-shadow: 0 2px 4px rgba(59, 130, 246, 0.2);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            box-shadow: 0 4px 8px rgba(59, 130, 246, 0.3);
            transform: translateY(-1px);
        }

        .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .btn-secondary {
            background: #f8fafc;
            color: #475569;
            border: 1px solid #eef2f7;
        }

        .btn-secondary:hover {
            background: #f1f5f9;
            border-color: #cbd5e1;
        }

        .btn-clear {
            background: transparent;
            color: #ef4444;
            border: 1px solid #ef4444;
        }

        .btn-clear:hover {
            background: #fee2e2;
            border-color: #dc2626;
        }

        /* Prescription Preview Card - Matches System Design */
        .prescription-preview-card {
            margin: 2rem 0;
            padding: 2rem;
            background: #ffffff;
            border: 2px solid #3B82F6;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(59, 130, 246, 0.15);
        }

        .preview-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #eef2f7;
        }

        .preview-header h3 {
            margin: 0;
            color: #1a365d;
            font-weight: 600;
        }

        .btn-close-preview {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #64748b;
            cursor: pointer;
            padding: 0;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.25rem;
            transition: background 0.2s;
        }

        .btn-close-preview:hover {
            background: #f1f5f9;
        }

        .preview-content {
            color: #1a365d;
        }

        .preview-section {
            margin-bottom: 1.5rem;
        }

        .preview-section:last-child {
            margin-bottom: 0;
        }

        .preview-row {
            margin-bottom: 0.5rem;
            line-height: 1.6;
            color: #475569;
        }

        .preview-label {
            color: #3B82F6;
            font-weight: 600;
        }

        .medication-preview-item {
            margin: 1rem 0;
            padding-left: 1.5rem;
        }

        .medication-preview-item strong {
            color: #1a365d;
            display: block;
            margin-bottom: 0.5rem;
        }

        .medication-details {
            margin-left: 1rem;
        }

        .medication-details > div {
            margin-bottom: 0.25rem;
            color: #475569;
        }

        .detail-label {
            color: #3B82F6;
            font-weight: 600;
        }

        .status-dispensed {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-dispensed .status-dot {
            background: #3b82f6;
        }

        /* Responsive for Prescription Forms */
        @media (max-width: 1024px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .medication-table {
                font-size: 0.8125rem;
            }
            
            .medication-table th,
            .medication-table td {
                padding: 0.5rem;
            }
            
            .prescriptions-table {
                font-size: 0.8125rem;
            }
            
            .prescriptions-table th,
            .prescriptions-table td {
                padding: 0.75rem 0.5rem;
            }
        }
    </style>
    <?php // Sidebar/header/footer are now handled by partials in app/Views/template/*.php ?>

    <div class="layout">
        <?= view('template/sidebar') ?>
        <main class="content">
            <?= view('template/header', ['useSidebar' => true, 'pageTitle' => $pageTitle ?? 'Dashboard']) ?>
            <?= $this->renderSection('content') ?>
        </main>
    </div>
<?php else: ?>
    <?= view('template/header', ['useSidebar' => false]) ?>

    <!-- Main Content -->
    <main class="main-content">
        <?= $this->renderSection('content') ?>
    </main>
<?php endif; ?>


<?= $this->renderSection('scripts') ?>
<?= view('template/footer') ?>
