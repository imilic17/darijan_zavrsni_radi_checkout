<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Proizvod;
use App\Models\Kategorija;
use App\Models\TipProizvoda;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    
 public function index(Request $request)
{
    $query = Proizvod::with('kategorija');

    // 🔍 SEARCH by any part of name or code (supports multiple words)
    if ($search = trim($request->input('q'))) {
        $terms = preg_split('/\s+/', $search); // split on spaces

        $query->where(function ($q) use ($terms) {
            foreach ($terms as $term) {
                $q->where(function ($sub) use ($term) {
                    $sub->where('Naziv', 'like', "%{$term}%")
                        ->orWhere('sifra', 'like', "%{$term}%");
                });
            }
        });
    }

    // 🏷 FILTER by category
    if ($categoryId = $request->input('category')) {
        $query->where('kategorija', $categoryId);
    }

    // endless list (no pagination)
    $products = $query->orderByDesc('Proizvod_ID')->get();
    $categories = Kategorija::orderBy('ImeKategorija')->get();

    return view('admin.products.index', compact('products', 'categories'));
}


    public function create()
    {
        $categories = Kategorija::orderBy('ImeKategorija')->get();
        $types = TipProizvoda::orderBy('Naziv')->get(); // <-- FIX: column is "Naziv"

        return view('admin.products.create', compact('categories', 'types'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            // FIXED TABLE NAME
            'sifra'               => ['required', 'string', 'max:100', 'unique:proizvod,sifra'],

            'Naziv'               => ['required', 'string', 'max:255'],
            'Opis'                => ['nullable', 'string'],
            'KratkiOpis'          => ['nullable', 'string', 'max:500'],
            'Cijena'              => ['required', 'numeric', 'min:0'],

            // FIXED: table = kategorija, PK = id_kategorija
            'kategorija'          => ['required', 'exists:kategorija,id_kategorija'],

            // FIXED: table = tipproizvoda, PK = id_tip
            'tip_id'              => ['nullable', 'exists:tipproizvoda,id_tip'],

            'StanjeNaSkladistu'   => ['required', 'integer', 'min:0'],
            'Slika'               => ['nullable', 'image'],
        ]);

        // handle image
        if ($request->hasFile('Slika')) {
            $data['Slika'] = $request->file('Slika')->store('products', 'public');
        }

        Proizvod::create($data);

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Proizvod je uspješno dodan.');
    }

    public function edit(Proizvod $product)
    {
        $categories = Kategorija::orderBy('Naziv')->get();
        $types = TipProizvoda::orderBy('Naziv')->get();

        return view('admin.products.edit', compact('product', 'categories', 'types'));
    }

    public function update(Request $request, Proizvod $product)
    {
        $data = $request->validate([
            'sifra'               => ['required', 'string', 'max:100', 'unique:proizvod,sifra,' . $product->Proizvod_ID . ',Proizvod_ID'],

            'Naziv'               => ['required', 'string', 'max:255'],
            'Opis'                => ['nullable', 'string'],
            'KratkiOpis'          => ['nullable', 'string', 'max:500'],
            'Cijena'              => ['required', 'numeric', 'min:0'],

            'kategorija'          => ['required', 'exists:kategorija,id_kategorija'],
            'tip_id'              => ['nullable', 'exists:tipproizvoda,id_tip'],

            'StanjeNaSkladistu'   => ['required', 'integer', 'min:0'],
            'Slika'               => ['nullable', 'image'],
        ]);

        if ($request->hasFile('Slika')) {

            if ($product->Slika) {
                Storage::disk('public')->delete($product->Slika);
            }

            $data['Slika'] = $request->file('Slika')->store('products', 'public');
        }

        $product->update($data);

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Proizvod je ažuriran.');
    }

    public function destroy(Proizvod $product)
    {
        if ($product->Slika) {
            Storage::disk('public')->delete($product->Slika);
        }

        $product->delete();

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Proizvod je obrisan.');
    }
}
