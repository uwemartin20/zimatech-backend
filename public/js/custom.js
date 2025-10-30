const alertDiv = document.getElementById('logAlert');
const btn = document.getElementById('runLogBtn');
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