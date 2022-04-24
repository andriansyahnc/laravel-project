<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Mockery as m;
use App\Services\Kost\KostRepository;
use App\Models\Kost;
use Exception;

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

    public function non_owner()
    {
        return [
            ['user'],
            ['premium'],
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

    public function test_exception_create_kost()
    {
        $this->withoutExceptionHandling();
        $this->expectException(Exception::class);
        $headers = $this->getHeader('owner');
        $kost_data = [
            'name' => $this->faker->name(),
            'description' => $this->faker->sentence(),
            'room_area' => $this->faker->randomNumber(5, false),
            'location' => $this->faker->name(),
            'price' => $this->faker->randomNumber(7, false),
        ];
        $kostMock = m::mock('KostRepository')
            ->shouldReceive('store')->andThrow(new Exception());

        $this->app->instance(KostRepository::class, $kostMock);

        $response = $this->json('POST', '/api/kost', $kost_data, $headers);
    }

    public function test_list_kosts() {
        $owners = $this->generateOwners(2);

        $kost = Kost::factory(Kost::class)->create([
            'user_id' => $owners[0]->id,
        ]);
        $missing_kost = Kost::factory(Kost::class)->create([
            'user_id' => $owners[1]->id,
        ]);

        $headers = $this->getHeader('owner', $owners[0]);
        $response = $this->json('GET', '/api/kost', [], $headers);
        $content = $response->decodeResponseJson();

        $response->assertStatus(200);
        $this->assertEquals($kost->id, $content['data']['0']['id']);
        $this->assertEquals(1, count($content['data']));
    }

    /**
     * @dataProvider non_owner
     */
    public function test_unauthorized_list_kosts($role_name) {
        $headers = $this->getHeader($role_name);
        $response = $this->json('GET', '/api/kost', [], $headers);
        $content = $response->decodeResponseJson();
        $response->assertStatus(403);
    }

    public function test_exception_list_kosts()
    {
        $this->withoutExceptionHandling();
        $this->expectException(Exception::class);
        $headers = $this->getHeader('owner');
        $kostMock = m::mock('KostRepository')
            ->shouldReceive('findByOwner')->andThrow(new Exception());

        $this->app->instance(KostRepository::class, $kostMock);

        $response = $this->json('GET', '/api/kost', [], $headers);
    }

    public function test_detail_kosts_not_found()
    {
        $response = $this->json('GET', '/api/kost/1');
        $response->assertStatus(404);
    }

    public function test_detail_kosts()
    {
        $kost = Kost::factory(Kost::class)->create();
        $response = $this->json('GET', '/api/kost/' . $kost->id);
        $response->assertStatus(200);
    }

    public function tearDown(): void
    {
        parent::tearDown();
        m::close();
    }

}
