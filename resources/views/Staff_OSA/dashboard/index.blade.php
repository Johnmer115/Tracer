@extends('Staff_OSA.dashboard.layout')

@section('title', 'Dashboard | SARF Tracking') 
@section('page-title', 'Dashboard') 

@section('content')
    <section class="panel">
        <h2>Welcome, {{ auth()->user()->username }}</h2>
        <p>You have been logged in as <strong>Staff OSA</strong>. Your dashboard is ready.</p>
    </section>
@endsection
