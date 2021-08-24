<?php

namespace TwigCsFixer\Tests\Report;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use TwigCsFixer\Report\Report;
use TwigCsFixer\Report\SniffViolation;
use TwigCsFixer\Report\TextFormatter;

use function sprintf;

/**
 * Test for TextFormatter.
 */
class TextFormatterTest extends TestCase
{
    /**
     * @param string      $expected
     * @param string|null $level
     *
     * @return void
     *
     * @dataProvider displayDataProvider
     */
    public function testDisplay(string $expected, ?string $level): void
    {
        $input = new ArrayInput([]);
        $output = new BufferedOutput();
        $textFormatter = new TextFormatter($input, $output);

        $file = __DIR__.'/Fixtures/file.twig';
        $report = new Report();
        $report->addFile($file);

        $violation0 = new SniffViolation(SniffViolation::LEVEL_NOTICE, 'Notice', $file, 1);
        $report->addMessage($violation0);
        $violation1 = new SniffViolation(SniffViolation::LEVEL_WARNING, 'Warning', $file, 2);
        $report->addMessage($violation1);
        $violation2 = new SniffViolation(SniffViolation::LEVEL_ERROR, 'Error', $file, 3);
        $report->addMessage($violation2);
        $violation3 = new SniffViolation(SniffViolation::LEVEL_FATAL, 'Fatal', $file);
        $report->addMessage($violation3);

        $textFormatter->display($report, $level);

        $text = $output->fetch();
        self::assertStringContainsString($expected, $text);
    }

    /**
     * @return iterable<array-key, array{string, string|null}>
     */
    public function displayDataProvider(): iterable
    {
        yield [
            sprintf(
                <<<EOD
                 KO %s/Fixtures/file.twig
                 --------- --------------------------------------- 
                  NOTICE    1    |     {# Some text line 1 #}      
                            >>   | Notice                          
                            2    | {# Some text line 2 #}          
                 --------- --------------------------------------- 
                  WARNING   1    |         {# Some text line 1 #}  
                            2    |     {# Some text line 2 #}      
                            >>   | Warning                         
                            3    | {# Some text line 3 #}          
                 --------- --------------------------------------- 
                  ERROR     2    |     {# Some text line 2 #}      
                            3    | {# Some text line 3 #}          
                            >>   | Error                           
                            4    |                                 
                 --------- --------------------------------------- 
                  FATAL     >>   | Fatal                           
                 --------- --------------------------------------- 
                EOD,
                __DIR__
            ),
            null,
        ];

        yield [
            sprintf(
                <<<EOD
                 KO %s/Fixtures/file.twig
                 ------- ----------------------------------- 
                  ERROR   2    |     {# Some text line 2 #}  
                          3    | {# Some text line 3 #}      
                          >>   | Error                       
                          4    |                             
                 ------- ----------------------------------- 
                  FATAL   >>   | Fatal                       
                 ------- ----------------------------------- 
                EOD,
                __DIR__
            ),
            Report::MESSAGE_TYPE_ERROR,
        ];
    }

    /**
     * @param string $expected
     * @param int    $level
     *
     * @return void
     *
     * @dataProvider displayBlockDataProvider
     */
    public function testDisplayBlock(string $expected, int $level): void
    {
        $input = new ArrayInput([]);
        $output = new BufferedOutput();
        $textFormatter = new TextFormatter($input, $output);

        $file = __DIR__.'/Fixtures/file.twig';
        $report = new Report();
        $report->addFile($file);

        $violation = new SniffViolation($level, 'Message', $file, 1);
        $report->addMessage($violation);

        $textFormatter->display($report);

        $text = $output->fetch();
        self::assertStringContainsString($expected, $text);
    }

    /**
     * @return iterable<array-key, array{string, int}>
     */
    public function displayBlockDataProvider(): iterable
    {
        yield ['[SUCCESS] Files linted: 1, notices: 1, warnings: 0, errors: 0', SniffViolation::LEVEL_NOTICE];
        yield ['[WARNING] Files linted: 1, notices: 0, warnings: 1, errors: 0', SniffViolation::LEVEL_WARNING];
        yield ['[ERROR] Files linted: 1, notices: 0, warnings: 0, errors: 1', SniffViolation::LEVEL_ERROR];
        yield ['[ERROR] Files linted: 1, notices: 0, warnings: 0, errors: 1', SniffViolation::LEVEL_FATAL];
    }
}
