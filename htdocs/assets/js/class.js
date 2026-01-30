        // Modal functionality
const modal = document.getElementById('uploadModal');
const uploadBtns = document.querySelectorAll('.btn-upload');
const closeBtn = document.querySelector('.close-upload-modal');
const cancelBtn = document.querySelector('.btn-cancel-upload');
const browseBtn = document.querySelector('.btn-browse');
const fileInput = document.getElementById('fileInput');

uploadBtns.forEach(btn => {
    btn.addEventListener('click', () => {
        modal.style.display = 'block';
        const assignmentName = btn.closest('.assignment-card').querySelector('.assignment-title').textContent;
        document.getElementById('assignment_Name').innerText = ': ' + assignmentName;

        // Thêm hidden field để gửi assignment name
        let hiddenInput = document.getElementById('assignment_name_hidden');
        if (!hiddenInput) {
            hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.id = 'assignment_name_hidden';
            hiddenInput.name = 'assignment_name';
            document.getElementById('uploadForm').appendChild(hiddenInput);
        }
        hiddenInput.value = assignmentName;
    });
});

closeBtn.addEventListener('click', () => {
    modal.style.display = 'none';
});

cancelBtn.addEventListener('click', () => {
    modal.style.display = 'none';
});

browseBtn.addEventListener('click', () => {
    fileInput.click();
});

fileInput.addEventListener('change', (e) => {
    const fileName = e.target.files[0]?.name;
    if (fileName) {
        document.querySelector('.upload-area p').textContent = `Selected: ${fileName}`;
    }
});

// Close modal when clicking outside
window.addEventListener('click', (e) => {
    if (e.target === modal) {
        modal.style.display = 'none';
    }
});

// Download functionality
document.querySelectorAll('.btn-download').forEach(btn => {
    btn.addEventListener('click', () => {
        // Simulate download
        const assignmentTitle = btn.closest('.assignment-card').querySelector('.assignment-title').textContent;
        console.log(`Downloading: ${assignmentTitle}`);
        // In real implementation, this would trigger actual file download
    });
});

// Challenge Modal Functions
function openChallengeModal() {
    document.getElementById('challengeModal').style.display = 'block';
    document.getElementById('challengeCode').value = '';
    hideError();
}

function closeChallengeModal() {
    document.getElementById('challengeModal').style.display = 'none';
    hideError();
}

// Create Challenge Modal Functions (for teachers)
function openCreateChallengeModal() {
    document.getElementById('createChallengeModal').style.display = 'block';
    document.getElementById('createChallengeForm').reset();
    updateHintCharCount();

    // Set default datetime to 7 days from now
    const now = new Date();
    now.setDate(now.getDate() + 7);
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const defaultDateTime = `${year}-${month}-${day}T${hours}:${minutes}`;

    const expInput = document.getElementById('challengeExp');
    if (expInput) {
        expInput.value = defaultDateTime;
    }
}

function closeCreateChallengeModal() {
    document.getElementById('createChallengeModal').style.display = 'none';
}

function closeToast(toastId) {
    const toast = document.getElementById(toastId);
    if (toast) {
        toast.classList.add('hiding');
        setTimeout(() => {
            toast.remove();
        }, 300);
    }
}

// Auto-close toasts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const toasts = document.querySelectorAll('.toast');
    toasts.forEach(function(toast) {
        setTimeout(() => {
            if (toast && !toast.classList.contains('hiding')) {
                closeToast(toast.id);
            }
        }, 5000);
    });
    
    // Auto-close assignment toasts specifically
    const assignmentErrorToast = document.getElementById('assignmentErrorToast');
    const assignmentSuccessToast = document.getElementById('assignmentSuccessToast');
    
    if (assignmentErrorToast) {
        setTimeout(() => {
            closeToast('assignmentErrorToast');
        }, 5000);
    }
    
    if (assignmentSuccessToast) {
        setTimeout(() => {
            closeToast('assignmentSuccessToast');
        }, 5000);
    }
});

function updateHintCharCount() {
    const textarea = document.getElementById('challengeHint');
    const charCount = document.getElementById('hintCharCount');
    if (!textarea || !charCount) {
        return;
    }
    const currentLength = textarea.value.length;
    charCount.textContent = currentLength;

    if (currentLength > 200) {
        charCount.style.color = '#dc3545';
    } else {
        charCount.style.color = '#6c757d';
    }
}

function showError(message) {
    const errorDiv = document.getElementById('challengeError');
    const errorText = document.getElementById('errorText');
    errorText.textContent = message;
    errorDiv.style.display = 'block';
}

function hideError() {
    const errorDiv = document.getElementById('challengeError');
    if (errorDiv) {
        errorDiv.style.display = 'none';
    }
}

function joinChallenge() {
    const code = document.getElementById('challengeCode').value.trim();

    if (!code) {
        showError('Vui lòng nhập mã challenge!');
        return;
    }

    // Submit form via POST
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/challenges/join';

    const codeInput = document.createElement('input');
    codeInput.type = 'hidden';
    codeInput.name = 'code';
    codeInput.value = code;

    form.appendChild(codeInput);
    document.body.appendChild(form);
    form.submit();
}

// Close modal when clicking outside
window.addEventListener('click', (e) => {
    const challengeModal = document.getElementById('challengeModal');
    if (e.target === challengeModal) {
        closeChallengeModal();
    }
});

// Create Assignment Modal Functions (for teachers)
function openCreateAssignmentModal() {
    document.getElementById('createAssignmentModal').style.display = 'block';
    document.getElementById('createAssignmentForm').reset();

    // Set default datetime to 7 days from now
    const now = new Date();
    now.setDate(now.getDate() + 7);
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const defaultDateTime = `${year}-${month}-${day}T${hours}:${minutes}`;

    const expInput = document.getElementById('assignmentExp');
    if (expInput) {
        expInput.value = defaultDateTime;
    }
}

function closeCreateAssignmentModal() {
    document.getElementById('createAssignmentModal').style.display = 'none';
}

// Enter key to submit
document.getElementById('challengeCode').addEventListener('keypress', (e) => {
    if (e.key === 'Enter') {
        joinChallenge();
    }
});

// Submissions Modal Functions
function showSubmissions(assignmentName, submissionsJson) {
    const modal = document.getElementById('submissionsModal');
    const submissionsList = document.getElementById('submissionsList');
    
    // Parse JSON
    const submissions = submissionsJson ? JSON.parse(submissionsJson) : {};
    
    // Generate HTML
    let html = '';
    if (Object.keys(submissions).length === 0) {
        html = '<p style="text-align: center; color: #95a5a6;">Chưa có ai nộp bài</p>';
    } else {
        html = '<div class="submissions-grid">';
        for (const [username, filePath] of Object.entries(submissions)) {
            html += `
                <div class="submission-item">
                    <div class="submission-info">
                        <i class="fas fa-user"></i>
                        <span class="student-name">${username}</span>
                    </div>
                    <a href="${filePath}" target="_blank" class="view-submission-btn">
                        <i class="fas fa-eye"></i>
                        Xem bài làm
                    </a>
                </div>
            `;
        }
        html += '</div>';
    }
    
    submissionsList.innerHTML = html;
    modal.style.display = 'block';
}

function closeSubmissionsModal() {
    document.getElementById('submissionsModal').style.display = 'none';
}

// Close submissions modal events
document.querySelector('.close-submissions-modal').addEventListener('click', closeSubmissionsModal);
window.addEventListener('click', (e) => {
    if (e.target === document.getElementById('submissionsModal')) {
        closeSubmissionsModal();
    }
});