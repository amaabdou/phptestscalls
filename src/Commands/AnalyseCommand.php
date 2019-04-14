<?php declare(strict_types=1);

namespace PhpNumStats\Commands;

use PhpParser\Node;
use PhpParser\Error;
use PhpParser\ParserFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;

class AnalyseCommand extends Command
{
    protected static $defaultName = 'analyse';

    protected function configure() :void
    {
        $this
        ->addOption(
            'path',
            'p',
            InputOption::VALUE_REQUIRED,
            'Directory path contains tests'
        )->addOption(
            'namespace',
            's',
            InputOption::VALUE_OPTIONAL,
            'Name space'
        )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $path = (string) $input->getOption('path');
        $namespace = (string) $input->getOption('namespace');

        $paths = [];
        if (empty($path) || !is_dir($path)) {
            throw new \RuntimeException(sprintf('must provide valid path'));
        }

        $phpParser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
        $consoleOutStyle = new SymfonyStyle($input, $output);
        $finder = new Finder();
        $finder
            ->ignoreDotFiles(true)
            ->ignoreVCS(true)
            ->ignoreUnreadableDirs()
            ->notPath(['vendor'])
            ->name('*.php')
            ->in($path)
            ;
        foreach ($finder as $splFileInfo) {
            try {
                $stmts = $phpParser->parse(file_get_contents($splFileInfo->getRealPath()));
                $paths[$splFileInfo->getRelativePathname()] = $this->getUsedClassesFromNodeStmtArray($stmts);
            } catch (Error $error) {
                $consoleOutStyle->error(sprintf('File %s parse error: %s', $splFileInfo->getRelativePathname(), $error->getMessage()));
                continue;
            }
        }

        $paths = array_filter($paths, static function (array $usedNamespaces) use ($namespace) {
            if (empty($namespace)) {
                return true;
            }
            foreach ($usedNamespaces as $usedNamespace) {
                if (strpos($usedNamespace, $namespace) === 0) {
                    return true;
                }
            }

            return false;
        });

        foreach ($paths as $path=>$usages) {
            $consoleOutStyle->writeln($path);
            $consoleOutStyle->listing(array_unique($usages));
        }
    }

    /**
     * @param array|null $stmts
     * @return array
     */
    private function getUsedClassesFromNodeStmtArray(?array $stmts) :array
    {
        $usages = [];
        if (empty($stmts)) {
            return [];
        }

        foreach ($stmts as $stmt) {
            if ($stmt instanceof Node\Stmt\Use_) {
                foreach ($stmt->uses as $use) {
                    $usages[] = (string) $use->name;
                }
            } elseif (!empty($stmt->stmts)) {
                $usages = array_merge($usages, $this->getUsedClassesFromNodeStmtArray($stmt->stmts));
            }
        }

        return $usages;
    }
}
