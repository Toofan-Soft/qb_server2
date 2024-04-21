<?php

namespace App\Http\Controllers;

use App\Enums\AccessibilityStatusEnum;
use App\Enums\CoursePartsEnum;
use App\Enums\CourseStatusEnum;
use App\Enums\CourseStudentStatusEnum;
use App\Enums\ExamConductMethodEnum;
use App\Enums\ExamDifficultyLevelEnum;
use App\Enums\ExamStatusEnum;
use App\Enums\ExamTypeEnum;
use App\Enums\FormConfigurationMethodEnum;
use App\Enums\FormNameEnum;
use App\Enums\FormNameMethodEnum;
use App\Enums\GenderEnum;
use App\Enums\JobTypeEnum;
use App\Enums\LanguageEnum;
use App\Enums\OnlineExamTakingStatusEnum;
use App\Enums\OwnerTypeEnum;
use App\Enums\QualificationEnum;
use App\Enums\QuestionStatusEnum;
use App\Enums\QuestionTypeEnum;
use App\Enums\RoleEnum;
use App\Enums\SemesterEnum;
use App\Enums\StudentOnlineExamStatusEnum;
use App\Enums\UserStatusEnum;
use App\Helpers\ResponseHelper;
use App\Models\CoursePart;
use Illuminate\Http\Request;

class EnumsController extends Controller
{
    public function retrieveCourseStatus  ()
    {
        return ResponseHelper::successWithData(CourseStatusEnum::getEnum());
    }

    public function retrieveCourseParts  ()
    {
        return ResponseHelper::successWithData(CoursePartsEnum::getEnum());
    }

    public function retrieveLanguages   ()
    {
        return ResponseHelper::successWithData(LanguageEnum::getEnum());
    }

    public function retrieveDifficultyLevels   ()
    {
        return ResponseHelper::successWithData(ExamDifficultyLevelEnum::getEnum());

    }

    public function  retrieveQuestionTypes    ()
    {
        return ResponseHelper::successWithData(QuestionTypeEnum::getEnum());
    }

    public function  retrieveAccessibilityStatus    ()
    {
        return ResponseHelper::successWithData(AccessibilityStatusEnum::getEnum());
    }

    public function  retrieveQuestionStatus     ()
    {
        return ResponseHelper::successWithData(QuestionStatusEnum::getEnum());
    }

    public function  retrieveAcceptanceStatus     ()
    {
        // enum not found
        //return response()->json(['data' => AcceptanceStatusEnum::getEnum()], 200);
    }

    public function  retrieveSemesters    ()
    {
        return ResponseHelper::successWithData(SemesterEnum::getEnum());
    }
    
    public function  retrieveJobTypes    ()
    {
        return ResponseHelper::successWithData(JobTypeEnum::getEnum());
    }

    public function  retrieveQualifications     ()
    {
        return ResponseHelper::successWithData(QualificationEnum::getEnum());
    }

    public function  retrieveGenders     ()
    {
        return ResponseHelper::successWithData(GenderEnum::getEnum());
    }

    public function  retrieveCourseStudentStatus     ()
    {
        return ResponseHelper::successWithData(CourseStudentStatusEnum::getEnum());
    }

    public function  retrieveOwnerTypes    ()
    {
        return ResponseHelper::successWithData(OwnerTypeEnum::getEnum());
    }

    public function  retrieveUserStatus     ()
    {
        return ResponseHelper::successWithData(UserStatusEnum::getEnum());
    }

    public function  retrieveConductMethods     ()
    {
        return ResponseHelper::successWithData(ExamConductMethodEnum::getEnum());
    }

    public function  retrieveExamTypes    ()
    {
        return ResponseHelper::successWithData(ExamTypeEnum::getEnum());
    }

    public function  retrieveformConfigurationMethods    ()
    {
        return ResponseHelper::successWithData(FormConfigurationMethodEnum::getEnum());
    }

    public function  retrieveformNameMethods   ()
    {
        return ResponseHelper::successWithData(FormNameMethodEnum::getEnum());
    }

    public function  retrieveOnlineExamStatus     ()
    {
        return response()->json(['data' => ExamStatusEnum::getEnum()], 200);
        return ResponseHelper::successWithData(ExamStatusEnum::getEnum());
    }

    public function  retrieveStudentOnlineExamStatus    ()
    {
        return ResponseHelper::successWithData(StudentOnlineExamStatusEnum::getEnum());
    }

    public function  retrieveOnlineExamTakingStatus    ()
    {
        return ResponseHelper::successWithData(OnlineExamTakingStatusEnum::getEnum());
    }

}
