<?php

namespace App\Http\Controllers;

use App\Traits\EnumTraits;
use App\Helpers\NullHelper;
use App\Helpers\ImageHelper;
use Illuminate\Http\Request;
use App\Helpers\DeleteHelper;
use App\Models\CourseStudent;
use App\Helpers\LanguageHelper;
use App\Helpers\ResponseHelper;
use App\Helpers\ValidateHelper;
use App\Helpers\EnumReplacement;
use App\Models\DepartmentCourse;
use App\Helpers\EnumReplacement1;
use App\Helpers\ProcessDataHelper;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Enum;
use App\Enums\CourseStudentStatusEnum;

class CourseStudentController extends Controller
{
    public function addCourseStudents(Request $request)
    {
        Gate::authorize('addCourseStudents', CourseStudentController::class);

        if (ValidateHelper::validateData($request, $this->rules($request))) {
            return  ResponseHelper::clientError();
        }
        DB::beginTransaction();
        try {
            return ResponseHelper::success();
            $departmenCourse = DepartmentCourse::findOrFail($request->department_course_id);
            if (count($request->students_ids) === 1) {
                $departmenCourse->course_students()->create([
                    'student_id' => $request->students_ids[0],
                    'status' => CourseStudentStatusEnum::ACTIVE->value,
                    'academic_year' => now()->format('Y'), ///need to ************
                ]);
            } else {
                $studentData = [];
                foreach ($request->students_ids as $student_id) {
                    $studentData[] = [
                        'student_id' => $student_id,
                        'status' => CourseStudentStatusEnum::ACTIVE->value,
                        'academic_year' => now()->format('Y'), ///need to ************
                    ];
                }
                $departmenCourse->course_students()->createMany($studentData);
                // //
                //         foreach ($request->students_ids as $student_id ) {
                //             $departmenCourse->course_students()->create([
                //                 'student_id' => $student_id,
                //                 'status' => CourseStudentStatusEnum::ACTIVE->value,
                //                 'academic_year' => now()->format('Y'), ///need to ************
                //             ]);
                //         }
            }
            DB::commit();
            return ResponseHelper::success();
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseHelper::serverError();
        }
    }

    // public function modifyCourseStudent(Request $request)
    // {
    //     if (ValidateHelper::validateData($request, $this->rules($request))) {
    //         return  ResponseHelper::clientError(401);
    //     }
    //     try {
    //         $courseStudent = CourseStudent::where('department_course_id', '=', $request->department_course_id)
    //             ->where('student_id', '=', $request->student_id);

    //         $courseStudent->update([
    //             'academic_year' => $request->academic_year
    //         ]);

    //         return ResponseHelper::success();
    //     } catch (\Exception $e) {
    //         return ResponseHelper::serverError();
    //     }
    // } this use case will be deleted 

