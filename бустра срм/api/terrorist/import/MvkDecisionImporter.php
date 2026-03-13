<?php

declare(strict_types=1);

namespace api\terrorist\import;

use api\terrorist\TerroristImporter;
use RuntimeException;
use SimpleXMLElement;
use XMLReader;

/**
 * Импорт mvk_decision_list (СписокРешений)
 *
 */
class MvkDecisionImporter extends TerroristImporter
{
    protected function doImport(string $filePath): int
    {
        $reader = new XMLReader();
        if (!$reader->open($filePath)) {
            throw new RuntimeException("Cannot open XML file: {$filePath}");
        }

        $listDate = null;
        $batch    = [];
        $total    = 0;

        while ($reader->read()) {
            // 1) Дата списка
            if (
                $reader->nodeType === XMLReader::ELEMENT
                && $reader->name === 'ДатаСписка'
                && $listDate === null
            ) {
                $listDate = $this->normalizeDate($reader->readInnerXML());
                continue;
            }

            // 2) Каждое <Решение>
            if ($reader->nodeType === XMLReader::ELEMENT && $reader->name === 'Решение') {
                $decisionXml = new SimpleXMLElement($reader->readOuterXML());

                $decisionNumber   = (string)$decisionXml->НомерРешения;
                $decisionDate     = $this->normalizeDate((string)$decisionXml->ДатаРешения);
                $decisionBody     = (string)$decisionXml->Орган;
                $decisionTypeName = (string)$decisionXml->ВидРешения->Наименование;

                if (!isset($decisionXml->СписокСубъектов->Субъект)) {
                    continue;
                }

                foreach ($decisionXml->СписокСубъектов->Субъект as $subj) {

                    $extId    = (string)$subj->ИдСубъекта;
                    $typeName = (string)$subj->ТипСубъекта->Наименование;

                    $fl = $subj->ФЛ;

                    // skip, если имя отсутствует (пусто/пробелы/nbsp)
                    $firstNameNorm = trim(str_replace("\xC2\xA0", ' ', (string)$fl->Имя));
                    if ($firstNameNorm === '') {
                        continue;
                    }

                    $fullName      = (string)$fl->ФИО;
                    $lastName      = (string)$fl->Фамилия;
                    $firstName     = (string)$fl->Имя;
                    $middleName    = (string)$fl->Отчество;
                    $latinFullName = (string)$fl->ФИОЛат;
                    $dob           = $this->normalizeDate((string)$fl->ДатаРождения);
                    $yob           = (string)$fl->ГодРождения;
                    $placeOfBirth  = (string)$fl->МестоРождения;

                    $decisionText  = (string)$subj->РешениеПоСубъекту;

                    $row = [
                        // --- для terrorist_subjects ---
                        'source_id'        => $this->sourceId,
                        'list_date'        => $listDate ?: date('Y-m-d'),
                        'external_id'      => $extId,

                        'full_name'        => $fullName,
                        'last_name'        => $lastName ?: null,
                        'first_name'       => $firstName ?: null,
                        'middle_name'      => $middleName ?: null,
                        'latin_full_name'  => $latinFullName ?: null,

                        'date_of_birth'    => $dob,
                        'year_of_birth'    => $yob !== '' ? (int)$yob : null,
                        'place_of_birth'   => $placeOfBirth ?: null,

                        'gender'           => null,
                        'nationality'      => null,

                        'inn'              => null,
                        'snils'            => null,

                        'person_type_name' => $typeName ?: null,

                        'is_terrorist'     => 1,

                        'created_at'       => date('Y-m-d H:i:s'),
                        'updated_at'       => date('Y-m-d H:i:s'),

                        // --- для terrorist_subject_lists (история) ---
                        'decision_number'    => $decisionNumber ?: null,
                        'decision_date'      => $decisionDate,
                        'decision_body'      => $decisionBody ?: null,
                        'decision_type_name' => $decisionTypeName ?: null,
                        'decision_text'      => $decisionText ?: null,
                        'comments'           => null,
                    ];

                    $batch[] = $row;
                    $total++;

                    if (count($batch) >= $this->batchSize) {
                        $this->flushBatch($batch);
                        $batch = [];
                    }
                }
            }
        }

        if ($batch) {
            $this->flushBatch($batch);
        }

        $reader->close();

        // финализируем is_current по дате списка (выпавшие субъекты → is_current = 0)
        if ($total > 0) {
            $effectiveListDate = $listDate ?: date('Y-m-d');
            $this->subjectsModel->finalizeCurrentFlags($this->sourceId,$effectiveListDate);
        }

        return $total;
    }
}
