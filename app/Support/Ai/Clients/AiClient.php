<?php

namespace App\Support\Ai\Clients;

/** IA appelée avec la clé d'un MJ : un texte en entrée, la réponse en sortie. */
interface AiClient
{
    /** @throws AiRequestFailed message prêt à montrer au MJ */
    public function complete(string $prompt): string;
}
