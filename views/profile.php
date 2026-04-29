<?php
$profileUser = $session->getUserData();
if (!$profileUser) {
    echo '<div class="alert alert-error">Unable to load profile details.</div>';
    return;
}

$csrfToken = $session->generateCSRFToken();
$displayName = trim((string)($profileUser['first_name'] ?? '') . ' ' . (string)($profileUser['last_name'] ?? ''));
$memberSince = !empty($profileUser['created_at']) ? date('M Y', strtotime((string)$profileUser['created_at'])) : 'N/A';
$walletBalance = number_format((float)($profileUser['wallet'] ?? 0), 2);
$dobValue = !empty($profileUser['dob']) ? date('Y-m-d', strtotime((string)$profileUser['dob'])) : '';
$phoneValue = (string)($profileUser['phone_number'] ?? '');
$avatarPath = !empty($profileUser['profile_image']) ? APP_URL . '/public/uploads/profiles/' . basename((string)$profileUser['profile_image']) : '';
$isActive = (int)($profileUser['is_active'] ?? 0) === 1;
$statusLabel = $isActive ? 'Active' : 'Inactive';
$statusDot = $isActive ? 'bg-emerald-400' : 'bg-red-400';
$loyaltyPoints = max(0, (int)floor(((float)($profileUser['wallet'] ?? 0)) * 10));
?>

<style>
    .profile-input {
        width: 100%;
        background: #e9e3f4;
        border: none;
        border-radius: 0.5rem;
        padding: 0.75rem 1rem;
        transition: box-shadow 0.2s ease, background-color 0.2s ease;
    }
    .profile-input:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(56, 0, 191, 0.25);
    }
    .profile-label {
        display: block;
        font-size: 0.75rem;
        font-weight: 700;
        color: #6f6a81;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        margin-left: 0.25rem;
        margin-bottom: 0.5rem;
    }
    .profile-card {
        background: #f7f1ff;
        border-radius: 0.75rem;
        padding: 2rem;
    }
</style>

