<?php

namespace App\Http\Controllers;

use App\Actions\MergeGuestCartAction;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request, MergeGuestCartAction $mergeCartAction): RedirectResponse
    {
        $guestSessionId = Session::getId();

        $request->authenticate();
        $request->session()->regenerate();

        $user = Auth::user();

        if ($user)
        {
            $mergeCartAction->execute($user, $guestSessionId);
        }

        if ($user && $user->hasRole('admin'))
        {
            return redirect()->intended('/admin');
        }

        if ($user && $user->hasRole('seller') && $user->seller?->isApproved())
        {
            return redirect()->intended('/satici');
        }

        return redirect()->intended('/');
    }

    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(RegisterRequest $request, MergeGuestCartAction $mergeCartAction): RedirectResponse
    {
        $guestSessionId = Session::getId();

        $user = User::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'phone' => $request->validated('phone'),
            'password' => Hash::make($request->validated('password')),
            'email_verified_at' => now(),
        ]);

        $customerRole = Role::firstOrCreate(['name' => 'customer']);
        $user->assignRole($customerRole);

        event(new Registered($user));

        Auth::login($user);
        $mergeCartAction->execute($user, $guestSessionId);

        return redirect('/hesabim')->with('success', 'LaraMart hesabınız başarıyla oluşturuldu!');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}

