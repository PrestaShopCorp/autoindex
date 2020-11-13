<?php
/**
 * 2007-2010 PrestaShop and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2010 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

namespace PrestaShop\AutoIndex\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class AddAutoIndexCommand extends Command
{
    const DEFAULT_FILTERS = [];

    /**
     * List of folders to exclude from the search
     *
     * @param array $filters
     */
    private $filters;

    protected function configure()
    {
        $this
        ->setName('prestashop:add:index')
        ->setDescription('Automatically add an "index.php" in all your directories or your zip file recursively')
        ->addArgument(
            'real_path',
            InputArgument::OPTIONAL,
            'The real path of your module'
        )
        ->addOption(
            'exclude',
            null,
            InputOption::VALUE_REQUIRED,
            'Comma-separated list of folders to exclude from the update',
            implode(',', self::DEFAULT_FILTERS)
        );
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->filters = explode(',', $input->getOption('exclude'));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $realPath = $input->getArgument('real_path');
        if ($realPath !== null) {
            $dir = $realPath;
        } else {
            $dir = getcwd();
        }

        $source = __DIR__ . '/../../assets/index.php';

        if ($dir === false) {
            throw new \Exception('Could not get current directory. Check your permissions.');
        }

        $finder = new Finder();
        $finder
            ->directories()
            ->in($dir)
            ->ignoreDotFiles(false)
            ->exclude($this->filters);

        $output->writeln('Updating directories in ' . $dir . ' folder ...');
        $progress = new ProgressBar($output, count($finder));
        $progress->start();

        foreach ($finder as $file) {
            $newfile = $file->getRealPath() . '/index.php';
            if (!file_exists($newfile)) {
                if (!copy($source, $newfile)) {
                    $output->writeln('Cannot add index file in ' . strtoupper($newfile));
                }
            }
            $progress->advance();
        }

        $progress->finish();
        $output->writeln('');
    }
}
