<?php

namespace App\Helpers;

use stdClass;
use Traversable;
use App\Models\Form;
use App\Models\Choice;
use App\Models\Student;
use App\Models\Question;
use App\Models\RealExam;
use App\Enums\LevelsEnum;
use App\Traits\EnumTraits;
use App\Enums\ExamTypeEnum;
use App\Enums\LanguageEnum;
use App\Enums\SemesterEnum;
use Illuminate\Http\Request;
use App\Enums\ExamStatusEnum;
use App\Models\StudentAnswer;
use App\Enums\CoursePartsEnum;
use App\Enums\QuestionTypeEnum;
use App\Enums\RealExamTypeEnum;
use App\Helpers\QuestionHelper;
use App\Enums\FormNameMethodEnum;
use App\Helpers\EnumReplacement1;
use App\Models\TrueFalseQuestion;
use Illuminate\Http\UploadedFile;
use App\Enums\TrueFalseAnswerEnum;
use App\Helpers\ProcessDataHelper;
use Illuminate\Support\Facades\DB;
use App\Enums\ExamConductMethodEnum;
use App\Models\RealExamQuestionType;
use Illuminate\Support\Facades\Storage;
use App\Enums\CombinationChoiceTypeEnum;
use App\Models\QuestionChoiceCombination;
use App\Enums\FormConfigurationMethodEnum;
use App\Enums\StudentOnlineExamStatusEnum;
use App\Models\QuestionChoicesCombination;
use Illuminate\Database\Eloquent\Collection;
use App\AlgorithmAPI\UncombineQuestionChoicesCombination;

class ExamHelper
{

    /**
     * delete real (paper, online) exam by id
     */
    public static function deleteRealExam($realExamId)
    {

        try {
            $realExam = RealExam::findOrFail($realExamId);
            $realExam->real_exam_question_types()->delete();
            $readExamForms = $realExam->forms();
            foreach ($readExamForms as $readExamForm) {
                $readExamForm->form_questions()->delete();
            }
            $realExam->forms()->delete();
            if ($realExam->exam_type === RealExamTypeEnum::PAPER->value) {

                $realExam->paper_exam()->delete();
            } else {
                $realExam->online_exam()->delete();
            }
            $realExam->delete();
        } catch (\Exception $e) {
            throw $e;
            // return ResponseHelper::serverError('An error occurred while deleting models.');
        }
    }
    /**
     * add total score of each exam.
     * $realExams: list of real exam
     */

    public static function getRealExamsScore($realExams)
    {
        // Check if $realExams is an array or a single object
        $isArray = is_array($realExams) || $realExams instanceof Traversable;

        $realExamsToProcess = $isArray ? $realExams : [$realExams];

        $processedRealExams = [];

        foreach ($realExamsToProcess as $realExam) {
            if (is_array($realExam)) {
                if (isset($realExam['id'])) {
                    $temp = RealExam::findOrFail($realExam['id']);
                    $realExamQuestionTypes = $temp->real_exam_question_types()->get(['questions_count', 'question_score']);
                    $score = 0;
                    foreach ($realExamQuestionTypes as $realExamQuestionType) {
                        $score += $realExamQuestionType->questions_count * $realExamQuestionType->question_score;
                    }
                    $realExam['score'] = $score;
                    $processedRealExams[] = $realExam;
                }
            } else {
                $temp = RealExam::findOrFail($realExam->id);
                $realExamQuestionTypes = $temp->real_exam_question_types()->get(['questions_count', 'question_score']);
                // $realExamQuestionTypes = $realExam->real_exam_question_types()->get(['questions_count', 'question_score']);
                $score = 0;
                foreach ($realExamQuestionTypes as $realExamQuestionType) {
                    $score += $realExamQuestionType->questions_count * $realExamQuestionType->question_score;
                }
                $realExam->score = $score;
                $processedRealExams[] = $realExam;
            }
        }
        // If $realExams was a single object, return the first item in $processedRealExams
        return $isArray ? $processedRealExams : $processedRealExams[0];
    }

    // public static function getRealExamsScore($realExams) // recieve object has multiple array data
    // {
    //     foreach ($realExams as $realExam ) {
    //         $temp = RealExam::findOrFail($realExam['id']);
    //         $realExamQuestionTypes = $temp->real_exam_question_types()->get(['questions_count', 'question_score']);

    //         $score = 0;
    //         foreach ($realExamQuestionTypes as $realExamQuestionType) {
    //             $score += $realExamQuestionType->questions_count *  $realExamQuestionType->question_score;
    //         }

