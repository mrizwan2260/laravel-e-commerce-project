<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Shipping;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ShippingController extends Controller
{


    public function create()
    {
        $countries = Country::get();
        $shippingCharges = Shipping::select('shippings.*', 'countries.name')
            ->leftJoin('countries', 'countries.id', 'shippings.country_id')->get();
        return view('admin.shipping.create', compact('countries', 'shippingCharges'));
    }

    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'country' => ['required'],
            'amount' => ['required', 'numeric']
        ]);

        if ($validator->passes()) {

            $count = Shipping::where('country_id', $request->country)->count();
            if ($count > 0) {
                session()->flash('error', 'Shipping already Added');
                return response()->json([
                    'status' => true,
                    'message' => 'shipping already Added',
                ]);
            }
            $shipping = new Shipping();
            $shipping->country_id = $request->country;
            $shipping->amount = $request->amount;
            $shipping->save();

            session()->flash('success', 'shipping added successfuly');
            return response()->json([
                'status' => true,
                'message' => 'shipping added successfully',
            ]);
        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    public function edit($id)
    {
        $shippingCharges = Shipping::find($id);
        $countries = Country::get();
        return view('admin.shipping.edit', compact('countries', 'shippingCharges'));
    }

    public function update($id, Request $request)
    {
        $shipping = Shipping::find($id);
        $validator = Validator::make($request->all(), [
            'country' => ['required'],
            'amount' => ['required', 'numeric']
        ]);

        if ($validator->passes()) {
            $shipping->country_id = $request->country;
            $shipping->amount = $request->amount;
            $shipping->save();

            session()->flash('success', 'shipping Updated successfuly');
            return response()->json([
                'status' => true,
                'message' => 'shipping added successfully',
            ]);
        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    public function destroy($id)
    {
        $shipping = Shipping::find($id);
        if ($shipping == null) {
            session()->flash('error', 'Record not Found');
            return response()->json([
                'status' => true,
            ]);
        }
        $shipping->delete();

        session()->flash('success', 'shipping Deleted successfuly');
        return response()->json([
            'status' => true,
            'message' => 'shipping Deleted successfully',
        ]);
    }
}
