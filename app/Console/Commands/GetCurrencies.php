<?php

namespace App\Console\Commands;

use App\Models\Currency;
use Illuminate\Console\Command;

class GetCurrencies extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'currencies:get {char-code?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Получаем валюты с веб сервиса';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $url = env('WEB_SERVICE_URL');
        $command_char_code = $this->argument('char-code');

        $xml = @simplexml_load_file($url);

        // Обрабатываем xml, если получили ответ
        if ($xml) {
            $currensiesArray = [];

            // Парсим xml в массив
            foreach($xml->{'Valute'} as $valute) {
                $char_code = (string)$valute->{'CharCode'};
                $rate = (float)str_replace(',', '.', $valute->{'Value'});

                $currensiesArray[$char_code] = [
                    'name' => $valute->{'Name'},
                    'rate' => $rate,
                ];
            }

            if ($command_char_code) {
                // Ищем валюту в xml, если ее нет, то останавливаем
                if (!isset($currensiesArray[$command_char_code])) {
                    $this->info("This currency is not found!");
                    exit;
                }

                // Если получили параметр валюты
                $currency = Currency::where('сhar_сode', $command_char_code);
                $name = $currensiesArray[$command_char_code]['name'];
                $rate = $currensiesArray[$command_char_code]['rate'];

                if (isset($currensiesArray[$command_char_code])) {
                    if ($currency->count() > 0) {
                        if ($rate != $currency->first()->rate) {
                            $currency->first()->update([
                                'rate' => $rate
                            ]);
                            $this->info("This currency has successfully updated!");
                        } else {
                            $this->info("This currency has not changed!");
                        }
                    } else {
                        Currency::create([
                            'сhar_сode' => $command_char_code,
                            'name' => $name,
                            'rate' => $rate,
                        ]);
                        $this->info("This currency has successfully created!");
                    }

                } else {
                    $this->info("This currency has not found!");
                }
            } else {
                // Если не получили название валюты, то обновляем все
                foreach($currensiesArray as $char_code => $val) {
                    $currency = Currency::where('сhar_сode', $char_code);
                    if ($currency->count() > 0) {
                        // Если валюта существует и курс отличается, то обновляем
                        if ($val['rate'] != $currency->first()->rate) {
                            $currency->first()->update([
                                'rate' => $val['rate']
                            ]);
                        }
                    } else {
                        // Если валюты не существует
                        Currency::create([
                            'сhar_сode' => $char_code,
                            'name' => $val['name'],
                            'rate' => $val['rate'],
                        ]);
                    }
                }
                $this->info("Updated successfully");
            }
        } else {
            $this->info("Web service has not found");
        }
    }
}
