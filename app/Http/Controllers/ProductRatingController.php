<?php

namespace App\Http\Controllers;

use App\Models\ProductRating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductRatingController extends Controller
{
    public function saveRating(Request $request, $id)
    {
        $validator      = Validator::make($request->all(), [
            'name'      => ['required'],
            'email'     => ['required', 'email'],
            'comment'   => ['required'],
            'rating'    => ['required'],
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()]);
            session()->flash('error', 'You already rated this product');
            return response()->json([
                'status'        => true,
            ]);
        }

        $count = ProductRating::where('email', $request->email)->count();
        if ($count > 0) {
        }
        $rating             = new ProductRating();
        $rating->product_id = $id;
        $rating->username   = $request->name;
        $rating->email      = $request->email;
        $rating->rating     = $request->rating;
        $rating->comment    = $request->comment;
        $rating->save();

        session()->flash('success', 'Thanks for your rating');
        return response()->json([
            'status'        => true,
            'message'       => 'Thanks for your rating, rating display after admin approve.'
        ]);
    }
}
