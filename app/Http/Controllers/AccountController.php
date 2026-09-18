<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AccountController extends Controller
{
    /**
     * Display all staff accounts.
     */
    public function index()
    {
        $users = User::with('roles')
            ->orderBy('name')
            ->get();

        return view('accounts.index', compact('users'));
    }

    /**
     * Show create account form.
     */
    public function create()
    {
        $user = auth()->user();

        $roles = [
            'designer',
            'cameraman',
        ];

        // Only Owner can create Admin accounts.
        if ($user->hasRole('owner')) {
            array_unshift($roles, 'admin');
        }

        return view('accounts.create', compact('roles'));
    }

    /**
     * Store a new staff account.
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        $allowedRoles = [
            'designer',
            'cameraman',
        ];

        // Only Owner can create Admin accounts.
        if ($user->hasRole('owner')) {
            $allowedRoles[] = 'admin';
        }

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                'unique:users,email',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:30',
            ],

            'role' => [
                'required',
                Rule::in($allowedRoles),
            ],

            'password' => [
                'required',
                'confirmed',
                'min:8',
            ],
        ]);

        $newUser = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
        ]);

        $newUser->assignRole($validated['role']);

        return redirect()
            ->route('accounts.index')
            ->with('success', 'Account created successfully.');
    }
}