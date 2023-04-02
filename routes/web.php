<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Frontend\IndexController;
use App\Http\Controllers\Frontend\CartController;
use App\Http\Controllers\Frontend\CheckoutController;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes(['register'=>false]);
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index']);

// <================Frontend Part============>
Route::get('/',[IndexController::class,'index'])->name('home');

//User Authentication
Route::get('user/auth/login-register',[IndexController::class,'userAuthLoginRegister'])->name('user.auth');
Route::post('user/login',[IndexController::class,'userLogin'])->name('user.login');
Route::post('user/register',[IndexController::class,'registerSubmit'])->name('user.register');
Route::get('user/logout',[IndexController::class,'logoutSubmit'])->name('user.logout');

//User Point
Route::group(['prefix'=>'user'],function(){
    Route::get('/dashboard',[IndexController::class,'userDashboard'])->name('user.myaccount');
    Route::get('/order',[IndexController::class,'userOrder'])->name('user.order');
    Route::get('/address',[IndexController::class,'userAddress'])->name('user.address');
    Route::post('/billing-address/{id}',[IndexController::class,'userBillingAddress'])->name('user.billingaddress.store');
    Route::post('/shipping-address/{id}',[IndexController::class,'userShippingAddress'])->name('user.shippingaddress.store');
    Route::get('/account-details',[IndexController::class,'userAccountDetails'])->name('user.account.details');
    Route::post('/account-update/{id}',[IndexController::class,'userAccountUpdate'])->name('user.account.update');
});

//Product Category Section
Route::get('product-category/{slug}',[IndexController::class,'productCategory'])->name('product.category');
Route::get('product-details/{slug}',[IndexController::class,'productDetails'])->name('product.details');

// Cart
Route::get('cart',[CartController::class,'cartIndex'])->name('cart.index');
Route::post('cart-store',[CartController::class,'cartStore'])->name('cart.store');
Route::post('cart-delete',[CartController::class,'cartDelete'])->name('cart.destroy');

Route::post('coupon-add',[CartController::class,'couponAdd'])->name('coupon.add');

//Checkout
Route::get('checkout1',[CheckoutController::class,'checkout1'])->name('checkout1')->middleware('user');
// <================Backend Part============>

//Admin Point
Route::group(['prefix'=>'admin','middleware'=>'auth','admin'],function(){
    Route::get('/',[\App\Http\Controllers\AdminController::class,'admin'])->name('admin');
//Banner Section
    Route::resource('/banner',BannerController::class);
    Route::post('/banner-status',[BannerController::class,'bannerStatus'])->name('banner.status');
//Category Section
    Route::resource('/category',CategoryController::class);
    Route::post('/category-status',[CategoryController::class,'categoryStatus'])->name('category.status');
//Brand Section
    Route::resource('/brand',BrandController::class);
    Route::post('/brand-status',[BrandController::class,'brandStatus'])->name('brand.status');
//Product Section
    Route::resource('/product',ProductController::class);
    Route::post('/product-status',[ProductController::class,'productStatus'])->name('product.status');
//User Section
    Route::resource('/user',UserController::class);
    Route::post('/user-status',[UserController::class,'userStatus'])->name('user.status');
//User Section
    Route::resource('/coupon',CouponController::class);
    Route::post('/coupon-status',[CouponController::class,'couponStatus'])->name('coupon.status');
});

//----------------------------------------------------------------------------------------------

//Seller Point
// Route::get(['prefix'=>'seller','middleware'=>['auth','seller']],function(){
//     Route::get();
// });
