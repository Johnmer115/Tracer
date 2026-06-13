<style>
    .print-only {
        display: none;
    }

    .tracer-print-btn {
        background: #014ea8 !important;
        border-color: #013f88 !important;
        color: #fff !important;
        font-weight: 800 !important;
        box-shadow: 0 8px 16px rgba(1, 78, 168, 0.24) !important;
    }

    .tracer-print-btn:hover {
        background: #013f88 !important;
        transform: translateY(-1px);
    }

    /* Screen Styles */
    .pipeline-tracker {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
        padding: 12px 16px;
        background: #f8fafc;
        border: 1px solid var(--border);
        border-radius: 10px;
        overflow-x: auto;
    }

    .pipeline-step {
        display: flex;
        align-items: center;
        flex: 1;
        min-width: 80px;
    }

    .pipeline-step-inner {
        text-align: center;
        flex: 1;
    }

    .pipeline-connector {
        flex: 0 0 14px;
        height: 2px;
        border-radius: 2px;
    }

    .approval-card {
        position: relative;
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 10px 14px;
        margin-bottom: 8px;
        border-radius: 8px;
    }

    .approval-card.status-approved {
        background: #dcfce7;
        border: 1px solid #86efac;
    }

    .approval-card.status-for-signature {
        background: #dbeafe;
        border: 1px solid #93c5fd;
    }

    .approval-card.status-disapproved {
        background: #fef2f2;
        border: 1px solid #fca5a5;
    }

    .approval-card.status-pending {
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
    }

    /* PDF Export Styles */
    .tracer-pdf-export {
        width: 920px;
        max-width: 920px;
        min-width: 0;
        padding: 0;
        overflow: hidden;
        background: #fff;
        color: #111827;
        font-family: Arial, Helvetica, sans-serif;
        box-sizing: border-box;
    }

    .tracer-pdf-export,
    .tracer-pdf-export * {
        box-shadow: none !important;
        box-sizing: border-box;
        overflow-wrap: anywhere;
    }

    .tracer-pdf-export .print-only {
        display: block !important;
    }

    .tracer-pdf-export .print-inline {
        display: inline !important;
    }

    .tracer-pdf-export .print-title-code {
        font-size: 14px !important;
        font-weight: 700 !important;
        color: #111827 !important;
        display: flex !important;
        gap: 8px !important;
        align-items: center !important;
        padding: 0 0 12px !important;
        margin: 0 0 12px !important;
        border-bottom: 1.5px solid #d7dee8 !important;
        page-break-inside: avoid !important;
        break-inside: avoid !important;
    }

    .tracer-pdf-export .print-second-page {
        page-break-before: auto !important;
        break-before: auto !important;
        padding-top: 0 !important;
        margin-top: 0 !important;
    }

    .tracer-pdf-export .print-second-page .approval-group {
        margin-bottom: 8px !important;
    }

    .tracer-pdf-export .print-second-page .approval-group-title {
        margin-bottom: 4px !important;
    }

    .tracer-pdf-export .print-details {
        padding: 8px 0 10px !important;
        background: #fff !important;
        border: 0 !important;
        border-bottom: 1.5px solid #d7dee8 !important;
        border-radius: 0 !important;
        margin-bottom: 10px !important;
    }

    .tracer-pdf-export .print-details > div[style*="display:grid"] {
        grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
        gap: 7px 18px !important;
    }

    .tracer-pdf-export .print-details div[style*="font-size:10px"] {
        font-size: 8.5px !important;
        margin-bottom: 2px !important;
    }

    .tracer-pdf-export .print-details div[style*="font-size:13px"] {
        font-size: 10.5px !important;
        line-height: 1.25 !important;
    }

    .tracer-pdf-export .pipeline-tracker {
        overflow: hidden !important;
        overflow-x: hidden !important;
        white-space: normal !important;
        flex-wrap: nowrap !important;
        width: 100% !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
        display: flex !important;
        background: #fff !important;
        border: 0 !important;
        border-top: 1px solid #e5e7eb !important;
        border-bottom: 1px solid #e5e7eb !important;
        border-radius: 0 !important;
        padding: 10px 60px 10px 0px!important;
        margin-bottom: 18px !important;
    }

    .tracer-pdf-export .pipeline-step {
        width: auto !important;
        max-width: 100% !important;
    }

    .tracer-pdf-export .pipeline-step-inner {

        max-width: 100% !important;
        overflow: hidden !important;
    }

    .tracer-pdf-export .pipeline-step:first-child .pipeline-step-inner {
        padding-left: 0 !important;
    }

    .tracer-pdf-export .pipeline-step:last-child .pipeline-step-inner {
        padding-right: 0 !important;
    }

    .tracer-pdf-export .pipeline-step:last-child .pipeline-step-inner > div:last-child {
        font-size: 8px !important;
        white-space: normal !important;
        overflow-wrap: normal !important;
    }

    .tracer-pdf-export .pipeline-connector {
        flex: 0 0 8px !important;
        width: 8px !important;
        min-width: 8px !important;
        height: 2px !important;
    }

    .tracer-pdf-export .approval-group {
        page-break-inside: auto;
        break-inside: auto;
        margin-bottom: 16px !important;
    }

    .tracer-pdf-export .approval-track {
        width: 100% !important;
        max-width: 100% !important;
        padding-left: 0 !important;
        margin-bottom: 4px !important;
        overflow: visible !important;
    }

    .tracer-pdf-export .approval-line,
    .tracer-pdf-export .approval-dot {
        display: none !important;
    }

    .tracer-pdf-export .approval-card {
        display: block !important;
        page-break-inside: avoid !important;
        break-inside: avoid !important;
        -webkit-column-break-inside: avoid !important;
        -webkit-region-break-inside: avoid !important;
        align-items: flex-start !important;
        width: 100% !important;
        max-width: 100% !important;
        background: #fff !important;
        border: 0 !important;
        border-bottom: 1px dashed #d7dee8 !important;
        border-radius: 0 !important;
        margin-bottom: 2px !important;
        padding: 5px 0 !important;
        overflow: visible !important;
    }

    .tracer-pdf-export .approval-card > i {
        display: none !important;
    }

    .tracer-pdf-export .print-second-page .approval-card {
        margin-bottom: 0 !important;
        padding: 4px 0 !important;
    }

    .tracer-pdf-export .print-second-page .approval-card div[style*="margin-top:8px"] {
        margin-top: 1px !important;
    }

    .tracer-pdf-export .approval-card span[style*="padding:4px 8px"] {
        background: #fff !important;
        padding: 0 !important;
    }

    .tracer-pdf-export .print-reschedule {
        margin-bottom: 0 !important;
        page-break-inside: auto !important;
        break-inside: auto !important;
    }

    .tracer-pdf-export .print-reschedule > div:first-child {
        margin-bottom: 6px !important;
    }

    .tracer-pdf-export .print-reschedule > div[style*="background:#f9fafb"] {
        width: 100% !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
        background: #fff !important;
        border: 0 !important;
        border-top: 1px solid #d7dee8 !important;
        border-bottom: 1px solid #d7dee8 !important;
        border-radius: 0 !important;
        padding: 7px 0 !important;
        overflow: hidden !important;
    }

    .tracer-pdf-export .print-reschedule div[style*="display:grid"] {
        gap: 8px 18px !important;
    }

    .tracer-pdf-export .pdf-page-break {
        display: none !important;
        height: 0 !important;
        page-break-after: auto;
        break-after: auto;
        padding-top: 0 !important;
    }

    .tracer-pdf-export .auto-page-break {
        display: block !important;
        height: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
        page-break-before: always !important;
        break-before: page !important;
    }

    .tracer-pdf-export .no-pdf,
    .tracer-pdf-export .no-print {
        display: none !important;
    }
