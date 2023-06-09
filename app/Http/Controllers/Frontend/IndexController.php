<?php

namespace App\Http\Controllers\Frontend;

use App\Models\User;
use App\Models\Brand;
use App\Models\Banner;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;

class IndexController extends Controller
{
    // Frontend Home Page : Home Page Category Products Brand etc
    public function index()
    {
        $banner=Banner::where(['status'=>'active','condition'=>'banner'])->orderBy('id','DESC')->limit('3')->get();
        $banner_promo=Banner::where(['status'=>'active','condition'=>'promo'])->orderBy('id','DESC')->limit('1')->get();

        $categories=Category::where(['status'=>'active','is_parent'=>1])->orderBy('id','DESC')->limit('3')->get();
        $new_products=Product::where(['status'=>'active','conditions'=>'new'])->orderBy('id','DESC')->limit('10')->get();

        $featured_products=Product::where(['status'=>'active','is_featured'=>1])->orderBy('id','DESC')->limit('6')->get();

        $brands=Brand::where('status','active',)->orderBy('id','DESC')->get();

        return view('frontend.index',compact('banner','categories','new_products','featured_products','banner_promo','brands'));
    }

    // Frontend Header : Shop
    public function shop()
    {
        $products=Product::where('status','active')->paginate(12);
        return view('frontend.pages.product.shop',compact('products'));
    }

    public function productCategory(Request $request,$slug)
    {
        $pdcat=Category::with('productsMR')->where('slug',$slug)->first();

        $sort='';
        if ($request->sort !=null) {
            $sort=$request->sort;
        }
        if ($pdcat==null) {
            return back();
        }
        else{
            if ($sort=='priceAsc') {
                # code...
            }
        }

        $route='product-category';
        return view('frontend.pages.product-category',compact(['pdcat','route']));
    }

    public function productDetails($slug)
    {
        //dd($slug);
        $prdct_dtls=Product::with('relatedProductMR')->where('slug',$slug)->first();
        if ($prdct_dtls) {
            return view('frontend.pages.product.product-details',compact('prdct_dtls'));
        }
        else{
            return 'Product Details Not Found!';
        }
    }

    // User Auth : Login Register Logout
    public function userAuthLoginRegister()
    {
        Session::put('url.intendend',URL::previous());
        return view('frontend.auth-user.login-register');
    }

    public function userLogin(Request $request)
    {
        $request->validate([
            'email'=>'required|email|exists:users,email',
            'password'=>'required|min:6'
        ]);
        if (Auth::attempt(['email'=>$request->email,'password'=>$request->password,'status'=>'active'])) {
            Session::put('user',$request->email);

            if (Session::get('url.intended')) {
                return Redirect::to(Session::get('url.intended'))->with('success','Successfully Login!');
            }
            else{
                return redirect()->route('home')->with('success','Successfully Login!');
            }
        }
        else{
            return back()->with('error','Invalid Email or Password!');
        }
    }

    public function registerSubmit(Request $request)
    {
        $request->validate([
            'full_name'=>'required|string',
            'username'=>'required|string',
            'email'=>'email|required|unique:users,email',
            'password'=>'required|min:6|confirmed',
        ]);

        $data=$request->all();
        $data['password']=Hash::make($request->password);
        $store=User::create($data);
        Session::put('user',$data['email']);
        Auth::login($store);
        if ($store) {
            return redirect()->route('home')->with('success','Registration Successfully!');
        }
        else{
            return back()->with('error','Please Try Again!');
        }
    }

    public function logoutSubmit()
    {
        Session::forget('user');
        Auth::logout();
        return redirect()->route('user.auth')->with('success','Successfully Logout!');
    }

    // User Account : Profile Edit Update Order Billing Shipping
    public function userDashboard()
    {
        $usr=Auth::user();
        return view('frontend.user.dashboard',compact('usr'));
    }

    public function userOrder()
    {
        return view('frontend.user.order');
    }

    public function userAddress()
    {
        $usr=Auth::user();
        return view('frontend.user.address',compact('usr'));
    }

    public function userBillingAddress(Request $request,$id)
    {
        $update=User::where('id',$id)->update(['address'=>$request->address,'city'=>$request->city,'postcode'=>$request->postcode,'state'=>$request->state,'country'=>$request->country]);
        if ($update) {
            return back()->with('success',"Billing Address Successfully Updated!");
        }
        else{
            return back()->with('error',"Something went wrong!");
        }
    }

    public function userShippingAddress(Request $request,$id)
    {
        $update=User::where('id',$id)->update(['shipping_address'=>$request->shipping_address,'shipping_city'=>$request->shipping_city,'shipping_postcode'=>$request->shipping_postcode,'shipping_state'=>$request->shipping_state,'shipping_country'=>$request->shipping_country]);
        if ($update) {
            return back()->with('success',"Shipping Address Successfully Updated!");
        }
        else{
            return back()->with('error',"Something went wrong!");
        }
    }

    public function userAccountDetails()
    {
        $usr=Auth::user();
        return view('frontend.user.account-details',compact('usr'));
    }

    public function userAccountUpdate(Request $request,$id)
    {
        $request->validate([
            'full_name'=>'required',
            'username'=>'required|unique:users',
            'phone'=>'required|min:11',
            'old_password'=>'nullable|min:6',
            'new_password'=>'nullable|min:6',
        ]);
        $current_password=Auth::user()->password;

        if ($request->old_password==null && $request->new_password==null) {
            User::where('id',$id)->update(['full_name'=>$request->full_name,'username'=>$request->username,'phone'=>$request->phone]);
            return back()->with('success','Account Successfully Updated!');
        }
        else{
            if (Hash::check($request->old_password,$current_password)) {
                if (!Hash::check($request->new_password,$current_password)) {
                    User::where('id',$id)->update(['full_name'=>$request->full_name,'username'=>$request->username,'phone'=>$request->phone,'password'=>Hash::make($request->new_password)]);
                    return back()->with('success','Account Successfully Updated with Password!');
                }
                else{
                    return back()->with('error','New Password Cant be same with Old password!');
                }
            }
            else{
                return back()->with('error','Current Password does not matched!');
            }
        }
    }
}
