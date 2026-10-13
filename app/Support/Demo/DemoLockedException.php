<?php

namespace App\Support\Demo;

use Symfony\Component\HttpKernel\Exception\HttpException;

/** Écriture refusée dans une campagne de démonstration verrouillée (traduite par la page d'erreur). */
class DemoLockedException extends HttpException
{
    public const MESSAGE = 'Campagne de démonstration : son contenu se consulte mais ne se modifie pas. Créez votre propre campagne pour essayer.';

    public function __construct()
    {
        parent::__construct(403, self::MESSAGE);
    }
}
