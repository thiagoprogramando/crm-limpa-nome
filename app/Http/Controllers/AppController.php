<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Lists;
use App\Models\Post;
use App\Models\Sale;
use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use Carbon\Carbon;


class AppController extends Controller {

    public function index() {

        $now = now()->setTimezone('America/Sao_Paulo');
    
        $sales = Sale::where('seller_id', Auth::id())->where('status', 1)->get();
    
        $salesDay = Auth::user()->type == 1
            ? Sale::where('status', 1)
                ->whereDate('created_at', $now->toDateString())
                ->count()
            : Sale::where('seller_id', Auth::id())
                ->where('status', 1)
                ->whereDate('created_at', $now->toDateString())
                ->count();
    
        return view('app.app', [
            'sales'    => $sales,
            'salesDay' => $salesDay,
        ]);
    }
}
