<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Helpers\ValidateHelper;

class TopicPolicy
{
    private static $validRoles = [
        RoleEnum::SYSTEM_ADMINISTRATOR,
        RoleEnum::DATA_ENTRY,
        RoleEnum::QUESTION_ENTRY
    ];

    public function addTopic(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }

    public function modifyTopic(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }

    public function deleteTopic(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
    
    public function retrieveTopic(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
    
    public function retrieveTopicDescription(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
    
    public function retrieveTopics(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
    
    public function retrieveAvailableTopics(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
}
