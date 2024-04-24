<?php

namespace App\Http\Controllers;

use App\Algorithm\Exam;
use App\Models\Form;
use App\Models\User;
use App\Models\Topic;
use App\Models\Employee;
use App\Models\RealExam;
use App\Enums\LevelsEnum;
use App\Helpers\AddHelper;
use App\Models\OnlineExam;
use App\Enums\ExamTypeEnum;
use App\Enums\LanguageEnum;
use App\Enums\SemesterEnum;
use App\Helpers\ExamHelper;
use Illuminate\Http\Request;
use App\Enums\ExamStatusEnum;
use App\Helpers\DeleteHelper;
use App\Enums\CoursePartsEnum;
use App\Models\CourseLecturer;
use App\Enums\QuestionTypeEnum;
use App\Enums\RealExamTypeEnum;
use App\Helpers\QuestionHelper;
use App\Helpers\ResponseHelper;
use App\Helpers\ValidateHelper;
use App\Helpers\EnumReplacement;
use App\Helpers\OnlinExamHelper;
use App\Enums\QuestionStatusEnum;
use App\Helpers\EnumReplacement1;
use App\Helpers\ProcessDataHelper;
use Illuminate\Support\Facades\DB;
use App\Enums\ExamConductMethodEnum;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rules\Enum;
use App\Enums\ExamDifficultyLevelEnum;
use App\Enums\FormConfigurationMethodEnum;
use App\Enums\FormNameMethodEnum;
use App\Helpers\ColumnReplacement;

class LecturerOnlinExamController extends Controller
{
    public function addOnlineExam(Request $request)
    {

        if ($x = ValidateHelper::validateData($request, $this->rules($request))) {
            return ResponseHelper::clientError1($x);
        }


        $user = User::findOrFail(auth()->user()->id);
        $employee = Employee::where('user_id',  $user->id )->first(); //6fb56ec0-76b9-3cc1-8367-0e2a5f65451d


        $courseLecturer = CourseLecturer::where('department_course_part_id', $request->department_course_part_id)  //;dd($courseLecturer);
            ->where('lecturer_id', $employee->id)  //$user->employee()->id
            // // ->where('academic_year', now()->format('Y'))
            ->first();


        $realExam = $courseLecturer->real_exams()->create([
        // $realExam = RealExam::create([
            'type' => $request->type_id,
            'datetime' => $request->datetime,
            'duration' => $request->duration,
            'language' => $request->language_id,
            'note' => $request->special_note ?? null,
            'difficulty_level' => $request->difficulty_level_id,
            'forms_count' => $request->forms_count,
            'form_configuration_method' => $request->form_configuration_method,
            'form_name_method' => $request->form_name_method,
            'exam_type' => RealExamTypeEnum::ONLINE->value,
            // 'course_lecturer_id' => $courseLecturer->id,
        ]);

        OnlineExam::create([
            'conduct_method' => $request->conduct_method_id,
            'exam_datetime_notification_datetime' => $request->datetime_notification_datetime,
            'result_notification_datetime'  => $request->result_notification_datetime,
            'proctor_id' => $request->proctor_id ?? null,
            'status' => ExamStatusEnum::ACTIVE->value,
            'id' => $realExam->id,
        ]);

        // foreach ($request->question_types as $question_type) {
            //how to save this data insid data of json into laravel db
            $realExam->real_exam_question_types()->create([
                'question_type' => $request->question_types['type_id'],
                'question_count' => $request->question_types['questions_count'],
                'question_score' => $request->question_types['question_score'],
            ]);
        // }

        if (intval($request->form_configuration_method) === FormConfigurationMethodEnum::SIMILAR_FORMS->value) {
            $realExam->forms()->create();
        } else {
            foreach ($request->forms_count as $form) {
                $realExam->forms()->create();
            }
        }
        Exam::generateOnlineExam($realExam->id);
        //////////add Topics of exam

        return ResponseHelper::successWithData($realExam->id);
    }

