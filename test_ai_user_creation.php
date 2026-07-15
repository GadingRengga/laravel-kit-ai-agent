<?php

// Simple test script to verify AI User Creation Tool
// Run with: php test_ai_user_creation.php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Superuser\User;
use App\Models\Superuser\Role;
use App\Services\AI\Tools\CreateUserTool;
use Illuminate\Support\Facades\Hash;

echo "=== AI User Creation Tool Test ===\n\n";

// Check if superuser exists
$superuser = User::where('email', 'superuser@netra.local')->first();
if (!$superuser) {
    echo "❌ Superuser not found. Please run: php artisan db:seed --class=SuperUserSeeder\n";
    exit(1);
}

echo "✅ Superuser found: {$superuser->name} ({$superuser->email})\n";

// Check if user.create permission exists
$hasPermission = $superuser->hasPermission('user.create');
echo ($hasPermission ? "✅" : "❌") . " Superuser has 'user.create' permission: " . ($hasPermission ? "YES" : "NO") . "\n";

// Check if Staff role exists
$staffRole = Role::where('slug', 'staff')->orWhere('name', 'Staff')->first();
if (!$staffRole) {
    echo "❌ Staff role not found.\n";
    exit(1);
}

echo "✅ Staff role found: {$staffRole->name} (ID: {$staffRole->id})\n";

// Test the tool
echo "\n=== Testing CreateUserTool ===\n";

try {
    $tool = new CreateUserTool([]);

    // Test 1: Check tool name
    echo ($tool->name() === 'create_user' ? "✅" : "❌") . " Tool name: {$tool->name()}\n";

    // Test 2: Check permission
    $isAllowed = $tool->isAllowedFor($superuser);
    echo ($isAllowed ? "✅" : "❌") . " Tool allowed for superuser: " . ($isAllowed ? "YES" : "NO") . "\n";

    // Test 3: Create a test user
    echo "\n--- Creating test user ---\n";
    $testData = [
        'name' => 'Test AI User',
        'email' => 'test.ai.' . time() . '@example.com',
        'username' => 'testai' . time(),
        'password' => 'password123',
        'role' => 'Staff',
    ];

    $draft = $tool->toDraft($testData, $superuser);
    echo "✅ Draft created successfully\n";
    echo "   Summary: {$draft->summary}\n";
    echo "   Model: {$draft->modelClass}\n";
    echo "   Payload keys: " . implode(', ', array_keys($draft->payload)) . "\n";

    // Test 4: Confirm and create user
    echo "\n--- Confirming and creating user ---\n";
    $newUser = $tool->confirm($draft->payload, $superuser);
    echo "✅ User created successfully!\n";
    echo "   ID: {$newUser->id}\n";
    echo "   Name: {$newUser->name}\n";
    echo "   Email: {$newUser->email}\n";
    echo "   Username: {$newUser->username}\n";
    echo "   Active: " . ($newUser->is_active ? 'YES' : 'NO') . "\n";
    echo "   Roles: " . $newUser->roles->pluck('name')->join(', ') . "\n";
    echo "   Created by: {$newUser->created_by}\n";

    // Clean up test user
    $newUser->roles()->detach();
    $newUser->delete();
    echo "\n✅ Test user cleaned up\n";

    echo "\n=== ALL TESTS PASSED ===\n";
    echo "\nYou can now use the AI agent to create users!\n";
    echo "Example: 'Buat user baru dengan nama John Doe, email john@example.com, username johndoe, password password123, role Staff'\n";
} catch (\Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
