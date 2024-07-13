<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\User;
use App\Models\Topic;
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
use App\Enums\ExamStatusEnum;
use App\Enums\CoursePartsEnum;
use App\Models\CourseLecturer;
use App\Enums\QuestionTypeEnum;
use App\Enums\RealExamTypeEnum;
use App\Helpers\DatetimeHelper;
use App\Helpers\ResponseHelper;
use App\Helpers\ValidateHelper;
use App\Helpers\EnumReplacement;
use App\Enums\FormNameMethodEnum;
use App\Enums\QuestionStatusEnum;
use App\AlgorithmAPI\GenerateExam;
use App\Helpers\ColumnReplacement;
use App\Helpers\ProcessDataHelper;
use Illuminate\Support\Facades\DB;
use App\Enums\ExamConductMethodEnum;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rules\Enum;
use App\Enums\AccessibilityStatusEnum;
use App\Enums\ExamDifficultyLevelEnum;
use App\Enums\FormConfigurationMethodEnum;

class LecturerOnlineExamController extends Controller
{
    public function addOnlineExam(Request $request)
    {

        if (ValidateHelper::validateData($request, $this->rules($request))) {
            return ResponseHelper::clientError(402);
        }
        // try {
            $algorithmData = $this->getAlgorithmData($request);
            
            return ResponseHelper::successWithData($algorithmData);
            $examFormsQuestions = (new GenerateExam())->execute($algorithmData);
            
            
            if ($examFormsQuestions) { // modify to use has function
                $employee = Employee::where('user_id',  auth()->user()->id)->first();

                $courseLecturer = CourseLecturer::where('department_course_part_id', '=', $request->department_course_part_id)
                    ->where('lecturer_id', $employee->id)
                    ->where('academic_year', now()->format('Y'))
                    ->first();
                DB::beginTransaction();
                $realExam = $courseLecturer->real_exams()->create([
                    'type' => $request->type_id,
                    'datetime' => $request->datetime,
                    'duration' => $request->duration,
                    'language' => $request->language_id,
                    'note' => $request->special_note ?? null,
                    'difficulty_level' => $request->difficulty_level_id,
                    'forms_count' => $request->forms_count,
                    'form_configuration_method' => $request->form_configuration_method_id,
                    'form_name_method' => $request->form_name_method_id,
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
                ////////// modify question usage table يفضل ان يتم عمل دالة مشتركة حتى يتم استخدامها في الاختبار الورقي
                DB::commit();
                return ResponseHelper::successWithData(['id' => $realExam->id]);
            } else {
                DB::rollBack();
                return ResponseHelper::serverError();
            }
        // } catch (\Exception $e) {
        //     DB::rollBack();
        //     return ResponseHelper::serverError();
        // }
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
            DB::beginTransaction();
            RealExam::findOrFail($request->id)
                ->update($params);

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
        try {
            $realExam = RealExam::findOrFail($request->id);
            $realExam->delete();
            // ExamHelper::deleteRealExam($request->id);
            return ResponseHelper::success();
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveOnlineExams(Request $request) ////**** يتم اضافة شرط ان يتم ارجاع الاختبارات التي تنتمي الى المستخدم الحالي
    {
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
                ->get();
                // ->map(function ($exam) {
                //     $exam->datetime = DatetimeHelper::convertTimestampToMilliseconds($exam->datetime);
                //     return $exam;
                // });

            if (!isset($request->status_id)) {
                array_push($enumReplacements,  new EnumReplacement('status_name', ExamStatusEnum::class));
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
        $enumReplacements  = [
            new EnumReplacement('type_name', ExamTypeEnum::class),
            new EnumReplacement('status_name', ExamStatusEnum::class),
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
                    'courses.arabic_name as course_name',
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
                    return $query->where('online_exams.status', '=', $request->stsatus_id);
                })
                ->when(isset($request->type_id), function ($query) use ($request) {
                    return $query->where('real_exams.type', '=', $request->type_id);
                })
                ->where('course_lecturers.lecturer_id', '=', $lecturer_id)
                ->get();
                // ->map(function ($exam) {
                //     $exam->datetime = DatetimeHelper::convertTimestampToMilliseconds($exam->datetime);
                //     return $exam;
                // });

            $onlineExams = ProcessDataHelper::enumsConvertIdToName($onlineExams, $enumReplacements);

            return ResponseHelper::successWithData($onlineExams);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveOnlineExam(Request $request)
    {
        try {
            $realExam = RealExam::findOrFail($request->id, [
                'id', 'language as language_name', 'difficulty_level as difficulty_level_name',
                'forms_count', 'form_configuration_method as form_configuration_method_name', 'form_name_method as form_name_method_name',
                'datetime', 'duration', 'type as type_name', 'note as special_note', 'course_lecturer_id'
            ]);
            
            $realExam = NullHelper::filter($realExam);

            $lecturer_id = CourseLecturer::findOrFail($realExam->course_lecturer_id)
                ->first(['lecturer_id'])['lecturer_id'];

            $realExam->lecturer_name = Employee::findOrFail($lecturer_id)
                ->first(['arabic_name'])['arabic_name'];

            $realExam = ProcessDataHelper::enumsConvertIdToName($realExam, [
                new EnumReplacement('language_name', LanguageEnum::class),
                new EnumReplacement('difficulty_level_name', ExamDifficultyLevelEnum::class),
                new EnumReplacement('form_configuration_method_name', FormConfigurationMethodEnum::class),
                new EnumReplacement('form_name_method_name', FormNameMethodEnum::class),
                new EnumReplacement('type_name', ExamTypeEnum::class),
            ]);

            // $onlineExam = OnlineExam::where('id', $realExam->id)->first(['conduct_method as conduct_method_name', 'status as status_name', 'proctor_id as proctor_name', 'exam_datetime_notification_datetime as datetime_notification_datetime', 'result_notification_datetime']);
            $onlineExam = OnlineExam::where('id', $realExam->id)->first([
                'conduct_method as conduct_method_name',
                'status as status_name',
                'proctor_id as proctor_name',
                'exam_datetime_notification_datetime as datetime_notification_datetime',
                'result_notification_datetime'
            ]);

            $onlineExam->is_suspended = intval($onlineExam->status_name) === ExamStatusEnum::SUSPENDED->value;
            $onlineExam->is_complete = intval($onlineExam->status_name) === ExamStatusEnum::COMPLETE->value;
            $onlineExam->is_editable = $realExam->datetime > now();
            // $onlineExam->is_deletable = $realExam->datetime > now();

            $onlineExam = ProcessDataHelper::enumsConvertIdToName($onlineExam, [
                new EnumReplacement('status_name', ExamStatusEnum::class),
                new EnumReplacement('conduct_method_name', ExamStatusEnum::class),
            ]);
            $onlineExam = ProcessDataHelper::columnConvertIdToName($onlineExam, [ // need to fix columnConvertIdToName method
                new ColumnReplacement('proctor_name', 'arabic_name', Employee::class),
            ]);
            $onlineExam = NullHelper::filter($onlineExam);

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
            $department = $departmentCourse->department()->first(['arabic_name as department_name', 'college_id']);
            $college = $department->college()->first(['arabic_name as college_name']);
            $course = $departmentCourse->course()->first(['arabic_name as course_name']);
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

            $realExam = $realExam->toArray();
            unset($realExam['course_lecturer_id']);
            unset($realExam['id']);

            $realExam =
                $realExam +
                $onlineExam->toArray() +
                $coursePart->toArray() +
                $departmentCourse  +
                $department +
                $college->toArray() +
                $course->toArray();

            $realExam['questions_types'] = $questionTypes;

            // $realExam['datetime'] = DatetimeHelper::convertTimestampToMilliseconds($realExam['datetime']);

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
        try {
            $exam = DB::table('real_exams')
                ->join('online_exams', 'real_exams.id', '=', 'online_exams.id')
                ->where('real_exams.id', $request->id)
                ->select(
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

            // $exam->datetime = DatetimeHelper::convertTimestampToMilliseconds($exam->datetime);
            // $exam->datetime_notification_datetime = DatetimeHelper::convertTimestampToMilliseconds($exam->datetime_notification_datetime);
            // $exam->result_notification_datetime = DatetimeHelper::convertTimestampToMilliseconds($exam->result_notification_datetime);

            $exam = NullHelper::filter($exam);

            return ResponseHelper::successWithData($exam);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveOnlineExamChapters(Request $request)
    {
        try {
            $chapters = ExamHelper::retrieveRealExamChapters($request->exam_id);
            return ResponseHelper::successWithData($chapters);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveOnlineExamChapterTopics(Request $request)
    {
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
        try {
            $questions = ExamHelper::getFormQuestionsWithDetails($request->form_id, false, false, true);
            return ResponseHelper::successWithData($questions);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function changeOnlineExamStatus(Request $request)
    {
        try {
            $onlineExam = OnlineExam::findOrFail($request->id);
            $realExam = RealExam::findOrFail($request->id);

            if (!(intval($onlineExam->status) === ExamStatusEnum::COMPLETE->value) ||
                $realExam->datetime > now()
            ) {
                if (intval($onlineExam->status) === ExamStatusEnum::SUSPENDED->value) {
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
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    private function getAlgorithmData($request)
    {
        try {
            // $types = [];
            // foreach ($request->questions_types as $type) {
            //     $t = [
            //         'id' => intval($type['type_id']),
            //         'count' => intval($type['questions_count'])
            //     ];

            //     array_push($types, $t);
            // }

            // // دالة مشتركة للاختبار الالكتروني والورقي
            // $algorithmData = [
            //     'estimated_time' => intval($request->duration),
            //     // 'difficulty_level' => floatval($request->difficulty_level_id),
            //     'difficulty_level' => ExamDifficultyLevelEnum::toFloat($request->difficulty_level_id),
            //     'forms_count' => ($request->form_configuration_method_id === FormConfigurationMethodEnum::DIFFERENT_FORMS->value) ? $request->forms_count : 1,
            //     'question_types_and_questions_count' => $types
            //     // 'question_types_and_questions_count' => [
            //     //     // 'id' => $request->questions_types['type_id'],
            //     //     // 'count' => $request->questions_types['questions_count']
            //     //     'id' => $request->questions_types->type_id,
            //     //     'count' => $request->questions_types->questions_count
            //     // ],
            // ];

            // $questionTypesIds = [];
            // foreach ($request->questions_types as $type) {
            //     array_push($questionTypesIds, $type['type_id']);
            // }

            // $questionTypesIds = $request->questions_types['type_id']; // التحقق من ان نحصل على مصفوفه
            $accessabilityStatusIds = [
                AccessibilityStatusEnum::REALEXAM->value,
                AccessibilityStatusEnum::PRACTICE_REALEXAM->value,
            ];
            // $questions =  DB::table('questions')
            //     ->join('question_usages', 'questions.id', '=', 'question_usages.question_id')
            //     ->join('topics', 'questions.topic_id', '=', 'topics.id')
            //     ->select(
            //         'questions.id',
            //         'questions.type as type_id',
            //         'questions.difficulty_level',
            //         'questions.estimated_answer_time as answer_time',
            //         'question_usages.online_exam_last_selection_datetime',
            //         'question_usages.practice_exam_last_selection_datetime',
            //         'question_usages.paper_exam_last_selection_datetime',
            //         'question_usages.online_exam_selection_times_count',
            //         'question_usages.practice_exam_selection_times_count',
            //         'question_usages.paper_exam_selection_times_count',
            //         'questions.topic_id',
            //         'topics.id as topic_id',
            //     )
            //     ->where('questions.status', '=', QuestionStatusEnum::ACCEPTED->value)
            //     ->where('questions.language', '=', $request->language_id)
            //     ->whereIn('questions.accessability_status', $accessabilityStatusIds)
            //     ->whereIn('questions.type', $questionTypesIds)
            //     ->whereIn('questions.topic_id', $request->topics_ids)
            //     // ->whereIn('topics.id', $request->topics_ids)
            //     // ->whereIn('topics.id', [3])
            //     ->get()
            //     ->toArray();

            // foreach ($questions as $question) {
            //     // يجب ان يتم تحديد اوزان هذه المتغيرات لضبط مقدار تاثير كل متغير على حل خوارزمية التوليد

            //     $question->type_id = intval($question->type_id);
            //     $question->difficulty_level = floatval($question->difficulty_level);

            //     // $selections = [1, 2, 3, 4, 5];
            //     // $randomIndex = array_rand($selections);
            //     // $question['last_selection'] = $selections[$randomIndex];
            //     $question->last_selection = 3;
            //     // $question['last_selection'] = DatetimeHelper::convertSecondsToDays(
            //     //     DatetimeHelper::getDifferenceInSeconds(now(), $question->online_exam_last_selection_datetime) +
            //     //         DatetimeHelper::getDifferenceInSeconds(now(), $question->practice_exam_last_selection_datetime) +
            //     //         DatetimeHelper::getDifferenceInSeconds(now(), $question->paper_exam_last_selection_datetime)
            //     // ) / 3;

            //     $question->selection_times = 2;
            //     // $question['selection_times'] = (
            //     //     $question->online_exam_selection_times_count +
            //     //     $question->practice_exam_selection_times_count +
            //     //     $question->paper_exam_selection_times_count
            //     // ) / 3;
            //     // حذف الاعمدة التي تم تحويلها الي عمودين فقط من الاسئلة 
            //     unset($question->online_exam_last_selection_datetime);
            //     unset($question->practice_exam_last_selection_datetime);
            //     unset($question->paper_exam_last_selection_datetime);
            //     unset($question->online_exam_selection_times_count);
            //     unset($question->practice_exam_selection_times_count);
            //     unset($question->paper_exam_selection_times_count);
            // }

            // $algorithmData['questions'] = $questions;
            $algorithmData = ExamHelper::getAlgorithmData($request, $accessabilityStatusIds);
            return $algorithmData;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    private function getQuestionsChoicesCombinations($questionsIds)
    {
        // يفضل ان يتم عملها مشترك ليتم استخداما في الاختبار الورقي والتجريبي
        // تقوم هذه الدالة باختيار توزيعة الاختيارات للاسئلة من نوع اختيار من متعدد
        /**
         * steps of function
         *   اختيار الاسئلة التي نوعها اختيار من متعدد
         *   اختيار احد التوزيعات التي يمتلكها السؤال بشكل عشوائي
         *   يتم اضافة رقم التوزيعة المختارة الي السؤال
         */
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
        // need to make rules
        /*
	- real exam data 
		- language
		- difficulty_level
		- form_configuration_method
		- forms_count
		- form_name_method
		- datetime
		- duration
		- type
		- exam_type
		- note
		- course_lecturer_id
	- real_exam_question_types
		- question_type_id
		- questions_count
		- question_score
	- online_exams
		- status
		- conduct_method
		- exam_datetime_notification_datetime
		- result_notification_datetime
		- proctor_id
	- topics_ids
        */
        $rules = [
            // real exam table 
            'language_id' => ['required', new Enum(LanguageEnum::class)],
            'difficulty_level_id' => ['required', new Enum(ExamDifficultyLevelEnum::class)],
            'datetime' => 'required|integer', // check for bigInteger data type
            'duration' => 'required|integer',
            'type_id' => ['required', new Enum(ExamTypeEnum::class)],
            'special_note' => 'nullable|string',
            'forms_count' => 'required|integer',
            'form_configuration_method_id' => ['required', new Enum(FormConfigurationMethodEnum::class)],
            'form_name_method_id' => ['required', new Enum(FormNameMethodEnum::class)],

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
