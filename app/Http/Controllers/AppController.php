<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Post;
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

        $baseQuery = User::where('type', 2);
        if (Auth::user()->type != 1) {
            $baseQuery = $baseQuery->where('sponsor_id', Auth::user()->sponsor_id);
        }

        $subscribers = [
            'actives'       => (clone $baseQuery)->where('status', 1)->count(),
            'inactives'     => (clone $baseQuery)->where('status', 2)->count(),
        ];


        $types = [
            'CONSULTOR'         => User::where('type', 2)->where('level', 2)->where('sponsor_id', Auth::user()->id)->count(),
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
        
        $userType = Auth::user()->type;
        
        $posts = Post::when($userType != 1, function ($query) use ($userType) {
                $query->where(function ($q) use ($userType) {
                    $q->where('access_type', $userType)
                    ->orWhereNull('access_type');
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(5);

        $banners = Banner::when($userType != 1, function ($query) use ($userType) {
                $query->where(function ($q) use ($userType) {
                    $q->where('access_type', $userType)
                    ->orWhereNull('access_type');
                });
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return view('app.app', [
            'list'          => $list,
            'remainingTime' => $remainingTime,
            'subscribers'   => $subscribers,
            'types'         => $types,
            'rankings'      => $rankings,
            'networks'      => $networks,
            'posts'         => $posts,
            'banners'       => $banners
        ]);
    }
}
