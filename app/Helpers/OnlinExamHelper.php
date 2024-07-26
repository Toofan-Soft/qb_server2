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
            $realExam = RealExam::findOrFail($onlineExamId);
            $language = LanguageEnum::symbolOf(intval($realExam->language));

            $studentFormName = '';

            $formsIds = $realExam->forms()->orderBy('id')->pluck('id')->toArray();

            $formsNames = ExamHelper::getRealExamFormsNames(intval($realExam->form_name_method), $realExam->forms_count, $language);

            if (intval($realExam->form_configuration_method) === FormConfigurationMethodEnum::DIFFERENT_FORMS->value) {
                $i = 0;
                
               foreach ($formsIds as $formId) {
                if($formId === $studentFormId){
                    $studentFormName = $formsNames[$i];
                    break;
                }
                $i++;
                }
            } else {
                $studentFormName = $formsNames[0];
            }
           
            return $studentFormName ;
        } catch (\Exception $e) {
            throw $e;
        }

    }

}
