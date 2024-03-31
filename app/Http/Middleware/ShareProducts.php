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
            $business = Product::where('level', null)
                ->orWhere('level', Auth::user()->level)
                ->get();

            $notifications = Notification::where('id_user', Auth::id())
                ->orWhere('id_user', null)
                ->get();

            $totalNotification = Notification::where('id_user', Auth::id())
                ->where('view', null)
                ->count();

            View::share([
                'business'          => $business,
                'notifications'     => $notifications,
                'totalNotification' => $totalNotification
            ]);
        }

        return $next($request);
    }
}
