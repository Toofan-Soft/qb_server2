<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\Student;
use App\Models\Employee;
use App\Models\Question;
use App\Models\RealExam;
use App\Enums\LevelsEnum;
use App\Models\Department;
use App\Models\OnlineExam;
use App\Traits\EnumTraits;
use App\Enums\ExamTypeEnum;
use App\Enums\LanguageEnum;
use App\Enums\SemesterEnum;
use App\Helpers\ExamHelper;
use App\Helpers\NullHelper;
use Illuminate\Http\Request;
use App\Enums\ExamStatusEnum;
use App\Models\StudentAnswer;
use App\Enums\CoursePartsEnum;
use App\Enums\QuestionTypeEnum;
use App\Enums\RealExamTypeEnum;
use App\Helpers\DatetimeHelper;
use App\Helpers\LanguageHelper;
use App\Helpers\QuestionHelper;
use App\Helpers\ResponseHelper;
use App\Helpers\EnumReplacement;
use App\Helpers\OnlinExamHelper;
use App\Helpers\EnumReplacement1;
use App\Models\StudentOnlineExam;
use App\Enums\TrueFalseAnswerEnum;
use App\Helpers\ProcessDataHelper;
use Illuminate\Support\Facades\DB;
use App\Enums\ExamConductMethodEnum;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Enum;
use App\Enums\CourseStudentStatusEnum;
use App\Enums\ExamDifficultyLevelEnum;
use Illuminate\Support\Facades\Storage;
use App\Enums\OnlineExamTakingStatusEnum;
use App\Helpers\OnlineExamListenerHelper;
use App\Enums\FormConfigurationMethodEnum;
use App\Enums\StudentOnlineExamStatusEnum;

