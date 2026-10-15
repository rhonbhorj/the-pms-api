<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>PGW API Documentation</title>
<link rel="shortcut icon" href="<?= base_url('/assets/img/ngsi.png') ?>">
  <link rel="stylesheet" type="text/css" href="https://unpkg.com/swagger-ui-dist/swagger-ui.css" />
  <style>
    body {
      margin: 0;
      padding: 0;
      
    }

    .url {
  display: none !important;
}

.swagger-ui .info .title small,
.swagger-ui .info .version-stamp {
    display: none !important;
}
  </style>
</head>
<body>
  <div id="swagger-ui"></div>
    <script src="https://unpkg.com/swagger-ui-dist/swagger-ui-bundle.js"></script>
  <script src="https://unpkg.com/swagger-ui-dist/swagger-ui-standalone-preset.js"></script>
  <script>
    window.onload = function () {
      SwaggerUIBundle({
      
        url: "<?= base_url('assets/swagger/docs/NGSI_PGW_PAYIN.json'); ?>",
        
        dom_id: '#swagger-ui',
        deepLinking: true,
      });
    };
  </script>


    <!-- Disable Developer Tools -->
    <script>
    // Simple protection - block shortcuts only
    (function() {
        // Disable right-click
        document.addEventListener('contextmenu', function(e) {
            e.preventDefault();
            return false;
        });

        // Block keyboard shortcuts for developer tools
        document.addEventListener('keydown', function(e) {
            // F12
            if (e.keyCode === 123 || e.key === 'F12') {
                e.preventDefault();
                return false;
            }
            // Ctrl+Shift+I
            if (e.ctrlKey && e.shiftKey && (e.keyCode === 73 || e.key === 'I')) {
                e.preventDefault();
                return false;
            }
            // Ctrl+Shift+J
            if (e.ctrlKey && e.shiftKey && (e.keyCode === 74 || e.key === 'J')) {
                e.preventDefault();
                return false;
            }
            // Ctrl+U (View Source)
            if (e.ctrlKey && e.keyCode === 85) {
                e.preventDefault();
                return false;
            }
            // Ctrl+Shift+C
            if (e.ctrlKey && e.shiftKey && (e.keyCode === 67 || e.key === 'C')) {
                e.preventDefault();
                return false;
            }
        }, true);
    })();
// </script>
</body>
</html>