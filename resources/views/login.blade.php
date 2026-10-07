<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-body-tertiary">
    <main class="container min-vh-100 d-flex align-items-center justify-content-center py-5">
        <div class="card border-0 shadow-sm w-100" style="max-width: 460px;">
            <div class="card-body p-4 p-md-5">
                <h1 class="h3 text-center mb-2">Masuk</h1>
                <p class="text-secondary text-center mb-4">Masuk menggunakan akun Anda.</p>

                <div id="login-message" class="alert d-none" role="alert" aria-live="polite"></div>

                <form id="login-form">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" autocomplete="email" required>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" autocomplete="current-password" required>
                    </div>

                    <button id="login-button" type="submit" class="btn btn-primary w-100">
                        Login
                    </button>
                </form>

                <p class="text-center text-secondary mt-4 mb-0">
                    Belum punya akun? <a href="{{ url('/register') }}">Daftar</a>
                </p>
            </div>
        </div>
    </main>

    <script>
        const form = document.getElementById('login-form');
        const submitButton = document.getElementById('login-button');
        const messageBox = document.getElementById('login-message');

        function showMessage(message) {
            messageBox.textContent = message;
            messageBox.className = 'alert alert-danger';
        }

        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            messageBox.classList.add('d-none');
            submitButton.disabled = true;
            submitButton.textContent = 'Memproses...';

            try {
                const response = await fetch(@json(url('/api/auth/login')), {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        email: form.elements.email.value.trim(),
                        password: form.elements.password.value,
                    }),
                });
                const data = await response.json();

                if (!response.ok || !data.token) {
                    showMessage(data.error === 'invalid_credentials'
                        ? 'Email atau password salah.'
                        : data.error || 'Login gagal. Silakan coba lagi.');
                    return;
                }

                localStorage.setItem('token', data.token);
                window.location.assign(@json(url('/home')));
            } catch (error) {
                showMessage('Tidak dapat terhubung ke server. Silakan coba lagi.');
            } finally {
                submitButton.disabled = false;
                submitButton.textContent = 'Login';
            }
        });
    </script>
</body>
</html>