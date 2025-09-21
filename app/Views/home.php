<?= $this->extend('template') ?>

<?= $this->section('content') ?>
    <!-- Hero Section -->
    <div class="hero-section">
        <div class="container">
            <h1 class="hero-title">Welcome to MediCare Hospital</h1>
            <p class="hero-subtitle">
                Providing exceptional healthcare services with compassion, excellence, and innovation.<br>
                Your health and well-being are our top priorities.
            </p>           

            <!-- Services Grid -->
            <div class="services-grid">
                <div class="service-card">
                    <div class="service-icon emergency-icon">🚑</div>
                    <h3 class="service-title">Emergency Care</h3>
                    <p class="service-description">24/7 emergency services with experienced medical professionals ready to provide immediate care when you need it most.</p>
</div>

                <div class="service-card">
                    <div class="service-icon treatment-icon">⚕️</div>
                    <h3 class="service-title">Specialized Treatment</h3>
                    <p class="service-description">Advanced medical treatments across multiple specialties with cutting-edge technology and expert medical teams.</p>
    </div>

                <div class="service-card">
                    <div class="service-icon care-icon">❤️</div>
                    <h3 class="service-title">Patient Care</h3>
                    <p class="service-description">Compassionate care focused on patient comfort, recovery, and overall well-being throughout your healthcare journey.</p>
        </div>
    </div>
</div>
</div>
<?= $this->endSection() ?>
