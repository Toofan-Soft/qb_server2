<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Guest;
use Ichtrojan\Otp\Otp;
use App\Models\Student;
use App\Models\Employee;
use App\Enums\GenderEnum;
use App\Events\FireEvent;
use App\Enums\JobTypeEnum;
use App\Enums\LanguageEnum;
use App\Helpers\NullHelper;
use App\Helpers\UserHelper;
use App\Enums\OwnerTypeEnum;
use Illuminate\Http\Request;
use App\Helpers\ResponseHelper;
use App\Enums\QualificationEnum;
use App\Helpers\ColumnReplacement;
use App\Helpers\DatetimeHelper;
use App\Helpers\EnumReplacement;
use App\Helpers\ProcessDataHelper;
use App\Models\UserRole;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Support\Facades\Validator;
use App\Notifications\EmaiVerificationNotification;
use Symfony\Component\Console\Helper\ProcessHelper;
use App\Notifications\ResetPasswordNotificationVerification;

class UserController extends Controller
{
    private $otp;

    public function __construct()
    {
        $this->otp = new Otp();
    }

    public function verifyAccount(Request $request)
    {
        try {
            $otp2 = $this->otp->validate($request->code);

            if (!$otp2->status) {
                return ResponseHelper::clientError();
            }

            $user = User::where('email', $otp2->email)->first();
            $user->update(['email_verified_at' => now()]);

            return ResponseHelper::success();
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function login(Request $request)
    {
        try {
            $input = $request->only('email', 'password');
            $validation = Validator::make($input, [
                'email' => 'required|email',
                'password' => 'required|min:8'
            ]);

            if ($validation->fails()) {
                return ResponseHelper::clientError();
            }

            if (auth()->attempt($input)) {
                $user = Auth::user();

                if ($user->email_verified_at !== false) {
                    $token =  $user->createToken('quesionbanklaravelapi')->accessToken;
                    $rolesIds = UserRole::where('user_id', $user->id)
                        ->get()
                        ->map(function ($role) {
                            return $role->role_id;
                        });

                    return [
                        "uid" => $user->id,
                        "user_type_id" => $user->owner_type,
                        "roles_ids" => $rolesIds,
                        "language_id" => $user->language,
                        "token" => $token
                    ];
                    return ResponseHelper::successWithTokenAndUserType($token, $user->owner_type);
                } else {
                    return ResponseHelper::clientError();
                }
            } else {
                return ResponseHelper::clientError();
            }
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function logout()
    {
        $user = Auth::guard('api')->user();
        if ($user) {
            $token = $user->token();
            $token->revoke();
            return response()->json(['message' => 'Successfully logged out'], 200);
        }

        return response()->json(['message' => 'Unable to logout'], 400);

        // try {
        //     Auth::logout();
        //     // if (Auth::check()) {
        //     //     return ResponseHelper::serverError();
        //     // } else {
        //     //     return ResponseHelper::success();
        //     // }
        // } catch (\Exception $e) {
        //     return ResponseHelper::serverError();
        // }
    }

    public function retrieveProfile()
    {
        // try {
            $user = auth()->user();
            $owner = null;
            $enumReplacements = [
                new EnumReplacement('gender_name', GenderEnum::class)
            ];
            $columRemplacements = [
                new ColumnReplacement('email', 'email', User::class)
            ];

            if (intval($user->owner_type) === OwnerTypeEnum::GUEST->value) {
                $attributes = ['name', 'user_id as email', 'phone', 'gender as gender_name', 'image_url'];
                $owner = Guest::where('user_id', $user->id)->first($attributes);
            } elseif (intval($user->owner_type) === OwnerTypeEnum::STUDENT->value) {
                $attributes = ['arabic_name', 'english_name', 'user_id as email', 'phone', 'birthdate', 'gender as gender_name', 'image_url'];
                $owner = Student::where('user_id', $user->id)->first($attributes);
            } else {
                $attributes = [
                    'arabic_name', 'english_name', 'user_id as email', 'phone', 'gender as gender_name', 'image_url', 'specialization',
                    'qualification as qualification_name', 'job_type as job_type_name'
                ];
                $owner = Employee::where('user_id', $user->id)->first($attributes);
                array_push($enumReplacements, new EnumReplacement('qualification_name', QualificationEnum::class));
                array_push($enumReplacements, new EnumReplacement('job_type_name', JobTypeEnum::class));
            }

            $owner = ProcessDataHelper::enumsConvertIdToName($owner, $enumReplacements);
            $owner = ProcessDataHelper::columnConvertIdToName($owner, $columRemplacements);
            $owner = NullHelper::filter($owner);

            return ResponseHelper::successWithData($owner);
        // } catch (\Exception $e) {
        //     return ResponseHelper::serverError();
        // }
    }

    public function changePassword(Request $request)
    {
        try {
            $user = User::where('email', auth()->user()->email)->first();
            if (Hash::check($request->old_password, $user->password)) {
                $validator = Validator::make($request->all(), ['new_password' => 'required|min:8']);
                if ($validator->fails()) {
                    return ResponseHelper::clientError(401);
                }
                $user->update([
                    'password' => bcrypt($request->new_password),
                ]);
            } else {
                return ResponseHelper::clientError(401);
            }
            return ResponseHelper::success();
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function requestAccountReovery(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), ['email' => 'required|email']);
            if ($validator->fails()) {
                return ResponseHelper::clientError(401);
            }
            $input = $request->only('email');
            $user = User::where('email', $input)->first();
            if ($user) {
                $user->notify(new ResetPasswordNotificationVerification());
                return ResponseHelper::success();
            } else {
                return ResponseHelper::clientError(401);
            }
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }
    public function verifyAccountAfterRecvery(Request $request)
    {
        try {
            $otp2 = $this->otp->validate($request->code);
            if (!$otp2->status) {
                return ResponseHelper::clientError(401);
            }
            return ResponseHelper::success();
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function changePasswordAfterAccountReovery(Request $request)
    {
        try {
            $user = Auth::user();
            $user = User::where('email', $user->email)->first();
            $user->update(['password' => bcrypt($request->new_password)]);
            return ResponseHelper::success();
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function resendCode(Request $request)
    {
        try {
            $generatedToken = self::generateAlphanumericToken(8);
            // $user = auth()->user();
            $user = User::where('email', auth()->user()->email)->first();
            $user->notify(new EmaiVerificationNotification($generatedToken));
            return ResponseHelper::success();
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function changeLanguage(Request $request)
    {
        try {
            $user = User::where('email', auth()->user()->email)->first();
            $validator = Validator::make($request->all(), ['language_id' => ['required', new Enum(LanguageEnum::class)]]);
            if ($validator->fails()) {
                return ResponseHelper::clientError(401);
            } else {
                $user->update([
                    'language' => $request->language_id
                ]);
                return ResponseHelper::success();
            }
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }
    private static function generateAlphanumericToken(int $length = 8): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        return substr(str_shuffle($characters), 0, $length);
    }
}
