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
        .floating-panel {
            position: fixed;
            top: 80px;
            right: -400px;
            width: 380px;
            max-height: calc(100vh - 100px);
            background: white;
            border-radius: 12px 0 0 12px;
            box-shadow: -4px 0 12px rgba(0, 0, 0, 0.15);
            transition: right 0.3s ease;
            z-index: 1000;
            overflow-y: auto;
        }
        .floating-panel.active {
            right: 0;
        }
        .floating-panel-content {
            padding: 20px;
        }
        .toggle-panel-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1001;
            background: white;
            border: none;
            border-radius: 50%;
            width: 56px;
            height: 56px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }
        .toggle-panel-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.25);
        }
        .stats-overlay {
            position: fixed;
            bottom: 20px;
            left: 20px;
            right: 20px;
            background: white;
            border-radius: 12px;
            padding: 15px;
            box-shadow: 0 -4px 12px rgba(0, 0, 0, 0.15);
            z-index: 999;
            max-height: 150px;
            overflow-x: auto;
            overflow-y: hidden;
            transition: max-height 0.3s ease;
        }
        .stats-overlay.collapsed {
            max-height: 0;
            padding: 0;
        }
        .stats-overlay .stats-scroll {
            display: flex;
            gap: 15px;
            overflow-x: auto;
            white-space: nowrap;
            padding-bottom: 10px;
        }
        .stats-overlay .stats-card {
            min-width: 200px;
            flex-shrink: 0;
        }
        .toggle-stats-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1001;
            background: white;
            border: none;
            border-radius: 50%;
            width: 48px;
            height: 48px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }
        .toggle-stats-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.25);
        }
        @media (max-width: 768px) {
            .floating-panel {
                width: 90%;
                right: -100%;
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