{{-- État vide : rien à afficher. Le slot accueille un bouton d'action. --}}
@props(['icon' => 'compass', 'title' => null, 'small' => false])
<div {{ $attributes->class(['empty', 'empty-sm' => $small]) }}>
    @unless ($small)<x-icon :name="$icon" />@endunless
    @if ($title)<h2 @class(['h4' => $small])>{{ $title }}</h2>@endif
    {{ $slot }}
</div>
