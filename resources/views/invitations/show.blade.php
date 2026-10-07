<x-layouts.guest title="Invitation">
    @if ($problem)
        <p class="text-stone-700">{{ $problem }}</p>
        <a href="{{ url('/') }}" class="btn-secondary mt-6 w-full justify-center">Retour à l'accueil</a>
    @else
        @php($campaign = $invitation->campaign)
        <p class="text-stone-700">
            {{ $invitation->inviter?->name ?? 'Le MJ' }} vous invite à rejoindre la campagne
        </p>
        <p class="mt-1 text-xl font-semibold">{{ $campaign->name }}</p>
        <p class="mt-1 text-sm text-stone-600">{{ $campaign->gameSystem->name }} · en tant que {{ mb_strtolower($invitation->role->label()) }}</p>

        <div class="mt-6">
            @guest
                <p class="mb-4 text-sm text-stone-600">Pour accepter, connectez-vous ou créez votre compte : vous reviendrez ensuite sur cette page.</p>
                <div class="grid gap-3 sm:grid-cols-2">
                    <a href="{{ route('register', array_filter(['email' => $invitation->email])) }}" class="btn-primary justify-center">Créer un compte</a>
                    <a href="{{ route('login') }}" class="btn-secondary justify-center">Se connecter</a>
                </div>
            @else
                @if ($member)
                    <p class="mb-4 text-sm text-stone-600">Vous faites déjà partie de cette campagne ({{ mb_strtolower($member->label()) }}).</p>
                    <a href="{{ route('campaigns.index') }}" class="btn-primary w-full justify-center">Mes campagnes</a>
                @else
                    <form method="POST" action="{{ route('invitations.accept', $invitation->token) }}">
                        @csrf
                        <button type="submit" class="btn-primary w-full justify-center">Rejoindre la campagne</button>
                    </form>
                    <p class="mt-3 text-center text-sm text-stone-500">Connecté en tant que {{ auth()->user()->name }}.</p>
                @endif
            @endguest
        </div>
    @endif
</x-layouts.guest>
