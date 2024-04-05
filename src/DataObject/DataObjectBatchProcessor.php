<?php

namespace SilverStripe\SearchService\DataObject;

use SilverStripe\Core\Config\Configurable;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\ORM\ValidationException;
use SilverStripe\SearchService\Interfaces\DocumentInterface;
use SilverStripe\SearchService\Jobs\IndexJob;
use SilverStripe\SearchService\Jobs\RemoveDataObjectJob;
use SilverStripe\SearchService\Service\BatchProcessor;
use SilverStripe\SearchService\Service\Indexer;

class DataObjectBatchProcessor extends BatchProcessor
{

    use Configurable;

    private static int $buffer_seconds = 5;

    /**
     * @param DocumentInterface[] $documents
     * @throws ValidationException
     */
    public function removeDocuments(array $documents): array
    {
        $timestamp = DBDatetime::now()->getTimestamp() - $this->config()->get('buffer_seconds');

        // Remove the dataobjects, ignore dependencies
        $job = IndexJob::create($documents, Indexer::METHOD_DELETE, null, false);
        $this->run($job);

        foreach ($documents as $doc) {
            // Indexer::METHOD_ADD as default parameter make sure we check first its related documents
            // and decide whether we should delete or update them automatically.
            $childJob = RemoveDataObjectJob::create($doc, $timestamp);
            $this->run($childJob);
        }

        return [];
    }

}
