<?php
/**
 * Test Runner for DOKO E-commerce System
 * Runs all unit tests manually since PHPUnit might not be available
 */

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the TestCase base class
require_once __DIR__ . '/TestCase.php';

/**
 * Simple Test Runner Class
 */
class TestRunner
{
    private $testsPassed = 0;
    private $testsFailed = 0;
    private $failures = [];
    
    public function runAllTests()
    {
        echo "=== DOKO E-commerce System Test Suite ===\n\n";
        
        // Get all test files
        $testFiles = $this->getTestFiles();
        
        foreach ($testFiles as $testFile) {
            $this->runTestFile($testFile);
        }
        
        $this->printSummary();
    }
    
    private function getTestFiles()
    {
        $testFiles = [];
        
        // Unit tests
        $unitTests = glob(__DIR__ . '/Unit/*Test.php');
        $testFiles = array_merge($testFiles, $unitTests);
        
        // Integration tests
        $integrationTests = glob(__DIR__ . '/Integration/*Test.php');
        $testFiles = array_merge($testFiles, $integrationTests);
        
        // API tests
        $apiTests = glob(__DIR__ . '/API/*Test.php');
        $testFiles = array_merge($testFiles, $apiTests);
        
        return $testFiles;
    }
    
    private function runTestFile($testFile)
    {
        echo "Running " . basename($testFile) . "...\n";
        
        try {
            require_once $testFile;
            
            // Get class name from file name
            $className = str_replace('.php', '', basename($testFile));
            
            if (class_exists($className)) {
                $testInstance = new $className();
                $this->runTestMethods($testInstance, $className);
            } else {
                echo "  ❌ Class $className not found\n";
                $this->testsFailed++;
            }
        } catch (Exception $e) {
            echo "  ❌ Error loading test file: " . $e->getMessage() . "\n";
            $this->testsFailed++;
        } catch (Error $e) {
            echo "  ❌ Fatal error in test file: " . $e->getMessage() . "\n";
            $this->testsFailed++;
        }
        
        echo "\n";
    }
    
    private function runTestMethods($testInstance, $className)
    {
        $reflection = new ReflectionClass($testInstance);
        $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
        
        foreach ($methods as $method) {
            $methodName = $method->getName();
            
            // Only run methods that start with 'test'
            if (strpos($methodName, 'test') === 0) {
                $this->runSingleTest($testInstance, $methodName, $className);
            }
        }
    }
    
    private function runSingleTest($testInstance, $methodName, $className)
    {
        try {
            // Setup
            if (method_exists($testInstance, 'setUp')) {
                $testInstance->setUp();
            }
            
            // Run the test
            $testInstance->$methodName();
            
            echo "  ✅ $methodName\n";
            $this->testsPassed++;
            
        } catch (Exception $e) {
            echo "  ❌ $methodName: " . $e->getMessage() . "\n";
            $this->testsFailed++;
            $this->failures[] = "$className::$methodName - " . $e->getMessage();
        } catch (Error $e) {
            echo "  ❌ $methodName: " . $e->getMessage() . "\n";
            $this->testsFailed++;
            $this->failures[] = "$className::$methodName - " . $e->getMessage();
        } finally {
            // Teardown
            if (method_exists($testInstance, 'tearDown')) {
                try {
                    $testInstance->tearDown();
                } catch (Exception $e) {
                    // Ignore teardown errors for now
                }
            }
        }
    }
    
    private function printSummary()
    {
        echo "=== Test Results ===\n";
        echo "Total Tests: " . ($this->testsPassed + $this->testsFailed) . "\n";
        echo "✅ Passed: " . $this->testsPassed . "\n";
        echo "❌ Failed: " . $this->testsFailed . "\n";
        
        if (!empty($this->failures)) {
            echo "\n=== Failures ===\n";
            foreach ($this->failures as $failure) {
                echo "- $failure\n";
            }
        }
        
        $successRate = $this->testsPassed + $this->testsFailed > 0 
            ? round(($this->testsPassed / ($this->testsPassed + $this->testsFailed)) * 100, 2)
            : 0;
            
        echo "\nSuccess Rate: {$successRate}%\n";
        
        if ($this->testsFailed === 0) {
            echo "\n🎉 All tests passed!\n";
        } else {
            echo "\n⚠️  Some tests failed. Please review the failures above.\n";
        }
    }
}

// Run the tests
$runner = new TestRunner();
$runner->runAllTests();
