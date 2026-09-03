<?php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin();

$errors = [];
$success = '';


// ============================================================
// PROSES TAMBAH USER
// ============================================================

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    ($_POST['action'] ?? '') === 'tambah_user'
) {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'user';


    // Validasi username

    if ($username === '') {

        $errors[] = 'Username wajib diisi.';

    } elseif (strlen($username) < 4) {

        $errors[] = 'Username minimal 4 karakter.';
    }


    // Validasi password

    if ($password === '') {

        $errors[] = 'Password wajib diisi.';

    } elseif (strlen($password) < 4) {

        $errors[] = 'Password minimal 4 karakter.';
    }


    // Konfirmasi password

    if ($password !== $confirmPassword) {

        $errors[] =
            'Konfirmasi password tidak sama.';
    }


    // Validasi role

    if (
        !in_array(
            $role,
            ['user', 'admin'],
            true
        )
    ) {

        $errors[] =
            'Role yang dipilih tidak valid.';
    }


    // Cek username

    if (!$errors) {

        $cek = $pdo->prepare(
            "SELECT id
             FROM users
             WHERE username = ?
             LIMIT 1"
        );

        $cek->execute([
            $username
        ]);

        if ($cek->fetch()) {

            $errors[] =
                'Username sudah digunakan.';
        }
    }


    // Simpan user

    if (!$errors) {

        try {

            /*
             * Password untuk login disimpan dalam bentuk hash.
             */

            $hash =
                password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );


            /*
             * password_plain digunakan untuk
             * menampilkan password kepada admin.
             */

            $stmt = $pdo->prepare(
                "INSERT INTO users
                (
                    username,
                    password,
                    password_plain,
                    role
                )
                VALUES (?, ?, ?, ?)"
            );


            $stmt->execute([
                $username,
                $hash,
                $password,
                $role
            ]);


            $success =
                "Akun '$username' berhasil dibuat.";


            // Kosongkan input

            $_POST = [];


        } catch (PDOException $e) {

            $errors[] =
                'Gagal membuat akun: ' .
                $e->getMessage();
        }
    }
}


// ============================================================
// PROSES UBAH PASSWORD
// ============================================================

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    ($_POST['action'] ?? '') === 'ubah_password'
) {

    $userId =
        (int) ($_POST['user_id'] ?? 0);

    $newPassword =
        $_POST['new_password'] ?? '';

    $confirmNewPassword =
        $_POST['confirm_new_password'] ?? '';


    // Validasi ID

    if ($userId <= 0) {

        $errors[] =
            'User tidak valid.';
    }


    // Validasi password baru

    if ($newPassword === '') {

        $errors[] =
            'Password baru wajib diisi.';

    } elseif (strlen($newPassword) < 4) {

        $errors[] =
            'Password baru minimal 4 karakter.';
    }


    // Konfirmasi password baru

    if (
        $newPassword !==
        $confirmNewPassword
    ) {

        $errors[] =
            'Konfirmasi password baru tidak sama.';
    }


    if (!$errors) {

        try {

            /*
             * Pastikan user ada.
             */

            $cek = $pdo->prepare(
                "SELECT id, username
                 FROM users
                 WHERE id = ?
                 LIMIT 1"
            );

            $cek->execute([
                $userId
            ]);

            $user = $cek->fetch();


            if (!$user) {

                $errors[] =
                    'User tidak ditemukan.';

            } else {

                /*
                 * Hash password baru
                 */

                $hash =
                    password_hash(
                        $newPassword,
                        PASSWORD_DEFAULT
                    );


                /*
                 * Update password hash
                 * dan password yang dapat
                 * dilihat admin.
                 */

                $stmt = $pdo->prepare(
                    "UPDATE users
                     SET
                        password = ?,
                        password_plain = ?
                     WHERE id = ?"
                );


                $stmt->execute([
                    $hash,
                    $newPassword,
                    $userId
                ]);


                $success =
                    "Password user '" .
                    $user['username'] .
                    "' berhasil diubah.";
            }


        } catch (PDOException $e) {

            $errors[] =
                'Gagal mengubah password: ' .
                $e->getMessage();
        }
    }
}


