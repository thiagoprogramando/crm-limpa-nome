<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleList;
use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Carbon\Carbon;

class AppController extends Controller {

    public function app() {

        $now = now()->setTimezone('America/Sao_Paulo');
        $list = SaleList::where('start', '<=', $now)
            ->where('end', '>=', $now)
            ->first();
    
        $remainingTime = '0d 0h';
        if ($list) {
            $endTime = Carbon::parse($list->end)->setTimezone('America/Sao_Paulo');
            $totalHours = $now->diffInHours($endTime);
            $totalDays = intdiv($totalHours, 24);
            $remainingHours = $totalHours % 24;
            $remainingTime = sprintf('%dd %dh', $totalDays, $remainingHours);
        }

        $subscribers = [
            'actives'   => User::where('status', 1)->where('type', 2)->where('association_id', Auth::user()->association_id)->count(),
            'inactives' => User::where('status', 3)->where('type', 2)->where('association_id', Auth::user()->association_id)->count(),
        ];

        $types = [
            'CONSULTOR'         => User::where('type', 2)->whereIn('level', [1, 2])->where('sponsor_id', Auth::user()->id)->count(),
            'CONSULTOR LIDER'   => User::where('type', 2)->where('level', 3)->where('sponsor_id', Auth::user()->id)->count(),
            'REGIONAL'          => User::where('type', 2)->where('level', 4)->where('sponsor_id', Auth::user()->id)->count(),
            'GERENTE REGIONAL'  => User::where('type', 2)->where('level', 5)->where('sponsor_id', Auth::user()->id)->count(),
            'VENDEDOR INTERNO'  => User::where('type', 2)->where('level', 6)->where('sponsor_id', Auth::user()->id)->count(),
            'DIRETOR'           => User::where('type', 2)->where('level', 7)->where('sponsor_id', Auth::user()->id)->count(),
            'DIRETOR REGIONAL'  => User::where('type', 2)->where('level', 8)->where('sponsor_id', Auth::user()->id)->count(),
            'PRESIDENTE VIP'    => User::where('type', 2)->where('level', 9)->where('sponsor_id', Auth::user()->id)->count(),
        ];

        $rankings = User::where('type', 2)->withCount('sales')->orderBy('sales_count', 'desc')->paginate(10);
        $networks = User::where('type', 2)->where('sponsor_id', Auth::user()->id)->orderBy('created_at', 'desc')->paginate(10);

        return view('app.app', [
            'list'          => $list,
            'remainingTime' => $remainingTime,
            'subscribers'   => $subscribers,
            'types'         => $types,
            'rankings'      => $rankings,
            'networks'      => $networks,
        ]);
    }
}
