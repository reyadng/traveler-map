<?php

namespace App\Models;

use BaoPham\DynamoDb\DynamoDbModel;

class Traveler extends DynamoDbModel
{
    protected $primaryKey = 'tg_id';

    protected $fillable = [
        'tg_id',
        'first_name',
        'last_name',
        'username',
        'traveler_location_id',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('dynamodb.table_prefix') . 'travelers';
    }
}
