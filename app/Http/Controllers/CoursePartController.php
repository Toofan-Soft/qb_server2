<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Helpers\AddHelper;
use App\Helpers\GetHelper;
use App\Models\CoursePart;
use Illuminate\Http\Request;
use App\Helpers\DeleteHelper;
use App\Helpers\ModifyHelper;
use App\Enums\CoursePartsEnum;
use App\Helpers\EnumReplacement;

class CoursePartController extends Controller
{

     // 	add course parts (course id, [part id, description?])
    // 	modify course part (course id, part id, status id?, description?) : {}
    // 	delete course part (course id, part id) : {}
    // 	retrieve course parts (id) : { [id, name, status id, description?] }
    public function addCoursePart(Request $request)
    {
        return AddHelper::addModel($request, Course::class,  $this->rules($request), 'course_parts', $request->course_id);
    }

    public function modifyCoursePart (Request $request, CoursePart $coursePart)
    {
        return ModifyHelper::modifyModel($request, $coursePart,  $this->rules($request));
    }


    public function deleteCoursePart (CoursePart $coursePart)
    {
        return DeleteHelper::deleteModel($coursePart);
    }

    public function retrieveCourseParts(Request $request) ///////////////////////////******* */
    {
        $attributes = ['id', 'part_id', 'status','description'];
        $conditionAttribute = ['course_id' => $request->course_id];
        $enumReplacements = [
            new EnumReplacement('part_id', 'part_name', CoursePartsEnum::class),
          //  new EnumReplacement('enum_id_column2_db', 'enum_name_name_2',CourseEnum::class),
          ];

          return GetHelper::retrieveModelsWithEnum(CoursePart::class, $attributes, $conditionAttribute, $enumReplacements);
        // $attributes = ['id', 'part_id', 'status','description'];
        // $conditionAttribute = ['course_id' => $request->course_id];
        // $enumAttributes = ['part_id'  => 'part_name'];
        // return GetHelper::retrieveModelsWithEnum(CoursePart::class, $attributes, $conditionAttribute, $enumAttributes, CoursePartsEnum::class );
    }

}
