<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Country;
use App\Models\DiscountCoupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Shipping;
use Illuminate\Http\Request;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    public function addToCart(Request $request)
    {
        $product = Product::with('product_image')->find($request->id);

        if ($product == null) {
            return response()->json([
                'status' => false,
                'message' => "Record not Found",
            ]);
        }

        if (Cart::count() > 0) {
            // echo "Product Already in Cart";
            $cartContent = Cart::content();
            $productAlreadyExist = false;

            foreach ($cartContent as $item) {
                if ($item->id == $product->id) {
                    $productAlreadyExist = true;
                }
            }

            if ($productAlreadyExist == false) {
                Cart::add($product->id, $product->title, 1, $product->price, ['productImage' => (!empty($product->product_image)) ? $product->product_image->first() : '']);

                $status = true;
                $message = '<strong>' . $product->title . '</strong> Added in Your Cart Successfully.';
                session()->flash('success', $message);
            } else {
                $status = false;
                $message = $product->title . ' Already Added in Cart';
                session()->flash('error', $message);
            }
        } else {
            Cart::add($product->id, $product->title, 1, $product->price, ['productImage' => (!empty($product->product_image)) ? $product->product_image->first() : '']);
            $status = true;
            $message = '<strong>' . $product->title . '</strong> Added in Your Cart Successfully.';
            session()->flash('success', $message);
        }
        return response()->json([
            'status' => $status,
            'message' => $message,
        ]);
        //Cart::add('293ad', 'Product 1', 1, 9.99);
    }


    public function cart()
    {
        $cartContent = Cart::content();

        session(['url.intended' => url()->previous()]);

        return view('front.cart', compact('cartContent'));
    }

    public function updateCart(Request $request)
    {
        $rowId = $request->rowId;
        $qty = $request->qty;

        $itemInfo = Cart::get($rowId);
        $product = Product::find($itemInfo->id);
        //check qty available stock
        if ($product->track_qty == "Yes") {
            if ($qty <= $product->qty) {
                Cart::update($rowId, $qty);
                $message = "Cart Updated SuccessFully.";
                $status = true;
                session()->flash('success', $message);
            } else {
                $message = "Requested Quantity($qty) not Available in Stock";
                $status = false;
                session()->flash('error', $message);
            }
        } else {
            Cart::update($rowId, $qty);
            $message = "Cart Updated SuccessFully.";
            $status = true;
            session()->flash('success', $message);
        }

        return response()->json([
            'status' => $status,
            'message' => $message,
        ]);
    }


    public function deleteItem(Request $request)
    {
        $itemInfo = Cart::get($request->rowId);
        if ($itemInfo == null) {
            session()->flash('error', 'Product not Found in Cart');
            return response()->json([
                'status' => false,
                'message' => 'Product not Found in Cart',
            ]);
        }

        Cart::remove($request->rowId);
        session()->flash('success', 'Product Remove from Cart Successfully.');
        return response()->json([
            'status' => true,
            'message' => 'Product Remove from Cart Successfully.',
        ]);
    }

    public function checkout()
    {
        $discount = 0;
        if (Cart::count() == 0) {
            return to_route('front.cart');
        }

        if (Auth::check() == false) {
            if (!session()->has('url.intended')) {
                session(['url.intended' => url()->current()]);
            }
            return to_route('account.login');
        }

        $customerAddress = Address::where('user_id', Auth::user()->id)->first();
        session()->forget('url.intended');

        $subTotal = Cart::subtotal(2, '.', '');

        // Apply Discount here
        if (session()->has('code')) {
            $code = session()->get('code');

            if ($code->type == 'percent') {
                $discount = ($code->discount_amount / 100) * $subTotal;
            } else {
                $discount = $code->discount_amount;
            }
        }


        $countries = Country::orderBy('name', 'ASC')->get();
        //calculate shipping here
        if ($customerAddress != '') {
            $userCountry = $customerAddress->country_id;
            $shippingInfo = Shipping::where('country_id', $userCountry)->first();

            $totalQty = 0;
            $totalShippingCharge = 0;
            $grandTotal = 0;
            foreach (Cart::content() as $item) {
                $totalQty += $item->qty;
            }

            $totalShippingCharge = $totalQty * $shippingInfo->amount;
            $grandTotal = ($subTotal - $discount) + $totalShippingCharge;
        } else {
            $totalShippingCharge = 0;
            $grandTotal = ($subTotal - $discount);
        }
        return view('front.checkout', compact('countries', 'customerAddress', 'totalShippingCharge', 'grandTotal', 'discount'));
    }


    public function processCheckout(Request $request)
    {
        //step 1 validation
        $validator = Validator::make($request->all(), [
            'first_name' => ['required', 'max:190'],
            'last_name' => ['required', 'max:190'],
            'email' => ['required', 'email'],
            'country' => ['required'],
            'address' => ['required', 'max:500'],
            'city' => ['required', 'max:190'],
            'state' => ['required', 'max:190'],
            'zip' => ['required', 'max:190'],
            'mobile' => ['required', 'max:190'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Please fix the errors',
                'status' => false,
                'errors' => $validator->errors(),
            ]);
        }

        //step 2 save user address
        $user = Auth::user();

        Address::updateOrCreate(
            ['user_id' => $user->id],
            [
                'user_id' => $user->id,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'mobile' => $request->mobile,
                'country_id' => $request->country,
                'address' => $request->address,
                'apartment' => $request->apartment,
                'city' => $request->city,
                'state' => $request->state,
                'zip' => $request->zip,
            ]
        );

        //step 3 store data in orders table
        if ($request->payment_method == 'cod') {

            $discountCodeId = '';
            $promoCode = '';
            $shipping = 0;
            $discount = 0;
            $subTotal = Cart::subtotal(2, '.', '');


            // Apply Discount here
            if (session()->has('code')) {
                $code = session()->get('code');

                if ($code->type == 'percent') {
                    $discount = ($code->discount_amount / 100) * $subTotal;
                } else {
                    $discount = $code->discount_amount;
                }
                $discountCodeId = $code->id;
                $promoCode = $code->code;
            }

            //shipping calculate
            $shippingInfo = Shipping::where('country_id', $request->country)->first();
            $totalQty = 0;

            foreach (Cart::content() as $item) {
                $totalQty += $item->qty;
            }

            if ($shippingInfo != null) {
                $shipping = $totalQty * $shippingInfo->amount;
                $grandTotal = ($subTotal-$discount) + $shipping;
            } else {
                $shippingInfo = Shipping::where('country_id', 'rest_of_world')->first();
                $shipping = $totalQty * $shippingInfo->amount;
                $grandTotal = ($subTotal-$discount) + $shipping;
            }

            $order = new Order();
            $order->subtotal = $subTotal;
            $order->shipping = $shipping;
            $order->grand_total = $grandTotal;
            $order->user_id = $user->id;
            $order->discount = $discount;
            $order->coupon_code_id = $discountCodeId;
            $order->coupon_code = $promoCode;
            $order->payment_status = 'not paid';
            $order->status = 'pending';

            $order->first_name = $request->first_name;
            $order->last_name = $request->last_name;
            $order->email = $request->email;
            $order->mobile = $request->mobile;
            $order->country_id = $request->country;
            $order->address = $request->address;
            $order->apartment = $request->apartment;
            $order->city = $request->city;
            $order->state = $request->state;
            $order->zip = $request->zip;
            $order->notes = $request->order_notes;
            $order->save();

            //step 4 store order item in order item table
            foreach (Cart::content() as $item) {
                $orderItem = new OrderItem();
                $orderItem->product_id = $item->id;
                $orderItem->order_id = $order->id;
                $orderItem->name = $item->name;
                $orderItem->qty = $item->qty;
                $orderItem->price = $item->price;
                $orderItem->total = $item->price * $item->qty;
                $orderItem->save();

                //Update Product stock
                $productData = Product::find($item->id);
                if ($productData->track_qty == 'Yes') {
                    $currentQty = $productData->qty;
                    $updateQty = $currentQty-$item->qty;
                    $productData->qty = $updateQty;
                    $productData->save();
                }
            }

            //Send Order Email
            orderEmail($order->id,'customer');

            session()->flash('success', 'You Have successfully placed your order');

            Cart::destroy();
            session()->forget('code');

            return response()->json([
                'message' => 'Order Saved successfully.',
                'orderId' => $order->id,
                'status' => true,
            ]);
        } else {
            //
        }
    }


    public function thankyou($id)
    {
        return view('front.thanks', compact('id'));
    }

    public function getOrderSummery(Request $request)
    {
        $subTotal = Cart::subtotal(2, '.', '');
        $discount = 0;
        $discountString = '';

        // Apply Discount here
        if (session()->has('code')) {
            $code = session()->get('code');

            if ($code->type == 'percent') {
                $discount = ($code->discount_amount / 100) * $subTotal;
            } else {
                $discount = $code->discount_amount;
            }
            $discountString = '<div class="mt-2" id="discount-response">
        <strong>' . session()->get('code')->code . '</strong>
            <a href="#" class="btn btn-danger btn-sm" id="remove-discount"><i class="fa fa-times"></i></a>
        </div>';
        }


        if ($request->country_id > 0) {

            $shippingInfo = Shipping::where('country_id', $request->country_id)->first();

            $totalQty = 0;
            foreach (Cart::content() as $item) {
                $totalQty += $item->qty;
            }

            if ($shippingInfo != null) {
                $shippingCharge = $totalQty * $shippingInfo->amount;
                $grandTotal = ($subTotal - $discount) + $shippingCharge;

                return response()->json([
                    'status' => true,
                    'grandTotal' => number_format($grandTotal, 2),
                    'discount' => number_format($discount, 2),
                    'discountString' => $discountString,
                    'shippingCharge' => number_format($shippingCharge, 2),
                ]);
            } else {
                $shippingInfo = Shipping::where('country_id', 'rest_of_world')->first();

                $shippingCharge = $totalQty * $shippingInfo->amount;
                $grandTotal = ($subTotal - $discount) + $shippingCharge;

                return response()->json([
                    'status' => true,
                    'grandTotal' => number_format($grandTotal, 2),
                    'discount' => number_format($discount, 2),
                    'discountString' => $discountString,
                    'shippingCharge' => number_format($shippingCharge, 2),
                ]);
            }
        } else {

            return response()->json([
                'status' => true,
                'grandTotal' => number_format(($subTotal - $discount), 2),
                'discount' => number_format($discount, 2),
                'discountString' => $discountString,
                'shippingCharge' => number_format(0, 2),
            ]);
        }
    }

    public function applyDiscount(Request $request)
    {
        $code = DiscountCoupon::where('code', $request->code)->first();

        if ($code == null) {
            return response()->json([
                'status' => false,
                'message' => 'invalid discount coupon',
            ]);
        }

        //check if coupon start date is valid or not
        $now = Carbon::now();
        // echo $now->format('Y-m-d H:i:s');
        if ($code->starts_at != "") {
            $startDate = Carbon::createFromFormat('Y-m-d H:i:s', $code->starts_at);

            if ($now->lt($startDate)) {
                return response()->json([
                    'status' => false,
                    'message' => 'invalid discount coupon code.',
                ]);
            }
        }

        if ($code->expires_at != "") {
            $endDate = Carbon::createFromFormat('Y-m-d H:i:s', $code->expires_at);

            if ($now->gt($endDate)) {
                return response()->json([
                    'status' => false,
                    'message' => 'invalid discount coupon code.',
                ]);
            }
        }

        //Max Uses Check
        if ($code->max_uses > 0) {
            $couponUsed = Order::where('coupon_code_id',$code->id)->count();

            if ($couponUsed >= $code->max_uses) {
                return response()->json([
                    'status' => false,
                    'message' => 'invalid discount coupon code.',
                ]);
            }
        }


        //Max Uses Check
        if ($code->max_uses_user > 0) {
            $couponUsedByUser = Order::where(['coupon_code_id' => $code->id, 'user_id' => Auth::user()->id])->count();

            if ($couponUsedByUser >= $code->max_uses_user) {
                return response()->json([
                    'status' => false,
                    'message' => 'You already used this coupon.',
                ]);
            }
        }

        $subTotal = Cart::subtotal(2,'.','');

        //Min amount condition check
        if ($code->min_amount > 0) {
            if ($subTotal < $code->min_amount){
                return response()->json([
                    'status' => false,
                    'message' => 'Your amount must be '.$code->min_amount.'.',
                ]);
            }
        }


        session()->put('code', $code);
        return $this->getOrderSummery($request);
    }


    public function removeCoupon(Request $request)
    {
        session()->forget('code');
        return $this->getOrderSummery($request);
    }
}
