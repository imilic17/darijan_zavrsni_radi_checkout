@extends('layouts.admin')

@section('content')
<h2 class="mb-4">Pregled administracije</h2>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <h6 class="text-muted">Proizvodi</h6>
                <h3 class="fw-bold">{{ $productsCount }}</h3>
                <p class="text-muted small">
                    Ukupan broj proizvoda u bazi.
                </p>
                <a href="{{ route('admin.products.index') }}" class="btn btn-sm btn-primary">
                    Upravljanje proizvodima
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <h6 class="text-muted">Narudžbe</h6>
                <h3 class="fw-bold">{{ $ordersCount }}</h3>
                <p class="text-muted small">
                    Sve narudžbe kupaca.
                </p>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-primary">
                    Upravljanje narudžbama
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <h6 class="text-muted">Korisnici</h6>
                <h3 class="fw-bold">{{ $usersCount }}</h3>
                <p class="text-muted small">
                    Registrirani korisnici (Laravel users).
                </p>
                <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-primary">
                    Pregled korisnika
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
