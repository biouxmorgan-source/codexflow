<x-layouts.guest :title="__('Invitation')">
    @if ($problem)
        <p class="text-stone-700">{{ $problem }}</p>
        <a href="{{ url('/') }}" class="btn-secondary mt-6 w-full justify-center">{{ __("Retour à l'accueil") }}</a>
    @else
        @php($campaign = $invitation->campaign)
        <p class="text-stone-700">
            {{ __(':name vous invite à rejoindre la campagne', ['name' => $invitation->inviter?->name ?? __('Le MJ')]) }}
        </p>
        <p class="mt-1 text-xl font-semibold">{{ $campaign->name }}</p>
        <p class="mt-1 text-sm text-stone-600">{{ $campaign->gameSystem->name }} · {{ __('en tant que :role', ['role' => $invitation->role === \App\Enums\CampaignRole::GameMaster ? __('Co-MJ') : mb_strtolower($invitation->role->label())]) }}</p>

        <div class="mt-6">
            @guest
                <p class="mb-4 text-sm text-stone-600">{{ __('Pour accepter, connectez-vous ou créez votre compte : vous reviendrez ensuite sur cette page.') }}</p>
                <div class="grid gap-3 sm:grid-cols-2">
                    <a href="{{ route('register', array_filter(['email' => $invitation->email])) }}" class="btn-primary justify-center">{{ __('Créer un compte') }}</a>
                    <a href="{{ route('login') }}" class="btn-secondary justify-center">{{ __('Se connecter') }}</a>
                </div>
            @else
                @if ($member)
                    <p class="mb-4 text-sm text-stone-600">{{ __('Vous faites déjà partie de cette campagne (:role).', ['role' => mb_strtolower($member->label())]) }}</p>
                    <a href="{{ route('campaigns.index') }}" class="btn-primary w-full justify-center">{{ __('Mes campagnes') }}</a>
                @else
                    <form method="POST" action="{{ route('invitations.accept', $invitation->token) }}">
                        @csrf
                        <button type="submit" class="btn-primary w-full justify-center">{{ __('Rejoindre la campagne') }}</button>
                    </form>
                    <p class="mt-3 text-center text-sm text-stone-500">{{ __('Connecté en tant que :name.', ['name' => auth()->user()->name]) }}</p>
                @endif
            @endguest
        </div>
    @endif
</x-layouts.guest>
