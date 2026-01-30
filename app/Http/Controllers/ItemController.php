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
            new ItemImport,
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
}