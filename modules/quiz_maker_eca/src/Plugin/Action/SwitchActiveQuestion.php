<?php

namespace Drupal\quiz_maker_eca\Plugin\Action;

use Drupal\Core\Form\FormStateInterface;
use Drupal\eca\Plugin\Action\ConfigurableActionBase;
use Drupal\quiz_maker\Event\QuestionNavigationEvent;

/**
 * Load the currently logged in user into the token environment.
 *
 * @Action(
 *   id = "quiz_maker_switch_active_question",
 *   label = @Translation("Quiz maker: switch active question."),
 *   description = @Translation("Switch active question of quiz.")
 * )
 */
class SwitchActiveQuestion extends ConfigurableActionBase {

  /**
   * {@inheritDoc}
   */
  public function execute(): void {
    if (!isset($this->configuration['question_number']) || $this->configuration['question_number'] === '') {
      return;
    }

    $event = $this->getEvent();
    if ($event instanceof QuestionNavigationEvent) {
      $event->nextQuestionNumber = $this->configuration['question_number'];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return [
        'question_number' => NULL,
      ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $form = parent::buildConfigurationForm($form, $form_state);
    $form['question_number'] = [
      '#type' => 'number',
      '#title' => $this->t('Question number'),
      '#default_value' => $this->configuration['question_number'],
      '#description' => $this->t('The number of question of quiz to switch to.'),
      '#weight' => -10,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state): void {
    $this->configuration['question_number'] = $form_state->getValue('question_number');
    parent::submitConfigurationForm($form, $form_state);
  }

}
