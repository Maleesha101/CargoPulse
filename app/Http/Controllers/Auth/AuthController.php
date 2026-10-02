<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = \App\Models\User::where('email', $request->email)->first();

        if ($user && password_verify($request->password, $user->password_hash)) {
            Session::put('user', [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'department' => $user->department,
            ]);

            \App\Models\AuditLog::create([
                'user_id' => $user->id,
                'action' => 'login',
                'resource' => 'auth',
                'metadata' => ['ip' => $request->ip()],
            ]);

            if ($user->role === 'customer') {
                return redirect('/portal/dashboard');
            }

            return redirect('/ops/dashboard');
        }

        return back()->with('error', 'Invalid email or password.');
    }

    public function logout()
    {
        $user = Session::get('user');
        if ($user) {
            \App\Models\AuditLog::create([
                'user_id' => $user['id'],
                'action' => 'logout',
                'resource' => 'auth',
                'metadata' => [],
            ]);
        }

        Session::forget('user');
        return redirect('/login')->with('success', 'You have been logged out.');
    }
}