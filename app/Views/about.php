<?= $this->extend('template') ?>

<?= $this->section('content') ?>
    <!-- About Section -->
    <div class="about-section">
        <div class="container">
            <h1 class="about-title">About MediCare Hospital</h1>
            <p class="about-subtitle">For over three decades, MediCare Hospital has been providing comprehensive healthcare services to our community with dedication and excellence.</p>

            <!-- Story and Vision Grid -->
            <div class="story-vision-grid">
                <div class="story-section">
                    <h2 class="section-title">Our Story</h2>
                    <p class="section-text">
                        Founded in 1985, MediCare Hospital began as a small community clinic with a big vision: to provide world-class healthcare that is accessible, affordable, and compassionate. Over the years, we have grown into a leading healthcare institution, serving thousands of patients with state-of-the-art facilities and a team of dedicated medical professionals.
                    </p>
                </div>

                <div class="vision-section">
                    <h2 class="section-title">Our Vision</h2>
                    <p class="section-text">
                        To be the premier healthcare provider in the region, recognized for clinical excellence, innovative treatments, and exceptional patient care. We strive to create a healing environment where every patient receives personalized attention and the highest quality medical care.
                    </p>
                </div>
            </div>

            <!-- Departments Grid -->
            <div class="departments-grid">
                <div class="department-card">
                    <div class="department-icon cardiology-icon">❤️</div>
                    <h3 class="department-title">Cardiology</h3>
                </div>

                <div class="department-card">
                    <div class="department-icon neurology-icon">🧠</div>
                    <h3 class="department-title">Neurology</h3>
                </div>

                <div class="department-card">
                    <div class="department-icon pediatrics-icon">👶</div>
                    <h3 class="department-title">Pediatrics</h3>
                </div>

                <div class="department-card">
                    <div class="department-icon surgery-icon">🔪</div>
                    <h3 class="department-title">Surgery</h3>
                </div>

                <div class="department-card">
                    <div class="department-icon pharmacy-icon">💊</div>
                    <h3 class="department-title">Pharmacy</h3>
                </div>

                <div class="department-card">
                    <div class="department-icon laboratory-icon">🔬</div>
                    <h3 class="department-title">Laboratory</h3>
                </div>
            </div>

            <!-- Stats Section -->
            <div class="stats-section">
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-number">300+</div>
                        <div class="stat-label">Hospital Beds</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">50+</div>
                        <div class="stat-label">Medical Specialists</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">24/7</div>
                        <div class="stat-label">Emergency Services</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?= $this->endSection() ?>
