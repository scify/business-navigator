<?php

namespace App\Enums;

/**
 * Enum representing the source of an organisation's data.
 */
enum OrganisationSource: string
{
    case IMPORT_XLS = 'import_xls'; // Bulk import via CSV file.
    case IMPORT_API = 'import_api'; // Synced organisation via external API.
    case IMPORT_LEGACY = 'import_legacy'; // Data migrated from old systems.
    case USER_MANUAL = 'user_manual'; // When a regular user adds an entry.
    case USER_ADMIN = 'user_admin'; // When an admin user adds an entry.
    case PARTNER_PORTAL = 'partner_portal'; // When partners contribute data through integrations.
    case DATA_AGGREGATOR = 'data_aggregator'; // Entries from third-party providers.
    case UNKNOWN = 'unknown'; // For legacy or uncategorised data.
}
