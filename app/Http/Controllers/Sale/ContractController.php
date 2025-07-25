<?php

namespace App\Http\Controllers\Sale;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

use Carbon\Carbon;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class ContractController extends Controller {

    public function viewContract($saleId)  {

        $sale = Sale::with(['product', 'client', 'seller'])->find($saleId);
        if (!$sale) {
            return redirect()->back()->with('info', 'Não foi possível localizar os dados da venda! Tente novamente mais tarde.');
        }

        if (empty($sale->product->subject_contract)) {
            return redirect()->back()->with('info', 'Não há Contrato disponível para venda N° '.$sale->id);
        }
  
        $contractContent = Str::of($sale->product->subject_contract)
            ->replace('{CLIENT_NAME}', $sale->client->name ?? 'N/A')
            ->replace('{CLIENT_CPFCNPJ}', $sale->client->cpfcnpj ?? 'N/A')
            ->replace('{CLIENT_BIRTH_DATE}', $sale->client->birth_date 
                ? Carbon::parse($sale->client->birth_date)->format('d/m/Y') 
                : 'N/A')
            ->replace('{SELLER_NAME}', 
                $sale->seller?->company_name ?? $sale->seller?->sponsor()?->company_name ?? 'Express Consultoria & Tecnologia'
            )
            ->replace('{SELLER_CPFCNPJ}', 
                $sale->seller?->company_cpfcnpj ?? $sale->seller?->sponsor()?->company_cpfcnpj ?? '60.730.811/0001-70'
            )
            ->replace('{SELLER_ADDRESS}', 
                $sale->seller?->company_address ?? $sale->seller?->sponsor()?->company_address ?? 'Av. Deodoro da Fonseca 301B - Natal/RN'
            )
            ->replace('{SELLER_EMAIL}', 
                $sale->seller?->company_email ?? $sale->seller?->sponsor()?->company_email ?? 'financas@expressoftwareclub.com'
            )
            ->replace('{SALE_VALUE}', 
                $sale->value ? number_format($sale->value, 2, ',', '.') : '---'
            )
            ->replace('{SALE_METHOD}', 
                $sale->paymentMethod() . ' em ' . $sale->installments . 'x'
            )
            ->replace('{SALE_DATE}', date('d') . '/' . date('m') . '/' . date('Y'));

        return view('app.Contract.contract', [
            'title'           => 'Contrato de serviço - ' . $sale->product->name,
            'contractContent' => $contractContent,
            'sale'            => $sale,
            'invoices'        => Invoice::where('sale_id', $sale->id)->get()
        ]);
    }

    public function signSale(Request $request) {

        $sale = Sale::find($request->id);
        if (!$sale) {
            return response()->json(['success' => false, 'message' => 'Contrato não encontrado na base de dados!'], 403, [], JSON_UNESCAPED_UNICODE);
        }

        $signatureImage = $request->sign;
        $signatureBlock = '
            <div class="container text-center mt-3 mb-5">
                <img src="' . $signatureImage . '" alt="Assinatura" style="max-width: 100%; height: auto;">
                <br>
                <small>Assinatura ' . e($sale->client->name) . '</small>
            </div>
        ';

        $sale->contract_url     = $request->html . $signatureBlock;
        $sale->contract_sign    = $signatureImage;
        if ($sale->save()) {
            return response()->json(['success' => true, 'message' => 'Contrato Assinado com sucesso!'], 200, [], JSON_UNESCAPED_UNICODE);
        }

        return response()->json(['success' => false, 'message' => ''], 403, [], JSON_UNESCAPED_UNICODE);
    }
}
