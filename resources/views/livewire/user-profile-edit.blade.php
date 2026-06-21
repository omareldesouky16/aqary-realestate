<?php

use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * UserProfileEdit - Livewire 4 Single-File Component
 *
 * Seller profile editing page with:
 *  - Verified seller badge display (heroicon-s-check-badge in blue-500)
 *  - Document upload module when user is NOT verified
 *  - Name / email editing
 *  - Stateful success/error feedback
 */
new class extends Component
{
    use WithFileUploads;

    // ── FORM FIELDS ──────────────────────────────────────────────────────────
    public string $name  = '';
    public string $email = '';

    // ── VERIFICATION DOCUMENT UPLOAD ─────────────────────────────────────────
    public $verificationDocument = null;   // Livewire temporary upload

    // ── STATE ────────────────────────────────────────────────────────────────
    public bool   $profileSaved       = false;
    public bool   $documentSubmitted  = false;
    public string $profileError       = '';
    public string $documentError      = '';
    public bool   $documentLoading    = false;

    // ── LIFECYCLE ────────────────────────────────────────────────────────────
    public function mount(): void
    {
        /** @var User $user */
        $user = Auth::user();

        if (! $user) {
            $this->redirect(route('login'));
            return;
        }

        $this->name  = $user->name;
        $this->email = $user->email;
    }

    // ── COMPUTED: Current user ────────────────────────────────────────────────
    public function getUserProperty(): ?User
    {
        return Auth::user();
    }

    // ── ACTION: Save Profile ─────────────────────────────────────────────────
    public function saveProfile(): void
    {
        $this->profileSaved = false;
        $this->profileError = '';

        /** @var User $user */
        $user = Auth::user();

        if (! $user) {
            $this->profileError = 'You must be logged in to update your profile.';
            return;
        }

        $validated = $this->validate([
            'name'  => ['required', 'string', 'min:2', 'max:100'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
        ], [
            'name.required'  => 'Your full name is required.',
            'name.min'       => 'Name must be at least 2 characters.',
            'email.required' => 'A valid email address is required.',
            'email.unique'   => 'This email is already in use by another account.',
        ]);

        $user->update([
            'name'  => $validated['name'],
            'email' => $validated['email'],
        ]);

        $this->profileSaved = true;
    }

    // ── ACTION: Submit Verification Document ─────────────────────────────────
    public function submitVerificationDocument(): void
    {
        $this->documentSubmitted = false;
        $this->documentError     = '';

        /** @var User $user */
        $user = Auth::user();

        if (! $user) {
            $this->documentError = 'Authentication required.';
            return;
        }

        if ($user->is_verified) {
            $this->documentError = 'Your account is already verified.';
            return;
        }

        $this->validate([
            'verificationDocument' => [
                'required',
                'file',
                'max:10240',    // 10 MB max
                'mimes:pdf,jpg,jpeg,png,webp',
            ],
        ], [
            'verificationDocument.required' => 'Please select a document to upload.',
            'verificationDocument.max'      => 'File must not exceed 10 MB.',
            'verificationDocument.mimes'    => 'Accepted formats: PDF, JPG, PNG, WebP.',
        ]);

        // Store the document securely in a private disk
        $path = $this->verificationDocument->store(
            'verification-documents/' . $user->id,
            'private'
        );

        // In production you would queue a notification to the admin here:
        // VerificationDocumentSubmitted::dispatch($user, $path);

        $this->verificationDocument = null;
        $this->documentSubmitted    = true;
    }
}; ?>

{{-- ============================================================
     TEMPLATE: UserProfileEdit — Livewire 4 SFC Blade Template
     ============================================================ --}}
<div class="min-h-screen bg-slate-50 py-10 px-4 font-sans">
    <div class="max-w-3xl mx-auto space-y-8">

        {{-- ── PAGE HEADER ──────────────────────────────────────────────── --}}
        <div>
            <h1 class="text-3xl font-extrabold text-slate-900">My Profile</h1>
            <p class="text-sm text-slate-500 mt-1">Manage your account information and verification status.</p>
        </div>

        {{-- ── VERIFICATION STATUS BANNER ──────────────────────────────── --}}
        @php /** @var \App\Models\User $authUser */ $authUser = $this->user; @endphp

        @if($authUser)
            @if($authUser->is_verified)
                {{-- VERIFIED BADGE DISPLAY --}}
                <div class="flex items-center gap-3 bg-blue-50 border border-blue-200 rounded-2xl px-5 py-4">
                    {{-- heroicon-s-check-badge rendered inline --}}
                    <svg class="w-8 h-8 text-blue-500 shrink-0" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd"
                              d="M8.603 3.799A4.49 4.49 0 0112 2.25c1.357 0 2.573.6 3.397 1.549a4.49 4.49 0 013.498 1.307 4.491 4.491 0 011.307 3.497A4.49 4.49 0 0121.75 12a4.49 4.49 0 01-1.549 3.397 4.491 4.491 0 01-1.307 3.497 4.491 4.491 0 01-3.497 1.307A4.49 4.49 0 0112 21.75a4.49 4.49 0 01-3.397-1.549 4.491 4.491 0 01-3.497-1.307 4.491 4.491 0 01-1.307-3.497A4.49 4.49 0 012.25 12c0-1.357.6-2.573 1.549-3.397a4.491 4.491 0 011.307-3.497 4.491 4.491 0 013.497-1.307zm7.007 6.387a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z"
                              clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <p class="font-bold text-blue-800 text-base">Verified Seller Account</p>
                        <p class="text-sm text-blue-600">Your identity has been confirmed. Buyers can trust your listings.</p>
                    </div>
                </div>

            @else
                {{-- NOT VERIFIED — DOCUMENT UPLOAD MODULE ──────────────── --}}
                <div class="bg-amber-50 border border-amber-200 rounded-2xl overflow-hidden">
                    <div class="px-5 py-4 border-b border-amber-200 flex items-start gap-3">
                        <svg class="w-7 h-7 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <div>
                            <p class="font-bold text-amber-800">Account Not Verified</p>
                            <p class="text-sm text-amber-700 mt-0.5">
                                Upload a government-issued ID, commercial registration, or equivalent document to get your account verified.
                                Verified sellers gain a badge on all their listings, increasing buyer trust and inquiries.
                            </p>
                        </div>
                    </div>

                    <div class="px-5 py-5">
                        @if($documentSubmitted)
                            <div class="flex items-start gap-2 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl p-4" role="alert">
                                <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <div>
                                    <p class="font-semibold">Document Submitted!</p>
                                    <p class="text-sm mt-0.5">Our team will review your document and update your status within 1–2 business days.</p>
                                </div>
                            </div>
                        @else
                            {{-- Document error --}}
                            @if($documentError)
                                <div class="flex items-start gap-2 bg-red-50 border border-red-200 text-red-700 rounded-xl p-3 mb-4 text-sm" role="alert">
                                    <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span>{{ $documentError }}</span>
                                </div>
                            @endif

                            {{-- Validation error --}}
                            @error('verificationDocument')
                                <div class="flex items-start gap-2 bg-red-50 border border-red-200 text-red-700 rounded-xl p-3 mb-4 text-sm" role="alert">
                                    <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span>{{ $message }}</span>
                                </div>
                            @enderror

                            <form wire:submit.prevent="submitVerificationDocument" class="space-y-4">
                                {{-- Drag-and-Drop Upload Zone --}}
                                <label
                                    for="verification-doc-input"
                                    class="flex flex-col items-center justify-center w-full h-44 border-2 border-dashed border-amber-300
                                           hover:border-amber-500 rounded-xl cursor-pointer bg-white hover:bg-amber-50 transition-colors duration-150"
                                >
                                    <div class="flex flex-col items-center gap-2 text-center px-4">
                                        <svg class="w-10 h-10 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                  d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>

                                        @if($verificationDocument)
                                            <p class="text-sm font-semibold text-emerald-700">
                                                ✓ {{ $verificationDocument->getClientOriginalName() }}
                                            </p>
                                            <p class="text-xs text-slate-400">
                                                {{ number_format($verificationDocument->getSize() / 1024, 1) }} KB — Click to change
                                            </p>
                                        @else
                                            <p class="text-sm font-semibold text-slate-700">
                                                Click to upload or drag & drop
                                            </p>
                                            <p class="text-xs text-slate-400">
                                                PDF, JPG, PNG or WebP — Max 10MB
                                            </p>
                                            <p class="text-xs text-amber-600 font-medium mt-1">
                                                Accepted: National ID, Passport, Commercial Registration
                                            </p>
                                        @endif
                                    </div>
                                    <input
                                        id="verification-doc-input"
                                        type="file"
                                        wire:model="verificationDocument"
                                        accept=".pdf,.jpg,.jpeg,.png,.webp"
                                        class="hidden"
                                    />
                                </label>

                                {{-- Upload loading indicator --}}
                                <div wire:loading wire:target="verificationDocument"
                                     class="flex items-center gap-2 text-sm text-amber-700">
                                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                    </svg>
                                    Uploading document…
                                </div>

                                <button
                                    type="submit"
                                    wire:loading.attr="disabled"
                                    wire:target="submitVerificationDocument, verificationDocument"
                                    class="w-full sm:w-auto bg-amber-500 hover:bg-amber-600 active:scale-95 disabled:opacity-60
                                           text-white font-bold px-6 py-2.5 rounded-xl transition-all duration-150 shadow-sm flex items-center gap-2"
                                >
                                    <span wire:loading.remove wire:target="submitVerificationDocument">
                                        Submit for Verification
                                    </span>
                                    <span wire:loading wire:target="submitVerificationDocument" class="flex items-center gap-2">
                                        <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                        </svg>
                                        Submitting…
                                    </span>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endif
        @endif

        {{-- ── PROFILE INFORMATION FORM ─────────────────────────────────── --}}
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100">
                <h2 class="text-lg font-bold text-slate-800">Profile Information</h2>
                <p class="text-sm text-slate-500 mt-0.5">Update your name and email address.</p>
            </div>

            <div class="px-6 py-6">
                {{-- Success Banner --}}
                @if($profileSaved)
                    <div class="flex items-start gap-2 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl p-4 mb-5 text-sm" role="alert">
                        <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Profile updated successfully.
                    </div>
                @endif

                {{-- Error Banner --}}
                @if($profileError)
                    <div class="flex items-start gap-2 bg-red-50 border border-red-200 text-red-700 rounded-xl p-4 mb-5 text-sm" role="alert">
                        <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ $profileError }}
                    </div>
                @endif

                <form wire:submit.prevent="saveProfile" class="space-y-5" novalidate>
                    {{-- Name --}}
                    <div>
                        <label for="profile-name" class="block text-sm font-semibold text-slate-700 mb-1.5">
                            Full Name
                        </label>
                        <input
                            id="profile-name"
                            type="text"
                            wire:model="name"
                            autocomplete="name"
                            class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-slate-800 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent
                                   placeholder:text-slate-400 transition"
                            placeholder="Your full name"
                        />
                        @error('name')
                            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Email --}}
                    <div>
                        <label for="profile-email" class="block text-sm font-semibold text-slate-700 mb-1.5">
                            Email Address
                        </label>
                        <input
                            id="profile-email"
                            type="email"
                            wire:model="email"
                            autocomplete="email"
                            class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-slate-800 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent
                                   placeholder:text-slate-400 transition"
                            placeholder="your@email.com"
                        />
                        @error('email')
                            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Role display (read-only) --}}
                    @if($authUser)
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Account Role</label>
                            <div class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-600">
                                @if($authUser->role === 'seller')
                                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                    <span class="font-medium capitalize">Seller</span>
                                    @if($authUser->is_verified)
                                        <svg class="w-4 h-4 text-blue-500" viewBox="0 0 24 24" fill="currentColor" aria-label="Verified">
                                            <path fill-rule="evenodd"
                                                  d="M8.603 3.799A4.49 4.49 0 0112 2.25c1.357 0 2.573.6 3.397 1.549a4.49 4.49 0 013.498 1.307 4.491 4.491 0 011.307 3.497A4.49 4.49 0 0121.75 12a4.49 4.49 0 01-1.549 3.397 4.491 4.491 0 01-1.307 3.497 4.491 4.491 0 01-3.497 1.307A4.49 4.49 0 0112 21.75a4.49 4.49 0 01-3.397-1.549 4.491 4.491 0 01-3.497-1.307 4.491 4.491 0 01-1.307-3.497A4.49 4.49 0 012.25 12c0-1.357.6-2.573 1.549-3.397a4.491 4.491 0 011.307-3.497 4.491 4.491 0 013.497-1.307zm7.007 6.387a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z"
                                                  clip-rule="evenodd"/>
                                        </svg>
                                    @endif
                                @else
                                    <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    <span class="font-medium capitalize">Buyer</span>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- Save Button --}}
                    <div class="flex items-center gap-4 pt-2">
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            wire:target="saveProfile"
                            class="bg-indigo-600 hover:bg-indigo-700 active:scale-95 disabled:opacity-60
                                   text-white font-bold px-6 py-2.5 rounded-xl transition-all duration-150 shadow-sm flex items-center gap-2 text-sm"
                        >
                            <span wire:loading.remove wire:target="saveProfile">Save Changes</span>
                            <span wire:loading wire:target="saveProfile" class="flex items-center gap-2">
                                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                </svg>
                                Saving…
                            </span>
                        </button>

                        @if($profileSaved)
                            <span class="text-sm text-emerald-600 font-medium flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Saved
                            </span>
                        @endif
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
