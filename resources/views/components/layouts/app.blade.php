<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? 'Egyptian Real Estate' }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="bg-gray-50 text-slate-800 antialiased min-h-screen flex flex-col">
        <!-- Optional Navbar -->
        <nav class="bg-white shadow-sm border-b border-gray-100 py-4">
            <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-between items-center">
                <a href="{{ route('home') }}" class="font-bold text-xl text-indigo-600">RealEstate Egypt</a>
                <div class="flex gap-4 items-center">
                    <a href="{{ route('properties') }}" class="text-sm font-medium text-gray-600 hover:text-indigo-600">Browse Properties</a>
                    @auth
                        <a href="{{ route('profile.edit') }}" class="text-sm font-medium text-gray-600 hover:text-indigo-600">Profile</a>
                        <a href="/admin" class="text-sm font-bold bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">Dashboard</a>
                    @else
                        <a href="/admin/login" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">Login / Register</a>
                    @endauth
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="flex-grow">
            {{ $slot }}
        </main>

        <!-- Optional Footer -->
        <footer class="bg-white border-t border-gray-200 mt-12 py-8 text-center text-sm text-gray-500">
            &copy; {{ date('Y') }} Egyptian Real Estate. All rights reserved.
        </footer>

        @livewireScripts
    </body>
</html>
