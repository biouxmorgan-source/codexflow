<x-layouts.app :title="__('Préparer l’import avec une IA')">
    <nav class="mb-2 text-sm text-stone-500">
        <a href="{{ route('campaigns.index') }}" class="crumb" wire:navigate>{{ __('Mes campagnes') }}</a>
        @if ($campaign)
            › <a href="{{ route('campaigns.show', $campaign) }}" class="crumb" wire:navigate>{{ $campaign->name }}</a>
            › <a href="{{ route('imports.create', $campaign) }}" class="crumb" wire:navigate>{{ __('Importer') }}</a>
        @endif
    </nav>

    <h1 class="text-2xl font-semibold">{{ __('Préparer l’import avec une IA') }}</h1>
    <p class="mt-1 mb-6 max-w-3xl text-sm text-stone-600">{{ __('Votre IA lit le PDF d’un jeu de rôle (livre de règles, scénario, supplément) et prépare les fichiers à importer dans SagaWyn, avec un guide pas à pas. SagaWyn n’envoie rien : vous utilisez l’IA de votre choix, avec votre compte.') }}</p>

    <ol class="mb-6 max-w-3xl list-inside list-decimal space-y-1 text-sm">
        <li>{{ __('Ouvrez votre IA (Claude, ChatGPT, Gemini, Le Chat…) et joignez le ou les fichiers du jeu.') }}</li>
        @if ($campaign)
            <li>{{ __('Copiez le texte ci-dessous et collez-le dans la conversation. Il décrit les formats d’import et ce que contient déjà cette campagne.') }}</li>
            <li>{{ __('Validez le plan que l’IA propose, puis récupérez les fichiers CSV et le guide IMPORT-GUIDE.md.') }}</li>
            <li>{{ __('Suivez le guide : champs, puis fiches, règles, documents, et les scènes en dernier. Chaque import montre un aperçu avant de créer quoi que ce soit.') }}</li>
        @else
            <li>{{ __('Copiez le texte ci-dessous et collez-le dans la conversation. Votre IA doit pouvoir exécuter du code (Claude, ChatGPT…) : elle fabrique une archive de campagne .zip.') }}</li>
            <li>{{ __('Validez le plan que l’IA propose, puis récupérez l’archive .zip et le guide IMPORT-GUIDE.md.') }}</li>
            <li>{{ __('Dans Mes campagnes, cliquez sur « Importer une campagne » et choisissez l’archive : le jeu, le monde et la campagne sont créés d’un coup.') }}</li>
        @endif
    </ol>

    <p class="mb-6 max-w-3xl rounded-lg bg-amber-50 px-4 py-2 text-sm text-amber-900">{{ __('Utilisez uniquement des documents que vous possédez, pour votre usage personnel. Le prompt demande tout le contenu utile pour jouer, avec la page du livre : gardez les fichiers produits pour vous.') }}
        <a href="{{ route('privacy') }}#importation" class="font-medium underline">{{ __('Importation et structuration de contenus') }}</a>
    </p>

    <section class="rounded-xl border border-stone-200 bg-white p-6 shadow-sm" x-data="{ copied: false }">
        <form method="GET" class="mb-4 flex flex-wrap items-center gap-2 text-sm">
            <label for="ai-language" class="font-medium">{{ __('Langue du contenu importé') }}</label>
            <select id="ai-language" name="lang" class="field w-auto" x-on:change="$el.form.submit()">
                @foreach (config('codexflow.locales') as $code => $name)
                    <option value="{{ $code }}" @selected($code === $language)>{{ $name }}</option>
                @endforeach
            </select>
            <span class="text-xs text-stone-500">{{ __('Le prompt est en anglais, que les IA suivent le mieux ; l’IA écrit les fiches, les scènes et le guide dans la langue choisie.') }}</span>
        </form>
        <div class="mb-3 flex flex-wrap items-center gap-3">
            <button type="button" class="btn-primary"
                x-on:click="navigator.clipboard.writeText($refs.prompt.value).then(() => { copied = true; setTimeout(() => copied = false, 2500) })">
                <span x-show="! copied">{{ __('Copier le prompt') }}</span>
                <span x-show="copied" x-cloak>{{ __('Copié !') }}</span>
            </button>
            <a href="{{ ($campaign ? route('imports.ai.prompt', [$campaign, 'lang' => $language]) : route('imports.ai.archive.prompt', ['lang' => $language])) }}" class="btn-secondary">{{ __('Télécharger (.md)') }}</a>
            <a href="{{ ($campaign ? route('imports.ai.skill', [$campaign, 'lang' => $language]) : route('imports.ai.archive.skill', ['lang' => $language])) }}" class="btn-secondary">{{ __('Télécharger comme skill Claude (.zip)') }}</a>
        </div>
        <label for="ai-prompt" class="sr-only">{{ __('Prompt') }}</label>
        <textarea id="ai-prompt" x-ref="prompt" readonly rows="24" class="field font-mono text-xs leading-relaxed">{{ $prompt }}</textarea>
        <p class="mt-2 text-xs text-stone-500">{{ __('Le skill Claude s’ajoute dans Claude, rubrique Compétences (Skills) : ensuite, il suffit de joindre le PDF et de demander « Prépare l’import SagaWyn », en précisant au besoin une autre langue pour le contenu.') }}</p>
    </section>
</x-layouts.app>
