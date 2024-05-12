<?php

namespace App\Entity;

enum UserStatusEnum : string 
{
    case ACTIVE = 'active';
    case BLOCKED = 'blocked';
}