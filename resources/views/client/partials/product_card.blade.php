<div class="col-6 col-md-4 col-lg-3">
    @php
        $cardImage = collect([
            $product->thumbnail,
            $product->productGroup?->images?->first()?->image_path,
            $product->images?->first()?->image_path,
            $product->variants?->first()?->image_path,
            $product->variants?->first()?->images?->first()?->image_path,
        ])->filter()->first();
    @endphp

    <article class="product-card h-100 border rounded-3 overflow-hidden bg-white" style="transition:.2s">
        <a href="{{ route('products.show', $product) }}" class="d-block overflow-hidden" style="height:180px">
            @if($cardImage)
                <img src="{{ Storage::url($cardImage) }}"
                     class="w-100 h-100 p-2" style="object-fit:contain" alt="{{ $product->name }}">
            @else
                <div class="w-100 h-100 bg-light d-flex align-items-center justify-content-center">
                    <i class="lni lni-image text-muted" style="font-size:2rem"></i>
                </div>
            @endif
        </a>

        <div class="card-body p-3 d-flex flex-column">
            <div class="mb-1">
                <span class="badge bg-light text-muted border small">{{ $product->brand?->name }}</span>
                <span class="badge bg-light text-muted border small">{{ $product->category?->name }}</span>
            </div>
            <h3 class="h6 fw-semibold mb-2">
                <a href="{{ route('products.show', $product) }}" class="text-dark text-decoration-none">
                    {{ $product->name }}
                </a>
            </h3>

            <div class="price-tag fw-bold text-primary mb-3">
                {{ number_format($product->price, 0, ',', '.') }}đ
            </div>

            <a href="{{ route('products.show', $product) }}" class="btn btn-outline-primary btn-sm w-100 mt-auto">
                Xem chi tiết
            </a>
        </div>
    </article>
</div>
