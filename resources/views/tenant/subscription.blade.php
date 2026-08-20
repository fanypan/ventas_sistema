@extends('admin.layouts.master')
@section('content')
@php $title = 'Mi plan'; @endphp
<div class="content-wrapper">
    <section class="content-header"><div class="container-fluid"><h1>Mi plan</h1></div></section>
    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-body">
                    <p><strong>Comercio:</strong> {{ $tenant->name }}</p>
                    <p><strong>Plan:</strong> {{ $tenant->plan?->name }}</p>
                    <p><strong>Estado:</strong> {{ $tenant->status }}</p>
                    <p><strong>Vence:</strong> {{ optional($tenant->subscription?->ends_at)->format('d/m/Y') ?: '—' }}</p>
                    <p>Pago por transferencia o efectivo a AranduTech. Si ya pagaste, avisanos por WhatsApp para renovar.</p>
                    <a class="btn btn-success" href="https://wa.me/{{ $whatsapp }}?text={{ urlencode('Hola, soy '.$tenant->name.' y quiero renovar el plan') }}">WhatsApp</a>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
