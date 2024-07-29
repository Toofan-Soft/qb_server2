<?php

namespace App\Http\Controllers;

use App\Enums\GenderEnum;
use App\Enums\JobTypeEnum;
use App\Traits\EnumTraits;
use App\Enums\ExamTypeEnum;
use App\Enums\LanguageEnum;
use App\Enums\SemesterEnum;
use App\Enums\OwnerTypeEnum;
use App\Enums\UserStatusEnum;
use App\Enums\CoursePartsEnum;
use App\Enums\LevelsCountEnum;
use App\Enums\QuestionTypeEnum;
use App\Helpers\LanguageHelper;
use App\Helpers\ResponseHelper;
use App\Enums\ChapterStatusEnum;
use App\Enums\QualificationEnum;
use App\Enums\FormNameMethodEnum;
use App\Enums\QuestionStatusEnum;
use App\Enums\CoursePartStatusEnum;
use App\Enums\OnlineExamStatusEnum;
use App\Enums\ExamConductMethodEnum;
use App\Enums\PracticeExamStatusEnum;
use App\Enums\AccessibilityStatusEnum;
use App\Enums\CourseStudentStatusEnum;
use App\Enums\ExamDifficultyLevelEnum;
use App\Enums\OnlineExamTakingStatusEnum;
use App\Enums\FormConfigurationMethodEnum;
use App\Enums\StudentOnlineExamStatusEnum;

class EnumsController extends Controller
{
    public function retrieveCoursePartStatus()
    {
        try {
            return ResponseHelper::successWithData(EnumTraits::getEnum(CoursePartStatusEnum::class, LanguageHelper::getEnumLanguageName()));
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }
    public function retrieveChapterStatus()
    {
        try {
            return ResponseHelper::successWithData(EnumTraits::getEnum(ChapterStatusEnum::class, LanguageHelper::getEnumLanguageName()));
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveCourseParts()
    {
        try {
            return ResponseHelper::successWithData(EnumTraits::getEnum(CoursePartsEnum::class, LanguageHelper::getEnumLanguageName()));
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveLanguages()
    {
        try {
            return ResponseHelper::successWithData(EnumTraits::getEnum(LanguageEnum::class, LanguageHelper::getEnumLanguageName()));
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveDifficultyLevels()
    {
        try {
            return ResponseHelper::successWithData(EnumTraits::getEnum(ExamDifficultyLevelEnum::class, LanguageHelper::getEnumLanguageName()));
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function  retrieveQuestionTypes()
    {

        try {
            return ResponseHelper::successWithData(EnumTraits::getEnum(QuestionTypeEnum::class, LanguageHelper::getEnumLanguageName()));
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function  retrieveAccessibilityStatus()
    {
        try {
            return ResponseHelper::successWithData(EnumTraits::getEnum(AccessibilityStatusEnum::class, LanguageHelper::getEnumLanguageName()));
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function  retrieveQuestionStatus()
    {
        try {
            return ResponseHelper::successWithData(EnumTraits::getEnum(QuestionStatusEnum::class, LanguageHelper::getEnumLanguageName()));
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function  retrieveSemesters()
    {
        try {
            return ResponseHelper::successWithData(EnumTraits::getEnum(SemesterEnum::class, LanguageHelper::getEnumLanguageName()));
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function  retrieveLevelsCounts()
    {
        try {
            return ResponseHelper::successWithData(EnumTraits::getEnum(LevelsCountEnum::class, LanguageHelper::getEnumLanguageName()));
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function  retrieveJobTypes()
    {
        try {
            return ResponseHelper::successWithData(EnumTraits::getEnum(JobTypeEnum::class, LanguageHelper::getEnumLanguageName()));
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function  retrieveQualifications()
    {

        try {
            return ResponseHelper::successWithData(EnumTraits::getEnum(QualificationEnum::class, LanguageHelper::getEnumLanguageName()));
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function  retrieveGenders()
    {
        try {
            return ResponseHelper::successWithData(EnumTraits::getEnum(GenderEnum::class, LanguageHelper::getEnumLanguageName()));
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }
    public function  retrieveRegisterGenders()
    {
        try {
            return ResponseHelper::successWithData(EnumTraits::getEnum(GenderEnum::class));
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function  retrieveCourseStudentStatus()
    {
        try {
            return ResponseHelper::successWithData(EnumTraits::getEnum(CourseStudentStatusEnum::class, LanguageHelper::getEnumLanguageName()));
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function  retrieveOwnerTypes()
    {

        try {
            $types = OwnerTypeEnum::getAvailableValues();
            ResponseHelper::successWithData($types);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function  retrieveUserStatus()
    {
        try {
            return ResponseHelper::successWithData(EnumTraits::getEnum(UserStatusEnum::class, LanguageHelper::getEnumLanguageName()));
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function  retrieveConductMethods()
    {
        try {
            return ResponseHelper::successWithData(EnumTraits::getEnum(ExamConductMethodEnum::class, LanguageHelper::getEnumLanguageName()));
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function  retrieveExamTypes()
    {
        try {
            return ResponseHelper::successWithData(EnumTraits::getEnum(ExamTypeEnum::class, LanguageHelper::getEnumLanguageName()));
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function  retrieveformConfigurationMethods()
    {
        try {
            return ResponseHelper::successWithData(EnumTraits::getEnum(FormConfigurationMethodEnum::class, LanguageHelper::getEnumLanguageName()));
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function  retrieveformNameMethods()
    {
        try {
            return ResponseHelper::successWithData(EnumTraits::getEnum(FormNameMethodEnum::class, LanguageHelper::getEnumLanguageName()));
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function  retrieveOnlineExamStatus()
    {
        try {
            return ResponseHelper::successWithData(EnumTraits::getEnum(OnlineExamStatusEnum::class, LanguageHelper::getEnumLanguageName()));
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrievePracticeExamStatus()
    {
        try {
            return ResponseHelper::successWithData(EnumTraits::getEnum(PracticeExamStatusEnum::class, LanguageHelper::getEnumLanguageName()));
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function  retrieveStudentOnlineExamStatus()
    {
        try {
            return ResponseHelper::successWithData(EnumTraits::getEnum(StudentOnlineExamStatusEnum::class, LanguageHelper::getEnumLanguageName()));
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function  retrieveOnlineExamTakingStatus()
    {
        try {
            return ResponseHelper::successWithData(EnumTraits::getEnum(OnlineExamTakingStatusEnum::class, LanguageHelper::getEnumLanguageName()));
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }
}
