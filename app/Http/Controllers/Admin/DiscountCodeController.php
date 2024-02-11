<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\DiscountCoupon;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class DiscountCodeController extends Controller
{
    public function index(Request $request){
        $discountCoupons = DiscountCoupon::latest();
        if (!empty($request->get('keyword'))) {
            $discountCoupons = $discountCoupons->where('name', 'like', '%' . $request->get('keyword') . '%');
            $discountCoupons = $discountCoupons->orWhere('code', 'like', '%' . $request->get('keyword') . '%');
        }
        $discountCoupons = $discountCoupons->paginate(10);
        return view('admin.coupon.index',compact('discountCoupons'));
    }

    public function create(){
        return view('admin.coupon.create');
    }

    public function store(Request $request){
        $validator = Validator::make($request->all(),[
            'code' => ['required'],
            'type' => ['required'],
            'discount_amount' => ['required','numeric'],
            'status' => ['required'],
        ]);

        if($validator->passes()){

            // starting date must be grator than current date
            if (!empty($request->starts_at)) {
                $now = Carbon::now();
                $startsAt = Carbon::createFromFormat('Y-m-d H:i:s',$request->starts_at);

                if ($startsAt->lte($now) == true) {
                    return response()->json([
                        'status' => false,
                        'errors' => ['starts_at' => 'Start date can not be less than current date time'],
                    ]);
                }
            }
            // expiry date must be grator than start date
            if (!empty($request->starts_at) && !empty($request->starts_at)) {
                $expiresAt = Carbon::createFromFormat('Y-m-d H:i:s',$request->expires_at);
                $startsAt = Carbon::createFromFormat('Y-m-d H:i:s',$request->starts_at);

                if ($expiresAt->gt($startsAt) == false) {
                    return response()->json([
                        'status' => false,
                        'errors' => ['expires_at' => 'Expiry date must be greator than start date.'],
                    ]);
                }
            }


            $discountCode = new DiscountCoupon();
            $discountCode->code = $request->code;
            $discountCode->name = $request->name;
            $discountCode->description = $request->description;
            $discountCode->max_uses = $request->max_uses;
            $discountCode->max_uses_user = $request->max_uses_user;
            $discountCode->type = $request->type;
            $discountCode->discount_amount = $request->discount_amount;
            $discountCode->min_amount = $request->min_amount;
            $discountCode->status = $request->status;
            $discountCode->starts_at = $request->starts_at;
            $discountCode->expires_at = $request->expires_at;
            $discountCode->save();

            $message = 'Discount Coupon created successfully.';
            session()->flash('success', $message);
            return response()->json([
                'status' => true,
                'message' => $message,
            ]);

        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ]);
        }
    }

    public function edit($id){
        $coupon = DiscountCoupon::find($id);
        if ($coupon == null) {
            session()->flash('error','Record not Found');
            return to_route('coupon.index');
        }
        return view('admin.coupon.edit',compact('coupon'));
    }

    public function update(Request $request, $id){
        $discountCode = DiscountCoupon::find($id);
        if ($discountCode == null) {
            session()->flash('error','Record not Found');
            return response()->json([
                'status' => true,
            ]);
        }

        $validator = Validator::make($request->all(),[
            'code' => ['required'],
            'type' => ['required'],
            'discount_amount' => ['required','numeric'],
            'status' => ['required'],
        ]);

        if($validator->passes()){
            // expiry date must be grator than start date
            if (!empty($request->starts_at) && !empty($request->starts_at)) {
                $expiresAt = Carbon::createFromFormat('Y-m-d H:i:s',$request->expires_at);
                $startsAt = Carbon::createFromFormat('Y-m-d H:i:s',$request->starts_at);

                if ($expiresAt->gt($startsAt) == false) {
                    return response()->json([
                        'status' => false,
                        'errors' => ['expires_at' => 'Expiry date must be greator than start date.'],
                    ]);
                }
            }

            $discountCode->code = $request->code;
            $discountCode->name = $request->name;
            $discountCode->description = $request->description;
            $discountCode->max_uses = $request->max_uses;
            $discountCode->max_uses_user = $request->max_uses_user;
            $discountCode->type = $request->type;
            $discountCode->discount_amount = $request->discount_amount;
            $discountCode->min_amount = $request->min_amount;
            $discountCode->status = $request->status;
            $discountCode->starts_at = $request->starts_at;
            $discountCode->expires_at = $request->expires_at;
            $discountCode->save();

            $message = 'Discount Coupon Updated successfully.';
            session()->flash('success', $message);
            return response()->json([
                'status' => true,
                'message' => $message,
            ]);

        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ]);
        }
    }

    public function destroy($id){
        $coupon = DiscountCoupon::find($id);
        if ($coupon == null) {
            session()->flash('error', 'Record not Found');
            return response()->json([
                'status' => false,
            ]);
        }

        $coupon->delete();
        session()->flash('success','Coupon Delete Successfully.');
        return response()->json([
            'status' => true,
            'message' => 'Coupon Delete Successfully.',
        ]);
    }
}
