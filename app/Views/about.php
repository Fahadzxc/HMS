<?= $this->extend('template') ?>

<?= $this->section('content') ?>
<!-- Page Header -->
<div class="card" style="text-align: center; background: linear-gradient(135deg, #2c3e50, #34495e); color: white; margin-bottom: 2rem;">
    <h1 class="card-title" style="color: white; font-size: 2.5rem; margin-bottom: 1rem;">About City General Hospital</h1>
    <p class="card-subtitle" style="color: #ecf0f1; font-size: 1.2rem;">Excellence in healthcare since 1974</p>
</div>

<!-- Mission & Vision -->
<div class="card">
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
        <div style="text-align: center; padding: 2rem; background: #f8f9fa; border-radius: 12px;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">🎯</div>
            <h3 style="color: #2c3e50; margin-bottom: 1rem;">Our Mission</h3>
            <p style="color: #5a6c7d; line-height: 1.6;">To provide exceptional, compassionate healthcare services that improve the health and well-being of our community through medical excellence, innovation, and personalized patient care.</p>
        </div>

        <div style="text-align: center; padding: 2rem; background: #f8f9fa; border-radius: 12px;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">👁️</div>
            <h3 style="color: #2c3e50; margin-bottom: 1rem;">Our Vision</h3>
            <p style="color: #5a6c7d; line-height: 1.6;">To be the premier healthcare institution recognized for clinical excellence, medical innovation, and outstanding patient experience, setting the standard for healthcare delivery in our region.</p>
        </div>
    </div>
</div>

<!-- History -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Our History</h2>
        <p class="card-subtitle">Five decades of dedicated healthcare service</p>
    </div>

    <div style="margin-top: 1rem;">
        <div style="position: relative; padding-left: 3rem; margin-bottom: 2rem;">
            <div style="position: absolute; left: 0; top: 0; width: 20px; height: 20px; background: #3498db; border-radius: 50%;"></div>
            <div style="position: absolute; left: 10px; top: 20px; bottom: -2rem; width: 2px; background: #3498db;"></div>
            <h4 style="color: #2c3e50; margin-bottom: 0.5rem;">1974 - Foundation</h4>
            <p style="color: #7f8c8d; line-height: 1.6;">City General Hospital was founded with a vision to provide comprehensive healthcare services to the growing community. Starting with just 50 beds, we began our journey of healing and care.</p>
        </div>

        <div style="position: relative; padding-left: 3rem; margin-bottom: 2rem;">
            <div style="position: absolute; left: 0; top: 0; width: 20px; height: 20px; background: #3498db; border-radius: 50%;"></div>
            <div style="position: absolute; left: 10px; top: 20px; bottom: -2rem; width: 2px; background: #3498db;"></div>
            <h4 style="color: #2c3e50; margin-bottom: 0.5rem;">1990 - Expansion</h4>
            <p style="color: #7f8c8d; line-height: 1.6;">Major expansion doubled our capacity to 150 beds and introduced specialized departments including Cardiology, Neurology, and Oncology, establishing us as a regional healthcare leader.</p>
        </div>

        <div style="position: relative; padding-left: 3rem; margin-bottom: 2rem;">
            <div style="position: absolute; left: 0; top: 0; width: 20px; height: 20px; background: #3498db; border-radius: 50%;"></div>
            <div style="position: absolute; left: 10px; top: 20px; bottom: -2rem; width: 2px; background: #3498db;"></div>
            <h4 style="color: #2c3e50; margin-bottom: 0.5rem;">2010 - Modernization</h4>
            <p style="color: #7f8c8d; line-height: 1.6;">Complete modernization with state-of-the-art equipment, electronic health records, and expanded to 250 beds. Achieved Joint Commission accreditation and national recognition for quality care.</p>
        </div>

        <div style="position: relative; padding-left: 3rem;">
            <div style="position: absolute; left: 0; top: 0; width: 20px; height: 20px; background: #3498db; border-radius: 50%;"></div>
            <h4 style="color: #2c3e50; margin-bottom: 0.5rem;">2024 - Today</h4>
            <p style="color: #7f8c8d; line-height: 1.6;">Celebrating 50 years of excellence with 250 beds, 25+ specialties, cutting-edge technology, and a commitment to providing world-class healthcare to our community.</p>
        </div>
    </div>
</div>

