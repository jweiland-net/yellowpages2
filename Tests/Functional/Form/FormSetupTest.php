<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Tests\Functional\Form;

use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Yaml\Yaml;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Form\Mvc\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Form\Mvc\Configuration\InheritancesResolverService;
use TYPO3\CMS\Form\Mvc\Configuration\YamlSource;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;
use TYPO3Fluid\Fluid\View\TemplatePaths;

/**
 * The form setup of yellowpages2 is loaded for all forms of the
 * installation. It must not change the mails of other forms.
 */
class FormSetupTest extends FunctionalTestCase
{
    private const CORE_FORM_SETUP = 'EXT:form/Configuration/Form/Base/config.yaml';

    private const FORM_SETUP = 'EXT:yellowpages2/Configuration/Form/Yellowpages2/config.yaml';

    private const TEMPLATE_PATH = 'EXT:yellowpages2/Resources/Private/FormFramework/Templates/Finishers/Email/';
    protected array $coreExtensionsToLoad = [
        'form',
        'install',
    ];

    protected array $testExtensionsToLoad = [
        'jweiland/glossary2',
        'jweiland/maps2',
        'jweiland/yellowpages2',
    ];

    #[Test]
    public function formSetupKeepsEmailFinisherOfCore(): void
    {
        self::assertSame(
            $this->getEmailFinisherDefinition([self::CORE_FORM_SETUP]),
            $this->getEmailFinisherDefinition([self::CORE_FORM_SETUP, self::FORM_SETUP]),
        );
    }

    #[Test]
    public function formSetupRegistersSaveCompanyFinisher(): void
    {
        // Uses the form sets discovered by EXT:form from all active extensions
        $prototype = $this->get(ConfigurationManagerInterface::class)
            ->getYamlConfiguration([], true)['prototypes']['standard'];

        self::assertArrayHasKey('SaveCompanyFinisher', $prototype['finishersDefinition']);
    }

    #[Test]
    public function companyFormUsesOwnMailTemplate(): void
    {
        $options = $this->getEmailFinisherOptionsOfCompanyForm();

        self::assertSame('CompanyModeration', $options['templateName']);
        self::assertSame(self::TEMPLATE_PATH, end($options['templateRootPaths']));

        $templatePaths = new TemplatePaths();
        $templatePaths->setTemplateRootPaths(array_map(GeneralUtility::getFileAbsFileName(...), $options['templateRootPaths']));
        foreach (['html', 'txt'] as $format) {
            self::assertSame(
                GeneralUtility::getFileAbsFileName(self::TEMPLATE_PATH . 'CompanyModeration.' . $format),
                $templatePaths->resolveTemplateFileForControllerAndActionAndFormat('Default', 'CompanyModeration', $format),
            );
        }
    }

    #[Test]
    public function mailTemplateUsesModerationTokenOfFinisher(): void
    {
        foreach (['html', 'txt'] as $format) {
            $template = (string)file_get_contents(GeneralUtility::getFileAbsFileName(self::TEMPLATE_PATH . 'CompanyModeration.' . $format));

            self::assertStringContainsString('{finisherVariableProvider.SaveCompany.moderationToken}', $template);
        }
    }

    /**
     * Options of EmailToReceiver like FormDefinition::createFinisher() merges them
     *
     * @return array<string, mixed>
     */
    private function getEmailFinisherOptionsOfCompanyForm(): array
    {
        $options = $this->getEmailFinisherDefinition([self::CORE_FORM_SETUP, self::FORM_SETUP])['options'];
        $formDefinition = Yaml::parseFile(__DIR__ . '/../../../Configuration/Form/Company.form.yaml');
        foreach ($formDefinition['finishers'] as $finisher) {
            if ($finisher['identifier'] === 'EmailToReceiver') {
                ArrayUtility::mergeRecursiveWithOverrule($options, $finisher['options']);
            }
        }
        ksort($options['templateRootPaths']);

        return $options;
    }

    /**
     * @param array<int, string> $yamlConfigurations
     * @return array<string, mixed>
     */
    private function getEmailFinisherDefinition(array $yamlConfigurations): array
    {
        return $this->getPrototype($yamlConfigurations)['finishersDefinition']['EmailToReceiver'];
    }

    /**
     * Merges the given form sets like EXT:form does, without the form sets of other extensions
     *
     * @param array<int, string> $yamlConfigurations
     * @return array<string, mixed>
     */
    private function getPrototype(array $yamlConfigurations): array
    {
        $configuration = InheritancesResolverService::create(
            $this->get(YamlSource::class)->load($yamlConfigurations),
        )->getResolvedConfiguration();

        return $configuration['prototypes']['standard'];
    }
}
