<?php

namespace App\Http\Controllers\Toko;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\Product;
use App\Support\TampilanProduk;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class KatalogController extends Controller
{
    private function query(): Builder
    {
        return Product::query()
            ->where('is_active', true)
            ->whereHas('variants')
            ->with(['images', 'variants.stocks'])
            ->withMin('variants', 'harga_idr');
    }

    private function kategori(): array
    {
        return Product::query()->where('is_active', true)->whereNotNull('kategori')
            ->distinct()->orderBy('kategori')->pluck('kategori')->all();
    }

    public function home(): View
    {
        $produk = $this->query()->latest()->take(8)->get();

        return view('toko.home', [
            'produk' => $produk->map(fn (Product $p) => TampilanProduk::kartu($p)),
            'mozaik' => $produk->take(6)->map(fn (Product $p) => TampilanProduk::kartu($p)),
            'kategori' => $this->kategori(),
        ]);
    }

    public function index(Request $request): View
    {
        $filter = $request->validate([
            'kategori' => ['nullable', 'string', 'max:100'],
            'q' => ['nullable', 'string', 'max:100'],
            'urut' => ['nullable', 'in:terbaru,termurah,termahal'],
        ]);

        $query = $this->query()
            ->when($filter['kategori'] ?? null, fn (Builder $q, string $k) => $q->where('kategori', $k))
            ->when($filter['q'] ?? null, fn (Builder $q, string $s) => $q->where('nama_terjemahan', 'like', '%'.addcslashes($s, '%_\\').'%'));

        match ($filter['urut'] ?? 'terbaru') {
            'termurah' => $query->orderBy('variants_min_harga_idr'),
            'termahal' => $query->orderByDesc('variants_min_harga_idr'),
            default => $query->latest(),
        };

        $produk = $query->paginate(12)->withQueryString();

        return view('toko.produk.index', [
            'produk' => $produk,
            'kartu' => $produk->getCollection()->map(fn (Product $p) => TampilanProduk::kartu($p)),
            'kategori' => $this->kategori(),
            'filter' => $filter + ['urut' => 'terbaru'],
        ]);
    }

    public function show(Product $product): View
    {
        abort_unless($product->is_active, 404);
        $product->load(['images', 'variants.stocks']);

        return view('toko.produk.show', [
            'p' => TampilanProduk::detail($product),
            'terkait' => $this->query()
                ->where('id', '!=', $product->id)
                ->when($product->kategori, fn (Builder $q, string $k) => $q->where('kategori', $k))
                ->take(4)->get()
                ->map(fn (Product $x) => TampilanProduk::kartu($x)),
        ]);
    }

    public function faq(): View
    {
        return view('toko.faq', [
            'faq' => Faq::query()->where('is_active', true)->orderBy('urutan')->get(),
        ]);
    }
}
