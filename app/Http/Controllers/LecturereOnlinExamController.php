<?php

namespace App\Http\Controllers;

use App\Enums\ExamStatusEnum;
use App\Enums\FormConfigurationMethodEnum;
use App\Enums\RealExamTypeEnum;
use Illuminate\Http\Request;
use App\Models\CourseLecturer;
use App\Models\RealExam;

class LecturereOnlinExamController extends Controller
{
    public function addOnlineExam(Request $request)
    {
        $user = User::findOrFail(auth()->user()->id);
        $courseLecturer = CourseLecturer::where('department_course_part_id', $request->department_course_part_id)
        ->where('lecturer_id',$user->employee()->id )
        ->where('academic_year', now()->format('Y'));
        $realExam = $courseLecturer->real_exams()->create([
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
        ]);

         $realExam->online_exam()->create([
            'conduct_method' => $request->conduct_method_id,
            'exam_datetime_notification_datetime' => $request->datetime_notification_datetime,
            'result_notification_datetime'  => $request->result_notification_datetime,
            'proctor_id' => $request->proctor_id ?? null,
            'status' => ExamStatusEnum::ACTIVE->value,
        ]);

        foreach ($request->question_types as $question_type ) {
             $realExam->real_exam_question_types()->create([
                'question_type' => $question_type->type_id,  ///// ensure 
                'questions_count' => $question_type->questions_count,
                'question_score'  => $question_type->question_score,
            ]);
        }

        if($request->form_configuration_method === FormConfigurationMethodEnum::SIMILAR_FORMS->value){
            $realExam->forms()->create();
        }else {
            foreach ($request->forms_count as $form ) {
                $realExam->forms()->create();
            }
        }

        //////////add Topics of exam

        return $realExam->id;

       // return AddHelper::addModel($request, Topic::class,  $this->rules($request), 'questions', $request->topic_id);
    }

    public function modifyRealExam (Request $request)
    {

        $realExam = RealExam::findOrFail($request->id);
        $realExam->update([
            'type' => $request->type_id ?? $realExam->type ,
            'datetime' => $request->datetime ?? $realExam->datetime ,
            'note' => $request->special_note ?? $realExam->note,
            'form_name_method' => $request->form_name_method ?? $realExam->form_name_method,
        ]);

        $onlinExam = $realExam->online_exam();
        $onlinExam->update([
            'conduct_method' =>  $request->conduct_method_id ??  $onlinExam->conduct_method ,
            'exam_datetime_notification_datetime' => $request->datetime_notification_datetime ?? $onlinExam->exam_datetime_notification_datetime ,
            'result_notification_datetime'  => $request->result_notification_datetime ?? $onlinExam->result_notification_datetime,
            'proctor_id' => $request->proctor_id ?? $onlinExam->proctor_id,
        ]);



    }

    public function deleteOnlineExam (Employee $employee)
    {
        return DeleteHelper::deleteModel($employee);
    }

    public function retrieveOnlineExams(Request $request) //////////////////////*********** More condition needed
    {
        $attributes = ['id','datetime','forms_count'];
        $conditionAttribute = [
            'topic_id' => $request->topic_id,
        ];

        $enumReplacements  =[];
        if ($request->status_id && !$request->type_id) {
            array_push($attributes, 'type');
            $conditionAttribute['status'] =  $request->status_id ;
            array_push($enumReplacements,  new EnumReplacement('status', 'status_name', QuestionStatusEnum::class));
        }
        if (!$request->status_id && $request->type_id) {
            array_push($attributes, 'status');
            $conditionAttribute['type'] =  $request->type_id ;
            array_push($enumReplacements,  new EnumReplacement('type', 'type_name', QuestionTypeEnum::class));
        }
        if (!$request->status_id && !$request->type_id) {
            array_push($attributes, 'status');
            array_push($attributes, 'type');
            array_push($enumReplacements,  new EnumReplacement('status', 'status_name', QuestionStatusEnum::class));
            array_push($enumReplacements,  new EnumReplacement('type', 'type_name', QuestionTypeEnum::class));
        }



        $realExam = RealExam::findOrFail($request->id)->get(['language as language_name', 'difficulty_level as defficulty_level_name' ,
        'forms_count','form_configuration_method as form_configuration_method_name', 'form_name_method as form_name_method_id' ,
         'datetime', 'duration', 'type as type_id', 'note as special_note']);
        $onlinExam = $realExam->online_exam()->get(['conduct_method as conduct_method_id','status as status_name','proctor_id','exam_datetime_notification_datetime as datetime_notification_datetime','result_notification_datetime']);
        $departmentCoursePart = $realExam->lecturer_course()->department_course_part();
        $coursePart = $departmentCoursePart->course_part(['part_id as course_part_name']);
        $departmentCourse = $departmentCoursePart->department_course()->get(['level as level_name', 'semester as semester_name']);
        $department = $departmentCourse->department()->get(['arabic_name as department_name']);
        $college = $department->college()->get(['arabic_name as college_name']);
        $course = $departmentCourse->course()->get(['arabic_name as course_name']);


    $result =  DB::table('real_exams')
    ->join('online_exams', 'real_exams.id', '=', 'online_exams.id')
    ->join('course_lucturers', 'real_exams.course_lucturer_id', '=', 'course_lucturers.id')
    ->join('department_course_parts', 'course_lucturers.department_course_part_id', '=', 'department_course_parts.id')
    ->join('department_courses', 'department_course_parts.department_course_id', '=', 'courses.id')
    ->join('departments', 'department_courses.departmen_id', '=', 'departments.id')
    ->join('colleges', 'departments.college_id', '=', 'colleges.id')
    ->join('courses', 'department_courses.course_id', '=', 'courses.id')
    ->join('course_parts', 'department_course_parts.course_part_id', '=', 'course_parts.id')
    ->select(
     'colleges.arabic_name as college_name',
     'departments.arabic_name as department_name',
     'department_courses.level as level_name', 'department_courses.semester as semester_name',
     'courses.arabic_name as course_name',
     'course_parts.part_id as course_part_name',
     'table4.column4', 'table5.column5',
     'table4.column4', 'table5.column5',
     'table4.column4', 'table5.column5',
     'table4.column4', 'table5.column5',
     'table4.column4', 'table5.column5',
     'table4.column4', 'table5.column5',
     )
    ->where('table1.column1', '=', 'value')
    ->orWhere('table2.column2', '=', 'value')
    ->get();
    }


    public function retrieveOnlineExam(Request $request) //////////////////////*********** More condition needed
    {

        $realExam = RealExam::findOrFail($request->id)->get(['language as language_name', 'difficulty_level as defficulty_level_name' ,
        'forms_count','form_configuration_method as form_configuration_method_name', 'form_name_method as form_name_method_id' ,
         'datetime', 'duration', 'type as type_id', 'note as special_note']);
        $onlinExam = $realExam->online_exam()->get(['conduct_method as conduct_method_id','status as status_name','proctor_id','exam_datetime_notification_datetime as datetime_notification_datetime','result_notification_datetime']);
        $departmentCoursePart = $realExam->lecturer_course()->department_course_part();
        $coursePart = $departmentCoursePart->course_part(['part_id as course_part_name']);
        $departmentCourse = $departmentCoursePart->department_course()->get(['level as level_name', 'semester as semester_name']);
        $department = $departmentCourse->department()->get(['arabic_name as department_name']);
        $college = $department->college()->get(['arabic_name as college_name']);
        $course = $departmentCourse->course()->get(['arabic_name as course_name']);

        $questionTypes = $realExam->real_exam_question_types()->get(['question_type as type_name','questions_count','question_score']);

    }
}
