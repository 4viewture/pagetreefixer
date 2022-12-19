<?php

return [
    'pagetreefixer:fixorphanedpages' => [
        'class' => \KayStrobach\PageTreeFixer\Command\FixOrphanedPagesCommand::class,
        'schedulable' => false,
    ],
];
