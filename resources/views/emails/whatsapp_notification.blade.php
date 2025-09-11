<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>$emailData Notification</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'Noto Sans', sans-serif;
            color: #111827;
        }

        .container {
            max-width: 640px;
            margin: 0 auto;
            padding: 24px;
            background: #ffffff;
        }

        .muted {
            color: #6B7280;
            font-size: 12px;
        }

        .pre {
            white-space: pre-wrap;
            word-wrap: break-word;
        }

        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 9999px;
            background: #EEF2FF;
            color: #3730A3;
            font-size: 12px;
        }

        .kv {
            margin: 0;
        }

        .kv dt {
            font-weight: 600;
        }

        .kv dd {
            margin: 0 0 8px 0;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Payment Success Notification</h2>
        <p class="muted">Type: <span class="badge">{{ $type }}</span></p>

        <h3>Summary</h3>
        <dl class="kv">
            <!-- <dt>Chat ID</dt>
            <dd>{{ $data['chatId'] ?? '—' }}</dd> -->

            @if($type === 'sendImage')
            <dt>Image URL</dt>
            <dd><a href="{{ $data['url'] ?? ($data['file']['url'] ?? '#') }}">{{ $data['url'] ?? ($data['file']['url'] ?? '—') }}</a></dd>
            <dt>Caption</dt>
            <dd class="pre">{{ $data['caption'] ?? '—' }}</dd>
            @else
            <!-- <dt>Link</dt> -->
            <dd><a href="{{ $data['url'] ?? '#' }}">{{ $data['url'] ?? '—' }}</a></dd>
            <!-- <dt>Text</dt> -->
            <dd class="pre">{{ $data['text'] ?? '—' }}</dd>
            @if(!empty($data['title']))
            <dt>Title</dt>
            <dd>{{ $data['title'] }}</dd>
            @endif
            @if(!empty($data['description']))
            <dt>Description</dt>
            <dd class="pre">{{ $data['description'] }}</dd>
            @endif
            @endif
        </dl>

        @if($type === 'paymentSuccess' && !empty($data['participant']['id']))
        <h3>E-ticket QR</h3>
        <p class="muted">Tunjukkan QR ini saat check-in.</p>
        <p>
            <img src="{{ url('/participants/' . $data['participant']['id'] . '.png') }}" alt="QR Code"
                style="max-width:260px;border:1px solid #e5e7eb;padding:8px;border-radius:8px;" />
        </p>
        <p>
            <a href="{{ url('/participants/' . $data['participant']['id'] . '.png') }}" target="_blank">Unduh QR</a>
        </p>
        @endif

        <hr />
        <!-- <p class="muted">This email was triggered automatically after a WhatsApp API call from the application.</p> -->
    </div>
</body>

</html>