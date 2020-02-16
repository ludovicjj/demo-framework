<?php

use Phinx\Seed\AbstractSeed;

class PostSeeder extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * http://docs.phinx.org/en/latest/seeding.html
     */
    public function run()
    {
        // seeding des categories
        $data = [];
        $faker = \Faker\Factory::create('fr_FR'); // create a French faker

        for ($i = 0; $i < 5; $i++) {
            $data[] = [
                'name' => $faker->catchPhrase,
                'slug' => $faker->slug,
            ];
        }

        $this->table('categories')->insert($data)->save();

        $data = [];
        $faker = \Faker\Factory::create('fr_FR');
        for ($i = 0; $i < 100; ++$i) {
            $data[] = [
                'name' => $faker->catchPhrase,
                'slug' => $faker->slug,
                'content' => $faker->text(3000),
                'created_at' => date('Y-m-d H:i:s', $faker->unixTime('now')),
                'updated_at' => date('Y-m-d H:i:s', $faker->unixTime('now')),
                'category_id' => rand(1, 5),
            ];
        }

        $this->table('posts')->insert($data)->save();
    }
}
