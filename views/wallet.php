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
    <!-- Wallet Overview -->
    <section class="mb-16">
        <div class="relative overflow-hidden bg-primary rounded-xl p-10 md:p-12 text-on-primary shadow-2xl flex flex-col md:flex-row justify-between items-center">
            <div class="absolute top-0 right-0 w-1/2 h-full opacity-10 pointer-events-none">
                <svg fill="none" viewBox="0 0 400 400" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="200" cy="200" r="180" stroke="white" stroke-width="2"></circle>
                    <circle cx="200" cy="200" r="140" stroke="white" stroke-width="1"></circle>
                    <circle cx="200" cy="200" r="100" stroke="white" stroke-width="0.5"></circle>
                </svg>
            </div>

            <div class="z-10 text-center md:text-left mb-8 md:mb-0">
                <span class="text-primary-fixed-dim font-medium tracking-widest uppercase text-xs mb-2 block">Current Balance</span>
                <h1 class="text-6xl md:text-7xl font-extrabold tracking-tighter mb-4" id="walletBalanceHero">₹0</h1>
                <p class="text-on-primary-container text-opacity-80 max-w-md">Your funds are ready for your next archival discovery or to settle any pending dues.</p>
            </div>

            <div class="z-10 flex flex-col sm:flex-row gap-3">
                <button id="walletTopUpBtn" class="bg-primary-container text-on-primary px-8 py-4 rounded-lg font-bold text-lg hover:brightness-110 active:scale-95 transition-all shadow-lg flex items-center gap-3">
                    <span class="material-symbols-outlined">add_circle</span>
                    Top Up Wallet
                </button>
            </div>
        </div>
    </section>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
        <!-- Payment Methods -->
        <div class="lg:col-span-1 space-y-8">
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold tracking-tight">Linked Payment Methods</h2>
            </div>

            <div class="space-y-4">
                <div class="bg-surface-container-low p-6 rounded-xl flex items-center justify-between hover:bg-surface-container transition-colors group">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-lg bg-white flex items-center justify-center shadow-sm p-2">
                            <img alt="" class="w-full h-full object-contain" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAM_BqhLsZTGpZG-Ge28e9Z4_4O1NLsetxl9JKuXXVIm5md9FE45XfwFoT7DZUr6fP1eLfMr5xlreT4ODKXriKJ_ESCh7gEFlRI-1WaWK3zeXsYqgCOkwx5-5U02MQpus0YCfn6TCwO_30aEGPyrW3cYit5kLYKIDSOunnYy3M1L0kbHOGQSLKe0wI2pYrC4fn-w4gzZrP8xaVgFrj1Bdx17KRzHEnxrw50IO-LXQ3LcLs7NmxgzhyjqaCcylxgDtokrC-RSidKBPo"/>
                        </div>
                        <div>
                            <p class="font-bold text-on-surface">eSewa</p>
                            <p class="text-xs text-on-surface-variant">Default</p>
                        </div>
                    </div>
                    <button class="text-primary font-semibold text-sm opacity-0 group-hover:opacity-100 transition-opacity" disabled>Manage</button>
                </div>

                <div class="bg-surface-container-low p-6 rounded-xl flex items-center justify-between hover:bg-surface-container transition-colors group">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-lg bg-white flex items-center justify-center shadow-sm p-2">
                            <img alt="" class="w-full h-full object-contain" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDoBbB_mumDHgEU9SBpSC41j9NsyA3TcSDWA--Uf-z7wYVkA4AuQhU5_ewfPstbxsdXFb_bWZzeh1EuqmTPgrNmMKh1aH6Wiib1YAkLmoaDj8Bzk_8ItJ4SZ5isd-5hAKqDYfnQXOrWUR60F3atCBl7XUcpHS3UbeVATWMS5GgUG4kvjlRrmHnep2U65Ul5sbkDb_cyJBSq4b_UbQdXz6AK1AHBMhQjbbQkk3EdViumuTqPKozG2M4NPM9o6PvQWI6uGDXt415uKyw"/>
                        </div>
                        <div>
                            <p class="font-bold text-on-surface">Khalti</p>
                            <p class="text-xs text-on-surface-variant">Secondary</p>
                        </div>
                    </div>
                    <button class="text-primary font-semibold text-sm opacity-0 group-hover:opacity-100 transition-opacity" disabled>Manage</button>
                </div>

                <div class="bg-surface-container-low p-6 rounded-xl flex items-center justify-between hover:bg-surface-container transition-colors group">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-lg bg-white flex items-center justify-center shadow-sm p-2">
                            <img alt="" class="w-full h-full object-contain" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAfzOI2yTPz5f9Ar6NzZd4_zokn0TuiE24LL7pDkvtoatob-gLcQKSC-obP61MCPQird7H42d7-szs6SVNWcJcNHP2VwLXpi0IGXyEcjXYt9lVnzjZPwp8So8kG74S7phgZ9GpdjMCRRhBsKOaQXXzRYJaSpDt33MzXlYD1zwCP4fdVcEkCFblAAawZMTY39J9wCmMKVSlr_5TWqnr-KTJ-tJJH34uEEq-zagHr4KqM-fVyJO6sUGmDvKCZfSTYCcPbQUOF4_aljI0"/>
                        </div>
                        <div>
                            <p class="font-bold text-on-surface">PhonePe</p>
                            <p class="text-xs text-on-surface-variant">Linked</p>
                        </div>
                    </div>
                    <button class="text-primary font-semibold text-sm opacity-0 group-hover:opacity-100 transition-opacity" disabled>Set Default</button>
                </div>

                <button class="w-full py-4 border-2 border-dashed border-outline-variant rounded-xl text-on-surface-variant hover:text-primary hover:border-primary transition-all flex items-center justify-center gap-2 font-medium" disabled>
                    <span class="material-symbols-outlined">add</span>
                    Add New Method
                </button>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="lg:col-span-2 space-y-8">
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold tracking-tight">Recent Transactions</h2>
                <button id="walletDownloadStatementBtn" class="text-primary font-semibold hover:underline">Download Statement</button>
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
                    <button id="walletLoadMoreBtn" class="text-sm font-bold text-primary flex items-center gap-2">
                        View Older Transactions
                        <span class="material-symbols-outlined text-sm">keyboard_arrow_down</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<dialog id="walletTopUpModal" class="modal">
    <div class="modal-box max-w-lg bg-white">
        <h3 class="font-bold text-lg mb-4 flex items-center gap-2">
            <span class="material-symbols-outlined text-primary">account_balance_wallet</span>
            Top Up Wallet
        </h3>
        <form id="walletTopUpForm" class="space-y-4">
            <div>
                <label class="block text-sm font-semibold text-on-surface-variant mb-1">Amount</label>
                <input name="amount" type="number" min="1" max="10000" step="1" required class="w-full px-4 py-3 rounded-lg border border-outline-variant/60 focus:border-primary focus:ring-4 focus:ring-primary/10" placeholder="Enter amount (max 10000)" />
            </div>
            <div>
                <label class="block text-sm font-semibold text-on-surface-variant mb-1">Payment Method</label>
                <select name="method" class="w-full px-4 py-3 rounded-lg border border-outline-variant/60 focus:border-primary focus:ring-4 focus:ring-primary/10">
                    <option value="ESEWA">eSewa</option>
                    <option value="KHALTI">Khalti</option>
                    <option value="PHONEPE">PhonePe</option>
                    <option value="OTHER">Other</option>
                </select>
            </div>
            <div id="walletOtpWrap" class="hidden">
                <label class="block text-sm font-semibold text-on-surface-variant mb-1">OTP</label>
                <input name="otp" type="text" inputmode="numeric" autocomplete="one-time-code" maxlength="6" class="w-full px-4 py-3 rounded-lg border border-outline-variant/60 focus:border-primary focus:ring-4 focus:ring-primary/10" placeholder="Enter 6-digit OTP" />
                <p class="text-xs text-on-surface-variant mt-2">We sent an OTP to your email. Enter it to confirm the top up.</p>
            </div>
            <div class="flex justify-end gap-2 pt-3">
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('walletTopUpModal').close()">Cancel</button>
                <button type="submit" class="btn btn-primary" id="walletTopUpSubmitBtn">Send OTP</button>
            </div>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop"><button>close</button></form>
</dialog>

<script src="<?php echo APP_URL; ?>/public/js/wallet.js"></script>

