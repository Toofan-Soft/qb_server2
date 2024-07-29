<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\RealExam;
use App\Enums\GenderEnum;
use App\Enums\LevelsEnum;
use App\Models\OnlineExam;
use App\Enums\ExamTypeEnum;
use App\Enums\SemesterEnum;
use App\Helpers\ExamHelper;
use App\Helpers\NullHelper;
use App\Models\FormQuestion;
use Illuminate\Http\Request;
use App\Models\StudentAnswer;
use App\Enums\CoursePartsEnum;
use App\Helpers\DatetimeHelper;
use App\Helpers\LanguageHelper;
use App\Helpers\ResponseHelper;
use App\Helpers\ValidateHelper;
use App\Helpers\EnumReplacement;
use App\Helpers\OnlinExamHelper;
use App\Models\StudentOnlineExam;
use App\Helpers\ProcessDataHelper;
use Illuminate\Support\Facades\DB;
use App\Enums\OnlineExamStatusEnum;
use App\Helpers\QuestionUsageHelper;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use App\Enums\CourseStudentStatusEnum;
use Illuminate\Support\Facades\Storage;
use App\Helpers\OnlineExamListenerHelper;
use App\Enums\StudentOnlineExamStatusEnum;

class ProctorOnlinExamController extends Controller
{
    public function retrieveOnlineExams()
    {
        Gate::authorize('retrieveOnlineExams', ProctorOnlinExamController::class);

        try {
            $proctor = Employee::where('user_id', auth()->user()->id)->first();
            // return $proctor;

            $onlineExams = DB::table('online_exams')
                ->join('real_exams', 'online_exams.id', '=', 'real_exams.id')
                ->join('course_lecturers', 'real_exams.course_lecturer_id', '=', 'course_lecturers.id')
                ->join('department_course_parts', 'course_lecturers.department_course_part_id', '=', 'department_course_parts.id')
                ->join('department_courses', 'department_course_parts.department_course_id', '=', 'department_courses.id')
                ->join('courses', 'department_courses.course_id', '=', 'courses.id')
                ->join('course_parts', 'department_course_parts.course_part_id', '=', 'course_parts.id')
                ->select(
                    'real_exams.id',
                    'real_exams.datetime',
                    'courses.arabic_name as course_name',
                    'course_parts.part_id as course_part_name'
                )
                ->where('online_exams.proctor_id', '=', $proctor->id)
                ->where('online_exams.status', '=', OnlineExamStatusEnum::ACTIVE->value)
                ->get()
                ->map(function ($exam) {
                    $exam->datetime = DatetimeHelper::convertDateTimeToLong($exam->datetime);
                    return $exam;
                });

            $onlineExams = ProcessDataHelper::enumsConvertIdToName($onlineExams, [new EnumReplacement('course_part_name', CoursePartsEnum::class)]);

            return ResponseHelper::successWithData($onlineExams);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveOnlineExam(Request $request)
    {
        Gate::authorize('retrieveOnlineExam', ProctorOnlinExamController::class);
        if (ValidateHelper::validateData($request, [
            'id' => 'required|integer'
        ])) {
            return  ResponseHelper::clientError();
        }
        try {
            $realExam = RealExam::findOrFail($request->id, [
                'id', 'datetime', 'duration',
                'type as type_name', 'note as special_note', 'course_lecturer_id'
            ]);

            $realExam = ProcessDataHelper::enumsConvertIdToName($realExam, [
                new EnumReplacement('type_name', ExamTypeEnum::class)
            ]);

            $jsonData = Storage::disk('local')->get('generalNotes.json'); // get notes from json file
            $general_note = json_decode($jsonData, true);
            $realExam['general_note'] =  $general_note;        //// Done

            $realExam = ExamHelper::getRealExamsScore($realExam);

            $onlineExamStatus = OnlineExam::findOrFail($request->id)
                ->first(['status'])['status'];

            if (intval($onlineExamStatus) === OnlineExamStatusEnum::COMPLETE->value) {
                $realExam->is_complete = true;
            } else {
                $realExam->is_takable = DatetimeHelper::convertLongToDateTime($realExam->datetime) <= DatetimeHelper::now();
            }

            $courselecturer = $realExam->course_lecturer()->first();
            $lecturer =  Employee::where('id', $courselecturer->lecturer_id)->first([LanguageHelper::getNameColumnName(null, 'lecturer_name')]);
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

            $department = $departmentCourse->department()->first([LanguageHelper::getNameColumnName(null, 'department_name'), 'college_id']);
            $college = $department->college()->first([LanguageHelper::getNameColumnName(null, 'college_name')]);
            $course = $departmentCourse->course()->first([LanguageHelper::getNameColumnName(null, 'course_name')]);

            //*** make unset to : 'department_id', 'course_id', 'college_id', 'course_lecturer_id' , 'id'
            $departmentCourse = $departmentCourse->toArray();
            unset($departmentCourse['department_id']);
            unset($departmentCourse['course_id']);

            $department = $department->toArray();
            unset($department['college_id']);

            $realExam = $realExam->toArray();
            unset($realExam['course_lecturer_id']);
            unset($realExam['id']);

            $realExam =
                $realExam  +
                $lecturer->toArray() +
                $coursePart->toArray() +
                $departmentCourse  +
                $department +
                $college->toArray() +
                $course->toArray();

            $realExam['datetime'] = DatetimeHelper::convertDateTimeToLong($realExam['datetime']);

            $realExam = NullHelper::filter($realExam);

            return ResponseHelper::successWithData($realExam);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveOnlineExamStudents(Request $request)
    {
        Gate::authorize('retrieveOnlineExamStudents', ProctorOnlinExamController::class);
        if (ValidateHelper::validateData($request, [
            'exam_id' => 'required|integer'
        ])) {
            return  ResponseHelper::clientError();
        }
        try {
            $results = DB::table('real_exams')
                ->select('students.id', 'students.academic_id', LanguageHelper::getNameColumnName('students', 'name'), 'students.gender as gender_name', 'students.image_url')
                ->join('online_exams', 'real_exams.id', '=', 'online_exams.id')
                ->join('course_lecturers', 'real_exams.course_lecturer_id', '=', 'course_lecturers.id')
                ->join('department_course_parts', 'course_lecturers.department_course_part_id', '=', 'department_course_parts.id')
                ->join('department_courses', 'department_course_parts.department_course_id', '=', 'department_courses.id')
                ->join('course_students', 'department_courses.id', '=', 'course_students.department_course_id')
                ->join('students', 'course_students.student_id', '=', 'students.id')
                ->where('real_exams.id', $request->exam_id)
                // ->where('real_exams.exam_type', RealExamTypeEnum::ONLINE->value) // ONLINE
                // ->where('real_exams.datetime', '>', now()) // Not-Taken
                ->where('real_exams.datetime', '<=', DatetimeHelper::now()) // ACTIVE-NOW
                ->where('online_exams.status', OnlineExamStatusEnum::ACTIVE->value) // ACTIVE
                ->where('course_students.status', CourseStudentStatusEnum::ACTIVE->value) // ACTIVE
                ->where('course_students.academic_year', '=', date('Y')) // CURRENT YEAR
                // ->where('course_lecturers.academic_year', '=', date('Y')) // CURRENT YEAR
                ->get();

            $results->transform(function ($item) use ($request) {
                $soe = StudentOnlineExam::where('student_id', $item->id)
                    ->where('online_exam_id', $request->exam_id)->first();

                if ($soe) {
                    $item->status_name = $soe->status;
                    $item->is_suspended = intval($soe->status) === StudentOnlineExamStatusEnum::SUSPENDED->value;
                    $item->start_time = DatetimeHelper::convertDatetimeToTimeToLong($soe->start_datetime);
                    $item->end_time = DatetimeHelper::convertDatetimeToTimeToLong($soe->end_datetime);
                    // $item->start_time = $soe->start_datetime;
                    // $item->end_time = $soe->end_datetime;
                    // $item->form_name = $soe->form_id;
                    $item->form_name = OnlinExamHelper::getStudentFormName($request->exam_id, $soe->form_id);

                    $item->answered_questions_count = StudentAnswer::where('student_id', $item->id)
                        ->where('form_id', $soe->form_id)
                        ->where('answer', '!=', null)->count();

                    $item = ProcessDataHelper::enumsConvertIdToName($item, [new EnumReplacement('status_name', StudentOnlineExamStatusEnum::class)]);
                }

                return $item;
            });
            $results = ProcessDataHelper::enumsConvertIdToName($results, [new EnumReplacement('gender_name', GenderEnum::class)]);

            $results = NullHelper::filter($results); /////////

            return ResponseHelper::successWithData($results);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function refreshOnlineExamStudents(Request $request)
    {
        // request : exam id, student id
        // return :
        try {
            $results = DB::table('real_exams as res')
                ->select('s.id')
                ->join('online_exams as oes', 'res.id', '=', 'oes.id')
                ->join('course_lecturers as cl', 'res.course_lecturer_id', '=', 'cl.id')
                ->join('department_course_parts as dcp', 'cl.department_course_part_id', '=', 'dcp.id')
                ->join('department_courses as dc', 'dcp.department_course_id', '=', 'dc.id')
                ->join('course_students as cs', 'dc.id', '=', 'cs.department_course_id')
                ->join('students as s', 'cs.student_id', '=', 's.id')
                ->where('res.id', $request->exam_id)
                // ->where('res.exam_type', RealExamTypeEnum::ONLINE->value) // ONLINE
                // ->where('res.datetime', '>', now()) // Not-Taken
                ->where('res.datetime', '<=', DatetimeHelper::now()) // ACTIVE-NOW
                ->where('oes.status', OnlineExamStatusEnum::ACTIVE->value) // ACTIVE
                ->where('cs.status', CourseStudentStatusEnum::ACTIVE->value) // ACTIVE
                ->where('cs.academic_year', '=', date('Y')) // CURRENT YEAR
                // ->where('cl.academic_year', '=', date('Y')) // CURRENT YEAR
                ->get();

            $results->transform(function ($item) {
                $soe = StudentOnlineExam::where('student_id', $item->id)->first();

                if ($soe) {
                    $item->status_name = $soe->status;
                    $item->start_datetime = $soe->start_datetime;
                    $item->end_datetime = $soe->end_datetime;

                    if (intval($soe->status) === StudentOnlineExamStatusEnum::ACTIVE->value) {
                        if ($soe->end_datetime === null) {
                            if ($soe->start_datetime !== null) {
                                $item->is_started = true;
                                // $item->is_finished = false;
                                // $item->is_suspended = false;
                            }
                        } else {
                            // handle error...
                        }
                    } elseif (intval($soe->status) === StudentOnlineExamStatusEnum::SUSPENDED->value) {
                        if ($soe->end_datetime === null) {
                            if ($soe->start_datetime !== null) {
                                $item->is_started = true;
                                // $item->is_finished = false;
                                $item->is_suspended = true;
                            }
                        } else {
                            // handle error...
                        }
                    } elseif (intval($soe->status) === StudentOnlineExamStatusEnum::COMPLETE) {
                        if ($soe->start_datetime !== null || $item->end_datetime !== null) {
                            $item->is_started = true;
                            $item->is_finished = true;
                            // $item->is_suspended = false;
                        } else {
                            // handle error...
                        }
                    } else {
                        // handle error...
                    }

                    // $item->answered_questions_count = StudentAnswer::where('student_id', $item->id)
                    //     ->where('form_id', $soe->form_id)->count();

                    $item = ProcessDataHelper::enumsConvertIdToName($item, [new EnumReplacement('status_name', StudentOnlineExamStatusEnum::class)]);
                }

                return $item;
            });

            //event(new StudentRefreshEvevnt($results));
            return $results;
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function startStudentOnlineExam(Request $request)
    {
        Gate::authorize('startStudentOnlineExam', ProctorOnlinExamController::class);
        if (ValidateHelper::validateData($request, [
            'exam_id' => 'required|integer',
            'student_id' => 'required|integer',
        ])) {
            return  ResponseHelper::clientError();
        }
        DB::beginTransaction();
        try {
            // rule: exam_id, student_id
            $studentOnlineExam = StudentOnlineExam::where('online_exam_id', $request->exam_id)
                ->where('student_id', $request->student_id);
            if (is_null($studentOnlineExam) || isset($studentOnlineExam->first()->status)) {
                DB::rollBack();
                return ResponseHelper::clientError();
            } else {
                $formId = $this->selectStudentFormId($request->exam_id);

                $studentOnlineExam = StudentOnlineExam::create([
                    'online_exam_id' => $request->exam_id,
                    'student_id' => $request->student_id,
                    'start_datetime' => DatetimeHelper::now(),
                    'status' => StudentOnlineExamStatusEnum::ACTIVE->value,
                    'form_id' => $formId
                ]);

                $formQuestions = FormQuestion::where('form_id', $studentOnlineExam->form_id)->get();
                foreach ($formQuestions as $formQuestion) {
                    StudentAnswer::create([
                        'student_id' => $studentOnlineExam->student_id,
                        'question_id' => $formQuestion->question_id,
                        'form_id' => $formQuestion->form_id,
                    ]);
                }
                // refresh proctor and student
                OnlineExamListenerHelper::refreshStudent($request->student_id, $request->exam_id);
                OnlineExamListenerHelper::refreshProctor($request->student_id, $request->exam_id);

                DB::commit();

                return ResponseHelper::success();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseHelper::serverError();
        }
    }

    private function selectStudentFormId($examId)
    {
        try {
            $realExam = RealExam::findOrFail($examId);
            $examFormsIds = $realExam->forms()->pluck('id')->toArray();
            // $examFormsIds = $realExam->forms()->get(['id'])
            //     ->map(function ($form) {
            //         return $form->id;
            //     })
            //     ->toArray();
            if (count($examFormsIds) > 1) {
                $selectedStudentFormId = array_rand($examFormsIds);
                return $examFormsIds[$selectedStudentFormId];
            } else {
                return $examFormsIds[0];
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function suspendStudentOnlineExam(Request $request)
    {
        Gate::authorize('suspendStudentOnlineExam', ProctorOnlinExamController::class);
        if (ValidateHelper::validateData($request, [
            'exam_id' => 'required|integer',
            'student_id' => 'required|integer',
        ])) {
            return  ResponseHelper::clientError();
        }
        DB::beginTransaction();
        try {
            // rule : exam_id, student_id
            $studentOnlineExam = StudentOnlineExam::where('online_exam_id', $request->exam_id)
                ->where('student_id', $request->student_id);

            if (intval($studentOnlineExam->first()->status) != StudentOnlineExamStatusEnum::ACTIVE->value) {
                DB::rollBack();
                return ResponseHelper::clientError();
            } else {
                $studentOnlineExam->update([
                    'status' => StudentOnlineExamStatusEnum::SUSPENDED->value,
                ]);

                OnlineExamListenerHelper::refreshStudent($request->student_id, $request->exam_id);
                OnlineExamListenerHelper::refreshProctor($request->student_id, $request->exam_id);
                DB::commit();
                return ResponseHelper::success();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseHelper::serverError();
        }
    }

    public function continueStudentOnlineExam(Request $request)
    {
        Gate::authorize('continueStudentOnlineExam', ProctorOnlinExamController::class);
        if (ValidateHelper::validateData($request, [
            'exam_id' => 'required|integer',
            'student_id' => 'required|integer',
        ])) {
            return  ResponseHelper::clientError();
        }
        DB::beginTransaction();
        try {
            // rule : exam_id, studnet_id
            $studentOnlineExam = StudentOnlineExam::where('online_exam_id', $request->exam_id)
                ->where('student_id', $request->student_id);
            // if ($studentOnlineExam && intval($studentOnlineExam->status) != StudentOnlineExamStatusEnum::SUSPENDED->value) {
            if (intval($studentOnlineExam->first()->status) != StudentOnlineExamStatusEnum::SUSPENDED->value) {
                DB::rollBack();
                return ResponseHelper::clientError();
            } else {
                $studentOnlineExam->update([
                    'status' => StudentOnlineExamStatusEnum::ACTIVE->value,
                ]);

                // refresh studnet and proctor
                OnlineExamListenerHelper::refreshStudent($request->student_id, $request->exam_id);
                OnlineExamListenerHelper::refreshProctor($request->student_id, $request->exam_id);
                DB::commit();
                return ResponseHelper::success();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseHelper::serverError();
        }
    }

    public function finishStudentOnlineExam(Request $request)
    {
        Gate::authorize('finishStudentOnlineExam', ProctorOnlinExamController::class);
        if (ValidateHelper::validateData($request, [
            'exam_id' => 'required|integer',
            'student_id' => 'required|integer',
        ])) {
            return  ResponseHelper::clientError();
        }
        DB::beginTransaction();
        try {
            $studentOnlineExam = StudentOnlineExam::where('online_exam_id', $request->exam_id)
                ->where('student_id', $request->student_id);
            if (
                intval($studentOnlineExam->first()->status) === StudentOnlineExamStatusEnum::COMPLETE->value ||
                intval($studentOnlineExam->first()->status) === StudentOnlineExamStatusEnum::CANCELED->value
            ) {
                DB::rollBack();
                return ResponseHelper::clientError();
            } else {
                $studentOnlineExam->update([
                    'status' => StudentOnlineExamStatusEnum::CANCELED->value,
                    'end_datetime' => DatetimeHelper::now(),
                ]);

                // refresh student and proctor
                OnlineExamListenerHelper::refreshStudent($request->student_id, $request->exam_id);
                OnlineExamListenerHelper::refreshProctor($request->student_id, $request->exam_id);
                DB::commit();
                return ResponseHelper::success();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseHelper::serverError();
        }
    }

    public function finishOnlineExam(Request $request)
    {
        // request {id}
        Gate::authorize('finishOnlineExam', ProctorOnlinExamController::class);
        if (ValidateHelper::validateData($request, [
            'id' => 'required|integer'
        ])) {
            return  ResponseHelper::clientError();
        }
        DB::beginTransaction();
        try {
            $onlineExam = OnlineExam::findOrFail($request->id);
            if (intval($onlineExam->status) != OnlineExamStatusEnum::ACTIVE->value) {
                DB::rollBack();
                return ResponseHelper::clientError();
            }


            $studentOnlineExams = $onlineExam->student_online_exams()->get();

            // return $studentOnlineExams;

            foreach ($studentOnlineExams as $studentOnlineExam) {
                // return $studentOnlineExam->start_datetime;
                if ((intval($studentOnlineExam->status) === StudentOnlineExamStatusEnum::ACTIVE->value)
                    || (intval($studentOnlineExam->status) === StudentOnlineExamStatusEnum::SUSPENDED->value)
                ) {

                    StudentOnlineExam::where('student_id', $studentOnlineExam->student_id)
                        ->where('online_exam_id', $studentOnlineExam->online_exam_id)
                        ->update([
                            'status' => StudentOnlineExamStatusEnum::COMPLETE->value,
                            'end_datetime' => DatetimeHelper::now(),
                        ]);

                    // return $studentOnlineExam;

                    OnlineExamListenerHelper::refreshStudent($studentOnlineExam->student_id, $studentOnlineExam->online_exam_id);
                    // OnlineExamListenerHelper::refreshProctor($studentOnlineExam->student_id, $studentOnlineExam->online_exam_id);

                }
            }

            $onlineExam->update([
                'status' => OnlineExamStatusEnum::COMPLETE->value
            ]);

            QuestionUsageHelper::updateOnlineExamQuestionsUsageAndAnswer($request->id);

            DB::commit();
            return ResponseHelper::success();
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseHelper::serverError();
        }
    }
}
