@props([])

@php
$btn = 'p-1.5 rounded hover:bg-zinc-200 dark:hover:bg-white/10 text-zinc-600 dark:text-zinc-300 transition-colors';
$div = 'w-px h-5 bg-zinc-200 dark:bg-white/10 mx-0.5';
@endphp

<div {{ $attributes->class('flex flex-wrap items-center gap-0.5 px-2 py-1.5 border-b border-zinc-100 dark:border-white/10 bg-zinc-50 dark:bg-white/5') }}>
    <button type="button" @click="exec('bold')"          :class="state('bold')          && 'bg-zinc-200 dark:bg-white/10'" class="{{ $btn }}"><flux:icon.bold          class="size-4" /></button>
    <button type="button" @click="exec('italic')"        :class="state('italic')        && 'bg-zinc-200 dark:bg-white/10'" class="{{ $btn }}"><flux:icon.italic        class="size-4" /></button>
    <button type="button" @click="exec('underline')"     :class="state('underline')     && 'bg-zinc-200 dark:bg-white/10'" class="{{ $btn }}"><flux:icon.underline     class="size-4" /></button>
    <button type="button" @click="exec('strikeThrough')" :class="state('strikeThrough') && 'bg-zinc-200 dark:bg-white/10'" class="{{ $btn }}"><flux:icon.strikethrough class="size-4" /></button>

    <div class="{{ $div }}"></div>

    <button type="button" @click="exec('insertUnorderedList')" class="{{ $btn }}"><flux:icon.list-bullet    class="size-4" /></button>
    <button type="button" @click="exec('insertOrderedList')"   class="{{ $btn }}"><flux:icon.numbered-list  class="size-4" /></button>

    <div class="{{ $div }}"></div>

    <button type="button" @click="exec('justifyLeft')"   class="{{ $btn }}"><flux:icon.bars-3-bottom-left class="size-4" /></button>
    <button type="button" @click="exec('justifyCenter')" class="{{ $btn }}"><flux:icon.bars-3             class="size-4" /></button>
    <button type="button" @click="exec('justifyRight')"  class="{{ $btn }}"><flux:icon.bars-3-bottom-right class="size-4" /></button>

    <div class="{{ $div }}"></div>

    <button type="button" @click="exec('createLink', prompt('Enter URL:'))" class="{{ $btn }}"><flux:icon.link       class="size-4" /></button>
    <button type="button" @click="exec('unlink')"                           class="{{ $btn }}"><flux:icon.link-slash class="size-4" /></button>

    <div class="{{ $div }}"></div>

    <button type="button" @click="exec('undo')" class="{{ $btn }}"><flux:icon.arrow-uturn-left  class="size-4" /></button>
    <button type="button" @click="exec('redo')" class="{{ $btn }}"><flux:icon.arrow-uturn-right class="size-4" /></button>

    {{ $slot ?? '' }}
</div>
