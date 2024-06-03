<?php

namespace App\Helpers;

use App\Models\Topic;
use App\Models\Course;
use App\Models\Chapter;
use App\Models\College;
use App\Models\Question;
use App\Models\Department;
use App\Enums\LanguageEnum;
use App\Enums\CoursePartsEnum;
use App\Enums\LevelsCountEnum;
use App\Enums\ChoiceStatusEnum;
use App\Enums\QuestionTypeEnum;
use TheSeer\Tokenizer\Exception;

class InitialDatabaseHelper
{

    public static function colleges()
    {
        $colleges = [
            [
                'arabic_name' => 'الهندسة وتقنية المعلومات',
                'english_name' => 'Engineering and Infromation Technology',
                'phone' => 777111222,
                'email' => 'engineering@gmail.com'
            ]
        ];
        foreach ($colleges as $college) {
            College::create([
                'arabic_name' => $college['arabic_name'],
                'english_name' => $college['english_name'],
                'phone' => $college['phone'] ?? null,
                'email' => $college['email'] ?? null,
                'description' => $college['description'] ?? null,
                'facebook' => $college['facebook'] ?? null,
                'youtube' => $college['youtube'] ?? null,
                'x_platform' => $college['x_platform'] ?? null,
                'telegram' => $college['telegram'] ?? null,
                'logo_url' => ImageHelper::uploadImage($college['logo'])
            ]);
        }
    }

    public static function departments()
    {
        $departments = [
            [
                'college_id' => 1,
                'arabic_name' => 'تقنية المعلومات',
                'english_name' => 'Infromation Technology',
                'levels_count' => LevelsCountEnum::FIVE->value,
            ],
            [
                'college_id' => 1,
                'arabic_name' => 'هندسة البرمجيات',
                'english_name' => 'Software Engineering',
                'levels_count' => LevelsCountEnum::FIVE->value,
            ],
            [
                'college_id' => 1,
                'arabic_name' => 'اختبارات القبول',
                'english_name' => 'Acceptance Exams',
                'levels_count' => LevelsCountEnum::TWO->value,
            ]
        ];
        foreach ($departments as $department) {
            Department::create([
                'college_id' => $department['college_id'],
                'arabic_name' => $department['arabic_name'],
                'english_name' => $department['english_name'],
                'levels_count' => $department['levels_count'],
                'description' => $department['description'] ?? null,
                'logo_url' => ImageHelper::uploadImage($department['logo'])
            ]);
        }

        // foreach ($departments as $department) {
        //     $college = College::findOrFail($department['college_id']);
        //     $college->departments()->create([
        //         'arabic_name' => $department['arabic_name'],
        //         'english_name' => $department['english_name'],
        //         'levels_count' => $department['levels_count'],
        //         'description' => $department['description'] ?? null,
        //         'logo_url' => ImageHelper::uploadImage($department['logo'])
        //     ]);
        // }
    }

    public static function courses()
    {
        $courses = [
            [
                'arabic_name' => 'ذكاء صناعي',
                'english_name' => 'Artificial Intelligent',
            ],
            [
                'arabic_name' => 'شبكات عصبية',
                'english_name' => 'Neural Network',
            ],
            [
                'arabic_name' => 'لغة انجليزية اختبار قبول',
                'english_name' => 'english language for acceptance exam',
            ],
        ];
        foreach ($courses as $course) {
            Course::create([
                'arabic_name' => $course['arabic_name'],
                'english_name' => $course['english_name'],
            ]);
        }
    }

    public static function courseParts()
    {
        $courses = [
            [
                'part_id' => CoursePartsEnum::THEORETICAL->value,
                'description' => 'this part for acceptance exam',
            ],
            [
                'part_id' => CoursePartsEnum::EXERCISES->value,
                'description' => 'this part for .....',
            ],
            [
                'part_id' => CoursePartsEnum::PRACTICAL->value,
                'description' => 'this part for ......',
            ],
        ];

        // foreach ($courses as $course) {
        //     Course::create([
        //         'arabic_name' => $course['arabic_name'],
        //         'english_name' => $course['english_name'],
        //     ]);
        // }
    }

