@extends('Branch_OSA.layouts.layout')

@section('title', 'Dashboard | SARF Tracking')
@section('page-title', 'Dashboard')

@push('styles')
<style>
.dash-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 12px;
    margin-bottom: 16px;
}
.dash-stat {
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    background: #fff;
    padding: 16px;
    position: relative;
    overflow: hidden;
}
.dash-stat::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    border-radius: 12px 12px 0 0;
}
.dash-stat[data-color="purple"]::before { background: #7F77DD; }
.dash-stat[data-color="amber"]::before  { background: #EF9F27; }
.dash-stat[data-color="blue"]::before   { background: #378ADD; }
.dash-stat[data-color="teal"]::before   { background: #1D9E75; }
.dash-stat[data-color="green"]::before  { background: #639922; }
.dash-stat-icon {
    width: 36px; height: 36px;
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: 16px;
    margin-bottom: 12px;
}
.dash-stat[data-color="purple"] .dash-stat-icon { background: #EEEDFE; color: #534AB7; }
.dash-stat[data-color="amber"]  .dash-stat-icon { background: #FAEEDA; color: #854F0B; }
.dash-stat[data-color="blue"]   .dash-stat-icon { background: #E6F1FB; color: #185FA5; }
.dash-stat[data-color="teal"]   .dash-stat-icon { background: #E1F5EE; color: #0F6E56; }
.dash-stat[data-color="green"]  .dash-stat-icon { background: #EAF3DE; color: #3B6D11; }
.dash-stat-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: #64748b; margin-bottom: 4px; }
.dash-stat-value { font-size: 28px; font-weight: 800; line-height: 1; color: #1e293b; }
.dash-stat-footer { margin-top: 10px; font-size: 11px; color: #94a3b8; display: flex; align-items: center; gap: 5px; }
</style>
@endpush

@php
    $statusBadge = function($activity) {
        if ($activity->dashboard_inside_status) {
            return ['label' => $activity->dashboard_inside_status, 'class' => 'b-ongoing', 'icon' => 'fa-map-marker-alt'];
        }
        return match($activity->status) {
            'pending' => ['label' => 'Pending', 'class' => 'b-pending', 'icon' => 'fa-clock'],
            'for approval', 'for approval finance' => ['label' => ucfirst($activity->status), 'class' => 'b-ongoing', 'icon' => 'fa-spinner'],
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

   
</section>
@endsection
