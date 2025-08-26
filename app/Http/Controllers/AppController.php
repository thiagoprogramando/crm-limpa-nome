<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Lists;
use App\Models\Post;
use App\Models\Sale;
use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use Carbon\Carbon;


class AppController extends Controller {

    public function index() {

        $now = now()->setTimezone('America/Sao_Paulo');
    
        $sales = Sale::where('seller_id', Auth::id())->where('status', 1)->get();
    
        $salesDay = Auth::user()->type == 1
            ? Sale::where('seller_id', Auth::id())
                ->where('status', 1)
                ->whereDate('created_at', $now->toDateString())
                ->count()
            : Sale::where('seller_id', Auth::id())
                ->where('status', 1)
                ->whereDate('created_at', $now->toDateString())
                ->count();

        $users = User::where('type', 2)
            ->withSum(['sales as total_sales' => function($query) {
                $query->join('invoices', 'sales.id', '=', 'invoices.sale_id')
                    ->select(DB::raw('COALESCE(SUM(invoices.value), 0)'));
            }], 'total_sales')
            ->orderByDesc('total_sales')
            ->take(10)
            ->get();
    
        return view('app.app', [
            'sales'    => $sales,
            'salesDay' => $salesDay,
            'ranking'  => $users
        ]);
    }
}
