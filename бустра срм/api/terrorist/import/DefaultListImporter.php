<?php

declare(strict_types=1);

namespace api\terrorist\import;

use api\terrorist\TerroristImporter;
use RuntimeException;
use SimpleXMLElement;
use XMLReader;

/**
 * Импорт default (Перечень Росфинмониторинга)
 */
class DefaultListImporter extends TerroristImporter
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
            // 1) Дата перечня
            if ($reader->nodeType === XMLReader::ELEMENT
                && $reader->name === 'ДатаПеречня'
                && $listDate === null
            ) {
                $listDate = $this->normalizeDate($reader->readInnerXML());
            }

            // 2) Субъект
            if ($reader->nodeType === XMLReader::ELEMENT && $reader->name === 'Субъект') {
                $subjXml = new SimpleXMLElement($reader->readOuterXML());

                $extId    = (string)$subjXml->ИдСубъекта;
                $typeName = (string)$subjXml->ТипСубъекта->Наименование;

                $fl = $subjXml->ФЛ;

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

                $inn           = (string)$fl->ИНН;
                $snils         = (string)$fl->СНИЛС;

                $nationality = null;
                if (isset($fl->СписокГражданств->Гражданство[0])) {
                    $nationality = (string)$fl->СписокГражданств->Гражданство[0];
                }

                $isTerrorist = ((string)$subjXml->Террорист === '1') ? 1 : 0;

                // История решений — для перечня РФ обычно есть хотя бы ДатаВключения
                $decisionDate = null;
                if (isset($subjXml->История->ДатаВключения)) {
                    $decisionDate = $this->normalizeDate((string)$subjXml->История->ДатаВключения);
                }

                $row = [
                    // для subjects:
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
                    'nationality'      => $nationality ?: null,

                    'inn'              => $inn ?: null,
                    'snils'            => $snils ?: null,

                    'person_type_name' => $typeName ?: null,

                    'is_terrorist'     => $isTerrorist,

                    'created_at'       => date('Y-m-d H:i:s'),
                    'updated_at'       => date('Y-m-d H:i:s'),

                    // для истории в terrorist_subject_lists:
                    'decision_number'    => null,
                    'decision_date'      => $decisionDate,
                    'decision_body'      => null,
                    'decision_type_name' => null,
                    'decision_text'      => null,
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

        if ($batch) {
            $this->flushBatch($batch);
        }

        $reader->close();

        if ($total > 0) {
            $effectiveListDate = $listDate ?: date('Y-m-d');
            $this->subjectsModel->finalizeCurrentFlags($this->sourceId,$effectiveListDate);
        }
        return $total;
    }
}
