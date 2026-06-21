<?php

use Livewire\Volt\Component;
use App\Models\Property;
use App\Models\Appointment;

new class extends Component {
    public Property $property;

    // Interactive Installment Calculator State
    public int $downPaymentPercentage = 10;
    public int $paymentYears = 5;

    // Booking System State
    public ?string $selectedTimeslot = null;
    public bool $bookingSuccess = false;

    // Regional averages for Price/Sqm insights
    private array $regionalAverages = [
        'New Cairo' => 25000,
        'Sheikh Zayed' => 27000,
        'Nasr City' => 20000,
        'Maadi' => 30000,
        '6th of October' => 18000,
        'Dokki' => 35000,
        'Smouha' => 22000,
        'Sidi Beshr' => 15000,
    ];

    public function mount(Property $property)
    {
        $this->property = $property;
    }

    // Computed Property: Installment calculations
    public function getInstallmentsProperty(): array
    {
        $price = (float) $this->property->price;
        $downPaymentAmount = $price * ($this->downPaymentPercentage / 100);
        $remainingBalance = $price - $downPaymentAmount;
        $totalMonths = $this->paymentYears * 12;
        $totalQuarters = $this->paymentYears * 4;

        return [
            'downPaymentAmount' => $downPaymentAmount,
            'remainingBalance' => $remainingBalance,
            'monthly' => $totalMonths > 0 ? $remainingBalance / $totalMonths : 0,
            'quarterly' => $totalQuarters > 0 ? $remainingBalance / $totalQuarters : 0,
        ];
    }

    // Computed Property: Price insight logic
    public function getPriceInsightProperty(): array
    {
        $pricePerSqm = $this->property->area_sqm > 0 
            ? (float) $this->property->price / $this->property->area_sqm 
            : 0;

        $average = $this->regionalAverages[$this->property->region] ?? 20000; // default benchmark

        if ($pricePerSqm < $average) {
            return ['status' => 'Below Market Value', 'color' => 'bg-green-100 text-green-800'];
        } elseif ($pricePerSqm > $average) {
            return ['status' => 'Premium Value', 'color' => 'bg-blue-100 text-blue-800'];
        } else {
            return ['status' => 'Market Price', 'color' => 'bg-amber-100 text-amber-800'];
        }
    }

    public function selectTimeslot(string $timeslot)
    {
        $this->selectedTimeslot = $timeslot;
        $this->bookingSuccess = false;
    }

    public function bookInspection()
    {
        $this->validate([
            'selectedTimeslot' => 'required|string'
        ]);

        // Simulating the booking action for the authenticated buyer.
        // Assuming auth()->id() is present. If not, fallback to a dummy ID for now.
        $buyerId = auth()->id() ?? 1;

        Appointment::create([
            'property_id' => $this->property->id,
            'buyer_id' => $buyerId,
            'selected_timeslot' => $this->selectedTimeslot,
            'status' => 'pending',
        ]);

        $this->bookingSuccess = true;
    }
}; ?>

