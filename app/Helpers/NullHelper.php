<?php

namespace App\Helpers;

use App\Models\User;
use App\Enums\RoleEnum;
use App\Enums\GenderEnum;
use App\Enums\JobTypeEnum;
use Illuminate\Support\Str;
use App\Enums\OwnerTypeEnum;
use App\Helpers\ImageHelper;
use Illuminate\Http\Request;
use App\Enums\UserStatusEnum;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Notifications\EmaiVerificationNotification;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class NullHelper
{
    public static function filter($value)
    {
        return $value->map(function ($item) {
            $itemArray = $item->toArray();
            
            foreach ($itemArray as $key => $value) {
                if (is_null($value)) {
                    unset($itemArray[$key]);
                }
            }
        
            return $itemArray;
        });
    }
}
