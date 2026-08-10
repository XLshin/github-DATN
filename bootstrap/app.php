<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'admin_or_staff' => \App\Http\Middleware\AdminOrStaffMiddleware::class,
            'only_admin' => \App\Http\Middleware\OnlyAdminMiddleware::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\ForceHttps::class,
        ]);
<<<<<<< HEAD

        // Webhook từ dịch vụ bên ngoài (SePay, đơn vị vận chuyển...) không gửi kèm CSRF token.
        $middleware->validateCsrfTokens(except: [
            'webhook/*',
        ]);
=======
>>>>>>> 204f2abead4a1d35f4d5df9f5cb75a9805df8706
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();
