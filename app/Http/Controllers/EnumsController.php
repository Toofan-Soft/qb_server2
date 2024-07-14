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
use App\Enums\CoursePartStatusEnum;
use App\Enums\ExamConductMethodEnum;
use App\Enums\AccessibilityStatusEnum;
use App\Enums\ChapterStatusEnum;
use App\Enums\CourseStudentStatusEnum;
use App\Enums\ExamDifficultyLevelEnum;
use App\Enums\OnlineExamTakingStatusEnum;
use App\Enums\FormConfigurationMethodEnum;
use App\Enums\LevelsCountEnum;
use App\Enums\StudentOnlineExamStatusEnum;
use App\Helpers\LanguageHelper;

class EnumsController extends Controller
{
    public function retrieveCoursePartStatus()
    {
        return ResponseHelper::successWithData(EnumTraits::getEnum(CoursePartStatusEnum::class, LanguageHelper::getEnumLanguageName()));
    }
    public function retrieveChapterStatus()
    {
        return ResponseHelper::successWithData(EnumTraits::getEnum(ChapterStatusEnum::class, LanguageHelper::getEnumLanguageName()));
    }

    public function retrieveCourseParts()
    {
        return ResponseHelper::successWithData(EnumTraits::getEnum(CoursePartsEnum::class, LanguageHelper::getEnumLanguageName()));
    }

    public function retrieveLanguages()
    {
        return ResponseHelper::successWithData(EnumTraits::getEnum(LanguageEnum::class, LanguageHelper::getEnumLanguageName()));
    }

    public function retrieveDifficultyLevels()
    {
        return ResponseHelper::successWithData(EnumTraits::getEnum(ExamDifficultyLevelEnum::class, LanguageHelper::getEnumLanguageName()));
    }

    public function  retrieveQuestionTypes()
    {

        return ResponseHelper::successWithData(EnumTraits::getEnum(QuestionTypeEnum::class, LanguageHelper::getEnumLanguageName()));
    }

    public function  retrieveAccessibilityStatus()
    {
        return ResponseHelper::successWithData(EnumTraits::getEnum(AccessibilityStatusEnum::class, LanguageHelper::getEnumLanguageName()));
    }

    public function  retrieveQuestionStatus()
    {
        return ResponseHelper::successWithData(EnumTraits::getEnum(QuestionStatusEnum::class, LanguageHelper::getEnumLanguageName()));
    }

    // public function  retrieveAcceptanceStatus()
    // {
    //     // enum not found
    //     // return response()->json(['data' => AcceptanceStatusEnum::getEnum()], 200);
    // }

    public function  retrieveSemesters()
    {
        return ResponseHelper::successWithData(EnumTraits::getEnum(SemesterEnum::class, LanguageHelper::getEnumLanguageName()));
    }

    public function  retrieveLevelsCounts()
    {
        return ResponseHelper::successWithData(EnumTraits::getEnum(LevelsCountEnum::class, LanguageHelper::getEnumLanguageName()));
    }

    public function  retrieveJobTypes()
    {
        return ResponseHelper::successWithData(EnumTraits::getEnum(JobTypeEnum::class, LanguageHelper::getEnumLanguageName()));
    }

    public function  retrieveQualifications()
    {

        return ResponseHelper::successWithData(EnumTraits::getEnum(QualificationEnum::class, LanguageHelper::getEnumLanguageName()));
    }

    public function  retrieveGenders()
    {
        return ResponseHelper::successWithData(EnumTraits::getEnum(GenderEnum::class, LanguageHelper::getEnumLanguageName()));
    }
    public function  retrieveLoginGenders()
    {
        return ResponseHelper::successWithData(EnumTraits::getEnum(GenderEnum::class));
    }

    public function  retrieveCourseStudentStatus()
    {
        return ResponseHelper::successWithData(EnumTraits::getEnum(CourseStudentStatusEnum::class, LanguageHelper::getEnumLanguageName()));
    }

    public function  retrieveOwnerTypes()
    {
        $types = OwnerTypeEnum::getAvailableValues();
        return ResponseHelper::successWithData($types);

        // return ResponseHelper::successWithData(EnumTraits::getEnum(OwnerTypeEnum::class, LanguageHelper::getEnumLanguageName()));
    }

    public function  retrieveUserStatus()
    {
        return ResponseHelper::successWithData(EnumTraits::getEnum(UserStatusEnum::class, LanguageHelper::getEnumLanguageName()));
    }

    public function  retrieveConductMethods()
    {
        return ResponseHelper::successWithData(EnumTraits::getEnum(ExamConductMethodEnum::class, LanguageHelper::getEnumLanguageName()));
    }

    public function  retrieveExamTypes()
    {
        return ResponseHelper::successWithData(EnumTraits::getEnum(ExamTypeEnum::class, LanguageHelper::getEnumLanguageName()));
    }

    public function  retrieveformConfigurationMethods()
    {
        return ResponseHelper::successWithData(EnumTraits::getEnum(FormConfigurationMethodEnum::class, LanguageHelper::getEnumLanguageName()));
    }

    public function  retrieveformNameMethods()
    {
        return ResponseHelper::successWithData(EnumTraits::getEnum(FormNameMethodEnum::class, LanguageHelper::getEnumLanguageName()));
    }

    public function  retrieveOnlineExamStatus()
    {
        return ResponseHelper::successWithData(EnumTraits::getEnum(ExamStatusEnum::class, LanguageHelper::getEnumLanguageName()));
    }

    public function  retrieveStudentOnlineExamStatus()
    {
        return ResponseHelper::successWithData(EnumTraits::getEnum(StudentOnlineExamStatusEnum::class, LanguageHelper::getEnumLanguageName()));
    }

    public function  retrieveOnlineExamTakingStatus()
    {
        return ResponseHelper::successWithData(EnumTraits::getEnum(OnlineExamTakingStatusEnum::class, LanguageHelper::getEnumLanguageName()));
    }
}
