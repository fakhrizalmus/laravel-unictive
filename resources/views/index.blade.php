<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Data Users</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-body-tertiary">
    <main class="container py-5">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <h1 class="h3 mb-1">Data Users</h1>
                <p class="text-secondary mb-0">Kelola data user dan hobi.</p>
            </div>
            <button id="add-user-button" type="button" class="btn btn-primary">
                Tambah User
            </button>
        </div>

        <div id="users-status" class="alert d-none" role="alert" aria-live="polite"></div>

        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Nama</th>
                            <th scope="col">Email</th>
                            <th scope="col">Hobi</th>
                            <th scope="col" class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="users-table-body">
                        <tr><td colspan="5" class="text-center text-secondary py-4">Memuat data user...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <div class="modal fade" id="user-modal" tabindex="-1" aria-labelledby="user-modal-title" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="user-form">
                    <div class="modal-header">
                        <h2 class="modal-title fs-5" id="user-modal-title">Tambah User</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="user-name" class="form-label">Nama</label>
                            <input type="text" class="form-control" id="user-name" name="name" maxlength="255" required>
                        </div>
                        <div class="mb-3">
                            <label for="user-email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="user-email" name="email" maxlength="255" required>
                        </div>
                        <div>
                            <label for="user-hobbies" class="form-label">Hobi</label>
                            <input type="text" class="form-control" id="user-hobbies" name="hobis" required>
                            <div class="form-text">Pisahkan beberapa hobi dengan koma.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button id="save-user-button" type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const apiUrl = @json(url('/api/user'));
        const loginUrl = @json(url('/login'));
        const tableBody = document.getElementById('users-table-body');
        const statusBox = document.getElementById('users-status');
        const userForm = document.getElementById('user-form');
        const userModal = new bootstrap.Modal(document.getElementById('user-modal'));
        const modalTitle = document.getElementById('user-modal-title');
        const saveButton = document.getElementById('save-user-button');
        let users = [];
        let editingUserId = null;

        function getToken() {
            const token = localStorage.getItem('token');

            if (!token) {
                window.location.assign(loginUrl);
                throw new Error('Sesi login tidak ditemukan.');
            }

            return token;
        }

        async function apiRequest(url, options = {}) {
            const response = await fetch(url, {
                ...options,
                headers: {
                    Accept: 'application/json',
                    Authorization: `Bearer ${getToken()}`,
                    ...(options.body ? { 'Content-Type': 'application/json' } : {}),
                    ...options.headers,
                },
            });
            const data = await response.json();

            if (response.status === 401) {
                localStorage.removeItem('token');
                window.location.assign(loginUrl);
                throw new Error('Sesi login telah berakhir. Silakan login kembali.');
            }

            if (!response.ok) {
                const message = data.message && typeof data.message === 'object'
                    ? Object.values(data.message).flat().join(' ')
                    : data.message || data.error || `Permintaan gagal (HTTP ${response.status}).`;
                throw new Error(message);
            }

            return data;
        }

        function showStatus(message, isError = false) {
            statusBox.textContent = message;
            statusBox.className = `alert ${isError ? 'alert-danger' : 'alert-success'}`;
        }

        function hideStatus() {
            statusBox.classList.add('d-none');
        }

        function createCell(value) {
            const cell = document.createElement('td');
            cell.textContent = value;
            return cell;
        }

        function renderUsers() {
            tableBody.replaceChildren();

            if (users.length === 0) {
                const row = document.createElement('tr');
                const cell = createCell('Belum ada data user.');
                cell.colSpan = 5;
                cell.className = 'text-center text-secondary py-4';
                row.appendChild(cell);
                tableBody.appendChild(row);
                return;
            }

            users.forEach((user, index) => {
                const row = document.createElement('tr');
                const hobbies = Array.isArray(user.hobis)
                    ? user.hobis.map((hobi) => hobi.nama_hobi).join(', ')
                    : '';
                const actionsCell = document.createElement('td');
                const actions = document.createElement('div');
                const editButton = document.createElement('button');
                const deleteButton = document.createElement('button');

                row.append(
                    createCell(String(index + 1)),
                    createCell(user.name ?? ''),
                    createCell(user.email ?? ''),
                    createCell(hobbies)
                );

                actions.className = 'd-flex justify-content-end gap-2';
                editButton.type = 'button';
                editButton.className = 'btn btn-sm btn-outline-primary';
                editButton.textContent = 'Edit';
                editButton.addEventListener('click', () => openEditModal(user));

                deleteButton.type = 'button';
                deleteButton.className = 'btn btn-sm btn-outline-danger';
                deleteButton.textContent = 'Delete';
                deleteButton.addEventListener('click', () => deleteUser(user));

                actions.append(editButton, deleteButton);
                actionsCell.appendChild(actions);
                row.appendChild(actionsCell);
                tableBody.appendChild(row);
            });
        }

        async function loadUsers() {
            hideStatus();

            try {
                const data = await apiRequest(apiUrl);

                if (!Array.isArray(data.users)) {
                    throw new Error('Format respons API tidak sesuai.');
                }

                users = data.users;
                renderUsers();
            } catch (error) {
                tableBody.replaceChildren();
                showStatus(`Gagal mengambil data user: ${error.message}`, true);
            }
        }

        function openAddModal() {
            editingUserId = null;
            userForm.reset();
            modalTitle.textContent = 'Tambah User';
            saveButton.textContent = 'Simpan';
            userModal.show();
        }

        function openEditModal(user) {
            editingUserId = user.id;
            userForm.elements.name.value = user.name ?? '';
            userForm.elements.email.value = user.email ?? '';
            userForm.elements.hobis.value = Array.isArray(user.hobis)
                ? user.hobis.map((hobi) => hobi.nama_hobi).join(', ')
                : '';
            modalTitle.textContent = 'Edit User';
            saveButton.textContent = 'Simpan Perubahan';
            userModal.show();
        }

        userForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            saveButton.disabled = true;
            hideStatus();

            const hobbies = userForm.elements.hobis.value
                .split(',')
                .map((name) => name.trim())
                .filter(Boolean)
                .map((nama_hobi) => ({ nama_hobi }));

            try {
                await apiRequest(
                    editingUserId ? `${apiUrl}/edit/${editingUserId}` : `${apiUrl}/create`,
                    {
                        method: editingUserId ? 'PUT' : 'POST',
                        body: JSON.stringify({
                            name: userForm.elements.name.value.trim(),
                            email: userForm.elements.email.value.trim(),
                            hobis: hobbies,
                        }),
                    }
                );

                userModal.hide();
                await loadUsers();
                showStatus(editingUserId ? 'Data user berhasil diperbarui.' : 'User berhasil ditambahkan.');
            } catch (error) {
                showStatus(`Gagal menyimpan user: ${error.message}`, true);
            } finally {
                saveButton.disabled = false;
            }
        });

        async function deleteUser(user) {
            if (!window.confirm(`Hapus user ${user.name}?`)) {
                return;
            }

            hideStatus();

            try {
                await apiRequest(`${apiUrl}/delete/${user.id}`, { method: 'DELETE' });
                await loadUsers();
                showStatus('User berhasil dihapus.');
            } catch (error) {
                showStatus(`Gagal menghapus user: ${error.message}`, true);
            }
        }

        document.getElementById('add-user-button').addEventListener('click', openAddModal);
        loadUsers();
    </script>
</body>
</html>
