@if (session('invitation_campaign'))
    <p class="mb-4 rounded-md bg-flow/10 px-3 py-2 text-sm text-ink">Vous êtes invité à rejoindre « {{ session('invitation_campaign') }} ». Une fois connecté, vous reviendrez à l'invitation.</p>
@endif
