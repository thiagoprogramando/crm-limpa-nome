<?php

namespace App\Http\Middleware;

use App\Models\Notification;
use Closure;

use Symfony\Component\HttpFoundation\Response;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

use App\Models\Product;

class ShareProducts {
 
    public function handle(Request $request, Closure $next): Response {

        if (Auth::check()) {
            $business = Product::where('active', 1)
                ->where(function ($query) {
                    $query->where('level', null)
                        ->orWhere('level', Auth::user()->level);
                })
                ->get();

            $notifications = Notification::where('user_id', Auth::id())->where('view', '!==', 1)->get();

            View::share([
                'business'      => $business,
                'notifications' => $notifications,
            ]);
        }

        return $next($request);
    }
}
