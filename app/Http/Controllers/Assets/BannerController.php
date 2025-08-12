<?php

namespace App\Http\Controllers\Assets;

use App\Http\Controllers\Controller;

use App\Models\Banner;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BannerController extends Controller {
    
    public function store(Request $request) {

        $banner                 = new Banner();
        $banner->title          = $request->title;
        $banner->description    = $request->description;
        $banner->url            = $request->url;
        $banner->level          = $request->level;

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('assets-banners', 'public');
            $banner->image = $path;
        } else {
            return back()->with('error', 'Nenhuma imagem foi enviada.');
        }

        if ($banner->save()) {
            return back()->with('success', 'Banner cadastrado com sucesso.');
        }

        return back()->with('info', 'Não foi possível cadastrar o banner, verifique os dados e tente novamente!');
    }

    public function destroy(Request $request, $id) {

        $banner = Banner::find($id);
        if ($banner) {

            if (Storage::delete('public/' . $banner->image) && $banner->delete()) {
                return back()->with('success', 'Banner excluído com sucesso!');
            } 

            return back()->with('error', 'Não foi possível excluir o banner, tente novamente mais tarde.');
        }

        return back()->with('error', 'Banner não encontrado ou já excluído.');
    }
}
