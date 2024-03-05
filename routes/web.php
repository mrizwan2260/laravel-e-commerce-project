<?php

use App\Http\Controllers\Admin\AdminLoginController;
use App\Http\Controllers\admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\admin\DiscountCodeController;
use App\Http\Controllers\admin\OrderController;
use App\Http\Controllers\admin\PageController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductImageController;
use App\Http\Controllers\admin\ProductSubCategoryController;
use App\Http\Controllers\admin\SettingController;
use App\Http\Controllers\admin\ShippingController;
use App\Http\Controllers\admin\SubCategoryControlle;
use App\Http\Controllers\Admin\TempImagesController;
use App\Http\Controllers\admin\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\FrontController;
use App\Http\Controllers\ProductRatingController;
use App\Http\Controllers\ShopController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });

// Route::get('/test', function () {
//     orderEmail(12);
// });

// Home Routes
Route::get('/', [FrontController::class, 'index'])->name('front.index');
Route::get('/shop/{categorySlug?}/{subCategoryslug?}',[ShopController::class,'index'])->name('front.shop');
Route::get('/product/{slug}',[ShopController::class,'product'])->name('front.product');
Route::get('/cart',[CartController::class,'cart'])->name('front.cart');
Route::post('/add-to-cart',[CartController::class,'addToCart'])->name('front.cartToCart');
Route::post('/update-cart',[CartController::class,'updateCart'])->name('front.updateCart');
Route::post('/delete-cart',[CartController::class,'deleteItem'])->name('front.deleteProduct.cart');
Route::get('/checkout',[CartController::class,'checkout'])->name('fornt.checkout');
Route::post('/process-checkout',[CartController::class,'processCheckout'])->name('fornt.processcheckout');
Route::get('/thanks/{orderId}',[CartController::class,'thankyou'])->name('front.thanks');
Route::post('/get-order-summery',[CartController::class,'getOrderSummery'])->name('fornt.getOrderSummery');
Route::post('/apply-discount',[CartController::class,'applyDiscount'])->name('fornt.applyDiscount');
Route::post('/remove-discount',[CartController::class,'removeCoupon'])->name('fornt.removeCoupon');
Route::post('/add-to-wishlist',[FrontController::class,'addToWishList'])->name('fornt.addToWishList');
Route::get('/page/{slug}',[FrontController::class,'page'])->name('fornt.page');

//rating routes
Route::post('save-rating/{productId}', [ProductRatingController::class,'saveRating'])->name('fornt.saveRating');


//forgotpassword routes
Route::get('/forgot-password',[AuthController::class,'forgotPassword'])->name('fornt.forgotPassword');
Route::post('/process-forgot-password',[AuthController::class,'processForgotPassword'])->name('fornt.processForgotPassword');
Route::get('/reset-password/{token}',[AuthController::class,'resetPassword'])->name('fornt.resetPassword');
Route::post('/process-reset-password',[AuthController::class,'processResetPassword'])->name('fornt.processResetPassword');


Route::group(['prefix' => 'account'], function () {

    Route::group(['middleware' => 'guest'], function () {
        Route::get('/register',[AuthController::class,'register'])->name('account.register');
        Route::post('/process-register',[AuthController::class,'processRegister'])->name('account.process.register');
        Route::get('/login',[AuthController::class,'login'])->name('account.login');
        Route::post('/login',[AuthController::class,'authenticate'])->name('account.authenticate');
        Route::get('/logout',[AuthController::class,'logout'])->name('account.logout');

    });

    Route::group(['middleware' => 'auth'], function () {
        Route::get('/profile',[AuthController::class,'profile'])->name('account.profile');
        Route::post('/update-profile',[AuthController::class,'updateProfile'])->name('account.updateProfile');
        Route::post('/update-address',[AuthController::class,'updateAddress'])->name('account.updateAddress');
        Route::get('/logout',[AuthController::class,'logout'])->name('account.logout');
        Route::get('/my-orders',[AuthController::class,'order'])->name('account.order');
        Route::get('/wishlist',[AuthController::class,'wishlist'])->name('account.wishlist');
        Route::post('/remove-product-from-wishlist',[AuthController::class,'removeProductfromWishlist'])->name('account.removeProductfromWishlist');
        Route::get('/order-detail/{orderId}',[AuthController::class,'orderDetail'])->name('account.orderDetail');
        Route::get('/change-password',[AuthController::class,'changePasswordPage'])->name('account.changePassword');
        Route::post('/process-change-password',[AuthController::class,'changePassword'])->name('account.processChangePassword');
    });

});

