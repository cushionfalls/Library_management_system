<div class="space-y-16 lg:space-y-24 pb-8">
    <section class="relative overflow-hidden rounded-3xl border border-[#c9c4da]/40 bg-gradient-to-br from-[#f7f1ff] via-[#fdf8ff] to-white px-6 py-16 sm:px-12 sm:py-20 lg:px-16 lg:py-24 shadow-sm">
        <div class="absolute -right-24 -top-24 h-64 w-64 rounded-full bg-[#4F1BF1]/10 blur-3xl" aria-hidden="true"></div>
        <div class="absolute -bottom-16 -left-16 h-48 w-48 rounded-full bg-[#4F1BF1]/5 blur-2xl" aria-hidden="true"></div>
        <div class="relative max-w-3xl">
            <p class="mb-4 inline-flex items-center gap-2 rounded-full border border-[#c9c4da]/50 bg-white/70 px-4 py-1.5 text-xs font-bold uppercase tracking-wider text-[#4F1BF1] font-['Manrope']">
                <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">auto_stories</span>
                Your library, simplified
            </p>
            <h1 class="text-4xl font-extrabold tracking-tight text-[#1c1a25] sm:text-5xl lg:text-6xl font-['Manrope'] leading-[1.1]">
                Discover books, manage loans, and stay on top of fines in one place.
            </h1>
            <p class="mt-6 text-lg text-[#474557] leading-relaxed max-w-2xl font-medium">
                <?php echo htmlspecialchars(APP_NAME); ?> helps readers browse the catalog, track borrowed titles, and keep their wallet and fines organized—with a calm, modern experience.
            </p>
            <div class="mt-10 flex flex-wrap items-center gap-4">
                <a href="<?php echo APP_ROUTE; ?>?page=books" class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#4F1BF1] px-6 py-3.5 text-sm font-bold text-white shadow-lg shadow-[#4F1BF1]/25 hover:brightness-110 transition-all font-['Manrope']">
                    <span class="material-symbols-outlined text-[20px]">travel_explore</span>
                    Browse catalog
                </a>
                <a href="<?php echo APP_ROUTE; ?>?page=register" class="inline-flex items-center justify-center gap-2 rounded-xl border-2 border-[#c9c4da]/60 bg-white px-6 py-3.5 text-sm font-bold text-[#1c1a25] hover:border-[#4F1BF1] hover:text-[#4F1BF1] transition-all font-['Manrope']">
                    Create account
                </a>
            </div>
        </div>
    </section>

    <section class="grid gap-8 md:grid-cols-3">
        <?php
        $features = [
            ['icon' => 'menu_book', 'title' => 'Rich catalog', 'text' => 'Search and explore titles with a fast, focused browsing experience.'],
            ['icon' => 'bookmark_added', 'title' => 'Loans & history', 'text' => 'See what you have checked out and manage your reading in one dashboard.'],
            ['icon' => 'account_balance_wallet', 'title' => 'Wallet & fines', 'text' => 'Transparent balances and fine tracking so nothing catches you off guard.'],
        ];
        foreach ($features as $f) :
        ?>
            <div class="rounded-2xl border border-[#c9c4da]/35 bg-white/80 p-8 shadow-sm backdrop-blur-sm">
                <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-xl bg-[#4F1BF1]/10 text-[#4F1BF1]">
                    <span class="material-symbols-outlined text-[28px]" style="font-variation-settings: 'FILL' 1;"><?php echo htmlspecialchars($f['icon']); ?></span>
                </div>
                <h2 class="text-lg font-bold text-[#1c1a25] font-['Manrope']"><?php echo htmlspecialchars($f['title']); ?></h2>
                <p class="mt-2 text-sm text-[#474557] leading-relaxed"><?php echo htmlspecialchars($f['text']); ?></p>
            </div>
        <?php endforeach; ?>
    </section>

    <section class="rounded-3xl border border-[#c9c4da]/40 bg-[#1c1a25] px-8 py-14 text-center sm:px-12">
        <h2 class="text-2xl font-extrabold text-white sm:text-3xl font-['Manrope']">Ready to get started?</h2>
        <p class="mx-auto mt-3 max-w-xl text-sm text-white/75">Sign in to access your dashboard, or register to join the library.</p>
        <div class="mt-8 flex flex-wrap justify-center gap-4">
            <a href="<?php echo APP_ROUTE; ?>?page=login" class="inline-flex items-center justify-center rounded-xl bg-white px-6 py-3 text-sm font-bold text-[#1c1a25] hover:bg-[#f7f1ff] transition-colors font-['Manrope']">Sign in</a>
            <a href="<?php echo APP_ROUTE; ?>?page=register" class="inline-flex items-center justify-center rounded-xl border-2 border-white/30 px-6 py-3 text-sm font-bold text-white hover:bg-white/10 transition-colors font-['Manrope']">Register</a>
        </div>
    </section>
</div>
