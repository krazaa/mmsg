<section>
    <header>
        <h2 class="text-lg font-black text-gray-900 dark:text-gray-100">
            {{ __('Personal Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('Keep your contact and identification details complete and up to date.') }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6">
        @csrf
        @method('patch')

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <x-input-label for="name" :value="__('Full name')" />
                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            </div>
            <div>
                <x-input-label for="father_name" :value="__('Father name')" />
                <x-text-input id="father_name" name="father_name" type="text" class="mt-1 block w-full" :value="old('father_name', $user->father_name)" autocomplete="off" />
                <x-input-error class="mt-2" :messages="$errors->get('father_name')" />
            </div>
            <div>
                <x-input-label for="phone" :value="__('Contact number')" />
                <x-text-input id="phone" name="phone" type="tel" class="mt-1 block w-full" :value="old('phone', $user->phone)" autocomplete="tel" />
                <x-input-error class="mt-2" :messages="$errors->get('phone')" />
            </div>
            <div>
                <x-input-label for="cnic" :value="__('CNIC')" />
                <x-text-input id="cnic" name="cnic" type="text" maxlength="15" placeholder="00000-0000000-0" class="mt-1 block w-full" :value="old('cnic', $user->cnic)" />
                <x-input-error class="mt-2" :messages="$errors->get('cnic')" />
            </div>
            <div class="sm:col-span-2">
                <x-input-label for="email" :value="__('Email address')" />
                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full {{ $user->role === 'customer' ? 'cursor-not-allowed bg-slate-100 text-slate-500 dark:bg-slate-800' : '' }}" :value="old('email', $user->email)" required autocomplete="username" :readonly="$user->role === 'customer'" />
                <x-input-error class="mt-2" :messages="$errors->get('email')" />
                @if($user->role === 'customer')
                    <p class="mt-1.5 flex items-center gap-1 text-xs font-semibold text-slate-400"><span>🔒</span> Contact the office to change your registered email address.</p>
                @endif

                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                    <p class="text-sm mt-2 text-gray-800 dark:text-gray-200">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600 dark:text-green-400">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                @endif
            </div>
            <div class="sm:col-span-2">
                <x-input-label for="address" :value="__('Complete address')" />
                <textarea id="address" name="address" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">{{ old('address', $user->address) }}</textarea>
                <x-input-error class="mt-2" :messages="$errors->get('address')" />
            </div>
        </div>

        <div class="mt-6 flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600 dark:text-gray-400"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
