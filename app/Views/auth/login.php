<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dana AI — Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        gray: {
                            950: '#0a0a0f',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="h-full min-h-[100dvh] bg-gray-950">

    <div class="flex min-h-[100dvh] items-center justify-center px-4 py-8 sm:px-6">
    <div class="w-full max-w-sm">

        <!-- Logo / Brand -->
        <div class="text-center mb-6 sm:mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-indigo-600 mb-4">
                <!-- Sparkle icon -->
                <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z" />
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Dana AI</h1>
            <p class="mt-1 text-sm text-gray-400">Sign in to your workspace</p>
        </div>

        <!-- Error Banner -->
        <?php if (session()->getFlashdata('error')): ?>
        <div class="mb-4 flex items-start gap-3 rounded-xl bg-red-500/10 border border-red-500/20 px-4 py-3">
            <svg class="w-5 h-5 text-red-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
            </svg>
            <span class="text-sm text-red-300"><?= esc(session()->getFlashdata('error')) ?></span>
        </div>
        <?php endif; ?>

        <!-- Login Card -->
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5 shadow-2xl sm:p-6">
            <form action="/login" method="POST" class="space-y-4" novalidate>
                <?= csrf_field() ?>

                <div>
                    <label for="username" class="block text-sm font-medium text-gray-300 mb-1.5">Username</label>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        value="<?= esc(old('username')) ?>"
                        required
                        autocomplete="username"
                        placeholder="your_username"
                           class="w-full rounded-xl bg-gray-800 border border-gray-700 text-white placeholder-gray-500
                               px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent
                               transition"
                    >
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-300 mb-1.5">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        placeholder="••••••••"
                           class="w-full rounded-xl bg-gray-800 border border-gray-700 text-white placeholder-gray-500
                               px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent
                               transition"
                    >
                </div>

                <button
                    type="submit"
                          class="w-full flex items-center justify-center gap-2 rounded-xl bg-indigo-600 hover:bg-indigo-500
                              text-white font-semibold text-sm px-4 py-3 transition focus:outline-none
                           focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-gray-900"
                >
                    Sign in
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                </button>
            </form>
        </div>

        <p class="mt-5 text-center text-xs text-gray-600 sm:mt-6">Dana AI &copy; <?= date('Y') ?></p>
    </div>
    </div>

</body>
</html>
