<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Report\Reporter;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use TwigCsFixer\Report\Report;
use TwigCsFixer\Report\Reporter\TextReporter;
use TwigCsFixer\Report\Violation;
use TwigCsFixer\Report\ViolationId;
use TwigCsFixer\Tests\TestHelper;

final class TextReporterTest extends TestCase
{
    public function testGetName(): void
    {
        static::assertSame(TextReporter::NAME, (new TextReporter())->getName());
    }

    /**
     * @dataProvider displayDataProvider
     */
    public function testDisplayErrors(string $expected, ?string $level, bool $debug): void
    {
        $textFormatter = new TextReporter();

        $file = TestHelper::getOsPath('tests/Report/Reporter/Fixtures/file.twig');
        $report = new Report([new \SplFileInfo($file)]);

        $violation0 = new Violation(
            Violation::LEVEL_NOTICE,
            'Notice',
            $file,
            'Rule',
            new ViolationId('NoticeId', null, 1)
        );
        $report->addViolation($violation0);
        $violation1 = new Violation(
            Violation::LEVEL_WARNING,
            'Warning',
            $file,
            'Rule',
            new ViolationId('WarningId', null, 2)
        );
        $report->addViolation($violation1);
        $violation2 = new Violation(
            Violation::LEVEL_ERROR,
            'Error',
            $file,
            'Rule',
            new ViolationId('ErrorId', null, 3)
        );
        $report->addViolation($violation2);
        $violation3 = new Violation(
            Violation::LEVEL_FATAL,
            'Fatal',
            $file,
            'Rule',
            new ViolationId('FatalId')
        );
        $report->addViolation($violation3);

        $output = new BufferedOutput(OutputInterface::VERBOSITY_NORMAL, true);
        $textFormatter->display($output, $report, $level, $debug);

        $text = $output->fetch();
        static::assertStringContainsString($expected, $text);
        static::assertStringContainsString('[ERROR]', $text);
    }

