<?php

namespace Tests\Feature;

use App\Models\Currency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CurrencyTest extends TestCase
{
    use RefreshDatabase;

    public function testGetAllCurrenciesIfUserHasWrongToken()
    {
        $token = 'test';
        $response = $response = $this->post('/api/currencies', ['api_token' => $token]);
        $response->assertStatus(401)->assertSeeText('Wrong token!');
    }

    public function testGetOneCurrencyIfUserHasWrongToken()
    {
        $token = 'test';
        $response = $response = $this->post('/api/currency/5', ['api_token' => $token]);
        $response->assertStatus(401)->assertSeeText('Wrong token!');
    }

    public function testGetAllCurrenciesIfUserHasRightToken()
    {
        $this->seed();
        $this->artisan('currencies:get');
        $user = User::first();
        $token = $user->api_token;
        $currency = Currency::first();

        $response = $this->post('/api/currencies', ['api_token' => $token]);
        $response->assertStatus(200)->assertJsonFragment([
            "id" => $currency->id,
            "сhar_сode" => $currency->сhar_сode,
            "name" => $currency->name,
            "rate" => number_format($currency->rate, 4)
        ]);
    }

    public function testGetOneCurrencyIfUserHasRightToken()
    {
        $this->seed();
        $this->artisan('currencies:get');
        $user = User::first();
        $token = $user->api_token;
        $currency = Currency::first();

        $response = $this->post('/api/currency/' . $currency->id, ['api_token' => $token]);
        $response->assertStatus(200)->assertJsonFragment([
            "id" => $currency->id,
            "сhar_сode" => $currency->сhar_сode,
            "name" => $currency->name,
            "rate" => number_format($currency->rate, 4)
        ]);
    }

    public function testGetOneCurrencyIfUserHasRightTokenButWrongId()
    {
        $this->seed();
        $this->artisan('currencies:get');
        $user = User::first();
        $token = $user->api_token;

        $response = $this->post('/api/currency/100000', ['api_token' => $token]);
        $response->assertStatus(401)->assertSeeText('This currency has not found!');
    }
}
