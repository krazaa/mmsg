<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="MMS Group property booking, installment, inventory and customer portal.">
    <title>MMS Group · Property made simple</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .hero-grid { background-image: linear-gradient(rgba(99,102,241,.08) 1px,transparent 1px),linear-gradient(90deg,rgba(99,102,241,.08) 1px,transparent 1px); background-size: 34px 34px; }
        .glass-ring { box-shadow: inset 0 1px 0 rgba(255,255,255,.12), 0 30px 80px rgba(2,6,23,.35); }
        @media (prefers-reduced-motion:no-preference) {
            .float-card { animation: float 5s ease-in-out infinite; }
            .pulse-dot { animation: pulse-dot 2s ease-in-out infinite; }
            @keyframes float { 50% { transform: translateY(-9px); } }
            @keyframes pulse-dot { 50% { box-shadow: 0 0 0 6px rgba(52,211,153,0); } }
        }
    </style>
</head>
<body class="bg-slate-950 font-sans text-slate-100 antialiased">
    <div class="relative overflow-hidden">
        <div class="pointer-events-none absolute inset-x-0 top-0 h-[720px] bg-[radial-gradient(circle_at_20%_10%,rgba(124,58,237,.32),transparent_36%),radial-gradient(circle_at_82%_20%,rgba(14,165,233,.2),transparent_30%)]"></div>

        <header class="sticky top-0 z-50 border-b border-white/10 bg-slate-950/80 backdrop-blur-xl">
            <nav class="mx-auto flex max-w-7xl items-center justify-between px-5 py-4 lg:px-8" aria-label="Main navigation">
                <a href="{{ url('/') }}" class="flex items-center gap-3">
                    <span class="grid h-11 w-11 place-items-center rounded-2xl bg-gradient-to-br from-violet-500 to-indigo-700 font-black text-white shadow-lg shadow-violet-950 ring-1 ring-white/20">M</span>
                    <span><b class="block text-base font-black tracking-tight">MMS Group</b><span class="block text-[10px] font-bold uppercase tracking-[.2em] text-indigo-300">Property platform</span></span>
                </a>
                <div class="hidden items-center gap-7 text-sm font-semibold text-slate-300 md:flex"><a href="#projects" class="hover:text-white">Projects</a><a href="#platform" class="hover:text-white">Platform</a><a href="#journey" class="hover:text-white">How it works</a><a href="#access" class="hover:text-white">Portal access</a></div>
                <div class="flex items-center gap-2">
                    @auth
                        <a href="{{ route('dashboard') }}" class="rounded-xl bg-white px-4 py-2.5 text-sm font-black text-slate-950 hover:bg-indigo-50">Open dashboard →</a>
                    @else
                        <a href="{{ route('login') }}" class="rounded-xl border border-white/15 bg-white/5 px-4 py-2.5 text-sm font-bold text-white hover:bg-white/10">Sign in</a>
                        <a href="{{ route('register') }}" class="hidden rounded-xl bg-white px-4 py-2.5 text-sm font-black text-slate-950 hover:bg-indigo-50 sm:inline-flex">Register</a>
                    @endauth
                </div>
            </nav>
        </header>

        <main>
            <section class="hero-grid relative">
                <div class="mx-auto grid min-h-[690px] max-w-7xl items-center gap-14 px-5 py-10 lg:grid-cols-[.9fr_1.1fr] lg:px-8 lg:py-12">
                    <div class="relative z-10">
                        <div class="inline-flex items-center gap-2 rounded-full border border-emerald-400/20 bg-emerald-400/10 px-3 py-1.5 text-xs font-bold text-emerald-300 shadow-lg shadow-emerald-950/20"><span class="pulse-dot h-2 w-2 rounded-full bg-emerald-400 shadow-[0_0_0_0_rgba(52,211,153,.5)]"></span> One secure property account</div>
                        <h1 class="mt-7 text-5xl font-black leading-[1.02] tracking-[-.045em] text-white sm:text-6xl lg:text-7xl">Your property journey, <span class="bg-gradient-to-r from-violet-400 via-indigo-300 to-sky-300 bg-clip-text text-transparent">clearly managed.</span></h1>
                        <p class="mt-7 max-w-xl text-lg leading-8 text-slate-300">Book a plot, follow every installment, submit payment proof and keep your verified receipts together—from first payment to final allotment.</p>
                        <div class="mt-9 flex flex-col gap-3 sm:flex-row">
                            <a href="{{ auth()->check() ? route('dashboard') : route('login') }}" class="rounded-xl bg-gradient-to-r from-violet-500 to-indigo-600 px-6 py-3.5 text-center font-black text-white shadow-xl shadow-indigo-950 transition hover:-translate-y-0.5 hover:shadow-indigo-900/50">{{ auth()->check() ? 'Go to my dashboard' : 'Access my property account' }} →</a>
                            @guest<a href="{{ route('register') }}" class="rounded-xl bg-white px-6 py-3.5 text-center font-black text-slate-950 hover:bg-indigo-50">Create new account</a>@endguest
                            <a href="#platform" class="rounded-xl border border-white/15 bg-white/5 px-6 py-3.5 text-center font-bold text-white hover:bg-white/10">Explore the platform</a>
                        </div>
                        <div class="mt-10 grid max-w-lg grid-cols-3 divide-x divide-white/10 border-y border-white/10 py-5"><div><b class="block text-xl text-white">24/7</b><span class="text-xs text-slate-400">Account access</span></div><div class="pl-5"><b class="block text-xl text-white">Live</b><span class="text-xs text-slate-400">Payment status</span></div><div class="pl-5"><b class="block text-xl text-white">Secure</b><span class="text-xs text-slate-400">Digital records</span></div></div>
                    </div>

                    @php
                        $heroProject = $projects->first();
                        $heroImage = $heroProject?->image_path
                            ? (str_starts_with($heroProject->image_path, 'projects/') ? asset('storage/'.$heroProject->image_path) : asset($heroProject->image_path))
                            : asset('images/projects/abdullah-town.jpg');
                        $heroSecondProject = $projects->skip(1)->first();
                        $heroSecondImage = $heroSecondProject?->image_path
                            ? (str_starts_with($heroSecondProject->image_path, 'projects/') ? asset('storage/'.$heroSecondProject->image_path) : asset($heroSecondProject->image_path))
                            : asset('images/projects/mms-guardian.jpg');
                    @endphp
                    <div class="relative mx-auto w-full max-w-2xl pb-8 sm:pl-8">
                        <div class="absolute -inset-12 rounded-full bg-gradient-to-br from-violet-500/20 to-sky-400/10 blur-3xl"></div>
                        <div class="glass-ring relative overflow-hidden rounded-[2.25rem] border border-white/15 bg-slate-900 p-2.5">
                            <div class="relative aspect-[4/5] overflow-hidden rounded-[1.8rem] sm:aspect-[5/4]">
                                <img src="{{ $heroImage }}" alt="{{ $heroProject?->name ?? 'MMS Group project' }}" class="h-full w-full object-cover">
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/10 to-transparent"></div>
                                <div class="absolute inset-x-0 bottom-0 p-6 sm:p-8">
                                    <div class="flex items-end justify-between gap-4">
                                        <div>
                                            <span class="rounded-full border border-white/20 bg-white/10 px-3 py-1.5 text-[10px] font-black uppercase tracking-[.18em] text-white backdrop-blur">Featured development</span>
                                            <h2 class="mt-4 text-3xl font-black text-white sm:text-4xl">{{ $heroProject?->name ?? 'MMS Group' }}</h2>
                                            <p class="mt-1 text-sm font-bold text-indigo-200">{{ $heroProject?->location ?? 'Premium property developments' }}</p>
                                        </div>
                                        <a href="#projects" class="grid h-12 w-12 shrink-0 place-items-center rounded-full bg-white text-xl font-black text-slate-950 shadow-xl transition hover:scale-105">↘</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @if($heroSecondProject)
                            <div class="float-card absolute -bottom-3 -right-2 w-44 overflow-hidden rounded-2xl border border-white/20 bg-slate-900 p-2 shadow-2xl backdrop-blur sm:-right-6 sm:w-52">
                                <img src="{{ $heroSecondImage }}" alt="{{ $heroSecondProject->name }}" class="aspect-[16/9] w-full rounded-xl object-cover">
                                <div class="flex items-center justify-between gap-2 px-2 pb-1 pt-2"><div><b class="block truncate text-xs text-white">{{ $heroSecondProject->name }}</b><span class="text-[10px] text-slate-400">{{ $heroSecondProject->location }}</span></div><span class="text-indigo-300">↗</span></div>
                            </div>
                        @endif
                        <div class="float-card absolute -left-2 top-8 rounded-2xl border border-white/15 bg-slate-950/80 p-3 shadow-2xl backdrop-blur sm:-left-5"><div class="flex items-center gap-2"><span class="grid h-9 w-9 place-items-center rounded-xl bg-emerald-400/15 text-emerald-300">✓</span><div><b class="block text-xs text-white">Trusted process</b><span class="text-[10px] text-slate-400">Clear. Secure. Verified.</span></div></div></div>
                    </div>
                </div>
            </section>

            <section class="relative border-y border-white/10 bg-white/[.035]">
                <div class="mx-auto grid max-w-7xl grid-cols-2 divide-x divide-y divide-white/10 px-5 sm:grid-cols-4 sm:divide-y-0 lg:px-8">
                    @foreach([['✓','Verified receipts'],['⌂','Organized inventory'],['↗','Live payment status'],['◇','Secure account access']] as $item)
                        <div class="flex items-center justify-center gap-2 px-3 py-5 text-center text-xs font-bold text-slate-300 sm:text-sm"><span class="text-indigo-300">{{ $item[0] }}</span>{{ $item[1] }}</div>
                    @endforeach
                </div>
            </section>

            <section id="projects" class="relative border-y border-white/10 bg-slate-950 py-12">
                <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_50%_0%,rgba(99,102,241,.18),transparent_42%)]"></div>
                <div class="relative mx-auto max-w-7xl px-5 lg:px-8">
                    <div class="flex flex-col justify-between gap-5 md:flex-row md:items-end">
                        <div class="max-w-2xl">
                            <span class="text-xs font-black uppercase tracking-[.2em] text-indigo-300">Our developments</span>
                            <h2 class="mt-3 whitespace-nowrap text-4xl font-black tracking-tight text-white sm:text-5xl">Find your place in an MMS Group project.</h2>
                            <p class="mt-4 text-lg leading-8 text-slate-400">Explore our growing portfolio of thoughtfully planned communities.</p>
                        </div>
                        <a href="{{ auth()->check() ? route('dashboard') : route('register') }}" class="inline-flex w-fit rounded-xl bg-white px-5 py-3 font-black text-slate-950 hover:bg-indigo-50">{{ auth()->check() ? 'View my properties' : 'Start your journey' }} →</a>
                    </div>

                    <div class="mt-12 grid gap-6 md:grid-cols-2">
                        @forelse($projects as $project)
                            @php
                                $initials = collect(explode(' ', $project->name))->map(fn ($word) => mb_substr($word, 0, 1))->take(3)->implode('');
                                $projectImage = $project->image_path
                                    ? (str_starts_with($project->image_path, 'projects/') ? asset('storage/'.$project->image_path) : asset($project->image_path))
                                    : null;
                                $blueprintImage = $project->blueprint_path
                                    ? (str_starts_with($project->blueprint_path, 'projects/') ? asset('storage/'.$project->blueprint_path) : asset($project->blueprint_path))
                                    : null;
                            @endphp
                            <article class="group overflow-hidden rounded-[2rem] border border-white/10 bg-white/[.06] shadow-2xl shadow-black/20 transition duration-500 hover:-translate-y-1 hover:border-indigo-300/30">
                                <div class="relative aspect-[16/10] overflow-hidden">
                                    @if($projectImage)
                                        <img src="{{ $projectImage }}" alt="{{ $project->name }} development view" class="h-full w-full object-cover transition duration-700 group-hover:scale-105" loading="lazy">
                                    @else
                                        <div class="grid h-full place-items-center bg-gradient-to-br from-indigo-700 via-violet-800 to-slate-950"><span class="text-7xl font-black text-white/15">{{ $initials }}</span></div>
                                    @endif
                                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/10 to-transparent"></div>
                                    <div class="absolute left-5 top-5 flex items-center gap-3">
                                        <span class="grid h-12 w-12 place-items-center rounded-2xl border border-white/20 bg-slate-950/60 text-xs font-black tracking-wide text-white backdrop-blur">{{ $initials }}</span>
                                        <span class="rounded-full border border-emerald-300/20 bg-slate-950/60 px-3 py-1.5 text-[10px] font-black uppercase tracking-widest text-emerald-200 backdrop-blur">Open for interest</span>
                                    </div>
                                    @if($blueprintImage)
                                        <button type="button" onclick="document.getElementById('blueprint-{{ $project->id }}').showModal()" class="absolute bottom-5 right-5 w-32 overflow-hidden rounded-xl border border-white/25 bg-slate-950/80 p-1.5 text-left shadow-xl backdrop-blur transition hover:-translate-y-1 sm:w-40" aria-label="View {{ $project->name }} masterplan">
                                            <img src="{{ $blueprintImage }}" alt="" class="aspect-[16/10] w-full rounded-lg object-cover">
                                            <span class="mt-1.5 block px-1 text-[10px] font-black uppercase tracking-widest text-white">View blueprint ↗</span>
                                        </button>
                                    @endif
                                </div>
                                <div class="p-7">
                                    <p class="text-xs font-bold uppercase tracking-[.18em] text-indigo-300">{{ $project->location }}</p>
                                    <h3 class="mt-2 text-3xl font-black tracking-tight text-white">{{ $project->name }}</h3>
                                    <p class="mt-3 max-w-lg leading-7 text-slate-400">{{ $project->description ?: number_format($project->gross_area_marla / 20, 0).' kanal planned development with secure digital booking and payment records.' }}</p>
                                    <div class="mt-6 flex flex-wrap gap-2 border-t border-white/10 pt-5">
                                        <span class="rounded-lg bg-white/[.06] px-3 py-2 text-xs font-bold text-slate-300"><b class="text-white">{{ number_format($project->gross_area_marla / 20) }}</b> kanal total</span>
                                        <span class="rounded-lg bg-white/[.06] px-3 py-2 text-xs font-bold text-slate-300"><b class="text-white">{{ number_format($project->saleable_area_marla / 20) }}</b> kanal saleable</span>
                                        @if($blueprintImage)<button type="button" onclick="document.getElementById('blueprint-{{ $project->id }}').showModal()" class="rounded-lg bg-indigo-500/15 px-3 py-2 text-xs font-black text-indigo-200 hover:bg-indigo-500/25">Explore masterplan →</button>@endif
                                    </div>
                                </div>

                                @if($blueprintImage)
                                    <dialog id="blueprint-{{ $project->id }}" class="m-auto w-[min(94vw,1100px)] rounded-[2rem] border border-white/15 bg-slate-950 p-0 text-white shadow-2xl backdrop:bg-slate-950/85 backdrop:backdrop-blur-sm">
                                        <div class="flex items-center justify-between border-b border-white/10 px-5 py-4">
                                            <div><p class="text-[10px] font-black uppercase tracking-widest text-indigo-300">Project blueprint</p><h4 class="mt-1 text-xl font-black">{{ $project->name }} masterplan</h4></div>
                                            <button type="button" onclick="this.closest('dialog').close()" class="grid h-10 w-10 place-items-center rounded-full border border-white/15 bg-white/5 text-xl hover:bg-white/10" aria-label="Close blueprint">×</button>
                                        </div>
                                        <img src="{{ $blueprintImage }}" alt="{{ $project->name }} conceptual masterplan blueprint" class="block max-h-[78vh] w-full object-contain">
                                        <p class="px-5 py-3 text-xs text-slate-500">Conceptual project visualization for presentation purposes.</p>
                                    </dialog>
                                @endif
                            </article>
                        @empty
                            <div class="rounded-2xl border border-dashed border-white/15 p-10 text-center text-slate-400 md:col-span-2">New projects are coming soon.</div>
                        @endforelse
                    </div>
                </div>
            </section>

            <section id="platform" class="bg-slate-50 py-12 text-slate-900">
                <div class="mx-auto max-w-7xl px-5 lg:px-8">
                    <div class="flex flex-col justify-between gap-5 md:flex-row md:items-end"><div class="max-w-2xl"><span class="text-xs font-black uppercase tracking-[.2em] text-indigo-600">Everything connected</span><h2 class="mt-3 whitespace-nowrap text-4xl font-black tracking-tight sm:text-5xl">One platform. Every property milestone.</h2><p class="mt-4 text-lg text-slate-600">Clear records for customers and complete operational control for the MMS Group team.</p></div><span class="w-fit rounded-full border border-indigo-200 bg-indigo-50 px-4 py-2 text-xs font-black uppercase tracking-wider text-indigo-700">Built for clarity</span></div>
                    <div class="mt-12 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                        @foreach([
                            ['01','Plot booking','Choose a package and submit your booking through a guided property account.','bg-violet-50 text-violet-700'],
                            ['02','Installment tracking','See due dates, paid amounts and remaining balances without paperwork.','bg-sky-50 text-sky-700'],
                            ['03','Payment receipts','Upload proof, follow verification and print a permanent digital receipt.','bg-emerald-50 text-emerald-700'],
                            ['04','Plot allotment & inventory','Track blocks, plot numbers, sizes and live availability through to final allotment.','bg-amber-50 text-amber-700'],
                            ['05','Referral network','Use a unique referral code and follow a transparent three-level network.','bg-fuchsia-50 text-fuchsia-700'],
                            ['06','Secure management','Bookings, payments, projects and commissions stay organized in one system.','bg-slate-100 text-slate-700']
                        ] as $feature)
                            <article class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition duration-300 hover:-translate-y-1 hover:border-indigo-200 hover:shadow-xl"><span class="absolute -right-5 -top-8 text-8xl font-black text-slate-50 transition group-hover:text-indigo-50">{{ $feature[0] }}</span><span class="relative grid h-11 w-11 place-items-center rounded-xl text-sm font-black {{ $feature[3] }}">{{ $feature[0] }}</span><h3 class="relative mt-5 text-lg font-black">{{ $feature[1] }}</h3><p class="relative mt-2 leading-7 text-slate-600">{{ $feature[2] }}</p></article>
                        @endforeach
                    </div>
                </div>
            </section>

            <section id="journey" class="relative overflow-hidden bg-white py-12 text-slate-900">
                <div class="absolute right-0 top-0 h-96 w-96 rounded-full bg-indigo-100/60 blur-3xl"></div>
                <div class="relative mx-auto grid max-w-7xl gap-14 px-5 lg:grid-cols-[.8fr_1.2fr] lg:px-8">
                    <div><span class="text-xs font-black uppercase tracking-[.2em] text-indigo-600">Simple by design</span><h2 class="mt-3 text-4xl font-black tracking-tight">From booking to ownership.</h2><p class="mt-5 leading-7 text-slate-600">Your dashboard keeps the next action obvious while maintaining a complete record of everything already completed.</p><a href="{{ auth()->check() ? route('dashboard') : route('login') }}" class="mt-7 inline-flex rounded-xl bg-slate-950 px-5 py-3 font-black text-white">Open your account →</a></div>
                    <ol class="grid gap-4 sm:grid-cols-2">
                        @foreach([['Book your plot','Select an available package and submit your details.'],['Verify first payment','Upload payment proof for office review.'],['Follow installments','Pay against each scheduled month and track progress.'],['Receive allotment','Your assigned block and plot remain connected to the booking.']] as $index=>$step)
                            <li class="group rounded-2xl border border-slate-200 bg-slate-50 p-6 transition hover:border-indigo-200 hover:bg-white hover:shadow-lg"><div class="flex items-center justify-between"><span class="text-xs font-black text-indigo-600">STEP 0{{ $index+1 }}</span><span class="grid h-7 w-7 place-items-center rounded-full bg-indigo-100 text-xs text-indigo-700 transition group-hover:bg-indigo-600 group-hover:text-white">→</span></div><h3 class="mt-3 font-black">{{ $step[0] }}</h3><p class="mt-2 text-sm leading-6 text-slate-600">{{ $step[1] }}</p></li>
                        @endforeach
                    </ol>
                </div>
            </section>

            <section id="access" class="relative overflow-hidden bg-gradient-to-br from-indigo-700 via-violet-700 to-indigo-900 py-20">
                <div class="pointer-events-none absolute -left-24 -top-24 h-72 w-72 rounded-full border-[48px] border-white/5"></div><div class="pointer-events-none absolute -bottom-28 -right-16 h-80 w-80 rounded-full border-[56px] border-white/5"></div>
                <div class="mx-auto flex max-w-5xl flex-col items-center px-5 text-center"><span class="rounded-full bg-white/10 px-3 py-1.5 text-xs font-bold text-indigo-100">CUSTOMER · AGENT · MANAGEMENT</span><h2 class="mt-5 text-4xl font-black tracking-tight text-white sm:text-5xl">Your account is ready when you are.</h2><p class="mt-4 max-w-2xl text-lg text-indigo-100">Sign in to view the information and actions relevant to your role in the MMS Group property network.</p><div class="mt-8 flex flex-col gap-3 sm:flex-row"><a href="{{ auth()->check() ? route('dashboard') : route('login') }}" class="rounded-xl bg-white px-7 py-3.5 font-black text-indigo-800 shadow-xl hover:bg-indigo-50">{{ auth()->check() ? 'Continue to dashboard' : 'Sign in securely' }} →</a>@guest<a href="{{ route('register') }}" class="rounded-xl border border-white/30 px-7 py-3.5 font-black text-white hover:bg-white/10">Register as customer</a>@endguest</div></div>
            </section>
        </main>

        <footer class="border-t border-white/10 bg-slate-950">
            <div class="mx-auto flex max-w-7xl flex-col gap-4 px-5 py-8 text-sm text-slate-400 sm:flex-row sm:items-center sm:justify-between lg:px-8"><div class="flex items-center gap-2"><span class="grid h-8 w-8 place-items-center rounded-lg bg-indigo-600 text-xs font-black text-white">MMS</span><b class="text-slate-200">MMS Group</b></div><p>Property records, payments and allotments in one secure platform.</p><p>© {{ date('Y') }} MMS Group</p></div>
        </footer>
    </div>
</body>
</html>
