<?php

use Livewire\Volt\Component;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

new class extends Component {
    public bool $isOpen = false;
    public string $locale = 'en'; // 'en' or 'ar'
    public string $message = '';
    public array $messages = [];
    public ?string $sessionId = null;

    public function mount()
    {
        $this->sessionId = Str::uuid()->toString();
        $this->messages[] = [
            'sender' => 'assistant',
            'text' => $this->locale === 'en' 
                ? 'Hello! I am your AI Real Estate Assistant. How can I help you find your dream property today?' 
                : 'مرحباً! أنا مساعدك العقاري الذكي. كيف يمكنني مساعدتك في العثور على عقار أحلامك اليوم؟',
            'properties' => []
        ];
    }

    public function toggleLocale()
    {
        $this->locale = $this->locale === 'en' ? 'ar' : 'en';
    }

    public function toggleChat()
    {
        $this->isOpen = !$this->isOpen;
    }

    public function sendMessage()
    {
        if (trim($this->message) === '') {
            return;
        }

        $userMessage = $this->message;
        $this->messages[] = [
            'sender' => 'user',
            'text' => $userMessage,
            'properties' => []
        ];

        $this->message = '';

        try {
            $microserviceUrl = config('services.ai_microservice_url', 'http://127.0.0.1:8001');
            $response = Http::timeout(60)->post(rtrim($microserviceUrl, '/') . '/api/chat', [
                'session_id' => $this->sessionId,
                'message' => $userMessage,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $this->messages[] = [
                    'sender' => 'assistant',
                    'text' => $data['reply'] ?? '',
                    'properties' => $data['properties'] ?? [],
                    'detail' => $data['property_detail'] ?? null,
                ];
            } else {
                $this->messages[] = [
                    'sender' => 'assistant',
                    'text' => $this->locale === 'en' 
                        ? 'I am currently unable to connect to the recommendation system. Please try again later.'
                        : 'أنا غير قادر حالياً على الاتصال بنظام التوصيات. يرجى المحاولة مرة أخرى لاحقاً.',
                    'properties' => []
                ];
            }
        } catch (\Exception $e) {
            $this->messages[] = [
                'sender' => 'assistant',
                'text' => $this->locale === 'en' 
                    ? 'The recommendation microservice is offline.' 
                    : 'خدمة التوصيات متوقفة حالياً.',
                'properties' => []
            ];
        }
    }
}; ?>

<div class="fixed bottom-6 right-6 z-50 font-sans" dir="{{ $locale === 'ar' ? 'rtl' : 'ltr' }}">
    <!-- Chat Toggle Button -->
    @if(!$isOpen)
        <button wire:click="toggleChat" class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-full p-4 shadow-2xl transition-transform transform hover:scale-105 flex items-center justify-center">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
        </button>
    @else
        <!-- Chat Window -->
        <div class="bg-white w-96 md:w-[400px] rounded-2xl shadow-2xl flex flex-col overflow-hidden border border-slate-200 transition-all" style="height: 600px; max-height: 80vh;">
            
            <!-- Header -->
            <div class="bg-indigo-600 text-white p-4 flex justify-between items-center shadow-md relative z-10">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-lg leading-none">Aqary AI</h3>
                        <p class="text-xs text-indigo-200 mt-1">{{ $locale === 'en' ? 'Real Estate Assistant' : 'المساعد العقاري' }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button wire:click="toggleLocale" class="text-xs bg-white/20 hover:bg-white/30 px-2 py-1 rounded font-bold uppercase transition-colors">
                        {{ $locale === 'en' ? 'عربي' : 'EN' }}
                    </button>
                    <button wire:click="toggleChat" class="text-indigo-200 hover:text-white transition-colors p-1">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>

            <!-- Messages Area -->
            <div class="flex-grow p-4 overflow-y-auto bg-slate-50 flex flex-col gap-4" id="chat-messages" wire:poll.keep-alive>
                @foreach($messages as $msg)
                    <div class="flex {{ $msg['sender'] === 'user' ? 'justify-end' : 'justify-start' }} w-full">
                        <div class="max-w-[85%] {{ $msg['sender'] === 'user' ? 'bg-indigo-600 text-white rounded-l-2xl rounded-tr-2xl' : 'bg-white border border-slate-200 text-slate-800 rounded-r-2xl rounded-tl-2xl shadow-sm' }} p-4 text-sm whitespace-pre-wrap leading-relaxed">
                            {{ $msg['text'] }}
                            
                            {{-- Render Property Mini-Cards if Recommended --}}
                            @if(isset($msg['properties']) && count($msg['properties']) > 0)
                                <div class="mt-4 flex flex-col gap-3">
                                    @foreach($msg['properties'] as $prop)
                                        <div class="bg-slate-50 border border-slate-200 rounded-xl p-3 flex flex-col text-slate-800 shadow-sm cursor-pointer hover:border-indigo-400 transition-colors" onclick="window.location.href='/properties/{{ $prop['id'] }}'">
                                            <div class="font-bold text-indigo-700 text-sm mb-1 line-clamp-1">{{ $prop['title'] }}</div>
                                            <div class="flex items-center gap-1 text-xs text-slate-500 mb-2">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                {{ $prop['region'] ?? 'Egypt' }}
                                            </div>
                                            <div class="flex justify-between items-center text-xs font-semibold">
                                                <span>{{ number_format($prop['price']) }} EGP</span>
                                                <span class="bg-indigo-100 text-indigo-800 px-2 py-0.5 rounded">{{ $prop['property_type'] ?? 'N/A' }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            {{-- Render Detail Info if requested --}}
                            @if(isset($msg['detail']) && $msg['detail'] !== null)
                                <div class="mt-4 bg-indigo-50 border border-indigo-100 rounded-xl p-3 text-xs text-indigo-900">
                                    <div class="grid grid-cols-2 gap-2 mb-2">
                                        <div><span class="font-bold">Bedrooms:</span> {{ $msg['detail']['bedrooms'] ?? 'N/A' }}</div>
                                        <div><span class="font-bold">Bathrooms:</span> {{ $msg['detail']['bathrooms'] ?? 'N/A' }}</div>
                                        <div><span class="font-bold">Area:</span> {{ $msg['detail']['area_sqm'] ?? 'N/A' }}m²</div>
                                        <div><span class="font-bold">Payment:</span> {{ $msg['detail']['payment_type'] ?? 'N/A' }}</div>
                                    </div>
                                    <a href="/properties/{{ $msg['detail']['id'] ?? '' }}" class="block text-center bg-indigo-600 text-white font-bold py-1.5 rounded mt-2 hover:bg-indigo-700 transition-colors">
                                        {{ $locale === 'en' ? 'View Details' : 'عرض التفاصيل' }}
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
                
                {{-- Loading Indicator --}}
                <div wire:loading wire:target="sendMessage" class="flex justify-start w-full">
                    <div class="bg-white border border-slate-200 text-slate-500 rounded-r-2xl rounded-tl-2xl shadow-sm p-4 text-sm flex items-center gap-2">
                        <svg class="animate-spin w-4 h-4 text-indigo-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        {{ $locale === 'en' ? 'Thinking...' : 'يفكر...' }}
                    </div>
                </div>
            </div>

            <!-- Input Area -->
            <div class="bg-white p-4 border-t border-slate-200 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)] relative z-10">
                <form wire:submit.prevent="sendMessage" class="flex items-center gap-2">
                    <input 
                        type="text" 
                        wire:model="message" 
                        placeholder="{{ $locale === 'en' ? 'Type your message...' : 'اكتب رسالتك...' }}" 
                        class="flex-grow bg-slate-50 border border-slate-300 rounded-full px-5 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all text-slate-800"
                        autocomplete="off"
                        required
                    >
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-full p-3 transition-colors flex-shrink-0" wire:loading.attr="disabled">
                        <svg class="w-5 h-5 {{ $locale === 'ar' ? 'transform rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    </button>
                </form>
            </div>
        </div>
    @endif
    
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.hook('morph.updated', (el, component) => {
                const container = document.getElementById('chat-messages');
                if (container) {
                    container.scrollTop = container.scrollHeight;
                }
            });
        });
    </script>
</div>
