# Hướng dẫn test: Giỏ hàng, Thanh toán, Ví, Hoàn tiền, Rút tiền, Thông báo

Tài liệu này liệt kê toàn bộ luồng (giao thức) liên quan đến giỏ hàng và thanh toán trong dự án,
kèm bước test cụ thể + kết quả mong đợi. Dùng để kiểm tra thủ công sau mỗi lần thay đổi code.

> Ghi chú chung: đây là đồ án — mọi cổng thanh toán (MoMo, VNPAY, ngân hàng, thẻ) đều được **mô
> phỏng**, không gọi API thật. Việc "ngân hàng báo có tiền" được giả lập bằng cột
> `simulate_confirm_at` (random 8–20 giây sau khi tạo giao dịch) và được xác nhận tự động qua 3 lớp
> (xem mục 8), không cần webhook thật.

---

## 0. Chuẩn bị trước khi test

- Tài khoản khách hàng (`role = customer`) đã đăng nhập.
- Có ít nhất 1 địa chỉ giao hàng, 1 tài khoản ngân hàng đã liên kết và **đã xác minh**
  (`is_verified = true`) nếu muốn test rút tiền/hoàn tiền ngân hàng.
- Có sản phẩm còn hàng trong kho (biến thể `stock_quantity > 0` hoặc còn IMEI `available`).
- Mở DevTools > tab Network để quan sát các request `fetch` (đặc biệt là `/notifications/recent`
  poll mỗi ~25s) khi cần debug chuông thông báo.

---

## 1. Giỏ hàng (`/cart`)

| Bước | Thao tác | Route | Kết quả mong đợi |
|---|---|---|---|
| 1.1 | Thêm sản phẩm vào giỏ từ trang chi tiết sản phẩm | `POST cart.add` | Badge số lượng giỏ hàng ở navbar (`#nav-cart-count`) tăng lên, không cần tải lại trang |
| 1.2 | Vào `/cart`, tăng/giảm số lượng bằng nút +/- | `POST cart.update` | Tổng tiền dòng + tổng giỏ hàng cập nhật realtime (debounce ~600ms), số lượng lưu lại nếu tải lại trang |
| 1.3 | Nhập số lượng vượt tồn kho | `POST cart.update` | Bị chặn, input tự trả về `max_quantity` server cho phép + thông báo lỗi |
| 1.4 | Bỏ chọn 1 sản phẩm bằng checkbox dòng | JS client-side | Tổng tiền tính lại chỉ trên các dòng đang chọn, nút "Thanh toán ngay" disable nếu không chọn gì |
| 1.5 | Xóa 1 sản phẩm khỏi giỏ | `POST cart.remove` | Dòng biến mất, nếu giỏ hàng trống → hiện trạng thái trống (minh họa SVG giỏ hàng) |
| 1.6 | Giỏ hàng trống, bấm "Mua sắm ngay" | — | Điều hướng về trang chủ |
| 1.7 | Bấm "Thanh toán ngay" với các dòng đã chọn | — | Điều hướng tới `/checkout?items=...` đúng với các item đã chọn |

---

## 2. Trang Checkout (`/checkout`)

| Bước | Thao tác | Kết quả mong đợi |
|---|---|---|
| 2.1 | Vào thẳng `/checkout` khi giỏ hàng trống | Hiện minh họa + cảnh báo "Giỏ hàng trống", có link "Tiếp tục mua sắm" |
| 2.2 | Chọn "Mua hộ người khác" | Hiện ô nhập tên/SĐT người đặt mua (khác người nhận), mặc định lấy theo tài khoản |
| 2.3 | Chọn 1 địa chỉ đã lưu | Radio chọn đúng, form ẩn tự điền tên/SĐT/địa chỉ theo lựa chọn |
| 2.4 | Bấm "Thêm địa chỉ mới" → điền → "Lưu địa chỉ" | Gọi `POST addresses.store` (AJAX), sau khi lưu **tải lại trang** để lấy đúng id địa chỉ mới, danh sách địa chỉ cập nhật |
| 2.5 | Chọn 1 voucher hợp lệ trong dropdown | `POST checkout.preview` (debounce 350ms) → dòng "Giảm voucher" hiện ra, tổng cộng giảm đúng |
| 2.6 | Chọn voucher không đủ điều kiện (đơn tối thiểu) | Hiện banner lỗi đỏ phía trên form, tổng tiền reset về giá gốc |
| 2.7 | Nhập số điểm dùng vượt quá điểm hiện có | `checkout.preview` tự giới hạn lại `points_to_use` theo điểm tối đa có thể quy đổi |
| 2.8 | Chọn phương thức "Ví ByteZone" khi số dư không đủ | Radio bị `disabled`, phụ đề hiện "không đủ để thanh toán đơn này" kèm link "Nạp thêm" |
| 2.9 | Bấm "Đặt hàng" | Route theo `payment_method` — xem mục 3 |

