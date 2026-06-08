<?php

use App\Concerns\HasFileUpload;
use App\Concerns\FileUploadValidationRules;
use Flux\Flux;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Profile settings')] class extends Component {
    use HasFileUpload, FileUploadValidationRules;

    public function mount(): void
    {
        Auth::user()->load(['company', 'branch', 'profile.religion', 'profile.maritalStatus', 'profile.country', 'profile.province', 'profile.city']);
    }

    public function updateAvatar(): void
    {
        $this->validate($this->imageUploadRules(), $this->imageUploadMessages());

        if (! $this->uploadedFile) {
            Flux::toast(variant: 'warning', text: __('Please select an image first.'));
            return;
        }

        Auth::user()
            ->addMedia($this->uploadedFile)
            ->toMediaCollection('avatar');

        Auth::user()->refresh();
        $this->uploadedFile = null;

        Flux::toast(variant: 'success', text: __('Avatar updated.'));
    }

    public function removeAvatar(): void
    {
        Auth::user()->clearMediaCollection('avatar');

        Flux::toast(variant: 'success', text: __('Avatar removed.'));
    }

    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));
            return;
        }

        $user->sendEmailVerificationNotification();

        Flux::toast(text: __('A new verification link has been sent to your email address.'));
    }

    #[Computed]
    public function hasProfile(): bool
    {
        return Auth::user()->profile !== null;
    }

    #[Computed]
    public function hasUnverifiedEmail(): bool
    {
        return Auth::user() instanceof MustVerifyEmail && ! Auth::user()->hasVerifiedEmail();
    }

    #[Computed]
    public function showDeleteUser(): bool
    {
        return ! Auth::user() instanceof MustVerifyEmail
            || (Auth::user() instanceof MustVerifyEmail && Auth::user()->hasVerifiedEmail());
    }
}; ?>

