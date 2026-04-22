<!DOCTYPE html>
<html lang="en" data-base="<?= $_ENV['APP_NAME'] ? '/' . $_ENV['APP_NAME'] : '' ?>" data-rol="<?= $_SESSION['auth_rol'] ?? '' ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="<?= asset('build/js/app.js') ?>"></script>
    <link rel="shortcut icon" href="<?= asset('images/BHR.png') ?>" type="image/x-icon">
    <link rel="stylesheet" href="<?= asset('build/styles.css') ?>">
    <title>VEHICULOS BHR</title>
    <style>
        /* ── LOADER BHR ── */
        #bhr-loader {
            position: fixed;
            inset: 0;
            background: rgba(15, 17, 23, .95);
            backdrop-filter: blur(10px);
            z-index: 9999;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 2rem;
            opacity: 0;
            pointer-events: none;
            transition: opacity .3s ease;
        }

        #bhr-loader.visible {
            opacity: 1;
            pointer-events: all;
        }

        .loader-wheel-wrap {
            position: relative;
            width: 110px;
            height: 110px;
        }

        .loader-wheel-outer {
            position: absolute;
            inset: 0;
            border-radius: 50%;
            border: 4px solid transparent;
            border-top-color: #e8b84b;
            border-right-color: rgba(232, 184, 75, .3);
            border-bottom-color: rgba(232, 184, 75, .1);
            animation: wheelSpin .9s linear infinite;
        }

        .loader-wheel-svg {
            position: absolute;
            inset: 8px;
            animation: wheelSpin .9s linear infinite;
        }

        .loader-wheel-inner {
            position: absolute;
            inset: 14px;
            border-radius: 50%;
            border: 3px solid transparent;
            border-left-color: #d4a032;
            border-bottom-color: rgba(212, 160, 50, .3);
            animation: wheelSpin .7s linear infinite reverse;
        }

        .loader-wheel-hub {
            position: absolute;
            inset: 38px;
            border-radius: 50%;
            background: radial-gradient(circle at 40% 35%, #3a3d4e, #1a1d27);
            border: 2px solid #e8b84b;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #e8b84b;
            font-size: .85rem;
            z-index: 2;
        }

        @keyframes wheelSpin {
            to {
                transform: rotate(360deg);
            }
        }

        .loader-dirt {
            position: absolute;
            bottom: -8px;
            left: -40px;
            width: 190px;
            height: 60px;
            pointer-events: none;
            overflow: visible;
        }

        .dirt-particle {
            position: absolute;
            border-radius: 50%;
            background: #c8a44a;
            opacity: 0;
            animation: dirtFly var(--dur) ease-out var(--delay) infinite;
        }

        .dirt-particle:nth-child(1) {
            width: 10px;
            height: 7px;
            bottom: 0;
            left: 10px;
            --dur: .8s;
            --delay: 0s;
            --dx: -55px;
            --dy: -35px;
        }

        .dirt-particle:nth-child(2) {
            width: 6px;
            height: 6px;
            bottom: 0;
            left: 5px;
            --dur: 1.0s;
            --delay: .1s;
            --dx: -65px;
            --dy: -20px;
            background: #a0742a;
        }

        .dirt-particle:nth-child(3) {
            width: 12px;
            height: 8px;
            bottom: 0;
            left: 15px;
            --dur: .7s;
            --delay: .25s;
            --dx: -48px;
            --dy: -55px;
        }

        .dirt-particle:nth-child(4) {
            width: 5px;
            height: 5px;
            bottom: 0;
            left: 8px;
            --dur: 1.1s;
            --delay: .05s;
            --dx: -70px;
            --dy: -25px;
            background: #b8912e;
        }

        .dirt-particle:nth-child(5) {
            width: 9px;
            height: 6px;
            bottom: 0;
            left: 12px;
            --dur: .85s;
            --delay: .35s;
            --dx: -42px;
            --dy: -65px;
        }

        .dirt-particle:nth-child(6) {
            width: 7px;
            height: 7px;
            bottom: 0;
            left: 6px;
            --dur: .9s;
            --delay: .18s;
            --dx: -60px;
            --dy: -18px;
            background: #907028;
        }

        .dirt-particle:nth-child(7) {
            width: 11px;
            height: 6px;
            bottom: 0;
            left: 18px;
            --dur: .75s;
            --delay: .3s;
            --dx: -50px;
            --dy: -45px;
        }

        .dirt-particle:nth-child(8) {
            width: 4px;
            height: 4px;
            bottom: 0;
            left: 4px;
            --dur: 1.2s;
            --delay: .08s;
            --dx: -75px;
            --dy: -15px;
            background: #c8a44a;
        }

        .dirt-particle:nth-child(9) {
            width: 8px;
            height: 5px;
            bottom: 0;
            left: 14px;
            --dur: .65s;
            --delay: .42s;
            --dx: -45px;
            --dy: -58px;
            background: #a0742a;
        }

        .dirt-particle:nth-child(10) {
            width: 6px;
            height: 6px;
            bottom: 0;
            left: 9px;
            --dur: .95s;
            --delay: .22s;
            --dx: -62px;
            --dy: -32px;
        }

        .dirt-particle:nth-child(11) {
            width: 13px;
            height: 7px;
            bottom: 0;
            left: 16px;
            --dur: .72s;
            --delay: .48s;
            --dx: -38px;
            --dy: -70px;
            background: #b8912e;
        }

        .dirt-particle:nth-child(12) {
            width: 5px;
            height: 5px;
            bottom: 0;
            left: 3px;
            --dur: 1.15s;
            --delay: .12s;
            --dx: -80px;
            --dy: -10px;
            background: #c8a44a;
        }

        .dirt-particle:nth-child(13) {
            width: 9px;
            height: 6px;
            bottom: 0;
            left: 20px;
            --dur: .78s;
            --delay: .38s;
            --dx: -44px;
            --dy: -48px;
        }

        .dirt-particle:nth-child(14) {
            width: 7px;
            height: 4px;
            bottom: 0;
            left: 7px;
            --dur: 1.05s;
            --delay: .55s;
            --dx: -58px;
            --dy: -38px;
            background: #907028;
        }

        .dirt-particle:nth-child(15) {
            width: 15px;
            height: 9px;
            bottom: 0;
            left: 11px;
            --dur: .68s;
            --delay: .28s;
            --dx: -35px;
            --dy: -75px;
            background: #c8a44a;
        }

        .dirt-particle:nth-child(16) {
            width: 4px;
            height: 4px;
            bottom: 0;
            left: 2px;
            --dur: 1.25s;
            --delay: .06s;
            --dx: -85px;
            --dy: -8px;
            background: #b8912e;
        }

        .dirt-particle:nth-child(17) {
            width: 8px;
            height: 5px;
            bottom: 0;
            left: 22px;
            --dur: .82s;
            --delay: .44s;
            --dx: -40px;
            --dy: -60px;
        }

        .dirt-particle:nth-child(18) {
            width: 6px;
            height: 6px;
            bottom: 0;
            left: 13px;
            --dur: .92s;
            --delay: .16s;
            --dx: -68px;
            --dy: -42px;
            background: #a0742a;
        }

        .dirt-particle:nth-child(19) {
            width: 10px;
            height: 7px;
            bottom: 0;
            left: 25px;
            --dur: .70s;
            --delay: .52s;
            --dx: -30px;
            --dy: -80px;
            background: #c8a44a;
        }

        .dirt-particle:nth-child(20) {
            width: 5px;
            height: 5px;
            bottom: 0;
            left: 1px;
            --dur: 1.18s;
            --delay: .32s;
            --dx: -78px;
            --dy: -22px;
            background: #907028;
        }

        @keyframes dirtFly {
            0% {
                transform: translate(0, 0) rotate(0deg);
                opacity: .9;
            }

            60% {
                opacity: .6;
            }

            100% {
                transform: translate(var(--dx), var(--dy)) rotate(120deg);
                opacity: 0;
            }
        }

        .loader-shadow {
            width: 90px;
            height: 10px;
            background: radial-gradient(ellipse, rgba(232, 184, 75, .25) 0%, transparent 70%);
            border-radius: 50%;
            margin-top: -6px;
            animation: shadowPulse .9s ease-in-out infinite alternate;
        }

        @keyframes shadowPulse {
            from {
                transform: scaleX(.8);
                opacity: .4;
            }

            to {
                transform: scaleX(1.1);
                opacity: .8;
            }
        }

        .loader-lines {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: .5rem;
        }

        .loader-titulo {
            font-family: 'Rajdhani', sans-serif;
            font-size: 1.1rem;
            font-weight: 700;
            color: #e8eaf0;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .loader-subtitulo {
            font-size: .78rem;
            color: #7c8398;
            letter-spacing: 1px;
        }

        .loader-bars {
            display: flex;
            align-items: flex-end;
            gap: 5px;
            height: 28px;
        }

        .loader-bars span {
            display: block;
            width: 4px;
            border-radius: 2px;
            animation: loaderBar .8s ease-in-out infinite alternate;
        }

        .loader-bars span:nth-child(1) {
            height: 8px;
            animation-delay: 0s;
            background: #e8b84b;
        }

        .loader-bars span:nth-child(2) {
            height: 16px;
            animation-delay: .1s;
            background: #d4a032;
        }

        .loader-bars span:nth-child(3) {
            height: 24px;
            animation-delay: .2s;
            background: #e8b84b;
        }

        .loader-bars span:nth-child(4) {
            height: 16px;
            animation-delay: .3s;
            background: #d4a032;
        }

        .loader-bars span:nth-child(5) {
            height: 28px;
            animation-delay: .4s;
            background: #e8b84b;
        }

        .loader-bars span:nth-child(6) {
            height: 16px;
            animation-delay: .3s;
            background: #d4a032;
        }

        .loader-bars span:nth-child(7) {
            height: 24px;
            animation-delay: .2s;
            background: #e8b84b;
        }

        .loader-bars span:nth-child(8) {
            height: 12px;
            animation-delay: .1s;
            background: #d4a032;
        }

        .loader-bars span:nth-child(9) {
            height: 8px;
            animation-delay: 0s;
            background: #e8b84b;
        }

        @keyframes loaderBar {
            from {
                transform: scaleY(.4);
                opacity: .5;
            }

            to {
                transform: scaleY(1);
                opacity: 1;
            }
        }

        .loader-progress-wrap {
            width: 220px;
        }

        .loader-progress-track {
            background: #242837;
            border: 1px solid #2e3347;
            border-radius: 20px;
            height: 4px;
            overflow: hidden;
        }

        .loader-progress-bar {
            height: 100%;
            width: 0%;
            background: linear-gradient(90deg, #e8b84b, #d4a032);
            border-radius: 20px;
            animation: loaderProgress 1.8s ease-in-out infinite;
        }

        @keyframes loaderProgress {
            0% {
                width: 0%;
                margin-left: 0;
            }

            50% {
                width: 60%;
                margin-left: 20%;
            }

            100% {
                width: 0%;
                margin-left: 100%;
            }
        }

        .loader-tag {
            font-size: .65rem;
            color: #2e3347;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-top: .4rem;
            text-align: center;
        }

        /* ── CAMPANITA ── */
        @keyframes pulseRed {
            0% {
                box-shadow: 0 0 0 0 rgba(224, 82, 82, .6);
            }

            70% {
                box-shadow: 0 0 0 8px rgba(224, 82, 82, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(224, 82, 82, 0);
            }
        }

        @keyframes shake {

            0%,
            100% {
                transform: rotate(0deg);
            }

            15% {
                transform: rotate(15deg);
            }

            30% {
                transform: rotate(-15deg);
            }

            45% {
                transform: rotate(10deg);
            }

            60% {
                transform: rotate(-10deg);
            }

            75% {
                transform: rotate(5deg);
            }
        }

        .campana-activa {
            animation: shake 0.8s ease-in-out;
            color: #e8b84b !important;
        }

        .btn-notif-activo {
            border-color: rgba(232, 184, 75, .4) !important;
            background: rgba(232, 184, 75, .08) !important;
        }
    </style>
</head>

<body style="background:#0f1117;min-height:100vh;color:#e8eaf0;">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarToggler">
                <span class="navbar-toggler-icon"></span>
            </button>
            <a class="navbar-brand" href="<?= $_ENV['APP_NAME'] ? '/' . $_ENV['APP_NAME'] : '' ?>/">
                <img src="<?= asset('images/BHR.png') ?>" width="55px" alt="BHR">
                INICIO
            </a>
            <div class="collapse navbar-collapse" id="navbarToggler">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0" style="margin:0;">

                    <!-- Control de Vehículos -->
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="bi bi-car-front"></i> Control de Vehículos
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark" style="margin:0;">
                            <li>
                                <a class="dropdown-item nav-link text-white"
                                    href="<?= $_ENV['APP_NAME'] ? '/' . $_ENV['APP_NAME'] : '' ?>/vehiculos">
                                    <i class="bi bi-car-front-fill"></i> Vehículos
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Panel Admin — solo SUPERUSUARIO -->
                    <?php if (($_SESSION['auth_rol'] ?? '') === 'SUPERUSUARIO'): ?>
                        <div class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                <i class="bi bi-shield-lock-fill" style="color:#e8b84b;"></i> Administración
                            </a>
                            <ul class="dropdown-menu dropdown-menu-dark" style="margin:0;">
                                <li>
                                    <a class="dropdown-item nav-link text-white"
                                        href="<?= $_ENV['APP_NAME'] ? '/' . $_ENV['APP_NAME'] : '' ?>/usuarios">
                                        <i class="bi bi-people-fill"></i> Gestión de Usuarios
                                    </a>
                                </li>
                            </ul>
                        </div>
                    <?php endif; ?>

                </ul>

                <!-- Campanita -->
                <?php if (isset($_SESSION['auth_user'])): ?>
                    <button id="btnNotificaciones"
                        style="position:relative;background:transparent;border:1px solid #2e3347;
                    border-radius:10px;color:#e8eaf0;padding:.5rem .9rem;cursor:pointer;
                    margin-right:.75rem;transition:all .25s;display:flex;align-items:center;gap:.5rem;"
                        title="Notificaciones">
                        <i class="bi bi-bell-fill" id="iconoCampana" style="font-size:1.1rem;transition:all .3s;"></i>
                        <span id="textoNotif" style="font-size:.78rem;font-weight:600;display:none;"></span>
                        <span id="badgeNotificaciones"
                            style="display:none;position:absolute;top:-8px;right:-8px;
                        background:#e05252;color:#fff;border-radius:50%;
                        width:20px;height:20px;font-size:.65rem;font-weight:700;
                        align-items:center;justify-content:center;
                        box-shadow:0 0 8px rgba(224,82,82,.6);
                        animation:pulseRed 1.5s ease-in-out infinite;">0</span>
                    </button>
                <?php endif; ?>

                <div class="d-grid mb-lg-0 mb-2">
                    <a href="<?= $_ENV['APP_NAME'] ? '/' . $_ENV['APP_NAME'] : '' ?>/logout" class="btn btn-danger">
                        <i class="bi bi-box-arrow-right"></i> Salir
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="progress fixed-bottom" style="height:6px;">
        <div class="progress-bar progress-bar-animated bg-danger" id="bar" role="progressbar"></div>
    </div>

    <div class="container-fluid pt-5 mb-4" style="min-height:85vh;background:#0f1117;">
        <?php echo $contenido; ?>
    </div>

    <div class="container-fluid">
        <div class="row justify-content-center text-center">
            <div class="col-12">
                <p style="font-size:xx-small;font-weight:bold;">
                    Frankd Development, <?= date('Y') ?> &copy;
                </p>
            </div>
        </div>
    </div>

    <!-- LOADER GLOBAL -->
    <div id="bhr-loader">
        <div style="position:relative;display:flex;flex-direction:column;align-items:center;">
            <div class="loader-wheel-wrap">
                <div class="loader-wheel-outer"></div>
                <svg class="loader-wheel-svg" viewBox="0 0 94 94" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="47" cy="47" r="44" fill="none" stroke="#2e3347" stroke-width="8" />
                    <circle cx="47" cy="47" r="44" fill="none" stroke="#e8b84b" stroke-width="2" stroke-dasharray="8 6" opacity=".6" />
                    <line x1="47" y1="10" x2="47" y2="84" stroke="#e8b84b" stroke-width="2.5" opacity=".5" />
                    <line x1="10" y1="47" x2="84" y2="47" stroke="#e8b84b" stroke-width="2.5" opacity=".5" />
                    <line x1="20" y1="20" x2="74" y2="74" stroke="#d4a032" stroke-width="2" opacity=".4" />
                    <line x1="74" y1="20" x2="20" y2="74" stroke="#d4a032" stroke-width="2" opacity=".4" />
                    <line x1="12" y1="32" x2="82" y2="62" stroke="#e8b84b" stroke-width="1.5" opacity=".25" />
                    <line x1="12" y1="62" x2="82" y2="32" stroke="#e8b84b" stroke-width="1.5" opacity=".25" />
                    <circle cx="47" cy="47" r="18" fill="#1a1d27" stroke="#e8b84b" stroke-width="2" />
                    <circle cx="47" cy="32" r="2.5" fill="#e8b84b" />
                    <circle cx="47" cy="62" r="2.5" fill="#e8b84b" />
                    <circle cx="32" cy="47" r="2.5" fill="#e8b84b" />
                    <circle cx="62" cy="47" r="2.5" fill="#e8b84b" />
                    <circle cx="36" cy="36" r="2" fill="#d4a032" />
                    <circle cx="58" cy="36" r="2" fill="#d4a032" />
                    <circle cx="36" cy="58" r="2" fill="#d4a032" />
                    <circle cx="58" cy="58" r="2" fill="#d4a032" />
                </svg>
                <div class="loader-wheel-inner"></div>
                <div class="loader-wheel-hub">
                    <i class="bi bi-gear-fill" style="animation:wheelSpin 2s linear infinite;"></i>
                </div>
            </div>
            <div class="loader-dirt">
                <?php for ($i = 0; $i < 20; $i++): ?>
                    <div class="dirt-particle"></div>
                <?php endfor; ?>
            </div>
            <div class="loader-shadow"></div>
        </div>
        <div class="loader-bars">
            <span></span><span></span><span></span>
            <span></span><span></span><span></span>
            <span></span><span></span><span></span>
        </div>
        <div class="loader-lines">
            <div class="loader-titulo">BRIGADA HUMANITARIA Y DE RESCATE</div>
            <div class="loader-subtitulo" id="loaderMensaje">Procesando...</div>
        </div>
        <div class="loader-progress-wrap">
            <div class="loader-progress-track">
                <div class="loader-progress-bar"></div>
            </div>
            <div class="loader-tag">MDN · BHR · SAGE</div>
        </div>
    </div>

    <!-- Scripts -->
    <?php if (isset($_SESSION['auth_user'])): ?>
        <script src="<?= asset('build/js/solicitudes.js') ?>" type="module"></script>
    <?php endif; ?>

</body>

</html>