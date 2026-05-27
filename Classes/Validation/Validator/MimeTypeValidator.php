<?php

declare(strict_types=1);

namespace Maispace\MaiJobs\Validation\Validator;

use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

/**
 * Validates that uploaded CV files have allowed MIME types.
 *
 * The allowed MIME types correspond to the file extensions permitted
 * in the TCA configuration (pdf, doc, docx, odt).
 */
class MimeTypeValidator extends AbstractValidator
{
    /**
     * @var array<string, string> Extension label → expected MIME type
     */
    private const ALLOWED_MIME_TYPES = [
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'odt' => 'application/vnd.oasis.opendocument.text',
    ];

    /**
     * @var array<int, string> Human-readable file extensions for error messages
     */
    private const ALLOWED_EXTENSIONS = ['pdf', 'doc', 'docx', 'odt'];

    protected function isValid(mixed $value): void
    {
        if ($value === null) {
            return;
        }

        if (!$value instanceof ObjectStorage) {
            $this->addError(
                $this->translateErrorMessage(
                    'validation.error.mimeType.invalid',
                    'mai_jobs',
                ),
                1715000001,
            );
            return;
        }

        if ($value->count() === 0) {
            return;
        }

        foreach ($value as $fileReference) {
            if (!$fileReference instanceof FileReference) {
                continue;
            }

            $originalResource = $fileReference->getOriginalResource();
            $mimeType = $originalResource->getMimeType();

            if (!in_array($mimeType, self::ALLOWED_MIME_TYPES, true)) {
                $this->addError(
                    $this->translateErrorMessage(
                        'validation.error.mimeType.notAllowed',
                        'mai_jobs',
                        [
                            $mimeType,
                            implode(', ', self::ALLOWED_EXTENSIONS),
                        ],
                    ),
                    1715000002,
                );
                return;
            }
        }
    }
}
