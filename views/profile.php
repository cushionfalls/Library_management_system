<?php
$profileUser = $session->getUserData();
if (!$profileUser) {
    echo '<div class="alert alert-error">Unable to load profile details.</div>';
    return;
}

$csrfToken = $session->generateCSRFToken();
$displayName = trim((string)($profileUser['first_name'] ?? '') . ' ' . (string)($profileUser['last_name'] ?? ''));
$memberSince = !empty($profileUser['created_at']) ? date('M Y', strtotime((string)$profileUser['created_at'])) : 'N/A';
$walletBalance = number_format(((float)($profileUser['wallet'] ?? 0)) / 100, 2);
$dobValue = !empty($profileUser['dob']) ? date('Y-m-d', strtotime((string)$profileUser['dob'])) : '';
$phoneValue = (string)($profileUser['phone_number'] ?? '');
$avatarPath = !empty($profileUser['profile_image']) ? APP_URL . '/public/uploads/profiles/' . basename((string)$profileUser['profile_image']) : '';
$isActive = (int)($profileUser['is_active'] ?? 0) === 1;
$statusLabel = $isActive ? 'Active' : 'Inactive';
$statusDot = $isActive ? 'bg-emerald-400' : 'bg-red-400';
$loyaltyPoints = max(0, (int)floor(((float)($profileUser['wallet'] ?? 0)) / 100 * 10));

require_once __DIR__ . '/../classes/Membership.php';
$membershipModel = new Membership();
$activeMem = $membershipModel->getActiveMembership($profileUser['id']);
$memPlan = $activeMem ? $activeMem['plan_name'] : 'None';
$daysLeft = 0;
if ($activeMem && !empty($activeMem['ends_at'])) {
    $now = new DateTime();
    $end = new DateTime($activeMem['ends_at']);
    if ($end > $now) {
        $interval = $now->diff($end);
        $daysLeft = $interval->days;
    }
}
?>

<style>
    .profile-input {
        width: 100%;
        background: rgb(var(--color-surface-container-high));
        border: 1px solid rgb(var(--color-outline-variant) / 0.3);
        color: rgb(var(--color-on-surface));
        border-radius: 0.5rem;
        padding: 0.75rem 1rem;
        transition: box-shadow 0.2s ease, background-color 0.2s ease;
    }
    .profile-input:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgb(var(--color-primary) / 0.25);
        border-color: rgb(var(--color-primary) / 0.5);
    }
    .profile-label {
        display: block;
        font-size: 0.75rem;
        font-weight: 700;
        color: rgb(var(--color-on-surface-variant));
        text-transform: uppercase;
        letter-spacing: 0.08em;
        margin-left: 0.25rem;
        margin-bottom: 0.5rem;
    }
    .profile-card {
        background: rgb(var(--color-surface-container-low));
        color: rgb(var(--color-on-surface));
        border: 1px solid rgb(var(--color-outline-variant) / 0.15);
        border-radius: 0.75rem;
        padding: 2rem;
    }
</style>

