<?= $this->extend('template') ?>

<?= $this->section('content') ?>
<!-- Welcome Section -->
<div class="card">
    <div class="card-header">
        <h1 class="card-title">Welcome to City General Hospital</h1>
        <p class="card-subtitle">Your trusted healthcare partner, providing comprehensive medical services with excellence and compassion</p>
    </div>

    <div style="text-align: center; margin: 2rem 0 3rem 0;">
        <div style="display: inline-block; padding: 2.5rem 2rem 2rem 2.5rem; background: linear-gradient(135deg, #3498db, #2980b9); border-radius: 15px 5px 15px 5px; color: white; margin-bottom: 2rem; transform: rotate(0.5deg); box-shadow: 0 8px 25px rgba(52, 152, 219, 0.3);">
            <h2 style="margin: 0 0 1rem 0; font-size: 2.1rem; font-weight: 700; text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">🏥 Excellence in Healthcare</h2>
            <p style="margin: 0; font-size: 1.15rem; opacity: 0.95; line-height: 1.4;">Serving our community with world-class medical care<br><em>for over 50 years</em></p>
            <div style="margin-top: 1rem; font-size: 0.9rem; opacity: 0.8;">❤️ We care because you matter</div>
        </div>
    </div>
</div>

<!-- Hospital Statistics -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Hospital Statistics</h2>
        <p class="card-subtitle">Our commitment to excellence by the numbers</p>
    </div>

    <div class="stats-grid">
        <div class="stat-card" style="transform: rotate(-0.3deg);">
            <div class="stat-number" style="color: #3498db; font-size: 2.8rem;">50+</div>
            <div class="stat-label">Years of Service</div>
            <div style="font-size: 0.8rem; color: #7f8c8d; margin-top: 0.3rem;">Since 1974! 🎉</div>
        </div>
        <div class="stat-card" style="transform: rotate(0.4deg);">
            <div class="stat-number" style="color: #27ae60;">250+</div>
            <div class="stat-label">Bed Capacity</div>
            <div style="font-size: 0.8rem; color: #7f8c8d; margin-top: 0.3rem;">Room for everyone</div>
        </div>
        <div class="stat-card" style="transform: rotate(-0.2deg);">
            <div class="stat-number" style="color: #e74c3c;">150+</div>
            <div class="stat-label">Medical Staff</div>
            <div style="font-size: 0.8rem; color: #7f8c8d; margin-top: 0.3rem;">Amazing people! 👨‍⚕️</div>
        </div>
        <div class="stat-card" style="transform: rotate(0.3deg);">
            <div class="stat-number" style="color: #f39c12;">25+</div>
            <div class="stat-label">Specialties</div>
            <div style="font-size: 0.8rem; color: #7f8c8d; margin-top: 0.3rem;">Whatever you need</div>
        </div>
        <div class="stat-card" style="transform: rotate(-0.4deg);">
            <div class="stat-number" style="color: #9b59b6; font-size: 2.6rem;">24/7</div>
            <div class="stat-label">Emergency Care</div>
            <div style="font-size: 0.8rem; color: #7f8c8d; margin-top: 0.3rem;">Always here for you</div>
        </div>
        <div class="stat-card" style="transform: rotate(0.2deg);">
            <div class="stat-number" style="color: #27ae60;">98%</div>
            <div class="stat-label">Patient Satisfaction</div>
            <div style="font-size: 0.8rem; color: #7f8c8d; margin-top: 0.3rem;">You make us smile 😊</div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Quick Actions</h2>
        <p class="card-subtitle">Easy access to our most used services</p>
    </div>

    <div class="quick-actions">
        <a href="<?= base_url('appointments') ?>" class="action-btn" style="transform: rotate(-0.5deg); background: linear-gradient(135deg, #f8f9fa, #e9ecef); border: 2px solid #3498db;">
            <div class="action-icon" style="color: #3498db; font-size: 2.8rem;">📅</div>
            <div class="action-text">Book Appointment</div>
            <div style="font-size: 0.8rem; color: #7f8c8d; margin-top: 0.3rem;">Click here! ⬅️</div>
        </a>
        <a href="<?= base_url('doctors') ?>" class="action-btn" style="transform: rotate(0.8deg); background: linear-gradient(135deg, #f8f9fa, #e9ecef); border: 2px solid #27ae60;">
            <div class="action-icon" style="color: #27ae60; font-size: 3.2rem;">👨‍⚕️</div>
            <div class="action-text">Find a Doctor</div>
            <div style="font-size: 0.8rem; color: #7f8c8d; margin-top: 0.3rem;">Expert help</div>
        </a>
        <a href="<?= base_url('services') ?>" class="action-btn" style="transform: rotate(-0.3deg); background: linear-gradient(135deg, #f8f9fa, #e9ecef); border: 2px solid #e74c3c;">
            <div class="action-icon" style="color: #e74c3c; font-size: 2.9rem;">🏥</div>
            <div class="action-text">Our Services</div>
            <div style="font-size: 0.8rem; color: #7f8c8d; margin-top: 0.3rem;">Everything you need</div>
        </a>
        <a href="#emergency" class="action-btn" style="transform: rotate(0.6deg); background: linear-gradient(135deg, #f8f9fa, #e9ecef); border: 2px solid #f39c12;">
            <div class="action-icon" style="color: #f39c12; font-size: 3.1rem;">🚨</div>
            <div class="action-text">Emergency</div>
            <div style="font-size: 0.8rem; color: #7f8c8d; margin-top: 0.3rem;">We're here 24/7</div>
        </a>
        <a href="<?= base_url('contact') ?>" class="action-btn" style="transform: rotate(-0.7deg); background: linear-gradient(135deg, #f8f9fa, #e9ecef); border: 2px solid #9b59b6;">
            <div class="action-icon" style="color: #9b59b6; font-size: 2.7rem;">📞</div>
            <div class="action-text">Contact Us</div>
            <div style="font-size: 0.8rem; color: #7f8c8d; margin-top: 0.3rem;">Say hello! 👋</div>
        </a>
        <a href="<?= base_url('about') ?>" class="action-btn" style="transform: rotate(0.4deg); background: linear-gradient(135deg, #f8f9fa, #e9ecef); border: 2px solid #34495e;">
            <div class="action-icon" style="color: #34495e; font-size: 3rem;">ℹ️</div>
            <div class="action-text">About Us</div>
            <div style="font-size: 0.8rem; color: #7f8c8d; margin-top: 0.3rem;">Our story</div>
        </a>
    </div>
