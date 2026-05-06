@php
$classes = Flux::classes()
    ->add('h-8 flex items-center justify-center')
    ->add('in-data-flux-sidebar-collapsed-desktop:opacity-0')
    ->add('in-data-flux-sidebar-collapsed-desktop:absolute')
    ->add('in-data-flux-sidebar-collapsed-desktop:in-data-flux-sidebar-active:opacity-100')
    ;

$buttonClasses = Flux::classes()
    ->add('relative items-center font-medium justify-center gap-2 me-2 cursor-pointer whitespace-nowrap disabled:opacity-75 dark:disabled:opacity-75 disabled:cursor-default disabled:pointer-events-none text-sm rounded-lg inline-flex  bg-transparent hover:bg-zinc-800/5 dark:hover:bg-white/15 text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-white')
    ->add('in-data-flux-sidebar-collapsed-desktop:cursor-pointer rtl:in-data-flux-sidebar-collapsed-desktop:cursor-pointer')
    ->add('[&[collapsible="mobile"]]:in-data-flux-sidebar-on-desktop:hidden')
    ->add('rtl:rotate-180')
    ;
@endphp

<ui-sidebar-toggle {{ $attributes->class($classes) }} data-flux-sidebar-collapse>
    <button type="button" class="{{ $buttonClasses }}">
        <svg class="text-zinc-500 dark:text-zinc-400" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M7.5 3.75V16.25M3.4375 16.25H16.5625C17.08 16.25 17.5 15.83 17.5 15.3125V4.6875C17.5 4.17 17.08 3.75 16.5625 3.75H3.4375C2.92 3.75 2.5 4.17 2.5 4.6875V15.3125C2.5 15.83 2.92 16.25 3.4375 16.25Z" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"></path>
        </svg>
    </button>
</ui-sidebar-toggle>
