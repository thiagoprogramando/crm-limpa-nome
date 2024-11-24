<?php

namespace App\Http\Controllers;

use App\Models\Lists;
use App\Models\Sale;
use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Carbon\Carbon;


class AppController extends Controller {

    public function handleApp() {

        $user = Auth::user();
        if ($user->type == 1) {
            return $this->dashboard();
        } else {
            return $this->app();
        }
    }

    public function app() {

        $now = now()->setTimezone('America/Sao_Paulo');
    
        $sales = Sale::where('id_seller', Auth::id())
            ->where('status', 1)
            ->count();
    
        $salesDay = Auth::user()->type == 1
            ? Sale::where('status', 1)
                ->whereDate('created_at', $now->toDateString())
                ->count()
            : Sale::where('id_seller', Auth::id())
                ->where('status', 1)
                ->whereDate('created_at', $now->toDateString())
                ->count();
    
        $saleValue = Sale::where('id_seller', Auth::id())
            ->where('status', 1)
            ->sum('commission');
    
        $invoicing = Sale::where('id_seller', Auth::id())
            ->where('status', 1)
            ->sum('value');
    
        $invoicingDay = Sale::where('id_seller', Auth::id())
            ->where('status', 1)
            ->whereDate('updated_at', $now->toDateString())
            ->sum('value');
    
        $list = Lists::where('start', '<=', $now)
            ->where('end', '>=', $now)
            ->first();
    
        if ($list) {
            $endTime = Carbon::parse($list->end)->setTimezone('America/Sao_Paulo');
            
            $totalDays = ceil($now->diffInHours($endTime) / 24);
            $totalHours = $now->diffInHours($endTime) % 24;
            $remainingTime = sprintf('%dd %dh', $totalDays, $totalHours);
        } else {
            $remainingTime = '0d 0h';
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
            'invoicingDay'  => $invoicingDay,
            'lists'         => Lists::orderBy('id', 'desc')->get(),
            'users'         => $users
        ]);
    }    

    public function dashboard() {
        $now = now()->setTimezone('America/Sao_Paulo');
    
        $sales = Sale::where('status', 1)
            ->count();
    
        $salesDay = Sale::where('status', 1)
            ->whereDate('created_at', $now->toDateString())
            ->count();
    
        $invoicing = Sale::where('status', 1)
            ->sum('value');
    
        $invoicingDay = Sale::where('status', 1)
            ->whereDate('updated_at', $now->toDateString())
            ->sum('value');
    
        $users = User::whereIn('type', [2, 5, 6, 7])->get();
        $sortedUsers = $users->sortByDesc(function($user) {
            return $user->saleTotal();
        });
        $users = $sortedUsers->take(10);
    
        $list = Lists::where('start', '<=', $now)
            ->where('end', '>=', $now)
            ->first();
    
        if ($list) {
            $endTime = Carbon::parse($list->end)->setTimezone('America/Sao_Paulo');
    
            $totalDays = ceil($now->diffInHours($endTime) / 24);
            $totalHours = $now->diffInHours($endTime) % 24;
            $remainingTime = sprintf('%dd %dh', $totalDays, $totalHours);
        } else {
            $remainingTime = '0d 0h';
        }
    
        $consultant = [
            'CONSULTOR' => User::where('level', 2)->count(),
            'LIDER'     => User::where('level', 3)->count(),
            'REGIONAL'  => User::where('level', 4)->count(),
            'GERENTE'   => User::where('level', 5)->count(),
        ];
    
        $actives = User::where('type', 2)->whereDoesntHave('invoices', function ($query) {
            $query->where('type', 1)
                ->where('status', 0);
        })->count();
    
        $inactives = User::where('type', 2)->whereHas('invoices', function ($query) {
            $query->where('type', 1)
                ->where('status', 0);
        })->count();
    
        return view('app.app', [
            'sales'         => $sales,
            'salesDay'      => $salesDay,
            'invoicing'     => $invoicing,
            'invoicingDay'  => $invoicingDay,
            'users'         => $users,
            'dashboard'     => true,
            'list'          => $list,
            'remainingTime' => $remainingTime,
            'consultant'    => $consultant,
            'actives'       => $actives,
            'inactives'     => $inactives
        ]);
    }    
}