    public static function chapters()
    {
        $chapters = [
            [
                'course_part_id' => 1,
                'arabic_title' => '1الفصل',
                'english_title' => 'chapter1',
            ],
            [
                'course_part_id' => 1,
                'arabic_title' => '2الفصل',
                'english_title' => 'chapter2',
            ],
            [
                'course_part_id' => 1,
                'arabic_title' => '3الفصل',
                'english_title' => 'chapter3',
            ],
            [
                'course_part_id' => 1,
                'arabic_title' => '4الفصل',
                'english_title' => 'chapter4',
            ],
            [
                'course_part_id' => 1,
                'arabic_title' => '5الفصل',
                'english_title' => 'chapter5',
            ],
            [
                'course_part_id' => 1,
                'arabic_title' => '6الفصل',
                'english_title' => 'chapter6',
            ],
            [
                'course_part_id' => 1,
                'arabic_title' => '7الفصل',
                'english_title' => 'chapter7',
            ],
            [
                'course_part_id' => 1,
                'arabic_title' => '8الفصل',
                'english_title' => 'chapter8',
            ],
            [
                'course_part_id' => 1,
                'arabic_title' => '9الفصل',
                'english_title' => 'chapter9',
            ],

        ];
        foreach ($chapters as $chapter) {
            Chapter::create([
                'course_part_id' => $chapter['course_part_id'],
                'arabic_title' => $chapter['arabic_title'],
                'english_title' => $chapter['english_title'],
                'description' => $chapter['description'] ?? null,
            ]);
        }
    }

    public static function topics()
    {
        $topics = [
            [
                'chapter_id' => 2,
                'arabic_title' => 'الموضوع1',
                'english_title' => 'topic1',
            ],
            [
                'chapter_id' => 2,
                'arabic_title' => 'الموضوع2',
                'english_title' => 'topic2',
            ],
            [
                'chapter_id' => 2,
                'arabic_title' => 'الموضوع3',
                'english_title' => 'topic3',
            ],
            [
                'chapter_id' => 3,
                'arabic_title' => 'الموضوع1',
                'english_title' => 'topic1',
            ],
            [
                'chapter_id' => 3,
                'arabic_title' => 'الموضوع2',
                'english_title' => 'topic2',
            ],
            [
                'chapter_id' => 3,
                'arabic_title' => 'الموضوع3',
                'english_title' => 'topic3',
            ],
            [
                'chapter_id' => 4,
                'arabic_title' => 'الموضوع1',
                'english_title' => 'topic1',
            ],
            [
                'chapter_id' => 4,
                'arabic_title' => 'الموضوع2',
                'english_title' => 'topic2',
            ],
            [
                'chapter_id' => 4,
                'arabic_title' => 'الموضوع3',
                'english_title' => 'topic3',
            ],
            [
                'chapter_id' => 5,
                'arabic_title' => 'الموضوع1',
                'english_title' => 'topic1',
            ],
            [
                'chapter_id' => 5,
                'arabic_title' => 'الموضوع2',
                'english_title' => 'topic2',
            ],
            [
                'chapter_id' => 5,
                'arabic_title' => 'الموضوع3',
                'english_title' => 'topic3',
            ],
            [
                'chapter_id' => 6,
                'arabic_title' => 'الموضوع1',
                'english_title' => 'topic1',
            ],
            [
                'chapter_id' => 6,
                'arabic_title' => 'الموضوع2',
                'english_title' => 'topic2',
            ],
            [
                'chapter_id' => 6,
                'arabic_title' => 'الموضوع3',
                'english_title' => 'topic3',
            ],
            [
                'chapter_id' => 7,
                'arabic_title' => 'الموضوع1',
                'english_title' => 'topic1',
            ],
            [
                'chapter_id' => 7,
                'arabic_title' => 'الموضوع2',
                'english_title' => 'topic2',
            ],
            [
                'chapter_id' => 7,
                'arabic_title' => 'الموضوع3',
                'english_title' => 'topic3',
            ],
            [
                'chapter_id' => 8,
                'arabic_title' => 'الموضوع1',
                'english_title' => 'topic1',
            ],
            [
                'chapter_id' => 8,
                'arabic_title' => 'الموضوع2',
                'english_title' => 'topic2',
            ],
            [
                'chapter_id' => 8,
                'arabic_title' => 'الموضوع3',
                'english_title' => 'topic3',
            ],
            [
                'chapter_id' => 9,
                'arabic_title' => 'الموضوع1',
                'english_title' => 'topic1',
            ],
            [
                'chapter_id' => 9,
                'arabic_title' => 'الموضوع2',
                'english_title' => 'topic2',
            ],
            [
                'chapter_id' => 9,
                'arabic_title' => 'الموضوع3',
                'english_title' => 'topic3',
            ],
            [
                'chapter_id' => 10,
                'arabic_title' => 'الموضوع1',
                'english_title' => 'topic1',
            ],
            [
                'chapter_id' => 10,
                'arabic_title' => 'الموضوع2',
                'english_title' => 'topic2',
            ],
            [
                'chapter_id' => 10,
                'arabic_title' => 'الموضوع3',
                'english_title' => 'topic3',
            ],

        ];

        foreach ($topics as $topic) {
            Topic::create([
                'chapter_id' => $topic['chapter_id'],
                'arabic_title' => $topic['arabic_title'],
                'english_title' => $topic['english_title'],
                'description' => $topic['description'] ?? null,
            ]);
        }
    }

