@extends('layouts.master')
@section('title')
    <title>SIG-FACTURATION | Console Boîtier Fiscal</title>
@endsection
@section('style')
    @include('partials.style')
@endsection
@section('content')
    <main>
        @include('pages.mcf.console')
    </main>
@endsection
