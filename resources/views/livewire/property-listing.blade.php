<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Property;

new class extends Component
{
    use WithPagination;

    public $search = '';
    public $region = '';
    public $type = '';
    public $maxPrice = 50000000;

    public function updating($property)
    {
        if (in_array($property, ['search', 'region', 'type', 'maxPrice'])) {
            $this->resetPage();
        }
    }

    public function with(): array
    {
        $query = Property::with('seller');

        if ($this->search) {
            $query->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('city', 'like', '%' . $this->search . '%');
        }

        if ($this->region) {
            $query->where('region', $this->region);
        }

        if ($this->type) {
            $query->where('property_type', $this->type);
        }

        if ($this->maxPrice) {
            $query->where('price', '<=', $this->maxPrice);
        }

        return [
            'properties' => $query->orderBy('created_at', 'desc')->paginate(9),
        ];
    }
}; ?>

<div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="mb-8 border-b border-slate-200 pb-8 flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <h1 class="text-4xl font-extrabold text-slate-900 tracking-tight">Browse Properties</h1>
            <p class="text-slate-500 mt-2">Find your next home or investment opportunity.</p>
        </div>
        <div class="text-sm font-medium text-slate-500">
            Showing {{ $properties->firstItem() ?? 0 }}-{{ $properties->lastItem() ?? 0 }} of {{ $properties->total() }} results
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8 items-start">
        {{-- FILTERS SIDEBAR --}}
        <div class="lg:col-span-1 bg-white border border-slate-200 rounded-2xl p-6 shadow-sm sticky top-24">
            <h3 class="text-lg font-bold text-slate-900 mb-6 flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                Filter Search
            </h3>
            
            <div class="space-y-6">
                {{-- Keyword --}}
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Keyword</label>
                    <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search title or city..." class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-colors">
                </div>

                {{-- Region --}}
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Region</label>
                    <select wire:model.live="region" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-colors appearance-none">
                        <option value="">All Regions</option>
                        <option value="New Cairo">New Cairo</option>
                        <option value="Sheikh Zayed">Sheikh Zayed</option>
                        <option value="6th of October">6th of October</option>
                        <option value="North Coast">North Coast</option>
                        <option value="El Gouna">El Gouna</option>
                    </select>
                </div>

                {{-- Property Type --}}
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Property Type</label>
                    <select wire:model.live="type" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-colors appearance-none">
                        <option value="">All Types</option>
                        <option value="villa">Villa</option>
                        <option value="apartment">Apartment</option>
                        <option value="chalet">Chalet</option>
                        <option value="duplex">Duplex</option>
                        <option value="penthouse">Penthouse</option>
                    </select>
                </div>

                {{-- Max Price --}}
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2 flex justify-between">
                        Max Price
                        <span class="text-indigo-600">{{ number_format($maxPrice) }} EGP</span>
                    </label>
                    <input wire:model.live="maxPrice" type="range" min="1000000" max="100000000" step="1000000" class="w-full h-2 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-indigo-600">
                    <div class="flex justify-between text-xs text-slate-400 mt-1">
                        <span>1M</span><span>100M</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- PROPERTIES GRID --}}
        <div class="lg:col-span-3 relative">
            <div wire:loading class="absolute inset-0 z-10 bg-white/50 backdrop-blur-sm rounded-2xl flex items-center justify-center">
                <svg class="animate-spin w-10 h-10 text-indigo-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
            </div>

            @if($properties->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
                    @foreach($properties as $property)
                        <a href="{{ route('property.detail', ['propertyId' => $property->id]) }}" class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 group flex flex-col">
                            <div class="relative h-56 bg-slate-200 overflow-hidden">
                                <div class="absolute inset-0 bg-slate-300 flex items-center justify-center">
                                    <svg class="w-10 h-10 text-slate-400 group-hover:scale-110 transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 9.5L12 3l9 6.5V21H3V9.5z"/></svg>
                                </div>
                                <div class="absolute top-3 left-3 flex gap-2">
                                    <span class="px-2.5 py-1 bg-white/90 backdrop-blur-sm text-[10px] font-bold uppercase tracking-wider text-slate-800 rounded-md shadow-sm">
                                        {{ $property->property_type }}
                                    </span>
                                    <span class="px-2.5 py-1 bg-indigo-600 text-[10px] font-bold uppercase tracking-wider text-white rounded-md shadow-sm">
                                        {{ $property->payment_type }}
                                    </span>
                                </div>
                            </div>
                            <div class="p-5 flex-grow flex flex-col justify-between">
                                <div>
                                    <div class="text-xs font-semibold text-indigo-600 mb-1.5 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        {{ $property->region }}
                                    </div>
                                    <h3 class="text-base font-bold text-slate-900 leading-tight mb-2 group-hover:text-indigo-600 transition-colors line-clamp-2">
                                        {{ $property->title }}
                                    </h3>
                                </div>
                                <div class="mt-4">
                                    <p class="text-xl font-extrabold text-slate-900 mb-3">
                                        {{ number_format($property->price) }} <span class="text-xs font-normal text-slate-500">EGP</span>
                                    </p>
                                    <div class="flex items-center gap-3 text-xs text-slate-600 border-t border-slate-100 pt-3">
                                        <div class="flex items-center gap-1"><svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12h18M3 6h18M3 18h18"/></svg>{{ $property->bedrooms }}</div>
                                        <div class="flex items-center gap-1"><svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>{{ $property->bathrooms }}</div>
                                        <div class="flex items-center gap-1"><svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>{{ $property->area_sqm }}m²</div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
                <div class="mt-8">
                    {{ $properties->links('livewire::tailwind') }}
                </div>
            @else
                <div class="bg-white border border-slate-200 rounded-2xl p-12 text-center shadow-sm">
                    <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-2">No properties found</h3>
                    <p class="text-slate-500">We couldn't find any properties matching your current filters. Try adjusting them to see more results.</p>
                    <button wire:click="$set('search', ''); $set('region', ''); $set('type', ''); $set('maxPrice', 50000000);" class="mt-6 px-6 py-2.5 bg-indigo-50 text-indigo-700 font-semibold rounded-xl hover:bg-indigo-100 transition-colors">
                        Clear all filters
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>
