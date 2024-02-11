<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::latest('orders.created_at')->select('orders.*','users.name','users.email');
        $orders = $orders->leftJoin('users','users.id','orders.user_id');

        if ($request->get('keyword') != '') {
            $orders = $orders->where('users.name','like','%'.$request->keyword.'%');
            $orders = $orders->orWhere('users.email','like','%'.$request->keyword.'%');
            $orders = $orders->orWhere('orders.id','like','%'.$request->keyword.'%');
        }

        $orders = $orders->paginate(10);
        return view('admin.orders.index',compact('orders'));
    }

    public function detail($orderId)
    {
        $order = Order::where('id',$orderId)->first();
        $orderItems = OrderItem::where('order_id',$orderId)->get();
        return view('admin.orders.detail',compact('order','orderItems'));
    }

    //changeOrderStatusForm
    public function changeOrderStatus(Request $request,$orderId)
    {
        $order = Order::find($orderId);
        $order->status = $request->status;
        $order->shipped_date = $request->shipped_date;
        $order->save();

        session()->flash('success','Order Status Update successfully');
        return response()->json([
            'status' => true,
            'message' => 'Order Status Update successfully'
        ]);
    }


    public function sendInvoiceemail(Request $request, $orderId)
    {
        orderEmail($orderId, $request->userType);

        session()->flash('success','Order sent successfully');
        return response()->json([
            'status' => true,
            'message' => 'Order sent successfully'
        ]);
    }
}
