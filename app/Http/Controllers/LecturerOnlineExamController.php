<?php

namespace App\Http\Controllers;

use App\Helpers\Param;
use App\Models\Employee;
use App\Models\Question;
use App\Models\RealExam;
use App\Enums\LevelsEnum;
use App\Models\OnlineExam;
use App\Enums\ExamTypeEnum;
use App\Enums\LanguageEnum;
use App\Enums\SemesterEnum;
use App\Helpers\ExamHelper;
use App\Helpers\NullHelper;
use App\Helpers\ParamHelper;
use Illuminate\Http\Request;
use App\Enums\CoursePartsEnum;
use App\Models\CourseLecturer;
use App\Enums\QuestionTypeEnum;
use App\Enums\RealExamTypeEnum;
use App\Helpers\DatetimeHelper;
use App\Helpers\LanguageHelper;
use App\Helpers\ResponseHelper;
use App\Helpers\ValidateHelper;
use App\Helpers\EnumReplacement;
use App\Enums\FormNameMethodEnum;
use App\AlgorithmAPI\GenerateExam;
use App\Helpers\ColumnReplacement;
use App\Helpers\ProcessDataHelper;
use Illuminate\Support\Facades\DB;
use App\Enums\OnlineExamStatusEnum;
use App\Enums\ExamConductMethodEnum;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Enum;
use App\Enums\AccessibilityStatusEnum;
use App\Enums\ExamDifficultyLevelEnum;
use App\Enums\FormConfigurationMethodEnum;

