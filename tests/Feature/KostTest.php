<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class KostTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function role_name()
    {
        return [
            ['user'],
            ['premium'],
            ['owner'],
        ];
    }

    public function test_create_kost()
    {
        $headers = $this->getHeader('owner');
        $kost_data = [
            'name' => $this->faker->name(),
            'description' => $this->faker->sentence(),
            'room_area' => $this->faker->randomNumber(5, false),
            'location' => $this->faker->name(),
            'price' => $this->faker->randomNumber(7, false),
        ];
        $response = $this->json('POST', '/api/kost', $kost_data, $headers);
        $response->assertStatus(200);
    }

    /**
     * @dataProvider role_name
     */
    public function test_failed_create_kost($role_name)
    {
        $headers = $this->getHeader($role_name);
        $response = $this->json('POST', '/api/kost', [], $headers);
        if ($role_name === 'owner') {
            $response->assertStatus(422);
            return;
        }
        $response->assertStatus(403);
    }

}
