<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dana AI — User Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="h-full min-h-[100dvh] bg-gray-950 text-gray-100">

<div class="mx-auto w-full max-w-5xl px-4 py-5 sm:px-6 sm:py-7">
    <header class="mb-5 rounded-2xl border border-gray-800 bg-gray-900 p-4 sm:p-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-indigo-400">Admin Module</p>
                <h1 class="mt-1 text-xl font-semibold text-white">User Management</h1>
                <p class="mt-1 text-sm text-gray-400">Manage roles for admin and user accounts.</p>
            </div>
            <div class="text-right">
                <p class="text-xs text-gray-500">Signed in: <span class="text-gray-300"><?= esc($username ?? 'admin') ?></span></p>
                <div class="mt-2 flex gap-2">
                    <a href="/useradmin/login" class="rounded-lg border border-gray-700 px-3 py-2 text-xs font-medium text-gray-200 hover:border-gray-600 hover:text-white">Re-verify</a>
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

    <section class="mb-5 rounded-2xl border border-gray-800 bg-gray-900 p-4 sm:p-5">
        <div class="mb-4 flex items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-white">Create user</h2>
                <p class="mt-1 text-sm text-gray-400">Add a new account with its initial role and password.</p>
            </div>
        </div>

        <form action="/useradmin/users" method="POST" class="grid gap-3 md:grid-cols-4" novalidate>
            <?= csrf_field() ?>
            <label class="block">
                <span class="mb-1 block text-xs font-semibold uppercase tracking-widest text-gray-500">Username</span>
                <input
                    type="text"
                    name="username"
                    value="<?= esc(old('username')) ?>"
                    class="w-full rounded-lg border border-gray-700 bg-gray-800 px-3 py-2 text-sm text-gray-100 placeholder-gray-500 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    placeholder="new.user"
                    required
                >
            </label>
            <label class="block">
                <span class="mb-1 block text-xs font-semibold uppercase tracking-widest text-gray-500">Password</span>
                <input
                    type="password"
                    name="password"
                    class="w-full rounded-lg border border-gray-700 bg-gray-800 px-3 py-2 text-sm text-gray-100 placeholder-gray-500 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    placeholder="Minimum 8 characters"
                    required
                >
            </label>
            <label class="block">
                <span class="mb-1 block text-xs font-semibold uppercase tracking-widest text-gray-500">Role</span>
                <select
                    name="role"
                    class="w-full rounded-lg border border-gray-700 bg-gray-800 px-3 py-2 text-sm text-gray-100 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
                    <option value="user" <?= old('role', 'user') === 'user' ? 'selected' : '' ?>>user</option>
                    <option value="admin" <?= old('role') === 'admin' ? 'selected' : '' ?>>admin</option>
                </select>
            </label>
            <div class="flex items-end">
                <button type="submit" class="w-full rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">
                    Create user
                </button>
            </div>
        </form>
    </section>

    <section class="overflow-hidden rounded-2xl border border-gray-800 bg-gray-900">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-800">
                <thead class="bg-gray-900/70">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-widest text-gray-500">Username</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-widest text-gray-500">Role</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-widest text-gray-500">Created</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-widest text-gray-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                <?php foreach (($users ?? []) as $user): ?>
                    <tr>
                        <td class="px-4 py-3 text-sm text-gray-200"><?= esc($user['username'] ?? '') ?></td>
                        <td class="px-4 py-3 text-sm text-gray-300"><?= esc($user['role'] ?? 'user') ?></td>
                        <td class="px-4 py-3 text-sm text-gray-400"><?= esc($user['created_at'] ?? '-') ?></td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex flex-wrap justify-end gap-2">
                                <a href="/useradmin/users/<?= (int) ($user['id'] ?? 0) ?>/edit" class="rounded-lg border border-gray-700 px-3 py-2 text-xs font-medium text-gray-200 hover:border-gray-600 hover:text-white">Edit</a>
                                <form action="/useradmin/users/<?= (int) ($user['id'] ?? 0) ?>/delete" method="POST" class="inline" onsubmit="return confirm('Delete this user?');">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="rounded-lg border border-red-500/30 px-3 py-2 text-xs font-semibold text-red-300 hover:border-red-400 hover:text-red-200">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="4" class="px-4 py-6 text-center text-sm text-gray-500">No users found.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>

</body>
</html>
