<?php

namespace App\Helpers;

use App\Models\Form;
use App\Models\Student;
use App\Models\RealExam;
use App\Enums\LevelsEnum;
use App\Enums\ExamTypeEnum;
use App\Enums\LanguageEnum;
use App\Enums\SemesterEnum;
use Illuminate\Http\Request;
use App\Enums\CoursePartsEnum;
use App\Enums\QuestionTypeEnum;
use App\Helpers\EnumReplacement1;
use Illuminate\Http\UploadedFile;
use App\Helpers\ProcessDataHelper;
use Illuminate\Support\Facades\DB;
use App\Enums\ExamConductMethodEnum;
use Illuminate\Support\Facades\Storage;
use App\Enums\FormConfigurationMethodEnum;
use App\Enums\StudentOnlineExamStatusEnum;

class OnlinExamHelper
{
    /**
     * sumation the score of exams .
     */
    public static function getExamsScore($data){

        foreach ($data as $onlineExam) {
            $realExam = RealExam::find($onlineExam->id);
            $realExamQuestionTypes = $realExam->real_exam_question_types()->get(['questions_count', ' question_score']);
            $score = 0;
            foreach ($realExamQuestionTypes as $realExamQuestionType) {
                $score += $realExamQuestionType->questions_count * $realExamQuestionType->question_score;
            }
            $onlineExam['score'] = $score;
            $score = 0;
        }

        return $data;
    }

    public static function getExamFormsNames($form_name_method, $forms_count){

        ////
        return [];
    }

    public static function getStudentFormName($onlineExamId, $studentFormId):string{

        try {
            $exam = RealExam::findOrFail($onlineExamId);
            // $forms = $realExam->forms()->get(['id']);

            $studentForm = '';

            $formsIds = $exam->forms()->get(['id'])
            ->map(function ($form) {
                return $form->id;
            });

            $formsNames = ExamHelper::getRealExamFormsNames(intval($exam->form_name_method), $exam->forms_count);

            if (intval($exam->form_configuration_method) === FormConfigurationMethodEnum::DIFFERENT_FORMS->value) {
                $i = 0;
                
               foreach ($formsIds as $formId) {
                if($formId === $studentFormId){
                    $studentForm = $formsNames[$i++];
                }
                $i++;
                }
            } else {
                $studentForm = $formsNames[0];
            }
           
            return $studentForm ;
        } catch (\Exception $e) {
            throw $e;
        }

    }


    public static function getStudentForm($realExam){
        // يتم عمل دالة تختار لي رقم النموذج المناسب لطالب
        $formId = 0;
        $form = Form::findOrFail($formId);
        return $form;

    }


}
