<?php

namespace MoneyManager\Models;

defined( 'ABSPATH' ) || die( 'No direct script access allowed.' );

/**
 * Class File
 * @package MoneyManager\Models
 */
class File extends Base
{
    protected static $table = 'money_manager_files';

    protected static $fillable = [
        'attachment_id',
        'filename',
        'description',
        'url',
        'created_by',
    ];

    protected static $hidden = [
        'created_at',
        'updated_at',
        'created_by',
    ];

    protected static $casts = [
        'account_id' => 'int',
        'transaction_id' => 'int',
        'attachment_id' => 'int',
    ];
}