class LecturerOnlineExamController extends Controller
{
    public function addOnlineExam(Request $request)
    {
        Gate::authorize('addOnlineExam', LecturerOnlineExamController::class);

        if (ValidateHelper::validateData($request, $this->rules($request))) {
            return ResponseHelper::clientError();
        }
        if (($request->datetime <= $request->datetime_notification_datetime) &&
            ($request->datetime >= $request->result_notification_datetime) &&
            ($request->datetime_notification_datetime <= DatetimeHelper::convertDateTimeToLong(DatetimeHelper::now()))
        ) {
            return ResponseHelper::clientError();
        }
        DB::beginTransaction();
        try {
            $formConfigurationMethodId = FormConfigurationMethodEnum::SIMILAR_FORMS->value;
            $formNameMethodId = FormNameMethodEnum::DECIMAL_NUMBERING->value;

            if ($request->forms_count > 1) {
                if ($request->has("form_configuration_method_id") && $request->has("form_name_method_id")) {
                    $formConfigurationMethodId = $request->form_configuration_method_id;
                    $formNameMethodId = $request->form_name_method_id;
                } else {
                    DB::rollBack();
                    return ResponseHelper::clientError();
                }
            }

            $algorithmData = $this->getAlgorithmData($request);

            $examFormsQuestions = (new GenerateExam())->execute($algorithmData);

            if ($examFormsQuestions) { // modify to use has function
                $employee = Employee::where('user_id',  auth()->user()->id)->first();

                $courseLecturer = CourseLecturer::where('department_course_part_id', '=', $request->department_course_part_id)
                    ->where('lecturer_id', $employee->id)
                    ->where('academic_year', now()->format('Y'))
                    ->first();
                $realExam = $courseLecturer->real_exams()->create([
                    'type' => $request->type_id,
                    'datetime' => $request->datetime,
                    'duration' => $request->duration,
                    'language' => $request->language_id,
                    'note' => $request->special_note ?? null,
                    'difficulty_level' => $request->difficulty_level_id,
                    'forms_count' => $request->forms_count,
                    'form_configuration_method' => $formConfigurationMethodId,
                    'form_name_method' => $formNameMethodId,
                    'exam_type' => RealExamTypeEnum::ONLINE->value,
                    // 'course_lecturer_id' => $courseLecturer->id,
                ]);

                OnlineExam::create([
                    'conduct_method' => $request->conduct_method_id,
                    'exam_datetime_notification_datetime' => $request->datetime_notification_datetime,
                    'result_notification_datetime'  => $request->result_notification_datetime,
                    'proctor_id' => $request->proctor_id ?? null,
                    'status' => OnlineExamStatusEnum::ACTIVE->value,
                    'id' => $realExam->id,
                ]);

                foreach ($request->questions_types as $question_type) {
                    $realExam->real_exam_question_types()->create([
                        'question_type' => $question_type['type_id'],
                        'questions_count' => $question_type['questions_count'],
                        'question_score' => $question_type['question_score'],
                    ]);
                }

                //////////add Topics of exam
                foreach ($examFormsQuestions as $questionsIds) {
                    $formQuestions = $this->getQuestionsChoicesCombinations($questionsIds);

                    $form = $realExam->forms()->create();
                    foreach ($formQuestions as $question) {
                        $form->form_questions()->create([
                            'question_id' => $question['question_id'],
                            'combination_id' => $question['combination_id'] ?? null,
                        ]);
                    }
                }
                DB::commit();
                return ResponseHelper::successWithData(['id' => $realExam->id]);
            } else {
                DB::rollBack();
                return ResponseHelper::serverError();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseHelper::serverError();
        }
    }

    public function modifyOnlineExam(Request $request)
    {
        // $realExam = RealExam::findOrFail($request->id);
        // $realExam->update([
        //     'type' => $request->type_id ?? $realExam->type,
        //     'datetime' => $request->datetime ?? $realExam->datetime,
        //     'note' => $request->special_note ?? $realExam->note,
        //     'form_name_method' => $request->form_name_method_id ?? $realExam->form_name_method,
        // ]);

        // $onlinExam = OnlineExam::findOrFail($realExam->id);
        // $onlinExam->update([
        //     'conduct_method' =>  $request->conduct_method_id ??  $onlinExam->conduct_method,
        //     'exam_datetime_notification_datetime' => $request->datetime_notification_datetime ?? $onlinExam->exam_datetime_notification_datetime,
        //     'result_notification_datetime'  => $request->result_notification_datetime ?? $onlinExam->result_notification_datetime,
        //     'proctor_id' => $request->proctor_id ?? $onlinExam->proctor_id,
        // ]);

        Gate::authorize('modifyOnlineExam', LecturerOnlineExamController::class);
        if (ValidateHelper::validateData($request, $this->rules($request))) {
            return ResponseHelper::clientError();
        }
        DB::beginTransaction();
        try {
            $params = ParamHelper::getParams(
                $request,
                [
                    new Param('type_id', 'type'),
                    new Param('datetime'),
                    new Param('note'),
                    new Param('form_name_method_id', 'form_name_method')
                ]
            );

            $realExam = RealExam::findOrFail($request->id);

            if (isset($params['form_name_method']) && $realExam->forms_count === 1) {
                DB::rollBack();
                return ResponseHelper::clientError();
            }

            $realExam->update($params);

            $params = ParamHelper::getParams(
                $request,
                [
                    new Param('conduct_method_id', 'conduct_method'),
                    new Param('datetime_notification_datetime', 'exam_datetime_notification_datetime'),
                    new Param('result_notification_datetime'),
                    new Param('proctor_id')
                ]
            );

            OnlineExam::findOrFail($request->id)
                ->update($params);
            DB::commit();
            return ResponseHelper::success();
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseHelper::serverError();
        }
    }

    public function deleteOnlineExam(Request $request)
    {
        // يتم حذف كل ما يتعلق بالاختبار وايضا اسئلة الاختبار التي قد تم توليدها
        // دراسة كيفية امكانية انقاص بيانات استخدام الاسئلة
        Gate::authorize('deleteOnlineExam', LecturerOnlineExamController::class);
        if (ValidateHelper::validateData($request, [
            'id' => 'required|integer'
        ])) {
            return  ResponseHelper::clientError();
        }
        try {
            $realExam = RealExam::findOrFail($request->id);
            $realExam->delete();
            return ResponseHelper::success();
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveOnlineExams(Request $request) ////**** يتم اضافة شرط ان يتم ارجاع الاختبارات التي تنتمي الى المستخدم الحالي
    {
        Gate::authorize('retrieveOnlineExams', LecturerOnlineExamController::class);
        if (ValidateHelper::validateData($request, [
            'department_course_part_id' => 'required|integer',
            'type_id' => ['nullable', new Enum(ExamTypeEnum::class)],
            'status_id' => ['nullable', new Enum(OnlineExamStatusEnum::class)],
        ])) {
            return  ResponseHelper::clientError();
        }
        $enumReplacements  = [];
        try {
            $lecturer_id = Employee::where('user_id', '=', auth()->user()->id)->first(['id'])['id'];

            $onlineExams =  DB::table('real_exams')
                ->join('online_exams', 'real_exams.id', '=', 'online_exams.id')
                ->join('course_lecturers', 'real_exams.course_lecturer_id', '=', 'course_lecturers.id')
                ->join('department_course_parts', 'course_lecturers.department_course_part_id', '=', 'department_course_parts.id')
                ->select(
                    'real_exams.id',
                    'real_exams.datetime',
                    'real_exams.forms_count'
                )
                ->when(isset($request->status_id), function ($query) use ($request) {
                    return $query->where('online_exams.status', '=', $request->status_id);
                })
                ->when(isset($request->type_id), function ($query) use ($request) {
                    return $query->where('real_exams.type', '=', $request->type_id);
                })
                ->when($request->type_id === null, function ($query) use ($request) {
                    return $query->selectRaw('real_exams.type as type_name');
                })
                ->when($request->status_id === null, function ($query) use ($request) {
                    return $query->selectRaw('online_exams.status as status_name');
                })
                ->where('department_course_parts.id', '=', $request->department_course_part_id)
                ->where('course_lecturers.lecturer_id', '=', $lecturer_id)
                ->get()
                ->map(function ($exam) {
                    $exam->datetime = DatetimeHelper::convertDateTimeToLong($exam->datetime);
                    return $exam;
                });

            if (!isset($request->status_id)) {
                array_push($enumReplacements,  new EnumReplacement('status_name', OnlineExamStatusEnum::class));
            }

            if (!isset($request->type_id)) {
                array_push($enumReplacements,  new EnumReplacement('type_name', ExamTypeEnum::class));
            }

            $onlineExams = ProcessDataHelper::enumsConvertIdToName($onlineExams, $enumReplacements);

            $onlineExams =  ExamHelper::getRealExamsScore($onlineExams); // sum score of

            return ResponseHelper::successWithData($onlineExams);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveOnlineExamsAndroid(Request $request) ////////** this attribute department_course_part_id can be null
    {
        Gate::authorize('retrieveOnlineExamsAndroid', LecturerOnlineExamController::class);
        if (ValidateHelper::validateData($request, [
            'department_course_part_id' => 'nullable|integer',
            'type_id' => ['nullable', new Enum(ExamTypeEnum::class)],
            'status_id' => ['nullable', new Enum(OnlineExamStatusEnum::class)]
        ])) {
            return  ResponseHelper::clientError();
        }

        $enumReplacements  = [
            new EnumReplacement('type_name', ExamTypeEnum::class),
            new EnumReplacement('status_name', OnlineExamStatusEnum::class),
            new EnumReplacement('course_part_name', CoursePartsEnum::class),
            new EnumReplacement('language_name', LanguageEnum::class),
        ];
        try {
            $lecturer_id = Employee::where('user_id', '=', auth()->user()->id)->first(['id'])['id'];

            $onlineExams =  DB::table('real_exams')
                ->join('online_exams', 'real_exams.id', '=', 'online_exams.id')
                ->join('course_lecturers', 'real_exams.course_lecturer_id', '=', 'course_lecturers.id')
                ->join('department_course_parts', 'course_lecturers.department_course_part_id', '=', 'department_course_parts.id')
                ->join('department_courses', 'department_course_parts.department_course_id', '=', 'department_courses.id')
                ->join('courses', 'department_courses.course_id', '=', 'courses.id')
                ->join('course_parts', 'department_course_parts.course_part_id', '=', 'course_parts.id')
                ->select(
                    LanguageHelper::getNameColumnName('courses', 'course_name'),
                    'course_parts.part_id as course_part_name',
                    'real_exams.id',
                    'real_exams.datetime',
                    'real_exams.language as language_name',
                    'real_exams.type as type_name',
                    'online_exams.status as status_name',
                )
                ->when(isset($request->department_course_part_id), function ($query) use ($request) {
                    return $query->where('department_course_parts.id', '=', $request->department_course_part_id);
                })
                ->when(isset($request->status_id), function ($query) use ($request) {
                    return $query->where('online_exams.status', '=', $request->status_id);
                })
                ->when(isset($request->type_id), function ($query) use ($request) {
                    return $query->where('real_exams.type', '=', $request->type_id);
                })
                ->where('course_lecturers.lecturer_id', '=', $lecturer_id)
                ->get()
                ->map(function ($exam) {
                    $exam->datetime = DatetimeHelper::convertDateTimeToLong($exam->datetime);
                    return $exam;
                });

            $onlineExams = ProcessDataHelper::enumsConvertIdToName($onlineExams, $enumReplacements);

            return ResponseHelper::successWithData($onlineExams);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveOnlineExam(Request $request)
    {
        Gate::authorize('retrieveOnlineExam', LecturerOnlineExamController::class);
        if (ValidateHelper::validateData($request, [
            'id' => 'required|integer'
        ])) {
            return  ResponseHelper::clientError();
        }
        try {
            $realExam = RealExam::findOrFail($request->id, [
                'id', 'language as language_name', 'difficulty_level as difficulty_level_name',
                'forms_count', 'form_configuration_method as form_configuration_method_name', 'form_name_method as form_name_method_name',
                'datetime', 'duration', 'type as type_name', 'note as special_note', 'course_lecturer_id'
            ]);

            // $realExam->datetime1 = $realExam->datetime;
            // unset($realExam['datetime']);
            // $realExam->datetime = $realExam->datetime1;

            // return $realExam->datetime;

            $lecturer_id = CourseLecturer::findOrFail($realExam->course_lecturer_id)
                ->first(['lecturer_id'])['lecturer_id'];

            $realExam->lecturer_name = Employee::findOrFail($lecturer_id)
                ->first([LanguageHelper::getNameColumnName(null, null)])[LanguageHelper::getNameColumnName(null, null)];

            $realExam = ProcessDataHelper::enumsConvertIdToName($realExam, [
                new EnumReplacement('language_name', LanguageEnum::class),
                new EnumReplacement('difficulty_level_name', ExamDifficultyLevelEnum::class),
                new EnumReplacement('type_name', ExamTypeEnum::class),
            ]);

            if ($realExam->forms_count > 1) {
                $realExam = ProcessDataHelper::enumsConvertIdToName($realExam, [
                    new EnumReplacement('form_configuration_method_name', FormConfigurationMethodEnum::class),
                    new EnumReplacement('form_name_method_name', FormNameMethodEnum::class),
                ]);
            } else {
                unset($realExam->form_configuration_method_name);
                unset($realExam->form_name_method_name);
            }

            // $onlineExam = OnlineExam::where('id', $realExam->id)->first(['conduct_method as conduct_method_name', 'status as status_name', 'proctor_id as proctor_name', 'exam_datetime_notification_datetime as datetime_notification_datetime', 'result_notification_datetime']);
            $onlineExam = OnlineExam::where('id', $realExam->id)->first([
                'conduct_method as conduct_method_name',
                'status as status_name',
                'proctor_id as proctor_name',
                // 'exam_datetime_notification_datetime as datetime_notification_datetime',
                'exam_datetime_notification_datetime',
                'result_notification_datetime'
            ]);

            $onlineExam->datetime_notification_datetime = $onlineExam->exam_datetime_notification_datetime;
            unset($onlineExam['exam_datetime_notification_datetime']);

            $onlineExam->is_suspended = intval($onlineExam->status_name) === OnlineExamStatusEnum::SUSPENDED->value;
            $onlineExam->is_complete = intval($onlineExam->status_name) === OnlineExamStatusEnum::COMPLETE->value;

            $onlineExam->is_editable = DatetimeHelper::convertLongToDateTime($realExam->datetime) > DatetimeHelper::now();
            // $onlineExam->is_deletable = $realExam->datetime > now();

            $onlineExam = ProcessDataHelper::enumsConvertIdToName($onlineExam, [
                new EnumReplacement('status_name', OnlineExamStatusEnum::class),
                new EnumReplacement('conduct_method_name', OnlineExamStatusEnum::class),
            ]);
            $onlineExam = ProcessDataHelper::columnConvertIdToName($onlineExam, [ // need to fix columnConvertIdToName method
                new ColumnReplacement('proctor_name', LanguageHelper::getNameColumnName(null, null), Employee::class),
            ]);

            $courseLecturer = $realExam->course_lecturer()->first();
            $departmentCoursePart = $courseLecturer->department_course_part()->first();
            $coursePart = $departmentCoursePart->course_part()->first(['part_id as course_part_name']);
            $coursePart = ProcessDataHelper::enumsConvertIdToName($coursePart, [
                new EnumReplacement('course_part_name', CoursePartsEnum::class),
            ]);
            $departmentCourse = $departmentCoursePart->department_course()->first(['level as level_name', 'semester as semester_name', 'department_id', 'course_id']);
            $departmentCourse = ProcessDataHelper::enumsConvertIdToName($departmentCourse, [
                new EnumReplacement('level_name', LevelsEnum::class),
                new EnumReplacement('semester_name', SemesterEnum::class),
            ]);
            $department = $departmentCourse->department()->first([LanguageHelper::getNameColumnName(null, 'department_name'), 'college_id']);
            $college = $department->college()->first([LanguageHelper::getNameColumnName(null, 'college_name')]);
            $course = $departmentCourse->course()->first([LanguageHelper::getNameColumnName(null, 'course_name')]);
            $questionTypes = $realExam->real_exam_question_types()->get(['question_type as type_name', 'questions_count', 'question_score']);
            $questionTypes = ProcessDataHelper::enumsConvertIdToName($questionTypes, [
                new EnumReplacement('type_name', QuestionTypeEnum::class),
            ]);

            $questionTypes = collect($questionTypes)->map(function ($type) {
                return [
                    'type_name' => $type->type_name,
                    'questions_count' => $type->questions_count,
                    'question_score' => (float) $type->question_score,
                ];
            })->toArray();

            //*** make unset to : 'department_id', 'course_id', 'college_id', 'course_lecturer_id'
            $departmentCourse = $departmentCourse->toArray();
            unset($departmentCourse['department_id']);
            unset($departmentCourse['course_id']);

            $department = $department->toArray();
            unset($department['college_id']);

            // $realExam = $realExam->toArray();
            unset($realExam['course_lecturer_id']);
            unset($realExam['id']);

            // $realExam = NullHelper::filter($realExam);
            // $onlineExam = NullHelper::filter($onlineExam);

            $realExam =
                // $realExam +
                // $onlineExam +
                $realExam->toArray() +
                $onlineExam->toArray() +
                $coursePart->toArray() +
                $departmentCourse  +
                $department +
                $college->toArray() +
                $course->toArray();

            $realExam['questions_types'] = $questionTypes;

            $realExam = NullHelper::filter($realExam);

            $realExam['datetime'] = DatetimeHelper::convertDateTimeToLong($realExam['datetime']);

            return ResponseHelper::successWithData($realExam);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveEditableOnlineExam(Request $request)
    {
        // $realExam = RealExam::findOrFail($request->id, [
        //     'form_name_method as form_name_method_id',
        //     'datetime', 'type as type_id', 'note as special_note'
        // ]);

        // $onlineExam = OnlineExam::where('id', $request->id)->first(
        //     [
        //         'conduct_method as conduct_method_id',
        //         'exam_datetime_notification_datetime as datetime_notification_datetime',
        //         'proctor_id', 'result_notification_datetime'
        //     ]
        // );

        // $exam = $realExam->toArray() + $onlineExam->toArray();
        Gate::authorize('retrieveEditableOnlineExam', LecturerOnlineExamController::class);
        if (ValidateHelper::validateData($request, [
            'id' => 'required|integer'
        ])) {
            return  ResponseHelper::clientError();
        }
        try {
            $exam = DB::table('real_exams')
                ->join('online_exams', 'real_exams.id', '=', 'online_exams.id')
                ->where('real_exams.id', $request->id)
                ->select(
                    'real_exams.forms_count',
                    'real_exams.form_name_method as form_name_method_id',
                    'real_exams.datetime',
                    'real_exams.duration',
                    'real_exams.type as type_id',
                    'real_exams.note as special_note',
                    'online_exams.conduct_method as conduct_method_id',
                    'online_exams.exam_datetime_notification_datetime as datetime_notification_datetime',
                    'online_exams.proctor_id',
                    'online_exams.result_notification_datetime'
                )
                ->first();

            if ($exam->forms_count === 1) {
                unset($exam->form_name_method_id);
            }
            unset($exam->forms_count);

            $exam->datetime = DatetimeHelper::convertDateTimeToLong($exam->datetime);
            $exam->datetime_notification_datetime = DatetimeHelper::convertDateTimeToLong($exam->datetime_notification_datetime);
            $exam->result_notification_datetime = DatetimeHelper::convertDateTimeToLong($exam->result_notification_datetime);

            $exam = NullHelper::filter($exam);

            return ResponseHelper::successWithData($exam);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveOnlineExamChapters(Request $request)
    {
        Gate::authorize('retrieveOnlineExamChapters', LecturerOnlineExamController::class);
        if (ValidateHelper::validateData($request, [
            'exam_id' => 'required|integer'
        ])) {
            return  ResponseHelper::clientError();
        }
        try {
            $chapters = ExamHelper::retrieveRealExamChapters($request->exam_id);
            return ResponseHelper::successWithData($chapters);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveOnlineExamChapterTopics(Request $request)
    {
        Gate::authorize('retrieveOnlineExamChapterTopics', LecturerOnlineExamController::class);
        if (ValidateHelper::validateData($request, [
            'exam_id' => 'required|integer',
            'chapter_id' => 'required|integer'
        ])) {
            return  ResponseHelper::clientError();
        }
        try {
            // return ExamHelper::retrieveRealExamChapterTopics($request->exam_id, $request->chapter_id);
            $topics = ExamHelper::retrieveRealExamChapterTopics($request->exam_id, $request->chapter_id);
            return ResponseHelper::successWithData($topics);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }


    public function retrieveOnlineExamForms(Request $request)
    {
        Gate::authorize('retrieveOnlineExamForms', LecturerOnlineExamController::class);
        if (ValidateHelper::validateData($request, [
            'exam_id' => 'required|integer'
        ])) {
            return  ResponseHelper::clientError();
        }
        try {
            // return LevelsEnum::values();
            $onlineExamForms = ExamHelper::retrieveRealExamForms($request->exam_id);
            return ResponseHelper::successWithData($onlineExamForms);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }


    public function retrieveOnlineExamFormQuestions(Request $request)
    {
        Gate::authorize('retrieveOnlineExamFormQuestions', LecturerOnlineExamController::class);
        if (ValidateHelper::validateData($request, [
            'form_id' => 'required|integer'
        ])) {
            return  ResponseHelper::clientError();
        }
        try {
            $questions = ExamHelper::getFormQuestionsWithDetails($request->form_id, false, false, true);
            return ResponseHelper::successWithData($questions);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function changeOnlineExamStatus(Request $request)
    {
        Gate::authorize('changeOnlineExamStatus', LecturerOnlineExamController::class);
        if (ValidateHelper::validateData($request, [
            'id' => 'required|integer'
        ])) {
            return  ResponseHelper::clientError();
        }
        try {
            $onlineExam = OnlineExam::findOrFail($request->id);
            $realExam = RealExam::findOrFail($request->id);

            if (
                !(intval($onlineExam->status) === OnlineExamStatusEnum::COMPLETE->value) ||
                $realExam->datetime > DatetimeHelper::now()
            ) {
                if (intval($onlineExam->status) === OnlineExamStatusEnum::SUSPENDED->value) {
                    $onlineExam->update([
                        'status' => OnlineExamStatusEnum::ACTIVE->value,
                    ]);
                } else {
                    $onlineExam->update([
                        'status' => OnlineExamStatusEnum::SUSPENDED->value,
                    ]);
                }
            } else {
                return ResponseHelper::clientError();
                // return ResponseHelper::clientError('this exam is completed, you cant chande its status');
            }
            return ResponseHelper::success();
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    private function getAlgorithmData($request)
    {
        try {
            $accessabilityStatusIds = [
                AccessibilityStatusEnum::REAL_EXAM->value,
                AccessibilityStatusEnum::PRACTICE_AND_REAL_EXAM->value,
            ];
            $algorithmData = ExamHelper::getAlgorithmData($request, $accessabilityStatusIds);
            return $algorithmData;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    private function getQuestionsChoicesCombinations($questionsIds)
    {
        try {
            $formQuestions = [];
            foreach ($questionsIds as $questionId) {
                $question = Question::findOrFail($questionId);
                $combination_id = null;

                if ($question->type == QuestionTypeEnum::MULTIPLE_CHOICE->value) {
                    $combination_id = $this->selectQuestionsChoicesCombination($question);
                }

                array_push($formQuestions, [
                    'question_id' => $questionId,
                    'combination_id' => $combination_id
                ]);
            }
            return $formQuestions;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    private function selectQuestionsChoicesCombination(Question $question): int
    {
        try {
            $qestionChoicesCombinationsIds = $question->question_choices_combinations()
                ->get(['combination_id'])
                ->map(function ($qestionChoicesCombination) {
                    return $qestionChoicesCombination->combination_id;
                })->toArray();

            $selectedIndex = array_rand($qestionChoicesCombinationsIds);
            return $qestionChoicesCombinationsIds[$selectedIndex];
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function rules(Request $request): array
    {
        $rules = [
            // real exam table
            'language_id' => ['required', new Enum(LanguageEnum::class)],
            'difficulty_level_id' => ['required', new Enum(ExamDifficultyLevelEnum::class)],
            'datetime' => 'required|integer', // check for bigInteger data type
            'duration' => 'required|integer',
            'type_id' => ['required', new Enum(ExamTypeEnum::class)],
            'special_note' => 'nullable|string',
            'forms_count' => 'required|integer',
            'form_configuration_method_id' => ['nullable', new Enum(FormConfigurationMethodEnum::class)],
            'form_name_method_id' => ['nullable', new Enum(FormNameMethodEnum::class)],

            // online exam table
            'conduct_method_id' => ['required', new Enum(ExamConductMethodEnum::class)],
            'datetime_notification_datetime' => 'required|integer', // check for bigInteger data type
            'result_notification_datetime' => 'required|integer', // check for bigInteger data type
            'proctor_id' => 'nullable|exists:employees,id',

            // real_exam_question_types
            'questions_types' => 'required|array|min:1',
            'questions_types.*.type_id' => ['required', new Enum(QuestionTypeEnum::class)],
            'questions_types.*.questions_count' => 'required|integer',
            'questions_types.*.question_score' => 'required|numeric', // Use 'numeric' to allow both integer and float

            // topice
            'topics_ids'                => 'required|array|min:1',
            'topics_ids.*'              => 'required|integer|exists:topics,id',

            // other variables
            'department_course_part_id' => 'required|exists:department_course_parts,id',

        ];
        if ($request->method() === 'PUT' || $request->method() === 'PATCH') {
            $rules = array_filter($rules, function ($attribute) use ($request) {
                // Ensure strict type comparison for security
                return $request->has($attribute);
            });
        }
        return $rules;
    }
    /**
     * التاكد من ان رقم المراقب المختار يملك صلاحية مراقب
     *
     */
}
