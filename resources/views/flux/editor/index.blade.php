@props([
    'name'        => null,
    'placeholder' => 'Start writing...',
    'height'      => '200px',
    'toolbar'     => true,
    'disabled'    => false,
])

<div
    x-data="{
        content: '',
        exec(cmd, val = null) { document.execCommand(cmd, false, val); this.$refs.body.focus(); },
        state(cmd) { return document.queryCommandState(cmd); },
    }"
    {{ $attributes->class(['border border-zinc-200 dark:border-white/10 rounded-xl overflow-hidden bg-white dark:bg-white/5', $disabled ? 'opacity-50 pointer-events-none' : '']) }}
    @if($toolbar)
        <flux:editor.toolbar />
    @endif

    <div
        x-ref="body"
        @if(!$disabled) contenteditable="true" @endif
        @input="content = $el.innerHTML; $dispatch('input', content)"
        placeholder="{{ $placeholder }}"
        class="outline-none px-4 py-3 text-sm text-zinc-700 dark:text-zinc-200 prose prose-sm max-w-none dark:prose-invert
            empty:before:content-[attr(placeholder)] empty:before:text-zinc-400 empty:before:pointer-events-none"
        style="min-height: {{ $height }}"
        data-flux-control
    ></div>

    @if($name)
        <input type="hidden" :value="content" name="{{ $name }}" />
    @endif
</div>