// ============================================================
// PROSES HAPUS USER
// ============================================================

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    ($_POST['action'] ?? '') === 'hapus_user'
) {

    $userId =
        (int) ($_POST['user_id'] ?? 0);


    if ($userId <= 0) {

        $errors[] =
            'User tidak valid.';

    } else {

        try {

            /*
             * Ambil data user terlebih dahulu.
             */

            $cek = $pdo->prepare(
                "SELECT id, username, role
                 FROM users
                 WHERE id = ?
                 LIMIT 1"
            );

            $cek->execute([
                $userId
            ]);

            $user = $cek->fetch();


            if (!$user) {

                $errors[] =
                    'User tidak ditemukan.';

            /*
             * Mencegah admin menghapus
             * akun admin yang sedang login.
             */

            } elseif (
                isset($_SESSION['user_id']) &&
                (int) $_SESSION['user_id'] === $userId
            ) {

                $errors[] =
                    'Akun yang sedang digunakan tidak dapat dihapus.';

            } else {

                /*
                 * Hapus user
                 */

                $stmt = $pdo->prepare(
                    "DELETE FROM users
                     WHERE id = ?"
                );

                $stmt->execute([
                    $userId
                ]);


                $success =
                    "User '" .
                    $user['username'] .
                    "' berhasil dihapus.";
            }


        } catch (PDOException $e) {

            $errors[] =
                'Gagal menghapus user: ' .
                $e->getMessage();
        }
    }
}


// ============================================================
// AMBIL DATA USER
// ============================================================

$stmtUsers = $pdo->query(
    "SELECT
        id,
        username,
        password_plain,
        role,
        created_at
     FROM users
     ORDER BY created_at DESC"
);

$users =
    $stmtUsers->fetchAll();


// ============================================================
// PAGE TITLE
// ============================================================

$pageTitle = 'Tambah User';


// ============================================================
// HEADER
// ============================================================

require_once __DIR__ . '/../includes/header.php';

?>


<!-- ============================================================
     CONTENT
============================================================ -->

