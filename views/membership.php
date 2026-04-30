<script id="tailwind-membership-lumina">
tailwind.config = {
    darkMode: "class",
    theme: {
        extend: {
            colors: {
                "on-surface-variant": "#474557",
                "outline-variant": "#c9c4da",
                "outline": "#787588",
                "surface-container-lowest": "#ffffff",
                "inverse-on-surface": "#f4eefe",
                "on-tertiary-container": "#ffb6a1",
                "error-container": "#ffdad6",
                "on-background": "#1c1a25",
                "on-secondary-container": "#595f7e",
                "tertiary-fixed-dim": "#ffb59f",
                "background": "#fdf8ff",
                "tertiary": "#741d00",
                "surface-container": "#f1ebfb",
                "surface-bright": "#fdf8ff",
                "on-tertiary": "#ffffff",
                "on-primary-container": "#cac1ff",
                "on-primary": "#ffffff",
                "primary-container": "#4f1bf1",
                "on-tertiary-fixed": "#3a0a00",
                "surface-container-highest": "#e5e0f0",
                "inverse-surface": "#312f3a",
                "on-primary-fixed-variant": "#4200da",
                "on-surface": "#1c1a25",
                "secondary-fixed-dim": "#bfc5e8",
                "inverse-primary": "#c8bfff",
                "on-secondary-fixed-variant": "#3f4563",
                "surface-tint": "#5a30fb",
                "tertiary-container": "#9c2a00",
                "surface": "#fdf8ff",
                "secondary": "#575d7c",
                "on-secondary-fixed": "#131a35",
                "surface-container-high": "#ebe6f5",
                "on-tertiary-fixed-variant": "#862300",
                "primary-fixed-dim": "#c8bfff",
                "on-primary-fixed": "#190064",
                "tertiary-fixed": "#ffdbd1",
                "surface-variant": "#e5e0f0",
                "on-error-container": "#93000a",
                "surface-dim": "#ddd8e7",
                "surface-container-low": "#f7f1ff",
                "primary-fixed": "#e5deff",
                "secondary-container": "#d6dbff",
                "on-error": "#ffffff",
                "secondary-fixed": "#dde1ff",
                "on-secondary": "#ffffff",
                "primary": "#3800bf",
                "error": "#ba1a1a"
            },
            borderRadius: { "DEFAULT": "0.125rem", "lg": "0.25rem", "xl": "0.5rem", "full": "0.75rem" },
            fontFamily: {
                "headline": ["Manrope"],
                "display": ["Manrope"],
                "body": ["Inter"],
                "label": ["Inter"]
            }
        }
    }
}
</script>
<style>
    .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
    .ambient-shadow { box-shadow: 0 32px 64px -12px rgba(28, 26, 37, 0.06); }
    .gradient-button { background: linear-gradient(135deg, #3800bf 0%, #4f1bf1 100%); }
</style>

<div class="max-w-screen-2xl mx-auto px-0 md:px-0 py-2">
    <main class="px-0 md:px-0 py-6">
        <!-- Membership Status Section -->
        <section class="mb-12">
            <div class="bg-surface-container-low border border-outline-variant/30 rounded-xl p-6 md:p-8 flex flex-col md:flex-row items-center justify-between gap-6 ambient-shadow">
                <div class="flex items-center space-x-6">
                    <div class="h-16 w-16 rounded-full bg-primary-container/20 flex items-center justify-center">
                        <span class="material-symbols-outlined text-primary text-3xl" style="font-variation-settings: 'FILL' 1;">verified_user</span>
                    </div>
                    <div>
                        <h2 class="font-headline font-bold text-xl text-on-surface">My Membership Status</h2>
                        <p class="text-on-surface-variant font-medium">
                            Current Plan:
                            <span class="text-primary font-bold" id="membershipCurrentPlan">Not Activated</span>
                        </p>
                        <p class="text-sm text-outline mt-1" id="membershipNextBilling">Activate a plan to unlock benefits.</p>
                        <p class="text-sm text-outline mt-1">Wallet balance: <span class="font-bold text-on-surface" id="membershipWalletBalance">₹0</span></p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <button id="membershipViewHistoryBtn" class="px-6 py-2.5 rounded-lg border border-outline text-on-surface font-bold text-sm hover:bg-surface-container-high transition-all">
                        View History
                    </button>
                    <a href="<?php echo APP_ROUTE; ?>?page=wallet" class="px-6 py-2.5 rounded-lg bg-primary text-white font-bold text-sm hover:opacity-90 transition-all shadow-sm">
                        Top Up Wallet
                    </a>
                </div>
            </div>
        </section>

        <!-- Hero Section -->
        <section class="text-center mb-20">
            <h1 class="font-headline font-extrabold text-5xl md:text-7xl text-primary mb-6 tracking-tight">Choose Your Membership</h1>
            <p class="max-w-2xl mx-auto text-on-surface-variant text-lg md:text-xl font-medium leading-relaxed">
                Buy a membership using your wallet balance. Plans are activated instantly after payment.
            </p>
        </section>

        <!-- Pricing Cards Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-stretch" id="membershipPlansGrid">
            <!-- Filled by JS -->
            <div class="p-10 rounded-xl bg-surface-container-low ambient-shadow">
                <p class="text-on-surface-variant font-medium">Loading plans…</p>
            </div>
        </div>
    </main>
</div>

<dialog id="membershipHistoryModal" class="modal">
    <div class="modal-box max-w-3xl bg-white">
        <h3 class="font-bold text-lg mb-4 flex items-center gap-2">
            <span class="material-symbols-outlined text-primary">history</span>
            Membership History
        </h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                <tr class="bg-surface-container-high border-b border-surface-container-highest">
                    <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-on-surface-variant">Purchased</th>
                    <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-on-surface-variant">Plan</th>
                    <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-on-surface-variant text-right">Amount</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-surface-container" id="membershipHistoryBody">
                <tr>
                    <td class="px-6 py-6 text-sm text-on-surface-variant" colspan="3">Loading…</td>
                </tr>
                </tbody>
            </table>
        </div>
        <div class="flex justify-end gap-2 pt-5">
            <button type="button" class="btn btn-ghost" onclick="document.getElementById('membershipHistoryModal').close()">Close</button>
        </div>
    </div>
    <form method="dialog" class="modal-backdrop"><button>close</button></form>
</dialog>

<script src="<?php echo APP_URL; ?>/public/js/membership.js"></script>