<div class="max-w-[1440px] mx-auto">
    <div class="flex flex-col md:flex-row items-end gap-8 mb-12">
        <div class="relative group">
            <div class="h-40 w-40 rounded-xl overflow-hidden bg-surface-container shadow-2xl">
                <?php if ($avatarPath !== ''): ?>
                    <img id="profileImagePreview" alt="User Profile Image" class="w-full h-full object-cover" src="<?php echo htmlspecialchars($avatarPath); ?>" />
                <?php else: ?>
                    <div id="profileImagePreviewFallback" class="w-full h-full flex items-center justify-center text-5xl text-primary">
                        <i class="fa-solid fa-user"></i>
                    </div>
                    <img id="profileImagePreview" alt="User Profile Image" class="w-full h-full object-cover hidden" src="" />
                <?php endif; ?>
            </div>
            <label class="absolute -bottom-4 -right-4 h-12 w-12 bg-primary rounded-full flex items-center justify-center text-on-primary cursor-pointer hover:scale-110 transition-transform shadow-lg" for="profileImagePicker">
                <i class="fa-solid fa-camera"></i>
            </label>
        </div>
        <div class="flex-1 pb-2">
            <div class="flex items-center gap-4 mb-2">
                <span class="text-on-surface-variant text-sm font-medium">Member since <?php echo htmlspecialchars($memberSince); ?></span>
            </div>
            <h1 class="font-['Manrope'] font-extrabold text-5xl text-on-surface tracking-tight"><?php echo htmlspecialchars($displayName ?: 'Library Member'); ?></h1>
            <p class="text-on-surface-variant mt-2 flex items-center gap-2">
                <i class="fa-solid fa-envelope text-sm"></i>
                <?php echo htmlspecialchars((string)($profileUser['email'] ?? '')); ?>
            </p>
        </div>
        <div class="pb-2">
            <div class="bg-surface-container-low p-6 rounded-xl min-w-[220px] shadow-sm border border-outline-variant/15">
                <p class="text-xs font-bold text-on-surface-variant uppercase tracking-widest mb-1">Wallet Balance</p>
                <div class="flex items-baseline gap-1">
                    <span class="text-3xl font-['Manrope'] font-extrabold text-primary">$<?php echo htmlspecialchars($walletBalance); ?></span>
                    <span class="text-xs text-on-surface-variant font-medium">USD</span>
                </div>
                <a href="<?php echo APP_ROUTE; ?>?page=wallet" class="mt-4 text-primary font-bold text-xs inline-flex items-center gap-1 hover:gap-2 transition-all">
                    Top up wallet <i class="fa-solid fa-arrow-right text-xs"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
        <div class="lg:col-span-2 space-y-10">
            <section class="profile-card">
                <div class="flex items-center justify-between mb-8">
                    <h2 class="font-['Manrope'] font-bold text-2xl">Personal Information</h2>
                    <i class="fa-solid fa-id-card text-on-surface-variant"></i>
                </div>
                <form id="profileInfoForm" class="space-y-6">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>" />
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div><label class="profile-label">First Name</label><input class="profile-input" type="text" name="first_name" value="<?php echo htmlspecialchars((string)($profileUser['first_name'] ?? '')); ?>" required /></div>
                        <div><label class="profile-label">Last Name</label><input class="profile-input" type="text" name="last_name" value="<?php echo htmlspecialchars((string)($profileUser['last_name'] ?? '')); ?>" required /></div>
                    </div>
                    <div><label class="profile-label">Email Address</label><input class="profile-input opacity-60 cursor-not-allowed" type="email" value="<?php echo htmlspecialchars((string)($profileUser['email'] ?? '')); ?>" readonly /></div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div><label class="profile-label">Date of Birth</label><input class="profile-input" type="date" name="dob" value="<?php echo htmlspecialchars($dobValue); ?>" /></div>
                        <div><label class="profile-label">Phone Number</label><input class="profile-input" type="text" name="phone_number" value="<?php echo htmlspecialchars($phoneValue); ?>" placeholder="+977 - 00000-00000" /></div>
                    </div>
                    <button class="bg-primary text-on-primary px-8 py-3 rounded-lg font-bold text-sm shadow-lg hover:opacity-90 transition-opacity" type="submit">Save Changes</button>
                </form>
            </section>

            <section class="profile-card">
                <div class="flex items-center justify-between mb-8">
                    <h2 class="font-['Manrope'] font-bold text-2xl">Security &amp; Authentication</h2>
                    <i class="fa-solid fa-lock text-on-surface-variant"></i>
                </div>
                <form id="profilePasswordForm" class="space-y-6">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>" />
                    <div><label class="profile-label">Current Password</label><input class="profile-input" type="password" name="current_password" required /></div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div><label class="profile-label">New Password</label><input class="profile-input" type="password" name="new_password" minlength="6" placeholder="Min. 6 characters" required /></div>
                        <div><label class="profile-label">Confirm New Password</label><input class="profile-input" type="password" name="confirm_password" minlength="6" placeholder="Re-type new password" required /></div>
                    </div>
                    <button class="text-primary font-bold px-4 py-3 rounded-lg border-2 border-primary/20 hover:bg-primary/5 transition-colors text-sm" type="submit">Update Password</button>
                </form>
            </section>
        </div>

        <div class="space-y-8">
            <form id="profileImageForm" class="bg-surface-container-high rounded-xl p-8 text-center border-2 border-dashed border-primary/15">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>" />
                <input type="hidden" name="first_name" value="<?php echo htmlspecialchars((string)($profileUser['first_name'] ?? '')); ?>" />
                <input type="hidden" name="last_name" value="<?php echo htmlspecialchars((string)($profileUser['last_name'] ?? '')); ?>" />
                <input type="hidden" name="dob" value="<?php echo htmlspecialchars($dobValue); ?>" />
                <input type="hidden" name="phone_number" value="<?php echo htmlspecialchars($phoneValue); ?>" />
                <input id="profileImagePicker" class="hidden" type="file" name="profile_image" accept=".jpg,.jpeg,.png,.gif,.webp,image/*" />
                <input id="profileImagePickerSecondary" class="hidden" type="file" name="profile_image_secondary" accept=".jpg,.jpeg,.png,.gif,.webp,image/*" />
                <div class="h-16 w-16 bg-primary/10 text-primary rounded-full flex items-center justify-center mx-auto mb-4"><i class="fa-solid fa-cloud-arrow-up text-2xl"></i></div>
                <h3 class="font-['Manrope'] font-bold text-lg mb-2">Change Profile Image</h3>
                <p class="text-xs text-on-surface-variant font-medium mb-6">JPG, PNG, or GIF. Max size of 5MB.</p>
                <label for="profileImagePickerSecondary" class="block w-full text-center py-3 bg-surface-container-lowest text-on-surface font-bold text-sm rounded-lg shadow-sm border border-outline-variant/50 cursor-pointer hover:bg-surface-container-high transition-colors">Choose File</label>
            </form>

            <div class="bg-primary text-on-primary rounded-xl p-8 relative overflow-hidden">
                <div class="relative z-10">
                    <p class="text-xs font-bold opacity-70 uppercase tracking-widest mb-4">Membership Status</p>
                    <div class="flex items-center gap-4 mb-6"><div class="h-3 w-3 <?php echo $statusDot; ?> rounded-full animate-pulse"></div><span class="font-['Manrope'] font-bold text-2xl tracking-tight">Status: <?php echo htmlspecialchars($statusLabel); ?></span></div>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center text-sm"><span class="opacity-70">Membership</span><span class="font-bold"><?php echo htmlspecialchars($memPlan); ?></span></div>
                        <?php if ($activeMem): ?>
                            <div class="flex justify-between items-center text-sm"><span class="opacity-70">Days Remaining</span><span class="font-bold"><?php echo (int)$daysLeft; ?> days</span></div>
                        <?php endif; ?>
                        <div class="flex justify-between items-center text-sm"><span class="opacity-70">Loyalty Points</span><span class="font-bold"><?php echo htmlspecialchars((string)$loyaltyPoints); ?></span></div>
                    </div>
                </div>
            </div>

            <div class="bg-surface-container-low rounded-xl p-8 space-y-4">
                <div class="flex items-center justify-between gap-3 pb-4 border-b border-outline-variant/20">
                    <div>
                        <p class="text-sm font-bold text-on-surface">Theme</p>
                        <p class="text-xs text-on-surface-variant">Switch light or dark mode</p>
                    </div>
                    <button type="button" data-lumina-theme-toggle class="lumina-theme-toggle shrink-0" title="Toggle theme" aria-label="Toggle light or dark mode">
                        <span class="material-symbols-outlined lumina-theme-icon lumina-icon-moon" aria-hidden="true">dark_mode</span>
                        <span class="material-symbols-outlined lumina-theme-icon lumina-icon-sun" aria-hidden="true">light_mode</span>
                    </button>
                </div>
                <h4 class="text-xs font-bold text-on-surface-variant uppercase tracking-widest mb-4">Quick Links</h4>
                <a class="flex items-center justify-between text-sm font-medium p-2 hover:bg-surface-container-high rounded-lg transition-colors" href="<?php echo APP_ROUTE; ?>?page=my-books"><span>My Books</span><i class="fa-solid fa-chevron-right text-xs"></i></a>
                <a class="flex items-center justify-between text-sm font-medium p-2 hover:bg-surface-container-high rounded-lg transition-colors" href="<?php echo APP_ROUTE; ?>?page=books"><span>Browse Catalog</span><i class="fa-solid fa-chevron-right text-xs"></i></a>
                <a class="flex items-center justify-between text-sm font-medium p-2 hover:bg-surface-container-high rounded-lg transition-colors" href="<?php echo APP_ROUTE; ?>?page=membership"><span>Membership</span><i class="fa-solid fa-chevron-right text-xs"></i></a>
                <form id="profileDeleteForm" class="pt-4 border-t border-outline-variant/20">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>" />
                    <label class="text-xs text-on-surface-variant font-medium">Type <strong>DELETE</strong> to confirm</label>
                    <input class="profile-input mt-2" type="text" name="confirm_delete" placeholder="DELETE" />
                    <button class="mt-3 flex items-center justify-between text-sm font-medium p-2 hover:bg-error-container/20 rounded-lg transition-colors text-error w-full" type="submit"><span>Delete Account</span><i class="fa-solid fa-trash text-xs"></i></button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
window.PROFILE_API_URL = '<?php echo APP_URL; ?>/controllers/profile.php';
</script>
<script src="<?php echo APP_URL; ?>/public/js/profile.js"></script>
