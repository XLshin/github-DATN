@extends('layouts.admin')

@section('title', 'Banner')
@section('page_icon', 'bi-image')
@section('page_eyebrow', 'Giao diện')
@section('page_title', 'Danh sách Banner')
@section('page_subtitle', 'Quản lý banner hiển thị trên trang chủ.')

@section('heading_actions')
    <a href="{{ route('admin.banners.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg"></i> Thêm banner
    </a>
@endsection

@section('content')
<section class="panel">
    <div class="panel-header">
        <div>
            <h2 class="h5 mb-1 section-title"><i class="bi bi-image"></i><span>Banner</span></h2>
            <p class="text-muted mb-0">
                Tổng {{ $banners->total() }} banner —
                <span class="text-success fw-semibold">{{ $banners->where('status', true)->count() }} đang hiện</span>
            </p>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Ảnh</th>
                    <th>Tiêu đề</th>
                    <th>Link</th>
                    <th>Lịch hẹn</th>
                    <th>Trạng thái</th>
                    <th class="text-end">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($banners as $banner)
                <tr class="{{ $banner->status ? '' : 'opacity-75' }}">
                    <td>{{ $banner->id }}</td>
                    <td>
                        <img src="{{ asset('storage/' . $banner->image) }}"
                             style="width:120px;height:60px;object-fit:cover"
                             class="rounded {{ $banner->status ? '' : 'grayscale' }}"
                             alt="{{ $banner->title }}">
                    </td>
                    <td class="fw-semibold">{{ $banner->title }}</td>
                    <td>
                        @if($banner->link)
                            <a href="{{ $banner->link }}" target="_blank"
                               class="text-truncate d-inline-block" style="max-width:160px">
                                {{ $banner->link }}
                            </a>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td style="min-width:160px">
                        @if($banner->starts_at || $banner->ends_at)
                            <div class="small">
                                @if($banner->starts_at)
                                    <div><i class="bi bi-play-circle text-success"></i>
                                        {{ $banner->starts_at->format('d/m/Y H:i') }}
                                    </div>
                                @endif
                                @if($banner->ends_at)
                                    <div><i class="bi bi-stop-circle text-danger"></i>
                                        {{ $banner->ends_at->format('d/m/Y H:i') }}
                                    </div>
                                @endif
                            </div>
                        @else
                            <span class="text-muted small">Không hẹn giờ</span>
                        @endif
                    </td>
                    <td>
                        @php $sched = $banner->schedule_status; @endphp
                        @if($sched === 'active')
                            <span class="badge bg-success-subtle text-success border border-success-subtle">
                                <i class="bi bi-eye"></i> Đang hiện
                            </span>
                        @elseif($sched === 'scheduled')
                            <span class="badge bg-info-subtle text-info border border-info-subtle">
                                <i class="bi bi-clock"></i> Chờ bắt đầu
                            </span>
                        @elseif($sched === 'expired')
                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle">
                                <i class="bi bi-clock-history"></i> Đã hết hạn
                            </span>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary border">
                                <i class="bi bi-eye-slash"></i> Đang ẩn
                            </span>
                        @endif
                    </td>
                    <td class="text-end">
                        {{-- Nút áp dụng / bỏ áp dụng --}}
                        <form action="{{ route('admin.banners.toggle', $banner) }}" method="POST" class="d-inline">
                            @csrf @method('PATCH')
                            @if($banner->status)
                                <button type="submit" class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-eye-slash"></i> Bỏ áp dụng
                                </button>
                            @else
                                <button type="submit" class="btn btn-success btn-sm">
                                    <i class="bi bi-check-lg"></i> Áp dụng
                                </button>
                            @endif
                        </form>
                        <a href="{{ route('admin.banners.edit', $banner) }}" class="btn btn-light btn-sm">
                            <i class="bi bi-pencil"></i> Sửa
                        </a>
                        <form action="{{ route('admin.banners.destroy', $banner) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Xóa banner này?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">Chưa có banner nào</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($banners->hasPages())
        <div class="p-3">{{ $banners->links() }}</div>
    @endif
</section>

@push('styles')
<style>
    .grayscale { filter: grayscale(1); }
</style>
@endpush
@endsection
