<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Report\Reporter;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use TwigCsFixer\Report\Report;
use TwigCsFixer\Report\Reporter\GitlabReporter;
use TwigCsFixer\Report\Violation;
use TwigCsFixer\Report\ViolationId;
use TwigCsFixer\Test\TestHelper;

final class GitlabReporterTest extends TestCase
{
    public function testGetName(): void
    {
        static::assertSame(GitlabReporter::NAME, (new GitlabReporter())->getName());
    }

    /**
     * @dataProvider displayDataProvider
     */
    public function testDisplayErrors(string $expected, ?string $level, bool $debug): void
    {
        $textFormatter = new GitlabReporter();

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
            new ViolationId('WarningId', null, 2, 22)
        );
        $report->addViolation($violation1);
        $violation2 = new Violation(
            Violation::LEVEL_ERROR,
            'Error',
            $file,
            'Rule',
            new ViolationId('ErrorId', null, 3, 33)
        );
        $report->addViolation($violation2);
        $violation3 = new Violation(
            Violation::LEVEL_FATAL,
            'Fatal'."\n".'with new line',
            $file,
            'Rule',
            new ViolationId('FatalId')
        );
        $report->addViolation($violation3);

        $output = new BufferedOutput(OutputInterface::VERBOSITY_NORMAL, true);
        $textFormatter->display($output, $report, $level, $debug);

        $text = $output->fetch();
        static::assertJsonStringEqualsJsonString($expected, $text);
    }

    /**
     * @return iterable<array-key, array{string, string|null, bool}>
     *
     * @throws \JsonException
     */
    public static function displayDataProvider(): iterable
    {
        $path = TestHelper::getOsPath('tests/Report/Reporter/Fixtures/file.twig');

        yield [
            json_encode([
                [
                    'description' => 'Notice',
                    'check_name' => 'Rule',
                    'fingerprint' => '3500f606b8f132de65826104a5765c2f',
                    'severity' => 'info',
                    'location' => [
                        'path' => $path,
                        'lines' => [
                            'begin' => 1,
                        ],
                    ],
                ],
                [
                    'description' => 'Warning',
                    'check_name' => 'Rule',
                    'fingerprint' => '50e9d055100fe5918856eac2a08387b3',
                    'severity' => 'minor',
                    'location' => [
                        'path' => $path,
                        'lines' => [
                            'begin' => 2,
                        ],
                    ],
                ],
                [
                    'description' => 'Error',
                    'check_name' => 'Rule',
                    'fingerprint' => '5b44cfe84bddc0f595bfa90b375b85fb',
                    'severity' => 'major',
                    'location' => [
                        'path' => $path,
                        'lines' => [
                            'begin' => 3,
                        ],
                    ],
                ],
                [
                    'description' => "Fatal\nwith new line",
                    'check_name' => 'Rule',
                    'fingerprint' => '05012bba79588cade3a7c78b288d023c',
                    'severity' => 'critical',
                    'location' => [
                        'path' => $path,
                        'lines' => [
                            'begin' => 1,
                        ],
                    ],
                ],
            ], \JSON_THROW_ON_ERROR),
            null,
            false,
        ];

        yield [
            json_encode([
                [
                    'description' => 'NoticeId:1 -- Notice',
                    'check_name' => 'Rule',
                    'fingerprint' => '3500f606b8f132de65826104a5765c2f',
                    'severity' => 'info',
                    'location' => [
                        'path' => $path,
                        'lines' => [
                            'begin' => 1,
                        ],
                    ],
                ],
                [
                    'description' => 'WarningId:2:22 -- Warning',
                    'check_name' => 'Rule',
                    'fingerprint' => '50e9d055100fe5918856eac2a08387b3',
                    'severity' => 'minor',
                    'location' => [
                        'path' => $path,
                        'lines' => [
                            'begin' => 2,
                        ],
                    ],
                ],
                [
                    'description' => 'ErrorId:3:33 -- Error',
                    'check_name' => 'Rule',
                    'fingerprint' => '5b44cfe84bddc0f595bfa90b375b85fb',
                    'severity' => 'major',
                    'location' => [
                        'path' => $path,
                        'lines' => [
                            'begin' => 3,
                        ],
                    ],
                ],
                [
                    'description' => "FatalId -- Fatal\nwith new line",
                    'check_name' => 'Rule',
                    'fingerprint' => '05012bba79588cade3a7c78b288d023c',
                    'severity' => 'critical',
                    'location' => [
                        'path' => $path,
                        'lines' => [
                            'begin' => 1,
                        ],
                    ],
                ],
            ], \JSON_THROW_ON_ERROR),
            null,
            true,
        ];
    }
}
