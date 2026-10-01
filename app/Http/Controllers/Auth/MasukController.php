<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Oauth\ProviderSosial;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

/** Daftar, masuk, dan keluar untuk pembeli (guard `web`). */
class MasukController extends Controller
{
    public function formMasuk(): View
    {
        return view('toko.auth.masuk', ['sosial' => ProviderSosial::aktif()]);
    }

    public function masuk(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $kunci = 'masuk:'.Str::lower($data['email']).'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($kunci, 5)) {
            throw ValidationException::withMessages([
                'email' => __('Too many attempts. Try again in :seconds seconds.', ['seconds' => RateLimiter::availableIn($kunci)]),
            ]);
        }

        if (! Auth::guard('web')->attempt($data, $request->boolean('ingat'))) {
            RateLimiter::hit($kunci, 60);
            throw ValidationException::withMessages(['email' => __('Email or password is incorrect.')]);
        }

        RateLimiter::clear($kunci);
        $request->session()->regenerate();

        return redirect()->intended(route('akun'));
    }

    public function formDaftar(): View
    {
        return view('toko.auth.daftar', ['sosial' => ProviderSosial::aktif()]);
    }

    public function daftar(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email', 'max:190', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::create($data + [
            'bahasa_preferensi' => app()->getLocale(),
            'mata_uang_preferensi' => app('toko.currency'),
        ]);

        event(new Registered($user));
        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('akun'));
    }

    public function keluar(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
