<?php

namespace MoneyManager\Models;

defined( 'ABSPATH' ) || die( 'No direct script access allowed.' );

/**
 * Class Party
 * @package MoneyManager\Models
 */
class Party extends Base
{
    protected static $table = 'money_manager_parties';

    protected static $fillable = [
        'title',
        'default_category_id',
        'color',
        'created_by',
    ];

    protected static $hidden = [
        'created_at',
        'updated_at',
        'created_by',
    ];

    protected static $casts = [
        'default_category_id' => 'int',
    ];
}