    public function passCourseStudent(Request $request)
    {
        Gate::authorize('passCourseStudent', CourseStudentController::class);

        if (ValidateHelper::validateData($request, $this->rules($request))) {
            return  ResponseHelper::clientError();
        }
        try {
            $courseStudent = CourseStudent::where('department_course_id', '=', $request->department_course_id)
                ->where('student_id', '=', $request->student_id);

            if (intval($courseStudent->first()->status) ===  CourseStudentStatusEnum::ACTIVE->value) {
                $courseStudent->update([
                    'status' => CourseStudentStatusEnum::PASSED->value,
                ]);
                return ResponseHelper::success();
            } else {
                return ResponseHelper::clientError();
                // غير مسموح تمرير الكرس وهو معلق او ممرر
            }
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function suspendCourseStudent(Request $request)
    {
        Gate::authorize('suspendCourseStudent', CourseStudentController::class);

        if (ValidateHelper::validateData($request, $this->rules($request))) {
            return  ResponseHelper::clientError();
        }
        try {
            return ResponseHelper::success();
            $courseStudent = CourseStudent::where('department_course_id', '=', $request->department_course_id)
                ->where('student_id', '=', $request->student_id);

            if (intval($courseStudent->first()->status) ===  CourseStudentStatusEnum::ACTIVE->value) {
                $courseStudent->update([
                    'status' => CourseStudentStatusEnum::SUSPENDED->value,
                ]);
                return ResponseHelper::success();
            } else {
                return ResponseHelper::clientError();
                // غير مسموح تعليق الكرس وهو معلق او مكتمل
            }
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function unsuspendCourseStudent(Request $request)
    {
        Gate::authorize('unsuspendCourseStudent', CourseStudentController::class);

        if (ValidateHelper::validateData($request, $this->rules($request))) {
            return  ResponseHelper::clientError();
        }
        try {
            return ResponseHelper::success();
            $courseStudent = CourseStudent::where('department_course_id', '=', $request->department_course_id)
                ->where('student_id', '=', $request->student_id);

            if (intval($courseStudent->first()->status) ===  CourseStudentStatusEnum::SUSPENDED->value) {
                $courseStudent->update([
                    'status' => CourseStudentStatusEnum::ACTIVE->value,
                    'academic_year' => now()->format('Y'),
                ]);
                return ResponseHelper::success();
            } else {
                return ResponseHelper::clientError();
                // غير مسموح فك تعليق الكرس وهو غير معلق
            }
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function deleteCourseStudent(Request $request)
    {
        Gate::authorize('deleteCourseStudent', CourseStudentController::class);

        // هل هناك قيود لحذف المقرر بناء على حالته الحالية او لا
        try {
            $courseStudent = CourseStudent::where('department_course_id', '=', $request->department_course_id)
                ->where('student_id', '=', $request->student_id);
            if (intval($courseStudent->first()->status) ===  CourseStudentStatusEnum::ACTIVE->value) {
                $courseStudent->delete();
                return ResponseHelper::success();
            } else {
                return ResponseHelper::clientError();
                // غير مسموح حذف الكرس وهو معلق او مكتمل
            }
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveCourseStudents(Request $request)
    {
        Gate::authorize('retrieveCourseStudents', CourseStudentController::class);

        // request : {department_course_id, academic_year, status_id?}
        // return : {}
        try {
            $courseStudents = DB::table('course_students')
                // ->join('course_students', 'department_courses.id', '=', 'course_students.department_course_id')
                ->join('students', 'course_students.student_id', '=', 'students.id')
                ->select(
                    'students.id',
                    'students.academic_id',
                    'students.image_url',
                    LanguageHelper::getNameColumnName('students', 'name')
                    // 'students.arabic_name as name'
                )
                ->Where('course_students.department_course_id', '=', $request->department_course_id)
                ->Where('course_students.academic_year', '=', $request->academic_year)
                ->when(is_null($request->status_id), function ($query) {
                    return  $query->selectRaw('course_students.status as status_name');
                })
                ->when(isset($request->status_id), function ($query) use ($request) {
                    return  $query->Where('course_students.status', '=', $request->status_id);
                })
                ->get();

            if (is_null($request->status_id)) {
                foreach ($courseStudents as $courseStudent) {
                    if (intval($courseStudent->status_name) === CourseStudentStatusEnum::ACTIVE->value) {
                        $courseStudent->is_active = true;
                    } elseif (intval($courseStudent->status_name) === CourseStudentStatusEnum::PASSED->value) {
                        $courseStudent->is_passed = true;
                    }
                    $courseStudent->status_name = EnumTraits::getNameByNumber(intval($courseStudent->status_name), CourseStudentStatusEnum::class);
                }
            } elseif (($request->status_id === CourseStudentStatusEnum::ACTIVE->value) || ($request->status_id === CourseStudentStatusEnum::PASSED->value)) {
                $isWhat = ($request->status_id === CourseStudentStatusEnum::ACTIVE->value) ? 'is_active' : 'is_passed';
                foreach ($courseStudents as $courseStudent) {
                    $courseStudent->isWhat = true;
                }
            }
            $courseStudents = NullHelper::filter($courseStudents);
            return ResponseHelper::successWithData($courseStudents);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveUnlinkCourceStudents(Request $request)
    {
        Gate::authorize('retrieveUnlinkCourceStudents', CourseStudentController::class);
        
        // هذا المتطلب ناقص ، ويتحاج الي ان يتم ايضا التركيز على المستوى الذي يدرس فيه الطالب واستثناء الطلاب الذين في مستويات اقل
        try {
            $department = DepartmentCourse::findOrFail($request->department_course_id, ['department_id']);
            $departmentStudents = []; // كل الطلاب الذين يدرسون في القسم الذي ينتمي اليه مقرر القسم المطلوب
            $departmentCourseStudents = []; // كل الطلاب الذين يدرسون او قد درسو مقرر القسم المطلوب
            $departmentStudents = DB::table('departments')
                ->join('department_courses', 'departments.id', '=', 'department_courses.department_id')
                ->join('course_students', 'department_courses.id', '=', 'course_students.department_course_id')
                ->join('students', 'course_students.student_id', '=', 'students.id')
                ->select('students.id', 'students.academic_id', LanguageHelper::getNameColumnName('students', 'name'), 'students.image_url')
                ->where('departments.id', '=', $department->department_id)
                ->distinct()
                ->get();

            $departmentCourseStudents = CourseStudent::where('department_course_id', '=', $request->department_course_id)
            ->pluck('student_id')->toArray();

            $unlinkCourseStudents = $departmentStudents->whereNotIn('id', $departmentCourseStudents->student_id);
            // $unlinkCourseStudents = $departmentStudents->whereNotIn('id', $departmentCourseStudents);
            $unlinkCourseStudents = NullHelper::filter($unlinkCourseStudents);
            // $unlinkCourseStudents = ImageHelper::addCompleteDomainToMediaUrls($unlinkCourseStudents);

            return ResponseHelper::successWithData($unlinkCourseStudents);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }


        // compare or تقاطع بين الناتجين
        // $department = DepartmentCourse::find($request->department_course_id)->get(['department_id']);
        // $studentQuery = DB::table('departments')
        //     ->join('department_courses', 'departments.id', '=', 'department_courses.department_id')
        //     ->join('course_students', 'department_courses.id', '=', 'course_students.department_course_id')
        //     ->join('students', 'course_students.student_id', '=', 'students.id')
        //     ->select('students.id', 'students.academic_id', 'students.arabic_name as name', 'students.image_url');

        // $allDepartmentStudents = $studentQuery->where('departments.id', '=', $department->department_id)->get();
        // $linkedCourseStudents = DB::table('department_courses')
        //     ->join('course_students', 'department_courses.id', '=', 'course_students.department_course_id')
        //     ->where('department_courses.id', '=', $request->department_course_id)
        //     ->select('course_students.student_id');

        // $unlinkCourseStudents = $allDepartmentStudents->whereNotIn('id', $linkedCourseStudents->pluck('student_id'));
        // return $unlinkCourseStudents;



        // OR
        // Union the queries:
        //  $unionQuery = $departmentStudents->union($departmentCourseStudents);
        // Get the results:
        //  $allStudents = $unionQuery->distinct()->get();

        // OR
        // $unionStudents = Collection::make($departmentStudents)->union($departmentCourseStudents)->all();
    }

    // public function retrieveEditableCourseStudent(Request $request)
    // {
    //     $attributes = ['academic_year'];
    //     try {
    //         $courseStudent = CourseStudent::where('department_course_id', '=', $request->department_course_id)
    //             ->where('student_id', '=', $request->student_id)->get($attributes);
    //         return ResponseHelper::successWithData($courseStudent);
    //     } catch (\Exception $e) {
    //         return ResponseHelper::serverError();
    //     }
    // } // this use case will be deleted 

    public function rules(Request $request): array
    {
        $rules = [
            'department_course_id' => 'required|exists:department_courses,id',
            'student_id' => 'nullable|exists:students,id',
            'students_ids'                => 'required|array|min:1',
            'students_ids.*'              => 'required|exists:students,id',
            // 'status' => ['nullable', new Enum(CourseStudentStatusEnum::class)], // Assuming CourseStudentStatusEnum holds valid values
            'academic_year' => 'nullable|integer',
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
