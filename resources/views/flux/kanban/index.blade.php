@props([])

<div
    x-data="{
        dragging: null,
        dragCol: null,
        startDrag(cardId, colId) { this.dragging = cardId; this.dragCol = colId; },
        endDrag() { this.dragging = null; this.dragCol = null; },
    }"
    {{ $attributes->class('flex gap-4 overflow-x-auto pb-4 items-start') }}
    data-flux-kanban
>
    {{ $slot }}
</div>