<!-- Leadership Team -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Leadership Team</h2>
        <p class="card-subtitle">Meet our dedicated leadership team</p>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem; margin-top: 1rem;">
        <div style="text-align: center; padding: 1.5rem; background: #f8f9fa; border-radius: 12px;">
            <div style="font-size: 4rem; margin-bottom: 1rem;">👨‍⚕️</div>
            <h4 style="color: #2c3e50; margin-bottom: 0.5rem;">Dr. Sarah Johnson</h4>
            <p style="color: #3498db; font-weight: 600; margin-bottom: 0.5rem;">Chief Executive Officer</p>
            <p style="color: #7f8c8d; font-size: 0.9rem;">MD, MBA, 25+ years in healthcare administration</p>
        </div>

        <div style="text-align: center; padding: 1.5rem; background: #f8f9fa; border-radius: 12px;">
            <div style="font-size: 4rem; margin-bottom: 1rem;">👩‍⚕️</div>
            <h4 style="color: #2c3e50; margin-bottom: 0.5rem;">Dr. Michael Chen</h4>
            <p style="color: #3498db; font-weight: 600; margin-bottom: 0.5rem;">Chief Medical Officer</p>
            <p style="color: #7f8c8d; font-size: 0.9rem;">MD, Board Certified in Internal Medicine</p>
        </div>

        <div style="text-align: center; padding: 1.5rem; background: #f8f9fa; border-radius: 12px;">
            <div style="font-size: 4rem; margin-bottom: 1rem;">👨‍💼</div>
            <h4 style="color: #2c3e50; margin-bottom: 0.5rem;">Robert Martinez</h4>
            <p style="color: #3498db; font-weight: 600; margin-bottom: 0.5rem;">Chief Financial Officer</p>
            <p style="color: #7f8c8d; font-size: 0.9rem;">CPA, MBA, Healthcare Finance Expert</p>
        </div>

        <div style="text-align: center; padding: 1.5rem; background: #f8f9fa; border-radius: 12px;">
            <div style="font-size: 4rem; margin-bottom: 1rem;">👩‍💼</div>
            <h4 style="color: #2c3e50; margin-bottom: 0.5rem;">Lisa Thompson</h4>
            <p style="color: #3498db; font-weight: 600; margin-bottom: 0.5rem;">Chief Nursing Officer</p>
            <p style="color: #7f8c8d; font-size: 0.9rem;">RN, MSN, 20+ years in nursing leadership</p>
        </div>
    </div>
</div>

<!-- Quality & Accreditation -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Quality & Accreditation</h2>
        <p class="card-subtitle">Our commitment to the highest standards of care</p>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem; margin-top: 1rem;">
        <div style="padding: 1.5rem; background: #f0f9ff; border-radius: 8px; border-left: 4px solid #3498db;">
            <h4 style="color: #1e40af; margin-bottom: 1rem;">🏆 Joint Commission Accredited</h4>
            <p style="color: #374151; line-height: 1.6;">Proud recipient of The Joint Commission Gold Seal of Approval for meeting the highest standards in healthcare quality and safety.</p>
        </div>

        <div style="padding: 1.5rem; background: #f0fdf4; border-radius: 8px; border-left: 4px solid #27ae60;">
            <h4 style="color: #166534; margin-bottom: 1rem;">⭐ Magnet Recognition</h4>
            <p style="color: #374151; line-height: 1.6;">Recognized by the American Nurses Credentialing Center as a Magnet hospital for excellence in nursing care and patient outcomes.</p>
        </div>

        <div style="padding: 1.5rem; background: #fef3c7; border-radius: 8px; border-left: 4px solid #f59e0b;">
            <h4 style="color: #92400e; margin-bottom: 1rem;">📊 Quality Metrics</h4>
            <p style="color: #374151; line-height: 1.6;">Consistently achieving top quartile performance in CMS quality measures and patient satisfaction scores above the 95th percentile.</p>
        </div>
    </div>
</div>

