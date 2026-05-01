<script id="tailwind-wallet-lumina">
tailwind.config = {
    darkMode: "class",
    theme: {
        extend: {
            colors: {
                "on-primary-fixed": "#190064",
                "tertiary-fixed": "#ffdbd1",
                "on-primary": "#ffffff",
                "on-error": "#ffffff",
                "on-secondary-fixed-variant": "#3f4563",
                "secondary-fixed-dim": "#bfc5e8",
                "on-surface": "#1c1a25",
                "primary-container": "#4f1bf1",
                "tertiary-container": "#9c2a00",
                "tertiary": "#741d00",
                "primary": "#3800bf",
                "on-primary-fixed-variant": "#4200da",
                "surface-dim": "#ddd8e7",
                "on-primary-container": "#cac1ff",
                "secondary": "#575d7c",
                "on-tertiary-fixed-variant": "#862300",
                "primary-fixed-dim": "#c8bfff",
                "outline": "#787588",
                "on-tertiary-container": "#ffb6a1",
                "surface-container-lowest": "#ffffff",
                "surface-tint": "#5a30fb",
                "inverse-on-surface": "#f4eefe",
                "on-secondary": "#ffffff",
                "outline-variant": "#c9c4da",
                "error-container": "#ffdad6",
                "on-background": "#1c1a25",
                "on-tertiary": "#ffffff",
                "surface-bright": "#fdf8ff",
                "on-error-container": "#93000a",
                "inverse-primary": "#c8bfff",
                "on-secondary-container": "#595f7e",
                "surface-container-high": "#ebe6f5",
                "surface-container-low": "#f7f1ff",
                "surface-container-highest": "#e5e0f0",
                "error": "#ba1a1a",
                "surface": "#fdf8ff",
                "on-tertiary-fixed": "#3a0a00",
                "background": "#fdf8ff",
                "tertiary-fixed-dim": "#ffb59f",
                "on-surface-variant": "#474557",
                "inverse-surface": "#312f3a",
                "surface-variant": "#e5e0f0",
                "on-secondary-fixed": "#131a35",
                "primary-fixed": "#e5deff",
                "secondary-fixed": "#dde1ff",
                "surface-container": "#f1ebfb",
                "secondary-container": "#d6dbff"
            },
            borderRadius: {
                "DEFAULT": "0.125rem",
                "lg": "0.5rem",
                "xl": "0.75rem",
                "full": "9999px"
            },
            fontFamily: {
                "headline": ["Manrope"],
                "display": ["Manrope"],
                "body": ["Inter"],
                "label": ["Inter"]
            }
        }
    }
};
</script>
<style>
    .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
    body { font-family: 'Inter', system-ui, sans-serif; }
    h1, h2, h3, .brand-logo { font-family: 'Manrope', system-ui, sans-serif; }
</style>

<div class="w-full">

    <!-- ── Wallet Overview ──────────────────────────────────────────────── -->
    <section class="mb-16">
        <div class="relative overflow-hidden bg-primary rounded-xl p-10 md:p-12 text-on-primary shadow-2xl flex flex-col md:flex-row justify-between items-center">
            <!-- decorative rings -->
            <div class="absolute top-0 right-0 w-1/2 h-full opacity-10 pointer-events-none">
                <svg fill="none" viewBox="0 0 400 400" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="200" cy="200" r="180" stroke="white" stroke-width="2"></circle>
                    <circle cx="200" cy="200" r="140" stroke="white" stroke-width="1"></circle>
                    <circle cx="200" cy="200" r="100" stroke="white" stroke-width="0.5"></circle>
                </svg>
            </div>

            <div class="z-10 text-center md:text-left mb-8 md:mb-0">
                <span class="text-primary-fixed-dim font-medium tracking-widest uppercase text-xs mb-2 block">
                    Current Balance
                </span>
                <h1 class="text-6xl md:text-7xl font-extrabold tracking-tighter mb-4"
                    id="walletBalanceHero">$0.00</h1>
                <p class="text-on-primary-container text-opacity-80 max-w-md">
                    Your funds are ready for your next archival discovery or to settle any pending dues.
                </p>
            </div>

            <div class="z-10 flex flex-col sm:flex-row gap-3">
                <button id="walletTopUpBtn"
                        class="bg-primary-container text-on-primary px-8 py-4 rounded-lg font-bold text-lg
                               hover:brightness-110 active:scale-95 transition-all shadow-lg flex items-center gap-3">
                    <span class="material-symbols-outlined">add_circle</span>
                    Top Up Wallet
                </button>
            </div>
        </div>
    </section>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">

        <!-- ── Payment Methods ────────────────────────────────────────── -->
        <div class="lg:col-span-1 space-y-8">
            <h2 class="text-2xl font-bold tracking-tight">Linked Payment Methods</h2>

            <div class="space-y-4">

                <!-- Stripe -->
                <div class="bg-surface-container-low p-6 rounded-xl flex items-center justify-between
                            hover:bg-surface-container transition-colors group">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-lg bg-white flex items-center justify-center shadow-sm p-2">
                            <span class="material-symbols-outlined text-primary" style="font-size:26px">credit_card</span>
                        </div>
                        <div>
                            <p class="font-bold text-on-surface">Stripe</p>
                            <p class="text-xs text-on-surface-variant">Card payments</p>
                        </div>
                    </div>
                    <span class="text-xs font-semibold px-3 py-1 rounded-full bg-secondary-container text-on-secondary-container
                                 opacity-0 group-hover:opacity-100 transition-opacity">Test mode</span>
                </div>
            </div>
        </div>

        <!-- ── Recent Transactions ────────────────────────────────────── -->
        <div class="lg:col-span-2 space-y-8">
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold tracking-tight">Recent Transactions</h2>
                <button id="walletDownloadStatementBtn" class="text-primary font-semibold hover:underline">
                    Download Statement
                </button>
            </div>

            <div class="bg-surface-container-low rounded-xl overflow-hidden">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-surface-container-high border-b border-surface-container-highest">
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-on-surface-variant">Date</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-on-surface-variant">Description</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-on-surface-variant text-right">Amount</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-on-surface-variant">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-container" id="walletTxBody">
                        <tr>
                            <td class="px-6 py-6 text-sm text-on-surface-variant" colspan="4">Loading transactions…</td>
                        </tr>
                    </tbody>
                </table>

                <div class="p-6 bg-surface-container-low flex justify-center">
                    <button id="walletLoadMoreBtn"
                            class="text-sm font-bold text-primary flex items-center gap-2 transition-opacity">
                        View Older Transactions
                        <span class="material-symbols-outlined text-sm">keyboard_arrow_down</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ── Top Up Modal ──────────────────────────────────────────────────────── -->
