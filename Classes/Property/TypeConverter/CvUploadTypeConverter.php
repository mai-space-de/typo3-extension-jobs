<?php

declare(strict_types=1);

namespace Maispace\MaiJobs\Property\TypeConverter;

use TYPO3\CMS\Core\Http\UploadedFile;
use TYPO3\CMS\Core\Resource\Enum\DuplicationBehavior;
use TYPO3\CMS\Core\Resource\File as CoreFile;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Resource\StorageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\StringUtility;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\Error\Error;
use TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface;
use TYPO3\CMS\Extbase\Property\TypeConverter\AbstractTypeConverter;

/**
 * Converts an uploaded CV file to an Extbase FileReference domain object
 * with MIME-type validation and FAL storage.
 *
 * @internal Purpose-built for the application CV upload flow.
 */
class CvUploadTypeConverter extends AbstractTypeConverter
{
    /**
     * @var array<string, string>
     */
    private const ALLOWED_MIME_TYPES = [
        'application/pdf' => 'pdf',
        'application/msword' => 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        'application/vnd.oasis.opendocument.text' => 'odt',
    ];

    /**
     * @var array<int, string>
     */
    private const ALLOWED_EXTENSIONS = ['pdf', 'doc', 'docx', 'odt'];

    protected ResourceFactory $resourceFactory;

    protected StorageRepository $storageRepository;

    public function injectResourceFactory(ResourceFactory $resourceFactory): void
    {
        $this->resourceFactory = $resourceFactory;
    }

    public function injectStorageRepository(StorageRepository $storageRepository): void
    {
        $this->storageRepository = $storageRepository;
    }

    /**
     * @param UploadedFile|array{tmp_name?: string, name?: string, type?: string, size?: int, error?: int}|null $source
     * @param array<string, mixed> $convertedChildProperties
     * @return FileReference|Error|null
     */
    public function convertFrom(
        $source,
        string $targetType,
        array $convertedChildProperties = [],
        ?PropertyMappingConfigurationInterface $configuration = null,
    ) {
        if ($source === null || $source === []) {
            return null;
        }

        $uploadInfo = $source instanceof UploadedFile
            ? $this->convertUploadedFileToArray($source)
            : $source;

        if (!isset($uploadInfo['error'])) {
            return null;
        }

        if ($uploadInfo['error'] === \UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if ($uploadInfo['error'] !== \UPLOAD_ERR_OK) {
            return new Error(
                $this->getUploadErrorMessage($uploadInfo['error']),
                1715100001,
            );
        }

        if (!$this->isAllowedMimeType($uploadInfo)) {
            return new Error(
                sprintf(
                    'File type "%s" is not allowed. Allowed types: %s.',
                    $uploadInfo['type'] ?? 'unknown',
                    implode(', ', self::ALLOWED_EXTENSIONS),
                ),
                1715100002,
            );
        }

        try {
            $coreFileReference = $this->storeUploadedFile($uploadInfo);
        } catch (\Throwable $e) {
            return new Error(
                'Failed to store uploaded file: ' . $e->getMessage(),
                1715100003,
            );
        }

        $fileReference = GeneralUtility::makeInstance(FileReference::class);
        $fileReference->setOriginalResource($coreFileReference);

        return $fileReference;
    }

    /**
     * Validates the uploaded file's MIME type against the allowed list.
     *
     * Checks both the browser-supplied type and (if the temp file exists) the
     * server-detected MIME type via finfo.
     */
    private function isAllowedMimeType(array $uploadInfo): bool
    {
        $clientMimeType = $uploadInfo['type'] ?? '';
        if ($clientMimeType !== '' && isset(self::ALLOWED_MIME_TYPES[$clientMimeType])) {
            return true;
        }

        $tmpName = $uploadInfo['tmp_name'] ?? '';
        if ($tmpName !== '' && is_file($tmpName) && is_readable($tmpName)) {
            $finfo = new \finfo(\FILEINFO_MIME_TYPE);
            $detectedMimeType = $finfo->file($tmpName);
            if ($detectedMimeType !== false && isset(self::ALLOWED_MIME_TYPES[$detectedMimeType])) {
                return true;
            }
        }

        return false;
    }

    private function storeUploadedFile(array $uploadInfo): \TYPO3\CMS\Core\Resource\FileReference
    {
        $storage = $this->storageRepository->getDefaultStorage();
        $targetFolder = $storage->getDefaultFolder();

        /** @var CoreFile $file */
        $file = $storage->addFile(
            $uploadInfo['tmp_name'],
            $targetFolder,
            $uploadInfo['name'] ?? '',
            DuplicationBehavior::RENAME,
        );

        return $this->resourceFactory->createFileReferenceObject([
            'uid_local' => $file->getUid(),
            'uid_foreign' => StringUtility::getUniqueId('NEW_'),
            'uid' => StringUtility::getUniqueId('NEW_'),
            'crop' => null,
        ]);
    }

    /**
     * @return array{name: string|null, tmp_name: string|null, type: string|null, size: int|null, error: int}
     */
    private function convertUploadedFileToArray(UploadedFile $uploadedFile): array
    {
        return [
            'name' => $uploadedFile->getClientFilename(),
            'tmp_name' => $uploadedFile->getTemporaryFileName(),
            'size' => $uploadedFile->getSize(),
            'error' => $uploadedFile->getError(),
            'type' => $uploadedFile->getClientMediaType(),
        ];
    }

    private function getUploadErrorMessage(int $errorCode): string
    {
        return match ($errorCode) {
            \UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the maximum allowed file size.',
            \UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the maximum file size specified in the form.',
            \UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded.',
            \UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder for file uploads.',
            \UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
            \UPLOAD_ERR_EXTENSION => 'File upload stopped by a PHP extension.',
            default => 'An unknown upload error occurred.',
        };
    }
}
