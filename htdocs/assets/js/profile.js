document.addEventListener('DOMContentLoaded', function() {
    const avatarContainer = document.querySelector('.avatar-container');
    const fileInput = document.getElementById('avatar-upload');
    const avatarPreview = document.getElementById('avatar-preview');

    if (!avatarContainer || !fileInput || !avatarPreview) return;

    let isUploading = false;

    avatarContainer.addEventListener('click', function() {
        if (!isUploading) fileInput.click();
    });

    fileInput.addEventListener('change', function(e) {
        const file = e.target.files && e.target.files[0];
        if (!file || isUploading) return;

        const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!allowedTypes.includes(file.type)) {
            alert('Chỉ chấp nhận file ảnh (JPG, PNG, GIF, WebP)!');
            fileInput.value = '';
            return;
        }

        if (file.size > 5 * 1024 * 1024) {
            alert('Kích thước file không được vượt quá 5MB!');
            fileInput.value = '';
            return;
        }

        isUploading = true;

        const reader = new FileReader();
        reader.onload = function(ev) {
            avatarPreview.src = ev.target.result;
        };
        reader.readAsDataURL(file);

        const formData = new FormData();
        formData.append('avatar', file);

        fetch('/profile/upload-avatar', {
            method: 'POST',
            body: formData
        })
            .then(res => res.json())
            .then(data => {
                if (data && data.success && data.avatarPath) {
                    avatarPreview.src = data.avatarPath + '?t=' + Date.now();
                } else {
                    alert('Lỗi khi tải lên ảnh: ' + ((data && data.message) ? data.message : 'Unknown error'));
                }
            })
            .catch(err => {
                console.error('Error uploading avatar:', err);
                alert('Lỗi khi tải lên ảnh!');
            })
            .finally(() => {
                isUploading = false;
                fileInput.value = '';
            });
    });
});
