<?php

namespace App\Http\Controllers\Sale;

use App\Http\Controllers\Assas\AssasController;
use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Invoice;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CouponController extends Controller {
    
    public function coupons(Request $request) {

        $query = Coupon::orderBy('created_at', 'desc');

        if (!empty($request->name)) {
            $query->where('name', 'LIKE', '%'.$request->name.'%');
        }

        if (!empty($request->expiry_date)) {
            $query->whereDate('expiry_date', $request->expiry_date);
        }

        if (!empty($request->id_user)) {
            $query->where('id_user', $request->id_user);
        }

        if (empty($request->id_user) && Auth::user()->type !== 1) {
            $query->where('id_user', Auth::user()->id);
        }

        $coupons = $query->paginate(30);
        return view('app.Sale.coupon', [
            'coupons' => $coupons,
            'users'   => User::orderBy('name', 'asc')->get()
        ]);
    }

    public function createCoupon(Request $request) {

        $couponName = $this->generateCouponName($request->name);

        if(!empty($request->id_user)) {
            $user = User::find($request->id_user);
            if (!$user) {
                return redirect()->back()->with('error', 'Usuário não localizado!');
            }
        }

        $coupon                 = new Coupon();
        $coupon->name           = $couponName;
        $coupon->description    = $request->description ?? $couponName;
        $coupon->expiry_date    = $request->expiry_date;
        $coupon->id_user        = $request->id_user;
        $coupon->percentage     = $request->percentage;
        $coupon->qtd            = $request->qtd;
        if($coupon->save()) {

            if(!empty($user)) {

                $expiryDate = \Carbon\Carbon::parse($request->expiry_date);
                $message =  "*Surpresa Especial para Você! 🎁* \r\n\r\n"
                            . "Como forma de agradecimento por ser um cliente incrível, preparamos um *cupom de {$request->percentage}% de desconto* para você aproveitar na sua próxima fatura! \r\n\r\n"
                            . "Código do cupom: *{$couponName}* \r\n"
                            . "Validade: *{$expiryDate->format('d/m/Y')}*\r\n\r\n"
                            . "Não deixe essa oportunidade passar! Use o código na sua fatura e aproveite para economizar. \r\n"
                            . "Agradecemos por fazer parte da nossa jornada! \r\n\r\n";

                $assas = new AssasController();
                $assas->sendWhatsapp('', $message, $user->phone, $user->token_whatsapp);
            }

            return redirect()->back()->with('success', 'CUPOM cadastrado com sucesso!');
        }

        return redirect()->back()->with('error', 'Não foi possível cadastrar o CUPOM!');
    }

    private function generateCouponName(string $name): string {

        $baseName = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $name));
        $existingCouponsCount = Coupon::where('name', 'like', "{$baseName}%")->count();
        return $existingCouponsCount > 0 ? "{$baseName}".($existingCouponsCount + 1) : $baseName;
    }

    public function deleteCoupon(Request $request) {

        $coupon = Coupon::find($request->id);
        if ($coupon && $coupon->delete()) {
            return redirect()->back()->with('success', 'CUPOM excluído com sucesso!');
        }

        return redirect()->back()->with('error', 'Não foi possível excluir o CUPOM!');
    }

    public function addCoupon(Request $request) {

        $coupon = Coupon::where('name', $request->name)->first();
        if (!$coupon) {
            return redirect()->back()->with('info', 'Nenhum CUPOM encontrado!');
        }

        if($coupon->qtd < 1) {
            return redirect()->back()->with('info', 'CUPOM esgotado!');
        }

        if(!empty($coupon->expiry_date) && $coupon->expiry_date < now()) {
            return redirect()->back()->with('info', 'CUPOM expirado!');
        }

        $assas = new AssasController();

        $invoice = Invoice::find($request->invoice_id);
        if ($invoice) {
            if ($coupon->percentage == 100) {
                if ($assas->cancelInvoice($invoice->token_payment)) {
                    
                    $invoice->status = 1;
                    if($invoice->save()) {
    
                        $coupon->qtd -= 1;
                        $coupon->save();
    
                        return redirect()->back()->with('success', 'CUPOM aplicado com sucesso!');
                    }
                }
            }
    
            $value      = $invoice->value * (1 - $coupon->percentage / 100);
            $commission = $invoice->commission * (1 - ($coupon->percentage + 5) / 100);
            $dueDate    = $invoice->due_date;
            $wallet     = $invoice->sale->seller->wallet;
           
            $charge = $assas->addDiscount($invoice->token_payment, $value, $dueDate, $commission, $wallet);
            if ($charge) {
    
                $invoice->url_payment   = $charge['invoiceUrl'];
                $invoice->token_payment = $charge['id'];
                $invoice->value         = $value;
                $invoice->commission    = $commission;
                if ($invoice->save()) {
    
                    $coupon->qtd -= 1;
                    $coupon->save();
    
                    return redirect()->back()->with('success', 'CUPOM aplicado com sucesso!');
                }
            }

            return redirect()->back()->with('error', 'Não foi possível aplicar o CUPOM!');
        }

        $sale = Sale::find($request->sale_id);
        if ($sale) {

            if ($coupon->percentage == 100) {
                $sale->status   = 1;
                $sale->type     = 3;
                if($sale->save()) {

                    $coupon->qtd -= 1;
                    $coupon->save();
                    return redirect()->back()->with('success', 'CUPOM aplicado com sucesso!');
                }
            }

            $sale->value_total      = $sale->value_total * (1 - $coupon->percentage / 100);
            $sale->commission       = $sale->commission * (1 - ($coupon->percentage + 5) / 100);
            $sale->token_payment    = null;
            if ($sale->save()) {
                $coupon->qtd -= 1;
                $coupon->save();

                return redirect()->back()->with('success', 'CUPOM aplicado com sucesso!');
            }
        }

        return redirect()->back()->with('error', 'Não foi possível aplicar o CUPOM!');
    }
}
