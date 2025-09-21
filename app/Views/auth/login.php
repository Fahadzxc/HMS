<?= $this->extend('template') ?>

<?= $this->section('content') ?>
    <!-- Login Section - Matching Homepage Design -->
    <div class="hero-section">
        <div class="container">
            <h1 class="hero-title">Welcome Back</h1>
            <p class="hero-subtitle">Sign in to your MediCare Hospital account</p>

            <!-- Login Card - Using same style as service cards -->
            <div class="login-card">
                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger">
                        <?= session()->getFlashdata('error') ?>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert-success">
                        <?= session()->getFlashdata('success') ?>
                    </div>
                <?php endif; ?>

                <form action="<?= base_url('login') ?>" method="post" class="login-form">
                    <?= csrf_field() ?>
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" value="<?= old('email') ?>" required placeholder="Enter your email address">
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required placeholder="Enter your password">
                    </div>

                    <button type="submit" class="btn-login">Sign In</button>

                    <div class="login-footer">
                        <a href="<?= base_url('/') ?>" class="forgot-password">← Back to Homepage</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?= $this->endSection() ?>
