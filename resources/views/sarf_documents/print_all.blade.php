<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Print Attachments - {{ $activity->code }}</title>
    <link rel="stylesheet" href="{{ asset('css/sarf-print-all.css') }}">

</head>
<body>
    @foreach($documents as $document)
        <iframe
            class="document-frame"
            src="{{ route($routeName, ['document' => $document, 'print' => 1]) }}"
            title="{{ $document->original_filename }}"></iframe>
    @endforeach

    <script>
        window.addEventListener('load', () => {
            setTimeout(() => window.print(), 900);
        });
    </script>
</body>
</html>
