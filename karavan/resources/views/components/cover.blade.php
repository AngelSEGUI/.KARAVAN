{{--
    Visuel d'un festival. Avec :image (bannière envoyée par l'organisateur), affiche la photo ;
    sinon, une affiche générée en CSS à partir de la teinte (hue) du festival.
--}}
@props(['name', 'hue' => 20, 'size' => 'card', 'image' => null])
@if ($image)
    <div {{ $attributes->class(['cover', 'cover-' . $size, 'cover-photo']) }} style="--h: {{ (int) $hue }}" aria-hidden="true">
        <img src="{{ $image }}" alt="" loading="lazy">
    </div>
@else
    <div {{ $attributes->class(['cover', 'cover-' . $size]) }} style="--h: {{ (int) $hue }}" aria-hidden="true">
        <span class="cover-sun"></span>
        <span class="cover-hills"></span>
        <span class="cover-word">{{ $name }}</span>
    </div>
@endif
