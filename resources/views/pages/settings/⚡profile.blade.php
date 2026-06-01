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

<section class="section w-full h-full flex flex-col gap-4">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('Profile settings') }}</flux:heading>

    <x-pages::settings.layout :heading="__('Profile')" :subheading="__('Manage your profile and account settings')">
        {{-- Section: Avatar --}}
        <div class="my-6 w-full">
            <flux:heading size="lg">{{ __('Avatar') }}</flux:heading>
            <flux:text variant="muted" size="sm">{{ __('Upload a profile photo.') }}</flux:text>

            <div class="mt-4 flex items-start gap-6">
                <flux:avatar
                    size="xl"
                    :src="Auth::user()->avatarUrl()"
                    :name="Auth::user()->displayName()"
                    :initials="Auth::user()->initials()"
                    circle
                />

                <div class="flex flex-col gap-3 flex-1">
                    <flux:file-upload
                        wire:model="uploadedFile"
                        accept="image/jpeg,image/png,image/webp,image/gif"
                    />

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
                            >
                                {{ __('Remove') }}
                            </flux:button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <flux:separator class="my-6" />

        {{-- Section A: Profile --}}
        <div class="my-6 w-full">
            <flux:heading size="lg">{{ __('Profile') }}</flux:heading>
            <flux:text variant="muted" size="sm">{{ __('Your personal information and login email.') }}</flux:text>

            <div class="mt-4 space-y-6">
                <div>
                    <flux:label>{{ __('Full Name') }}</flux:label>
                    <flux:text class="mt-1 font-medium">{{ Auth::user()->name ?? '—' }}</flux:text>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <flux:label>{{ __('Phone Number') }}</flux:label>
                        <flux:text class="mt-1 font-medium">{{ Auth::user()->phonenumber ?? '—' }}</flux:text>
                    </div>
                    <div>
                        <flux:label>{{ __('Gender') }}</flux:label>
                        <flux:text class="mt-1 font-medium">{{ Auth::user()->gender ?? '—' }}</flux:text>
                    </div>
                </div>

                <div>
                    <flux:label>{{ __('Email') }}</flux:label>
                    <flux:text class="mt-1 font-medium">{{ Auth::user()->email }}</flux:text>
                    @if ($this->hasUnverifiedEmail)
                        <flux:text class="mt-4 text-amber-600 dark:text-amber-500">
                            {{ __('Your email address is unverified.') }}
                            <flux:link class="text-sm cursor-pointer" wire:click.prevent="resendVerificationNotification">
                                {{ __('Click here to re-send the verification email.') }}
                            </flux:link>
                        </flux:text>
                    @endif
                </div>

                @if(Auth::user()->company || Auth::user()->branch)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <flux:label>{{ __('Company') }}</flux:label>
                            <flux:text class="mt-1 font-medium">{{ Auth::user()->company?->name ?? '—' }}</flux:text>
                        </div>
                        <div>
                            <flux:label>{{ __('Branch') }}</flux:label>
                            <flux:text class="mt-1 font-medium">{{ Auth::user()->branch?->name ?? '—' }}</flux:text>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <flux:separator class="my-6" />

        {{-- Section B: Profile Data --}}
        <div class="w-full">
            <flux:heading size="lg">{{ __('Profile Data') }}</flux:heading>
            <flux:text variant="muted" size="sm">{{ __('Additional personal data linked to your profile record.') }}</flux:text>

            @if ($this->hasProfile)
                <div class="mt-4 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <flux:label>{{ __('Identity Number') }}</flux:label>
                            <flux:text class="mt-1 font-medium">{{ Auth::user()->profile->identity_number ?? '—' }}</flux:text>
                        </div>
                        <div>
                            <flux:label>{{ __('Religion') }}</flux:label>
                            <flux:text class="mt-1 font-medium">{{ Auth::user()->profile->religion?->name ?? '—' }}</flux:text>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <flux:label>{{ __('Marital Status') }}</flux:label>
                            <flux:text class="mt-1 font-medium">{{ Auth::user()->profile->maritalStatus?->name ?? '—' }}</flux:text>
                        </div>
                        <div>
                            <flux:label>{{ __('Birth Date') }}</flux:label>
                            <flux:text class="mt-1 font-medium">
                                {{ Auth::user()->profile->birth_date?->format('d-m-Y') ?? '—' }}
                            </flux:text>
                        </div>
                    </div>

                    <div>
                        <flux:label>{{ __('Address') }}</flux:label>
                        <flux:text class="mt-1 font-medium">{{ Auth::user()->profile->address ?? '—' }}</flux:text>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <div>
                            <flux:label>{{ __('Country') }}</flux:label>
                            <flux:text class="mt-1 font-medium">
                                {{ Auth::user()->profile->country?->name ?? '—' }}
                            </flux:text>
                        </div>
                        <div>
                            <flux:label>{{ __('Province') }}</flux:label>
                            <flux:text class="mt-1 font-medium">
                                {{ Auth::user()->profile->province?->name ?? '—' }}
                            </flux:text>
                        </div>
                        <div>
                            <flux:label>{{ __('City') }}</flux:label>
                            <flux:text class="mt-1 font-medium">
                                {{ Auth::user()->profile->city?->name ?? '—' }}
                            </flux:text>
                        </div>
                    </div>
                </div>
            @else
                <div class="mt-6 flex flex-col items-center justify-center gap-3 rounded-lg border border-dashed border-zinc-200 dark:border-zinc-700 py-12">
                    <flux:icon.identification class="size-16 text-zinc-300 dark:text-zinc-600" />
                    <flux:heading size="md">{{ __('No profile data') }}</flux:heading>
                    <flux:text variant="muted" size="sm">{{ __('Your account is not linked to a profile record yet.') }}</flux:text>
                </div>
            @endif
        </div>

        @if ($this->showDeleteUser)
            <flux:separator class="my-6" />
            <livewire:pages::settings.delete-user-form />
        @endif
    </x-pages::settings.layout>
</section>
