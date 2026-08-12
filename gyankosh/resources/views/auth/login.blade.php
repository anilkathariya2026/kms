<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Gyankosh Smart Knowledge Management System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gradient-to-br from-blue-500 to-blue-700 min-h-screen flex items-center justify-center p-4">
    <div x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" :class="{ 'dark': darkMode }" class="w-full max-w-md">
        <!-- Login Card -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-8 py-10 text-center">
                <h1 class="text-3xl font-bold text-white mb-2">GYANKOSH</h1>
                <p class="text-blue-100">Smart Knowledge Management System</p>
                <p class="text-blue-200 text-sm mt-1">Discover • Manage • Share • Learn</p>
            </div>

            <!-- Login Form -->
            <div class="px-8 py-8">
                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf

                    <!-- Email Input -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Email Address
                        </label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               value="{{ old('email') }}"
                               required 
                               autofocus
                               class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white @error('email') border-red-500 @enderror"
                               placeholder="your@email.com">
                        @error('email')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password Input -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Password
                        </label>
                        <input type="password" 
                               id="password" 
                               name="password" 
                               required 
                               class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white @error('password') border-red-500 @enderror"
                               placeholder="••••••••">
                        @error('password')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Remember Me & Forgot Password -->
                    <div class="flex items-center justify-between">
                        <label class="flex items-center">
                            <input type="checkbox" 
                                   name="remember" 
                                   class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Remember me</span>
                        </label>
                        <a href="#" class="text-sm text-blue-600 hover:text-blue-500 dark:text-blue-400">
                            Forgot password?
                        </a>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" 
                            class="w-full bg-gradient-to-r from-blue-600 to-blue-700 text-white py-3 rounded-lg font-semibold hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-200">
                        Sign In
                    </button>
                </form>

                <!-- Demo Accounts Info -->
                <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <p class="text-xs text-center text-gray-500 dark:text-gray-400 mb-3">Demo Accounts:</p>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div class="bg-gray-50 dark:bg-gray-700 p-2 rounded">
                            <strong class="text-gray-700 dark:text-gray-300">Admin:</strong>
                            <p class="text-gray-600 dark:text-gray-400">admin@gyankosh.com</p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 p-2 rounded">
                            <strong class="text-gray-700 dark:text-gray-300">Librarian:</strong>
                            <p class="text-gray-600 dark:text-gray-400">librarian@gyankosh.com</p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 p-2 rounded">
                            <strong class="text-gray-700 dark:text-gray-300">Staff:</strong>
                            <p class="text-gray-600 dark:text-gray-400">staff@gyankosh.com</p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 p-2 rounded">
                            <strong class="text-gray-700 dark:text-gray-300">Student:</strong>
                            <p class="text-gray-600 dark:text-gray-400">student@gyankosh.com</p>
                        </div>
                    </div>
                    <p class="text-xs text-center text-gray-500 dark:text-gray-400 mt-2">Password: password</p>
                </div>
            </div>

            <!-- Footer -->
            <div class="bg-gray-50 dark:bg-gray-700 px-8 py-4 text-center">
                <a href="/" class="text-sm text-blue-600 hover:text-blue-500 dark:text-blue-400">
                    ← Back to Home
                </a>
            </div>
        </div>

        <!-- Dark Mode Toggle -->
        <button @click="darkMode = !darkMode; localStorage.setItem('darkMode', darkMode)" 
                class="fixed bottom-4 right-4 p-3 bg-white dark:bg-gray-800 rounded-full shadow-lg text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
            <svg x-show="!darkMode" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
            </svg>
            <svg x-show="darkMode" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
        </button>
    </div>
</body>
</html>
