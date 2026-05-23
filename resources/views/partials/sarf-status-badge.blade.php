@php
    $status = (string) ($activity->status ?? '');

    $approvalFields = [
        ['col' => 'approval_dean_sa',             'office' => 'OSA'],
        ['col' => 'approval_avp_sps',             'office' => 'SPS'],
        ['col' => 'approval_dir_basic_ed',        'office' => 'Basic Ed'],
        ['col' => 'approval_vp_acad',             'office' => 'Acad'],
        ['col' => 'approval_vp_hrd_legal',        'office' => 'Legal'],
        ['col' => 'approval_auditing',            'office' => 'Auditing'],
        ['col' => 'approval_comptroller_initial', 'office' => 'Comptroller 1'],
        ['col' => 'approval_finance_initial',     'office' => 'Finance 1'],
        ['col' => 'approval_osa_finance',         'office' => 'OSA Finance'],
        ['col' => 'approval_finance_final',       'office' => 'Finance 2'],
        ['col' => 'approval_comptroller_final',   'office' => 'Comptroller 2'],
    ];

    $levels = is_array($activity->level ?? null)
        ? $activity->level
        : (filled($activity->level ?? null) ? [$activity->level] : []);

    $needsBasicEd = collect($levels)->contains(function ($level) {
        $level = Str::lower((string) $level);
        return Str::contains($level, ['elementary', 'junior high', 'senior high', 'basic', 'all levels']);
    });

    $needsFinance = ($activity->funds ?? null) === 'With Budget';

    $applicableApprovalFields = collect($approvalFields)->filter(function ($field) use ($needsBasicEd, $needsFinance) {
        if ($field['col'] === 'approval_dir_basic_ed') {
            return $needsBasicEd;
        }

        if (in_array($field['col'], [
            'approval_auditing',
            'approval_comptroller_initial',
            'approval_finance_initial',
            'approval_osa_finance',
            'approval_finance_final',
            'approval_comptroller_final',
        ], true)) {
            return $needsFinance;
        }

        return true;
    });

    $approvalLocation = null;
    if (in_array($status, ['for approval', 'for approval finance'], true)) {
        foreach ($applicableApprovalFields as $field) {
            if (($activity->{$field['col']} ?? 'pending') !== 'approved') {
                $approvalLocation = $field['office'];
                break;
            }
        }
    }

    $badge = $status === 'for approval for rescheduling'
        ? ['label' => 'For Approval for Rescheduling', 'bg' => '#dbeafe', 'color' => '#014ea8', 'border' => '#93c5fd', 'icon' => 'fa-calendar-alt']
        : ($approvalLocation
        ? ['label' => 'Pending in ' . $approvalLocation, 'bg' => '#dbeafe', 'color' => '#014ea8', 'border' => '#93c5fd', 'icon' => 'fa-map-marker-alt']
        : match($status) {
            'pending'               => ['label' => 'Pending',           'bg' => '#f1f5f9', 'color' => '#475569', 'border' => '#cbd5e1', 'icon' => 'fa-clock'],
            'ongoing'               => ['label' => 'Ongoing',           'bg' => '#fef9c3', 'color' => '#854d0e', 'border' => '#fde68a', 'icon' => 'fa-spinner'],
            'for approval'          => ['label' => 'For Approval',      'bg' => '#dbeafe', 'color' => '#014ea8', 'border' => '#93c5fd', 'icon' => 'fa-clipboard-check'],
            'for approval finance'  => ['label' => 'Finance Approval',  'bg' => '#dbeafe', 'color' => '#014ea8', 'border' => '#93c5fd', 'icon' => 'fa-file-invoice-dollar'],
            'for revision'          => ['label' => 'For Revision',      'bg' => '#fff1f2', 'color' => '#da281c', 'border' => '#fca5a5', 'icon' => 'fa-redo'],
            'for reschedule',
            'for rescheduling',
            'reshedule'             => ['label' => 'For Rescheduling',  'bg' => '#fef3c7', 'color' => '#92400e', 'border' => '#fbbf24', 'icon' => 'fa-calendar-alt'],
            'approved'              => ['label' => 'Approved',          'bg' => '#dcfce7', 'color' => '#15803d', 'border' => '#86efac', 'icon' => 'fa-check-circle'],
            'completed'             => ['label' => 'Completed',         'bg' => '#f0fdf4', 'color' => '#166534', 'border' => '#4ade80', 'icon' => 'fa-check-double'],
            'cancelled'             => ['label' => 'Cancelled',         'bg' => '#f8fafc', 'color' => '#94a3b8', 'border' => '#e2e8f0', 'icon' => 'fa-ban'],
            default                 => ['label' => Str::headline($status), 'bg' => '#f1f5f9', 'color' => '#475569', 'border' => '#cbd5e1', 'icon' => 'fa-circle'],
        });
@endphp

<span class="sarf-status-badge"
    style="--sarf-status-bg: {{ $badge['bg'] }}; --sarf-status-color: {{ $badge['color'] }}; --sarf-status-border: {{ $badge['border'] }};">
    <i class="fas {{ $badge['icon'] }}"></i>
    {{ $badge['label'] }}
</span>
