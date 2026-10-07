# Laravel Unictive

Aplikasi CRUD User dan Hobi menggunakan Laravel, Blade, dan JWT Authentication.

## Persiapan

1. Instal dependency: `composer install`.
2. Salin `.env.example` menjadi `.env`, lalu atur koneksi database.
3. Generate application key dan JWT secret:

   ```sh
   php artisan key:generate
   php artisan jwt:secret
   ```

4. Buat tabel: `php artisan migrate`.
5. Jalankan aplikasi: `php artisan serve`.
6. Buka `/register` untuk membuat akun admin, lalu login melalui `/login`.

## Blade

Setelah login, buka `/home` untuk menambah, melihat, mengubah, dan menghapus user beserta hobinya. Form menerima beberapa hobi yang dipisahkan dengan koma; saat mengedit, kosongkan form hobi untuk menghapus semuanya.

## API

Semua endpoint mengembalikan JSON. Gunakan header `Accept: application/json`. Endpoint berikut tersedia tanpa token untuk membuat akun dan memperoleh JWT:

| Method | Endpoint | Fungsi |
| --- | --- | --- |
| `POST` | `/api/auth/register` | Membuat akun admin |
| `POST` | `/api/auth/login` | Login dan memperoleh token |

Endpoint berikut memerlukan token JWT pada header `Authorization` dengan skema `Bearer`:

| Method | Endpoint | Fungsi |
| --- | --- | --- |
| `POST` | `/api/auth/logout` | Logout dan mencabut token |
| `GET` | `/api/user` | Daftar user dan hobinya |
| `POST` | `/api/user/create` | Membuat user dan daftar hobi |
| `GET` | `/api/user/{id}` | Detail user dan hobinya |
| `PUT` | `/api/user/edit/{id}` | Memperbarui user dan daftar hobi |
| `DELETE` | `/api/user/delete/{id}` | Menghapus user beserta hobinya |

Contoh body untuk membuat atau memperbarui user:

```json
{
  "name": "Ayu",
  "email": "ayu@example.com",
  "hobis": [
    { "nama_hobi": "Membaca" },
    { "nama_hobi": "Bersepeda" }
  ]
}
```

Saat memperbarui hobi, kirim daftar lengkap hobi yang diinginkan. Sertakan `id` untuk mengubah hobi yang sudah ada; item tanpa `id` akan ditambahkan. Kirim `"hobis": []` untuk menghapus semua hobi.