Route::group(['prefix' => 'admin'], function () {

    Route::group(['middleware' => 'admin.guest'], function () {
        Route::get('/login', [AdminLoginController::class, 'index'])->name('admin.login');
        Route::post('/authenticate', [AdminLoginController::class, 'authenticate'])->name('admin.authenticate');
    });

    Route::group(['middleware' => 'admin.auth'], function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
        Route::get('/logout', [DashboardController::class, 'logout'])->name('admin.logout');

        //category routes
        Route::get('/category', [CategoryController::class, 'index'])->name('category.index');
        Route::get('/category/create', [CategoryController::class, 'create'])->name('category.craete');
        Route::post('/category', [CategoryController::class, 'store'])->name('category.store');
        Route::get('/category/{category}/edit', [CategoryController::class, 'edit'])->name('categort.edit');
        Route::put('/category/{category}', [CategoryController::class, 'update'])->name('categort.update');
        Route::delete('/category/{category}', [CategoryController::class, 'destroy'])->name('categort.delete');

        //sub-category routes
        Route::get('/sub-categories', [SubCategoryControlle::class, 'index'])->name('sub-categories.index');
        Route::get('/sub-categories/create', [SubCategoryControlle::class, 'create'])->name('sub-categories.create');
        Route::post('/sub-categories', [SubCategoryControlle::class, 'store'])->name('sub-categories.store');
        Route::get('/sub-categories/{subCategory}/edit', [SubCategoryControlle::class, 'edit'])->name('sub-categories.edit');
        Route::put('/sub-categories/{subCategory}', [SubCategoryControlle::class, 'update'])->name('sub-categories.update');
        Route::delete('/sub-categories/{subCategory}', [SubCategoryControlle::class, 'destroy'])->name('sub-categories.destroy');

        //brands routes
        Route::get('/brands', [BrandController::class, 'index'])->name('brand.index');
        Route::get('/brands/create', [BrandController::class, 'create'])->name('brand.create');
        Route::post('/brands', [BrandController::class, 'store'])->name('brand.store');
        Route::get('/brands/{brands}/edit', [BrandController::class, 'edit'])->name('brand.edit');
        Route::put('/brands/{brands}', [BrandController::class, 'update'])->name('brand.update');
        Route::delete('/brands/{brands}', [BrandController::class, 'destroy'])->name('brand.destroy');

        //Product Routes
        Route::get('/products', [ProductController::class, 'index'])->name('product.index');
        Route::get('/products/create', [ProductController::class, 'create'])->name('product.create');
        Route::post('/products', [ProductController::class, 'store'])->name('product.store');
        Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('product.edit');
        Route::put('/products/{product}', [ProductController::class, 'update'])->name('product.update');
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('product.destroy');
        Route::get('/get-products',[ProductController::class,'getProducts'])->name('product.getProducts');
        Route::get('/ratings', [ProductController::class, 'productRating'])->name('product.productRating');
        Route::get('/change-rating-status', [ProductController::class, 'changeRatingStatus'])->name('product.changeRatingStatus');


        Route::post('/product-images/update', [ProductImageController::class, 'update'])->name('product-images.update');
        Route::delete('/product-images', [ProductImageController::class, 'destroy'])->name('product-images.destroy');
        Route::get('/product-subcategories', [ProductSubCategoryController::class, 'index'])->name('productsubcategories.index');

        //Shipping routes
        Route::get('/shipping/create',[ShippingController::class,'create'])->name('shipping.create');
        Route::post('/shipping',[ShippingController::class,'store'])->name('shipping.store');
        Route::get('/shipping/{id}',[ShippingController::class,'edit'])->name('shipping.edit');
        Route::put('/shipping/{id}',[ShippingController::class,'update'])->name('shipping.update');
        Route::delete('/shipping/{id}',[ShippingController::class,'destroy'])->name('shipping.destroy');

        //Coupon code Routes
        Route::get('/coupon',[DiscountCodeController::class,'index'])->name('coupon.index');
        Route::get('/coupon/create',[DiscountCodeController::class,'create'])->name('coupon.create');
        Route::post('/coupon',[DiscountCodeController::class,'store'])->name('coupon.store');
        Route::get('/coupon/{coupon}/edit',[DiscountCodeController::class,'edit'])->name('coupon.edit');
        Route::put('/coupon/{coupon}',[DiscountCodeController::class,'update'])->name('coupon.update');
        Route::delete('/coupon/{coupon}',[DiscountCodeController::class,'destroy'])->name('coupon.delete');


        //Order Routes
        Route::get('/orders',[OrderController::class,'index'])->name('order.index');
        Route::get('/orders/{id}',[OrderController::class,'detail'])->name('order.detail');
        Route::post('/order/change-status/{id}',[OrderController::class,'changeOrderStatus'])->name('order.changeOrderStatusForm');
        Route::post('/order/send-email/{id}',[OrderController::class,'sendInvoiceemail'])->name('order.sendInvoiceemail');


        //Users Routes
        Route::get('/users',[UserController::class,'index'])->name('user.index');
        Route::get('/user/create',[UserController::class,'create'])->name('user.create');
        Route::post('/user',[UserController::class,'store'])->name('user.store');
        Route::get('/user/{userId}/edit',[UserController::class,'edit'])->name('user.edit');
        Route::put('/user/{userId}',[UserController::class,'update'])->name('user.update');
        Route::delete('/user/{user}',[UserController::class,'destroy'])->name('user.delete');


        //Pages Routes
        Route::get('/pages',[PageController::class,'index'])->name('page.index');
        Route::get('/page/create',[PageController::class,'create'])->name('page.create');
        Route::post('/page',[PageController::class,'store'])->name('page.store');
        Route::get('/page/{page}/edit',[PageController::class,'edit'])->name('page.edit');
        Route::put('/page/{page}',[PageController::class,'update'])->name('page.update');
        Route::delete('/page/{page}',[PageController::class,'destroy'])->name('page.delete');


        //settings routes
        Route::get('/change-password',[SettingController::class,'showChangePasswordForm'])->name('admin.showChangePasswordForm');
        Route::post('/process-change-password',[SettingController::class,'processChangePassword'])->name('admin.processChangePassword');

        // temp-images.create
        Route::post('/upload-temp-image', [TempImagesController::class, 'create'])->name('temp-images.create');

        Route::get('/getslug', function (Request $request) {
            $slug = '';
            if (!empty($request->title)) {
                $slug = Str::slug($request->title);
            }
            return response()->json([
                'status' => true,
                'slug' => $slug,
            ]);
        })->name('getSlug');
    });
});
