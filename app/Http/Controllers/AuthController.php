<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\UserActivity;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login (Request $request) {
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password, 'activate' => 1])) {
            if (Auth::user()){
                // Log the activity
                UserActivity::create([
                    'user_id' => Auth::user()->id,
                    'action' => 'login',
                    'description' => 'Connexion réussie',
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);

                smilify('success', 'BIENVENUE ! Vous etes bien connecté !');
                return redirect()->route('dashboard');
            }
             else {
                emotify('error', 'Login ou mot de passe incorrect !');
                return redirect()->route('login')->with('status', 'Login ou mot de passe incorrect !');
            }
        }

        emotify('error', 'Login ou mot de passe incorrect !');
        return redirect()->route('login')->with('status', 'Login ou mot de passe incorrect !');
    }

    public function logout () {
        $userId = Auth::user()?->id;
        
        if ($userId) {
            // Log the activity
            UserActivity::create([
                'user_id' => $userId,
                'action' => 'logout',
                'description' => 'Déconnexion',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        }

        Auth::logout();
        return redirect()->route('login');
    }
}
