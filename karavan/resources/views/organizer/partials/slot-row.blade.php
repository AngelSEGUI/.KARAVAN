{{-- Une ligne de la programmation. $i vaut __INDEX__ dans le <template>. --}}
@php $err = fn ($f) => $errors->has("performances.$i.$f") ? 'has-error' : ''; @endphp
<li class="repeater-row slot-row" data-repeater-row>
    <input type="hidden" name="performances[{{ $i }}][id]" value="{{ $row['id'] ?? '' }}">
    <label class="slot-field {{ $err('day') }}" data-label="Journée">
        <span class="sr-only">Journée</span>
        <select name="performances[{{ $i }}][day]" data-copy="day">
            @foreach ($dayOptions as $value => $text)
                <option value="{{ $value }}" @selected(($row['day'] ?? null) === $value)>{{ $text }}</option>
            @endforeach
        </select>
    </label>
    <label class="slot-field {{ $err('start') }}" data-label="Début">
        <span class="sr-only">Début</span>
        <input type="time" name="performances[{{ $i }}][start]" value="{{ $row['start'] ?? '' }}" step="300">
    </label>
    <label class="slot-field {{ $err('end') }}" data-label="Fin">
        <span class="sr-only">Fin</span>
        <input type="time" name="performances[{{ $i }}][end]" value="{{ $row['end'] ?? '' }}" step="300">
    </label>
    <label class="slot-field {{ $err('artist') }}" data-label="Artiste">
        <span class="sr-only">Artiste</span>
        <input type="text" name="performances[{{ $i }}][artist]" value="{{ $row['artist'] ?? '' }}" maxlength="120" placeholder="Artiste">
    </label>
    <label class="slot-field {{ $err('stage') }}" data-label="Scène">
        <span class="sr-only">Scène</span>
        <input type="text" name="performances[{{ $i }}][stage]" value="{{ $row['stage'] ?? '' }}" maxlength="80" placeholder="Scène" list="stages-list" data-copy="stage">
    </label>
    <label class="slot-field {{ $err('capacity') }}" data-label="Capacité">
        <span class="sr-only">Capacité (facultatif)</span>
        <input type="number" name="performances[{{ $i }}][capacity]" value="{{ $row['capacity'] ?? '' }}" min="1" step="1" placeholder="—" title="Facultatif : masquée au public si vide">
    </label>
    <button type="button" class="icon-btn icon-btn-danger" data-repeater-remove title="Retirer ce créneau" aria-label="Retirer ce créneau"><x-icon name="trash" /></button>
</li>
