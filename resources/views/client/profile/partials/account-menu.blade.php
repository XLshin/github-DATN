@php
    $currentRouteName = Route::currentRouteName();
    $menuItems = [
        ['route' => 'dashboard', 'icon' => 'speedometer2', 'label' => 'Dashboard'],
        ['route' => 'orders.index', 'icon' => 'bag', 'label' => 'Đơn hàng của tôi'],
        ['route' => 'points.index', 'icon' => 'star-fill', 'label' => 'Điểm tích lũy'],
        ['route' => 'points.history', 'icon' => 'clock-history', 'label' => 'Lịch sử điểm'],
        ['route' => 'profile.show', 'icon' => 'person', 'label' => 'Hồ sơ'],
    ];
@endphp

<div class="card border-0 sticky-top" style="top: 2rem;">
    <div class="card-header bg-light border-0">
        <h5 class="card-title mb-0">Menu</h5>
    </div>
    <div class="card-body">
        <div class="list-group list-group-flush">
            @foreach ($menuItems as $item)
                <a href="{{ route($item['route']) }}"
                   class="list-group-item list-group-item-action {{ $currentRouteName === $item['route'] ? 'active' : '' }}">
                    <i class="bi bi-{{ $item['icon'] }}"></i> {{ $item['label'] }}
                </a>
            @endforeach
        </div>
    </div>
</div>