</style>

<style media="print">
    /* Hides everything except the print content */
    nav, 
    .sidebar, 
    .navbar, 
    .panel-controls, 
    .btn, 
    .abtn,
    .panel-header,
    .app-header,
    header,
    aside,
    footer:not(.print-footer),
    .no-print {
        display: none !important;
    }

    /* Unset positioning of layout wrappers to allow viewport-relative position: fixed for print header/footer */
    body,
    .main,
    .content,
    .panel,
    main {
        position: static !important;
        transform: none !important;
        filter: none !important;
        perspective: none !important;
        background: white !important;
        color: black !important;
        box-shadow: none !important;
        border: none !important;
    }

    .panel {
        padding: 0 !important;
        margin: 0 !important;
    }

    /* Content wrapper padding to clear fixed header/footer */
    .print-padding {
        box-sizing: border-box !important;
        width: 100% !important;
        max-width: 100% !important;
        padding-top: 0 !important;
        padding-right: 0.8in !important;
        padding-bottom: 0 !important;
        padding-left: 0.8in !important;
        overflow: hidden !important;
    }

    #tracerPdfContent,
    #tracerPdfContent * {
        box-sizing: border-box !important;
        max-width: 100% !important;
    }

    #tracerPdfContent {
        width: 100% !important;
        overflow: hidden !important;
    }

    .print-title-code {
        font-size: 14px !important;
        font-weight: 700 !important;
        color: #111827 !important;
        display: flex !important;
        gap: 8px !important;
        align-items: center !important;
        padding: 0 0 10px !important;
        margin: 0 0 12px !important;
        border-bottom: 1.5px solid #d7dee8 !important;
        page-break-inside: avoid !important;
        break-inside: avoid !important;
    }

    .print-inline {
        display: inline !important;
    }

    /* Clean and minimal styles for Activity Details section only */
    .print-details {
        background: none !important;
        border: none !important;
        border-bottom: 2px solid #e2e8f0 !important;
        border-radius: 0 !important;
        padding: 7px 0 9px !important;
        margin-bottom: 10px !important;
        width: 100% !important;
        max-width: 100% !important;
        overflow: hidden !important;
    }

    .print-details > div[style*="display:grid"] {
        display: grid !important;
        grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
        width: 100% !important;
        max-width: 100% !important;
        gap: 7px 18px !important;
        overflow: hidden !important;
    }

    .print-details div[style*="font-size:10px"] {
        font-size: 8.5px !important;
        margin-bottom: 2px !important;
    }

    .print-details div[style*="font-size:13px"] {
        font-size: 10.5px !important;
        line-height: 1.25 !important;
    }

    /* Rescheduling section flows after approvals and can split if approvals continue. */
    .print-reschedule {
        page-break-before: auto;
        page-break-inside: auto;
        break-inside: auto;
        margin-bottom: 15px !important;
    }

    .print-second-page {
        page-break-before: auto !important;
        break-before: auto !important;
        padding-top: 0 !important;
    }

    .pdf-page-break {
        display: none !important;
        height: 0 !important;
        page-break-after: auto !important;
        break-after: auto !important;
    }

    .auto-page-break {
        display: block !important;
        height: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
        page-break-before: always !important;
        break-before: page !important;
    }

    .print-reschedule > div[style*="background:#f9fafb"] {
        background: none !important;
        border: none !important;
        border-bottom: 1px solid #e2e8f0 !important;
        border-radius: 0 !important;
        padding: 10px 0 !important;
    }

    /* Clean and minimal styles for pipeline tracker */
    .pipeline-tracker {
        width: 100% !important;
        max-width: 100% !important;
        background: none !important;
        border: none !important;
        padding: 5px 45px !important;
        margin-bottom: 15px !important;
        overflow: hidden !important;
        overflow-x: hidden !important;
    }

    .pipeline-step {
        min-width: 0 !important;
        width: auto !important;
        max-width: 100% !important;
    }

    /* Clean and minimal styles for signatories (remove heavy borders and backgrounds) */
    .approval-group {
        margin-bottom: 15px !important;
    }

    /* Force Finance Approval group to start on a new page */
    .print-finance-group {
        page-break-before: auto !important;
        break-before: auto !important;
        padding-top: 0 !important;
    }

    /* Reset timeline container indentation */
    .approval-track {
        width: 100% !important;
        max-width: 100% !important;
        padding-left: 0 !important;
        margin-bottom: 10px !important;
        overflow: visible !important;
    }

    /* Hide the timeline vertical line and circular dot to clean up space and avoid alignment issues */
    .approval-line,
    .approval-dot {
        display: none !important;
    }

    /* Clean signatory row cards */
    .approval-card {
        display: block !important;
        page-break-inside: avoid !important;
        break-inside: avoid !important;
        -webkit-column-break-inside: avoid !important;
        -webkit-region-break-inside: avoid !important;
        width: 100% !important;
        max-width: 100% !important;
        background: none !important;
        border: none !important;
        border-bottom: 1px dashed #e2e8f0 !important;
        border-radius: 0 !important;
        padding: 6px 0 !important;
        margin-bottom: 4px !important;
        box-shadow: none !important;
        overflow: visible !important;
    }

    .approval-card > i {
        display: none !important;
    }

    /* Font size reductions for printing */
    .approval-group div[style*="font-size:13px"] {
        font-size: 11px !important;
    }

    .approval-group div[style*="display:flex;gap:8px;flex-wrap:wrap"] {
        margin-top: 2px !important;
        gap: 12px !important;
    }

    .approval-group span[style*="padding:4px 8px"] {
        padding: 0 !important;
        background: none !important;
        font-size: 10px !important;
    }

    /* Page margins — top margin tall enough for fixed header, bottom for fixed footer */
    @page {
        size: letter portrait;
        margin-top: 1.0in;
        margin-right: 0.8in;
        margin-bottom: 0.9in;
        margin-left: 0.8in;

        @bottom-right {
            content: "Page " counter(page);
            font-size: 10px;
        }
    }

    /* Display print-only elements */
    .print-only {
        display: block !important;
    }

    /* Fixed header — repeats on every printed page */
    .print-header {
        position: fixed !important;
        top: 0;
        left: 0;
        right: 0;
        width: 100%;
        background: #fff;
        z-index: 1000;
        padding-bottom: 5px;
    }

    /* Fixed footer — repeats on every printed page */
    .print-footer {
        position: fixed !important;
        bottom: 0;
        left: 0;
        right: 0;
        width: 100%;
        background: #fff;
        z-index: 1000;
        padding-top: 5px;
    }

    /* Page number via CSS counter(page) */
    .print-footer-right::after {
        content: "";
    }

    /* Prevent breaking inside specific sections */
    .view-section,
    .approved-upload-card,
    .approval-group {
        page-break-inside: avoid;
    }
