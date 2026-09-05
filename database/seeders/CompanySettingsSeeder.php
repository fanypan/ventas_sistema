<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class CompanySettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $settings = [
            [
                'key' => 'company_name',
                'name' => 'Nombre de la Empresa',
                'value' => 'Sistema de Ventas Laravel',
                'type' => 'text',
                'category' => 'company',
                'ext' => null,
            ],
            [
                'key' => 'company_nit',
                'name' => 'NIT / RUC',
                'value' => '1234567-8',
                'type' => 'text',
                'category' => 'company',
                'ext' => null,
            ],
            [
                'key' => 'company_phone',
                'name' => 'Teléfono',
                'value' => '021 123 456',
                'type' => 'text',
                'category' => 'company',
                'ext' => null,
            ],
            [
                'key' => 'company_email',
                'name' => 'Correo Electrónico',
                'value' => 'info@empresa.com',
                'type' => 'text',
                'category' => 'company',
                'ext' => null,
            ],
            [
                'key' => 'company_address',
                'name' => 'Dirección',
                'value' => 'Calle Falsa 123',
                'type' => 'textarea',
                'category' => 'company',
                'ext' => null,
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
