<?php

namespace MoneyManager\Models;

defined( 'ABSPATH' ) || die( 'No direct script access allowed.' );

/**
 * Class Account
 * @package MoneyManager\Models
 */
class Account extends Base
{
    protected static $table = 'money_manager_accounts';

    protected static $fillable = [
        'title',
        'type',
        'currency',
        'initial_balance',
        'notes',
        'color',
        'created_by',
    ];

    protected static $hidden = [
        'created_at',
        'updated_at',
        'created_by',
    ];

    protected static $casts = [
    ];
}