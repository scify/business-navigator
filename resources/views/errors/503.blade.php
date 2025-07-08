<html lang="en">
<head>
    <title>ILT 503</title>
    <style>
        * {
            margin: 0; padding: 0;
        }
        body {
            background-color: #f0d151;
            color: #000;
            font-family: ui-sans-serif, system-ui, sans-serif;
        }
        .container-xxl {
            max-width: 60ch;
            margin: 0 auto;
            padding-inline: 1rem;
        }
        .row {
            display: flex;
            min-height: 100dvh;
            align-items: center;
        }
        h1 {
            font-size: 5rem;
        }
        p {
            margin-block: 1rem;
        }
        .lead {
            font-size: 1.25em;
        }
        .text-center {
            text-align: center;
        }
        .small {
            font-size: 0.875em;
        }
    </style>
</head>
<body>
    <main>
        <section id="section-error" class="section-error">
            <div class="container-xxl">
                <div class="row">
                    <div class="col">
                        <div>
                            <h1 class="text-center">503</h1>
                            <p class="lead text-center">
                                <strong>{{ $exception->getMessage() }}</strong><br>
                                It seems that the server is down for scheduled maintenance.
                            </p>
                        </div>
                        <div class="pt-5">
                            <p class="small text-center">
                                Please note that this error page is under construction
                                and it should probably be far more colorful than it is.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
