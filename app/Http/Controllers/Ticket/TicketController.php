<?php

namespace App\Http\Controllers\Ticket;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TicketController extends Controller {
    
    public function index(Request $request) {
        
        $query = Ticket::orderBy('status', 'desc');
        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }
    
        if ($request->has('created_at') && !empty($request->created_at)) {
            $query->whereDate('created_at', $request->created_at);
        }
    
        if ($request->has('user_id') && !empty($request->user_id)) {
            $query->where('user_id', $request->user_id);
        }

        if (empty($request->user_id) && Auth::user()->type !== 1) {
            $query->where(function ($q) {
                $q->where('user_id', Auth::id());
            });
        }

        return view('app.Ticket.list-tickets', [
            'tickets' => $query->paginate(10)
        ]);
    }

    public function create() {
        
    }

    public function store(Request $request) {
        
        $ticket                 = new Ticket();
        $ticket->uuid           = str::uuid();
        $ticket->user_id        = Auth::id();
        $ticket->problem        = $request->input('problem');
        if ($ticket->save()) {
            return redirect()->back()->with('success', 'Ticket aberto! Aguarde notificações sobre o andamento do seu ticket.');
        }

        return redirect()->back()->with('error', 'Não foi possível criar o ticket.');
    }

    public function update(Request $request, $id) {
        
        $ticket = Ticket::find($id);
        if (!$ticket) {
            return redirect()->back()->with('error', 'Ticket não encontrado!');
        }

        $ticket->problem     = $request->input('problem');
        $ticket->resolution  = $request->input('resolution');
        $ticket->status      = $request->input('status');
        if ($ticket->save()) {
            return redirect()->back()->with('success', 'Ticket atualizado!');
        }

        return redirect()->back()->with('error', 'Não foi possível criar o ticket.');
    }

    public function destroy(Request $request, $id) {
        
        $ticket = Ticket::find($id);
        if ($ticket && $ticket->delete()) {
           return redirect()->back()->with('success', 'Ticket excluído com sucesso!');
        }

         return redirect()->back()->with('error', 'Ticket não encontrado!');
    }
}
