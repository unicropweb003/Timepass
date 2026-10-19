<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,follow">
    <title>Admin Login · TimePass</title>
    <script src="https://cdn.tailwindcss.com/3.4.16"></script>
</head>
<body class="flex min-h-screen items-center justify-center bg-gradient-to-br from-gray-900 via-gray-900 to-indigo-950 p-4 text-gray-900">
    <div class="w-full max-w-md">
        <div class="mb-6 text-center text-white">
            <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-600 text-xl font-bold">T</span>
            <h1 class="mt-4 text-2xl font-bold">TimePass Admin</h1>
            <p class="mt-1 text-sm text-gray-400">Sign in to manage your store</p>
        </div>

        <div class="rounded-2xl bg-white p-8 shadow-xl">
            @if ($errors->any())
                <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login.submit') }}" class="space-y-5">
                @csrf
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                        class="mt-1 block w-full rounded-lg border border-gray-300 px-3.5 py-2.5 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/40"
                        placeholder="admin@example.com">
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <input type="password" id="password" name="password" required
                        class="mt-1 block w-full rounded-lg border border-gray-300 px-3.5 py-2.5 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/40"
                        placeholder="••••••••">
                </div>
                <label class="flex items-center gap-2 text-sm text-gray-600">
                    <input type="checkbox" name="remember" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    Remember me
                </label>
                <button type="submit" class="w-full rounded-lg bg-indigo-600 px-4 py-2.5 font-semibold text-white shadow-sm transition hover:bg-indigo-500">
                    Sign in
                </button>
            </form>

            @if ($googleEnabled)
                <div class="my-6 flex items-center gap-4">
                    <div class="h-px flex-1 bg-gray-200"></div>
                    <span class="text-xs font-medium uppercase tracking-wide text-gray-400">or</span>
                    <div class="h-px flex-1 bg-gray-200"></div>
                </div>
                <a href="{{ route('admin.google.redirect') }}"
                   class="flex w-full items-center justify-center gap-3 rounded-lg border border-gray-300 px-4 py-2.5 font-medium text-gray-700 transition hover:bg-gray-50">
                    <svg class="h-5 w-5" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1Z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84A11 11 0 0 0 12 23Z"/><path fill="#FBBC05" d="M5.84 14.1a6.6 6.6 0 0 1 0-4.2V7.06H2.18a11 11 0 0 0 0 9.88l3.66-2.84Z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1A11 11 0 0 0 2.18 7.06l3.66 2.84C6.71 7.3 9.14 5.38 12 5.38Z"/></svg>
                    Continue with Google
                </a>
            @else
                <p class="mt-6 rounded-lg bg-amber-50 px-3 py-2 text-center text-xs text-amber-700">
                    Google sign-in is not configured yet. Add your Google credentials to <code>.env</code> to enable it.
                </p>
            @endif
        </div>

        <p class="mt-6 text-center text-xs text-gray-500">
            &larr; <a href="{{ route('home') }}" class="hover:text-gray-300">Back to store</a>
        </p>
    </div>
</body>
</html>
