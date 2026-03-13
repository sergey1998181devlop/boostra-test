<?php

declare(strict_types=1);

namespace api\terrorist\import;

use api\terrorist\TerroristImporter;
use RuntimeException;
use SimpleXMLElement;
use XMLReader;

/**
 * Импорт un_consolidated (UN CONSOLIDATED_LIST)
 */
class UnConsolidatedImporter extends TerroristImporter
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
            // 1) Атрибут dateGenerated у корневого CONSOLIDATED_LIST
            if (
                $reader->nodeType === XMLReader::ELEMENT
                && $reader->name === 'CONSOLIDATED_LIST'
                && $listDate === null
            ) {
                $listDateAttr = $reader->getAttribute('dateGenerated');
                $listDate     = $this->normalizeDate($listDateAttr);
            }

            // 2) Каждый INDIVIDUAL
            if ($reader->nodeType === XMLReader::ELEMENT && $reader->name === 'INDIVIDUAL') {
                $indXml = new SimpleXMLElement($reader->readOuterXML());

                // skip, если имя отсутствует (пусто/пробелы/nbsp)
                $first = trim(str_replace("\xC2\xA0", ' ', (string)$indXml->FIRST_NAME));
                if ($first === '') {
                    continue;
                }

                $dataId  = (string)$indXml->DATAID;
                $first   = (string)$indXml->FIRST_NAME;
                $second  = (string)$indXml->SECOND_NAME;
                $fullName = trim($first . ' ' . $second);

                $gender  = (string)$indXml->GENDER;
                $comment = (string)$indXml->COMMENTS1;

                $nationality = null;
                if (isset($indXml->NATIONALITY->VALUE)) {
                    $nationality = (string)$indXml->NATIONALITY->VALUE;
                }

                $unListType = (string)$indXml->UN_LIST_TYPE;
                if (isset($indXml->LIST_TYPE->VALUE) && (string)$indXml->LIST_TYPE->VALUE !== '') {
                    $unListType = (string)$indXml->LIST_TYPE->VALUE;
                }

                $yob = null;
                if (isset($indXml->INDIVIDUAL_DATE_OF_BIRTH->YEAR)) {
                    $yob = (int)$indXml->INDIVIDUAL_DATE_OF_BIRTH->YEAR;
                }

                $row = [
                    // --- для terrorist_subjects ---
                    'source_id'        => $this->sourceId,
                    'list_date'        => $listDate ?: date('Y-m-d'),
                    'external_id'      => $dataId,

                    'full_name'        => $fullName ?: $dataId,
                    'last_name'        => null,
                    'first_name'       => $first ?: null,
                    'middle_name'      => null,
                    'latin_full_name'  => null,

                    'date_of_birth'    => null,
                    'year_of_birth'    => $yob ?: null,
                    'place_of_birth'   => null,

                    'gender'           => $gender ?: null,
                    'nationality'      => $nationality ?: null,

                    'inn'              => null,
                    'snils'            => null,

                    'person_type_name' => $unListType ?: null,

                    'is_terrorist'     => 1,

                    'created_at'       => date('Y-m-d H:i:s'),
                    'updated_at'       => date('Y-m-d H:i:s'),

                    // --- для terrorist_subject_lists (история) ---
                    'decision_number'    => null,
                    'decision_date'      => null,
                    'decision_body'      => null,
                    'decision_type_name' => null,
                    'decision_text'      => null,
                    'comments'           => $comment ?: null,
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

        // финализируем актуальность для этого источника по дате списка
        if ($total > 0) {
            $effectiveListDate = $listDate ?: date('Y-m-d');
            $this->subjectsModel->finalizeCurrentFlags($this->sourceId,$effectiveListDate);
        }

        return $total;
    }
}
