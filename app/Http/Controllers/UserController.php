<?php

namespace App\Http\Controllers;

use App\Actions\CreateUser;
use App\Actions\UpdateUser;
use App\Exceptions\BusinessRuleException;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    private const PROTECTED_ROLE = 'superadmin';

    public function index()
    {
        $x['title'] = 'User';
        $x['data'] = User::get();
        $x['role'] = Role::whereIn('name', StoreUserRequest::ASSIGNABLE_ROLES)->orderBy('name')->get();

        return view('admin.user', $x);
    }

    public function store(StoreUserRequest $request, CreateUser $createUser)
    {
        try {
            $user = $createUser->execute($request->validated());
            Alert::success('Listo', 'Usuario '.$user->name.' creado.')->toToast();
        } catch (BusinessRuleException $e) {
            Alert::error('Plan', $e->getMessage())->toToast();

            return back()->withInput();
        } catch (\Throwable $th) {
            report($th);
            Alert::error('No se pudo crear el usuario', 'Revisá los datos e intentá de nuevo.')->toToast();
        }

        return back();
    }

    public function show(Request $request)
    {
        $user = UserResource::collection(User::where(['id' => $request->id])->get());

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Data user by id',
            'data' => $user[0],
        ], Response::HTTP_OK);
    }

    public function update(UpdateUserRequest $request, UpdateUser $updateUser)
    {
        $user = User::find($request->validated('id'));
        $this->denyProtectedUser($user);

        try {
            $user = $updateUser->execute($user, $request->validated());
            Alert::success('Listo', 'Usuario '.$user->name.' guardado.')->toToast();
        } catch (\Throwable $th) {
            report($th);
            Alert::error('No se pudo guardar el usuario', 'Revisá los datos e intentá de nuevo.')->toToast();
        }

        return back();
    }

    public function destroy(Request $request)
    {
        $user = User::find($request->id);
        $this->denyProtectedUser($user);

        try {
            $name = $user->name;
            $user->delete();
            Alert::success('Listo', 'Usuario '.$name.' eliminado.')->toToast();
        } catch (\Throwable $th) {
            report($th);
            Alert::error('No se pudo eliminar el usuario', 'Intentá de nuevo.')->toToast();
        }

        return back();
    }

    private function denyProtectedUser(?User $user): void
    {
        if (! $user) {
            abort(404);
        }

        if ($user->hasRole(self::PROTECTED_ROLE)) {
            abort(403, 'No se puede modificar el superadmin.');
        }
    }
}
