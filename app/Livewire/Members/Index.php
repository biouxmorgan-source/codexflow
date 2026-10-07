<?php

namespace App\Livewire\Members;

use App\Enums\CampaignRole;
use App\Models\ActivityLog;
use App\Models\Campaign;
use App\Models\CampaignInvitation;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Les joueurs de la campagne : liens d'invitation à transmettre, membres actuels.
 * Réservé au MJ.
 */
class Index extends Component
{
    public Campaign $campaign;

    public string $label = '';

    public string $email = '';

    /** Invitation qui vient d'être créée, mise en avant pour copier son lien. */
    public ?int $createdId = null;

    public function mount(Campaign $campaign): void
    {
        $this->authorize('update', $campaign);
    }

    /** @return Collection<int, User> */
    #[Computed]
    public function members(): Collection
    {
        return $this->campaign->members()->get()
            ->sortBy(fn (User $user) => [$user->pivot->role === CampaignRole::GameMaster ? 0 : 1, mb_strtolower($user->name)])
            ->values();
    }

    /** @return Collection<int, CampaignInvitation> */
    #[Computed]
    public function invitations(): Collection
    {
        return $this->campaign->invitations()->pending()->latest('id')->get();
    }

    public function invite(): void
    {
        $this->authorize('update', $this->campaign);

        $validated = $this->validate([
            'label' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
        ], attributes: ['label' => 'nom', 'email' => 'adresse e-mail']);

        $invitation = new CampaignInvitation([
            'label' => $validated['label'] ?: null,
            'email' => $validated['email'] ? mb_strtolower($validated['email']) : null,
            'role' => CampaignRole::Player,
        ]);
        $invitation->campaign()->associate($this->campaign);
        $invitation->inviter()->associate(auth()->user());
        $invitation->save();

        $this->createdId = $invitation->id;
        $this->reset(['label', 'email']);
        unset($this->invitations);
    }

    public function revoke(int $invitationId): void
    {
        $this->authorize('update', $this->campaign);

        $this->campaign->invitations()->whereKey($invitationId)->whereNull('accepted_at')->delete();
        unset($this->invitations);
    }

    public function remove(int $userId): void
    {
        $this->authorize('update', $this->campaign);

        // Le créateur de la campagne en reste toujours le MJ.
        abort_if($userId === $this->campaign->user_id, 403);

        $member = $this->campaign->members()->whereKey($userId)->first();

        if ($member === null) {
            return;
        }

        $this->campaign->members()->detach($userId);
        ActivityLog::record('member', $member->id, $member->name, 'deleted', ['role' => ['old' => $member->pivot->role->value, 'new' => null]], ['campaign_id' => $this->campaign->id]);

        unset($this->members);
    }

    public function render()
    {
        return view('livewire.members.index')->title('Joueurs · '.$this->campaign->name);
    }
}
