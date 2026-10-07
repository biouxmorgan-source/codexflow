<x-layouts.app title="Configuration recommandée">
    <div class="max-w-3xl space-y-6">
        <div>
            <h1 class="text-2xl font-semibold">Configuration recommandée</h1>
            <p class="mt-1 text-sm text-stone-600">CodexFlow fonctionne dans un navigateur récent, sans rien installer. Quelques conseils pour en profiter au mieux.</p>
        </div>

        <section class="rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
            <h2 class="mb-2 font-semibold">Navigateur</h2>
            <ul class="list-disc space-y-1 pl-5 text-stone-700">
                <li>Chrome, Edge, Firefox ou Safari, dans une version récente (mis à jour automatiquement en général).</li>
                <li>Pour installer CodexFlow comme une application et recevoir les notifications sur l'appareil : Chrome ou Edge sur ordinateur et Android, Safari sur iPhone et iPad (iOS 16.4 ou plus récent, après « Sur l'écran d'accueil »).</li>
                <li>Les réglages se trouvent dans <a href="{{ route('notifications.index') }}" class="link" wire:navigate>Notifications</a>, bloc « Sur cet appareil ».</li>
            </ul>
        </section>

        <section class="rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
            <h2 class="mb-2 font-semibold">Pour le MJ</h2>
            <ul class="list-disc space-y-1 pl-5 text-stone-700">
                <li>Un ordinateur portable ou une tablette avec un écran d'au moins 13 pouces pour le mode Session.</li>
                <li>Une télé ou un projecteur branché en second écran (HDMI ou recopie sans fil) pour l'écran de table : ouvrez-le depuis le mode Session, glissez la fenêtre dessus, puis « Plein écran ».</li>
                <li>Une connexion Internet stable pendant la partie, pour que les joueurs reçoivent révélations et messages en direct.</li>
            </ul>
        </section>

        <section class="rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
            <h2 class="mb-2 font-semibold">Pour les joueurs</h2>
            <ul class="list-disc space-y-1 pl-5 text-stone-700">
                <li>Un téléphone ou une tablette suffit : la fiche du personnage s'adapte aux petits écrans.</li>
                <li>Installez l'application et ouvrez une fois votre fiche : elle reste lisible même sans réseau.</li>
                <li>Activez les notifications sur l'appareil pour être prévenu d'une révélation ou d'un message, même appli fermée.</li>
            </ul>
        </section>

        <section class="rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
            <h2 class="mb-2 font-semibold">Confort</h2>
            <p class="text-stone-700">Thème sombre pour les parties à la lumière tamisée, couleur d'accent et taille du texte : tout se règle dans <a href="{{ route('preferences') }}" class="link" wire:navigate>Préférences</a>.</p>
        </section>
    </div>
</x-layouts.app>
