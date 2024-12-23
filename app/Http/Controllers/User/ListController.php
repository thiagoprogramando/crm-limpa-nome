<?php

namespace App\Http\Controllers\User;

use App\Exports\SalesExport;
use App\Http\Controllers\Controller;

use App\Models\Lists;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;

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
        if ($list) {
            
            $currentStartDate = Carbon::parse($list->start);
            $currentEndDate = Carbon::parse($list->end);
    
            $newStartDate = !empty($request->date_start) ? Carbon::parse($request->date_start) : null;
            $newEndDate = !empty($request->date_end) ? Carbon::parse($request->date_end) : null;
    
            $isDateRangeDifferent = !($currentStartDate->eq($newStartDate) && $currentEndDate->eq($newEndDate));
    
            if ($isDateRangeDifferent && $newStartDate && $newEndDate) {
                
                $existingListOverlap = Lists::where('id', '!=', $list->id)
                    ->where(function ($query) use ($newStartDate, $newEndDate) {
                        $query->whereBetween('start', [$newStartDate, $newEndDate])
                            ->orWhereBetween('end', [$newStartDate, $newEndDate])
                            ->orWhere(function ($query) use ($newStartDate, $newEndDate) {
                                $query->where('start', '<=', $newStartDate)
                                      ->where('end', '>=', $newEndDate);
                            });
                    })
                    ->exists();
    
                if ($existingListOverlap) {
                    return redirect()->back()->with('error', 'Já existe uma Lista nesse período!');
                }

                $list->start = $newStartDate;
                $list->end = $newEndDate;
            }

            if ($request->name) {
                $list->name = $request->name;
            }
            if ($request->description) {
                $list->description = $request->description;
            }
            if ($request->status) {
                $list->status = $request->status;
            }

            if ($request->has('serasa_status')) {
                $list->serasa_status = $request->serasa_status;
            }
            if ($request->has('status_spc')) {
                $list->status_spc = $request->status_spc;
            }
            if ($request->has('status_boa_vista')) {
                $list->status_boa_vista = $request->status_boa_vista;
            }
            if ($request->has('status_quod')) {
                $list->status_quod = $request->status_quod;
            }
            if ($request->has('status_cenprot')) {
                $list->status_cenprot = $request->status_cenprot;
            }
    
            if ($list->save()) {
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

    public function excelList($id) {

        $list = Lists::find($id);
        if ($list) {

            $sales = Sale::where('id_list', $list->id)->where('status', 1)->get();
            return Excel::download(new SalesExport($sales), $list->name.$list->description.'.xlsx');
        }
        
        return redirect()->back()->with('error', 'Não foi possível realizar essa ação, dados da Lista não encontrados!');
    }

}
