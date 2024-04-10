<?php

namespace App\Models;

use BaoPham\DynamoDb\DynamoDbModel;

class TravelerLocation extends DynamoDbModel
{
    protected $primaryKey = 'id';
    protected $fillable = [
        'id',
        'tg_id',
        'latitude',
        'longitude',
        'country',
        'locality',
        'country_code',
    ];
}
