@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Welcome to the Dashboard!</h1>
    <p>This is the dashboard page with a welcome message.</p>

    <!-- Tempat untuk menampilkan Swagger UI -->
    <div id="swagger-ui"></div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/swagger-ui-dist/3.52.5/swagger-ui-bundle.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/swagger-ui-dist/3.52.5/swagger-ui-standalone-preset.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/swagger-ui-dist/3.52.5/swagger-ui.css" />

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ui = SwaggerUIBundle({
            url: "/swagger/v1/swagger.json", // Ganti dengan path ke file Swagger JSON Anda
            dom_id: '#swagger-ui', // Tempat di halaman untuk menampilkan Swagger UI
            deepLinking: true,
            presets: [
                SwaggerUIBundle.presets.apis,
                SwaggerUIStandalonePreset
            ],
            layout: "BaseLayout"
        });
    });
</script>
@endsection
