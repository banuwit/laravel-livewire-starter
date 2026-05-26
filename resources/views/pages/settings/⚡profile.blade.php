<?php

use App\Concerns\ProfileDataValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\City;
use App\Models\Country;
use App\Models\Province;
use Flux\Flux;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Profile settings')] class extends Component {
    use ProfileValidationRules, ProfileDataValidationRules;

    // Account
    public string $email = '';

    // Profile (on user)
    public string $name = '';
    public ?string $phonenumber = null;
    public ?string $gender = null;

    // Profile data
    public ?string $identity_number = null;
    public ?string $religion = null;
    public ?string $birth_date = null;
    public ?string $marital_status = null;
    public ?string $address = null;
    public ?int $country_id = null;
    public ?int $province_id = null;
    public ?int $city_id = null;

    public array $countries = [];
    public array $provinces = [];
    public array $cities = [];

    public function mount(): void
    {
        $user = Auth::user();
        $this->email = $user->email;
        $this->name = $user->name ?? '';
        $this->phonenumber = $user->phonenumber;
        $this->gender = $user->gender;

        $this->countries = Country::orderBy('name')->get()->toArray();

        if ($profile = $user->profile) {
            $this->identity_number = $profile->identity_number;
            $this->religion = $profile->religion;
            $this->birth_date = $profile->birth_date?->format('Y-m-d');
            $this->marital_status = $profile->marital_status;
            $this->address = $profile->address;
            $this->country_id = $profile->country_id;
            $this->province_id = $profile->province_id;
            $this->city_id = $profile->city_id;

            $this->provinces = $this->country_id ? Province::where('country_id', $this->country_id)->orderBy('name')->get()->toArray() : [];
            $this->cities = $this->province_id ? City::where('province_id', $this->province_id)->orderBy('name')->get()->toArray() : [];
        }
    }

    public function updatedCountryId($value): void
    {
        $this->province_id = null;
        $this->city_id = null;
        $this->provinces = $value ? Province::where('country_id', $value)->orderBy('name')->get()->toArray() : [];
        $this->cities = [];
    }

    public function updatedProvinceId($value): void
    {
        $this->city_id = null;
        $this->cities = $value ? City::where('province_id', $value)->orderBy('name')->get()->toArray() : [];
    }

    public function updateProfile(): void
    {
        $user = Auth::user();
        $validated = $this->validate([
            ...$this->accountRules($user->id),
            ...$this->profileRules(),
        ]);

        $user->fill($validated);
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }
        $user->save();

        Flux::toast(variant: 'success', text: __('Profile updated.'));
    }

    public function updateProfileData(): void
    {
        $user = Auth::user();
        if (! $user->profile) {
            return;
        }

        $profile = $user->profile;
        $validated = $this->validate($this->profileDataRules($profile->id));

        $profile->update($validated);

        Flux::toast(variant: 'success', text: __('Profile data updated.'));
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
        {{-- Section A: Profile --}}
        <div class="my-6 w-full">
            <flux:heading size="lg">{{ __('Profile') }}</flux:heading>
            <flux:text variant="muted" size="sm">{{ __('Your personal information and login email.') }}</flux:text>

            <form wire:submit="updateProfile" class="mt-4 space-y-6">
                <flux:input wire:model="name" :label="__('Full Name')" type="text" required autocomplete="name" />

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <flux:input wire:model="phonenumber" type="tel" :label="__('Phone Number')" icon="phone" />
                    <flux:select wire:model="gender" variant="listbox" :label="__('Gender')" clearable :placeholder="__('Choose gender')">
                        <flux:select.option value="male">Male</flux:select.option>
                        <flux:select.option value="female">Female</flux:select.option>
                    </flux:select>
                </div>

                <div>
                    <flux:input wire:model="email" :label="__('Email')" type="email" required autocomplete="email" />
                    @if ($this->hasUnverifiedEmail)
                        <flux:text class="mt-4">
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

                <flux:button variant="primary" type="submit" data-test="update-account-button">
                    {{ __('Save profile') }}
                </flux:button>
            </form>
        </div>

        <flux:separator class="my-6" />

        {{-- Section B: Profile Data --}}
        <div class="w-full">
            <flux:heading size="lg">{{ __('Profile Data') }}</flux:heading>
            <flux:text variant="muted" size="sm">{{ __('Additional personal data linked to your profile record.') }}</flux:text>

            @if ($this->hasProfile)
                <form wire:submit="updateProfileData" class="mt-4 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <flux:input wire:model="identity_number" :label="__('Identity Number')" :placeholder="__('e.g. KTP / ID number')" />
                        <flux:select wire:model="religion" variant="listbox" :label="__('Religion')" searchable clearable :placeholder="__('Choose religion')">
                            <flux:select.option value="islam">Islam</flux:select.option>
                            <flux:select.option value="kristen">Christian</flux:select.option>
                            <flux:select.option value="hindu">Hindu</flux:select.option>
                            <flux:select.option value="buddhist">Buddhist</flux:select.option>
                            <flux:select.option value="other">Other</flux:select.option>
                        </flux:select>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <flux:select wire:model="marital_status" variant="listbox" :label="__('Marital Status')" clearable :placeholder="__('Choose status')">
                            <flux:select.option value="single">Single</flux:select.option>
                            <flux:select.option value="married">Married</flux:select.option>
                            <flux:select.option value="divorced">Divorced</flux:select.option>
                            <flux:select.option value="widowed">Widowed</flux:select.option>
                        </flux:select>
                        <flux:input wire:model="birth_date" type="date" :label="__('Birth Date')" />
                    </div>

                    <flux:textarea wire:model="address" :label="__('Address')" :placeholder="__('Street, building, etc.')" rows="2" />

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <flux:select wire:model.live="country_id" variant="listbox" :label="__('Country')" searchable clearable :placeholder="__('Choose country')">
                            @foreach ($countries as $country)
                                <flux:select.option value="{{ $country['id'] }}">{{ $country['name'] }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:select wire:model.live="province_id" variant="listbox" :label="__('Province')" searchable clearable :disabled="!$country_id" :placeholder="__('Choose province')">
                            @foreach ($provinces as $province)
                                <flux:select.option value="{{ $province['id'] }}">{{ $province['name'] }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:select wire:model="city_id" variant="listbox" :label="__('City')" searchable clearable :disabled="!$province_id" :placeholder="__('Choose city')">
                            @foreach ($cities as $city)
                                <flux:select.option value="{{ $city['id'] }}">{{ $city['name'] }}</flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>

                    <flux:button variant="primary" type="submit" data-test="update-profile-data-button">
                        {{ __('Save profile data') }}
                    </flux:button>
                </form>
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
