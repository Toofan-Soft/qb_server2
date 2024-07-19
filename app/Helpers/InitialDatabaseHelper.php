<?php

namespace App\Helpers;

use App\Models\Topic;
use App\Models\Course;
use App\Models\Chapter;
use App\Models\College;
use App\Models\Question;
use App\Models\RealExam;
use App\Models\CoursePart;
use App\Models\Department;
use App\Models\OnlineExam;
use App\Enums\LanguageEnum;
use App\Enums\ExamStatusEnum;
use App\Enums\CoursePartsEnum;
use App\Enums\LevelsCountEnum;
use App\Enums\ChoiceStatusEnum;
use App\Enums\QuestionTypeEnum;
use App\Enums\RealExamTypeEnum;
use TheSeer\Tokenizer\Exception;
use App\Enums\QuestionStatusEnum;
use App\Enums\TrueFalseAnswerEnum;

class InitialDatabaseHelper
{

    public static function colleges()
    {
        $colleges = [
            [
                'arabic_name' => 'الطب',
                'english_name' => 'Medicine',
                'phone' => '777333444',
                'email' => 'medicine@gmail.com',
                'description' => 'College of Medicine.',
                'facebook' => 'https://www.facebook.com/medicine',
                'youtube' => 'https://www.youtube.com/channel/medicine',
                'x_platform' => 'https://twitter.com/medicine',
                'telegram' => 'https://t.me/medicine',
                'logo' => 'path/to/medicine/logo.jpg'
            ],
            [
                'arabic_name' => 'الهندسة وتقنية المعلومات',
                'english_name' => 'Engineering and Information Technology',
                'phone' => '777111222',
                'email' => 'engineering@gmail.com',
                'description' => 'College of Engineering and Information Technology.',
                'facebook' => 'https://www.facebook.com/engineering',
                'youtube' => 'https://www.youtube.com/channel/engineering',
                'x_platform' => 'https://twitter.com/engineering',
                'telegram' => 'https://t.me/engineering',
                'logo' => 'path/to/engineering/logo.jpg'
            ],
            [
                'arabic_name' => 'العلوم الادارية',
                'english_name' => 'Administrative Sciences',
                'phone' => '777555666',
                'email' => 'admin_sciences@gmail.com',
                'description' => 'College of Administrative Sciences.',
                'facebook' => 'https://www.facebook.com/admin_sciences',
                'youtube' => 'https://www.youtube.com/channel/admin_sciences',
                'x_platform' => 'https://twitter.com/admin_sciences',
                'telegram' => 'https://t.me/admin_sciences',
                'logo' => 'path/to/admin_sciences/logo.jpg'
            ],
            [
                'arabic_name' => 'التربية',
                'english_name' => 'Education',
                'phone' => '777777888',
                'email' => 'education@gmail.com',
                'description' => 'College of Education.',
                'facebook' => 'https://www.facebook.com/education',
                'youtube' => 'https://www.youtube.com/channel/education',
                'x_platform' => 'https://twitter.com/education',
                'telegram' => 'https://t.me/education',
                'logo' => 'path/to/education/logo.jpg'
            ],
            [
                'arabic_name' => 'الحاسوب',
                'english_name' => 'Computer Science',
                'phone' => '777999000',
                'email' => 'computer_science@gmail.com',
                'description' => 'College of Computer Science.',
                'facebook' => 'https://www.facebook.com/computer_science',
                'youtube' => 'https://www.youtube.com/channel/computer_science',
                'x_platform' => 'https://twitter.com/computer_science',
                'telegram' => 'https://t.me/computer_science',
                'logo' => 'path/to/computer_science/logo.jpg'
            ],
            [
                'arabic_name' => 'العلوم التطبيقية',
                'english_name' => 'Applied Sciences',
                'phone' => '777123456',
                'email' => 'applied_sciences@gmail.com',
                'description' => 'College of Applied Sciences.',
                'facebook' => 'https://www.facebook.com/applied_sciences',
                'youtube' => 'https://www.youtube.com/channel/applied_sciences',
                'x_platform' => 'https://twitter.com/applied_sciences',
                'telegram' => 'https://t.me/applied_sciences',
                'logo' => 'path/to/applied_sciences/logo.jpg'
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
                'logo_url' => isset($college['logo']) ? ImageHelper::uploadImage($college['logo']) : null
            ]);
        }
    }


    public static function departments()
    {
        $departments = [
            [
                'college_id' => 1,
                'arabic_name' => 'الجراحة',
                'english_name' => 'Surgery',
                'levels_count' => 5,
                'description' => 'Department of Surgery.',
                'logo' => 'path/to/surgery/logo.jpg'
            ],
            [
                'college_id' => 1,
                'arabic_name' => 'الطب الباطني',
                'english_name' => 'Internal Medicine',
                'levels_count' => 5,
                'description' => 'Department of Internal Medicine.',
                'logo' => 'path/to/internal_medicine/logo.jpg'
            ],
            [
                'college_id' => 1,
                'arabic_name' => 'طب الأطفال',
                'english_name' => 'Pediatrics',
                'levels_count' => 5,
                'description' => 'Department of Pediatrics.',
                'logo' => 'path/to/pediatrics/logo.jpg'
            ],
            [
                'college_id' => 1,
                'arabic_name' => 'طب النساء والتوليد',
                'english_name' => 'Obstetrics and Gynecology',
                'levels_count' => 5,
                'description' => 'Department of Obstetrics and Gynecology.',
                'logo' => 'path/to/obgyn/logo.jpg'
            ],
            [
                'college_id' => 1,
                'arabic_name' => 'الطب النفسي',
                'english_name' => 'Psychiatry',
                'levels_count' => 5,
                'description' => 'Department of Psychiatry.',
                'logo' => 'path/to/psychiatry/logo.jpg'
            ],
            [
                'college_id' => 2,
                'arabic_name' => 'البرمجيات',
                'english_name' => 'Software Engineering',
                'levels_count' => 5,
                'description' => 'Department of Software Engineering.',
                'logo' => 'path/to/software/logo.jpg'
            ],
            [
                'college_id' => 2,
                'arabic_name' => 'تقنية المعلومات',
                'english_name' => 'Information Technology',
                'levels_count' => 5,
                'description' => 'Department of Information Technology.',
                'logo' => 'path/to/information_technology/logo.jpg'
            ],
            [
                'college_id' => 2,
                'arabic_name' => 'الشبكات',
                'english_name' => 'Networking',
                'levels_count' => 5,
                'description' => 'Department of Networking.',
                'logo' => 'path/to/networking/logo.jpg'
            ],
            [
                'college_id' => 2,
                'arabic_name' => 'الاتصالات',
                'english_name' => 'Communications',
                'levels_count' => 5,
                'description' => 'Department of Communications.',
                'logo' => 'path/to/communications/logo.jpg'
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
                'arabic_name' => 'اللغة العربية',
                'english_name' => 'Arabic Language',
            ],
            [
                'arabic_name' => 'الانجليزي',
                'english_name' => 'English Language',
            ],
            [
                'arabic_name' => 'ثقافة اسلامية',
                'english_name' => 'Islamic Culture',
            ],
            [
                'arabic_name' => 'اساسيات البرمجة',
                'english_name' => 'Programming Fundamentals',
            ],
            [
                'arabic_name' => 'الرياضيات',
                'english_name' => 'Mathematics',
            ],
            [
                'arabic_name' => 'الفيزياء',
                'english_name' => 'Physics',
            ],
            [
                'arabic_name' => 'ذكاء صناعي',
                'english_name' => 'Artificial Intelligence',
            ],
            [
                'arabic_name' => 'شبكات عصبية',
                'english_name' => 'Neural Network',
            ],
            [
                'arabic_name' => 'لغة انجليزية اختبار قبول',
                'english_name' => 'English Language for Acceptance Exam',
            ],
            [
                'arabic_name' => 'هندسة طبية حيوية',
                'english_name' => 'Biomedical Engineering',
            ],
            [
                'arabic_name' => 'علم الأدوية',
                'english_name' => 'Pharmacology',
            ],
            [
                'arabic_name' => 'إدارة أنظمة صحية',
                'english_name' => 'Health Systems Management',
            ],
            [
                'arabic_name' => 'ميكانيكا حيوية',
                'english_name' => 'Biomechanics',
            ],
            [
                'arabic_name' => 'معالجة الصور الطبية',
                'english_name' => 'Medical Image Processing',
            ],
            [
                'arabic_name' => 'الروبوتات الجراحية',
                'english_name' => 'Surgical Robotics',
            ],
            [
                'arabic_name' => 'علم التشريح',
                'english_name' => 'Anatomy',
            ],
            [
                'arabic_name' => 'أجهزة طبية',
                'english_name' => 'Medical Devices',
            ],
            [
                'arabic_name' => 'هندسة أنسجة',
                'english_name' => 'Tissue Engineering',
            ]

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
        $courseParts = [
            [
                'course_id' => 1,
                'part_id' => CoursePartsEnum::THEORETICAL->value,
                'description' => 'this part for acceptance exam',
            ],
            [
                'course_id' => 1,
                'part_id' => CoursePartsEnum::EXERCISES->value,
                'description' => 'this part for .....',
            ],
            [
                'course_id' => 1,
                'part_id' => CoursePartsEnum::PRACTICAL->value,
                'description' => 'this part for ......',
            ],
            [
                'course_id' => 2,
                'part_id' => CoursePartsEnum::THEORETICAL->value,
                'description' => 'this part for acceptance exam',
            ],
            [
                'course_id' => 2,
                'part_id' => CoursePartsEnum::EXERCISES->value,
                'description' => 'this part for .....',
            ],
            [
                'course_id' => 2,
                'part_id' => CoursePartsEnum::PRACTICAL->value,
                'description' => 'this part for ......',
            ],
        ];

        foreach ($courseParts as $coursePart) {
            CoursePart::create([
                'course_id' => $coursePart['course_id'],
                'part_id' => $coursePart['part_id'],
                'description' => $coursePart['description'],
            ]);
        }
    }

    public static function chapters()
    {
        $chapters = [
            [
                'arabic_title' => 'مقدمة في البرمجة',
                'english_title' => 'Introduction to Programming',
                'description' => 'This chapter introduces the basics of programming.',
                'course_part_id' => 1, // Replace with the actual course part ID
            ],
            [
                'arabic_title' => 'أساسيات الرياضيات',
                'english_title' => 'Mathematics Fundamentals',
                'description' => 'This chapter covers the fundamental concepts of mathematics.',
                'course_part_id' => 2, // Replace with the actual course part ID
            ],
            [
                'arabic_title' => 'مبادئ الفيزياء',
                'english_title' => 'Principles of Physics',
                'description' => 'This chapter explains the basic principles of physics.',
                'course_part_id' => 3, // Replace with the actual course part ID
            ],
            [
                'arabic_title' => 'الثقافة الإسلامية',
                'english_title' => 'Islamic Culture',
                'description' => 'This chapter discusses the essentials of Islamic culture.',
                'course_part_id' => 4, // Replace with the actual course part ID
            ],
            [
                'arabic_title' => 'أساسيات اللغة العربية',
                'english_title' => 'Arabic Language Basics',
                'description' => 'This chapter covers the basics of the Arabic language.',
                'course_part_id' => 5, // Replace with the actual course part ID
            ],
            [
                'arabic_title' => 'أساسيات اللغة الإنجليزية',
                'english_title' => 'English Language Basics',
                'description' => 'This chapter covers the basics of the English language.',
                'course_part_id' => 6, // Replace with the actual course part ID
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
                'chapter_id' => 1,
                'arabic_title' => 'مقدمة في لغة البرمجة',
                'english_title' => 'Introduction to Programming Language',
                'description' => 'An overview of programming languages and their importance.',
            ],
            [
                'chapter_id' => 1,
                'arabic_title' => 'تاريخ البرمجة',
                'english_title' => 'History of Programming',
                'description' => 'A brief history of programming from its origins to modern languages.',
            ],
            [
                'chapter_id' => 1,
                'arabic_title' => 'أساسيات البرمجة',
                'english_title' => 'Programming Basics',
                'description' => 'Fundamental concepts in programming such as variables, data types, and control structures.',
            ],
            [
                'chapter_id' => 1,
                'arabic_title' => 'التطبيقات الحديثة للبرمجة',
                'english_title' => 'Modern Applications of Programming',
                'description' => 'Exploring how programming is used in various modern applications.',
            ],

            // Topics for Chapter 2: Mathematics Fundamentals
            [
                'chapter_id' => 2,
                'arabic_title' => 'أساسيات الجبر',
                'english_title' => 'Fundamentals of Algebra',
                'description' => 'Basic algebraic concepts and operations.',
            ],
            [
                'chapter_id' => 2,
                'arabic_title' => 'الهندسة الفراغية',
                'english_title' => 'Geometry',
                'description' => 'Introduction to geometric shapes, angles, and measurements.',
            ],
            [
                'chapter_id' => 2,
                'arabic_title' => 'التفاضل والتكامل',
                'english_title' => 'Calculus',
                'description' => 'Fundamentals of differentiation and integration.',
            ],
            [
                'chapter_id' => 2,
                'arabic_title' => 'الإحصاء والاحتمالات',
                'english_title' => 'Statistics and Probability',
                'description' => 'Basic principles of statistics and probability theory.',
            ],

            // Topics for Chapter 3: Principles of Physics
            [
                'chapter_id' => 3,
                'arabic_title' => 'الحركة والسرعة',
                'english_title' => 'Motion and Velocity',
                'description' => 'Basic concepts of motion, velocity, and acceleration.',
            ],
            [
                'chapter_id' => 3,
                'arabic_title' => 'القوى والحركة الدائرية',
                'english_title' => 'Forces and Circular Motion',
                'description' => 'Newtonian mechanics, forces, and circular motion.',
            ],
            [
                'chapter_id' => 3,
                'arabic_title' => 'الطاقة والعمل',
                'english_title' => 'Energy and Work',
                'description' => 'Concepts of energy, work, and conservation laws in physics.',
            ],
            [
                'chapter_id' => 3,
                'arabic_title' => 'الكهرباء والمغناطيسية',
                'english_title' => 'Electricity and Magnetism',
                'description' => 'Basic principles of electricity, magnetism, and electromagnetic waves.',
            ],

            // Topics for Chapter 4: Islamic Culture
            [
                'chapter_id' => 4,
                'arabic_title' => 'القيم الإسلامية',
                'english_title' => 'Islamic Values',
                'description' => 'Core values and principles in Islamic culture.',
            ],
            [
                'chapter_id' => 4,
                'arabic_title' => 'التاريخ الإسلامي',
                'english_title' => 'Islamic History',
                'description' => 'Key events and figures in Islamic history.',
            ],
            [
                'chapter_id' => 4,
                'arabic_title' => 'الفقه الإسلامي',
                'english_title' => 'Islamic Jurisprudence (Fiqh)',
                'description' => 'Fundamental principles and practices of Islamic jurisprudence.',
            ],
            [
                'chapter_id' => 4,
                'arabic_title' => 'الفلسفة الإسلامية',
                'english_title' => 'Islamic Philosophy',
                'description' => 'Overview of philosophical thought in Islamic tradition.',
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

        $englishQuestions = [
            // Questions for Topic 1
            [
                'topic_id' => 1,
                'type' => 0, // true/false question
                'content' => 'Is programming essential for modern technology?',
                'is_true' => true,
            ],
            [
                'topic_id' => 1,
                'type' => 0, // true/false question
                'content' => 'Is programming only about writing code?',
                'is_true' => false,
            ],
            [
                'topic_id' => 1,
                'type' => 0, // true/false question
                'content' => 'Is programming essential for modern technology?',
                'is_true' => true,
            ],
            [
                'topic_id' => 1,
                'type' => 0, // true/false question
                'content' => 'Is programming only about writing code?',
                'is_true' => false,
            ],
            [
                'topic_id' => 1,
                'type' => 0, // true/false question
                'content' => 'Does every programming language use the same syntax?',
                'is_true' => false,
            ],
            [
                'topic_id' => 1,
                'type' => 0, // true/false question
                'content' => 'Is Python a programming language?',
                'is_true' => true,
            ],
            [
                'topic_id' => 1,
                'type' => 0, // true/false question
                'content' => 'Can HTML be used to develop mobile apps?',
                'is_true' => false,
            ],
            [
                'topic_id' => 1,
                'type' => 0, // true/false question
                'content' => 'Is JavaScript used for both front-end and back-end development?',
                'is_true' => true,
            ],
            [
                'topic_id' => 1,
                'type' => 0, // true/false question
                'content' => 'Is C++ an object-oriented programming language?',
                'is_true' => true,
            ],
            [
                'topic_id' => 1,
                'type' => 0, // true/false question
                'content' => 'Does SQL stand for Structured Query Language?',
                'is_true' => true,
            ],
            [
                'topic_id' => 1,
                'type' => 0, // true/false question
                'content' => 'Can CSS be used to style a webpage?',
                'is_true' => true,
            ],
            [
                'topic_id' => 1,
                'type' => 0, // true/false question
                'content' => 'Is Java the same as JavaScript?',
                'is_true' => false,
            ],
            [
                'topic_id' => 1,
                'type' => 1, // multiple choice question
                'content' => 'Which of the following is a programming language?',
                'choices' => [
                    [
                        'content' => 'HTML',
                        'attachment' => null,
                        'status' => false,
                    ],
                    [
                        'content' => 'Python',
                        'attachment' => null,
                        'status' => true,
                    ],
                    [
                        'content' => 'CSS',
                        'attachment' => null,
                        'status' => false,
                    ],
                    [
                        'content' => 'SQL',
                        'attachment' => null,
                        'status' => false,
                    ]
                ]
            ],
            [
                'topic_id' => 1,
                'type' => 1, // multiple choice question
                'content' => 'Which of the following is a programming language?',
                'choices' => [
                    [
                        'content' => 'HTML',
                        'attachment' => null,
                        'status' => false,
                    ],
                    [
                        'content' => 'Python',
                        'attachment' => null,
                        'status' => true,
                    ],
                    [
                        'content' => 'CSS',
                        'attachment' => null,
                        'status' => false,
                    ],
                    [
                        'content' => 'SQL',
                        'attachment' => null,
                        'status' => false,
                    ]
                ]
            ],
            [
                'topic_id' => 1,
                'type' => 1, // multiple choice question
                'content' => 'Which language is primarily used for web development?',
                'choices' => [
                    [
                        'content' => 'Python',
                        'attachment' => null,
                        'status' => false,
                    ],
                    [
                        'content' => 'JavaScript',
                        'attachment' => null,
                        'status' => true,
                    ],
                    [
                        'content' => 'C++',
                        'attachment' => null,
                        'status' => false,
                    ],
                    [
                        'content' => 'Java',
                        'attachment' => null,
                        'status' => false,
                    ]
                ]
            ],
            [
                'topic_id' => 1,
                'type' => 1, // multiple choice question
                'content' => 'Which of the following is used for styling web pages?',
                'choices' => [
                    [
                        'content' => 'Python',
                        'attachment' => null,
                        'status' => false,
                    ],
                    [
                        'content' => 'HTML',
                        'attachment' => null,
                        'status' => false,
                    ],
                    [
                        'content' => 'CSS',
                        'attachment' => null,
                        'status' => true,
                    ],
                    [
                        'content' => 'SQL',
                        'attachment' => null,
                        'status' => false,
                    ]
                ]
            ],
            [
                'topic_id' => 1,
                'type' => 1, // multiple choice question
                'content' => 'Which of these is a database query language?',
                'choices' => [
                    [
                        'content' => 'Java',
                        'attachment' => null,
                        'status' => false,
                    ],
                    [
                        'content' => 'CSS',
                        'attachment' => null,
                        'status' => false,
                    ],
                    [
                        'content' => 'SQL',
                        'attachment' => null,
                        'status' => true,
                    ],
                    [
                        'content' => 'JavaScript',
                        'attachment' => null,
                        'status' => false,
                    ]
                ]
            ],
            [
                'topic_id' => 1,
                'type' => 1, // multiple choice question
                'content' => 'Which of the following is not a programming language?',
                'choices' => [
                    [
                        'content' => 'Ruby',
                        'attachment' => null,
                        'status' => false,
                    ],
                    [
                        'content' => 'HTML',
                        'attachment' => null,
                        'status' => false,
                    ],
                    [
                        'content' => 'C#',
                        'attachment' => null,
                        'status' => false,
                    ],
                    [
                        'content' => 'SQL',
                        'attachment' => null,
                        'status' => true,
                    ]
                ]
            ],


            // Questions for Topic 2

            [
                'topic_id' => 2,
                'type' => 0, // true/false question
                'content' => 'Is algebra a branch of mathematics?',
                'is_true' => true,
            ],
            [
                'topic_id' => 2,
                'type' => 0, // true/false question
                'content' => 'Is geometry only about shapes?',
                'is_true' => false,
            ],
            [
                'topic_id' => 2,
                'type' => 0, // true/false question
                'content' => 'Is algebra a branch of mathematics?',
                'is_true' => true,
            ],
            [
                'topic_id' => 2,
                'type' => 0, // true/false question
                'content' => 'Is geometry only about shapes?',
                'is_true' => false,
            ],
            [
                'topic_id' => 2,
                'type' => 0, // true/false question
                'content' => 'Can calculus be used to find the area under a curve?',
                'is_true' => true,
            ],
            [
                'topic_id' => 2,
                'type' => 0, // true/false question
                'content' => 'Is statistics only about collecting data?',
                'is_true' => false,
            ],
            [
                'topic_id' => 2,
                'type' => 0, // true/false question
                'content' => 'Is probability a part of statistics?',
                'is_true' => true,
            ],
            [
                'topic_id' => 2,
                'type' => 0, // true/false question
                'content' => 'Is trigonometry used to study triangles?',
                'is_true' => true,
            ],
            [
                'topic_id' => 2,
                'type' => 0, // true/false question
                'content' => 'Is differential equations a part of linear algebra?',
                'is_true' => false,
            ],
            [
                'topic_id' => 2,
                'type' => 0, // true/false question
                'content' => 'Is set theory used to study collections of objects?',
                'is_true' => true,
            ],
            [
                'topic_id' => 2,
                'type' => 0, // true/false question
                'content' => 'Is combinatorics the study of counting?',
                'is_true' => true,
            ],
            [
                'topic_id' => 2,
                'type' => 0, // true/false question
                'content' => 'Is complex analysis the study of complex numbers?',
                'is_true' => true,
            ],
            [
                'topic_id' => 2,
                'type' => 1, // multiple choice question
                'content' => 'Which of the following is a fundamental concept in algebra?',
                'choices' => [
                    [
                        'content' => 'Equation',
                        'attachment' => null,
                        'status' => true,
                    ],
                    [
                        'content' => 'Frequency',
                        'attachment' => null,
                        'status' => false,
                    ],
                    [
                        'content' => 'Amplitude',
                        'attachment' => null,
                        'status' => false,
                    ],
                    [
                        'content' => 'Wavelength',
                        'attachment' => null,
                        'status' => false,
                    ]
                ]
            ], [
                'topic_id' => 2,
                'type' => 1, // multiple choice question
                'content' => 'Which of the following is a fundamental concept in algebra?',
                'choices' => [
                    [
                        'content' => 'Equation',
                        'attachment' => null,
                        'status' => true,
                    ],
                    [
                        'content' => 'Frequency',
                        'attachment' => null,
                        'status' => false,
                    ],
                    [
                        'content' => 'Amplitude',
                        'attachment' => null,
                        'status' => false,
                    ],
                    [
                        'content' => 'Wavelength',
                        'attachment' => null,
                        'status' => false,
                    ]
                ]
            ],
            [
                'topic_id' => 2,
                'type' => 1, // multiple choice question
                'content' => 'Which branch of mathematics deals with shapes and their properties?',
                'choices' => [
                    [
                        'content' => 'Algebra',
                        'attachment' => null,
                        'status' => false,
                    ],
                    [
                        'content' => 'Geometry',
                        'attachment' => null,
                        'status' => true,
                    ],
                    [
                        'content' => 'Calculus',
                        'attachment' => null,
                        'status' => false,
                    ],
                    [
                        'content' => 'Statistics',
                        'attachment' => null,
                        'status' => false,
                    ]
                ]
            ],
            [
                'topic_id' => 2,
                'type' => 1, // multiple choice question
                'content' => 'Which field of mathematics is concerned with the study of change?',
                'choices' => [
                    [
                        'content' => 'Trigonometry',
                        'attachment' => null,
                        'status' => false,
                    ],
                    [
                        'content' => 'Calculus',
                        'attachment' => null,
                        'status' => true,
                    ],
                    [
                        'content' => 'Linear Algebra',
                        'attachment' => null,
                        'status' => false,
                    ],
                    [
                        'content' => 'Set Theory',
                        'attachment' => null,
                        'status' => false,
                    ]
                ]
            ],
            [
                'topic_id' => 2,
                'type' => 1, // multiple choice question
                'content' => 'Which branch of mathematics involves the study of angles and their relationships?',
                'choices' => [
                    [
                        'content' => 'Algebra',
                        'attachment' => null,
                        'status' => false,
                    ],
                    [
                        'content' => 'Trigonometry',
                        'attachment' => null,
                        'status' => true,
                    ],
                    [
                        'content' => 'Calculus',
                        'attachment' => null,
                        'status' => false,
                    ],
                    [
                        'content' => 'Statistics',
                        'attachment' => null,
                        'status' => false,
                    ]
                ]
            ],
            [
                'topic_id' => 2,
                'type' => 1, // multiple choice question
                'content' => 'Which field of mathematics is focused on the study of data and its analysis?',
                'choices' => [
                    [
                        'content' => 'Geometry',
                        'attachment' => null,
                        'status' => false,
                    ],
                    [
                        'content' => 'Statistics',
                        'attachment' => null,
                        'status' => true,
                    ],
                    [
                        'content' => 'Algebra',
                        'attachment' => null,
                        'status' => false,
                    ],
                    [
                        'content' => 'Calculus',
                        'attachment' => null,
                        'status' => false,
                    ]
                ]
            ],

            // Questions for Topic 3


            [
                'topic_id' => 3,
                'type' => 0, // true/false question
                'content' => 'Is circular motion a type of motion in physics?',
                'is_true' => true,
            ],
            [
                'topic_id' => 3,
                'type' => 0, // true/false question
                'content' => 'Is energy always conserved?',
                'is_true' => true,
            ],
            [
                'topic_id' => 3,
                'type' => 0, // true/false question
                'content' => 'Is the Earth round?',
                'is_true' => true,
            ],
            [
                'topic_id' => 3,
                'type' => 0, // true/false question
                'content' => 'Do all plants photosynthesize?',
                'is_true' => true,
            ],
            [
                'topic_id' => 3,
                'type' => 0, // true/false question
                'content' => 'Is water made up of two hydrogen atoms and one oxygen atom?',
                'is_true' => true,
            ],
            [
                'topic_id' => 3,
                'type' => 0, // true/false question
                'content' => 'Is the sun a planet?',
                'is_true' => false,
            ],
            [
                'topic_id' => 3,
                'type' => 0, // true/false question
                'content' => 'Do humans have more than one heart?',
                'is_true' => false,
            ],
            [
                'topic_id' => 3,
                'type' => 0, // true/false question
                'content' => 'Does gravity only exist on Earth?',
                'is_true' => false,
            ],
            [
                'topic_id' => 3,
                'type' => 0, // true/false question
                'content' => 'Is the human body composed of 60% water?',
                'is_true' => true,
            ],
            [
                'topic_id' => 3,
                'type' => 0, // true/false question
                'content' => 'Is a light year a measure of time?',
                'is_true' => false,
            ],
            [
                'topic_id' => 3,
                'type' => 0, // true/false question
                'content' => 'Does the moon emit its own light?',
                'is_true' => false,
            ],
            [
                'topic_id' => 3,
                'type' => 0, // true/false question
                'content' => 'Can energy be created or destroyed?',
                'is_true' => false,
            ],
            [
                'topic_id' => 3,
                'type' => 1, // multiple choice question
                'content' => 'Which of the following is a type of energy?',
                'choices' => [
                    [
                        'content' => 'Kinetic',
                        'attachment' => null,
                        'status' => true,
                    ],
                    [
                        'content' => 'Frequency',
                        'attachment' => null,
                        'status' => false,
                    ],
                    [
                        'content' => 'Amplitude',
                        'attachment' => null,
                        'status' => false,
                    ],
                    [
                        'content' => 'Wavelength',
                        'attachment' => null,
                        'status' => false,
                    ]
                ]
            ],
            [
                'topic_id' => 3,
                'type' => 1, // multiple choice question
                'content' => 'Which planet is known as the Red Planet?',
                'choices' => [
                    [
                        'content' => 'Earth',
                        'attachment' => null,
                        'status' => false,
                    ],
                    [
                        'content' => 'Mars',
                        'attachment' => null,
                        'status' => true,
                    ],
                    [
                        'content' => 'Jupiter',
                        'attachment' => null,
                        'status' => false,
                    ],
                    [
                        'content' => 'Venus',
                        'attachment' => null,
                        'status' => false,
                    ]
                ]
            ],
            [
                'topic_id' => 3,
                'type' => 1, // multiple choice question
                'content' => 'What is the chemical symbol for water?',
                'choices' => [
                    [
                        'content' => 'O2',
                        'attachment' => null,
                        'status' => false,
                    ],
                    [
                        'content' => 'H2O',
                        'attachment' => null,
                        'status' => true,
                    ],
                    [
                        'content' => 'CO2',
                        'attachment' => null,
                        'status' => false,
                    ],
                    [
                        'content' => 'NaCl',
                        'attachment' => null,
                        'status' => false,
                    ]
                ]
            ],
            [
                'topic_id' => 3,
                'type' => 1, // multiple choice question
                'content' => 'Which gas is most abundant in the Earth\'s atmosphere?',
                'choices' => [
                    [
                        'content' => 'Oxygen',
                        'attachment' => null,
                        'status' => false,
                    ],
                    [
                        'content' => 'Hydrogen',
                        'attachment' => null,
                        'status' => false,
                    ],
                    [
                        'content' => 'Nitrogen',
                        'attachment' => null,
                        'status' => true,
                    ],
                    [
                        'content' => 'Carbon Dioxide',
                        'attachment' => null,
                        'status' => false,
                    ]
                ]
            ],
            [
                'topic_id' => 3,
                'type' => 1, // multiple choice question
                'content' => 'What is the powerhouse of the cell?',
                'choices' => [
                    [
                        'content' => 'Nucleus',
                        'attachment' => null,
                        'status' => false,
                    ],
                    [
                        'content' => 'Mitochondria',
                        'attachment' => null,
                        'status' => true,
                    ],
                    [
                        'content' => 'Ribosome',
                        'attachment' => null,
                        'status' => false,
                    ],
                    [
                        'content' => 'Endoplasmic Reticulum',
                        'attachment' => null,
                        'status' => false,
                    ]
                ]
            ],
            [
                'topic_id' => 3,
                'type' => 1, // multiple choice question
                'content' => 'Which of these is not a type of rock?',
                'choices' => [
                    [
                        'content' => 'Igneous',
                        'attachment' => null,
                        'status' => false,
                    ],
                    [
                        'content' => 'Sedimentary',
                        'attachment' => null,
                        'status' => false,
                    ],
                    [
                        'content' => 'Metamorphic',
                        'attachment' => null,
                        'status' => false,
                    ],
                    [
                        'content' => 'Aquatic',
                        'attachment' => null,
                        'status' => true,
                    ]
                ]
            ],

            // Questions for Topic 4
            [
                'topic_id' => 4,
                'type' => 0, // true/false question
                'content' => 'Is Islamic culture a part of world history?',
                'is_true' => true,
            ],
            [
                'topic_id' => 4,
                'type' => 0, // true/false question
                'content' => 'Is philosophy irrelevant to Islamic culture?',
                'is_true' => false,
            ],
            [
                'topic_id' => 4,
                'type' => 0, // true/false question
                'content' => 'The speed of light is constant in a vacuum.',
                'is_true' => true,
            ],
            [
                'topic_id' => 4,
                'type' => 0, // true/false question
                'content' => 'Sound can travel through a vacuum.',
                'is_true' => false,
            ],
            [
                'topic_id' => 4,
                'type' => 0, // true/false question
                'content' => 'Newton discovered the law of gravity.',
                'is_true' => true,
            ],
            [
                'topic_id' => 4,
                'type' => 0, // true/false question
                'content' => 'The Earth is the center of the solar system.',
                'is_true' => false,
            ],
            [
                'topic_id' => 4,
                'type' => 0, // true/false question
                'content' => 'Einstein developed the theory of relativity.',
                'is_true' => true,
            ],
            [
                'topic_id' => 4,
                'type' => 0, // true/false question
                'content' => 'Atoms are the smallest unit of matter.',
                'is_true' => false,
            ],
            [
                'topic_id' => 4,
                'type' => 0, // true/false question
                'content' => 'Electricity is the flow of protons.',
                'is_true' => false,
            ],
            [
                'topic_id' => 4,
                'type' => 0, // true/false question
                'content' => 'Water boils at 100 degrees Celsius at sea level.',
                'is_true' => true,
            ],
            [
                'topic_id' => 4,
                'type' => 0, // true/false question
                'content' => 'Light can be bent by gravity.',
                'is_true' => true,
            ],
            [
                'topic_id' => 4,
                'type' => 0, // true/false question
                'content' => 'The Milky Way is the only galaxy in the universe.',
                'is_true' => false,
            ],
            [
                'topic_id' => 4,
                'type' => 1, // multiple choice question
                'content' => 'Which of the following is a significant aspect of Islamic culture?',
                'choices' => [
                    [
                        'content' => 'Arts and Sciences',
                        'attachment' => null,
                        'status' => true,
                    ],
                    [
                        'content' => 'Astronomy',
                        'attachment' => null,
                        'status' => false,
                    ],
                    [
                        'content' => 'Geology',
                        'attachment' => null,
                        'status' => false,
                    ],
                    [
                        'content' => 'Biology',
                        'attachment' => null,
                        'status' => false,
                    ]
                ]
            ],
            [
                'topic_id' => 4,
                'type' => 1, // multiple choice question
                'content' => 'What is the acceleration due to gravity on Earth?',
                'choices' => [
                    [
                        'content' => '9.8 m/s²',
                        'attachment' => null,
                        'status' => true,
                    ],
                    [
                        'content' => '12 m/s²',
                        'attachment' => null,
                        'status' => false,
                    ],
                    [
                        'content' => '15 m/s²',
                        'attachment' => null,
                        'status' => false,
                    ],
                    [
                        'content' => '5 m/s²',
                        'attachment' => null,
                        'status' => false,
                    ]
                ]
            ],
            [
                'topic_id' => 4,
                'type' => 1, // multiple choice question
                'content' => 'Which scientist is known for the laws of motion?',
                'choices' => [
                    [
                        'content' => 'Albert Einstein',
                        'attachment' => null,
                        'status' => false,
                    ],
                    [
                        'content' => 'Isaac Newton',
                        'attachment' => null,
                        'status' => true,
                    ],
                    [
                        'content' => 'Nikola Tesla',
                        'attachment' => null,
                        'status' => false,
                    ],
                    [
                        'content' => 'Galileo Galilei',
                        'attachment' => null,
                        'status' => false,
                    ]
                ]
            ],
            [
                'topic_id' => 4,
                'type' => 1, // multiple choice question
                'content' => 'What is the primary component of the sun?',
                'choices' => [
                    [
                        'content' => 'Oxygen',
                        'attachment' => null,
                        'status' => false,
                    ],
                    [
                        'content' => 'Hydrogen',
                        'attachment' => null,
                        'status' => true,
                    ],
                    [
                        'content' => 'Carbon',
                        'attachment' => null,
                        'status' => false,
                    ],
                    [
                        'content' => 'Nitrogen',
                        'attachment' => null,
                        'status' => false,
                    ]
                ]
            ],
            [
                'topic_id' => 4,
                'type' => 1, // multiple choice question
                'content' => 'Which planet is known as the "Red Planet"?',
                'choices' => [
                    [
                        'content' => 'Earth',
                        'attachment' => null,
                        'status' => false,
                    ],
                    [
                        'content' => 'Mars',
                        'attachment' => null,
                        'status' => true,
                    ],
                    [
                        'content' => 'Jupiter',
                        'attachment' => null,
                        'status' => false,
                    ],
                    [
                        'content' => 'Venus',
                        'attachment' => null,
                        'status' => false,
                    ]
                ]
            ],
            [
                'topic_id' => 4,
                'type' => 1, // multiple choice question
                'content' => 'What is the chemical symbol for gold?',
                'choices' => [
                    [
                        'content' => 'Au',
                        'attachment' => null,
                        'status' => true,
                    ],
                    [
                        'content' => 'Ag',
                        'attachment' => null,
                        'status' => false,
                    ],
                    [
                        'content' => 'Fe',
                        'attachment' => null,
                        'status' => false,
                    ],
                    [
                        'content' => 'Pb',
                        'attachment' => null,
                        'status' => false,
                    ]
                ]
            ]
        ];

        $arabicQuestions = [
            [
                'topic_id' => 1,
                'type' => 0, // true/false question
                'content' => 'لغة بايثون تستخدم لتطوير تطبيقات الويب.',
                'is_true' => true,
            ],
            [
                'topic_id' => 1,
                'type' => 0, // true/false question
                'content' => 'جافا هي لغة برمجة مفسرة وليست مترجمة.',
                'is_true' => false,
            ],
            [
                'topic_id' => 1,
                'type' => 0, // true/false question
                'content' => 'HTML هي لغة برمجة.',
                'is_true' => false,
            ],
            [
                'topic_id' => 1,
                'type' => 0, // true/false question
                'content' => 'C++ تدعم البرمجة الكائنية التوجه.',
                'is_true' => true,
            ],
            [
                'topic_id' => 1,
                'type' => 0, // true/false question
                'content' => 'PHP تستخدم لتطوير تطبيقات سطح المكتب.',
                'is_true' => false,
            ],
            [
                'topic_id' => 1,
                'type' => 0, // true/false question
                'content' => 'بايثون تدعم البرمجة الكائنية التوجه والبرمجة الإجرائية.',
                'is_true' => true,
            ],
            [
                'topic_id' => 1,
                'type' => 0, // true/false question
                'content' => 'CSS تستخدم لتحديد شكل صفحات الويب.',
                'is_true' => true,
            ],
            [
                'topic_id' => 1,
                'type' => 0, // true/false question
                'content' => 'JavaScript تعمل فقط على جانب الخادم.',
                'is_true' => false,
            ],
            [
                'topic_id' => 1,
                'type' => 0, // true/false question
                'content' => 'SQL هي لغة استعلامات تستخدم لإدارة قواعد البيانات.',
                'is_true' => true,
            ],
            [
                'topic_id' => 1,
                'type' => 0, // true/false question
                'content' => 'Git هو نظام إدارة النسخ الموزعة.',
                'is_true' => true,
            ],

            // Multiple choice questions for Topic 1 (Programming)
            [
                'topic_id' => 1,
                'type' => 1, // multiple choice question
                'content' => 'ما هي لغة البرمجة التي تُستخدم بشكل رئيسي لتطوير تطبيقات أندرويد؟',
                'choices' => [
                    [
                        'content' => 'بايثون',
                        'attachment' => null,
                        'status' => false,
                    ],
                    [
                        'content' => 'جافا',
                        'attachment' => null,
                        'status' => true,
                    ],
                    [
                        'content' => 'C++',
                        'attachment' => null,
                        'status' => false,
                    ],
                    [
                        'content' => 'PHP',
                        'attachment' => null,
                        'status' => false,
                    ]
                ]
            ],
            [
                'topic_id' => 1,
                'type' => 1, // multiple choice question
                'content' => 'أي من التالي ليس إطار عمل لتطوير الويب؟',
                'choices' => [
                    [
                        'content' => 'Django',
                        'attachment' => null,
                        'status' => false,
                    ],
                    [
                        'content' => 'React',
                        'attachment' => null,
                        'status' => false,
                    ],
                    [
                        'content' => 'Laravel',
                        'attachment' => null,
                        'status' => false,
                    ],
                    [
                        'content' => 'MySQL',
                        'attachment' => null,
                        'status' => true,
                    ]
                ]
            ],
            [
                'topic_id' => 1,
                'type' => 1, // multiple choice question
                'content' => 'ما هي لغة البرمجة التي تُستخدم لتطوير تطبيقات الويب الديناميكية؟',
                'choices' => [
                    [
                        'content' => 'HTML',
                        'attachment' => null,
                        'status' => false,
                    ],
                    [
                        'content' => 'CSS',
                        'attachment' => null,
                        'status' => false,
                    ],
                    [
                        'content' => 'JavaScript',
                        'attachment' => null,
                        'status' => true,
                    ],
                    [
                        'content' => 'SQL',
                        'attachment' => null,
                        'status' => false,
                    ]
                ]
            ],
            [
                'topic_id' => 1,
                'type' => 1, // multiple choice question
                'content' => 'أي من هذه اللغات تُعتبر لغة استعلامات قواعد البيانات؟',
                'choices' => [
                    [
                        'content' => 'Python',
                        'attachment' => null,
                        'status' => false,
                    ],
                    [
                        'content' => 'Ruby',
                        'attachment' => null,
                        'status' => false,
                    ],
                    [
                        'content' => 'SQL',
                        'attachment' => null,
                        'status' => true,
                    ],
                    [
                        'content' => 'C#',
                        'attachment' => null,
                        'status' => false,
                    ]
                ]
            ],
            [
                'topic_id' => 1,
                'type' => 1, // multiple choice question
                'content' => 'ما هو النظام الذي يُستخدم لإدارة النسخ في البرمجة؟',
                'choices' => [
                    [
                        'content' => 'JIRA',
                        'attachment' => null,
                        'status' => false,
                    ],
                    [
                        'content' => 'Trello',
                        'attachment' => null,
                        'status' => false,
                    ],
                    [
                        'content' => 'Git',
                        'attachment' => null,
                        'status' => true,
                    ],
                    [
                        'content' => 'Slack',
                        'attachment' => null,
                        'status' => false,
                    ]
                ]
                    ],

                    // topic 2



                    
        ];

        foreach ($englishQuestions as $raw) {
            // $topic = Topic::findOrFail(self::selectRandomTopic());
            $topic = Topic::findOrFail(4);
            // return $topic;

            $question = Question::create([
                'type' => $raw['type'],
                // 'type' => QuestionTypeEnum::TRUE_FALSE->value,
                'difficulty_level' => self::selectRandomDifficultyLevel(),
                'accessability_status' => self::selectRandomAccessabilityStatus(),
                'language' => LanguageEnum::ARABIC->value,
                'estimated_answer_time' => self::selectRandomEstimatedAnswerTime(),
                'content' => $raw['content'],
                'attachment' => null,
                'title' => null,
                'status' => 2,
            ]);

            if ($raw['type'] === QuestionTypeEnum::TRUE_FALSE->value) {
                $question->true_false_question()->create([
                    'answer' => ($raw['is_true']) ? TrueFalseAnswerEnum::TRUE->value : TrueFalseAnswerEnum::FALSE->value,
                ]);
            } else {
                foreach ($raw['choices'] as $choice) {
                    $question->choice()->create([
                        'content' => $choice['content'],
                        'attachment' => $choice['attachment'] ?? null,
                        'status' => $choice['is_true'],
                    ]);
                }
            }

                // $question = Question::create([
                //     'topic_id' => self::selectRandomTopic(),
                //     'type' => QuestionTypeEnum::MULTIPLE_CHOICE->value,
                //     'difficulty_level' => self::selectRandomDifficultyLevel(),
                //     'accessbility_status' => self::selectRandomAccessbilityStatus(),
                //     'language' => LanguageEnum::ARABIC->value,
                //     'estimated_answer_time' => self::selectRandomEstimatedAnswerTime(),
                //     'content' => $raw['content'],
                // ]);

                // return $question;
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

    public static function online_exam()
    {
        $exams = [
            [
                'type' => 1,
                'exam_type' => 1,
                'datetime' => now(),
                'duration' => 1,

                'language' => 1,
                'difficulty_level' => 50.0,

                'forms_count' => 1,
                'form_configuration_method' => 1,
                'form_name_method' => 1,

                'conduct_method' => 1,
                'datetime_notification_datetime' => now(),
                'result_notification_datetime' => now(),
                'status' => 1,

                'questions_types' => [
                    [
                        'type' => 1,
                        'questions_count' => 10,
                        'question_score' => 1,
                    ]
                ]
            ],
        ];

        foreach ($exams as $exam) {
            $realExam = RealExam::create([
                'type' => $exam['type_id'],
                'datetime' => $exam['datetime'],
                'duration' => $exam['duration'],
                'language' => $exam['language'],
                'note' => $exam['special_note'] ?? null,
                'difficulty_level' => $exam['difficulty_level'],
                'forms_count' => $exam['forms_count'],
                'form_configuration_method' => $exam['form_configuration_method'],
                'form_name_method' => $exam['form_name_method'],
                'exam_type' => RealExamTypeEnum::ONLINE->value,
                'course_lecturer_id' => 1
            ]);

            OnlineExam::create([
                'conduct_method' => $exam['conduct_method'],
                'exam_datetime_notification_datetime' => $exam['exam_datetime_notification_datetime'],
                'result_notification_datetime'  => $exam['result_notification_datetime'],
                'proctor_id' => $exam['proctor_id'] ?? null,
                'status' => ExamStatusEnum::ACTIVE->value,
                'id' => $realExam->id,
            ]);

            foreach ($exam['questions_types'] as $question_type) {
                $realExam->real_exam_question_types()->create([
                    'question_type' => $question_type['type'],
                    'questions_count' => $question_type['questions_count'],
                    'question_score' => $question_type['question_score'],
                ]);
            }
        }
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
    private static function selectRandomAccessabilityStatus(): int
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
            $question->choices()->create([
                'content' => $choice['content'],
                'status' => ($choice['isCorrect']) ? ChoiceStatusEnum::CORRECT_ANSWER->value : ChoiceStatusEnum::INCORRECT_ANSWER->value
            ]);
        }
    }
}
