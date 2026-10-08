<?php

namespace App\Livewire\Admin;

use App\Support\Plans\Plans;
use Illuminate\Validation\Rule;
use Livewire\Component;

/** Ce que comprend chaque formule : stockage, nombre de campagnes, fonctions de la formule gratuite. */
class PlanSettings extends Component
{
    public int $freeStorageMb = 0;

    public int $premiumStorageMb = 0;

    public int $freeMaxCampaigns = 0;

    /** @var list<string> */
    public array $freeFeatures = [];

    public function mount(): void
    {
        $this->authorize('admin');

        $settings = Plans::settings();
        $this->freeStorageMb = (int) $settings['free_storage_mb'];
        $this->premiumStorageMb = (int) $settings['premium_storage_mb'];
        $this->freeMaxCampaigns = (int) $settings['free_max_campaigns'];
        $this->freeFeatures = array_values($settings['free_features']);
    }

    public function save(): void
    {
        $this->authorize('admin');

        $this->validate([
            'freeStorageMb' => ['required', 'integer', 'min:0', 'max:10000000'],
            'premiumStorageMb' => ['required', 'integer', 'min:0', 'max:10000000'],
            'freeMaxCampaigns' => ['required', 'integer', 'min:0', 'max:1000'],
            'freeFeatures' => ['array'],
            'freeFeatures.*' => [Rule::in(Plans::FEATURES)],
        ], attributes: [
            'freeStorageMb' => __('stockage de la formule gratuite'),
            'premiumStorageMb' => __('stockage de la formule premium'),
            'freeMaxCampaigns' => __('campagnes de la formule gratuite'),
        ]);

        Plans::save([
            'free_storage_mb' => $this->freeStorageMb,
            'premium_storage_mb' => $this->premiumStorageMb,
            'free_max_campaigns' => $this->freeMaxCampaigns,
            'free_features' => array_values(array_intersect(Plans::FEATURES, $this->freeFeatures)),
        ]);

        session()->now('status', __('Formules enregistrées.'));
    }

    public function render()
    {
        return view('livewire.admin.plan-settings', ['features' => Plans::features()])->title(__('Administration · Formules'));
    }
}
