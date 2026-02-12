<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function processOrder($id)
    {
        $customer = DB::table('customer as c')->join('orderinfo as o', 'o.customer_id', '=', 'c.customer_id')
            ->where('o.orderinfo_id', $id)
            ->select('c.lname', 'c.fname', 'c.addressline', 'c.phone', 'o.orderinfo_id', 'o.date_placed', 'o.status')
            ->first();
        // dd($customer);
        $orders = DB::table('customer as c')->join('orderinfo as o', 'o.customer_id', '=', 'c.customer_id')
            ->join('orderline as ol', 'o.orderinfo_id', '=', 'ol.orderinfo_id')
            ->join('item as i', 'ol.item_id', '=', 'i.item_id')
            ->where('o.orderinfo_id', $id)
            ->select('i.description', 'ol.quantity', 'i.img_path', 'i.sell_price')
            ->get();
        // dd($orders);
        $total = $orders->map(function ($item, $key) {
            return $item->sell_price * $item->quantity;
        })->sum();
        // ->sum();
        // dd($total);
        return view('order.processOrder', compact('customer', 'orders', 'total'));
    }
}
