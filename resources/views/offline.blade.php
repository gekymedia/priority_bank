@extends('layouts.app')

@section('title', 'Offline')

@section('content')
<div class="container mx-auto px-4 py-8 text-center">
    <h1 class="text-2xl font-bold mb-4">You're Offline</h1>
    <p class="mb-4">This app requires an internet connection to sync data.</p>
    <p>Basic functionality may be available when you reconnect.</p>
</div>
@endsection