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

        if (empty($request->id_user) && Auth::user()->type !== 1) {
            $query->where('id_user', Auth::user()->id);
        }

        $coupons = $query->paginate(30);
        return view('app.Sale.coupon', [
            'coupons' => $coupons,
            'users'   => User::orderBy('name', 'asc')->get()
        ]);
    }

    public function created(Request $request) {

        $couponName = $this->generateCouponName($request->name);

        $coupon                 = new Coupon();
        $coupon->name           = $couponName;
        $coupon->description    = $request->description ?? $couponName;
        $coupon->expiry_date    = $request->expiry_date;
        $coupon->percentage     = $request->percentage;
        $coupon->qtd            = $request->qtd;
        if($coupon->save()) {
            return redirect()->back()->with('success', 'CUPOM cadastrado com sucesso!');
        }

        return redirect()->back()->with('error', 'Não foi possível cadastrar o CUPOM!');
    }

    public function deleted(Request $request) {

        $coupon = Coupon::find($request->id);
        if ($coupon && $coupon->delete()) {
            return redirect()->back()->with('success', 'CUPOM excluído com sucesso!');
        }

        return redirect()->back()->with('error', 'Não foi possível excluir o CUPOM!');
    }

    public function addCoupon(Request $request) {

        $coupon = Coupon::where('name', $request->name)->first();
        if (!$coupon) {
            return redirect()->back()->with('info', 'Código inválido!');
        }

        if($coupon->qtd < 1) {
            return redirect()->back()->with('info', 'CUPOM já foi  utilizado!');
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
    
            $value   = $invoice->value * (1 - $coupon->percentage / 100);
            $dueDate = $invoice->due_date;
           
            $charge = $assas->updateCharge($invoice->token_payment, $dueDate, $value);
            if ($charge) {
    
                $invoice->payment_url   = $charge['invoiceUrl'];
                $invoice->payment_token = $charge['id'];
                $invoice->value         = $value;
                if ($invoice->save()) {
    
                    $coupon->qtd -= 1;
                    $coupon->save();
    
                    return redirect()->back()->with('success', 'CUPOM aplicado com sucesso!');
                }
            }

            return redirect()->back()->with('error', 'Não foi possível aplicar o CUPOM!');
        }

        return redirect()->back()->with('error', 'Não foi possível aplicar o CUPOM!');
    }

    private function generateCouponName(string $name): string {

        $baseName = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $name));
        $existingCouponsCount = Coupon::where('name', 'like', "{$baseName}%")->count();
        return $existingCouponsCount > 0 ? "{$baseName}".($existingCouponsCount + 1) : $baseName;
    }
}