    public function modifyOnlineExam(Request $request)
    {

        $realExam = RealExam::findOrFail($request->id);
        $realExam->update([
            'type' => $request->type_id ?? $realExam->type,
            'datetime' => $request->datetime ?? $realExam->datetime,
            'note' => $request->special_note ?? $realExam->note,
            'form_name_method' => $request->form_name_method ?? $realExam->form_name_method,
        ]);

        $onlinExam = $realExam->online_exam();
        $onlinExam->update([
            'conduct_method' =>  $request->conduct_method_id ??  $onlinExam->conduct_method,
            'exam_datetime_notification_datetime' => $request->datetime_notification_datetime ?? $onlinExam->exam_datetime_notification_datetime,
            'result_notification_datetime'  => $request->result_notification_datetime ?? $onlinExam->result_notification_datetime,
            'proctor_id' => $request->proctor_id ?? $onlinExam->proctor_id,
        ]);

        return ResponseHelper::success();
    }


    public function deleteOnlineExam(Request $request)
    {
        // يتم حذف كل ما يتعلق بالاختبار وايضا اسئلة الاختبار التي قد تم توليدها
        return ExamHelper::deleteRealExam($request->id);
    }

    public function retrieveOnlineExams(Request $request) ////**** يتم اضافة شرط ان يتم ارجاع الاختبارات التي تنتمي الى المستخدم الحالي
    {

        $enumReplacements  = [];

        $onlineExams =  DB::table('real_exams')
            ->join('online_exams', 'real_exams.id', '=', 'online_exams.id')
            ->join('course_lecturers', 'real_exams.course_lecturer_id', '=', 'course_lecturers.id')
            ->join('department_course_parts', 'course_lecturers.department_course_part_id', '=', 'department_course_parts.id')
            ->select(
                'real_exams.id',
                'real_exams.datetime',
                'real_exams.forms_count',
            )
            ->when($request->status_id, function ($query) use ($request) {
                return $query->where('online_exams.status', '=', $request->status_id);
            })
            ->when($request->type_id, function ($query) use ($request) {
                return $query->where('real_exams.type', '=', $request->type_id);
            })
            ->when($request->type_id === null, function ($query) use ($request) {
                return $query->selectRaw('real_exams.type as type_name');
            })
            ->when($request->status_id === null, function ($query) use ($request) {
                return $query->selectRaw('online_exams.status as status_name');
            })
            ->where('department_course_parts.id', '=', $request->department_course_part_id)
            ->get();

        if (!$request->status_id && !$request->type_id) {

            array_push($enumReplacements,  new EnumReplacement('type_name', ExamTypeEnum::class));
            array_push($enumReplacements,  new EnumReplacement('status_name', ExamStatusEnum::class));
        } elseif ($request->status_id && !$request->type_id) {
            array_push($enumReplacements,  new EnumReplacement('type_name', ExamTypeEnum::class));
        } else {
            array_push($enumReplacements,  new EnumReplacement('status_name', ExamStatusEnum::class));
        }

        $onlineExams = ProcessDataHelper::enumsConvertIdToName($onlineExams, $enumReplacements);
        $onlineExams =  ExamHelper::getRealExamsScore($onlineExams); // sum score of
        return ResponseHelper::successWithData($onlineExams);
    }


