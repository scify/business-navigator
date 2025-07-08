// Defines the structure for the Organisation Model.
export interface Organisation {
    id: number;
    slug: string;
    name: string;
    short_description?: string;
    description?: string;
    region?: string;
    city?: string;
    address_1?: string;
    address_2?: string;
    formatted_address?: string;
    lat?: number;
    lng?: number;
    location_confidence?: number;
    location_source?: 'manual' | 'opencage' | 'google' | 'mapbox' | 'osm' | 'import_xls' | 'unknown';
    website_url?: string;
    social_bluesky?: string;
    social_facebook?: string;
    social_instagram?: string;
    social_linkedin?: string;
    social_x?: string;
    marketplace_slug?: string;
    founding_year?: number;
    number_of_employees?: string;
    turnover?: string;
    turnover_short?: string;
    source: 'import_xls' | 'import_api' | 'import_legacy' | 'user_manual' | 'user_admin' | 'partner_portal' | 'data_aggregator' | 'unknown';
    is_active: boolean;
    updated_at: string;
    // Relations
    logo?: Logo;
    country?: Country;
    organisation_types?: OrganisationType[];
    industry_sectors?: IndustrySector[];
    enterprise_functions?: EnterpriseFunction[];
    solution_types?: SolutionType[];
    technology_types?: TechnologyType[];
    offer_types?: OfferType[];
}

export interface Logo {
    id: number;
    uuid: string;
    filename: string;
    original_filename: string;
    file_extension: string;
    mime_type?: string;
    alt?: string;
    width?: number;
    height?: number;
    size?: number;
    has_transparency: boolean;
    background_color?: string;
    source: 'import_xls' | 'user_upload' | 'user_admin' | 'unknown';
}

export interface OrganisationGeoProperties {
    id: number;
    slug: string;
    name: string;
    address?: string;
    country?: string;
    organisation_types?: string[];
}

// Defines the structure for the Country Model.
export interface Country {
    id: number;
    slug: string;
    name: string;
    alpha2: string;
    alpha3: string;
    demonym: string | null;
    lat: number | null;
    lng: number | null;
}

export interface BaseCategoryType {
    id: number;
    slug: string;
    name: string;
    description: string | null;
    label: string;
    order: number;
}

export type OrganisationType = BaseCategoryType;
export type IndustrySector = BaseCategoryType;
export type EnterpriseFunction = BaseCategoryType;
export type SolutionType = BaseCategoryType;
export type TechnologyType = BaseCategoryType;
export type OfferType = BaseCategoryType;
