<?php

namespace Database\Seeders\v2_0_0;

use App\Model\Mailjob\QueueService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QueueServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('queue_services')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        QueueService::create([
            'id' => 1,
            'name' => 'Sync',
            'short_name' => 'sync',
            'status' => 1,
            'created_at' => '2020-09-02 05:23:18',
            'updated_at' => '2020-09-02 05:23:18',

        ]);

        QueueService::create([
            'id' => 2,
            'name' => 'Database',
            'short_name' => 'database',
            'status' => 0,
            'created_at' => '2020-09-02 05:23:18',
            'updated_at' => '2020-09-02 05:23:18',

        ]);

        QueueService::create([
            'id' => 3,
            'name' => 'Beanstalkd',
            'short_name' => 'beanstalkd',
            'status' => 0,
            'created_at' => '2020-09-02 05:23:18',
            'updated_at' => '2020-09-02 05:23:18',

        ]);

        QueueService::create([
            'id' => 4,
            'name' => 'SQS',
            'short_name' => 'sqs',
            'status' => 0,
            'created_at' => '2020-09-02 05:23:18',
            'updated_at' => '2020-09-02 05:23:18',

        ]);

        QueueService::create([
            'id' => 5,
            'name' => 'Iron',
            'short_name' => 'iron',
            'status' => 0,
            'created_at' => '2020-09-02 05:23:18',
            'updated_at' => '2020-09-02 05:23:18',

        ]);

        QueueService::create([
            'id' => 6,
            'name' => 'Redis',
            'short_name' => 'redis',
            'status' => 0,
            'created_at' => '2020-09-02 05:23:18',
            'updated_at' => '2020-09-02 05:23:18',

        ]);
    }
}
