@props(['title' => 'Monitoramento'])
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="icon" href="{{ asset('images/icone.png') }}" type="image/png">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.5.1/dist/chart.umd.min.js"></script>
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e8ecf1 100%);
            min-height: 100vh;
        }
        .chart-container {
            position: relative;
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 24px;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .chart-container:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 12px rgba(0, 0, 0, 0.15);
        }
        .chart-wrapper {
            position: relative;
            height: 500px;
        }
        @media (max-width: 768px) {
            .chart-wrapper {
                height: 350px;
            }
        }
        .chart-header {
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .chart-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #2c3e50;
            margin: 0;
        }
        .chart-subtitle {
            font-size: 0.875rem;
            color: #6c757d;
            margin: 0;
        }
        .stats-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
            transition: transform 0.2s;
        }
        .stats-card:hover {
            transform: translateY(-2px);
        }
        .stat-value {
            font-size: 1.75rem;
            font-weight: 700;
            color: #2c3e50;
        }
        .stat-label {
            font-size: 0.875rem;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .sidebar {
            position: fixed;
            top: 0;
            left: -300px;
            width: 300px;
            height: 100vh;
            background: white;
            box-shadow: 4px 0 12px rgba(0, 0, 0, 0.15);
            transition: left 0.3s ease;
            z-index: 1002;
            overflow-y: auto;
        }
        .sidebar.active {
            left: 0;
        }
        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid #e9ecef;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .sidebar-content {
            padding: 20px;
        }
        .toggle-sidebar-btn {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1001;
            background: white;
            border: none;
            border-radius: 8px;
            padding: 12px 16px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 16px;
        }
        .toggle-sidebar-btn:hover {
            transform: translateX(5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.25);
        }
        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1001;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s, visibility 0.3s;
        }
        .sidebar-overlay.active {
            opacity: 1;
            visibility: visible;
        }
        .floating-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            padding: 15px 80px;
            transition: transform 0.3s;
        }
        .floating-header.hidden {
            transform: translateY(-100%);
        }
        .main-content {
            padding-top: 80px;
        }
        .stats-container {
            margin-bottom: 24px;
        }
        .stats-scroll {
            display: flex;
            gap: 15px;
            overflow-x: auto;
            padding-bottom: 10px;
            scrollbar-width: thin;
            scrollbar-color: #cbd5e0 #f1f5f9;
        }
        .stats-scroll::-webkit-scrollbar {
            height: 8px;
        }
        .stats-scroll::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 4px;
        }
        .stats-scroll::-webkit-scrollbar-thumb {
            background: #cbd5e0;
            border-radius: 4px;
        }
        .stats-scroll::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        .stats-scroll .stats-card {
            min-width: 220px;
            flex-shrink: 0;
        }
        @media (max-width: 768px) {
            .sidebar {
                width: 85%;
                left: -85%;
            }
            .floating-header {
                padding: 15px 20px;
            }
            .main-content {
                padding-top: 70px;
            }
            .stats-scroll .stats-card {
                min-width: 180px;
            }
        }
    </style>
</head>
<body>

    {{-- O $slot é onde o conteúdo da sua index.blade.php será injetado --}}
    {{ $slot }}

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Aguarda 3 segundos e depois remove a mensagem de sucesso
        setTimeout(function() {
            let alert = document.getElementById('success-alert');
            if (alert) {
                alert.style.display = 'none';
            }
        }, 3000); // 3000 milissegundos = 3 segundos
    </script>
</body>
</html>