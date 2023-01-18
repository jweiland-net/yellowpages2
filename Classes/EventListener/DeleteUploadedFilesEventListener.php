<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\EventListener;

use JWeiland\Yellowpages2\Event\PostProcessControllerActionEvent;

/**
 * Files will be uploaded in typeConverter automatically.
 * But, if an error occurs, we have to remove them.
 */
class DeleteUploadedFilesEventListener extends AbstractControllerEventListener
{
    protected $allowedControllerActions = [
        'Company' => [
            'create',
        ],
    ];

    public function __invoke(PostProcessControllerActionEvent $event): void
    {
        $argumentName = 'company';
        if (
            $this->isValidRequest($event)
            && $event->getRequest()->hasArgument($argumentName)
        ) {
            /** @var array $company */
            $company = $event->getRequest()->getArgument($argumentName);
            if ($company['images'] !== []) {
                unset($company['images']);
            }
            if ($company['logo'] !== []) {
                unset($company['logo']);
            }
            $event->getRequest()->setArgument($argumentName, $company);
        }
    }
}
