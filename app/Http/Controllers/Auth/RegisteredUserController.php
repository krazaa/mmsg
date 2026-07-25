<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\CustomerWelcomeMail;
use App\Models\Customer;
use App\Models\Referral;
use App\Models\User;
use App\Support\Permissions;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        $firstNumber = random_int(1, 9);
        $secondNumber = random_int(1, 9);

        session(['registration_captcha_answer' => $firstNumber + $secondNumber]);

        return view('auth.register', [
            'captchaQuestion' => "$firstNumber + $secondNumber",
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $captchaAnswer = $request->session()->pull('registration_captcha_answer');

        if ($request->filled('referral_code')) {
            $request->merge(['referral_code' => Str::upper(trim($request->string('referral_code')->toString()))]);
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'father_name' => ['required', 'string', 'max:255'],
            'cnic' => ['required', 'string', 'max:15', 'unique:users,cnic'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone' => ['required', 'string', 'max:30'],
            'address' => ['required', 'string', 'max:200'],
            'referral_code' => [
                'nullable', 'string', 'max:30',
                Rule::exists('users', 'referral_code')->where(fn ($query) => $query->where('role', 'customer')->where('status', true)),
            ],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'captcha' => [
                'required',
                'integer',
                function (string $attribute, mixed $value, \Closure $fail) use ($captchaAnswer): void {
                    if ($captchaAnswer === null || (int) $value !== (int) $captchaAnswer) {
                        $fail('The security answer is incorrect. Please try the new question.');
                    }
                },
            ],
        ]);

        $sponsorId = $request->filled('referral_code')
            ? Customer::where('referral_code', $request->string('referral_code')->trim())->value('id')
            : User::where('email', 'direct-sales@mmsgroup.pk')->value('id');

        $user = DB::transaction(function () use ($request, $sponsorId) {
            foreach (Permissions::customer() as $permission) {
                Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
            }
            $customerRole = Role::firstOrCreate(['name' => 'customer', 'guard_name' => 'web']);
            $customerRole->syncPermissions(Permissions::customer());
            $customer = Customer::create([
                'name' => $request->name,
                'father_name' => $request->father_name,
                'cnic' => $request->cnic,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'referral_agent_id' => $sponsorId,
                'password' => Hash::make($request->password),
                'status' => true,
            ]);
            User::findOrFail($customer->id)->syncRoles($customerRole);
            Referral::updateOrCreate(['user_id' => $customer->id], ['sponsor_id' => $customer->referral_agent_id]);

            return $customer;
        });

        try {
            event(new Registered($user));
        } catch (\Throwable $exception) {
            report($exception);
        }

        try {
            Mail::to($user->email)->send(new CustomerWelcomeMail($user));
        } catch (\Throwable $exception) {
            report($exception);
        }

        Auth::login($user);

        return redirect()->route('verification.notice');
    }
}