    public function retrieveOnlineExamsAndroid(Request $request) ////////** this attribute department_course_part_id can be null
    {

        $enumReplacements  = [
            new EnumReplacement('type_name', ExamTypeEnum::class),
            new EnumReplacement('status_name', ExamStatusEnum::class),
            new EnumReplacement('course_part_name', CoursePartsEnum::class),
            new EnumReplacement('language_name', LanguageEnum::class),
        ];

        $onlineExams =  DB::table('real_exams')
            ->join('online_exams', 'real_exams.id', '=', 'online_exams.id')
            ->join('course_lecturers', 'real_exams.course_lecturer_id', '=', 'course_lecturers.id')
            ->join('department_course_parts', 'course_lecturers.department_course_part_id', '=', 'department_course_parts.id')
            ->join('department_courses', 'department_course_parts.department_course_id', '=', 'department_courses.id')
            ->join('courses', 'department_courses.course_id', '=', 'courses.id')
            ->join('course_parts', 'department_course_parts.course_part_id', '=', 'course_parts.id')
            ->select(
                'courses.arabic_name as course_name ',
                'course_parts.part_id as course_part_name ',
                'real_exams.id',
                'real_exams.datetime',
                'real_exams.language as language_name',
                'real_exams.type as type_name',
                'online_exams.status as status_name',
            )
            ->when($request->department_course_part_id, function ($query) use ($request) {
                return $query->where('department_course_parts.id', '=', $request->department_course_part_id);
            })
            ->when($request->status_id, function ($query) use ($request) {
                return $query->where('online_exams.status', '=', $request->stsatus_id);
            })
            ->when($request->type_id, function ($query) use ($request) {
                return $query->where('real_exams.type', '=', $request->type_id);
            })
            ->get();

        $onlineExams = ProcessDataHelper::enumsConvertIdToName($onlineExams, $enumReplacements);

        return ResponseHelper::successWithData($onlineExams);
    }

    public function retrieveOnlineExam(Request $request)
    {
        $realExam = RealExam::findOrFail($request->id, [
            'language as language_name', 'difficulty_level as defficulty_level_name',
            'forms_count', 'form_configuration_method as form_configuration_method_name', 'form_name_method as form_name_method_name',
            'datetime', 'duration', 'type as type_name', 'note as special_note'
        ]);
        $realExam = ProcessDataHelper::enumsConvertIdToName($realExam, [
            new EnumReplacement('language_name', LanguageEnum::class),
            new EnumReplacement('defficulty_level_name', ExamDifficultyLevelEnum::class),
            new EnumReplacement('form_configuration_method_name', FormConfigurationMethodEnum::class),
            new EnumReplacement('form_name_method_name', FormNameMethodEnum::class),
            new EnumReplacement('type_name', ExamTypeEnum::class),
        ]);
        $onlineExam = $realExam->online_exam()->get(['conduct_method as conduct_method_name', 'status as status_name', 'proctor_id as proctor_name', 'exam_datetime_notification_datetime as datetime_notification_datetime', 'result_notification_datetime']);
        $onlineExam = ProcessDataHelper::enumsConvertIdToName($onlineExam, [
            new EnumReplacement('status_name', ExamStatusEnum::class),
            new EnumReplacement('conduct_method_name', ExamStatusEnum::class),
        ]);
        $onlineExam = ProcessDataHelper::columnConvertIdToName($onlineExam, [
            new ColumnReplacement('proctor_name', 'arabic_name', Employee::class),
        ]);

        $departmentCoursePart = $realExam->lecturer_course()->department_course_part();
        $coursePart = $departmentCoursePart->course_part(['part_id as course_part_name']);
        $coursePart = ProcessDataHelper::enumsConvertIdToName($coursePart, [
            new EnumReplacement('course_part_name', CoursePartsEnum::class),
        ]);
        $departmentCourse = $departmentCoursePart->department_course()->get(['level as level_name', 'semester as semester_name']);
        $departmentCourse = ProcessDataHelper::enumsConvertIdToName($departmentCourse, [
            new EnumReplacement('level_name', LevelsEnum::class),
            new EnumReplacement('semester_name', SemesterEnum::class),
        ]);
        $department = $departmentCourse->department()->get(['arabic_name as department_name']);
        $college = $department->college()->get(['arabic_name as college_name']);
        $course = $departmentCourse->course()->get(['arabic_name as course_name']);
        $questionTypes = $realExam->real_exam_question_types()->get(['question_type as type_name', 'questions_count', 'question_score'])->toArray();
        $questionTypes = ProcessDataHelper::enumsConvertIdToName($questionTypes, [
            new EnumReplacement('type_name', QuestionTypeEnum::class),
        ]);

        array_merge($realExam, $onlineExam, $coursePart, $departmentCourse, $department, $college, $course); // merge all with realExam
        $realExam['questionTypes'] = $questionTypes;

        return ResponseHelper::successWithData($realExam);
    }

