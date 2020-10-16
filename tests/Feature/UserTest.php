<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testExample()
    {
        $response = $this->call('GET', '/api/getUserInfo', ['email' => 'ada@das.ru']);
        $response->assertStatus(200)->assertJson(['status' => 'success']);
    }
}
