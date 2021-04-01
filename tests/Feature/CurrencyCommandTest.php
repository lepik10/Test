<?php

namespace Tests\Feature;

use App\Models\Currency;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CurrencyCommandTest extends TestCase
{
    use RefreshDatabase;

    public function testCreateAllCurrencies()
    {
        $xml = $this->fillCurrencies();

        foreach($xml->{'Valute'} as $valute) {
            $this->assertDatabaseHas('currencies', [
                'сhar_сode' => (string)$valute->{'CharCode'},
                'name' => (string)$valute->{'Name'},
                'rate' => (float)str_replace(',', '.', $valute->{'Value'}),
            ]);
        }
    }

    public function testUpdateAllCurrenciesIfValueNotChanged()
    {
        $xml = $this->fillCurrencies();
        $this->artisan('currencies:get');

        foreach($xml->{'Valute'} as $valute) {
            $this->assertDatabaseMissing('currency_history', [
                'rate' => (float)str_replace(',', '.', $valute->{'Value'})
            ]);
        }
    }

    public function testUpdateAllCurrenciesIfValueChanged()
    {
        $xml = $this->fillCurrencies();
        $currencies = Currency::all();

        foreach($currencies as $currency) {
            $currency->update(['rate' => 1.1]);
        }

        foreach($xml->{'Valute'} as $valute) {
            $this->assertDatabaseHas('currency_history', [
                'rate' => (float)str_replace(',', '.', $valute->{'Value'})
            ]);
        }
    }

    public function testUpdateOneCurrencyIfNotCreated()
    {
        $valute = $this->createOneCurrency();

        $this->assertDatabaseHas('currencies', [
            'сhar_сode' => (string)$valute->{'CharCode'},
            'name' => (string)$valute->{'Name'},
            'rate' => (float)str_replace(',', '.', $valute->{'Value'}),
        ]);
    }

    public function testUpdateOneCurrencyIfCreatedAndHasSameValue()
    {
        $valute = $this->createOneCurrency();
        $this->artisan('currencies:get USD')->expectsOutput('This currency has not changed!');
    }

    public function testUpdateOneCurrencyIfCreatedAndHasDifferentValue()
    {
        $valute = $this->createOneCurrency();
        $currency = Currency::first();

        $currency->update(['rate' => 1.11]);

        $this->assertDatabaseHas('currencies', [
            'сhar_сode' => (string)$valute->{'CharCode'},
            'name' => (string)$valute->{'Name'},
            'rate' => 1.11,
        ]);

        $this->assertDatabaseHas('currency_history', [
            'currency_id' => $currency->id,
            'rate' => (float)str_replace(',', '.', $valute->{'Value'})
        ]);
    }

    private function fillCurrencies()
    {
        // Заполняем валюты
        $url = env('WEB_SERVICE_URL');
        $this->artisan('currencies:get')->expectsOutput('Updated successfully');
        $xml = @simplexml_load_file($url);

        return $xml;
    }

    private function createOneCurrency()
    {
        $url = env('WEB_SERVICE_URL');
        $this->artisan('currencies:get USD')->expectsOutput('This currency has successfully created!');
        $xml = @simplexml_load_file($url);

        foreach($xml->{'Valute'} as $valute) {
            if ($valute->{'CharCode'} == 'USD') {
                return $valute;
            }
        }
    }

}
