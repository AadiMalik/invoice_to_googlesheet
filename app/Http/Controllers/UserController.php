<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::all();
        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        ini_set('max_execution_time', 0);
        $request->validate([
            'name'             => 'required',
            'email'            => [
                'required',
                'string',
                'max:190',
                Rule::unique('users')->ignore($request->id)
            ],
            'phone'         => [
                'required',
                'string',
                'max:190',
                Rule::unique('users')->ignore($request->id)
            ],
            'role'          => 'required',
            'password'          => 'required'
        ]);
        if ($request->id != '' && $request->id != null) {
            $obj = [
                "name" => $request->name ?? '',
                "email" => $request->email,
                "phone" => $request->phone,
                "role" => $request->role,
                "password" => Hash::make($request->password)
            ];
            // Find the record first
            $user = User::find($request->id);

            if ($user) {
                $user->update($obj);
                return redirect('user')->with('success', 'user updated successfully!');
            }

            return redirect()->back()->with('error', 'Something went wrong!');
        } else {
            $obj = [
                "name" => $request->name ?? '',
                "email" => $request->email,
                "phone" => $request->phone,
                "role" => $request->role,
                "password" => Hash::make($request->password)
            ];
            $user = User::create($obj);


            return redirect('user')->with('success', 'User created successfully!');
        }
        return redirect()->back()->with('error', 'Something went wrong!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $user = User::find($id);
        return view('users.create', compact('user'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $user = User::find($id);
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'user deleted successfully!'
        ]);
    }

    public function changePassword()
    {
        return view('users.change_password');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'password'             => 'required|string|min:8|confirmed',
        ]);

        // Find the record first
        $user = User::find(Auth::user()->id);
        $user->password = Hash::make($request->password);
        $user->update();
        if ($user) {
            return redirect()->back()->with('success', 'Password updated successfully!');
        }

        return redirect()->back()->with('error', 'Something went wrong!');
    }
}
