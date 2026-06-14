<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report PDF | SARF Tracking</title>
    <link rel="icon" type="image/png" href="{{ asset('image/logo/arellano_logo.png') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; }

        @page {
            size: letter portrait;
            margin: 24mm 10mm 26mm 10mm;
        }

        body {
            margin: 0;
            background: #eef2f7;
            color: #111827;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
        }

        .pdf-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            width: min(744px, calc(100% - 32px));
            margin: 18px auto 10px;
        }

        .pdf-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .pdf-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            min-height: 36px;
            padding: 8px 13px;
            border: 1px solid #cbd5e1;
            border-radius: 7px;
            background: #fff;
            color: #334155;
            font: inherit;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
        }

        .pdf-btn.primary {
            background: #014ea8;
            border-color: #014ea8;
            color: #fff;
        }

        .pdf-status {
            color: #64748b;
            font-size: 12px;
            font-weight: 700;
        }

        .pdf-preview-modal {
            position: fixed;
            inset: 0;
            z-index: 9999;
            display: none;
            align-items: center;
            justify-content: center;
            background: rgba(15, 23, 42, .48);
            padding: 20px;
        }

        .pdf-preview-box {
            width: min(360px, 100%);
            padding: 22px 24px;
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 20px 44px rgba(15, 23, 42, .24);
            text-align: center;
        }

        .pdf-spinner {
            width: 38px;
            height: 38px;
            margin: 0 auto 12px;
            border: 4px solid #dbeafe;
            border-top-color: #014ea8;
            border-radius: 50%;
            animation: pdf-spin .8s linear infinite;
        }

        @keyframes pdf-spin {
            to { transform: rotate(360deg); }
        }

        .pdf-preview-shell {
            width: min(744px, calc(100% - 32px));
            margin: 0 auto 24px;
            background: #fff;
            border: 1px solid #d7dee8;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 12px 28px rgba(15, 23, 42, .12);
        }

        .report-pdf-content {
            width: 740px;
            max-width: 740px;
            padding: 0;
            background: #fff;
            color: #111827;
            font-family: Arial, Helvetica, sans-serif;
            overflow: visible;
        }

        .report-pdf-page {
            width: 100%;
            padding: 0;
            background: #fff;
        }

        .pdf-export .report-pdf-page {
            min-height: 866px;
            break-after: page;
            page-break-after: always;
        }

        .pdf-export .report-pdf-page:last-child {
            break-after: auto;
            page-break-after: auto;
        }

        .report-pdf-title {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 16px;
            padding: 0 0 9px;
            margin: 0 0 8px;
            border-bottom: 2px solid #111827;
            break-inside: avoid;
            page-break-inside: avoid;
        }

        .report-overview {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 5px;
            margin: 0 0 8px;
            break-inside: avoid;
            page-break-inside: avoid;
        }

        .overview-item {
            min-width: 0;
            padding: 5px 6px;
            border: 1px solid #d7dee8;
            border-radius: 6px;
            background: #f8fafc;
        }

        .overview-label {
            color: #64748b;
            font-size: 6.6px;
            font-weight: 900;
            line-height: 1.05;
            text-transform: uppercase;
        }

        .overview-value {
            margin-top: 2px;
            color: #0f172a;
            font-size: 13px;
            font-weight: 900;
            line-height: 1;
        }

        .report-pdf-title h1 {
            margin: 0;
            font-size: 15px;
            line-height: 1.1;
            text-transform: uppercase;
            letter-spacing: .2px;
        }

        .report-pdf-meta {
            color: #64748b;
            font-size: 8.5px;
            font-weight: 700;
            text-align: right;
            white-space: nowrap;
        }

        .report-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 8.4px;
        }

        .report-table thead {
            display: table-header-group;
            background: #f8fafc;
        }

        .report-table tr {
            break-inside: avoid;
            page-break-inside: avoid;
            break-after: auto;
            page-break-after: auto;
        }

        .report-table th,
        .report-table td {
            border-bottom: 1px solid #e5e7eb;
            padding: 5px 5px;
            text-align: left;
            vertical-align: middle;
            overflow-wrap: anywhere;
        }

        .report-table th {
            color: #475569;
            font-size: 7.4px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: .35px;
            border-top: 1px solid #d7dee8;
            border-bottom: 1.5px solid #d7dee8;
        }

        .report-table td {
            color: #111827;
            font-weight: 600;
            line-height: 1.22;
        }

        .report-table .code-col { width: 8.5%; }
        .report-table .name-col { width: 15%; }
        .report-table .branch-col { width: 23%; }
        .report-table .level-col { width: 16%; }
        .report-table .date-col { width: 12%; }
        .report-table .fund-col { width: 8.5%; }
        .report-table .status-col { width: 17%; }

        .row-code {
            color: #475569;
            font-size: 8px;
            font-weight: 800;
            white-space: nowrap;
        }

        .cell-main {
            font-weight: 800;
            color: #0f172a;
        }

        .pill-line {
            display: flex;
            gap: 3px;
            flex-wrap: wrap;
            margin-top: 3px;
        }

        .mini-pill,
        .status-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 3px;
            max-width: 100%;
            padding: 1.5px 4px;
            border-radius: 999px;
            background: #f1f5f9;
            color: #334155;
            font-size: 7.2px;
            font-weight: 800;
            line-height: 1.2;
            white-space: nowrap;
        }

        .status-pill {
            border-radius: 8px;
            white-space: normal;
            text-align: center;
        }

        .status-pill i {
            flex: 0 0 auto;
        }

        .pill-blue { background: #dbeafe; color: #1d4ed8; }
        .pill-green { background: #dcfce7; color: #15803d; }
        .pill-amber { background: #fef3c7; color: #92400e; }
        .pill-slate { background: #f1f5f9; color: #475569; }

        .status-approved,
        .status-completed { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
        .status-pending { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }
        .status-approval { background: #dbeafe; color: #014ea8; border: 1px solid #93c5fd; }
        .status-reschedule { background: #fef3c7; color: #92400e; border: 1px solid #fbbf24; }
        .status-revision { background: #fff1f2; color: #da281c; border: 1px solid #fca5a5; }
        .status-ongoing { background: #fef9c3; color: #854d0e; border: 1px solid #fde68a; }

        .empty-row {
            padding: 28px !important;
            color: #64748b !important;
            text-align: center !important;
        }

        .pdf-export {
            box-shadow: none !important;
            border: 0 !important;
            border-radius: 0 !important;
            width: 740px !important;
            max-width: 740px !important;
            padding: 0 !important;
            overflow: visible !important;
            background: #fff !important;
        }

        .pdf-export,
        .pdf-export * {
            box-sizing: border-box !important;
        }

        .pdf-export .report-table tr,
        .pdf-export .report-pdf-title {
            break-inside: avoid !important;
            page-break-inside: avoid !important;
        }

        @media print {
            html,
            body {
                width: 100%;
                margin: 0;
                background: #fff;
            }

            .pdf-toolbar,
            .pdf-preview-modal {
                display: none !important;
            }

            .pdf-preview-shell {
                width: 100%;
                margin: 0;
                border: 0;
                border-radius: 0;
                box-shadow: none;
                overflow: visible;
            }

            .report-pdf-content {
                width: 100%;
                max-width: 100%;
            }

            .report-pdf-page {
                padding: 0;
            }
        }
    </style>
</head>
<body>
@php
    $asList = fn ($value) => collect(is_array($value) ? $value : (filled($value) ? [$value] : []))
        ->filter(fn ($item) => filled($item))
        ->values();

    $morePill = function ($items, $limit = 1) {
        $items = collect($items)->values();
        $visible = $items->take($limit);
        $extra = max($items->count() - $visible->count(), 0);

        return [$visible, $extra];
    };

    $statusMeta = fn ($status) => match((string) $status) {
        'approved' => ['Approved', 'status-approved', 'fa-check-circle'],
        'completed' => ['Completed', 'status-completed', 'fa-check-double'],
        'pending' => ['Pending', 'status-pending', 'fa-clock'],
        'ongoing' => ['Ongoing', 'status-ongoing', 'fa-spinner'],
        'for approval', 'for approval finance' => [(string) $status === 'for approval finance' ? 'Finance Approval' : 'For Approval', 'status-approval', 'fa-clipboard-check'],
        'for approval for rescheduling' => ['For Approval for Rescheduling', 'status-approval', 'fa-calendar-alt'],
        'for revision' => ['For Revision', 'status-revision', 'fa-redo'],
        'for reschedule', 'for rescheduling', 'reshedule' => ['For Rescheduling', 'status-reschedule', 'fa-calendar-alt'],
        default => [Str::headline((string) $status), 'status-pending', 'fa-circle'],
    };

    $fundsClass = fn ($funds) => match($funds) {
        'With Budget' => 'pill-green',
        'ATC' => 'pill-amber',
        'No Fee' => 'pill-slate',
        default => 'pill-slate',
    };
@endphp

<div class="pdf-toolbar">
    <div>
        <div style="font-size:15px;font-weight:900;color:#0f172a;">Report PDF</div>
        <div class="pdf-status" id="pdfStatus">Preparing all {{ $activities->count() }} filtered records...</div>
    </div>
    <div class="pdf-actions">
        <button type="button" class="pdf-btn primary" id="downloadReportPdf">
            <i class="fas fa-file-pdf"></i> Generate PDF
        </button>
        <a href="{{ route('dean_osa.report.index', request()->except('page')) }}" class="pdf-btn">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="pdf-preview-modal" id="pdfPreviewModal" aria-live="polite" aria-modal="true" role="dialog">
    <div class="pdf-preview-box">
        <div class="pdf-spinner"></div>
        <div id="pdfPreviewLoadingText" style="font-size:14px;font-weight:900;color:#0f172a;">Generating PDF...</div>
        <div style="margin-top:5px;font-size:11px;color:#64748b;font-weight:700;">Please wait while the report is prepared.</div>
    </div>
</div>

<div class="pdf-preview-shell">
    <div id="reportPdfContent" class="report-pdf-content">
        <section class="report-pdf-page">
            <div class="report-pdf-title">
                <div>
                    <h1>Activity Report</h1>
                    <div style="font-size:10px;color:#64748b;font-weight:700;">
                        All filtered records from the Report table
                    </div>
                </div>
                <div class="report-pdf-meta">
                    {{ $printedAt->format('M j, Y') }}<br>
                    {{ $printedAt->format('g:i A') }}
                </div>
            </div>

            <div class="report-overview">
                <div class="overview-item">
                    <div class="overview-label">Total</div>
                    <div class="overview-value">{{ $counts['total'] ?? $activities->count() }}</div>
                </div>
                <div class="overview-item">
                    <div class="overview-label">Pending</div>
                    <div class="overview-value">{{ $counts['pending'] ?? 0 }}</div>
                </div>
                <div class="overview-item">
                    <div class="overview-label">For Approval</div>
                    <div class="overview-value">{{ $counts['for_approval'] ?? 0 }}</div>
                </div>
                <div class="overview-item">
                    <div class="overview-label">Rescheduling</div>
                    <div class="overview-value">{{ $counts['rescheduling'] ?? 0 }}</div>
                </div>
                <div class="overview-item">
                    <div class="overview-label">Approved</div>
                    <div class="overview-value">{{ $counts['approved'] ?? 0 }}</div>
                </div>
                <div class="overview-item">
                    <div class="overview-label">Completed</div>
                    <div class="overview-value">{{ $counts['completed'] ?? 0 }}</div>
                </div>
            </div>

            <table class="report-table">
                <thead>
                    <tr>
                        <th class="code-col">Code</th>
                        <th class="name-col">Activity Name</th>
                        <th class="branch-col">Branch</th>
                        <th class="level-col">Level</th>
                        <th class="date-col">Date of Activity</th>
                        <th class="fund-col">Fund's</th>
                        <th class="status-col">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activities as $activity)
                            @php
                                $departments = $asList($activity->department);
                                $orgs = $asList($activity->organizations);
                                $levels = $asList($activity->level);
                                $dates = collect($activity->activityDateValues());
                                [$visibleDates, $extraDates] = $morePill($dates, 1);

                                [$statusLabel, $statusClass, $statusIcon] = $statusMeta($activity->status);
                            @endphp
                            <tr>
                                <td><span class="row-code">{{ $activity->code }}</span></td>
                                <td><span class="cell-main">{{ $activity->title }}</span></td>
                                <td>
                                    <div class="cell-main">{{ $activity->branch->name ?? '-' }}</div>
                                    <div class="pill-line">
                                        @foreach($departments as $department)
                                            <span class="mini-pill pill-blue">{{ $department }}</span>
                                        @endforeach
                                        @foreach($orgs as $org)
                                            <span class="mini-pill pill-slate">{{ $org }}</span>
                                        @endforeach
                                    </div>
                                </td>
                                <td>
                                    <div class="pill-line" style="margin-top:0;">
                                        @forelse($levels as $level)
                                            <span class="mini-pill pill-slate">{{ $level }}</span>
                                        @empty
                                            -
                                        @endforelse
                                    </div>
                                </td>
                                <td>
                                    @forelse($visibleDates as $date)
                                        <span class="cell-main">{{ \Carbon\Carbon::parse($date)->format('M j, Y') }}</span>
                                        @if($extraDates > 0)
                                            <span class="mini-pill pill-slate" style="margin-left:4px;">+{{ $extraDates }}</span>
                                        @endif
                                    @empty
                                        -
                                    @endforelse
                                </td>
                                <td>
                                    @if($activity->funds)
                                        <span class="mini-pill {{ $fundsClass($activity->funds) }}">{{ $activity->funds }}</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    <span class="status-pill {{ $statusClass }}">
                                        <i class="fas {{ $statusIcon }}"></i>
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                            </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="empty-row">No activities found for this report.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"
    crossorigin="anonymous"
    referrerpolicy="no-referrer"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const button = document.getElementById('downloadReportPdf');
        const content = document.getElementById('reportPdfContent');
        const status = document.getElementById('pdfStatus');
        const modal = document.getElementById('pdfPreviewModal');
        const loadingText = document.getElementById('pdfPreviewLoadingText');

        const toDataUrl = (src) => new Promise((resolve) => {
            const image = new Image();
            image.crossOrigin = 'anonymous';
            image.onload = () => {
                const canvas = document.createElement('canvas');
                canvas.width = image.naturalWidth;
                canvas.height = image.naturalHeight;
                const context = canvas.getContext('2d');
                context.drawImage(image, 0, 0);
                resolve(canvas.toDataURL('image/png'));
            };
            image.onerror = () => resolve(null);
            image.src = src;
        });

        const getLogos = () => Promise.all([
            toDataUrl("{{ asset('image/logo/arellano_logo.png') }}"),
            toDataUrl("{{ asset('image/logo/au_osa_logo.png') }}"),
            toDataUrl("{{ asset('image/logo/osa_logo.png') }}"),
            toDataUrl("{{ asset('image/logo/globe.logo.png') }}"),
            toDataUrl("{{ asset('image/logo/gmail_logo.png') }}"),
            toDataUrl("{{ asset('image/logo/call_logo.png') }}"),
            toDataUrl("{{ asset('image/logo/fb_logo.png') }}"),
            toDataUrl("{{ asset('image/logo/insta_logo.png') }}"),
        ]);

        const addPageChrome = (pdf, logos) => {
            const pageCount = pdf.internal.getNumberOfPages();
            const width = pdf.internal.pageSize.getWidth();
            const height = pdf.internal.pageSize.getHeight();
            const inset = 10;
            const centerX = width / 2;

            for (let page = 1; page <= pageCount; page += 1) {
                pdf.setPage(page);
                pdf.setTextColor(0, 0, 0);

                pdf.setDrawColor(17, 24, 39);
                pdf.setLineWidth(0.65);
                pdf.line(inset, 19.5, width - inset, 19.5);
                pdf.setLineWidth(0.2);
                pdf.line(inset, 20.4, width - inset, 20.4);

                pdf.setFont('helvetica', 'bold');
                pdf.setFontSize(12);
                const titleText = 'ARELLANO UNIVERSITY';
                const titleWidth = pdf.getTextWidth(titleText);
                const logoSize = 13.5;
                const gap = 3.5;

                if (logos[0]) {
                    pdf.addImage(logos[0], 'PNG', centerX - (titleWidth / 2) - gap - logoSize, 3.8, logoSize, logoSize);
                }
                if (logos[1]) {
                    pdf.addImage(logos[1], 'PNG', centerX + (titleWidth / 2) + gap, 3.8, logoSize, logoSize);
                }

                pdf.text(titleText, centerX, 8.2, { align: 'center' });
                pdf.setFontSize(9.5);
                pdf.text('OFFICE FOR STUDENT AFFAIRS', centerX, 12.2, { align: 'center' });
                pdf.setFont('helvetica', 'normal');
                pdf.setFontSize(8);
                pdf.text('2600 Legarda Street, Sampaloc, Manila', centerX, 15.8, { align: 'center' });

                const footerTopY = height - 16.5;
                const footerBaseY = height - 7.2;
                const footerLogoX = inset + 32;
                const footerContactX = inset + 58;
                const footerRightX = width - inset;

                pdf.setDrawColor(17, 24, 39);
                pdf.setLineWidth(0.65);
                pdf.line(footerContactX, footerTopY, footerRightX, footerTopY);
                pdf.setLineWidth(0.2);
                pdf.line(footerContactX, footerTopY + 1.1, footerRightX, footerTopY + 1.1);

                pdf.setFont('helvetica', 'bold');
                pdf.setFontSize(10);
                pdf.text('#oneArellano', inset, footerBaseY);

                if (logos[2]) {
                    pdf.addImage(logos[2], 'PNG', footerLogoX, height - 16.2, 10.5, 10.5);
                }

                pdf.setFont('helvetica', 'normal');
                pdf.setFontSize(5.9);
                const iconSize = 3.1;
                const row1Y = height - 11.2;
                const row2Y = height - 7.2;
                const col1X = footerContactX;
                const col2X = footerContactX + 48;

                if (logos[3]) pdf.addImage(logos[3], 'PNG', col1X, row1Y - 3.1, iconSize, iconSize);
                pdf.text('www.arellano.edu.ph', col1X + 5, row1Y);
                if (logos[5]) pdf.addImage(logos[5], 'PNG', col1X, row2Y - 3.1, iconSize, iconSize);
                pdf.text('(02) 8 734 7371 to 75 loc. 206', col1X + 5, row2Y);
                if (logos[4]) pdf.addImage(logos[4], 'PNG', col2X, row1Y - 3.1, iconSize, iconSize);
                pdf.text('main.osa@arellano.edu.ph', col2X + 5, row1Y);
                if (logos[6]) pdf.addImage(logos[6], 'PNG', col2X, row2Y - 3.1, iconSize, iconSize);
                if (logos[7]) pdf.addImage(logos[7], 'PNG', col2X + 4.5, row2Y - 3.1, iconSize, iconSize);
                pdf.text('ArellanoUniversityOSA', col2X + 9.5, row2Y);

                pdf.setFontSize(6.5);
                pdf.text(`Page ${page} of ${pageCount}`, width - inset, height - 3, { align: 'right' });
            }
        };

        const createExportNode = () => {
            const wrapper = document.createElement('div');
            wrapper.style.position = 'absolute';
            wrapper.style.left = '0';
            wrapper.style.top = '-9999px';
            wrapper.style.width = '740px';
            wrapper.style.maxWidth = '740px';
            wrapper.style.background = '#fff';
            wrapper.style.zIndex = '-9999';

            document.body.appendChild(wrapper);
            buildPaginatedContent(wrapper);

            return wrapper;
        };

        const buildTable = (sourceTable) => {
            const table = sourceTable.cloneNode(false);
            const thead = sourceTable.querySelector('thead').cloneNode(true);
            const tbody = document.createElement('tbody');
            table.appendChild(thead);
            table.appendChild(tbody);

            return { table, tbody };
        };

        const buildPaginatedContent = (wrapper) => {
            const pageHeight = 866;
            const sourcePage = content.querySelector('.report-pdf-page');
            const sourceTitle = sourcePage.querySelector('.report-pdf-title');
            const sourceOverview = sourcePage.querySelector('.report-overview');
            const sourceTable = sourcePage.querySelector('.report-table');
            const sourceRows = Array.from(sourceTable.querySelectorAll('tbody tr'));
            const exportContent = document.createElement('div');
            let currentPage;
            let currentTbody;

            exportContent.className = 'report-pdf-content pdf-export';
            wrapper.appendChild(exportContent);

            const addPage = (withTitle = false) => {
                currentPage = document.createElement('section');
                currentPage.className = 'report-pdf-page';

                if (withTitle) {
                    currentPage.appendChild(sourceTitle.cloneNode(true));
                    if (sourceOverview) {
                        currentPage.appendChild(sourceOverview.cloneNode(true));
                    }
                }

                const built = buildTable(sourceTable);
                currentTbody = built.tbody;
                currentPage.appendChild(built.table);
                exportContent.appendChild(currentPage);
            };

            addPage(true);

            sourceRows.forEach((sourceRow) => {
                const row = sourceRow.cloneNode(true);
                currentTbody.appendChild(row);

                if (currentPage.offsetHeight > pageHeight && currentTbody.children.length > 1) {
                    currentTbody.removeChild(row);
                    addPage(false);
                    currentTbody.appendChild(row);
                }
            });

            return exportContent;
        };

        const setProgress = (message) => {
            status.textContent = message;
            loadingText.textContent = message;
        };

        const openModal = () => {
            modal.style.display = 'flex';
        };

        const closeModal = () => {
            modal.style.display = 'none';
        };

        const generatePdf = async () => {
            if (!content || typeof html2pdf === 'undefined') {
                status.textContent = 'PDF library failed to load.';
                return;
            }

            openModal();
            button.disabled = true;
            setProgress('Loading logos...');
            const logos = await getLogos();
            const wrapper = createExportNode();

            await new Promise(resolve => setTimeout(resolve, 150));
            setProgress('Rendering PDF...');

            const options = {
                margin: [24, 10, 26, 10],
                filename: 'sarf-report.pdf',
                image: { type: 'jpeg', quality: 0.94 },
                html2canvas: {
                    scale: 1.6,
                    useCORS: true,
                    backgroundColor: '#ffffff',
                    scrollX: 0,
                    scrollY: 0,
                    windowWidth: 740,
                    width: 740,
                    x: 0,
                    y: 0,
                },
                jsPDF: {
                    unit: 'mm',
                    format: 'letter',
                    orientation: 'portrait',
                    compress: true,
                },
                pagebreak: {
                    mode: ['css', 'legacy'],
                    avoid: ['tr', 'thead', '.report-pdf-title'],
                },
            };

            try {
                await html2pdf()
                    .set(options)
                    .from(wrapper.firstElementChild)
                    .toPdf()
                    .get('pdf')
                    .then((pdf) => {
                        setProgress('Adding header and footer...');
                        addPageChrome(pdf, logos);
                        const blob = pdf.output('blob');
                        const blobUrl = URL.createObjectURL(blob);
                        closeModal();
                        window.open(blobUrl, '_blank');
                    });

                status.textContent = 'PDF opened in a new tab.';
            } catch (error) {
                console.error(error);
                setProgress('Failed to generate PDF.');
                setTimeout(closeModal, 1400);
            } finally {
                wrapper.remove();
                button.disabled = false;
            }
        };

        button.addEventListener('click', generatePdf);
        setTimeout(generatePdf, 400);
    });
</script>
</body>
</html>
