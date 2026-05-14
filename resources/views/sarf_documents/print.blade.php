<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Print {{ $document->original_filename }}</title>
    <style>
        html,
        body {
            height: 100%;
            margin: 0;
            background: #111827;
        }

        iframe {
            width: 100%;
            height: 100%;
            border: 0;
            background: #fff;
        }
    </style>
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
