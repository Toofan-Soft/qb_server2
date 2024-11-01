<?php

namespace App\Helpers;

use Traversable;
use App\Models\Form;
use App\Models\Choice;
use App\Models\Question;
use App\Models\RealExam;
use App\Traits\EnumTraits;
use App\Enums\LanguageEnum;
use App\Enums\AppreciationEnum;
use App\Enums\QuestionTypeEnum;
use App\Enums\FormNameMethodEnum;
use App\Enums\QuestionStatusEnum;
use App\Models\TrueFalseQuestion;
use App\Enums\TrueFalseAnswerEnum;
use App\Helpers\ProcessDataHelper;
use Illuminate\Support\Facades\DB;
use App\Enums\ExamDifficultyLevelEnum;
use App\Enums\CombinationChoiceTypeEnum;
use App\Enums\FormConfigurationMethodEnum;
use App\Models\QuestionChoicesCombination;
use App\AlgorithmAPI\UncombineQuestionChoicesCombination;
use App\AlgorithmAPI\CheckQuestionChoicesCombinationAnswer;

class ExamHelper
{

    /**
     * add total score of each exam.
     * $realExams: list of real exam
     */

    public static function getRealExamsScore($realExams)
    {
        try {
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
        } catch (\Exception $e) {
            throw $e;
        }
    }


