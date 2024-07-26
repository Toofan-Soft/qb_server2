<?php

namespace App\Http\Controllers;

use App\Helpers\Param;
use App\Models\Employee;
use App\Models\Question;
use App\Models\RealExam;
use App\Enums\LevelsEnum;
use App\Models\PaperExam;
use App\Enums\ExamTypeEnum;
use App\Enums\LanguageEnum;
use App\Enums\SemesterEnum;
use App\Helpers\ExamHelper;
use App\Helpers\NullHelper;
use App\Helpers\ParamHelper;
use App\Models\FormQuestion;
use Illuminate\Http\Request;
use App\Helpers\DeleteHelper;
use App\Enums\CoursePartsEnum;
use App\Models\CourseLecturer;
use App\Enums\QuestionTypeEnum;
use App\Enums\RealExamTypeEnum;
use App\Helpers\DatetimeHelper;
use App\Helpers\LanguageHelper;
use App\Helpers\ResponseHelper;
use App\Helpers\ValidateHelper;
use App\Helpers\EnumReplacement;
use App\Helpers\OnlinExamHelper;
use App\Enums\FormNameMethodEnum;
use App\Enums\QuestionStatusEnum;
use App\Models\TrueFalseQuestion;
use App\AlgorithmAPI\GenerateExam;
use App\Enums\TrueFalseAnswerEnum;
use App\Helpers\ProcessDataHelper;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\DepartmentCoursePart;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Enum;
use App\Enums\AccessibilityStatusEnum;
use App\Enums\ExamDifficultyLevelEnum;
use Illuminate\Support\Facades\Storage;
use App\Enums\FormConfigurationMethodEnum;
use App\Helpers\QuestionUsageHelper;

