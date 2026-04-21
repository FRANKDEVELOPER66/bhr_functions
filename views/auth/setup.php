<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VEHICULOS BHR — Crear contraseña</title>
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
        }

        .wrap {
            width: 100%;
            max-width: 420px;
            padding: 1.5rem;
        }

        .header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .header img {
            width: 220px;
            height: 120px;
            object-fit: contain;
            margin-bottom: 1rem;
            filter: drop-shadow(0 4px 20px rgba(232, 184, 75, .3));
        }

        .header h1 {
            font-family: 'Rajdhani', sans-serif;
            font-size: 1.6rem;
            font-weight: 700;
            color: #e8eaf0;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .header p {
            font-size: .78rem;
            color: #7c8398;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-top: .25rem;
        }

        .card {
            background: #1a1d27;
            border: 1px solid #2e3347;
            border-radius: 18px;
            padding: 2rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, .5);
        }

        .titulo {
            font-family: 'Rajdhani', sans-serif;
            font-size: 1.1rem;
            font-weight: 700;
            color: #e8eaf0;
            margin-bottom: .4rem;
        }

        .sub {
            font-size: .8rem;
            color: #7c8398;
            margin-bottom: 1.5rem;
            line-height: 1.5;
        }

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

        .btn-guardar {
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

        .btn-guardar:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(232, 184, 75, .35);
        }

        .btn-guardar:disabled {
            opacity: .6;
            cursor: not-allowed;
            transform: none;
        }

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

        .success-wrap {
            text-align: center;
            padding: 1rem 0;
            display: none;
        }

        .footer {
            text-align: center;
            margin-top: 1.5rem;
            font-size: .72rem;
            color: #3a3d4e;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        /* Indicador de fortaleza */
        .strength-wrap {
            margin-top: -.5rem;
            margin-bottom: 1rem;
        }

        .strength-bar {
            height: 4px;
            border-radius: 20px;
            background: #2e3347;
            overflow: hidden;
            margin-bottom: .3rem;
        }

        .strength-fill {
            height: 100%;
            border-radius: 20px;
            width: 0%;
            transition: all .3s;
        }

        .strength-text {
            font-size: .72rem;
            color: #7c8398;
        }
    </style>
</head>

<body>

    <div class="wrap">
        <div class="header">
            <img src="<?= asset('images/CARROSBHR.png') ?>" alt="BHR">
            <h1>Vehículos BHR</h1>
            <p>Brigada Humanitaria y de Rescate</p>
        </div>

        <div class="card">
            <div class="error-msg" id="errorMsg">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <span id="errorTexto"></span>
            </div>

            <!-- Formulario -->
            <div id="formWrap">
                <div class="titulo">Crear contraseña</div>
                <div class="sub">Elige una contraseña segura para acceder al sistema.</div>

                <div class="input-wrap">
                    <i class="bi bi-lock-fill"></i>
                    <input type="password" id="inputPassword" placeholder="Nueva contraseña">
                </div>

                <div class="strength-wrap">
                    <div class="strength-bar">
                        <div class="strength-fill" id="strengthFill"></div>
                    </div>
                    <div class="strength-text" id="strengthText"></div>
                </div>

                <div class="input-wrap">
                    <i class="bi bi-lock-fill"></i>
                    <input type="password" id="inputConfirm" placeholder="Confirmar contraseña">
                </div>

                <button class="btn-guardar" id="btnGuardar">
                    <i class="bi bi-check-circle-fill"></i> Crear contraseña
                </button>
            </div>

            <!-- Éxito -->
            <div class="success-wrap" id="successWrap">
                <i class="bi bi-shield-check-fill" style="font-size:3rem;color:#4caf7d;display:block;margin-bottom:1rem;"></i>
                <div class="titulo" style="text-align:center;">¡Contraseña creada!</div>
                <div class="sub" style="text-align:center;margin-top:.5rem;">
                    Ya puedes ingresar al sistema con tu catálogo y contraseña.
                </div>
                <a href="<?= $_ENV['APP_NAME'] ? '/' . $_ENV['APP_NAME'] : '' ?>/"
                    style="display:inline-flex;align-items:center;gap:.5rem;margin-top:1.5rem;
                background:linear-gradient(135deg,#e8b84b,#d4a032);border-radius:10px;
                color:#0f1117;padding:.85rem 2rem;font-family:'Rajdhani',sans-serif;
                font-weight:700;font-size:1rem;text-decoration:none;letter-spacing:1px;">
                    <i class="bi bi-box-arrow-in-right"></i> Ir al login
                </a>
            </div>
        </div>

        <div class="footer">MDN · Ejército de Guatemala · BHR © <?= date('Y') ?></div>
    </div>

    <script>
        const BASE = '<?= $_ENV["APP_NAME"] ? "/" . $_ENV["APP_NAME"] : "" ?>';
        const TOKEN = '<?= htmlspecialchars($token ?? '') ?>';

        const mostrarError = (msg) => {
            const el = document.getElementById('errorMsg');
            document.getElementById('errorTexto').textContent = msg;
            el.classList.add('visible');
            setTimeout(() => el.classList.remove('visible'), 4000);
        };

        // Indicador de fortaleza
        document.getElementById('inputPassword').addEventListener('input', function() {
            const val = this.value;
            const fill = document.getElementById('strengthFill');
            const text = document.getElementById('strengthText');
            let score = 0;
            if (val.length >= 8) score++;
            if (/[A-Z]/.test(val)) score++;
            if (/[0-9]/.test(val)) score++;
            if (/[^A-Za-z0-9]/.test(val)) score++;

            const map = {
                0: {
                    w: '0%',
                    c: '#e05252',
                    t: ''
                },
                1: {
                    w: '25%',
                    c: '#e05252',
                    t: 'Muy débil'
                },
                2: {
                    w: '50%',
                    c: '#e8b84b',
                    t: 'Débil'
                },
                3: {
                    w: '75%',
                    c: '#3a7bd5',
                    t: 'Buena'
                },
                4: {
                    w: '100%',
                    c: '#4caf7d',
                    t: 'Fuerte'
                },
            };

            fill.style.width = map[score].w;
            fill.style.background = map[score].c;
            text.textContent = map[score].t;
            text.style.color = map[score].c;
        });

        document.getElementById('btnGuardar').addEventListener('click', async () => {
            const password = document.getElementById('inputPassword').value;
            const confirm = document.getElementById('inputConfirm').value;

            if (!password) {
                mostrarError('Ingresa una contraseña');
                return;
            }
            if (password.length < 8) {
                mostrarError('Mínimo 8 caracteres');
                return;
            }
            if (password !== confirm) {
                mostrarError('Las contraseñas no coinciden');
                return;
            }

            const btn = document.getElementById('btnGuardar');
            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Guardando...';

            const body = new FormData();
            body.append('token', TOKEN);
            body.append('password', password);
            body.append('password_confirm', confirm);

            try {
                const r = await fetch(`${BASE}/API/auth/guardar-password`, {
                    method: 'POST',
                    body
                });
                const d = await r.json();
                if (d.codigo === 1) {
                    document.getElementById('formWrap').style.display = 'none';
                    document.getElementById('successWrap').style.display = 'block';
                } else {
                    mostrarError(d.mensaje);
                }
            } catch {
                mostrarError('Error de conexión');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-check-circle-fill"></i> Crear contraseña';
            }
        });
    </script>
</body>

</html>