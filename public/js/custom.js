const alertDiv = document.getElementById('logAlert');
const btn = document.getElementById('runLogBtn');
if (btn != null) {
    const url = btn.dataset.url;

    btn.addEventListener('click', function() {
        alertDiv.innerHTML = `
            <div class="alert alert-info" role="alert">
                Running log parser...
            </div>
        `;

        fetch(url)
            .then(response => response.json())
            .then(data => {
                console.log(data);
                const alertClass = data.status === 'success' ? 'alert-success' : 'alert-danger';
                alertDiv.innerHTML = `
                    <div class="alert ${alertClass}" role="alert">
                        ${data.message}
                    </div>
                `;
            })
            .catch(err => {
                alertDiv.innerHTML = `
                    <div class="alert alert-danger" role="alert">
                        Error: ${err.message}
                    </div>
                `;
            });
    });
}

// ===== Function to show Bootstrap alert dynamically =====
function showAlert(message, type = 'success') {
    const alertBox = document.getElementById('logAlert');
    alertBox.innerHTML = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;

    // Auto-dismiss after 3 seconds
    setTimeout(() => {
        const alertEl = bootstrap.Alert.getOrCreateInstance(document.querySelector('.alert'));
        if (alertEl) alertEl.close();
    }, 3000);
}

document.addEventListener('DOMContentLoaded', function () {
    const items = document.querySelectorAll('.notification-item');

    if (items != null) {
        items.forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();

                const id = this.dataset.id;
                const url = this.dataset.url;

                fetch(`/admin/notifications/read/${id}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        this.classList.remove('fw-semibold');

                        if (url) window.location.href = url;
                    }
                })
                .catch(err => console.error(err));
            });
        });
    }
});