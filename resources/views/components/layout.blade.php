{{-- resources/views/components/layout.blade.php --}}
@props(['title' => 'Monitoramento'])

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="icon" href="{{ asset('images/icone.png') }}" type="image/png">
    <style>
        .table-responsive {
            max-height: 80vh;
        }
        .table thead th {
            position: sticky;
            top: 0;
            background-color: #212529;
            color: white;
            z-index: 10;
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