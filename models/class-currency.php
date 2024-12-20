<?php

namespace MoneyManager\Models;

defined( 'ABSPATH' ) || die( 'No direct script access allowed.' );

/**
 * Class Currency
 * @package MoneyManager\Models
 */
class Currency extends Base
{
    protected static $table = 'money_manager_currencies';

    protected static $fillable = [
        'code',
        'color',
        'is_base',
        'default_quote',
        'created_by',
    ];

    protected static $hidden = [
        'created_at',
        'updated_at',
        'created_by',
    ];

    protected static $casts = [
        'is_base' => 'bool',
        'default_quote' => 'double',
    ];
}