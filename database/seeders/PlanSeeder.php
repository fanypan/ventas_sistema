<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Starter',
                'slug' => 'starter',
                'price_monthly' => 150000,
                'price_yearly' => 1620000,
                'max_users' => 2,
                'max_cajas' => 1,
                'sifen_documents_monthly' => 0,
                'features' => ['pos', 'stock', 'customers', 'cash', 'reports'],
                'description' => 'POS, stock, clientes, caja y reportes. Factura PDF. 2 usuarios y 1 caja.',
                'sort_order' => 1,
            ],
            [
                'name' => 'Negocio',
                'slug' => 'negocio',
                'price_monthly' => 280000,
                'price_yearly' => 3024000,
                'max_users' => 5,
                'max_cajas' => 3,
                'sifen_documents_monthly' => 500,
                'features' => ['pos', 'stock', 'customers', 'cash', 'reports', 'purchases', 'credits', 'sifen'],
                'description' => 'Compras, créditos y SIFEN (500 documentos/mes). 5 usuarios y 3 cajas.',
                'sort_order' => 2,
            ],
            [
                'name' => 'Empresa',
                'slug' => 'empresa',
                'price_monthly' => 490000,
                'price_yearly' => 5292000,
                'max_users' => 20,
                'max_cajas' => 10,
                'sifen_documents_monthly' => 2000,
                'features' => ['pos', 'stock', 'customers', 'cash', 'reports', 'purchases', 'credits', 'sifen', 'priority_support'],
                'description' => 'Hasta 20 usuarios, 10 cajas y 2.000 documentos SIFEN/mes. Soporte prioritario.',
                'sort_order' => 3,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(['slug' => $plan['slug']], $plan);
        }
    }
}
