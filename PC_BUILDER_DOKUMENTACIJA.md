# PC Builder - Kompletni vodič implementacije


## 1. NOVE DATOTEKE

### 1.1 Database Migracije

| Datoteka | Opis |
|----------|------|
| `database/migrations/2025_12_10_000001_create_pc_component_types_table.php` | Tablica za tipove komponenti (CPU, GPU, RAM...) |
| `database/migrations/2025_12_10_000002_create_pc_component_specs_table.php` | Tablica za tehničke specifikacije proizvoda |
| `database/migrations/2025_12_10_000003_create_pc_configurations_table.php` | Tablica za korisničke konfiguracije |
| `database/migrations/2025_12_10_000004_create_pc_configuration_items_table.php` | Tablica za stavke u konfiguraciji |

### 1.2 Modeli

| Datoteka | Opis |
|----------|------|
| `app/Models/PcComponentType.php` | Model za tipove komponenti |
| `app/Models/PcComponentSpec.php` | Model za specifikacije + logika kompatibilnosti |
| `app/Models/PcConfiguration.php` | Model za konfiguracije |
| `app/Models/PcConfigurationItem.php` | Model za stavke konfiguracije |

### 1.3 Controller

| Datoteka | Opis |
|----------|------|
| `app/Http/Controllers/PcBuilderController.php` | Glavni controller za PC Builder |

### 1.4 Seederi

| Datoteka | Opis |
|----------|------|
| `database/seeders/PcComponentTypeSeeder.php` | Seeder za 7 tipova komponenti |
| `database/seeders/PcComponentsSeeder.php` | Seeder za 49 PC proizvoda s specifikacijama |
| `database/seeders/PcComponentImagesSeeder.php` | Seeder za ažuriranje putanja slika |

### 1.5 Views

| Datoteka | Opis |
|----------|------|
| `resources/views/pc-builder/index.blade.php` | Glavna stranica wizarda |
| `resources/views/pc-builder/saved.blade.php` | Prikaz spremljenih konfiguracija |
| `resources/views/pc-builder/partials/component-card.blade.php` | Partial za karticu komponente (referenca) |

### 1.6 JavaScript

| Datoteka | Opis |
|----------|------|
| `public/js/pc-builder.js` | Sva interaktivnost wizarda (AJAX, filteri, navigacija) |

### 1.7 Ostalo

| Datoteka | Opis |
|----------|------|
| `storage/app/public/uploads/pc-components/SLIKE_ZA_SKINUTI.txt` | Popis slika za skidanje |

---

## 2. IZMIJENJENE DATOTEKE

### 2.1 `routes/web.php`

**Dodano:** Import PcBuilderController i PC Builder rute

```php
use App\Http\Controllers\PcBuilderController;

// ... na kraju datoteke prije auth.php ...

Route::prefix('pc-builder')->name('pc-builder.')->group(function () {
    Route::get('/', [PcBuilderController::class, 'index'])->name('index');
    Route::get('/step/{step}', [PcBuilderController::class, 'getStep'])->name('step');
    Route::post('/add-component', [PcBuilderController::class, 'addComponent'])->name('add-component');
    Route::delete('/remove-component/{typeId}', [PcBuilderController::class, 'removeComponent'])->name('remove-component');
    Route::get('/configuration', [PcBuilderController::class, 'getConfiguration'])->name('configuration');
    Route::get('/compatible-products/{typeId}', [PcBuilderController::class, 'getCompatibleProducts'])->name('compatible');
    Route::post('/add-to-cart', [PcBuilderController::class, 'addAllToCart'])->name('add-to-cart');

    Route::middleware('auth')->group(function () {
        Route::post('/save', [PcBuilderController::class, 'saveConfiguration'])->name('save');
        Route::get('/saved', [PcBuilderController::class, 'savedConfigurations'])->name('saved');
        Route::get('/load/{id}', [PcBuilderController::class, 'loadConfiguration'])->name('load');
    });
});
```

### 2.2 `app/Models/Proizvod.php`

**Dodano:** Relacija s PcComponentSpec

```php
public function pcSpec()
{
    return $this->hasOne(PcComponentSpec::class, 'proizvod_id', 'Proizvod_ID');
}
```

### 2.3 `app/Models/Kategorija.php`

**Izmijenjeno:** Fillable polje

```php
// Prije:
protected $fillable = ['Naziv'];

// Poslije:
protected $fillable = ['ImeKategorija'];
```

### 2.4 `resources/views/partials/navbar.blade.php`

