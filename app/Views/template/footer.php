<script>
    const observerOptions = { threshold: 0.1, rootMargin: '0px 0px -50px 0px' };
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => { if (entry.isIntersecting) entry.target.classList.add('animate'); });
    }, observerOptions);
    document.querySelectorAll('.fade-in, .about-section, .service-card, .department-card').forEach(el => observer.observe(el));
</script>
</body>
</html>


