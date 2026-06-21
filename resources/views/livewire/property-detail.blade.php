<?php

use Livewire\Volt\Component;
use App\Models\Property;
use App\Models\Appointment;
use Illuminate\Support\Facades\Auth;

/**
 * PropertyDetail - Livewire 4 Single-File Component
 *
 * Displays a full property details page for buyers.
 * Features:
 *  - Price per Sqm market insight badge
 *  - Interactive installment calculator
 *  - Timeslot booking chips with stateful success indicator
 *  - Secure PDF brochure download CTA
 *  - Verified seller badge
 */
new class extends Component
{
    // ── COMPONENT INPUTS ────────────────────────────────────────────────────
    public int $propertyId;

    // ── LOADED MODEL ────────────────────────────────────────────────────────
    public Property $property;

    // ── INSTALLMENT CALCULATOR STATE ────────────────────────────────────────
    public int $downPaymentPercentage = 10;
    public int $paymentYears          = 5;

    // ── BOOKING STATE ───────────────────────────────────────────────────────
    public ?string $selectedTimeslot  = null;
    public bool    $bookingSuccess    = false;
    public bool    $bookingLoading    = false;
    public string  $bookingError      = '';

    // ── HARDCODED EGYPTIAN REGIONAL BENCHMARKS (EGP / sqm) ──────────────────
    protected array $regionalBenchmarks = [
        'New Cairo'                  => 25000,
        'Sheikh Zayed'               => 27000,
        '6th of October'             => 18000,
        'Maadi'                      => 30000,
        'Zamalek'                    => 45000,
        'Heliopolis'                 => 32000,
        'Nasr City'                  => 20000,
        'Dokki'                      => 28000,
        'Mohandessin'                => 26000,
        'Smouha'                     => 22000,
        'San Stefano'                => 35000,
        'Gleem'                      => 30000,
        'Hurghada'                   => 15000,
        'El Gouna'                   => 22000,
        'Sahel'                      => 12000,
        'Marina'                     => 14000,
        'Sharm El Sheikh'            => 18000,
        'New Administrative Capital' => 20000,
        'Downtown Cairo'             => 38000,
        'Sahl Hasheesh'              => 20000,
    ];

    // ── LIFECYCLE ────────────────────────────────────────────────────────────
    public function mount(int $propertyId): void
    {
        $this->propertyId = $propertyId;
        $this->property   = Property::with('seller')->findOrFail($propertyId);

        // Increment views counter
        $this->property->increment('views_count');
    }

    // ── COMPUTED: Price per Sqm ──────────────────────────────────────────────
    public function getPricePerSqmProperty(): float
    {
        if (! $this->property->area_sqm || $this->property->area_sqm === 0) {
            return 0.0;
        }
        return round($this->property->price / $this->property->area_sqm, 2);
    }

    // ── COMPUTED: Market Benchmark for the region ────────────────────────────
    public function getRegionalBenchmarkProperty(): ?int
    {
        return $this->regionalBenchmarks[$this->property->region] ?? null;
    }

    // ── COMPUTED: Market Value Badge ─────────────────────────────────────────
    public function getMarketValueBadgeProperty(): array
    {
        $pricePerSqm = $this->getPricePerSqmProperty();
        $benchmark   = $this->getRegionalBenchmarkProperty();

        if (! $benchmark) {
            return ['label' => 'No Benchmark', 'classes' => 'bg-gray-100 text-gray-600 border-gray-200'];
        }

        if ($pricePerSqm < $benchmark) {
            return [
                'label'   => '✅ Below Market Value',
                'classes' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                'dot'     => 'bg-emerald-500',
                'detail'  => number_format($benchmark - $pricePerSqm) . ' EGP/m² below benchmark',
            ];
        }

        if ($pricePerSqm === (float) $benchmark) {
            return [
                'label'   => '🟡 Market Price',
                'classes' => 'bg-amber-50 text-amber-700 border-amber-200',
                'dot'     => 'bg-amber-500',
                'detail'  => 'Exactly at regional average',
            ];
        }

        return [
            'label'   => '💎 Premium Value',
            'classes' => 'bg-blue-50 text-blue-700 border-blue-200',
            'dot'     => 'bg-blue-500',
            'detail'  => number_format($pricePerSqm - $benchmark) . ' EGP/m² above benchmark',
        ];
    }

    // ── COMPUTED: Installment Calculator ─────────────────────────────────────
    public function getInstallmentDataProperty(): array
    {
        $price              = (float) $this->property->price;
        $downPct            = max(5, min(50, $this->downPaymentPercentage));
        $years              = max(1, min(30, $this->paymentYears));

        $downPaymentAmount  = round($price * ($downPct / 100), 2);
        $remainingBalance   = round($price - $downPaymentAmount, 2);
        $totalMonths        = $years * 12;
        $monthlyInstallment = $totalMonths > 0
            ? round($remainingBalance / $totalMonths, 2)
            : 0;
        $quarterlyInstallment = round($monthlyInstallment * 3, 2);

        return [
            'down_payment_amount'   => $downPaymentAmount,
            'remaining_balance'     => $remainingBalance,
            'monthly_installment'   => $monthlyInstallment,
            'quarterly_installment' => $quarterlyInstallment,
            'total_months'          => $totalMonths,
        ];
    }

    // ── ACTION: Select a timeslot chip ───────────────────────────────────────
    public function selectTimeslot(string $timeslot): void
    {
        $validTimeslots = is_array($this->property->timeslots)
            ? $this->property->timeslots
            : json_decode($this->property->timeslots ?? '[]', true);

        if (! in_array($timeslot, $validTimeslots, true)) {
            $this->bookingError = 'Invalid timeslot selected.';
            return;
        }

        $this->selectedTimeslot = $timeslot;
        $this->bookingError     = '';
        $this->bookingSuccess   = false;
    }

    // ── ACTION: Book Inspection ───────────────────────────────────────────────
    public function bookInspection(): void
    {
        // Reset error state
        $this->bookingError   = '';
        $this->bookingSuccess = false;

        // Guard: must be authenticated
        if (! Auth::check()) {
            $this->bookingError = 'You must be logged in to book an inspection.';
            return;
        }

        $buyer = Auth::user();

        // Guard: only buyers can book
        if ($buyer->role !== 'buyer') {
            $this->bookingError = 'Only registered buyers can book property inspections.';
            return;
        }

        // Guard: timeslot must be selected
        if (! $this->selectedTimeslot) {
            $this->bookingError = 'Please select a preferred timeslot before booking.';
            return;
        }

        // Guard: prevent double-booking the same timeslot
        $alreadyBooked = Appointment::where('property_id', $this->property->id)
            ->where('buyer_id', $buyer->id)
            ->where('selected_timeslot', $this->selectedTimeslot)
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        if ($alreadyBooked) {
            $this->bookingError = 'You already have a booking for this timeslot. Please choose a different one.';
            return;
        }

        // Create the appointment record
        Appointment::create([
            'property_id'       => $this->property->id,
            'buyer_id'          => $buyer->id,
            'selected_timeslot' => $this->selectedTimeslot,
            'status'            => 'pending',
        ]);

        $this->bookingSuccess = true;
    }
}; ?>

