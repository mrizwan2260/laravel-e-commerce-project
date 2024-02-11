<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $totalOrders = Order::where('status', '!=', 'cancelled')->count();
        $totalProducts = Product::count();
        $totalUsers = User::where('role',0)->count();

        $totalSale = Order::where('status', '!=', 'cancelled')->sum('grand_total');

        //This month sale
        $startOfMonth = Carbon::now()->startOfMonth()->format('Y-m-d');
        $currentDate = Carbon::now()->format('Y-m-d');

        $saleThisMonth = Order::where('status', '!=', 'cancelled')->whereDate('created_at', '>=', $startOfMonth)->whereDate('created_at','<=',$currentDate)->sum('grand_total');

        //last month sale
        $lastMonthStartDate = Carbon::now()->subMonth()->startOfMonth()->format('Y-m-d');
        $lastMonthEndDate = Carbon::now()->subMonth()->endOfMonth()->format('Y-m-d');
        $lastMontName = Carbon::now()->subMonth()->startOfMonth()->format('M');

        $saleLastMonth = Order::where('status', '!=', 'cancelled')->whereDate('created_at', '>=', $lastMonthStartDate)->whereDate('created_at','<=',$lastMonthEndDate)->sum('grand_total');

        //last 30 days sale
        $lastThirtyDayStartDate = Carbon::now()->subDays(30)->format('Y-m-d');
        $lastThiryDaysSale = Order::where('status', '!=', 'cancelled')->whereDate('created_at', '>=', $lastThirtyDayStartDate)->whereDate('created_at','<=',$currentDate)->sum('grand_total');

        return view('admin.dashboard',compact('totalOrders','totalProducts','totalUsers','totalSale','saleThisMonth','saleLastMonth','lastThiryDaysSale','lastMontName'));
    }

    public function logout()
    {
        Auth::guard('admin')->logout();
        return to_route('admin.login');
    }
}
