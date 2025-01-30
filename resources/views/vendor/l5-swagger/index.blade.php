<!-- resources/views/vendor/l5-swagger/index.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{config('l5-swagger.documentations.'.$documentation.'.api.title')}}</title>
    <link rel="stylesheet" type="text/css" href="{{ l5_swagger_asset($documentation, 'swagger-ui.css') }}">
    <link rel="icon" type="image/png" href="{{ l5_swagger_asset($documentation, 'favicon-32x32.png') }}" sizes="32x32"/>
    <link rel="icon" type="image/png" href="{{ l5_swagger_asset($documentation, 'favicon-16x16.png') }}" sizes="16x16"/>
    <style>
        html {
            box-sizing: border-box;
            overflow: -moz-scrollbars-vertical;
            overflow-y: scroll;
        }
        *, *:before, *:after {
            box-sizing: inherit;
        }
        body {
            margin: 0;
            background: #fafafa;
            display: flex;
        }

        /* Sidebar Styles */
        #sidebar {
            width: 250px;
            background-color: #2c3e50;
            color: white;
            padding-top: 20px;
            position: fixed;
            height: 100%;
        }
        #sidebar ul {
            list-style-type: none;
            padding-left: 0;
        }
        #sidebar ul li {
            padding: 10px 15px;
        }
        #sidebar ul li a {
            color: white;
            text-decoration: none;
        }
        #sidebar ul li a:hover {
            background-color: #34495e;
        }

        /* Main Content Styles */
        #main-content {
            margin-left: 250px;
            width: 100%;
            padding: 20px;
        }

        /* Dark Mode Styles */
        @if(config('l5-swagger.defaults.ui.display.dark_mode'))
            body#dark-mode, #dark-mode .scheme-container {
                background: #1b1b1b;
            }
            /* Dark Mode Styling For Sidebar */
            #sidebar {
                background-color: #34495e;
            }
            #sidebar ul li a {
                color: #e7e7e7;
            }
            #sidebar ul li a:hover {
                background-color: #1abc9c;
            }
        @endif
    </style>
</head>
<body @if(config('l5-swagger.defaults.ui.display.dark_mode')) id="dark-mode" @endif>
    <!-- Sidebar -->
    <div id="sidebar">
        <ul>
            <li><a href="{{ url('/dashboard') }}">Dashboard</a></li> <!-- Link ke Dashboard -->
            <li><a href="{{ url('/api/documentation') }}">API Documentation</a></li>
            <li><a href="#">Settings</a></li>
            <li><a href="#">Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div id="main-content">
        <!-- Button ke Dashboard -->
       

        <!-- Swagger UI -->
        <div id="swagger-ui"></div>
    </div>

    <script src="{{ l5_swagger_asset($documentation, 'swagger-ui-bundle.js') }}"></script>
    <script src="{{ l5_swagger_asset($documentation, 'swagger-ui-standalone-preset.js') }}"></script>
    <script>
    window.onload = function() {
        // Build a system
        const ui = SwaggerUIBundle({
            dom_id: '#swagger-ui',
            url: "{!! $urlToDocs !!}",
            operationsSorter: {!! isset($operationsSorter) ? '"' . $operationsSorter . '"' : 'null' !!},
            configUrl: {!! isset($configUrl) ? '"' . $configUrl . '"' : 'null' !!},
            validatorUrl: {!! isset($validatorUrl) ? '"' . $validatorUrl . '"' : 'null' !!},
            oauth2RedirectUrl: "{{ route('l5-swagger.'.$documentation.'.oauth2_callback', [], $useAbsolutePath) }}",

            requestInterceptor: function(request) {
                request.headers['X-CSRF-TOKEN'] = '{{ csrf_token() }}';
                return request;
            },

            presets: [
                SwaggerUIBundle.presets.apis,
                SwaggerUIStandalonePreset
            ],

            plugins: [
                SwaggerUIBundle.plugins.DownloadUrl
            ],

            layout: "StandaloneLayout",
            docExpansion : "{!! config('l5-swagger.defaults.ui.display.doc_expansion', 'none') !!}",
            deepLinking: true,
            filter: {!! config('l5-swagger.defaults.ui.display.filter') ? 'true' : 'false' !!},
            persistAuthorization: "{!! config('l5-swagger.defaults.ui.authorization.persist_authorization') ? 'true' : 'false' !!}",
        });

        window.ui = ui;

        @if(in_array('oauth2', array_column(config('l5-swagger.defaults.securityDefinitions.securitySchemes'), 'type')))
            ui.initOAuth({
                usePkceWithAuthorizationCodeGrant: "{!! (bool)config('l5-swagger.defaults.ui.authorization.oauth2.use_pkce_with_authorization_code_grant') !!}"
            })
        @endif
    }
</script>

</body>
</html>
