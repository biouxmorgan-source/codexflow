<?php

namespace App\Support\Ai;

use App\Models\User;
use App\Support\Ai\Clients\AiClient;
use App\Support\Ai\Clients\AnthropicClient;
use App\Support\Ai\Clients\ChatCompletionsClient;

/**
 * IA qu'un MJ peut brancher avec sa propre clé. Les modèles proposés ne sont que des suggestions :
 * le MJ peut en saisir un autre, celui que son compte chez le fournisseur autorise.
 */
class AiProviders
{
    public const PROVIDERS = [
        'anthropic' => [
            'name' => 'Claude (Anthropic)',
            'models' => ['claude-opus-5-5', 'claude-sonnet-5-5', 'claude-haiku-5-5'],
            'keys' => 'https://platform.claude.com/settings/keys',
        ],
        'openai' => [
            'name' => 'ChatGPT (OpenAI)',
            'models' => ['gpt-5', 'gpt-5-mini'],
            'keys' => 'https://platform.openai.com/api-keys',
            'url' => 'https://api.openai.com/v1/chat/completions',
        ],
        'mistral' => [
            'name' => 'Le Chat (Mistral)',
            'models' => ['mistral-large-latest', 'mistral-medium-latest'],
            'keys' => 'https://console.mistral.ai/api-keys',
            'url' => 'https://api.mistral.ai/v1/chat/completions',
        ],
    ];

    public static function defaultModel(string $provider): string
    {
        return self::PROVIDERS[$provider]['models'][0];
    }

    public static function name(?string $provider): string
    {
        return self::PROVIDERS[$provider]['name'] ?? '';
    }

    /** Client de l'IA du MJ, ou null s'il n'a pas branché de clé. */
    public static function clientFor(User $user): ?AiClient
    {
        if (! $user->hasAiKey() || ! isset(self::PROVIDERS[$user->ai_provider])) {
            return null;
        }

        $model = $user->ai_model ?: self::defaultModel($user->ai_provider);

        return $user->ai_provider === 'anthropic'
            ? new AnthropicClient($user->ai_api_key, $model)
            : new ChatCompletionsClient(self::name($user->ai_provider), self::PROVIDERS[$user->ai_provider]['url'], $user->ai_api_key, $model);
    }
}
