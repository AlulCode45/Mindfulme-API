<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserDetail extends Model
{
    use HasUuids, SoftDeletes;

    public $incrementing = false;
    protected $primaryKey = 'uuid';
    protected $keyType = 'string';
    protected $guarded = [''];
    public $timestamps = false;
}
