<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Teacher;

class ParseTeachers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parse:teachers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Parse teachers ID\'s';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $i = 1;
        while (true) {
            $data = $this->getPageData($i);
            if(isset($data->meta->has_next) === false || (bool) $data->meta->has_next === false) {
                break;
            }
            $this->parseData($data);
            $i++;
            sleep(60);
        }
    }

    private function getPageData($page = 1)
    {
        $client = new \GuzzleHttp\Client();
        $response = $client->get('https://www.italki.com/api/teachersv2?page=' . $page);
        return json_decode($response->getBody());
    }
    private function parseData($data)
    {
        if(isset($data->data) && is_array($data->data)) {
            foreach ($data->data as $item) {
                if(isset($item->id)) {
                    $id = intval($item->id);
                    Teacher::addID($id);
                    $this->info('Added teacher ID: ' . $id);
                }
            }
        }
    }
}
