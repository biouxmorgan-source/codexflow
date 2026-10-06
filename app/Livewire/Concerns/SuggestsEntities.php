<?php

namespace App\Livewire\Concerns;

use Livewire\Attributes\Renderless;

/**
 * Suggestions pour l'autocomplétion des liens [[…]] dans les champs de texte.
 * Le composant doit exposer une propriété publique $campaign.
 */
trait SuggestsEntities
{
    /**
     * @return list<array{id: int, name: string, type: string}>
     */
    #[Renderless]
    public function suggestEntities(string $query): array
    {
        $query = trim(mb_substr($query, 0, 60));

        return $this->campaign->availableEntities()
            ->with('type')
            ->when($query !== '', fn ($q) => $q->where('name', 'ilike', '%'.addcslashes($query, '%_\\').'%'))
            ->orderByRaw('lower(name) like ? desc', [mb_strtolower(addcslashes($query, '%_\\')).'%'])
            ->orderBy('name')
            ->limit(8)
            ->get()
            ->map(fn ($entity) => [
                'id' => $entity->id,
                'name' => $entity->name,
                'type' => $entity->type->name,
            ])
            ->all();
    }
}
