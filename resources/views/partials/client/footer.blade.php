<footer class="footer" style="background:#f8f9fa; color:#333;">

    {{-- ƯU ĐÃI DỊCH VỤ --}}
    <div class="container py-4" style="border-bottom:1px solid #e0e0e0;">
        <div class="row g-3 text-center">
            @foreach([['🚚','Giao hàng nhanh','Toàn quốc 24h'],['🛡️','Bảo hành chính hãng','12-24 tháng'],['💳','Thanh toán đa dạng','COD, VNPay, MoMo'],['🔄','Đổi trả dễ dàng','7 ngày đổi trả']] as $item)
            <div class="col-6 col-md-3">
                <div class="p-3 rounded-3 border">
                    <div style="font-size:1.8rem;">{{ $item[0] }}</div>
                    <div class="fw-semibold mt-1" style="font-size:13px; color:#222;">{{ $item[1] }}</div>
                    <div style="font-size:11px; color:#777;">{{ $item[2] }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Footer Top --}}
    <div class="footer-top" style="background:#f8f9fa;">
        <div class="container">
            <div class="inner-content" style="padding:40px 0; border-bottom:1px solid #e0e0e0;">
                <div class="row align-items-center justify-content-between">
                    <div class="col-lg-3 col-md-4 col-12">
                        <div class="footer-logo">
                            <a href="{{ route('home') }}">
                                <img src="{{ asset('assets-client/images/logo/logo.png') }}" alt="Byte Zone Store" style="width:200px;">
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-5 col-md-4 col-12">
                        <h4 style="color:#1565c0; font-size:17px; font-weight:600; margin:0 0 4px;">
                            Đăng ký nhận tin khuyến mãi
                        </h4>
                        <span style="color:#666; font-size:13px;">Nhận thông tin điện thoại mới, ưu đãi hot và mã giảm giá từ Byte Zone Store.</span>
                    </div>
                    <div class="col-lg-4 col-md-4 col-12 mt-3 mt-lg-0">
                        <form action="#" method="POST" class="d-flex gap-2">
                            @csrf
                            <input name="email" type="email" placeholder="Nhập email của bạn..." required
                                class="form-control"
                                style="background:#fff; color:#333; border:1px solid #ccc; border-radius:6px; height:42px;">
                            <button type="submit"
                                class="btn btn-primary flex-shrink-0"
                                style="height:42px; padding:0 20px; white-space:nowrap;">
                                Đăng ký
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Footer Middle --}}
    <div style="background:#f8f9fa; padding:40px 0; border-bottom:1px solid #e0e0e0;">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 col-md-6 col-12 mb-4">
                    <h3 style="color:#1565c0; font-size:16px; font-weight:600; margin-bottom:16px;">Liên hệ với chúng tôi</h3>
                    <p style="color:#333; font-weight:600; margin-bottom:8px;">Hotline: 0909 999 888</p>
                    <ul style="list-style:none; padding:0; margin-bottom:8px;">
                        <li style="color:#555; font-size:13px; margin-bottom:4px;"><span style="color:#333; font-weight:500;">Thứ 2 - Thứ 6:</span> 8:00 - 21:00</li>
                        <li style="color:#555; font-size:13px;"><span style="color:#333; font-weight:500;">Thứ 7 - Chủ nhật:</span> 9:00 - 20:00</li>
                    </ul>
                    <a href="mailto:support@bytezone.vn" style="color:#1565c0; font-size:13px;">support@bytezone.vn</a>
                </div>

                <div class="col-lg-3 col-md-6 col-12 mb-4">
                    <h3 style="color:#1565c0; font-size:16px; font-weight:600; margin-bottom:16px;">Về Byte Zone</h3>
                    <ul style="list-style:none; padding:0;">
                        <li style="margin-bottom:8px;"><a href="{{ url('/gioi-thieu') }}" style="color:#555; text-decoration:none; font-size:13px;">Giới thiệu</a></li>
                        <li style="margin-bottom:8px;"><a href="{{ url('/lien-he') }}" style="color:#555; text-decoration:none; font-size:13px;">Liên hệ</a></li>
                        <li style="margin-bottom:8px;"><a href="{{ url('/chinh-sach-bao-hanh') }}" style="color:#555; text-decoration:none; font-size:13px;">Chính sách bảo hành</a></li>
                        <li style="margin-bottom:8px;"><a href="{{ url('/chinh-sach-doi-tra') }}" style="color:#555; text-decoration:none; font-size:13px;">Chính sách đổi trả</a></li>
                        <li><a href="{{ url('/cau-hoi-thuong-gap') }}" style="color:#555; text-decoration:none; font-size:13px;">Câu hỏi thường gặp</a></li>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-6 col-12 mb-4">
                    <h3 style="color:#1565c0; font-size:16px; font-weight:600; margin-bottom:16px;">Danh mục sản phẩm</h3>
                    <ul style="list-style:none; padding:0;">
                        <li style="margin-bottom:8px;"><a href="{{ url('/san-pham?brand=iphone') }}" style="color:#555; text-decoration:none; font-size:13px;">iPhone</a></li>
                        <li style="margin-bottom:8px;"><a href="{{ url('/san-pham?brand=samsung') }}" style="color:#555; text-decoration:none; font-size:13px;">Samsung</a></li>
                        <li style="margin-bottom:8px;"><a href="{{ url('/san-pham?brand=xiaomi') }}" style="color:#555; text-decoration:none; font-size:13px;">Xiaomi</a></li>
                        <li style="margin-bottom:8px;"><a href="{{ url('/san-pham?brand=oppo') }}" style="color:#555; text-decoration:none; font-size:13px;">OPPO</a></li>
                        <li><a href="{{ url('/phu-kien') }}" style="color:#555; text-decoration:none; font-size:13px;">Phụ kiện điện thoại</a></li>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-6 col-12 mb-4">
                    <h3 style="color:#1565c0; font-size:16px; font-weight:600; margin-bottom:16px;">Dịch vụ hỗ trợ</h3>
                    <ul style="list-style:none; padding:0;">
                        <li style="margin-bottom:8px;"><a href="{{ url('/thu-cu-doi-moi') }}" style="color:#555; text-decoration:none; font-size:13px;">Thu cũ đổi mới</a></li>
                        <li style="margin-bottom:8px;"><a href="{{ url('/tra-gop') }}" style="color:#555; text-decoration:none; font-size:13px;">Trả góp 0%</a></li>
                        <li style="margin-bottom:8px;"><a href="{{ url('/giao-hang') }}" style="color:#555; text-decoration:none; font-size:13px;">Giao hàng nhanh</a></li>
                        <li style="margin-bottom:8px;"><a href="{{ url('/bao-hanh-mo-rong') }}" style="color:#555; text-decoration:none; font-size:13px;">Bảo hành mở rộng</a></li>
                        <li><a href="{{ url('/ho-tro-ky-thuat') }}" style="color:#555; text-decoration:none; font-size:13px;">Hỗ trợ kỹ thuật</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- Footer Bottom --}}
    <div style="background:#eef1f7; padding:20px 0;">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-4 col-12 mb-2 mb-lg-0">
                    <div class="d-flex align-items-center gap-2">
                        <span style="color:#555; font-size:13px;">Thanh toán:</span>
                        <img src="{{ asset('assets-client/images/footer/credit-cards-footer.png') }}" alt="Phương thức thanh toán" style="max-height:28px;">
                    </div>
                </div>
                <div class="col-lg-4 col-12 text-center mb-2 mb-lg-0">
                    <p style="color:#555; font-size:13px; margin:0;">
                        &copy; {{ date('Y') }} Byte Zone Store — Hệ thống thương mại điện tử.
                    </p>
                </div>
                <div class="col-lg-4 col-12">
                    <div class="d-flex align-items-center gap-3 justify-content-lg-end">
                        <span style="color:#555; font-size:13px;">Theo dõi:</span>
                        <a href="javascript:void(0)" aria-label="Facebook" style="color:#1565c0; font-size:18px;"><i class="lni lni-facebook-filled"></i></a>
                        <a href="javascript:void(0)" aria-label="Instagram" style="color:#1565c0; font-size:18px;"><i class="lni lni-instagram"></i></a>
                        <a href="javascript:void(0)" aria-label="YouTube" style="color:#1565c0; font-size:18px;"><i class="lni lni-youtube"></i></a>
                        <a href="javascript:void(0)" aria-label="Google" style="color:#1565c0; font-size:18px;"><i class="lni lni-google"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</footer>

<a href="#" class="scroll-top">
    <i class="lni lni-chevron-up"></i>
</a>
