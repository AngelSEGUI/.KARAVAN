{{--
    Affiche générée en CSS à partir de la teinte (hue) du festival : aucune image nécessaire.
    Pour utiliser de vraies photos plus tard : ajouter une colonne image et un <img> ici.
--}}
@props(['name', 'hue' => 20, 'size' => 'card'])
<div {{ $attributes->class(['cover', 'cover-' . $size]) }} style="--h: {{ (int) $hue }}" aria-hidden="true">
    <span class="cover-sun"></span>
    <span class="cover-hills"></span>
    <span class="cover-word">{{ $name }}</span>
</div>
