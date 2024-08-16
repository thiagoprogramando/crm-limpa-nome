<?php

namespace App\Http\Controllers\Photoshop;

use App\Http\Controllers\Controller;

use App\Models\Photoshop;

use setasign\Fpdi\Fpdi;
use TCPDF;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PhotoshopController extends Controller {
    
    public function list(Request $request) {

        $query = Photoshop::orderBy('created_at', 'desc');

        if (!empty($request->name)) {
            $query->where('name', 'LIKE', '%'.$request->name.'%');
        }

        $photoshops = $query->get();

        return view('app.Photoshop.list', [
            'photoshops' => $photoshops
        ]);
    }

    public function createPhotoshop(Request $request) {

        if(!empty($request->name) && $request->hasFile('file')) {

            $path = $request->file('file')->store('photoshop', 'public');

            $photoshop         = new Photoshop();
            $photoshop->name   = $request->name;
            $photoshop->file   = $path;
           
            if($photoshop->save()) {
                return redirect()->back()->with('success', 'Arquivo salvo com sucesso!');
            }

            return redirect()->back()->with('error', 'Falha ao salvar o arquivo!');
        }

        return redirect()->back()->with('error', 'É necessário informar um nome e um arquivo!');
    }

    public function deletePhotoshop(Request $request) {

        $photoshop = Photoshop::find($request->id);
        if($photoshop) {

            if(!empty($photoshop->file) && Storage::exists($photoshop->file)) {
                Storage::delete($photoshop->file);
            }

            $photoshop->delete();
            return redirect()->back()->with('success', 'Registro excluído com sucesso!');
        }

        return redirect()->back()->with('error', 'Não foi possível realizar essa ação, tente novamente mais tarde!');
    }

    public function createMidia(Request $request) {
        $photoshop = Photoshop::find($request->photoshop_id);
    
        if ($photoshop) {
            $pdfPath = storage_path('app/public/' . $photoshop->file);
    
            // Inicializar FPDI e TCPDF
            $pdf = new FPDI();
            $pdf->AddPage();
            $pdf->setSourceFile($pdfPath);
    
            // Importar a primeira página do PDF existente
            $tplId = $pdf->importPage(1);
            $pdf->useTemplate($tplId);
    
            // Adicionar texto no canto inferior direito
            $pdf->SetFont('Helvetica', '', 12);
            $pdf->SetXY(10, -40);
            
            // Ajustar a posição Y para adicionar o texto embaixo
            $yPosition = -40;
    
            if ($request->has('name')) {
                $pdf->SetXY(10, $yPosition);
                $pdf->MultiCell(0, 10, 'Name: ' . $request->name);
                $yPosition += 10; // Ajustar a posição Y para o próximo texto
            }
    
            if ($request->has('address')) {
                $pdf->SetXY(10, $yPosition);
                $pdf->MultiCell(0, 10, 'Address: ' . $request->address);
                $yPosition += 10; // Ajustar a posição Y para o próximo texto
            }
    
            if ($request->has('whatsapp')) {
                $pdf->SetXY(10, $yPosition);
                $pdf->MultiCell(0, 10, 'WhatsApp: ' . $request->whatsapp);
            }
    
            // Salvar o PDF modificado
            $outputPath = storage_path('app/public/final_document.pdf');
            $pdf->Output('F', $outputPath);
    
            // Retornar o PDF para download
            return response()->download($outputPath);
        } else {
            return response()->json(['error' => 'Dados insuficientes ou Photoshop não encontrado'], 400);
        }
    }

}
