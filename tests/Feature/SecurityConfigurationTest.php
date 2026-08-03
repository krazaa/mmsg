<?php

namespace Tests\Feature;

use Tests\TestCase;

class SecurityConfigurationTest extends TestCase
{
    public function test_session_payloads_are_encrypted_before_storage(): void
    {
        $this->assertTrue(config('session.encrypt'));
    }
}
