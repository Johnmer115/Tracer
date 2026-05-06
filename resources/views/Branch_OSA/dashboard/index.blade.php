@extends('Branch_OSA.dashboard.layout')

@section('title', 'Dashboard | SARF Tracking') 
@section('page-title', 'Dashboard') 

@section('content')
    <section class="panel" style="padding: 24px;">
        <h2>Welcome, {{ auth()->user()->username }}</h2>
        <p>You have been logged in as <strong>Branch OSA</strong>. Your dashboard is ready.</p>
    </section>
@endsection
