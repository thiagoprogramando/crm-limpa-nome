<?php

namespace App\Http\Middleware;

use App\Models\Banner;
use App\Models\Lists;
use App\Models\Post;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class Share {
    
    public function handle(Request $request, Closure $next): Response {

        if (Auth::check()) {

            $userType   = Auth::user()->type;
            $now        = now()->setTimezone('America/Sao_Paulo');
            
            $posts = Post::when($userType != 1, function ($query) use ($userType) {
                $query->where(function ($q) use ($userType) {
                    $q->where('access_type', $userType)
                    ->orWhereNull('access_type');
                });
            })->orderBy('created_at', 'desc')->paginate(5);

            $banners = Banner::where('level', Auth::user()->level)->orWhereNull('level')->inRandomOrder()->get();

            $list = Lists::where('start', '<=', $now)->where('end', '>=', $now)->first();
            if ($list) {
                $endTime        = Carbon::parse($list->end)->setTimezone('America/Sao_Paulo');
                $totalHours     = $now->diffInHours($endTime);
                $totalDays      = intdiv($totalHours, 24);
                $remainingHours = $totalHours % 24;
                $remainingTime  = sprintf('%dd %dh', $totalDays, $remainingHours);
            }

            View::share([
                'posts'         => $posts,
                'banners'       => $banners,
                'list'          => $list,
                'remainingTime' => $remainingTime ?? '0d 0h',
            ]);
        }

        return $next($request);
    }
}
