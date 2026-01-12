const API_URL = 'http://localhost:8000/api';

document.addEventListener('DOMContentLoaded', () => {
    const holdForm = document.getElementById('hold-form');
    const confirmForm = document.getElementById('confirm-form');
    const responseEl = document.getElementById('response');

    async function callApi(url, data) {
        try {
            responseEl.textContent = 'Loading...';
            responseEl.className = '';

            const res = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data),
            });

            const json = await res.json();
            responseEl.textContent = JSON.stringify(json, null, 2);
            responseEl.className = json.success ? 'success' : 'error';
        } catch (error) {
            responseEl.textContent = JSON.stringify({ error: error.message }, null, 2);
            responseEl.className = 'error';
        }
    }

    holdForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(holdForm);

        const sku = formData.get('sku');
        const priceStr = formData.get('price');

        await callApi(`${API_URL}/stock/hold`, {
            sku: sku ? String(sku) : null,
            price: priceStr ? parseFloat(String(priceStr)) : null,
        });
    });

    confirmForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(confirmForm);

        const orderId = formData.get('orderId');

        await callApi(`${API_URL}/stock/confirm`, {
            orderId: orderId ? String(orderId) : null,
        });
    });
});
