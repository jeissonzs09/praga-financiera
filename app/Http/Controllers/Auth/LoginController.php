<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
{
    if (auth()->check()) {
        return redirect()->route('dashboard'); // o la ruta que uses
    }

    return view('auth.login'); // tu vista personalizada
}


    public function login(Request $request)
{

    if (Auth::attempt(['username' => $request->username, 'password' => $request->password])) {
        return redirect()->intended('dashboard');
    }

    return back()->withErrors([
        'username' => 'Credenciales incorrectas.',
    ]);
}

    public function logout(Request $request)
    {
        Auth::logout();
        return redirect()->route('login');
    }

    public function username()
{
    return 'username';
}



}

