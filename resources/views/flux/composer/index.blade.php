@props([
    'name'        => null,
    'placeholder' => 'Write a message...',
    'disabled'    => false,
])

<div
    x-data="{
        content: '',
        files: [],
        submit() {
            if (!this.content.trim() && !this.files.length) return;
            this.$dispatch('composer-submit', { content: this.content, files: this.files });
            this.content = '';
            this.files = [];
            this.$refs.body.innerHTML = '';
        },
        addFiles(e) {
            this.files.push(...[...e.target.files].map(f => ({ name: f.name, size: f.size })));
        },
        removeFile(i) { this.files.splice(i, 1); },
        fmt(b) { return b < 1048576 ? (b/1024).toFixed(1)+' KB' : (b/1048576).toFixed(1)+' MB'; }
    }"
    {{ $attributes->class(['border border-zinc-200 dark:border-white/10 rounded-xl bg-white dark:bg-zinc-900 overflow-hidden', $disabled ? 'opacity-50 pointer-events-none' : '']) }}
>
    <div
        x-ref="body"
        contenteditable="true"
        @input="content = $el.innerText"
        @keydown.ctrl.enter="submit()"
        @keydown.meta.enter="submit()"
        placeholder="{{ $placeholder }}"
        class="min-h-20 max-h-48 overflow-y-auto px-4 py-3 text-sm text-zinc-700 dark:text-zinc-200 outline-none
            empty:before:content-[attr(placeholder)] empty:before:text-zinc-400 empty:before:pointer-events-none"
    ></div>

    <template x-if="files.length">
        <div class="flex flex-wrap gap-1.5 px-3 pb-2">
            <template x-for="(f, i) in files" :key="i">
                <span class="flex items-center gap-1.5 text-xs bg-zinc-100 dark:bg-white/10 text-zinc-600 dark:text-zinc-300 px-2 py-1 rounded-lg">
                    <flux:icon.paper-clip class="size-3 shrink-0" />
                    <span x-text="f.name" class="max-w-28 truncate"></span>
                    <button type="button" @click="removeFile(i)" class="text-zinc-400 hover:text-zinc-600">
                        <flux:icon.x-mark class="size-3" />
                    </button>
                </span>
            </template>
        </div>
    </template>

    <div class="flex items-center justify-between gap-2 px-3 py-2 border-t border-zinc-100 dark:border-white/10">
        <div class="flex items-center gap-1">
            <label class="cursor-pointer p-1.5 rounded-lg hover:bg-zinc-100 dark:hover:bg-white/10 text-zinc-400 hover:text-zinc-600 transition-colors">
                <flux:icon.paper-clip class="size-4" />
                <input type="file" multiple @change="addFiles($event)" class="hidden" />
            </label>
            {{ $toolbar ?? '' }}
        </div>
        <div class="flex items-center gap-2">
            <span class="text-xs text-zinc-400 hidden sm:block">⌘↵ to send</span>
            <flux:button size="sm" variant="primary" icon="paper-airplane" type="button" @click="submit()">
                Send
            </flux:button>
        </div>
    </div>

    @if($name)
        <input type="hidden" :value="content" name="{{ $name }}" />
    @endif
</div>
