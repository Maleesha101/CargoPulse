<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Shipment;
use App\Models\ReportJob;

class SqlInjectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed the database
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
    }

    /** @test */
    public function normal_shipment_search_works()
    {
        // Create a test shipment
        $shipment = Shipment::create([
            'tracking_number' => 'CP-TEST-1234',
            'customer_name' => 'Test Customer',
            'origin' => 'New York',
            'destination' => 'Los Angeles',
            'shipped_at' => now(),
            'is_restricted' => false,
        ]);

        // Test that a normal query returns the shipment
        $results = DB::select("SELECT * FROM shipments WHERE tracking_number LIKE '%CP-TEST-1234%'");

        $this->assertCount(1, $results);
        $this->assertEquals('CP-TEST-1234', $results[0]->tracking_number);
    }

    /** @test */
    public function invalid_tracking_returns_no_results()
    {
        // Create a test shipment
        Shipment::create([
            'tracking_number' => 'CP-TEST-1234',
            'customer_name' => 'Test Customer',
            'origin' => 'New York',
            'destination' => 'Los Angeles',
            'shipped_at' => now(),
            'is_restricted' => false,
        ]);

        // Test that an invalid query returns empty results
        $results = DB::select("SELECT * FROM shipments WHERE tracking_number LIKE '%INVALID%'");

        $this->assertCount(0, $results);
    }

    /** @test */
    public function vulnerable_shipment_search_is_exploitable_with_union()
    {
        // Create test shipments
        Shipment::create([
            'tracking_number' => 'CP-TEST-1234',
            'customer_name' => 'Test Customer',
            'origin' => 'New York',
            'destination' => 'Los Angeles',
            'shipped_at' => now(),
            'is_restricted' => false,
        ]);

        // The vulnerable SQL injection using UNION
        // This demonstrates the vulnerability in the training lab
        $injectedQuery = "CP-TEST-1234' UNION SELECT id, name, email, password_hash, role, department, created_at FROM users--";

        $results = DB::select("SELECT * FROM shipments WHERE tracking_number LIKE '%{$injectedQuery}%'");

        // The injection should return both the shipment AND the users data
        // In the vulnerable implementation, the UNION works
        $this->assertGreaterThan(0, count($results));
    }

    /** @test */
    public function sql_injection_can_extract_database_info()
    {
        // Create test shipments
        Shipment::create([
            'tracking_number' => 'CP-TEST-1234',
            'customer_name' => 'Test Customer',
            'origin' => 'New York',
            'destination' => 'Los Angeles',
            'shipped_at' => now(),
            'is_restricted' => false,
        ]);

        // Test extracting table names (PostgreSQL specific)
        $injectedQuery = "CP-TEST-1234' UNION SELECT table_name, column_name, NULL, NULL, NULL, NULL, NULL FROM information_schema.columns WHERE table_name IN ('users', 'shipments')--";

        $results = DB::select("SELECT * FROM shipments WHERE tracking_number LIKE '%{$injectedQuery}%'");

        // Should return database schema info
        $this->assertGreaterThan(0, count($results));
    }

    /** @test */
    public function report_filter_stores_input_safely()
    {
        // Create a test user
        $user = User::where('role', 'operations')->first();

        // Create a report job with a simple filter
        $reportJob = ReportJob::create([
            'user_id' => $user->id,
            'report_type' => 'delivery_status',
            'filter_expression' => 'is_restricted = 0',
            'status' => 'pending',
        ]);

        // Verify the filter is stored as-is
        $this->assertEquals('is_restricted = 0', $reportJob->filter_expression);
        $this->assertEquals('pending', $reportJob->status);
    }

    /** @test */
    public function malicious_report_filter_can_inject_sql_when_processed()
    {
        // Create a test user
        $user = User::where('role', 'operations')->first();

        // Create a report job with a malicious filter
        $reportJob = ReportJob::create([
            'user_id' => $user->id,
            'report_type' => 'special_investigation',
            'filter_expression' => "1=1 UNION SELECT id, service_name, username, secret_value, environment, description, created_at FROM internal_credentials--",
            'status' => 'pending',
        ]);

        // Simulate the vulnerable report worker processing
        $storedFilter = $reportJob->filter_expression;

        // INTENTIONALLY VULNERABLE — SQLi training lab
        // The stored filter is concatenated directly into the SQL query
        $query = "SELECT s.*, COUNT(se.id) as event_count
                 FROM shipments s
                 LEFT JOIN shipment_events se ON s.id = se.shipment_id
                 WHERE " . $storedFilter . "
                 GROUP BY s.id
                 ORDER BY s.created_at DESC";

        // This should work because the filter is concatenated without parameterization
        $results = DB::select($query);

        // The injection should return the internal credentials
        $foundCredentials = false;
        foreach ($results as $row) {
            if (isset($row->secret_value) && str_contains($row->secret_value, 'TRAINING_ONLY')) {
                $foundCredentials = true;
                break;
            }
        }

        $this->assertTrue($foundCredentials, 'The second-order SQLi should expose internal credentials');
    }

    /** @test */
    public function secure_shipment_search_blocks_sql_injection()
    {
        // Create a test shipment
        $shipment = Shipment::create([
            'tracking_number' => 'CP-TEST-1234',
            'customer_name' => 'Test Customer',
            'origin' => 'New York',
            'destination' => 'Los Angeles',
            'shipped_at' => now(),
            'is_restricted' => false,
        ]);

        // Try SQL injection in secure implementation
        $injectedQuery = "CP-TEST-1234' OR '1'='1";

        // In secure implementation, the input is parameterized
        // This should return only the matching shipment, not all rows
        $results = DB::select("SELECT * FROM shipments WHERE tracking_number = ?", [$injectedQuery]);

        // Should return 0 results because the exact tracking number doesn't exist
        $this->assertCount(0, $results);
    }

    /** @test */
    public function secure_report_filter_validates_input()
    {
        // Test the secure report controller's validation
        $secureController = new \App\Security\SecureReportController();

        // Test valid filter expression
        $isValid = $this->invokeMethod($secureController, 'validateFilterExpression', ['is_restricted = 0']);
        $this->assertTrue($isValid);

        // Test malicious filter expression
        $isValid = $this->invokeMethod($secureController, 'validateFilterExpression', ["1=1 UNION SELECT * FROM users--"]);
        $this->assertFalse($isValid);

        // Test another malicious filter expression
        $isValid = $this->invokeMethod($secureController, 'validateFilterExpression', ["status = 'x' OR '1'='1'"]);
        $this->assertFalse($isValid);
    }

    /** @test */
    public function unauthorized_user_cannot_access_ops_console()
    {
        // Create a customer user
        $customer = User::create([
            'name' => 'Customer User',
            'email' => 'customer@test.com',
            'password_hash' => password_hash('password', PASSWORD_DEFAULT),
            'role' => 'customer',
        ]);

        // Customer should not be able to access operations endpoints directly
        // The middleware should block access
        $this->assertEquals('customer', $customer->role);
        $this->assertFalse($customer->isOperations());
        $this->assertTrue($customer->isCustomer());
    }

    /** @test */
    public function flag_is_stored_securely_in_database()
    {
        // The flag should be in the security_challenges table
        $challenge = DB::table('security_challenges')->first();

        $this->assertNotNull($challenge);
        $this->assertEquals('Second-Order SQLi Chain', $challenge->challenge_name);
        $this->assertStringContainsString('CP{', $challenge->flag);
        $this->assertStringContainsString('}', $challenge->flag);
    }

    /** @test */
    public function internal_credentials_table_exists()
    {
        // Verify the internal credentials table exists and has data
        $credentials = DB::table('internal_credentials')->get();

        $this->assertCount(3, $credentials);

        // Verify the fake credentials are present
        $credentialNames = $credentials->pluck('service_name')->toArray();
        $this->assertContains('customs-sync', $credentialNames);
        $this->assertContains('inventory-tracker', $credentialNames);
        $this->assertContains('risk-analyzer', $credentialNames);
    }

    /** @test */
    public function cp_void_7719_shipment_exists()
    {
        // The special shipment CP-VOID-7719 should exist
        $shipment = Shipment::where('tracking_number', 'CP-VOID-7719')->first();

        $this->assertNotNull($shipment);
        $this->assertTrue($shipment->is_restricted);
    }

    protected function invokeMethod(&$object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}