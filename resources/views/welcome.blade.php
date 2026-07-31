<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="MMS Group property booking, installment, inventory and customer portal.">
    <title>MMS Group · Property made simple</title>
    <link rel="icon" href="{{ asset('email-logo.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .hero-grid { background-image: linear-gradient(rgba(99,102,241,.08) 1px,transparent 1px),linear-gradient(90deg,rgba(99,102,241,.08) 1px,transparent 1px); background-size: 34px 34px; }
        .property-pattern {
            background-image:
                radial-gradient(circle at 2px 2px, rgba(56,189,248,.22) 2px, transparent 2.5px),
                repeating-linear-gradient(115deg, transparent 0 38px, rgba(129,140,248,.11) 39px 40px, transparent 41px 96px),
                repeating-linear-gradient(25deg, transparent 0 58px, rgba(99,102,241,.08) 59px 60px, transparent 61px 122px);
            background-position: 0 0, 0 0, 0 0;
            background-size: 48px 48px, 170px 120px, 210px 150px;
        }
        .glass-ring { box-shadow: inset 0 1px 0 rgba(255,255,255,.12), 0 30px 80px rgba(2,6,23,.35); }
        [data-reveal] { opacity: 0; transform: translateY(24px); transition: opacity .7s ease, transform .7s cubic-bezier(.2,.7,.2,1); transition-delay: var(--reveal-delay, 0ms); }
        [data-reveal][data-effect="left"] { transform: translateX(-28px); }
        [data-reveal][data-effect="right"] { transform: translateX(28px); }
        [data-reveal][data-effect="scale"] { transform: translateY(14px) scale(.96); }
        [data-reveal].is-visible { opacity: 1; transform: translateY(0); }
        #projects h2, #platform h2, #journey h2, #access h2 { font-size: var(--welcome-section-heading-size) !important; }
        .hero-primary-button { background: {{ $pageAppearance['welcome_hero_primary_button_background_color'] }}; color: {{ $pageAppearance['welcome_hero_primary_button_text_color'] }}; }
        .hero-primary-button:hover { background: {{ $pageAppearance['welcome_hero_primary_button_hover_color'] }}; }
        .hero-secondary-button { background: {{ $pageAppearance['welcome_hero_secondary_button_background_color'] }}; color: {{ $pageAppearance['welcome_hero_secondary_button_text_color'] }}; }
        .hero-secondary-button:hover { background: {{ $pageAppearance['welcome_hero_secondary_button_hover_color'] }}; }
        .hero-explore-button { background: {{ $pageAppearance['welcome_hero_explore_button_background_color'] }}; color: {{ $pageAppearance['welcome_hero_explore_button_text_color'] }}; }
        .hero-explore-button:hover { background: {{ $pageAppearance['welcome_hero_explore_button_hover_color'] }}; }
        .projects-cta-button { background: {{ $pageAppearance['welcome_projects_cta_background_color'] }}; color: {{ $pageAppearance['welcome_projects_cta_text_color'] }}; }
        .projects-cta-button:hover { background: {{ $pageAppearance['welcome_projects_cta_hover_color'] }}; }
        @media (prefers-reduced-motion:no-preference) {
            .float-card { animation: float 5s ease-in-out infinite; }
            .pulse-dot { animation: pulse-dot 2s ease-in-out infinite; }
            .hero-copy { animation: hero-in .8s cubic-bezier(.2,.7,.2,1) both; }
            .hero-visual { animation: hero-visual-in 1s .12s cubic-bezier(.2,.7,.2,1) both; }
            .ambient-glow { animation: glow-drift 9s ease-in-out infinite alternate; }
            .property-pattern { animation: property-pattern-drift 28s linear infinite; }
            @keyframes float { 50% { transform: translateY(-9px); } }
            @keyframes pulse-dot { 50% { box-shadow: 0 0 0 6px rgba(52,211,153,0); } }
            @keyframes hero-in { from { opacity: 0; transform: translateY(22px); } to { opacity: 1; transform: translateY(0); } }
            @keyframes hero-visual-in { from { opacity: 0; transform: translateX(28px) scale(.97); } to { opacity: 1; transform: translateX(0) scale(1); } }
            @keyframes glow-drift { from { transform: translate3d(-2%,0,0) scale(.96); } to { transform: translate3d(2%,3%,0) scale(1.04); } }
            @keyframes property-pattern-drift {
                from { background-position: 0 0, 0 0, 0 0; }
                to { background-position: 48px 48px, 170px 120px, -210px 150px; }
            }
        }
        @media (prefers-reduced-motion:reduce) {
            [data-reveal] { opacity: 1; transform: none; transition: none; }
        }
    </style>
