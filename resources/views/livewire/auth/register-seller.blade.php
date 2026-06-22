<?php

use Livewire\Volt\Component;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

new class extends Component {
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function register()
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'seller',
            'is_verified' => false,
        ]);

        Auth::login($user);

        return redirect()->intended('/admin/properties');
    }
}; ?>

<div class="min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8 bg-slate-900">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <h2 class="mt-6 text-center text-3xl font-extrabold text-white">
            Sell Your Property on Aqary
        </h2>
        <p class="mt-2 text-center text-sm text-slate-300">
            Create your seller account to list properties and manage leads.
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-4 shadow-xl sm:rounded-2xl sm:px-10 border border-slate-100">
            <form wire:submit="register" class="space-y-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-slate-700">Full Name / Agency Name</label>
                    <div class="mt-1">
                        <input wire:model="name" id="name" type="text" required class="appearance-none block w-full px-3 py-2 border border-slate-300 rounded-lg shadow-sm placeholder-slate-400 focus:outline-none focus:ring-slate-900 focus:border-slate-900 sm:text-sm">
                    </div>
                    @error('name') <span class="text-sm text-red-600 mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700">Work Email address</label>
                    <div class="mt-1">
                        <input wire:model="email" id="email" type="email" required class="appearance-none block w-full px-3 py-2 border border-slate-300 rounded-lg shadow-sm placeholder-slate-400 focus:outline-none focus:ring-slate-900 focus:border-slate-900 sm:text-sm">
                    </div>
                    @error('email') <span class="text-sm text-red-600 mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
                    <div class="mt-1">
                        <input wire:model="password" id="password" type="password" required class="appearance-none block w-full px-3 py-2 border border-slate-300 rounded-lg shadow-sm placeholder-slate-400 focus:outline-none focus:ring-slate-900 focus:border-slate-900 sm:text-sm">
                    </div>
                    @error('password') <span class="text-sm text-red-600 mt-1">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-slate-700">Confirm Password</label>
                    <div class="mt-1">
                        <input wire:model="password_confirmation" id="password_confirmation" type="password" required class="appearance-none block w-full px-3 py-2 border border-slate-300 rounded-lg shadow-sm placeholder-slate-400 focus:outline-none focus:ring-slate-900 focus:border-slate-900 sm:text-sm">
                    </div>
                </div>

                <div>
                    <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-lg text-sm font-bold text-white bg-slate-900 hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-900 transition-colors">
                        Create Seller Account & Continue to Dashboard
                        <span wire:loading wire:target="register" class="ml-2">...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
