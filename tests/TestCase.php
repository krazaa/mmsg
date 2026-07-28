<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\Support\TestDatabaseSeeder;

abstract class TestCase extends BaseTestCase
{
    public function seed($class = TestDatabaseSeeder::class)
    {
        return parent::seed($class);
    }
}