**Dodano:** Link na PC Builder u navigaciji (nakon "Početna", prije "Kategorije")

```html
<li class="nav-item">
    <a class="nav-link text-white fw-semibold" href="{{ route('pc-builder.index') }}">
        <i class="bi bi-pc-display-horizontal me-1"></i> Konfiguriraj PC
    </a>
</li>
```

### 2.5 `database/seeders/DatabaseSeeder.php`

**Dodano:** Poziv PC Builder seedera

```php
$this->call([
    PcComponentTypeSeeder::class,
    PcComponentsSeeder::class,
]);
```

### 2.6 `.env`

**Izmijenjeno:** Baza podataka (zbog corrupted tablespace problema)

```
# Prije:
DB_DATABASE=web_trgovina

# Poslije:
DB_DATABASE=web_trgovina_new
```

---

## 3. BAZA PODATAKA

### 3.1 Nova baza

Zbog problema s corrupted tablespace datotekama, kreirana je nova baza:
- **Stara:** `web_trgovina` (corrupted)
- **Nova:** `web_trgovina_new` (aktivna)

### 3.2 Nove tablice

#### `pc_component_types`
```sql
CREATE TABLE pc_component_types (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    naziv VARCHAR(100) NOT NULL,
    slug VARCHAR(50) NOT NULL UNIQUE,
    redoslijed INT DEFAULT 0,
    ikona VARCHAR(50),
    obavezan TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

**Podaci (7 zapisa):**
| ID | Naziv | Slug | Redoslijed | Ikona | Obavezan |
|----|-------|------|------------|-------|----------|
| 1 | Procesor | cpu | 1 | bi-cpu | Da |
| 2 | Matična ploča | maticna-ploca | 2 | bi-motherboard | Da |
| 3 | RAM memorija | ram | 3 | bi-memory | Da |
| 4 | Grafička kartica | gpu | 4 | bi-gpu-card | Ne |
| 5 | SSD/HDD | storage | 5 | bi-device-hdd | Da |
| 6 | Napajanje | napajanje | 6 | bi-lightning | Da |
| 7 | Kućište | kuciste | 7 | bi-pc-display | Da |

#### `pc_component_specs`
```sql
CREATE TABLE pc_component_specs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    proizvod_id INT NOT NULL,
    component_type_id BIGINT UNSIGNED NOT NULL,
    socket_type VARCHAR(50),      -- AM4, AM5, LGA1700
    ram_type VARCHAR(20),         -- DDR4, DDR5
    form_factor VARCHAR(20),      -- ATX, mATX, ITX
    wattage INT,                  -- Za napajanja
    tdp INT,                      -- Za CPU/GPU
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY (proizvod_id, component_type_id),
    FOREIGN KEY (proizvod_id) REFERENCES proizvod(Proizvod_ID),
    FOREIGN KEY (component_type_id) REFERENCES pc_component_types(id)
);
```

#### `pc_configurations`
```sql
CREATE TABLE pc_configurations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    session_id VARCHAR(100) NULL,
    naziv VARCHAR(255) NULL,
    ukupna_cijena DECIMAL(10,2) DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX (session_id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

#### `pc_configuration_items`
```sql
CREATE TABLE pc_configuration_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    configuration_id BIGINT UNSIGNED NOT NULL,
    component_type_id BIGINT UNSIGNED NOT NULL,
    proizvod_id INT NOT NULL,
    cijena_u_trenutku DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY (configuration_id, component_type_id),
    FOREIGN KEY (configuration_id) REFERENCES pc_configurations(id),
    FOREIGN KEY (component_type_id) REFERENCES pc_component_types(id),
    FOREIGN KEY (proizvod_id) REFERENCES proizvod(Proizvod_ID)
);
```

### 3.3 Novi proizvodi (49 ukupno)

Dodano u tablicu `proizvod` s šiframa:

| Prefiks | Kategorija | Broj proizvoda |
|---------|------------|----------------|
| CPU-001 do CPU-009 | Procesori | 9 |
| MB-001 do MB-008 | Matične ploče | 8 |
| RAM-001 do RAM-006 | RAM memorija | 6 |
| GPU-001 do GPU-008 | Grafičke kartice | 8 |
| SSD-001 do SSD-006 | SSD/HDD | 6 |
| PSU-001 do PSU-006 | Napajanja | 6 |
| CASE-001 do CASE-006 | Kućišta | 6 |

### 3.4 Novi tipovi proizvoda

Dodano u tablicu `tip_proizvoda`:
- Procesori
- Matične ploče
- RAM memorija
- Grafičke kartice
- SSD i HDD
- Napajanja
- Kućišta

---

## 4. STRUKTURA SLIKA

```
storage/app/public/uploads/pc-components/
├── cpu/           (9 slika)
├── motherboard/   (8 slika)
├── ram/           (6 slika)
├── gpu/           (8 slika)
├── storage/       (6 slika)
├── psu/           (6 slika)
└── case/          (6 slika)
```

**Naming konvencija:** `slug-naziv-proizvoda.jpg`
- Primjer: `intel-core-i5-12400f.jpg`, `corsair-4000d-airflow.jpg`

---

## 5. LOGIKA KOMPATIBILNOSTI

### Socket kompatibilnost (CPU ↔ Matična)
| Socket | CPU | Matične ploče |
|--------|-----|---------------|
| LGA1700 | Intel 12th/13th Gen | B660M, B760 |
| AM4 | AMD Ryzen 5000 | B550M, B550 |
| AM5 | AMD Ryzen 7000 | B650M, B650 |

### RAM kompatibilnost
| RAM tip | Kompatibilne matične |
|---------|---------------------|
| DDR4 | B660M, B550M, B550 |
| DDR5 | B760, B650M, B650 |

### Form factor (Matična ↔ Kućište)
| Kućište | Prima matične |
|---------|---------------|
| ATX | ATX, mATX, ITX |
| mATX | mATX, ITX |
| ITX | samo ITX |

### Napajanje
- Preporučena snaga = (Ukupni TDP komponenti + 50W) × 1.2
- Upozorenje ako odabrano napajanje ima manju snagu

---

## 6. API RUTE

| Metoda | Ruta | Opis |
|--------|------|------|
| GET | `/pc-builder` | Glavna stranica wizarda |
| GET | `/pc-builder/step/{slug}` | Dohvat komponenti za korak |
| GET | `/pc-builder/compatible-products/{typeId}` | Filtrirane kompatibilne komponente |
| POST | `/pc-builder/add-component` | Dodaj komponentu u konfiguraciju |
| DELETE | `/pc-builder/remove-component/{typeId}` | Ukloni komponentu |
| GET | `/pc-builder/configuration` | Dohvat trenutne konfiguracije |
| POST | `/pc-builder/add-to-cart` | Dodaj sve u košaricu |
| POST | `/pc-builder/save` | Spremi konfiguraciju (auth) |
| GET | `/pc-builder/saved` | Lista spremljenih (auth) |
| GET | `/pc-builder/load/{id}` | Učitaj spremljenu (auth) |

---

## 7. NAREDBE ZA POKRETANJE

### Migracije (ako treba ponovo)
```bash
cd laravel
php artisan migrate --path=database/migrations/2025_12_10_000001_create_pc_component_types_table.php
php artisan migrate --path=database/migrations/2025_12_10_000002_create_pc_component_specs_table.php
php artisan migrate --path=database/migrations/2025_12_10_000003_create_pc_configurations_table.php
php artisan migrate --path=database/migrations/2025_12_10_000004_create_pc_configuration_items_table.php
```

### Seederi
```bash
php artisan db:seed --class=PcComponentTypeSeeder
php artisan db:seed --class=PcComponentsSeeder
php artisan db:seed --class=PcComponentImagesSeeder
```

### Storage link (ako treba ponovo)
```bash
php artisan storage:link
```

### Server
```bash
php artisan serve
```

---

## 8. PRISTUP FUNKCIONALNOSTIMA

- **PC Builder:** http://127.0.0.1:8000/pc-builder
- **Spremljene konfiguracije:** http://127.0.0.1:8000/pc-builder/saved (potrebna prijava)

---

## 9. NAPOMENE

1. **Baza podataka** je preimenovana u `web_trgovina_new` zbog corrupted tablespace problema u originalnoj bazi.

2. **Slike** moraju biti u JPG formatu s točnim imenima (slug format).

3. **Kompatibilnost** se automatski provjerava pri odabiru komponenti.

4. **Konfiguracije** se spremaju:
   - Za prijavljene korisnike: u bazu (pc_configurations)
   - Za goste: u session

5. **Košarica** integracija radi s postojećim sustavom (session za goste, baza za prijavljene).

---

## 10. PITANJA O IMPLEMENTACIJI

Za pitanja o implementaciji, pogledaj:
- Controller: `app/Http/Controllers/PcBuilderController.php`
- Modeli: `app/Models/PcComponent*.php`
- JavaScript: `public/js/pc-builder.js`
