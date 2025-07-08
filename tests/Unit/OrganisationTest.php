<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Filters\Country;
use App\Models\Organisation;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OrganisationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    #[Group('organisation-creation')]
    public function organisation_boot_creation_generates_match_hash_and_slug()
    {
        $germany = Country::where('alpha2', 'DE')->first();

        $organisation = Organisation::create([
            'name' => 'Test Company',
            'country_id' => $germany->id,
        ]);

        $this->assertNotNull($organisation->match_hash);
        $this->assertIsString($organisation->match_hash);
        $this->assertEquals(64, strlen($organisation->match_hash));
        $this->assertNotNull($organisation->slug);
        $this->assertEquals('test-company', $organisation->slug);
    }

    #[Test]
    #[Group('organisation-creation')]
    public function organisation_boot_creation_prevents_duplicate_match_hash()
    {
        $germany = Country::where('alpha2', 'DE')->first();

        Organisation::create([
            'name' => 'Test Company',
            'country_id' => $germany->id,
        ]);

        $this->expectException(QueryException::class);

        Organisation::create([
            'name' => 'Test Company',
            'country_id' => $germany->id,
        ]);
    }

    #[Test]
    #[Group('organisation-creation')]
    public function organisation_boot_creation_prevents_duplicate_slug()
    {
        // Create first organisation
        Organisation::create([
            'name' => 'Test Company',
            'country_id' => null,
        ]);

        $this->expectException(QueryException::class);

        // Try to create organisation with same slug
        Organisation::create([
            'name' => 'Test Company',
            'country_id' => null,
        ]);
    }

    #[Test]
    #[Group('organisation-updates')]
    public function organisation_boot_updating_regenerates_match_hash_on_name_change()
    {
        $germany = Country::where('alpha2', 'DE')->first();

        $organisation = Organisation::create([
            'name' => 'Original Name',
            'country_id' => $germany->id,
        ]);

        $originalHash = $organisation->match_hash;

        $organisation->update(['name' => 'Updated Name']);

        $this->assertNotEquals($originalHash, $organisation->fresh()->match_hash);
    }

    #[Test]
    #[Group('organisation-updates')]
    public function organisation_boot_updating_regenerates_match_hash_on_country_change()
    {
        $germany = Country::where('alpha2', 'DE')->first();
        $france = Country::where('alpha2', 'FR')->first();

        $organisation = Organisation::create([
            'name' => 'Test Company',
            'country_id' => $germany->id,
        ]);

        $originalHash = $organisation->match_hash;

        $organisation->update(['country_id' => $france->id]);

        $this->assertNotEquals($originalHash, $organisation->fresh()->match_hash);
    }

    #[Test]
    #[Group('organisation-updates')]
    public function organisation_boot_updating_preserves_match_hash_on_other_changes()
    {
        $germany = Country::where('alpha2', 'DE')->first();

        $organisation = Organisation::create([
            'name' => 'Test Company',
            'country_id' => $germany->id,
        ]);

        $originalHash = $organisation->match_hash;

        $organisation->update([
            'short_description' => 'Updated description',
            'website_url' => 'https://updated.com',
        ]);

        $this->assertEquals($originalHash, $organisation->fresh()->match_hash);
    }

    #[Test]
    #[Group('organisation-match-hash')]
    public function organisation_match_hash_generation_is_consistent()
    {
        $germany = Country::where('alpha2', 'DE')->first();

        $org1 = new Organisation([
            'name' => 'Test Company',
            'country_id' => $germany->id,
        ]);

        $org2 = new Organisation([
            'name' => 'Test Company',
            'country_id' => $germany->id,
        ]);

        $hash1 = $org1->generateMatchHash();
        $hash2 = $org2->generateMatchHash();

        $this->assertEquals($hash1, $hash2);
    }

    #[Test]
    #[Group('organisation-match-hash')]
    public function organisation_match_hash_differs_for_different_names()
    {
        $germany = Country::where('alpha2', 'DE')->first();

        $org1 = Organisation::create([
            'name' => 'Test Company A',
            'country_id' => $germany->id,
        ]);

        $org2 = Organisation::create([
            'name' => 'Test Company B',
            'country_id' => $germany->id,
        ]);

        $this->assertNotEquals($org1->match_hash, $org2->match_hash);
    }

    #[Test]
    #[Group('organisation-match-hash')]
    public function organisation_match_hash_differs_for_different_countries()
    {
        $germany = Country::where('alpha2', 'DE')->first();
        $france = Country::where('alpha2', 'FR')->first();

        $org1 = Organisation::create([
            'name' => 'Test Company',
            'country_id' => $germany->id,
        ]);

        $org2 = Organisation::create([
            'name' => 'Test Company',
            'country_id' => $france->id,
        ]);

        $this->assertNotEquals($org1->match_hash, $org2->match_hash);
    }

    #[Test]
    #[Group('organisation-match-hash')]
    public function organisation_match_hash_handles_unicode_normalization()
    {
        $germany = Country::where('alpha2', 'DE')->first();

        $org1 = new Organisation([
            'name' => 'Café',
            'country_id' => $germany->id,
        ]);

        $org2 = new Organisation([
            'name' => 'Café',
            'country_id' => $germany->id,
        ]);

        $this->assertEquals($org1->generateMatchHash(), $org2->generateMatchHash());
    }

    #[Test]
    #[Group('organisation-match-hash')]
    public function organisation_match_hash_handles_greek_characters()
    {
        $greece = Country::where('alpha2', 'GR')->first();

        $organisation = Organisation::create([
            'name' => 'Δημόσιος Οργανισμός Τηλεπικοινωνιών (ΔΟΤ)',
            'country_id' => $greece->id,
        ]);

        $testOrg = new Organisation([
            'name' => 'Δημόσιος Οργανισμός Τηλεπικοινωνιών (ΔΟΤ)',
            'country_id' => $greece->id,
        ]);

        $this->assertEquals($organisation->match_hash, $testOrg->generateMatchHash());
        $this->assertNotNull($organisation->match_hash);
    }

    #[Test]
    #[Group('organisation-match-hash')]
    public function organisation_match_hash_handles_null_country()
    {
        $org1 = new Organisation([
            'name' => 'Test Company',
            'country_id' => null,
        ]);

        $org2 = new Organisation([
            'name' => 'Test Company',
            'country_id' => null,
        ]);

        $this->assertEquals($org1->generateMatchHash(), $org2->generateMatchHash());
    }

    #[Test]
    #[Group('organisation-slug')]
    public function organisation_slug_basic_generation()
    {
        $germany = Country::where('alpha2', 'DE')->first();

        $org1 = Organisation::create([
            'name' => 'Test Company Name',
            'country_id' => $germany->id,
        ]);

        $org2 = Organisation::create([
            'name' => 'Company With Special!#^^" Characters',
            'country_id' => $germany->id,
        ]);

        $org3 = Organisation::create([
            'name' => 'Email@Company.com',
            'country_id' => $germany->id,
        ]);

        // Basic slug generation - spaces become hyphens
        $this->assertEquals('test-company-name', $org1->slug);

        // Special characters get stripped out
        $this->assertEquals('company-with-special-characters', $org2->slug);

        // @ symbol gets converted to "at" with hyphens, dots removed
        $this->assertEquals('email-at-companycom', $org3->slug);
    }

    #[Test]
    #[Group('organisation-slug')]
    public function organisation_slug_unicode_generation()
    {
        $greece = Country::where('alpha2', 'GR')->first();

        $org1 = Organisation::create([
            'name' => 'Café & Restaurant',
            'country_id' => $greece->id,
        ]);

        $org2 = Organisation::create([
            'name' => 'RocketMen 🚀 Innovation Labs',
            'country_id' => $greece->id,
        ]);

        $org3 = Organisation::create([
            'name' => 'Τρία κιλά κώδικα & ΣΙΑ',
            'country_id' => $greece->id,
        ]);

        $org4 = Organisation::create([
            'name' => 'Τεχνητός Μετασχηματισμός Α.Ε.',
            'country_id' => $greece->id,
        ]);

        // French accented characters get normalized, & gets stripped:
        $this->assertEquals('cafe-restaurant', $org1->slug);

        // Emojis get stripped out completely:
        $this->assertEquals('rocketmen-innovation-labs', $org2->slug);

        // Greek characters should be transliterated - ώ becomes w (lol):
        $this->assertEquals('tria-kila-kwdika-sia', $org3->slug);

        // Greek characters with company suffix - η becomes i, χ becomes kh, dots removed:
        $this->assertEquals('tekhnitos-metaskhimatismos-ae', $org4->slug);
    }

    #[Test]
    #[Group('organisation-slug')]
    public function organisation_slug_handles_country_code_collisions()
    {
        $germany = Country::where('alpha2', 'DE')->first();
        $france = Country::where('alpha2', 'FR')->first();

        // Create org without country first
        $org1 = Organisation::create([
            'name' => 'Test Company',
            'country_id' => null,
        ]);

        // Create org with same name but with country (should get country code suffix)
        $org2 = Organisation::create([
            'name' => 'Test Company',
            'country_id' => $germany->id,
        ]);

        // Create third org with same name but different country
        $org3 = Organisation::create([
            'name' => 'Test Company',
            'country_id' => $france->id,
        ]);

        $this->assertEquals('test-company', $org1->slug);
        $this->assertEquals('test-company-de', $org2->slug);
        $this->assertEquals('test-company-fr', $org3->slug);
    }

    #[Test]
    #[Group('organisation-slug')]
    public function organisation_slug_handles_multiple_country_collisions()
    {
        $greece = Country::where('alpha2', 'GR')->first();
        $germany = Country::where('alpha2', 'DE')->first();
        $france = Country::where('alpha2', 'FR')->first();

        // First organisation in Greece
        $org1 = Organisation::create([
            'name' => 'Generic Company',
            'country_id' => $greece->id,
        ]);

        // Second organisation in Germany
        $org2 = Organisation::create([
            'name' => 'Generic Company',
            'country_id' => $germany->id,
        ]);

        // Third organisation in France
        $org3 = Organisation::create([
            'name' => 'Generic Company',
            'country_id' => $france->id,
        ]);

        // Fourth organisation in France with different name but same base slug
        $org4 = Organisation::create([
            'name' => 'Generic-Company', // Different name, same slug
            'country_id' => $france->id,
        ]);

        $this->assertEquals('generic-company', $org1->slug);
        $this->assertEquals('generic-company-de', $org2->slug);
        $this->assertEquals('generic-company-fr', $org3->slug);
        $this->assertEquals('generic-company-fr-2', $org4->slug);
    }
}
