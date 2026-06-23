@if (session('success'))
    <div class="alert-success">{{ session('success') }}</div>
@endif
@if (session('error'))
    <div class="alert-error">{{ session('error') }}</div>
@endif
@if (session('info'))
    <div class="alert-success">{{ session('info') }}</div>
@endif
