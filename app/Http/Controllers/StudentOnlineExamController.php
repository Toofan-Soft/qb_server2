<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\Student;
use App\Models\Question;
use App\Models\RealExam;
use App\Enums\LevelsEnum;
use App\Models\OnlineExam;
use App\Enums\ExamTypeEnum;
use App\Enums\LanguageEnum;
use App\Enums\SemesterEnum;
use App\Helpers\ExamHelper;
use App\Helpers\NullHelper;
use Illuminate\Http\Request;
use App\Models\StudentAnswer;
use App\Enums\CoursePartsEnum;
use App\Helpers\BooleanHelper;
use App\Enums\QuestionTypeEnum;
use App\Enums\RealExamTypeEnum;
use App\Helpers\DatetimeHelper;
use App\Helpers\LanguageHelper;
use App\Helpers\ResponseHelper;
use App\Helpers\ValidateHelper;
use App\Helpers\EnumReplacement;
use App\Models\StudentOnlineExam;
use App\Enums\TrueFalseAnswerEnum;
use App\Helpers\ProcessDataHelper;
use Illuminate\Support\Facades\DB;
use App\Enums\OnlineExamStatusEnum;
use App\Enums\ExamConductMethodEnum;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Enum;
use App\Enums\CourseStudentStatusEnum;
use App\Enums\OnlineExamTakingStatusEnum;
use App\Helpers\OnlineExamListenerHelper;
use App\Enums\StudentOnlineExamStatusEnum;

