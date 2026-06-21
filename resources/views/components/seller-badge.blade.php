{{--
    Blade Component: seller-badge
    ─────────────────────────────────────────────────────────────────────
    Usage in any Blade template:
        <x-seller-badge :user="$seller" />
        <x-seller-badge :user="$property->seller" :show-name="true" />

    Props:
        $user       — The User model instance (must have: name, is_verified)
        $showName   — (bool, default true) Whether to display the seller name alongside the badge
        $size       — (string, default 'md') Icon size: 'sm' | 'md' | 'lg'
--}}

@props([
    'user'     => null,
    'showName' => true,
    'size'     => 'md',
])

@if($user)
    @php
        $sizeClasses = match($size) {
            'sm'  => 'w-4 h-4',
            'lg'  => 'w-7 h-7',
            default => 'w-5 h-5',  // 'md'
        };

        $textSizeClass = match($size) {
            'sm'  => 'text-xs',
            'lg'  => 'text-lg',
            default => 'text-sm',
        };
    @endphp

    <span class="inline-flex items-center gap-1.5 {{ $textSizeClass }} font-semibold text-slate-800"
          title="{{ $user->is_verified ? 'Verified Seller: ' . $user->name : 'Seller: ' . $user->name }}">

        @if($showName)
            <span>{{ $user->name }}</span>
        @endif

        @if($user->is_verified)
            {{-- heroicon-s-check-badge (solid) — rendered inline, text-blue-500 --}}
            <svg class="{{ $sizeClasses }} text-blue-500 shrink-0"
                 viewBox="0 0 24 24"
                 fill="currentColor"
                 aria-label="Verified Seller"
                 role="img">
                <title>Verified Seller</title>
                <path fill-rule="evenodd"
                      d="M8.603 3.799A4.49 4.49 0 0112 2.25c1.357 0 2.573.6 3.397 1.549a4.49 4.49 0 013.498 1.307 4.491 4.491 0 011.307 3.497A4.49 4.49 0 0121.75 12a4.49 4.49 0 01-1.549 3.397 4.491 4.491 0 01-1.307 3.497 4.491 4.491 0 01-3.497 1.307A4.49 4.49 0 0112 21.75a4.49 4.49 0 01-3.397-1.549 4.491 4.491 0 01-3.497-1.307 4.491 4.491 0 01-1.307-3.497A4.49 4.49 0 012.25 12c0-1.357.6-2.573 1.549-3.397a4.491 4.491 0 011.307-3.497 4.491 4.491 0 013.497-1.307zm7.007 6.387a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z"
                      clip-rule="evenodd"/>
            </svg>
        @else
            {{-- Unverified indicator — subtle gray shield --}}
            <svg class="{{ $sizeClasses }} text-slate-300 shrink-0"
                 viewBox="0 0 24 24"
                 fill="none"
                 stroke="currentColor"
                 aria-label="Unverified Seller"
                 role="img">
                <title>Unverified Seller</title>
                <path stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="1.5"
                      d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/>
            </svg>
        @endif

    </span>
@endif