{{-- ============================================================
     TEMPLATE: PropertyDetail — Livewire 4 SFC Blade Template
     ============================================================ --}}
<div class="min-h-screen bg-gray-50 font-sans">

    {{-- ── HERO IMAGE GALLERY ──────────────────────────────────────────── --}}
    @php
        $images = is_array($property->images)
            ? $property->images
            : json_decode($property->images ?? '[]', true);
        $heroImage   = $images[0] ?? null;
        $extraImages = array_slice($images, 1, 4);
    @endphp

    <section class="relative w-full bg-slate-800">
        <div class="grid grid-cols-4 grid-rows-2 gap-1 h-[480px] lg:h-[560px] max-w-screen-2xl mx-auto">
            {{-- Main image --}}
            <div class="col-span-4 lg:col-span-2 row-span-2 relative overflow-hidden">
                @if($heroImage)
                    <img
                        src="{{ Storage::url($heroImage) }}"
                        alt="{{ $property->title }}"
                        class="w-full h-full object-cover"
                        loading="eager"
                    />
                @else
                    <div class="w-full h-full flex items-center justify-center bg-slate-700">
                        <svg class="w-24 h-24 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M3 9.5L12 3l9 6.5V21H3V9.5z"/>
                        </svg>
                    </div>
                @endif
                {{-- Listing badge --}}
                <div class="absolute top-4 left-4 flex gap-2">
                    <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide
                        {{ $property->payment_type === 'installments'
                            ? 'bg-indigo-600 text-white'
                            : 'bg-emerald-600 text-white' }}">
                        {{ ucfirst($property->payment_type) }}
                    </span>
                    <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide bg-white/90 text-slate-700">
                        {{ ucfirst($property->property_type) }}
                    </span>
                </div>
            </div>

            {{-- Thumbnail strip --}}
            @foreach($extraImages as $img)
                <div class="col-span-1 row-span-1 overflow-hidden relative">
                    <img
                        src="{{ Storage::url($img) }}"
                        alt="Property image"
                        class="w-full h-full object-cover hover:opacity-90 transition-opacity cursor-pointer"
                    />
                </div>
            @endforeach
            @if(count($images) > 5)
                <div class="col-span-1 row-span-1 overflow-hidden relative">
                    <div class="w-full h-full bg-slate-900/70 flex items-center justify-center cursor-pointer">
                        <span class="text-white text-xl font-bold">+{{ count($images) - 5 }} Photos</span>
                    </div>
                </div>
            @endif
        </div>
    </section>

    {{-- ── MAIN CONTENT ────────────────────────────────────────────────────── --}}
    <div class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8 py-10 grid grid-cols-1 lg:grid-cols-3 gap-10">

        {{-- ── LEFT COLUMN: Detail Content ──────────────────────────────── --}}
        <div class="lg:col-span-2 space-y-8">

            {{-- ── PROPERTY TITLE & META ──────────────────────────────────── --}}
            <div>
                <div class="flex flex-wrap items-center gap-2 mb-2">
                    <span class="inline-flex items-center gap-1.5 text-sm text-slate-500 font-medium">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        {{ $property->region }}, {{ $property->city }}
                    </span>
                </div>

                <h1 class="text-3xl lg:text-4xl font-extrabold text-slate-900 leading-tight mb-4">
                    {{ $property->title }}
                </h1>

                {{-- Stats Row --}}
                <div class="flex flex-wrap gap-4 text-sm text-slate-600 border-t border-b border-slate-200 py-4">
                    <div class="flex items-center gap-1.5 font-medium">
                        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M20 7l-8-4-8 4m16 0v10l-8 4m0-10L4 7m8 10V11"/>
                        </svg>
                        {{ number_format($property->area_sqm) }} m²
                    </div>
                    <div class="flex items-center gap-1.5 font-medium">
                        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 12h18M3 6h18M3 18h18"/>
                        </svg>
                        {{ $property->bedrooms }} Bed{{ $property->bedrooms !== 1 ? 's' : '' }}
                    </div>
                    <div class="flex items-center gap-1.5 font-medium">
                        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        {{ $property->bathrooms }} Bath{{ $property->bathrooms !== 1 ? 's' : '' }}
                    </div>
                    @if($property->is_furnished)
                        <div class="flex items-center gap-1.5 font-medium text-emerald-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M5 13l4 4L19 7"/>
                            </svg>
                            Furnished
                        </div>
                    @else
                        <div class="flex items-center gap-1.5 font-medium text-slate-400">
                            Unfurnished
                        </div>
                    @endif
                    <div class="flex items-center gap-1.5 text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        {{ number_format($property->views_count) }} views
                    </div>
                </div>
            </div>

            {{-- ── PRICE PER SQM MARKET INSIGHT ────────────────────────────── --}}
            @php
                $badge     = $this->marketValueBadge;
                $priceSqm  = $this->pricePerSqm;
                $benchmark = $this->regionalBenchmark;
            @endphp

            <div class="rounded-2xl border {{ $badge['classes'] }} p-5">
                <div class="flex items-start gap-4">
                    <div class="shrink-0 w-12 h-12 rounded-xl flex items-center justify-center
                        {{ str_contains($badge['classes'], 'emerald') ? 'bg-emerald-100' :
                           (str_contains($badge['classes'], 'amber') ? 'bg-amber-100' :
                           (str_contains($badge['classes'], 'blue') ? 'bg-blue-100' : 'bg-gray-100')) }}">
                        <svg class="w-6 h-6 {{ str_contains($badge['classes'], 'emerald') ? 'text-emerald-600' :
                                               (str_contains($badge['classes'], 'amber') ? 'text-amber-600' :
                                               (str_contains($badge['classes'], 'blue') ? 'text-blue-600' : 'text-gray-600')) }}"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-xs font-semibold uppercase tracking-widest opacity-70 mb-1">
                            Price per m² Market Insight
                        </p>
                        <p class="text-2xl font-extrabold mb-1">
                            {{ number_format($priceSqm) }} <span class="text-base font-semibold">EGP/m²</span>
                        </p>
                        <div class="flex flex-wrap items-center gap-2 mt-2">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-bold border {{ $badge['classes'] }}">
                                @if(isset($badge['dot']))
                                    <span class="w-2 h-2 rounded-full {{ $badge['dot'] }}"></span>
                                @endif
                                {{ $badge['label'] }}
                            </span>
                            @if($benchmark)
                                <span class="text-sm opacity-75">
                                    Regional avg: {{ number_format($benchmark) }} EGP/m² in {{ $property->region }}
                                </span>
                            @endif
                        </div>
                        @if(isset($badge['detail']))
                            <p class="mt-2 text-sm opacity-75">{{ $badge['detail'] }}</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ── FEATURES / AMENITIES ────────────────────────────────────── --}}
            @php
                $features = is_array($property->features)
                    ? $property->features
                    : json_decode($property->features ?? '[]', true);
            @endphp

            @if(! empty($features))
                <div>
                    <h2 class="text-xl font-bold text-slate-900 mb-4">Features & Amenities</h2>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        @foreach($features as $feature)
                            <div class="flex items-center gap-2.5 bg-white border border-slate-200 rounded-xl px-4 py-3 shadow-sm">
                                <svg class="w-4 h-4 text-indigo-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span class="text-sm font-medium text-slate-700">{{ $feature }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- ── INSTALLMENT CALCULATOR ──────────────────────────────────── --}}
            @if($property->payment_type === 'installments')
                @php $calc = $this->installmentData; @endphp

                <div class="bg-gradient-to-br from-indigo-50 to-violet-50 border border-indigo-200 rounded-2xl p-6">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 11h.01M12 11h.01M15 11h.01M4 19h16a2 2 0 002-2V7a2 2 0 00-2-2H4a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-indigo-900">Installment Calculator</h2>
                            <p class="text-sm text-indigo-600">Adjust the sliders to simulate your payment plan</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
                        {{-- Down Payment Slider --}}
                        <div>
                            <label class="block text-sm font-semibold text-indigo-800 mb-2">
                                Down Payment: <span class="text-indigo-600">{{ $downPaymentPercentage }}%</span>
                            </label>
                            <input
                                type="range"
                                wire:model.live="downPaymentPercentage"
                                min="5"
                                max="50"
                                step="5"
                                class="w-full h-2 bg-indigo-200 rounded-lg appearance-none cursor-pointer accent-indigo-600"
                            />
                            <div class="flex justify-between text-xs text-indigo-500 mt-1">
                                <span>5%</span><span>50%</span>
                            </div>
                        </div>

                        {{-- Payment Years Slider --}}
                        <div>
                            <label class="block text-sm font-semibold text-indigo-800 mb-2">
                                Payment Period: <span class="text-indigo-600">{{ $paymentYears }} year{{ $paymentYears !== 1 ? 's' : '' }}</span>
                            </label>
                            <input
                                type="range"
                                wire:model.live="paymentYears"
                                min="1"
                                max="30"
                                step="1"
                                class="w-full h-2 bg-indigo-200 rounded-lg appearance-none cursor-pointer accent-indigo-600"
                            />
                            <div class="flex justify-between text-xs text-indigo-500 mt-1">
                                <span>1 yr</span><span>30 yrs</span>
                            </div>
                        </div>
                    </div>

                    {{-- Results Grid --}}
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                        <div class="bg-white/80 backdrop-blur-sm border border-indigo-100 rounded-xl p-4 text-center">
                            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Down Payment</p>
                            <p class="text-xl font-extrabold text-indigo-700">
                                {{ number_format($calc['down_payment_amount']) }}
                            </p>
                            <p class="text-xs text-slate-400 mt-0.5">EGP</p>
                        </div>
                        <div class="bg-white/80 backdrop-blur-sm border border-indigo-100 rounded-xl p-4 text-center">
                            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Remaining</p>
                            <p class="text-xl font-extrabold text-violet-700">
                                {{ number_format($calc['remaining_balance']) }}
                            </p>
                            <p class="text-xs text-slate-400 mt-0.5">EGP</p>
                        </div>
                        <div class="bg-white/80 backdrop-blur-sm border border-emerald-100 rounded-xl p-4 text-center">
                            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Monthly</p>
                            <p class="text-xl font-extrabold text-emerald-700">
                                {{ number_format($calc['monthly_installment']) }}
                            </p>
                            <p class="text-xs text-slate-400 mt-0.5">EGP/mo</p>
                        </div>
                        <div class="bg-white/80 backdrop-blur-sm border border-amber-100 rounded-xl p-4 text-center">
                            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Quarterly</p>
                            <p class="text-xl font-extrabold text-amber-700">
                                {{ number_format($calc['quarterly_installment']) }}
                            </p>
                            <p class="text-xs text-slate-400 mt-0.5">EGP/qtr</p>
                        </div>
                    </div>

                    <p class="text-xs text-indigo-400 mt-4 text-center">
                        * Estimates are indicative. Final terms subject to seller agreement. Over {{ $calc['total_months'] }} months.
                    </p>
                </div>
            @endif

        </div>

        {{-- ── RIGHT SIDEBAR ─────────────────────────────────────────────── --}}
        <div class="lg:col-span-1 space-y-6">

            {{-- ── PRICE CARD ──────────────────────────────────────────────── --}}
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 sticky top-6">

                <div class="mb-4">
                    <p class="text-sm text-slate-500 font-medium mb-0.5">Listed Price</p>
                    <p class="text-4xl font-black text-slate-900">
                        EGP <span class="text-indigo-700">{{ number_format($property->price) }}</span>
                    </p>
                    <p class="text-sm text-slate-400 mt-1">
                        {{ number_format($this->pricePerSqm) }} EGP/m² · {{ $property->area_sqm }} m²
                    </p>
                </div>

                {{-- ── SELLER CARD ─────────────────────────────────────────── --}}
                @if($property->seller)
                    <div class="flex items-center gap-3 p-4 bg-slate-50 rounded-xl mb-5">
                        <div class="w-10 h-10 rounded-full bg-indigo-600 flex items-center justify-center text-white font-bold text-lg shrink-0">
                            {{ strtoupper(substr($property->seller->name, 0, 1)) }}
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-1.5">
                                <p class="text-sm font-semibold text-slate-800 truncate">
                                    {{ $property->seller->name }}
                                </p>
                                @if($property->seller->is_verified)
                                    {{-- Verified Seller Badge --}}
                                    <svg class="w-5 h-5 text-blue-500 shrink-0" viewBox="0 0 24 24" fill="currentColor" aria-label="Verified Seller">
                                        <path fill-rule="evenodd"
                                              d="M8.603 3.799A4.49 4.49 0 0112 2.25c1.357 0 2.573.6 3.397 1.549a4.49 4.49 0 013.498 1.307 4.491 4.491 0 011.307 3.497A4.49 4.49 0 0121.75 12a4.49 4.49 0 01-1.549 3.397 4.491 4.491 0 01-1.307 3.497 4.491 4.491 0 01-3.497 1.307A4.49 4.49 0 0112 21.75a4.49 4.49 0 01-3.397-1.549 4.491 4.491 0 01-3.497-1.307 4.491 4.491 0 01-1.307-3.497A4.49 4.49 0 012.25 12c0-1.357.6-2.573 1.549-3.397a4.491 4.491 0 011.307-3.497 4.491 4.491 0 013.497-1.307zm7.007 6.387a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z"
                                              clip-rule="evenodd"/>
                                    </svg>
                                @endif
                            </div>
                            <p class="text-xs text-slate-500">
                                {{ $property->seller->is_verified ? 'Verified Seller' : 'Seller' }}
                            </p>
                        </div>
                    </div>
                @endif

                {{-- ── TIMESLOT BOOKING ────────────────────────────────────── --}}
                @php
                    $timeslots = is_array($property->timeslots)
                        ? $property->timeslots
                        : json_decode($property->timeslots ?? '[]', true);
                @endphp

                @if(! empty($timeslots))
                    <div class="mb-5">
                        <p class="text-sm font-semibold text-slate-700 mb-3">Select Inspection Timeslot</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach($timeslots as $timeslot)
                                <button
                                    wire:click="selectTimeslot('{{ $timeslot }}')"
                                    type="button"
                                    class="px-3 py-1.5 rounded-full text-xs font-semibold border transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-indigo-500
                                        {{ $selectedTimeslot === $timeslot
                                            ? 'bg-indigo-600 text-white border-indigo-600 shadow-md shadow-indigo-200'
                                            : 'bg-white text-slate-600 border-slate-300 hover:border-indigo-400 hover:text-indigo-600' }}"
                                >
                                    {{ $timeslot }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Error Message --}}
                @if($bookingError)
                    <div class="flex items-start gap-2 bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl px-4 py-3 mb-4" role="alert">
                        <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>{{ $bookingError }}</span>
                    </div>
                @endif

                {{-- Success Message --}}
                @if($bookingSuccess)
                    <div class="flex items-start gap-2 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm rounded-xl px-4 py-3 mb-4" role="alert">
                        <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <p class="font-semibold">Inspection Requested!</p>
                            <p class="text-xs mt-0.5">Your booking for <strong>{{ $selectedTimeslot }}</strong> is now <em>pending</em> seller approval. We'll notify you shortly.</p>
                        </div>
                    </div>
                @else
                    {{-- Book Inspection Button --}}
                    <button
                        wire:click="bookInspection"
                        wire:loading.attr="disabled"
                        wire:target="bookInspection"
                        type="button"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 active:scale-95 disabled:opacity-60 disabled:cursor-not-allowed
                               text-white font-bold py-3.5 rounded-xl transition-all duration-150 shadow-md shadow-indigo-200
                               flex items-center justify-center gap-2 mb-3"
                    >
                        <span wire:loading.remove wire:target="bookInspection">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </span>
                        <span wire:loading wire:target="bookInspection">
                            <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor"
                                      d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                        </span>
                        <span wire:loading.remove wire:target="bookInspection">Book Inspection</span>
                        <span wire:loading wire:target="bookInspection">Processing…</span>
                    </button>
                @endif

                {{-- ── PDF BROCHURE CTA ─────────────────────────────────────── --}}
                <a
                    href="{{ route('properties.pdf', ['id' => $property->id]) }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="w-full flex items-center justify-center gap-2 border border-slate-300 hover:border-indigo-400 hover:text-indigo-600
                           text-slate-600 font-semibold py-3 rounded-xl transition-all duration-150 text-sm"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    Download PDF Brochure
                </a>

                {{-- Engagement meta --}}
                <div class="flex items-center justify-center gap-4 mt-4 text-xs text-slate-400">
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        {{ number_format($property->views_count) }} views
                    </span>
                    <span class="flex items-center gap-1 text-red-400">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z"/>
                        </svg>
                        {{ number_format($property->favorites_count) }} saved
                    </span>
                </div>

            </div>

        </div>

    </div>

</div>
