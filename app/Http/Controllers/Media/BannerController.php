<?php

namespace App\Http\Controllers\Media;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller {
    
    public function created(Request $request) {
        
        $request->validate([
            'image'         => 'required|image',
        ], [
            'image.required'        => 'A imagem é obrigatória.',
            'image.image'           => 'O arquivo deve ser uma imagem válida.',
        ]);

        $banner = new Banner();
        $banner->image = $request->file('image')->store('images', 'public');
        $banner->link = $request->input('link');
        $banner->content = $request->input('content_banner');
        $banner->access_type = $request->input('access_type');

        if ($banner->save()) {
            return redirect()->back()->with('success', 'Banner salvo com sucesso!');
        }

        return redirect()->back()->with('error', 'Falha ao salvar Banner!');
    }

    public function deleted($id) {
        
        $banner = Banner::find($id);
        if (!$banner) {
            return redirect()->back()->with('error', 'Banner não encontrado!');
        }

        if ($banner->image && Storage::disk('public')->exists($banner->image)) {
            Storage::disk('public')->delete($banner->image);
        }

        if ($banner->delete()) {
            return redirect()->back()->with('success', 'Banner excluído com sucesso!');
        }

        return redirect()->back()->with('error', 'Falha ao excluir o Banner!');
    }
}
