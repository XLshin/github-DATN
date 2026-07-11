<script src="{{ asset('assets-client/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('assets-client/js/tiny-slider.js') }}"></script>
<script src="{{ asset('assets-client/js/glightbox.min.js') }}"></script>
<script src="{{ asset('assets-client/js/main.js') }}"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof tns !== 'undefined' && document.querySelector('.hero-slider')) {
            tns({
                container: '.hero-slider',
                slideBy: 'page',
                autoplay: true,
                autoplayButtonOutput: false,
                mouseDrag: true,
                gutter: 0,
                items: 1,
                nav: false,
                controls: true,
                controlsText: [
                    '<i class="lni lni-chevron-left"></i>',
                    '<i class="lni lni-chevron-right"></i>'
                ],
            });
        }
    });
</script>

<style>
    .fly-to-cart {
        position: fixed;
        z-index: 9999;
        border-radius: 50%;
        background: #0d6efd;
        pointer-events: none;
        transition: left 0.7s cubic-bezier(0.55, 0, 1, 0.45), top 0.7s cubic-bezier(0.55, 0, 1, 0.45), width 0.7s, height 0.7s, opacity 0.7s;
    }

    .total-items.bump {
        animation: cart-bump 0.35s ease;
    }

    @keyframes cart-bump {
        0% { transform: scale(1); }
        40% { transform: scale(1.5); }
        100% { transform: scale(1); }
    }
</style>

<script>
    (function () {
        window.showToast = function (message) {
            let container = document.getElementById('toast-container');
            if (!container) {
                container = document.createElement('div');
                container.id = 'toast-container';
                container.style.cssText = 'position:fixed;top:90px;right:20px;z-index:10000';
                document.body.appendChild(container);
            }

            const toast = document.createElement('div');
            toast.className = 'alert alert-success shadow-sm mb-2';
            toast.style.cssText = 'min-width:220px;opacity:0;transition:opacity .3s';
            toast.textContent = message;
            container.appendChild(toast);

            requestAnimationFrame(function () { toast.style.opacity = '1'; });
            setTimeout(function () {
                toast.style.opacity = '0';
                setTimeout(function () { toast.remove(); }, 300);
            }, 2000);
        };

        function updateCartBadges(count) {
            document.querySelectorAll('.total-items').forEach(function (el) {
                el.textContent = count;
                el.classList.remove('bump');
                void el.offsetWidth;
                el.classList.add('bump');
            });
        }

        function flyToCart(sourceEl) {
            const target = document.getElementById('cart-fly-target');
            if (!target || !sourceEl) return;

            const startRect = sourceEl.getBoundingClientRect();
            const endRect = target.getBoundingClientRect();

            const flyEl = document.createElement('div');
            flyEl.className = 'fly-to-cart';
            flyEl.style.left = startRect.left + startRect.width / 2 - 8 + 'px';
            flyEl.style.top = startRect.top + startRect.height / 2 - 8 + 'px';
            flyEl.style.width = '16px';
            flyEl.style.height = '16px';
            document.body.appendChild(flyEl);

            requestAnimationFrame(function () {
                flyEl.style.left = endRect.left + endRect.width / 2 - 4 + 'px';
                flyEl.style.top = endRect.top + endRect.height / 2 - 4 + 'px';
                flyEl.style.width = '8px';
                flyEl.style.height = '8px';
                flyEl.style.opacity = '0.3';
            });

            setTimeout(function () { flyEl.remove(); }, 750);
        }

        document.addEventListener('submit', function (e) {
            const form = e.target;
            if (!form.matches('.add-to-cart-form')) return;

            e.preventDefault();

            const tokenMeta = document.querySelector('meta[name="csrf-token"]');
            const token = tokenMeta ? tokenMeta.getAttribute('content') : null;
            const submitBtn = form.querySelector('[type="submit"]');
            const originalHtml = submitBtn ? submitBtn.innerHTML : null;

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
            }

            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                },
                body: new FormData(form),
            })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.success) {
                        flyToCart(submitBtn || form);
                        updateCartBadges(data.cart_count);

                        if (typeof window.showToast === 'function') {
                            window.showToast(data.message || 'Đã thêm vào giỏ hàng.');
                        }
                    }
                })
                .catch(function () {
                    form.submit();
                })
                .finally(function () {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalHtml;
                    }
                });
        });
    })();
</script>