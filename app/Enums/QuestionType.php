<?php

namespace App\Enums;

enum QuestionType: string
{
    case MCQ = 'mcq';
    case Short = 'short';
    case TrueFalse = 'true_false';
}
