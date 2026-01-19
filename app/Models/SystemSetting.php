<?php

namespace App\Models;

use App\Traits\Models\SystemSettingMethods;
use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    use SystemSettingMethods;

    protected $guarded = ['id'];
}
