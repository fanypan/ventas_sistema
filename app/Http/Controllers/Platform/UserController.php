<?php

namespace App\Http\Controllers\Platform;

use App\Actions\Platform\CreatePlatformUser;
use App\Actions\Platform\DeletePlatformUser;
use App\Actions\Platform\UpdatePlatformUser;
use App\Exceptions\BusinessRuleException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\StorePlatformUserRequest;
use App\Http\Requests\Platform\UpdatePlatformUserRequest;
use App\Models\PlatformUser;
use App\Support\PlatformAccess;
use RealRashid\SweetAlert\Facades\Alert;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        $users = PlatformUser::with('roles')->orderBy('name')->get();

        return view('platform.users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::where('guard_name', PlatformAccess::GUARD)->orderBy('name')->get();

        return view('platform.users.create', compact('roles'));
    }

    public function store(StorePlatformUserRequest $request, CreatePlatformUser $createUser)
    {
        $createUser->execute($request->validated());

        Alert::success('Usuario creado', 'Ya puede entrar al panel.')->toToast();

        return redirect()->route('platform.users.index');
    }

    public function edit(PlatformUser $platformUser)
    {
        $roles = Role::where('guard_name', PlatformAccess::GUARD)->orderBy('name')->get();

        return view('platform.users.edit', [
            'user' => $platformUser,
            'roles' => $roles,
        ]);
    }

    public function update(UpdatePlatformUserRequest $request, PlatformUser $platformUser, UpdatePlatformUser $updateUser)
    {
        try {
            $updateUser->execute($platformUser, $request->validated(), $request->user('platform'));
        } catch (BusinessRuleException $e) {
            Alert::error('No se pudo guardar', $e->getMessage())->toToast();

            return back()->withInput();
        }

        Alert::success('Usuario guardado', $platformUser->name)->toToast();

        return redirect()->route('platform.users.index');
    }

    public function destroy(PlatformUser $platformUser, DeletePlatformUser $deleteUser)
    {
        try {
            $deleteUser->execute($platformUser, request()->user('platform'));
        } catch (BusinessRuleException $e) {
            Alert::error('No se pudo eliminar', $e->getMessage())->toToast();

            return back();
        }

        Alert::success('Eliminado', 'Se sacó a '.$platformUser->name.' del equipo.')->toToast();

        return redirect()->route('platform.users.index');
    }
}
