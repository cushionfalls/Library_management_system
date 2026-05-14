function profileToast(message, type) {
    if (typeof window.showToast === 'function') {
        window.showToast(message, type || 'info');
        return;
    }
    alert(message);
}

function profileApi(action) {
    return (window.PROFILE_API_URL || '') + '?action=' + encodeURIComponent(action);
}

async function profileFetch(action, options = {}) {
    const res = await fetch(profileApi(action), options);
    return res.json();
}

function bindProfileForms() {
    const profileForm = document.getElementById('profileInfoForm');
    const passwordForm = document.getElementById('profilePasswordForm');
    const imageForm = document.getElementById('profileImageForm');
    const deleteForm = document.getElementById('profileDeleteForm');
    const imagePreview = document.getElementById('profileImagePreview');
    const imageFallback = document.getElementById('profileImagePreviewFallback');
    const hiddenImageInput = document.getElementById('profileImagePicker');
    const hiddenImageInputSecondary = document.getElementById('profileImagePickerSecondary');

    const attachImagePicker = (input) => {
        if (!input) return;
        input.addEventListener('change', async () => {
            if (!input.files || !input.files[0]) return;
            const data = new FormData(imageForm);
            data.set('profile_image', input.files[0]);
            const result = await profileFetch('upload-image', { method: 'POST', body: data });

            if (!result || !result.success) {
                profileToast((result && result.error) || 'Failed to upload image', 'error');
                return;
            }
            if (imagePreview && result.image_url) {
                imagePreview.src = result.image_url;
                imagePreview.classList.remove('hidden');
                if (imageFallback) imageFallback.classList.add('hidden');
            }
            profileToast(result.message || 'Image uploaded', 'success');
        });
    };

    attachImagePicker(hiddenImageInput);
    attachImagePicker(hiddenImageInputSecondary);

    if (profileForm) {
        profileForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            const result = await profileFetch('update-profile', {
                method: 'POST',
                body: new FormData(profileForm)
            });
            if (!result || !result.success) {
                profileToast((result && result.error) || 'Failed to update profile', 'error');
                return;
            }
            profileToast(result.message || 'Profile updated successfully', 'success');
        });
    }

    if (passwordForm) {
        passwordForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            const result = await profileFetch('change-password', {
                method: 'POST',
                body: new FormData(passwordForm)
            });
            if (!result || !result.success) {
                profileToast((result && result.error) || 'Failed to update password', 'error');
                return;
            }
            passwordForm.reset();
            profileToast(result.message || 'Password updated successfully', 'success');
        });
    }

    if (deleteForm) {
        deleteForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            if (!confirm('Are you sure you want to permanently delete your account?')) return;
            const result = await profileFetch('delete-account', {
                method: 'POST',
                body: new FormData(deleteForm)
            });
            if (!result || !result.success) {
                profileToast((result && result.error) || 'Failed to delete account', 'error');
                return;
            }
            profileToast(result.message || 'Account deleted', 'success');
            if (result.redirect) {
                window.location.href = result.redirect;
            }
        });
    }
}

document.addEventListener('DOMContentLoaded', bindProfileForms);
