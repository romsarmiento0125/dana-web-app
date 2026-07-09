<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dana AI — Edit User</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="h-full min-h-[100dvh] bg-gray-950 text-gray-100">

<div class="mx-auto w-full max-w-4xl px-4 py-5 sm:px-6 sm:py-7">
    <header class="mb-5 rounded-2xl border border-gray-800 bg-gray-900 p-4 sm:p-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-indigo-400">Admin Module</p>
                <h1 class="mt-1 text-xl font-semibold text-white">Edit User</h1>
                <p class="mt-1 text-sm text-gray-400">Update the account details or reset the password.</p>
            </div>
            <div class="text-right">
                <p class="text-xs text-gray-500">Signed in: <span class="text-gray-300"><?= esc($username ?? 'admin') ?></span></p>
                <div class="mt-2 flex gap-2">
                    <a href="/useradmin" class="rounded-lg border border-gray-700 px-3 py-2 text-xs font-medium text-gray-200 hover:border-gray-600 hover:text-white">Back to users</a>
                    <a href="/dashboard" class="rounded-lg border border-gray-700 px-3 py-2 text-xs font-medium text-gray-200 hover:border-gray-600 hover:text-white">Dashboard</a>
                </div>
            </div>
        </div>
    </header>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="mb-4 rounded-xl border border-red-500/20 bg-red-500/10 px-4 py-3 text-sm text-red-300">
            <?= esc(session()->getFlashdata('error')) ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="mb-4 rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300">
            <?= esc(session()->getFlashdata('success')) ?>
        </div>
    <?php endif; ?>

    <section class="grid gap-5 lg:grid-cols-2">
        <div class="rounded-2xl border border-gray-800 bg-gray-900 p-4 sm:p-5">
            <div class="mb-4">
                <h2 class="text-lg font-semibold text-white">User details</h2>
                <p class="mt-1 text-sm text-gray-400">Change the username or role for this account.</p>
            </div>

            <form action="/useradmin/users/<?= (int) ($user['id'] ?? 0) ?>" method="POST" class="space-y-4" novalidate>
                <?= csrf_field() ?>
                <label class="block">
                    <span class="mb-1 block text-xs font-semibold uppercase tracking-widest text-gray-500">Username</span>
                    <input
                        type="text"
                        name="username"
                        value="<?= esc(old('username', $user['username'] ?? '')) ?>"
                        class="w-full rounded-lg border border-gray-700 bg-gray-800 px-3 py-2 text-sm text-gray-100 placeholder-gray-500 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        required
                    >
                </label>

                <label class="block">
                    <span class="mb-1 block text-xs font-semibold uppercase tracking-widest text-gray-500">Role</span>
                    <select
                        name="role"
                        class="w-full rounded-lg border border-gray-700 bg-gray-800 px-3 py-2 text-sm text-gray-100 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                        <option value="user" <?= old('role', $user['role'] ?? 'user') === 'user' ? 'selected' : '' ?>>user</option>
                        <option value="admin" <?= old('role', $user['role'] ?? 'user') === 'admin' ? 'selected' : '' ?>>admin</option>
                    </select>
                </label>

                <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">
                    Save changes
                </button>
            </form>
        </div>

        <div class="rounded-2xl border border-gray-800 bg-gray-900 p-4 sm:p-5">
            <div class="mb-4">
                <h2 class="text-lg font-semibold text-white">Change password</h2>
                <p class="mt-1 text-sm text-gray-400">Set a new password for the selected account.</p>
            </div>

            <form action="/useradmin/users/<?= (int) ($user['id'] ?? 0) ?>/password" method="POST" class="space-y-4" novalidate>
                <?= csrf_field() ?>
                <label class="block">
                    <span class="mb-1 block text-xs font-semibold uppercase tracking-widest text-gray-500">New password</span>
                    <input
                        type="password"
                        name="password"
                        class="w-full rounded-lg border border-gray-700 bg-gray-800 px-3 py-2 text-sm text-gray-100 placeholder-gray-500 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="Minimum 8 characters"
                        required
                    >
                </label>

                <label class="block">
                    <span class="mb-1 block text-xs font-semibold uppercase tracking-widest text-gray-500">Confirm password</span>
                    <input
                        type="password"
                        name="password_confirm"
                        class="w-full rounded-lg border border-gray-700 bg-gray-800 px-3 py-2 text-sm text-gray-100 placeholder-gray-500 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="Repeat the new password"
                        required
                    >
                </label>

                <button type="submit" class="rounded-lg bg-amber-500 px-4 py-2.5 text-sm font-semibold text-gray-950 hover:bg-amber-400">
                    Change password
                </button>
            </form>
        </div>
    </section>
</div>

</body>
</html>