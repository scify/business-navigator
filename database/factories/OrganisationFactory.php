<?php

namespace Database\Factories;

use App\Enums\OrganisationNumberOfEmployees;
use App\Enums\OrganisationTurnover;
use App\Models\Filters\Country;
use App\Models\Filters\EnterpriseFunction;
use App\Models\Filters\IndustrySector;
use App\Models\Filters\OfferType;
use App\Models\Filters\OrganisationType;
use App\Models\Filters\SolutionType;
use App\Models\Filters\TechnologyType;
use App\Models\Organisation;
use Faker\Factory as FakerFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Organisation>
 */
class OrganisationFactory extends Factory
{
    /**
     * Mapping of country codes to (existing) Faker locales.
     *
     * For a list of existing faker locales see:
     *
     * @link https://fakerjs.dev/guide/localization#available-locales
     */
    private const COUNTRY_LOCALES = [
        'AT' => 'de_AT', // Austria
        'BE' => 'nl_BE', // Belgium (Dutch)
        'HR' => 'hr',    // Croatia
        'CY' => 'el',    // Cyprus (Greek)
        'CZ' => 'cs_CZ', // Czechia
        'DK' => 'da',    // Denmark
        'FI' => 'fi',    // Finland
        'FR' => 'fr',    // France
        'DE' => 'de_DE', // Germany
        'GR' => 'el',    // Greece
        'HU' => 'hu',    // Hungary
        'IE' => 'en_IE', // Ireland
        'IT' => 'it',    // Italy
        'LV' => 'lv',    // Latvia
        'LU' => 'fr_LU', // Luxembourg (French)
        'NL' => 'nl',    // Netherlands
        'PL' => 'pl',    // Poland
        'PT' => 'pt_PT', // Portugal
        'RO' => 'ro',    // Romania
        'SK' => 'sk',    // Slovakia
        'ES' => 'es',    // Spain
        'SE' => 'sv',    // Sweden
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Attempts to get a random existing country, otherwise set as null:
        $country = Country::inRandomOrder()->first();

        // Determines Faker locale based on country code if available, or default locale:
        $defaultLocale = config('app.faker_locale', 'en');
        $locale = $country ? (self::COUNTRY_LOCALES[$country->alpha2] ?? $defaultLocale) : $defaultLocale;

        // Creates a new Faker instance with the determined locale if needed:
        $localizedFaker = $this->faker;
        if ($locale !== $defaultLocale) {
            $localizedFaker = FakerFactory::create($locale);
        }

        // Sets latitude and longitude based on country if available:
        $offset = 0.1;
        $lat = $country ? $country->lat + $localizedFaker->randomFloat(8, -$offset, $offset) : null;
        $lng = $country ? $country->lng + $localizedFaker->randomFloat(8, -$offset, $offset) : null;
        $name = $localizedFaker->company();

        $data = [
            'name' => $name,
            'short_description' => $localizedFaker->sentence,
            'description' => collect(
                // Random number of paragraphs between 2 and 4
                range(1, $localizedFaker->numberBetween(2, 4)))
                // Each paragraph has 6 to 10 sentences
                ->map(fn () => $localizedFaker->sentences($localizedFaker->numberBetween(6, 10), true))
                ->implode("\n"),
            'address' => $localizedFaker->streetAddress,
            'place' => $localizedFaker->city,
            'postal_code' => $localizedFaker->postcode,
            'country_id' => $country?->id,
            'lng' => $lng,
            'lat' => $lat,
            'email' => $localizedFaker->companyEmail,
            'phone' => $localizedFaker->phoneNumber,
            'website_url' => $localizedFaker->url,
            'social_linkedin' => $localizedFaker->url,
            // Enums & other details:
            'founding_year' => $localizedFaker->year,
            'number_of_employees' => $localizedFaker->boolean(80)
                ? $localizedFaker->randomElement(OrganisationNumberOfEmployees::cases())->value
                : null,
            'turnover' => $localizedFaker->boolean(80)
                ? $localizedFaker->randomElement(OrganisationTurnover::cases())->value
                : null,
            'is_active' => $localizedFaker->boolean(99),
            'slug' => Str::slug($name),
        ];

        unset($localizedFaker);

        return $data;

    }

    /**
     * Configure the factory to attach existing related models after creation.
     *
     * @return $this
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Organisation $organisation) {
            // Attaches 1 random organisation type:
            $organisation->organisationTypes()->attach(
                OrganisationType::inRandomOrder()->take(1)->pluck('id')->toArray()
            );

            // Attaches 1 or 2 random industry sectors:
            $organisation->industrySectors()->attach(
                IndustrySector::inRandomOrder()->take(rand(1, 2))->pluck('id')->toArray()
            );

            // Attaches 1 or 2 random enterprise functions:
            $organisation->enterpriseFunctions()->attach(
                EnterpriseFunction::inRandomOrder()->take(rand(1, 2))->pluck('id')->toArray()
            );

            // Attaches 1 or 2 random solution types:
            $organisation->solutionTypes()->attach(
                SolutionType::inRandomOrder()->take(rand(1, 2))->pluck('id')->toArray()
            );

            // Attaches 0 to 1 random technology types:
            $technologyCount = rand(0, 1);
            if ($technologyCount > 0) {
                $organisation->technologyTypes()->attach(
                    TechnologyType::inRandomOrder()->take($technologyCount)->pluck('id')->toArray()
                );
            }

            // Attaches 0 to 1 random offer types:
            $offerCount = rand(0, 1);
            if ($offerCount > 0) {
                $organisation->offerTypes()->attach(
                    OfferType::inRandomOrder()->take($offerCount)->pluck('id')->toArray()
                );
            }

        });
    }
}
