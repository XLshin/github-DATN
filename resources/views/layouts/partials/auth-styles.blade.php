<style>
    * { box-sizing: border-box; font-family: Arial, Helvetica, sans-serif; }
    body {
        margin: 0;
        min-height: 100vh;
        background: linear-gradient(135deg, #1e3a8a, #2563eb, #0f172a);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px;
    }
    .auth-wrapper {
        width: 100%;
        max-width: 980px;
        min-height: 560px;
        background: #fff;
        border-radius: 24px;
        overflow: hidden;
        display: grid;
        grid-template-columns: 1fr 1fr;
        box-shadow: 0 24px 70px rgba(15, 23, 42, 0.35);
    }
    .auth-wrapper.single-column {
        max-width: 500px;
        min-height: auto;
        grid-template-columns: 1fr;
    }
    .auth-banner {
        background: linear-gradient(160deg, #0f172a, #1d4ed8);
        color: #fff;
        padding: 56px 44px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    .auth-banner h1 { font-size: 38px; line-height: 1.2; margin: 0 0 18px; }
    .auth-banner p { font-size: 16px; line-height: 1.7; color: #dbeafe; margin: 0; }
    .auth-card { padding: 56px 44px; display: flex; flex-direction: column; justify-content: center; }
    .auth-card h2 { margin: 0 0 8px; font-size: 30px; color: #0f172a; }
    .auth-card .subtitle { margin: 0 0 28px; color: #64748b; font-size: 15px; }
    .alert-success { background: #dcfce7; color: #166534; padding: 12px 14px; border-radius: 10px; margin-bottom: 18px; font-size: 14px; }
    .alert-error { background: #fee2e2; color: #991b1b; padding: 12px 14px; border-radius: 10px; margin-bottom: 18px; font-size: 14px; }
    .form-group { margin-bottom: 18px; }
    .form-group label { display: block; margin-bottom: 8px; color: #334155; font-weight: 600; font-size: 14px; }
    .form-control {
        width: 100%; height: 46px; border: 1px solid #cbd5e1; border-radius: 12px;
        padding: 0 14px; font-size: 15px; outline: none; transition: 0.2s;
    }
    .form-control:focus { border-color: #2563eb; box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12); }
    .form-control.is-invalid { border-color: #dc2626; }
    .error-message { margin-top: 6px; color: #dc2626; font-size: 13px; }
    .hint { margin-top: 6px; color: #64748b; font-size: 13px; }
    .remember-row { display: flex; align-items: center; gap: 8px; margin: 4px 0 22px; color: #475569; font-size: 14px; }
    .btn-primary {
        width: 100%; height: 48px; border: none; border-radius: 12px;
        background: #2563eb; color: #fff; font-size: 16px; font-weight: 700; cursor: pointer;
    }
    .btn-primary:hover { background: #1d4ed8; }
    .auth-footer { margin-top: 24px; text-align: center; color: #64748b; font-size: 14px; }
    .auth-footer a { color: #2563eb; font-weight: 700; text-decoration: none; }
    .captcha-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 14px; margin-top: 16px; }
    @media (max-width: 768px) {
        .auth-wrapper { grid-template-columns: 1fr; }
        .auth-banner { display: none; }
        .auth-card { padding: 38px 24px; }
    }
</style>
