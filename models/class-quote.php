<?php

namespace MoneyManager\Models;

defined( 'ABSPATH' ) || die( 'No direct script access allowed.' );

/**
 * Class Quote
 * @package MoneyManager\Models
 */
class Quote extends Base
{
    protected static $table = 'money_manager_quotes';

    protected static $fillable = [
        'currency',
        'date',
        'value',
        'created_by',
    ];

    protected static $hidden = [
        'created_at',
        'updated_at',
        'created_by',
    ];

    protected static $casts = [
        'value' => 'double',
    ];
}