<?php

namespace App\Support\Ai\Clients;

use Anthropic\Client;
use Anthropic\Core\Exceptions\APIConnectionException;
use Anthropic\Core\Exceptions\APIStatusException;
use Anthropic\Core\Exceptions\AuthenticationException;
use Anthropic\Core\Exceptions\PermissionDeniedException;
use Anthropic\Core\Exceptions\RateLimitException;
use GuzzleHttp\Client as Guzzle;
use Psr\Http\Client\ClientInterface;

/** Claude, par le SDK officiel d'Anthropic, avec la clé du MJ et jamais celle du serveur. */
class AnthropicClient implements AiClient
{
    /** Modèles qui acceptent le repli automatique côté serveur quand une demande est déclinée. */
    private const FALLBACK_MODELS = ['claude-opus-5-5', 'claude-sonnet-5-5'];

    public function __construct(
        private readonly string $apiKey,
        private readonly string $model,
        private readonly ?ClientInterface $transporter = null,
    ) {}

    public function complete(string $prompt): string
    {
        // Clé, jeton et adresse donnés explicitement : le SDK ne va rien chercher dans l'environnement du serveur.
        $client = new Client(
            apiKey: $this->apiKey,
            authToken: '',
            baseUrl: 'https://api.anthropic.com',
            requestOptions: ['transporter' => $this->transporter ?? new Guzzle(['timeout' => 180]), 'maxRetries' => 1],
        );

        $fallback = in_array($this->model, self::FALLBACK_MODELS, true);

        try {
            $message = $client->beta->messages->create(
                model: $this->model,
                maxTokens: 16000,
                messages: [['role' => 'user', 'content' => $prompt]],
                fallbacks: $fallback ? 'default' : null,
                betas: $fallback ? ['server-side-fallback-2026-07-01'] : null,
            );
        } catch (AuthenticationException|PermissionDeniedException) {
            throw new AiRequestFailed(__('Anthropic refuse la clé : vérifiez-la dans vos préférences.'));
        } catch (RateLimitException) {
            throw new AiRequestFailed(__('Anthropic limite vos appels pour l’instant : réessayez dans un moment.'));
        } catch (APIStatusException $e) {
            throw new AiRequestFailed(__('Anthropic a répondu par une erreur (:status). Vérifiez le modèle et le crédit de votre compte.', ['status' => $e->status]));
        } catch (APIConnectionException) {
            throw new AiRequestFailed(__('Impossible de joindre :provider. Réessayez plus tard.', ['provider' => 'Anthropic']));
        }

        if ($message->stopReason === 'refusal') {
            throw new AiRequestFailed(__('L’IA a refusé de traiter cette demande. Essayez le mode « texte à coller » avec une autre IA.'));
        }

        $text = collect($message->content)->where('type', 'text')->pluck('text')->implode("\n");

        if ($message->stopReason === 'max_tokens' || trim($text) === '') {
            throw new AiRequestFailed(__('La réponse de l’IA est incomplète. Réessayez, ou réduisez les notes à analyser.'));
        }

        return $text;
    }
}