class StudentOnlineExamController extends Controller
{
    public function retrieveOnlineExams(Request $request)
    {
        Gate::authorize('retrieveOnlineExams', StudentOnlineExamController::class);
        if (ValidateHelper::validateData($request, [
            'status_id' =>  ['required', new Enum(OnlineExamTakingStatusEnum::class)],
        ])) {
            return  ResponseHelper::clientError();
        }
        try {
            $studentId = Student::where('user_id', auth()->user()->id)->first()['id'];
            $onlineExams = [];

            if (isset($request->status_id) && intval($request->status_id) === OnlineExamTakingStatusEnum::COMPLETE->value) {
                $onlineExams = $this->retrieveCompleteStudentOnlineExams($studentId);
            } else {
                $onlineExams = $this->retrieveIncompleteStudentOnlineExams($studentId);
            }

            return ResponseHelper::successWithData($onlineExams);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveOnlineExam(Request $request)
    {
        Gate::authorize('retrieveOnlineExam', StudentOnlineExamController::class);
        if (ValidateHelper::validateData($request, [
            'id' => 'required|integer'
        ])) {
            return  ResponseHelper::clientError();
        }
        $studentId = Student::where('user_id', auth()->user()->id)->first()['id'];
        try {
            $exam = DB::table('real_exams')
                ->join('online_exams', 'real_exams.id', '=', 'online_exams.id')
                ->join('course_lecturers', 'real_exams.course_lecturer_id', '=', 'course_lecturers.id')
                ->join('employees', 'course_lecturers.lecturer_id', '=', 'employees.id')
                ->join('department_course_parts', 'course_lecturers.department_course_part_id', '=', 'department_course_parts.id')
                ->join('department_courses', 'department_course_parts.department_course_id', '=', 'department_courses.id')
                ->join('departments', 'department_courses.department_id', '=', 'departments.id')
                ->join('colleges', 'departments.college_id', '=', 'colleges.id')
                ->join('course_students', 'department_course_parts.department_course_id', '=', 'course_students.department_course_id')
                ->join('course_parts', 'department_course_parts.course_part_id', '=', 'course_parts.id')
                ->join('courses', 'course_parts.course_id', '=', 'courses.id')
                ->where('real_exams.id', $request->id)
                ->where('real_exams.exam_type', RealExamTypeEnum::ONLINE->value) // ONLINE
                // ->where('real_exams.datetime', '>', now()) // Not-Taken
                // ->where('online_exams.status', OnlineExamStatusEnum::ACTIVE->value) // ACTIVE
                ->where('online_exams.exam_datetime_notification_datetime', '<=', DatetimeHelper::now()) // VISIBLE
                ->where('course_students.student_id', $studentId)
                // ->where('course_students.status', CourseStudentStatusEnum::ACTIVE->value) // ACTIVE
                // ->where('course_students.academic_year', '=', date('Y')) // CURRENT YEAR
                // ->where('course_lecturers.academic_year', '=', date('Y')) // CURRENT YEAR
                ->select([
                    LanguageHelper::getNameColumnName('colleges', 'college_name'),
                    // 'colleges.arabic_name as college_name',
                    LanguageHelper::getNameColumnName('departments', 'department_name'),
                    'department_courses.level as level_name',
                    'department_courses.semester as semester_name',
                    'real_exams.datetime',
                    'real_exams.type as type_name',
                    'real_exams.language as language_name',
                    'real_exams.note as special_note',
                    'real_exams.duration',
                    DB::raw("CASE WHEN online_exams.conduct_method = '" . ExamConductMethodEnum::MANDATORY->value . "' THEN true ELSE false END as is_mandatory_question_sequence"),
                    LanguageHelper::getNameColumnName('courses', 'course_name'),
                    'course_parts.part_id as course_part_name',
                    LanguageHelper::getNameColumnName('employees', 'lecturer_name'),
                    DB::raw("(SELECT SUM(questions_count * question_score)
                            FROM public.real_exam_question_types
                            WHERE real_exam_id = real_exams.id) as total_score"),
                    'online_exams.status'
                ])
                ->first();

            if ($exam) {
                $studentOnlinExam = StudentOnlineExam::where('online_exam_id', $request->id)
                    ->where('student_id', $studentId)
                    ->first();
                if (
                    $exam->datetime <= DatetimeHelper::now() &&
                    intval($exam->status) === OnlineExamStatusEnum::ACTIVE->value
                ) {
                    if ($studentOnlinExam) {
                        $takingStatus = intval($studentOnlinExam->status);

                        if ($takingStatus === StudentOnlineExamStatusEnum::ACTIVE->value) {
                            $exam->is_takable = true;
                        } elseif ($takingStatus === StudentOnlineExamStatusEnum::SUSPENDED->value) {
                            $exam->is_suspended = true;
                        } elseif ($takingStatus === StudentOnlineExamStatusEnum::CANCELED->value) {
                            $exam->is_canceled = true;
                        } elseif ($takingStatus === StudentOnlineExamStatusEnum::COMPLETE->value) {
                            // student finish exam..
                            $exam->is_complete = true;
                        }
                    } else {
                        // default..
                        $exam->is_takable = false;
                    }
                } elseif (intval($exam->status) === OnlineExamStatusEnum::COMPLETE->value) {
                    // exam complete..
                    $exam->is_complete = true;
                }

                unset($exam->status);

                $exam->datetime = DatetimeHelper::convertDateTimeToLong($exam->datetime);

                $exam = ProcessDataHelper::enumsConvertIdToName(
                    $exam,
                    [
                        new EnumReplacement('level_name', LevelsEnum::class),
                        new EnumReplacement('semester_name', SemesterEnum::class),
                        new EnumReplacement('course_part_name', CoursePartsEnum::class),
                        new EnumReplacement('language_name', LanguageEnum::class),
                        new EnumReplacement('type_name', ExamTypeEnum::class)
                    ]
                );

                $exam = NullHelper::filter($exam);

                return ResponseHelper::successWithData($exam);
            } else {
                return ResponseHelper::clientError();
            }
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    // public function retrieveOnlineExam1(Request $request)
    // {
    //     Gate::authorize('retrieveOnlineExam', StudentOnlineExamController::class);
    //     // تستخدم هذه الدالة لارجاع الاختبارات الغير مكتملة فقط
    //     try {
    //         $studentonlinExam = StudentOnlineExam::where('online_exam_id', $request->id)->first();
    //         $realExam = [];

    //         return $studentonlinExam;

    //         $isComplete = (intval($studentonlinExam->status) === StudentOnlineExamStatusEnum::COMPLETE->value) ? true : false;

    //         if (!$isComplete) {
    //             $realExam = RealExam::find($studentonlinExam->online_exam_id, [
    //                 'id', 'language as language_name',
    //                 'datetime', 'duration', 'type as type_name', 'note as special_note', 'course_lecturer_id'
    //             ]);

    //             $enumReplacement = [
    //                 new EnumReplacement('language_name', LanguageEnum::class),
    //                 new EnumReplacement('type_name', ExamTypeEnum::class),
    //             ];

    //             $realExam = ProcessDataHelper::enumsConvertIdToName($realExam, $enumReplacement);
    //             $jsonData = Storage::disk('local')->get('generalNotes.json'); // get notes from json file
    //             $general_note = json_decode($jsonData, true);
    //             $realExam['general_note'] =  $general_note;        //// Done

    //             $realExam = ExamHelper::getRealExamsScore($realExam);
    //             $onlineExam = OnlineExam::where('id', $realExam->id)->first(['conduct_method as is_mandatory_question_sequense']);
    //             $onlineExam->is_mandatory_question_sequense = ($onlineExam->is_mandatory_question_sequense === ExamConductMethodEnum::MANDATORY->value) ? true : false;
    //             $courselecturer = $realExam->course_lecturer()->first();
    //             $lecturer =  Employee::where('id', $courselecturer->lecturer_id)->first(['arabic_name as lecturer_name']);
    //             $departmentCoursePart = $courselecturer->department_course_part()->first();
    //             $coursePart = $departmentCoursePart->course_part()->first(['part_id as course_part_name']);
    //             $coursePart = ProcessDataHelper::enumsConvertIdToName($coursePart, [
    //                 new EnumReplacement('course_part_name', CoursePartsEnum::class),
    //             ]);
    //             $departmentCourse = $departmentCoursePart->department_course()->first(['level as level_name', 'semester as semester_name', 'department_id', 'course_id']);
    //             $departmentCourse = ProcessDataHelper::enumsConvertIdToName($departmentCourse, [
    //                 new EnumReplacement('level_name', LevelsEnum::class),
    //                 new EnumReplacement('semester_name', SemesterEnum::class),
    //             ]);

    //             $department = $departmentCourse->department()->first(['arabic_name as department_name', 'college_id']);
    //             $college = $department->college()->first(['arabic_name as college_name']);
    //             $course = $departmentCourse->course()->first(['arabic_name as course_name']);

    //             //*** make unset to : 'department_id', 'course_id', 'college_id', 'course_lecturer_id'
    //             $departmentCourse = $departmentCourse->toArray();
    //             unset($departmentCourse['department_id']);
    //             unset($departmentCourse['course_id']);

    //             $department = $department->toArray();
    //             unset($department['college_id']);

    //             $realExam = $realExam->toArray();
    //             unset($realExam['course_lecturer_id']);
    //             $realExam = NullHelper::filter($realExam);
    //         }

    //         $realExam =
    //             $realExam  +
    //             $onlineExam->toArray() +
    //             $lecturer->toArray() +
    //             $coursePart->toArray() +
    //             $departmentCourse  +
    //             $department +
    //             $college->toArray() +
    //             $course->toArray();

    //         return ResponseHelper::successWithData($realExam);
    //     } catch (\Exception $e) {
    //         return ResponseHelper::serverError();
    //     }
    // }

    public function retrieveOnlineExamQuestions(Request $request)
    {
        Gate::authorize('retrieveOnlineExamQuestions', StudentOnlineExamController::class);
        if (ValidateHelper::validateData($request, [
            'exam_id' => 'required|integer'
        ])) {
            return  ResponseHelper::clientError();
        }
        try {
            $exam = OnlineExam::findOrFail($request->exam_id);

            if (
                $exam->datetime <= DatetimeHelper::now() &&
                intval($exam->status) === OnlineExamStatusEnum::ACTIVE->value
            ) {
                $studentId = Student::where('user_id', auth()->user()->id)
                    ->first()['id'];

                $studentOnlineExam = StudentOnlineExam::where('online_exam_id', $request->exam_id)
                    ->where('student_id', $studentId)
                    ->first(['status', 'form_id']);

                if (!is_null($studentOnlineExam)) {
                    if (intval($studentOnlineExam->status) === StudentOnlineExamStatusEnum::ACTIVE->value) {
                        $questions = $this->getFormQuestions($studentOnlineExam->form_id, $studentId);

                        $questions = NullHelper::filter($questions);

                        return ResponseHelper::successWithData($questions);
                    }
                } else {
                    return ResponseHelper::clientError();
                }
            } else {
                return ResponseHelper::clientError();
            }

            // $studentId = Student::where('user_id', auth()->user()->id)->first()['id'];
            // $formId = StudentOnlineExam::where('online_exam_id', $request->id)
            //     ->where('student_id', $studentId)
            //     ->first(['form_id']);

            // $questions = $this->getFormQuestions($formId);

            // $questions = NullHelper::filter($questions);

            // return ResponseHelper::successWithData($questions);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    private function getFormQuestions($formId, $studentId)
    {
        $questions = [];
        try {
            $form = Form::findOrFail($formId);
            $realExam = RealExam::findOrFail($form->real_exam_id);
            $language = LanguageEnum::symbolOf($realExam->language);

            $formQuestions = $form->form_questions()->orderBy('question_id')->get(['question_id', 'combination_id']);

            foreach ($formQuestions as $formQuestion) {
                $question = $formQuestion->question()->first(['id', 'content', 'attachment as attachment_url']);

                $answer = StudentAnswer::where('student_id', $studentId)
                    ->where('form_id', $formId)
                    ->where('question_id', $question->id)
                    ->first()['answer'];
                
                if ($formQuestion->combination_id) {
                    $question['choices'] = ExamHelper::retrieveCombinationChoices($formQuestion->question_id, $formQuestion->combination_id, true, false, $language);

                    if (!is_null($answer)) {
                        $choices = $question->choices; // Retrieve the choices array

                        if (isset($choices['mixed'])) {
                            if ($choices['mixed']['id'] === $answer) {
                                $choices['mixed']['is_selected'] = true;
                            } else {
                                $choices['mixed']['choices'] = collect($choices['mixed']['choices'])->map(function ($choice) use ($answer) {
                                    if ($choice['id'] === $answer) {
                                        $choice['is_selected'] = true;
                                    }

                                    return $choice;
                                })->toArray();
                            }
                        }

                        if (isset($choices['unmixed'])) {
                            $choices['unmixed'] = collect($choices['unmixed'])->map(function ($choice) use ($answer) {
                                if ($choice['id'] === $answer) {
                                    $choice['is_selected'] = true;
                                }

                                return $choice;
                            })->toArray();
                        }

                        $question->choices = $choices; // Set the modified choices array back to the property
                    }
                } else {
                    if ($answer === TrueFalseAnswerEnum::TRUE->value) {
                        $question->user_answer = true;
                    } elseif ($answer === TrueFalseAnswerEnum::FALSE->value) {
                        $question->user_answer = false;
                    }
                }
                array_push($questions, $question);
            }
            return $questions;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function finishOnlineExam(Request $request)
    {
        Gate::authorize('finishOnlineExam', StudentOnlineExamController::class);
        if (ValidateHelper::validateData($request, [
            'id' => 'required|integer'
        ])) {
            return  ResponseHelper::clientError();
        }
        DB::beginTransaction();
        try {
            $student = Student::where('user_id', auth()->user()->id)->first();
            $studentOnlineExam = StudentOnlineExam::where('student_id', $student->id)
                ->where('online_exam_id', $request->id)
                ->update([
                    'status' => StudentOnlineExamStatusEnum::COMPLETE->value,
                    'end_datetime' => DatetimeHelper::now(),
                ]);

            // refresh student and proctor
            OnlineExamListenerHelper::refreshProctor($student->id, $request->id);

            DB::commit();
            return ResponseHelper::success();
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseHelper::serverError();
        }
    }

    public function saveOnlineExamQuestionAnswer(Request $request)
    {
        Gate::authorize('saveOnlineExamQuestionAnswer', StudentOnlineExamController::class);
        if (ValidateHelper::validateData($request, [
            'exam_id' => 'required|integer',
            'question_id' => 'required|integer',
            'choice_id' => 'nullable|integer',
            // 'is_true' => 'nullable|boolean',
        ])) {
            return  ResponseHelper::clientError();
        }
        
        try {
            $studentId = Student::where('user_id', auth()->user()->id)->first()['id'];
            $formId = StudentOnlineExam::where('online_exam_id', $request->exam_id)
                ->where('student_id', $studentId)
                ->first()->form_id;

            $questionType = Question::findOrFail($request->question_id, ['type']);
            $answerId = null;
            if (intval($questionType->type) === QuestionTypeEnum::TRUE_FALSE->value) {
                if(!isset($request->is_true) || is_null($request->is_true)){
                    return  ResponseHelper::clientError();
                }
                $answerId = BooleanHelper::toBoolean($request->is_true) ? TrueFalseAnswerEnum::TRUE->value : TrueFalseAnswerEnum::FALSE->value;
                
            } elseif (intval($questionType->type) === QuestionTypeEnum::MULTIPLE_CHOICE->value) {
                if(!isset($request->choice_id) || is_null($request->choice_id)){
                    return  ResponseHelper::clientError();
                }

                $answerId =  $request->choice_id;
            }else{
                return ResponseHelper::clientError();
            }

            StudentAnswer::where('student_id', $studentId)
                ->where('form_id', $formId)
                ->where('question_id', $request->question_id)
                ->update([
                    'answer' =>  $answerId
                ]);
            
            // refresh student and proctor 
            OnlineExamListenerHelper::refreshProctor($studentId, $request->exam_id);
            
            return ResponseHelper::success();
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    // need to test
    private function retrieveCompleteStudentOnlineExams($studentId)
    {
        try {
            $onlineExams =  DB::table('student_online_exams')
                ->join('online_exams', 'student_online_exams.online_exam_id', '=', 'online_exams.id')
                ->join('real_exams', 'online_exams.id', '=', 'real_exams.id')
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
                    'student_online_exams.form_id',
                    'online_exams.result_notification_datetime',
                )
                // ->where('real_exams.datetime', '<=', DatetimeHelper::now()) // هل يتم اضافة هذا التحقق 
                ->where('online_exams.status', OnlineExamStatusEnum::COMPLETE->value) // ACTIVE
                // ->where('online_exams.exam_datetime_notification_datetime', '<=', DatetimeHelper::now()) // هل يتم اضافة هذا التحقق

                ->where('student_online_exams.student_id', '=', $studentId)
                ->where('student_online_exams.status', '=', StudentOnlineExamStatusEnum::COMPLETE->value) // هل يتم ايضا التحقق اذا كانت حالة الاختبار ملغي
                ->get();
            $onlineExams = ProcessDataHelper::enumsConvertIdToName($onlineExams, [new EnumReplacement('course_part_name', CoursePartsEnum::class)]);
            foreach ($onlineExams as $onlineExam) {
                if(DatetimeHelper::convertDateTimeToLong($onlineExam->result_notification_datetime) <= DatetimeHelper::convertDateTimeToLong(DatetimeHelper::now())){
                // if($onlineExam->result_notification_datetime <= DatetimeHelper::now()){
                    $studentResult = $this->retrieveStudentOnlineExamsResult($onlineExam->id, $onlineExam->form_id, $studentId);
                    $onlineExam->score_rate = $studentResult['score_rate'];
                    $onlineExam->appreciation = $studentResult['appreciation'];
                }
                unset($onlineExam->result_notification_datetime);
            }

            return $onlineExams;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    private function retrieveStudentOnlineExamsResult($onlineExamId, $formId, $studentId)
    {
        try {
            $questionsAnswers =  DB::table('student_answers')
                ->join('form_questions', 'student_answers.form_id', '=', 'form_questions.form_id')
                ->join('form_questions', 'student_answers.question_id', '=', 'form_questions.question_id')
                ->select(
                    'student_answers.answer',
                    'form_questions.question_id',
                    'form_questions.combination_id'
                )
                ->where('student_answers.student_id', '=', $studentId)
                ->where('student_answers.form_id', '=', $formId)
                ->get();

            $examScores = $this->getRealExamQuestionScore($onlineExamId);

            $StudentScore = 0;

            foreach ($questionsAnswers as $questionAnswer) {
                if (ExamHelper::checkQuestionAnswer($questionAnswer->question_id, $questionAnswer->answer, $questionAnswer->combination_id)) {
                    $StudentScore++;
                }
            }

            $scoreRate = $StudentScore / $examScores * 100;
            $appreciation = ExamHelper::getExamResultAppreciation($scoreRate);

            $studentResult = [
                'score_rate' => $scoreRate,
                'appreciation' => $appreciation
            ];

            return $studentResult;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    private function getRealExamQuestionScore($onlineExamId)
    {
        // $examScores = []; php artisan passport:install
        try {
            $realExam = RealExam::findOrFail($onlineExamId);

            $realExamQuestionTypes = $realExam->real_exam_question_types()
                ->get(['question_type', 'question_score', 'questions_count']);

            $totalScore = 0;
            foreach ($realExamQuestionTypes as $realExamQuestionType) {
                // $examScores[intval($realExamQuestionType->question_type)] = $realExamQuestionType->question_score;
                $totalScore += $realExamQuestionType->questions_count * $realExamQuestionType->question_score;
            }

            $examScores['totalScore'] = $totalScore;
            // return $examScores;
            return $totalScore;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    private function retrieveIncompleteStudentOnlineExams($studentId)
    {
        try {
            // $onlineExams = DB::table('student_online_exams')
            //     ->join('online_exams', 'student_online_exams.online_exam_id', '=', 'online_exams.id')
            //     ->join('real_exams', 'online_exams.id', '=', 'real_exams.id')
            //     ->join('course_lecturers', 'real_exams.course_lecturer_id', '=', 'course_lecturers.id')
            //     ->join('department_course_parts', 'course_lecturers.department_course_part_id', '=', 'department_course_parts.id')
            //     ->join('department_courses', 'department_course_parts.department_course_id', '=', 'department_courses.id')
            //     ->join('courses', 'department_courses.course_id', '=', 'courses.id')
            //     ->join('course_parts', 'department_course_parts.course_part_id', '=', 'course_parts.id')
            //     ->select(
            //         'courses.arabic_name as course_name ',
            //         'course_parts.part_id as course_part_name ',
            //         'real_exams.id',
            //         'real_exams.datetime',
            //     )
            //     ->where('student_online_exams.student_id', '=', $studentId)
            //     ->where('student_online_exams.status', '!=', StudentOnlineExamStatusEnum::COMPLETE->value)
            //     ->get();

            $exams = DB::table('real_exams')
                ->join('online_exams', 'real_exams.id', '=', 'online_exams.id')
                ->join('course_lecturers', 'real_exams.course_lecturer_id', '=', 'course_lecturers.id')
                ->join('department_course_parts', 'course_lecturers.department_course_part_id', '=', 'department_course_parts.id')
                ->join('course_students', 'department_course_parts.department_course_id', '=', 'course_students.department_course_id')
                ->join('course_parts', 'department_course_parts.course_part_id', '=', 'course_parts.id')
                ->join('courses', 'course_parts.course_id', '=', 'courses.id')
                ->where('real_exams.exam_type', RealExamTypeEnum::ONLINE->value) // ONLINE
                // ->where('real_exams.datetime', '>', DatetimeHelper::now()) // Not-Taken
                ->where('online_exams.status', OnlineExamStatusEnum::ACTIVE->value) // ACTIVE
                ->where('online_exams.exam_datetime_notification_datetime', '<=', DatetimeHelper::now()) // VISIBLE
                ->where('course_students.student_id', $studentId)
                ->where('course_students.status', CourseStudentStatusEnum::ACTIVE->value) // ACTIVE
                ->where('course_students.academic_year', '=', date('Y')) // CURRENT YEAR
                ->where('course_lecturers.academic_year', '=', date('Y')) // CURRENT YEAR
                ->select(
                    'real_exams.id',
                    'real_exams.datetime',
                    'courses.arabic_name as course_name',
                    'course_parts.part_id as course_part_name'
                )
                ->get()
                ->map(function ($exam) {
                    $exam->datetime = DatetimeHelper::convertDateTimeToLong($exam->datetime);
                    return $exam;
                });

            $exams = ProcessDataHelper::enumsConvertIdToName($exams, [new EnumReplacement('course_part_name', CoursePartsEnum::class)]);

            return $exams;
        } catch (\Exception $e) {
            throw $e;
        }
    }
   
}
