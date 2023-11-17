<?php

namespace Martenasoft\Bundlemaker\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use function Symfony\Component\String\u;

class BundleMakerCommand extends Command
{
    protected static $defaultName = 'ms:make:bundle';
    protected static $defaultDescription = 'Bundle Maker';
    private $packageName;

    private $bundleName;

    private $filesystem;

    private $templatePath;

    private $path;

    private $force = false;

    private $isEscapeExists = false;
    private $bundlePath = false;

    public function __construct(Filesystem $filesystem, string $name = null)
    {
        $this->filesystem = $filesystem;
        $this->templatePath = __DIR__ . '/../../templates';
        parent::__construct($name);
    }

    public static function getCommandName(): string
    {
        return self::$defaultName;
    }

    /**
     * @return string
     */
    public static function getDefaultDescription(): string
    {
        return self::$defaultDescription;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('package_name',
                InputArgument::REQUIRED,
                "Choose a name for your Package (e.g. <fg=yellow;options=bold>MyPackage</>/<fg=gray>MyBundle</>)"
            )
            ->addOption('path',
                null,
                InputOption::VALUE_OPTIONAL,
                'Save new bundle path (bundle as default)',
                'bundles'
            )
            ->addOption('force',
                null,
                InputOption::VALUE_NEGATABLE,
                'Rewrite old file if exists (default is false)',
                false
            )
            ->addOption('escape-exists',
                null,
                InputOption::VALUE_NEGATABLE,
                'Escape exists file(default is false)',
                false
            )
            ->addOption('command',
                null,
                InputOption::VALUE_NEGATABLE,
                'Without command'
            )
            ->addOption('controller',
                null,
                InputOption::VALUE_NEGATABLE,
                'Without controller'
            );

    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $packageName = $input->getArgument('package_name');
        $packageName = preg_split("/\/|\\\/", $packageName);

        $isCommand = $input->getOption('command');
        $isController = $input->getOption('controller');

        $this->path = $input->getOption('path');
        $this->force = $input->getOption('force');
        $this->isEscapeExists = $input->getOption('escape-exists');

        if (empty($packageName[1])) {
            throw new \Exception("Choose a name for your Package (e.g. MyPackage/MyBundle)");
        }

        $this->packageName = u($packageName[0])->trim()->title()->toString();
        $this->bundleName = u($packageName[1])->trim()->title()->toString();

        $this->bundlePath = $this->path .
            DIRECTORY_SEPARATOR .
            u($this->packageName)->snake()->toString() .
            DIRECTORY_SEPARATOR .
            u($this->bundleName)->snake()->toString();

        $this->addBundle();
        $this->addConfigService();
        $this->addDependencyInjection();


        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion("Do create command? [Y/n]:", true);

        if ($isCommand === true || ($isCommand === null && $helper->ask($input, $output, $question))) {
            $this->addCommand();
        }


        $question = new ConfirmationQuestion("Do create controller? [Y/n]:", true);
        if ($isController === true ||
            ($isController === null && ($isController = $helper->ask($input, $output, $question)))) {
            $this->addRoutesService();
            $this->addController();
        }

        $this->afterSave($isController, $output);

        return Command::SUCCESS;
    }



    private function addBundle()
    {
        $this->copyFromTemplate(
            $this->templatePath . DIRECTORY_SEPARATOR . '__REPLACE_CLASS_NAME__Bundle.php',
            $this->bundlePath .
            DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . $this->packageName . $this->bundleName . 'Bundle.php',
            function ($content) {
                $result = str_replace(
                    [
                        '__REPLACE_NAMESPACE__',
                        '__REPLACE_CLASS_NAME__'
                    ],
                    [
                        $this->packageName . '\\' . $this->bundleName,
                        $this->packageName . $this->bundleName
                    ],
                    $content);

                return $result;
            }
        );
    }

    private function addDependencyInjection()
    {
        $this->copyFromTemplate(
            $this->templatePath .
            DIRECTORY_SEPARATOR .
            'DependencyInjection' .
            DIRECTORY_SEPARATOR .
            '__REPLACE_CLASS_NAME__Extension.php',

            $this->bundlePath .
            DIRECTORY_SEPARATOR .
            'src' .
            DIRECTORY_SEPARATOR .
            'DependencyInjection' .
            DIRECTORY_SEPARATOR .
            $this->packageName .
            $this->bundleName .
            'Extension.php',

            function ($content) {
                $result = str_replace(
                    [
                        '__REPLACE_NAMESPACE__',
                        '__REPLACE_CLASS_NAME__'
                    ],
                    [
                        $this->packageName . '\\' . $this->bundleName ,
                        $this->packageName . $this->bundleName
                    ],
                    $content);

                return $result;
            }
        );


        $this->copyFromTemplate(
            $this->templatePath .
            DIRECTORY_SEPARATOR .
            'DependencyInjection' .
            DIRECTORY_SEPARATOR .
            'Configuration.php',

            $this->bundlePath .
            DIRECTORY_SEPARATOR .
            'src' .
            DIRECTORY_SEPARATOR .
            'DependencyInjection' .
            DIRECTORY_SEPARATOR .
            'Configuration.php',

            function ($content) {
                $result = str_replace(
                    [
                        '__REPLACE_NAMESPACE__',
                        '__REPLACE_NAMESPACE_FOR_CONFIG_NAME__'
                    ],
                    [
                        $this->packageName . '\\' . $this->bundleName ,
                        u($this->packageName)->snake()->toString() .
                        '_' .
                        u($this->bundleName)->snake()->toString()
                    ],
                    $content);

                return $result;
            }
        );
    }

