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
    public function index()
    {
        $products = Proizvod::with(['kategorija', 'tip'])
            ->orderBy('id', 'desc')
            ->paginate(20);

        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        $categories = Kategorija::orderBy('Naziv')->get();
        $types = TipProizvoda::orderBy('naziv')->get();

        return view('admin.products.create', compact('categories', 'types'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'sifra'               => ['required', 'string', 'max:100', 'unique:proizvodi,sifra'],
            'Naziv'               => ['required', 'string', 'max:255'],
            'Opis'                => ['nullable', 'string'],
            'KratkiOpis'          => ['nullable', 'string', 'max:500'],
            'Cijena'              => ['required', 'numeric', 'min:0'],
            'kategorija'          => ['required', 'exists:kategorije,id'],
            'tip_id'              => ['nullable', 'exists:tipovi,id'],
            'StanjeNaSkladistu'   => ['required', 'integer', 'min:0'],
            'Slika'               => ['nullable', 'image'],
        ]);

        // handle image upload
        if ($request->hasFile('Slika')) {
            $path = $request->file('Slika')->store('products', 'public');
            $data['Slika'] = $path;
        }

        Proizvod::create($data);

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Proizvod je uspješno dodan.');
    }

    public function edit(Proizvod $product)
    {
        $categories = Kategorija::orderBy('Naziv')->get();
        $types = TipProizvoda::orderBy('naziv')->get();

        return view('admin.products.edit', compact('product', 'categories', 'types'));
    }

    public function update(Request $request, Proizvod $product)
    {
        $data = $request->validate([
            'sifra'               => ['required', 'string', 'max:100', 'unique:proizvodi,sifra,' . $product->id],
            'Naziv'               => ['required', 'string', 'max:255'],
            'Opis'                => ['nullable', 'string'],
            'KratkiOpis'          => ['nullable', 'string', 'max:500'],
            'Cijena'              => ['required', 'numeric', 'min:0'],
            'kategorija'          => ['required', 'exists:kategorije,id'],
            'tip_id'              => ['nullable', 'exists:tipovi,id'],
            'StanjeNaSkladistu'   => ['required', 'integer', 'min:0'],
            'Slika'               => ['nullable', 'image'],
        ]);

        if ($request->hasFile('Slika')) {
            if ($product->Slika) {
                Storage::disk('public')->delete($product->Slika);
            }

            $path = $request->file('Slika')->store('products', 'public');
            $data['Slika'] = $path;
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
