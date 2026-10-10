<?php

namespace App\Support;

use App\Models\Campaign;
use App\Models\FeedbackRequest;
use App\Models\FeedbackResponse;
use App\Models\PlaySession;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Avis des joueurs : le MJ les demande, chaque joueur répond une fois, le MJ est prévenu.
 */
final class FeedbackRequests
{
    public static function ask(Campaign $campaign, ?PlaySession $session, bool $anonymous, User $by): FeedbackRequest
    {
        abort_if($session !== null && $session->campaign_id !== $campaign->id, 404);

        $request = new FeedbackRequest(['anonymous' => $anonymous]);
        $request->campaign()->associate($campaign);
        $request->playSession()->associate($session);
        $request->author()->associate($by);
        $request->save();

        $number = $session?->number;
        Notify::players(
            $campaign,
            'feedback',
            fn (string $locale) => $number === null
                ? __('Le MJ vous demande votre avis sur la campagne.', [], $locale)
                : __('Le MJ vous demande votre avis sur la session :number.', ['number' => $number], $locale),
            route('feedback.answer', [$campaign, $request]),
        );

        return $request;
    }

    /** @param array{rating: int, liked: ?string, improve: ?string} $answer */
    public static function answer(FeedbackRequest $request, User $player, array $answer): FeedbackResponse
    {
        abort_unless($request->canAnswer($player), 403);

        $response = DB::transaction(function () use ($request, $player, $answer) {
            $response = new FeedbackResponse($answer);
            $response->request()->associate($request);
            $response->author()->associate($player);
            $response->save();

            return $response;
        });

        $number = $request->playSession?->number;
        $name = $request->anonymous ? null : $player->name;
        $rating = $response->rating;
        Notify::gameMasters(
            $request->campaign,
            'feedback',
            fn (string $locale) => match (true) {
                $name !== null && $number !== null => __(':name a donné son avis sur la session :number : :rating/5.', ['name' => $name, 'number' => $number, 'rating' => $rating], $locale),
                $name !== null => __(':name a donné son avis sur la campagne : :rating/5.', ['name' => $name, 'rating' => $rating], $locale),
                $number !== null => __('Nouvel avis anonyme sur la session :number : :rating/5.', ['number' => $number, 'rating' => $rating], $locale),
                default => __('Nouvel avis anonyme sur la campagne : :rating/5.', ['rating' => $rating], $locale),
            },
            route('feedback.index', $request->campaign),
        );

        return $response;
    }

    /**
     * Demandes ouvertes auxquelles ce joueur n'a pas encore répondu.
     *
     * @return Collection<int, FeedbackRequest>
     */
    public static function pendingFor(User $player, Campaign $campaign)
    {
        return $campaign->feedbackRequests()
            ->whereNull('closed_at')
            ->whereDoesntHave('responses', fn ($q) => $q->where('user_id', $player->id))
            ->with('playSession')
            ->latest()
            ->get();
    }
}
