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

        $users = User::whereIn('type', [2, 5, 6, 7])->get();

        $sortedUsers = $users->sortByDesc(function($user) {
            return $user->saleTotal();
        });

        $users = $sortedUsers->take(10);


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

    public function dashboard() {

        $today = Carbon::today();

        $clients['inadimplentes'] = User::where('type', '=', 3)
            ->whereHas('invoices', function ($query) use ($today) {
                $query->where('status', 0)
                    ->where('due_date', '<', $today);
            })->count();

        $clients['inday'] = User::where('type', '=', 3)
            ->whereHas('invoices', function ($query) use ($today) {
                $query->where('status', 0)
                      ->where('due_date', '>=', $today);
            })->count();

        $invoices = [
            'previstas' => Invoice::where('status', 0)
                ->where('due_date', '>=', $today)
                ->count(),

            'vencidas' => Invoice::where('status', 0)
                ->where('due_date', '<', $today)
                ->count(),
    
            'recebidas' => Invoice::where('status', 1)
                ->count(),
        ];

        $invoicing = [
            'previstas' => Invoice::where('status', 0)
                ->where('due_date', '>=', $today)
                ->sum('value'),

            'vencidas' => Invoice::where('status', 0)
                ->where('due_date', '<', $today)
                ->sum('value'),
    
            'recebidas' => Invoice::where('status', 1)
                ->sum('value'),
        ];

        $users = User::whereIn('type', [2, 5, 6, 7])->get();
        $sortedUsers = $users->sortByDesc(function($user) {
            return $user->saleTotal();
        });
        $users = $sortedUsers->take(10);

        return view('app.app', [
            'clients'   => $clients,
            'invoices'  => $invoices,
            'invoicing' => $invoicing,
            'users'     => $users,
            'dashboard' => true
        ]);
    }
    
}