</div>

<!-- Services Overview -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Our Medical Services</h2>
        <p class="card-subtitle">Comprehensive healthcare services under one roof</p>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; margin-top: 1rem;">
        <div style="padding: 1.5rem; border-left: 4px solid #3498db; background: #f8f9fa; transform: rotate(0.2deg);">
            <h4 style="color: #2c3e50; margin-bottom: 1rem;">🏥 Emergency Medicine</h4>
            <p style="color: #7f8c8d; line-height: 1.6;">24/7 emergency care with state-of-the-art equipment and experienced medical professionals ready to handle any emergency.</p>
            <div style="font-size: 0.8rem; color: #3498db; margin-top: 0.5rem; font-style: italic;">"When every second counts, we're here" - Dr. Johnson</div>
        </div>

        <div style="padding: 1.5rem; border-left: 4px solid #27ae60; background: #f8f9fa; transform: rotate(-0.3deg);">
            <h4 style="color: #2c3e50; margin-bottom: 1rem;">👨‍⚕️ Specialized Care</h4>
            <p style="color: #7f8c8d; line-height: 1.6;">Expert physicians across 25+ specialties including Cardiology, Neurology, Oncology, and Orthopedics.</p>
            <div style="font-size: 0.8rem; color: #27ae60; margin-top: 0.5rem; font-style: italic;">Whatever your need, we've got you covered!</div>
        </div>

        <div style="padding: 1.5rem; border-left: 4px solid #e74c3c; background: #f8f9fa; transform: rotate(0.1deg);">
            <h4 style="color: #2c3e50; margin-bottom: 1rem;">🔬 Diagnostic Services</h4>
            <p style="color: #7f8c8d; line-height: 1.6;">Advanced diagnostic imaging, laboratory services, and screening programs for accurate diagnosis.</p>
            <div style="font-size: 0.8rem; color: #e74c3c; margin-top: 0.5rem; font-style: italic;">Getting to the heart of the matter, literally 💓</div>
        </div>

        <div style="padding: 1.5rem; border-left: 4px solid #f39c12; background: #f8f9fa; transform: rotate(-0.2deg);">
            <h4 style="color: #2c3e50; margin-bottom: 1rem;">💊 Pharmacy</h4>
            <p style="color: #7f8c8d; line-height: 1.6;">Full-service pharmacy with 24/7 availability and medication management services.</p>
            <div style="font-size: 0.8rem; color: #f39c12; margin-top: 0.5rem; font-style: italic;">Your meds, our priority. Open all night! 🌙</div>
        </div>
    </div>
</div>

