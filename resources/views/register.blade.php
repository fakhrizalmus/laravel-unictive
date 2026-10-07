<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar Akun</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-body-tertiary">
    <main class="container min-vh-100 d-flex align-items-center justify-content-center py-5">
        <div class="card border-0 shadow-sm w-100" style="max-width: 460px;">
            <div class="card-body p-4 p-md-5">
                <h1 class="h3 text-center mb-2">Buat Akun</h1>
                <p class="text-secondary text-center mb-4">Isi data berikut untuk mendaftar.</p>

                <div id="register-message" class="alert d-none" role="alert" aria-live="polite"></div>

                <form id="register-form">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nama</label>
                        <input type="text" class="form-control" id="name" name="name" autocomplete="name" required>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" autocomplete="email" required>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" minlength="6" autocomplete="new-password" required>
                    </div>

                    <button id="register-button" type="submit" class="btn btn-primary w-100">
                        Daftar
                    </button>
                </form>
            </div>
        </div>
    </main>

    <script>
        const form = document.getElementById('register-form');
        const submitButton = document.getElementById('register-button');
        const messageBox = document.getElementById('register-message');

        function showMessage(message, isSuccess) {
            messageBox.textContent = message;
            messageBox.className = `alert ${isSuccess ? 'alert-success' : 'alert-danger'}`;
        }

        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            messageBox.classList.add('d-none');
            submitButton.disabled = true;
            submitButton.textContent = 'Memproses...';

            try {
                const response = await fetch(@json(url('/api/auth/register')), {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        name: form.elements.name.value.trim(),
                        email: form.elements.email.value.trim(),
                        password: form.elements.password.value,
                    }),
                });
                const data = await response.json();

                if (response.ok && data.status === true) {
                    window.location.assign(@json(url('/login')));
                    return;
                }

                const errors = Object.values(data)
                    .flatMap((value) => Array.isArray(value) ? value : [value])
                    .filter((value) => typeof value === 'string');

                showMessage(errors.join(' ') || data.message || data.error || 'Pendaftaran gagal. Silakan coba lagi.', false);
            } catch (error) {
                showMessage('Tidak dapat terhubung ke server. Silakan coba lagi.', false);
            } finally {
                submitButton.disabled = false;
                submitButton.textContent = 'Daftar';
            }
        });
    </script>
</body>
</html>
