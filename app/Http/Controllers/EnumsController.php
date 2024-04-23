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
use App\Enums\GenderEnum;
use App\Enums\JobTypeEnum;
use App\Enums\LanguageEnum;
use App\Enums\OwnerTypeEnum;
use App\Enums\QualificationEnum;
use App\Enums\QuestionStatusEnum;
use App\Enums\QuestionTypeEnum;
use App\Enums\RoleEnum;
use App\Enums\SemesterEnum;
use App\Enums\StudentOnlineExamStatusEnum;
use App\Enums\UserStatusEnum;
use App\Models\CoursePart;
use Illuminate\Http\Request;

class EnumsController extends Controller
{
    public function retrieveCourseStatus  ()
    {
        return response()->json(['data' => CourseStatusEnum::getEnum()], 200);
    }


    public function retrieveCourseParts  ()
    {
        return response()->json(['data' => CoursePartsEnum::getEnum()], 200);
    }
    public function retrieveLanguages   ()
    {
        return response()->json(['data' => LanguageEnum::getEnum()], 200);
    }
    public function retrieveDifficultyLevels   ()
    {
        return response()->json(['data' => ExamDifficultyLevelEnum::getEnum()], 200);
    }
    public function  retrieveQuestionTypes    ()
    {
        return response()->json(['data' => QuestionTypeEnum::getEnum()], 200);
    }
    public function  retrieveAccessibilityStatus    ()
    {
        return response()->json(['data' => AccessibilityStatusEnum::getEnum()], 200);
    }
    public function  retrieveQuestionStatus     ()
    {
        return response()->json(['data' => QuestionStatusEnum::getEnum()], 200);
    }
    public function  retrieveAcceptanceStatus     ()
    {
        // enum not found
        //return response()->json(['data' => AcceptanceStatusEnum::getEnum()], 200);
    }
    public function  retrieveSemesters    ()
    {
        return response()->json(['data' => SemesterEnum::getEnum()], 200);
    }
    public function  retrieveJobTypes    ()
    {
        return response()->json(['data' => JobTypeEnum::getEnum()], 200);
    }
    public function  retrieveQualifications     ()
    {
        return response()->json(['data' => QualificationEnum::getEnum()], 200);
    }
    public function  retrieveGenders     ()
    {
        return response()->json(['data' => GenderEnum::getEnum()], 200);
    }
    public function  retrieveCourseStudentStatus     ()
    {
        return response()->json(['data' => CourseStudentStatusEnum::getEnum()], 200);
    }
    public function  retrieveOwnerTypes    ()
    {
        return response()->json(['data' => OwnerTypeEnum::getEnum()], 200);
    }
    public function  retrieveRoles    ()
    {
        return response()->json(['data' => RoleEnum::getEnum()], 200);
    }
    public function  retrieveUserStatus     ()
    {
        return response()->json(['data' => UserStatusEnum::getEnum()], 200);
    }
    public function  retrieveConductMethods     ()
    {
        return response()->json(['data' => ExamConductMethodEnum::getEnum()], 200);
    }
    public function  retrieveExamTypes    ()
    {
        return response()->json(['data' => ExamTypeEnum::getEnum()], 200);
    }
    public function  retrieveformConfigurationMethods    ()
    {
        return response()->json(['data' => FormConfigurationMethodEnum::getEnum()], 200);
    }
    public function  retrieveformNameMethods   ()
    {
        return response()->json(['data' => FormNameEnum::getEnum()], 200);
    }
    public function  retrieveOnlineExamStatus     ()
    {
        /// ensure from enum
        return response()->json(['data' => ExamStatusEnum::getEnum()], 200);
    }
    public function  retrieveStudentOnlineExamStatus    ()
    {
        return response()->json(['data' => StudentOnlineExamStatusEnum::getEnum()], 200);
    }

}