    private function addConfigService()
    {
        $path = DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'services.yaml';

        $this->copyFromTemplate(
            $this->templatePath . $path,
            $this->bundlePath . $path,
            function ($content) {
                $result = str_replace(
                    ['__REPLACE_NAMESPACE__'],
                    [$this->packageName . '\\' . $this->bundleName],
                    $content
                );

                return $result;
            });
    }

    private function addRoutesService()
    {
        $path = DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'routes.yaml';

        $this->copyFromTemplate(
            $this->templatePath . $path,
            $this->bundlePath . $path,
            function ($content) {
                $result = str_replace(
                    [
                        '__REPLACE_NAMESPACE__',
                        '__REPLACE_ROUTE__',
                        '__REPLACE_ROUTE_NAME__'
                    ],

                    [
                        $this->packageName . '\\' . $this->bundleName,
                        u($this->packageName . '/' . $this->bundleName)->lower()->toString(),
                        u($this->packageName . '_' . $this->bundleName)->lower()->toString(),
                    ],
                    $content
                );

                return $result;
            });
    }
    private function addCommand()
    {
        $path = DIRECTORY_SEPARATOR . 'Command' . DIRECTORY_SEPARATOR . 'DefaultCommand.php';

        $this->copyFromTemplate(
            $this->templatePath . $path,
            $this->bundlePath . DIRECTORY_SEPARATOR . 'src' . $path,
            function ($content) {
                $result = str_replace(['__REPLACE_NAMESPACE__', '__REPLACE_RENDER_NAMESPACE__'],
                    [$this->packageName . '\\' . $this->bundleName . '\\Command', $this->bundleName],
                    $content);

                return $result;
            }
        );
    }
    private function addController()
    {
        $path = DIRECTORY_SEPARATOR . 'Controller' . DIRECTORY_SEPARATOR . 'DefaultController.php';

        $this->copyFromTemplate(
            $this->templatePath . $path,
            $this->bundlePath . DIRECTORY_SEPARATOR . 'src' . $path,
            function ($content) {
                $result = str_replace(
                    [
                        '__REPLACE_NAMESPACE__',
                        '__REPLACE_RENDER_NAMESPACE__'
                    ],

                    [
                        $this->packageName . '\\' . $this->bundleName . '\\Controller',
                        $this->packageName.$this->bundleName
                    ],
                    $content
                );

                return $result;
            }
        );

        $template = DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'base.html.twig';

        $this->copyFromTemplate(
            $this->templatePath . $template,
            $this->bundlePath . $template
        );

        $template = DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'default' . DIRECTORY_SEPARATOR . 'index.html.twig';

        $this->copyFromTemplate(
            $this->templatePath . $template,
            $this->bundlePath . $template
        );

    }

    private function copyFromTemplate(string $pathFrom, string $pathTo, ?callable $replace = null): void
    {
        if (!$this->filesystem->exists($pathFrom)) {
            throw new \Exception("Template [ $pathFrom ] not found!");
        }

        if ($this->filesystem->exists($pathTo)) {
            if ($this->force) {
                $this->filesystem->remove($pathTo);
            } elseif (!$this->isEscapeExists) {
                throw new \Exception("File [ $pathTo ] already exists.");
            }
        }

        $newFileContent = file_get_contents($pathFrom);

        if ($replace !== null) {
            $newFileContent = $replace($newFileContent) ?? $newFileContent;
        }

        $dir = pathinfo($pathTo)['dirname'];
        $this->filesystem->mkdir($dir);
        file_put_contents($pathTo, $newFileContent);
    }

    private function getDir()
    {
        return
            $this->path .
            DIRECTORY_SEPARATOR .
            u($this->packageName)
                ->snake() .
            DIRECTORY_SEPARATOR .
            u($this->bundleName)
                ->snake();
    }

    private function afterSave(bool $isController, OutputInterface $output)
    {

        $messages = [
            "<info>Bundle {$this->packageName}/{$this->bundleName} was added</info>",

            "1. Add record to composer.json\n",
            '"psr-4": {',
            '   ...',
            "   \"" . $this->packageName . '\\\\' . $this->bundleName . '\\\\" : "' . $this->bundlePath . '/src/"',
            '   ...',
            "\n2. run composer install\n",
            "composer install",
            "\n3. Add record to file config/bundles.php\n",
            "return [",
            "   ...",
            "   " . $this->packageName . '\\' . $this->bundleName . '\\' . $this->packageName . $this->bundleName . 'Bundle::class => ["all" => true]',
            "   ...",

        ];

        if ($isController) {
            $messages[] = "\n4. Add route file: config/routes/" . u($this->packageName . '_' . $this->bundleName)->snake() . ".yaml\n";
            $messages[] = u($this->packageName . '_' . $this->bundleName)->snake() . ':';
            $messages[] = ' resource: "@' . $this->packageName . $this->bundleName . 'Bundle/config/routes.yaml"';
        }

        $messages[] = "\n\n----------------------------------------------------------------------------------";

        foreach ($messages as $message) {
            $output->writeln($message);
        }

    }

}
