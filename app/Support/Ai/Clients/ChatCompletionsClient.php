<?php

namespace App\Support\Ai\Clients;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

/** ChatGPT (OpenAI) et Mistral : même forme d'API « chat completions », chacun à son adresse. */
class ChatCompletionsClient implements AiClient
{
    public function __construct(
        private readonly string $provider,
        private readonly string $url,
        private readonly string $apiKey,
        private readonly string $model,
    ) {}

    public function complete(string $prompt): string
    {
        try {
            $response = Http::withToken($this->apiKey)
                ->acceptJson()
                ->timeout(180)
                ->post($this->url, [
                    'model' => $this->model,
                    'messages' => [['role' => 'user', 'content' => $prompt]],
                    'response_format' => ['type' => 'json_object'],
                ]);
        } catch (ConnectionException) {
            throw new AiRequestFailed(__('Impossible de joindre :provider. Réessayez plus tard.', ['provider' => $this->provider]));
        }

        if (in_array($response->status(), [401, 403], true)) {
            throw new AiRequestFailed(__(':provider refuse la clé : vérifiez-la dans vos préférences.', ['provider' => $this->provider]));
        }

        if ($response->status() === 429) {
            throw new AiRequestFailed(__(':provider limite vos appels ou votre crédit est épuisé : réessayez plus tard.', ['provider' => $this->provider]));
        }

        if ($response->failed()) {
            throw new AiRequestFailed(__(':provider a répondu par une erreur (:status). Vérifiez le modèle choisi.', ['provider' => $this->provider, 'status' => $response->status()]));
        }

        $text = $response->json('choices.0.message.content');

        if (! is_string($text) || trim($text) === '' || $response->json('choices.0.finish_reason') === 'length') {
            throw new AiRequestFailed(__('La réponse de l’IA est incomplète. Réessayez, ou réduisez les notes à analyser.'));
        }

        return $text;
    }
}
