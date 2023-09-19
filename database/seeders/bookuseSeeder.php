<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\bookuse;

class bookuseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //level 1
        bookuse::create([
            'name' => 'G11-G20',
            'type' => '1',
            'qty' => '10',
            'level_id' => '1',
            'term_id' => '1',
            'status' => 1
        ]);
        bookuse::create([
            'name' => 'G11-G15',
            'type' => '2',
            'qty' => '5',
            'level_id' => '1',
            'term_id' => '1',
            'status' => 1
        ]);
        bookuse::create([
            'name' => 'G16-G20',
            'type' => '2',
            'qty' => '6',
            'level_id' => '1',
            'term_id' => '1',
            'status' => 1
        ]);

        bookuse::create([
            'name' => 'G21-G30',
            'type' => '1',
            'qty' => '10',
            'level_id' => '1',
            'term_id' => '2',
            'status' => 1
        ]);
        bookuse::create([
            'name' => 'G21-G25',
            'type' => '2',
            'qty' => '5',
            'level_id' => '1',
            'term_id' => '2',
            'status' => 1
        ]);
        bookuse::create([
            'name' => 'G26-G30',
            'type' => '2',
            'qty' => '6',
            'level_id' => '1',
            'term_id' => '2',
            'status' => 1
        ]);

        bookuse::create([
            'name' => 'G31-G40',
            'type' => '1',
            'qty' => '10',
            'level_id' => '1',
            'term_id' => '3',
            'status' => 1
        ]);
        bookuse::create([
            'name' => 'G31-G35',
            'type' => '2',
            'qty' => '5',
            'level_id' => '1',
            'term_id' => '3',
            'status' => 1
        ]);
        bookuse::create([
            'name' => 'G36-G40',
            'type' => '2',
            'qty' => '6',
            'level_id' => '1',
            'term_id' => '3',
            'status' => 1
        ]);

        bookuse::create([
            'name' => 'G41-G50',
            'type' => '1',
            'qty' => '10',
            'level_id' => '1',
            'term_id' => '4',
            'status' => 1
        ]);
        bookuse::create([
            'name' => 'G41-G45',
            'type' => '2',
            'qty' => '5',
            'level_id' => '1',
            'term_id' => '4',
            'status' => 1
        ]);
        bookuse::create([
            'name' => 'G46-G50',
            'type' => '2',
            'qty' => '6',
            'level_id' => '1',
            'term_id' => '4',
            'status' => 1
        ]);

        //level 2
        bookuse::create([
            'name' => 'G51-G60',
            'type' => '1',
            'qty' => '10',
            'level_id' => '2',
            'term_id' => '1',
            'status' => 1
        ]);
        bookuse::create([
            'name' => 'G51-G55',
            'type' => '2',
            'qty' => '5',
            'level_id' => '2',
            'term_id' => '1',
            'status' => 1
        ]);
        bookuse::create([
            'name' => 'G56-G60',
            'type' => '2',
            'qty' => '6',
            'level_id' => '2',
            'term_id' => '1',
            'status' => 1
        ]);

        bookuse::create([
            'name' => 'G61-G70',
            'type' => '1',
            'qty' => '10',
            'level_id' => '2',
            'term_id' => '2',
            'status' => 1
        ]);
        bookuse::create([
            'name' => 'G61-G65',
            'type' => '2',
            'qty' => '5',
            'level_id' => '2',
            'term_id' => '2',
            'status' => 1
        ]);
        bookuse::create([
            'name' => 'G66-G70',
            'type' => '2',
            'qty' => '6',
            'level_id' => '2',
            'term_id' => '2',
            'status' => 1
        ]);

        bookuse::create([
            'name' => 'G71-G80',
            'type' => '1',
            'qty' => '10',
            'level_id' => '2',
            'term_id' => '3',
            'status' => 1
        ]);
        bookuse::create([
            'name' => 'G71-G75',
            'type' => '2',
            'qty' => '5',
            'level_id' => '2',
            'term_id' => '3',
            'status' => 1
        ]);
        bookuse::create([
            'name' => 'G76-G80',
            'type' => '2',
            'qty' => '6',
            'level_id' => '2',
            'term_id' => '3',
            'status' => 1
        ]);

        bookuse::create([
            'name' => 'G81-G90',
            'type' => '1',
            'qty' => '10',
            'level_id' => '2',
            'term_id' => '4',
            'status' => 1
        ]);
        bookuse::create([
            'name' => 'G81-G85',
            'type' => '2',
            'qty' => '5',
            'level_id' => '2',
            'term_id' => '4',
            'status' => 1
        ]);
        bookuse::create([
            'name' => 'G86-G90',
            'type' => '2',
            'qty' => '6',
            'level_id' => '2',
            'term_id' => '4',
            'status' => 1
        ]);

        //level 3
        bookuse::create([
            'name' => 'G111-G120',
            'type' => '1',
            'qty' => '10',
            'level_id' => '3',
            'term_id' => '1',
            'status' => 1
        ]);
        bookuse::create([
            'name' => 'G111-G115',
            'type' => '2',
            'qty' => '5',
            'level_id' => '3',
            'term_id' => '1',
            'status' => 1
        ]);
        bookuse::create([
            'name' => 'G116-G120',
            'type' => '2',
            'qty' => '6',
            'level_id' => '3',
            'term_id' => '1',
            'status' => 1
        ]);

        bookuse::create([
            'name' => 'G121-G130',
            'type' => '1',
            'qty' => '10',
            'level_id' => '3',
            'term_id' => '2',
            'status' => 1
        ]);
        bookuse::create([
            'name' => 'G121-G125',
            'type' => '2',
            'qty' => '5',
            'level_id' => '3',
            'term_id' => '2',
            'status' => 1
        ]);
        bookuse::create([
            'name' => 'G126-G130',
            'type' => '2',
            'qty' => '6',
            'level_id' => '3',
            'term_id' => '2',
            'status' => 1
        ]);

        bookuse::create([
            'name' => 'G131-G140',
            'type' => '1',
            'qty' => '10',
            'level_id' => '3',
            'term_id' => '3',
            'status' => 1
        ]);
        bookuse::create([
            'name' => 'G131-G135',
            'type' => '2',
            'qty' => '5',
            'level_id' => '3',
            'term_id' => '3',
            'status' => 1
        ]);
        bookuse::create([
            'name' => 'G136-G140',
            'type' => '2',
            'qty' => '6',
            'level_id' => '3',
            'term_id' => '3',
            'status' => 1
        ]);

        bookuse::create([
            'name' => 'G141-G150',
            'type' => '1',
            'qty' => '10',
            'level_id' => '3',
            'term_id' => '4',
            'status' => 1
        ]);
        bookuse::create([
            'name' => 'G141-G145',
            'type' => '2',
            'qty' => '5',
            'level_id' => '3',
            'term_id' => '4',
            'status' => 1
        ]);
        bookuse::create([
            'name' => 'G146-G150',
            'type' => '2',
            'qty' => '6',
            'level_id' => '3',
            'term_id' => '4',
            'status' => 1
        ]);

        //level 4
        bookuse::create([
            'name' => 'G211-G220',
            'type' => '1',
            'qty' => '10',
            'level_id' => '4',
            'term_id' => '1',
            'status' => 1
        ]);
        bookuse::create([
            'name' => 'G211-G215',
            'type' => '2',
            'qty' => '5',
            'level_id' => '4',
            'term_id' => '1',
            'status' => 1
        ]);
        bookuse::create([
            'name' => 'G216-G220',
            'type' => '2',
            'qty' => '6',
            'level_id' => '4',
            'term_id' => '1',
            'status' => 1
        ]);


        bookuse::create([
            'name' => 'G221-G230',
            'type' => '1',
            'qty' => '10',
            'level_id' => '4',
            'term_id' => '2',
            'status' => 1
        ]);
        bookuse::create([
            'name' => 'G221-G225',
            'type' => '2',
            'qty' => '5',
            'level_id' => '4',
            'term_id' => '2',
            'status' => 1
        ]);
        bookuse::create([
            'name' => 'G226-G230',
            'type' => '2',
            'qty' => '6',
            'level_id' => '4',
            'term_id' => '2',
            'status' => 1
        ]);

        bookuse::create([
            'name' => 'G231-G240',
            'type' => '1',
            'qty' => '10',
            'level_id' => '4',
            'term_id' => '3',
            'status' => 1
        ]);
        bookuse::create([
            'name' => 'G231-G235',
            'type' => '2',
            'qty' => '5',
            'level_id' => '4',
            'term_id' => '3',
            'status' => 1
        ]);
        bookuse::create([
            'name' => 'G236-G240',
            'type' => '2',
            'qty' => '6',
            'level_id' => '4',
            'term_id' => '3',
            'status' => 1
        ]);

        bookuse::create([
            'name' => 'G241-G250',
            'type' => '1',
            'qty' => '10',
            'level_id' => '4',
            'term_id' => '4',
            'status' => 1
        ]);
        bookuse::create([
            'name' => 'G241-G245',
            'type' => '2',
            'qty' => '5',
            'level_id' => '4',
            'term_id' => '4',
            'status' => 1
        ]);
        bookuse::create([
            'name' => 'G246-G250',
            'type' => '2',
            'qty' => '6',
            'level_id' => '4',
            'term_id' => '4',
            'status' => 1
        ]);

        //level 5

        bookuse::create([
            'name' => 'G311-G320',
            'type' => '1',
            'qty' => '10',
            'level_id' => '5',
            'term_id' => '1',
            'status' => 1
        ]);
        bookuse::create([
            'name' => 'G311-G315',
            'type' => '2',
            'qty' => '5',
            'level_id' => '5',
            'term_id' => '1',
            'status' => 1
        ]);
        bookuse::create([
            'name' => 'G316-G320',
            'type' => '2',
            'qty' => '6',
            'level_id' => '5',
            'term_id' => '1',
            'status' => 1
        ]);


        bookuse::create([
            'name' => 'G321-G330',
            'type' => '1',
            'qty' => '10',
            'level_id' => '5',
            'term_id' => '2',
            'status' => 1
        ]);
        bookuse::create([
            'name' => 'G321-G325',
            'type' => '2',
            'qty' => '5',
            'level_id' => '5',
            'term_id' => '2',
            'status' => 1
        ]);
        bookuse::create([
            'name' => 'G326-G330',
            'type' => '2',
            'qty' => '6',
            'level_id' => '5',
            'term_id' => '2',
            'status' => 1
        ]);

        bookuse::create([
            'name' => 'G331-G340',
            'type' => '1',
            'qty' => '10',
            'level_id' => '5',
            'term_id' => '3',
            'status' => 1
        ]);
        bookuse::create([
            'name' => 'G331-G335',
            'type' => '2',
            'qty' => '5',
            'level_id' => '5',
            'term_id' => '3',
            'status' => 1
        ]);
        bookuse::create([
            'name' => 'G336-G340',
            'type' => '2',
            'qty' => '6',
            'level_id' => '5',
            'term_id' => '3',
            'status' => 1
        ]);

        bookuse::create([
            'name' => 'G341-G350',
            'type' => '1',
            'qty' => '10',
            'level_id' => '5',
            'term_id' => '4',
            'status' => 1
        ]);
        bookuse::create([
            'name' => 'G341-G345',
            'type' => '2',
            'qty' => '5',
            'level_id' => '5',
            'term_id' => '4',
            'status' => 1
        ]);
        bookuse::create([
            'name' => 'G446-G350',
            'type' => '2',
            'qty' => '6',
            'level_id' => '5',
            'term_id' => '4',
            'status' => 1
        ]);

        //level 6

        bookuse::create([
            'name' => 'G411-G420',
            'type' => '1',
            'qty' => '10',
            'level_id' => '6',
            'term_id' => '1',
            'status' => 1
        ]);
        bookuse::create([
            'name' => 'G411-G415',
            'type' => '2',
            'qty' => '5',
            'level_id' => '6',
            'term_id' => '1',
            'status' => 1
        ]);
        bookuse::create([
            'name' => 'G416-G420',
            'type' => '2',
            'qty' => '6',
            'level_id' => '6',
            'term_id' => '1',
            'status' => 1
        ]);


        bookuse::create([
            'name' => 'G421-G430',
            'type' => '1',
            'qty' => '10',
            'level_id' => '6',
            'term_id' => '2',
            'status' => 1
        ]);
        bookuse::create([
            'name' => 'G421-G425',
            'type' => '2',
            'qty' => '5',
            'level_id' => '6',
            'term_id' => '2',
            'status' => 1
        ]);
        bookuse::create([
            'name' => 'G426-G430',
            'type' => '2',
            'qty' => '6',
            'level_id' => '6',
            'term_id' => '2',
            'status' => 1
        ]);

        bookuse::create([
            'name' => 'G431-G440',
            'type' => '1',
            'qty' => '10',
            'level_id' => '6',
            'term_id' => '3',
            'status' => 1
        ]);
        bookuse::create([
            'name' => 'G431-G435',
            'type' => '2',
            'qty' => '5',
            'level_id' => '6',
            'term_id' => '3',
            'status' => 1
        ]);
        bookuse::create([
            'name' => 'G436-G440',
            'type' => '2',
            'qty' => '6',
            'level_id' => '6',
            'term_id' => '3',
            'status' => 1
        ]);

        bookuse::create([
            'name' => 'G441-G450',
            'type' => '1',
            'qty' => '10',
            'level_id' => '6',
            'term_id' => '4',
            'status' => 1
        ]);
        bookuse::create([
            'name' => 'G441-G445',
            'type' => '2',
            'qty' => '5',
            'level_id' => '6',
            'term_id' => '4',
            'status' => 1
        ]);
        bookuse::create([
            'name' => 'G446-G450',
            'type' => '2',
            'qty' => '6',
            'level_id' => '6',
            'term_id' => '4',
            'status' => 1
        ]);

        //level 7

        bookuse::create([
            'name' => 'G511-G520',
            'type' => '1',
            'qty' => '10',
            'level_id' => '7',
            'term_id' => '1',
            'status' => 1
        ]);
        bookuse::create([
            'name' => 'G511-G515',
            'type' => '2',
            'qty' => '5',
            'level_id' => '7',
            'term_id' => '1',
            'status' => 1
        ]);
        bookuse::create([
            'name' => 'G516-G520',
            'type' => '2',
            'qty' => '6',
            'level_id' => '7',
            'term_id' => '1',
            'status' => 1
        ]);


        bookuse::create([
            'name' => 'G521-G530',
            'type' => '1',
            'qty' => '10',
            'level_id' => '7',
            'term_id' => '2',
            'status' => 1
        ]);
        bookuse::create([
            'name' => 'G521-G525',
            'type' => '2',
            'qty' => '5',
            'level_id' => '7',
            'term_id' => '2',
            'status' => 1
        ]);
        bookuse::create([
            'name' => 'G526-G530',
            'type' => '2',
            'qty' => '6',
            'level_id' => '7',
            'term_id' => '2',
            'status' => 1
        ]);

        bookuse::create([
            'name' => 'G531-G540',
            'type' => '1',
            'qty' => '10',
            'level_id' => '7',
            'term_id' => '3',
            'status' => 1
        ]);
        bookuse::create([
            'name' => 'G531-G535',
            'type' => '2',
            'qty' => '5',
            'level_id' => '7',
            'term_id' => '3',
            'status' => 1
        ]);
        bookuse::create([
            'name' => 'G536-G540',
            'type' => '2',
            'qty' => '6',
            'level_id' => '7',
            'term_id' => '3',
            'status' => 1
        ]);

        bookuse::create([
            'name' => 'G541-G550',
            'type' => '1',
            'qty' => '10',
            'level_id' => '7',
            'term_id' => '4',
            'status' => 1
        ]);
        bookuse::create([
            'name' => 'G541-G545',
            'type' => '2',
            'qty' => '5',
            'level_id' => '7',
            'term_id' => '4',
            'status' => 1
        ]);
        bookuse::create([
            'name' => 'G546-G550',
            'type' => '2',
            'qty' => '6',
            'level_id' => '7',
            'term_id' => '4',
            'status' => 1
        ]);

         //level 8

         bookuse::create([
            'name' => 'G611-G620',
            'type' => '1',
            'qty' => '10',
            'level_id' => '8',
            'term_id' => '1',
            'status' => 1
        ]);
        bookuse::create([
            'name' => 'G611-G615',
            'type' => '2',
            'qty' => '5',
            'level_id' => '8',
            'term_id' => '1',
            'status' => 1
        ]);
        bookuse::create([
            'name' => 'G616-G620',
            'type' => '2',
            'qty' => '6',
            'level_id' => '8',
            'term_id' => '1',
            'status' => 1
        ]);


        bookuse::create([
            'name' => 'G621-G630',
            'type' => '1',
            'qty' => '10',
            'level_id' => '8',
            'term_id' => '2',
            'status' => 1
        ]);
        bookuse::create([
            'name' => 'G621-G625',
            'type' => '2',
            'qty' => '5',
            'level_id' => '8',
            'term_id' => '2',
            'status' => 1
        ]);
        bookuse::create([
            'name' => 'G626-G630',
            'type' => '2',
            'qty' => '6',
            'level_id' => '8',
            'term_id' => '2',
            'status' => 1
        ]);

        bookuse::create([
            'name' => 'G631-G640',
            'type' => '1',
            'qty' => '10',
            'level_id' => '8',
            'term_id' => '3',
            'status' => 1
        ]);
        bookuse::create([
            'name' => 'G631-G635',
            'type' => '2',
            'qty' => '5',
            'level_id' => '8',
            'term_id' => '3',
            'status' => 1
        ]);
        bookuse::create([
            'name' => 'G636-G640',
            'type' => '2',
            'qty' => '5',
            'level_id' => '8',
            'term_id' => '3',
            'status' => 1
        ]);


    }
}
