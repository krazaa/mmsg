<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function seed($class = \Tests\Support\TestDatabaseSeeder::class)
    {
        return parent::seed($class);
    }
}
