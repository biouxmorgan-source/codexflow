<?php

namespace App\Livewire\Members;

use App\Enums\CampaignRole;
use App\Models\ActivityLog;
use App\Models\Campaign;
use App\Models\CampaignInvitation;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Les membres de la campagne : liens d'invitation à transmettre, membres actuels et leur rôle.
 * Les co-MJ consultent la liste ; seul le propriétaire invite, change les rôles et retire.
 */
class Index extends Component
{
    public Campaign $campaign;

    public string $label = '';

    public string $email = '';

    public string $role = 'player';

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
            ->sortBy(fn (User $user) => [$user->id === $this->campaign->user_id ? 0 : 1, array_search($user->pivot->role, CampaignRole::cases(), true), mb_strtolower($user->name)])
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
        $this->authorize('manage', $this->campaign);

        $validated = $this->validate([
            'label' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'role' => ['required', Rule::enum(CampaignRole::class)],
        ], attributes: ['label' => __('nom'), 'email' => __('adresse e-mail'), 'role' => __('rôle')]);

        $invitation = new CampaignInvitation([
            'label' => $validated['label'] ?: null,
            'email' => $validated['email'] ? mb_strtolower($validated['email']) : null,
            'role' => CampaignRole::from($validated['role']),
        ]);
        $invitation->campaign()->associate($this->campaign);
        $invitation->inviter()->associate(auth()->user());
        $invitation->save();

        $this->createdId = $invitation->id;
        $this->reset(['label', 'email', 'role']);
        unset($this->invitations);
    }

    public function revoke(int $invitationId): void
    {
        $this->authorize('manage', $this->campaign);

        $this->campaign->invitations()->whereKey($invitationId)->whereNull('accepted_at')->delete();
        unset($this->invitations);
    }

    public function changeRole(int $userId, string $role): void
    {
        $this->authorize('manage', $this->campaign);

        // Le propriétaire reste MJ de sa campagne.
        abort_if($userId === $this->campaign->user_id, 403);

        $member = $this->campaign->members()->whereKey($userId)->firstOrFail();
        $new = CampaignRole::from($role);

        if ($member->pivot->role === $new) {
            return;
        }

        $this->campaign->members()->updateExistingPivot($userId, ['role' => $new->value]);

        if ($new !== CampaignRole::Player) {
            $this->releaseCharacters($userId);
        }
        ActivityLog::record('member', $member->id, $member->name, 'updated', ['role' => ['old' => $member->pivot->role->value, 'new' => $new->value]], ['campaign_id' => $this->campaign->id]);

        unset($this->members);
    }

    public function remove(int $userId): void
    {
        $this->authorize('manage', $this->campaign);

        // Le créateur de la campagne en reste toujours le MJ.
        abort_if($userId === $this->campaign->user_id, 403);

        $member = $this->campaign->members()->whereKey($userId)->first();

        if ($member === null) {
            return;
        }

        $this->campaign->members()->detach($userId);
        $this->releaseCharacters($userId);
        ActivityLog::record('member', $member->id, $member->name, 'deleted', ['role' => ['old' => $member->pivot->role->value, 'new' => null]], ['campaign_id' => $this->campaign->id]);

        unset($this->members);
    }

    /**
     * Qui n'est plus joueur ne joue plus ses personnages : ils restent dans la campagne,
     * sans joueur, prêts à être confiés, et plus rien de ce qu'on leur révèle ne lui parvient.
     */
    private function releaseCharacters(int $userId): void
    {
        $this->campaign->playerCharacters()->where('user_id', $userId)->update(['user_id' => null]);
    }

    public function render()
    {
        return view('livewire.members.index')->title(__('Membres · :name', ['name' => $this->campaign->name]));
    }
}