class PaperExamController extends Controller
{
    public function addPaperExam(Request $request)
    {
        Gate::authorize('addPaperExam', PaperExamController::class);

        if (ValidateHelper::validateData($request, $this->rules($request))) {
            return  ResponseHelper::clientError();
        }

        try {
            $formConfigurationMethodId = FormConfigurationMethodEnum::SIMILAR_FORMS->value;
            $formNameMethodId = FormNameMethodEnum::DECIMAL_NUMBERING->value;

            if ($request->forms_count > 1) {
                if ($request->has("form_configuration_method_id") && $request->has("form_name_method_id")) {
                    $formConfigurationMethodId = $request->form_configuration_method_id;
                    $formNameMethodId = $request->form_name_method_id;
                } else {
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

                DB::beginTransaction();

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
                    'exam_type' => RealExamTypeEnum::PAPER->value,
                ]);

                $paperExam = PaperExam::create([
                    'id' => $realExam->id,
                    'course_lecturer_name' => $request->lecturer_name ?? 'unknown',
                    // 'course_lecturer_name' => $request->lecturer_name?? null,
                ]);

                foreach ($request->questions_types as $question_type) {
                    $realExam->real_exam_question_types()->create([
                        'question_type' => $question_type['type_id'],
                        'questions_count' => $question_type['questions_count'],
                        'question_score' => $question_type['question_score'],
                    ]);
                }

                //////////add Topics of exam

                // if (intval($request->form_configuration_method) === FormConfigurationMethodEnum::SIMILAR_FORMS->value) {
                //     $realExam->forms()->create();
                // } else {
                //     for ($i = 0; $i <= $request->forms_count; $i++ ) {
                //         $realExam->forms()->create();
                //     }

                // }

                foreach ($examFormsQuestions as $questionsIds) {
                    $formQuestions = $this->getQuestionsChoicesCombinations($questionsIds);
                    $form = $realExam->forms()->create();
                    foreach ($formQuestions as $question) {
                        $form->form_questions()->create([
                            'question_id' => $question['question_id'],
                            'combination_id' => $question['combination_id'] ?? null,
                        ]);
                        QuestionUsageHelper::updatePaperExamQuestionUsage($question['question_id']);
                    }
                }
                DB::commit();
                return ResponseHelper::successWithData(['id' => $realExam->id]);
            } else {
                DB::rollBack();
                return ResponseHelper::serverError();
                // error in the Algorithm model
            }
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function modifyPaperExam(Request $request)
    {
        Gate::authorize('modifyPaperExam', PaperExamController::class);
        if (ValidateHelper::validateData($request, $this->rules($request))) {
            return ResponseHelper::clientError();
        }
        try {
            $params = ParamHelper::getParams(
                $request,
                [
                    new Param('type_id', 'type'),
                    new Param('datetime'),
                    new Param('form_name_method_id', 'form_name_method'),
                    new Param('special_note', 'note')
                ]
            );
            DB::beginTransaction();

            $realExam = RealExam::findOrFail($request->id);

            if (isset($params['form_name_method']) && $realExam->forms_count === 1) {
                return ResponseHelper::clientError();
            }

            $realExam->update($params);

            $params = ParamHelper::getParams(
                $request,
                [
                    new Param('lecturer_name', 'course_lecturer_name')
                ]
            );

            PaperExam::findOrFail($request->id)
                ->first()
                ->update($params);
            DB::commit();
            return ResponseHelper::success();
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseHelper::serverError();
        }
    }

    public function deletePaperExam(Request $request)
    {
        Gate::authorize('deletePaperExam', PaperExamController::class);
        if (ValidateHelper::validateData($request, [
            'id' => 'required|integer'
        ])) {
            return  ResponseHelper::clientError();
        }
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

    public function retrievePaperExams(Request $request) ////**** يتم اضافة شرط ان يتم ارجاع الاختبارات التي تنتمي الى المستخدم الحالي
    {
        // request {department_course_part_id, type_id?}
        Gate::authorize('retrievePaperExams', PaperExamController::class);
        if (ValidateHelper::validateData($request, [
            'department_course_part_id' => 'required|integer',
            'type_id' => ['nullable', new Enum(ExamTypeEnum::class)]
        ])) {
            return  ResponseHelper::clientError();
        }
        $enumReplacements  = [];
        try {
            $lecturer_id = Employee::where('user_id', '=', auth()->user()->id)->first(['id'])['id'];

            $paperExams =  DB::table('real_exams')
                ->join('paper_exams', 'real_exams.id', '=', 'paper_exams.id')
                ->join('course_lecturers', 'real_exams.course_lecturer_id', '=', 'course_lecturers.id')
                ->join('department_course_parts', 'course_lecturers.department_course_part_id', '=', 'department_course_parts.id')
                ->select(
                    'real_exams.id',
                    'real_exams.datetime',
                    'real_exams.forms_count',
                    'paper_exams.course_lecturer_name as lecturer_name'
                )
                ->when(isset($request->type_id), function ($query) use ($request) {
                    return $query->where('real_exams.type', '=', $request->type_id);
                })
                ->when($request->type_id === null, function ($query) use ($request) {
                    return $query->selectRaw('real_exams.type as type_name');
                })
                ->where('department_course_parts.id', '=', $request->department_course_part_id)
                ->where('course_lecturers.lecturer_id', '=', $lecturer_id)
                ->get()
                ->map(function ($exam) {
                    $exam->datetime = DatetimeHelper::convertDateTimeToLong($exam->datetime);
                    return $exam;
                });

            if (!isset($request->type_id)) {
                array_push($enumReplacements,  new EnumReplacement('type_name', ExamTypeEnum::class));
            }
            $paperExams = ProcessDataHelper::enumsConvertIdToName($paperExams, $enumReplacements);

            $paperExams =  ExamHelper::getRealExamsScore($paperExams); // sum score of

            $paperExams = NullHelper::filter($paperExams);

            return ResponseHelper::successWithData($paperExams);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrievePaperExamsAndroid(Request $request) ////////** this attribute department_course_part_id can be null
    {
        Gate::authorize('retrievePaperExamsAndroid', PaperExamController::class);
        if (ValidateHelper::validateData($request, [
            'department_course_part_id' => 'nullable|integer',
            'type_id' => ['nullable', new Enum(ExamTypeEnum::class)]
        ])) {
            return  ResponseHelper::clientError();
        }
        $enumReplacements  = [
            new EnumReplacement('type_name', ExamTypeEnum::class),
            new EnumReplacement('course_part_name', CoursePartsEnum::class),
            new EnumReplacement('language_name', LanguageEnum::class),
        ];
        try {
            $lecturer_id = Employee::where('user_id', '=', auth()->user()->id)->first(['id'])['id'];

            $paperExams =  DB::table('real_exams')
                ->join('paper_exams', 'real_exams.id', '=', 'paper_exams.id')
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
                    'paper_exams.course_lecturer_name as lecturer_name'
                )
                ->when(isset($request->department_course_part_id), function ($query) use ($request) {
                    return $query->where('department_course_parts.id', '=', $request->department_course_part_id);
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

            $paperExams = ProcessDataHelper::enumsConvertIdToName($paperExams, $enumReplacements);

            $paperExams = NullHelper::filter($paperExams);

            return ResponseHelper::successWithData($paperExams);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrievePaperExam(Request $request)
    {
        // request {id}
        Gate::authorize('retrievePaperExam', PaperExamController::class);
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

            $realExam->lecturer_name = PaperExam::findOrFail($request->id, ['course_lecturer_name'])->first()['course_lecturer_name'];

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

            $realExam = $realExam->toArray();
            unset($realExam['course_lecturer_id']);
            unset($realExam['id']);

            $realExam =
                $realExam +
                $coursePart->toArray() +
                $departmentCourse  +
                $department +
                $college->toArray() +
                $course->toArray();

            $realExam['questions_types'] = $questionTypes;

            $realExam['datetime'] = DatetimeHelper::convertDateTimeToLong($realExam['datetime']);

            $realExam = NullHelper::filter($realExam);

            return ResponseHelper::successWithData($realExam);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveEditablePaperExam(Request $request)
    {
        Gate::authorize('retrieveEditablePaperExam', PaperExamController::class);
        if (ValidateHelper::validateData($request, [
            'id' => 'required|integer'
        ])) {
            return  ResponseHelper::clientError();
        }
        try {
            $realExam = RealExam::findOrFail($request->id, [
                'id', 'forms_count', 'form_name_method as form_name_method_id',
                'datetime', 'type as type_id', 'note as special_note'
            ]);

            if ($realExam->forms_count === 1) {
                unset($realExam->form_name_method_id);
            }
            unset($realExam->forms_count);

            $paperExam =  PaperExam::where('id', $realExam->id)->first(['course_lecturer_name as lecturer_name']);
            $realExam = $realExam->toArray();

            unset($realExam['id']);
            $realExam = $realExam + $paperExam->toArray();

            $realExam['datetime'] = DatetimeHelper::convertDateTimeToLong($realExam['datetime']);

            $exam = NullHelper::filter($realExam);

            return ResponseHelper::successWithData($exam);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrievePaperExamChapters(Request $request)
    {
        Gate::authorize('retrievePaperExamChapters', PaperExamController::class);
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

    public function retrievePaperExamChapterTopics(Request $request)
    {
        Gate::authorize('retrievePaperExamChapterTopics', PaperExamController::class);
        if (ValidateHelper::validateData($request, [
            'exam_id' => 'required|integer',
            'chapter_id' => 'required|integer'
        ])) {
            return  ResponseHelper::clientError();
        }
        try {
            $topics = ExamHelper::retrieveRealExamChapterTopics($request->exam_id, $request->chapter_id);
            return ResponseHelper::successWithData($topics);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrievePaperExamForms(Request $request)
    {
        Gate::authorize('retrievePaperExamForms', PaperExamController::class);
        if (ValidateHelper::validateData($request, [
            'exam_id' => 'required|integer'
        ])) {
            return  ResponseHelper::clientError();
        }
        try {
            $forms = ExamHelper::retrieveRealExamForms($request->exam_id);
            return ResponseHelper::successWithData($forms);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrievePaperExamFormQuestions(Request $request)
    {
        Gate::authorize('retrievePaperExamFormQuestions', PaperExamController::class);
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

    public function exportPaperExamToPDF(Request $request)
    {
        // request {id, with_answer}
        // Gate::authorize('exportPaperExamToPDF', PaperExamController::class);

        // id, with mirror?, with answered mirror?
        /* Data:
        *   .
        *   . university *
        *   . college *
        *   . department *
        *   . level *
        *   .
        *   . course *
        *   . course part *
        *   . exam type *
        *   . date *
        *   . duration *
        *
        *   . lecturer *
        *   . score *
        *
        *   . forms
        *       . form name?
        *       . questions
        *           . content
        *           . attachment?
        *           . is true?
        *           . choices?
        *               . content
        *               . attachment?
        *               . is true?
        *   .
        * */
        // تبقى جزء فحص اذا كان يشتي مع اجابة او لا
        if (ValidateHelper::validateData($request, [
            'id' => 'required|integer',
            'with_answer' => 'required|boolean'
        ])) {
            return  ResponseHelper::clientError();
        }
        try {
            $realExam = RealExam::findOrFail($request->id, [
                'id', 'datetime', 'duration', 'type as type_name', 'course_lecturer_id',
                'forms_count', 'form_name_method', 'form_configuration_method', 'language as language_id'
            ]);

            $realExam = ProcessDataHelper::enumsConvertIdToName($realExam, [
                new EnumReplacement('type_name', ExamTypeEnum::class),
            ]);

            $paperExam = PaperExam::where('id', $realExam->id)->first(['course_lecturer_name as lecturer_name']);
            if (is_null($paperExam->lecturer_name)) {
                $lecturerId = $realExam->course_lecturer()->first()['lecturer_id'];
                $paperExam = Employee::findOrFail($lecturerId, [LanguageHelper::getEmployeeNameColumnName($realExam->id)]);
            }
            $courseLecturer = $realExam->course_lecturer()->first();
            $departmentCoursePart = $courseLecturer->department_course_part()->first();

            $coursePart = $departmentCoursePart->course_part()->first(['part_id as course_part_name']);
            $coursePart = ProcessDataHelper::enumsConvertIdToName($coursePart, [
                new EnumReplacement('course_part_name', CoursePartsEnum::class)
            ]);
            $departmentCourse = $departmentCoursePart->department_course()->first(['level as level_name', 'semester as semester_name', 'department_id', 'course_id']);
            $departmentCourse = ProcessDataHelper::enumsConvertIdToName($departmentCourse, [
                new EnumReplacement('level_name', LevelsEnum::class),
                new EnumReplacement('semester_name', SemesterEnum::class)
            ]);
            $department = $departmentCourse->department()->first(['arabic_name as department_name', 'college_id']);

            $college = $department->college()->first(['arabic_name as college_name']);

            $course = $departmentCourse->course()->first(['arabic_name as course_name']);
            // Total Score of exam 
            $questionsTypes = $realExam->real_exam_question_types()->get(['questions_count', 'question_score']);
            $totalScore = 0;
            foreach ($questionsTypes as $questionType) {
                $totalScore += ($questionType->questions_count * $questionType->question_score);
            }

            // university name 
            $jsonData = Storage::disk('local')->get('university.json');
            $universityData = json_decode($jsonData, true);
            $universityName = $universityData['arabic_name'];
            $universityLogoUrl = $universityData['logo'];

            // form and form questions 
            // as [formName, questions[], .....] or [formsName[name,...], questoins[]]
            $formsNames = ExamHelper::getRealExamFormsNames(intval($realExam->form_name_method), $realExam->forms_count);
            $examForms = $realExam->forms()->get(['id']);

            if (intval($realExam->form_count) > 1) {
                $examFormsQuestions = [];
                if (intval($realExam->form_configuration_methode) === FormConfigurationMethodEnum::DIFFERENT_FORMS->value) {
                    $i = 0;
                    foreach ($examForms as $formId) {
                        $formQuestions = ExamHelper::getFormQuestionsWithoutDetails($formId->id, false, false, $request->with_answer ?? false);
                        $examFormsQuestions[] = [
                            'form_name' => [$formsNames[$i++],
                            'questions' => $formQuestions]
                        ];
                    }
                } elseif (intval($realExam->form_configuration_methode) === FormConfigurationMethodEnum::SIMILAR_FORMS->value) {
                    $formQuestions = ExamHelper::getFormQuestionsWithoutDetails($examForms->first()->id, false, false, $request->with_answer ?? false);
                    for ($i=0; $i < $realExam->form_count; $i++) { 
                        $examFormsQuestions[] = [
                            'form_name' => $i + 1,
                            'questions' => $formQuestions
                        ];
                    }
                } elseif (intval($realExam->form_configuration_methode) === FormConfigurationMethodEnum::DIFFERENT_FORMS->value) {
                    $formQuestions = ExamHelper::getFormQuestionsWithoutDetails($examForms->first()->id, false, false, $request->with_answer ?? false);
                    for ($i=0; $i < $realExam->form_count; $i++) { 
                        $examFormsQuestions[] = [
                            'form_name' => $i + 1,
                            'questions' => collect($formQuestions)->shuffle()
                        ];
                    }
                } else {
                    return ResponseHelper::serverError();
                }
                $realExam['forms'] = $examFormsQuestions;
            } else {
                $formQuestions = ExamHelper::getFormQuestionsWithoutDetails($examForms->first()->id, false, false, $request->with_answer ?? false);
                $realExam['questions'] = $formQuestions;
            }

            $departmentCourse = $departmentCourse->toArray();
            unset($departmentCourse['department_id']);
            unset($departmentCourse['course_id']);

            $department = $department->toArray();
            unset($department['college_id']);

            $realExam = $realExam->toArray();
            unset($realExam['course_lecturer_id']);
            unset($realExam['id']);
            unset($realExam['form_name_method']);
            unset($realExam['forms_count']);
            unset($realExam['form_configuration_method']);

            $realExam = $realExam +
                $paperExam->toArray() +
                $coursePart->toArray() +
                $departmentCourse +
                $department +
                $college->toArray() +
                $course->toArray();

            $realExam['score'] = $totalScore;
            $realExam['university_name'] = $universityName;
            $realExam['university_logo_url'] = $universityLogoUrl;

            $realExam = NullHelper::filter($realExam);

            $realExam['datetime'] = DatetimeHelper::convertDateTimeToLong($realExam['datetime']);

            return ResponseHelper::successWithData($realExam);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    private function getFormQuestions($formId, bool $withAnsweredMirror, $language)
    {
        // return form questoin as [content, attachment, is_true, choices[content, attachment, is_true]]
        $language = LanguageEnum::symbolOf($language);
        $questions = [];

        // $form = Form::findOrFail($formId);
        // $formQuestions = $form->form_questions()->get(['question_id', 'combination_id']);
        try {
            $formQuestions = FormQuestion::where('form_id', '=', $formId)->get();

            foreach ($formQuestions as $formQuestion) {
                $question = $formQuestion->question()->first(['content', 'attachment as attachment_url']);
                if ($formQuestion->combination_id) {
                    if ($withAnsweredMirror) {
                        $question['choices'] = ExamHelper::retrieveCombinationChoices($formQuestion->question_id, $formQuestion->combination_id, false, true, $language);
                    } else {
                        $question['choices'] = ExamHelper::retrieveCombinationChoices($formQuestion->question_id, $formQuestion->combination_id, false, false, $language);
                    }
                } else {
                    if ($withAnsweredMirror) {
                        $trueFalseQuestion = TrueFalseQuestion::findOrFail($formQuestion->question_id)->first(['answer']);
                        if (intval($trueFalseQuestion->answer) === TrueFalseAnswerEnum::TRUE->value) {
                            $question['is_true'] = true;
                        } else {
                            $question['is_true'] = false;
                        }
                    }
                }
                array_push($questions, $question);
            }

            return $questions;
        } catch (\Exception $e) {
            throw $e;
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
        // يفضل ان يتم عملها مشترك ليتم استخداما في الاختبار الورقي والتجريبي
        // تقوم هذه الدالة باختيار توزيعة الاختيارات للاسئلة من نوع اختيار من متعدد
        /**
         * steps of function
         *   اختيار الاسئلة التي نوعها اختيار من متعدد
         *   اختيار احد التوزيعات التي يمتلكها السؤال بشكل عشوائي
         *   يتم اضافة رقم التوزيعة المختارة الي السؤال
         */
        $formQuestions = [];
        try {
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
	- note => special_note
	- course_lecturer_id
- real_exam_question_types => questions_types
	- question_type => type_id
	- questions_count
	- question_score
- paper_exams
	- course_lecturer_name => lecturer_name
- topics_ids
- another variables
	- department_course_part_id
		- 
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
            'form_configuration_method_id' => ['nullable', new Enum(FormConfigurationMethodEnum::class)],
            'form_name_method_id' => ['nullable', new Enum(FormNameMethodEnum::class)],

            // real_exam_question_types
            'questions_types' => 'required|array|min:1',
            'questions_types.*.type_id' => ['required', new Enum(QuestionTypeEnum::class)],
            'questions_types.*.questions_count' => 'required|integer',
            'questions_types.*.question_score' => 'required|numeric', // Use 'numeric' to allow both integer and float

            // paper_exam
            'lecturer_name' => 'nullable|string',

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
}
