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