    public static function questions()
    {
        // topics [1.. 27]
        // question type : true false 0, choice 1
        // difficulty_level [0...4]
        // accessbility_status [0...2]
        // language : english = 1
        // estimated_answer_time: float [1, 10]
        // 
        $raws = self::ReadDataFromJson();
        // return $raws;
        $temp = [];
       
            foreach ($raws as $raw) {
                $topic = Topic::findOrFail(self::selectRandomTopic());
                // return $topic;
                $question = $topic->questions()->create([
                    'type' => QuestionTypeEnum::MULTIPLE_CHOICE->value, 
                    'difficulty_level' => 2.12,//self::selectRandomDifficultyLevel(), 
                    'accessbility_status' => 1,//self::selectRandomAccessbilityStatus(), 
                    'language' => LanguageEnum::ARABIC->value, 
                    'estimated_answer_time' => 180,//self::selectRandomEstimatedAnswerTime(), 
                    'content' => $raw['content'], 
                    'attachment' => null,
                    'title' => null,
                ]);

                // $question = Question::create([
                //     'topic_id' => self::selectRandomTopic(), 
                //     'type' => QuestionTypeEnum::MULTIPLE_CHOICE->value, 
                //     'difficulty_level' => self::selectRandomDifficultyLevel(), 
                //     'accessbility_status' => self::selectRandomAccessbilityStatus(), 
                //     'language' => LanguageEnum::ARABIC->value, 
                //     'estimated_answer_time' => self::selectRandomEstimatedAnswerTime(), 
                //     'content' => $raw['content'], 
                // ]);

                return $question;
                // self::saveQuestionChoices($question, $raw['choices']);

            // $question = [
            //     'id' => $raw['id'],
            //     'topic_id' => self::selectRandomTopic(),
            //     'type' => QuestionTypeEnum::MULTIPLE_CHOICE->value,
            //     'difficulty_level' => self::selectRandomDifficultyLevel(),
            //     'accessbility_status' => self::selectRandomAccessbilityStatus(),
            //     'language' => LanguageEnum::ARABIC->value,
            //     'estimated_answer_time' => self::selectRandomEstimatedAnswerTime(),
            //     'content' => $raw['content'],
            // ];
            // array_push($temp, $question);
            }

        return $temp;
    }

    private static function ReadDataFromJson()
    {
        $filePath = base_path() . '/app/AlgorithmAPI/PythonModules/combinationGeneratorAPI/questions.json';
        // $filePath =  __DIR__ . '/app/AlgorithmAPI/PythonModules/combinationGeneratorAPI/questions.json';

        if (!file_exists($filePath)) {
            throw new Exception("File not found: " . $filePath);
        }

        $jsonContents = file_get_contents($filePath);
        $data = json_decode($jsonContents, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Error decoding JSON: " . json_last_error_msg());
        }

        return $data;
    }
    private static function selectRandomTopic(): int
    {
        $topics = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27];
        $randomIndex = array_rand($topics);
        return $topics[$randomIndex];
    }
    private static function selectRandomDifficultyLevel(): int
    {
        $difficultyLevels = [0.0, 1.0, 2.0, 3.0, 4.0];
        $randomIndex = array_rand($difficultyLevels);
        return $difficultyLevels[$randomIndex];
    }
    private static function selectRandomAccessbilityStatus(): int
    {
        $accessibilityStatuses = [0, 1, 2];
        $randomIndex = array_rand($accessibilityStatuses);
        return $accessibilityStatuses[$randomIndex];
    }
    private static function selectRandomEstimatedAnswerTime(): float
    {
        // select randomly int number
        // this number represent time in second. 
        // the selected time must be >= 1 minute and <= 10 minute 

        $minSeconds = 1 * 60; // 1 minute in seconds
        $maxSeconds = 10 * 60; // 10 minutes in seconds
        $randomSeconds = mt_rand($minSeconds, $maxSeconds);
        return $randomSeconds;

    }
    private static function saveQuestionChoices(Question $question, $choices)
    {
        foreach ($choices as $choice) {
            $question =  $question->choices()->create([
                'content' => $choice['content'],
                'status' => ($choice['isCorrect']) ? ChoiceStatusEnum::CORRECT_ANSWER->value : ChoiceStatusEnum::INCORRECT_ANSWER->value
            ]);
        }
    }
}
