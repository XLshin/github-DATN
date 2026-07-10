<?php

namespace App\Services\AI;

use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class ShoppingAssistantService
{
    /** Model Gemini có gói miễn phí, hỗ trợ function calling. */
    private const MODEL = 'gemini-2.5-flash';

    private const API_URL = 'https://generativelanguage.googleapis.com/v1beta/models/'.self::MODEL.':generateContent';

    private const MAX_TOOL_ITERATIONS = 5;

    private const SYSTEM_PROMPT = <<<'PROMPT'
Bạn là trợ lý mua sắm của Byte Zone Store — cửa hàng điện thoại và phụ kiện chính hãng.

Nhiệm vụ: giúp khách tìm sản phẩm phù hợp, so sánh, xem chi tiết và thêm vào giỏ hàng.

Quy tắc bắt buộc:
- Luôn dùng tool để tra cứu sản phẩm/giá/tồn kho thật — không tự bịa tên sản phẩm, giá, hoặc thông tin tồn kho.
- Nếu một sản phẩm có nhiều biến thể (màu, dung lượng), hỏi khách chọn biến thể trước khi thêm vào giỏ, trừ khi khách đã nói rõ.
- Không tự ý thêm sản phẩm vào giỏ hàng nếu khách chưa xác nhận muốn mua — chỉ gợi ý và hỏi trước.
- Không xử lý thanh toán, không tạo đơn hàng — hướng khách ra trang giỏ hàng/thanh toán khi họ sẵn sàng mua.
- Trả lời ngắn gọn, thân thiện, bằng tiếng Việt.
PROMPT;

    public function __construct(
        private readonly CartService $cartService,
    ) {}

    /**
     * @param  array<int, array{role: string, parts: array<int, array{text: string}>}>  $history
     * @return array{reply: string, history: array<int, array{role: string, parts: array<int, array{text: string}>}>}
     */
    public function reply(User $user, array $history, string $userMessage): array
    {
        $apiKey = config('services.gemini.api_key');

        if (empty($apiKey)) {
            throw new RuntimeException('Chưa cấu hình GEMINI_API_KEY.');
        }

        $contents = $history;
        $contents[] = ['role' => 'user', 'parts' => [['text' => $userMessage]]];

        $tools = $this->toolDeclarations();
        $replyText = '';

        for ($i = 0; $i < self::MAX_TOOL_ITERATIONS; $i++) {
            $response = Http::timeout(30)->post(self::API_URL.'?key='.$apiKey, [
                'contents' => $contents,
                'systemInstruction' => ['parts' => [['text' => self::SYSTEM_PROMPT]]],
                'tools' => [['functionDeclarations' => $tools]],
                'generationConfig' => ['maxOutputTokens' => 1024],
            ]);

            if (! $response->successful()) {
                throw new RuntimeException('Gemini API lỗi: '.$response->body());
            }

            $parts = $response->json('candidates.0.content.parts', []);

            if (empty($parts)) {
                break;
            }

            // Giữ nguyên phần phản hồi của model (có thể chứa functionCall) để nối tiếp hội thoại.
            $contents[] = ['role' => 'model', 'parts' => $parts];

            $functionCalls = array_values(array_filter($parts, fn ($p) => isset($p['functionCall'])));

            foreach ($parts as $part) {
                if (isset($part['text'])) {
                    $replyText .= $part['text'];
                }
            }

            if (empty($functionCalls)) {
                break;
            }

            $functionResponses = [];
            foreach ($functionCalls as $call) {
                $name = $call['functionCall']['name'];
                $args = $call['functionCall']['args'] ?? [];
                $result = $this->executeTool($name, $args, $user);

                $functionResponses[] = [
                    'functionResponse' => [
                        'name' => $name,
                        'response' => $result,
                    ],
                ];
            }

            $contents[] = ['role' => 'user', 'parts' => $functionResponses];
        }

        if ($replyText === '') {
            $replyText = 'Xin lỗi, tôi chưa thể trả lời câu này. Bạn thử hỏi lại theo cách khác nhé.';
        }

        $history[] = ['role' => 'user', 'parts' => [['text' => $userMessage]]];
        $history[] = ['role' => 'model', 'parts' => [['text' => $replyText]]];

        return ['reply' => $replyText, 'history' => $history];
    }

    private function executeTool(string $name, array $args, User $user): array
    {
        return match ($name) {
            'search_products' => $this->searchProducts($args),
            'get_product_detail' => $this->getProductDetail($args),
            'add_to_cart' => $this->addToCart($user, $args),
            default => ['error' => "Không rõ tool: {$name}"],
        };
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function toolDeclarations(): array
    {
        return [
            [
                'name' => 'search_products',
                'description' => 'Tìm sản phẩm trong cửa hàng theo từ khóa, danh mục, thương hiệu hoặc khoảng giá. Luôn gọi tool này trước khi gợi ý sản phẩm cụ thể.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'keyword' => ['type' => 'string', 'description' => 'Từ khóa tìm theo tên sản phẩm, ví dụ "iPhone 16"'],
                        'category' => ['type' => 'string', 'description' => 'Tên danh mục, ví dụ "Điện thoại", "Phụ kiện"'],
                        'brand' => ['type' => 'string', 'description' => 'Tên thương hiệu, ví dụ "Apple", "Samsung"'],
                        'price_min' => ['type' => 'number', 'description' => 'Giá tối thiểu (VNĐ)'],
                        'price_max' => ['type' => 'number', 'description' => 'Giá tối đa (VNĐ)'],
                    ],
                ],
            ],
            [
                'name' => 'get_product_detail',
                'description' => 'Lấy thông tin chi tiết một sản phẩm theo product_id, bao gồm mô tả và danh sách biến thể (màu/dung lượng) kèm tồn kho thực tế.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'product_id' => ['type' => 'integer', 'description' => 'ID sản phẩm, lấy từ kết quả search_products'],
                    ],
                    'required' => ['product_id'],
                ],
            ],
            [
                'name' => 'add_to_cart',
                'description' => 'Thêm một sản phẩm (và biến thể nếu có) vào giỏ hàng của khách đang trò chuyện. Chỉ gọi khi khách đã xác nhận rõ muốn thêm vào giỏ.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'product_id' => ['type' => 'integer', 'description' => 'ID sản phẩm'],
                        'variant_id' => ['type' => 'integer', 'description' => 'ID biến thể đã chọn (nếu sản phẩm có biến thể)'],
                        'quantity' => ['type' => 'integer', 'description' => 'Số lượng, mặc định 1'],
                    ],
                    'required' => ['product_id'],
                ],
            ],
        ];
    }

    private function searchProducts(array $input): array
    {
        $query = Product::query()->where('status', true);

        if (! empty($input['keyword'])) {
            $query->where('name', 'like', '%'.$input['keyword'].'%');
        }
        if (! empty($input['category'])) {
            $query->whereHas('category', fn ($q) => $q->where('name', 'like', '%'.$input['category'].'%'));
        }
        if (! empty($input['brand'])) {
            $query->whereHas('brand', fn ($q) => $q->where('name', 'like', '%'.$input['brand'].'%'));
        }
        if (isset($input['price_min'])) {
            $query->where('price', '>=', $input['price_min']);
        }
        if (isset($input['price_max'])) {
            $query->where('price', '<=', $input['price_max']);
        }

        $products = $query->limit(8)->get(['id', 'name', 'price', 'slug'])
            ->map(fn (Product $p) => [
                'product_id' => $p->id,
                'name' => $p->name,
                'price' => (float) $p->price,
                'url' => route('products.show', $p->slug),
            ])->all();

        if (empty($products)) {
            return ['message' => 'Không tìm thấy sản phẩm phù hợp.'];
        }

        return ['products' => $products];
    }

    private function getProductDetail(array $input): array
    {
        $product = Product::with('variants')->find($input['product_id'] ?? null);

        if (! $product) {
            return ['error' => 'Không tìm thấy sản phẩm với product_id này.'];
        }

        $variants = $product->variants->map(function ($variant) use ($product) {
            $stock = $product->product_type === 'imei/serial'
                ? $variant->imeis()->where('status', 'available')->count()
                : max(0, (int) $variant->stock_quantity);

            return [
                'variant_id' => $variant->id,
                'color' => $variant->color,
                'storage' => $variant->storage,
                'available_stock' => $stock,
            ];
        })->all();

        return [
            'product_id' => $product->id,
            'name' => $product->name,
            'price' => (float) $product->price,
            'description' => $product->description,
            'url' => route('products.show', $product->slug),
            'variants' => $variants,
        ];
    }

    private function addToCart(User $user, array $input): array
    {
        $product = Product::find($input['product_id'] ?? null);

        if (! $product) {
            return ['error' => 'Không tìm thấy sản phẩm với product_id này.'];
        }

        $variantId = isset($input['variant_id']) ? (int) $input['variant_id'] : null;
        $quantity = max(1, (int) ($input['quantity'] ?? 1));

        if (! $variantId) {
            $firstVariant = $product->variants()->where('status', 1)->first();
            $variantId = $firstVariant?->id;
        }

        $available = $this->cartService->getAvailableStock($product, $product->variants()->find($variantId));

        if ($quantity > $available) {
            return [
                'error' => $available > 0
                    ? "Chỉ còn {$available} sản phẩm trong kho, không đủ số lượng yêu cầu."
                    : 'Sản phẩm này hiện đã hết hàng.',
            ];
        }

        $this->cartService->addItem($user, $product, $quantity, $variantId);

        return [
            'success' => true,
            'message' => "Đã thêm {$quantity} \"{$product->name}\" vào giỏ hàng.",
            'cart_count' => $this->cartService->getCount($user),
        ];
    }
}
