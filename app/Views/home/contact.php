<?= $this->extend('template') ?>

<?= $this->section('content') ?>
    <!-- Contact Hero Section -->
    <div class="contact-hero">
        <div class="container">
            <h1 class="contact-hero-title">Get in <span class="highlight">Touch</span></h1>
            <p class="contact-hero-subtitle">Have questions about our services or need support? We're here to help you with your healthcare needs.</p>
        </div>
    </div>

    <!-- Contact Info Cards -->
    <div class="contact-cards-section">
        <div class="container">
            <div class="info-cards-grid">
                <div class="info-card">
                    <div class="card-icon email">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <h3>Email Us</h3>
                    <p class="card-detail">fahadalalawi1815@gmail.com</p>
                    <p class="card-description">Send us an email anytime</p>
                </div>

                <div class="info-card">
                    <div class="card-icon phone">
                        <i class="fas fa-phone-alt"></i>
                    </div>
                    <h3>Call Us</h3>
                    <p class="card-detail">09551168026</p>
                    <p class="card-description">Mon-Fri from 9am to 6pm EST</p>
                </div>

                <div class="info-card">
                    <div class="card-icon address">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <h3>Visit Us</h3>
                    <p class="card-detail">Bulaong, General Santos City</p>
                    <p class="card-description">Our headquarters location</p>
                </div>

                <div class="info-card">
                    <div class="card-icon support">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3>Support Hours</h3>
                    <p class="card-detail">24/7 Online Support</p>
                    <p class="card-description">Always here to help you</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Contact Form and Map Section -->
    <div class="contact-form-map-section">
        <div class="container">
            <div class="form-map-grid">
                <div class="message-form-card">
                    <h3>Send us a Message</h3>
                    <form class="contact-form">
                        <div class="form-group">
                            <label for="fullname">Full Name *</label>
                            <input type="text" id="fullname" name="fullname" placeholder="Your full name" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address *</label>
                            <input type="email" id="email" name="email" placeholder="your@email.com" required>
                        </div>
                        <div class="form-group">
                            <label for="subject">Subject *</label>
                            <select id="subject" name="subject" required>
                                <option value="">Select a subject</option>
                                <option value="general">General Inquiry</option>
                                <option value="appointment">Book Appointment</option>
                                <option value="emergency">Emergency</option>
                                <option value="support">Technical Support</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="message">Message *</label>
                            <textarea id="message" name="message" rows="5" placeholder="Tell us how we can help you..." required></textarea>
                            <div class="char-counter">0/500 characters</div>
                        </div>
                        <button type="submit" class="send-message-btn">
                            <i class="fas fa-paper-plane"></i>
                            Send Message
                        </button>
                    </form>
                </div>

                <div class="map-card">
                    <h3 class="find-us-title">Find Us</h3>
                    <iframe 
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3966.123456789!2d125.1667!3d6.1128!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x32f79c8b8b8b8b8b%3A0x1234567890abcdef!2sBulaong%2C%20General%20Santos%20City!5e0!3m2!1sen!2sph!4v1234567890123!5m2!1sen!2sph" 
                        width="100%" 
                        height="240" 
                        style="border:0; border-radius: 15px;" 
                        allowfullscreen="" 
                        loading="lazy" 
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                    
                    <!-- FAQ Section moved here -->
                    <div class="faq-section-inline">
                        <h4 class="faq-title-inline">Frequently Asked Questions</h4>
                        <div class="faq-list-inline">
                            <div class="faq-item-inline">
                                <h5 class="faq-question-inline">How do I book an appointment?</h5>
                                <p class="faq-answer-inline">Simply call our main number 09551168026 or send us an email at fahadalalawi1815@gmail.com. Our staff will help you schedule your appointment at your convenience.</p>
                            </div>
                            <div class="faq-item-inline">
                                <h5 class="faq-question-inline">Do you offer emergency services?</h5>
                                <p class="faq-answer-inline">Yes, we provide 24/7 emergency services. For urgent medical situations, call our emergency line immediately or visit our emergency department at Bulaong, General Santos City.</p>
                            </div>
                            <div class="faq-item-inline">
                                <h5 class="faq-question-inline">What are your operating hours?</h5>
                                <p class="faq-answer-inline">We operate 24/7 for emergency services. Regular consultations are available Monday to Friday from 9am to 6pm, and Saturday from 9am to 2pm.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Social Media Section -->
    <div class="social-section">
        <div class="container">
            <h3 class="social-title">Follow Us</h3>
            <div class="social-icons">
                <a href="#" class="social-icon facebook">
                    <i class="fab fa-facebook-f"></i>
                </a>
                <a href="#" class="social-icon twitter">
                    <i class="fab fa-twitter"></i>
                </a>
                <a href="#" class="social-icon linkedin">
                    <i class="fab fa-linkedin-in"></i>
                </a>
                <a href="#" class="social-icon instagram">
                    <i class="fab fa-instagram"></i>
                </a>
                <a href="#" class="social-icon youtube">
                    <i class="fab fa-youtube"></i>
                </a>
            </div>
        </div>
    </div>
<?= $this->endSection() ?>