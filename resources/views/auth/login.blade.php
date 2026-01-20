<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Login - {{ config('app.name', 'PGN One Portal') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-gray-900">
    <div class="min-h-screen flex items-center justify-center bg-cover bg-center relative" 
         style="background-image: url('{{ asset('images/login-bg.png') }}');">
        
        <!-- Card -->
        <div class="bg-white rounded-[15px] shadow-2xl p-6 md:p-10 w-full max-w-[480px] mx-4 relative z-10 flex flex-col items-start transition-all duration-300">
             
             <!-- Logo -->
             <img src="{{ asset('images/pgn-logo.png') }}" alt="PGN Logo" class="w-[150px] h-auto mb-6 select-none">

             <!-- Title -->
             <h1 class="text-3xl font-extrabold mb-2 text-black leading-tight tracking-tight select-none">
                 Welcome!<br>
                 What’s your email?
             </h1>

             <!-- Form -->
             <form method="POST" action="{{ route('login') }}" class="w-full mt-6" x-data="{ loading: false }" @submit="loading = true">
                @csrf

                @php
                    $baseInputClasses = 'w-full h-[45px] px-4 text-base border rounded-[5px] focus:ring-2 block shadow-sm transition-all duration-200 placeholder-gray-400';
                    $errorClasses = 'border-red-500 focus:ring-red-500 focus:border-red-500';
                    $defaultClasses = 'border-gray-300 focus:ring-[#0492ff] focus:border-[#0492ff]';
                @endphp
                
                <!-- Email / Username -->
                <div class="mb-5 group">
                    <label for="email" class="block text-base font-semibold text-gray-700 mb-2 group-focus-within:text-[#0492ff] transition-colors duration-200">Username</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus 
                        class="{{ $baseInputClasses }} {{ $errors->has('email') ? $errorClasses : $defaultClasses }}"
                        placeholder="Enter your username or email">
                    <x-input-error :messages="$errors->get('email')" class="mt-1 text-sm text-red-500 font-medium" />
                </div>

                <!-- Password -->
                <div class="mb-8 group">
                    <label for="password" class="block text-base font-semibold text-gray-700 mb-2 group-focus-within:text-[#0492ff] transition-colors duration-200">Password</label>
                    <input id="password" type="password" name="password" required autocomplete="current-password"
                        class="{{ $baseInputClasses }} {{ $errors->has('password') ? $errorClasses : $defaultClasses }}"
                        placeholder="Enter your password">
                    <x-input-error :messages="$errors->get('password')" class="mt-1 text-sm text-red-500 font-medium" />
                </div>

                <!-- Button -->
                <div class="flex items-center mt-6">
                    <button type="submit" :disabled="loading" :class="{ 'opacity-75 cursor-not-allowed': loading }"
                            class="bg-[#0492ff] text-white text-base font-bold py-2.5 px-8 rounded-[5px] hover:bg-blue-600 transition-all duration-200 flex items-center justify-center shadow-md hover:shadow-lg min-w-[120px]">
                        <span x-show="!loading">Login</span>
                        <span x-show="loading" class="flex items-center" style="display: none;">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Processing...
                        </span>
                    </button>
                </div>
             </form>
        </div>
    </div>
</body>
</html>