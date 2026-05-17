<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Print Attachments - {{ $activity->code }}</title>
    <style>
        html,
        body {
            margin: 0;
            background: #111827;
        }

        .document-frame {
            width: 100%;
            height: 100vh;
            border: 0;
            background: #fff;
            display: block;
            page-break-after: always;
        }

        @media print {
            body {
                background: #fff;
            }

            .document-frame {
                height: 100vh;
            }
        }
    </style>
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
