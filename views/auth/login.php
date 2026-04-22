<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VEHICULOS BHR — Acceso</title>
    <link rel="shortcut icon" href="<?= asset('images/BHR.png') ?>" type="image/x-icon">
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
            position: relative;
            overflow: hidden;
        }

        /* Fondo animado */
        body::before {
            content: '';
            position: absolute;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(232, 184, 75, .08) 0%, transparent 70%);
            top: -100px;
            right: -100px;
            border-radius: 50%;
            animation: pulse 6s ease-in-out infinite alternate;
        }

        body::after {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(232, 184, 75, .05) 0%, transparent 70%);
            bottom: -100px;
            left: -100px;
            border-radius: 50%;
            animation: pulse 8s ease-in-out infinite alternate-reverse;
        }

        @keyframes pulse {
            from {
                transform: scale(1);
                opacity: .5;
            }

            to {
                transform: scale(1.2);
                opacity: 1;
            }
        }

        .login-wrap {
            width: 100%;
            max-width: 420px;
            padding: 1.5rem;
            position: relative;
            z-index: 1;
        }

        /* Header */
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-header img {
            width: 200px;
            height: 120px;
            object-fit: contain;
            margin-bottom: 1rem;
            filter: drop-shadow(0 4px 20px rgba(232, 184, 75, .3));
        }

        .login-header h1 {
            font-family: 'Rajdhani', sans-serif;
            font-size: 1.6rem;
            font-weight: 700;
            color: #e8eaf0;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .login-header p {
            font-size: .78rem;
            color: #7c8398;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-top: .25rem;
        }

        /* Card */
        .login-card {
            background: #1a1d27;
            border: 1px solid #2e3347;
            border-radius: 18px;
            padding: 2rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, .5);
        }

        /* Steps */
        .step {
            display: none;
        }

        .step.activo {
            display: block;
        }

        .step-titulo {
            font-family: 'Rajdhani', sans-serif;
            font-size: 1.1rem;
            font-weight: 700;
            color: #e8eaf0;
            margin-bottom: .4rem;
            letter-spacing: .5px;
        }

        .step-sub {
            font-size: .8rem;
            color: #7c8398;
            margin-bottom: 1.5rem;
            line-height: 1.5;
        }

        .step-sub strong {
            color: #e8b84b;
        }

        /* Input */
        .input-wrap {
            position: relative;
            margin-bottom: 1rem;
        }

        .input-wrap i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #7c8398;
            font-size: .95rem;
        }

        .input-wrap input {
            width: 100%;
            background: #242837;
            border: 1.5px solid #2e3347;
            border-radius: 10px;
            color: #e8eaf0;
            padding: .85rem 1rem .85rem 2.75rem;
            font-size: .95rem;
            font-family: 'Inter', sans-serif;
            transition: all .25s;
            outline: none;
        }

        .input-wrap input:focus {
            border-color: #e8b84b;
            box-shadow: 0 0 0 3px rgba(232, 184, 75, .12);
        }

        .input-wrap input::placeholder {
            color: #4a5068;
        }

        /* Botón */
        .btn-login {
            width: 100%;
            background: linear-gradient(135deg, #e8b84b, #d4a032);
            border: none;
            border-radius: 10px;
            color: #0f1117;
            padding: .9rem;
            font-family: 'Rajdhani', sans-serif;
            font-size: 1.05rem;
            font-weight: 700;
            letter-spacing: 1px;
            cursor: pointer;
            transition: all .3s;
            margin-top: .5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(232, 184, 75, .35);
        }

        .btn-login:disabled {
            opacity: .6;
            cursor: not-allowed;
            transform: none;
        }

        /* Botón volver */
        .btn-back {
            background: transparent;
            border: none;
            color: #7c8398;
            font-size: .82rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: .4rem;
            margin-bottom: 1.25rem;
            padding: 0;
            font-family: 'Inter', sans-serif;
            transition: color .2s;
        }

        .btn-back:hover {
            color: #e8b84b;
        }

        /* Nombre del usuario */
        .usuario-badge {
            background: rgba(232, 184, 75, .1);
            border: 1px solid rgba(232, 184, 75, .25);
            border-radius: 8px;
            padding: .65rem 1rem;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: .6rem;
            font-size: .85rem;
            color: #e8b84b;
        }

        /* Error */
        .error-msg {
            background: rgba(224, 82, 82, .1);
            border: 1px solid rgba(224, 82, 82, .3);
            border-radius: 8px;
            padding: .65rem 1rem;
            font-size: .82rem;
            color: #e05252;
            margin-bottom: 1rem;
            display: none;
            align-items: center;
            gap: .5rem;
        }

        .error-msg.visible {
            display: flex;
        }

        /* Footer */
        .login-footer {
            text-align: center;
            margin-top: 1.5rem;
            font-size: .72rem;
            color: #3a3d4e;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
    </style>
</head>

<body>

    <div class="login-wrap">

        <!-- Header -->
        <div class="login-header">
            <img src="<?= asset('images/CARROSBHR.png') ?>" alt="BHR">
            <h1>Vehículos BHR</h1>
            <p>Brigada Humanitaria y de Rescate</p>
        </div>

        <!-- Card -->
        <div class="login-card">

            <!-- Error global -->
            <div class="error-msg" id="errorMsg">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <span id="errorTexto"></span>
            </div>

            <!-- STEP 1: Catálogo -->
            <div class="step activo" id="step1">
                <div class="step-titulo">Identificación</div>
                <div class="step-sub">Ingresa tu número de catálogo para continuar.</div>

                <div class="input-wrap">
                    <i class="bi bi-person-badge"></i>
                    <input type="text" id="inputCatalogo" placeholder="Número de catálogo"
                        maxlength="10" inputmode="numeric">
                </div>

                <button class="btn-login" id="btnCatalogo">
                    <i class="bi bi-arrow-right-circle-fill"></i> Continuar
                </button>
            </div>

            <!-- STEP 2A: Primer ingreso — registrar correo -->
            <div class="step" id="step2a">
                <button class="btn-back" onclick="volverStep1()">
                    <i class="bi bi-arrow-left"></i> Volver
                </button>
                <div class="usuario-badge" id="badgeNombrePrimerIngreso">
                    <i class="bi bi-person-fill"></i>
                    <span id="nombrePrimerIngreso"></span>
                </div>
                <div class="step-titulo">Primer acceso</div>
                <div class="step-sub">
                    Es tu primera vez en el sistema. Ingresa tu correo electrónico para recibir el enlace de activación.
                </div>

                <div class="input-wrap">
                    <i class="bi bi-envelope"></i>
                    <input type="email" id="inputCorreo" placeholder="tu.correo@ejemplo.com">
                </div>

                <button class="btn-login" id="btnEnviarCorreo">
                    <i class="bi bi-send-fill"></i> Enviar enlace
                </button>
            </div>

            <!-- STEP 2B: Login normal — contraseña -->
            <div class="step" id="step2b">
                <button class="btn-back" onclick="volverStep1()">
                    <i class="bi bi-arrow-left"></i> Volver
                </button>
                <div class="usuario-badge" id="badgeNombreLogin">
                    <i class="bi bi-person-fill"></i>
                    <span id="nombreLogin"></span>
                </div>
                <div class="step-titulo">Contraseña</div>
                <div class="step-sub">Ingresa tu contraseña para acceder al sistema.</div>

                <div class="input-wrap">
                    <i class="bi bi-lock-fill"></i>
                    <input type="password" id="inputPassword" placeholder="Contraseña">
                </div>

                <button class="btn-login" id="btnLogin">
                    <i class="bi bi-box-arrow-in-right"></i> Ingresar
                </button>
            </div>

            <!-- STEP 3: Correo enviado -->
            <div class="step" id="step3">
                <div style="text-align:center;padding:1rem 0;">
                    <i class="bi bi-envelope-check-fill" style="font-size:3rem;color:#4caf7d;display:block;margin-bottom:1rem;"></i>
                    <div class="step-titulo" style="text-align:center;">¡Correo enviado!</div>
                    <div class="step-sub" style="text-align:center;margin-top:.5rem;">
                        Revisa tu bandeja de entrada y sigue el enlace para crear tu contraseña.<br>
                        <small>El enlace expira en <strong>2 horas</strong>.</small>
                    </div>
                </div>
            </div>

        </div>

        <div class="login-footer">MDN · Ejército de Guatemala · BHR © <?= date('Y') ?></div>
    </div>

    <script>
        const BASE = '<?= $_ENV["APP_NAME"] ? "/" . $_ENV["APP_NAME"] : "" ?>';

        const mostrarError = (msg) => {
            const el = document.getElementById('errorMsg');
            document.getElementById('errorTexto').textContent = msg;
            el.classList.add('visible');
            setTimeout(() => el.classList.remove('visible'), 4000);
        };

        const irStep = (id) => {
            document.querySelectorAll('.step').forEach(s => s.classList.remove('activo'));
            document.getElementById(id).classList.add('activo');
        };

        const volverStep1 = () => {
            irStep('step1');
            document.getElementById('inputCatalogo').value = '';
        };

        // ── STEP 1: verificar catálogo ────────────────────────────────────────────────
        document.getElementById('btnCatalogo').addEventListener('click', async () => {
            const catalogo = document.getElementById('inputCatalogo').value.trim();
            if (!catalogo) {
                mostrarError('Ingresa tu número de catálogo');
                return;
            }

            const btn = document.getElementById('btnCatalogo');
            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Verificando...';

            const body = new FormData();
            body.append('catalogo', catalogo);

            try {
                const r = await fetch(`${BASE}/API/auth/verificar-catalogo`, {
                    method: 'POST',
                    body
                });
                const d = await r.json();

                if (d.codigo === 0) {
                    mostrarError(d.mensaje);
                } else if (d.codigo === 2) {
                    // Primer ingreso
                    document.getElementById('nombrePrimerIngreso').textContent = d.nombre;
                    irStep('step2a');
                } else if (d.codigo === 1) {
                    // Login normal
                    document.getElementById('nombreLogin').textContent = d.nombre;
                    irStep('step2b');
                    setTimeout(() => document.getElementById('inputPassword').focus(), 100);
                }
            } catch (err) {
                mostrarError('Error al enviar Verificar Catalogo. Intenta de nuevo.');
                console.error(err);

            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-arrow-right-circle-fill"></i> Continuar';
            }
        });

        // Enter en catálogo
        document.getElementById('inputCatalogo').addEventListener('keydown', e => {
            if (e.key === 'Enter') document.getElementById('btnCatalogo').click();
        });

        // ── STEP 2A: enviar correo ────────────────────────────────────────────────────
        document.getElementById('btnEnviarCorreo').addEventListener('click', async () => {
            const catalogo = document.getElementById('inputCatalogo').value.trim();
            const correo = document.getElementById('inputCorreo').value.trim();
            if (!correo) {
                mostrarError('Ingresa tu correo');
                return;
            }

            const btn = document.getElementById('btnEnviarCorreo');
            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Enviando...';

            const body = new FormData();
            body.append('catalogo', catalogo);
            body.append('correo', correo);

            try {
                const r = await fetch(`${BASE}/API/auth/registrar-correo`, {
                    method: 'POST',
                    body
                });
                const d = await r.json();
                if (d.codigo === 1) {
                    irStep('step3');
                    if (d.debug_link) console.log('LINK:', d.debug_link);
                } else {
                    mostrarError(d.mensaje);
                }
            } catch (err) {
                mostrarError('Error al enviar el correo. Intenta de nuevo.');
                console.error(err);
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-send-fill"></i> Enviar enlace';
            }
        });

        // ── STEP 2B: login ────────────────────────────────────────────────────────────
        document.getElementById('btnLogin').addEventListener('click', async () => {
            const catalogo = document.getElementById('inputCatalogo').value.trim();
            const password = document.getElementById('inputPassword').value;
            if (!password) {
                mostrarError('Ingresa tu contraseña');
                return;
            }

            const btn = document.getElementById('btnLogin');
            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Ingresando...';

            const body = new FormData();
            body.append('catalogo', catalogo);
            body.append('password', password);

            try {
                const r = await fetch(`${BASE}/API/auth/login`, {
                    method: 'POST',
                    body
                });
                const d = await r.json();
                if (d.codigo === 1) {
                    window.location.href = `${BASE}/vehiculos`;
                } else {
                    mostrarError(d.mensaje);
                }
            } catch (err) {
                mostrarError('Error al Ingresar. Intenta de nuevo.');
                console.error(err);
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-box-arrow-in-right"></i> Ingresar';
            }
        });

        // Enter en contraseña
        document.getElementById('inputPassword').addEventListener('keydown', e => {
            if (e.key === 'Enter') document.getElementById('btnLogin').click();
        });
    </script>
</body>

</html>