<!-- Community Involvement -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Community Involvement</h2>
        <p class="card-subtitle">Giving back to the community we serve</p>
    </div>

    <div style="margin-top: 1rem;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
            <div style="padding: 1rem; background: #f8f9fa; border-radius: 8px;">
                <h5 style="color: #2c3e50; margin-bottom: 0.5rem;">🎓 Medical Education</h5>
                <p style="color: #7f8c8d; font-size: 0.9rem;">Teaching hospital affiliated with State Medical University, training the next generation of healthcare professionals.</p>
            </div>

            <div style="padding: 1rem; background: #f8f9fa; border-radius: 8px;">
                <h5 style="color: #2c3e50; margin-bottom: 0.5rem;">❤️ Health Screenings</h5>
                <p style="color: #7f8c8d; font-size: 0.9rem;">Annual free health screenings and community health fairs reaching over 5,000 community members each year.</p>
            </div>

            <div style="padding: 1rem; background: #f8f9fa; border-radius: 8px;">
                <h5 style="color: #2c3e50; margin-bottom: 0.5rem;">🤝 Partnerships</h5>
                <p style="color: #7f8c8d; font-size: 0.9rem;">Collaborating with local organizations to address health disparities and improve community health outcomes.</p>
            </div>
        </div>
    </div>
</div>

<!-- Values -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Our Values</h2>
        <p class="card-subtitle">The principles that guide everything we do</p>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-top: 1rem;">
        <div style="text-align: center; padding: 1.5rem; background: #f8f9fa; border-radius: 12px;">
            <div style="font-size: 2.5rem; margin-bottom: 1rem;">💝</div>
            <h4 style="color: #2c3e50; margin-bottom: 0.5rem;">Compassion</h4>
            <p style="color: #7f8c8d; font-size: 0.9rem;">Treating every patient with dignity, respect, and empathy</p>
        </div>

        <div style="text-align: center; padding: 1.5rem; background: #f8f9fa; border-radius: 12px;">
            <div style="font-size: 2.5rem; margin-bottom: 1rem;">⭐</div>
            <h4 style="color: #2c3e50; margin-bottom: 0.5rem;">Excellence</h4>
            <p style="color: #7f8c8d; font-size: 0.9rem;">Striving for the highest quality in everything we do</p>
        </div>

        <div style="text-align: center; padding: 1.5rem; background: #f8f9fa; border-radius: 12px;">
            <div style="font-size: 2.5rem; margin-bottom: 1rem;">🔬</div>
            <h4 style="color: #2c3e50; margin-bottom: 0.5rem;">Innovation</h4>
            <p style="color: #7f8c8d; font-size: 0.9rem;">Embracing new technologies and medical advances</p>
        </div>

        <div style="text-align: center; padding: 1.5rem; background: #f8f9fa; border-radius: 12px;">
            <div style="font-size: 2.5rem; margin-bottom: 1rem;">🤝</div>
            <h4 style="color: #2c3e50; margin-bottom: 0.5rem;">Integrity</h4>
            <p style="color: #7f8c8d; font-size: 0.9rem;">Acting with honesty, transparency, and ethical standards</p>
        </div>

        <div style="text-align: center; padding: 1.5rem; background: #f8f9fa; border-radius: 12px;">
            <div style="font-size: 2.5rem; margin-bottom: 1rem;">👥</div>
            <h4 style="color: #2c3e50; margin-bottom: 0.5rem;">Teamwork</h4>
            <p style="color: #7f8c8d; font-size: 0.9rem;">Collaborating effectively for optimal patient care</p>
        </div>

        <div style="text-align: center; padding: 1.5rem; background: #f8f9fa; border-radius: 12px;">
            <div style="font-size: 2.5rem; margin-bottom: 1rem;">🌍</div>
            <h4 style="color: #2c3e50; margin-bottom: 0.5rem;">Community</h4>
            <p style="color: #7f8c8d; font-size: 0.9rem;">Serving our community with dedication and care</p>
        </div>
    </div>
</div>

<!-- Call to Action -->
<div class="card" style="text-align: center; background: linear-gradient(135deg, #3498db, #2980b9); color: white;">
    <h2 style="margin-bottom: 1rem; color: white;">Experience the City General Difference</h2>
    <p style="font-size: 1.1rem; margin-bottom: 2rem; opacity: 0.9;">Join thousands of patients who trust us with their healthcare needs</p>

    <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
        <a href="<?= base_url('appointments') ?>" class="btn" style="background: white; color: #3498db;">Schedule a Visit</a>
        <a href="<?= base_url('contact') ?>" class="btn" style="background: rgba(255,255,255,0.2);">Get in Touch</a>
        <a href="<?= base_url('services') ?>" class="btn" style="background: rgba(255,255,255,0.2);">View Services</a>
    </div>
</div>
<?= $this->endSection() ?>
