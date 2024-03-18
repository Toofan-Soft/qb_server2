<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Enums\StudentStatusEnum;
use App\Enums\StudentTypeEnum;
use Illuminate\Validation\Rules\Enum;
use  Illuminate\Support\Facades\Validator;
use  App\Models\Student;
use \Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
class StudentController extends Controller
{

    public function index(){

        return response()->json(['students'=>Student::all()],200);
    }


    public function create(Request $request,  ){

        $input=$request->all();
        $validation=Validator::make($input,[
            'name' => 'required',
            'number' => 'required',
            'collage' => 'required',
            'status' => ['required', new Enum(StudentStatusEnum::class)],
            'type' => ['required', new Enum(StudentTypeEnum::class)],
        ]);

        if($validation->fails()){
            return response()->json(['error' => $validation->errors() ], 422);
        }
        // dd($request->file('photo'));
        $student = Student::create($request->except('photo'));

        if($request->hasFile('photo')){
            $photo = $request->file('photo');
            $extension = $photo->getClientOriginalExtension();
            $filename = Str::uuid() . $photo->getClientOriginalName();
            $path = public_path('images') . '/' .  $filename ;
            if (!File::exists(public_path('images'))) {
                File::makeDirectory(public_path('images'), 0755, true); // Create with recursive permissions
            }
            $photo->move(public_path('images'), $filename);
            $student->update(['photo' => $path]);
        }
        // Student::create( [
        //     'name' => $request->name,
        //     'number' => $request->number,
        //     'collage' => $request->collage,
        //     'status' => $request->status,
        //     'type' => $request->type,
        // ]);

        return response()->json(['success'=> 'create student successfully .....'],200);
        // هنا نقدر نرجع اكثر من قيمة مش فقط التوكين
    }

    public function studentInfo($id ){
        $student =  Student::find($id);
        return response()->json(['student'=>$student],200);
    }


}
