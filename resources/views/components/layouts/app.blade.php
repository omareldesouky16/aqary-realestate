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
                        @if(auth()->user()->role === 'admin')
                            <a href="/admin" class="text-sm font-bold bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">Dashboard</a>
                        @elseif(auth()->user()->role === 'seller')
                            <a href="/admin/properties" class="text-sm font-bold bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">Dashboard</a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-sm font-medium text-red-600 hover:text-red-800">Logout</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">Login</a>
                        <a href="{{ route('register') }}" class="text-sm font-medium bg-indigo-50 text-indigo-700 px-3 py-1.5 rounded hover:bg-indigo-100">Register</a>
                    @endauth
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="flex-grow">
            {{ $slot }}
        </main>

        <!-- Optional Footer -->
        <footer class="bg-white border-t border-gray-200 mt-12 py-10 text-center">
            <div class="max-w-screen-xl mx-auto px-4 flex flex-col items-center gap-4">
                <p class="text-lg font-medium text-slate-700">Are you a property owner or agent?</p>
                <a href="{{ route('seller.register') }}" class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-bold rounded-xl text-white bg-slate-900 hover:bg-slate-800 transition-colors shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                    Sell on Aqary
                </a>
                <p class="text-sm text-gray-500 mt-6">&copy; {{ date('Y') }} Egyptian Real Estate. All rights reserved.</p>
            </div>
        </footer>

        @livewireScripts
    </body>
</html>
