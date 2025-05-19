<?php

namespace App\Http\Controllers\User;

use App\Exports\SalesExport;
use App\Http\Controllers\Controller;

use App\Models\Lists;
use App\Models\Sale;
use App\Models\SaleList;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class ListController extends Controller {
    
    public function listLists() {

        $lists = SaleList::orderBy('created_at', 'desc')->get();
        return view('app.List.list-lists', [
            'lists' => $lists
        ]);
    }

    public function viewList($id) {

        $list = SaleList::find($id);
        if (!$list) {
            return redirect()->back()->with('info', 'Lista indisponível!');
        }

        return view('app.List.list-update', [
            'list' => $list
        ]);
    }

    public function createdList(Request $request) {

        $startDate  = Carbon::parse($request->date_start);
        $endDate    = Carbon::parse($request->date_end);
    
        $existingListInside     = SaleList::where('start', '>=', $startDate)->where('end', '<=', $endDate)->exists();
        $existingListContains   = SaleList::where('start', '<=', $startDate)->where('end', '>=', $endDate)->exists();
    
        if ($existingListInside || $existingListContains) {
            return redirect()->back()->with('info', 'Já existe uma Lista nesse período e horário!');
        }
    
        $list = new SaleList();
        $list->name             = $request->name;
        $list->description      = $request->description;
        $list->start            = $startDate;
        $list->end              = $endDate;
        $list->status           = $request->status;
    
        if($list->save()) {
            return redirect()->route('list-lists')->with('success', 'Lista cadastrada com sucesso!');
        }
    
        return redirect()->route('list-lists')->with('error', 'Não foi possível cadastrar a Lista, tente novamente mais tarde!');
    }    
    
    public function updatedList(Request $request) {
        
        $list = SaleList::find($request->id);
        if ($list) {

            $currentStartDate = Carbon::parse($list->start);
            $currentEndDate = Carbon::parse($list->end);
    
            $newStartDate = !empty($request->date_start) ? Carbon::parse($request->date_start) : null;
            $newEndDate = !empty($request->date_end) ? Carbon::parse($request->date_end) : null;
    
            $isDateRangeDifferent = !($currentStartDate->eq($newStartDate) && $currentEndDate->eq($newEndDate));
    
            if ($isDateRangeDifferent && $newStartDate && $newEndDate) {

                $existingListInside = SaleList::where('id', '!==', $list->id)->where('start', '>=', $newStartDate)->where('end', '<=', $newEndDate)->exists();
                $existingListContains = SaleList::where('id', '!==', $list->id)->where('start', '<=', $newStartDate)->where('end', '>=', $newEndDate)->exists();
            
                if ($existingListInside || $existingListContains) {
                    return redirect()->back()->with('info', 'Já existe uma Lista nesse período e horário!');
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
            if ($request->has('status_protocol')) {
                $list->status_protocol = $request->status_protocol;
            }

            if ($list->save()) {
                return redirect()->back()->with('success', 'Lista atualizada com sucesso!');
            }
    
            return redirect()->route('list-lists')->with('error', 'Não foi possível atualizar a Lista, tente novamente mais tarde!');
        }
    
        return redirect()->route('list-lists')->with('error', 'Não foi possível atualizar a Lista, tente novamente mais tarde!');
    }      

    public function deletedList(Request $request) {

        $list = SaleList::find($request->id);
        if($list && $list->delete()) {

            return redirect()->back()->with('success', 'Lista excluída com sucesso!');
        }

        return redirect()->back()->with('error', 'Não foi possível excluir a Lista, dados da Lista não encontrados!');
    }

    public function excelList($id) {

        $list = SaleList::find($id);
        if ($list) {

            $sales = Sale::where('id_list', $list->id)->where('status', 1)->orderBy('label', 'asc')->get();
            return Excel::download(new SalesExport($sales), $list->name.$list->description.'.xlsx');
        }
        
        return redirect()->back()->with('error', 'Não foi possível realizar essa ação, dados da Lista não encontrados!');
    }

}