    //         $realExam['score'] = $score;
    //         // return $realExam;
    //         $score = 0;
    //     }

    //     return $realExams;
    // }

    // public static function getRealExamScore1($realExam) // recieve single array of data , not object has multiple
    // {
    //         $temp = RealExam::findOrFail($realExam['id']);
    //         $realExamQuestionTypes = $temp->real_exam_question_types()->get(['questions_count', 'question_score']);

    //         $score = 0;
    //         foreach ($realExamQuestionTypes as $realExamQuestionType) {
    //             $score += $realExamQuestionType->questions_count *  $realExamQuestionType->question_score;
    //         }
    //         $realExam['score'] = $score;

    //     return $realExam;
    // }


    /**
     * return froms nams: [name1, name2, name3,......]
     */


    public static function retrieveRealExamChapters($realExamId)
    {
        try {
            $realExamChapters = DB::table('real_exams')
                ->join('forms', 'real_exams.id', '=', 'forms.real_exam_id')
                ->join('form_questions', 'forms.id', '=', 'form_questions.form_id')
                ->join('questions', 'form_questions.question_id', '=', 'questions.id')
                ->join('topics', 'questions.topic_id', '=', 'topics.id')
                ->join('chapters', 'topics.chapter_id', '=', 'chapters.id')
                ->select('chapters.id', 'chapters.arabic_title as title')
                ->where('real_exams.id', '=', $realExamId)
                ->distinct()
                ->get();

            return $realExamChapters;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public static function retrieveRealExamChapterTopics($realExamId, $chapterId)
    {
        try {
            $realExamChapterTopics = DB::table('real_exams')
                ->join('forms', 'real_exams.id', '=', 'forms.real_exam_id')
                ->join('form_questions', 'forms.id', '=', 'form_questions.form_id')
                ->join('questions', 'form_questions.question_id', '=', 'questions.id')
                ->join('topics', 'questions.topic_id', '=', 'topics.id')
                ->select('topics.arabic_title as title')
                ->where('real_exams.id', '=', $realExamId)
                ->where('topics.chapter_id', '=', $chapterId)
                ->distinct()
                ->get();
            return $realExamChapterTopics;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * return forms: [[id, name1], [id, name2], ....].
     */
    public static function retrieveRealExamForms($realExamId)
    {
        try {
            $realExam = RealExam::findOrFail($realExamId);
            // $forms = $realExam->forms()->get(['id']);

            $forms = [];

            $formsIds = $realExam->forms()->get(['id'])
                ->map(function ($form) {
                    return $form->id;
                });

            $formsNames = self::getRealExamFormsNames(intval($realExam->form_name_method), $realExam->forms_count);

            if (intval($realExam->form_configuration_method) === FormConfigurationMethodEnum::DIFFERENT_FORMS->value) {
                $i = 0;
                foreach ($formsIds as $formId) {
                    $form['id'] = $formId;
                    $form['name'] = $formsNames[$i++];
                    array_push($forms, $form);
                }
            } else {
                if (count($formsIds) == 1) {
                    $formId = $formsIds->first();

                    foreach ($formsNames as $formName) {
                        $form['id'] = $formId;
                        $form['name'] = $formName;
                        array_push($forms, $form);
                    }
                } else {
                    // handle error..
                }
            }
            return $forms;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // need to test
    public static function getRealExamFormsNames($form_name_method, $forms_count)
    {
        $formsNames = [];
        if ($form_name_method === FormNameMethodEnum::DECIMAL_NUMBERING->value) {
            for ($i = 1; $i <= $forms_count; $i++) {
                array_push($formsNames, strval($i));
            }
        } elseif ($form_name_method === FormNameMethodEnum::ROMAN_NUMBERING->value) {
            $formsNames = self::generateRomanNumerals($forms_count);
        } elseif ($form_name_method === FormNameMethodEnum::ALPHANUMERIC_NUMBERING->value) {
            $formsNames = self::generateArabicLetters($forms_count);
            // $formsNames = self::generateEnglishLetters($forms_count);
        }

        return $formsNames;
    }

    private static function generateRomanNumerals($count)
    {
        $romanSymbols = array(
            1    => 'I',
            4    => 'IV',
            5    => 'V',
            9    => 'IX',
            10   => 'X',
            40   => 'XL',
            50   => 'L',
            90   => 'XC',
            100  => 'C',
            400  => 'CD',
            500  => 'D',
            900  => 'CM',
            1000 => 'M'
        );

        // Initialize an empty array to store the Roman numerals
        $result = array();

        // Iterate through numbers from 1 to the specified count
        for ($i = 1; $i <= $count; $i++) {
            $number = $i;
            $romanNumeral = '';

            // Iterate through the Roman numeral symbols in descending order
            foreach (array_keys(array_reverse($romanSymbols, true)) as $value) {
                // Divide the number by the current symbol's value and get the quotient
                $count = intval($number / $value);

                // Append the symbol to the Roman numeral count times
                $romanNumeral .= str_repeat($romanSymbols[$value], $count);

                // Update the number by subtracting the value of the symbols added to the result
                $number %= $value;
            }

            // Add the Roman numeral for the current number to the result array
            $result[] = $romanNumeral;
        }

        return $result;
    }

    private static function generateArabicLetters($count)
    {
        // Define an array to store Arabic numerals (digits)
        $arabicLetters = array();

        // Unicode value of Arabic numeral ١ (U+0661) for digit 1
        $unicodeStart = 0x0661; // Arabic numeral 1

        // Generate Arabic numerals for numbers from 1 to $count
        for ($i = 1; $i <= $count; $i++) {
            $arabicLetters[] = mb_chr($unicodeStart + $i - 1, 'UTF-8');
        }

        return $arabicLetters;
    }

    private static function generateEnglishLetters($count)
    {
        // Validate the input parameter
        if (!is_int($count) || $count <= 0) {
            return []; // Return an empty array if count is not a positive integer
        }

        // Define an array to store English letters
        $englishLetters = [];

        // Generate English letters for numbers from 1 to $count
        for ($i = 1; $i <= $count; $i++) {
            // Convert the number to its corresponding letter (a=1, b=2, ..., z=26)
            $letter = chr(ord('a') + ($i - 1) % 26);
            // Append the letter to the array
            $englishLetters[] = $letter;
        }

        return $englishLetters;
    }

    public static function getFormQuestionsWithDetails($formId, bool $withQuestionId, bool $withChoiceId, bool $withAnswer)
    {
        $questions = [];
        $form = Form::findOrFail($formId);

        $formQuestions = $form->form_questions()->get(['question_id', 'combination_id']);

        foreach ($formQuestions as $formQuestion) {
            $question = $formQuestion->question()->first(['id', 'content', 'attachment as attachment_url', 'topic_id', 'type as type_name']);

            $topic = $question->topic()->first(['arabic_title', 'chapter_id']);

            $chapter_title = $topic->chapter()->first()['arabic_title'];
            $topic_title = $topic->arabic_title;

            $question->chapter_name = $chapter_title;
            $question->topic_name = $topic_title;

            unset($question['topic_id']);

            $question = NullHelper::filter($question);

            if ($formQuestion->combination_id) {
                if ($withAnswer) {
                    $question['choices'] = self::retrieveCombinationChoices($formQuestion->question_id, $formQuestion->combination_id, $withChoiceId, true);
                } else {
                    $question['choices'] = self::retrieveCombinationChoices($formQuestion->question_id, $formQuestion->combination_id, $withChoiceId, false);
                }
            } else {
                if ($withAnswer) {
                    $trueFalseQuestion = TrueFalseQuestion::findOrFail($formQuestion->question_id)->first(['answer']);
                    if (intval($trueFalseQuestion->answer) === TrueFalseAnswerEnum::TRUE->value) {
                        $question['is_true'] = true;
                    } else {
                        $question['is_true'] = false;
                    }
                }
            }

            if (!$withQuestionId) {
                unset($question['id']);
            }

            array_push($questions, $question);
        }

        $groupedQuestions = [];

        foreach ($questions as $question) {
            $typeName = $question['type_name'];
            if (!isset($groupedQuestions[$typeName])) {
                $groupedQuestions[$typeName] = [
                    'type_name' => $typeName,
                    'questions' => []
                ];
            }

            unset($question['type_name']);

            $groupedQuestions[$typeName]['questions'][] = $question;
        }

        $groupedQuestions = array_values($groupedQuestions);

        $groupedQuestions = ProcessDataHelper::enumsConvertIdToName(
            $groupedQuestions,
            [
                new EnumReplacement('type_name', QuestionTypeEnum::class)
            ]
        );

        return $groupedQuestions;
    }



    public static function retrieveRealExamFormQuestions($formId) //////////////////////*********** More condition needed
    {
        $form = Form::findOrFail($formId);
        $formQuestions = [];
        $realExam = RealExam::where('id', $form->real_exam_id)->first();
        $queationsTypes =  $realExam->real_exam_question_types()->get(['question_type as type_name']);

        foreach ($queationsTypes as $type) {
            $questions = DB::table('forms')
                ->join('form_questions', 'forms.id', '=', 'form_questions.form_id')
                ->join('questions', 'form_questions.question_id', '=', 'questions.id')
                ->join('topics', 'questions.topic_id', '=', 'topics.id')
                ->join('chapters', 'topics.chapter_id', '=', 'chapters.id')
                ->select(
                    'chapters.arabic_title as chapter_title',
                    'topics.arabic_title as topic_title',
                    'questions.id',
                    'questions.content',
                    'questions.attachment',
                    'form_questions.combination_id',
                )
                ->where('forms.id', '=', $form->id)
                ->where('questions.type', '=', $type->type_name)
                ->get();

            $questions = QuestionHelper::retrieveQuestionsAnswer($questions, $type->type_name);
            return $questions;

            // $formQuestions[QuestionTypeEnum::getNameByNumber($type->type_name)] = $questions;
            $formQuestions[EnumTraits::getNameByNumber($type->type_name, QuestionTypeEnum::class)] = $questions;
        }
        return $formQuestions;
    }


    public static function checkTrueFalseQuestionAnswer(Question $qeustion, $answer): bool
    {
        return true;
    }
    public static function checkChoicesQuestionAnswer(Question $qeustion, $answer, $combinationId): bool
    {
        return true;
    }

    public static function getExamResultAppreciation($scoreRate)
    {
        return 'exelent';
    }

    /**
     ***** job: 
     * this function using for multi choice question in exam 
     ***** parameters: 
     * $qeustionId: int 
     * combinationId: int 
     * withChoiceId: int 
     * withAnswer: int 
     ***** return: 
     * choices [id, content, attachment, is_true]
     */
    public static function retrieveCombinationChoices($qeustionId, $combinationId, bool $withChoiceId, bool $withAnswer)
    {
        $result = null;
        $choices = self::uncombinateCombination($qeustionId, $combinationId);

        // 453, 454, 459, -2

        // return $choices;

        // if($withChoiceId && $withAnswer){
        //     $result = self::retrieveCombinationChoicesWithIdAndAnswer($choices);
        // }elseif($withChoiceId && !$withAnswer){
        //     $result = self::retrieveCombinationChoicesWithId($choices);
        // }elseif(!$withChoiceId && $withAnswer){
        //     $result = self::retrieveCombinationChoicesWithAnswer($choices);
        // }else{
        //     $result = self::retrieveCombinationChoicesWithoutIdAndAnswer($choices);
        // }


        $result = [];

        foreach ($choices as $choice) {
            $temp = [];

            if (property_exists($choice, 'ids')) {
                $temp['content'] = EnumTraits::getNameByNumber(CombinationChoiceTypeEnum::MIX->value, CombinationChoiceTypeEnum::class);

                if ($withChoiceId) {
                    $temp['id'] = CombinationChoiceTypeEnum::MIX->value;
                }
            } else {
                if ($choice->id == -1) {
                    $temp['content'] = EnumTraits::getNameByNumber(CombinationChoiceTypeEnum::ALL->value, CombinationChoiceTypeEnum::class);
                } elseif ($choice->id == -2) {
                    $temp['content'] = EnumTraits::getNameByNumber(CombinationChoiceTypeEnum::NOTHING->value, CombinationChoiceTypeEnum::class);
                } else {
                    $temp = Choice::where('id', $choice->id)->first(['content', 'attachment as attachment_url']);
                }

                if ($withChoiceId) {
                    $temp['id'] = $choice->id;
                }
            }

            if ($withAnswer) {
                $temp['is_true'] = $choice->isCorrect;
            }

            array_push($result, $temp);
        }

        return $result;
    }

    private static function uncombinateCombination($qeustionId, $combinationId)
    {
        $combinationChoices = QuestionChoicesCombination::where('combination_id', '=', $combinationId)
            ->where('question_id', '=', $qeustionId)
            ->first(['combination_choices'])['combination_choices'];

        $choices = (new UncombineQuestionChoicesCombination())->execute($combinationChoices);

        return $choices;
    }

    private static function retrieveCombinationChoicesWithIdAndAnswer($choices)
    {
        $result = [];
        foreach ($choices as $choice) {
            $temp = [];
            if (property_exists($choice, 'ids')) {
                $temp['id'] = CombinationChoiceTypeEnum::MIX->value;
                $temp['content'] = EnumTraits::getNameByNumber(CombinationChoiceTypeEnum::MIX->value, CombinationChoiceTypeEnum::class);
            } else {
                if ($choice->id == -1) {
                    $temp['id'] = CombinationChoiceTypeEnum::ALL->value;
                    $temp['content'] = EnumTraits::getNameByNumber(CombinationChoiceTypeEnum::ALL->value, CombinationChoiceTypeEnum::class);
                } elseif ($choice->id == -2) {
                    $temp['id'] = CombinationChoiceTypeEnum::NOTHING->value;
                    $temp['content'] = EnumTraits::getNameByNumber(CombinationChoiceTypeEnum::NOTHING->value, CombinationChoiceTypeEnum::class);
                } else {
                    $temp = Choice::findOrFail($choice->id)->first(['id', 'content', 'attachment as attachment_url']);
                }
            }
            $temp['is_true'] = $choice->isCorrect;
            array_push($result, $temp);
        }
        return $result;
    }

    private static function retrieveCombinationChoicesWithId($choices)
    {
        $result = [];
        foreach ($choices as $choice) {
            $temp = [];
            if (property_exists($choice, 'ids')) {
                $temp['id'] = CombinationChoiceTypeEnum::MIX->value;
                $temp['content'] = EnumTraits::getNameByNumber(CombinationChoiceTypeEnum::MIX->value, CombinationChoiceTypeEnum::class);
            } else {
                if ($choice->id == -1) {
                    $temp['id'] = CombinationChoiceTypeEnum::ALL->value;
                    $temp['content'] = EnumTraits::getNameByNumber(CombinationChoiceTypeEnum::ALL->value, CombinationChoiceTypeEnum::class);
                } elseif ($choice->id == -2) {
                    $temp['id'] = CombinationChoiceTypeEnum::NOTHING->value;
                    $temp['content'] = EnumTraits::getNameByNumber(CombinationChoiceTypeEnum::NOTHING->value, CombinationChoiceTypeEnum::class);
                } else {
                    $temp = Choice::findOrFail($choice->id)->first(['id', 'content', 'attachment as attachment_url']);
                }
            }
            array_push($result, $temp);
        }
        return $result;
    }

    private static function retrieveCombinationChoicesWithAnswer($choices)
    {
        $result = [];
        foreach ($choices as $choice) {
            $temp = [];
            if (property_exists($choice, 'ids')) {
                $temp['content'] = EnumTraits::getNameByNumber(CombinationChoiceTypeEnum::MIX->value, CombinationChoiceTypeEnum::class);
            } else {
                if ($choice->id == -1) {
                    $temp['content'] = EnumTraits::getNameByNumber(CombinationChoiceTypeEnum::ALL->value, CombinationChoiceTypeEnum::class);
                } elseif ($choice->id == -2) {
                    $temp['content'] = EnumTraits::getNameByNumber(CombinationChoiceTypeEnum::NOTHING->value, CombinationChoiceTypeEnum::class);
                } else {
                    // $temp = Choice::findOrFail($choice->id)->first(['content', 'attachment as attachment_url']); // threre's problem in pk (id)
                    $temp = Choice::where('id', $choice->id)->first(['content', 'attachment as attachment_url']);

                    return $temp;
                }
            }
            $temp['is_true'] = $choice->isCorrect;
            array_push($result, $temp);
        }
        return $result;
    }

    private static function retrieveCombinationChoicesWithoutIdAndAnswer($choices)
    {
        $result = [];
        foreach ($choices as $choice) {
            $temp = [];
            if (property_exists($choice, 'ids')) {
                $temp['content'] = EnumTraits::getNameByNumber(CombinationChoiceTypeEnum::MIX->value, CombinationChoiceTypeEnum::class);
            } else {
                if ($choice->id == -1) {
                    $temp['content'] = EnumTraits::getNameByNumber(CombinationChoiceTypeEnum::ALL->value, CombinationChoiceTypeEnum::class);
                } elseif ($choice->id == -2) {
                    $temp['content'] = EnumTraits::getNameByNumber(CombinationChoiceTypeEnum::NOTHING->value, CombinationChoiceTypeEnum::class);
                } else {
                    $temp = Choice::findOrFail($choice->id)->first(['content', 'attachment as attachment_url']);
                }
            }
            array_push($result, $temp);
        }
        return $result;
    }
}
