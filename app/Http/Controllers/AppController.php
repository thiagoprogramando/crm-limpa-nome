<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Lists;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use Carbon\Carbon;


class AppController extends Controller {
    
    // public function app($id_list = null) {

    //     $sales      = Sale::where('id_seller', Auth::id())->where('status', 1)->count();
    //     $salesDay   = Auth::user()->type == 1 ? Sale::where('status', 1)->whereDate('created_at', Carbon::today())->count() : Sale::where('id_seller', Auth::id())->where('status', 1)->whereDate('created_at', Carbon::today())->count();
    //     $saleValue  = Sale::where('id_seller', Auth::id())->where('status', 1)->sum('commission');
    //     $commission = Invoice::where('id_user', Auth::id())->where('status', 1)->whereIn('type', [2, 3])->sum('commission');
    //     $invoicing  = Sale::where('id_seller', Auth::id())->where('status', 1)->sum('value');

    //     $list = Lists::where('start', '<=', now())->where('end', '>=', now())->first();
    //     if ($list) {
    //         $createdAt = new Carbon($list->end);
    //         $now = now();
    //         $diff = $now->diff($createdAt);
    
    //         $remainingTime = $diff->format('%dd %hh %im %ss');
    //     } else {
    //         $remainingTime = 0;
    //     }

    //     $sale = Sale::where('id_seller', Auth::id())->where('status', 1)->select(DB::raw('COUNT(*) as totalSales'), DB::raw('MONTH(created_at) as month'))->groupBy(DB::raw('MONTH(created_at)'))->get();
    //     $saleGraph = $sale->map(function($sale) {
    //         return [
    //             'month' => date('M', mktime(0, 0, 0, $sale->month, 1)),
    //             'totalSales' => $sale->totalSales,
    //         ];
    //     });

    //     $commissions = Invoice::where('id_user', Auth::id())->where('status', 1)->whereIn('type', [2, 3])->get();
    //     $commissionGraph = $commissions->map(function($commission) {
    //         return [
    //             'month' => $commission->created_at->format('M'),
    //             'totalCommissions' => $commission->commission,
    //         ];
    //     });

    //     return view('app.app', [
    //         'sales'           => $sales,
    //         'salesDay'        => $salesDay,
    //         'saleValue'       => $saleValue,
    //         'commission'      => $commission,
    //         'saleGraph'       => $saleGraph,
    //         'commissionGraph' => $commissionGraph,
    //         'list'            => $list,
    //         'remainingTime'   => $remainingTime,
    //         'invoicing'       => $invoicing,
    //         'lists'           => Lists::orderBy('id', 'desc')->get(),
    //     ]);
    // }

    public function app($id_list = null) {
        // Defina a condição para id_list
        $id_list_condition = $id_list ? [['id', $id_list]] : [];
    
        $sales = Sale::where('id_seller', Auth::id())
            ->where('status', 1)
            ->where($id_list_condition)
            ->count();
    
        $salesDay = Auth::user()->type == 1
            ? Sale::where('status', 1)
                ->whereDate('created_at', Carbon::today())
                ->where($id_list_condition)
                ->count()
            : Sale::where('id_seller', Auth::id())
                ->where('status', 1)
                ->whereDate('created_at', Carbon::today())
                ->where($id_list_condition)
                ->count();
    
        $saleValue = Sale::where('id_seller', Auth::id())
            ->where('status', 1)
            ->where($id_list_condition)
            ->sum('commission');
    
        $commission = Invoice::where('id_user', Auth::id())
            ->where('status', 1)
            ->whereIn('type', [2, 3])
            ->where($id_list_condition)
            ->sum('commission');
    
        $invoicing = Sale::where('id_seller', Auth::id())
            ->where('status', 1)
            ->where($id_list_condition)
            ->sum('value');
    
        $list = Lists::where('start', '<=', now())
            ->where('end', '>=', now())
            ->where($id_list_condition)
            ->first();
    
        if ($list) {
            $createdAt = new Carbon($list->end);
            $now = now();
            $diff = $now->diff($createdAt);
    
            $remainingTime = $diff->format('%dd %hh %im %ss');
        } else {
            $remainingTime = 0;
        }
    
        $sale = Sale::where('id_seller', Auth::id())
            ->where('status', 1)
            ->where($id_list_condition)
            ->select(DB::raw('COUNT(*) as totalSales'), DB::raw('MONTH(created_at) as month'))
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->get();
    
        $saleGraph = $sale->map(function($sale) {
            return [
                'month' => date('M', mktime(0, 0, 0, $sale->month, 1)),
                'totalSales' => $sale->totalSales,
            ];
        });
    
        $commissions = Invoice::where('id_user', Auth::id())
            ->where('status', 1)
            ->whereIn('type', [2, 3])
            ->where($id_list_condition)
            ->get();
    
        $commissionGraph = $commissions->map(function($commission) {
            return [
                'month' => $commission->created_at->format('M'),
                'totalCommissions' => $commission->commission,
            ];
        });
    
        return view('app.app', [
            'sales' => $sales,
            'salesDay' => $salesDay,
            'saleValue' => $saleValue,
            'commission' => $commission,
            'saleGraph' => $saleGraph,
            'commissionGraph' => $commissionGraph,
            'list' => $list,
            'remainingTime' => $remainingTime,
            'invoicing' => $invoicing,
            'lists' => Lists::orderBy('id', 'desc')->get(),
        ]);
    }
    
}
