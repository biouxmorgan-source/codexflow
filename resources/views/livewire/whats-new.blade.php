<div>
    @if ($versions !== [])
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" role="dialog" aria-modal="true" aria-labelledby="whats-new-title"
            x-data x-init="$nextTick(() => $refs.ok.focus())" x-on:keydown.escape.window="$wire.dismiss()">
            <div class="max-h-[85vh] w-full max-w-lg overflow-y-auto rounded-xl border border-stone-200 bg-white p-6 shadow-xl">
                <p class="text-sm font-medium text-flow">SagaWyn {{ \App\Support\Changelog::version() }}</p>
                <h2 id="whats-new-title" class="text-xl font-semibold">{{ __('Quoi de neuf ?') }}</h2>
                @foreach ($versions as $version => $entry)
                    <section class="mt-4">
                        <h3 class="font-semibold">{{ $entry['title'] }} <span class="text-sm font-normal text-stone-500">· {{ __('version :number', ['number' => $version]) }}</span></h3>
                        <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-stone-700">
                            @foreach ($entry['items'] as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ul>
                    </section>
                @endforeach
                <div class="mt-6 flex flex-wrap items-center gap-3">
                    <button type="button" x-ref="ok" wire:click="dismiss" class="btn-primary">{{ __("C'est noté") }}</button>
                    <a href="{{ route('recommended') }}" wire:click="dismiss" class="link text-sm" wire:navigate>{{ __('Configuration recommandée') }}</a>
                </div>
            </div>
        </div>
    @endif
</div>
