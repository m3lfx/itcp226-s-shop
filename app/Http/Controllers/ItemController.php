<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Storage;
use App\Models\Item;
use App\Models\Stock;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ItemImport;
use App\Imports\ItemStockImport;
use Illuminate\Support\Facades\Session;
use App\Cart;

class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = DB::table('item as i')
            ->leftJoin('stock as s', 'i.item_id', '=', 's.item_id')
            ->select('i.*', 's.quantity')
            ->get();
        // dd($items);

        return view('item.index', compact('items'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('item.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $rules = [
            'description' => 'required|min:4',
            'image' => 'mimes:jpg,png'
        ];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        $path = Storage::putFileAs(
            'public/images',
            $request->file('image'),
            $request->file('image')->hashName()
        );
        $item = Item::create([
            'description' => trim($request->description),
            'cost_price' => $request->cost_price,
            'sell_price' => $request->sell_price,
            'img_path' => $path
        ]);

        $stock = new Stock();
        $stock->item_id = $item->item_id;
        $stock->quantity = $request->quantity;
        $stock->save();

        return redirect()->route('items.create')->with('success', 'item added');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $item = Item::find($id);
        $stock = Stock::find($id);
        // dd($stock);
        return view('item.edit', compact('item', 'stock'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $item = Item::find($id);
        $item->description = $request->description;
        $item->cost_price = $request->cost_price;
        $item->sell_price = $request->sell_price;
        $name = $request->file('image')->getClientOriginalName();


        $path = Storage::putFileAs(
            'public/images',
            $request->file('image'),
            $request->file('image')->hashName()
        );
        $item->img_path = $path;
        $item->save();

        $stock = Stock::find($id);
        if (empty($stock)) {
            $stock = new Stock;
            $stock->item_id = $item->item_id;
            $stock->quantity = $request->quantity;
            $stock->save();
        } else {
            $stock->quantity = $request->quantity;
            $stock->save();
        }

        return redirect()->route('items.index')->with('success', 'item added');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function import()
    {

        Excel::import(
            new ItemStockImport,
            request()
                ->file('item_upload')
                ->storeAs(
                    'files',
                    request()
                        ->file('item_upload')
                        ->getClientOriginalName()
                )
        );
        return redirect()->back()->with('success', 'Excel file Imported Successfully');
    }

    public function getItems()
    {
        dump(Session::get('cart'));
        $items = DB::table('item')->join('stock', 'item.item_id', '=', 'stock.item_id')->get();
        return view('shop.index', compact('items'));
    }

    public function addToCart($id)
    {
        $item = Item::find($id);
        // dd($item);
        $oldCart = Session::has('cart') ? Session::get('cart') : null;
        // dd($oldCart);
        $cart = new Cart($oldCart);
        // dd($cart);
        $cart->add($item, $id);
        // dd($cart);
        Session::put('cart', $cart);
        return redirect('/')->with('success', 'item added to cart');
    }

    public function getCart()
    {
        // dump(Session::get('cart'));
        if (!Session::has('cart')) {
            return view('shop.shopping-cart');
        }
        $oldCart = Session::get('cart');
        $cart = new Cart($oldCart);
        // dd($cart);
        return view('shop.shopping-cart', ['products' => $cart->items, 'totalPrice' => $cart->totalPrice]);
    }

    public function getReduceByOne($id)
    {
        $oldCart = Session::has('cart') ? Session::get('cart') : null;
        $cart = new Cart($oldCart);
        $cart->reduceByOne($id);
        if (count($cart->items) > 0) {
            Session::put('cart', $cart);
        } else {
            Session::forget('cart');
        }
        return redirect()->route('getCart');
    }

    public function getRemoveItem($id)
    {
        $oldCart = Session::has('cart') ? Session::get('cart') : null;
        $cart = new Cart($oldCart);
        $cart->removeItem($id);
        if (count($cart->items) > 0) {
            Session::put('cart', $cart);
        } else {
            Session::forget('cart');
        }
        return redirect()->route('getCart');
    }

    public function postCheckout()
    {

        if (!Session::has('cart')) {
            return redirect()->route('getCart');
        }
        $oldCart = Session::get('cart');
        $cart = new Cart($oldCart);
        // dd($cart, $cart->items);
        try {
            $customer = Customer::where('user_id', Auth::id())->first();
            // dd($customer);
            DB::beginTransaction();
            $order = new Order();
            // $order->customer_id = $customer->customer_id;
            $order->customer_id = 1;
            $order->date_placed = now();
            $order->date_shipped = Carbon::now()->addDays(5);

            $order->shipping = 10.00;
            // $order->status = 'Processing';

            // $order->save();
            // dd($cart->items);

            foreach ($cart->items as $items) {
                $id = $items['item']['item_id'];
                // dd($id);

                DB::table('orderline')
                    ->insert(
                        [
                            'item_id' => $id,
                            'orderinfo_id' => $order->orderinfo_id,
                            'quantity' => $items['qty']
                        ]
                    );
                $stock = Stock::find($id);
                $stock->quantity = $stock->quantity - $items['qty'];
                $stock->save();
            }
            // dd($order);
        } catch (\Exception $e) {
            dd($e->getMessage());
            DB::rollback();
            // dd($order);
            return redirect()->route('getCart')->with('error', $e->getMessage());
        }

        DB::commit();
        Session::forget('cart');
        return redirect('/')->with('success', 'Successfully Purchased Your Products!!!');
    }
}
