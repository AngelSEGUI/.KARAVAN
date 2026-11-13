{{-- En-tête de page : sur-titre, titre, phrase d'intro et boutons d'action (slot) --}}
@props(['eyebrow' => null, 'title', 'lead' => null])
<section class="page-head">
    <div class="container page-head-row">
        <div>
            @if ($eyebrow)<span class="eyebrow">{{ $eyebrow }}</span>@endif
            <h1>{{ $title }}</h1>
            @if ($lead)<p class="lead">{{ $lead }}</p>@endif
        </div>
        {{ $slot }}
    </div>
</section>
