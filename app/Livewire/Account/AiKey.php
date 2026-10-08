<?php

namespace App\Livewire\Account;

use App\Support\Ai\AiProviders;
use Illuminate\Validation\Rule;
use Livewire\Component;

/**
 * Clé d'API personnelle pour l'assistant IA. Elle est chiffrée en base et ne revient jamais
 * au navigateur : le champ reste vide, seuls ses quatre derniers caractères sont rappelés.
 */
class AiKey extends Component
{
    public string $provider = '';

    public string $model = '';

    /** Nouvelle clé ; vide pour garder celle enregistrée. */
    public string $apiKey = '';

    public function mount(): void
    {
        $this->provider = (string) auth()->user()->ai_provider;
        $this->model = (string) auth()->user()->ai_model;
    }

    public function updatedProvider(): void
    {
        $this->model = $this->provider === '' ? '' : AiProviders::defaultModel($this->provider);
    }

    public function save(): void
    {
        $user = auth()->user();
        $changing = $this->provider !== (string) $user->ai_provider;

        $this->validate([
            'provider' => ['required', Rule::in(array_keys(AiProviders::PROVIDERS))],
            'model' => ['required', 'string', 'max:100', 'regex:/^[A-Za-z0-9._:\/-]+$/'],
            // Changer de fournisseur demande sa clé : celle d'un autre ne servirait à rien.
            'apiKey' => [$changing || ! $user->hasAiKey() ? 'required' : 'nullable', 'string', 'min:20', 'max:500', 'regex:/^\S+$/'],
        ], attributes: ['provider' => __('fournisseur'), 'model' => __('modèle'), 'apiKey' => __('clé d’API')]);

        $user->forceFill(['ai_provider' => $this->provider, 'ai_model' => trim($this->model)]);

        if ($this->apiKey !== '') {
            $user->ai_api_key = trim($this->apiKey);
        }

        $user->save();
        $this->reset('apiKey');
        session()->now('ai-status', __('Clé enregistrée. L’assistant IA peut maintenant analyser directement, à vos frais chez :provider.', ['provider' => AiProviders::name($this->provider)]));
    }

    public function forget(): void
    {
        auth()->user()->forceFill(['ai_provider' => null, 'ai_model' => null, 'ai_api_key' => null])->save();
        $this->reset('provider', 'model', 'apiKey');
        session()->now('ai-status', __('Clé effacée.'));
    }

    public function render()
    {
        $user = auth()->user();

        return view('livewire.account.ai-key', [
            'providers' => AiProviders::PROVIDERS,
            'savedKey' => $user->hasAiKey() ? '…'.substr($user->ai_api_key, -4) : null,
            'savedProvider' => AiProviders::name($user->ai_provider),
        ]);
    }
}
