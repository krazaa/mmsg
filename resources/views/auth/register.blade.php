<x-guest-layout>
    <div class="mb-6 text-center"><h1 class="text-2xl font-black text-gray-900 dark:text-white">Create your property account</h1><p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Register to book plots and manage payments online.</p></div>
    <form method="POST" action="{{ route('register', array_filter(['ref' => $referralCode])) }}">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="father_name" :value="__('Father name')" />
            <x-text-input id="father_name" class="mt-1 block w-full" type="text" name="father_name" :value="old('father_name')" required autocomplete="off" />
            <x-input-error :messages="$errors->get('father_name')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="cnic" :value="__('CNIC')" />
            <x-text-input id="cnic" class="mt-1 block w-full" type="text" name="cnic" :value="old('cnic')" required maxlength="15" placeholder="12345-1234567-1" inputmode="numeric" />
            <x-input-error :messages="$errors->get('cnic')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="phone" :value="__('Phone number')" />
            <x-text-input id="phone" class="mt-1 block w-full" type="text" name="phone" :value="old('phone')" required autocomplete="tel" />
            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="referral_code" :value="__('Referral code (optional)')" />
            <x-text-input id="referral_code" class="mt-1 block w-full font-mono uppercase {{ $referralCode ? 'cursor-not-allowed bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400' : '' }}" type="text" name="referral_code" :value="old('referral_code', $referralCode)" maxlength="30" placeholder="Leave blank for Direct Sales" :readonly="filled($referralCode)" oninput="this.value=this.value.toUpperCase().replace(/\s+/g,'')" />
            @if($referralCode)
                <p class="mt-2 rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700 dark:border-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">Referral code {{ $referralCode }} was added from your invitation link.</p>
            @else
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">If entered, the code must belong to an active registered customer.</p>
            @endif
            <x-input-error :messages="$errors->get('referral_code')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="address" :value="__('Address')" />
            <textarea id="address" name="address" rows="3" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">{{ old('address') }}</textarea>
            <x-input-error :messages="$errors->get('address')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="captcha" :value="__('Security check: What is :question?', ['question' => $captchaQuestion])" />
            <x-text-input id="captcha" class="block mt-1 w-full" type="number" name="captcha"
                          required inputmode="numeric" autocomplete="off" />
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Answer this question to confirm you are human.</p>
            <x-input-error :messages="$errors->get('captcha')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
