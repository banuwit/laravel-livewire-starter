<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-zinc-50 dark:bg-zinc-950">
        <div class="flex min-h-screen">
            <!-- Left Side - Hero Section -->
            <div class="flex-1 p-4 max-lg:hidden">
                <div class="relative rounded-2xl h-full w-full overflow-hidden flex flex-col items-start justify-end p-12
                    bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900">
                    <!-- Subtle grid overlay -->
                    <div class="absolute inset-0 opacity-[0.03]"
                        style="background-image: linear-gradient(rgba(255,255,255,.5) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.5) 1px, transparent 1px); background-size: 40px 40px;"></div>
                    <!-- Accent glow -->
                    <div class="absolute top-1/3 left-1/2 -translate-x-1/2 -translate-y-1/2 w-96 h-96 rounded-full opacity-20 blur-3xl"
                        style="background: radial-gradient(circle, var(--color-accent) 0%, transparent 70%);"></div>

                    <div class="relative text-white">
                        <div class="flex gap-1 mb-6">
                            @for ($i = 0; $i < 5; $i++)
                                <flux:icon.star variant="solid" class="text-yellow-400 size-4" />
                            @endfor
                        </div>

                        <blockquote class="mb-8 text-2xl xl:text-3xl font-medium leading-snug tracking-tight text-white/90">
                            "Flux has enabled me to design, build, and deliver apps faster than ever before."
                        </blockquote>

                        <div class="flex items-center gap-4">
                            <flux:avatar src="https://fluxui.dev/img/demo/caleb.png" size="lg" />
                            <div>
                                <div class="font-semibold text-white">Caleb Porzio</div>
                                <div class="text-sm text-white/50">Creator of Livewire</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side - Login Form -->
            <div class="flex-1 flex justify-center items-center bg-white dark:bg-zinc-950">
                <div class="w-full max-w-sm space-y-6 px-8">
                    {{ $slot }}
                </div>
            </div>
        </div>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
