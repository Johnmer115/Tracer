<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Print {{ $document->original_filename }}</title>
    <link rel="stylesheet" href="{{ asset('css/sarf-print.css') }}">

</head>
<body>
    <iframe src="{{ $url }}" title="{{ $document->original_filename }}"></iframe>
    <script>
        window.addEventListener('load', () => {
            setTimeout(() => window.print(), 500);
        });
    </script>
</body>
</html>
