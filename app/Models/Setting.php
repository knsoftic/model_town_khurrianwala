<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;

#[Table('website_settings')]
#[Fillable(['group', 'key', 'value', 'type'])]
class Setting extends Model {}
