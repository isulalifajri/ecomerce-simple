<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index()
    {
        $User = User::all();
        return view('User.index', compact('User'));
    }

    public function create()
    {
        return view('User.create');
    }

    public function store(Request $request)
    {

        // VALIDASI
        $request->validate([
            'name' => 'required',
        ]);

        DB::beginTransaction();

        try {

            $User = User::create([
                'kode' => $kode,
                'name' => $request->name,
            ]);

            DB::commit();

            return redirect()->route('User.index')
                ->with('success', 'Kategory berhasil dibuat');

        } catch (\Exception $e) {

            DB::rollBack();

            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(User $User)
    {
        $User->load('items');
        return view('User.show',compact('User'));
    }

    public function edit(User $User)
    {
        return view('User.edit', [
            'title' => 'Page Edit User',
            'User' => $User,
        ]);
    }

    public function update(Request $request, User $User)
    {

        // VALIDASI
        $request->validate([
            'name' => 'string|required',
        ]);

        DB::beginTransaction();

        try {

            $User->update([
                'name' => $request->name,
            ]);

            DB::commit();

            return redirect()->route('User.index')
                ->with('success', 'User berhasil diupdate');

        } catch (\Exception $e) {

            DB::rollBack();

            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function delete(User $User)
    {
        $User->delete();

        return back()->with('success', 'User Berhasil dihapus');
    }
}
