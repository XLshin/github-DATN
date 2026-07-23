@extends('layouts.app')

@section('title', 'Thông báo')

@section('header')
    <div class="d-flex align-items-center justify-content-between gap-3">
        <div>
            <h1 class="h2 mb-1">Thông báo</h1>
            <p class="text-muted mb-0">Khuyến mãi, trạng thái đơn hàng và các cập nhật từ ByteZone</p>
        </div>
        @if($notifications->isNotEmpty())
            <form method="POST" action="{{ route('notifications.readAll') }}">
                @csrf
                <button class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-check2-all me-1"></i>Đánh dấu tất cả đã đọc
                </button>
            </form>
        @endif
    </div>
@endsection

@push('styles')
<style>
    .notif-item {
        display: flex;
        gap: 14px;
        padding: 16px;
        border: 1px solid #e6ebf2;
        border-radius: 10px;
        background: #fff;
        margin-bottom: 10px;
        text-decoration: none;
        color: inherit;
        transition: border-color .15s ease, background-color .15s ease;
    }
    .notif-item:hover { border-color: #9fc2ff; background: #f8faff; color: inherit; }
    .notif-item.unread { background: #f0f7ff; border-color: #cfe2ff; }
    .notif-icon {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        background: #eef5ff;
        color: #0d6efd;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 1.1rem;
    }
    .notif-item.unread .notif-icon { background: #0d6efd; color: #fff; }
</style>
@endpush

@section('content')
<div class="container py-3">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            @if($notifications->isEmpty())
                <div class="text-center py-5">
                    <svg width="140" height="112" viewBox="0 0 140 112" class="mb-3" aria-hidden="true">
                        <ellipse cx="70" cy="100" rx="46" ry="7" fill="#f1f3f5"/>
                        <path d="M70 18a26 26 0 0 0-26 26v18l-8 14h68l-8-14V44a26 26 0 0 0-26-26z" fill="#eef5ff" stroke="#9fc2ff" stroke-width="3"/>
                        <path d="M58 84a12 12 0 0 0 24 0" fill="none" stroke="#9fc2ff" stroke-width="3" stroke-linecap="round"/>
                    </svg>
                    <h2 class="h5 mb-2">Chưa có thông báo nào</h2>
                    <p class="text-muted mb-0">Khuyến mãi và cập nhật đơn hàng sẽ xuất hiện ở đây.</p>
                </div>
            @else
                @foreach($notifications as $notification)
                    @php
                        $data = $notification->data;
                    @endphp
                    <a href="{{ route('notifications.read', $notification->id) }}"
                       class="notif-item {{ $notification->read_at ? '' : 'unread' }}">
                        <span class="notif-icon"><i class="bi {{ $data['icon'] ?? 'bi-bell' }}"></i></span>
                        <div class="flex-grow-1">
                            <div class="fw-semibold">{{ $data['title'] ?? 'Thông báo' }}</div>
                            <div class="text-muted small mt-1">{{ $data['message'] ?? '' }}</div>
                            <div class="text-muted small mt-1">{{ $notification->created_at->diffForHumans() }}</div>
                        </div>
                    </a>
                @endforeach

                <div class="mt-3">
                    {{ $notifications->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
