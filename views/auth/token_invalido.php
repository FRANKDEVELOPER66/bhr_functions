<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>VEHICULOS BHR — Enlace inválido</title>
    <link rel="stylesheet" href="<?= asset('build/styles.css') ?>">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #0f1117;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', sans-serif;
        }

        .card {
            background: #1a1d27;
            border: 1px solid #2e3347;
            border-radius: 18px;
            padding: 2.5rem;
            text-align: center;
            max-width: 400px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, .5);
        }
    </style>
</head>

<body>
    <div class="card">
        <i class="bi bi-x-circle-fill" style="font-size:3rem;color:#e05252;display:block;margin-bottom:1rem;"></i>
        <div style="font-family:'Rajdhani',sans-serif;font-size:1.2rem;font-weight:700;color:#e8eaf0;margin-bottom:.5rem;">
            Enlace inválido o expirado
        </div>
        <div style="font-size:.82rem;color:#7c8398;margin-bottom:1.5rem;">
            Este enlace ya fue usado o expiró. Solicita uno nuevo desde el login.
        </div>
        <a href="<?= $_ENV['APP_NAME'] ? '/' . $_ENV['APP_NAME'] : '' ?>/"
            style="display:inline-flex;align-items:center;gap:.5rem;
        background:linear-gradient(135deg,#e8b84b,#d4a032);border-radius:10px;
        color:#0f1117;padding:.75rem 1.5rem;font-family:'Rajdhani',sans-serif;
        font-weight:700;font-size:.95rem;text-decoration:none;">
            <i class="bi bi-arrow-left"></i> Volver al login
        </a>
    </div>
</body>

</html>