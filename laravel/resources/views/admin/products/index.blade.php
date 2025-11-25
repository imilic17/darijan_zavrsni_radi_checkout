@extends('layouts.admin')

@section('title', 'Proizvodi — TechShop Admin')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">
                <i class="bi bi-box-seam me-2"></i> Proizvodi
            </h2>
            <p class="text-muted mb-0">
                Pronađeno proizvoda: {{ $products->count() }}
            </p>
        </div>

        <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Novi proizvod
        </a>
    </div>

    {{-- 🔍 SEARCH + CATEGORY FILTER --}}
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                {{-- Search term --}}
                <div class="col-md-5">
                    <label class="form-label small text-muted">Pretraži</label>
                    <input type="text"
                           name="q"
                           class="form-control"
                           placeholder="Naziv ili šifra proizvoda..."
                           value="{{ request('q') }}">
                </div>

                {{-- Category filter 
                <div class="col-md-4">
                    <label class="form-label small text-muted">Kategorija</label>
                    <select name="category" class="form-select">
                        <option value="">Sve kategorije</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id_kategorija }}"
                                @selected(request('category') == $cat->id_kategorija)>
                                {{ $cat->Naziv }}
                            </option>
                        @endforeach
                    </select>
                </div>
--}}
                <div class="col-md-3 d-flex gap-2">
                    <button class="btn btn-outline-secondary flex-fill" type="submit">
                        <i class="bi bi-search me-1"></i> Filtriraj
                    </button>

                    <a href="{{ route('admin.products.index') }}" class="btn btn-outline-light border flex-fill">
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- 📋 ENDLESS TABLE (no pagination) --}}
    <div class="card shadow-sm border-0">
        <div class="table-responsive" style="max-height: 70vh; overflow-y: auto;">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light sticky-top">
                    <tr>
                        <th style="width: 70px;">ID</th>
                        <th style="width: 80px;">Slika</th>
                        <th>Naziv</th>
                        <th>Šifra</th>
                        <th>Kategorija</th>
                        <th class="text-end">Cijena</th>
                        <th class="text-center">Zaliha</th>
                        <th class="text-end" style="width: 160px;">Akcije</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($products as $product)
                    <tr>
                        <td>#{{ $product->Proizvod_ID }}</td>

                        {{-- Thumbnail --}}
                        <td>
                            @if($product->Slika)
                                <img src="{{ asset($product->Slika) }}"
                                     alt="{{ $product->Naziv }}"
                                     class="rounded"
                                     style="width: 56px; height: 56px; object-fit: cover;">
                            @else
                                <div class="bg-light border rounded d-flex align-items-center justify-content-center"
                                     style="width: 56px; height: 56px;">
                                    <i class="bi bi-image text-muted"></i>
                                </div>
                            @endif
                        </td>

                        {{-- Name + short description --}}
                        <td>
                            <div class="fw-semibold">{{ $product->Naziv }}</div>
                            @if($product->KratkiOpis)
                                <div class="small text-muted">
                                    {{ \Illuminate\Support\Str::limit($product->KratkiOpis, 60) }}
                                </div>
                            @endif
                        </td>

                        {{-- Code --}}
                        <td>
                            <span class="badge bg-secondary-subtle text-dark border">
                                {{ $product->sifra }}
                            </span>
                        </td>

                        {{-- Category name --}}
                        <td>
                            {{ $product->kategorija->Naziv ?? '—' }}
                        </td>

                        {{-- Price --}}
                        <td class="text-end">
                            {{ number_format($product->Cijena, 2, ',', '.') }} €
                        </td>

                        {{-- Stock --}}
                        <td class="text-center">
                            @php $stock = $product->StanjeNaSkladistu; @endphp

                            @if($stock <= 0)
                                <span class="badge bg-danger">Nema</span>
                            @elseif($stock <= 5)
                                <span class="badge bg-warning text-dark">Niska ({{ $stock }})</span>
                            @else
                                <span class="badge bg-success">Na zalihi ({{ $stock }})</span>
                            @endif
                        </td>

                        {{-- Actions --}}
                        <td class="text-end">
                            <a href="{{ route('admin.products.edit', $product) }}"
                               class="btn btn-sm btn-outline-primary me-1">
                                <i class="bi bi-pencil-square me-1"></i> Uredi
                            </a>

                            <form action="{{ route('admin.products.destroy', $product) }}"
                                  method="POST" class="d-inline"
                                  onsubmit="return confirm('Jesi siguran da želiš obrisati ovaj proizvod?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash me-1"></i> Obriši
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">
                            Nema proizvoda za zadane kriterije.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
