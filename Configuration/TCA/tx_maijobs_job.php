<?php

declare(strict_types=1);

use Maispace\MaiBase\TableConfigurationArray\FieldConfig\CategoryConfig;
use Maispace\MaiBase\TableConfigurationArray\FieldConfig\DatetimeConfig;
use Maispace\MaiBase\TableConfigurationArray\FieldConfig\InputConfig;
use Maispace\MaiBase\TableConfigurationArray\FieldConfig\SelectSingleConfig;
use Maispace\MaiBase\TableConfigurationArray\FieldConfig\TextConfig;
use Maispace\MaiBase\TableConfigurationArray\Helper;
use Maispace\MaiBase\TableConfigurationArray\Table;

$lang = Helper::localLangHelperFactory('mai_jobs', 'Default/locallang_tca.xlf');

return (new Table($lang('table.tx_maijobs_job')))
    ->setSearchFields('title,description')
    ->setDefaultConfig()
    ->setLabel('title')
    ->setIconFile('EXT:mai_jobs/Resources/Public/Icons/tx_maijobs_job.svg')
    ->setSortingField()
    ->addColumn(
        'title',
        $lang('tx_maijobs_job.title'),
        (new InputConfig())->setSize(50)->setMax(255)->setEval('trim')->setRequired()
    )
    ->addColumn(
        'description',
        $lang('tx_maijobs_job.description'),
        (new TextConfig())->setRows(10)->setCols(50)->enableRte()->setRichtextConfiguration('default')->setRequired()
    )
    ->addColumn(
        'requirements',
        $lang('tx_maijobs_job.requirements'),
        (new TextConfig())->setRows(8)->setCols(50)->enableRte()->setRichtextConfiguration('default')
    )
    ->addColumn(
        'deadline',
        $lang('tx_maijobs_job.deadline'),
        (new DatetimeConfig())->setFormat('date')
    )
    ->addColumn(
        'status',
        $lang('tx_maijobs_job.status'),
        (new SelectSingleConfig())
            ->setItems([
                ['label' => $lang('tx_maijobs_job.status.open'), 'value' => 'open'],
                ['label' => $lang('tx_maijobs_job.status.filled'), 'value' => 'filled'],
                ['label' => $lang('tx_maijobs_job.status.closed'), 'value' => 'closed'],
            ])
            ->setDefault('open')
    )
    ->addColumn(
        'categories',
        $lang('tx_maijobs_job.categories'),
        new CategoryConfig()
    )
    ->addTypeShowItem(
        '0',
        'title, description, requirements, deadline,
        --div--;' . $lang('tab.meta') . ', status, categories,
        --div--;' . $lang('tab.language') . ', --palette--;;language,
        --div--;' . $lang('tab.access') . ', --palette--;;hidden, --palette--;;access'
    )
    ->getConfig();
