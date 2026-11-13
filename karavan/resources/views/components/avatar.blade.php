{{-- Pastille avec les initiales d'un membre. Tailles : sm | (normal) | lg | xl --}}
@props(['user', 'size' => null])
<span {{ $attributes->class(['avatar', 'avatar-' . $size => $size]) }} title="{{ $user->name }}">{{ $user->initials }}</span>
