<?php

use App\Models\Document;
use App\Models\User;
use Illuminate\Database\Seeder;

class DocumentsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $manager = User::whereEmail('manager1@gmail.com')->with('clients')->first();
        $manager2 = User::whereEmail('manager2@gmail.com')->with('clients')->first();
        $clients = $manager->getClients();
        $clients2 = $manager2->getClients();

        $faker = Faker\Factory::create('ru_RU');

        foreach ($clients as $client) {

            $limit = 6;
            for ($i = 0; $i < $limit; $i++) {
                $document = Document::Create([
                    'name' => 'Отчет ' . $faker->date('d.m.Y'),
                    'amount' => $faker->numberBetween(10000, 100000),
                    'client_id' => $client->id,
                    'manager_id' => $manager->id,
                ]);

                $document->addFile([
                    'type' => 'application/pdf',
                    'origin_name' => 'отчет.pdf',
                    'name' => '1.pdf',
                    'path' => '/00001',
                    'size' => '123456',
                ]);
            }
        }

        foreach ($clients2 as $client2) {
            $limit = 3;
            for ($i = 0; $i < $limit; $i++) {
                $document = Document::Create([
                    'name' => 'Отчет ' . $faker->date('d.m.Y'),
                    'amount' => $faker->numberBetween(10000, 100000),
                    'client_id' => $client2->id,
                    'manager_id' => $manager2->id,
                ]);

                $document->addFile([
                    'type' => 'application/pdf',
                    'origin_name' => 'отчет.pdf',
                    'name' => '11111111111111111.pdf',
                    'path' => '/00001',
                    'size' => '123456',
                ]);
            }
        }
    }
}