class StudentOnlineExamController extends Controller
{
    public function retrieveOnlineExams(Request $request)
    {
        Gate::authorize('retrieveOnlineExams', StudentOnlineExamController::class);

        try {
            $studentId = Student::where('user_id', auth()->user()->id)->first()['id'];
            $onlineExams = [];
            if (intval($request->status_id) === OnlineExamTakingStatusEnum::COMPLETE->value) {
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
                // ->where('online_exams.status', ExamStatusEnum::ACTIVE->value) // ACTIVE
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
                $studentOnlinExam = StudentOnlineExam::where('online_exam_id', $request->id)->first();

                if ($exam->datetime <= DatetimeHelper::now() &&
                    intval($exam->status) === ExamStatusEnum::ACTIVE->value
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
                } elseif (intval($exam->status) === ExamStatusEnum::COMPLETE->value) {
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

    public function retrieveOnlineExam1(Request $request)
    {
        Gate::authorize('retrieveOnlineExam', StudentOnlineExamController::class);
        // تستخدم هذه الدالة لارجاع الاختبارات الغير مكتملة فقط
        try {
            $studentonlinExam = StudentOnlineExam::where('online_exam_id', $request->id)->first();
            $realExam = [];

            return $studentonlinExam;

            $isComplete = (intval($studentonlinExam->status) === StudentOnlineExamStatusEnum::COMPLETE->value) ? true : false;

            if (!$isComplete) {
                $realExam = RealExam::find($studentonlinExam->online_exam_id, [
                    'id', 'language as language_name',
                    'datetime', 'duration', 'type as type_name', 'note as special_note', 'course_lecturer_id'
                ]);

                $enumReplacement = [
                    new EnumReplacement('language_name', LanguageEnum::class),
                    new EnumReplacement('type_name', ExamTypeEnum::class),
                ];

                $realExam = ProcessDataHelper::enumsConvertIdToName($realExam, $enumReplacement);
                $jsonData = Storage::disk('local')->get('generalNotes.json'); // get notes from json file
                $general_note = json_decode($jsonData, true);
                $realExam['general_note'] =  $general_note;        //// Done

                $realExam = ExamHelper::getRealExamsScore($realExam);
                $onlineExam = OnlineExam::where('id', $realExam->id)->first(['conduct_method as is_mandatory_question_sequense']);
                $onlineExam->is_mandatory_question_sequense = ($onlineExam->is_mandatory_question_sequense === ExamConductMethodEnum::MANDATORY->value) ? true : false;
                $courselecturer = $realExam->course_lecturer()->first();
                $lecturer =  Employee::where('id', $courselecturer->lecturer_id)->first(['arabic_name as lecturer_name']);
                $departmentCoursePart = $courselecturer->department_course_part()->first();
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

                //*** make unset to : 'department_id', 'course_id', 'college_id', 'course_lecturer_id'
                $departmentCourse = $departmentCourse->toArray();
                unset($departmentCourse['department_id']);
                unset($departmentCourse['course_id']);

                $department = $department->toArray();
                unset($department['college_id']);

                $realExam = $realExam->toArray();
                unset($realExam['course_lecturer_id']);
                $realExam = NullHelper::filter($realExam);
            }

            $realExam =
                $realExam  +
                $onlineExam->toArray() +
                $lecturer->toArray() +
                $coursePart->toArray() +
                $departmentCourse  +
                $department +
                $college->toArray() +
                $course->toArray();

            return ResponseHelper::successWithData($realExam);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveOnlineExamQuestions(Request $request)
    {
        Gate::authorize('retrieveOnlineExamQuestions', StudentOnlineExamController::class);
        try {
            $exam = OnlineExam::findOrFail($request->id);
            
            if ($exam->datetime <= DatetimeHelper::now() &&
                intval($exam->status) === ExamStatusEnum::ACTIVE->value
            ) {
                $studentId = Student::where('user_id', auth()->user()->id)
                    ->first()['id'];

                $studentOnlineExam = StudentOnlineExam::where('online_exam_id', $request->id)
                    ->where('student_id', $studentId)
                    ->first(['status', 'form_id']);
                
                if ($studentOnlineExam) {
                    if ($studentOnlineExam->status === StudentOnlineExamStatusEnum::ACTIVE->value) {
                        $questions = $this->getFormQuestions($studentOnlineExam->form_id);
    
                        $questions = NullHelper::filter($questions);
                        
                        return ResponseHelper::successWithData($questions);        
                    }
                } else {
                    return ResponseHelper::clientError();
                }
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

    private function getFormQuestions($formId)
    {
        $questions = [];
        try {
            $form = Form::findOrFail($formId);
            $realExam = RealExam::findOrFail($form->real_exam_id);
            $language = LanguageEnum::symbolOf($realExam->language);
    
            $formQuestions = $form->form_questions()->get(['question_id', 'combination_id']);

            foreach ($formQuestions as $formQuestion) {
                $question = $formQuestion->question()->first(['id', 'content', 'attachment as attachment_url']);
                if ($formQuestion->combination_id) {
                    // $question['choices'] = ExamHelper::retrieveCombinationChoices($formQuestion->question_id, $formQuestion->combination_id, false, false);
                    $question['choices'] = ExamHelper::retrieveCombinationChoices($formQuestion->question_id, $formQuestion->combination_id, true, false, $language);
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

        try {
            $student = Student::where('user_id', auth()->user()->id)->first();
            $studentOnlineExam = StudentOnlineExam::where('student_id', $student->id)
                ->where('online_exam_id', $request->id)->firstOrFail();
            $studentOnlineExam->update([
                'status' => StudentOnlineExamStatusEnum::COMPLETE->value,
                'end_datetime' => DatetimeHelper::now(),
            ]);
            // StudentOnlineExam::where('student_id', $student->id)
            //     ->where('online_exam_id', $request->id)
            //     ->update([
            //         'status' => StudentOnlineExamStatusEnum::COMPLETE->value,
            //         'end_datetime' => now(),
            //     ]);

            // refresh student and proctor
            OnlineExamListenerHelper::refreshProctor($student->id, $request->exam_id, $studentOnlineExam->form_id);

            return ResponseHelper::success();
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }


    public function saveOnlineExamQuestionAnswer(Request $request)
    {
        Gate::authorize('saveOnlineExamQuestionAnswer', StudentOnlineExamController::class);

        try {
            // يتم تحديث بيانات استخدام السؤال
            // $student = Student::where('user_id', auth()->user()->id)->first();
            // $studentAnswer = StudentAnswer::where('student_id', $student->id)
            //     ->where('form_id', $request->form_id)
            //     ->where('question_id', $request->question_id); // need to get() func

            // $questionType = Question::findOrFail($request->question_id, ['type']);

            //$answerId = null;
            // if ($questionType->type === QuestionTypeEnum::TRUE_FALSE->value) {

            //     $answerId = ($request->is_true === true) ? TrueFalseAnswerEnum::TRUE->value : TrueFalseAnswerEnum::FALSE->value;
            // } else {
            //     $answerId =  $request->choice_id;
            // }

            ////////////**** THIS FOR MAKE LISTENER TO CHECK THE STATUS OF ONLINE EXAM  ****////
            // $studentOnlineExam =  DB::table('student_online_exams')
            //     ->join('students', 'student_online_exams.student_id', '=', 'students.id')
            //     ->join('student_answers', 'students.id', '=', 'student_answers.student_id')
            //     ->select(
            //        'student_online_exams.online_exam_id',
            //        'student_online_exams.start_datetime',
            //        'student_online_exams.end_datetime',
            //        'student_online_exams.status'
            //     )
            //     ->where('student_online_exams.student_id', '=', $student->id)
            //     // ->where('student_answers.student_id', '=', $student->id)
            //     ->first();

            /////////////////////////////////////////////////////

            // StudentAnswer::createOrUpdate([
            //     'student_id' => $student->id,
            //     'form_id' => $request->form_id,
            //     'question_id' => $request->question_id,
            //     'answer' =>  $request->choice_id ?? $request->is_true,  //$answerId,
            //     'answer_duration' => $request->answer_duration ?? null,
            // ]);

            $studentId = Student::where('user_id', auth()->user()->id)->first()['id'];
            $formId = StudentOnlineExam::where('online_exam_id', $request->id)
                ->where('student_id', $studentId)
                ->first(['form_id']);
                $questionType = Question::findOrFail($request->question_id, ['type']);

            $answerId = null;
            if (intval($questionType->type) === QuestionTypeEnum::TRUE_FALSE->value) {
                $answerId = ($request->is_true === true) ? TrueFalseAnswerEnum::TRUE->value : TrueFalseAnswerEnum::FALSE->value;
            } else {
                $answerId =  $request->choice_id;
            }
            StudentAnswer::where('student_id', $studentId)
            ->where('form_id', $formId)
            ->where('question_id', $request->question_id)
            ->update([
                'answer' =>  $answerId
            ]);

            // refresh student and proctor 
            OnlineExamListenerHelper::refreshProctor($studentId, $request->exam_id, $formId);
            
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
                )
                ->where('student_online_exams.student_id', '=', $studentId)
                ->where('student_online_exams.status', '=', StudentOnlineExamStatusEnum::COMPLETE->value)
                ->get();
            $onlineExams = ProcessDataHelper::enumsConvertIdToName($onlineExams, [new EnumReplacement('course_part_name', CoursePartsEnum::class)]);
            foreach ($onlineExams as $onlineExam) {
                $studentResult = $this->retrieveStudentOnlineExamsResult($onlineExam->id, 1, $studentId);
                $onlineExam->score_rate = $studentResult['score_rate'];
                $onlineExam->appreciation = $studentResult['appreciation'];
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
                $question = Question::findOrFail($questionAnswer->questoin_id);
                if (intval($question->type) === QuestionTypeEnum::TRUE_FALSE->value) {
                    if (ExamHelper::checkTrueFalseQuestionAnswer($question, $questionAnswer->answer)) {
                        $StudentScore += $examScores[QuestionTypeEnum::TRUE_FALSE->value];
                    }
                } else {
                    if (ExamHelper::checkChoicesQuestionAnswer($question, $questionAnswer->answer, $questionAnswer->combination_id)) {
                        $StudentScore += $examScores[QuestionTypeEnum::MULTIPLE_CHOICE->value];
                    }
                }
            }

            $scoreRate = $StudentScore / $examScores['totalScore'] * 100;
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
        $examScores = [];
        try {
            $realExam = RealExam::findOrFail($onlineExamId);

            $realExamQuestionTypes = $realExam->real_exam_question_types()
                ->get(['question_type', 'question_score', 'questions_count']);

            $totalScore = 0;
            foreach ($realExamQuestionTypes as $realExamQuestionType) {
                $examScores[intval($realExamQuestionType->question_type)] = $realExamQuestionType->question_score;
                $totalScore += $realExamQuestionType->questions_count * $realExamQuestionType->question_score;
            }

            $examScores['totalScore'] = $totalScore;
            return $examScores;
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

            $exams = DB::table('real_exams as res')
                ->join('online_exams as oes', 'res.id', '=', 'oes.id')
                ->join('course_lecturers as cl', 'res.course_lecturer_id', '=', 'cl.id')
                ->join('department_course_parts as dcp', 'cl.department_course_part_id', '=', 'dcp.id')
                ->join('course_students as cs', 'dcp.department_course_id', '=', 'cs.department_course_id')
                ->join('course_parts as cp', 'dcp.course_part_id', '=', 'cp.id')
                ->join('courses as c', 'cp.course_id', '=', 'c.id')
                ->where('res.exam_type', RealExamTypeEnum::ONLINE->value) // ONLINE
                ->where('res.datetime', '>', DatetimeHelper::now()) // Not-Taken
                ->where('oes.status', ExamStatusEnum::ACTIVE->value) // ACTIVE
                ->where('oes.exam_datetime_notification_datetime', '<=', DatetimeHelper::now()) // VISIBLE
                ->where('cs.student_id', $studentId)
                ->where('cs.status', CourseStudentStatusEnum::ACTIVE->value) // ACTIVE
                ->where('cs.academic_year', '=', date('Y')) // CURRENT YEAR
                ->where('cl.academic_year', '=', date('Y')) // CURRENT YEAR
                ->select(
                    'res.id',
                    'res.datetime',
                    'c.arabic_name as course_name',
                    'cp.part_id as course_part_name'
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
    /** for rules
     * retrieveOnlineExams attributes {status id}
     * saveOnlineExamQuestionAnswer attributes {exam id, question id, answer}
     *
     */
}
