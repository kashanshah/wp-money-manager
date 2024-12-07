<?php

namespace MoneyManager\Models;

defined( 'ABSPATH' ) || die( 'No direct script access allowed.' );

/**
 * Class Notification
 * @package MoneyManager\Models
 */
class Notification extends Base
{
    protected static $table = 'money_manager_notifications';

    protected static $fillable = [
        'recurring_transaction_id',
        'method',
        'enabled',
        'to',
        'subject',
        'message',
        'schedule',
        'created_by',
    ];

    protected static $hidden = [
        'next_date',
        'sent_at',
        'created_at',
        'updated_at',
        'created_by',
    ];

    protected static $casts = [
        'recurring_transaction_id' => 'int',
        'enabled' => 'bool',
        'schedule' => 'json',
    ];
}