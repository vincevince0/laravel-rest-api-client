<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;


class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
{
    $response = Http::api()
        ->acceptJson()
        ->post('/users/login', [
            'email' => $request->email,
            'password' => $request->password,
        ]);

    if (!$response->successful()) {
        return back()->withErrors([
            'email' => 'Hibás bejelentkezési adatok.',
        ]);
    }

    $userData = $response['user'];
    $token = $response['token'];

    // Store API token in session
    session([
        'api_token' => $token,
        'user_name' => $userData['name'],
        'user_email' => $userData['email'],
    ]);

    // Optional: also log in Laravel web guard so Auth::user() works
    $user = \App\Models\User::firstOrCreate(
        ['email' => $userData['email']],
        ['name' => $userData['name'], 'password' => bcrypt('temporary')]
    );
    Auth::login($user);

    return redirect()->intended('/dashboard');
}

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
{
    $token = session('api_token');

    // Revoke token in API if exists
    if ($token) {
        Http::api()
            ->withToken($token)
            ->delete('/user/logout'); // API route to revoke Sanctum token
    }

    // Logout Laravel auth guard
    Auth::guard('web')->logout();

    // Clear session variables
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/login');
}

}
