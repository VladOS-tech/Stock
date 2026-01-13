const API_URL = 'http://localhost:8000';

document.addEventListener('DOMContentLoaded', () => {
    const holdForm = document.getElementById('hold-form');
    const confirmForm = document.getElementById('confirm-form');
    const responseEl = document.getElementById('response');

    async function callApi(endpoint, data) {
        try {
            responseEl.textContent = 'Loading...';
            responseEl.className = '';

            const idempotencyKey = crypto.randomUUID();
            console.log('Key:', idempotencyKey);

            const url = `${API_URL}${endpoint}`;
            console.log('URL:', url);

            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Idempotency-Key': idempotencyKey
                },
                body: JSON.stringify(data),
            });

            console.log('Status:', res.status);

            const json = await res.json();
            responseEl.textContent = JSON.stringify(json, null, 2);
            responseEl.className = json.success ? 'success' : 'error';
        } catch (error) {
            console.error('Fetch error:', error);
            responseEl.textContent = `Error: ${error.message}`;
            responseEl.className = 'error';
        }
    }

    holdForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(holdForm);

        await callApi('/api/stock/hold', {
            sku: formData.get('sku') || null,
            price: formData.get('price') ? parseFloat(formData.get('price')) : null,
        });
    });

    confirmForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(confirmForm);

        await callApi('/api/stock/confirm', {
            orderId: formData.get('orderId') || null,
        });
    });
});
