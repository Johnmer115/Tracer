@extends('Branch_OSA.layouts.layout')

@section('title', 'Dashboard | SARF Tracking')
@section('page-title', 'Dashboard')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/branch-dashboard.css') }}">
@endpush

@php
    $statusBadge = function($activity) {
        if ($activity->dashboard_inside_status) {
            return ['label' => $activity->dashboard_inside_status, 'class' => 'b-ongoing', 'icon' => 'fa-map-marker-alt'];
        }
        return match($activity->status) {
            'pending' => ['label' => 'Pending', 'class' => 'b-pending', 'icon' => 'fa-clock'],
            'for approval', 'for approval finance' => ['label' => ucfirst($activity->status), 'class' => 'b-ongoing', 'icon' => 'fa-spinner'],
            'for approval for rescheduling' => ['label' => 'Reschedule Approval', 'class' => 'b-ongoing', 'icon' => 'fa-calendar-check'],
            'for reschedule', 'for rescheduling', 'reshedule' => ['label' => 'For Rescheduling', 'class' => 'b-revision', 'icon' => 'fa-calendar-alt'],
            'approved' => ['label' => 'Approved', 'class' => 'b-approved', 'icon' => 'fa-check-circle'],
            'completed' => ['label' => 'Completed', 'class' => 'b-completed', 'icon' => 'fa-check-double'],
            'for revision' => ['label' => 'For Revision', 'class' => 'b-revision', 'icon' => 'fa-redo'],
            default => ['label' => ucfirst((string) $activity->status), 'class' => 'b-pending', 'icon' => 'fa-circle'],
        };
    };
@endphp

@section('content')
<section class="panel" style="padding: 25px;">
    <div class="panel-header" style="margin-bottom:16px;">
        <div>
            <div class="panel-title">
                <i class="fas fa-chart-line"></i> Branch Dashboard — {{ $branchName }}
            </div>
            <div class="td-sub" style="margin-top:4px;">Activity overview for your designated branch.</div>
        </div>
    </div>

    <div class="dash-grid">
        <div class="dash-stat" data-color="purple">
            <div class="dash-stat-icon"><i class="fas fa-layer-group"></i></div>
            <div class="dash-stat-label">Total</div>
            <div class="dash-stat-value">{{ $counts['total'] }}</div>
            <div class="dash-stat-footer"><i class="fas fa-calendar-alt"></i> all time</div>
        </div>
        <div class="dash-stat" data-color="amber">
            <div class="dash-stat-icon"><i class="fas fa-clock"></i></div>
            <div class="dash-stat-label">Pending</div>
            <div class="dash-stat-value">{{ $counts['pending'] }}</div>
            <div class="dash-stat-footer"><i class="fas fa-circle" style="font-size:6px;"></i> awaiting action</div>
        </div>
        <div class="dash-stat" data-color="blue">
            <div class="dash-stat-icon"><i class="fas fa-clipboard-check"></i></div>
            <div class="dash-stat-label">For Approval</div>
            <div class="dash-stat-value">{{ $counts['for_approval'] }}</div>
            <div class="dash-stat-footer"><i class="fas fa-circle" style="font-size:6px;"></i> in pipeline</div>
        </div>
        <div class="dash-stat" data-color="amber">
            <div class="dash-stat-icon"><i class="fas fa-calendar-alt"></i></div>
            <div class="dash-stat-label">Rescheduling</div>
            <div class="dash-stat-value">{{ $counts['rescheduling'] }}</div>
            <div class="dash-stat-footer"><i class="fas fa-circle" style="font-size:6px;"></i> schedule changes</div>
        </div>
        <div class="dash-stat" data-color="teal">
            <div class="dash-stat-icon"><i class="fas fa-check-circle"></i></div>
            <div class="dash-stat-label">Approved</div>
            <div class="dash-stat-value">{{ $counts['approved'] }}</div>
            <div class="dash-stat-footer"><i class="fas fa-circle" style="font-size:6px;"></i> cleared</div>
        </div>
        <div class="dash-stat" data-color="green">
            <div class="dash-stat-icon"><i class="fas fa-check-double"></i></div>
            <div class="dash-stat-label">Completed</div>
            <div class="dash-stat-value">{{ $counts['completed'] }}</div>
            <div class="dash-stat-footer"><i class="fas fa-circle" style="font-size:6px;"></i> done</div>
        </div>
    </div>

    @include('partials.dashboard-message-board', [
        'messageRoutePrefix' => 'branch_osa',
        'canComposeMessages' => false,
        'canManageMessages' => false,
        'messageBranches' => collect(),
    ])

</section>
@endsection
