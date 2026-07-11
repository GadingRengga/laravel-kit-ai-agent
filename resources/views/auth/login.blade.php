<!DOCTYPE html>
<html lang="id" class="h-full">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login — Netra UI</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=DM+Mono:wght@400;500&display=swap"
        rel="stylesheet" />

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />

    <!-- Tailwind + semua CSS Netra UI (dikompilasi via Vite, lihat resources/css/app.css) -->
    @vite('resources/css/app.css')

    <!-- Dark mode: no-flash init (wajib inline di <head>) -->
    <script>
        (function() {
            const s = localStorage.getItem('theme');
            const isDark = s === 'dark' || (!s && window.matchMedia('(prefers-color-scheme:dark)').matches);
            if (isDark) {
                document.documentElement.classList.add('dark');
                document.documentElement.setAttribute('data-theme', 'dark');
            } else {
                document.documentElement.setAttribute('data-theme', 'light');
            }
        })();
    </script>
</head>

<body>

    <!-- ── Dark mode toggle (fixed) ── -->
    <button class="auth-dark-toggle" onclick="toggleDark()" aria-label="Toggle dark mode">
        <i id="dark-toggle-icon" class="fa-solid fa-moon"></i>
    </button>

    <!-- ══════════════════════════════════════
       AUTH PAGE SHELL
  ══════════════════════════════════════ -->
    <div class="auth-page">

        <!-- ════════ LEFT: Brand Panel ════════ -->
        <div class="auth-panel-brand">

            <!-- Background decoration -->
            <div class="auth-grid-pattern"></div>
            <div class="auth-orb auth-orb-1"></div>
            <div class="auth-orb auth-orb-2"></div>
            <div class="auth-orb auth-orb-3"></div>

            <!-- Logo -->
            <div class="auth-brand-logo">
                <div class="auth-brand-mark">
                    <i class="fa-solid fa-eye"></i>
                </div>
                <span class="auth-brand-name">Netra UI<span></span></span>
            </div>

            <!-- Hero copy -->
            <div class="auth-hero">
                <h1 class="auth-hero-title">
                    Kelola bisnis<br>
                    lebih <em>cerdas</em> &amp;<br>
                    <em>efisien</em> bersama<br>
                    Netra UI.
                </h1>
                <p class="auth-hero-desc">
                    Platform admin dashboard modern dengan komponen siap pakai,
                    dirancang untuk kecepatan dan kemudahan tim Anda.
                </p>
            </div>

            <!-- Feature list -->
            <div class="auth-features">
                <div class="auth-feature-item">
                    <div class="auth-feature-icon">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <span class="auth-feature-text">Keamanan enterprise-grade bawaan</span>
                </div>
                <div class="auth-feature-item">
                    <div class="auth-feature-icon">
                        <i class="fa-solid fa-gauge-high"></i>
                    </div>
                    <span class="auth-feature-text">Performa tinggi, ringan di semua perangkat</span>
                </div>
                <div class="auth-feature-item">
                    <div class="auth-feature-icon">
                        <i class="fa-solid fa-puzzle-piece"></i>
                    </div>
                    <span class="auth-feature-text">Komponen modular &amp; mudah dikustomisasi</span>
                </div>
            </div>

            <!-- Version -->
            <div class="auth-version">Netra UI v1.0.0 · Enterprise Suite</div>

        </div>
        <!-- /left panel -->

        <!-- ════════ RIGHT: Form Panel ════════ -->
        <div class="auth-panel-form">
            <div class="auth-card">

                <!-- Mobile-only brand -->
                <div class="auth-mobile-brand">
                    <div class="auth-mobile-brand-mark">
                        <i class="fa-solid fa-eye"></i>
                    </div>
                    <span class="auth-mobile-brand-name">Netra UI</span>
                </div>

                <!-- Heading -->
                <h2 class="auth-heading">Selamat datang kembali 👋</h2>
                <p class="auth-subheading">Masuk ke akun Anda untuk melanjutkan ke dashboard.</p>

                <!-- Session status (misal: setelah reset password / logout) -->
                @if (session('status'))
                    <div class="auth-alert auth-alert-success" style="margin-bottom:18px;">
                        {{ session('status') }}
                    </div>
                @endif

                <!-- Alert container (error validasi dari server) -->
                @if ($errors->any())
                    <div id="login-alert" class="auth-alert auth-alert-error" style="margin-bottom:18px;">
                        {{ $errors->first() }}
                    </div>
                @else
                    <div id="login-alert" class="auth-alert" style="display:none; margin-bottom:18px;"></div>
                @endif

                <!-- Login Form -->
                <form id="login-form" class="auth-form" method="POST" action="{{ route('login.attempt') }}" novalidate>
                    @csrf

                    <!-- Email -->
                    <div class="form-group">
                        <label class="auth-label" for="login-email">
                            Alamat Email
                        </label>
                        <div class="input-group">
                            <span class="input-addon">
                                <i class="fa-regular fa-envelope"></i>
                            </span>
                            <input id="login-email" name="email" type="email"
                                class="form-input @error('email') is-invalid @enderror"
                                placeholder="nama@perusahaan.com" value="{{ old('email') }}" autocomplete="email"
                                spellcheck="false" required autofocus />
                        </div>
                        @error('email')
                            <span class="form-error-text">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="form-group">
                        <div class="auth-forgot-row">
                            <label class="auth-label" for="login-password">Password</label>
                            {{-- Belum ada route reset password. Kalau nanti dibuat, ganti href ini ke route('password.request') --}}
                            <a href="#" class="auth-forgot-link">Lupa password?</a>
                        </div>
                        <div class="input-group">
                            <span class="input-addon">
                                <i class="fa-solid fa-lock"></i>
                            </span>
                            <input id="login-password" name="password" type="password"
                                class="form-input @error('password') is-invalid @enderror"
                                placeholder="Masukkan password" autocomplete="current-password" required />
                            <button type="button" class="input-addon-btn"
                                onclick="toggleAuthPassword('login-password', 'pw-eye-icon')"
                                aria-label="Tampilkan password">
                                <i id="pw-eye-icon" class="fa-regular fa-eye text-sm"></i>
                            </button>
                        </div>
                        @error('password')
                            <span class="form-error-text">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Remember me -->
                    <div class="auth-checkbox-row">
                        <input type="checkbox" id="remember-me" name="remember" class="auth-checkbox"
                            {{ old('remember') ? 'checked' : '' }} />
                        <label class="auth-checkbox-label" for="remember-me">Ingat saya selama 30 hari</label>
                    </div>

                    <!-- Submit -->
                    <button id="login-btn" type="submit" class="auth-btn">
                        <span class="auth-btn-spinner"></span>
                        <span class="auth-btn-text">
                            <i class="fa-solid fa-arrow-right-to-bracket"></i>
                            Masuk ke Dashboard
                        </span>
                    </button>

                </form>
                <!-- /form -->



                <!-- Trust badges -->
                <div class="auth-trust-row">
                    <span class="auth-trust-item">
                        <i class="fa-solid fa-lock"></i>
                        SSL Terenkripsi
                    </span>
                    <span class="auth-trust-item">
                        <i class="fa-solid fa-shield-halved"></i>
                        GDPR Compliant
                    </span>
                    <span class="auth-trust-item">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                        99.9% Uptime
                    </span>
                </div>

            </div>
        </div>
        <!-- /form panel -->

    </div>
    <!-- /auth-page -->

    <!-- Scripts (semua JS Netra UI, lihat resources/js/app.js) -->
    @vite('resources/js/app.js')

</body>

</html>
