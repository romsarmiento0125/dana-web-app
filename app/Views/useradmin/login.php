<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dana AI — Admin Verification</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="h-full min-h-[100dvh] bg-gray-950 text-gray-100">

<div class="mx-auto flex min-h-[100dvh] w-full max-w-md items-center px-4 py-8 sm:px-6">
    <div class="w-full rounded-2xl border border-gray-800 bg-gray-900 p-5 shadow-2xl sm:p-6">
        <p class="text-xs font-semibold uppercase tracking-widest text-indigo-400">User Admin</p>
        <h1 class="mt-2 text-xl font-semibold text-white">Admin verification required</h1>
        <p class="mt-1 text-sm text-gray-400">
            Signed in as <span class="font-medium text-gray-200"><?= esc($username ?? 'admin') ?></span>. Enter your password to continue.
        </p>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="mt-4 rounded-xl border border-red-500/20 bg-red-500/10 px-4 py-3 text-sm text-red-300">
                <?= esc(session()->getFlashdata('error')) ?>
            </div>
        <?php endif; ?>

        <form action="/useradmin/login" method="POST" class="mt-5 space-y-4" novalidate>
            <?= csrf_field() ?>
            <div>
                <label for="password" class="mb-1.5 block text-sm font-medium text-gray-300">Admin password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                    autocomplete="current-password"
                    placeholder="••••••••"
                    class="w-full rounded-xl border border-gray-700 bg-gray-800 px-4 py-3 text-sm text-white placeholder-gray-500 transition focus:border-transparent focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
            </div>

            <button
                type="submit"
                class="flex w-full items-center justify-center rounded-xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
            >
                Verify and open user admin
            </button>
        </form>

        <div class="mt-4 flex items-center justify-between text-xs text-gray-500">
            <span>Session window: 15 minutes</span>
            <a href="/dashboard" class="font-medium text-gray-300 transition hover:text-white">Back to dashboard</a>
        </div>
    </div>
</div>

</body>
</html>
