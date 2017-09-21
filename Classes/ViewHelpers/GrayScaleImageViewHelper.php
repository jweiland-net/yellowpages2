<?php
declare(strict_types=1);
namespace JWeiland\Yellowpages2\ViewHelpers;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Fluid\Core\ViewHelper\Exception;
use TYPO3\CMS\Fluid\ViewHelpers\ImageViewHelper;

/**
 * Class GrayScaleImageViewHelper
 *
 * @package JWeiland\Clubdirectory\ViewHelpers
 */
class GrayScaleImageViewHelper extends ImageViewHelper
{
    /**
     * Resizes a given image (if required) and renders the respective img tag.
     *
     * @see http://typo3.org/documentation/document-library/references/doc_core_tsref/4.2.0/view/1/5/#id4164427
     *
     * @throws Exception
     *
     * @return string Rendered tag
     */
    public function render()
    {
        if (($this->arguments['src'] === null && $this->arguments['image'] === null) ||
            ($this->arguments['src'] !== null && $this->arguments['image'] !== null)) {
            throw new Exception('You must either specify a string src or a File object.', 1382284105);
        }
        $image = $this->imageService->getImage(
            $this->arguments['src'],
            $this->arguments['image'],
            $this->arguments['treatIdAsReference']
        );
        $processingInstructions = [
            'width' => $this->arguments['width'],
            'height' => $this->arguments['height'],
            'minWidth' => $this->arguments['minWidth'],
            'minHeight' => $this->arguments['minHeight'],
            'maxWidth' => $this->arguments['maxWidth'],
            'maxHeight' => $this->arguments['maxHeight'],
            'additionalParameters' => '-colorspace GRAY'
        ];
        $processedImage = $this->imageService->applyProcessingInstructions($image, $processingInstructions);
        $imageUri = $this->imageService->getImageUri($processedImage);

        $this->tag->addAttribute('src', $imageUri);
        $this->tag->addAttribute('width', $processedImage->getProperty('width'));
        $this->tag->addAttribute('height', $processedImage->getProperty('height'));

        $alt = $image->getProperty('alternative');
        $title = $image->getProperty('title');

        // The alt-attribute is mandatory to have valid html-code, therefore add it even if it is empty
        if (empty($this->arguments['alt'])) {
            $this->tag->addAttribute('alt', $alt);
        }
        if ($title && empty($this->arguments['title'])) {
            $this->tag->addAttribute('title', $title);
        }

        return $this->tag->render();
    }
}
