@csrf
@if(isset($customer)) @method('PUT') @endif
<div class="grid gap-4 sm:grid-cols-2">
    @if(isset($customer))
        <label class="text-sm font-medium">Referral code<input value="{{ $customer->referral_code }}" readonly class="mt-1 w-full cursor-not-allowed rounded-md border-gray-300 bg-gray-100 font-mono text-gray-600"></label>
    @else
        <div class="rounded-lg border border-violet-200 bg-violet-50 p-4 text-sm text-violet-800"><div class="font-semibold">Referral code</div><div class="mt-1">A unique referral code will be generated automatically after registration.</div></div>
    @endif
    <label class="text-sm font-medium">File number <span class="text-xs font-normal text-gray-500">(optional)</span><input name="file_no" value="{{ old('file_no',$customer->file_no??'') }}" class="mt-1 w-full rounded-md border-gray-300 font-mono uppercase" placeholder="e.g. AT-000123"></label>
    <label class="text-sm font-medium">Name<input name="name" value="{{ old('name',$customer->name??'') }}" required class="mt-1 w-full rounded-md border-gray-300"></label>
    <label class="text-sm font-medium">Father name<input name="father_name" value="{{ old('father_name',$customer->father_name??'') }}" class="mt-1 w-full rounded-md border-gray-300"></label>
    <label class="text-sm font-medium">CNIC <span class="text-xs font-normal text-gray-500">(optional)</span><input name="cnic" value="{{ old('cnic',$customer->cnic??'') }}" class="mt-1 w-full rounded-md border-gray-300"></label>
    <label class="text-sm font-medium">Phone<input name="phone" value="{{ old('phone',$customer->phone??'') }}" required class="mt-1 w-full rounded-md border-gray-300"></label>
    <label class="text-sm font-medium">Email<input type="email" name="email" value="{{ old('email',$customer->email??'') }}" required class="mt-1 w-full rounded-md border-gray-300"></label>
    <label class="text-sm font-medium">{{ isset($customer) ? 'New password (optional)' : 'Login password' }}<input type="password" name="password" {{ isset($customer) ? '' : 'required' }} minlength="8" class="mt-1 w-full rounded-md border-gray-300" placeholder="{{ isset($customer) ? 'Leave blank to keep current password' : 'Minimum 8 characters' }}"></label>
    <label class="text-sm font-medium">Referred by referral code<input name="referred_by_code" value="{{ old('referred_by_code',isset($customer) ? $customer->referralAgent?->customer?->referral_code : '') }}" class="mt-1 w-full rounded-md border-gray-300 font-mono" placeholder="Leave blank for Direct Sales"><span class="mt-1 block text-xs font-normal text-gray-500">Enter the referral code of the customer who referred this customer.</span></label>
    <label class="flex items-center gap-2 self-end pb-3"><input type="hidden" name="status" value="0"><input type="checkbox" name="status" value="1" @checked(old('status',$customer->status??true))> Active</label>
</div>
<label class="mt-4 block text-sm font-medium">Address<textarea name="address" class="mt-1 w-full rounded-md border-gray-300">{{ old('address',$customer->address??'') }}</textarea></label>
<div class="mt-5 flex gap-3"><button class="rounded bg-indigo-600 px-5 py-2.5 font-semibold text-white">Save customer</button><a href="{{ route('customers.index') }}" class="rounded border px-5 py-2.5">Cancel</a></div>
