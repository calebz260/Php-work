/* ============================================================
   XY Shop — Main JavaScript
   ============================================================ */

/* ---------- Auto-compute TotalPrice ---------- */
function initPriceCalc() {
    const qtyInput   = document.getElementById('Quantity');
    const priceInput = document.getElementById('UnitPrice');
    const totalEl    = document.getElementById('TotalPrice');

    if (!qtyInput || !priceInput || !totalEl) return;

    function compute() {
        const qty   = parseFloat(qtyInput.value)  || 0;
        const price = parseFloat(priceInput.value) || 0;
        totalEl.value = (qty * price).toFixed(2);
    }
    qtyInput.addEventListener('input',   compute);
    priceInput.addEventListener('input', compute);
}

/* ---------- Modal helpers ---------- */
function openModal(id) {
    const el = document.getElementById(id);
    if (el) el.classList.add('open');
}
function closeModal(id) {
    const el = document.getElementById(id);
    if (el) el.classList.remove('open');
}

// Close modal when clicking overlay backdrop
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.classList.remove('open');
    }
});

/* ---------- Delete confirmation ---------- */
function confirmDelete(url, name) {
    if (confirm('Are you sure you want to delete "' + name + '"?\nThis action cannot be undone.')) {
        window.location.href = url;
    }
}

/* ---------- Populate Edit Modal ---------- */
function openEditProduct(code, name) {
    document.getElementById('edit_ProductCode').value = code;
    document.getElementById('edit_ProductName').value  = name;
    openModal('editModal');
}

/* ---------- Flash message auto-dismiss ---------- */
function initAlertDismiss() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-8px)';
            alert.style.transition = 'all 0.4s ease';
            setTimeout(function() { alert.remove(); }, 400);
        }, 4000);
    });
}

/* ---------- Set DateTime default to now ---------- */
function initDateTimeDefault() {
    const dtInput = document.getElementById('DateTime');
    if (!dtInput) return;
    const now = new Date();
    // Format: YYYY-MM-DDTHH:MM
    const pad = n => String(n).padStart(2, '0');
    const local = now.getFullYear() + '-' +
                  pad(now.getMonth() + 1) + '-' +
                  pad(now.getDate()) + 'T' +
                  pad(now.getHours()) + ':' +
                  pad(now.getMinutes());
    dtInput.value = local;
}

/* ---------- Password toggle ---------- */
function initPasswordToggle() {
    document.querySelectorAll('.toggle-pw').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const target = document.getElementById(btn.dataset.target);
            if (!target) return;
            if (target.type === 'password') {
                target.type = 'text';
                btn.innerHTML = "<i class='bx bx-hide'></i>";
            } else {
                target.type = 'password';
                btn.innerHTML = "<i class='bx bx-show'></i>";
            }
        });
    });
}

/* ---------- Staggered card animations ---------- */
function initStagger() {
    const cards = document.querySelectorAll('.stat-card, .card');
    cards.forEach(function(card, i) {
        card.style.animationDelay = (i * 0.07) + 's';
    });
}

/* ---------- Init on DOM ready ---------- */
document.addEventListener('DOMContentLoaded', function() {
    initPriceCalc();
    initAlertDismiss();
    initDateTimeDefault();
    initPasswordToggle();
    initStagger();
});

