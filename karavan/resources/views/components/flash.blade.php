{{-- Messages de session : redirect()->with('success' | 'error' | 'info', '...') --}}
@php
    $messages = collect(['success', 'error', 'info'])
        ->filter(fn ($type) => session()->has($type))
        ->mapWithKeys(fn ($type) => [$type => session($type)]);
@endphp
@if ($messages->isNotEmpty())
    <div {{ $attributes->merge(['class' => 'flashes']) }} role="status">
        @foreach ($messages as $type => $message)
            <div class="flash flash-{{ $type }}">
                <span>{{ $message }}</span>
                <button type="button" class="flash-close" aria-label="Fermer">×</button>
            </div>
        @endforeach
    </div>
@endif