---

## 3. Đặt hàng theo từng phương thức thanh toán

Route xử lý: `POST checkout.process` → `CheckoutService::process()` tạo `Order` + `Payment`, sau đó
điều hướng tùy phương thức.

### 3.1 COD (thanh toán khi nhận hàng)
- Kết quả: chuyển thẳng `checkout.success`, đơn ở trạng thái `pending`/`fulfillment_status=pending`.
- Không có `Payment` cần xác nhận online (payment_status mặc định `pending`, chỉ chuyển `paid` khi
  admin xác nhận đã giao — xem mục 5).

### 3.2 Ví ByteZone (`wallet`)
- Trừ ví **ngay lập tức** lúc đặt hàng (nếu không đủ số dư → rollback toàn bộ, báo lỗi).
- `Payment.payment_status = paid` ngay, `Order.status = processing`.
- Chuyển thẳng `checkout.success`, **không** qua trang `checkout.payment`.

### 3.3 Chuyển khoản ngân hàng (`bank_transfer`)
- Chuyển tới `checkout.payment` → hiện QR VietQR + thông tin chuyển khoản (nội dung CK = mã đơn).
- `simulate_confirm_at` = ngay lúc tạo + random 8–20 giây.
- **Test tự động xác nhận**: đợi 10–25 giây, trang tự poll `checkout.payment.status` mỗi 4s →
  badge chuyển "Đã xác nhận thanh toán!" → tự chuyển sang `checkout.success`. Không cần thao tác gì.
- **Test lối dự phòng thủ công**: mở khối `<details>` "Gửi ảnh biên lai để đối soát thủ công",
  upload ảnh bất kỳ → `POST checkout.payment.confirm` → chuyển `checkout.success` với thông báo
  "chờ đối soát" (admin phải duyệt tay ở `/admin/orders/{id}`).
- **Test hết hạn**: nếu quá 15 phút không thanh toán, tải lại trang → hiện màn "Giao dịch đã hết
  hạn" (minh họa SVG đồng hồ) + nút "Thử lại thanh toán" (`checkout.payment.retry`) → mở phiên mới,
  tồn kho được cấp lại.

### 3.4 Ví MoMo / VNPAY (`momo` / `vnpay`)
- Giống hệt luồng 3.3 (badge "Đang chờ thanh toán" tự poll, tự xác nhận trong 8–20s, có lối dự
  phòng gửi ảnh thủ công trong `<details>`).
- Khác biệt: mỗi phương thức có QR/màu thương hiệu riêng; VNPAY có thêm tab "ATM/Internet Banking"
  (chỉ là UI minh họa, không thao tác thật).

### 3.5 Thẻ tín dụng/ghi nợ (`card`)
- Chuyển tới `checkout.payment`, nhập số thẻ / tên chủ thẻ / hạn dùng / CVV.
- **Test số thẻ hợp lệ**: dùng `4111 1111 1111 1111` (Luhn hợp lệ), hạn dùng tương lai (VD `12/28`)
  → bấm "Thanh toán" → xử lý **ngay lập tức** (không cần đợi random delay, không cần ảnh minh
  chứng) → chuyển `checkout.success` với "Thanh toán thẻ thành công!".
- **Test số thẻ không hợp lệ Luhn**: bất kỳ dãy số nào không qua kiểm tra Luhn → lỗi
  "Số thẻ không hợp lệ".
- **Test thẻ bị từ chối (mô phỏng)**: số thẻ kết thúc bằng `0000` (VD `4111 1111 1111 0000`) → lỗi
  "Giao dịch bị từ chối bởi ngân hàng phát hành thẻ".
- **Test thẻ hết hạn**: nhập `MM/YY` ở quá khứ → lỗi "Thẻ đã hết hạn".

