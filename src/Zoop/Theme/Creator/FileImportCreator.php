<?php

namespace Zoop\Theme\Creator;

use \Exception;
use \SplFileInfo;
use Zend\Http\Response;
use Zend\Mvc\MvcEvent;
use Zoop\Theme\Helper\FileHelperTrait;
use Zoop\Theme\DataModel\ThemeInterface;
use Zoop\Theme\Parser\DirectoryParser;

/**
 * @author Josh Stuart <josh.stuart@zoopcommerce.com>
 */
class FileImportCreator implements CreatorInterface
{
    use FileHelperTrait;

    protected $tempDirectory;
    protected $tempThemeDirectory;
    protected $directoryParser;
    protected $maxFileUploadSize;

    public function __construct(DirectoryParser $directoryParser, $tempDirectory, $maxFileUploadSize = null)
    {
        $this->setDirectoryParser($directoryParser);
        $this->setTempDirectory($tempDirectory);
        $this->setMaxFileUploadSize($maxFileUploadSize);
    }

    /**
     * @param MvcEvent $event
     * @return Response
     */
    public function create(MvcEvent $event)
    {
        $file = $this->getUploadedFile($event);

        //check if the upload is valid
        if ($this->isValidUpload($file, $this->getMaxFileUploadSize()) === true) {
            $result = $event->getResult();
            $result->setStatusCode(201);
            $theme = $result->getModel();

            $this->doCreate($file, $theme);
        } else {
            $result = $event->getResult();
            $result->setStatusCode(400);
        }

        return $result;
    }

    /**
     * @param SplFileInfo $file
     * @throws Exception
     */
    protected function doCreate(SplFileInfo $file, ThemeInterface $theme)
    {
        try {
            if ($this->unzipTheme($file) === true) {
                //set theme name
                $theme->setName(str_replace('.zip', '', $file->getFilename()));

                // use directory creator to parse and get assets
                $assets = $this->getDirectoryParser()
                    ->parse($this->getTempThemeDirectory());

                $theme->setAssets($assets);
            } else {
                throw new Exception(
                    sprintf(
                        'Could not unzip the file "%s" into "%s"',
                        $file->getFilename(),
                        $this->getTempThemeDirectory()
                    )
                );
            }
        } catch (Exception $e) {
//            $this->removeLocalFile($this->getTempDirectory(), $file->getFilename());
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @param MvcEvent $event
     * @return SplFileInfo
     * @throws Exception
     */
    protected function getUploadedFile(MvcEvent $event)
    {
        $request = $event->getRequest();
        $uploadedFile = $request->getFiles()->toArray();

        if (isset($uploadedFile['theme'])) {
            $uploadedFileName = $uploadedFile['theme']['tmp_name'];
        } else {
            $uploadedContent = $request->getContent();
            if (!empty($uploadedContent)) {
                $filename = $request->getHeaders()
                    ->get('X-File-Name')
                    ->getFieldValue();

                $uploadedFileName = $this->saveFile(
                    $this->getTempDirectory(),
                    $filename,
                    $uploadedContent
                );
            } else {
                throw new Exception('No file uploaded');
            }
        }

        $file = new SplFileInfo($uploadedFileName);

        if (empty($file)) {
            throw new Exception('No file uploaded');
        }

        return $file;
    }

    /**
     *
     * @param SplFileInfo $uploadedFile
     * @return boolean
     * @throws Exception
     */
    protected function unzipTheme(SplFileInfo $uploadedFile)
    {
        try {
            if ($this->unzip($uploadedFile->getPathname(), $this->getTempThemeDirectory()) === true) {
                return true;
            } else {
                throw new Exception(
                    sprintf(
                        'Could not unzip the file "%s" into "%s"',
                        $uploadedFile->getFilename(),
                        $this->getTempThemeDirectory()
                    )
                );
            }
        } catch (Exception $e) {
            $this->removeFile(
                $this->getTempDirectory(),
                $uploadedFile->getFilename()
            );
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @param string $tempDirectory
     * @return ThemeCreatorImport
     */
    public function setTempThemeDirectory($tempDirectory)
    {
        $this->tempThemeDirectory = $tempDirectory;
        return $this;
    }

    /**
     * @return string
     */
    public function getTempThemeDirectory()
    {
        return $this->tempThemeDirectory;
    }

    /**
     * @param string $tempDirectory
     * @return ThemeCreatorImport
     * @throws Exception
     */
    public function setTempDirectory($tempDirectory)
    {
        if (is_dir($tempDirectory)) {
            $this->tempDirectory = $tempDirectory;
            $this->setTempThemeDirectory(
                $this->createDirectory(
                    $tempDirectory,
                    uniqid(null, true)
                )
            );
        } else {
            throw new Exception('The directory "' . $tempDirectory . '" does not exist');
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getTempDirectory()
    {
        return $this->tempDirectory;
    }

    /**
     * @return int
     */
    public function getMaxFileUploadSize()
    {
        return $this->maxFileUploadSize;
    }

    /**
     * Sets the max upload size allowed in bytes
     *
     * @param integer $maxFileUploadSize
     */
    public function setMaxFileUploadSize($maxFileUploadSize)
    {
        $this->maxFileUploadSize = (int) $maxFileUploadSize;
    }

    /**
     * @return DirectoryParser
     */
    public function getDirectoryParser()
    {
        return $this->directoryParser;
    }

    /**
     * @param DirectoryParser $directoryParser
     */
    public function setDirectoryParser(DirectoryParser $directoryParser)
    {
        $this->directoryParser = $directoryParser;
    }

    /**
     * Remove all files
     */
    public function __destruct()
    {
        $this->deleteTempDirectory($this->getTempThemeDirectory());
    }
}
