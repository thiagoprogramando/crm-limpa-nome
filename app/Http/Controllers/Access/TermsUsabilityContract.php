<?php

namespace App\Http\Controllers\Access;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class TermsUsabilityContract extends Controller {
    
    public function index() {

        return view('app.Contract.terms_of_usability', [
            'title' => 'Contrato de Adesão e Uso do Sistema – Consultores'
        ]);
    }

    public function signTerms(Request $request) {

        $user = User::where('uuid', $request->uuid)->first();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Você precisa acessar o sistema para Assinar!'
            ], 403, [], JSON_UNESCAPED_UNICODE);
        }

        $signatureImage = $request->sign;
        $signatureBlock = '
            <div class="container text-center mt-3 mb-5">
                <img src="' . $signatureImage . '" alt="Assinatura" style="max-width: 100%; height: auto;">
                <br>
                <small>Assinatura ' . e($user->company_name) . '</small>
            </div>
        ';

        $user->terms_of_usability = $request->html . $signatureBlock;
        if ($user->save()) {
            return response()->json([
                'success' => true,
                'message' => 'Contrato Assinado com sucesso!'
            ], 200, [], JSON_UNESCAPED_UNICODE);
        }

        return response()->json([
            'success' => false,
            'message' => 'Erro ao salvar o contrato!'
        ], 403, [], JSON_UNESCAPED_UNICODE);
    }
}
