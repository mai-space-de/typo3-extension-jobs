<?php

declare(strict_types=1);

defined('TYPO3') or die();

use Maispace\MaiBase\Backend\CustomPageModule\DoktypeIds;
use Maispace\MaiBase\TableConfigurationArray\Field;
use Maispace\MaiBase\TableConfigurationArray\FieldConfig\DatetimeConfig;
use Maispace\MaiBase\TableConfigurationArray\FieldConfig\NumberConfig;
use Maispace\MaiBase\TableConfigurationArray\FieldConfig\SelectSingleConfig;
use Maispace\MaiBase\TableConfigurationArray\FieldConfig\TextConfig;
use Maispace\MaiBase\TableConfigurationArray\Helper;
use Maispace\MaiBase\TableConfigurationArray\Palette;

$lang = Helper::localLangHelperFactory('mai_jobs', 'Default/locallang_tca.xlf');
$jobDoktype = (string) DoktypeIds::JOB;

(new Field('pages', 'tx_maijobs_description', $lang('tx_maijobs_job.description'), true))
    ->setConfig(
        (new TextConfig())
            ->setRows(10)
            ->setCols(50)
            ->enableRte()
            ->setRichtextConfiguration('default')
            ->setRequired()
    )
    ->registerField();

(new Field('pages', 'tx_maijobs_requirements', $lang('tx_maijobs_job.requirements'), true))
    ->setConfig(
        (new TextConfig())
            ->setRows(8)
            ->setCols(50)
            ->enableRte()
            ->setRichtextConfiguration('default')
    )
    ->registerField();

(new Field('pages', 'tx_maijobs_deadline', $lang('tx_maijobs_job.deadline'), true))
    ->setConfig((new DatetimeConfig())->setFormat('date'))
    ->registerField();

(new Field('pages', 'tx_maijobs_status', $lang('tx_maijobs_job.status'), true))
    ->setConfig(
        (new SelectSingleConfig())
            ->setItems([
                ['label' => $lang('tx_maijobs_job.status.open'), 'value' => 'open'],
                ['label' => $lang('tx_maijobs_job.status.filled'), 'value' => 'filled'],
                ['label' => $lang('tx_maijobs_job.status.closed'), 'value' => 'closed'],
            ])
            ->setDefault('open')
    )
    ->registerField();

// Migration idempotency marker (hidden from editors).
(new Field('pages', 'tx_maijobs_legacy_job_uid', $lang('tx_maijobs_job.legacy_job_uid'), true))
    ->setConfig((new NumberConfig())->setFormat('integer')->setDefault(0)->setReadOnly())
    ->registerField();

(new Palette('pages', 'tx_maijobs_meta', $lang('palette.jobMeta')))
    ->override('tx_maijobs_status, tx_maijobs_deadline');

$jobTabs = ',--div--;' . $lang('tab.job')
    . ',--palette--;;tx_maijobs_meta,'
    . 'tx_maijobs_description,tx_maijobs_requirements';

$showitem = (string) ($GLOBALS['TCA']['pages']['types'][$jobDoktype]['showitem'] ?? '');
if ($showitem !== '' && !str_contains($showitem, 'tx_maijobs_description')) {
    $GLOBALS['TCA']['pages']['types'][$jobDoktype]['showitem'] = preg_replace(
        '/(--palette--;;title)/',
        '$1' . $jobTabs,
        $showitem,
        1,
    ) ?? ($showitem . $jobTabs);
}

Helper::overrideColumns('pages', $jobDoktype, 'nav_hide', [
    'config' => [
        'default' => 1,
    ],
]);
