<?php

namespace App\Http\Controllers\Toko;

use App\Http\Controllers\Controller;
use App\Support\TampilanOrder;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class AkunController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        return view('toko.akun.index', [
            'user' => $user,
            'pesanan' => $user->orders()->latest()->take(5)->get()->map(fn ($o) => TampilanOrder::ringkas($o)),
            'alamat' => $user->addresses()->orderByDesc('is_default')->oldest()->get(),
            'punyaSosial' => $user->socialAccounts()->pluck('provider')->all(),
        ]);
    }

    public function updateProfil(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:120'],
            'bahasa_preferensi' => ['required', Rule::in(array_keys(config('toko.locales')))],
            'mata_uang_preferensi' => ['required', Rule::in(config('toko.currencies'))],
        ]);

        $request->user()->update($data);
        $request->session()->put(['locale' => $data['bahasa_preferensi'], 'currency' => $data['mata_uang_preferensi']]);

        return back()->with('status', __('Profile saved.'));
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $data = $request->validateWithBag('password', [
            'password_lama' => ['required', 'current_password:web'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $request->user()->update(['password' => Hash::make($data['password'])]);

        return back()->with('status', __('Password changed.'));
    }
}