<!-- Latest News & Announcements -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Latest News & Announcements</h2>
        <p class="card-subtitle">Stay updated with our latest developments and health tips</p>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; margin-top: 1rem;">
        <div style="padding: 1.5rem; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #3498db;">
            <h4 style="color: #2c3e50; margin-bottom: 0.5rem;">New Cardiology Wing Opening</h4>
            <p style="color: #7f8c8d; font-size: 0.9rem; margin-bottom: 0.5rem;">December 15, 2024</p>
            <p style="color: #5a6c7d; line-height: 1.5;">We're excited to announce the opening of our new state-of-the-art Cardiology Wing, featuring the latest in cardiac care technology.</p>
        </div>

        <div style="padding: 1.5rem; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #27ae60;">
            <h4 style="color: #2c3e50; margin-bottom: 0.5rem;">Free Health Screening Camp</h4>
            <p style="color: #7f8c8d; font-size: 0.9rem; margin-bottom: 0.5rem;">January 5, 2025</p>
            <p style="color: #5a6c7d; line-height: 1.5;">Join us for our annual free health screening camp. Early detection saves lives!</p>
        </div>

        <div style="padding: 1.5rem; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #e74c3c;">
            <h4 style="color: #2c3e50; margin-bottom: 0.5rem;">Flu Season Precautions</h4>
            <p style="color: #7f8c8d; font-size: 0.9rem; margin-bottom: 0.5rem;">December 1, 2024</p>
            <p style="color: #5a6c7d; line-height: 1.5;">Important information about flu prevention and vaccination during this season.</p>
        </div>
    </div>
</div>

<!-- Emergency Information -->
<div id="emergency" class="card" style="border-left: 4px solid #e74c3c;">
    <div class="card-header">
        <h2 class="card-title" style="color: #e74c3c;">🚨 Emergency Information</h2>
        <p class="card-subtitle">For medical emergencies, dial our 24/7 emergency line</p>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-top: 1rem;">
        <div style="padding: 1.5rem; background: #fdf2f2; border-radius: 8px; text-align: center;">
            <h3 style="color: #dc2626; margin-bottom: 1rem;">Emergency Hotline</h3>
            <p style="font-size: 2rem; font-weight: bold; color: #dc2626; margin: 1rem 0;">(555) 911-0000</p>
            <p style="color: #7f1d1d;">Available 24 hours a day, 7 days a week</p>
        </div>

        <div style="padding: 1.5rem; background: #f0f9ff; border-radius: 8px;">
            <h4 style="color: #1e40af; margin-bottom: 1rem;">When to Call Emergency</h4>
            <ul style="color: #374151; line-height: 1.8;">
                <li>Chest pain or difficulty breathing</li>
                <li>Severe bleeding or trauma</li>
                <li>Sudden numbness or weakness</li>
                <li>Severe allergic reactions</li>
                <li>Loss of consciousness</li>
            </ul>
        </div>

        <div style="padding: 1.5rem; background: #f0fdf4; border-radius: 8px;">
            <h4 style="color: #166534; margin-bottom: 1rem;">Emergency Department</h4>
            <p style="color: #166534; font-weight: 600; margin-bottom: 0.5rem;">Location: Building A, Ground Floor</p>
            <p style="color: #166534; font-weight: 600; margin-bottom: 0.5rem;">Hours: 24/7</p>
            <p style="color: #374151;">Walk-ins welcome. No appointment needed for emergencies.</p>
        </div>
    </div>
</div>

<!-- Call to Action -->
<div class="card" style="text-align: center; background: linear-gradient(135deg, #3498db, #2980b9); color: white; transform: rotate(-0.1deg);">
    <h2 style="margin-bottom: 1rem;">Your Health is Our Priority</h2>
    <p style="font-size: 1.1rem; margin-bottom: 2rem; opacity: 0.9;">Experience world-class healthcare with personalized attention and cutting-edge medical technology.</p>

    <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
        <a href="<?= base_url('appointments') ?>" class="btn" style="background: white; color: #3498db; transform: rotate(0.2deg);">Book Appointment</a>
        <a href="<?= base_url('contact') ?>" class="btn" style="background: rgba(255,255,255,0.2); transform: rotate(-0.3deg);">Contact Us</a>
        <a href="#emergency" class="btn btn-danger" style="transform: rotate(0.1deg);">Emergency</a>
    </div>

    <div style="margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid rgba(255,255,255,0.2); font-size: 0.9rem; opacity: 0.8;">
        <p>Built with ❤️ by our amazing web team</p>
        <p style="font-size: 0.8rem; margin-top: 0.3rem;">"Making healthcare accessible, one click at a time" - Web Team 2024</p>
    </div>
</div>

<!-- Personal Touch -->
<div style="text-align: center; margin-top: 2rem; padding: 1rem; background: white; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); transform: rotate(0.3deg);">
    <p style="color: #7f8c8d; font-size: 0.9rem; margin-bottom: 0.5rem;">✨ Hey there! This site was crafted with care by humans who actually care about healthcare</p>
    <p style="color: #95a5a6; font-size: 0.8rem;">No AI here - just good old-fashioned web development with a personal touch! 😊</p>
</div>
<?= $this->endSection() ?>
