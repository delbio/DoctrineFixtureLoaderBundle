<?php
namespace Delbio\Bundle\DoctrineFixtureLoaderBundle\DataFixtures\ORM;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\PropertyAccess\PropertyAccessor;

abstract class AbstractUploadableLoadDataFromResource extends AbstractLoadDataFromResource
{
    /**
     * @var Filesystem
     */
    protected $fs;

    /**
     * AbstractUploadableLoadDataFromResource constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->fs = new Filesystem();
    }

    protected function populateEntity(PropertyAccessor $accessor, $entity, $definition)
    {

        if ( isset($definition['files']) ){
            foreach ($definition['files'] as $property) {
                $this->writeConsole("> setting " . $property);
                $sfFile = $definition['properties'][$property];
                $filePath = $this->locateResource($sfFile);
                $this->writeConsole(sprintf('%s %s', $sfFile, $filePath ));
                $value = $this->createTempUploadedFileFixture($filePath, uniqid());
                $accessor->setValue($entity, $property, $value);
                unset($definition['properties'][$property]);
            }
        }

        parent::populateEntity($accessor, $entity, $definition);
    }

    /**
     * Restituisce una copia di una fixture con un nome impostato a piacimento come UploadedFile
     * @param string $fixturePath
     * @param $tempFileNameWithoutExtension
     * @return UploadedFile
     */
    protected function createTempUploadedFileFixture(string $fixturePath, $tempFileNameWithoutExtension) : UploadedFile
    {
        return $this->toUploadedFile(new File($this->createTempFixtureFile($fixturePath, $tempFileNameWithoutExtension)));
    }

    /**
     * Restituisce il percorso di una copia della fixture scelta che risiede nella cartella temporanea del sistema
     * @param $fixtureFilePath
     * @param $tempFileNameWithoutExtension
     * @return string
     */
    protected function createTempFixtureFile($fixtureFilePath, $tempFileNameWithoutExtension)
    {
        $originFile = new File($fixtureFilePath);
        $destFilePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $tempFileNameWithoutExtension . '.' . $originFile->getExtension();

        $this->fs->copy(
            $originFile->getPathname(),
            $destFilePath
        );
        return $destFilePath;
    }

    /**
     * @param File $file
     * @return UploadedFile
     */
    protected function toUploadedFile(File $file)
    {
        if ($file instanceof UploadedFile){
            return $file;
        }
        return new UploadedFile(
            $file->getPathname(),
            $file->getFilename(),
            $file->getMimeType(),
            $file->getSize(),
            UPLOAD_ERR_OK,
            true
        );
    }


}