<div class="max-w-5xl mx-auto p-6 bg-white shadow-lg rounded-xl mt-10">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        
        <!-- Left Column: Details -->
        <div class="col-span-2">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $property->title }}</h1>
            <p class="text-gray-500 mb-4">{{ $property->city }} &bull; {{ $property->region }}</p>

            <div class="flex flex-wrap gap-2 mb-6">
                <!-- Insight Badge -->
                @php $insight = $this->priceInsight; @endphp
                <span class="px-3 py-1 text-sm font-semibold rounded-full {{ $insight['color'] }}">
                    {{ $insight['status'] }}
                </span>
                <span class="px-3 py-1 text-sm font-semibold bg-gray-100 text-gray-800 rounded-full">
                    {{ ucfirst($property->property_type) }}
                </span>
                @if($property->is_furnished)
                    <span class="px-3 py-1 text-sm font-semibold bg-purple-100 text-purple-800 rounded-full">
                        Furnished
                    </span>
                @endif
            </div>

            <!-- Basic Structural Details -->
            <div class="grid grid-cols-3 gap-4 border-y border-gray-200 py-4 mb-6">
                <div class="text-center border-r border-gray-200">
                    <p class="text-gray-500 text-sm">Area</p>
                    <p class="font-bold text-lg">{{ $property->area_sqm }} Sqm</p>
                </div>
                <div class="text-center border-r border-gray-200">
                    <p class="text-gray-500 text-sm">Bedrooms</p>
                    <p class="font-bold text-lg">{{ $property->bedrooms }}</p>
                </div>
                <div class="text-center">
                    <p class="text-gray-500 text-sm">Bathrooms</p>
                    <p class="font-bold text-lg">{{ $property->bathrooms }}</p>
                </div>
            </div>

            <!-- Interactive Installment Calculator -->
            @if($property->payment_type === 'installments')
                <div class="bg-gray-50 p-6 rounded-lg mb-6 border border-gray-100">
                    <h3 class="text-xl font-bold text-gray-800 mb-4">Payment Calculator</h3>
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Down Payment (%)</label>
                            <input type="range" wire:model.live="downPaymentPercentage" min="5" max="50" step="5" class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer">
                            <div class="text-right text-sm text-gray-500 mt-1">{{ $downPaymentPercentage }}%</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Years to Pay</label>
                            <input type="range" wire:model.live="paymentYears" min="1" max="10" step="1" class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer">
                            <div class="text-right text-sm text-gray-500 mt-1">{{ $paymentYears }} Years</div>
                        </div>
                    </div>

                    @php $calc = $this->installments; @endphp
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div class="bg-white p-3 rounded shadow-sm border border-gray-100">
                            <p class="text-xs text-gray-500">Down Payment</p>
                            <p class="font-bold text-gray-900">{{ number_format($calc['downPaymentAmount']) }} EGP</p>
                        </div>
                        <div class="bg-white p-3 rounded shadow-sm border border-gray-100">
                            <p class="text-xs text-gray-500">Balance</p>
                            <p class="font-bold text-gray-900">{{ number_format($calc['remainingBalance']) }} EGP</p>
                        </div>
                        <div class="bg-white p-3 rounded shadow-sm border border-gray-100 border-l-4 border-l-blue-500">
                            <p class="text-xs text-blue-600">Monthly</p>
                            <p class="font-bold text-blue-900">{{ number_format($calc['monthly']) }} EGP</p>
                        </div>
                        <div class="bg-white p-3 rounded shadow-sm border border-gray-100 border-l-4 border-l-indigo-500">
                            <p class="text-xs text-indigo-600">Quarterly</p>
                            <p class="font-bold text-indigo-900">{{ number_format($calc['quarterly']) }} EGP</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- PDF CTA -->
            <div class="mt-6">
                <a href="/properties/{{ $property->id }}/pdf" target="_blank" class="inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                    <svg class="mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Download Property Brochure (PDF)
                </a>
            </div>

        </div>

        <!-- Right Column: Booking & Seller -->
        <div class="col-span-1">
            <!-- Seller Card -->
            <div class="bg-gray-50 p-6 rounded-lg border border-gray-100 mb-6 flex items-center gap-4">
                <div class="w-12 h-12 bg-gray-300 rounded-full flex items-center justify-center text-gray-600 font-bold text-xl">
                    {{ substr($property->seller->name, 0, 1) }}
                </div>
                <div>
                    <div class="flex items-center gap-1">
                        <h4 class="font-bold text-gray-900">{{ $property->seller->name }}</h4>
                        @if($property->seller->is_verified)
                            <x-heroicon-s-check-badge class="w-5 h-5 text-blue-500" title="Verified Seller" />
                        @endif
                    </div>
                    <p class="text-sm text-gray-500">Listed by Seller</p>
                </div>
            </div>

            <!-- Booking System -->
            <div class="bg-white p-6 rounded-lg border border-gray-200 shadow-sm">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Book an Inspection</h3>
                
                @if(is_array($property->timeslots) && count($property->timeslots) > 0)
                    <p class="text-sm text-gray-600 mb-3">Select a preferred timeslot:</p>
                    <div class="flex flex-col gap-2 mb-6">
                        @foreach($property->timeslots as $timeslot)
                            <button 
                                wire:click="selectTimeslot('{{ $timeslot }}')" 
                                class="text-left px-4 py-3 rounded-lg border text-sm transition-all focus:outline-none 
                                {{ $selectedTimeslot === $timeslot ? 'border-blue-500 bg-blue-50 text-blue-800 font-semibold shadow-sm' : 'border-gray-200 text-gray-700 hover:border-gray-300 hover:bg-gray-50' }}"
                            >
                                {{ \Carbon\Carbon::parse($timeslot)->format('l, M j, Y h:i A') }}
                            </button>
                        @endforeach
                    </div>

                    @if($bookingSuccess)
                        <div class="mb-4 p-4 bg-green-50 text-green-800 rounded-lg text-sm border border-green-200 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Booking request sent successfully!
                        </div>
                    @else
                        <button 
                            wire:click="bookInspection" 
                            @if(!$selectedTimeslot) disabled @endif
                            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-gray-900 hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-900 disabled:opacity-50 disabled:cursor-not-allowed transition-all relative overflow-hidden"
                        >
                            <span wire:loading.remove wire:target="bookInspection">Confirm Booking</span>
                            <span wire:loading wire:target="bookInspection" class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                Processing...
                            </span>
                        </button>
                    @endif
                @else
                    <div class="p-4 bg-gray-50 text-gray-500 rounded-lg text-sm border border-gray-100 text-center">
                        No inspection timeslots available at the moment.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