<section class="w-full flex flex-col gap-6">
    {{-- Header --}}
    <div class="flex items-center justify-between gap-4">
        <div class="flex flex-col gap-1">
            <flux:heading size="xl">{{ __('Profile Settings') }}</flux:heading>
            <flux:text variant="muted">{{ __('View and manage your profile information.') }}</flux:text>
        </div>
    </div>

    {{-- Avatar Section --}}
    <flux:card class="space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="md">{{ __('Avatar') }}</flux:heading>
                <flux:text variant="muted" size="sm">{{ __('Upload a profile photo.') }}</flux:text>
            </div>
            <flux:avatar
                size="lg"
                :src="Auth::user()->avatarUrl()"
                :name="Auth::user()->displayName()"
                :initials="Auth::user()->initials()"
                circle
            />
        </div>
        <flux:separator />

        <div class="space-y-3">
            <div>
                <flux:file-upload
                    wire:model="uploadedFile"
                    accept="image/jpeg,image/png,image/webp,image/gif"
                />
                <flux:text size="xs" variant="muted" class="mt-2">
                    {{ __('JPG, PNG, WebP, or GIF • Maximum 2MB') }}
                </flux:text>
            </div>

            @error('uploadedFile')
                <flux:text size="sm" class="!text-red-500">{{ $message }}</flux:text>
            @enderror

            <div class="flex gap-2">
                <flux:button
                    wire:click="updateAvatar"
                    wire:loading.attr="disabled"
                    wire:target="uploadedFile,updateAvatar"
                    variant="primary"
                    size="sm"
                    icon="arrow-up-tray"
                >
                    <span wire:loading.remove wire:target="uploadedFile,updateAvatar">{{ __('Upload') }}</span>
                    <span wire:loading wire:target="uploadedFile,updateAvatar">{{ __('Uploading…') }}</span>
                </flux:button>

                @if(Auth::user()->getFirstMedia('avatar'))
                    <flux:button
                        wire:click="removeAvatar"
                        wire:confirm="{{ __('Remove your profile photo?') }}"
                        variant="ghost"
                        size="sm"
                        icon="trash"
                    >
                        {{ __('Remove') }}
                    </flux:button>
                @endif
            </div>
        </div>
    </flux:card>

    {{-- Personal Information Section --}}
    <flux:card class="space-y-5">
        <div>
            <flux:heading size="lg">{{ __('Personal Information') }}</flux:heading>
            <flux:text variant="muted" size="sm">{{ __('Your basic profile and contact details.') }}</flux:text>
        </div>
        <flux:separator />

        <div class="space-y-5">
            <div>
                <flux:label>{{ __('Full Name') }}</flux:label>
                <flux:text class="mt-2 font-medium text-zinc-900 dark:text-zinc-100">{{ Auth::user()->name ?? '—' }}</flux:text>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <flux:label>{{ __('Email Address') }}</flux:label>
                    <div class="mt-2 flex items-center gap-2">
                        <flux:text class="font-medium text-zinc-900 dark:text-zinc-100">{{ Auth::user()->email }}</flux:text>
                        @if (Auth::user()->hasVerifiedEmail())
                            <flux:badge color="emerald" size="sm">{{ __('Verified') }}</flux:badge>
                        @else
                            <flux:badge color="amber" size="sm">{{ __('Unverified') }}</flux:badge>
                        @endif
                    </div>
                    @if ($this->hasUnverifiedEmail)
                        <flux:text class="mt-2 text-sm text-amber-600 dark:text-amber-500">
                            {{ __('Your email address is unverified.') }}
                            <flux:link class="cursor-pointer font-medium" wire:click.prevent="resendVerificationNotification">
                                {{ __('Resend verification link') }}
                            </flux:link>
                        </flux:text>
                    @endif
                </div>

                <div>
                    <flux:label>{{ __('Phone Number') }}</flux:label>
                    <flux:text class="mt-2 font-medium text-zinc-900 dark:text-zinc-100">{{ Auth::user()->phonenumber ?? '—' }}</flux:text>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <flux:label>{{ __('Gender') }}</flux:label>
                    <flux:text class="mt-2 font-medium text-zinc-900 dark:text-zinc-100">
                        @if (Auth::user()->gender === 'male')
                            <flux:badge color="sky">{{ __('Male') }}</flux:badge>
                        @elseif (Auth::user()->gender === 'female')
                            <flux:badge color="pink">{{ __('Female') }}</flux:badge>
                        @else
                            <span>—</span>
                        @endif
                    </flux:text>
                </div>

                @if (Auth::user()->company || Auth::user()->branch)
                    <div>
                        <flux:label>{{ __('Company') }}</flux:label>
                        <flux:text class="mt-2 font-medium text-zinc-900 dark:text-zinc-100">{{ Auth::user()->company?->name ?? '—' }}</flux:text>
                    </div>
                @endif
            </div>

            @if (Auth::user()->branch)
                <div>
                    <flux:label>{{ __('Branch') }}</flux:label>
                    <flux:text class="mt-2 font-medium text-zinc-900 dark:text-zinc-100">{{ Auth::user()->branch?->name ?? '—' }}</flux:text>
                </div>
            @endif
        </div>
    </flux:card>

    {{-- Profile Data Section --}}
    @if ($this->hasProfile)
        <flux:card class="space-y-5">
            <div>
                <flux:heading size="lg">{{ __('Additional Information') }}</flux:heading>
                <flux:text variant="muted" size="sm">{{ __('Extended profile and identity details.') }}</flux:text>
            </div>
            <flux:separator />

            <div class="space-y-5">
                {{-- Identity Section --}}
                <div>
                    <flux:label class="font-medium text-xs text-zinc-500 dark:text-zinc-400 uppercase tracking-wide">{{ __('Identity') }}</flux:label>
                    <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <flux:label>{{ __('Identity Number') }}</flux:label>
                            <flux:text class="mt-2 font-medium text-zinc-900 dark:text-zinc-100">{{ Auth::user()->profile->identity_number ?? '—' }}</flux:text>
                        </div>
                        <div>
                            <flux:label>{{ __('Birth Date') }}</flux:label>
                            <flux:text class="mt-2 font-medium text-zinc-900 dark:text-zinc-100">
                                {{ Auth::user()->profile->birth_date?->format('d M Y') ?? '—' }}
                            </flux:text>
                        </div>
                    </div>
                </div>

                {{-- Personal Details Section --}}
                <div>
                    <flux:label class="font-medium text-xs text-zinc-500 dark:text-zinc-400 uppercase tracking-wide">{{ __('Personal Details') }}</flux:label>
                    <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <flux:label>{{ __('Religion') }}</flux:label>
                            <flux:text class="mt-2 font-medium text-zinc-900 dark:text-zinc-100">{{ Auth::user()->profile->religion?->value ?? '—' }}</flux:text>
                        </div>
                        <div>
                            <flux:label>{{ __('Marital Status') }}</flux:label>
                            <flux:text class="mt-2 font-medium text-zinc-900 dark:text-zinc-100">{{ Auth::user()->profile->maritalStatus?->value ?? '—' }}</flux:text>
                        </div>
                    </div>
                </div>

                {{-- Address Section --}}
                <div>
                    <flux:label class="font-medium text-xs text-zinc-500 dark:text-zinc-400 uppercase tracking-wide">{{ __('Address') }}</flux:label>
                    <div class="mt-3 space-y-5">
                        <div>
                            <flux:label>{{ __('Street Address') }}</flux:label>
                            <flux:text class="mt-2 font-medium text-zinc-900 dark:text-zinc-100">{{ Auth::user()->profile->address ?? '—' }}</flux:text>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                            <div>
                                <flux:label>{{ __('Country') }}</flux:label>
                                <flux:text class="mt-2 font-medium text-zinc-900 dark:text-zinc-100">{{ Auth::user()->profile->country?->name ?? '—' }}</flux:text>
                            </div>
                            <div>
                                <flux:label>{{ __('Province') }}</flux:label>
                                <flux:text class="mt-2 font-medium text-zinc-900 dark:text-zinc-100">{{ Auth::user()->profile->province?->name ?? '—' }}</flux:text>
                            </div>
                            <div>
                                <flux:label>{{ __('City') }}</flux:label>
                                <flux:text class="mt-2 font-medium text-zinc-900 dark:text-zinc-100">{{ Auth::user()->profile->city?->name ?? '—' }}</flux:text>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </flux:card>
    @else
        <flux:card class="space-y-4">
            <div class="flex flex-col items-center justify-center gap-3 py-8">
                <flux:icon.identification class="size-12 text-zinc-300 dark:text-zinc-600" />
                <div class="text-center">
                    <flux:heading size="md">{{ __('No Additional Information') }}</flux:heading>
                    <flux:text variant="muted" size="sm" class="mt-1">{{ __('Your account is not linked to an extended profile yet.') }}</flux:text>
                </div>
            </div>
        </flux:card>
    @endif

    {{-- Danger Zone --}}
    @if ($this->showDeleteUser)
        <flux:card class="space-y-5 border-red-200 dark:border-red-900">
            <div>
                <flux:heading size="lg" class="text-red-600 dark:text-red-400">{{ __('Danger Zone') }}</flux:heading>
                <flux:text variant="muted" size="sm">{{ __('Permanent actions that cannot be undone.') }}</flux:text>
            </div>
            <flux:separator />
            <livewire:pages::settings.delete-user-form />
        </flux:card>
    @endif
</section>