---

## 4. Trạng thái đơn hàng & vòng đời (Admin)

Test tại `/admin/orders/{id}`, mỗi bước xong kiểm tra: (a) `fulfillment_status` đổi đúng,
(b) chuông thông báo của khách (đăng nhập tab khác) nhận được thông báo tương ứng trong ≤25s.

| Trạng thái hiện tại | Hành động admin | Route | Trạng thái mới | Thông báo khách nhận |
|---|---|---|---|---|
| `pending` | Xác nhận đơn (yêu cầu đã thanh toán nếu trả trước) | `admin.orders.confirm` | `waiting_pack` | "Đơn hàng đã được xác nhận" |
| Payment `pending` (trả trước) | Xác nhận đã nhận tiền (nhập đúng số tiền) | `admin.orders.confirmPayment` | Payment → `paid`, Order → `processing` | — |
| Payment `pending` | Từ chối thanh toán | `admin.orders.rejectPayment` | Order → `cancelled` tự động | Email PaymentFailed + "Đơn hàng đã bị hủy" |
| `waiting_pack` | Đóng gói + gán IMEI + upload ảnh | `admin.orders.markPacked` | `waiting_handover` | "Đóng gói hoàn tất" |
| `waiting_handover` | Bàn giao vận chuyển | `admin.orders.handover` | `shipping` | "Đơn hàng đang được giao" |
| `shipping` | Xác nhận giao thành công + upload ảnh | `admin.orders.markDelivered` | `completed` | "Giao hàng thành công" |
| `shipping` | Báo giao thất bại | `admin.orders.markFailed` | `failed` | "Giao hàng không thành công" |
| `failed` | Giao lại | `admin.orders.retryDelivery` | `shipping` | "Đơn hàng đang được giao" |
| `pending`/`waiting_pack` | Hủy đơn (admin) | `admin.orders.cancel` | `cancelled` | "Đơn hàng đã bị hủy" (+ hoàn tiền nếu đã thanh toán, xem mục 5) |
| `pending`/`waiting_pack` | Khách tự hủy | `orders.cancel` (client) | `cancelled` | Không tự thông báo (khách tự thao tác) |

---

## 5. Hoàn tiền (Refund)

Kích hoạt khi: đơn đã thanh toán (`payment_status = paid`) bị hủy (admin hoặc khách tự hủy).
Ngưỡng tự động: **`AUTO_REFUND_MAX_AMOUNT = 5.000.000đ`** (`app/Models/RefundRequest.php`).

| Kịch bản | Điều kiện | Kết quả |
|---|---|---|
| 5.1 Hoàn vào Ví | `refund_method = wallet` | Cộng tiền vào ví **ngay lập tức**, `status = completed`, có thông báo + log mô phỏng Email/SMS ngay |
| 5.2 Hoàn ngân hàng, ≤ 5.000.000đ | `refund_method = bank`, amount ≤ ngưỡng | `status = pending` với `simulate_confirm_at` (8–20s) → tự động chuyển `completed` (xem mục 8), sau đó có thông báo + log Email/SMS |
| 5.3 Hoàn ngân hàng, > 5.000.000đ | `refund_method = bank`, amount > ngưỡng | `status = pending`, **không** có `simulate_confirm_at` → bắt buộc admin vào `/admin/refunds/{id}` upload ảnh minh chứng đã chuyển khoản (`admin.refunds.complete`) → mới `completed` + thông báo |
| 5.4 Khách tự hủy đơn, chọn hoàn ngân hàng | Trang `orders.cancel` (client) | Y hệt 5.2/5.3 tùy số tiền |
| 5.5 Admin hủy đơn thay khách | `admin.orders.cancel` | Luôn mặc định hoàn vào **Ví** (method = wallet) để không phải chờ khách cung cấp tài khoản ngân hàng |

**Cách test nhanh 5.2 (không cần đợi thật 8-20s)**: dùng tinker để set `simulate_confirm_at` về quá
khứ rồi gọi lại `/notifications/recent` (hoặc đợi tối đa ~25s nếu đang đăng nhập, chuông sẽ tự kích
hoạt xác nhận — xem mục 8).

