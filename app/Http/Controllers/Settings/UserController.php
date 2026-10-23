<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        return view('settings.users.index', ['users' => User::latest()->paginate(20)]);
    }

    public function create(): View
    {
        return view('settings.users.create', ['roles' => UserRole::cases()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $valid = $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'email' => ['required', 'email', 'max:180', 'unique:users,email'],
            'role' => ['required', Rule::enum(UserRole::class)],
            'password' => ['required', 'confirmed', Password::min(8)],
            'is_active' => ['boolean'],
        ]);

        $user = User::create([
            'name' => $valid['name'],
            'email' => $valid['email'],
            'role' => $valid['role'],
            'password' => Hash::make($valid['password']),
            'is_active' => $request->boolean('is_active', true),
        ]);
        AuditLogger::record('user.created', $user);

        return redirect()->route('users.index')->with('success', 'Usuário criado.');
    }

    public function edit(User $user): View
    {
        return view('settings.users.edit', ['user' => $user, 'roles' => UserRole::cases()]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $valid = $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'email' => ['required', 'email', 'max:180', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::enum(UserRole::class)],
            'password' => ['nullable', 'confirmed', Password::min(8)],
            'is_active' => ['boolean'],
        ]);

        if (! empty($valid['password'])) {
            $valid['password'] = Hash::make($valid['password']);
        } else {
            unset($valid['password']);
        }
        $valid['is_active'] = $request->boolean('is_active');

        $user->update($valid);
        AuditLogger::record('user.updated', $user);

        return back()->with('success', 'Usuário atualizado.');
    }

    public function toggleActive(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('warning', 'Você não pode desativar a própria conta.');
        }

        $user->update(['is_active' => ! $user->is_active]);
        AuditLogger::record('user.toggled', $user, ['is_active' => $user->is_active]);

        return back()->with('success', 'Status do usuário atualizado.');
    }
}
