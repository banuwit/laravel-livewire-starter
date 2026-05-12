@props([
    'name' => 'file',
    'multiple' => false,
    'accept' => null,
    'maxSize' => null,
    'disabled' => false,
])

<div
    x-data="{
        files: [],
        isDragging: false,

        handleDrop(e) {
            this.isDragging = false;
            this.addFiles([...e.dataTransfer.files]);
        },

        handleInput(e) {
            this.addFiles([...e.target.files]);
        },

        addFiles(newFiles) {
            @if(!$multiple) this.files = []; @endif
            newFiles.forEach(f => {
                const reader = new FileReader();
                reader.onload = e => {
                    this.files.push({
                        name: f.name,
                        size: f.size,
                        type: f.type,
                        preview: f.type.startsWith('image/') ? e.target.result : null,
                    });
                };
                reader.readAsDataURL(f);
            });
        },

        remove(index) { this.files.splice(index, 1); },

        fmt(bytes) {
            if (bytes < 1024) return bytes + ' B';
            if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
            return (bytes / 1048576).toFixed(1) + ' MB';
        }
    }"
    {{ $attributes }}
>
    <div
        @dragover.prevent="isDragging = true"
        @dragleave.prevent="isDragging = false"
        @drop.prevent="!{{ $disabled ? 'true' : 'false' }} && handleDrop($event)"
        @click="!{{ $disabled ? 'true' : 'false' }} && $refs.input.click()"
        :class="isDragging
            ? 'border-accent bg-accent/5'
            : 'border-zinc-300 dark:border-white/20 hover:border-accent/50 hover:bg-zinc-50 dark:hover:bg-white/5'"
        class="flex flex-col items-center justify-center gap-3 border-2 border-dashed rounded-xl p-8 cursor-pointer transition-colors
            {{ $disabled ? 'opacity-50 cursor-not-allowed pointer-events-none' : '' }}"
    >
        <div class="flex items-center justify-center size-12 rounded-xl bg-zinc-100 dark:bg-white/10">
            <flux:icon.arrow-up-tray class="size-6 text-zinc-500 dark:text-zinc-400" />
        </div>
        <div class="text-center">
            <p class="text-sm font-medium text-zinc-700 dark:text-zinc-200">
                Drop files here or <span class="text-accent">browse</span>
            </p>
            @if($accept || $maxSize)
                <p class="text-xs text-zinc-400 mt-1">
                    {{ implode(' · ', array_filter([$accept, $maxSize ? "Max {$maxSize}MB" : null])) }}
                </p>
            @endif
        </div>

        <input
            x-ref="input"
            type="file"
            name="{{ $name }}"
            @if($multiple) multiple @endif
            @if($accept) accept="{{ $accept }}" @endif
            @change="handleInput($event)"
            class="hidden"
        />
    </div>

    <ul x-show="files.length > 0" class="mt-3 space-y-2">
        <template x-for="(file, i) in files" :key="i">
            <li class="flex items-center gap-3 p-3 bg-zinc-50 dark:bg-white/5 border border-zinc-200 dark:border-white/10 rounded-lg">
                <div class="size-10 rounded-lg overflow-hidden bg-zinc-200 dark:bg-white/10 shrink-0 flex items-center justify-center">
                    <template x-if="file.preview">
                        <img :src="file.preview" class="size-full object-cover" />
                    </template>
                    <template x-if="!file.preview">
                        <flux:icon.document class="size-5 text-zinc-400" />
                    </template>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-zinc-700 dark:text-zinc-200 truncate" x-text="file.name"></p>
                    <p class="text-xs text-zinc-400" x-text="fmt(file.size)"></p>
                </div>
                <button type="button" @click="remove(i)"
                    class="shrink-0 p-1 rounded-lg text-zinc-400 hover:text-zinc-600 hover:bg-zinc-100 dark:hover:bg-white/10 transition-colors">
                    <flux:icon.x-mark class="size-4" />
                </button>
            </li>
        </template>
    </ul>
</div>
