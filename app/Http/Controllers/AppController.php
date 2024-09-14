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

    public function app() {
    
        $sales = Sale::where('id_seller', Auth::id())
            ->where('status', 1)
            ->count();
    
        $salesDay = Auth::user()->type == 1
            ? Sale::where('status', 1)
                ->whereDate('created_at', Carbon::today())
                ->count()
            : Sale::where('id_seller', Auth::id())
                ->where('status', 1)
                ->whereDate('created_at', Carbon::today())
                ->count();
    
        $saleValue = Sale::where('id_seller', Auth::id())
            ->where('status', 1)
            ->sum('commission');
    
        $commission = Invoice::where('id_user', Auth::id())
            ->where('status', 1)
            ->whereIn('type', [2, 3])
            ->sum('commission');
    
        $invoicing = Sale::where('id_seller', Auth::id())
            ->where('status', 1)
            ->sum('value');
    
        $list = Lists::where('start', '<=', now())
            ->where('end', '>=', now())
            ->first();
    
        if ($list) {
            $createdAt = new Carbon($list->end);
            $now = now();
            $diff = $now->diff($createdAt);
    
            $remainingTime = $diff->format('%dd %hh %im %ss');
        } else {
            $remainingTime = 0;
        }

        $users = User::whereIn('type', [2, 3, 4, 5, 6, 7])->paginate(20)->sortByDesc(function($user) {
            return $user->saleTotal();
        });

    
        return view('app.app', [
            'sales'         => $sales,
            'salesDay'      => $salesDay,
            'saleValue'     => $saleValue,
            'list'          => $list,
            'remainingTime' => $remainingTime,
            'invoicing'     => $invoicing,
            'lists'         => Lists::orderBy('id', 'desc')->get(),
            'users'         => $users
        ]);
    }
    
}