    public function retrieveEditableOnlineExam(Request $request)
    {
        $realExam = RealExam::findOrFail($request->id, [
            'form_name_method as form_name_method_id',
            'datetime', 'type as type_id', 'note as special_note'
        ]);

        $onlinExam = $realExam->online_exam()->get([
            'conduct_method as conduct_method_id',
            'exam_datetime_notification_datetime as datetime_notification_datetime',
            'proctor_id', 'result_notification_datetime'
        ]);

        array_merge($realExam, $onlinExam); // merge all with realExam

        return ResponseHelper::successWithData($realExam);
    }

    public function retrieveOnlineExamChapters(Request $request)
    {
        $onlineExamChapters = ExamHelper::retrieveRealExamChapters($request->exam_id);
        return ResponseHelper::successWithData($onlineExamChapters);
    }

    public function retrieveOnlineExamChapterTopics(Request $request)
    {
        $onlineExamChapterTopics = ExamHelper::retrieveRealExamChapterTopics($request->exam_id, $request->chapter_id);
        return ResponseHelper::successWithData($onlineExamChapterTopics);
    }


    public function retrieveOnlineExamForms(Request $request)
    {
        $onlineExamForms = ExamHelper::retrieveRealExamForms($request->exam_id);
        return ResponseHelper::successWithData($onlineExamForms);
    }


    public function retrieveOnlineExamFormQuestions(Request $request)
    {
        $onlineExamFormQuestions = ExamHelper::retrieveRealExamFormQuestions($request->form_id);
        return ResponseHelper::successWithData($onlineExamFormQuestions);
    }

    public function changeOnlineExamStatus(Request $request)
    {
        $onlineExam = OnlineExam::findOrFail($request->id);
        if (!$onlineExam->status === ExamStatusEnum::COMPLETE->value) {

            if ($onlineExam->status === ExamStatusEnum::SUSPENDED->value) {

                $onlineExam->update([
                    'status' => ExamStatusEnum::ACTIVE->value,
                ]);
            } else {

                $onlineExam->update([
                    'status' => ExamStatusEnum::SUSPENDED->value,
                ]);
            }
        } else {
            return ResponseHelper::clientError(401);
            // return ResponseHelper::clientError('this exam is completed, you cant chande its status');
        }
        return ResponseHelper::success();
    }




    public function rules(Request $request): array
    {

        $rules = [
            'language_id' => ['required', new Enum(LanguageEnum::class)],
            'duration' => 'required|integer',
            'difficulty_level_id' => ['required', new Enum(ExamDifficultyLevelEnum::class)],
            'conduct_method_id' => ['required', new Enum(ExamConductMethodEnum::class)],
            'department_course_part_id' => 'required|exists:department_course_parts,id',
            'proctor_id' => 'required|exists:employees,id',
            // 'status' => ['required', new Enum(ExamStatusEnum::class)],
            'datetime_notification_datetime' => 'required|date',
            'result_notification_datetime' => 'required|date',
            'question_types' =>'required|array',
            'type_id' => ['required', new Enum(ExamTypeEnum::class)],
            'datetime' => 'required|date',
            'special_note' => 'nullable|string',
            'forms_count' => 'required|integer',
            'form_configuration_method' => ['required', new Enum(FormConfigurationMethodEnum::class)],
            'form_name_method' => ['required', new Enum(FormNameMethodEnum::class)],

        ];
        if ($request->method() === 'PUT' || $request->method() === 'PATCH') {
            $rules = array_filter($rules, function ($attribute) use ($request) {
                // Ensure strict type comparison for security
                return $request->has($attribute);
            });
        }
        return $rules;
    }
}
