<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CartController extends Controller
{
    function getCartProducts()
    {
        $categories = DB::table('items')
                        ->select('category as name')
                        ->distinct()
                        ->where('category', '!=', '')
                        ->orderBy('category')
                        ->get();

        $subcategories = DB::table('items')
                        ->select('subcategory as name')
                        ->distinct()
                        ->where('subcategory', '!=', '')
                        ->orderBy('subcategory')
                        ->get();

        return view('cart.cart-products', compact('categories', 'subcategories'));
    }

    function getCartProductsJson(Request $request)
    {
        $data = $request->all();
        if (isset($data['draw'])) {
            $columnArray = ['id', 'web_id', 'card_name', 'u_vsppan', 'u_trpbpnm', 'u_csubcat', 'u_csubcat2'];

            try {
                DB::enableQueryLog();

                $query = DB::table('items');

                if ($data['category'] != '') {
                    $query->where('category', '=', $data['category']);
                }
                if ($data['subcategory'] != '') {
                    $query->where('subcategory', '=', $data['subcategory']);
                }

                if ($data['search_item'] != '') {
                    $query->where(function($query) use ($data) {
                        $query->where('item_no', 'like', $data['search_item'] . '%')
                             ->orWhere('item_desc', 'like', '%' . $data['search_item'] . '%');
                    });

                }


                $count = $data['branch_count'] ?? '10';
                $userCount = count($query->get());
                $query->orderBy('item_no');

                if ($data['length'] != -1) {
                    $query->skip($data['start'])->take($count);
                }

                $users = $query->get();

            // $sql = $query->toSql();
            // $bindings = $query->getBindings();
            // echo vsprintf(str_replace('?', "'%s'", $sql), $bindings);
            // exit;

            } catch (\Exception $e) {
                $users = [];
                $userCount = 0;
                Log::info("Cart Products Error". $e->getMessage());
            }

            return response()->json([
                'draw' => $data['draw'],
                'recordsTotal' => $userCount,
                'recordsFiltered' => $userCount,
                // 'sql' => $query->toSql(),
                'data' => $users
            ]);
        }
    }

    public function addToCart(Request $request)
    {
        $userId = auth()->id();
        $cart = DB::table('carts')->where('user_id', $userId)->first();

        try {

            $itemId = $request->input('item_id');
            $quantity = $request->input('quantity');

            $cartId = $cart->id;
            if (!$cart) {
                $cartId = DB::table('carts')->insertGetId(['user_id' => $userId]);
            }

            $item = Item::findOrFail($itemId);

            CartItem::updateOrCreate(
                ['cart_id' => $cartId, 'item_id' => $itemId],
                [
                    'item_id' =>  $itemId,
                    'item_no' =>  $item->item_no,
                    'item_desc' =>  $item->item_desc,
                    'quantity' =>  $quantity,
                    'uom_code' => $item->uom_code,
                    'price' => $item->price,
                    'gst_rate' => $item->gst_rate,
                    'gst_value' =>  $item->price * $item->gst_rate / 100,
                ]
            );

            return response()->json(['success' => 1, 'message' => 'Item added to cart!']);
        } catch (\Exception $e) {
            Log::info('Failed to add item to cart.'. $e->getMessage());
            return response()->json(['success' => 0, 'message' => 'Failed to add item to cart.']);
        }
    }

    function getItemCatSubCats($category)
    {
        $sub_categories = DB::table('items')
                        ->select('subcategory')
                        ->distinct()
                        ->where('category', $category)
                        ->where('subcategory', '!=', '')
                        ->orderBy('subcategory')
                        ->get()
                        ->toArray();

        return response()->json($sub_categories);
    }

    function viewCart() {
        $userId = auth()->id();
        $cart = DB::table('carts')->where('user_id', $userId)->first();
        $items = DB::table('cart_items')->where('cart_id', $cart->id)->get();
        return view('cart.list', compact('items'));
    }

    public function deleteCartItem(Request $request)
    {
        $userId = auth()->id();
        $itemId = $request->item_id;
        try {
            CartItem::where('user_id', $userId)->where('item_id', $itemId);
            return response()->json(['success' => 1, 'message' => 'Item deleted to cart!']);
        } catch (\Exception $e) {
            Log::info('Failed to add item to cart.'. $e->getMessage());
            return response()->json(['success' => 0, 'message' => 'Failed to add item to cart.']);
        }
    }



}