</style>

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"
        crossorigin="anonymous"
        referrerpolicy="no-referrer"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const button = document.getElementById('downloadTracerPdf');
            const content = document.getElementById('tracerPdfContent');
            const modal = document.getElementById('pdfPreviewModal');
            const closeBtn = document.getElementById('pdfPreviewClose');
            const loadingText = document.getElementById('pdfPreviewLoadingText');

            if (!button || !content || !modal) {
                return;
            }

            let logoPromise = null;
            let fontPromise = null;
            let cachedPdfBlob = null;

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

            const getLogos = () => {
                if (!logoPromise) {
                    logoPromise = Promise.all([
                        toDataUrl("{{ asset('image/logo/arellano_logo.png') }}"),
                        toDataUrl("{{ asset('image/logo/au_osa_logo.png') }}"),
                        toDataUrl("{{ asset('image/logo/osa_logo.png') }}"),
                        toDataUrl("{{ asset('image/logo/globe.logo.png') }}"),
                        toDataUrl("{{ asset('image/logo/gmail_logo.png') }}"),
                        toDataUrl("{{ asset('image/logo/call_logo.png') }}"),
                        toDataUrl("{{ asset('image/logo/fb_logo.png') }}"),
                        toDataUrl("{{ asset('image/logo/insta_logo.png') }}"),
                    ]);
                }
                return logoPromise;
            };

            const toBase64Font = async (url, name, style) => {
                try {
                    const res = await fetch(url);
                    if (!res.ok) return null;
                    const buffer = await res.arrayBuffer();
                    const bytes = new Uint8Array(buffer);
                    let binary = '';
                    const len = bytes.byteLength;
                    for (let i = 0; i < len; i++) {
                        binary += String.fromCharCode(bytes[i]);
                    }
                    return { name, style, base64: btoa(binary) };
                } catch (e) {
                    console.error('Failed to load font:', url, e);
                    return null;
                }
            };

            const getFonts = () => {
                if (!fontPromise) {
                    fontPromise = Promise.all([
                        toBase64Font('https://fonts.gstatic.com/s/ptsansnarrow/v17/jx5YrD424m5X1n4B3Xb3w00.ttf', 'PTSansNarrow', 'normal'),
                        toBase64Font('https://fonts.gstatic.com/s/ptsansnarrow/v17/jx5VD424m5X1n4B3Xb3w1067t8T.ttf', 'PTSansNarrow', 'bold')
                    ]);
                }
                return fontPromise;
            };

            const addPageChrome = (pdf, logos, fonts) => {
                const pageCount = pdf.internal.getNumberOfPages();
                const width = pdf.internal.pageSize.getWidth();
                const height = pdf.internal.pageSize.getHeight();
                const printInset = 20.32;
                const centerX = width / 2;

                const hasNarrow = fonts && fonts.some(f => f && f.name === 'PTSansNarrow');
                const fontName = hasNarrow ? 'PTSansNarrow' : 'helvetica';

                for (let page = 1; page <= pageCount; page += 1) {
                    pdf.setPage(page);
                    
                    // Double underline matching reference
                    pdf.setDrawColor(17, 24, 39);
                    pdf.setLineWidth(0.65);
                    pdf.line(printInset, 19.5, width - printInset, 19.5);
                    pdf.setLineWidth(0.2);
                    pdf.line(printInset, 20.4, width - printInset, 20.4);

                    // Set font details to measure the width of the main title
                    pdf.setFont(fontName, 'bold');
                    const titleFontSize = hasNarrow ? 14 : 11.5;
                    pdf.setFontSize(titleFontSize);
                    
                    const titleText = 'ARELLANO UNIVERSITY';
                    const titleWidth = pdf.getTextWidth(titleText);
                    const gap = 3.5; // gap between logo and text
                    const logoSize = 13.5; // size of the circular logos

                    const leftLogoX = centerX - (titleWidth / 2) - gap - logoSize;
                    const rightLogoX = centerX + (titleWidth / 2) + gap;

                    // Left Logo
                    if (logos.arellano) {
                        pdf.addImage(logos.arellano, 'PNG', leftLogoX, 3.8, logoSize, logoSize);
                    }

                    // Right Logo
                    if (logos.auOsa) {
                        pdf.addImage(logos.auOsa, 'PNG', rightLogoX, 3.8, logoSize, logoSize);
                    }

                    // Center-aligned text block
                    pdf.text(titleText, centerX, 8.2, { align: 'center' });
                    
                    pdf.setFontSize(hasNarrow ? 11 : 9.5);
                    pdf.text('OFFICE FOR STUDENT AFFAIRS', centerX, 12.2, { align: 'center' });
                    
                    pdf.setFont(fontName, 'normal');
                    pdf.setFontSize(hasNarrow ? 9.5 : 8);
                    pdf.text('2600 Legarda Street, Sampaloc, Manila', centerX, 15.8, { align: 'center' });

                    // Footer layout matching the print reference
                    const footerTopY = height - 16.5;
                    const footerBaseY = height - 7.2;
                    const footerLogoX = printInset + 36;
                    const footerContactX = printInset + 70;
                    const footerRightX = width - printInset;

                    // Double underline above contact information
                    const lineStartX = footerContactX;
                    pdf.setDrawColor(17, 24, 39);
                    pdf.setLineWidth(0.65);
                    pdf.line(lineStartX, footerTopY, footerRightX, footerTopY);
                    pdf.setLineWidth(0.2);
                    pdf.line(lineStartX, footerTopY + 1.1, footerRightX, footerTopY + 1.1);

                    // #oneArellano on the left
                    pdf.setFont(fontName, 'bold');
                    pdf.setFontSize(hasNarrow ? 14 : 11.5);
                    pdf.setTextColor(0, 0, 0);
                    pdf.text('#oneArellano', printInset, footerBaseY);

                    // OSA Logo beside #oneArellano (circular/square 1:1 aspect ratio)
                    if (logos.osa) {
                        pdf.addImage(logos.osa, 'PNG', footerLogoX, height - 17.5, 12.5, 12.5);
                    }

                    // Contact Info Details
                    pdf.setFont(fontName, 'normal');
                    pdf.setFontSize(hasNarrow ? 8.5 : 7.2);
                    pdf.setTextColor(0, 0, 0);

                    const iconSize = 3.6;
                    const row1Y = height - 11.2;
                    const row2Y = height - 7.2;
                    const col1X = footerContactX;
                    const col2X = footerContactX + 54;

                    if (logos.globe) {
                        pdf.addImage(logos.globe, 'PNG', col1X, row1Y - 3.1, iconSize, iconSize);
                    }
                    pdf.text('www.arellano.edu.ph', col1X + 5, row1Y);

                    if (logos.call) {
                        pdf.addImage(logos.call, 'PNG', col1X, row2Y - 3.1, iconSize, iconSize);
                    }
                    pdf.text('(02) 8 734 7371 to 75 loc. 206', col1X + 5, row2Y);

                    if (logos.gmail) {
                        pdf.addImage(logos.gmail, 'PNG', col2X, row1Y - 3.1, iconSize, iconSize);
                    }
                    pdf.text('main.osa@arellano.edu.ph', col2X + 5, row1Y);

                    // Use the fb and insta image logos
                    if (logos.fb) {
                        pdf.addImage(logos.fb, 'PNG', col2X, row2Y - 3.1, iconSize, iconSize);
                    }
                    if (logos.insta) {
                        pdf.addImage(logos.insta, 'PNG', col2X + 4.5, row2Y - 3.1, iconSize, iconSize);
                    }
                    pdf.text('ArellanoUniversityOSA', col2X + 9.5, row2Y);

                    // Page number at the very bottom right
                    pdf.setFontSize(hasNarrow ? 7.5 : 6);
                    pdf.text(`Page ${page} of ${pageCount}`, width - printInset, height - 3, { align: 'right' });
                }
            };

            const createExportNode = () => {
                const wrapper = document.createElement('div');
                wrapper.style.position = 'absolute';
                wrapper.style.left = '0';
                wrapper.style.top = '-9999px';
                wrapper.style.width = '920px';
                wrapper.style.maxWidth = '920px';
                wrapper.style.overflow = 'hidden';
                wrapper.style.zIndex = '-9999';
                wrapper.style.background = '#fff';

                const clone = content.cloneNode(true);
                clone.removeAttribute('id');
                clone.classList.add('tracer-pdf-export');
                clone.style.width = '920px';
                clone.style.maxWidth = '920px';
                clone.style.minWidth = '0';
                clone.style.overflow = 'hidden';
                clone.style.overflowX = 'hidden';
                clone.style.boxSizing = 'border-box';
                clone.style.padding = '0';
                clone.style.background = '#fff';

                wrapper.appendChild(clone);
                document.body.appendChild(wrapper);

                // Force layout recalculation so html2canvas measures correctly
                void wrapper.offsetWidth;
                void wrapper.offsetHeight;

                return { wrapper, clone };
            };

            const createPdfPageNode = (clone, selectors) => {
                const page = document.createElement('div');
                page.className = 'tracer-pdf-export tracer-pdf-page';
                page.style.width = '920px';
                page.style.maxWidth = '920px';
                page.style.minWidth = '0';
                page.style.overflow = 'hidden';
                page.style.boxSizing = 'border-box';
                page.style.padding = '0';
                page.style.margin = '0';
                page.style.background = '#fff';

                selectors.forEach((selector) => {
                    const node = clone.querySelector(selector);
                    if (node) {
                        const copy = node.cloneNode(true);
                        copy.style.marginTop = '0';
                        page.appendChild(copy);
                    }
                });

                return page.childElementCount ? page : null;
            };

            const renderPageToCanvas = (page) => html2canvas(page, {
                scale: 1.5,
                useCORS: true,
                backgroundColor: '#ffffff',
                scrollX: 0,
                scrollY: 0,
                windowWidth: 920,
                width: 920,
                x: 0,
                y: 0,
                ignoreElements: (el) => el.classList.contains('no-pdf') || el.classList.contains('no-print'),
            });

            const insertApprovalRowBreaks = (clone) => {
                clone.querySelectorAll('.auto-page-break').forEach(el => el.remove());

                const pageHeightMm = 279.4;
                const pageWidthMm = 215.9;
                const marginTopMm = 24;
                const marginRightMm = 0;
                const marginBottomMm = 38;
                const marginLeftMm = 20.32;
                const contentWidthMm = pageWidthMm - marginLeftMm - marginRightMm;
                const contentHeightMm = pageHeightMm - marginTopMm - marginBottomMm;
                const pxPerMm = clone.getBoundingClientRect().width / contentWidthMm;
                const usablePageHeight = (contentHeightMm * pxPerMm) - 22;

                let pageStart = 0;
                const breakableRows = Array.from(clone.querySelectorAll('.approval-card'));

                breakableRows.forEach((row) => {
                    const rowTop = row.offsetTop;
                    const rowHeight = row.offsetHeight;

                    if (rowHeight >= usablePageHeight) {
                        return;
                    }

                    if ((rowTop - pageStart + rowHeight) > usablePageHeight) {
                        const pageBreak = document.createElement('div');
                        pageBreak.className = 'auto-page-break html2pdf__page-break';
                        row.parentNode.insertBefore(pageBreak, row);
                        pageStart = rowTop;
                    }
                });
            };

            const generatePdf = async (onProgress) => {
                if (cachedPdfBlob) {
                    return cachedPdfBlob;
                }

                if (onProgress) onProgress('Creating export node...');
                const { wrapper, clone } = createExportNode();

                if (onProgress) onProgress('Waiting for layout to settle...');
                await new Promise(resolve => setTimeout(resolve, 300));

                if (onProgress) onProgress('Loading logos...');
                const logos = await getLogos();

                if (onProgress) onProgress('Loading fonts...');
                const fonts = await getFonts();

                if (onProgress) onProgress('Checking page breaks...');
                insertApprovalRowBreaks(clone);
                await new Promise(resolve => setTimeout(resolve, 50));

                if (onProgress) onProgress('Rendering PDF...');
                const options = {
                    margin: [24, 0, 38, 20.32],
                    filename: button.dataset.filename || 'sarf-tracer.pdf',
                    image: { type: 'jpeg', quality: 0.92 },
                    html2canvas: {
                        scale: 1.5,
                        useCORS: true,
                        backgroundColor: '#ffffff',
                        scrollX: -window.scrollX,
                        scrollY: -window.scrollY,
                        windowWidth: 920,
                        width: 920,
                        x: 0,
                        y: 0,
                        ignoreElements: (el) => el.classList.contains('no-pdf') || el.classList.contains('no-print'),
                    },
                    jsPDF: {
                        unit: 'mm',
                        format: 'letter',
                        orientation: 'portrait',
                        compress: true,
                    },
                    pagebreak: {
                        mode: ['css', 'legacy'],
                        before: ['.auto-page-break'],
                        avoid: ['.approval-card', '.approval-group-title', '.print-details'],
                    },
                };

                return new Promise((resolve, reject) => {
                    html2pdf()
                        .set(options)
                        .from(clone)
                        .toPdf()
                        .get('pdf')
                        .then((pdf) => {
                            if (onProgress) onProgress('Adding headers and footers...');
                            
                            if (fonts) {
                                fonts.forEach(f => {
                                    if (f && f.base64) {
                                        const filename = `${f.name}-${f.style}.ttf`;
                                        pdf.addFileToVFS(filename, f.base64);
                                        pdf.addFont(filename, f.name, f.style);
                                    }
                                });
                            }

                            addPageChrome(pdf, {
                                arellano: logos[0],
                                auOsa: logos[1],
                                osa: logos[2],
                                globe: logos[3],
                                gmail: logos[4],
                                call: logos[5],
                                fb: logos[6],
                                insta: logos[7],
                            }, fonts);
                            const blob = pdf.output('blob');
                            cachedPdfBlob = blob;
                            wrapper.remove();
                            resolve(blob);
                        })
                        .catch((err) => {
                            wrapper.remove();
                            reject(err);
                        });
                });
            };

            const openPreview = async () => {
                if (typeof html2pdf === 'undefined') {
                    window.print();
                    return;
                }

                modal.style.display = 'flex';
                loadingText.textContent = 'Generating PDF...';

                try {
                    const blob = await generatePdf((message) => {
                        loadingText.textContent = message;
                    });

                    const blobUrl = URL.createObjectURL(blob);
                    
                    // Close the modal cleanly
                    closeModal();
                    
                    // Open the PDF blob in a new browser window/tab
                    window.open(blobUrl, '_blank');
                } catch (error) {
                    console.error('PDF generation failed:', error);
                    loadingText.textContent = 'Failed to generate PDF';
                    setTimeout(closeModal, 1500);
                }
            };

            const closeModal = () => {
                modal.style.display = 'none';
                cachedPdfBlob = null;
            };

            button.addEventListener('click', openPreview);
            closeBtn.addEventListener('click', closeModal);

            // Close modal on backdrop click
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    closeModal();
                }
            });

            // Close modal on Escape key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && modal.style.display === 'flex') {
                    closeModal();
                }
            });
        });
    </script>
@endpush