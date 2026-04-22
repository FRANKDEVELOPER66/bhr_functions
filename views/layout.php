<!DOCTYPE html>
<html lang="en" data-base="<?= $_ENV['APP_NAME'] ? '/' . $_ENV['APP_NAME'] : '' ?>" data-rol="<?= $_SESSION['auth_rol'] ?? '' ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="<?= asset('build/js/app.js') ?>"></script>
    <link rel="shortcut icon" href="<?= asset('images/BHR.png') ?>" type="image/x-icon">
    <link rel="stylesheet" href="<?= asset('build/styles.css') ?>">
    <title>VEHICULOS BHR</title>
</head>

<body>
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
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="bi bi-car-front"></i> Control de Vehículos
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark" style="margin:0;">
                            <li>
                                <a class="dropdown-item nav-link text-white" href="<?= $_ENV['APP_NAME'] ? '/' . $_ENV['APP_NAME'] : '' ?>/vehiculos">
                                    <i class="bi bi-car-front-fill"></i> Vehículos
                                </a>
                            </li>
                        </ul>
                    </div>
                </ul>

                <!-- Campanita de notificaciones -->
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

                    <style>
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

    <div class="container-fluid pt-5 mb-4" style="min-height:85vh">
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

    <!-- Script de solicitudes y notificaciones -->
    <?php if (isset($_SESSION['auth_user'])): ?>
        <script src="<?= asset('build/js/solicitudes.js') ?>" type="module"></script>
    <?php endif; ?>

</body>

</html>