<?php

namespace App\Entity;

enum CategoriesEnum : string 
{
    case BOOKS = 'Books';
    case SIGNS = 'Signs';
    case SILVERWARE = 'Silverware';
    case OTHER = 'Other';
}