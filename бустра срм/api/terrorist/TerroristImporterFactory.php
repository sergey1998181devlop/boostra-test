<?php

declare(strict_types=1);
namespace api\terrorist;

use api\terrorist\import\DefaultListImporter;
use api\terrorist\import\MvkDecisionImporter;
use api\terrorist\import\UnConsolidatedImporter;
use Database;
use InvalidArgumentException;
use api\terrorist\models\TerroristSources;

/**
 * Фабрика импортёров.
 */
class TerroristImporterFactory
{
    /**
     * @param Database $db
     */
    public function create(string $sourceCode, $db, int $sourceId, $file)
    {
        switch ($sourceCode) {
            case TerroristSources::MVK_DECISION_CODE:
                return new MvkDecisionImporter($db, $sourceId, $file);
            case TerroristSources::UN_CONSOLIDATED_CODE:
                return new UnConsolidatedImporter($db, $sourceId,$file);
            case TerroristSources::DEFAULT_CODE:
                return new DefaultListImporter($db, $sourceId, $file);
            default:
                throw new InvalidArgumentException("Unsupported source code: {$sourceCode}");
        }
    }
}
