<?php

namespace App\Http\Controllers\Toko;

use App\Support\GambarOg;
use App\Support\Seo;
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
            'seo' => [
                'gambar' => GambarOg::toko($produk),
                'jsonld' => [Seo::jsonldToko()],
            ],
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
            ->when($filter['q'] ?? null, fn (Builder $q, string $s) => Product::cariNama($q, $s));

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
            'seo' => [
                // Urutan & halaman tidak membuat halaman baru di mata Google; kategori iya.
                'kanonik' => route('produk.index', array_filter([
                    'kategori' => $filter['kategori'] ?? null,
                    'page' => $produk->currentPage() > 1 ? $produk->currentPage() : null,
                ])),
                'noindex' => filled($filter['q'] ?? null) || $produk->isEmpty(),
                'gambar' => GambarOg::toko($produk->getCollection()),
                'deskripsi' => isset($filter['kategori'])
                    ? __(':category from :store. Prices in rupiah, US dollars or Taiwan dollars.', ['category' => __($filter['kategori']), 'store' => config('toko.nama')])
                    : null,
            ],
        ]);
    }

    public function show(Request $request, Product $product): View|\Illuminate\Http\RedirectResponse
    {
        abort_unless($product->is_active, 404);

        // Alamat lama (UUID) -> alamat kanonik berbasis slug.
        if ($request->route()->originalParameter('product') !== $product->slug) {
            return redirect()->route('produk.show', $product, 301);
        }
        $product->load(['images', 'variants.stocks']);

        $detail = TampilanProduk::detail($product);

        return view('toko.produk.show', [
            'p' => $detail,
            'seo' => [
                'tipe' => 'product',
                'gambar' => GambarOg::produk($product),
                'jsonld' => [Seo::jsonldProduk($product, $detail, $product->images->map->url()->all())],
            ],
            'terkait' => $this->query()
                ->where('id', '!=', $product->id)
                ->when($product->kategori, fn (Builder $q, string $k) => $q->where('kategori', $k))
                ->take(4)->get()
                ->map(fn (Product $x) => TampilanProduk::kartu($x)),
        ]);
    }

    public function kebijakan(\App\Models\HalamanKebijakan $halaman): View
    {
        abort_unless($halaman->is_active, 404);

        return view('toko.kebijakan', [
            'h' => $halaman,
            'lain' => \App\Models\HalamanKebijakan::tautan(),
        ]);
    }

    public function faq(): View
    {
        return view('toko.faq', [
            'faq' => Faq::query()->where('is_active', true)->orderBy('urutan')->get(),
        ]);
    }
}
