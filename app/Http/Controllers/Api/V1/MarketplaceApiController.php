<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddToCartRequest;
use App\Http\Resources\Api\V1\CategoryResource;
use App\Http\Resources\Api\V1\OrderResource;
use App\Http\Resources\Api\V1\ProductResource;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\CalculateCartTotalsService;
use App\Services\CatalogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Hash;

class MarketplaceApiController extends Controller
{
    public function __construct(
        private readonly CatalogService $catalogService,
        private readonly CalculateCartTotalsService $cartService,
    ) {}

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password))
        {
            return response()->json([
                'status' => 'error',
                'message' => trans('auth.failed'),
            ], 401);
        }

        $token = $user->createToken('laramart-api-token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames(),
            ],
        ]);
    }

    public function products(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only(['q', 'kategori', 'marka', 'min', 'max', 'renk', 'beden', 'puan', 'sirala']);
        $products = $this->catalogService->filterProducts($filters, 24);

        return ProductResource::collection($products);
    }

    public function showProduct(string $slug): ProductResource
    {
        $product = Product::where('slug', $slug)
            ->with(['seller', 'category', 'brand', 'variants', 'approvedReviews'])
            ->firstOrFail();

        return new ProductResource($product);
    }

    public function categories(): AnonymousResourceCollection
    {
        $categories = $this->catalogService->getMegaMenuCategories();

        return CategoryResource::collection($categories);
    }

    public function getCart(Request $request): JsonResponse
    {
        $cart = $this->cartService->resolveCart($request->user());
        $summary = $this->cartService->calculate($cart);

        return response()->json([
            'status' => 'success',
            'data' => $summary,
        ]);
    }

    public function addToCart(AddToCartRequest $request): JsonResponse
    {
        $variant = ProductVariant::findOrFail($request->validated('variant_id'));
        $cart = $this->cartService->resolveCart($request->user());

        try
        {
            $this->cartService->addItem($cart, $variant, (int) $request->validated('quantity'));
            $summary = $this->cartService->calculate($cart->fresh());

            return response()->json([
                'status' => 'success',
                'message' => 'Ürün sepete eklendi.',
                'data' => $summary,
            ], 201);
        }
        catch (\Throwable $e)
        {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function orders(Request $request): AnonymousResourceCollection
    {
        $orders = $request->user()
            ->orders()
            ->with(['subOrders.items', 'payments'])
            ->latest('id')
            ->paginate(15);

        return OrderResource::collection($orders);
    }
}
