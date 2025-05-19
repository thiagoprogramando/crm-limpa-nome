<?php

namespace App\Http\Controllers\Media;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller {

    public function created(Request $request) {
        
        $request->validate([
            'image'         => 'required|image',
            'title'         => 'required|string|max:255',
            'tags'          => 'nullable|string',
            'content'        => 'required|string',
        ], [
            'image.required'        => 'A imagem é obrigatória.',
            'image.image'           => 'O arquivo deve ser uma imagem válida.',
            'title.required'        => 'O título é obrigatório.',
            'title.string'          => 'O título deve ser um texto.',
            'title.max'             => 'O título não pode ter mais que 255 caracteres.',
            'content.required'      => 'O conteúdo é obrigatório.',
        ]);

        $post = new Post();
        $post->image = $request->file('image')->store('images', 'public');
        $post->title = $request->input('title');
        $post->tags = $request->input('tags');
        $post->content = $request->input('content');
        $post->access_type = $request->input('access_type');

        if ($post->save()) {
            return redirect()->back()->with('success', 'Publicação criada com sucesso!');
        }

        return redirect()->back()->with('error', 'Falha ao criar a Publicação!');
    }

    public function deleted(Request $request) {
        
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
