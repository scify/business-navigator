import type { Organisation } from '@/scripts/types/ModelTypes';
import { computed } from 'vue';

export function useOrganisationDescription(organisation: Organisation) {
    const countriesWithThe = [
        // EU Countries
        'Netherlands',
        'Czech Republic',
        // Other European Countries
        // 'United Kingdom', 'Russian Federation',
        // Rest of World
        // 'United States', 'Philippines', 'UAE', 'Republic of Ireland',
        // 'Bahamas', 'Maldives', 'Seychelles', 'Marshall Islands',
        // 'Solomon Islands', 'Central African Republic', 'Democratic Republic of Congo',
        // 'Republic of Korea', 'Gambia', 'Sudan'
    ];

    const organisationType = computed(() => {
        if (organisation.organisation_types?.length) {
            return organisation.organisation_types.map((type: { name: string }) => type.name).join(', ');
        }
        return 'AI-Powered Organisation';
    });

    const formatListWithAnd = (items: string[]): string => {
        if (items.length === 0) return '';
        if (items.length === 1) return items[0];
        if (items.length === 2) return `${items[0]} and ${items[1]}`;

        // For 3+ items: "item1, item2, and item3"
        return `${items.slice(0, -1).join(', ')}, and ${items[items.length - 1]}`;
    };

    const needsArticle = computed(() => {
        return organisation.country ? countriesWithThe.includes(organisation.country.name) : false;
    });

    const countryWithArticle = computed(() => {
        if (!organisation.country) return '';
        return `${needsArticle.value ? 'the ' : ''}${organisation.country.name}`;
    });

    const metaDescription = computed(() => {
        let description = `${organisation.name} is an AI ${organisationType.value}`;

        if (organisation.country) {
            description += ` based in ${countryWithArticle.value}`;
        }

        if (organisation.founding_year) {
            description += `, founded in ${organisation.founding_year}`;
        }

        if (organisation.industry_sectors?.length) {
            const sectors = organisation.industry_sectors
                .map((sector: { name: string }) => sector.name)
                .filter((sector) => sector !== 'All Sectors');

            if (sectors.length > 0) {
                const formattedSectors = formatListWithAnd(sectors);
                description += `, specialising in ${formattedSectors}`;
            }
        }
        return description;
    });

    return {
        organisationType,
        metaDescription,
        countryWithArticle,
        needsArticle,
    };
}
