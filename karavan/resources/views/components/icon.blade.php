{{-- Icône du sprite (resources/views/partials/icons.blade.php). Ex. : <x-icon name="car" class="icon-sm" /> --}}
@props(['name'])
<svg {{ $attributes->merge(['class' => 'icon']) }} aria-hidden="true"><use href="#i-{{ $name }}"/></svg>
