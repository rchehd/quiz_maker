<?php

namespace Drupal\quiz_maker\Form;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Entity\BundleEntityFormBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\quiz_maker\Entity\QuestionType;
use Drupal\quiz_maker\Trait\QuizMakerPluginTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form handler for question type forms.
 */
class QuestionTypeForm extends BundleEntityFormBase {

  use QuizMakerPluginTrait;

  /**
   * Form constructor.
   *
   * @param \Drupal\Component\Plugin\PluginManagerInterface $pluginManager
   *   The plugin manager.
   */
  public function __construct(
    protected PluginManagerInterface $pluginManager
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.quiz_maker.question')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state): array {
    $form = parent::form($form, $form_state);
    /** @var \Drupal\quiz_maker\Entity\QuestionType $question_type */
    $question_type = $this->entity;

    if ($this->operation === 'edit') {
      $form['#title'] = $this->t('Edit %label question type', ['%label' => $this->entity->label()]);
    }

    $form['label'] = [
      '#title' => $this->t('Label'),
      '#type' => 'textfield',
      '#default_value' => $this->entity->label(),
      '#description' => $this->t('The human-readable name of this question type.'),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $this->entity->id(),
      '#maxlength' => EntityTypeInterface::BUNDLE_MAX_LENGTH,
      '#machine_name' => [
        'exists' => [QuestionType::class, 'load'],
        'source' => ['label'],
      ],
      '#description' => $this->t('A unique machine-readable name for this question type. It must only contain lowercase letters, numbers, and underscores.'),
    ];

    $form['plugin'] = [
      '#title' => $this->t('The question type'),
      '#type' => 'select',
      '#options' => $this->getPlugins(),
      '#default_value' => $question_type->getPluginId(),
      '#description' => $this->t('The plugin of this question type.'),
      '#required' => TRUE,
    ];

    return $this->protectBundleIdElement($form);
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state): array {
    $actions = parent::actions($form, $form_state);
    $actions['submit']['#value'] = $this->t('Save question type');
    $actions['delete']['#value'] = $this->t('Delete question type');
    return $actions;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state): int {
    $result = parent::save($form, $form_state);

    $message_args = ['%label' => $this->entity->label()];
    $this->messenger()->addStatus(
      match($result) {
        default => $this->t('The question type %label has been added.', $message_args),
        SAVED_UPDATED => $this->t('The question type %label has been updated.', $message_args),
      }
    );
    $form_state->setRedirectUrl($this->entity->toUrl('collection'));

    return $result;
  }

}