<dialog id="walletTopUpModal" class="modal">
    <div class="modal-box max-w-lg bg-white">

        <h3 class="font-bold text-lg mb-4 flex items-center gap-2">
            <span class="material-symbols-outlined text-primary">account_balance_wallet</span>
            Top Up Wallet
        </h3>

        <form id="walletTopUpForm" class="space-y-4">

            <!-- Amount -->
            <div>
                <label class="block text-sm font-semibold text-on-surface-variant mb-1">Amount (USD)</label>
                <input name="amount" type="number" min="1" max="1000" step="0.01" required
                       class="w-full px-4 py-3 rounded-lg border border-outline-variant/60
                              focus:border-primary focus:ring-4 focus:ring-primary/10"
                       placeholder="Enter amount (max $1,000.00)" />
            </div>

            <!-- Card input -->
            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-semibold text-on-surface-variant mb-1">Card number</label>
                    <div class="relative">
                        <div id="walletStripeCardNumber"
                             class="w-full px-4 pr-12 py-3 rounded-lg border border-outline-variant/60 bg-white"></div>
                        <span id="walletCardBrandIcon"
                              class="absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant pointer-events-none">
                            <i class="fa-regular fa-credit-card text-lg"></i>
                        </span>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold text-on-surface-variant mb-1">Expiry</label>
                        <div id="walletStripeCardExpiry"
                             class="w-full px-4 py-3 rounded-lg border border-outline-variant/60 bg-white"></div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-on-surface-variant mb-1">CVC</label>
                        <div id="walletStripeCardCvc"
                             class="w-full px-4 py-3 rounded-lg border border-outline-variant/60 bg-white"></div>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-on-surface-variant mb-1">ZIP / Postal code</label>
                    <div id="walletStripePostal"
                         class="w-full px-4 py-3 rounded-lg border border-outline-variant/60 bg-white"></div>
                </div>
                <p class="text-xs text-on-surface-variant">
                    Test card: <code>4242 4242 4242 4242</code> — any future expiry — any CVC — any ZIP.
                </p>
            </div>

            <!-- Info banner -->
            <div id="walletGatewayInfo"
                 class="hidden bg-secondary-container text-on-secondary-container rounded-lg px-4 py-3 text-sm flex items-start gap-2">
                <span class="material-symbols-outlined text-base mt-0.5">info</span>
                <span id="walletGatewayInfoText">
                    You will pay using Stripe test mode. No real money is charged.
                </span>
            </div>

            <!-- Actions -->
            <div class="flex justify-end gap-2 pt-3">
                <button type="button" class="btn btn-ghost"
                        onclick="document.getElementById('walletTopUpModal').close()">
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary" id="walletTopUpSubmitBtn">
                    Pay with card
                </button>
            </div>

        </form>
    </div>
    <form method="dialog" class="modal-backdrop"><button>close</button></form>
</dialog>

<script>
window.STRIPE_PUBLISHABLE_KEY = <?php echo json_encode(defined('STRIPE_PUBLISHABLE_KEY') ? STRIPE_PUBLISHABLE_KEY : ''); ?>;
</script>
<script src="https://js.stripe.com/v3/"></script>
<script src="<?php echo APP_URL; ?>/public/js/wallet.js"></script>