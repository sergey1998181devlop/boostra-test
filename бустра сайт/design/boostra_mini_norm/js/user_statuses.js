(function () {
    const hiddenEl = document.getElementById("userData");
    if (!hiddenEl) return;

    const orderId = parseInt(hiddenEl.dataset.orderId);
    const orderStatus = parseInt(hiddenEl.dataset.orderStatus);
    const number = hiddenEl.dataset.number || '';
    const status1C = hiddenEl.dataset['1cStatus'] || '';

    const isFinalStatus = orderStatus === 10 && status1C === '5.Выдан';
    if (isFinalStatus) return;

    const reloadPage = () => location.reload();

    const fetchJson = async (url, options = {}) => {
        try {
            const resp = await fetch(url, options);
            return await resp.json();
        } catch (err) {
            console.error("Fetch error:", err);
            return null;
        }
    };

    // --- Polling for statuses that require reload ---
    const reloadPageStatuses = [1, 2, 8, 9, 10, 15, 16];

    if (reloadPageStatuses.includes(orderStatus)) {
        const checkOrderStatus = async () => {
            const params = new URLSearchParams({
                order_id: orderId,
                number: number,
                order_status: orderStatus,
                order_1c_status: status1C
            });

            const data = await fetchJson(`ajax/check_status.php?${params.toString()}`);
            if (data?.change) reloadPage();
        };

        // Initial check
        checkOrderStatus();

        // Recheck after 5s and then every 30s
        setTimeout(() => {
            checkOrderStatus();
            setInterval(checkOrderStatus, 30000);
        }, 5000);
    }
})();