Kiểm tra sau khi hoàn tất (mọi kịch bản): log `storage/logs/laravel.log` phải có 2 dòng
`[MÔ PHỎNG EMAIL] Gửi email báo hoàn tiền` và `[MÔ PHỎNG SMS] Gửi SMS báo hoàn tiền`, đồng thời có
1 bản ghi trong bảng `notifications` loại `refund`.

---

## 6. Nạp ví (`/wallet` → "Nạp tiền")

Giống hệt luồng thanh toán checkout (mục 3.3–3.5) nhưng áp dụng cho nạp ví:

| Phương thức | Route trang thanh toán | Hành vi |
|---|---|---|
| `bank_transfer` / `momo` / `vnpay` | `wallet.topup.payment` | Badge tự poll `wallet.topup.status`, tự cộng tiền sau 8–20s; có lối dự phòng gửi ảnh thủ công |
| `card` | `wallet.topup.payment` | Nhập thẻ hợp lệ (Luhn, còn hạn, không kết thúc `0000`) → cộng tiền **ngay lập tức**, không cần ảnh |

Kiểm tra sau khi cộng tiền thành công: `wallet_balance` của user tăng đúng số tiền, có thông báo
loại `wallet` trong chuông, log có `[MÔ PHỎNG EMAIL]`/`[MÔ PHỎNG SMS] ... báo nạp ví thành công`.

**Test hết hạn/thử lại**: giống mục 3.3 (`wallet.topup.retry`).

---

## 7. Rút tiền (`/wallet` → "Rút tiền")

Route: `POST wallet.withdraw` → `WalletWithdrawalService::request()`. Số tiền tối thiểu
**`MIN_AMOUNT = 50.000đ`**, ngưỡng tự động **`AUTO_WITHDRAWAL_MAX_AMOUNT = 2.000.000đ`**
(`app/Models/WalletWithdrawal.php`).

| Kịch bản | Điều kiện | Kết quả |
|---|---|---|
| 7.1 Tài khoản ngân hàng chưa xác minh | `is_verified = false` | Lỗi ngay, không tạo được yêu cầu |
| 7.2 Rút < 50.000đ | — | Lỗi "Số tiền rút tối thiểu là 50.000 đ" |
| 7.3 Rút ≤ 2.000.000đ | Tài khoản đã xác minh | Trừ ví ngay (tạm giữ), `status = pending` + `simulate_confirm_at` → tự động `completed` sau 8-20s (xem mục 8), có thông báo + log Email/SMS |
| 7.4 Rút > 2.000.000đ | Tài khoản đã xác minh | Trừ ví ngay, `status = pending`, không tự động — admin vào `/admin/wallet-withdrawals/{id}` upload ảnh minh chứng (`admin.wallet-withdrawals.complete`) → `completed` + thông báo |
| 7.5 Admin từ chối yêu cầu | Bất kỳ | `status = rejected`, **hoàn lại đúng số tiền đã tạm giữ vào ví khách** |

---

## 8. Cơ chế tự động xác nhận (không phụ thuộc cron)

Tất cả giao dịch mô phỏng (thanh toán checkout, nạp ví, hoàn tiền ngân hàng dưới ngưỡng, rút tiền
dưới ngưỡng) dùng chung 1 cột `simulate_confirm_at`. Có **3 lớp** đảm bảo nó luôn được xác nhận,
không phụ thuộc việc có ai mở đúng trang:

1. **Trang polling trực tiếp** (nhanh nhất, ~4s/lần): trang `checkout.payment`,
   `wallet.topup.payment`, `orders.show` tự gọi API status khi đang mở.
2. **Chuông thông báo** (`notifications.recent`, poll mỗi 25s trên **mọi trang** khi đã đăng nhập):
   tiện thể quét toàn bộ giao dịch mô phỏng *của user đang xem* đã đến hạn và xác nhận luôn —
   đảm bảo dù khách không ở đúng trang, tối đa 25s sau vẫn có thông báo.
3. **Lệnh nền `php artisan transactions:confirm-simulated`** (chạy mỗi phút qua Laravel Scheduler,
   xem `app/Console/Kernel.php`): quét toàn hệ thống, phòng trường hợp không ai đăng nhập để kích
   hoạt lớp 1/2. **Cần server đã cấu hình cron/Task Scheduler gọi `php artisan schedule:run` mỗi
   phút** thì lớp này mới thực sự chạy nền — kiểm tra bằng lệnh:
   ```
   php artisan schedule:list
   ```

