<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HelperController extends Controller
{


    public function modify($attributes, $model , $deletedAttributes=['id'])
    {
        $dataToUpdate = array_diff_key($attributes, array_flip($deletedAttributes)); // Extract attributes to update (excluding deletedAttributes)
        $modelInstance = $model::findOrFail($attributes['id']);
        $modelInstance->update($dataToUpdate);
        return response()->json([
            'message' => 'Successfully updated attributes: ' . implode(', ', array_keys($dataToUpdate)),
            'data' => $modelInstance->fresh(), // Refresh data after update
        ], 200);
    }

////////FOR GET SPESEFIC ATTRIBUTES OF ANY MODEL ?
// function getAttributes(array $attributes, Model $model)
// {
//     $result = [];
//     foreach ($attributes as $attribute) {
//         $result[$attribute] = $model->$attribute;
//     }
//     return $result;
// }


    // retrieve online exams (part id?, type id?, status id?) :    //    كيف ممكن يكون ال id null
    //{ [id, course name, course part name, datetime, type name, status name, language name] }

    public  function getOnlineExam(){
        $part_id =1;
       $result= DB::table('department_course_parts')
        ->join('course_lecturers', 'department_course_parts.id', '=', 'course_lecturers.department_course_part_id')
        ->join('real_exams', 'course_lecturers.id', '=', 'real_exams.course_lecturer_id')
        ->join('online_exams', 'real_exams.id', '=', 'online_exams.id')
        ->join('department_courses', 'department_course_parts.department_courses_id', '=', 'department_courses.id')
        ->join('courses', 'department_courses.course_id', '=', 'courses.id')
        ->select('online_exams.id', 'courses.arabic_name', 'real_exams.datetime', 'real_exams.type', 'online_exams.status', 'real_exams.language')
        ->where('department_course_parts.id', '=', $part_id)
        ->orWhere('table2.column2', '=', 'value')
        ->get();
    }


    //long query for more tables ?
    //$result =  DB::table('table1')
    // ->join('table2', 'table1.id', '=', 'table2.table1_id')
    // ->join('table3', 'table2.id', '=', 'table3.table2_id')
    // ->join('table4', 'table3.id', '=', 'table4.table3_id')
    // ->join('table5', 'table4.id', '=', 'table5.table4_id')
    // ->select('table1.column1', 'table2.column2', 'table3.column3', 'table4.column4', 'table5.column5')
    // ->where('table1.column1', '=', 'value')
    // ->orWhere('table2.column2', '=', 'value')
    // ->get();
    ////////////////
    // Or another way for long query
    // $results = DB::select('
    // SELECT table1.column1, table2.column2, table3.column3, table4.column4, table5.column5
    // FROM table1
    // JOIN table2 ON table1.id = table2.table1_id
    // JOIN table3 ON table2.id = table3.table2_id
    // JOIN table4 ON table3.id = table4.table3_id
    // JOIN table5 ON table4.id = table5.table4_id
    // WHERE table1.column1 = :value OR table2.column2 = :value',
    // ['value' => 'your_value']
    //);




    //get questions by topic_id
    // function getQuestionsForTopicWithConditions(int $topicId, array $conditions = []): array
    // {
    //     $query = Question::whereHas('topic', function ($query) use ($topicId) {
    //         $query->where('id', $topicId);
    //     });

    //     // Apply additional conditions from the $conditions array
    //     foreach ($conditions as $field => $value) {
    //         $query->whereHas('relatedModel', function ($query) use ($field, $value) {
    //             $query->where($field, $value);
    //         });
    //     }

    //     return $query->with('type:id,name', 'status:id,name')
    //     ->get()
    //         ->map(function ($question) {
    //             return [
    //                 'id' => $question->id,
    //                 'content' => $question->content,
    //                 'type_name' => $question->type ? $question->type->name : null,
    //                 'status_name' => $question->status ? $question->status->name : null,
    //             ];
    //         })
    //         ->toArray();
    // }



    // if i have many to many relationship
    // $user = User::create([
    //     'name' => 'John Doe'
    // ]);

    // $roles = Role::whereIn('name', ['admin', 'user'])->get(); // Retrieve desired roles
    // $user->roles()->attach($roles); // Assign roles to the user
    // $role = Role::create([
    //     'name' => 'editor'
    // ]);
//     $users = User::where('name', 'like', '%Doe')->get(); // Retrieve desired users
//     $role->users()->attach($users); // Assign users to the role

/////////// if i have m:m relationship with some attribute in middle table
// $user->roles()->attach($role->id, ['status' => 'active']);

////////to get
// $usersWithRole = Role::where('name', 'Admin')->first()->users;


// we can make
 // Post::with('category', 'users')->where('id', $id)->orWhere('status', $status)

 //if want spesefic attributes
//  $post = Post::with([
//     'category' => function ($query) {
//         $query->select('id', 'name'); // Select specific attributes from category
//     },
//     'user' => function ($query) {
//         $query->select('id', 'username'); // Select specific attributes from user
//     },
// ])
// ->select('id', 'title') // Select specific attributes from post
// ->where('id', $id)
// ->orWhere('status', $status)
// ->first();

// Or
// $post = Post::with(['category:id,name', 'user:id,username'])
//     ->select('id', 'title')
//     ->where('id', $id)
//     ->orWhere('status', $status)
//     ->first();


// EXAMPLE HOW TO GET COLLEGE WITH ITS DEPARTMENTS , BUT SPESEFIC ATTRIBUTES OF DEPARTMENT
// $college = College::with([
//     'departments' => function ($query) {
//         $query->select('id', 'name'); // Specify desired attributes for departments
//     },
// ])
// ->find($collegeId);

// IF WANT ALL ATTRIBUTES:
// $college = College::with('departments')
//   ->find($collegeId);


// to add element into array:
// $attributes = ['id', 'part_id', 'status', 'description'];
// array_push($attributes, 'additional_attribute');

//to remove element from array:
// $attributes = ['id', 'part_id', 'status', 'description'];
// unset($attributes['description']);

// add element to array(key-value)
//  $myArray['key3'] = 'value3';

//to delete
// unset($myArray['key2']);

// we can use select some in find :
// $college = College::select('id', 'arabic_name')->find($id);
// Or
// $college = College::find($id)->get(['id','arabic_name']);

// for get data of related model by relationship
// $college = College::find($id);
//         $dep = $college->departments()->get();


// if want reolace element in key=>value array?
//   [
//     'id' => 1,
//     'arabic_title' => 'عنوان الفصل العربي 1',
//     'english_title' => 'Chapter 1 English Title',
//     'topics_count' => 5,
//   ]
//   we use
// $array[0]['arabic_title'] = 'عنوان جديد';
// $array[0]['english_title'] = 'New English Title';
// $array[0]['topics_count'] = 10;

//replace element in normal array
// $array = ['element1', 'element2', 'element3'];
// $array[1] = 'new_element2';
// output: ['element1', 'new_element2', 'element3']

}
