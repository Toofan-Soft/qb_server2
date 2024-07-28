<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Helpers\ValidateHelper;

class DepartmentPolicy
{
    public function addDepartment(): bool
    {
        return ValidateHelper::validatePolicy([
            RoleEnum::SYSTEM_ADMINISTRATOR->value,
            RoleEnum::DATA_ENTRY->value
        ]);
    }

    public function modifyDepartment(): bool
    {
        return ValidateHelper::validatePolicy([
            RoleEnum::SYSTEM_ADMINISTRATOR->value,
            RoleEnum::DATA_ENTRY->value
        ]);
    }

    public function deleteDepartment(): bool
    {
        return ValidateHelper::validatePolicy([
            RoleEnum::SYSTEM_ADMINISTRATOR->value,
            RoleEnum::DATA_ENTRY->value
        ]);
    }

    public function retrieveEditableDepartment(): bool
    {
        return ValidateHelper::validatePolicy([
            RoleEnum::SYSTEM_ADMINISTRATOR->value,
            RoleEnum::DATA_ENTRY->value
        ]);
    }

    public function retrieveDepartment(): bool
    {
        return ValidateHelper::validateUser();
    }
    
    public function retrieveDepartments(): bool
    {
        return ValidateHelper::validateUser();
    }
    
    public function retrieveBasicDepartmentsInfo(): bool
    {
        return ValidateHelper::validateUser();
    }
}
