<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;

use App\Models\Lists;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ListController extends Controller {
    
    public function list() {

        $lists = Lists::orderBy('created_at', 'desc')->get();
        return view('app.List.list', ['lists' => $lists]);
    }

    public function create() {

        return view('app.List.create');
    }

    public function createList(Request $request) {

        $startDate = Carbon::parse($request->date_start);
        $endDate = Carbon::parse($request->date_end);

        $existingListInside = Lists::where('start', '>=', $startDate)->where('end', '<=', $endDate)->exists();
        $existingListContains = Lists::where('start', '<=', $startDate)->where('end', '>=', $endDate)->exists();
        if ($existingListInside || $existingListContains) {
            return redirect()->back()->with('error', 'Já existe uma Lista nesse período!');
        }

        $list = new Lists();
        $list->name         = $request->name;
        $list->description  = $request->description;
        $list->start        = $startDate;
        $list->end          = $endDate;
        $list->status       = $request->status;
        if($list->save()) {
            return redirect()->route('lists')->with('success', 'Lista criada com sucesso!');
        }

        return redirect()->route('lists')->with('error', 'Não foi possível realizar essa ação, tente novamente mais tarde!');
    }

    public function update($id) {

        $list = Lists::find($id);
        return view('app.List.update', ['list' => $list]);
    }

    public function updateList(Request $request) {

        $list = Lists::find($request->id);

        if(!empty($request->date_end) && !empty($request->date_end)) {
            $startDate = Carbon::parse($request->date_start);
            $endDate = Carbon::parse($request->date_end);

            $existingListInside = Lists::where('start', '>=', $startDate)->where('end', '<=', $endDate)->exists();
            $existingListContains = Lists::where('start', '<=', $startDate)->where('end', '>=', $endDate)->exists();
            if ($existingListInside || $existingListContains) {
                return redirect()->back()->with('error', 'Já existe uma Lista nesse período!');
            }
        }
        
        if($list) {
            if($request->name) {
                $list->name = $request->name;
            }
            if($request->description) {
                $list->description = $request->description;
            }
            if($request->status) {
                $list->status = $request->status;
            }

            if(!empty($endDate && !empty($startDate))) {
                $list->start = $startDate;
                $list->end   = $endDate;
            }
            
            if($list->save()) {
                return redirect()->back()->with('success', 'Lista atualizada com sucesso!');
            }

            return redirect()->route('lists')->with('error', 'Não foi possível realizar essa ação, tente novamente mais tarde!');
        }

        return redirect()->route('lists')->with('error', 'Não foi possível realizar essa ação, tente novamente mais tarde!');
    }

    public function delete(Request $request) {

        $list = Lists::find($request->id);
        if($list) {

            $list->delete();
            return redirect()->back()->with('success', 'Lista excluída com sucesso!');
        }

        return redirect()->back()->with('error', 'Não foi possível realizar essa ação, dados da Lista não encontrados!');
    }

}