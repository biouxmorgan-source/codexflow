<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        // Les requêtes de test annoncent « en-us » par défaut (Symfony) : les parcours sont écrits en français.
        $this->withHeader('Accept-Language', 'fr-FR,fr;q=0.9');
    }
}
