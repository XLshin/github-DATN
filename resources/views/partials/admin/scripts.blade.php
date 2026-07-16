<script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}" data-turbo-eval="false"></script>
<script src="{{ asset('assets/js/main.js') }}" data-turbo-eval="false"></script>

<style>
    @keyframes admin-bell-ring {
        0%, 100% { transform: rotate(0deg); }
        10%, 30%, 50%, 70% { transform: rotate(-18deg); }
        20%, 40%, 60%, 80% { transform: rotate(18deg); }
        90% { transform: rotate(0deg); }
    }
    .admin-bell-ring {
        display: inline-block;
        transform-origin: top center;
        animation: admin-bell-ring 0.7s ease-in-out 2;
        color: #dc3545 !important;
    }
</style>

<script>
(function () {
    var pollUrl = @json(route('admin.notifications.pendingCount'));
    var pollIntervalMs = 20000;
    var lastSeenIds = null;
    var badgeEl, menuEl, bellEl, bellIconEl, toastContainer;

    document.addEventListener('DOMContentLoaded', function () {
        badgeEl = document.querySelector('[data-admin-notif-badge]');
        menuEl = document.querySelector('[data-admin-notif-menu]');
        bellEl = document.querySelector('[data-admin-notif-bell]');
        bellIconEl = document.querySelector('[data-admin-notif-bell-icon]');
        toastContainer = document.createElement('div');
        toastContainer.style.cssText = 'position:fixed;top:80px;right:20px;z-index:10600;display:flex;flex-direction:column;gap:8px';
        document.body.appendChild(toastContainer);

        maybeShowSessionModal();

        poll(true);
        setInterval(function () { poll(false); }, pollIntervalMs);
    });

    // Hiện bảng tổng hợp công việc cần xử lý — chỉ 1 lần khi bắt đầu phiên làm việc mới (mở tab/đăng nhập lại).
    function maybeShowSessionModal() {
        var modalEl = document.getElementById('adminPendingTasksModal');
        if (!modalEl || typeof bootstrap === 'undefined') return;

        var pendingCount = parseInt(modalEl.dataset.adminPendingCount || '0', 10);
        var alreadyShown = sessionStorage.getItem('adminPendingTasksModalShown');

        if (pendingCount > 0 && !alreadyShown) {
            sessionStorage.setItem('adminPendingTasksModalShown', '1');
            new bootstrap.Modal(modalEl).show();
        }
    }

    function poll(isFirstLoad) {
        fetch(pollUrl, { headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (data) { render(data, isFirstLoad); })
            .catch(function () {});
    }

    function render(data, isFirstLoad) {
        var currentIds = data.items.map(function (i) { return i.id; });

        if (badgeEl) {
            if (data.count > 0) {
                badgeEl.textContent = data.count > 99 ? '99+' : data.count;
                badgeEl.classList.remove('d-none');
            } else {
                badgeEl.classList.add('d-none');
            }
        }

        if (menuEl) {
            menuEl.innerHTML = data.items.length ? data.items.map(function (item) {
                return '<a href="' + item.url + '" class="dropdown-item d-flex align-items-start gap-2 py-2 px-3 border-bottom">' +
                    '<i class="bi ' + item.icon + ' text-primary mt-1"></i>' +
                    '<span class="flex-grow-1">' +
                        '<span class="d-block fw-semibold small">' + item.type + ' — ' + item.user + '</span>' +
                        '<span class="d-block text-muted small">' + formatVnd(item.amount) + '</span>' +
                    '</span>' +
                '</a>';
            }).join('') : '<div class="px-3 py-4 text-center text-muted small">Không có yêu cầu nào đang chờ xử lý.</div>';
        }

        if (!isFirstLoad && lastSeenIds !== null) {
            var newItems = data.items.filter(function (item) { return lastSeenIds.indexOf(item.id) === -1; });
            if (newItems.length) {
                ringBell();
                newItems.forEach(showToast);
            }
        }

        lastSeenIds = currentIds;
    }

    function ringBell() {
        if (!bellIconEl) return;
        bellIconEl.classList.remove('admin-bell-ring');
        void bellIconEl.offsetWidth; // reset animation
        bellIconEl.classList.add('admin-bell-ring');
        setTimeout(function () { bellIconEl.classList.remove('admin-bell-ring'); }, 1500);
    }

    function showToast(item) {
        var toast = document.createElement('a');
        toast.href = item.url;
        toast.className = 'shadow-lg rounded-3 bg-white border-start border-4 border-primary p-3 d-block text-decoration-none';
        toast.style.cssText = 'min-width:280px;max-width:340px;opacity:0;transition:opacity .3s,transform .3s;transform:translateX(20px)';
        toast.innerHTML =
            '<div class="d-flex align-items-start gap-2">' +
                '<i class="bi ' + item.icon + ' text-primary fs-5"></i>' +
                '<div>' +
                    '<div class="fw-bold small text-dark">Yêu cầu mới: ' + item.type + '</div>' +
                    '<div class="text-muted small">' + item.user + ' — ' + formatVnd(item.amount) + '</div>' +
                '</div>' +
            '</div>';

        toastContainer.appendChild(toast);
        requestAnimationFrame(function () {
            toast.style.opacity = '1';
            toast.style.transform = 'translateX(0)';
        });

        setTimeout(function () {
            toast.style.opacity = '0';
            setTimeout(function () { toast.remove(); }, 300);
        }, 8000);
    }

    function formatVnd(amount) {
        return new Intl.NumberFormat('vi-VN').format(Math.round(amount)) + ' đ';
    }
})();
</script>
