<?php

declare(strict_types=1);

use Maispace\MaiBase\TableConfigurationArray\FieldConfig\DatetimeConfig;
use Maispace\MaiBase\TableConfigurationArray\FieldConfig\EmailConfig;
use Maispace\MaiBase\TableConfigurationArray\FieldConfig\FileConfig;
use Maispace\MaiBase\TableConfigurationArray\FieldConfig\InputConfig;
use Maispace\MaiBase\TableConfigurationArray\FieldConfig\SelectSingleConfig;
use Maispace\MaiBase\TableConfigurationArray\FieldConfig\TextConfig;
use Maispace\MaiBase\TableConfigurationArray\Helper;
use Maispace\MaiBase\TableConfigurationArray\Table;

$lang = Helper::localLangHelperFactory('mai_jobs', 'Default/locallang_tca.xlf');

return (new Table($lang('table.tx_maijobs_application')))
    ->setDefaultConfig()
    ->setLabel('last_name')
    ->setAlternativeLabelFields('first_name, email')
    ->appendAlternativeLabelToLabel()
    ->setIconFile('EXT:mai_jobs/Resources/Public/Icons/tx_maijobs_application.svg')
    ->setDefaultSorting('ORDER BY submitted_at DESC')
    ->addColumn(
        'first_name',
        $lang('tx_maijobs_application.first_name'),
        (new InputConfig())->setSize(30)->setMax(100)->setEval('trim')->setRequired()
    )
    ->addColumn(
        'last_name',
        $lang('tx_maijobs_application.last_name'),
        (new InputConfig())->setSize(30)->setMax(100)->setEval('trim')->setRequired()
    )
    ->addColumn(
        'email',
        $lang('tx_maijobs_application.email'),
        (new EmailConfig())->setRequired()
    )
    ->addColumn(
        'message',
        $lang('tx_maijobs_application.message'),
        (new TextConfig())->setRows(8)->setCols(50)->setEval('trim')
    )
    ->addColumn(
        'cv',
        $lang('tx_maijobs_application.cv'),
        (new FileConfig())
            ->setAllowed('pdf,doc,docx,odt')
            ->setMaxItems(1)
            ->setAppearance([
                'createNewRelationLinkTitle' => $lang('tx_maijobs_application.cv.addFile'),
            ])
    )
    ->addColumn(
        'status',
        $lang('tx_maijobs_application.status'),
        (new SelectSingleConfig())
            ->setItems([
                ['label' => $lang('tx_maijobs_application.status.pending'), 'value' => 'pending'],
                ['label' => $lang('tx_maijobs_application.status.reviewed'), 'value' => 'reviewed'],
                ['label' => $lang('tx_maijobs_application.status.accepted'), 'value' => 'accepted'],
                ['label' => $lang('tx_maijobs_application.status.rejected'), 'value' => 'rejected'],
            ])
            ->setDefault('pending')
    )
    ->addColumn(
        'submitted_at',
        $lang('tx_maijobs_application.submitted_at'),
        (new DatetimeConfig())->setFormat('datetime')->setReadOnly()
    )
    ->addColumn(
        'job',
        $lang('tx_maijobs_application.job'),
        (new SelectSingleConfig())
            ->setForeignTable('tx_maijobs_job')
            ->setForeignTableWhere('ORDER BY tx_maijobs_job.title')
            ->setItems([['label' => '', 'value' => 0]])
            ->setMinItems(0)
            ->setMaxItems(1)
    )
    ->addPalette(
        'name',
        $lang('palette.name'),
        'first_name, last_name'
    )
    ->addTypeShowItem(
        '0',
        '--palette--;;name, email, message, cv,
        --div--;' . $lang('tab.workflow') . ', status, submitted_at, job,
        --div--;' . $lang('tab.access') . ', --palette--;;hidden'
    )
    ->getConfig();
