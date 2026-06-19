<footer class="footer">
    {{-- Footer Top --}}
    <div class="footer-top">
        <div class="container">
            <div class="inner-content">
                <div class="row align-items-center">
                    <div class="col-lg-3 col-md-4 col-12">
                        <div class="footer-logo">
                            <a href="{{ route('home') }}">
                                <img src="{{ asset('assets-client/images/logo/white-logo.svg') }}" alt="Byte Zone Store">
                            </a>
                        </div>
                    </div>

                    <div class="col-lg-9 col-md-8 col-12">
                        <div class="footer-newsletter">
                            <h4 class="title">
                                Đăng ký nhận tin khuyến mãi
                                <span>Nhận thông tin điện thoại mới, ưu đãi hot và mã giảm giá từ Byte Zone Store.</span>
                            </h4>

                            <div class="newsletter-form-head">
                                <form action="#" method="POST" class="newsletter-form">
                                    @csrf
                                    <input name="email" type="email" placeholder="Nhập email của bạn..." required>

                                    <div class="button">
                                        <button class="btn" type="submit">
                                            Đăng ký
                                            <span class="dir-part"></span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Footer Middle --}}
    <div class="footer-middle">
        <div class="container">
            <div class="bottom-inner">
                <div class="row">
                    <div class="col-lg-3 col-md-6 col-12">
                        <div class="single-footer f-contact">
                            <h3>Liên hệ với chúng tôi</h3>

                            <p class="phone">
                                Hotline: 0909 999 888
                            </p>

                            <ul>
                                <li><span>Thứ 2 - Thứ 6:</span> 8:00 - 21:00</li>
                                <li><span>Thứ 7 - Chủ nhật:</span> 9:00 - 20:00</li>
                            </ul>

                            <p class="mail">
                                <a href="mailto:support@bytezone.vn">support@bytezone.vn</a>
                            </p>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6 col-12">
                        <div class="single-footer f-link">
                            <h3>Về Byte Zone</h3>

                            <ul>
                                <li><a href="{{ url('/gioi-thieu') }}">Giới thiệu</a></li>
                                <li><a href="{{ url('/lien-he') }}">Liên hệ</a></li>
                                <li><a href="{{ url('/chinh-sach-bao-hanh') }}">Chính sách bảo hành</a></li>
                                <li><a href="{{ url('/chinh-sach-doi-tra') }}">Chính sách đổi trả</a></li>
                                <li><a href="{{ url('/cau-hoi-thuong-gap') }}">Câu hỏi thường gặp</a></li>
                            </ul>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6 col-12">
                        <div class="single-footer f-link">
                            <h3>Danh mục sản phẩm</h3>

                            <ul>
                                <li><a href="{{ url('/san-pham?brand=iphone') }}">iPhone</a></li>
                                <li><a href="{{ url('/san-pham?brand=samsung') }}">Samsung</a></li>
                                <li><a href="{{ url('/san-pham?brand=xiaomi') }}">Xiaomi</a></li>
                                <li><a href="{{ url('/san-pham?brand=oppo') }}">OPPO</a></li>
                                <li><a href="{{ url('/phu-kien') }}">Phụ kiện điện thoại</a></li>
                            </ul>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6 col-12">
                        <div class="single-footer f-link">
                            <h3>Dịch vụ hỗ trợ</h3>

                            <ul>
                                <li><a href="{{ url('/thu-cu-doi-moi') }}">Thu cũ đổi mới</a></li>
                                <li><a href="{{ url('/tra-gop') }}">Trả góp 0%</a></li>
                                <li><a href="{{ url('/giao-hang') }}">Giao hàng nhanh</a></li>
                                <li><a href="{{ url('/bao-hanh-mo-rong') }}">Bảo hành mở rộng</a></li>
                                <li><a href="{{ url('/ho-tro-ky-thuat') }}">Hỗ trợ kỹ thuật</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Footer Bottom --}}
    <div class="footer-bottom">
        <div class="container">
            <div class="inner-content">
                <div class="row align-items-center">
                    <div class="col-lg-4 col-12">
                        <div class="payment-gateway">
                            <span>Thanh toán:</span>
                            <img src="{{ asset('assets-client/images/footer/credit-cards-footer.png') }}" alt="Phương thức thanh toán">
                        </div>
                    </div>

                    <div class="col-lg-4 col-12">
                        <div class="copyright">
                            <p>
                                &copy; {{ date('Y') }} Byte Zone Store — Hệ thống thương mại điện tử.
                            </p>
                        </div>
                    </div>

                    <div class="col-lg-4 col-12">
                        <ul class="socila">
                            <li>
                                <span>Theo dõi:</span>
                            </li>
                            <li>
                                <a href="javascript:void(0)" aria-label="Facebook">
                                    <i class="lni lni-facebook-filled"></i>
                                </a>
                            </li>
                            <li>
                                <a href="javascript:void(0)" aria-label="Instagram">
                                    <i class="lni lni-instagram"></i>
                                </a>
                            </li>
                            <li>
                                <a href="javascript:void(0)" aria-label="YouTube">
                                    <i class="lni lni-youtube"></i>
                                </a>
                            </li>
                            <li>
                                <a href="javascript:void(0)" aria-label="Google">
                                    <i class="lni lni-google"></i>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>

<a href="#" class="scroll-top">
    <i class="lni lni-chevron-up"></i>
</a>