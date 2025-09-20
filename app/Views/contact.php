<?= $this->extend('template') ?>

<?= $this->section('content') ?>
<!-- Page Header -->
<div class="card" style="text-align: center; background: linear-gradient(135deg, #27ae60, #229954); color: white; margin-bottom: 2rem;">
    <h1 class="card-title" style="color: white; font-size: 2.5rem; margin-bottom: 1rem;">Contact Us</h1>
    <p class="card-subtitle" style="color: #d4edda; font-size: 1.2rem;">We're here to help you 24/7</p>
</div>

<!-- Contact Information -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Get in Touch</h2>
        <p class="card-subtitle">Multiple ways to reach our healthcare professionals</p>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; margin-top: 1rem;">
        <!-- Main Hospital -->
        <div style="padding: 2rem; background: #f8f9fa; border-radius: 12px; border-left: 4px solid #3498db;">
            <div style="font-size: 2.5rem; margin-bottom: 1rem;">🏥</div>
            <h3 style="color: #2c3e50; margin-bottom: 1rem;">Main Hospital</h3>
            <div style="color: #5a6c7d; line-height: 1.8;">
                <p><strong>Address:</strong><br>123 Healthcare Avenue<br>Medical City, MC 12345</p>
                <p><strong>Main Line:</strong><br>(555) 123-4567</p>
                <p><strong>Fax:</strong><br>(555) 123-4568</p>
                <p><strong>Email:</strong><br>info@citygeneralhospital.com</p>
            </div>
        </div>

        <!-- Emergency -->
        <div style="padding: 2rem; background: #fdf2f2; border-radius: 12px; border-left: 4px solid #e74c3c;">
            <div style="font-size: 2.5rem; margin-bottom: 1rem;">🚨</div>
            <h3 style="color: #dc2626; margin-bottom: 1rem;">Emergency Services</h3>
            <div style="color: #7f1d1d; line-height: 1.8;">
                <p><strong>Emergency Hotline:</strong><br>(555) 911-0000</p>
                <p><strong>Direct Line:</strong><br>(555) 911-0001</p>
                <p><strong>Available:</strong><br>24/7 - No appointment needed</p>
                <p><strong>Location:</strong><br>Building A, Ground Floor</p>
            </div>
        </div>

        <!-- Appointments -->
        <div style="padding: 2rem; background: #f0f9ff; border-radius: 12px; border-left: 4px solid #3498db;">
            <div style="font-size: 2.5rem; margin-bottom: 1rem;">📅</div>
            <h3 style="color: #1e40af; margin-bottom: 1rem;">Appointments</h3>
            <div style="color: #374151; line-height: 1.8;">
                <p><strong>Scheduling:</strong><br>(555) 123-4567</p>
                <p><strong>Online Booking:</strong><br>Available 24/7</p>
                <p><strong>Hours:</strong><br>Mon-Fri: 6:00 AM - 10:00 PM</p>
                <p><strong>Weekend:</strong><br>Sat-Sun: 8:00 AM - 8:00 PM</p>
            </div>
        </div>
    </div>
</div>

<!-- Department Contacts -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Department Direct Lines</h2>
        <p class="card-subtitle">Direct access to specific hospital departments</p>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-top: 1rem;">
        <div style="padding: 1rem; background: #f8f9fa; border-radius: 8px; text-align: center;">
            <div style="font-size: 2rem; margin-bottom: 0.5rem;">❤️</div>
            <h4 style="color: #2c3e50; margin-bottom: 0.5rem;">Cardiology</h4>
            <p style="color: #3498db; font-weight: 600;">(555) 123-4570</p>
        </div>

        <div style="padding: 1rem; background: #f8f9fa; border-radius: 8px; text-align: center;">
            <div style="font-size: 2rem; margin-bottom: 0.5rem;">🧠</div>
            <h4 style="color: #2c3e50; margin-bottom: 0.5rem;">Neurology</h4>
            <p style="color: #3498db; font-weight: 600;">(555) 123-4571</p>
        </div>

        <div style="padding: 1rem; background: #f8f9fa; border-radius: 8px; text-align: center;">
            <div style="font-size: 2rem; margin-bottom: 0.5rem;">🦴</div>
            <h4 style="color: #2c3e50; margin-bottom: 0.5rem;">Orthopedics</h4>
            <p style="color: #3498db; font-weight: 600;">(555) 123-4572</p>
        </div>

        <div style="padding: 1rem; background: #f8f9fa; border-radius: 8px; text-align: center;">
            <div style="font-size: 2rem; margin-bottom: 0.5rem;">👶</div>
            <h4 style="color: #2c3e50; margin-bottom: 0.5rem;">Pediatrics</h4>
            <p style="color: #3498db; font-weight: 600;">(555) 123-4573</p>
        </div>

        <div style="padding: 1rem; background: #f8f9fa; border-radius: 8px; text-align: center;">
            <div style="font-size: 2rem; margin-bottom: 0.5rem;">🏥</div>
            <h4 style="color: #2c3e50; margin-bottom: 0.5rem;">Emergency</h4>
            <p style="color: #e74c3c; font-weight: 600;">(555) 911-0000</p>
        </div>

        <div style="padding: 1rem; background: #f8f9fa; border-radius: 8px; text-align: center;">
            <div style="font-size: 2rem; margin-bottom: 0.5rem;">💊</div>
            <h4 style="color: #2c3e50; margin-bottom: 0.5rem;">Pharmacy</h4>
            <p style="color: #3498db; font-weight: 600;">(555) 123-4574</p>
        </div>

        <div style="padding: 1rem; background: #f8f9fa; border-radius: 8px; text-align: center;">
            <div style="font-size: 2rem; margin-bottom: 0.5rem;">🔬</div>
            <h4 style="color: #2c3e50; margin-bottom: 0.5rem;">Laboratory</h4>
            <p style="color: #3498db; font-weight: 600;">(555) 123-4575</p>
        </div>

        <div style="padding: 1rem; background: #f8f9fa; border-radius: 8px; text-align: center;">
            <div style="font-size: 2rem; margin-bottom: 0.5rem;">📊</div>
            <h4 style="color: #2c3e50; margin-bottom: 0.5rem;">Radiology</h4>
            <p style="color: #3498db; font-weight: 600;">(555) 123-4576</p>
        </div>
    </div>
