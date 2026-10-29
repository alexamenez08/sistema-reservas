<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Throwable;
use App\Http\Requests\UserRequest;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        //
        $usuarios = User::with('roles')->latest()->paginate(10);
        return view('usuarios.index', compact('usuarios'));
    }
 
    /**
     * Show the form for creating a new resource.
     */
    public function create() : View
    {
        //
        $roles = Role::all();
        return view('usuarios.create', compact('roles'));
 
    }
 
    /**
     * Store a newly created resource in storage.
     */
    public function store(UserRequest $request): RedirectResponse
    {
        //
        $usuario = User::create([
            'name' => $request->name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make(Str::random(12)),
            'is_active' => true,
        ]);
 
        $usuario->assignRole($request->rol);
 
        // La contraseña inicial es aleatoria y nadie la conoce: se envía un correo con un enlace
        // (el mismo de "Olvidé mi contraseña", con vigencia limitada) para que el usuario defina la suya.
        $mensaje = 'Usuario creado correctamente.';
        try {
            $estado = Password::sendResetLink(['email' => $usuario->email]);
 
            $mensaje .= $estado === Password::RESET_LINK_SENT
                ? ' Se envió un correo a ' . $usuario->email . ' para que establezca su contraseña.'
                : ' No se pudo enviar el correo de acceso; edita el usuario para asignarle una contraseña.';
        } catch (Throwable $e) {
            Log::warning('No se pudo enviar el correo de acceso: ' . $e->getMessage());
            $mensaje .= ' No se pudo enviar el correo (revisa la configuración de correo); edita el usuario para asignarle una contraseña.';
        }
 
        return redirect()->route('usuarios.index')
            ->with('success', $mensaje);
    }
 
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }
 
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $usuario): View
    {
        //
        $roles = Role::all();
        return view('usuarios.edit', compact('usuario', 'roles'));
    }
 
    /**
     * Update the specified resource in storage.
     */
    public function update(UserRequest $request, User $usuario): RedirectResponse
    {
        //
        $usuario->update([
            'name' => $request->name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'is_active' => $request->boolean('is_active'),
        ]);
 
        if ($request->filled('password')) {
            $usuario->update(['password' => Hash::make($request->password)]);
        }
 
        $usuario->syncRoles($request->rol);
 
        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario actualizado correctamente.');
    }
 
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $usuario): RedirectResponse
    {
        //
        $usuario->update(['is_active' => false]);
        $usuario->delete();
 
        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario eliminado correctamente.');
    }
}
