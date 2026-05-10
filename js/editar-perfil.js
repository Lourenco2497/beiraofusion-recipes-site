document.addEventListener('DOMContentLoaded', function() {

    const profilePictureContainer = document.querySelector('.profile-picture');
    const editPhotoText = document.querySelector('.edit-photo');
    const fileInput = document.getElementById('profile_image_input');
    const imagePreview = document.getElementById('profile-image-preview');

    const triggerFileInput = (event) => {
        event.preventDefault();
        fileInput.click();
    };

    if (profilePictureContainer) profilePictureContainer.addEventListener('click', triggerFileInput);
    if (editPhotoText) editPhotoText.addEventListener('click', triggerFileInput);

    if (fileInput && imagePreview) {
        fileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
    }


    const changeEmailLink = document.querySelector('.change-email');
    const emailInput = document.getElementById('email');

    if (changeEmailLink && emailInput) {
        changeEmailLink.addEventListener('click', function(event) {
            event.preventDefault();
            emailInput.disabled = false;
            emailInput.focus();
            changeEmailLink.style.display = 'none';
        });
    }


    const changePasswordLink = document.querySelector('.change-password');
    const passwordInput = document.getElementById('password');
    const passwordLabel = document.querySelector('label[for="password"]');
    const passwordInputGroup = passwordInput ? passwordInput.parentElement : null;

    if (changePasswordLink && passwordInput && passwordLabel && passwordInputGroup) {
        changePasswordLink.addEventListener('click', function(event) {
            event.preventDefault();

            if (passwordLabel) {
                passwordLabel.textContent = 'Nova Password';
            }
            passwordInput.disabled = false;
            passwordInput.value = '';
            passwordInput.setAttribute('placeholder', 'Digite a nova password');
            passwordInput.setAttribute('name', 'new_password');

            const confirmPasswordDiv = document.createElement('div');
            confirmPasswordDiv.className = 'mb-3 mt-2';

            const confirmPasswordLabel = document.createElement('label');
            confirmPasswordLabel.setAttribute('for', 'confirmNewPassword');
            confirmPasswordLabel.className = 'form-label';
            confirmPasswordLabel.textContent = 'Confirmar Nova Password';

            const confirmPasswordInput = document.createElement('input');
            confirmPasswordInput.type = 'password';
            confirmPasswordInput.className = 'form-control';
            confirmPasswordInput.id = 'confirmNewPassword';
            confirmPasswordInput.setAttribute('name', 'confirm_new_password');
            confirmPasswordInput.setAttribute('placeholder', 'Confirme a nova password');

            confirmPasswordDiv.appendChild(confirmPasswordLabel);
            confirmPasswordDiv.appendChild(confirmPasswordInput);

            if (passwordInputGroup.parentNode) {
                 passwordInputGroup.parentNode.insertBefore(confirmPasswordDiv, passwordInputGroup.nextSibling);
            }

            changePasswordLink.style.display = 'none';
            passwordInput.focus();
        });
    }
});