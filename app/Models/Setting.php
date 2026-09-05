<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Setting extends Model
{
    use HasFactory;

    public const CATEGORY_ORDER = ['company', 'information', 'contact', 'payment', 'email', 'api'];

    protected $fillable = [
        'key',
        'value',
        'name',
        'type',
        'ext',
        'category',
    ];

    public function displayLabel(): string
    {
        return match ($this->key) {
            'app_name' => 'Nombre en pantalla',
            'app_short_name' => 'Nombre corto',
            'app_logo' => 'Logo',
            'app_favicon' => 'Favicon',
            'app_loading_gif' => 'Imagen de carga',
            'app_map_loaction' => 'Ubicación (mapa)',
            'company_name' => 'Nombre de la empresa',
            'company_nit' => 'NIT / RUC',
            'company_phone' => 'Teléfono',
            'company_email' => 'Correo',
            'company_address' => 'Dirección',
            default => trim((string) preg_replace('/^Application\s+/i', '', (string) $this->name)) ?: (string) $this->name,
        };
    }

    public function hint(): ?string
    {
        return match ($this->key) {
            'app_name' => 'Sale en la barra, el login y el título de las páginas.',
            'app_short_name' => 'Versión corta del nombre. Si no la usás, dejá el mismo.',
            'app_logo' => 'Se ve en la barra y en el login.',
            'app_favicon' => 'Ícono de la pestaña del navegador.',
            'app_loading_gif' => 'Aparece un instante al abrir el sistema.',
            'app_map_loaction' => 'Pegá el link de Google Maps del local.',
            'company_name' => 'El que va en tickets y documentos.',
            'company_nit' => 'NIT o RUC de la empresa.',
            default => null,
        };
    }

    public function isWideField(): bool
    {
        return $this->type === 'textarea';
    }

    public static function categoryLabel(?string $category): string
    {
        return match ($category) {
            'company' => 'Empresa',
            'information' => 'Identidad',
            'contact' => 'Contacto',
            'payment' => 'Pagos',
            'email' => 'Correo',
            'api' => 'API',
            default => $category ? Str::ucfirst($category) : 'General',
        };
    }

    public static function categoryLead(?string $category): ?string
    {
        return match ($category) {
            'company' => 'Datos que salen en tickets y documentos.',
            'information' => 'Nombre y archivos que se ven en la caja y el login.',
            'contact' => 'Ubicación del local, si la querés mostrar.',
            'payment' => 'Medios de pago del comercio.',
            'email' => 'Correo de salida y avisos.',
            'api' => 'Conexiones con otros sistemas.',
            default => null,
        };
    }
}
