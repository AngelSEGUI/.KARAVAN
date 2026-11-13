{{--
    Bouton de suppression avec confirmation (méthode DELETE + jeton CSRF).
    redirect : page où revenir après la suppression (ex. une liste d'administration).
--}}
@props(['action', 'confirm', 'label' => null, 'redirect' => null, 'method' => 'DELETE', 'icon' => 'trash'])
<form method="POST" action="{{ $action }}" data-confirm="{{ $confirm }}" class="inline-form">
    @csrf
    @method($method)
    @if ($redirect)<input type="hidden" name="redirect" value="{{ $redirect }}">@endif
    {{-- Sans classe fournie : petit bouton icône ; sinon la classe donnée remplace (ex. "btn btn-danger") --}}
    <button type="submit" class="{{ $attributes->get('class', 'icon-btn icon-btn-danger') }}" {{ $attributes->except('class') }} title="{{ $label ?? 'Supprimer' }}" @unless($label) aria-label="Supprimer" @endunless>
        <x-icon :name="$icon" />@if ($label)<span>{{ $label }}</span>@endif
    </button>
</form>