<div class="row g-3">


    <!-- ========================================================
         FORM TAMBAH USER
    ========================================================= -->

    <div class="col-lg-5">

        <div class="card p-4">

            <h6 class="fw-bold mb-3">

                <i
                    class="bi bi-person-plus-fill me-1"
                    style="color:#1d4ed8;"
                ></i>

                Buat Akun Baru

            </h6>


            <!-- SUCCESS -->

            <?php if ($success): ?>

                <div
                    class="alert alert-success py-2 small"
                >

                    <i
                        class="bi bi-check-circle-fill me-1"
                    ></i>

                    <?= e($success) ?>

                </div>

            <?php endif; ?>


            <!-- ERROR -->

            <?php if ($errors): ?>

                <div
                    class="alert alert-danger py-2 small"
                >

                    <ul class="mb-0 ps-3">

                        <?php foreach ($errors as $err): ?>

                            <li>
                                <?= e($err) ?>
                            </li>

                        <?php endforeach; ?>

                    </ul>

                </div>

            <?php endif; ?>


            <!-- FORM -->

            <form
                method="POST"
                autocomplete="off"
            >

                <input
                    type="hidden"
                    name="action"
                    value="tambah_user"
                >


                <!-- USERNAME -->

                <div class="mb-3">

                    <label class="form-label">

                        Username

                    </label>

                    <input
                        type="text"
                        name="username"
                        class="form-control"
                        required
                        minlength="4"
                        value="<?= e(
                            $_POST['username'] ?? ''
                        ) ?>"
                    >

                </div>


                <!-- PASSWORD -->

                <div class="mb-3">

                    <label class="form-label">

                        Password

                    </label>


                    <div class="input-group">

                        <input
                            type="password"
                            name="password"
                            id="password"
                            class="form-control"
                            required
                            minlength="4"
                        >


                        <button
                            type="button"
                            class="btn btn-outline-secondary"
                            onclick="togglePassword(
                                'password',
                                'iconPassword'
                            )"
                        >

                            <i
                                id="iconPassword"
                                class="bi bi-eye"
                            ></i>

                        </button>

                    </div>

                </div>


                <!-- KONFIRMASI PASSWORD -->

                <div class="mb-3">

                    <label class="form-label">

                        Konfirmasi Password

                    </label>


                    <div class="input-group">

                        <input
                            type="password"
                            name="confirm_password"
                            id="confirm_password"
                            class="form-control"
                            required
                            minlength="4"
                        >


                        <button
                            type="button"
                            class="btn btn-outline-secondary"
                            onclick="togglePassword(
                                'confirm_password',
                                'iconConfirmPassword'
                            )"
                        >

                            <i
                                id="iconConfirmPassword"
                                class="bi bi-eye"
                            ></i>

                        </button>

                    </div>

                </div>


                <!-- ROLE -->

                <div class="mb-4">

                    <label class="form-label">

                        Role

                    </label>


                    <select
                        name="role"
                        class="form-select"
                    >

                        <option
                            value="user"
                            <?= (
                                ($_POST['role'] ?? 'user')
                                === 'user'
                            )
                                ? 'selected'
                                : ''
                            ?>
                        >
                            User (Pemohon)
                        </option>


                        <option
                            value="admin"
                            <?= (
                                ($_POST['role'] ?? '')
                                === 'admin'
                            )
                                ? 'selected'
                                : ''
                            ?>
                        >
                            Admin
                        </option>

                    </select>

                </div>


                <!-- BUTTON -->

                <button
                    type="submit"
                    class="btn btn-primary w-100"
                >

                    <i
                        class="bi bi-check2-circle me-1"
                    ></i>

                    Simpan User

                </button>

            </form>

        </div>

    </div>



    <!-- ========================================================
         DAFTAR USER
    ========================================================= -->

    <div class="col-lg-7">

        <div class="card p-4">


            <!-- HEADER -->

            <div
                class="d-flex align-items-center justify-content-between mb-3"
            >

                <h6 class="fw-bold mb-0">

                    <i
                        class="bi bi-people me-1"
                        style="color:#1d4ed8;"
                    ></i>

                    Daftar User

                </h6>


                <span class="text-muted small">

                    <?= count($users) ?> akun

                </span>

            </div>


            <!-- TABLE -->

            <div class="table-responsive">

                <table
                    class="table table-hover align-middle mb-0"
                >

                    <thead>

                        <tr>

                            <th>
                                Username
                            </th>

                            <th>
                                Password
                            </th>

                            <th>
                                Role
                            </th>

                            <th>
                                Dibuat
                            </th>

                            <th>
                                Aksi
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                    <?php if (!$users): ?>

                        <tr>

                            <td
                                colspan="5"
                                class="text-center text-muted py-4"
                            >

                                Belum ada user.

                            </td>

                        </tr>


                    <?php else: ?>


                        <?php foreach ($users as $u): ?>

                            <?php
                            $passwordId =
                                'password_' .
                                (int) $u['id'];

                            $newPasswordId =
                                'new_password_' .
                                (int) $u['id'];

                            $confirmNewPasswordId =
                                'confirm_new_password_' .
                                (int) $u['id'];
                            ?>


                            <tr>

                                <!-- USERNAME -->

                                <td>

                                    <div
                                        class="fw-semibold"
                                    >

                                        <?= e(
                                            $u['username']
                                        ) ?>

                                    </div>

                                </td>


                                <!-- PASSWORD -->

                                <td>

                                    <div
                                        class="input-group"
                                        style="min-width:170px;"
                                    >

                                        <input
                                            type="password"
                                            class="form-control form-control-sm"
                                            id="<?= e(
                                                $passwordId
                                            ) ?>"
                                            value="<?= e(
                                                $u['password_plain']
                                                ?? ''
                                            ) ?>"
                                            readonly
                                        >


                                        <button
                                            type="button"
                                            class="btn btn-outline-secondary btn-sm"
                                            onclick="togglePassword(
                                                '<?= e($passwordId) ?>',
                                                'icon_<?= e($passwordId) ?>'
                                            )"
                                            title="Lihat password"
                                        >

                                            <i
                                                id="icon_<?= e(
                                                    $passwordId
                                                ) ?>"
                                                class="bi bi-eye"
                                            ></i>

                                        </button>

                                    </div>

                                </td>


                                <!-- ROLE -->

                                <td>

                                    <span
                                        class="status-badge
                                        <?= $u['role'] === 'admin'
                                            ? 'status-diproses'
                                            : 'status-selesai'
                                        ?>"
                                    >

                                        <?= e(
                                            $u['role'] === 'admin'
                                                ? 'Admin'
                                                : 'Pemohon'
                                        ) ?>

                                    </span>

                                </td>


                                <!-- CREATED -->

                                <td class="text-muted small">

                                    <?php if (
                                        !empty(
                                            $u['created_at']
                                        )
                                    ): ?>

                                        <?= e(
                                            date(
                                                'd M Y',
                                                strtotime(
                                                    $u['created_at']
                                                )
                                            )
                                        ) ?>

                                    <?php else: ?>

                                        -

                                    <?php endif; ?>

                                </td>


                                <!-- AKSI -->

                                <td>

                                    <div
                                        class="d-flex gap-1"
                                    >


                                        <!-- UBAH PASSWORD -->

                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-primary"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalPassword<?= (int) $u['id'] ?>"
                                            title="Ubah password"
                                        >

                                            <i
                                                class="bi bi-key-fill"
                                            ></i>

                                        </button>


                                        <!-- HAPUS -->

                                        <?php if (
                                            !isset(
                                                $_SESSION['user_id']
                                            ) ||
                                            (int) $_SESSION['user_id']
                                            !== (int) $u['id']
                                        ): ?>

                                            <form
                                                method="POST"
                                                onsubmit="
                                                    return confirm(
                                                        'Yakin ingin menghapus user <?= e(addslashes($u['username'])) ?>?'
                                                    );
                                                "
                                            >

                                                <input
                                                    type="hidden"
                                                    name="action"
                                                    value="hapus_user"
                                                >

                                                <input
                                                    type="hidden"
                                                    name="user_id"
                                                    value="<?= (int) $u['id'] ?>"
                                                >

                                                <button
                                                    type="submit"
                                                    class="btn btn-sm btn-outline-danger"
                                                    title="Hapus user"
                                                >

                                                    <i
                                                        class="bi bi-trash-fill"
                                                    ></i>

                                                </button>

                                            </form>

                                        <?php endif; ?>

                                    </div>

                                </td>

                            </tr>


                            <!-- =================================================
                                 MODAL UBAH PASSWORD
                            ================================================== -->

                            <div
                                class="modal fade"
                                id="modalPassword<?= (int) $u['id'] ?>"
                                tabindex="-1"
                                aria-hidden="true"
                            >

                                <div
                                    class="modal-dialog modal-dialog-centered"
                                >

                                    <div class="modal-content">


                                        <!-- MODAL HEADER -->

                                        <div class="modal-header">

                                            <h5
                                                class="modal-title"
                                            >

                                                <i
                                                    class="bi bi-key-fill me-2"
                                                    style="color:#7c3aed;"
                                                ></i>

                                                Ubah Password

                                            </h5>


                                            <button
                                                type="button"
                                                class="btn-close"
                                                data-bs-dismiss="modal"
                                            ></button>

                                        </div>


                                        <!-- MODAL BODY -->

                                        <div class="modal-body">

                                            <div
                                                class="alert alert-warning"
                                            >

                                                <i
                                                    class="bi bi-exclamation-triangle-fill me-1"
                                                ></i>

                                                Password lama tidak perlu
                                                diketahui. Masukkan
                                                password baru di bawah.

                                            </div>


                                            <div class="mb-3">

                                                <label
                                                    class="form-label"
                                                >

                                                    Username

                                                </label>

                                                <input
                                                    type="text"
                                                    class="form-control"
                                                    value="<?= e(
                                                        $u['username']
                                                    ) ?>"
                                                    readonly
                                                >

                                            </div>


                                            <!-- PASSWORD BARU -->

                                            <div class="mb-3">

                                                <label
                                                    class="form-label"
                                                >

                                                    Password Baru

                                                </label>


                                                <div
                                                    class="input-group"
                                                >

                                                    <input
                                                        type="password"
                                                        name="dummy"
                                                        id="<?= e(
                                                            $newPasswordId
                                                        ) ?>"
                                                        class="form-control"
                                                        minlength="4"
                                                        placeholder="Masukkan password baru"
                                                    >


                                                    <button
                                                        type="button"
                                                        class="btn btn-outline-secondary"
                                                        onclick="togglePassword(
                                                            '<?= e($newPasswordId) ?>',
                                                            'icon<?= e($newPasswordId) ?>'
                                                        )"
                                                    >

                                                        <i
                                                            id="icon<?= e($newPasswordId) ?>"
                                                            class="bi bi-eye"
                                                        ></i>

                                                    </button>

                                                </div>

                                            </div>


                                            <!-- FORM SEBENARNYA -->

                                            <form
                                                method="POST"
                                                id="formPassword<?= (int) $u['id'] ?>"
                                            >

                                                <input
                                                    type="hidden"
                                                    name="action"
                                                    value="ubah_password"
                                                >

                                                <input
                                                    type="hidden"
                                                    name="user_id"
                                                    value="<?= (int) $u['id'] ?>"
                                                >


                                                <!-- KONFIRMASI -->

                                                <div class="mb-3">

                                                    <label
                                                        class="form-label"
                                                    >

                                                        Konfirmasi Password Baru

                                                    </label>


                                                    <div
                                                        class="input-group"
                                                    >

                                                        <input
                                                            type="password"
                                                            name="confirm_new_password"
                                                            id="<?= e(
                                                                $confirmNewPasswordId
                                                            ) ?>"
                                                            class="form-control"
                                                            minlength="4"
                                                            placeholder="Ulangi password baru"
                                                            required
                                                        >


                                                        <button
                                                            type="button"
                                                            class="btn btn-outline-secondary"
                                                            onclick="togglePassword(
                                                                '<?= e($confirmNewPasswordId) ?>',
                                                                'icon<?= e($confirmNewPasswordId) ?>'
                                                            )"
                                                        >

                                                            <i
                                                                id="icon<?= e(
                                                                    $confirmNewPasswordId
                                                                ) ?>"
                                                                class="bi bi-eye"
                                                            ></i>

                                                        </button>

                                                    </div>

                                                </div>


                                                <div
                                                    class="d-grid"
                                                >

                                                    <button
                                                        type="submit"
                                                        class="btn btn-primary"
                                                    >

                                                        <i
                                                            class="bi bi-shield-lock-fill me-1"
                                                        ></i>

                                                        Simpan Password Baru

                                                    </button>

                                                </div>

                                            </form>

                                        </div>

                                    </div>

                                </div>

                            </div>

                        <?php endforeach; ?>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>


