<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\College;
use App\Enums\LevelsEnum;
use App\Helpers\AddHelper;
use App\Helpers\GetHelper;
use App\Models\CoursePart;
use App\Models\Department;
use App\Traits\EnumTraits;
use App\Enums\SemesterEnum;
use App\Helpers\NullHelper;
use Illuminate\Http\Request;
use App\Helpers\DeleteHelper;
use App\Helpers\ModifyHelper;
use App\Enums\CoursePartsEnum;
use App\Helpers\ResponseHelper;
use App\Helpers\ValidateHelper;
use App\Helpers\EnumReplacement;
use App\Models\DepartmentCourse;
use App\Helpers\ColumnReplacement;
use App\Helpers\ProcessDataHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Enum;

class DepartmentCourseController extends Controller
{
    public function addDepartmentCourse(Request $request)
    {
        if (ValidateHelper::validateData($request, $this->rules($request))) {
            return  ResponseHelper::clientError(401);
        }
        try {
            $departmentCourse = DepartmentCourse::create([
                'department_id' => $request->department_id,
                'course_id' => $request->course_id,
                'level' => $request->level_id,
                'semester' => $request->semester_id,
            ]);
            return ResponseHelper::successWithData(['id' => $departmentCourse->id]);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function modifyDepartmentCourse(Request $request)
    {
        if (ValidateHelper::validateData($request, $this->rules($request))) {
            return  ResponseHelper::clientError(401);
        }
        try {
            $departmentCourse = DepartmentCourse::findOrFail($request->id);
            $departmentCourse->update([
                'level' => $request->level_id ?? $departmentCourse->level,
                'semester' => $request->semester_id ?? $departmentCourse->semester,
            ]);
            return ResponseHelper::success();
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function deleteDepartmentCourse(Request  $request)
    {
        try {
            $departmentCourse = DepartmentCourse::findOrFail($request->id);
            $departmentCourse->delete();
            // return DeleteHelper::deleteModel($departmentCourse);
            return ResponseHelper::success();
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveDepartmentCourses(Request $request)
    {
        $attributes = ['id', 'course_id as course_name', 'level as level_name', 'semester as semester_name'];
        $conditionAttribute = ['department_id' => $request->department_id];
        $enumReplacements = [
            new EnumReplacement('level_name', LevelsEnum::class),
            new EnumReplacement('semester_name', SemesterEnum::class),
        ];
        $columnReplacement = [
            new ColumnReplacement('course_name', 'arabic_name', Course::class),
        ];
        try {
            $departmentCourses = GetHelper::retrieveModels(DepartmentCourse::class, $attributes, $conditionAttribute, $enumReplacements, $columnReplacement);
            return ResponseHelper::successWithData($departmentCourses);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }

        // // Optional: Return only necessary attributes (using map)
        // return $department->department_courses->map(function ($departmentCourse) {
        //     return [
        //         'id' => $departmentCourse->id,
        //         'course_name' => $departmentCourse->course->arabic_name,
        //         'level_name' => $departmentCourse->level_name,
        //         'semester_name' => $departmentCourse->semester_name,
        //     ];
        // });


    }

    ////// **** NEED TO GOIN BETWEEN MORE THAN ONEW
    public function retrieveCourseDepartments(Request $request)
    {
        try {
            $coursesDepartments =  DB::table('courses')
                ->join('department_courses', 'courses.id', '=', 'department_courses.course_id')
                ->join('departments', 'department_courses.department_id', '=', 'departments.id')
                ->join('colleges', 'departments.college_id', '=', 'colleges.id')
                ->select(
                    'department_courses.id as department_course_id ',
                    'department_courses.level as level_name',
                    'semester as semester_name',
                    'departments.arabic_name as department_name',
                    'colleges.arabic_name as college_name'
                )
                ->where('courses.id', '=', $request->course_id)
                ->get();

            $enumReplacements = [
                new EnumReplacement('level_name', LevelsEnum::class),
                new EnumReplacement('semester_name', SemesterEnum::class),
            ];

            $coursesDepartments = ProcessDataHelper::enumsConvertIdToName($coursesDepartments, $enumReplacements);

            return ResponseHelper::successWithData($coursesDepartments);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }

        //we can optimize to this :
        // $course = Course::with([
        //     'department_courses:id,department_id,level,semester', // Eager load with required attributes
        //     'department_courses.department:id,arabic_name.college:id,arabic_name as college_name', // Nested eager load
        //     'department_courses.level:number,name as level_name',
        //     'department_courses.semester:number,name as semester_name',
        //   ])->find($request->course_id);

        //   // No need to modify $departmentCourses, data is already available through eager loading

        //   // Return only necessary attributes (optional)
        //   return $course->department_courses->map(function ($departmentCourse) {
        //     return [
        //       'id' => $departmentCourse->id,
        //       'department_name' => $departmentCourse->department->arabic_name,
        //       'college_name' => $departmentCourse->department->college->arabic_name,
        //       'level_name' => $departmentCourse->level_name,
        //       'semester_name' => $departmentCourse->semester_name,
        //     ];
        //   });
    }

    ////// **** NEED TO GOIN BETWEEN MORE THAN ONEW

    public function retrieveDepartmentLevelCourses(Request $request)
    {
        try {
            $semesters = DepartmentCourse::where('department_id', $request->department_id)
                ->where('level', $request->level_id)->get(['semester']);
            $departmentCourses = [];

            foreach ($semesters as $semester) {
                $departmentCourse = [
                    'id' => $semester['semester'],
                    'name' =>  EnumTraits::getNameByNumber($semester->semester, SemesterEnum::class)
                ];
                $semestersCourses = DepartmentCourse::where('department_id', $request->department_id)
                    ->where('level', $request->level_id)
                    ->where('semester', $semester->semester)
                    ->get(['id', 'course_id as name']);

                $columnReplacement = [
                    new ColumnReplacement('name', 'arabic_name', Course::class),
                ];

                $semestersCourses = ProcessDataHelper::columnConvertIdToName($semestersCourses, $columnReplacement);
                $departmentCourse['courses'] = $semestersCourses;

                $departmentCourses[] = $departmentCourse;
            }

            return ResponseHelper::successWithData($departmentCourses);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }

        // if we optimized that :
        // $departmentCourses = DepartmentCourse::with([
        //     'semester:number,name as semester_name', // Eager load semester with renamed attribute
        //     'course:id,arabic_name', // Eager load course with arabic_name
        //   ])
        //   ->where('department_id', $request->department_id)
        //   ->where('level', $request->level_id)
        //   ->get();

        //   $semesters = $departmentCourses->groupBy('semester_name')->map(function ($semesterCourses) {
        //     return [
        //       'id' => $semesterCourses->first()->semester_name, // Get semester_name from first course
        //       'name' => $semesterCourses->first()->semester_name, // Same as id (assuming unique names)
        //       'department_courses' => $semesterCourses->map(function ($departmentCourse) {
        //         return [
        //           'id' => $departmentCourse->id,
        //           'name' => $departmentCourse->course->arabic_name,
        //         ];
        //       }),
        //     ];
        //   })->values(); // Remove empty keys from grouped results
        //   return $semesters;
    }

    public function retrieveDepartmentCourse(Request $request)
    {
        try {
            return ResponseHelper::success();
            $departmentCourse = DepartmentCourse::findOrFail($request->id); //updated successfull
            $course = $departmentCourse->course()->first(['arabic_name']);
            $department = $departmentCourse->department()->first(['college_id', 'arabic_name']);
            $college = $department->college()->first(['arabic_name']);

            $departmentCourseParts = $departmentCourse->department_course_parts()->get([
                'id', 'course_part_id as name', 'score', 'lectures_count', 'lecture_duration', 'note'
            ]);

            $departmentCourseParts = NullHelper::filter($departmentCourseParts);

            $departmentCourse = ProcessDataHelper::enumsConvertIdToName($departmentCourse, [
                new EnumReplacement('level', LevelsEnum::class),
                new EnumReplacement('semester', SemesterEnum::class)
            ]);


            $departmentCourseParts = ProcessDataHelper::columnConvertIdToName(
                $departmentCourseParts,
                [
                    new ColumnReplacement('name', 'part_id', CoursePart::class)
                ]
            );

            $departmentCourseParts = ProcessDataHelper::enumsConvertIdToName(
                $departmentCourseParts,
                [
                    new EnumReplacement('name', CoursePartsEnum::class)
                ]
            );

            $data = [
                'college_name' => $college->arabic_name,
                'department_name' => $department->arabic_name,
                'level_name' => $departmentCourse->level,
                'semester_name' => $departmentCourse->semester,
                'course_name' => $course->arabic_name,
                'course_parts' => $departmentCourseParts
            ];

            return ResponseHelper::successWithData($data);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveEditableDepartmentCourse(Request $request)
    {
        $attributes = ['level as level_id', 'semester as semester_id'];
        try {
            $departmentCourse = DepartmentCourse::findOrFail($request->id, $attributes); //updated successfull
            return ResponseHelper::successWithData($departmentCourse);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function rules(Request $request): array
    {
        $rules = [
            'course_id' => 'required|exists:courses,id',
            'department_id' => 'required|exists:departments,id',
            'level_id' => ['required', new Enum(LevelsEnum::class)], // Assuming LevelsEnum holds valid values
            'semester_id' => ['required', new Enum(SemesterEnum::class)], // Assuming SemesterEnum holds valid values
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