    /**
     * @return iterable<array-key, array{string, string|null, bool}>
     */
    public static function displayDataProvider(): iterable
    {
        yield [
            sprintf(
                <<<EOD
                     \e[31mKO\e[39m %s
                     --------- --------------------------------------- 
                      \e[33mNOTICE\e[39m    1    |     {# Some text line 1 #}      
                                \e[31m>>   | Notice\e[39m                          
                                2    | {# Some text line 2 #}          
                     --------- --------------------------------------- 
                      \e[33mWARNING\e[39m   1    |         {# Some text line 1 #}  
                                2    |     {# Some text line 2 #}      
                                \e[31m>>   | Warning\e[39m                         
                                3    | {# Some text line 3 #}          
                     --------- --------------------------------------- 
                      \e[33mERROR\e[39m     2    |     {# Some text line 2 #}      
                                3    | {# Some text line 3 #}          
                                \e[31m>>   | Error\e[39m                           
                                4    |                                 
                     --------- --------------------------------------- 
                      \e[33mFATAL\e[39m     \e[31m>>   | Fatal\e[39m                           
                     --------- --------------------------------------- 
                    EOD,
                TestHelper::getOsPath('tests/Report/Reporter/Fixtures/file.twig')
            ),
            null,
            false,
        ];

        yield [
            sprintf(
                <<<EOD
                     \e[31mKO\e[39m %s
                     ------- ----------------------------------- 
                      \e[33mERROR\e[39m   2    |     {# Some text line 2 #}  
                              3    | {# Some text line 3 #}      
                              \e[31m>>   | Error\e[39m                       
                              4    |                             
                     ------- ----------------------------------- 
                      \e[33mFATAL\e[39m   \e[31m>>   | Fatal\e[39m                       
                     ------- ----------------------------------- 
                    EOD,
                TestHelper::getOsPath('tests/Report/Reporter/Fixtures/file.twig')
            ),
            Report::MESSAGE_TYPE_ERROR,
            false,
        ];

        yield [
            sprintf(
                <<<EOD
                     \e[31mKO\e[39m %s
                     ------- ----------------------------------- 
                      \e[33mERROR\e[39m   2    |     {# Some text line 2 #}  
                              3    | {# Some text line 3 #}      
                              \e[31m>>   | ErrorId:3 -- Error\e[39m          
                              4    |                             
                     ------- ----------------------------------- 
                      \e[33mFATAL\e[39m   \e[31m>>   | FatalId -- Fatal\e[39m            
                     ------- ----------------------------------- 
                    EOD,
                TestHelper::getOsPath('tests/Report/Reporter/Fixtures/file.twig')
            ),
            Report::MESSAGE_TYPE_ERROR,
            true,
        ];
    }

    public function testDisplaySuccess(): void
    {
        $textFormatter = new TextReporter();

        $file = TestHelper::getOsPath(__DIR__.'/Fixtures/file.twig');
        $report = new Report([new \SplFileInfo($file)]);

        $output = new BufferedOutput();
        $textFormatter->display($output, $report, null, false);

        $text = $output->fetch();
        static::assertStringNotContainsString(sprintf('KO %s', $file), $text);
        static::assertStringContainsString('[OK]', $text);
    }

    public function testDisplayMultipleFiles(): void
    {
        $textFormatter = new TextReporter();

        $file = TestHelper::getOsPath(__DIR__.'/Fixtures/file.twig');
        $file2 = TestHelper::getOsPath(__DIR__.'/Fixtures/file2.twig');

        $report = new Report([new \SplFileInfo($file), new \SplFileInfo($file2)]);
        $violation = new Violation(Violation::LEVEL_ERROR, 'Error', $file, null, new ViolationId(line: 3));
        $report->addViolation($violation);

        $output = new BufferedOutput();
        $textFormatter->display($output, $report, null, false);

        static::assertStringContainsString(
            sprintf(
                <<<EOD
                     KO %s
                     ------- ----------------------------------- 
                      ERROR   2    |     {# Some text line 2 #}  
                              3    | {# Some text line 3 #}      
                              >>   | Error                       
                              4    |                             
                     ------- ----------------------------------- 
                    
                     [ERROR] Files linted: 2, notices: 0, warnings: 0, errors: 1
                    EOD,
                $file
            ),
            $output->fetch()
        );
    }

    public function testDisplayNotFoundFile(): void
    {
        $textFormatter = new TextReporter();

        $file = TestHelper::getOsPath(__DIR__.'/Fixtures/fileNotFound.twig');

        $report = new Report([new \SplFileInfo($file)]);
        $violation = new Violation(Violation::LEVEL_ERROR, 'Error', $file, null, new ViolationId(line: 1));
        $report->addViolation($violation);

        $output = new BufferedOutput();
        $textFormatter->display($output, $report, null, false);

        static::assertStringContainsString(
            sprintf(
                <<<EOD
                     KO %s
                     ------- -------------- 
                      ERROR   >>   | Error  
                     ------- -------------- 
                    EOD,
                $file
            ),
            rtrim($output->fetch())
        );
    }

    /**
     * @dataProvider displayBlockDataProvider
     */
    public function testDisplayBlock(string $expected, int $level): void
    {
        $textFormatter = new TextReporter();

        $file = TestHelper::getOsPath(__DIR__.'/Fixtures/file.twig');
        $report = new Report([new \SplFileInfo($file)]);

        $violation = new Violation($level, 'Message', $file, null, new ViolationId(line: 1));
        $report->addViolation($violation);

        $output = new BufferedOutput(OutputInterface::VERBOSITY_NORMAL, true);
        $textFormatter->display($output, $report, null, false);

        $text = $output->fetch();
        static::assertStringContainsString($expected, $text);
    }

    /**
     * @return iterable<array-key, array{string, int}>
     */
    public static function displayBlockDataProvider(): iterable
    {
        yield ['[OK] Files linted: 1, notices: 1, warnings: 0, errors: 0', Violation::LEVEL_NOTICE];
        yield ['[WARNING] Files linted: 1, notices: 0, warnings: 1, errors: 0', Violation::LEVEL_WARNING];
        yield ['[ERROR] Files linted: 1, notices: 0, warnings: 0, errors: 1', Violation::LEVEL_ERROR];
        yield ['[ERROR] Files linted: 1, notices: 0, warnings: 0, errors: 1', Violation::LEVEL_FATAL];
    }
}
