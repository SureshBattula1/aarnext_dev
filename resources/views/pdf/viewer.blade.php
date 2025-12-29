<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Viewer</title>
    <style>
        html, body { margin: 0; padding: 0; height: 100%; overflow: hidden; }
        object { width: 100%; height: 100%; border: none; }
    </style>
</head>
<body>

<object 
    data="{{ $pdfUrl }}#toolbar=0&navpanes=0&scrollbar=0" 
    type="application/pdf">
    <p>Your browser does not support PDFs. 
        <a href="{{ $pdfUrl }}">Download PDF</a>
    </p>
</object>

<script>
    // Disable right-click
    document.addEventListener('contextmenu', function(e) {
        e.preventDefault();
        alert("Download is disabled for this PDF.");
    });

    // Disable Ctrl+S / Cmd+S
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && (e.key === 's' || e.key === 'S')) {
            e.preventDefault();
            alert("Download is disabled for this PDF.");
        }
    });
</script>

</body>
</html>
