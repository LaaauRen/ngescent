<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin(Request $request)
    {
        if (Auth::check()) return redirect($this->home(Auth::user()));

        return view('auth.login', [
            'mode'         => $request->query('mode') === 'register' ? 'register' : 'login',
            'fromCheckout' => $request->query('from') === 'checkout',
        ]);
    }

    /** Masuk dengan email ATAU username + password. */
    public function login(Request $request)
    {
        $data = $request->validate([
            'login'    => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $field = filter_var($data['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if (!Auth::attempt([$field => $data['login'], 'password' => $data['password'], 'is_active' => true], $request->boolean('remember'))) {
            return back()
                ->withErrors(['login' => "Email/Username atau Password salah! Jika belum punya akun, klik tab 'Daftar Akun Baru'."])
                ->onlyInput('login');
        }

        $request->session()->regenerate();
        return redirect()->intended($this->home(Auth::user()));
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:40', 'alpha_dash', 'unique:users,username'],
            'email'    => ['required', 'email', 'max:150', 'unique:users,email'],
            'phone'    => ['required', 'regex:/^(\+?62|0)?\d{8,13}$/'],
            'password' => ['required', 'string', 'min:6'],
        ], [
            'username.unique' => 'Username sudah terdaftar.',
            'email.unique'    => 'Email sudah terdaftar. Silakan langsung masuk di tab "Masuk".',
            'phone.regex'     => 'Nomor WhatsApp tidak valid.',
        ]);

        // role TIDAK dari input -> selalu customer
        $user = User::create($data + ['role' => 'customer']);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'))->with('success', "Pendaftaran berhasil! Selamat datang, {$user->name}.");
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/')->with('success', 'Anda telah keluar dari akun.');
    }

    private function home(User $user): string
    {
        return $user->isAdmin() ? route('admin.index') : route('dashboard');
    }
}
