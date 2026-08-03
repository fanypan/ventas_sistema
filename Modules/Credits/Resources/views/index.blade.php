@extends('credits::layouts.master')

@section('content')
<div class="content-wrapper">
    <h1>Hello World</h1>

    <p>
        This view is loaded from module: {!! config('credits.name') !!}
    </p>
</div>
@endsection