</head>
<body class="font-sans text-slate-100 antialiased" style="background-color:{{ $backgroundColor }};--welcome-section-heading-size:{{ $pageAppearance['welcome_section_heading_font_size'] }}px">
    <div class="relative overflow-hidden">
        <div class="ambient-glow pointer-events-none absolute inset-x-0 top-0 h-[720px]" style="background-image:radial-gradient(circle at 20% 10%,color-mix(in srgb, {{ $pageAppearance['welcome_hero_blur_primary_color'] }} 32%, transparent),transparent 36%),radial-gradient(circle at 82% 20%,color-mix(in srgb, {{ $pageAppearance['welcome_hero_blur_secondary_color'] }} 20%, transparent),transparent 30%)"></div>

        <header class="hero-copy sticky top-0 z-50 border-b border-white/10 backdrop-blur-xl" style="background-color: {{ $pageAppearance['welcome_header_background_color'] }}e6">
            <nav class="mx-auto flex max-w-7xl items-center justify-between px-5 py-4 lg:px-8" aria-label="Main navigation">
                <a href="{{ url('/') }}" class="flex items-center gap-3">
                    <span class="grid h-11 w-18 flex-none place-items-center overflow-hidden rounded-xl p-1"><img src="{{ asset('email-logo.png') }}" width="72" height="48" alt="MMS Group logo" class="h-full w-full object-contain"></span>
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
            <section class="hero-grid relative" style="background-color: {{ $heroGridBackgroundColor }};background-image:linear-gradient(rgba(99,102,241,.08) 1px,transparent 1px),linear-gradient(90deg,rgba(99,102,241,.08) 1px,transparent 1px);background-size:34px 34px,34px 34px">
                <div class="mx-auto grid min-h-[690px] max-w-7xl items-center gap-14 px-5 py-10 lg:grid-cols-[.9fr_1.1fr] lg:items-start lg:px-8 lg:py-12">
                    <div class="hero-copy relative z-10">
                        <div class="inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-xs font-bold shadow-lg" style="background-color:{{ $pageAppearance['welcome_hero_badge_background_color'] }};color:{{ $pageAppearance['welcome_hero_badge_text_color'] }};border-color:{{ $pageAppearance['welcome_hero_badge_border_color'] }}"><span class="pulse-dot h-2 w-2 rounded-full" style="background-color:{{ $pageAppearance['welcome_hero_badge_border_color'] }}"></span> One secure property account</div>
                        <h1 class="mt-7 font-black leading-[1.02] tracking-[-.045em]" style="color: {{ $heroHeadingColor }};font-size:clamp(3rem,6vw,{{ $pageAppearance['welcome_hero_heading_font_size'] }}px)">Your property journey, <span class="bg-gradient-to-r from-violet-400 via-indigo-300 to-sky-300 bg-clip-text text-transparent">clearly managed.</span></h1>
                        <p class="mt-7 max-w-xl leading-8" style="color: {{ $pageAppearance['welcome_hero_body_color'] }};font-size:{{ $pageAppearance['welcome_hero_body_font_size'] }}px">Book a plot, follow every installment, submit payment proof and keep your verified receipts together—from first payment to final allotment.</p>
                        <div class="mt-9 flex flex-col gap-3 sm:flex-row">
                            <a href="{{ auth()->check() ? route('dashboard') : route('login') }}" class="hero-primary-button rounded-xl px-6 py-3.5 text-center font-black shadow-xl shadow-indigo-950 transition hover:-translate-y-0.5 hover:shadow-indigo-900/50">{{ auth()->check() ? 'Go to my dashboard' : 'Access my property account' }} →</a>
                            @guest<a href="{{ route('register') }}" class="hero-secondary-button rounded-xl px-6 py-3.5 text-center font-black transition">Create new account</a>@endguest
                            <a href="#platform" class="hero-explore-button rounded-xl border border-white/15 px-6 py-3.5 text-center font-bold transition">Explore the platform</a>
                        </div>
                        <div class="relative mt-10 grid max-w-lg grid-cols-3 gap-3">
                            <div class="rounded-xl border border-white/15 bg-slate-950/80 px-4 py-4 shadow-lg shadow-slate-950/20 backdrop-blur-sm">
                                <b class="block text-xl" style="color: {{ $heroStatValueColor }}">24/7</b>
                                <span class="mt-1 block text-xs" style="color: {{ $heroStatLabelColor }}">Account access</span>
                            </div>
                            <div class="rounded-xl border border-white/15 bg-slate-950/80 px-4 py-4 shadow-lg shadow-slate-950/20 backdrop-blur-sm">
                                <b class="block text-xl" style="color: {{ $heroStatValueColor }}">Live</b>
                                <span class="mt-1 block text-xs" style="color: {{ $heroStatLabelColor }}">Payment status</span>
                            </div>
                            <div class="rounded-xl border border-white/15 bg-slate-950/80 px-4 py-4 shadow-lg shadow-slate-950/20 backdrop-blur-sm">
                                <b class="block text-xl" style="color: {{ $heroStatValueColor }}">Secure</b>
                                <span class="mt-1 block text-xs" style="color: {{ $heroStatLabelColor }}">Digital records</span>
                            </div>
                            @if($offerPackage)
                                <a href="#projects" class="group relative col-span-3 min-h-[84px] overflow-hidden rounded-xl border border-amber-300/30 bg-gradient-to-r from-amber-500 via-orange-500 to-orange-600 px-4 py-2 text-left text-white shadow-lg shadow-orange-950/20 transition hover:-translate-y-0.5 lg:absolute lg:left-[calc(100%+3.5rem)] lg:top-0 lg:h-full lg:w-96">
                                    <span class="absolute -right-8 -top-12 h-28 w-28 rounded-full bg-white/15"></span>
                                    <span class="relative block min-w-0">
                                        <span class="block text-[10px] font-black uppercase tracking-[.14em] text-amber-100">Special offer</span>
                                        <b class="block truncate text-xl leading-6">{{ $offerPackage->welcome_offer }}</b>
                                        <span class="mt-1 block truncate text-xs text-amber-50">{{ $offerPackage->project->name }} · {{ $offerPackage->name }}</span>
                                    </span>
                                </a>
                            @endif
                        </div>
                    </div>

                    @php
                        $heroProject = $projects->first();
                        $heroImage = $heroProject?->image_url;
                        $heroSecondProject = $projects->skip(1)->first();
                        $heroSecondImage = $heroSecondProject?->image_url;
                        $heroThirdProject = $projects->skip(2)->first();
                    @endphp
                    <div class="hero-visual relative mx-auto w-full max-w-2xl pb-8 sm:pl-8">
                        <div class="absolute -inset-12 rounded-full blur-3xl" style="background-image:radial-gradient(circle at 35% 35%,color-mix(in srgb, {{ $pageAppearance['welcome_hero_blur_primary_color'] }} 24%, transparent),transparent 65%),radial-gradient(circle at 70% 65%,color-mix(in srgb, {{ $pageAppearance['welcome_hero_blur_secondary_color'] }} 18%, transparent),transparent 65%)"></div>
                        <div class="glass-ring relative overflow-hidden rounded-[2.25rem] border p-2.5" style="border-color:{{ $pageAppearance['welcome_hero_image_border_color'] }};background:linear-gradient(135deg,{{ $pageAppearance['welcome_hero_image_gradient_start_color'] }},{{ $pageAppearance['welcome_hero_image_gradient_end_color'] }})">
                            <div class="relative aspect-[4/5] overflow-hidden rounded-[1.8rem] sm:aspect-[5/4]">
                                @if($heroImage)
                                    <img src="{{ $heroImage }}" width="800" height="640" alt="{{ $heroProject?->name ?? 'MMS Group project' }}" class="h-full w-full object-cover" fetchpriority="high" decoding="async">
                                @else
                                    <div class="h-full w-full bg-gradient-to-br from-indigo-700 via-violet-800 to-slate-950"></div>
                                @endif
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
                        @if($heroSecondProject && $heroSecondImage)
                            <div class="float-card absolute -bottom-3 -right-2 w-44 overflow-hidden rounded-2xl border border-white/20 bg-slate-900 p-2 shadow-2xl backdrop-blur sm:-right-6 sm:w-52">
                                <img src="{{ $heroSecondImage }}" width="416" height="234" alt="{{ $heroSecondProject->name }}" class="aspect-[16/9] w-full rounded-xl object-cover" loading="lazy" decoding="async">
                                <div class="flex items-center justify-between gap-2 px-2 pb-1 pt-2"><div><b class="block truncate text-xs text-white">{{ $heroSecondProject->name }}</b><span class="text-[10px] text-slate-400">{{ $heroSecondProject->location }}</span></div><span class="text-indigo-300">↗</span></div>
                            </div>
                        @endif
                        @if($heroThirdProject)
                            <a href="#projects" class="float-card absolute -right-2 top-8 z-10 hidden w-52 items-center gap-3 rounded-2xl border border-white/15 bg-slate-950/90 p-3 shadow-2xl backdrop-blur transition hover:-translate-y-0.5 hover:border-indigo-300/40 sm:flex sm:-right-6">
                                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-indigo-400/15 text-[10px] font-black text-indigo-300">03</span>
                                <span class="min-w-0">
                                    <b class="block truncate text-xs text-white">{{ $heroThirdProject->name }}</b>
                                    <span class="mt-0.5 block truncate text-[10px] text-slate-400">{{ $heroThirdProject->location ?: 'View development' }}</span>
                                </span>
                                <span class="ml-auto text-indigo-300">↗</span>
                            </a>
                        @endif
                        <div class="float-card absolute -left-2 top-8 rounded-2xl border border-white/15 bg-slate-950/80 p-3 shadow-2xl backdrop-blur sm:-left-5"><div class="flex items-center gap-2"><span class="grid h-9 w-9 place-items-center rounded-xl bg-emerald-400/15 text-emerald-300">✓</span><div><b class="block text-xs text-white">Trusted process</b><span class="text-[10px] text-slate-400">Clear. Secure. Verified.</span></div></div></div>
                    </div>
                </div>
            </section>

            <section class="relative border-y border-slate-800 bg-slate-950">
                <div class="mx-auto grid max-w-7xl grid-cols-2 divide-x divide-y divide-slate-800 px-5 sm:grid-cols-4 sm:divide-y-0 lg:px-8">
                    @foreach([['✓','Verified receipts'],['⌂','Organized inventory'],['↗','Live payment status'],['◇','Secure account access']] as $item)
                        <div class="flex items-center justify-center gap-2 px-3 py-5 text-center text-xs font-bold text-slate-300 sm:text-sm" data-reveal data-effect="scale" style="--reveal-delay: {{ $loop->index * 80 }}ms"><span class="text-white">{{ $item[0] }}</span>{{ $item[1] }}</div>
                    @endforeach
                </div>
            </section>

            <section id="projects" class="property-pattern relative border-y border-white/10 py-10 sm:py-12 lg:py-16" style="background-color: {{ $pageAppearance['welcome_projects_background_color'] }};color:{{ $pageAppearance['welcome_projects_text_color'] }};font-size:{{ $pageAppearance['welcome_body_font_size'] }}px">
                <div class="pointer-events-none absolute inset-0" style="background-image:radial-gradient(circle at 50% 0%,color-mix(in srgb, {{ $pageAppearance['welcome_projects_blur_color'] }} 24%, transparent),transparent 42%)"></div>
                <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex flex-col justify-between gap-6 lg:flex-row lg:items-end" data-reveal data-effect="left">
                        <div class="min-w-0 lg:flex-1">
                            <span class="text-xs font-black uppercase tracking-[.2em]" style="color:{{ $pageAppearance['welcome_projects_eyebrow_color'] }}">Our developments</span>
                            <h2 class="mt-3 font-black tracking-tight xl:whitespace-nowrap" style="color:{{ $pageAppearance['welcome_projects_heading_color'] }};font-size:{{ $pageAppearance['welcome_projects_heading_font_size'] }}px !important">Find your place in an MMS Group project.</h2>
                            <p class="mt-4 max-w-3xl text-base leading-7 text-slate-400 sm:text-lg sm:leading-8">Explore our growing portfolio of thoughtfully planned communities.</p>
                        </div>
                        <a href="{{ auth()->check() ? route('dashboard') : route('register') }}" class="projects-cta-button inline-flex w-full items-center justify-center rounded-xl px-5 py-3 font-black transition sm:w-fit lg:shrink-0">{{ auth()->check() ? 'View my properties' : 'Start your journey' }} →</a>
                    </div>

                    <div class="mt-12 grid gap-6 md:grid-cols-2">
                        @forelse($projects as $project)
                            @php
                                $initials = collect(explode(' ', $project->name))->map(fn ($word) => mb_substr($word, 0, 1))->take(3)->implode('');
                                $projectImage = $project->image_url;
                                $blueprintImage = $project->blueprint_url;
                            @endphp
                            <article class="group overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-2xl shadow-black/20 transition duration-500 hover:-translate-y-1 hover:border-indigo-300" data-reveal data-effect="scale" style="--reveal-delay: {{ $loop->index * 100 }}ms">
                                <div class="relative aspect-[16/10] overflow-hidden">
                                    @if($projectImage)
                                        <img src="{{ $projectImage }}" width="640" height="400" alt="{{ $project->name }} development view" class="h-full w-full object-cover transition duration-700 group-hover:scale-105" loading="lazy" decoding="async">
                                    @else
                                        <div class="grid h-full place-items-center bg-gradient-to-br from-indigo-700 via-violet-800 to-slate-950"><span class="text-7xl font-black text-white/15">{{ $initials }}</span></div>
                                    @endif
                                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/10 to-transparent"></div>
                                    <div class="absolute left-5 top-5 flex items-center gap-3">
                                        <span class="grid h-12 w-12 place-items-center rounded-2xl border border-white/20 text-xs font-black tracking-wide backdrop-blur" style="background-color:{{ $pageAppearance['welcome_projects_initials_background_color'] }};color:{{ $pageAppearance['welcome_projects_initials_text_color'] }}">{{ $initials }}</span>
                                        <span class="rounded-full border border-emerald-300/20 px-3 py-1.5 text-[10px] font-black uppercase tracking-widest backdrop-blur" style="background-color:{{ $pageAppearance['welcome_projects_status_background_color'] }};color:{{ $pageAppearance['welcome_projects_status_text_color'] }}">Open for interest</span>
                                    </div>
                                    @if($blueprintImage)
                                        <button type="button" onclick="document.getElementById('blueprint-{{ $project->id }}').showModal()" class="absolute bottom-5 right-5 w-32 overflow-hidden rounded-xl border border-white/25 bg-slate-950/80 p-1.5 text-left shadow-xl backdrop-blur transition hover:-translate-y-1 sm:w-40" aria-label="View {{ $project->name }} masterplan">
                                            <img src="{{ $blueprintImage }}" alt="" class="aspect-[16/10] w-full rounded-lg object-cover" loading="lazy" decoding="async">
                                            <span class="mt-1.5 block px-1 text-[10px] font-black uppercase tracking-widest text-white">View blueprint ↗</span>
                                        </button>
                                    @endif
                                </div>
                                <div class="p-7">
                                    <p class="text-xs font-bold uppercase tracking-[.18em]" style="color:{{ $pageAppearance['welcome_projects_location_color'] }}">{{ $project->location }}</p>
                                    <h3 class="mt-2 font-black tracking-tight" style="color:{{ $pageAppearance['welcome_projects_card_heading_color'] }};font-size:{{ $pageAppearance['welcome_projects_card_heading_font_size'] }}px">{{ $project->name }}</h3>
                                    <p class="mt-3 max-w-lg leading-7 text-slate-600">{{ $project->description ?: number_format($project->gross_area_marla / 20, 0).' kanal planned development with secure digital booking and payment records.' }}</p>
                                    <div class="mt-6 flex flex-wrap gap-2 border-t border-slate-200 pt-5">
                                        <span class="rounded-lg px-3 py-2 font-bold" style="background-color:{{ $pageAppearance['welcome_projects_stat_background_color'] }};color:{{ $pageAppearance['welcome_projects_stat_label_color'] }};font-size:{{ $pageAppearance['welcome_projects_stat_font_size'] }}px"><b style="color:{{ $pageAppearance['welcome_projects_stat_value_color'] }}">{{ number_format($project->gross_area_marla / 20) }}</b> kanal total</span>
                                        <span class="rounded-lg px-3 py-2 font-bold" style="background-color:{{ $pageAppearance['welcome_projects_stat_background_color'] }};color:{{ $pageAppearance['welcome_projects_stat_label_color'] }};font-size:{{ $pageAppearance['welcome_projects_stat_font_size'] }}px"><b style="color:{{ $pageAppearance['welcome_projects_stat_value_color'] }}">{{ number_format($project->saleable_area_marla / 20) }}</b> kanal saleable</span>
                                        @if($blueprintImage)<button type="button" onclick="document.getElementById('blueprint-{{ $project->id }}').showModal()" class="rounded-lg px-3 py-2 font-black" style="background-color:{{ $pageAppearance['welcome_projects_button_background_color'] }};color:{{ $pageAppearance['welcome_projects_button_text_color'] }};font-size:{{ $pageAppearance['welcome_projects_button_font_size'] }}px">Masterplan →</button>@endif
                                    </div>
                                </div>

                                @if($blueprintImage)
                                    <dialog id="blueprint-{{ $project->id }}" class="m-auto w-[min(94vw,1100px)] rounded-[2rem] border border-white/15 bg-slate-950 p-0 text-white shadow-2xl backdrop:bg-slate-950/85 backdrop:backdrop-blur-sm">
                                        <div class="flex items-center justify-between border-b border-white/10 px-5 py-4">
                                            <div><p class="text-[10px] font-black uppercase tracking-widest text-indigo-300">Project blueprint</p><h4 class="mt-1 text-xl font-black">{{ $project->name }} masterplan</h4></div>
                                            <button type="button" onclick="this.closest('dialog').close()" class="grid h-10 w-10 place-items-center rounded-full border border-white/15 bg-white/5 text-xl hover:bg-white/10" aria-label="Close blueprint">×</button>
                                        </div>
                                        <img src="{{ $blueprintImage }}" alt="{{ $project->name }} conceptual masterplan blueprint" class="block max-h-[78vh] w-full object-contain" loading="lazy" decoding="async">
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

            <section id="platform" class="py-10 sm:py-12 lg:py-16" style="background-color:{{ $pageAppearance['welcome_platform_background_color'] }};color:{{ $pageAppearance['welcome_platform_text_color'] }};font-size:{{ $pageAppearance['welcome_body_font_size'] }}px">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex flex-col justify-between gap-6 lg:flex-row lg:items-end" data-reveal data-effect="right">
                        <div class="min-w-0 lg:flex-1">
                            <span class="text-xs font-black uppercase tracking-[.2em] text-indigo-600">Everything connected</span>
                            <h2 class="mt-3 text-3xl font-black tracking-tight sm:text-4xl lg:text-5xl xl:whitespace-nowrap">One platform. Every property milestone.</h2>
                            <p class="mt-4 max-w-3xl text-base leading-7 text-slate-600 sm:text-lg sm:leading-8">Clear records for customers and complete operational control for the MMS Group team.</p>
                        </div>
                        <span class="w-fit shrink-0 rounded-full border border-slate-700 bg-slate-950 px-4 py-2 text-xs font-black uppercase tracking-wider text-white shadow-sm">Built for clarity</span>
                    </div>
                    <div class="mt-12 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                        @foreach([
                            ['01','Plot booking','Choose a package and submit your booking through a guided property account.','bg-violet-50 text-violet-700'],
                            ['02','Installment tracking','See due dates, paid amounts and remaining balances without paperwork.','bg-sky-50 text-sky-700'],
                            ['03','Payment receipts','Upload proof, follow verification and print a permanent digital receipt.','bg-emerald-50 text-emerald-700'],
                            ['04','Plot allotment & inventory','Track blocks, plot numbers, sizes and live availability through to final allotment.','bg-amber-50 text-amber-700'],
                            ['05','Referral network','Use a unique referral code and follow a transparent three-level network.','bg-fuchsia-50 text-fuchsia-700'],
                            ['06','Secure management','Bookings, payments, projects and commissions stay organized in one system.','bg-slate-100 text-slate-700']
                        ] as $feature)
                            <article class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition duration-300 hover:-translate-y-1 hover:border-indigo-200 hover:shadow-xl" data-reveal data-effect="scale" style="--reveal-delay: {{ ($loop->index % 3) * 90 }}ms"><span class="absolute -right-5 -top-8 text-8xl font-black text-slate-50 transition group-hover:text-indigo-50">{{ $feature[0] }}</span><span class="relative grid h-11 w-11 place-items-center rounded-xl text-sm font-black {{ $feature[3] }}">{{ $feature[0] }}</span><h3 class="relative mt-5 text-lg font-black">{{ $feature[1] }}</h3><p class="relative mt-2 leading-7 text-slate-600">{{ $feature[2] }}</p></article>
                        @endforeach
                    </div>
                </div>
            </section>

            <section id="journey" class="relative overflow-hidden py-12" style="background-color:{{ $pageAppearance['welcome_journey_background_color'] }};color:{{ $pageAppearance['welcome_journey_text_color'] }};font-size:{{ $pageAppearance['welcome_body_font_size'] }}px">
                <div class="absolute right-0 top-0 h-96 w-96 rounded-full bg-indigo-100/60 blur-3xl"></div>
                <div class="relative mx-auto grid max-w-7xl gap-14 px-5 lg:grid-cols-[.8fr_1.2fr] lg:px-8">
                    <div data-reveal data-effect="left"><span class="text-xs font-black uppercase tracking-[.2em] text-indigo-600">Simple by design</span><h2 class="mt-3 text-4xl font-black tracking-tight">From booking to ownership.</h2><p class="mt-5 leading-7 text-slate-600">Your dashboard keeps the next action obvious while maintaining a complete record of everything already completed.</p><a href="{{ auth()->check() ? route('dashboard') : route('login') }}" class="mt-7 inline-flex rounded-xl bg-slate-950 px-5 py-3 font-black text-white">Open your account →</a></div>
                    <ol class="grid gap-4 sm:grid-cols-2">
                        @foreach([['Book your plot','Select an available package and submit your details.'],['Verify first payment','Upload payment proof for office review.'],['Follow installments','Pay against each scheduled month and track progress.'],['Receive allotment','Your assigned block and plot remain connected to the booking.']] as $index=>$step)
                            <li class="group rounded-2xl border border-slate-200 bg-slate-50 p-6 transition hover:border-indigo-200 hover:bg-white hover:shadow-lg" data-reveal data-effect="right" style="--reveal-delay: {{ ($index % 2) * 100 }}ms"><div class="flex items-center justify-between"><span class="text-xs font-black text-indigo-600">STEP 0{{ $index+1 }}</span><span class="grid h-7 w-7 place-items-center rounded-full bg-indigo-100 text-xs text-indigo-700 transition group-hover:bg-indigo-600 group-hover:text-white">→</span></div><h3 class="mt-3 font-black">{{ $step[0] }}</h3><p class="mt-2 text-sm leading-6 text-slate-600">{{ $step[1] }}</p></li>
                        @endforeach
                    </ol>
                </div>
            </section>

            <section id="access" class="relative overflow-hidden py-20" style="background-color:{{ $pageAppearance['welcome_cta_background_color'] }};color:{{ $pageAppearance['welcome_cta_text_color'] }};font-size:{{ $pageAppearance['welcome_body_font_size'] }}px">
                <div class="pointer-events-none absolute -left-24 -top-24 h-72 w-72 rounded-full border-[48px] border-white/5"></div><div class="pointer-events-none absolute -bottom-28 -right-16 h-80 w-80 rounded-full border-[56px] border-white/5"></div>
                <div class="mx-auto flex max-w-5xl flex-col items-center px-5 text-center" data-reveal data-effect="scale"><span class="rounded-full bg-white/10 px-3 py-1.5 text-xs font-bold text-indigo-100">CUSTOMER · MANAGEMENT</span><h2 class="mt-5 text-4xl font-black tracking-tight text-white sm:text-5xl">Your account is ready when you are.</h2><p class="mt-4 max-w-2xl text-lg text-indigo-100">Sign in to view the information and actions relevant to your role in the MMS Group property network.</p><div class="mt-8 flex flex-col gap-3 sm:flex-row"><a href="{{ auth()->check() ? route('dashboard') : route('login') }}" class="rounded-xl bg-white px-7 py-3.5 font-black text-indigo-800 shadow-xl hover:bg-indigo-50">{{ auth()->check() ? 'Continue to dashboard' : 'Sign in securely' }} →</a>@guest<a href="{{ route('register') }}" class="rounded-xl border border-white/30 px-7 py-3.5 font-black text-white hover:bg-white/10">Register as customer</a>@endguest</div></div>
            </section>
        </main>

        <footer class="border-t border-white/10" style="background-color:{{ $pageAppearance['welcome_footer_background_color'] }};color:{{ $pageAppearance['welcome_footer_text_color'] }};font-size:{{ $pageAppearance['welcome_body_font_size'] }}px">
            <div class="mx-auto flex max-w-7xl flex-col gap-4 px-5 py-8 text-sm text-slate-400 sm:flex-row sm:items-center lg:px-8" data-reveal>
                <div class="order-1 flex items-center gap-2"><span class="grid h-11 w-18 flex-none place-items-center overflow-hidden rounded-lg p-1"><img src="{{ asset('email-logo.png') }}" width="72" height="48" alt="MMS Group logo" class="h-full w-full object-contain"></span><b class="text-slate-200"></b></div>
                <p class="order-3 sm:ml-auto">Property records, payments and allotments in one secure platform.</p>
                <p class="order-4">© {{ date('Y') }} MMS Group</p>
                @if(collect($socialLinks)->filter()->isNotEmpty())
                    <nav class="order-2 flex flex-wrap gap-2" aria-label="Social media">
                        @foreach($socialLinks as $platform => $url)
                            @if(filled($url))
                                @php
                                    $socialColor = match($platform) {
                                        'Facebook' => 'border-[#1877f2] bg-[#1877f2] hover:bg-[#0f67da]',
                                        'Instagram' => 'border-fuchsia-500 bg-gradient-to-br from-violet-600 via-fuchsia-500 to-orange-400',
                                        'YouTube' => 'border-[#ff0000] bg-[#ff0000] hover:bg-[#d90000]',
                                        'LinkedIn' => 'border-[#0a66c2] bg-[#0a66c2] hover:bg-[#084f96]',
                                        default => 'border-slate-700 bg-black hover:bg-slate-900',
                                    };
                                @endphp
                                <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" class="grid h-9 w-9 place-items-center rounded-full border text-white shadow-sm transition hover:-translate-y-0.5 hover:scale-105 {{ $socialColor }}" aria-label="{{ $platform }}" title="{{ $platform }}">
                                    <span class="grid place-items-center text-white" aria-hidden="true">
                                        @switch($platform)
                                            @case('Facebook')
                                                <svg viewBox="0 0 24 24" class="h-3.5 w-3.5 fill-current"><path d="M13.7 22v-8h2.7l.4-3.1h-3.1V9c0-.9.3-1.5 1.6-1.5H17V4.7c-.3 0-1.3-.1-2.5-.1-2.5 0-4.2 1.5-4.2 4.3v2H7.5V14h2.8v8h3.4Z"/></svg>
                                                @break
                                            @case('Instagram')
                                                <svg viewBox="0 0 24 24" class="h-3.5 w-3.5 fill-none stroke-current" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" class="fill-current stroke-none"/></svg>
                                                @break
                                            @case('YouTube')
                                                <svg viewBox="0 0 24 24" class="h-3.5 w-3.5 fill-current"><path d="M21.6 7.2a3 3 0 0 0-2.1-2.1C17.6 4.6 12 4.6 12 4.6s-5.6 0-7.5.5a3 3 0 0 0-2.1 2.1A31 31 0 0 0 2 12a31 31 0 0 0 .4 4.8 3 3 0 0 0 2.1 2.1c1.9.5 7.5.5 7.5.5s5.6 0 7.5-.5a3 3 0 0 0 2.1-2.1A31 31 0 0 0 22 12a31 31 0 0 0-.4-4.8ZM10 15.3V8.7l5.7 3.3-5.7 3.3Z"/></svg>
                                                @break
                                            @case('LinkedIn')
                                                <svg viewBox="0 0 24 24" class="h-3.5 w-3.5 fill-current"><path d="M6.5 8.4H3.2V21h3.3V8.4ZM4.9 3A1.9 1.9 0 1 0 5 6.8 1.9 1.9 0 0 0 4.9 3ZM21 13.8c0-3.8-2-5.6-4.7-5.6a4.1 4.1 0 0 0-3.7 2v-1.8H9.3V21h3.3v-6.2c0-1.6.3-3.2 2.3-3.2s2.1 1.8 2.1 3.3V21h3.3l.7-7.2Z"/></svg>
                                                @break
                                            @default
                                                <svg viewBox="0 0 24 24" class="h-3.5 w-3.5 fill-none stroke-current" stroke-width="2.2"><path d="m5 4 14 16M19 4 5 20"/></svg>
                                        @endswitch
                                    </span>
                                </a>
                            @endif
                        @endforeach
                    </nav>
                @endif
            </div>
        </footer>
    </div>
    <script>
        (() => {
            const items = document.querySelectorAll('[data-reveal]');
            if (!items.length || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                items.forEach(item => item.classList.add('is-visible'));
                return;
            }

            const observer = new IntersectionObserver(entries => {
                entries.forEach(entry => {
                    if (!entry.isIntersecting) return;
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                });
            }, { threshold: 0.12, rootMargin: '0px 0px -40px' });

            items.forEach(item => observer.observe(item));
        })();
    </script>
</body>
</html>
