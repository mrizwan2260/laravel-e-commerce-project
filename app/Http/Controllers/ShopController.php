<?php

namespace App\Http\Controllers;

use App\Models\brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\SubCategory;
use Illuminate\Http\Request;
// use Illuminate\Database\Eloquent\Builder;
class ShopController extends Controller
{
    public function index(Request $request, $categorySlug = null, $subCategorySlug = null){
        $categorySelected = '';
        $subCategorySelected = '';
        $brandsArray = [];

        $categories = Category::orderBy('name','ASC')->with('sub_category')->where('status',1)->get();
        $brands = brand::orderBy('name','ASC')->where('status',1)->get();
        $products = Product::where('status',1);

        if($request->get('short') != ''){
            if($request->get('short') == 'latest'){
                $products = $products->orderBy('id','DESC');
            } elseif ($request->get('short') == 'price_desc'){
                $products = $products->orderBy('price','DESC');
            } elseif ($request->get('short') == 'price_asc') {
                $products = $products->orderBy('price','ASC');
            }
        } else{
            $products = $products->orderBy('id','DESC');
        }

        //Apply filters Here
        if(!empty($categorySlug)){
            $category = Category::where('slug', $categorySlug)->first();
            $products = $products->where('category_id',$category->id);
            $categorySelected = $category->id;
        }

        if(!empty($subCategorySlug)){
            $subCategory = SubCategory::where('slug',$subCategorySlug)->first();
            $products = $products->where('sub_category_id',$subCategory->id);
            $subCategorySelected = $subCategory->id;
        }

        if(!empty($request->get('brand'))){
            $brandsArray = explode(',',$request->get('brand'));
            $products = $products->whereIn('brand_id',$brandsArray);
        }
        if($request->get('price_max') != '' && $request->get('price_min') != ''){
            if($request->get('price_max') == 1000){
                $products = $products->whereBetween('price',[intval($request->get('price_min')),1000000]);
            }else{
                $products = $products->whereBetween('price',[intval($request->get('price_min')),intval($request->get('price_max'))]);
            }
        }

        if (!empty($request->get('search'))) {
            $products = $products->where('title','like','%'.$request->get('search').'%');
        }

        $priceMin = intval($request->get('price_min'));
        $priceMax = (intval($request->get('price_max')) == 0 ) ? 1000 : $request->get('price_max');
        $short = $request->get('short');

        $products = $products->paginate(6);

        return view('front.shop',compact('categories','brands','products','categorySelected','subCategorySelected','brandsArray','priceMin','priceMax','short'));
    }


    public function product($slug){
        $product = Product::where('slug',$slug)->with('product_image')->first();
        if($product == null){
            abort(404);
        }

        //Fetch Related Product
        $relatedProducts = [];
        if($product->related_products != ''){
            $productArray = explode(',',$product->related_products);
            $relatedProducts = Product::wherein('id',$productArray)->where('status', 1)->with('product_image')->get();
        }

        return view('front.product',compact('product','relatedProducts'));
    }
}
