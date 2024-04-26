<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Employee;
use App\Models\RealExam;
use App\Enums\LevelsEnum;
use App\Models\PaperExam;
use App\Enums\ExamTypeEnum;
use App\Enums\LanguageEnum;
use App\Enums\SemesterEnum;
use App\Helpers\ExamHelper;
use Illuminate\Http\Request;
use App\Helpers\DeleteHelper;
use App\Enums\CoursePartsEnum;
use App\Models\CourseLecturer;
use App\Enums\QuestionTypeEnum;
use App\Enums\RealExamTypeEnum;
use App\Helpers\ResponseHelper;
use App\Helpers\ValidateHelper;
use App\Helpers\EnumReplacement;
use App\Helpers\OnlinExamHelper;
use App\Enums\FormNameMethodEnum;
use App\Enums\QuestionStatusEnum;
use App\Helpers\ProcessDataHelper;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rules\Enum;
use App\Enums\AccessibilityStatusEnum;
use App\Enums\ExamDifficultyLevelEnum;
use App\AlgorithmAPI\GeneratePaperExam;
use App\Enums\FormConfigurationMethodEnum;

class PaperExamController extends Controller
{
    public function addPaperExam(Request $request)
    {
        if (ValidateHelper::validateData($request, $this->rules($request))) {
            return  ResponseHelper::clientError(401);
        }

        $algorithmData = $this->getAlgorithmData($request);
        $examFormsQuestions = (new GeneratePaperExam())->execute($algorithmData);

        if ($examFormsQuestions->data) { // modify to use has function

            $user = User::findOrFail(auth()->user()->id);
            $courseLecturer = CourseLecturer::where('department_course_part_id', $request->department_course_part_id)
                ->where('lecturer_id', $user->employee()->id)
                ->where('academic_year', now()->format('Y'));

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
                'exam_type' => RealExamTypeEnum::PAPER->value,
            ]);

            $realExam->paper_exam()->create([
                'course_lecturer_name' => $request->lecturer_name ?? $user->employee()->arabic_name,
            ]);

            foreach ($request->question_types as $question_type) {
                $realExam->real_exam_question_types()->create([
                    'question_type' => $question_type->type_id,  ///// ensure
                    'questions_count' => $question_type->questions_count,
                    'question_score'  => $question_type->question_score,
                ]);
            }

            //////////add Topics of exam

            if ($request->form_configuration_method === FormConfigurationMethodEnum::SIMILAR_FORMS->value) {
                $realExam->forms()->create();
            } else {
                foreach ($request->forms_count as $form) {
                    $realExam->forms()->create();
                }
            }

            foreach ($examFormsQuestions as $formQuestions) {
                $formQuestions = $this->getQuestionsChoicesCombinations($formQuestions);
                $form = $realExam->forms()->create();
                foreach ($formQuestions as $formQuestion) {
                    $form->form_questions()->create([
                        'question_id' => $formQuestion->question_id,
                        'combination_id' => $formQuestion->combination_id?? null,
                    ]);
                }
            }
            ////////// modify question usage table يفضل ان يتم عمل دالة مشتركة حتى يتم استخدامها في الاختبار الورقي

