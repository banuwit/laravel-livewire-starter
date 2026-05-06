<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen">
        <div class="flex min-h-screen">
            <!-- Left Side - Hero Section -->
            <div class="flex-1 p-4 max-lg:hidden">
                <div class="text-white relative rounded-lg h-full w-full bg-zinc-900 flex flex-col items-start justify-end p-16 bg-gradient-to-br from-zinc-800 via-zinc-900 to-zinc-950">
                    <div class="flex gap-2 mb-4">
                        <flux:icon.star variant="solid" />
                        <flux:icon.star variant="solid" />
                        <flux:icon.star variant="solid" />
                        <flux:icon.star variant="solid" />
                        <flux:icon.star variant="solid" />
                    </div>

                    <div class="mb-6 italic font-base text-3xl xl:text-4xl">
                        Flux has enabled me to design, build, and deliver apps faster than ever before.
                    </div>

                    <div class="flex gap-4">
                        <flux:avatar src="https://fluxui.dev/img/demo/caleb.png" size="xl" />

                        <div class="flex flex-col justify-center font-medium">
                            <div class="text-lg">Caleb Porzio</div>
                            <div class="text-zinc-300">Creator of Livewire</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side - Login Form -->
            <div class="flex-1 flex justify-center items-center">
                <div class="w-full max-w-md space-y-6 px-8">
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
