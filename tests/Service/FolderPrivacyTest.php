<?php

namespace SilverStripe\SearchService\Tests\Service;

use SilverStripe\Forms\Form;
use SilverStripe\Assets\File;
use SilverStripe\Assets\Image;
use SilverStripe\Forms\TabSet;
use SilverStripe\Assets\Folder;
use SilverStripe\Security\Group;
use SilverStripe\Forms\FieldList;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\SearchService\DataObject\DataObjectDocument;
use SilverStripe\SearchService\Extensions\SearchFormFactoryExtension;


class FolderPrivacyTest extends SapphireTest
{

    protected static $fixture_file = [ // phpcs:ignore
        '../fixtures.yml',
        '../pages.yml',
    ];

    public function testDefaultFolderFileShowInSearch(): void
    {
        $folder = $this->objFromFixture(Folder::class, 'folder');
        // Add file to folder
        $file = $this->objFromFixture(File::class, 'pdf-file');
        $file->ParentID = $folder->ID;
        $file->write();

        $this->assertCount(1, $folder->myChildren());

        // File should be indexed
        $doc = DataObjectDocument::create($file);
        $this->assertTrue($doc->shouldIndex());
    }

    public function testRestrictedFolderFileShowInSearch(): void
    {
        $folder = $this->objFromFixture(Folder::class, 'folder');
        // Set permission to admin only
        $folder->CanViewType = 'OnlyTheseUsers';
        $folder->ViewerGroups()->add($this->objFromFixture(Group::class, 'admin-group'));
        $folder->write();
        $folder->publishRecursive();

        // File::add_extension(SearchServiceExtension::class,);

        // Add files to folder
        $file = $this->objFromFixture(File::class, 'pdf-file');
        $file->ParentID = $folder->ID;
        $file->write();
        $image = $this->objFromFixture(Image::class, 'image');
        $image->ParentID = $folder->ID;
        $image->write();

        $this->assertCount(2, $folder->myChildren());

        // Remove permission on folder and check if file is indexed
        $folder->CanViewType = 'Inherit';
        $folder->ViewerGroups()->removeAll();
        $folder->write();

        // File should be indexed
        $doc1 = DataObjectDocument::create($file);
        $this->assertTrue($doc1->shouldIndex());

        // Image file should not be indexed
        $doc2 = DataObjectDocument::create($image);
        $this->assertFalse($doc2->shouldIndex());
    }
}
