<?php

namespace App\Enums;

/**
 * Enum representing the source of a logo's data.
 */
enum LogoSource: string
{
    case IMPORT_XLS = 'import_xls'; // Logo imported via CSV/Excel file.
    case USER_UPLOAD = 'user_upload'; // Logo uploaded by user via web interface.
    case USER_ADMIN = 'user_admin'; // Logo uploaded by admin user.
    case UNKNOWN = 'unknown'; // For legacy or uncategorised logos.
}