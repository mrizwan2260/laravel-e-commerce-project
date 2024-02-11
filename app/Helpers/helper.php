<?php

use App\Mail\OrderEmail;
use App\Models\Category;
use App\Models\Country;
use App\Models\Order;
use App\Models\Page;
use App\Models\Product_Image;
use Illuminate\Support\Facades\Mail;

function getCategories(){
    return Category::orderby('name','ASC')
    ->where('status',1)
    ->with('sub_category')
    ->orderBy('name','ASC')
    ->where('showHome','Yes')->get();
}

function getProductImage($productId){
    return Product_Image::where('product_id',$productId)->first();
}
function orderEmail($orderId, $userType="customer"){
    $order = Order::where('id',$orderId)->with('items')->first();

    if ($userType == "customer") {
        $subject = "Thanks for Your Order";
        $email = $order->email;
    } else {
        $subject = "You have Received an Order";
        $email = env('ADMIN_EMAIL');
    }

    $mailData = [
        'subject' => $subject,
        'order' => $order,
        'userType' => $userType,
    ];

    Mail::to($email)->send(new OrderEmail($mailData));
    // dd($order);
}

function getCountryInfo($id){
    return Country::where('id',$id)->first();
}


function pageStatic(){
    $pages = Page::orderBy('name','ASC')->get();
    return $pages;
}

?>
