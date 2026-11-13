{{--
    Champ de formulaire complet : libellé + champ + aide + erreur de validation.
      <x-field name="city" label="Ville" :value="$user->city" optional />
      <x-field name="notes" label="Message" type="textarea" rows="4" />
      <x-field name="festival_id" label="Festival" type="select" :options="[1 => 'Hellfest']" placeholder="Choisir…" />
    Les autres attributs (required, min, max, step, placeholder…) sont transmis au champ.
    bag : nom du « sac » d'erreurs quand une page a plusieurs formulaires (ex. 'password').
--}}
@props([
    'name', 'label', 'type' => 'text', 'value' => null, 'options' => [],
    'placeholder' => null, 'hint' => null, 'optional' => false, 'bag' => 'default',
])
@php
    $error = $errors->getBag($bag)->first($name);
    $current = old($name, $value);
@endphp
<label @class(['field-label', 'has-error' => $error])>
    <span>{{ $label }}@if ($optional) <span class="muted">(facultatif)</span>@endif</span>
    @if ($type === 'textarea')
        <textarea name="{{ $name }}" placeholder="{{ $placeholder }}" {{ $attributes }}>{{ $current }}</textarea>
    @elseif ($type === 'select')
        <select name="{{ $name }}" {{ $attributes }}>
            @if ($placeholder)<option value="">{{ $placeholder }}</option>@endif
            @foreach ($options as $key => $text)
                <option value="{{ $key }}" @selected((string) $key === (string) $current)>{{ $text }}</option>
            @endforeach
        </select>
    @else
        <input type="{{ $type }}" name="{{ $name }}" value="{{ $type === 'password' ? '' : $current }}" placeholder="{{ $placeholder }}" {{ $attributes }}>
    @endif
    @if ($hint)<span class="hint">{{ $hint }}</span>@endif
    @if ($error)<span class="error">{{ $error }}</span>@endif
</label>
