{{-- Une ligne du répéteur « paragraphes ». $i vaut __INDEX__ dans le <template>. --}}
<li class="repeater-row section-row" data-repeater-row>
    <div class="repeater-fields">
        <label class="field-label @error("sections.$i.title") has-error @enderror">
            <span>Titre <span class="muted">(facultatif)</span></span>
            <input type="text" name="sections[{{ $i }}][title]" value="{{ $section['title'] ?? '' }}" maxlength="120" placeholder="Ex. Venir au festival">
        </label>
        <label class="field-label @error("sections.$i.body") has-error @enderror">
            <span>Texte</span>
            <textarea name="sections[{{ $i }}][body]" rows="4" maxlength="5000">{{ $section['body'] ?? '' }}</textarea>
            @error("sections.$i.body")<span class="error">{{ $message }}</span>@enderror
        </label>
    </div>
    <div class="repeater-tools">
        <button type="button" class="icon-btn" data-repeater-up title="Monter"><x-icon name="chevron-left" class="rot-90" /></button>
        <button type="button" class="icon-btn" data-repeater-down title="Descendre"><x-icon name="chevron-right" class="rot-90" /></button>
        <button type="button" class="icon-btn icon-btn-danger" data-repeater-remove title="Retirer"><x-icon name="trash" /></button>
    </div>
</li>