</div>

<!-- Location & Directions -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Location & Directions</h2>
        <p class="card-subtitle">Find us easily with these directions</p>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; margin-top: 1rem;">
        <div style="padding: 1.5rem; background: #f0f9ff; border-radius: 8px;">
            <h4 style="color: #1e40af; margin-bottom: 1rem;">📍 Getting Here</h4>
            <div style="color: #374151; line-height: 1.6;">
                <p><strong>From Downtown:</strong><br>Take Main Street east for 2 miles, turn right on Healthcare Ave.</p>
                <p><strong>From Highway:</strong><br>Exit 45, go north 1.5 miles, hospital is on the right.</p>
                <p><strong>Public Transit:</strong><br>Bus routes 15, 22, and 45 stop at our main entrance.</p>
            </div>
        </div>

        <div style="padding: 1.5rem; background: #f0fdf4; border-radius: 8px;">
            <h4 style="color: #166534; margin-bottom: 1rem;">🅿️ Parking Information</h4>
            <div style="color: #374151; line-height: 1.6;">
                <p><strong>Patient Parking:</strong><br>Free parking in designated patient areas</p>
                <p><strong>Visitor Parking:</strong><br>Free 2-hour parking in visitor lots</p>
                <p><strong>Valet Service:</strong><br>Available at main entrance, $5 fee</p>
                <p><strong>Handicap Parking:</strong><br>Available near all entrances</p>
            </div>
        </div>

        <div style="padding: 1.5rem; background: #fef3c7; border-radius: 8px;">
            <h4 style="color: #92400e; margin-bottom: 1rem;">🚶 Visiting Hours</h4>
            <div style="color: #374151; line-height: 1.6;">
                <p><strong>General:</strong><br>8:00 AM - 8:00 PM daily</p>
                <p><strong>ICU:</strong><br>10:00 AM - 12:00 PM, 4:00 PM - 6:00 PM</p>
                <p><strong>Emergency:</strong><br>24/7 access for immediate family</p>
                <p><strong>Special Circumstances:</strong><br>Contact nursing staff for exceptions</p>
            </div>
        </div>
    </div>
</div>

