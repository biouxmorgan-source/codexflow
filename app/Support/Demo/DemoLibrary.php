<?php

namespace App\Support\Demo;

use App\Models\Campaign;
use App\Models\DemoLibraryItem;
use App\Models\User;
use App\Support\Archive\ArchiveException;
use App\Support\Archive\CampaignExport;
use App\Support\Archive\CampaignImport;
use App\Support\Plans\Plans;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Bibliothèque de démos. L'administrateur prépare une campagne dans son compte et la publie :
 * son contenu est figé dans une archive SagaWyn (sans membres ni personnages joueurs, comme un export).
 * Un MJ charge ensuite sa propre copie, qu'il modifie librement ; la démo ne bouge pas.
 */
class DemoLibrary
{
    private const FOLDER = 'demo-library';

    /** Fige le contenu actuel de la campagne dans l'archive de la démo (nouvelle ou mise à jour). */
    public function snapshot(DemoLibraryItem $item, Campaign $campaign): void
    {
        $temporary = (new CampaignExport($campaign))->write();
        $path = self::FOLDER.'/'.Str::uuid().'.zip';

        try {
            Storage::disk(DemoLibraryItem::DISK)->put($path, fopen($temporary, 'r'));
        } finally {
            @unlink($temporary);
        }

        $previous = $item->archive_path;
        $item->forceFill([
            'archive_path' => $path,
            'archive_size' => Storage::disk(DemoLibraryItem::DISK)->size($path),
            'source_campaign_id' => $campaign->id,
        ])->save();

        if ($previous && $previous !== $path) {
            Storage::disk(DemoLibraryItem::DISK)->delete($previous);
        }
    }

    /**
     * Copie de la démo dans le compte du MJ, qui en devient propriétaire. Mêmes limites que pour
     * créer ou importer une campagne : nombre de campagnes de la formule et espace de stockage.
     *
     * @return array{0: Campaign, 1: list<string>} la campagne et les remarques de l'import
     *
     * @throws ArchiveException
     */
    public function load(User $user, DemoLibraryItem $item, string $field = 'demo'): array
    {
        Plans::ensureCanCreateCampaign($user, $field);
        Plans::ensureRoom($user, (int) $item->archive_size, $field);

        $import = new CampaignImport($user);
        $campaign = $import->handle(Storage::disk(DemoLibraryItem::DISK)->path($item->archive_path));
        $campaign->forceFill(['is_demo' => true, 'demo_library_item_id' => $item->id])->save();
        $item->increment('loads_count');

        return [$campaign, $import->notices];
    }
}
