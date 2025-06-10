<?php

declare(strict_types=1);

namespace CodeRhapsodie\Bundle\ConnectorGemini\Form\Type;

use Ibexa\Bundle\ConnectorAi\Form\Type\ActionConfiguration\ActionConfigurationOptions;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @internal
 */
abstract class AbstractActionConfigurationOptions extends AbstractType
{
    /** @var array<string, string> */
    private array $models;

    private string $defaultModel;

    private int $defaultMaxTokens;

    private float $defaultTemperature;

    /**
     * @param string[] $models
     */
    public function __construct(array $models, string $defaultModel, int $defaultMaxTokens, float $defaultTemperature)
    {
        $this->models = $models;
        $this->defaultModel = $defaultModel;
        $this->defaultMaxTokens = $defaultMaxTokens;
        $this->defaultTemperature = $defaultTemperature;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('prompt', TextareaType::class, [
            'required' => false,
            'disabled' => $options['translation_mode'],
        ]);

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($options): void {
                $data = $event->getData();
                $form = $event->getForm();

                $form->add('model', ChoiceType::class, [
                    'disabled' => $options['translation_mode'],
                    'choices' => array_flip($this->models),
                    'data' => $data['model'] ?? $this->defaultModel,
                ]);

                $form->add('max_tokens', NumberType::class, [
                    'disabled' => $options['translation_mode'],
                    'data' => $data['max_tokens'] ?? $this->defaultMaxTokens,
                ]);

                $form->add('temperature', NumberType::class, [
                    'disabled' => $options['translation_mode'],
                    'html5' => true,
                    'scale' => 2,
                    'data' => $data['temperature'] ?? $this->defaultTemperature,
                ]);
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'ibexa_connector_ai',
            'translation_mode' => false,
        ]);

        $resolver->setAllowedTypes('translation_mode', 'bool');
    }

    public function getParent(): string
    {
        return ActionConfigurationOptions::class;
    }
}
