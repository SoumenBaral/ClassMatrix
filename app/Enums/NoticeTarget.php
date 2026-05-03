<?php

namespace App\Enums;

enum NoticeTarget: string
{
    case All = 'all';
    case Students = 'students';
    case Teachers = 'teachers';
    case Parents = 'parents';
    case Class = 'class';
}
