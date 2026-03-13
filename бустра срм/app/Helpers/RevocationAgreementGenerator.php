<?php

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Style\Language;
use PhpOffice\PhpWord\Style\Table as TableStyle;
use PhpOffice\PhpWord\SimpleType\JcTable;

require_once __DIR__ . '/../../vendor/autoload.php';

class RevocationAgreementGenerator
{
    private function ruDate(?string $date = null): string
    {
        $ts = $date ? strtotime($date) : time();
        $months = [
            1 => 'января', 2 => 'февраля', 3 => 'марта', 4 => 'апреля',
            5 => 'мая', 6 => 'июня', 7 => 'июля', 8 => 'августа',
            9 => 'сентября', 10 => 'октября', 11 => 'ноября', 12 => 'декабря',
        ];
        $d = (int)date('j', $ts);
        $m = (int)date('n', $ts);
        $y = date('Y', $ts);
        return "{$d} {$months[$m]} {$y} года";
    }

    private function money($n): string
    {
        return number_format((float)$n, 2, ',', ' ');
    }

    public function generate(
        array   $rows,
        string  $agreementNumber = 'ЗСР',
        string  $agreementDate = '31.07.2024',
        ?string $docDate = null
    ): string
    {
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Times New Roman');
        $phpWord->setDefaultFontSize(10);

        if (class_exists(Language::class)) {
            $phpWord->getSettings()->setThemeFontLang(new Language(Language::RU_RU));
        }

        $section = $phpWord->addSection([
            'orientation' => 'landscape',
            'marginLeft' => 400,
            'marginRight' => 400,
            'marginTop' => 400,
            'marginBottom' => 400,
        ]);

        $docDate = $docDate ? $this->ruDate($docDate) : $this->ruDate();

        $row = (array)($rows[0] ?? []);
        $agreementNumberResolved = trim((string)($row['cession_number'] ?? $agreementNumber));
        $agreementDateResolved = trim((string)($row['cession_date'] ?? $agreementDate));
        $counterparty = trim((string)($row['counterparty'] ?? 'Сириус'));
        $cedent = trim((string)($row['cedent'] ?? 'Алфавит'));

        $counterparty_data = $row['counterparty_data'] ?? null;

        if (is_object($counterparty_data)) {
            $counterpartyDirectorName = $counterparty_data->director_name ?? 'Пантюхина Л.В.';
            $counterpartyDirectorPosition = $counterparty_data->director_position ?? 'Директор';
        } elseif (is_array($counterparty_data)) {
            $counterpartyDirectorName = $counterparty_data['director_name'] ?? 'Пантюхина Л.В.';
            $counterpartyDirectorPosition = $counterparty_data['director_position'] ?? 'Директор';
        } else {
            $counterpartyDirectorName = 'Пантюхина Л.В.';
            $counterpartyDirectorPosition = 'Директор';
        }

        $section->addText(
            'ДОПОЛНИТЕЛЬНОЕ СОГЛАШЕНИЕ к',
            ['bold' => true, 'size' => 14],
            ['alignment' => 'center', 'spaceAfter' => 0]
        );
        $section->addText(
            "Договору № {$agreementNumberResolved} от {$agreementDateResolved} г.",
            ['bold' => true, 'size' => 14],
            ['alignment' => 'center', 'spaceAfter' => 0]
        );
        $section->addText(
            'уступки права требования (цессии)',
            ['bold' => true, 'size' => 14],
            ['alignment' => 'center', 'spaceAfter' => 200]
        );

        $hdr = $section->addTable([
            'borderSize' => 0,
            'cellMargin' => 0,
            'alignment' => JcTable::CENTER,
            'width' => 11000,
        ]);
        $hdr->addRow();
        $hdr->addCell(8000)->addText('г. Самара', [], ['alignment' => 'left']);
        $hdr->addCell(3000)->addText($docDate, [], ['alignment' => 'left']);

        $section->addTextBreak();

        $pStyle = ['alignment' => 'both', 'spaceAfter' => 120, 'lineHeight' => 1.0];

        $r1 = $section->addTextRun($pStyle);
        $r1->addText('Общество с ограниченной ответственностью «' . $cedent . '»', ['bold' => true]);
        $r1->addText(', именуемое в дальнейшем «Цедент», в лице генерального директора Татарских Д.А., действующего на основании ');
        $r1->addText('Устава', ['underline' => 'single']);
        $r1->addText(' с одной стороны, и');

        $r2 = $section->addTextRun($pStyle);
        $r2->addText('Общество с ограниченной ответственностью Профессиональная коллекторская организация «' . $counterparty . '»', ['bold' => true]);
        $r2->addText(', именуемое в дальнейшем «Цессионарий», в лице директора Пантюхиной Л.В., действующего на основании ');
        $r2->addText('устава', ['underline' => 'single']);
        $r2->addText(' с другой стороны,');

        $section->addText(
            'далее именуемые «Стороны», заключили настоящее Соглашение о нижеследующем:',
            ['bold' => true],
            $pStyle
        );
        $section->addTextBreak();

        $section->addListItem(
            "Внести изменения в Приложение №1 от {$agreementDateResolved} года к договору уступки права требования № {$agreementNumberResolved} от {$agreementDateResolved} года, исключить из перечня следующих должников:",
            0, [],
            ['listType' => \PhpOffice\PhpWord\Style\ListItem::TYPE_NUMBER]
        );

        $tableStyle = [
            'borderSize' => 8,
            'borderColor' => '000000',
            'cellMargin' => 80,
            'layout' => TableStyle::LAYOUT_FIXED,
            'width' => 10000,
        ];
        $phpWord->addTableStyle('RevocationTable', $tableStyle);

        $headers = [
            '№', 'Идентификатор доп. Услуги', 'Номер договора', 'Дата договора',
            'Клиент', 'Сумма займа', 'Оплачено', 'Общая сумма долга', 'Сумма долга',
        ];
        $widths = [700, 1800, 1700, 1300, 3100, 1300, 1300, 1300, 1200];

        $t = $section->addTable('RevocationTable');

        $t->addRow(null, ['tblHeader' => true]);
        foreach ($headers as $i => $h) {
            $t->addCell($widths[$i], ['valign' => 'center'])
                ->addText($h, ['bold' => false, 'size' => 9], ['alignment' => 'center']);
        }

        foreach ($rows as $i => $r) {
            $r = (array)$r;

            $debt_sum = round($r['total_debt'] * $r['percent'], 2);
            $debt_rubles = intval(floor($debt_sum));
            $debt_cent = intval(round(($debt_sum - $debt_rubles) * 100));
            $paid_sum = $r['loan_sum'] - $r['total_debt'];

            $t->addRow(null, ['cantSplit' => true]);
            $t->addCell($widths[0], ['valign' => 'center'])
                ->addText((string)($i + 1), [], ['alignment' => 'center']);
            $t->addCell($widths[1], ['valign' => 'center'])
                ->addText((string)($r['shkd_number'] ?? ''), [], ['alignment' => 'center']);
            $t->addCell($widths[2], ['valign' => 'center'])
                ->addText((string)($r['contract_number'] ?? ''), [], ['alignment' => 'center']);
            $t->addCell($widths[3], ['valign' => 'center'])
                ->addText((string)($r['contract_date'] ?? ''), [], ['alignment' => 'center']);
            $t->addCell($widths[4], ['valign' => 'center'])
                ->addText((string)($r['full_name_with_birth'] ?? ''), [], ['alignment' => 'center']);
            $t->addCell($widths[5], ['valign' => 'center'])
                ->addText($this->money($r['loan_sum'] ?? 0), [], ['alignment' => 'center']);
            $t->addCell($widths[6], ['valign' => 'center'])
                ->addText($this->money($paid_sum), [], ['alignment' => 'center']);
            $t->addCell($widths[7], ['valign' => 'center'])
                ->addText($this->money($r['total_debt'] ?? 0), [], ['alignment' => 'center']);
            $t->addCell($widths[8], ['valign' => 'center'])
                ->addText($this->money($debt_sum), [], ['alignment' => 'center']);
        }

        $section->addListItem(
            'Цедент перечисляет Цессионарию сумму в размере ' . $debt_rubles . ' рублей ' . str_pad($debt_cent, 2, '0', STR_PAD_LEFT) . ' копеек как возврат стоимости прав требования в течение 10 календарных дней с момента подписания настоящего Дополнительного соглашения.',
            0, [], ['listType' => \PhpOffice\PhpWord\Style\ListItem::TYPE_NUMBER]
        );
        $section->addListItem(
            'В остальном все положения Договора и Приложений к нему остаются без изменений.',
            0, [], ['listType' => \PhpOffice\PhpWord\Style\ListItem::TYPE_NUMBER]
        );
        $section->addListItem(
            'Данное соглашение составлено в 2 экземплярах — по одному для каждой из сторон и вступает в силу с момента его подписания обеими сторонами.',
            0, [], ['listType' => \PhpOffice\PhpWord\Style\ListItem::TYPE_NUMBER]
        );

        $section->addTextBreak();

        $section->addText('ПОДПИСИ СТОРОН:', ['bold' => true, 'size' => 12], ['alignment' => 'center']);

        $sig = $section->addTable([
            'borderSize' => 0,
            'cellMargin' => 0,
            'alignment' => JcTable::CENTER,
            'width' => 11000,
        ]);
        $sig->addRow();
        $left = $sig->addCell(5500);
        $right = $sig->addCell(5500);

        $left->addText('ООО «' . $cedent . '»', ['bold' => true, 'size' => 12], ['alignment' => 'left']);
        $left->addText('Генеральный директор:', ['size' => 12], ['alignment' => 'left']);
        $lr = $left->addTextRun(['alignment' => 'left']);
        $lr->addText('__________________');
        $lr->addText(' /Татарских Д.А./');

        $right->addText('ООО ПКО «' . $counterparty . '»', ['bold' => true, 'size' => 12], ['alignment' => 'right']);
        $right->addText($counterpartyDirectorPosition . ':', ['size' => 12], ['alignment' => 'right']);
        $rr = $right->addTextRun(['alignment' => 'right']);
        $rr->addText('__________________');
        $rr->addText(' /' . $counterpartyDirectorName . '/');

        $fileName = sys_get_temp_dir() . '/Доп_соглашение_' . date('Ymd_His') . '.docx';
        IOFactory::createWriter($phpWord, 'Word2007')->save($fileName);
        return $fileName;
    }
}