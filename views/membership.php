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
                        <p class="text-sm text-outline mt-1">Wallet balance: <span class="font-bold text-on-surface" id="membershipWalletBalance">$0.00</span></p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <button id="membershipRenewBtn" class="px-6 py-2.5 rounded-lg bg-tertiary-fixed text-on-tertiary-fixed font-bold text-sm hover:opacity-90 transition-all hidden">
                        Renew Membership
                    </button>
                    <button id="membershipDeactivateBtn" class="px-6 py-2.5 rounded-lg border border-error/40 text-error font-bold text-sm hover:bg-error-container transition-all hidden">
                        Deactivate Membership
                    </button>
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
    <div class="modal-box max-w-3xl bg-surface-container-lowest">
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

<dialog id="membershipConfirmModal" class="modal">
    <div class="modal-box max-w-sm bg-surface-container-lowest">
        <h3 class="font-bold text-lg mb-2">Confirm Purchase</h3>
        <p class="text-sm text-on-surface-variant mb-6" id="membershipConfirmText">Are you sure you want to purchase this membership?</p>
        <div class="flex justify-end gap-3">
            <button type="button" class="px-4 py-2 text-sm font-semibold text-on-surface-variant hover:bg-surface-container-high rounded-lg transition-colors" onclick="document.getElementById('membershipConfirmModal').close()">Cancel</button>
            <button type="button" id="membershipConfirmBtn" class="px-4 py-2 text-sm font-semibold bg-primary text-white hover:opacity-90 rounded-lg transition-opacity">Confirm</button>
        </div>
    </div>
    <form method="dialog" class="modal-backdrop"><button>close</button></form>
</dialog>

<dialog id="membershipDeactivateModal" class="modal">
    <div class="modal-box max-w-sm bg-surface-container-lowest">
        <h3 class="font-bold text-lg mb-2">Deactivate Membership</h3>
        <p class="text-sm text-on-surface-variant mb-6">Your membership access will end immediately. Are you sure you want to continue?</p>
        <div class="flex justify-end gap-3">
            <button type="button" class="px-4 py-2 text-sm font-semibold text-on-surface-variant hover:bg-surface-container-high rounded-lg transition-colors" onclick="document.getElementById('membershipDeactivateModal').close()">Cancel</button>
            <button type="button" id="membershipDeactivateConfirmBtn" class="px-4 py-2 text-sm font-semibold bg-error text-white hover:opacity-90 rounded-lg transition-opacity">Deactivate</button>
        </div>
    </div>
    <form method="dialog" class="modal-backdrop"><button>close</button></form>
</dialog>

<script>
window.MEMBERSHIP_API_URL = '<?php echo APP_URL; ?>/controllers/membership.php';
window.MEMBERSHIP_WALLET_URL = '<?php echo APP_ROUTE; ?>?page=wallet';
window.MEMBERSHIP_IS_VERIFIED = <?php echo ($session->isLoggedIn() && $session->isVerified()) ? 'true' : 'false'; ?>;
</script>
<script src="<?php echo APP_URL; ?>/public/js/membership.js"></script>

