<?php

namespace KayStrobach\PageTreeFixer\Service;

use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Exception\Page\PageNotFoundException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\RootlineUtility;

/**
 * Created by kay.
 */
class FixOrphanedPagesService
{
    /**
     * @var OutputInterface
     */
    protected OutputInterface $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function run()
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('pages');
        $queryBuilder->getRestrictions()
            ->removeAll()
            ->add(GeneralUtility::makeInstance(DeletedRestriction::class));

        $query = $queryBuilder
            ->select('pid', 'uid')
            ->from('pages')
            ->where(
                $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0, \PDO::PARAM_INT))
            );
        $pages = $query->execute();

        $this->output->writeln('Found ' . $pages->rowCount() . ' pages to be checked');

        foreach ($pages as $page) {
            try {
                $rootline = GeneralUtility::makeInstance(RootlineUtility::class, $page['uid']);
                $rootlinePages = $rootline->get();
                $this->output->writeln('  ✓  ' . $page['uid']);
            } catch (PageNotFoundException $e) {
                $this->output->writeln(' --> ' . $page['uid'] . ' has no connection to rootline');

                $updateQueryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('pages');
                $updateQueryBuilder
                    ->update('pages')
                    ->where(
                        $updateQueryBuilder->expr()->eq(
                            'uid',
                            $updateQueryBuilder->createNamedParameter($page['uid'], \PDO::PARAM_INT)
                        )
                    )
                    ->set('deleted', $updateQueryBuilder->createNamedParameter(1, \PDO::PARAM_INT), false);
                $updateQueryBuilder->execute();
            }
        }
    }
}