            return ResponseHelper::successWithData($realExam->id);
        }else{
            return ResponseHelper::serverError();
        }
    }

    public function modifyPaperExam(Request $request)
    {
        $realExam = RealExam::findOrFail($request->id);
        $realExam->update([
            'type' => $request->type_id ?? $realExam->type,
            'datetime' => $request->datetime ?? $realExam->datetime,
            'note' => $request->special_note ?? $realExam->note,
            'form_name_method' => $request->form_name_method_id ?? $realExam->form_name_method,
        ]);

        $paperExam = $realExam->paper_exam();
        $paperExam->update([
            'course_lecturer_name' => $request->lecturer_name ?? $paperExam->lecturer_name
        ]);
    }

    public function deletePaperExam(PaperExam $paperExam)
    {
        return DeleteHelper::deleteModel($paperExam);
    }

    public function retrievePaperExams(Request $request) ////**** يتم اضافة شرط ان يتم ارجاع الاختبارات التي تنتمي الى المستخدم الحالي
    {
        // يجب الاخذ بالاعتبار انه سيتم التعامل مع اختبارات تنتمي الي اعوام سابقة

        $employee = Employee::where('user_id', '=', auth()->user()->id);
        $paperExams = [];

        $enumReplacements  = [];
        if ($request->type_id) {
            $paperExams =  DB::table('real_exams')
                ->join('paper_exams', 'real_exams.id', '=', 'paper_exams.id')
                ->join('course_lucturers', 'real_exams.course_lucturer_id', '=', 'course_lucturers.id')
                ->join('department_course_parts', 'course_lucturers.department_course_part_id', '=', 'department_course_parts.id')
                ->select(
                    'real_exams.id ',
                    'real_exams.datetime',
                    'real_exams.forms_count',
                    'paper_exams.course_lecturer_name as lecturer_name'
                )
                ->where('department_course_parts.id', '=', $request->department_course_part_id)
                ->where('real_exams.type', '=', $request->type_id)
                ->where('course_lucturers.lecturer_id', '=', $employee->id)
                ->get();
        } else {
            $paperExams =  DB::table('real_exams')
                ->join('paper_exams', 'real_exams.id', '=', 'paper_exams.id')
                ->join('course_lucturers', 'real_exams.course_lucturer_id', '=', 'course_lucturers.id')
                ->join('department_course_parts', 'course_lucturers.department_course_part_id', '=', 'department_course_parts.id')
                ->select(
                    'real_exams.id ',
                    'real_exams.type as type_name ',
                    'real_exams.datetime',
                    'real_exams.forms_count',
                    'paper_exams.course_lecturer_name as lecturer_name'
                )
                ->where('department_course_parts.id', '=', $request->department_course_part_id)
                ->where('course_lucturers.lecturer_id', '=', $employee->id)
                ->get();
            array_push($enumReplacements,  new EnumReplacement('type_name', ExamTypeEnum::class));
        }

        $paperExams = ProcessDataHelper::enumsConvertIdToName($paperExams, $enumReplacements);
        return ExamHelper::getRealExamsScore($paperExams);
    }

    public function retrievePaperExamsAndroid(Request $request)
    {
        $employee = Employee::where('user_id', '=', auth()->user()->id);
        $paperExams = [];

        $enumReplacements  = [
            new EnumReplacement('type_name', ExamTypeEnum::class),
            new EnumReplacement('course_part_name', CoursePartsEnum::class),
            new EnumReplacement('language_name', LanguageEnum::class),
        ];
        if ($request->department_course_part_id && $request->type_id) {
            $paperExams =  DB::table('real_exams')
                ->join('paper_exams', 'real_exams.id', '=', 'paper_exams.id')
                ->join('course_lucturers', 'real_exams.course_lucturer_id', '=', 'course_lucturers.id')
                ->join('department_course_parts', 'course_lucturers.department_course_part_id', '=', 'department_course_parts.id')
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
                    'paper_exams.course_lecturer_name as lecturer_name',

                )
                ->where('department_course_parts.id', '=', $request->department_course_part_id)
                ->where('real_exams.type', '=', $request->type_id)
                ->where('course_lucturers.lecturer_id', '=', $employee->id) // add this to lecturer online exam
                ->get();
        } elseif (!$request->department_course_part_id && !$request->type_id) {
            $paperExams =  DB::table('real_exams')
                ->join('paper_exams', 'real_exams.id', '=', 'paper_exams.id')
                ->join('course_lucturers', 'real_exams.course_lucturer_id', '=', 'course_lucturers.id')
                ->join('department_course_parts', 'course_lucturers.department_course_part_id', '=', 'department_course_parts.id')
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
                    'paper_exams.course_lecturer_name as lecturer_name',

                )
                ->where('course_lucturers.lecturer_id', '=', $employee->id) // add this to lecturer online exam
                ->get();
        } elseif ($request->department_course_part_id && !$request->type_id) {
            $paperExams =  DB::table('real_exams')
                ->join('paper_exams', 'real_exams.id', '=', 'paper_exams.id')
                ->join('course_lucturers', 'real_exams.course_lucturer_id', '=', 'course_lucturers.id')
                ->join('department_course_parts', 'course_lucturers.department_course_part_id', '=', 'department_course_parts.id')
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
                    'paper_exams.course_lecturer_name as lecturer_name',

                )
                ->where('department_course_parts.id', '=', $request->department_course_part_id)
                ->where('course_lucturers.lecturer_id', '=', $employee->id) // add this to lecturer online exam
                ->get();
        } else {
            $paperExams =  DB::table('real_exams')
                ->join('paper_exams', 'real_exams.id', '=', 'paper_exams.id')
                ->join('course_lucturers', 'real_exams.course_lucturer_id', '=', 'course_lucturers.id')
                ->join('department_course_parts', 'course_lucturers.department_course_part_id', '=', 'department_course_parts.id')
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
                    'paper_exams.course_lecturer_name as lecturer_name',

                )
                ->where('real_exams.type', '=', $request->type_id)
                ->where('course_lucturers.lecturer_id', '=', $employee->id) // add this to lecturer online exam
                ->get();
        }

        $paperExams = ProcessDataHelper::enumsConvertIdToName($paperExams, $enumReplacements);

        return $paperExams;
    }

    public function retrievePaperExam(Request $request)
    {
        $realExam = RealExam::findOrFail($request->id)->get([
            'language as language_name', 'difficulty_level as defficulty_level_name',
            'forms_count', 'form_configuration_method as form_configuration_method_name',
            'form_name_method as form_name_method_name',
            'datetime', 'duration', 'type as type_name', 'note as special_note'
        ]);
        $realExam = ProcessDataHelper::enumsConvertIdToName($realExam, [
            new EnumReplacement('language_name', LanguageEnum::class),
            new EnumReplacement('defficulty_level_name', ExamDifficultyLevelEnum::class),
            new EnumReplacement('form_configuration_method_name', FormConfigurationMethodEnum::class),
            new EnumReplacement('form_name_method_name', FormNameMethodEnum::class),
            new EnumReplacement('type_name', ExamTypeEnum::class),
        ]);

        $paperExam = $realExam->paper_exam()->get(['course_lecturer_name as lecturer_name']);

        $departmentCoursePart = $realExam->lecturer_course()->department_course_part();

        $coursePart = $departmentCoursePart->course_part(['part_id as course_part_name']);
        $coursePart = ProcessDataHelper::enumsConvertIdToName($coursePart, [
            new EnumReplacement('course_part_name', CoursePartsEnum::class)
        ]);

        $departmentCourse = $departmentCoursePart->department_course()->get(['level as level_name', 'semester as semester_name']);
        $departmentCourse = ProcessDataHelper::enumsConvertIdToName($departmentCourse, [
            new EnumReplacement('level_name', LevelsEnum::class),
            new EnumReplacement('semester_name', SemesterEnum::class)
        ]);

        $department = $departmentCourse->department()->get(['arabic_name as department_name']);

        $college = $department->college()->get(['arabic_name as college_name']);

        $course = $departmentCourse->course()->get(['arabic_name as course_name']);

        $questionTypes = $realExam->real_exam_question_types()->get(['question_type as type_name', 'questions_count', 'question_score']);
        $questionTypes = ProcessDataHelper::enumsConvertIdToName($questionTypes, [
            new EnumReplacement('type_name', QuestionTypeEnum::class)
        ]);

        array_merge($realExam, $paperExam, $coursePart, $departmentCourse, $department, $college, $course); // merge all with realExam
        $realExam['questionTypes'] = $questionTypes;

        return ResponseHelper::successWithData($realExam);
    }

    public function retrieveEditablePaperExam(Request $request)
    {
        $realExam = RealExam::findOrFail($request->id)->get([
            'form_name_method as form_name_method_id',
            'datetime', 'type as type_id', 'note as special_note'
        ]);

        $paperExam = $realExam->paper_exam()->get(['course_lecturer_name as lecturer_name']);

        array_merge($realExam, $paperExam); // merge all with realExam

        return ResponseHelper::successWithData($realExam);
    }

    public function retrievePaperExamChapters(Request $request)
    {
        return ExamHelper::retrieveRealExamChapters($request->exam_id);
    }

    public function retrievePaperExamChapterTopics(Request $request)
    {
        return ExamHelper::retrieveRealExamChapterTopics($request->exam_id, $request->chapter_id);
    }

    public function retrievePaperExamForms(Request $request)
    {
        return ExamHelper::retrieveRealExamForms($request->exam_id);
    }
    public function retrievePaperExamFormQuestions(Request $request)
    {
        return ExamHelper::retrieveRealExamFormQuestions($request->form_id);
    }

    public function exportPaperExamToPDF(Request $request)
    {
        // id, with mirror?, with answered mirror?
    }

    private function getAlgorithmData($request)
    {
        // دالة مشتركة للاختبار الحقيقي والورقي
        $algorithmData = [
            'duration' => $request->duration,
            'language_id' => $request->language_id,
            'difficulty_level_id' => $request->difficulty_level_id,
            'forms_count' => $request->forms_count,
            'form_configuration_method_id' => $request->form_configuration_method_id,
            'questions_types' => $request->questions_types,
        ];

        $questionTypesIds = $request->questions_types['type_id']; // التحقق من ان نحصل على مصفوفه 
        $accessabilityStatusIds = [
            AccessibilityStatusEnum::REALEXAM->value,
            AccessibilityStatusEnum::PRACTICE_REALEXAM->value,
        ];
        $questions =  DB::table('questions')
            ->join('question_usages', 'questions.id', '=', 'question_usages.question_id')
            ->join('topics', 'questions.topic_id', '=', 'topics.id')
            ->select(
                'questions.id',
                'questions.type',
                'questions.difficulty_level',
                'questions.estimated_answer_time',
                'question_usages.online_exam_last_selection_datetime',
                'question_usages.practice_exam_last_selection_datetime',
                'question_usages.paper_exam_last_selection_datetime',
                'question_usages.online_exam_selection_times_count',
                'question_usages.practice_exam_selection_times_count',
                'question_usages.paper_exam_selection_times_count',
                'topics.id',
                'topics.chapter_id'
            )
            ->where('questions.status', '=', QuestionStatusEnum::ACCEPTED->value)
            ->where('questions.language', '=', $request->language_id)
            ->whereIn('questions.accessability_status', $accessabilityStatusIds)
            ->whereIn('questions.type', $questionTypesIds)
            ->whereIn('topics.id', $request->topicsIds)
            ->get();
        $algorithmData['questions'] = $questions;
        return $algorithmData;
    }

    private function getQuestionsChoicesCombinations($formQuestions)
    {
        // يفضل ان يتم عملها مشترك ليتم استخداما في الاختبار الورقي والتجريبي
        // تقوم هذه الدالة باختيار توزيعة الاختيارات للاسئلة من نوع اختيار من متعدد
        /**
         * steps of function 
         *   اختيار الاسئلة التي نوعها اختيار من متعدد
         *   اختيار احد التوزيعات التي يمتلكها السؤال بشكل عشوائي
         *   يتم اضافة رقم التوزيعة المختارة الي السؤال 
         */
        return $formQuestions;
    }


    public function rules(Request $request): array
    {
        $rules = [
            'title' => 'nullable|string',
            'language_id' => ['required', new Enum(LanguageEnum::class)],
            'duration' => 'required|integer',
            'difficulty_level_id' => ['required', ExamDifficultyLevelEnum::class],
            'department_course_part_id' => 'required|exists:department_course_parts,id',
            'course_lecturer_name' => 'nullable|string',
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
