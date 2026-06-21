<?php

use Livewire\Volt\Component;
use App\Models\Property;

new class extends Component
{
    public $featuredProperties;

    public function mount()
    {
        // Load the 3 most viewed properties
        $this->featuredProperties = Property::with('seller')
            ->orderByDesc('views_count')
            ->take(3)
            ->get();
    }
}; ?>

<div class="space-y-20 pb-20">
    {{-- HERO SECTION --}}
    <section class="relative bg-slate-900 text-white pt-24 pb-32 overflow-hidden">
        {{-- Background Image Overlay (using a CSS gradient since we don't have images) --}}
        <div class="absolute inset-0 bg-gradient-to-br from-indigo-900 via-slate-900 to-black opacity-90 z-0"></div>
        <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?ixlib=rb-4.0.3&auto=format&fit=crop&w=2000&q=80')] bg-cover bg-center mix-blend-overlay opacity-30 z-0"></div>
        
        <div class="relative z-10 max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8 text-center mt-12">
            <h1 class="text-5xl md:text-6xl font-extrabold tracking-tight mb-6 text-transparent bg-clip-text bg-gradient-to-r from-white to-indigo-200">
                Discover Premium Real Estate <br class="hidden md:block"/> in Egypt
            </h1>
            <p class="text-xl md:text-2xl text-indigo-100 max-w-3xl mx-auto mb-10 opacity-90 font-light">
                Find your dream luxury villa, apartment, or coastal home in New Cairo, Sheikh Zayed, and beyond.
            </p>
            
            {{-- Quick Search Bar --}}
            <div class="max-w-4xl mx-auto bg-white/10 backdrop-blur-md p-3 rounded-2xl border border-white/20 shadow-2xl flex flex-col md:flex-row gap-3">
                <input type="text" placeholder="Search by city, region or keyword..." class="flex-grow bg-white/90 text-slate-800 rounded-xl px-5 py-4 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-medium placeholder-slate-400">
                <button onclick="window.location.href='{{ route('properties') }}'" class="bg-indigo-600 hover:bg-indigo-500 text-white font-bold px-8 py-4 rounded-xl transition-colors shadow-lg whitespace-nowrap">
                    Search Properties
                </button>
            </div>
        </div>
    </section>

    {{-- BROWSE BY REGION --}}
    <section class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-end mb-8">
            <div>
                <h2 class="text-3xl font-bold text-slate-900 tracking-tight">Explore by Region</h2>
                <p class="text-slate-500 mt-2">The most sought-after neighborhoods.</p>
            </div>
            <a href="{{ route('properties') }}" class="hidden sm:flex text-indigo-600 font-semibold hover:text-indigo-800 items-center gap-1 group">
                View all regions
                <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
        </div>
        
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
            @foreach(['New Cairo' => 'bg-indigo-600', 'Sheikh Zayed' => 'bg-emerald-600', 'North Coast' => 'bg-blue-500', 'El Gouna' => 'bg-amber-500'] as $region => $color)
                <a href="{{ route('properties', ['region' => $region]) }}" class="group relative rounded-2xl overflow-hidden aspect-square flex items-end shadow-md hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 cursor-pointer">
                    <div class="absolute inset-0 {{ $color }} opacity-80 group-hover:opacity-90 transition-opacity"></div>
                    <div class="relative z-10 p-6 w-full text-white">
                        <h3 class="text-xl font-bold mb-1">{{ $region }}</h3>
                        <p class="text-sm opacity-80 group-hover:opacity-100 transition-opacity flex items-center gap-1">
                            Explore <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </p>
                    </div>
                </a>
            @endforeach
        </div>
    </section>

    {{-- FEATURED PROPERTIES --}}
    <section class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8 bg-slate-50 py-16 rounded-3xl">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-slate-900 tracking-tight">Featured Properties</h2>
            <p class="text-slate-500 mt-2 max-w-2xl mx-auto">Hand-picked premium listings that offer exceptional value and luxury living.</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($featuredProperties as $property)
                <a href="{{ route('property.detail', ['propertyId' => $property->id]) }}" class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 group flex flex-col">
                    <div class="relative h-64 bg-slate-200 overflow-hidden">
                        {{-- Dummy Image Placeholder --}}
                        <div class="absolute inset-0 bg-slate-300 flex items-center justify-center">
                            <svg class="w-12 h-12 text-slate-400 group-hover:scale-110 transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 9.5L12 3l9 6.5V21H3V9.5z"/>
                            </svg>
                        </div>
                        <div class="absolute top-4 left-4">
                            <span class="px-3 py-1 bg-white/90 backdrop-blur-sm text-xs font-bold uppercase tracking-wider text-slate-800 rounded-full shadow-sm">
                                {{ $property->property_type }}
                            </span>
                        </div>
                        <div class="absolute bottom-4 right-4">
                            <span class="px-3 py-1 bg-indigo-600 text-xs font-bold uppercase tracking-wider text-white rounded-full shadow-sm">
                                {{ $property->payment_type }}
                            </span>
                        </div>
                    </div>
                    <div class="p-6 flex-grow flex flex-col justify-between">
                        <div>
                            <div class="text-sm font-medium text-indigo-600 mb-2 flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                {{ $property->region }}, {{ $property->city }}
                            </div>
                            <h3 class="text-lg font-bold text-slate-900 leading-tight mb-2 group-hover:text-indigo-600 transition-colors">
                                {{ $property->title }}
                            </h3>
                        </div>
                        <div class="mt-6">
                            <p class="text-2xl font-extrabold text-slate-900 mb-4">
                                {{ number_format($property->price) }} <span class="text-sm font-normal text-slate-500">EGP</span>
                            </p>
                            <div class="flex items-center gap-4 text-sm text-slate-600 border-t border-slate-100 pt-4">
                                <div class="flex items-center gap-1"><svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12h18M3 6h18M3 18h18"/></svg> {{ $property->bedrooms }}</div>
                                <div class="flex items-center gap-1"><svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg> {{ $property->bathrooms }}</div>
                                <div class="flex items-center gap-1"><svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg> {{ $property->area_sqm }}m²</div>
                            </div>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
        <div class="text-center mt-10">
            <a href="{{ route('properties') }}" class="inline-flex items-center gap-2 px-6 py-3 border border-slate-300 rounded-xl font-bold text-slate-700 hover:bg-slate-100 transition-colors">
                View All Properties
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
        </div>
    </section>

    {{-- Chatbot Interface Widget --}}
    <livewire:chatbot-interface />
</div>