<div class="max-w-[1440px] mx-auto">
    <div class="flex flex-col md:flex-row items-end gap-8 mb-12">
        <div class="relative group">
            <div class="h-40 w-40 rounded-xl overflow-hidden bg-[#ece5fa] shadow-2xl">
                <?php if ($avatarPath !== ''): ?>
                    <img id="profileImagePreview" alt="User Profile Image" class="w-full h-full object-cover" src="<?php echo htmlspecialchars($avatarPath); ?>" />
                <?php else: ?>
                    <div id="profileImagePreviewFallback" class="w-full h-full flex items-center justify-center text-5xl text-[#4f1bf1]">
                        <i class="fa-solid fa-user"></i>
                    </div>
                    <img id="profileImagePreview" alt="User Profile Image" class="w-full h-full object-cover hidden" src="" />
                <?php endif; ?>
            </div>
            <label class="absolute -bottom-4 -right-4 h-12 w-12 bg-[#4f1bf1] rounded-full flex items-center justify-center text-white cursor-pointer hover:scale-110 transition-transform shadow-lg" for="profileImagePicker">
                <i class="fa-solid fa-camera"></i>
            </label>
        </div>
        <div class="flex-1 pb-2">
            <div class="flex items-center gap-4 mb-2">
                <span class="bg-[#9c2a00] text-[#ffb6a1] px-3 py-1 rounded-full text-xs font-bold uppercase tracking-widest">Active Patron</span>
                <span class="text-slate-400 text-sm font-medium">Member since <?php echo htmlspecialchars($memberSince); ?></span>
            </div>
            <h1 class="font-['Manrope'] font-extrabold text-5xl text-[#1c1a25] tracking-tight"><?php echo htmlspecialchars($displayName ?: 'Library Member'); ?></h1>
            <p class="text-[#5b566a] mt-2 flex items-center gap-2">
                <i class="fa-solid fa-envelope text-sm"></i>
                <?php echo htmlspecialchars((string)($profileUser['email'] ?? '')); ?>
            </p>
        </div>
        <div class="pb-2">
            <div class="bg-[#f7f1ff] p-6 rounded-xl min-w-[220px] shadow-sm">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Wallet Balance</p>
                <div class="flex items-baseline gap-1">
                    <span class="text-3xl font-['Manrope'] font-extrabold text-[#3800bf]">$<?php echo htmlspecialchars($walletBalance); ?></span>
                    <span class="text-xs text-[#6a657a] font-medium">USD</span>
                </div>
                <a href="<?php echo APP_ROUTE; ?>?page=wallet" class="mt-4 text-[#3800bf] font-bold text-xs inline-flex items-center gap-1 hover:gap-2 transition-all">
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
                    <i class="fa-solid fa-id-card text-[#7b768d]"></i>
                </div>
                <form id="profileInfoForm" class="space-y-6">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>" />
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div><label class="profile-label">First Name</label><input class="profile-input" type="text" name="first_name" value="<?php echo htmlspecialchars((string)($profileUser['first_name'] ?? '')); ?>" required /></div>
                        <div><label class="profile-label">Last Name</label><input class="profile-input" type="text" name="last_name" value="<?php echo htmlspecialchars((string)($profileUser['last_name'] ?? '')); ?>" required /></div>
                    </div>
                    <div><label class="profile-label">Email Address (Read Only)</label><input class="profile-input opacity-60 cursor-not-allowed" type="email" value="<?php echo htmlspecialchars((string)($profileUser['email'] ?? '')); ?>" readonly /></div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div><label class="profile-label">Date of Birth</label><input class="profile-input" type="date" name="dob" value="<?php echo htmlspecialchars($dobValue); ?>" /></div>
                        <div><label class="profile-label">Phone Number</label><input class="profile-input" type="text" name="phone_number" value="<?php echo htmlspecialchars($phoneValue); ?>" placeholder="+1 (555) 000-0000" /></div>
                    </div>
                    <button class="bg-gradient-to-r from-[#3800bf] to-[#4f1bf1] text-white px-8 py-3 rounded-lg font-bold text-sm shadow-lg hover:opacity-90 transition-opacity" type="submit">Save Changes</button>
                </form>
            </section>

            <section class="profile-card">
                <div class="flex items-center justify-between mb-8">
                    <h2 class="font-['Manrope'] font-bold text-2xl">Security &amp; Authentication</h2>
                    <i class="fa-solid fa-lock text-[#7b768d]"></i>
                </div>
                <form id="profilePasswordForm" class="space-y-6">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>" />
                    <div><label class="profile-label">Current Password</label><input class="profile-input" type="password" name="current_password" required /></div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div><label class="profile-label">New Password</label><input class="profile-input" type="password" name="new_password" minlength="6" placeholder="Min. 6 characters" required /></div>
                        <div><label class="profile-label">Confirm New Password</label><input class="profile-input" type="password" name="confirm_password" minlength="6" placeholder="Re-type new password" required /></div>
                    </div>
                    <button class="text-[#3800bf] font-bold px-4 py-3 rounded-lg border-2 border-[#3800bf]/20 hover:bg-[#3800bf]/5 transition-colors text-sm" type="submit">Update Password</button>
                </form>
            </section>
        </div>

        <div class="space-y-8">
            <form id="profileImageForm" class="bg-[#e8e1f2] rounded-xl p-8 text-center border-2 border-dashed border-[#3800bf]/15">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>" />
                <input type="hidden" name="first_name" value="<?php echo htmlspecialchars((string)($profileUser['first_name'] ?? '')); ?>" />
                <input type="hidden" name="last_name" value="<?php echo htmlspecialchars((string)($profileUser['last_name'] ?? '')); ?>" />
                <input type="hidden" name="dob" value="<?php echo htmlspecialchars($dobValue); ?>" />
                <input type="hidden" name="phone_number" value="<?php echo htmlspecialchars($phoneValue); ?>" />
                <input id="profileImagePicker" class="hidden" type="file" name="profile_image" accept=".jpg,.jpeg,.png,.gif,.webp,image/*" />
                <input id="profileImagePickerSecondary" class="hidden" type="file" name="profile_image_secondary" accept=".jpg,.jpeg,.png,.gif,.webp,image/*" />
                <div class="h-16 w-16 bg-[#4f1bf1]/10 text-[#4f1bf1] rounded-full flex items-center justify-center mx-auto mb-4"><i class="fa-solid fa-cloud-arrow-up text-2xl"></i></div>
                <h3 class="font-['Manrope'] font-bold text-lg mb-2">Change Profile Image</h3>
                <p class="text-xs text-[#696477] font-medium mb-6">JPG, PNG, or GIF. Max size of 5MB.</p>
                <label for="profileImagePickerSecondary" class="block w-full text-center py-3 bg-white text-[#1c1a25] font-bold text-sm rounded-lg shadow-sm border border-[#c9c4da]/50 cursor-pointer hover:bg-[#fdf8ff] transition-colors">Choose File</label>
            </form>

            <div class="bg-[#3800bf] text-white rounded-xl p-8 relative overflow-hidden">
                <div class="relative z-10">
                    <p class="text-xs font-bold opacity-70 uppercase tracking-widest mb-4">Account Health</p>
                    <div class="flex items-center gap-4 mb-6"><div class="h-3 w-3 <?php echo $statusDot; ?> rounded-full animate-pulse"></div><span class="font-['Manrope'] font-bold text-2xl tracking-tight">Status: <?php echo htmlspecialchars($statusLabel); ?></span></div>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center text-sm"><span class="opacity-70">Overdue Items</span><span class="font-bold">0</span></div>
                        <div class="flex justify-between items-center text-sm"><span class="opacity-70">Holds Pending</span><span class="font-bold">0</span></div>
                        <div class="flex justify-between items-center text-sm"><span class="opacity-70">Loyalty Points</span><span class="font-bold"><?php echo htmlspecialchars((string)$loyaltyPoints); ?></span></div>
                    </div>
                </div>
            </div>

            <div class="bg-[#f7f1ff] rounded-xl p-8 space-y-4">
                <h4 class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-4">Quick Links</h4>
                <a class="flex items-center justify-between text-sm font-medium p-2 hover:bg-white rounded-lg transition-colors" href="<?php echo APP_ROUTE; ?>?page=my-books"><span>Borrowing History</span><i class="fa-solid fa-chevron-right text-xs"></i></a>
                <a class="flex items-center justify-between text-sm font-medium p-2 hover:bg-white rounded-lg transition-colors" href="<?php echo APP_ROUTE; ?>?page=books"><span>Favorite Collections</span><i class="fa-solid fa-chevron-right text-xs"></i></a>
                <form id="profileDeleteForm" class="pt-4 border-t border-[#d8d1e9]">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>" />
                    <label class="text-xs text-[#7b768d] font-medium">Type <strong>DELETE</strong> to confirm</label>
                    <input class="profile-input mt-2" type="text" name="confirm_delete" placeholder="DELETE" />
                    <button class="mt-3 flex items-center justify-between text-sm font-medium p-2 hover:bg-[#ffecec] rounded-lg transition-colors text-[#ba1a1a] w-full" type="submit"><span>Delete Account</span><i class="fa-solid fa-trash text-xs"></i></button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
window.PROFILE_API_URL = '<?php echo APP_URL; ?>/controllers/profile.php';
</script>
<script src="<?php echo APP_URL; ?>/public/js/profile.js"></script>
