<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>API Documentation</title>
<link rel="shortcut icon" href="<?= base_url('/assets/img/ngsi.png') ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body {
      margin: 0;
      padding: 0;
    }

    #redoc-container {
      height: 100vh;
      
    }


  </style>
</head>
<body>

  <div id="redoc-container">
  
  </div>

  <!-- Load ReDoc -->
  <script src="https://cdn.redoc.ly/redoc/latest/bundles/redoc.standalone.js"></script>

  <!-- Initialize ReDoc-->
  <script>
    Redoc.init(
      '<?= base_url("assets/swagger/docs/NGSI_PGW_PAYIN.json"); ?>', // CI3 dynamic URL
      {
        expandResponses: "200,400",
        scrollYOffset: 50,
        hideDownloadButton: false,
        theme: {
          typography: {
            fontSize: "14px",
            fontFamily: "Arial, sans-serif",
          },
          colors: {
            primary: { main: "#0C61A6" }
          }
        }
      },
      document.getElementById('redoc-container'),
      function(err) {
        if (err) {
          console.error("ReDoc failed to load:", err);
        }
      }
    );
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
</script>
</body>
</html>
