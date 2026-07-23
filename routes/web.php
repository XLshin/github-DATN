<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\BankAccountController;
use App\Http\Controllers\Admin\BankAccountController as AdminBankAccountController;
use App\Http\Controllers\Admin\WalletWithdrawalController as AdminWalletWithdrawalController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\AssistantController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\CouponUserController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ImeiController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\PointController as AdminPointController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ProductGroupController;
use App\Http\Controllers\Admin\ShipmentController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\WarrantyController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PointController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ClientCouponController;
use App\Http\Controllers\ClientWarrantyController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\Admin\RefundController;
use App\Http\Controllers\Admin\WalletTopupController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\CarrierWebhookController;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| Public routes
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
Route::get('/categories/{category}', [HomeController::class, 'byCategory'])->name('category.products');
Route::get('/brands/{brand}', [HomeController::class, 'byBrand'])->name('brand.products');

Route::middleware('auth')->group(function () {
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
    Route::post('/buy-now', [CartController::class, 'buyNow'])->name('buy.now');
    Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
    Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
    Route::get('/cart/count', [CartController::class, 'count'])->name('cart.count');
});

// DEV helper: quick-login demo user (only in local)
if (app()->environment('local')) {
    Route::get('/dev-login-xuanbac', function () {
        $email = 'xuanbac@example.com';
        $user = User::firstWhere('email', $email);
        if (! $user) {
            $user = User::create([
                'name' => 'Xuân Bắc',
                'email' => $email,
                'password' => bcrypt('password'),
                'role' => 'customer',
            ]);
        }
        Auth::login($user);
        request()->session()->regenerate();
        return redirect()->route('dashboard');
    });
}

// Webhook endpoints
Route::post('/webhook/payment', [WebhookController::class, 'paymentCallback']);
Route::post('/webhook/carrier/{code}', [CarrierWebhookController::class, 'handle']);

