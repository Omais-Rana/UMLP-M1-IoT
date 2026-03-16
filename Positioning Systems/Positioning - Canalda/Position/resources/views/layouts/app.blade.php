<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UFR STGI - Indoor Positioning Systems</title>
    <style>
        :root {
            --primary: #0984e3;
            --secondary: #2d3436;
            --accent: #d63031;
            --bg: #f0f2f5;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--bg);
            margin: 0;
            padding: 0;
        }

        /* Navbar Styling */
        nav {
            background: var(--secondary);
            padding: 0 50px;
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        .logo {
            color: white;
            font-weight: bold;
            font-size: 1.5rem;
            letter-spacing: 1px;
        }

        .nav-links {
            list-style: none;
            display: flex;
            gap: 30px;
        }

        .nav-links a {
            color: #dfe6e9;
            text-decoration: none;
            font-weight: 500;
            transition: 0.3s;
            padding: 10px 15px;
            border-radius: 5px;
        }

        .nav-links a:hover {
            color: white;
            background: rgba(255, 255, 255, 0.1);
        }

        .nav-links a.active {
            color: white;
            border-bottom: 2px solid var(--primary);
        }

        .container {
            padding: 40px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 900px;
        }
    </style>
    @yield('styles')
</head>

<body>

    <nav>
        <div class="logo">TD Indoor Positioning</div>
        <ul class="nav-links">
            <li><a href="{{ route('dashboard') }}"
                    class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a></li>
            <li><a href="{{ route('lateration') }}"
                    class="{{ request()->routeIs('lateration') ? 'active' : '' }}">N-Lateration</a></li>
            <li><a href="{{ route('fingerprint') }}"
                    class="{{ request()->routeIs('fingerprint') ? 'active' : '' }}">Fingerprinting</a></li>
            <li><a href="{{ route('markov') }}" class="{{ request()->routeIs('markov') ? 'active' : '' }}">Markov
                    Model</a></li>
        </ul>
    </nav>

    <div class="container">
        <div class="card">
            @yield('content')
        </div>
    </div>

    @yield('scripts')
</body>

</html>
