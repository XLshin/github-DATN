<div class="col-6 col-md-4 col-lg-3">
    @php
        $cardImage = collect([
            $product->productGroup?->images?->first()?->image_path,
            $product->thumbnail,
            $product->images?->first()?->image_path,
            $product->variants?->first()?->image_path,
            $product->variants?->first()?->images?->first()?->image_path,
        ])->filter()->first();
    @endphp

    <article class="product-card h-100 bg-white">
        <div class="product-card__media">
            @auth
                <form method="POST" action="{{ route('cart.add') }}" class="add-to-cart-form">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <button type="submit" class="product-card__quick-add" title="Thêm vào giỏ hàng" aria-label="Thêm vào giỏ hàng">
                        <i class="lni lni-cart"></i>
                    </button>
                </form>
            @endauth

            <a href="{{ route('products.show', $product) }}" class="d-block w-100 h-100">
                @if($cardImage)
                    <img src="{{ Storage::url($cardImage) }}"
                         class="w-100 h-100 p-3" style="object-fit:contain" alt="{{ $product->name }}" loading="lazy">
                @else
                    <div class="w-100 h-100 d-flex align-items-center justify-content-center">
                        <i class="lni lni-image text-muted" style="font-size:2rem"></i>
                    </div>
                @endif
            </a>
        </div>

        <div class="card-body p-3 d-flex flex-column">
            <div class="product-card__tags">
                @if($product->brand?->name)
                    <span class="product-card__tag">{{ $product->brand->name }}</span>
                @endif
                @if($product->category?->name)
                    <span class="product-card__tag">{{ $product->category->name }}</span>
                @endif
            </div>

            <h3 class="product-card__title mb-2">
                <a href="{{ route('products.show', $product) }}" class="text-decoration-none" style="color:inherit">
                    {{ $product->name }}
                </a>
            </h3>

            <div class="product-card__price mb-3">
                {{ number_format($product->price, 0, ',', '.') }}đ
            </div>

            <a href="{{ route('products.show', $product) }}" class="btn btn-outline-primary btn-sm product-card__cta w-100 mt-auto">
                Xem chi tiết
            </a>
        </div>
    </article>
</div>
