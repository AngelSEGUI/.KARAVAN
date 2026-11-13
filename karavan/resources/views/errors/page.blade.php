{{-- Gabarit commun des pages d'erreur (403, 404, 419, 429) --}}
<x-layouts.app :title="$title">
    <div class="container">
        <x-empty class="empty-page" :icon="$icon ?? 'compass'">
            <h1>{{ $title }}</h1>
            <p>{{ $message }}</p>
            <a href="{{ route('home') }}" class="btn btn-primary">Retour à l'accueil</a>
        </x-empty>
    </div>
</x-layouts.app>
