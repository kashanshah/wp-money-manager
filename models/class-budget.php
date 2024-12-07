<?php

namespace MoneyManager\Models;

defined( 'ABSPATH' ) || die( 'No direct script access allowed.' );

/**
 * Class Budget
 * @package MoneyManager\Models
 */
class Budget extends Base
{
    protected static $table = 'money_manager_budgets';

    protected static $fillable = [
        'date',
        'type',
        'amount',
        'currency',
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