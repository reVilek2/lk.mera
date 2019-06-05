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
        $client = User::whereEmail('user1@gmail.com')->first();
        $manager = User::whereEmail('manager1@gmail.com')->first();

        $faker = Faker\Factory::create('ru_RU');
        $limit = 6;
        for ($i = 0; $i < $limit; $i++) {
            $document = Document::Create([
                'name'=> 'Отчет '.$faker->date('d.m.Y'),
                'amount'=> $faker->numberBetween(10000, 100000),
                'client_id' => $client->id,
                'manager_id' => $manager->id,
            ]);

            $document->addFile([
                'path' => '/documents/00001',
                'name' => 'doument',
                'type' => 'document',
                'size' => '123456',
                'ext' => 'pdf',
            ]);
        }
    }
}