**Test nhanh lớp 3 thủ công** (không cần đợi cron thật):
```
php artisan transactions:confirm-simulated
```
Chạy xong kiểm tra lại trạng thái các bản ghi `pending` có `simulate_confirm_at` đã qua hạn.

---

## 9. Chuông thông báo (Navbar)

| Bước | Thao tác | Kết quả mong đợi |
|---|---|---|
| 9.1 | Đăng nhập, để yên trang bất kỳ ≥25s | Badge đỏ cạnh chuông hiện số thông báo chưa đọc (nếu có) |
| 9.2 | Bấm vào chuông | Dropdown hiện tối đa 8 thông báo gần nhất, thông báo chưa đọc có nền xanh nhạt |
| 9.3 | Bấm 1 thông báo | Điều hướng tới `url` tương ứng (đơn hàng/ví/voucher), đồng thời đánh dấu đã đọc |
| 9.4 | Bấm "Đánh dấu đã đọc" (trong dropdown) | Toàn bộ thông báo hết in đậm/nền xanh, badge về 0 |
| 9.5 | Vào `/notifications` | Trang danh sách đầy đủ (phân trang 20/trang), có thể lọc theo đọc/chưa đọc bằng mắt |
| 9.6 | Click ra ngoài dropdown | Dropdown tự đóng |
| 9.7 | Có thông báo mới xuất hiện giữa 2 lần poll | Chuông có hiệu ứng rung (class `ringing`) |

**Nếu chuông không hoạt động**: mở DevTools Console kiểm tra lỗi JS; kiểm tra tab Network xem
`/notifications/recent` có trả JSON hợp lệ (status 200) hay bị lỗi CORS/404 — nếu `APP_URL` trong
`.env` khác với host:port bạn đang truy cập (VD `.env` để `http://localhost` nhưng chạy
`127.0.0.1:8000`), toàn bộ link tuyệt đối do `route()` sinh ra sẽ sai origin; navbar hiện tại đã
dùng `route(..., [], false)` (đường dẫn tương đối) để tránh vấn đề này.

---

## 10. Bảng tổng hợp ngưỡng số tiền quan trọng

| Loại giao dịch | Hằng số | Giá trị | File |
|---|---|---|---|
| Hoàn tiền tự động tối đa | `RefundRequest::AUTO_REFUND_MAX_AMOUNT` | 5.000.000đ | `app/Models/RefundRequest.php` |
| Rút tiền tối thiểu | `WalletWithdrawal::MIN_AMOUNT` | 50.000đ | `app/Models/WalletWithdrawal.php` |
| Rút tiền tự động tối đa | `WalletWithdrawal::AUTO_WITHDRAWAL_MAX_AMOUNT` | 2.000.000đ | `app/Models/WalletWithdrawal.php` |
| Nạp ví tối thiểu | — (validate trong `WalletController::topup`) | 10.000đ | `app/Http/Controllers/WalletController.php` |
| Phiên thanh toán online hết hạn | `CheckoutService::PAYMENT_EXPIRY_MINUTES` | 15 phút | `app/Services/CheckoutService.php` |
| Phiên mô phỏng xác nhận | random 8–20 giây sau khi tạo | — | mọi nơi có `simulate_confirm_at` |

---

## 11. Checklist test hồi quy nhanh (5 phút)

1. Thêm sản phẩm vào giỏ → badge tăng.
2. Checkout bằng `card` với số thẻ `4111 1111 1111 1111`, hạn `12/30` → thanh toán thành công ngay.
3. Vào `/admin/orders`, xác nhận → đóng gói → bàn giao → giao thành công cho đơn vừa tạo.
4. Kiểm tra chuông của khách nhận đủ 4 thông báo tương ứng (trong ≤25s mỗi bước).
5. Hủy 1 đơn đã thanh toán < 5.000.000đ (chọn hoàn qua ngân hàng) → đợi ~20s → refund tự
   `completed`, có thông báo "Hoàn tiền thành công".
6. Rút 100.000đ từ ví → đợi ~20s → tự `completed`, có thông báo "Rút tiền thành công".
7. Kiểm tra `storage/logs/laravel.log` có đủ các dòng `[MÔ PHỎNG EMAIL]` / `[MÔ PHỎNG SMS]` tương
   ứng với bước 5 và 6.
