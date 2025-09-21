<?= $this->extend('template') ?>

<?= $this->section('content') ?>
    <!-- Contact Section -->
    <div class="contact-section">
        <div class="container">
            <h1 class="contact-title">Contact Us</h1>
            <p class="contact-subtitle">We're here to help you with any questions or concerns. Get in touch with us today.</p>

            <div class="contact-content">
                <!-- Contact Information -->
                <div class="contact-info">
                    <h2>Get in Touch</h2>
                    
                    <div class="contact-item">
                        <div class="contact-icon address">📍</div>
                        <div class="contact-details">
                            <h4>Address</h4>
                            <p>123 Medical Center Drive<br>Healthcare City, HC 12345</p>
                        </div>
                    </div>

                    <div class="contact-item">
                        <div class="contact-icon phone">📞</div>
                        <div class="contact-details">
                            <h4>Phone</h4>
                            <p>Main: (555) 123-4567<br>Emergency: (555) 911-HELP</p>
                        </div>
                    </div>

                    <div class="contact-item">
                        <div class="contact-icon email">✉️</div>
                        <div class="contact-details">
                            <h4>Email</h4>
                            <p>info@medicare-hospital.com<br>emergency@medicare-hospital.com</p>
                        </div>
                    </div>

                    <div class="contact-item">
                        <div class="contact-icon emergency">🚨</div>
                        <div class="contact-details">
                            <h4>Emergency Services</h4>
                            <p>Available 24/7 for life-threatening emergencies</p>
                        </div>
                    </div>
                </div>

                <!-- Contact Form -->
                <div class="contact-form">
                    <h2>Send us a Message</h2>
                    <form>
                        <div class="form-group">
                            <label for="name">Full Name *</label>
                            <input type="text" id="name" name="name" required placeholder="Enter your full name">
                        </div>

                        <div class="form-group">
                            <label for="email">Email Address *</label>
                            <input type="email" id="email" name="email" required placeholder="Enter your email address">
                        </div>

                        <div class="form-group">
                            <label for="message">Message *</label>
                            <textarea id="message" name="message" required placeholder="How can we help you? Please provide details about your inquiry."></textarea>
                        </div>

                        <button type="submit" class="btn-submit">Send Message</button>
                    </form>
                </div>
            </div>

            <!-- Emergency Section -->
            <div class="emergency-section">
                <h2>🚨 Emergency Services</h2>
                <p>Available 24/7 for life-threatening emergencies</p>
                <div class="emergency-number">Emergency: 911 or (555) 911-HELP</div>
            </div>
        </div>
    </div>
<?= $this->endSection() ?>
