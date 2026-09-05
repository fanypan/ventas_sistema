<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSettingRequest;
use App\Http\Requests\UpdateSettingsRequest;
use App\Http\Resources\SettingResource;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RealRashid\SweetAlert\Facades\Alert;

class SettingController extends Controller
{
    public function index(): View
    {
        $settings = Setting::query()->orderBy('id')->get();
        $grouped = $settings->groupBy(fn (Setting $setting) => $setting->category ?: 'information');
        $known = collect(Setting::CATEGORY_ORDER)->filter(fn (string $category) => $grouped->has($category));
        $categories = $known->concat($grouped->keys()->diff($known))->values();

        return view('admin.setting', [
            'title' => 'Configuración',
            'categories' => $categories,
            'grouped' => $grouped,
            'canUpdate' => auth()->user()?->can('update setting') ?? false,
        ]);
    }

    public function store(StoreSettingRequest $request): RedirectResponse
    {
        try {
            Setting::create($request->validated());
            Alert::success('Listo', 'Ajuste creado.')->toToast();
        } catch (\Throwable $th) {
            report($th);
            Alert::error('No se pudo crear el ajuste', 'Revisá los datos e intentá de nuevo.')->toToast();
        }

        return back();
    }

    public function show(Request $request): SettingResource
    {
        $setting = Setting::findOrFail($request->id);

        return new SettingResource($setting);
    }

    public function update(UpdateSettingsRequest $request): RedirectResponse
    {
        $tab = (string) $request->validated('tab', '');

        try {
            $keys = $request->validated('key');
            $values = $request->validated('value');
            for ($i = 0; $i < count($keys); $i++) {
                Setting::where(['key' => $keys[$i]])->update(['value' => $values[$i]]);
            }
            Alert::success('Listo', 'Configuración guardada.')->toToast();
        } catch (\Throwable $th) {
            report($th);
            Alert::error('No se pudieron guardar los ajustes', 'Intentá de nuevo.')->toToast();
        }

        $fragment = $tab !== '' ? 'settings-'.$tab : '';

        return redirect()->route('setting.index')->withFragment($fragment);
    }

    public function destroy(Request $request): RedirectResponse
    {
        try {
            $setting = Setting::find($request->id);
            $name = $setting->name;
            $setting->delete();
            Alert::success('Listo', 'El ajuste '.$name.' fue eliminado.')->toToast();
        } catch (\Throwable $th) {
            report($th);
            Alert::error('No se pudo eliminar el ajuste', 'Intentá de nuevo.')->toToast();
        }

        return back();
    }
}
