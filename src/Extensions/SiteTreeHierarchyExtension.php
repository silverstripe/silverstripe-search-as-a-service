<?php

namespace SilverStripe\SearchService\Extensions;

use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Core\Extension;
use SilverStripe\SearchService\DataObject\DataObjectDocument;

class SiteTreeHierarchyExtension extends Extension
{

    public function updateSearchDependentDocuments(array &$dependentDocs): void
    {
        if (!SiteTree::config()->get('enforce_strict_hierarchy')) {
            return;
        }

        $page = $this->getOwner();

        foreach ($page->AllChildren() as $record) {
            $document = DataObjectDocument::create($record);
            $dependentDocs[$document->getIdentifier()] = $document;
            $dependentDocs = array_merge($dependentDocs, $document->getDependentDocuments());
        }
    }

}
