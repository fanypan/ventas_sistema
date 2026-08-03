@extends('financials::layouts.master')

@section('content')
<div class="content-wrapper">
    <h1>Hello World</h1>

    <p>
        This view is loaded from module: {!! config('financials.name') !!}
    </p>
</div>
@endsection
