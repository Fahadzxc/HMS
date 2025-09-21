<?= $this->extend('template') ?>

<?= $this->section('content') ?>
    <!-- About Section -->
    <div class="about-section">
        <div class="container">
            <h1 class="about-title">About MediCare Hospital</h1>
            <p class="about-subtitle">For over three decades, MediCare Hospital has been providing comprehensive healthcare services to our community with dedication and excellence.</p>
            
            <!-- Mission Section -->
            <div class="mission-section">
                <div class="mission-content">
                    <div class="mission-text">
                        <h3>Our Mission</h3>
                        <p>To provide exceptional healthcare services with compassion, innovation, and excellence. We are committed to improving the health and well-being of our community through advanced medical care, cutting-edge technology, and a patient-centered approach.</p>
                        <p>We strive to set new standards in healthcare delivery and make quality medical services accessible to all members of our community, regardless of their background or circumstances.</p>
                    </div>
                    <div class="mission-image">
                        <img src="<?= base_url('public/images/mission-hospital.jpg.png') ?>" alt="MediCare Hospital Mission" class="mission-photo" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                        <div class="placeholder-image" style="display: none;">
                            <i class="fas fa-hospital"></i>
                            <p>Mission Image</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Team Section -->
            <div class="team-section">
                <h3 class="team-title">Meet Our Doctors</h3>
                <div class="doctors-grid">
                    <div class="doctor-card">
                        <div class="doctor-image">
                            <img src="<?= base_url('public/images/doctors/zyf-diga.jpg') ?>" alt="Dr. Zyf Diga" class="doctor-photo" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="placeholder-photo" style="display: none;">
                                <i class="fas fa-user-md"></i>
                            </div>
                        </div>
                        <div class="doctor-info">
                            <h4>Dr. Zyf Diga</h4>
                            <p class="doctor-title">Chief Medical Officer</p>
                            <p class="doctor-description">Leading our medical team with over 15 years of experience in emergency medicine and critical care. Dr. Diga specializes in trauma surgery and has saved countless lives through his expertise and dedication.</p>
                            <a href="#" class="linkedin-btn">
                                <i class="fab fa-linkedin"></i>
                                LinkedIn
                            </a>
                        </div>
                    </div>

                    <div class="doctor-card">
                        <div class="doctor-image">
                            <img src="<?= base_url('public/images/doctors/jul-pakdol.jpg') ?>" alt="Dr. Jul Pakdol" class="doctor-photo" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="placeholder-photo" style="display: none;">
                                <i class="fas fa-user-md"></i>
                            </div>
                        </div>
                        <div class="doctor-info">
                            <h4>Dr. Jul Pakdol</h4>
                            <p class="doctor-title">Cardiologist</p>
                            <p class="doctor-description">Expert in cardiovascular diseases with advanced training in interventional cardiology. Dr. Pakdol has performed over 1000 successful heart procedures and is known for his precision and patient care.</p>
                            <a href="#" class="linkedin-btn">
                                <i class="fab fa-linkedin"></i>
                                LinkedIn
                            </a>
                        </div>
                    </div>

                    <div class="doctor-card">
                        <div class="doctor-image">
                            <img src="<?= base_url('public/images/doctors/akilla-jasque.jpg') ?>" alt="Dr. Akilla Jasque" class="doctor-photo" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="placeholder-photo" style="display: none;">
                                <i class="fas fa-user-md"></i>
                            </div>
                        </div>
                        <div class="doctor-info">
                            <h4>Dr. Akilla Jasque</h4>
                            <p class="doctor-title">Pediatrician</p>
                            <p class="doctor-description">Dedicated to children's health with specialized training in pediatric emergency care. Dr. Jasque combines medical expertise with a gentle approach, making children feel comfortable during treatment.</p>
                            <a href="#" class="linkedin-btn">
                                <i class="fab fa-linkedin"></i>
                                LinkedIn
                            </a>
                        </div>
                    </div>

                    <div class="doctor-card">
                        <div class="doctor-image">
                            <img src="<?= base_url('public/images/doctors/fahad-al-alawi.jpg') ?>" alt="Dr. Fahad Al-Alawi" class="doctor-photo" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="placeholder-photo" style="display: none;">
                                <i class="fas fa-user-md"></i>
                            </div>
                        </div>
                        <div class="doctor-info">
                            <h4>Dr. Fahad Al-Alawi</h4>
                            <p class="doctor-title">Neurologist</p>
                            <p class="doctor-description">Specialist in neurological disorders and brain surgery with international training. Dr. Al-Alawi is renowned for his innovative treatment approaches and has published numerous research papers in neurology.</p>
                            <a href="#" class="linkedin-btn">
                                <i class="fab fa-linkedin"></i>
                                LinkedIn
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?= $this->endSection() ?>
