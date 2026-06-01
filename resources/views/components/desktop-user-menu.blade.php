<flux:dropdown position="bottom" align="start">
    <x-header-profile
        :name="auth()->user()->displayName()"
        :initials="auth()->user()->initials()"
        :role="auth()->user()->getRoleNames()->first()"
    />

    <flux:menu>
        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
            <flux:avatar
                size="md"
                :src="auth()->user()->avatarUrl()"
                :name="auth()->user()->displayName()"
                :initials="auth()->user()->initials()"
            />
            <div class="grid flex-1 text-start text-sm leading-tight">
                <flux:heading class="truncate">{{ auth()->user()->displayName() }}</flux:heading>
                <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
            </div>
        </div>
        <flux:menu.separator />

        <flux:menu.radio.group>
            <flux:menu.item :href="route('profile.edit')" icon="user" wire:navigate>
                {{ __('Profile') }}
            </flux:menu.item>
            <flux:menu.item :href="route('security.edit')" icon="lock-closed" wire:navigate>
                {{ __('Change Password') }}
            </flux:menu.item>
            <form method="POST" action="{{ route('logout') }}" class="w-full">
                @csrf
                <flux:menu.item
                    as="button"
                    type="submit"
                    variant="danger"
                    class="w-full cursor-pointer !text-red-500 dark:!text-red-400"
                    data-test="logout-button"
                >
                    <flux:icon icon="arrow-right-start-on-rectangle" class="me-1" />
                    {{ __('Log out') }}
                </flux:menu.item>
            </form>
        </flux:menu.radio.group>
    </flux:menu>
</flux:dropdown>
