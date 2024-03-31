<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Lists;
use App\Models\Sale;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use Carbon\Carbon;


class AppController extends Controller {
    
    public function app() {

        $sales      = Sale::where('id_seller', Auth::id())->where('status', 1)->count();
        $saleValue  = Sale::where('id_seller', Auth::id())->where('status', 1)->sum('commission');
        $commission = Invoice::where('id_user', Auth::id())->where('status', 1)->sum('commission');

        $list = Lists::where('start', '<=', now())->where('end', '>=', now())->first();
        if ($list) {
            $createdAt = new Carbon($list->end);
            $now = now();
            $diff = $now->diff($createdAt);
    
            $remainingTime = $diff->format('%dd %hh %im %ss');
        }

        $sale = Sale::where('id_seller', Auth::id())->where('status', 1)->select(DB::raw('COUNT(*) as totalSales'), DB::raw('MONTH(created_at) as month'))->groupBy(DB::raw('MONTH(created_at)'))->get();
        $saleGraph = $sale->map(function($sale) {
            return [
                'month' => date('M', mktime(0, 0, 0, $sale->month, 1)),
                'totalSales' => $sale->totalSales,
            ];
        });

        $commissions = Invoice::where('id_user', Auth::id())->where('status', 1)->get();
        $commissionGraph = $commissions->map(function($commission) {
            return [
                'month' => $commission->created_at->format('M'),
                'totalCommissions' => $commission->commission,
            ];
        });

        return view('app.app', [
            'sales'           => $sales,
            'saleValue'       => $saleValue,
            'commission'      => $commission,
            'saleGraph'       => $saleGraph,
            'commissionGraph' => $commissionGraph,
            'list'            => $list,
            'remainingTime'   => $remainingTime,
        ]);
    }
}
