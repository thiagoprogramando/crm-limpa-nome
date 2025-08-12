<?php

namespace App\Http\Controllers\Assets;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller {

    public function store(Request $request) {
        
        $post               = new Post();
        $post->image        = $request->file('image')->store('assets-posts', 'public');
        $post->title        = $request->input('title');
        $post->content      = $request->input('content');
        $post->access_type  = $request->input('access_type');

        if ($post->save()) {
            return redirect()->back()->with('success', 'Publicação criada com sucesso!');
        }

        return redirect()->back()->with('error', 'Falha ao criar a Publicação!');
    }

    public function destroy(Request $request) {
        
        $post = Post::find($request->id);
        if (!$post) {
            return redirect()->back()->with('error', 'Post não encontrado!');
        }

        if ($post->image && Storage::disk('public')->exists($post->image)) {
            Storage::disk('public')->delete($post->image);
        }

        if ($post->delete()) {
            return redirect()->back()->with('success', 'Publicação excluída com sucesso!');
        }

        return redirect()->back()->with('error', 'Falha ao excluir o Publicação!');
    }
}