    public static function retrieveRealExamChapters($realExamId)
    {
        try {
            $realExamChapters = DB::table('real_exams')
                ->join('forms', 'real_exams.id', '=', 'forms.real_exam_id')
                ->join('form_questions', 'forms.id', '=', 'form_questions.form_id')
                ->join('questions', 'form_questions.question_id', '=', 'questions.id')
                ->join('topics', 'questions.topic_id', '=', 'topics.id')
                ->join('chapters', 'topics.chapter_id', '=', 'chapters.id')
                ->select('chapters.id', LanguageHelper::getTitleColumnName('chapters', 'title'))
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
                ->select(LanguageHelper::getTitleColumnName('topics', 'title'))
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
            $language = LanguageEnum::symbolOf(intval($realExam->language));

            $forms = [];

            $formsIds = $realExam->forms()->orderBy('id')->pluck('id')->toArray();
            $formsNames = self::getRealExamFormsNames(intval($realExam->form_name_method), $realExam->forms_count, $language);

            if (intval($realExam->form_configuration_method) === FormConfigurationMethodEnum::DIFFERENT_FORMS->value) {
                $i = 0;
                foreach ($formsIds as $formId) {
                    $form['id'] = $formId;
                    $form['name'] = $formsNames[$i++];
                    array_push($forms, $form);
                }
            } else {
                if (count($formsIds) == 1) {
                    $formId = $formsIds[0];

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

    /**
     * return froms nams: [name1, name2, name3,......]
     */

    public static function getRealExamFormsNames($form_name_method, $forms_count, string $language)
    {
        try {
            $formsNames = [];
            if ($form_name_method === FormNameMethodEnum::DECIMAL_NUMBERING->value) {
                for ($i = 1; $i <= $forms_count; $i++) {
                    // $romanNumerals[] = strval($i);
                    array_push($formsNames, strval($i));
                }
            } elseif ($form_name_method === FormNameMethodEnum::ROMAN_NUMBERING->value) {
                for ($i = 1; $i <= $forms_count; $i++) {
                    // $romanNumerals[] = NameMethodHelper::convertToRomanNumber($i);
                    array_push($formsNames, NameMethodHelper::convertToRomanNumber($i));
                }
            } elseif ($form_name_method === FormNameMethodEnum::ALPHANUMERIC_NUMBERING->value) {
                $formsNames = NameMethodHelper::generateAlphanumericNummering($forms_count, $language);
            }

            return $formsNames;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public static function getFormQuestionsWithDetails($formId, bool $withQuestionId, bool $withChoiceId, bool $withAnswer)
    {
        try {
            $questions = [];
            $form = Form::findOrFail($formId);
            $realExam = RealExam::findOrFail($form->real_exam_id);
            $language = LanguageEnum::symbolOf(intval($realExam->language));

            $formQuestions = $form->form_questions()->orderBy('question_id')->get(['question_id', 'combination_id']);

            foreach ($formQuestions as $formQuestion) {
                $question = $formQuestion->question()->first(['id', 'content', 'attachment as attachment_url', 'topic_id', 'type as type_name']);

                $topic = $question->topic()->first([LanguageHelper::getTitleColumnName(null, null), 'chapter_id']);

                $chapter_title = $topic->chapter()->first()[LanguageHelper::getTitleColumnName(null, null)];
                $topic_title = $topic[LanguageHelper::getTitleColumnName(null, null)];

                $question->chapter_title = $chapter_title;
                $question->topic_title = $topic_title;

                unset($question['topic_id']);

                // $question = NullHelper::filter($question);

                if ($formQuestion->combination_id) {
                    $question->choices = self::retrieveCombinationChoices($formQuestion->question_id, $formQuestion->combination_id, $withChoiceId, $withAnswer, $language);
                    // if ($withAnswer) {
                    //     // $question['choices'] = self::retrieveCombinationChoices($formQuestion->question_id, $formQuestion->combination_id, $withChoiceId, true, $language);
                    //     $question->choices = self::retrieveCombinationChoices($formQuestion->question_id, $formQuestion->combination_id, $withChoiceId, true, $language);
                    // } else {
                    //     // $question['choices'] = self::retrieveCombinationChoices($formQuestion->question_id, $formQuestion->combination_id, $withChoiceId, false, $language);
                    //     $question->choices = self::retrieveCombinationChoices($formQuestion->question_id, $formQuestion->combination_id, $withChoiceId, false, $language);
                    // }
                } else {
                    if ($withAnswer) {
                        $trueFalseQuestion = TrueFalseQuestion::where('question_id', $formQuestion->question_id)->first(['answer']);
                        if (intval($trueFalseQuestion->answer) === TrueFalseAnswerEnum::TRUE->value) {
                            $question->is_true = true;
                        } else {
                            $question->is_true = false;
                        }
                    }
                }

                if (!$withQuestionId) {
                    // unset($question['id']);
                    unset($question->id);
                }

                array_push($questions, $question);
            }

            $groupedQuestions = [];

            foreach ($questions as $question) {
                // $typeName = $question['type_name'];
                $typeName = $question->type_name;
                if (!isset($groupedQuestions[$typeName])) {
                    $groupedQuestions[$typeName] = [
                        'type_name' => $typeName,
                        'questions' => []
                    ];
                }

                // unset($question['type_name']);
                unset($question->type_name);

                $groupedQuestions[$typeName]['questions'][] = $question;
            }

            $groupedQuestions = array_values($groupedQuestions);

            $groupedQuestions = ProcessDataHelper::enumsConvertIdToName(
                $groupedQuestions,
                [
                    new EnumReplacement('type_name', QuestionTypeEnum::class)
                ]
            );

            $groupedQuestions = NullHelper::filter($groupedQuestions);

            return $groupedQuestions;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public static function getFormQuestionsWithoutDetails($formId, bool $withQuestionId, bool $withChoiceId, bool $withAnswer)
    {
        try {
            $questions = [];
            $form = Form::findOrFail($formId);
            $realExam = RealExam::findOrFail($form->real_exam_id);
            $language = LanguageEnum::symbolOf(intval($realExam->language));

            $formQuestions = $form->form_questions()->orderBy('question_id')->get(['question_id', 'combination_id']);

            foreach ($formQuestions as $formQuestion) {
                $question = $formQuestion->question()->first(['id', 'content', 'attachment as attachment_url']);

                if ($formQuestion->combination_id) {
                    $question->choices = self::retrieveCombinationChoices($formQuestion->question_id, $formQuestion->combination_id, $withChoiceId, $withAnswer, $language);
                } else {
                    if ($withAnswer) {
                        $answer = TrueFalseQuestion::where('question_id', $formQuestion->question_id)->first(['answer'])['answer'];
                        $question->is_true = intval($answer) === TrueFalseAnswerEnum::TRUE->value;
                    }
                }

                if (!$withQuestionId) {
                    unset($question->id);
                }

                array_push($questions, $question);
            }

            $questions = NullHelper::filter($questions);

            return $questions;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // public static function retrieveRealExamFormQuestions($formId) //////////////////////*********** More condition needed
    // {
    //     $form = Form::findOrFail($formId);
    //     $formQuestions = [];
    //     $realExam = RealExam::where('id', $form->real_exam_id)->first();
    //     $queationsTypes =  $realExam->real_exam_question_types()->get(['question_type as type_name']);

    //     foreach ($queationsTypes as $type) {
    //         $questions = DB::table('forms')
    //             ->join('form_questions', 'forms.id', '=', 'form_questions.form_id')
    //             ->join('questions', 'form_questions.question_id', '=', 'questions.id')
    //             ->join('topics', 'questions.topic_id', '=', 'topics.id')
    //             ->join('chapters', 'topics.chapter_id', '=', 'chapters.id')
    //             ->select(
    //                 'chapters.arabic_title as chapter_title',
    //                 'topics.arabic_title as topic_title',
    //                 'questions.id',
    //                 'questions.content',
    //                 'questions.attachment',
    //                 'form_questions.combination_id',
    //             )
    //             ->where('forms.id', '=', $form->id)
    //             ->where('questions.type', '=', $type->type_name)
    //             ->get();

    //         $questions = QuestionHelper::retrieveQuestionsAnswer($questions, $type->type_name);
    //         return $questions;

    //         // $formQuestions[QuestionTypeEnum::getNameByNumber($type->type_name)] = $questions;
    //         $formQuestions[EnumTraits::getNameByNumber($type->type_name, QuestionTypeEnum::class)] = $questions;
    //     }
    //     return $formQuestions;
    // }

    public static function checkQuestionAnswer($questionId, $answer, $combinationId): bool
    {
        try {
            $question = Question::findOrFail($questionId);
            if (intval($question->type) === QuestionTypeEnum::TRUE_FALSE->value) {
                $trueFalseQuestion = $question->true_false_question()->first();
                if (intval($trueFalseQuestion->answer) === $answer) {
                    return true;
                } else {
                    return false;
                }
            } else {
                $data = [
                    'combination_choices' => $question->question_choices_combinations()->where('combination_id', '=', $combinationId)->first(['combination_choices'])['combination_choices'],
                    'answer_id' => $answer
                ];

                return (new CheckQuestionChoicesCombinationAnswer())->execute($data);
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public static function getExamResultAppreciation($scoreRate)
    {
        try {
            //for language we must choice like exam langauage or user language
            return AppreciationEnum::getScoreRateAppreciation($scoreRate, LanguageHelper::getEnumLanguageName());
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     ***** job: 
     * this function using for retrieve data that will use in algorithm 
     * it used in lecturer online exam, paper exam, practice exam 
     ***** parameters: 
     * request: Request 
     * accessabilityStatusIds: [int] 
     * 
     ***** return: 
     * algorithmData = { estimated_time, difficulty_level, forms_count,  
     *      question_types_and_questions_count [id, count], 
     *      question [id, type_id, difficulty_level, answer_time, topic_id, last_selection, selection_times]
     *  }
     */
    public static function getAlgorithmData($request, $accessabilityStatusIds)
    {
        try {
            $types = [];
            $questionTypesIds = [];
            foreach ($request->questions_types as $type) {
                $t = [
                    'id' => intval($type['type_id']),
                    'count' => intval($type['questions_count'])
                ];

                array_push($types, $t);
                array_push($questionTypesIds, $type['type_id']);
            }

            // return $questionTypesIds;
            $algorithmData = [
                'estimated_time' => intval($request->duration),
                // 'difficulty_level' => floatval($request->difficulty_level_id),
                'difficulty_level' => ExamDifficultyLevelEnum::toFloat($request->difficulty_level_id),
                'forms_count' => ($request->form_configuration_method_id === FormConfigurationMethodEnum::DIFFERENT_FORMS->value) ? $request->forms_count : 1,
                'question_types_and_questions_count' => $types
                // 'question_types_and_questions_count' => [
                //     // 'id' => $request->questions_types['type_id'],
                //     // 'count' => $request->questions_types['questions_count']
                //     'id' => $request->questions_types->type_id,
                //     'count' => $request->questions_types->questions_count
                // ],
            ];

            $questions =  DB::table('questions')
                ->join('question_usages', 'questions.id', '=', 'question_usages.question_id')
                // ->join('topics', 'questions.topic_id', '=', 'topics.id')
                ->select(
                    'questions.id',
                    'questions.type as type_id',
                    'questions.difficulty_level',
                    'questions.estimated_answer_time as answer_time',
                    'question_usages.online_exam_last_selection_datetime',
                    'question_usages.practice_exam_last_selection_datetime',
                    'question_usages.paper_exam_last_selection_datetime',
                    'question_usages.online_exam_selection_times_count',
                    'question_usages.practice_exam_selection_times_count',
                    'question_usages.paper_exam_selection_times_count',
                    'questions.topic_id',
                    // 'topics.id as topic_id',
                )
                ->where('questions.status', '=', QuestionStatusEnum::ACCEPTED->value)
                ->where('questions.language', '=', $request->language_id)
                ->whereIn('questions.accessibility_status', $accessabilityStatusIds)
                ->whereIn('questions.type', $questionTypesIds)
                ->whereIn('questions.topic_id', $request->topics_ids)
                // ->whereIn('topics.id', $request->topics_ids)
                // ->whereIn('topics.id', [3])
                ->get()
                ->toArray();

            foreach ($questions as $question) {
                // يجب ان يتم تحديد اوزان هذه المتغيرات لضبط مقدار تاثير كل متغير على حل خوارزمية التوليد

                $question->type_id = intval($question->type_id);
                $question->difficulty_level = floatval($question->difficulty_level);

                // $selections = [1, 2, 3, 4, 5];
                // $randomIndex = array_rand($selections);
                // $question['last_selection'] = $selections[$randomIndex];
                // $question->last_selection = 3;
                $question->last_selection = intval((
                    DatetimeHelper::getDifferenceInDays(now(), $question->online_exam_last_selection_datetime) +
                    DatetimeHelper::getDifferenceInDays(now(), $question->practice_exam_last_selection_datetime) +
                    DatetimeHelper::getDifferenceInDays(now(), $question->paper_exam_last_selection_datetime)
                ) / 3);

                // $question->selection_times = 2;
                $question->selection_times = intval((
                    $question->online_exam_selection_times_count +
                    $question->practice_exam_selection_times_count +
                    $question->paper_exam_selection_times_count
                ) / 3);
                // حذف الاعمدة التي تم تحويلها الي عمودين فقط من الاسئلة 
                unset($question->online_exam_last_selection_datetime);
                unset($question->practice_exam_last_selection_datetime);
                unset($question->paper_exam_last_selection_datetime);
                unset($question->online_exam_selection_times_count);
                unset($question->practice_exam_selection_times_count);
                unset($question->paper_exam_selection_times_count);
            }

            $algorithmData['questions'] = $questions;

            return $algorithmData;
        } catch (\Exception $e) {
            throw $e;
        }
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
    // public static function retrieveCombinationChoices($qeustionId, $combinationId, bool $withChoiceId, bool $withAnswer)
    public static function retrieveCombinationChoices($qeustionId, $combinationId, bool $withChoiceId, bool $withAnswer, string $language = 'ar')
    {
        try {
            // $result = null;
            $choices = self::uncombinateCombination($qeustionId, $combinationId);

            $result = [];

            foreach ($choices as $choice) {
                $temp = [];

                if (property_exists($choice, 'ids')) {
                    $temp['content'] = EnumTraits::getNameByNumber(CombinationChoiceTypeEnum::MIX->value, CombinationChoiceTypeEnum::class, $language);
                    $temp['ids'] = $choice->ids;
                } else {
                    if ($choice->id == -1) {
                        $temp['content'] = EnumTraits::getNameByNumber(CombinationChoiceTypeEnum::ALL->value, CombinationChoiceTypeEnum::class, $language);
                    } elseif ($choice->id == -2) {
                        $temp['content'] = EnumTraits::getNameByNumber(CombinationChoiceTypeEnum::NOTHING->value, CombinationChoiceTypeEnum::class, $language);
                    } else {
                        $temp = Choice::where('id', $choice->id)->first(['content', 'attachment as attachment_url']);
                    }

                    // if ($withChoiceId) {
                    $temp['id'] = $choice->id;
                    // }
                }

                if ($withAnswer) {
                    $temp['is_true'] = $choice->isCorrect;
                }

                array_push($result, $temp);
            }

            $mixIds = collect($result)->filter(function ($item) {
                if (isset($item['ids'])) {
                    return $item;
                }
            })->map(function ($item) {
                return collect($item['ids'])->toArray();
            })->first();

            $isMixTrue = collect($result)->filter(function ($item) {
                if (isset($item['is_true']) && isset($item['ids'])) {
                    return $item;
                }
            })->map(function ($item) {
                return $item['is_true'];
            })->first();

            $finalChoices = [];

            if (!isset($mixIds) && empty($mixIds)) {
                $finalUnmixed = [];

                foreach ($result as $item) {
                    if (!$withChoiceId) {
                        unset($item['id']);
                    }
                    $finalUnmixed[] = $item;
                }

                $finalChoices['unmixed'] = $finalUnmixed;
            } else {
                $mixed = collect($result)->filter(function ($item) use ($mixIds) {
                    return !isset($item['ids']) && !is_null($mixIds) && in_array($item->id, $mixIds);
                })->toArray();

                $unmixed = collect($result)->filter(function ($item) use ($mixIds) {
                    return !isset($item['ids']) && ((!is_null($mixIds) && !in_array($item->id, $mixIds)) || is_null($mixIds));
                })->toArray();

                if (!$withChoiceId) {
                    $mixed = collect($mixed)->map(function ($item) {
                        unset($item['id']);
                        return $item;
                    });

                    $unmixed = collect($unmixed)->map(function ($item) {
                        unset($item['id']);
                        return $item;
                    });
                }

                if (!empty($mixed)) {
                    $finalMixed = [];

                    foreach ($mixed as $mixedItem) {
                        $finalMixed[] = $mixedItem;
                    }

                    $final['choices'] = $finalMixed;

                    if ($withChoiceId) {
                        $final['id'] = CombinationChoiceTypeEnum::MIX->value;
                    }

                    if ($withAnswer) {
                        $final['is_true'] = $isMixTrue;
                    }

                    $finalChoices['mixed'] = $final;
                }

                if (!empty($unmixed)) {
                    $finalUnmixed = [];

                    foreach ($unmixed as $unmixedItem) {
                        $finalUnmixed[] = $unmixedItem;
                    }

                    $finalChoices['unmixed'] = $finalUnmixed;
                }
            }

            return $finalChoices;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public static function retrieveCombinationChoices1($qeustionId, $combinationId, bool $withChoiceId, bool $withAnswer, string $language = 'ar')
    {
        try {
            // $result = null;
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
                    $temp['content'] = EnumTraits::getNameByNumber(CombinationChoiceTypeEnum::MIX->value, CombinationChoiceTypeEnum::class, $language);
                    $temp['ids'] = $choice->ids;

                    // if ($withChoiceId) {
                    //     $temp['id'] = CombinationChoiceTypeEnum::MIX->value;
                    // }
                } else {
                    if ($choice->id == -1) {
                        $temp['content'] = EnumTraits::getNameByNumber(CombinationChoiceTypeEnum::ALL->value, CombinationChoiceTypeEnum::class, $language);
                    } elseif ($choice->id == -2) {
                        $temp['content'] = EnumTraits::getNameByNumber(CombinationChoiceTypeEnum::NOTHING->value, CombinationChoiceTypeEnum::class, $language);
                    } else {
                        $temp = Choice::where('id', $choice->id)->first(['content', 'attachment as attachment_url']);
                    }

                    // if ($withChoiceId) {
                    $temp['id'] = $choice->id;
                    // }
                }

                if ($withAnswer) {
                    $temp['is_true'] = $choice->isCorrect;
                }

                array_push($result, $temp);
            }

            $mixIds = collect($result)->filter(function ($item) {
                if (isset($item['ids'])) {
                    return $item;
                }
            })->map(function ($item) {
                return collect($item['ids'])->toArray();
            })->first();

            $isMixTrue = collect($result)->filter(function ($item) {
                if (isset($item['is_true'])) {
                    return $item;
                }
            })->map(function ($item) {
                return $item['is_true'];
            })->first();

            $finalChoices = [];

            if (!isset($mixIds) && empty($mixIds)) {
                foreach ($result as $item) {
                    if (!$withChoiceId) {
                        unset($item['id']);
                    }
                    $finalChoices[] = $item;
                }
            } else {
                $mixed = collect($result)->filter(function ($item) use ($mixIds) {
                    return !isset($item['ids']) && !is_null($mixIds) && in_array($item->id, $mixIds);
                })->toArray();

                $unmixed = collect($result)->filter(function ($item) use ($mixIds) {
                    return !isset($item['ids']) && ((!is_null($mixIds) && !in_array($item->id, $mixIds)) || is_null($mixIds));
                })->toArray();

                if (!$withChoiceId) {
                    $mixed = collect($mixed)->map(function ($item) {
                        unset($item['id']);
                        return $item;
                    });

                    $unmixed = collect($unmixed)->map(function ($item) {
                        unset($item['id']);
                        return $item;
                    });
                }

                if (!empty($mixed)) {
                    $finalMixed = [];

                    foreach ($mixed as $mixedItem) {
                        $finalMixed[] = $mixedItem;
                    }
                    // $finalChoices[] = ['mix' => $finalMixed];

                    // if (!$withChoiceId) {
                    //     $finalChoices[] = [
                    //         'mix' => $finalMixed
                    //     ];
                    // } else {
                    //     $finalChoices[] = [
                    //         'id' => CombinationChoiceTypeEnum::MIX->value,
                    //         'mix' => $finalMixed
                    //     ];
                    // }

                    $final['mix'] = $finalMixed;

                    if ($withChoiceId) {
                        $final['id'] = CombinationChoiceTypeEnum::MIX->value;
                    }

                    if ($withAnswer) {
                        $final['is_true'] = $isMixTrue;
                    }

                    $finalChoices[] = $final;
                }

                // foreach ($unmixed as $unmixedItem) {
                //     $finalChoices[] = $unmixedItem;
                // }

                if (!empty($unmixed)) {
                    $finalUnmixed = [];

                    foreach ($unmixed as $unmixedItem) {
                        $finalUnmixed[] = $unmixedItem;
                    }

                    $final2['unmix'] = $finalUnmixed;

                    $finalChoices[] = $final2;
                }
            }

            return $finalChoices;
        } catch (\Exception $e) {
            throw $e;
        }
    }


    private static function uncombinateCombination($qeustionId, $combinationId)
    {
        try {
            $combinationChoices = QuestionChoicesCombination::where('combination_id', '=', $combinationId)
                ->where('question_id', '=', $qeustionId)
                ->first(['combination_choices'])['combination_choices'];

            $choices = (new UncombineQuestionChoicesCombination())->execute($combinationChoices);

            return $choices;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    private static function retrieveCombinationChoicesWithIdAndAnswer($choices)
    {
        try {
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
        } catch (\Exception $e) {
            throw $e;
        }
    }

    private static function retrieveCombinationChoicesWithId($choices)
    {
        try {
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
        } catch (\Exception $e) {
            throw $e;
        }
    }

    private static function retrieveCombinationChoicesWithAnswer($choices)
    {
        try {
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
        } catch (\Exception $e) {
            throw $e;
        }
    }

    private static function retrieveCombinationChoicesWithoutIdAndAnswer($choices)
    {
        try {
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
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
