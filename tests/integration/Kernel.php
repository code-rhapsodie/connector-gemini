<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace CodeRhapsodie\Tests\Integration\ConnectorGemini;

use CodeRhapsodie\Bundle\ConnectorGemini\CRConnectorGeminiBundle;
use DAMA\DoctrineTestBundle\DAMADoctrineTestBundle;
use Hautelook\TemplatedUriBundle\HautelookTemplatedUriBundle;
use Ibexa\Bundle\AdminUi\IbexaAdminUiBundle;
use Ibexa\Bundle\ConnectorAi\IbexaConnectorAiBundle;
use Ibexa\Bundle\ContentForms\IbexaContentFormsBundle;
use Ibexa\Bundle\CorePersistence\IbexaCorePersistenceBundle;
use Ibexa\Bundle\CoreSearch\IbexaCoreSearchBundle;
use Ibexa\Bundle\DesignEngine\IbexaDesignEngineBundle;
use Ibexa\Bundle\FieldTypeRichText\IbexaFieldTypeRichTextBundle;
use Ibexa\Bundle\GraphQL\IbexaGraphQLBundle;
use Ibexa\Bundle\Notifications\IbexaNotificationsBundle;
use Ibexa\Bundle\ProductCatalog\EventSubscriber\MainMenuSubscriber;
use Ibexa\Bundle\ProductCatalog\IbexaProductCatalogBundle;
use Ibexa\Bundle\ProductCatalog\Serializer\ProductNormalizer;
use Ibexa\Bundle\Rest\IbexaRestBundle;
use Ibexa\Bundle\Search\IbexaSearchBundle;
use Ibexa\Bundle\TwigComponents\IbexaTwigComponentsBundle;
use Ibexa\Bundle\User\IbexaUserBundle;
use Ibexa\ConnectorAi\CriterionMapper\ActionConfiguration\TypeCriterionMapper;
use Ibexa\ConnectorAi\GenerateAltTextActionType;
use Ibexa\ConnectorAi\RefineTextActionType;
use Ibexa\Contracts\ConnectorAi\ActionConfigurationServiceInterface;
use Ibexa\Contracts\ConnectorAi\ActionServiceInterface;
use Ibexa\Contracts\ConnectorAi\ActionType\ActionTypeInterface;
use Ibexa\Contracts\ConnectorAi\ActionType\ActionTypeRegistryInterface;
use Ibexa\Contracts\Core\Test\Persistence\Fixture\YamlFixture;
use Ibexa\Contracts\Taxonomy\Service\TaxonomyServiceInterface;
use Ibexa\Contracts\Test\Core\IbexaTestKernel;
use Ibexa\GraphQL\Mutation\InputHandler\FieldType\RichText\HtmlRichTextConverter;
use Ibexa\GraphQL\Mutation\InputHandler\FieldType\RichText\MarkdownRichTextConverter;
use Ibexa\GraphQL\Resolver\RichTextResolver;
use Ibexa\Personalization\Authentication\AuthenticationInterface;
use Ibexa\Personalization\Service\Storage\DataSourceServiceInterface;
use Ibexa\Taxonomy\Service\TaxonomyConfiguration;
use Knp\Bundle\MenuBundle\KnpMenuBundle;
use Lexik\Bundle\JWTAuthenticationBundle\LexikJWTAuthenticationBundle;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Overblog\GraphQLBundle\OverblogGraphQLBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\WebpackEncoreBundle\WebpackEncoreBundle;

final class Kernel extends IbexaTestKernel
{
    #[\Override]
    public function registerBundles(): iterable
    {
        yield from parent::registerBundles();

        yield new LexikJWTAuthenticationBundle();
        yield new HautelookTemplatedUriBundle();
        yield new WebpackEncoreBundle();
        yield new KnpMenuBundle();

        yield new IbexaRestBundle();
        yield new IbexaContentFormsBundle();
        yield new IbexaSearchBundle();
        yield new IbexaUserBundle();
        yield new IbexaDesignEngineBundle();
        yield new IbexaAdminUiBundle();
        yield new IbexaNotificationsBundle();
        yield new IbexaCorePersistenceBundle();
        yield new IbexaCoreSearchBundle();
        yield new IbexaFieldTypeRichTextBundle();

        yield new IbexaProductCatalogBundle();
        yield new IbexaGraphQLBundle();
        yield new OverblogGraphQLBundle();

        yield new CRConnectorGeminiBundle();
        yield new IbexaConnectorAiBundle();
        yield new DAMADoctrineTestBundle();
        yield new IbexaTwigComponentsBundle();
    }

    #[\Override]
    public function getSchemaFiles(): iterable
    {
        yield from parent::getSchemaFiles();

        yield $this->locateResource('@IbexaConnectorAiBundle/Resources/config/schema.yaml');
    }

    /**
     * @return iterable<\Ibexa\Contracts\Core\Test\Persistence\Fixture>
     */
    public function getConnectorAiFixtures(): iterable
    {
        $path = $this->locateResource('@IbexaConnectorAiBundle/Resources/data/action_configurations.yaml');

        yield new YamlFixture(__DIR__ . $path);
    }

    #[\Override]
    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        parent::registerContainerConfiguration($loader);

        $loader->load(__DIR__ . '/Resources/config.yaml');
//        $loader->load(__DIR__ . '/Resources/services.yaml');
        $loader->load(static function (ContainerBuilder $container): void {
            $resource = new FileResource(__DIR__ . '/Resources/routing.yaml');
            $container->addResource($resource);
            $container->setParameter('form.type_extension.csrf.enabled', false);
            $container->loadFromExtension('framework', [
                'router' => [
                    'resource' => $resource->getResource(),
                ],
            ]);
            self::createSyntheticProductCatalogDerivativeServices($container);
        });
    }

    #[\Override]
    protected static function getExposedServicesByClass(): iterable
    {
        yield from parent::getExposedServicesByClass();

        yield ActionConfigurationServiceInterface::class;
        yield ActionTypeRegistryInterface::class;
        yield TypeCriterionMapper::class;
        yield ActionServiceInterface::class;
    }

    #[\Override]
    protected static function getExposedServicesById(): iterable
    {
        yield GenerateAltTextActionType::class => ActionTypeInterface::class;
        yield RefineTextActionType::class => ActionTypeInterface::class;
    }

    private static function createSyntheticProductCatalogDerivativeServices(ContainerBuilder $container): void
    {
        self::addSyntheticService($container, ProductNormalizer::class);
        self::addSyntheticService($container, HtmlRichTextConverter::class);
        self::addSyntheticService($container, MarkdownRichTextConverter::class);
        self::addSyntheticService($container, RichTextResolver::class);
        self::addSyntheticService($container, MainMenuSubscriber::class);
        self::addSyntheticService($container, TaxonomyServiceInterface::class);
        self::addSyntheticService($container, JWTTokenManagerInterface::class);
        self::addSyntheticService($container, AuthenticationInterface::class);
        self::addSyntheticService($container, DataSourceServiceInterface::class);

        self::addSyntheticService($container, TaxonomyConfiguration::class);
    }
}
