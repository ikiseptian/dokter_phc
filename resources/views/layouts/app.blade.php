<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>App - Dashboard</title>
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
            <li><a href="{{ url('/item_test') }}">Item_Test</a></li>
            <li><a href="{{ url('/lab_trans') }}">Lab_Trans</a></li>
            <li><a href="{{ url('/lab_trans_detail') }}">Lab_Trans_Detail</a></li>
            <li><a href="{{ url('/lab_trans_other') }}">Lab_Trans_Other</a></li>
            <li><a href="{{ url('/patient') }}">Patient</a></li>
           
            <!-- Additional Links -->
            
        </ul>
    </div>

    <!-- Main Content -->
    <div id="main-content">
        @yield('content') <!-- Konten utama lainnya akan di-render di sini -->
    </div>
</body>
</html>
