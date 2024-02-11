<?php

namespace App\Http\Controllers;

use App\Mail\ResetPasswordEmail;
use App\Models\Address;
use App\Models\Country;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\WishList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    public function login(){
        return view('front.account.login');
    }


    public function register(){
        return view('front.account.register');
    }


    public function processRegister(Request $request){
        $validator = Validator::make($request->all(),[
            'name' => ['required','min:3','max:190'],
            'email' => ['required','email','unique:users'],
            'password' => ['required','min:5','confirmed'],
        ]);

        if($validator->passes()){

            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->phone = $request->phone;
            $user->password = Hash::make($request->password);
            $user->save();

            session()->flash('success','You Have been registerd successfully.');

            return response()->json([
                'status' => true,

            ]);
        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ]);
        }
    }


    public function authenticate(Request $request){
        $validator = Validator::make($request->all(),[
            'email' => ['required','email'],
            'password' => ['required']
        ]);

        if($validator->passes()){
            if(Auth::attempt(['email' => $request->email, 'password' => $request->password], $request->get('remember'))) {

                if (session()->has('url.intended')) {
                    return redirect(session()->get('url.intended'));
                }

                return redirect()->route('front.index')->withInput($request->only('email'));
                } else {
                    session()->flash('error','Either Email/Password is incorrect.');
                    return to_route('account.login')->withInput($request->only('email'));
                }

        } else {
            return redirect()->route('account.login')->withErrors($validator)->withInput($request->only('email'));
        }
    }

    public function profile(){
        $countries = Country::orderBy('name','ASC')->get();
        $address = Address::where('user_id',Auth::user()->id)->first();
        return view('front.account.profile',compact('countries','address'));
    }

    public function updateProfile(Request $request){
        $userId = Auth::user()->id;
        $validator = Validator::make($request->all(),[
            'name' => ['required','max:190'],
            'email' => ['required','email','unique:users,email,'.$userId.',id'],
            'phone' => ['required','max:19'],
        ]);

        if ($validator->passes()){

            $user = User::find($userId);
            $user->name = $request->name;
            $user->phone = $request->phone;
            $user->save();

            session()->flash('success','Profile Updated Successfully.');
            return response()->json([
                'status' => true,
                'message' => 'Profile Updated Successfully.'
            ]);

        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ]);
        }
    }

    //update Address
    public function updateAddress(Request $request){
        $userId = Auth::user()->id;
        $validator = Validator::make($request->all(), [
            'first_name' => ['required', 'max:190'],
            'last_name' => ['required', 'max:190'],
            'email' => ['required', 'email'],
            'country_id' => ['required'],
            'address' => ['required', 'max:500'],
            'city' => ['required', 'max:190'],
            'state' => ['required', 'max:190'],
            'zip' => ['required', 'max:190'],
            'mobile' => ['required', 'max:190'],
        ]);

        if ($validator->passes()){

        $user = Auth::user();

        Address::updateOrCreate(
            ['user_id' => $user->id],
            [
                'user_id' => $user->id,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'mobile' => $request->mobile,
                'country_id' => $request->country_id,
                'address' => $request->address,
                'apartment' => $request->apartment,
                'city' => $request->city,
                'state' => $request->state,
                'zip' => $request->zip,
            ]
        );

            session()->flash('success','Address Updated Successfully.');
            return response()->json([
                'status' => true,
                'message' => 'Address Updated Successfully.'
            ]);

        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ]);
        }
    }


    public function order(){
        $user = Auth::user();
        $orders = Order::where('user_id',$user->id)->orderBy('created_at','DESC')->get();
        return view('front.account.order',compact('orders'));
    }


    public function orderDetail($id){
        $user = Auth::user();
        $order = Order::where('user_id',$user->id)->where('id',$id)->first();

        $orderItems = OrderItem::where('order_id',$id)->get();
        $product = Product::all();

        return view('front.account.order-detail',compact('order','orderItems','product'));
    }



    public function logout(){
        Auth::logout();
        return to_route('account.login')->with('success','You Successfully Logged Out!');
    }

    public function wishlist(){
        $wishlists = WishList::where('user_id', Auth::user()->id)->get();
        return view('front.account.wishlist',compact('wishlists'));
    }

    public function removeProductfromWishlist(Request $request){
        $wishlist = WishList::where('user_id',Auth::user()->id)->where('product_id',$request->id)->first();

        if ($wishlist == null) {
            session()->flash('error', 'Product Already Removed');

            return response()->json([
                'status' => true,
            ]);
        } else {
            WishList::where('user_id',Auth::user()->id)->where('product_id',$request->id)->delete();
            session()->flash('success', 'Product Remove Successfully');

            return response()->json([
                'status' => true,
            ]);
        }
    }


    public function changePasswordPage(){
        return view('front.account.chang-password');
    }


    public function changePassword(Request $request){
        $validator = Validator::make($request->all(),[
            'old_password' => ['required'],
            'new_password' => ['required','min:5'],
            'confirm_password' => ['required','same:new_password']
        ]);

        if ($validator->passes()) {

            $user = User::select('id','password')->where('id',Auth::user()->id)->first();
            if (!Hash::check($request->old_password, $user->password)) {

                session()->flash('error','Your Old Password is Incorrect, Please try again');
                return response()->json([
                    'status' => true,
                    'message' => 'Your Old Password is Incorrect, Please try again'
                ]);
            }

            User::where('id',Auth::user()->id)->update([
                'password' => Hash::make($request->new_password)
            ]);

            session()->flash('success','Your password is updated successfuly');
                return response()->json([
                    'status' => true,
                    'message' => 'Your password is updated successfuly'
                ]);


        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }


    public function forgotPassword(){
        return view('front.account.forgot-password');
    }

    public function processForgotPassword(Request $request){
        $validator = Validator::make($request->all(),[
            'email' => ['required','email','exists:users,email']
        ]);

        if ($validator->fails()) {
            return back()->withInput()->withErrors($validator);
        }

        $token = Str::random(60);

        \DB::table('password_reset_tokens')->where('email',$request->email)->delete();

        \DB::table('password_reset_tokens')->insert([
            'email' => $request->email,
            'token' => $token,
            'created_at' => now(),
        ]);

        //Maikl send
        $user = User::where('email',$request->email)->first();
        $formData = [
            'token' => $token,
            'user' => $user,
            'mailSubject' => 'You have requested to reset your password.'
        ];

        Mail::to($request->email)->send(new ResetPasswordEmail($formData));
        return redirect()->back()->with('success','Please check your inbox to reset your password.');
    }


    public function resetPassword($token){

        $tokenExist = \DB::table('password_reset_tokens')->where('token',$token)->first();

        if ($tokenExist == null) {
            return to_route('fornt.forgotPassword')->with('error','Invaid request');
        }
        return view('front.account.reset-password',compact('token'));
    }


    public function processResetPassword(Request $request){
        $token = $request->token;

        $tokenExist = \DB::table('password_reset_tokens')->where('token',$token)->first();

        if ($tokenExist == null) {
            return to_route('fornt.forgotPassword')->with('error','Invaid request');
        }

        $user = User::where('email',$tokenExist->email)->first();
        $validator = Validator::make($request->all(),[
            'new_password' => ['required','min:5'],
            'confirm_password' => ['required','same:new_password']
        ]);

        if ($validator->fails()) {
            return redirect()->route('fornt.resetPassword',$token)->withErrors($validator);
        }

        User::where('id',$user->id)->update([
            'password' => Hash::make($request->new_password)
        ]);

        \DB::table('password_reset_tokens')->where('email',$user->email)->delete();

        return to_route('account.login')->with('success','You have successfully updated your password');
    }
}