<!-- ============================================================
     JAVASCRIPT
============================================================ -->

<script>

/*
|--------------------------------------------------------------------------
| SHOW / HIDE PASSWORD
|--------------------------------------------------------------------------
*/

function togglePassword(
    inputId,
    iconId
) {

    const input =
        document.getElementById(inputId);

    const icon =
        document.getElementById(iconId);


    if (!input || !icon) {
        return;
    }


    if (input.type === 'password') {

        input.type = 'text';

        icon.classList.remove(
            'bi-eye'
        );

        icon.classList.add(
            'bi-eye-slash'
        );

    } else {

        input.type = 'password';

        icon.classList.remove(
            'bi-eye-slash'
        );

        icon.classList.add(
            'bi-eye'
        );
    }
}


/*
|--------------------------------------------------------------------------
| HUBUNGKAN PASSWORD BARU MODAL
|--------------------------------------------------------------------------
|
| Karena input password baru berada di luar form,
| sebelum form dikirim kita salin nilainya ke hidden input.
|
*/

document.addEventListener(
    'DOMContentLoaded',
    function () {

        <?php foreach ($users as $u): ?>

        (function () {

            const form =
                document.getElementById(
                    'formPassword<?= (int) $u['id'] ?>'
                );

            const newPassword =
                document.getElementById(
                    'new_password_<?= (int) $u['id'] ?>'
                );


            if (
                !form ||
                !newPassword
            ) {
                return;
            }


            form.addEventListener(
                'submit',
                function () {

                    let hidden =
                        form.querySelector(
                            'input[name="new_password"]'
                        );


                    if (!hidden) {

                        hidden =
                            document.createElement(
                                'input'
                            );

                        hidden.type =
                            'hidden';

                        hidden.name =
                            'new_password';

                        form.appendChild(
                            hidden
                        );
                    }


                    hidden.value =
                        newPassword.value;

                }
            );

        })();

        <?php endforeach; ?>

    }
);

</script>


<?php

require_once __DIR__ . '/../includes/footer.php';

?>