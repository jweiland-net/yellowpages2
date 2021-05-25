<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Tests\Unit\EventListener;

use JWeiland\Glossary2\Service\GlossaryService;
use JWeiland\Yellowpages2\Domain\Repository\CompanyRepository;
use JWeiland\Yellowpages2\Event\PostProcessFluidVariablesEvent;
use JWeiland\Yellowpages2\EventListener\AddGlossaryEventListener;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

/**
 * Test case.
 */
class AddGlossaryEventListenerTest extends UnitTestCase
{
    /**
     * @var AddGlossaryEventListener
     */
    protected $subject;

    /**
     * @var QueryBuilder|ObjectProphecy
     */
    protected $queryBuilderProphecy;

    /**
     * @var GlossaryService|ObjectProphecy
     */
    protected $glossaryServiceProphecy;

    /**
     * @var CompanyRepository|ObjectProphecy
     */
    protected $companyRepositoryProphecy;

    /**
     * @var PostProcessFluidVariablesEvent|ObjectProphecy
     */
    protected $eventProphecy;

    public function setUp(): void
    {
        parent::setUp();

        $this->queryBuilderProphecy = $this->prophesize(QueryBuilder::class);

        $this->glossaryServiceProphecy = $this->prophesize(GlossaryService::class);
        $this->glossaryServiceProphecy
            ->buildGlossary(Argument::cetera())
            ->willReturn('html');

        $this->companyRepositoryProphecy = $this->prophesize(CompanyRepository::class);
        $this->companyRepositoryProphecy
            ->getQueryBuilderToFindAllEntries()
            ->willReturn($this->queryBuilderProphecy->reveal());

        $this->eventProphecy = $this->prophesize(PostProcessFluidVariablesEvent::class);
        $this->eventProphecy
            ->getControllerName()
            ->willReturn('Company');
        $this->eventProphecy
            ->getActionName()
            ->willReturn('list');

        $this->subject = new AddGlossaryEventListener(
            $this->glossaryServiceProphecy->reveal(),
            $this->companyRepositoryProphecy->reveal()
        );
    }

    public function tearDown(): void
    {
        unset(
            $this->subject,
            $this->glossaryServiceProphecy,
            $this->companyRepositoryProphecy
        );

        parent::tearDown();
    }

    /**
     * @test
     */
    public function invokeToAddGlossary(): void
    {
        $this->eventProphecy
            ->addFluidVariable('glossar', 'html')
            ->shouldBeCalled();

        $this->subject->__invoke($this->eventProphecy->reveal());
    }
}
