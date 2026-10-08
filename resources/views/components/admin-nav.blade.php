{{-- Onglets de la console d'administration. --}}
@php($newReports = \App\Models\BugReport::where('status', 'new')->count())
<nav class="mb-6 flex flex-wrap gap-2 border-b border-stone-200 pb-3 text-sm" aria-label="{{ __('Administration') }}">
    @foreach ([
        'admin.users' => __('Comptes'),
        'admin.plans' => __('Formules'),
        'admin.backlog' => __('Backlog'),
        'admin.recettes' => __('Recettes'),
    ] as $route => $label)
        <a href="{{ route($route) }}" @class(['rounded-md px-3 py-1.5 font-medium', 'bg-codex-soft text-codex' => request()->routeIs($route), 'text-stone-600 hover:bg-stone-100 hover:text-ink' => ! request()->routeIs($route)]) @if (request()->routeIs($route)) aria-current="page" @endif wire:navigate>
            {{ $label }}@if ($route === 'admin.backlog' && $newReports) <span class="ml-1 rounded-full bg-flow px-1.5 text-xs text-white">{{ $newReports }}</span>@endif
        </a>
    @endforeach
</nav>
