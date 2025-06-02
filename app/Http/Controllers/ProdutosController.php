<?php

namespace App\Http\Controllers;

use App\Models\Produtos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProdutosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Produtos::select('id', 'nome', 'preco_custo', 'preco_venda', 'imagem')->get();
    }


    /**
     * Store a newly created resource in storage.
     */
  public function store(Request $request)
{
    $request->validate([
        'nome' => 'required|max:100',
        'preco_custo' => 'required|numeric|min:0',
        'preco_venda' => 'required|numeric|min:0|gte:preco_custo',
        'image' => 'required|image|max:2048'
    ]);

    $nomeImage = Str::random() . '.' . $request->image->getClientOriginalExtension();

    Storage::disk('public')->putFileAs('produtos/img', $request->image, $nomeImage);

    Produtos::create($request->post() + ['image' => $nomeImage]);

    return response()->json([
        'success' => true,
        'message' => 'Dados salvos com sucesso.',
    ]);
}


    /**
     * Display the specified resource.
     */
    public function show(Produtos $produtos)
    {
        return response()->json([
            'produtos' => $produtos
        ]);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Produtos $produtos)
    {
        $request->validate([
            'nome' => 'required|max:100',
            'preco_custo' => 'required|numeric|min:0',
            'preco_venda' => 'required|numeric|min:0|gte:preco_custo',
            'image' => 'nullable'
        ]);
        $produtos->fill($request->post())->update();
        if ($request->hasFile('image')) {
            if ($produtos->image) {
                $imagexiste = Storage::disk('public')->exists("produtos/img{$produtos->image}");
                if ($imagexiste) {
                    Storage::disk('public')->delete("produtos/img{$produtos->image}");
                }
            }
            $nomeImage = Str::random() . '.' . $request->image->getClientOriginalExtesion();
            Storage::disk('public')->putFileAs('produtos/img', $request->image, $nomeImage);
            $produtos->image = $nomeImage;
            $produtos->save();
        }
        return response()->json([
            'success' => true,
            'message' => 'Dados atualizados com sucesso.',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Produtos $produtos)
    {
        if ($produtos->image) {
            $imagexiste = Storage::disk('public')->exists("produtos/img{$produtos->image}");
            if ($imagexiste) {
                Storage::disk('public')->delete("produtos/img{$produtos->image}");
            }
        }
        $produtos->delete();
        return response()->json([
            'success' => true,
            'message' => 'Dados apagados com sucesso.',
        ]);
    }
}