<!-- Contact Form -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Send Us a Message</h2>
        <p class="card-subtitle">We'd love to hear from you. Send us a message and we'll respond as soon as possible.</p>
    </div>

    <form style="margin-top: 1rem;" action="#" method="post">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
            <div>
                <label style="display: block; margin-bottom: 0.5rem; color: #2c3e50; font-weight: 600;">First Name *</label>
                <input type="text" name="first_name" required style="width: 100%; padding: 0.75rem; border: 2px solid #d1d5db; border-radius: 8px; font-size: 1rem;">
            </div>
            <div>
                <label style="display: block; margin-bottom: 0.5rem; color: #2c3e50; font-weight: 600;">Last Name *</label>
                <input type="text" name="last_name" required style="width: 100%; padding: 0.75rem; border: 2px solid #d1d5db; border-radius: 8px; font-size: 1rem;">
            </div>
        </div>

        <div style="margin-bottom: 1rem;">
            <label style="display: block; margin-bottom: 0.5rem; color: #2c3e50; font-weight: 600;">Email Address *</label>
            <input type="email" name="email" required style="width: 100%; padding: 0.75rem; border: 2px solid #d1d5db; border-radius: 8px; font-size: 1rem;">
        </div>

        <div style="margin-bottom: 1rem;">
            <label style="display: block; margin-bottom: 0.5rem; color: #2c3e50; font-weight: 600;">Phone Number</label>
            <input type="tel" name="phone" style="width: 100%; padding: 0.75rem; border: 2px solid #d1d5db; border-radius: 8px; font-size: 1rem;">
        </div>

        <div style="margin-bottom: 1rem;">
            <label style="display: block; margin-bottom: 0.5rem; color: #2c3e50; font-weight: 600;">Department</label>
            <select name="department" style="width: 100%; padding: 0.75rem; border: 2px solid #d1d5db; border-radius: 8px; font-size: 1rem;">
                <option value="">Select Department</option>
                <option value="emergency">Emergency</option>
                <option value="appointments">Appointments</option>
                <option value="cardiology">Cardiology</option>
                <option value="neurology">Neurology</option>
                <option value="orthopedics">Orthopedics</option>
                <option value="pediatrics">Pediatrics</option>
                <option value="general">General Inquiry</option>
                <option value="billing">Billing</option>
                <option value="other">Other</option>
            </select>
        </div>

        <div style="margin-bottom: 1rem;">
            <label style="display: block; margin-bottom: 0.5rem; color: #2c3e50; font-weight: 600;">Subject *</label>
            <input type="text" name="subject" required style="width: 100%; padding: 0.75rem; border: 2px solid #d1d5db; border-radius: 8px; font-size: 1rem;">
        </div>

        <div style="margin-bottom: 1rem;">
            <label style="display: block; margin-bottom: 0.5rem; color: #2c3e50; font-weight: 600;">Message *</label>
            <textarea name="message" required rows="5" style="width: 100%; padding: 0.75rem; border: 2px solid #d1d5db; border-radius: 8px; font-size: 1rem; resize: vertical;"></textarea>
        </div>

        <div style="margin-bottom: 1rem;">
            <label style="display: block; margin-bottom: 0.5rem; color: #2c3e50; font-weight: 600;">
                <input type="checkbox" name="urgent" style="margin-right: 0.5rem;"> This is an urgent matter
            </label>
        </div>

        <button type="submit" class="btn btn-success" style="width: 100%;">Send Message</button>
    </form>
</div>

<!-- Quick Contact Options -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Quick Contact Options</h2>
        <p class="card-subtitle">Choose the fastest way to reach us</p>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-top: 1rem;">
        <a href="tel:5559110000" class="action-btn" style="background: linear-gradient(135deg, #e74c3c, #c0392b); color: white;">
            <div class="action-icon">📞</div>
            <div class="action-text">Call Emergency<br><span style="font-size: 0.9rem; opacity: 0.9;">(555) 911-0000</span></div>
        </a>

        <a href="tel:5551234567" class="action-btn" style="background: linear-gradient(135deg, #3498db, #2980b9); color: white;">
            <div class="action-icon">🏥</div>
            <div class="action-text">Call Main Line<br><span style="font-size: 0.9rem; opacity: 0.9;">(555) 123-4567</span></div>
        </a>

        <a href="mailto:info@citygeneralhospital.com" class="action-btn" style="background: linear-gradient(135deg, #27ae60, #229954); color: white;">
            <div class="action-icon">✉️</div>
            <div class="action-text">Send Email<br><span style="font-size: 0.9rem; opacity: 0.9;">info@citygeneralhospital.com</span></div>
        </a>

        <a href="<?= base_url('appointments') ?>" class="action-btn" style="background: linear-gradient(135deg, #9b59b6, #8e44ad); color: white;">
            <div class="action-icon">📅</div>
            <div class="action-text">Book Online<br><span style="font-size: 0.9rem; opacity: 0.9;">24/7 Available</span></div>
        </a>
    </div>
</div>

<!-- Emergency Notice -->
<div class="card" style="background: linear-gradient(135deg, #e74c3c, #c0392b); color: white; border-left: 4px solid #a93226;">
    <div style="text-align: center;">
        <h2 style="margin-bottom: 1rem; color: white;">🚨 Medical Emergency?</h2>
        <p style="font-size: 1.1rem; margin-bottom: 1rem; opacity: 0.9;">Don't fill out forms. Call our emergency line immediately.</p>
        <div style="font-size: 2rem; margin: 1rem 0;">(555) 911-0000</div>
        <p style="opacity: 0.8;">Available 24 hours a day, 7 days a week</p>
    </div>
</div>
<?= $this->endSection() ?>
