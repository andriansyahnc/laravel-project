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

    public function test_detail_kosts_with_exception()
    {
        $this->withoutExceptionHandling();
        $this->expectException(Exception::class);

        $kostMock = m::mock('KostRepository')
            ->shouldReceive('findById')->andThrow(new Exception());

        $this->app->instance(KostRepository::class, $kostMock);

        $response = $this->json('GET', '/api/kost/1');
    }

    public function test_search_kost_by_name_contains()
    {
        $first_kost = Kost::factory(Kost::class)->create([
            'name' => 'my beautiful kostan',
        ]);
        $second_kost = Kost::factory(Kost::class)->create([
            'name' => 'my chemical romance',
        ]);
        $third_kost = Kost::factory(Kost::class)->create([
            'name' => 'kos siapa?',
        ]);
        $response1 = $this->json('GET', '/api/kost/search?search[name][contains]=my');
        $response1->assertStatus(200);
        $content1 = $response1->decodeResponseJson();
        $this->assertEquals(2, count($content1["data"]));
        $response2 = $this->json('GET', '/api/kost/search?search[name][contains]=siapa?');
        $response2->assertStatus(200);
        $content2 = $response2->decodeResponseJson();
        $this->assertEquals(1, count($content2["data"]));
    }

    public function test_search_kost_by_name_is()
    {
        $first_kost = Kost::factory(Kost::class)->create([
            'name' => 'my beautiful kostan',
        ]);
        $second_kost = Kost::factory(Kost::class)->create([
            'name' => 'my chemical romance',
        ]);
        $response1 = $this->json('GET', '/api/kost/search?search[name][is]=my beautiful kostan');
        $response1->assertStatus(200);
        $content1 = $response1->decodeResponseJson();
        $this->assertEquals(1, count($content1["data"]));
        $response2 = $this->json('GET', '/api/kost/search?search[name][is]=my chemical romance');
        $response2->assertStatus(200);
        $content2 = $response2->decodeResponseJson();
        $this->assertEquals(1, count($content2["data"]));
    }

    public function price_assert_provider()
    {
        return [
            ['lte', 2, 2000],
            ['gte', 2, 2000],
            ['gt', 1, 2000],
            ['lt', 1, 2000],
            ['between', 1, "1500,2000"],
        ];
    }

    /**
     * @dataProvider price_assert_provider
     */
    public function test_search_kost_by_price_lte_gte($op, $count, $price)
    {
        $first_kost = Kost::factory(Kost::class)->create([
            'price' => 1000
        ]);
        $second_kost = Kost::factory(Kost::class)->create([
            'price' => 2000
        ]);
        $third_kost = Kost::factory(Kost::class)->create([
            'price' => 3000
        ]);
        $response = $this->json('GET', '/api/kost/search?search[price][' . $op . ']=' . $price);
        $response->assertStatus(200);
        $content = $response->decodeResponseJson();
        $this->assertEquals($count, count($content["data"]));
    }

    public function sort_provider()
    {
        return [
            ['price', 2],
            ['-price', 1],
        ];
    }

    /**
     * @dataProvider sort_provider
     */
    public function test_search_kost_sort($sort, $idx)
    {
        $kost = [];
        $kost[] = Kost::factory(Kost::class)->create([
            'price' => 2000
        ]);
        $kost[] = Kost::factory(Kost::class)->create([
            'price' => 3000
        ]);
        $kost[] = Kost::factory(Kost::class)->create([
            'price' => 1000
        ]);
        $response = $this->json('GET', '/api/kost/search?sort=' . $sort);
        $response->assertStatus(200);
        $content = $response->decodeResponseJson();
        $this->assertEquals($kost[$idx]->id, $content["data"][0]["id"]);
    }

    public function test_search_kosts_with_exception()
    {
        $this->withoutExceptionHandling();
        $this->expectException(Exception::class);

        $kostMock = m::mock('KostRepository')
            ->shouldReceive('findByParams')->andThrow(new Exception());

        $this->app->instance(KostRepository::class, $kostMock);

        $response = $this->json('GET', '/api/kost/search');
    }

    /**
     * @dataProvider non_owner
     */
    public function test_unauthorized_update_kost($role_name)
    {
        $headers = $this->getHeader($role_name);
        $response = $this->json('PATCH', '/api/kost/1', [], $headers);
        $content = $response->decodeResponseJson();
        $response->assertStatus(403);
    }

    public function test_update_kost_but_not_found()
    {
        $headers = $this->getHeader('owner');
        $response = $this->json('PATCH', '/api/kost/1', [], $headers);
        $content = $response->decodeResponseJson();
        $response->assertStatus(404);
    }

    public function test_update_kost_but_throw_exceptions()
    {
        $this->withoutExceptionHandling();
        $this->expectException(Exception::class);

        $kostMock = m::mock('KostRepository')
            ->shouldReceive('findById')->andThrow(new Exception());

        $this->app->instance(KostRepository::class, $kostMock);

        $headers = $this->getHeader('owner');
        $response = $this->json('PATCH', '/api/kost/1', [], $headers);
    }

    public function test_update_kost_but_forbidden()
    {
        $owners = $this->generateOwners(2);
        $first_kost = Kost::factory(Kost::class)->create([
            'user_id' => $owners[0]->id,
        ]);
        $second_kost = Kost::factory(Kost::class)->create([
            'user_id' => $owners[1]->id,
        ]);
        $headers = $this->getHeader('owner', $owners[1]);
        $response = $this->json('PATCH', '/api/kost/' . $first_kost->id, [], $headers);
        $content = $response->decodeResponseJson();
        $response->assertStatus(403);
    }

}
