<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Accès gratuit | MedKey</title>
    <link rel="icon" href="/images/landing/logo/fav-icon.png">
    <link rel="stylesheet" href="/css/vendors/bootstrap.min.css">
    <link rel="stylesheet" href="/fonts/fontawesome/fontawesome.css">
    <link rel="stylesheet" href="/fonts/bootstrap-icons/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Jost:wght@400;500;600;700;800&display=swap">
    <style>
        :root {
            --mk-blue: #0a64c9;
            --mk-blue-soft: #3189eb;
            --mk-card: rgba(248, 250, 255, 0.92);
            --mk-text: #1c2941;
            --mk-muted: #6b7486;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Jost', sans-serif;
            color: var(--mk-text);
            background: linear-gradient(120deg, rgba(3, 56, 117, 0.65), rgba(53, 146, 249, 0.45)),
                        url('/images/landing/hero/pilo2.jpeg') center/cover no-repeat fixed;
        }
        .free-access-page {
            min-height: 100vh;
            padding: 1.1rem 1.1rem 2rem;
            display: flex;
            flex-direction: column;
        }
        .top-actions {
            display: flex;
            justify-content: flex-end;
        }
        .login-pill {
            border: 1px solid rgba(255, 255, 255, 0.6);
            color: #fff;
            border-radius: 999px;
            padding: 0.25rem 0.8rem;
            font-size: 0.85rem;
            text-decoration: none;
            transition: all .2s ease;
        }
        .login-pill:hover {
            background: #fff;
            color: var(--mk-blue);
        }
        .split-wrap {
            flex: 1;
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
            padding: 1rem 0;
        }
        .brand-side {
            color: #fff;
            text-align: center;
        }
        .brand-side .logo {
            width: 220px;
            max-width: 65%;
            margin-bottom: 0.6rem;
        }
        .brand-side .tagline {
            margin: 0;
            opacity: .95;
            font-size: 1.05rem;
        }
        .card-side {
            display: flex;
            justify-content: center;
        }
        .signup-card {
            width: 100%;
            max-width: 430px;
            border-radius: 16px;
            background: var(--mk-card);
            border: 1px solid rgba(255, 255, 255, .65);
            backdrop-filter: blur(5px);
            box-shadow: 0 16px 42px rgba(10, 33, 74, 0.28);
            padding: 1.45rem 1.25rem;
        }
        .signup-title {
            text-align: center;
            margin: 0 0 0.35rem;
            font-size: 1.7rem;
            font-weight: 700;
        }
        .signup-subtitle {
            text-align: center;
            color: var(--mk-muted);
            margin-bottom: 1rem;
            font-size: .95rem;
        }
        .mk-label {
            margin-bottom: 0.3rem;
            font-size: .92rem;
            color: #32415f;
            font-weight: 500;
        }
        .mk-input {
            width: 100%;
            border: 1px solid #d9e0eb;
            border-radius: 8px;
            padding: 0.64rem 0.76rem;
            font-size: 0.95rem;
            background: #fff;
            margin-bottom: 0.7rem;
        }
        .mk-input:focus {
            outline: none;
            border-color: var(--mk-blue-soft);
            box-shadow: 0 0 0 3px rgba(58, 148, 248, .15);
        }
        .mk-checkbox {
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
            margin: 0.38rem 0;
            color: #5d6880;
            font-size: .84rem;
            line-height: 1.3;
        }
        .captcha-box {
            border: 1px solid #d4dce8;
            border-radius: 8px;
            background: #fff;
            margin: 0.7rem 0 0.9rem;
            padding: 0.7rem 0.75rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: .5rem;
        }
        .captcha-box .left {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: .85rem;
            color: #37445f;
        }
        .captcha-box .badge {
            font-size: .7rem;
            color: #7a8499;
            background: #f4f6fa;
            padding: .2rem .45rem;
            border-radius: 999px;
        }
        .mk-btn {
            width: 100%;
            border: none;
            background: #cfd5de;
            color: #fff;
            border-radius: 8px;
            font-size: 0.98rem;
            font-weight: 600;
            padding: 0.62rem 0.9rem;
            cursor: not-allowed;
            transition: .2s;
        }
        .mk-btn.enabled {
            background: var(--mk-blue);
            cursor: pointer;
        }
        .mk-btn.enabled:hover {
            background: #0857b0;
        }
        .legal-note {
            margin-top: .7rem;
            color: #73809a;
            font-size: .78rem;
            line-height: 1.3;
        }
        .social-signup {
            margin-top: .85rem;
            text-align: center;
        }
        .social-signup p {
            margin-bottom: .5rem;
            color: #60708e;
            font-size: .88rem;
        }
        .social-list {
            display: flex;
            justify-content: center;
            gap: .45rem;
            flex-wrap: wrap;
        }
        .social-btn {
            width: 34px;
            height: 34px;
            border: 1px solid #dae2ed;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #fff;
            color: #4d5f7b;
            text-decoration: none;
            transition: .2s;
        }
        .social-btn:hover {
            border-color: var(--mk-blue);
            color: var(--mk-blue);
        }
        .result-message {
            margin-top: .75rem;
            font-size: .86rem;
            display: none;
        }
        .result-message.show { display: block; }
        .result-message.success { color: #0f7d40; }
        .result-message.error { color: #b12828; }
        @media (min-width: 992px) {
            .split-wrap {
                grid-template-columns: 1fr 430px;
                gap: 4rem;
                padding: 0;
            }
            .brand-side {
                text-align: left;
                padding-left: 1.2rem;
            }
            .brand-side .logo {
                max-width: 260px;
                width: 260px;
            }
            .brand-side .tagline {
                font-size: 1.25rem;
            }
        }
    </style>
</head>
<body>
    <main class="free-access-page">
        <div class="top-actions">
            <a class="login-pill" href="/login">Log in</a>
        </div>

        <section class="split-wrap">
            <aside class="brand-side">
                <img class="logo" src="/images/landing/logo/logo1.png" alt="MedKey">
                <p class="tagline">Votre plateforme SIH moderne pour piloter l'hôpital, de l'admission à la facturation.</p>
            </aside>

            <div class="card-side">
                <div class="signup-card">
                    <h1 class="signup-title">Créer votre espace MedKey</h1>
                    <p class="signup-subtitle">Démarrez gratuitement votre environnement hospitalier en quelques minutes.</p>

                    <form id="free-access-form">
                        <label class="mk-label" for="organization_name">Nom de l'établissement</label>
                        <input class="mk-input" id="organization_name" name="organization_name" type="text" placeholder="Ex: Clinique Saint Michel" required>

                        <label class="mk-label" for="email">Email administrateur</label>
                        <input class="mk-input" id="email" name="email" type="email" placeholder="admin@hopital.com" required>

                        <label class="mk-checkbox">
                            <input id="updates_optin" type="checkbox">
                            <span>J'accepte de recevoir les nouveautés produit, conseils d'usage et actualités MedKey.</span>
                        </label>
                        <label class="mk-checkbox">
                            <input id="training_optin" type="checkbox">
                            <span>Je souhaite recevoir des ressources de formation pour mon équipe.</span>
                        </label>

                        <!-- <div class="captcha-box">
                            <div class="left">
                                <input id="captcha_ok" type="checkbox">
                                <span>Je ne suis pas un robot</span>
                            </div>
                            <span class="badge">Captcha</span>
                        </div> -->

                        <button id="submit-btn" class="mk-btn" type="submit" disabled>Continuer</button>
                        <p class="legal-note">
                            En poursuivant, vous acceptez nos <a href="{{ route('legal.conditions') }}">Conditions</a> et notre
                            <a href="{{ route('legal.confidentialite') }}">Politique de confidentialité</a>.
                        </p>
                        <p id="result-message" class="result-message"></p>
                    </form>

                    <div class="social-signup">
                        <p>Inscription rapide via un compte social</p>
                        <div class="social-list">
                            <a class="social-btn" href="#0" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                            <a class="social-btn" href="#0" aria-label="Google"><i class="fab fa-google"></i></a>
                            <a class="social-btn" href="#0" aria-label="Apple"><i class="fab fa-apple"></i></a>
                            <a class="social-btn" href="#0" aria-label="Microsoft"><i class="fab fa-windows"></i></a>
                            <a class="social-btn" href="#0" aria-label="Plus"><i class="fas fa-ellipsis-h"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script>
        (function () {
            const form = document.getElementById('free-access-form');
            const submitBtn = document.getElementById('submit-btn');
            const orgInput = document.getElementById('organization_name');
            const emailInput = document.getElementById('email');
            const resultMessage = document.getElementById('result-message');
            const POLL_TIMEOUT_MS = 150000;
            const POLL_DELAYS = [2000, 4000, 6000];

            function setMessage(type, text) {
                resultMessage.className = 'result-message show ' + type;
                resultMessage.textContent = text;
            }

            function toggleSubmitState() {
                const enabled = orgInput.value.trim().length > 2 && emailInput.value.trim().length > 5;
                submitBtn.disabled = !enabled;
                submitBtn.classList.toggle('enabled', enabled);
            }

            orgInput.addEventListener('input', toggleSubmitState);
            emailInput.addEventListener('input', toggleSubmitState);
            toggleSubmitState();

            function sleep(ms) {
                return new Promise((resolve) => setTimeout(resolve, ms));
            }

            async function waitForProvisioning(uuid) {
                const start = Date.now();
                let attempt = 0;

                while (Date.now() - start < POLL_TIMEOUT_MS) {
                    const statusResponse = await fetch('/api/v1/public/tenants/' + encodeURIComponent(uuid) + '/status', {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    const statusJson = await statusResponse.json().catch(() => ({}));
                    if (!statusResponse.ok) {
                        throw new Error(statusJson.message || "Impossible de suivre la création de l'espace.");
                    }

                    const tenant = statusJson.data || {};
                    if (tenant.onboarding_status === 'failed') {
                        throw new Error("La création de l'espace a échoué. Veuillez contacter le support.");
                    }

                    if (tenant.onboarding_status === 'provisioned') {
                        if (!tenant.autologin_url) {
                            throw new Error("L'espace est prêt, mais le lien de connexion automatique est indisponible.");
                        }
                        return tenant;
                    }

                    setMessage('success', "Espace en cours de génération... vous allez être redirigé automatiquement.");
                    const delay = POLL_DELAYS[Math.min(attempt, POLL_DELAYS.length - 1)];
                    attempt += 1;
                    await sleep(delay);
                }

                throw new Error("Le provisioning prend plus de temps que prévu. Vérifiez votre email puis réessayez.");
            }

            form.addEventListener('submit', async function (event) {
                event.preventDefault();
                if (submitBtn.disabled) return;

                submitBtn.disabled = true;
                submitBtn.textContent = 'Création en cours...';
                resultMessage.className = 'result-message';
                resultMessage.textContent = '';

                const payload = {
                    email: emailInput.value.trim(),
                    organization_name: orgInput.value.trim(),
                    hospital_name: orgInput.value.trim(),
                    admin_email: emailInput.value.trim(),
                    plan: 'free'
                };

                try {
                    const response = await fetch('/api/v1/public/tenants/register', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify(payload)
                    });

                    const data = await response.json().catch(() => ({}));
                    if (!response.ok) {
                        const message = data.message || "Inscription impossible pour le moment. Veuillez réessayer.";
                        throw new Error(message);
                    }

                    const tenant = data.data || {};
                    if (!tenant.uuid) {
                        throw new Error("Réponse inattendue: UUID du tenant introuvable.");
                    }

                    setMessage('success', "Compte créé. Configuration de votre espace en cours...");
                    const finalTenant = await waitForProvisioning(tenant.uuid);
                    setMessage('success', "Votre espace est prêt. Redirection vers le dashboard...");
                    window.location.assign(finalTenant.autologin_url);
                } catch (error) {
                    setMessage('error', error.message || "Une erreur est survenue.");
                    toggleSubmitState();
                } finally {
                    submitBtn.textContent = 'Continuer';
                }
            });
        })();
    </script>
</body>
</html>
