<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Models\Guest;
use App\Enums\RoleEnum;
use App\Models\UserRole;
use App\Enums\GenderEnum;
use App\Enums\JobTypeEnum;
use App\Helpers\AddHelper;
use Illuminate\Support\Str;
use App\Enums\OwnerTypeEnum;
use App\Helpers\ImageHelper;
use Illuminate\Http\Request;
use App\Enums\UserStatusEnum;
use App\Enums\QualificationEnum;
use App\Helpers\LoginHelper;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Support\Facades\Storage;
use App\Notifications\LoginNotification;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\Auth\UserUpdateRequest;
use App\Notifications\EmaiVerificationNotification;

class UserController extends Controller
{

    // this only for test
    public function index()
    {
        //    $users = UserResource::make(User::all()); // we use make for one object
        //  $users = UserResource::collection(User::all()); // we use collection for multi objects

        return response()->json(['data' => User::all(), 'error' => ''], 200);
    }


    public function register(Request $request)
    {
        // $userRoles = Auth::user()->user_roles();
        // $userHasSystemAdminRole = $userRoles->contains(function ($role) {
        //     return $role->role_id === RoleEnum::SYSTEM_ADMINISTRATOR->value;
        // });

        if ($request->owner_type) {
            // for admin when add new employee or student
            return AddHelper::createNewUser($request, User::class, $this->rules($request), $request->owner_type);
        } else {
            // for user guest
            return AddHelper::createNewUser($request, User::class, $this->rules($request));
        }


        // $imagePath = null;
        // $input = $request->all();
        // $validation = Validator::make($input, []);
        // if ($validation->fails()) {
        //     return response()->json(['error_message' =>  $validation->errors()->first()], 422);
        // }
        // try {
        //     // Start a database transaction
        //     DB::beginTransaction();
        //     $user = User::create([
        //         'name' => $request->name,
        //         'password' => bcrypt($request->password),
        //         'status' => UserStatusEnum::ACTIVATED->value,
        //         'account_owner_type' => OwnerTypeEnum::GUEST->value,
        //     ]);

        //     if ($request->hasFile('image')) {
        //         $imagePath = ImageHelper::uploadImage($request->file('image'));
        //     }

        //     $guest =  Guest::create([
        //         'user_id' => $user->id,
        //         'name' => 'nasser',
        //         'email' => $request->email,
        //         'phone'  => null,
        //         'gender'  => $request->gender,
        //         'image'  => $imagePath,
        //         // Set other guest details from the request
        //     ]);
        //     $urole = UserRole::create([
        //         'user_account_id' => $user->id,
        //         // 'role_id' => RoleEnum::GUEST->value,
        //     ]);

        //     // $user->user_account_roles()->create([RoleEnum::GUEST->value]);

        //     $token =  $user->createToken('quesionbanklaravelapi')->accessToken;  // هنا يجب ان يكون ال salt قوي جدا
        //     DB::commit();
        // } catch (\Exception $e) {
        //     // An error occurred, rollback the transaction
        //     DB::rollback();
        //     throw $e;
        // }
        // //$user->notify(new EmaiVerificationNotification()); // for verification email when register
        // return response()->json(null, 200);
        // //  ->json(['token'=> $token],200);
    }


    public function login(Request $request)
    {
        return LoginHelper::userLogin($request, User::class);
        // $input = $request->all();
        // $validation = Validator::make($input, [
        //     'email' => 'required|email',
        //     'password' => 'required|min:8'
        // ]);

        // if ($validation->fails()) {
        //     return response()->json(['error_message' =>  $validation->errors()->first()], 422);
        // }


        // if (auth()->attempt($input)) {
        //         $user = Auth::user();
        //         $token =  auth()->user()->createToken('quesionbanklaravelapi')->accessToken;
        //         // $user->notify(new LoginNotification()); // for notify user by email
        //         return response()->json(['token' => $token], 200);
        //     } else {
        //             return response()->json(['error' => 'Unauthorised'], 401);
        //     }



                // $email = $request->input('email');
                // $guest = Guest::where('email', '=', $email)->first();
                // $user = User::where('id', '=', $guest->user_account_id)->first();
                // if ($guest && Hash::check($request->input('password'), $user->password)) {
                //     // Credentials match - successful login
                //     //    $ujser = Auth::user();
                //     $token =  $user->createToken('quesionbanklaravelapi')->accessToken;
                //     return response()->json(['token' => $token], 200);
                // } else {
                //     return response()->json(['error' => 'Unauthorised'], 401);
                // }
            }


    public function update(UserUpdateRequest $request)
    {
        $user = $request->user();
        $validateData = $request->validated();
        $user->update($validateData);
        $user = $user->referesh();

        $success['success'] = true;
        $success['user'] = $user;
        return response()->json($success, 200);
    }


    public function userInfo()
    {
        $user =  auth()->user();
        return response()->json(['user' => $user], 200);
    }



    public function updateRoles(Request $request, User $user)
    {
        $request->validate([
            'roles' => 'required|array',
            'roles.*' => 'integer|exists:user_account_roles',
        ]);

        $user->roles()->sync($request->roles);

        return response()->json(['message' => 'User roles updated successfully']);
    }

    public function rules(Request $request): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => ($request->owner_type) ? 'nullable' : 'required|min:8',
             'status' =>  ($request->status)  ?  new Enum(UserStatusEnum::class) : 'nullable',
             'owner_type' => ($request->owner_type ) ? new Enum(OwnerTypeEnum::class):'nullable',
             'gender'  => ($request->gender)  ? new Enum(GenderEnum::class) : 'nullable' ,
            'image_url' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'phone' => 'nullable|string|unique:guests,phone',
        ];
        $studentRules = [
            'academic_id' => 'required|integer',
            'arabic_name' => 'required|string|max:255',
            'english_name' => 'required|string|max:255',
            'birthdate' => 'nullable|date',
        ];
        $employeeRules = [
            'arabic_name' => 'required|string|max:255',
            'english_name' => 'required|string|max:255',
            'job_type' => new Enum(JobTypeEnum::class),
            'qualification' => new Enum(QualificationEnum::class),
            'specialization' => 'nullable|string',
        ];
        if ($request->owner_type === OwnerTypeEnum::STUDENT->value) {
            $rules = array_merge($rules, $studentRules);
        } elseif ($request->owner_type && $request->owner_type !== OwnerTypeEnum::GUEST->value && $request->owner_type !== OwnerTypeEnum::STUDENT->value) {
            $rules = array_merge($rules, $employeeRules);
        }
        if ($request->method() === 'PUT' || $request->method() === 'PATCH' ) {
            $rules = array_filter($rules, function ($attribute) use ($request) {
                // Ensure strict type comparison for security
                return $request->has($attribute);
            });
        }
        return $rules;
    }
}