/*
|--------------------------------------------------------------------------
| Guest authentication (US01-US04)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');

    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');

    Route::get('/forgot-password', [PasswordController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [PasswordController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password', [PasswordController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [PasswordController::class, 'resetPassword'])->name('password.update');
});

/*
|--------------------------------------------------------------------------
| Authenticated user routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    // Route::get('/dashboard', fn() => view('client.profile.dashboard'))->name('dashboard');
    // Sửa thành:
    Route::get('/dashboard', [ProfileController::class, 'dashboard'])->name('dashboard');

    // Giữ nguyên các route profile khác nếu cần
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Thêm group quản lý địa chỉ (vẫn trong middleware auth)
    Route::middleware('auth')->group(function () {
        // ... các route khác

        // Địa chỉ nhận hàng
        Route::post('/addresses', [AddressController::class, 'store'])->name('addresses.store');
        Route::put('/addresses/{address}', [AddressController::class, 'update'])->name('addresses.update');
        Route::delete('/addresses/{address}', [AddressController::class, 'destroy'])->name('addresses.destroy');
        Route::patch('/addresses/{address}/default', [AddressController::class, 'setDefault'])->name('addresses.default');
    });

    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('/change-password', [PasswordController::class, 'showChangePassword'])->name('password.change');
    Route::put('/change-password', [PasswordController::class, 'changePassword'])->name('password.change.update');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::post('/products/{product}/reviews', [ReviewController::class, 'store'])->name('reviews.store');

    // Client: My vouchers & warranty lookup
    Route::get('/my-vouchers', [ClientCouponController::class, 'index'])->name('client.vouchers.index');
    Route::post('/my-vouchers/{coupon}/claim', [ClientCouponController::class, 'claim'])->name('client.vouchers.claim');

    Route::get('/warranty', [ClientWarrantyController::class, 'showLookupForm'])->name('warranties.lookup');
    Route::get('/warranty/{warranty}', [ClientWarrantyController::class, 'show'])->name('warranties.show');

    Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
    Route::post('/checkout', [CheckoutController::class, 'process'])->name('checkout.process');
    Route::post('/checkout/preview', [CheckoutController::class, 'preview'])->name('checkout.preview');
    Route::get('/checkout/success/{order}', [CheckoutController::class, 'success'])->name('checkout.success');
    Route::get('/checkout/payment/{order}', [CheckoutController::class, 'showPayment'])->name('checkout.payment');
    Route::post('/checkout/payment/{order}/confirm', [CheckoutController::class, 'confirmPayment'])->name('checkout.payment.confirm');
    Route::post('/checkout/payment/{order}/retry', [CheckoutController::class, 'retryPayment'])->name('checkout.payment.retry');

    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{id}', [OrderController::class, 'show'])->name('orders.show');
    Route::get('/orders/{id}/status-check', [OrderController::class, 'statusCheck'])->name('orders.statusCheck');
    Route::post('/orders/{id}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');

    Route::get('/wallet', [WalletController::class, 'index'])->name('wallet.index');
    Route::post('/wallet/topup', [WalletController::class, 'topup'])->name('wallet.topup');
    Route::get('/wallet/topup/{topup}', [WalletController::class, 'showTopupPayment'])->name('wallet.topup.payment');
    Route::post('/wallet/topup/{topup}/confirm', [WalletController::class, 'confirmTopupPayment'])->name('wallet.topup.confirm');
    Route::post('/wallet/topup/{topup}/retry', [WalletController::class, 'retryTopupPayment'])->name('wallet.topup.retry');
    Route::post('/wallet/withdraw', [WalletController::class, 'withdraw'])->name('wallet.withdraw');

    Route::post('/bank-accounts', [BankAccountController::class, 'store'])->name('bank-accounts.store');
    Route::delete('/bank-accounts/{bankAccount}', [BankAccountController::class, 'destroy'])->name('bank-accounts.destroy');
    Route::patch('/bank-accounts/{bankAccount}/default', [BankAccountController::class, 'setDefault'])->name('bank-accounts.default');

    Route::get('/my-points', [PointController::class, 'index'])->name('points.index');
    Route::get('/point-history', [PointController::class, 'history'])->name('points.history');

    Route::post('/assistant/chat', [AssistantController::class, 'chat'])->name('assistant.chat');
    Route::post('/assistant/reset', [AssistantController::class, 'reset'])->name('assistant.reset');
});

/*
|--------------------------------------------------------------------------
| Admin routes (auth + admin/staff middleware)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin_or_staff'])->group(function () {
    Route::redirect('/admin', '/admin/dashboard');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/notifications/pending-count', [\App\Http\Controllers\Admin\NotificationController::class, 'pendingCount'])
            ->name('notifications.pendingCount');

        /*
        |--------------------------------------------------------------------------
        | Chỉ admin được quản lý người dùng, danh mục, thương hiệu, voucher, điểm
        |--------------------------------------------------------------------------
        */
        Route::middleware('only_admin')->group(function () {
            Route::resource('users', AdminUserController::class)->only([
                'index',
                'show',
                'create',
                'store',
            ]);

            Route::patch('users/{user}/toggle-lock', [AdminUserController::class, 'toggleLock'])
                ->name('users.toggle-lock');

            Route::resource('categories', CategoryController::class);
            Route::resource('brands', BrandController::class);

            Route::resource('coupons', CouponController::class);
            Route::get('coupons/{coupon}/assign-users', [CouponUserController::class, 'edit'])
                ->name('coupons.assign-users-edit');
            Route::patch('coupons/{coupon}/assign-users', [CouponUserController::class, 'update'])
                ->name('coupons.assign-users-update');

            Route::get('points', [AdminPointController::class, 'index'])
                ->name('points.index');
            Route::get('points/{user}', [AdminPointController::class, 'show'])
                ->name('points.show');
            Route::post('points/{user}/add', [AdminPointController::class, 'addPoints'])
                ->name('points.add');
            Route::post('points/{user}/deduct', [AdminPointController::class, 'deductPoints'])
                ->name('points.deduct');
            Route::post('points/{user}/reset', [AdminPointController::class, 'reset'])
                ->name('points.reset');
        });

        /*
        |--------------------------------------------------------------------------
        | Sản phẩm
        |--------------------------------------------------------------------------
        */
        Route::resource('products', ProductGroupController::class)
            ->parameters(['products' => 'productGroup']);

        Route::resource('product-versions', AdminProductController::class)
            ->except(['index', 'create', 'store'])
            ->parameters(['product-versions' => 'product']);

        Route::get('product-groups/{productGroup}/specifications', [ProductGroupController::class, 'specifications'])
            ->name('product-groups.specifications');

        Route::patch('products/variants/{variant}/price', [ProductGroupController::class, 'updateVariantPrice'])
            ->middleware('only_admin')
            ->name('products.variants.price.update');

        // AJAX endpoint to quickly create a Product Group from the product create form
        Route::post('products/ajax-group', [AdminProductController::class, 'ajaxStore'])
            ->name('products.ajaxStore');

        Route::delete('product-images/{image}', [AdminProductController::class, 'destroyImage'])
            ->middleware('only_admin')
            ->name('products.image.destroy');

        Route::put('variants/{variant}', [AdminProductController::class, 'updateVariant'])
            ->middleware('only_admin')
            ->name('variants.update');

        Route::delete('variants/{variant}', [AdminProductController::class, 'destroyVariant'])
            ->middleware('only_admin')
            ->name('variants.destroy');

        Route::delete('variants/{variant}/main-image', [AdminProductController::class, 'destroyVariantMainImage'])
            ->middleware('only_admin')
            ->name('variants.image.destroy');

        Route::get('variants/{variant}', [AdminProductController::class, 'showVariant'])
            ->name('variants.show');

        /*
        |--------------------------------------------------------------------------
        | Đơn hàng
        |--------------------------------------------------------------------------
        */
        Route::get('orders', [AdminOrderController::class, 'index'])
            ->name('orders.index');

        Route::get('orders/{order}', [AdminOrderController::class, 'show'])
            ->name('orders.show');

        Route::post('orders/{order}/confirm', [AdminOrderController::class, 'confirm'])
            ->name('orders.confirm');

        Route::post('orders/{order}/confirm-payment', [AdminOrderController::class, 'confirmPayment'])
            ->name('orders.confirmPayment');
        Route::post('orders/{order}/reject-payment', [AdminOrderController::class, 'rejectPayment'])
            ->name('orders.rejectPayment');

        Route::post('orders/{order}/mark-packed', [AdminOrderController::class, 'markPacked'])
            ->name('orders.markPacked');

        Route::post('orders/{order}/handover', [AdminOrderController::class, 'handover'])
            ->name('orders.handover');

        Route::post('orders/{order}/mark-delivered', [AdminOrderController::class, 'markDelivered'])
            ->name('orders.markDelivered');

        Route::post('orders/{order}/mark-failed', [AdminOrderController::class, 'markFailed'])
            ->name('orders.markFailed');

        Route::post('orders/{order}/retry-delivery', [AdminOrderController::class, 'retryDelivery'])
            ->name('orders.retryDelivery');

        Route::post('orders/{order}/cancel', [AdminOrderController::class, 'cancel'])
            ->name('orders.cancel');

        Route::get('orders/{order}/print-shipping-label', [AdminOrderController::class, 'printShippingLabel'])
            ->name('orders.printShippingLabel');

        Route::post('/orders/{order}/receiver', [OrderController::class, 'updateReceiver'])
        ->name('orders.updateReceiver');

        /*
        |--------------------------------------------------------------------------
        | Ví & hoàn tiền
        |--------------------------------------------------------------------------
        */
        Route::get('wallet-topups', [WalletTopupController::class, 'index'])
            ->name('wallet-topups.index');
        Route::get('wallet-topups/{topup}', [WalletTopupController::class, 'show'])
            ->name('wallet-topups.show');
        Route::post('wallet-topups/{topup}/confirm', [WalletTopupController::class, 'confirm'])
            ->name('wallet-topups.confirm');
        Route::post('wallet-topups/{topup}/reject', [WalletTopupController::class, 'reject'])
            ->name('wallet-topups.reject');

        Route::get('refunds', [RefundController::class, 'index'])
            ->name('refunds.index');
        Route::get('refunds/{refund}', [RefundController::class, 'show'])
            ->name('refunds.show');
        Route::post('refunds/{refund}/processing', [RefundController::class, 'markProcessing'])
            ->name('refunds.processing');
        Route::post('refunds/{refund}/complete', [RefundController::class, 'complete'])
            ->name('refunds.complete');

        Route::get('wallet-withdrawals', [AdminWalletWithdrawalController::class, 'index'])
            ->name('wallet-withdrawals.index');
        Route::get('wallet-withdrawals/{withdrawal}', [AdminWalletWithdrawalController::class, 'show'])
            ->name('wallet-withdrawals.show');
        Route::post('wallet-withdrawals/{withdrawal}/processing', [AdminWalletWithdrawalController::class, 'markProcessing'])
            ->name('wallet-withdrawals.processing');
        Route::post('wallet-withdrawals/{withdrawal}/complete', [AdminWalletWithdrawalController::class, 'complete'])
            ->name('wallet-withdrawals.complete');
        Route::post('wallet-withdrawals/{withdrawal}/reject', [AdminWalletWithdrawalController::class, 'reject'])
            ->name('wallet-withdrawals.reject');

        Route::get('bank-accounts', [AdminBankAccountController::class, 'index'])
            ->name('bank-accounts.index');
        Route::post('bank-accounts/{bankAccount}/verify', [AdminBankAccountController::class, 'verify'])
            ->name('bank-accounts.verify');

        Route::get('bank-transaction-logs', [\App\Http\Controllers\Admin\BankTransactionLogController::class, 'index'])
            ->name('bank-transaction-logs.index');

        /*
        |--------------------------------------------------------------------------
        | Vận chuyển
        |--------------------------------------------------------------------------
        */
        Route::get('shipments/lookup', [ShipmentController::class, 'lookup'])
            ->name('shipments.lookup');

        Route::get('shipments/create-from-order/{order}', [ShipmentController::class, 'createFromOrder'])
            ->name('shipments.createFromOrder');

        Route::post('shipments/store-from-order/{order}', [ShipmentController::class, 'storeFromOrder'])
            ->name('shipments.storeFromOrder');

        Route::patch('shipments/{shipment}/status', [ShipmentController::class, 'updateStatus'])
            ->name('shipments.updateStatus');

        Route::resource('shipments', ShipmentController::class)->only([
            'index',
            'show',
            'create',
            'store',
        ]);

        /*
            |--------------------------------------------------------------------------
            | Bảo hành
            |--------------------------------------------------------------------------
            */
            Route::get('warranties/lookup-imei', [WarrantyController::class, 'lookupImei'])
                ->name('warranties.lookupImei');

            Route::get('warranties/{warranty}/receipt', [App\Http\Controllers\Admin\WarrantyController::class, 'receipt'])->name('warranties.receipt');
            Route::put('warranties/{warranty}/receipt', [App\Http\Controllers\Admin\WarrantyController::class, 'updateReceipt'])->name('warranties.updateReceipt');

            Route::resource('warranties', WarrantyController::class)->except([
                'destroy',
            ]);

        /*
        |--------------------------------------------------------------------------
        | IMEI, kho hàng, tồn kho
        |--------------------------------------------------------------------------
        */
        Route::resource('imeis', ImeiController::class)->except([
            'edit',
            'update',
            'destroy',
        ]);

        Route::middleware('only_admin')->group(function () {
            Route::get('inventory/imeis/bulk-transfer', [ImeiController::class, 'createBulkTransfer'])
                ->name('imeis.bulk-transfer.create');
            Route::post('inventory/imeis/bulk-transfer', [ImeiController::class, 'storeBulkTransfer'])
                ->name('imeis.bulk-transfer.store');

            Route::get('imeis/{imei}/edit', [ImeiController::class, 'edit'])
                ->name('imeis.edit');
            Route::put('imeis/{imei}', [ImeiController::class, 'update'])
                ->name('imeis.update');
            Route::patch('imeis/{imei}', [ImeiController::class, 'update']);
            Route::delete('imeis/{imei}', [ImeiController::class, 'destroy'])
                ->name('imeis.destroy');

            Route::get('inventory/adjustments/create', [InventoryController::class, 'createAdjustment'])
                ->name('inventory.adjustments.create');
            Route::post('inventory/adjustments', [InventoryController::class, 'storeAdjustment'])
                ->name('inventory.adjustments.store');
        });

        Route::resource('inventory', InventoryController::class)->only([
            'index',
            'create',
            'store',
        ]);

        Route::get('/stocks', [ImeiController::class, 'stock'])
            ->name('stocks');

        Route::get('/stocks/accessories', [ImeiController::class, 'accessoryStock'])
            ->name('stocks.accessories');

        /*
        |--------------------------------------------------------------------------
        | Đánh giá
        |--------------------------------------------------------------------------
        */
        Route::get('reviews', [ReviewController::class, 'index'])
            ->name('reviews.index');

        Route::patch('reviews/{review}/hide', [ReviewController::class, 'hide'])
            ->name('reviews.hide');

        Route::delete('reviews/{review}', [ReviewController::class, 'destroy'])
            ->middleware('only_admin')
            ->name('reviews.destroy');

        // Banner — chỉ admin
        Route::middleware('only_admin')->group(function () {
            Route::resource('banners', \App\Http\Controllers\Admin\BannerController::class);
            Route::patch('banners/{banner}/toggle', [\App\Http\Controllers\Admin\BannerController::class, 'toggleStatus'])
                ->name('banners.toggle');
        });
    });
});
