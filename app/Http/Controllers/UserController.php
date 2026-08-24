<?php

namespace App\Http\Controllers;

use App\Services\Billing\PlanLimitService;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;
use RealRashid\SweetAlert\Facades\Alert;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    /** Roles que se pueden asignar desde el ABM de usuarios (sin superadmin). */
    private const ASSIGNABLE_ROLES = ['admin', 'operator'];

    private const PROTECTED_ROLE = 'superadmin';

    private const PASSWORD_RULES = ['string', 'min:8', 'max:72'];

    public function index()
    {
        $x['title']     = 'User';
        $x['data']      = User::get();
        $x['role']      = Role::whereIn('name', self::ASSIGNABLE_ROLES)->orderBy('name')->get();
        return view('admin.user', $x);
    }

    private function roleValidationRule(): array
    {
        return ['required', 'string', 'in:'.implode(',', self::ASSIGNABLE_ROLES)];
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

    public function store(Request $request)
    {
        if (! app(PlanLimitService::class)->canCreateUser()) {
            Alert::error('Plan', app(PlanLimitService::class)->userLimitMessage())->toToast();
            return back()->withInput();
        }
        $validator = Validator::make($request->all(), [
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password'  => array_merge(['required'], self::PASSWORD_RULES),
            'role'      => $this->roleValidationRule(),
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)
                ->withInput();
        }
        DB::beginTransaction();
        try {
            $user = User::create([
                'name'      => $request->name,
                'email'     => $request->email,
                'password'  => Hash::make($request->password)
            ]);
            $user->assignRole($request->role);
            DB::commit();
            Alert::success('Listo', 'Usuario '.$user->name.' creado.')->toToast();
        } catch (\Throwable $th) {
            DB::rollback();
            report($th);
            Alert::error('No se pudo crear el usuario', 'Revisá los datos e intentá de nuevo.')->toToast();
        }
        return back();
    }

    public function show(Request $request)
    {
        $user = UserResource::collection(User::where(['id' => $request->id])->get());
        return response()->json([
            'status'    => Response::HTTP_OK,
            'message'   => 'Data user by id',
            'data'      => $user[0]
        ], Response::HTTP_OK);
    }

    public function update(Request $request)
    {
        $rules = [
            'name'      => ['required', 'string', 'max:255'],
            'password'  => array_merge(['nullable'], self::PASSWORD_RULES),
            'role'      => $this->roleValidationRule(),
        ];

        if ($request->email != $request->old_email) {
            $rules['email'] = ['required', 'string', 'email', 'max:255', 'unique:users'];
            $validator = Validator::make($request->all(), $rules);
        } else {
            $rules['email'] = ['required', 'string', 'email', 'max:255'];
            $validator = Validator::make($request->all(), $rules);
        }

        if ($validator->fails()) {
            return back()->withErrors($validator)
                ->withInput();
        }
        $data = [
            'name'      => $request->name,
            'email'     => $request->email,
        ];
        if (!empty($request->password)) {
            $data['password']   = Hash::make($request->password);
        }

        $user = User::find($request->id);
        $this->denyProtectedUser($user);

        DB::beginTransaction();
        try {
            $user->update($data);
            $user->syncRoles($request->role);
            DB::commit();
            Alert::success('Listo', 'Usuario '.$user->name.' guardado.')->toToast();
        } catch (\Throwable $th) {
            DB::rollback();
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
            $user->delete();
            Alert::success('Listo', 'Usuario '.$user->name.' eliminado.')->toToast();
        } catch (\Throwable $th) {
            report($th);
            Alert::error('No se pudo eliminar el usuario', 'Intentá de nuevo.')->toToast();
        }
        return back();
    }
}
