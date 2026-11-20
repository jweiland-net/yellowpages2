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
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;
use TYPO3Fluid\Fluid\View\TemplatePaths;

/**
 * The form setup of yellowpages2 is loaded for all forms of the
 * installation. It must not change the mails of other forms.
 */
class FormSetupTest extends FunctionalTestCase
{
    private const CORE_FORM_SETUP = 'EXT:form/Configuration/Yaml/FormSetup.yaml';

    private const FORM_SETUP = 'EXT:yellowpages2/Configuration/Form/OverrideFormSettings.yaml';

    private const TEMPLATE_PATH = 'EXT:yellowpages2/Resources/Private/FormFramework/Templates/Finishers/Email/';
    protected array $coreExtensionsToLoad = [
        'form',
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
            $this->getEmailFinisherDefinition([10 => self::CORE_FORM_SETUP]),
            $this->getEmailFinisherDefinition([10 => self::CORE_FORM_SETUP, 1763380800 => self::FORM_SETUP]),
        );
    }

    #[Test]
    public function formSetupRegistersSaveCompanyFinisher(): void
    {
        $prototype = $this->getPrototype([10 => self::CORE_FORM_SETUP, 1763380800 => self::FORM_SETUP]);

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
        $options = $this->getEmailFinisherDefinition([10 => self::CORE_FORM_SETUP, 1763380800 => self::FORM_SETUP])['options'];
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
     * @param array<int, string> $yamlConfigurations
     * @return array<string, mixed>
     */
    private function getPrototype(array $yamlConfigurations): array
    {
        $configuration = $this->get(ConfigurationManagerInterface::class)
            ->getYamlConfiguration(['yamlConfigurations' => $yamlConfigurations], true);

        return ($configuration['prototypes'] ?? $configuration['TYPO3']['CMS']['Form']['prototypes'])['standard'];
    }
}
