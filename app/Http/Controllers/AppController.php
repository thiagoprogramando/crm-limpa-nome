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
            ->sum('value_total');
    
        $invoicingDay = Sale::where('id_seller', Auth::id())
            ->where('status', 1)
            ->whereDate('updated_at', $now->toDateString())
            ->sum('value_total');
    
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

        $consultant = [
            'CONSULTOR' => User::where('type', 2)->whereIn('level', [1, 2])->where('filiate', Auth::user()->id)->count(),
            'LIDER'     => User::where('type', 2)->where('level', 3)->where('filiate', Auth::user()->id)->count(),
            'REGIONAL'  => User::where('type', 2)->where('level', 4)->where('filiate', Auth::user()->id)->count(),
            'GERENTE'   => User::where('type', 2)->where('level', 5)->where('filiate', Auth::user()->id)->count(),
        ];

        $actives = User::where('type', 2)->where('filiate', Auth::user()->id)->whereDoesntHave('invoices', function ($query) {
            $query->where('type', 1)->where('status', 0);
        })->count();

        $inactives = User::where('type', 2)->where('filiate', Auth::user()->id)->whereHas('invoices', function ($query) {
            $query->where('type', 1)->where('status', 0);
        })->count();
    
        return view('app.app', [
            'sales'         => $sales,
            'salesDay'      => $salesDay,
            'saleValue'     => $saleValue,
            'list'          => $list,
            'remainingTime' => $remainingTime,
            'invoicing'     => $invoicing,
            'invoicingDay'  => $invoicingDay,
            'lists'         => Lists::orderBy('id', 'desc')->get(),
            'users'         => $users,
            'consultant'    => $consultant,
            'actives'       => $actives,
            'inactives'     => $inactives,
        ]);
    }    

    public function dashboard() {

        $now = now()->setTimezone('America/Sao_Paulo');
        $formattedNow = $now->format('Y-m-d H:i:s');
        $today = $now->toDateString();

        $sales = Sale::where('status', 1)->count();

        $salesDay = Sale::where('status', 1)
            ->whereDate('created_at', $today)
            ->count();

        $invoicing = Sale::where('status', 1)->sum('value');

        $invoicingDay = Sale::where('status', 1)
            ->whereDate('updated_at', $today)
            ->sum('value');

        $users = User::whereIn('type', [2, 5, 6, 7])->get();
        $sortedUsers = $users->sortByDesc(fn($user) => $user->saleTotal());
        $topUsers = $sortedUsers->take(10);

        $list = Lists::where('start', '<=', $formattedNow)
        ->where('end', '>=', $formattedNow)
        ->first();

        $remainingTime = '0d 0h';
        if ($list) {
            $endTime = Carbon::parse($list->end)->setTimezone('America/Sao_Paulo');
            $totalHours = $now->diffInHours($endTime);
            $totalDays = intdiv($totalHours, 24);
            $remainingHours = $totalHours % 24;
            $remainingTime = sprintf('%dd %dh', $totalDays, $remainingHours);
        }

        $consultant = [
            'CONSULTOR' => User::where('type', 2)->where('level', 2)->count(),
            'LIDER'     => User::where('type', 2)->where('level', 3)->count(),
            'REGIONAL'  => User::where('type', 2)->where('level', 4)->count(),
            'GERENTE'   => User::where('type', 2)->where('level', 5)->count(),
        ];

        $actives = User::where('type', 2)->whereDoesntHave('invoices', function ($query) {
            $query->where('type', 1)->where('status', 0);
        })->count();

        $inactives = User::where('type', 2)->whereHas('invoices', function ($query) {
            $query->where('type', 1)->where('status', 0);
        })->count();

        return view('app.app', [
            'sales'         => $sales,
            'salesDay'      => $salesDay,
            'invoicing'     => $invoicing,
            'invoicingDay'  => $invoicingDay,
            'users'         => $topUsers,
            'dashboard'     => true,
            'list'          => $list,
            'remainingTime' => $remainingTime,
            'consultant'    => $consultant,
            'actives'       => $actives,
            'inactives'     => $inactives,
        ]);
    } 
}
