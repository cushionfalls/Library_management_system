<!-- Hero Section -->
<div class="hero min-h-[80vh] bg-surface border-b border-outline-variant/30 relative overflow-hidden">
    <div class="absolute inset-0 pointer-events-none opacity-40">
        <div class="absolute inset-0 bg-[radial-gradient(#cac1ff_0.5px,transparent_0.5px)] [background-size:24px_24px]"></div>
    </div>
    <div class="hero-content text-center max-w-4xl">
        <div class="space-y-8 relative z-10">
            <h1 class="font-headline text-6xl font-extrabold leading-tight tracking-tight text-on-surface"><?php echo APP_NAME; ?></h1>
            <p class="text-xl text-on-surface-variant max-w-2xl mx-auto">Your digital library management solution. Rent, buy, or borrow books online and offline with ease.</p>

            <?php if (!isset($_SESSION['user_id'])): ?>
                <div class="flex gap-6 justify-center flex-wrap">
                    <a href="<?php echo APP_URL; ?>/public/index.php?page=register" class="inline-flex items-center justify-center gap-3 px-8 py-4 text-lg text-white font-bold rounded-xl bg-primary text-on-primary shadow-md hover:bg-primary-container transition-all active:scale-[0.99]">
                        <i class="fas fa-rocket mr-3"></i> Get Started
                    </a>
                    <a href="<?php echo APP_URL; ?>/public/index.php?page=login" class="inline-flex items-center justify-center gap-3 px-8 py-4 text-lg font-bold rounded-xl border border-outline-variant/60 text-on-surface hover:bg-surface-container-high transition-all active:scale-[0.99]">
                        <i class="fas fa-sign-in-alt mr-3"></i> Sign In
                    </a>
                </div>
            <?php
else: ?>
                <a href="<?php echo APP_URL; ?>/public/index.php?page=books" class="inline-flex items-center justify-center gap-3 px-8 py-4 text-lg font-bold rounded-xl bg-primary text-on-primary shadow-md hover:bg-primary-container transition-all active:scale-[0.99]">
                    <i class="fas fa-book mr-3"></i> Browse Books
                </a>
            <?php
endif; ?>
        </div>
    </div>
</div>

<!-- Features Section -->
<div class="py-16 bg-surface-container-low/40">
    <div class="max-w-6xl mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="font-headline text-4xl font-bold mb-4 text-on-surface">Why Choose Our Library?</h2>
            <p class="text-lg text-on-surface-variant max-w-2xl mx-auto">Experience the future of book management with our comprehensive platform designed for modern readers.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="bg-surface-container-lowest border border-outline-variant/20 shadow-sm rounded-2xl">
                <div class="card-body text-center">
                    <div class="text-5xl mb-6 text-primary">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <h3 class="card-title text-2xl mb-4 text-on-surface">Vast Collection</h3>
                    <p class="text-on-surface-variant text-lg">Browse thousands of books across multiple genres and find your next favorite read.</p>
                </div>
            </div>

            <div class="bg-surface-container-lowest border border-outline-variant/20 shadow-sm rounded-2xl">
                <div class="card-body text-center">
                    <div class="text-5xl mb-6 text-secondary">
                        <i class="fas fa-tags"></i>
                    </div>
                    <h3 class="card-title text-2xl mb-4 text-on-surface">Flexible Pricing</h3>
                    <p class="text-on-surface-variant text-lg">Rent or buy books at affordable prices. Choose what works best for your budget and needs.</p>
                </div>
            </div>

            <div class="bg-surface-container-lowest border border-outline-variant/20 shadow-sm rounded-2xl">
                <div class="card-body text-center">
                    <div class="text-5xl mb-6 text-tertiary">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 class="card-title text-2xl mb-4 text-on-surface">Community Driven</h3>
                    <p class="text-on-surface-variant text-lg">Read and write reviews to help other readers discover great books and make informed choices.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stats Section -->
<div class="py-16 bg-surface">
    <div class="max-w-4xl mx-auto px-4 text-center">
        <h2 class="font-headline text-4xl font-bold mb-8 text-on-surface">Join Thousands of Readers</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
            <div>
                <div class="text-4xl font-bold text-primary mb-2">10K+</div>
                <div class="text-on-surface-variant">Books Available</div>
            </div>
            <div>
                <div class="text-4xl font-bold text-secondary mb-2">5K+</div>
                <div class="text-on-surface-variant">Active Users</div>
            </div>
            <div>
                <div class="text-4xl font-bold text-tertiary mb-2">50K+</div>
                <div class="text-on-surface-variant">Books Rented</div>
            </div>
            <div>
                <div class="text-4xl font-bold text-primary-container mb-2">4.8</div>
                <div class="text-on-surface-variant">Average Rating</div>
            </div>
        </div>
    </div>
</div>
