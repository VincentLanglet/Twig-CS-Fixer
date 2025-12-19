<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Console\Command;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;
use TwigCsFixer\Cache\Cache;
use TwigCsFixer\Cache\CacheEncoder;
use TwigCsFixer\Cache\Signature;
use TwigCsFixer\Config\Config;
use TwigCsFixer\Console\Command\TwigCsFixerCommand;
use TwigCsFixer\Test\TestHelper;
use TwigCsFixer\Tests\FileTestCase;

final class TwigCsFixerCommandTest extends FileTestCase
{
    public function testExecuteWithSuccess(): void
    {
        $command = new TwigCsFixerCommand();

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'paths' => [$this->getTmpPath(__DIR__.'/Fixtures/file.twig')],
        ]);

        static::assertStringContainsString(
            '[OK] Files linted: 1, notices: 0, warnings: 0, errors: 0',
            $commandTester->getDisplay()
        );
        static::assertSame(Command::SUCCESS, $commandTester->getStatusCode());
    }

    public function testExecuteWithReportErrors(): void
    {
        $command = new TwigCsFixerCommand();

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'paths' => [$this->getTmpPath(__DIR__.'/Fixtures')],
        ]);

        $display = $commandTester->getDisplay();
        static::assertStringContainsString(TestHelper::getOsPath('directory/fixable/file.twig'), $display);
        static::assertStringContainsString(TestHelper::getOsPath('directory/error/file.twig'), $display);
        static::assertStringNotContainsString('DelimiterSpacing.After', $display);
        static::assertStringContainsString(
            '[ERROR] Files linted: 3, notices: 0, warnings: 0, errors: 3',
            $display
        );
        static::assertSame(Command::FAILURE, $commandTester->getStatusCode());
    }

    public function testExecuteWithReportErrorsAndDebug(): void
    {
        $command = new TwigCsFixerCommand();

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'paths' => [$this->getTmpPath(__DIR__.'/Fixtures')],
            '--debug' => true,
        ]);

        $display = $commandTester->getDisplay();
        static::assertStringContainsString(TestHelper::getOsPath('directory/fixable/file.twig'), $display);
        static::assertStringContainsString(TestHelper::getOsPath('directory/error/file.twig'), $display);
        static::assertStringContainsString('DelimiterSpacing.After', $display);
        static::assertStringContainsString(
            '[ERROR] Files linted: 3, notices: 0, warnings: 0, errors: 3',
            $display
        );
        static::assertSame(Command::FAILURE, $commandTester->getStatusCode());
    }

    public function testExecuteWithReportErrorsFixed(): void
    {
        $command = new TwigCsFixerCommand();

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'paths' => [$this->getTmpPath(__DIR__.'/Fixtures')],
            '--fix' => true,
        ]);

        $display = $commandTester->getDisplay();
        static::assertStringNotContainsString('Changed', $display);
        static::assertStringContainsString(TestHelper::getOsPath('directory/error/file.twig'), $display);
        static::assertStringContainsString(
            '[ERROR] Files linted: 3, notices: 0, warnings: 0, errors: 1',
            $display
        );
        static::assertSame(Command::FAILURE, $commandTester->getStatusCode());
    }

    public function testExecuteWithReportErrorsFixedVerbose(): void
    {
        $command = new TwigCsFixerCommand();

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'paths' => [$this->getTmpPath(__DIR__.'/Fixtures')],
            '--fix' => true,
        ], ['verbosity' => OutputInterface::VERBOSITY_VERBOSE]);

        $display = $commandTester->getDisplay();
        static::assertStringContainsString('Changed', $display);
        static::assertStringContainsString(TestHelper::getOsPath('directory/fixable/file.twig'), $display);
        static::assertStringContainsString(TestHelper::getOsPath('directory/error/file.twig'), $display);
        static::assertStringContainsString(
            '[ERROR] Files linted: 3, notices: 0, warnings: 0, errors: 1',
            $display
        );
        static::assertSame(Command::FAILURE, $commandTester->getStatusCode());
    }

    /**
     * @dataProvider aliasesDataProvider
     */
    #[DataProvider('aliasesDataProvider')]
    public function testExecuteAliases(string $alias, bool $shouldFix): void
    {
        $command = new TwigCsFixerCommand();
        $command->setApplication(new Application());

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $alias,
            'paths' => [$this->getTmpPath(__DIR__.'/Fixtures/directory/fixable')],
        ]);

        if ($shouldFix) {
            static::assertSame(Command::SUCCESS, $commandTester->getStatusCode());
            static::assertStringContainsString('OK', $commandTester->getDisplay());
        } else {
            static::assertSame(Command::FAILURE, $commandTester->getStatusCode());
            static::assertStringContainsString('KO', $commandTester->getDisplay());
        }
    }

    /**
     * @return iterable<array-key, array{string, bool}>
     */
    public static function aliasesDataProvider(): iterable
    {
        yield ['check', false];
        yield ['fix', true];
    }

    public function testExecuteWithReportOption(): void
    {
        $command = new TwigCsFixerCommand();

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'paths' => [$this->getTmpPath(__DIR__.'/Fixtures')],
            '--no-cache' => true, // To avoid cache output
            '--report' => 'null',
        ]);

        static::assertSame('', $commandTester->getDisplay());
        static::assertSame(Command::FAILURE, $commandTester->getStatusCode());
    }

    public function testExecuteWithConfig(): void
    {
        $command = new TwigCsFixerCommand();

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'paths' => [$this->getTmpPath(__DIR__.'/Fixtures')],
            '--config' => $this->getTmpPath(__DIR__.'/Fixtures/.twig-cs-fixer.php'),
        ]);

        static::assertStringContainsString(
            '[ERROR] Files linted: 3, notices: 0, warnings: 0, errors: 1',
            $commandTester->getDisplay()
        );
        static::assertSame(Command::FAILURE, $commandTester->getStatusCode());
    }

    public function testExecuteWithError(): void
    {
        $command = new TwigCsFixerCommand();

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'paths' => [$this->getTmpPath(__DIR__.'/Fixtures')],
            '--config' => $this->getTmpPath(__DIR__.'/Fixtures/.config-not-found.php'),
        ]);

        static::assertStringStartsWith('Error: Cannot find the config file', $commandTester->getDisplay());
        static::assertSame(Command::INVALID, $commandTester->getStatusCode());
    }

    public function testExecuteWithCacheByDefault(): void
    {
        $command = new TwigCsFixerCommand();

        $commandTester = new CommandTester($command);

        $path = $this->getTmpPath(__DIR__.'/Fixtures/file.twig');

        // Run two times to be sure to generate the cache.
        $commandTester->execute([
            'paths' => [$path],
        ]);
        $commandTester->execute([
            'paths' => [$path],
        ], [
            'verbosity' => OutputInterface::VERBOSITY_DEBUG,
        ]);

        static::assertStringContainsString(
            \sprintf('Using cache file "%s".', Config::DEFAULT_CACHE_PATH),
            $commandTester->getDisplay()
        );
        static::assertSame(Command::SUCCESS, $commandTester->getStatusCode());
    }

    public function testExecuteWithCacheDisabled(): void
    {
        $command = new TwigCsFixerCommand();

        $commandTester = new CommandTester($command);

        $path = $this->getTmpPath(__DIR__.'/Fixtures/file.twig');

        // Run two times to be sure to generate the cache if we were using one.
        $commandTester->execute([
            'paths' => [$path],
            '--no-cache' => true,
        ]);
        $commandTester->execute([
            'paths' => [$path],
            '--no-cache' => true,
        ], [
            'verbosity' => OutputInterface::VERBOSITY_DEBUG,
        ]);

        static::assertStringNotContainsString(
            'Using cache file',
            $commandTester->getDisplay()
        );
        static::assertSame(Command::SUCCESS, $commandTester->getStatusCode());
    }

    public function testExecuteWithCacheFile(): void
    {
        $command = new TwigCsFixerCommand();

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'paths' => [$this->getTmpPath(__DIR__.'/Fixtures')],
            '--config' => $this->getTmpPath(__DIR__.'/Fixtures/.twig-cs-fixer-with-cache.php'),
        ]);

        // Result with no ruleset
        static::assertStringContainsString(
            '[ERROR] Files linted: 3, notices: 0, warnings: 0, errors: 1',
            $commandTester->getDisplay()
        );

        $cachePath = $this->getTmpPath(__DIR__.'/Fixtures/.twig-cs-fixer.cache');
        $cacheContent = file_get_contents($cachePath);
        static::assertNotFalse($cacheContent);

        // Save the hashes in order to rewrite manually the cache later
        $hashes = CacheEncoder::fromJson($cacheContent)->getHashes();

        $commandTester->execute([
            'paths' => [$this->getTmpPath(__DIR__.'/Fixtures')],
            '--config' => $this->getTmpPath(__DIR__.'/Fixtures/.twig-cs-fixer-with-cache2.php'),
        ]);

        // Result with standard ruleset
        // It's different even with the same cache file
        static::assertStringContainsString(
            '[ERROR] Files linted: 3, notices: 0, warnings: 0, errors: 3',
            $commandTester->getDisplay()
        );

        $cacheContent = file_get_contents($cachePath);
        static::assertNotFalse($cacheContent);

        $cache = CacheEncoder::fromJson($cacheContent);
        foreach ($hashes as $file => $hash) {
            $cache->set($file, $hash);
        }

        // Save the signature for later tests
        $signature = $cache->getSignature();

        // We're manually rewriting the cache in order to simulate valid files
        file_put_contents($cachePath, CacheEncoder::toJson($cache));

        $commandTester->execute([
            'paths' => [$this->getTmpPath(__DIR__.'/Fixtures')],
            '--config' => $this->getTmpPath(__DIR__.'/Fixtures/.twig-cs-fixer-with-cache2.php'),
        ]);

        // We get the same result as with no ruleset because of the cache
        static::assertStringContainsString(
            '[ERROR] Files linted: 3, notices: 0, warnings: 0, errors: 1',
            $commandTester->getDisplay()
        );

        $newCache = new Cache(new Signature(
            '0',
            $signature->getFixerVersion(),
            $signature->getRules(),
        ));
        foreach ($hashes as $file => $hash) {
            $newCache->set($file, $hash);
        }

        // We're manually rewriting the cache with a different php version
        file_put_contents($cachePath, CacheEncoder::toJson($newCache));

        $commandTester->execute([
            'paths' => [$this->getTmpPath(__DIR__.'/Fixtures')],
            '--config' => $this->getTmpPath(__DIR__.'/Fixtures/.twig-cs-fixer-with-cache2.php'),
        ]);

        // We get back the real result because of the different php version
        static::assertStringContainsString(
            '[ERROR] Files linted: 3, notices: 0, warnings: 0, errors: 3',
            $commandTester->getDisplay()
        );

        $newCache = new Cache(new Signature(
            $signature->getPhpVersion(),
            '0',
            $signature->getRules(),
        ));
        foreach ($hashes as $file => $hash) {
            $newCache->set($file, $hash);
        }

        // We're manually rewriting the cache with a different fixer version
        file_put_contents($cachePath, CacheEncoder::toJson($newCache));

        $commandTester->execute([
            'paths' => [$this->getTmpPath(__DIR__.'/Fixtures')],
            '--config' => $this->getTmpPath(__DIR__.'/Fixtures/.twig-cs-fixer-with-cache2.php'),
        ]);

        // We get back the real result because of the different fixer version
        static::assertStringContainsString(
            '[ERROR] Files linted: 3, notices: 0, warnings: 0, errors: 3',
            $commandTester->getDisplay()
        );
    }
}
