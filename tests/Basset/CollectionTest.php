<?php

use Mockery as m;
use Basset\Asset;
use Basset\Collection;
use Illuminate\Support\Collection as IlluminateCollection;

class CollectionTest extends PHPUnit_Framework_TestCase {


    public function tearDown()
    {
        m::close();
    }


    public function setUp()
    {
        $this->collection = new Collection('foo', $this->directory = m::mock('Basset\Directory'));
    }


    public function testGetIdentifierOfCollection()
    {
        $this->assertEquals('foo', $this->collection->getIdentifier());
    }


    public function testGetDefaultDirectory()
    {
        $this->assertEquals($this->directory, $this->collection->getDefaultDirectory());
    }


    public function testGetExtensionFromGroup()
    {
        $this->assertEquals('css', $this->collection->getExtension('stylesheets'));
        $this->assertEquals('js', $this->collection->getExtension('javascripts'));
    }


    public function testGettingCollectionAssetsWithDefaultOrdering()
    {
        $this->directory->shouldReceive('getAssets')->andReturn($expected = new IlluminateCollection(array(
            $this->newAsset('bar.css', 'path/to/bar.css', 'stylesheets', 1),
            $this->newAsset('baz.css', 'path/to/baz.css', 'stylesheets', 2)
        )));

        $this->assertEquals($expected->all(), $this->collection->getAssets('stylesheets')->all());
    }


    public function testGettingCollectionWithMultipleAssetGroupsReturnsOnlyRequestedGroup()
    {
        $this->directory->shouldReceive('getAssets')->andReturn(new IlluminateCollection(array(
            $assets[] = $this->newAsset('foo.css', 'path/to/foo.css', 'stylesheets', 1),
            $assets[] = $this->newAsset('bar.js', 'path/to/bar.js', 'javascripts', 2),
            $assets[] = $this->newAsset('baz.js', 'path/to/baz.js', 'javascripts', 3),
            $assets[] = $this->newAsset('qux.css', 'path/to/qux.css', 'stylesheets', 4)
        )));

        $expected = array(0 => $assets[0], 3 => $assets[3]);
        $this->assertEquals($expected, $this->collection->getAssets('stylesheets')->all());
    }


    public function testGettingCollectionAssetsWithCustomOrdering()
    {
        $this->directory->shouldReceive('getAssets')->andReturn(new IlluminateCollection(array(
            $assets[] = $this->newAsset('foo.css', 'path/to/foo.css', 'stylesheets', 1), // Becomes 2nd
            $assets[] = $this->newAsset('bar.css', 'path/to/bar.css', 'stylesheets', 2), // Becomes 4th
            $assets[] = $this->newAsset('baz.css', 'path/to/baz.css', 'stylesheets', 1), // Becomes 1st
            $assets[] = $this->newAsset('qux.css', 'path/to/qux.css', 'stylesheets', 4), // Becomes 5th
            $assets[] = $this->newAsset('zin.css', 'path/to/zin.css', 'stylesheets', 3)  // Becomes 3rd
        )));

        $expected = array($assets[2], $assets[0], $assets[4], $assets[1], $assets[3]);
        $this->assertEquals($expected, $this->collection->getAssets('stylesheets')->all());
    }


    public function testGettingCollectionExcludedAssets()
    {
        $this->directory->shouldReceive('getAssets')->andReturn(new IlluminateCollection(array(
            $assets[] = $this->newAsset('foo.css', 'path/to/foo.css', 'stylesheets', 1),
            $assets[] = $this->newAsset('bar.css', 'path/to/bar.css', 'stylesheets', 2)
        )));

        $assets[1]->exclude();

        $this->assertEquals(array(1 => $assets[1]), $this->collection->getAssetsOnlyExcluded('stylesheets')->all());
    }


    public function newAsset($relative, $absolute, $group, $order)
    {
        $asset = new Asset(m::mock('Illuminate\Filesystem\Filesystem'), m::mock('Basset\Factory\FilterFactory'), m::mock('Illuminate\Log\Writer'), $absolute, $relative);

        return $asset->setOrder($order)->setGroup($group);
    }


}