<?php
namespace KayStrobach\PageTreeFixer\Updates;

use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Backend\Tree\Repository\PageTreeRepository;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\WorkspaceRestriction;
use TYPO3\CMS\Core\Exception\Page\PageNotFoundException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\RootlineUtility;
use TYPO3\CMS\Install\Updates\ChattyInterface;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

class FixOrphanedPagesWizard implements ChattyInterface, UpgradeWizardInterface
{
    /**
     * @var OutputInterface
     */
    protected $output;

    protected $deletedPagesBuffer = [];

    protected $visiblePagesBuffer = [];


    public function setOutput(OutputInterface $output): void
    {
        $this->output = $output;
    }

    /**
     * Return the identifier for this wizard
     * This should be the same string as used in the ext_localconf class registration
     *
     * @return string
     */
    public function getIdentifier(): string
    {
        return 'fixOrphanedPagesWizard';
    }

    /**
     * Return the speaking name of this wizard
     *
     * @return string
     */
    public function getTitle(): string
    {
        return 'pagetreefixer';
    }

    /**
     * Return the description for this wizard
     *
     * @return string
     */
    public function getDescription(): string
    {
        return 'Fixes broken root pathes, by marking pages records without a valid rootline as deleted';
    }

    /**
     * Execute the update
     *
     * Called when a wizard reports that an update is necessary
     *
     * @return bool
     */
    public function executeUpdate(): bool
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

        return true;
    }

    /**
     * Is an update necessary?
     *
     * Is used to determine whether a wizard needs to be run.
     * Check if data for migration exists.
     *
     * @return bool
     */
    public function updateNecessary(): bool
    {
        return true;
    }

    /**
     * Returns an array of class names of prerequisite classes
     *
     * This way a wizard can define dependencies like "database up-to-date" or
     * "reference index updated"
     *
     * @return string[]
     */
    public function getPrerequisites(): array
    {
        return [
            DatabaseUpdatedPrerequisite::class,
        ];
    }
}
