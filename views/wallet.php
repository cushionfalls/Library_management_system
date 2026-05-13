<style>
    .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
    body { font-family: 'Inter', system-ui, sans-serif; }
    h1, h2, h3, .brand-logo { font-family: 'Manrope', system-ui, sans-serif; }
    /* Top-up modal: Stripe mount hosts must NOT be display:flex — it shrinks the
       iframe wrapper and is a known cause of invisible typed text in Card Elements. */
    #walletTopUpModal #walletStripeCardNumber,
    #walletTopUpModal #walletStripeCardExpiry,
    #walletTopUpModal #walletStripeCardCvc,
    #walletTopUpModal #walletStripePostal {
        box-sizing: border-box;
        width: 100%;
        min-height: 3.25rem;
        display: block;
        padding: 0.75rem 1rem;
        background: white !important;
        color: #1c1a25 !important;
        border-radius: 12px;
        border: 1.5px solid #cbd5e1;
        transition: border-color 0.2s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        font-size: 16px;
        line-height: 1.5;
        overflow: visible;
    }
    #walletTopUpModal .StripeElement iframe {
        width: 100% !important;
        min-height: 1.5rem;
    }
    #walletStripeCardNumber.StripeElement--focus,
    #walletStripeCardExpiry.StripeElement--focus,
    #walletStripeCardCvc.StripeElement--focus,
    #walletStripePostal.StripeElement--focus {
        border-color: #3800bf;
        box-shadow: 0 0 0 4px rgba(56, 0, 191, 0.12), 0 4px 12px rgba(0,0,0,0.05);
        background: white !important;
    }
    #walletTopUpModal .StripeElement:hover:not(.StripeElement--focus) {
        border-color: #94a3b8;
        background: #fdfbff !important;
    }
    #walletTopUpModal .wallet-topup-preset.ring-preset {
        background: #3800bf !important;
        color: white !important;
        border-color: #3800bf !important;
        box-shadow: 0 4px 12px rgba(56, 0, 191, 0.25);
    }
    .wallet-topup-preset {
        transition: all 0.2s ease;
    }
    .wallet-topup-preset:hover:not(.ring-preset) {
        background: #f1ebfb !important;
        border-color: #3800bf/30 !important;
    }
    .balance-gradient {
        background: linear-gradient(135deg, #3800bf 0%, #4f1bf1 50%, #7c3aed 100%);
    }
    @keyframes fadeInRow {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .tx-row { animation: fadeInRow 0.4s ease forwards; }
</style>

<div class="w-full">

    <!-- ── Wallet Overview ──────────────────────────────────────────────── -->
    <section class="mb-16">
        <div class="relative overflow-hidden balance-gradient rounded-3xl p-10 md:p-14 text-white shadow-[0_20px_50px_rgba(56,0,191,0.3)] flex flex-col md:flex-row justify-between items-center border border-white/10">
            <!-- decorative rings -->
            <div class="absolute top-0 right-0 w-1/2 h-full opacity-20 pointer-events-none">
                <svg fill="none" viewBox="0 0 400 400" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="200" cy="200" r="180" stroke="white" stroke-width="2" stroke-dasharray="10 5"></circle>
                    <circle cx="200" cy="200" r="140" stroke="white" stroke-width="1"></circle>
                    <circle cx="200" cy="200" r="100" stroke="white" stroke-width="0.5" stroke-dasharray="2 2"></circle>
                </svg>
            </div>
            
            <div class="absolute -left-10 -bottom-10 w-40 h-40 bg-white/10 rounded-full blur-3xl"></div>
            <div class="absolute -right-10 -top-10 w-60 h-60 bg-purple-400/20 rounded-full blur-3xl"></div>

            <div class="z-10 text-center md:text-left mb-8 md:mb-0">
                <div class="inline-flex items-center gap-2 bg-white/10 backdrop-blur-md px-4 py-1.5 rounded-full border border-white/20 mb-6">
                    <span class="material-symbols-outlined text-[16px] text-white/90">verified_user</span>
                    <span class="text-white font-bold tracking-widest uppercase text-[10px] drop-shadow-sm">
                        Secure Digital Vault
                    </span>
                </div>
                <h1 class="text-7xl md:text-8xl font-extrabold tracking-tighter mb-4 drop-shadow-sm text-white"
                    id="walletBalanceHero">$0.00</h1>
                <p class="text-white/80 max-w-md text-lg font-medium leading-relaxed">
                    Your financial hub for seamless reading experiences and premium memberships.
                </p>
            </div>

            <div class="z-10 flex flex-col sm:flex-row gap-4">
                <button id="walletTopUpBtn"
                        class="bg-surface-container-lowest text-primary px-10 py-5 rounded-2xl font-black text-lg
                               hover:shadow-[0_10px_25px_rgba(255,255,255,0.4)] hover:-translate-y-1 active:scale-95 transition-all flex items-center gap-3">
                    <span class="material-symbols-outlined font-bold">add_card</span>
                    Add Funds
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
                        <div class="w-12 h-12 rounded-lg bg-surface-container-lowest flex items-center justify-center shadow-sm p-2">
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
                        <tr class="bg-surface-container-high/50 border-b border-surface-container">
                            <th class="px-6 py-4 text-xs font-black uppercase tracking-widest text-on-surface">Date</th>
                            <th class="px-6 py-4 text-xs font-black uppercase tracking-widest text-on-surface">Transaction</th>
                            <th class="px-6 py-4 text-xs font-black uppercase tracking-widest text-on-surface text-right">Amount</th>
                            <th class="px-6 py-4 text-xs font-black uppercase tracking-widest text-on-surface text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-container" id="walletTxBody">
                        <tr>
                            <td class="px-6 py-6 text-sm text-on-surface" colspan="4">Loading transactions…</td>
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
    <div class="modal-box w-full max-w-[800px] h-[950px] max-h-[95vh] p-0 overflow-hidden rounded-2xl border border-outline-variant/25 bg-surface-bright shadow-2xl font-body flex flex-col">

        <!-- Header -->
        <div class="relative overflow-hidden bg-gradient-to-br from-primary via-[#4720c4] to-primary-container px-6 sm:px-8 pt-7 pb-8 text-white">
            <div class="pointer-events-none absolute -right-16 -top-20 h-48 w-48 rounded-full bg-white/10 blur-2xl"></div>
            <div class="pointer-events-none absolute -bottom-24 -left-8 h-40 w-40 rounded-full bg-primary-fixed-dim/20 blur-3xl"></div>

            <button type="button"
                    class="absolute right-4 top-4 flex h-10 w-10 items-center justify-center rounded-full bg-white/10 text-white backdrop-blur-sm transition-colors hover:bg-white/20 focus:outline-none focus-visible:ring-2 focus-visible:ring-white/50 focus-visible:ring-offset-2 focus-visible:ring-offset-primary z-10"
                    onclick="document.getElementById('walletTopUpModal').close()"
                    aria-label="Close">
                <span class="material-symbols-outlined text-[22px]">close</span>
            </button>

            <div class="relative z-[1] flex flex-col gap-5 sm:flex-row sm:items-start">
                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-white/15 shadow-inner ring-1 ring-white/20 backdrop-blur-sm">
                    <span class="material-symbols-outlined text-[30px] text-white">account_balance_wallet</span>
                </div>
                <div class="min-w-0 flex-1 pr-10 sm:pr-12">
                    <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-white/85">Secure checkout</p>
                    <h3 class="mt-2 font-display text-2xl font-extrabold tracking-tight text-white sm:text-[1.75rem] leading-tight drop-shadow-sm">
                        Top up your wallet
                    </h3>
                    <p class="mt-2 max-w-md text-sm leading-relaxed text-white/90">
                        Add USD to your balance for digital books and membership. Your card is processed by Stripe — we never store full card numbers.
                    </p>
                </div>
            </div>
        </div>

        <form id="walletTopUpForm" class="flex-1 flex flex-col min-h-0">
            <input type="hidden" name="csrf_token" value="<?php echo (new Session())->generateCSRFToken(); ?>">

            <!-- Scrollable Body -->
            <div class="flex-1 overflow-y-auto space-y-6 px-6 sm:px-8 py-7 sm:py-8">

            <!-- Amount -->
            <div>
                <label for="walletTopUpAmountInput" class="flex items-center justify-between gap-2 text-sm font-bold text-on-surface">
                    <span>Amount</span>
                    <span class="text-xs font-semibold uppercase tracking-wide text-primary">USD</span>
                </label>
                <div class="relative mt-2">
                    <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-xl font-bold text-primary" aria-hidden="true">$</span>
                    <input id="walletTopUpAmountInput"
                           name="amount"
                           type="number"
                           min="1"
                           max="1000"
                           step="0.01"
                           required
                           class="wallet-topup-amount-input w-full rounded-xl border-1.5 border-outline-variant/45 bg-surface-container-lowest py-3 pl-10 pr-4 text-2xl font-black text-on-surface shadow-sm transition-all
                                  placeholder:text-slate-400 placeholder:font-medium
                                  focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10"
                           style="color: #1c1a25 !important;"
                           placeholder="0.00" />
                </div>
                <p class="mt-2 text-xs text-on-surface-variant">
                    Minimum <strong class="font-semibold text-on-surface">$1.00</strong> · Maximum <strong class="font-semibold text-on-surface">$1,000.00</strong>
                </p>

                <div class="mt-4 flex flex-wrap gap-2" role="group" aria-label="Quick amounts">
                    <button type="button" class="wallet-topup-preset rounded-full border border-outline-variant/55 bg-surface-container px-4 py-2 text-sm font-bold text-on-surface shadow-sm transition-all hover:border-primary/40 hover:bg-primary-fixed hover:text-on-primary-fixed-variant active:scale-[0.97]" data-dollar="10">$10</button>
                    <button type="button" class="wallet-topup-preset rounded-full border border-outline-variant/55 bg-surface-container px-4 py-2 text-sm font-bold text-on-surface shadow-sm transition-all hover:border-primary/40 hover:bg-primary-fixed hover:text-on-primary-fixed-variant active:scale-[0.97]" data-dollar="25">$25</button>
                    <button type="button" class="wallet-topup-preset rounded-full border border-outline-variant/55 bg-surface-container px-4 py-2 text-sm font-bold text-on-surface shadow-sm transition-all hover:border-primary/40 hover:bg-primary-fixed hover:text-on-primary-fixed-variant active:scale-[0.97]" data-dollar="50">$50</button>
                    <button type="button" class="wallet-topup-preset rounded-full border border-outline-variant/55 bg-surface-container px-4 py-2 text-sm font-bold text-on-surface shadow-sm transition-all hover:border-primary/40 hover:bg-primary-fixed hover:text-on-primary-fixed-variant active:scale-[0.97]" data-dollar="100">$100</button>
                    <button type="button" class="wallet-topup-preset rounded-full border border-outline-variant/55 bg-surface-container px-4 py-2 text-sm font-bold text-on-surface shadow-sm transition-all hover:border-primary/40 hover:bg-primary-fixed hover:text-on-primary-fixed-variant active:scale-[0.97]" data-dollar="250">$250</button>
                </div>
            </div>

            <!-- Card inputs -->
            <div class="rounded-2xl border border-outline-variant/40 bg-gradient-to-b from-surface-container-low to-surface-bright p-5 shadow-sm">
                <div class="mb-4 flex flex-wrap items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary">
                        <span class="material-symbols-outlined text-[22px]">credit_card</span>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="font-display text-base font-extrabold text-on-surface">Payment method</p>
                        <p class="text-xs text-on-surface-variant">Debit or credit card via Stripe</p>
                    </div>
                    <span class="inline-flex items-center gap-1 rounded-full bg-secondary-container/80 px-3 py-1 text-[11px] font-bold uppercase tracking-wide text-on-secondary-container">
                        <span class="material-symbols-outlined text-[14px]">lock</span>
                        Encrypted
                    </span>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-on-surface-variant">Card number</label>
                        <div class="relative group">
                            <div id="walletStripeCardNumber"
                                 class="w-full transition-all duration-200"></div>
                            <span id="walletCardBrandIcon"
                                  class="absolute right-4 top-1/2 -translate-y-1/2 text-on-surface-variant pointer-events-none opacity-40 group-focus-within:opacity-100 transition-opacity">
                                <i class="fa-regular fa-credit-card text-lg"></i>
                            </span>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">Expiry</label>
                            <div id="walletStripeCardExpiry"
                                 class="w-full"></div>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">CVC</label>
                            <div id="walletStripeCardCvc"
                                 class="w-full"></div>
                        </div>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">Billing ZIP / postal code</label>
                        <div id="walletStripePostal"
                             class="w-full"></div>
                    </div>

                    <div class="rounded-xl bg-primary/5 px-4 py-3 text-[11px] leading-relaxed text-on-surface-variant border border-primary/10">
                        <span class="font-bold text-primary uppercase tracking-tighter mr-1">Test mode:</span>
                        Use <code class="rounded bg-surface-container-lowest px-1.5 py-0.5 font-mono font-bold text-primary shadow-sm border border-primary/20">4242 4242 4242 4242</code>
                        <span class="mx-1">·</span> Any future date <span class="mx-1">·</span> Any CVC
                    </div>
                </div>
            </div>

            <!-- Info banner (toggled via JS) -->
            <div id="walletGatewayInfo"
                 class="hidden items-start gap-3 rounded-xl border border-primary-fixed-dim bg-primary-fixed px-4 py-3.5 text-sm text-on-primary-fixed-variant shadow-sm">
                <span class="material-symbols-outlined mt-0.5 shrink-0 text-primary text-xl">verified_user</span>
                <span id="walletGatewayInfoText" class="leading-snug pt-0.5">
                    You will pay using Stripe test mode. No real money is charged.
                </span>
            </div>

            </div>

            <!-- Actions (Sticky Footer) -->
            <div class="flex flex-col-reverse gap-3 border-t border-outline-variant/15 bg-surface-bright p-6 sm:flex-row sm:justify-end sm:gap-4 sticky bottom-0 z-10 shadow-[0_-4px_12px_rgba(0,0,0,0.02)]">
                <button type="button"
                        class="inline-flex justify-center rounded-xl px-6 py-3.5 text-sm font-bold text-on-surface-variant transition-colors hover:bg-surface-container-high focus:outline-none focus-visible:ring-2 focus-visible:ring-outline-variant focus-visible:ring-offset-2"
                        onclick="document.getElementById('walletTopUpModal').close()">
                    Cancel
                </button>
                <button type="submit"
                        id="walletTopUpSubmitBtn"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-8 py-3.5 text-sm font-extrabold text-on-primary shadow-lg shadow-primary/25 transition-[transform,filter] hover:brightness-110 hover:shadow-xl hover:shadow-primary/20 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-55 disabled:active:scale-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2">
                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">payments</span>
                    <span id="walletTopUpSubmitLabel">Pay with card</span>
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