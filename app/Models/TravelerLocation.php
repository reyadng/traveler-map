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

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('dynamodb.table_prefix') . 'traveler_locations';
    }
}
