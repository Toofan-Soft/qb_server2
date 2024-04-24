<?php

namespace App\Algorithm\AlgorithmController;

use App\Enums\AccessibilityStatusEnum;
use App\Enums\QuestionStatusEnum;
use Illuminate\Support\Facades\DB;

class GeneratePractiseExam
{

    public static function execute($request)
    {
        $examData = $this->retrievePractiseExamData($request);
        $questions = $this->retrieveQuestions($request);

        // call algorithm model and recive output 
        // عملية الاستدعاء تكون مرتين، اولا لتكوين الاسئلة ثم اختيار توزيعة الاختيارات
        // save output
        // هذه الدالة بترجع صح اذا نجحت كل العمليات، وبيرجع خطى اذا حصل فشل 

    }

    private static function retrievePractiseExamData($request)
    {
        $examData = [
            'exam_type' => 'practise_exam',
            'duration' => $request->duration,
            'language_id' => $request->language_id,
            'difficulty_level_id' => $request->difficulty_level_id,
            // 'forms_count' => $request->forms_count,
            // 'form_configuration_method_id' => $request->form_configuration_method_id,
            'questions_types' => $request->questions_types,
        ];
        return $examData;
    }


    private static function retrieveQuestions($request)
    {
        $questionTypesIds = $request->questions_types['type_id']; // التحقق من ان نحصل على مصفوفه 
        $accessabilityStatusIds = [
            AccessibilityStatusEnum::PRACTICE_EXAM->value,
            AccessibilityStatusEnum::PRACTICE_REALEXAM->value,
        ];
        $questions =  DB::table('questions')
            ->join('question_usages', 'questions.id', '=', 'question_usages.question_id')
            ->join('topics', 'questions.topic_id', '=', 'topics.id')
            ->select(
                'questions.id',
                'questions.type',
                'questions.difficulty_level',
                'questions.estimated_answer_time',
                'question_usages.online_exam_last_selection_datetime',
                'question_usages.practice_exam_last_selection_datetime',
                'question_usages.paper_exam_last_selection_datetime',
                'question_usages.online_exam_selection_times_count',
                'question_usages.practice_exam_selection_times_count',
                'question_usages.paper_exam_selection_times_count',
                'topics.id',
                'topics.chapter_id'
            )
            ->where('questions.status', '=', QuestionStatusEnum::ACCEPTED->value)
            ->where('questions.language', '=', $request->language_id)
            ->whereIn('questions.accessability_status', $accessabilityStatusIds)
            ->whereIn('questions.type', $questionTypesIds)
            ->whereIn('topics.id', $request->topicsIds)
            ->get();
        return $questions;
    }

    private static function generatePractiseExamQuestions(){

    }
    private static function selectPractiseExamQuestionsChoicesCombination(){
        
    }
}

