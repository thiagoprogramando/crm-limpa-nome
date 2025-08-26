<?php

namespace App\Http\Controllers\Sale;

use App\Http\Controllers\Assas\AssasController;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InvoiceController extends Controller {

    public function show($id) {

        $invoice = Invoice::find($id);
        if (!$invoice) {
            return redirect()->back()->with('infor', 'Cobrança não encontrada! Verifique os dados e tente novamente.');
        }

        return view('app.Sale.view-invoice', [
            'invoice' => $invoice
        ]);
    }

    public function store(Request $request) {

        $assasController    = new AssasController();
        $uuid               = Str::uuid();

        $product = Product::find($request->product_id);
        if (!$product) {
            return redirect()->back()->with('info', 'Produto indisponível!');
        }

        $sale = Sale::find($request->sale_id);
        if (!$sale) {
            return redirect()->back()->with('info', 'Não foi possível localizar os dados da Venda!');
        }
       
        $customer = $assasController->createCustomer($sale->client->name, $sale->client->cpfcnpj);
        if (!$customer) {
            return redirect()->back()->with('info', 'Verifique os dados do cliente e tente novamente!');
        }

        $percent         = ($this->formatarValor($request->value) * 0.05);
        $totalCommission = $this->formatarValor($request->value) - $percent;

        $commissions[] = [
            'walletId'          => $sale->seller->token_wallet,
            'fixedValue'        => number_format($totalCommission - 2, 2, '.', ''),
            'externalReference' => $uuid,
            'description'       => 'Comissão do vendedor - Venda N° '.$sale->id,
        ];
        $commissions[] = [
            'walletId'          =>  env('WALLET_G7'),
            'fixedValue'        => number_format($percent - 1, 2, '.', ''),
            'externalReference' => $uuid,
            'description'       => 'Comissão % - Venda N° '.$sale->id,
        ];
        $commissions[] = [
            'walletId'          => env('WALLET_EXPRESS'),
            'fixedValue'        => number_format(1, 1, '.', ''),
            'externalReference' => $uuid,
            'description'       => 'Comissão % - Venda N° '.$sale->id,
        ];
        
        $assasInvoice = $assasController->createCharge($customer, $request->payment_method, $this->formatarValor($request->value), 'Fatura '.($sale->invoices()->count() + 1).' para venda N° '.$sale->id, $request->due_date, $commissions);
        if ($assasInvoice <> false) {

            $invoice                        = new Invoice();
            $invoice->user_id               = $sale->client_id;
            $invoice->product_id            = $product->id;
            $invoice->sale_id               = $sale->id;
            $invoice->name                  = 'Fatura '.($sale->invoices()->count() + 1).' para venda N° '.$sale->id;
            $invoice->description           = 'Fatura '.($sale->invoices()->count() + 1).' para venda N° '.$sale->id;
            $invoice->payment_token         = $assasInvoice['id'];
            $invoice->payment_url           = $assasInvoice['invoiceUrl'];
            $invoice->payment_splits        = json_encode($assasInvoice['splits']);
            $invoice->due_date              = $request->due_date;
            $invoice->value                 = $this->formatarValor($request->value);
            $invoice->commission_seller     = $totalCommission ?? 0;
            $invoice->commission_filiate    = $sponsorCommission ?? 0;
            $invoice->status                = 2;
            $invoice->num                   = $sale->invoices()->count() + 1;
            $invoice->type                  = 3;
            if($invoice->save()) {
                return redirect()->back()->with('success', 'Fatura adicionada com sucesso!');
            }

            return redirect()->back()->with('info', 'Não foi possível adicionar Fatura, verifique os dados e tentar novamente!');
        }
            
        return redirect()->back()->with('info', 'Não foi possível adicionar Fatura, verifique os dados e tentar novamente!');
    }

    public function update(Request $request) {

        $invoice = Invoice::find($request->id);
        if(!$invoice) {
            return redirect()->back()->with('info', 'Não foi possível localizar os dados da Fatura!');
        }

        $assasController = new AssasController();
        if($invoice->status !== 1) {

            $invoiceAssas = $assasController->updateCharge($invoice->payment_token, $request->due_date);
            if($invoiceAssas !== false) {
                $invoice->due_date       = $request->due_date;
                $invoice->payment_url    = $invoiceAssas['invoiceUrl'];
                $invoice->payment_token  = $invoiceAssas['id'];
                $invoice->save();

                return redirect()->back()->with('success', 'Fatura atualizada com sucesso!');
            }

            return redirect()->back()->with('error', 'Não foi possível alterar a Fatura, tente novamente mais tarde!');
        }

        return redirect()->back()->with('error', 'Não é possível alterar uma Fatura já conciliada!');
    }

    public function destroy(Request $request) {

        $assasController = new AssasController();

        $invoice = Invoice::find($request->id);
        if(!$invoice) {
            return redirect()->back()->with('info', 'Não foi possível localizar os dados da Fatura!');
        }

        if($invoice->status !== 1) {

            $invoiceAssas = $assasController->cancelCharge($invoice->payment_token);
            if($invoice->delete() && $invoiceAssas !== false) {
                return redirect()->back()->with('success', 'Fatura excluída com sucesso!');
            }

            return redirect()->back()->with('error', 'Não foi possível excluir a Fatura, tente novamente mais tarde!');
        }

        return redirect()->back()->with('error', 'Não é possível excluir uma Fatura já conciliada!');
    }

    private function formatarValor($valor) {
        
        $valor = preg_replace('/[^0-9,]/', '', $valor);
        $valor = str_replace(',', '.', $valor);
        $valorFloat = floatval($valor);
    
        return number_format($valorFloat, 2, '.', '');
    }
}
