{{-- Barre « Modifier / Supprimer » affichée sous une annonce dans Mon espace --}}
@props(['edit', 'destroy', 'confirm', 'info' => null])
<div class="owner-actions">
    @if ($info)<span class="muted">{{ $info }}</span>@endif
    <a href="{{ $edit }}" class="icon-btn"><x-icon name="edit" /><span>Modifier</span></a>
    <x-delete-form :action="$destroy" :confirm="$confirm" label="Supprimer" :redirect="route('account', [], false)" />
</div>
