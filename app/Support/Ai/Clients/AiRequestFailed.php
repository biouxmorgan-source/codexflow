<?php

namespace App\Support\Ai\Clients;

use RuntimeException;

/** Appel d'IA refusé ou impossible ; le message s'affiche tel quel au MJ, sans jamais citer la clé. */
class AiRequestFailed extends RuntimeException {}
