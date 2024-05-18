<?php

namespace App\Http\Controllers;

use App\Enums\RoleEnum;
use App\Enums\GenderEnum;
use App\Enums\JobTypeEnum;
use App\Models\CoursePart;
use App\Traits\EnumTraits;
use App\Enums\ExamTypeEnum;
use App\Enums\FormNameEnum;
use App\Enums\LanguageEnum;
use App\Enums\SemesterEnum;
use App\Enums\OwnerTypeEnum;
use Illuminate\Http\Request;
use App\Enums\ExamStatusEnum;
use App\Enums\UserStatusEnum;
use App\Enums\CoursePartsEnum;
use App\Enums\CourseStatusEnum;
use App\Enums\QuestionTypeEnum;
use App\Helpers\ResponseHelper;
use App\Enums\QualificationEnum;
use App\Enums\FormNameMethodEnum;
use App\Enums\QuestionStatusEnum;
use App\Enums\ExamConductMethodEnum;
use App\Enums\AccessibilityStatusEnum;
use App\Enums\CourseStudentStatusEnum;
use App\Enums\ExamDifficultyLevelEnum;
use App\Enums\OnlineExamTakingStatusEnum;
use App\Enums\FormConfigurationMethodEnum;
use App\Enums\StudentOnlineExamStatusEnum;

class EnumsController extends Controller
{
    public function retrieveCourseStatus  ()
    {
        return ResponseHelper::successWithData(EnumTraits::getEnum(CourseStatusEnum::class));
    }

    public function retrieveCourseParts  ()
    {
        return ResponseHelper::successWithData(EnumTraits::getEnum(CoursePartsEnum::class));
    }

    public function retrieveLanguages   ()
    {
        return ResponseHelper::successWithData(EnumTraits::getEnum(LanguageEnum::class));
    }

    public function retrieveDifficultyLevels   ()
    {
        return ResponseHelper::successWithData(EnumTraits::getEnum(ExamDifficultyLevelEnum::class));
    }

    public function  retrieveQuestionTypes    ()
    {

        return ResponseHelper::successWithData(EnumTraits::getEnum(QuestionTypeEnum::class));
    }

    public function  retrieveAccessibilityStatus    ()
    {
        return ResponseHelper::successWithData(EnumTraits::getEnum(AccessibilityStatusEnum::class));
    }

    public function  retrieveQuestionStatus     ()
    {
        return ResponseHelper::successWithData(EnumTraits::getEnum(QuestionStatusEnum::class));
    }

    public function  retrieveAcceptanceStatus     ()
    {
        // enum not found
        //return response()->json(['data' => AcceptanceStatusEnum::getEnum()], 200);
    }

    public function  retrieveSemesters    ()
    {

        return ResponseHelper::successWithData(EnumTraits::getEnum(SemesterEnum::class));
    }

    public function  retrieveJobTypes    ()
    {
        return ResponseHelper::successWithData(EnumTraits::getEnum(JobTypeEnum::class));
    }

    public function  retrieveQualifications     ()
    {

        return ResponseHelper::successWithData(EnumTraits::getEnum(QualificationEnum::class));
    }

    public function  retrieveGenders ()
    {

        return ResponseHelper::successWithData(EnumTraits::getEnum(GenderEnum::class));
    }

    public function  retrieveCourseStudentStatus     ()
    {
        return ResponseHelper::successWithData(EnumTraits::getEnum(CourseStudentStatusEnum::class));
    }

    public function  retrieveOwnerTypes    ()
    {
        return ResponseHelper::successWithData(EnumTraits::getEnum(OwnerTypeEnum::class));
    }

    public function  retrieveUserStatus     ()
    {

        return ResponseHelper::successWithData(EnumTraits::getEnum(UserStatusEnum::class));
    }

    public function  retrieveConductMethods     ()
    {
        return ResponseHelper::successWithData(EnumTraits::getEnum(ExamConductMethodEnum::class));
    }

    public function  retrieveExamTypes    ()
    {
        return ResponseHelper::successWithData(EnumTraits::getEnum(ExamTypeEnum::class));
    }

    public function  retrieveformConfigurationMethods    ()
    {
        return ResponseHelper::successWithData(EnumTraits::getEnum(FormConfigurationMethodEnum::class));

    }

    public function  retrieveformNameMethods   ()
    {
        return ResponseHelper::successWithData(EnumTraits::getEnum(FormNameMethodEnum::class));

    }

    public function  retrieveOnlineExamStatus     ()
    {
        return ResponseHelper::successWithData(EnumTraits::getEnum(ExamStatusEnum::class));

    }

    public function  retrieveStudentOnlineExamStatus    ()
    {
        return ResponseHelper::successWithData(EnumTraits::getEnum(StudentOnlineExamStatusEnum::class));

    }

    public function  retrieveOnlineExamTakingStatus    ()
    {
        return ResponseHelper::successWithData(EnumTraits::getEnum(OnlineExamTakingStatusEnum::class));

    }

}
