<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Vendor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ApiController extends Controller
{
    private function ok(string $message, mixed $data = [], int $status = 200, array $meta = []) { return response()->json(['success' => true, 'message' => $message, 'data' => $data, 'meta' => $meta], $status); }
    private function fail(string $message, mixed $errors = [], int $status = 422) { return response()->json(['success' => false, 'message' => $message, 'errors' => $errors], $status); }
    private function user(Request $request) { return $request->user(); }
    private function role(Request $request, string ...$roles): bool { return in_array($this->user($request)->role, $roles, true); }

    public function register(Request $request) {
        $data = $request->validate(['name' => 'required|string|max:100', 'email' => 'required|email|unique:users', 'password' => 'required|string|min:8|confirmed', 'role' => 'sometimes|in:customer,vendor']);
        $user = User::create([...$data, 'password' => Hash::make($data['password']), 'role' => $data['role'] ?? 'customer']);
        if ($user->role === 'vendor') $user->vendor()->create(['shop_name' => $user->name, 'slug' => Str::slug($user->name).'-'.$user->id]);
        return $this->ok('Registered successfully', ['user' => $user, 'token' => $user->createToken('api')->plainTextToken], 201);
    }
    public function login(Request $request) {
        $data = $request->validate(['email' => 'required|email', 'password' => 'required']); $user = User::where('email', $data['email'])->first();
        if (!$user || !Hash::check($data['password'], $user->password)) return $this->fail('Invalid credentials', [], 401);
        return $this->ok('Logged in successfully', ['user' => $user, 'token' => $user->createToken('api')->plainTextToken]);
    }
    public function logout(Request $request) { $request->user()->currentAccessToken()?->delete(); return response()->json(null, 204); }
    public function me(Request $request) { return $this->ok('Authenticated user', $request->user()->load('vendor')); }

    public function categories(Request $request) {
        $query = Category::query()->where('status', $request->query('status', 'active'));
        if ($search = $request->query('search')) $query->where('name', 'like', "%{$search}%");
        $categories = $query->orderBy($request->query('sort', 'name'), $request->query('direction', 'asc'))->paginate((int) $request->query('per_page', 15));
        return $this->ok('Categories retrieved successfully', $categories->items(), 200, ['current_page' => $categories->currentPage(), 'last_page' => $categories->lastPage(), 'total' => $categories->total()]);
    }
    public function category(Category $category) { return $this->ok('Category retrieved successfully', $category->load('products')); }
    public function categoryProducts(Category $category) { return $this->ok('Category products retrieved successfully', $category->products()->where('status', 'active')->paginate(15)); }
    public function storeCategory(Request $request) { $this->authorizeAdmin($request); $data = $request->validate(['name' => 'required|string|max:150', 'description' => 'nullable|string', 'image' => 'nullable|string']); $data['slug'] = Str::slug($data['name']); return $this->ok('Category created successfully', Category::create($data), 201); }
    public function updateCategory(Request $request, Category $category) { $this->authorizeAdmin($request); $data = $request->validate(['name' => 'sometimes|string|max:150', 'description' => 'nullable|string', 'image' => 'nullable|string', 'status' => 'sometimes|in:active,inactive']); if (isset($data['name'])) $data['slug'] = Str::slug($data['name']); $category->update($data); return $this->ok('Category updated successfully', $category); }
    public function deleteCategory(Request $request, Category $category) { $this->authorizeAdmin($request); $category->delete(); return response()->json(null, 204); }

    private function productQuery(Request $request) {
        $query = Product::with(['category', 'vendor'])->where('status', 'active');
        foreach (['category_id', 'vendor_id', 'featured'] as $field) if ($request->filled($field)) $query->where($field, $request->query($field));
        if ($request->filled('min_price')) $query->whereRaw('COALESCE(discount_price, price) >= ?', [$request->query('min_price')]);
        if ($request->filled('max_price')) $query->whereRaw('COALESCE(discount_price, price) <= ?', [$request->query('max_price')]);
        if ($request->filled('search')) $query->where(fn ($q) => $q->where('name', 'like', '%'.$request->query('search').'%')->orWhere('description', 'like', '%'.$request->query('search').'%'));
        return $query;
    }
    public function products(Request $request) { $products = $this->productQuery($request)->orderBy($request->query('sort', 'created_at'), $request->query('direction', 'desc'))->paginate((int) $request->query('per_page', 15)); return $this->ok('Products retrieved successfully', $products->items(), 200, ['current_page' => $products->currentPage(), 'last_page' => $products->lastPage(), 'total' => $products->total()]); }
    public function product(Product $product) { return $this->ok('Product retrieved successfully', $product->load(['category', 'vendor'])); }
    public function storeProduct(Request $request) { if (!$this->role($request, 'vendor', 'admin')) return $this->fail('Forbidden', [], 403); $data = $request->validate(['category_id' => 'required|exists:categories,id', 'name' => 'required|string|max:200', 'sku' => 'required|string|unique:products,sku', 'description' => 'nullable|string', 'price' => 'required|numeric|min:0', 'discount_price' => 'nullable|numeric|min:0|lte:price', 'stock' => 'required|integer|min:0', 'image' => 'nullable|string']); $vendor = $request->user()->vendor; if (!$vendor && $request->user()->role === 'admin') return $this->fail('Admin must select a vendor', [], 422); $data['vendor_id'] = $vendor->id; $data['slug'] = Str::slug($data['name']).'-'.Str::lower(Str::random(5)); return $this->ok('Product created successfully', Product::create($data), 201); }
    public function updateProduct(Request $request, Product $product) { if (!$this->role($request, 'admin') && $product->vendor?->user_id !== $request->user()->id) return $this->fail('Forbidden', [], 403); $data = $request->validate(['name' => 'sometimes|string|max:200', 'description' => 'nullable|string', 'price' => 'sometimes|numeric|min:0', 'discount_price' => 'nullable|numeric|min:0', 'stock' => 'sometimes|integer|min:0', 'status' => 'sometimes|in:active,inactive']); $product->update($data); return $this->ok('Product updated successfully', $product); }
    public function deleteProduct(Request $request, Product $product) { if (!$this->role($request, 'admin') && $product->vendor?->user_id !== $request->user()->id) return $this->fail('Forbidden', [], 403); $product->delete(); return response()->json(null, 204); }

        public function cart(Request $request) {
            $cart = DB::table('carts')->where('user_id', $request->user()->id)->first();
            if (!$cart) $cart = DB::table('carts')->insertGetId(['user_id' => $request->user()->id, 'created_at' => now(), 'updated_at' => now()]);
            $items = DB::table('cart_items')->join('products', 'products.id', '=', 'cart_items.product_id')->where('cart_id', $cart->id ?? $cart)->select('cart_items.*', 'products.name', 'products.image', 'products.stock')->get();
            $subtotal = $items->sum(fn ($item) => $item->unit_price * $item->quantity);
            return $this->ok('Cart retrieved successfully', ['items' => $items, 'subtotal' => round($subtotal, 2), 'discount' => 0, 'tax' => round($subtotal * .1, 2), 'shipping' => $subtotal >= 50 ? 0 : 3, 'grand_total' => round($subtotal * 1.1 + ($subtotal >= 50 ? 0 : 3), 2)]);
        }

        public function addCartItem(Request $request) {
            $data = $request->validate(['product_id' => 'required|exists:products,id', 'quantity' => 'required|integer|min:1']);
            $product = Product::findOrFail($data['product_id']);
            if ($data['quantity'] > $product->stock) return $this->fail('Insufficient stock', ['quantity' => ['Only '.$product->stock.' items are available.']], 422);
            $cart = DB::table('carts')->firstOrInsert(['user_id' => $request->user()->id], ['created_at' => now(), 'updated_at' => now()]);
            $cartId = DB::table('carts')->where('user_id', $request->user()->id)->value('id');
            DB::table('cart_items')->updateOrInsert(['cart_id' => $cartId, 'product_id' => $product->id], ['quantity' => $data['quantity'], 'unit_price' => $product->sale_price, 'updated_at' => now(), 'created_at' => now()]);
            return $this->cart($request);
        }

        public function removeCartItem(Request $request, int $item) {
            $cart = DB::table('carts')->where('user_id', $request->user()->id)->value('id');
            DB::table('cart_items')->where('cart_id', $cart)->where('id', $item)->delete();
            return response()->json(null, 204);
        }

        public function clearCart(Request $request) {
            $cart = DB::table('carts')->where('user_id', $request->user()->id)->value('id');
            DB::table('cart_items')->where('cart_id', $cart)->delete();
            return response()->json(null, 204);
        }

        public function wishlist(Request $request) {
            $items = DB::table('wishlists')->join('products', 'products.id', '=', 'wishlists.product_id')->where('user_id', $request->user()->id)->select('products.*')->get();
            return $this->ok('Wishlist retrieved successfully', $items);
        }

        public function addWishlist(Request $request) {
            $data = $request->validate(['product_id' => 'required|exists:products,id']);
            DB::table('wishlists')->insertOrIgnore(['user_id' => $request->user()->id, 'product_id' => $data['product_id'], 'created_at' => now(), 'updated_at' => now()]);
            return $this->wishlist($request);
        }

        public function removeWishlist(Request $request, int $product) {
            DB::table('wishlists')->where('user_id', $request->user()->id)->where('product_id', $product)->delete();
            return response()->json(null, 204);
        }

        public function clearWishlist(Request $request) {
            DB::table('wishlists')->where('user_id', $request->user()->id)->delete();
            return response()->json(null, 204);
        }

    private function authorizeAdmin(Request $request): void { abort_unless($this->role($request, 'admin'), 403, 'Admin access required'); }
}
