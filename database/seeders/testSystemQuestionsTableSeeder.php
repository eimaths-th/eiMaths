<?php

namespace Database\Seeders;

use App\Models\TestSystemQuestions;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class testSystemQuestionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TestSystemQuestions::create([
            'question_text' => 'What is 1/2 + 1/4?',
            'answer_numerator' => 3,
            'answer_denominator' => 4,
        ]);

        TestSystemQuestions::create([
            'question_text' => 'What is 5/8 - 3/8?',
            'answer_numerator' => 1,
            'answer_denominator' => 4,
        ]);
    }
}
