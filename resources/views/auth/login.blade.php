<!DOCTYPE html>
<html lang="fr">

<head>
    @include('partials.meta')
    @yield('title')
    <title>SIG-FACTURATION | Connexion</title>
    @yield('style')
    @include('partials.style')
    @notifyCss
    <style>
        :root {
            --primary-color: #1d3f7f;
            --primary-dark: #0f2147;
            --primary-light: #2d5fa3;
            --accent-color: #00d4ff;
            --text-dark: #1f2937;
            --text-light: #6b7280;
            --bg-overlay: rgba(255, 255, 255, 0.98);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f4f8;
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }

        /* Filigrane SVG en arrière-plan */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image:
                url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='120' height='120' viewBox='0 0 120 120'%3E%3Ccircle cx='60' cy='60' r='30' fill='none' stroke='%231d3f7f' stroke-width='0.6' stroke-opacity='0.12'/%3E%3Ccircle cx='60' cy='60' r='45' fill='none' stroke='%231d3f7f' stroke-width='0.4' stroke-opacity='0.08'/%3E%3Cpath d='M30 60 L90 60 M60 30 L60 90' stroke='%231d3f7f' stroke-width='0.4' stroke-opacity='0.08'/%3E%3Cpath d='M38 38 L82 82 M82 38 L38 82' stroke='%231d3f7f' stroke-width='0.3' stroke-opacity='0.06'/%3E%3C/svg%3E"),
                url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='200' height='200' viewBox='0 0 200 200'%3E%3Crect x='70' y='80' width='60' height='40' rx='4' fill='none' stroke='%231d3f7f' stroke-width='0.7' stroke-opacity='0.07'/%3E%3Cline x1='70' y1='93' x2='130' y2='93' stroke='%231d3f7f' stroke-width='0.5' stroke-opacity='0.06'/%3E%3Cline x1='70' y1='106' x2='110' y2='106' stroke='%231d3f7f' stroke-width='0.5' stroke-opacity='0.06'/%3E%3Ccircle cx='100' cy='55' r='12' fill='none' stroke='%231d3f7f' stroke-width='0.7' stroke-opacity='0.07'/%3E%3C/svg%3E");
            background-size: 120px 120px, 200px 200px;
            background-position: 0 0, 60px 60px;
            z-index: 0;
            pointer-events: none;
        }

        /* Dégradé subtil en overlay */
        body::after {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background:
                radial-gradient(ellipse at 15% 40%, rgba(29, 63, 127, 0.06) 0%, transparent 55%),
                radial-gradient(ellipse at 85% 70%, rgba(0, 212, 255, 0.05) 0%, transparent 55%);
            z-index: 0;
            pointer-events: none;
        }

        .login-container {
            position: relative;
            z-index: 1;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .login-card {
            background: var(--bg-overlay);
            backdrop-filter: blur(30px);
            border-radius: 10px;
            box-shadow: 0 8px 40px rgba(0, 0, 0, 0.15),
                0 0 1px rgba(255, 255, 255, 0.5) inset;
            border: 1px solid rgba(255, 255, 255, 0.3);
            overflow: hidden;
            max-width: 450px;
            width: 100%;
            display: flex;
            flex-direction: column;
            animation: fadeInUp 0.8s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .info-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            padding: 2.5rem 2.5rem 2rem;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
            text-align: center;
        }

        .info-section::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.08) 0%, transparent 70%);
            animation: rotate 25s linear infinite;
        }

        @keyframes rotate {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        .info-content {
            position: relative;
            z-index: 1;
        }

        .brand-title {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
            letter-spacing: -0.5px;
        }

        .brand-description {
            font-size: 0.875rem;
            line-height: 1.5;
            opacity: 0.9;
            font-weight: 300;
        }

        .feature-list {
            display: none;
        }

        .feature-item {
            display: none;
        }

        .feature-icon {
            display: none;
        }

        .feature-text {
            display: none;
        }

        .form-section {
            padding: 2.5rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: transparent;
        }

        .form-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .form-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 0.375rem;
            letter-spacing: -0.3px;
        }

        .form-subtitle {
            font-size: 0.875rem;
            color: var(--text-light);
            font-weight: 400;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-label {
            display: block;
            font-size: 0.8125rem;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 0.375rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            width: 18px;
            height: 18px;
            color: var(--text-light);
            opacity: 0.6;
        }

        .form-input {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.75rem;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-size: 0.9375rem;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.8);
            color: var(--text-dark);
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary-color);
            background: white;
            box-shadow: 0 0 0 3px rgba(29, 63, 127, 0.1);
        }

        .form-input::placeholder {
            color: #d1d5db;
            font-weight: 400;
        }

        .remember-forgot {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.75rem;
            font-size: 0.8125rem;
        }

        .checkbox-wrapper {
            display: flex;
            align-items: center;
        }

        .checkbox-wrapper input[type="checkbox"] {
            width: 16px;
            height: 16px;
            margin-right: 0.5rem;
            cursor: pointer;
            accent-color: var(--primary-color);
        }

        .checkbox-label {
            font-size: 0.8125rem;
            color: var(--text-dark);
            cursor: pointer;
            font-weight: 500;
        }

        .btn-login {
            width: 100%;
            padding: 0.875rem 1rem;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(29, 63, 127, 0.25);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(29, 63, 127, 0.35);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        @media (max-width: 768px) {
            .login-card {
                max-width: 90%;
            }

            .info-section {
                padding: 2rem 1.5rem 1.5rem;
            }

            .form-section {
                padding: 1.75rem;
            }

            .brand-title {
                font-size: 1.5rem;
            }

            .form-title {
                font-size: 1.25rem;
            }

            .form-subtitle {
                font-size: 0.8125rem;
            }
        }

        .inset-0 {
            z-index: 999999999 !important;
        }
    </style>
</head>

<body>
    @include('notify::components.notify')

    <div class="login-container">
        <div class="login-card">
            <!-- Section Information -->
            <div class="info-section">
                <div class="info-content">
                    <h1 class="brand-title text-light">SIG-FACTURATION</h1>
                    <p class="brand-description">
                        Un système de gestion informatisée moderne et efficace pour la facturation et le suivi
                        budgétaire de votre entreprise.
                    </p>

                    <ul class="feature-list">
                        <li class="feature-item">
                            <svg class="feature-icon" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                            <span class="feature-text">Gestion des factures</span>
                        </li>
                        <li class="feature-item">
                            <svg class="feature-icon" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                            <span class="feature-text">Gestion des factures Fournisseur</span>
                        </li>
                        <li class="feature-item">
                            <svg class="feature-icon" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                            <span class="feature-text">États des factures</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Section Formulaire -->
            <div class="form-section">
                <div class="form-header">
                    <h2 class="form-title">Connexion</h2>
                    <p class="form-subtitle">Accédez à votre espace de gestion</p>
                </div>

                <form method="POST" action="{{ route('auth') }}">
                    @csrf

                    <div class="form-group">
                        <label class="form-label" for="email">Adresse e-mail</label>
                        <div class="input-wrapper">
                            <svg class="input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <input type="email" id="email" name="email" class="form-input"
                                placeholder="votreemail@exemple.com" required autocomplete="email">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="password">Mot de passe</label>
                        <div class="input-wrapper">
                            <svg class="input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            <input type="password" id="password" name="password" class="form-input"
                                placeholder="Entrez votre mot de passe" required autocomplete="current-password">
                        </div>
                    </div>

                    <div class="remember-forgot">
                        <div class="checkbox-wrapper">
                            <input type="checkbox" id="rememberMe" name="remember" value="true">
                            <label class="checkbox-label" for="rememberMe">
                                Se souvenir de moi
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="btn-login">
                        Se connecter
                    </button>
                </form>
            </div>
        </div>
    </div>

    @include('partials.script')
    @notifyJs
</body>

</html>
