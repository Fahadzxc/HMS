<?= $this->extend('template') ?>

<?= $this->section('content') ?>
    <!-- Hero Section -->
    <div class="hero-section">
        <div class="container">
            <div class="hero-icon">
                <i class="fas fa-hospital"></i>
            </div>
            <h1 class="hero-title">Welcome to <span class="highlight">MediCare Hospital</span></h1>
            <p class="hero-subtitle">
                Your comprehensive healthcare partner is ready! Explore our specialized medical services, learn about our mission, or get in touch with our expert medical team.
            </p>           

            <!-- Services Grid -->
            <div class="services-grid">
                <div class="service-card">
                    <div class="service-icon emergency-icon">
                        <i class="fas fa-ambulance"></i>
                    </div>
                    <h3 class="service-title">Emergency Care</h3>
                    <p class="service-description">24/7 emergency services with experienced medical professionals ready to provide immediate care when you need it most.</p>
                </div>

                <div class="service-card">
                    <div class="service-icon treatment-icon">
                        <i class="fas fa-stethoscope"></i>
                    </div>
                    <h3 class="service-title">Specialized Treatment</h3>
                    <p class="service-description">Advanced medical treatments across multiple specialties with cutting-edge technology and expert medical teams.</p>
                </div>

                <div class="service-card">
                    <div class="service-icon care-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h3 class="service-title">Patient Care</h3>
                    <p class="service-description">Compassionate care focused on patient comfort, recovery, and overall well-being throughout your healthcare journey.</p>
                </div>
            </div>
</div>
</div>
<?= $this->endSection() ?>
