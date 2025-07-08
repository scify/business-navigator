// Defines the structure for Paginated Data.
// This handles responses provided via Model::all()->paginate(n).
// @link https://laravel.com/docs/11.x/pagination#converting-results-to-json
export interface PaginatedResults<T> {
    current_page: number;
    data: T[];
    first_page_url: string;
    last_page: number;
    next_page_url: string | null;
    prev_page_url: string | null;
    total: number;
    per_page: number;
    links: PageLink[];
}

// Defines the structure for Page Link Items (for Bootstrap 5).
// Enabling Bootstrap 5 on boot() adds the PageLink[] to results.
// @link https://laravel.com/docs/11.x/pagination#using-bootstrap
export interface PageLink {
    url: string | null;
    label: string;
    active: boolean;
}
