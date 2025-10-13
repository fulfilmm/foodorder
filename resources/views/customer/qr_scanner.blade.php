{{-- <!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Scan Table QR</title>
  <script src="https://unpkg.com/html5-qrcode"></script>
  <style>
    body { text-align: center; padding: 20px; font-family: sans-serif; }
    #qr-reader { width: 300px; margin: 0 auto; }
  </style>
</head>
<body>
  <h2>Scan Table QR</h2>
  <div id="qr-reader"></div>

  <script>
    function onScanSuccess(decodedText, decodedResult) {
      console.log(`Scanned: ${decodedText}`);
      // Redirect to validation route with scanned table name
      window.location.href = `/customer/die-in/validate?table=${encodeURIComponent(decodedText)}`;
    }

    const html5QrCode = new Html5Qrcode("qr-reader");
    html5QrCode.start(
      { facingMode: "environment" }, // use back camera
      { fps: 10, qrbox: 250 },
      onScanSuccess
    );
  </script>
</body>
</html> --}}
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Scan Table QR</title>
  <script src="https://unpkg.com/html5-qrcode"></script>
  <style>
    body { text-align:center; padding:20px; font-family: system-ui, -apple-system, Segoe UI, Roboto, "Helvetica Neue", Arial, "Noto Sans", "Apple Color Emoji", "Segoe UI Emoji"; }
    #qr-reader { width: 320px; margin: 16px auto; }
    .alert { margin: 10px auto 0; padding: 10px 12px; border-radius: 8px; width: min(480px, 92vw); text-align: left; border: 1px solid transparent; }
    .alert.error   { background:#fee2e2; color:#991b1b; border-color:#fecaca; }
    .alert.success { background:#dcfce7; color:#166534; border-color:#bbf7d0; }
    .hidden { display: none; }
    .muted { color:#6b7280; font-size: 12px; }
    .spinner { display:inline-block; width:14px; height:14px; border:2px solid #999; border-top-color: transparent; border-radius:50%; animation:spin 0.7s linear infinite; vertical-align: middle; margin-left:6px; }
    @keyframes spin { to { transform: rotate(360deg); } }
  </style>
</head>
<body>
  <h2>Scan Table QR</h2>

  {{-- Server-side flash messages (from controller redirects) --}}
  @if(session('error'))
    <div class="alert error" id="flash-error">
      {{ session('error') }}
    </div>
  @endif

  @if(session('success'))
    <div class="alert success" id="flash-success">
      {{ session('success') }}
    </div>
  @endif

  {{-- Client-side error placeholder --}}
  <div class="alert error hidden" id="client-error"></div>

  <div id="qr-reader"></div>
  <div class="muted">Point your camera at the table QR</div>

  <script>
    // Helper to show client-side errors
    function showClientError(msg) {
      const el = document.getElementById('client-error');
      el.textContent = msg;
      el.classList.remove('hidden');
    }

    let redirecting = false;

    function onScanSuccess(decodedText/*, decodedResult*/) {
      if (redirecting) return; // prevent double redirects on rapid reads
      redirecting = true;

      // Optional UX: show a tiny spinner in the flash area
      const ok = document.getElementById('flash-success');
      if (ok) ok.remove();
      let el = document.getElementById('client-error');
      el.classList.add('hidden');

      // Navigate to your validation route (controller will allow/deny & flash message)
      const url = `/customer/die-in/validate?table=${encodeURIComponent(decodedText)}`;
      // Small visual feedback
      const wait = document.createElement('div');
      wait.className = 'alert success';
      wait.innerHTML = `Checking table… <span class="spinner"></span>`;
      document.body.insertBefore(wait, document.getElementById('qr-reader'));
      window.location.href = url;
    }

    function onScanFailure(error) {
      // This callback can be noisy; surface only meaningful messages if you want.
      // Example: ignore common "QR decode failed" chatter, show permission/device errors.
      if (typeof error === 'string' && error.toLowerCase().includes('not found')) return;
      if (typeof error === 'string' && error.toLowerCase().includes('qr code parse error')) return;
      // Uncomment to see all failures:
      // showClientError(`Scan error: ${error}`);
    }

    // Start scanner with graceful error handling
    (async () => {
      try {
        const html5QrCode = new Html5Qrcode("qr-reader");
        // Prefer environment camera
        await html5QrCode.start(
          { facingMode: "environment" },
          { fps: 10, qrbox: 250 },
          onScanSuccess,
          onScanFailure
        );
      } catch (err) {
        // Camera denied or no camera
        showClientError(
          (err && err.message)
            ? `Camera error: ${err.message}. Please allow camera access and reload.`
            : 'Camera unavailable. Please allow camera access and reload.'
        );
      }
    })();
  </script>
</body>
</html>
