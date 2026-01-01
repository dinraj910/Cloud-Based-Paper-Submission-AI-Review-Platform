<?php
/**
 * AWS S3 Configuration Test Script
 * Run this to verify your AWS setup is working correctly
 * 
 * Usage: php test_aws_config.php
 */

require_once __DIR__ . '/config/aws.php';

echo "=== AWS S3 Configuration Test ===\n\n";

// Test 1: Check if AWS is configured
echo "1. Checking AWS Configuration...\n";
if (isAwsConfigured()) {
    echo "   ✓ AWS credentials are configured\n";
    echo "   - Region: " . AWS_REGION . "\n";
    echo "   - Bucket: " . AWS_S3_BUCKET . "\n\n";
} else {
    echo "   ✗ AWS is NOT configured\n";
    echo "   → Please update config/aws.php with your credentials\n";
    echo "   → See AWS_SETUP_GUIDE.md for instructions\n\n";
    exit(1);
}

// Test 2: Try to create S3 client
echo "2. Testing S3 Client Connection...\n";
try {
    $s3Client = getS3Client();
    echo "   ✓ S3 Client created successfully\n\n";
} catch (Exception $e) {
    echo "   ✗ Failed to create S3 Client\n";
    echo "   Error: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Test 3: Check if bucket exists
echo "3. Checking S3 Bucket Access...\n";
try {
    $result = $s3Client->headBucket([
        'Bucket' => AWS_S3_BUCKET
    ]);
    echo "   ✓ Bucket '" . AWS_S3_BUCKET . "' exists and is accessible\n\n";
} catch (Exception $e) {
    echo "   ✗ Cannot access bucket '" . AWS_S3_BUCKET . "'\n";
    echo "   Error: " . $e->getMessage() . "\n";
    echo "   → Make sure the bucket exists in your AWS account\n";
    echo "   → Verify your AWS credentials have S3 permissions\n\n";
    exit(1);
}

// Test 4: Try to upload a test file
echo "4. Testing File Upload to S3...\n";
$testContent = "This is a test file created at " . date('Y-m-d H:i:s');
$testFile = sys_get_temp_dir() . '/test-' . time() . '.txt';
file_put_contents($testFile, $testContent);

try {
    $testKey = 'test/test-' . time() . '.txt';
    $result = $s3Client->putObject([
        'Bucket'      => AWS_S3_BUCKET,
        'Key'         => $testKey,
        'SourceFile'  => $testFile,
        'ContentType' => 'text/plain',
        'ACL'         => S3_ACL,
    ]);
    
    echo "   ✓ Test file uploaded successfully\n";
    echo "   - S3 URL: " . $result['ObjectURL'] . "\n";
    echo "   - Key: " . $testKey . "\n\n";
    
    // Clean up test file from S3
    echo "5. Cleaning up test file...\n";
    $s3Client->deleteObject([
        'Bucket' => AWS_S3_BUCKET,
        'Key'    => $testKey,
    ]);
    echo "   ✓ Test file deleted from S3\n\n";
    
} catch (Exception $e) {
    echo "   ✗ Failed to upload test file\n";
    echo "   Error: " . $e->getMessage() . "\n\n";
    exit(1);
} finally {
    // Clean up local test file
    if (file_exists($testFile)) {
        unlink($testFile);
    }
}

echo "=== All Tests Passed! ✓ ===\n";
echo "Your AWS S3 integration is working correctly!\n";
echo "You can now upload research papers through the portal.\n\n";
?>
