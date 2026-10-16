<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $collection = User::all();

        return view('pages.users.index',compact('collection'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        User::create([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'email' => $request->email,
            'role' => $request->role,
            'activate' => $request->activate,
            'module' => $request->module,
            'password' => Hash::make('password'),
        ]);

        smilify('success', 'Utilisateur a été ajouté avec success !');
        return redirect()->back();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $finds = User::find($id);
        return view('pages.users.profil', compact('finds'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $finds = User::find($id);
        
        if (!$finds) {
            smilify('error', 'Utilisateur non trouvé !');
            return redirect()->back();
        }

        $finds->update([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'email' => $request->email,
        ]);

        // Log the activity
        UserActivity::create([
            'user_id' => $id,
            'action' => 'update_profile',
            'description' => 'Profil mis à jour',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        smilify('success', 'Profil mis à jour avec succès !');
        return redirect()->back();
    }

    public function change_password(Request $request, string $id)
    {
        $finds = User::find($id);
        $finds->update([
            'password' => Hash::make($request->password),
        ]);

        // Log the activity
        UserActivity::create([
            'user_id' => $id,
            'action' => 'change_password',
            'description' => 'Mot de passe modifié',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        smilify('success', 'Mot de passe change avec success !');
        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // Check if user is admin
        $currentUser = auth()->user();
        if ($currentUser->role !== 'admin') {
            smilify('error', 'Vous n\'avez pas la permission de supprimer un utilisateur !');
            return redirect()->back();
        }

        $use = User::find($id);
        
        if ($use) {
            // Log the activity
            UserActivity::create([
                'user_id' => $id,
                'action' => 'account_deleted',
                'description' => 'Compte supprimé par un administrateur',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            $use->delete();
            smilify('error', 'L\'utilisateur a été supprimer avec success !');
        }

        return redirect()->route('module_utilisateur.index');
    }

    /**
     * Get user activity history
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getActivityHistory($id)
    {
        $finds = User::find($id);
        
        if (!$finds) {
            return response()->json(['error' => 'Utilisateur non trouvé'], 404);
        }

        $activities = UserActivity::where('user_id', $id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($activities);
    }
}
