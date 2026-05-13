<style>
    :root {
        --primary: #4c1d95;
        --primary-light: #6d28d9;
        --primary-dark: #3b0764;
        --accent: #7c3aed;
        --surface: #faf5ff;
        --surface-card: #ffffff;
        --text-primary: #1f2937;
        --text-secondary: #6b7280;
        --text-light: #9ca3af;
        --border: #e5e7eb;
        --shadow-sm: 0 1px 2px rgba(0,0,0,0.05);
        --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);
        --shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05);
        --shadow-xl: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04);
    }

    body {
        font-family: 'Inter', sans-serif;
        background: var(--surface);
        color: var(--text-primary);
        overflow-x: hidden;
    }

    /* ===== NAVBAR ===== */
    .navbar {
        position: fixed;
        top: 0; left: 0; right: 0;
        z-index: 1000;
        background: rgba(255,255,255,0.85);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border-bottom: 1px solid rgba(229,231,235,0.5);
        transition: all 0.3s ease;
    }
    .navbar.scrolled {
        background: rgba(255,255,255,0.95);
        box-shadow: var(--shadow-md);
    }
    .nav-container {
        max-width: 1280px;
        margin: 0 auto;
        padding: 0 24px;
        height: 72px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .nav-logo {
        display: flex;
        align-items: center;
        gap: 10px;
        text-decoration: none;
        font-weight: 700;
        font-size: 1.25rem;
        color: var(--primary);
    }
    .nav-actions { display: flex; align-items: center; gap: 16px; }
    
    .btn-lms {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 10px 24px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        border: none;
    }
    .btn-lms-outline {
        background: white;
        color: var(--primary);
        border: 2px solid var(--primary);
    }
    .btn-lms-outline:hover {
        background: var(--primary);
        color: white;
        transform: translateY(-2px);
    }
    .btn-lms-primary {
        background: var(--primary);
        color: white;
        box-shadow: 0 4px 14px rgba(76, 29, 149, 0.35);
    }
    .btn-lms-primary:hover {
        background: var(--primary-light);
        transform: translateY(-2px);
    }

    /* ===== HERO ===== */
    .hero-lms {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
        padding: 120px 24px 80px;
    }
    .hero-bg { position: absolute; inset: 0; z-index: 0; }
    .gradient-orb { position: absolute; border-radius: 50%; filter: blur(80px); opacity: 0.5; }
    .orb-1 { width: 500px; height: 500px; background: linear-gradient(135deg, #8b5cf6, #6366f1); top: -10%; right: -5%; }
    .orb-2 { width: 400px; height: 400px; background: linear-gradient(135deg, #a855f7, #ec4899); bottom: -10%; left: -5%; }
    .orb-3 { width: 300px; height: 300px; background: linear-gradient(135deg, #3b82f6, #8b5cf6); top: 40%; left: 30%; opacity: 0.3; }
    
    .hero-grid {
        position: absolute; inset: 0;
        background-image: linear-gradient(rgba(139, 92, 246, 0.03) 1px, transparent 1px), linear-gradient(90deg, rgba(139, 92, 246, 0.03) 1px, transparent 1px);
        background-size: 60px 60px;
        z-index: 1;
    }
    .hero-content {
        position: relative; z-index: 2; max-width: 1280px; width: 100%;
        display: grid; grid-template-columns: 1fr 1.1fr; gap: 80px; align-items: center;
        margin: 0 auto;
    }
    .hero-brand {
        display: flex;
        align-items: center;
        gap: 12px;
        color: var(--primary);
        font-weight: 800;
        font-size: 1.5rem;
        margin-bottom: 24px;
    }
    .hero-badge {
        display: inline-flex; align-items: center; gap: 8px; padding: 8px 16px;
        background: rgba(139, 92, 246, 0.1); border: 1px solid rgba(139, 92, 246, 0.2);
        border-radius: 100px; color: var(--primary); font-size: 0.85rem; font-weight: 600; margin-bottom: 24px;
    }
    .hero-title { font-size: 4rem; font-weight: 900; line-height: 1.1; margin-bottom: 24px; text-align: left; }
    .hero-title span { background: linear-gradient(135deg, var(--primary), var(--accent)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    .hero-desc { font-size: 1.15rem; color: var(--text-secondary); line-height: 1.7; margin-bottom: 40px; max-width: 540px; text-align: left; margin-left: 0; }
    
    .hero-buttons {
        display: flex;
        gap: 16px;
        justify-content: flex-start;
    }
    
    .floating-card {
        position: absolute; background: white; border-radius: 20px; padding: 12px;
        box-shadow: var(--shadow-xl); animation: floatCard 6s ease-in-out infinite;
        z-index: 5;
        pointer-events: auto;
        transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    .floating-card:hover {
        transform: translateY(-20px) scale(1.1) rotate(var(--rotation, 0deg)) !important;
        z-index: 10;
        box-shadow: 0 25px 50px -12px rgba(76, 29, 149, 0.25);
        animation-play-state: paused;
    }
    .card-1 { top: -60px; left: 95%; width: 220px; animation-delay: 0s; --rotation: 4deg; }
    .card-2 { bottom: -80px; right: 95%; width: 200px; animation-delay: -2s; --rotation: -5deg; }
    .card-3 { top: 50%; left: 105%; width: 180px; animation-delay: -4s; --rotation: 2deg; }
    @keyframes floatCard { 
        0%, 100% { transform: translateY(0) rotate(var(--rotation, 0deg)); } 
        50% { transform: translateY(-25px) rotate(calc(var(--rotation, 0deg) + 3deg)); } 
    }
    
    .hero-main-card {
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        border-radius: 32px; padding: 60px 48px; color: white; box-shadow: var(--shadow-xl);
        position: relative; z-index: 2; overflow: hidden;
    }

    /* ===== STATS & FEATURES ===== */
    .stats { padding: 80px 24px; display: flex; justify-content: center; width: 100%; }
    .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; max-width: 1280px; width: 100%; margin: 0 auto; }
    .stat-card { background: white; border-radius: 20px; padding: 32px; text-align: center; border: 1px solid var(--border); transition: 0.4s; }
    .stat-card:hover { transform: translateY(-8px); box-shadow: var(--shadow-xl); }
    .stat-number { font-size: 2.5rem; font-weight: 800; color: var(--primary); }

    .features { padding: 100px 24px; display: flex; flex-direction: column; align-items: center; }
    .features-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 32px; max-width: 1280px; width: 100%; margin: 0 auto; }
    .feature-card { background: white; border-radius: 24px; padding: 40px; border: 1px solid var(--border); transition: 0.4s; }
    .feature-card:hover { transform: translateY(-8px); box-shadow: var(--shadow-xl); }
    .feature-icon { width: 64px; height: 64px; border-radius: 20px; background: linear-gradient(135deg, var(--primary), var(--accent)); display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem; margin-bottom: 24px; }

    /* ===== SHOWCASE ===== */
    .showcase { background: linear-gradient(135deg, var(--primary-dark), var(--primary)); color: white; padding: 100px 24px; }
    .showcase-content { max-width: 1280px; margin: 0 auto; display: grid; grid-template-columns: 1fr 1fr; gap: 60px; align-items: center; }
    .showcase-mockup { background: white; border-radius: 24px; padding: 24px; color: var(--text-primary); transform: perspective(1000px) rotateY(-5deg); transition: 0.6s; }
    .showcase-mockup:hover { transform: perspective(1000px) rotateY(0deg); }

    /* ===== CTA & FOOTER ===== */
    .cta { padding: 100px 24px; background: linear-gradient(135deg, var(--primary), var(--primary-dark)); color: white; text-align: center; }
    .footer { background: var(--text-primary); color: white; padding: 60px 24px; }
    .footer-grid { display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 48px; max-width: 1280px; margin: 0 auto; }

    .reveal { opacity: 0; transform: translateY(40px); transition: 0.8s ease-out; }
    .reveal.active { opacity: 1; transform: translateY(0); }

    html.dark {
        --primary: #c4b5fd;
        --primary-light: #ddd6fe;
        --primary-dark: #7c3aed;
        --accent: #a78bfa;
        --surface: #0f0d14;
        --surface-card: #1a1724;
        --text-primary: #ece8f0;
        --text-secondary: #b9b2cc;
        --text-light: #8a8299;
        --border: #3d3658;
        --shadow-sm: 0 1px 2px rgba(0,0,0,0.35);
        --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.45), 0 2px 4px -1px rgba(0,0,0,0.35);
        --shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.45), 0 4px 6px -2px rgba(0,0,0,0.35);
        --shadow-xl: 0 20px 25px -5px rgba(0,0,0,0.5), 0 10px 10px -5px rgba(0,0,0,0.35);
    }
    html.dark .stat-card,
    html.dark .feature-card {
        background: var(--surface-card);
        border-color: var(--border);
        color: var(--text-primary);
    }
    html.dark .showcase-mockup {
        background: #1e1a26;
        color: var(--text-primary);
    }
    html.dark .btn-lms-outline {
        background: transparent;
        color: var(--primary);
        border-color: var(--primary);
    }
    html.dark .btn-lms-outline:hover {
        background: var(--primary);
        color: #1a1025;
    }

    @media (max-width: 1024px) {
        .hero-content, .showcase-content, .footer-grid { grid-template-columns: 1fr; }
        .stats-grid, .features-grid { grid-template-columns: repeat(2, 1fr); }
    }
</style>

<!-- HERO -->
<section class="hero-lms" id="home">
    <div class="hero-bg">
        <div class="gradient-orb orb-1 parallax" data-speed="0.3"></div>
        <div class="gradient-orb orb-2 parallax" data-speed="0.2"></div>
        <div class="gradient-orb orb-3 parallax" data-speed="0.15"></div>
    </div>
    <div class="hero-grid"></div>
    <div class="hero-content">
        <div class="hero-text">
            <div class="hero-brand">
                <i class="fas fa-book-open"></i>
                Paper Library
            </div>
            <div class="hero-badge"><i class="fas fa-wand-magic-sparkles"></i> New Collection Available</div>
            <h1 class="hero-title">Your Digital<br><span>Library</span> Awaits</h1>
            <p class="hero-desc">Buy books or unlock them with a membership. Discover thousands of titles across every genre, all in one place.</p>
            <div class="hero-buttons">
                <a href="<?php echo APP_ROUTE; ?>?page=register" class="btn-lms btn-lms-primary hero-btn-primary"><i class="fas fa-rocket"></i> Get Started</a>
                <a href="<?php echo APP_ROUTE; ?>?page=login" class="btn-lms btn-lms-outline"><i class="fas fa-play-circle"></i> Sign In</a>
            </div>
        </div>
        <div class="hero-visual">
            <div class="relative w-full max-w-[520px] mx-auto">
                <div class="hero-main-card">
                    <span class="material-symbols-outlined text-4xl mb-4 opacity-50">auto_stories</span>
                    <h3 class="text-3xl font-black mb-4 font-lumina">Paper Library</h3>
                    <p class="text-lg opacity-80 mb-8 leading-relaxed">Dive into your next adventure. Your library is waiting for you.</p>
                </div>

                <!-- Floating Elements -->
                <div class="floating-card card-1">
                    <img src="https://images.unsplash.com/photo-1544947950-fa07a98d237f?w=200&h=280&fit=crop" class="rounded-xl mb-2" alt="Book">
                    <h4 class="text-sm font-semibold">The Great Gatsby</h4>
                    <p class="text-xs text-gray-500">F. Scott Fitzgerald</p>
                </div>
                <div class="floating-card card-2">
                    <img src="https://images.unsplash.com/photo-1543002588-bfa74002ed7e?w=200&h=280&fit=crop" class="rounded-xl mb-2" alt="Book">
                    <h4 class="text-sm font-semibold">1984</h4>
                    <p class="text-xs text-gray-500">George Orwell</p>
                </div>
                <div class="floating-card card-3">
                    <img src="https://images.unsplash.com/photo-1541963463532-d68292c34b19?w=200&h=280&fit=crop" class="rounded-xl mb-2" alt="Book">
                    <h4 class="text-sm font-semibold">Harry Potter</h4>
                    <p class="text-xs text-gray-500">J.K. Rowling</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- STATS -->
<section class="stats">
    <div class="stats-grid">
        <div class="stat-card reveal">
            <div class="text-primary text-2xl mb-4"><i class="fas fa-book"></i></div>
            <div class="stat-number" data-target="10000">0</div>
            <div class="text-gray-500 font-medium">Books Available</div>
        </div>
        <div class="stat-card reveal">
            <div class="text-primary text-2xl mb-4"><i class="fas fa-users"></i></div>
            <div class="stat-number" data-target="5000">0</div>
            <div class="text-gray-500 font-medium">Active Users</div>
        </div>
        <div class="stat-card reveal">
            <div class="text-primary text-2xl mb-4"><i class="fas fa-hand-holding-heart"></i></div>
            <div class="stat-number" data-target="50000">0</div>
            <div class="text-gray-500 font-medium">Books Read</div>
        </div>
        <div class="stat-card reveal">
            <div class="text-primary text-2xl mb-4"><i class="fas fa-star"></i></div>
            <div class="stat-number" data-target="4.8" data-decimal="true">0</div>
            <div class="text-gray-500 font-medium">Average Rating</div>
        </div>
    </div>
</section>

<!-- FEATURES -->
<section class="features" id="features">
    <div class="text-center max-w-2xl mx-auto mb-16">
        <div class="hero-badge"><i class="fas fa-wand-magic-sparkles"></i> Why Choose Us</div>
        <h2 class="text-4xl font-extrabold mb-4">Experience the Future of Reading</h2>
        <p class="text-gray-500">Our comprehensive platform is designed for modern readers who want seamless access to their favorite books.</p>
    </div>
    <div class="features-grid">
        <div class="feature-card reveal">
            <div class="feature-icon"><i class="fas fa-book-open"></i></div>
            <h3 class="text-xl font-bold mb-2">Vast Collection</h3>
            <p class="text-gray-500">Browse thousands of books across multiple genres and find your next favorite read.</p>
        </div>
        <div class="feature-card reveal">
            <div class="feature-icon"><i class="fas fa-tags"></i></div>
            <h3 class="text-xl font-bold mb-2">Instant Access</h3>
            <p class="text-gray-500">Buy books once or use your membership for unlimited access. Choose what works best for you.</p>
        </div>
        <div class="feature-card reveal">
            <div class="feature-icon"><i class="fas fa-comments"></i></div>
            <h3 class="text-xl font-bold mb-2">Community Driven</h3>
            <p class="text-gray-500">Read and write reviews to help other readers discover great books.</p>
        </div>
    </div>
</section>

<!-- SHOWCASE -->
<section class="showcase" id="showcase">
    <div class="showcase-content">
        <div class="reveal">
            <h2 class="text-4xl font-extrabold mb-6">Manage Your Library<br>Like a Pro</h2>
            <p class="opacity-80 mb-8">Our intuitive dashboard gives you complete control over your reading journey. Track borrowed books, manage your wallet, and explore new titles effortlessly.</p>
            <div class="space-y-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-white/10 flex items-center justify-center"><i class="fas fa-wallet"></i></div>
                    <div><h4 class="font-bold">Digital Wallet</h4><p class="text-sm opacity-70">Manage credits and payments securely</p></div>
                </div>
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-white/10 flex items-center justify-center"><i class="fas fa-bookmark"></i></div>
                    <div><h4 class="font-bold">My Books</h4><p class="text-sm opacity-70">Track all your purchased and membership books</p></div>
                </div>
            </div>
        </div>
        <div class="showcase-mockup reveal">
            <div class="flex gap-2 mb-4 border-b pb-4">
                <div class="w-3 h-3 rounded-full bg-red-500"></div>
                <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                <div class="w-3 h-3 rounded-full bg-green-500"></div>
                <span class="text-xs text-gray-400 ml-2">Paper Library Dashboard</span>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-gray-50 p-4 rounded-xl text-center">
                    <div class="h-24 bg-gradient-to-br from-purple-500 to-indigo-500 rounded-lg mb-2 flex items-center justify-center text-white text-2xl"><i class="fas fa-book-open"></i></div>
                    <h5 class="text-xs font-bold">Huckleberry Finn</h5>
                    <p class="text-[10px] text-gray-400">Mark Twain</p>
                </div>
                <div class="bg-gray-50 p-4 rounded-xl text-center">
                    <div class="h-24 bg-gradient-to-br from-pink-500 to-purple-500 rounded-lg mb-2 flex items-center justify-center text-white text-2xl"><i class="fas fa-ring"></i></div>
                    <h5 class="text-xs font-bold">Lord of the Rings</h5>
                    <p class="text-[10px] text-gray-400">J.R.R. Tolkien</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="cta">
    <div class="max-w-2xl mx-auto reveal">
        <h2 class="text-4xl font-extrabold mb-4">Ready to Start Reading?</h2>
        <p class="opacity-80 mb-8">Join thousands of readers who have already discovered their next favorite book. Sign up today and dive into your first adventure.</p>
        <div class="flex justify-center gap-4">
            <a href="<?php echo APP_ROUTE; ?>?page=register" class="btn-lms bg-white text-primary px-10 py-4 text-lg">Get Started Free</a>
            <a href="#" class="btn-lms border-2 border-white/30 text-white px-10 py-4 text-lg">Learn More</a>
        </div>
    </div>
</section>

<!-- FOOTER -->
<footer class="footer">
    <div class="footer-grid">
        <div>
            <a href="#" class="nav-logo text-white mb-6">
                <i class="fas fa-book-open"></i>
                <?php echo APP_NAME; ?>
            </a>
            <p class="text-gray-400 text-sm">Your digital library management solution. Buy books or unlock them with a membership with ease.</p>
        </div>
        <div>
            <h4 class="font-bold mb-4 uppercase text-xs tracking-widest">Quick Links</h4>
            <ul class="text-gray-400 text-sm space-y-2">
                <li><a href="<?php echo APP_ROUTE; ?>?page=home">Home</a></li>
                <li><a href="<?php echo APP_ROUTE; ?>?page=home#features">How It Works</a></li>
            </ul>
        </div>
    </div>
    <div class="max-w-[1280px] mx-auto mt-12 pt-8 border-t border-white/10 flex justify-between items-center text-gray-500 text-sm">
        <p>&copy; 2026 <?php echo APP_NAME; ?>. All rights reserved.</p>
        <div class="flex gap-4">
            <a href="#" class="hover:text-white"><i class="fab fa-twitter"></i></a>
            <a href="#" class="hover:text-white"><i class="fab fa-github"></i></a>
            <a href="#" class="hover:text-white"><i class="fab fa-linkedin"></i></a>
        </div>
    </div>
</footer>

<script>
    // Navbar scroll effect
    const navbar = document.getElementById('navbar');
    window.addEventListener('scroll', () => {
        if (window.scrollY > 50) navbar.classList.add('scrolled');
        else navbar.classList.remove('scrolled');
    });

    // Parallax effect
    const parallaxElements = document.querySelectorAll('.parallax');
    window.addEventListener('scroll', () => {
        const scrolled = window.scrollY;
        parallaxElements.forEach(el => {
            const speed = parseFloat(el.dataset.speed) || 0.2;
            el.style.transform = `translateY(${scrolled * speed}px)`;
        });
    });

    // Scroll reveal animation
    const revealElements = document.querySelectorAll('.reveal');
    const revealObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) entry.target.classList.add('active');
        });
    }, { threshold: 0.1 });
    revealElements.forEach(el => revealObserver.observe(el));

    // Counter animation
    const counters = document.querySelectorAll('.stat-number');
    const counterObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const target = parseFloat(entry.target.dataset.target);
                const isDecimal = entry.target.dataset.decimal === 'true';
                const duration = 2000;
                let start = 0;
                const startTime = performance.now();
                function updateCounter(currentTime) {
                    const elapsed = currentTime - startTime;
                    const progress = Math.min(elapsed / duration, 1);
                    const easeOut = 1 - Math.pow(1 - progress, 3);
                    const current = start + (target - start) * easeOut;
                    if (isDecimal) entry.target.textContent = current.toFixed(1);
                    else entry.target.textContent = Math.floor(current).toLocaleString() + '+';
                    if (progress < 1) requestAnimationFrame(updateCounter);
                }
                requestAnimationFrame(updateCounter);
                counterObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });
    counters.forEach(counter => counterObserver.observe(counter));
</script>
