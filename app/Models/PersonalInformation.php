<?php

namespace App\Models;

use App\Traits\Models\PersonalInformationRelations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonalInformation extends Model
{
    use HasFactory, PersonalInformationRelations;

    protected $table = 'personal_information';

    protected $guarded = ['id'];

    protected $casts = [
        'date_of_birth' => 'date',
    ];
}
