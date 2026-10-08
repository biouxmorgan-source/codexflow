<?php

namespace App\Support\Ai;

use RuntimeException;

/** Réponse d'IA dont on ne tire aucun JSON : le message s'affiche tel quel au MJ. */
class UnreadableResponse extends RuntimeException {}
