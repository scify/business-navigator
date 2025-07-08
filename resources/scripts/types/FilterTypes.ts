// SelectedFilters represents the currently chosen filter values by the user.
// These correspond to the "slug" of a selected option in each filter, or null if none is selected.
export interface SelectedFilters {
    organisation_type: string | null;
    industry_sector: string | null;
    enterprise_function: string | null;
    solution_type: string | null;
    technology_type: string | null;
    offer_type: string | null;
    country: string | null;
}

// Filters represent all the available filter categories and their options.
// Each filter category (e.g. organisation_types) can be null or a Filter object
// that contains label information and a list of filter options.
export interface Filters {
    organisation_types: Filter | null;
    industry_sectors: Filter | null;
    enterprise_functions: Filter | null;
    solution_types: Filter | null;
    technology_types: Filter | null;
    offer_types: Filter | null;
    countries: Filter | null;
}

// Filter defines a single filter category, including its
// display labels and all available options.
export interface Filter {
    label: FilterLabel;
    options: FilterOptions[];
    slug: string;
}

// FilterLabel holds the singular and plural forms of a filter label.
export interface FilterLabel {
    singular: string;
    plural: string;
}

// FilterOptions represents a single choice within a filter category.
export interface FilterOptions {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    label: string | null;
    order: number | null;
}
