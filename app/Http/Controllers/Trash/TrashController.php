<?php

namespace App\Http\Controllers\Trash;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TrashController extends Controller {
    
    public function trashSales(Request $request) {

        $query = Sale::onlyTrashed()->orderBy('created_at', 'desc');
    
        $currentUser = Auth::user();
        $affiliateIds = User::where('sponsor_id', $currentUser->id)->pluck('id')->toArray();
        $accessibleUserIds = array_merge([$currentUser->id], $affiliateIds);
    
        if (Auth::user()->type == 1) {
            if (!empty($request->seller_id)) {
                $query->where('seller_id', $request->seller_id);
            }
        } else {
            if (!empty($request->seller_id)) {
                $query->where('seller_id', $request->seller_id);
            } else {
                $query->whereIn('seller_id', $accessibleUserIds);
            }
        }

        if (!empty($request->name)) {
            $users = User::where('name', 'LIKE', '%' . $request->name . '%')->pluck('id')->toArray();
            if (!empty($users)) {
                $query->whereIn('client_id', $users);
            }
        }

        if (!empty($request->id)) {
            $query->where('id', $request->id);
        }
    
        if (!empty($request->created_at)) {
            $query->whereDate('created_at', $request->created_at);
        }

        $sales = $query->paginate(30);
        return view('app.Trash.sales', [
            'sales' => $sales,
        ]);
    }

    public function trashUsers(Request $request) {

        $query = User::onlyTrashed()->orderBy('name', 'desc');

        $currentUser = Auth::user();
        $affiliateIds = User::where('sponsor_id', $currentUser->id)->pluck('id')->toArray();
        $accessibleUserIds = array_merge([$currentUser->id], $affiliateIds);
        
        if (Auth::user()->type == 1) {
            if (!empty($request->id)) {
                $query->where('id', $request->id);
            }
        } else {
            if (!empty($request->id)) {
                $query->where('id', $request->id);
            } else {
                $query->whereIn('id', $accessibleUserIds);
            }
        }

        if (!empty($request->name)) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        if (!empty($request->email)) {
            $query->where('email', 'like', '%' . $request->email . '%');
        }

        if (!empty($request->cpfcnpj)) {
            $query->where('cpfcnpj', $request->cpfcnpj);
        }

        $users = $query->paginate(30);
        return view('app.Trash.users', [
            'users' => $users,
        ]);
    }

}
