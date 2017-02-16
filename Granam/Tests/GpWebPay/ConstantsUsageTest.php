<?php
namespace Granam\Tests\GpWebPay;

use Granam\GpWebPay\Codes;
use Granam\GpWebPay\RequestDigestKeys;
use PHPUnit\Framework\TestCase;

class ConstantsUsageTest extends TestCase
{
    /**
     * @test
     */
    public function everyCodeIsTakenFromHelperAsConstant()
    {
        foreach ($this->getProjectClasses() as $projectClass) {
            if (is_a($projectClass, Codes::class, true)) {
                continue; // Codes are the only ones with GP WebPay constants
            }
            $reflectionClass = new \ReflectionClass($projectClass);
            $classContent = file_get_contents($reflectionClass->getFileName());
            self::assertSame(
                0,
                preg_match('~([\'"])[A-Z_]+\1~', $classContent, $matches),
                "Class {$projectClass} uses an internal constant-like value: " . implode(';', $matches)
                . " Every 'CODE_NAME' should be taken from one of " . implode(', ', $this->getCodeClasses())
            );
        }
    }

    /**
     * @return array|string[]
     */
    private function getProjectClasses()
    {
        $projectClasses = [];
        $namespace = (new \ReflectionClass(RequestDigestKeys::class))->getNamespaceName();
        foreach (new \DirectoryIterator(__DIR__ . '/../../GpWebPay') as $directoryIterator) {
            if ($directoryIterator->isDir()) {
                continue;
            }
            $projectClass = $namespace . '\\' . $directoryIterator->getBasename('.php');
            self::assertTrue(
                class_exists($projectClass) || interface_exists($projectClass),
                "Class {$projectClass} does not exist or can not be auto-loaded"
            );
            $projectClasses[] = $projectClass;
        }

        return $projectClasses;
    }

    /**
     * @return array|string[]
     */
    private function getCodeClasses()
    {
        $codeClasses = [];
        foreach ($this->getProjectClasses() as $projectClass) {
            if (is_a($projectClass, Codes::class, true)) {
                $codeClasses[] = $projectClass;
            }
        }

        return $codeClasses;
    }
}