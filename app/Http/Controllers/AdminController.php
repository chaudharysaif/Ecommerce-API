<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateRequest;
use App\Models\cart;
use App\Models\product;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    //
    // Display all user to dashboard
    function allUser()
    {
        $users = User::all();
        return response()->json([
            'status' => true,
            'data' => $users,
        ]);
    }

    function signUp(Request $request)
    {
        Session::start();
        $user = new User;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->save();
        // $token = $user->createToken('auth_token')->plainTextToken;

        Session::put('id', $user->id);

        return response()->json([
            'status' => true,
            'message' => 'User registered successfully',
            'user' => $user,
            // 'token' => $token,
            // 'token_type' => 'Bearer',
        ]);
    }

    function login(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => false,
                'error' => "Email or password not matched"
            ]);
        }
        $token = $user->createToken('auth_token')->plainTextToken;
        Session::start();
        Session::put('id', $user->id);
        Session::save();

        return response()->json([
            'status' => true,
            'message' => 'Login successful',
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    //     // $imagename = $request->image->getClientOriginalName();
    //     // $path = $request->image->storeAs('public', $imagename);
    //     // $image_name_array = explode("/", $path);
    //     // $image_name = $image_name_array[1];

    // Add new product from dashboard
    public function addProduct(Request $request)
    {
        // Validate the input
        // $request->validate([
        //     'name' => 'required|string',
        //     'price' => 'required|numeric',
        //     'category' => 'required|string',
        //     'image' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        // ]);

        // // Add image to media collection
        // if ($request->hasFile('image')) {
        //     $product->addMedia($request->file('image'))->toMediaCollection('image');
        // }

        $product = Product::create($request->all());
        $product->addMediaFromRequest('image')
            // ->usingName($product->name)
            ->toMediaCollection('default');

        return response()->json([
            "status" => true,
            "message" => "Data inserted Sucsessfully",
            "data" => $product
        ]);
    }

    // View All Product on web and dashboard
    function viewAllProduct()
    {
        $product = Product::all()->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'category' => $product->category,
                'image' => $product->getFirstMediaUrl('default')
            ];
        });
        return response()->json([
            "status" => true,
            "data" => $product
        ]);
    }


    // Add to Cart
    public function addToCart(Request $request)
    {
        $user = Auth::user();
        $user_id = $user->id;

        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        $existingCart = Cart::where('user_id', $user_id)
            ->where('product_id', $request->id)
            ->first();

        if ($existingCart) {
            $existingCart->increment('quantity');
        } else {
            $addcart = new Cart();
            $addcart->product_id = $request->id;
            $addcart->quantity = 1;
            $addcart->user_id = $user_id;
            $addcart->save();
        }

        return response()->json([
            'message' => 'Product added to cart successfully',
            'data' => $user_id,
            'id' => $request->id
        ]);
    }

    // Display product on cart page
    public function getCartProduct()
    {
        $user = Auth::user();
        $user_id = $user->id;

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not authenticated'
            ], 401);
        }

        $cartProducts = Cart::with('products')->where('user_id', $user_id)->get();

        $totalPrice = Cart::with('products')
            ->where('user_id', $user_id)
            ->get()
            ->sum(fn($cart) => $cart->products->price * $cart->quantity);

        $cartProducts->each(function ($cartItem) {
            if ($cartItem->products) {
                $cartItem->products->image = $cartItem->products->getFirstMediaUrl('default');
            }
        });
        return response()->json([
            'status' => true,
            'data' => $cartProducts,
            'totalPrice' => $totalPrice
        ]);
    }

    // Update Quantity
    public function updateQuantity(Request $request)
    {
        $cartItem = Cart::where('id', $request->cart_id)->first();

        if (!$cartItem) {
            return response()->json([
                "status" => false,
                "message" => "Cart item not found"
            ], 404);
        }

        $cartItem->quantity = $request->quantity;
        $cartItem->save();

        return response()->json([
            "status" => true,
            "data" => $cartItem
        ]);
    }

    // Remove Product from cart
    function removeCart($id)
    {
        $product = Cart::findOrFail($id);
        $product->delete();

        return response()->json([
            "status" => true,
            "message" => "Product removed successfully",
        ], 200);
    }

    // Update Product from Admin Panel
    function updateProduct(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $product->name = $request->name;
        $product->price = $request->price;
        $product->category = $request->category;
        if ($request->hasFile('image')) {
            $product->clearMediaCollection(); // Remove old image
            $product->addMediaFromRequest('image')->toMediaCollection('default');
        }
        $product->save();
        return response()->json([
            "status" => true,
            "data" => $product
        ]);
    }

    // Display product on checkout
    function checkoutProduct()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not authenticated'
            ], 401);
        }

        $data = User::with(['carts.products'])->whereHas('carts', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->get();

        // Total directly with cart (Optional)
        // $total = Cart::with('products')
        //     ->where('user_id', $user->id)
        //     ->get()
        //     ->sum(fn($cart) => $cart->quantity * $cart->products->price);

        $totalPrice = $data->sum(function ($user) {
            return $user->carts->sum(function ($cart) {
                return $cart->quantity * $cart->products->price;
            });
        });

        $totalQuantity = $data->sum(function ($user){
            return $user->carts->sum('quantity');
        });

        if (!$data) {
            return response()->json([
                'status' => 'error',
                'message' => 'No cart items found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'totalPrice' => $totalPrice,
            'totalQuantity' => $totalQuantity,
            'data' => $data
        ]);
    }


    // Get Single Product
    function getProduct($id)
    {
        $getProduct = product::findOrFail($id);
        $getProduct->image = $getProduct->getFirstMediaUrl('default');

        return response()->json([
            "status" => true,
            "message" => "Product Get successfully",
            "data" => $getProduct
        ], 200);
    }

    // Delete product with admin panel
    function deleteProduct($id)
    {
        $product = product::findOrFail($id);
        $product->delete();
        $product->clearMediaCollection();

        return response()->json([
            "status" => true,
            "message" => "Product deleted successfully",
        ], 200);
    }

    // Search product with admin panel
    function searchProduct($key)
    {
        $product = Product::where('name', 'LIKE', "%$key%")->get()->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'category' => $product->category,
                'image' => $product->getFirstMediaUrl('default') // Fetch the first image
            ];
        });
        return response()->json([
            "status" => true,
            "data" => $product
        ]);
    }
}
