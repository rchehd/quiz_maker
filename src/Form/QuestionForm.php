<?php

namespace Drupal\quiz_maker\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\quiz_maker\Entity\QuestionAnswer;

/**
 * Form controller for the question entity edit forms.
 */
final class QuestionForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

//    $form['answers_form'] = [
//      '#type' => 'fieldset',
//      '#title' => $this->t('Answers'),
//      '#weight' => 10
//    ];
//
//    // Add a button to dynamically add answer options.
//    $form['answers_form']['add_option'] = [
//      '#type' => 'submit',
//      '#submit' => ['::addAnswer'],
//      '#value' => $this->t('Add Answer'),
//      '#limit_validation_errors' => [],
//      '#ajax' => [
//        'callback' => '::updateAnswers',
//        'wrapper' => 'answers',
//      ],
//    ];
//
//    // Container for dynamically added answer options.
//    $form['answers_form']['answers'] = [
//      '#type' => 'container',
//      '#attributes' => ['id' => 'answers'],
//    ];



    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state): int {
    $result = parent::save($form, $form_state);

    $message_args = ['%label' => $this->entity->toLink()->toString()];
    $logger_args = [
      '%label' => $this->entity->label(),
      'link' => $this->entity->toLink($this->t('View'))->toString(),
    ];

    switch ($result) {
      case SAVED_NEW:
        $this->messenger()->addStatus($this->t('New question %label has been created.', $message_args));
        $this->logger('quiz_maker')->notice('New question %label has been created.', $logger_args);
        break;

      case SAVED_UPDATED:
        $this->messenger()->addStatus($this->t('The question %label has been updated.', $message_args));
        $this->logger('quiz_maker')->notice('The question %label has been updated.', $logger_args);
        break;

      default:
        throw new \LogicException('Could not save the entity.');
    }

    $form_state->setRedirectUrl($this->entity->toUrl());

    return $result;
  }

  /**
   * AJAX callback to add more answer options.
   */
  public function updateAnswers(array &$form, FormStateInterface $form_state) {
    // Redraw the answer options container.
    return $form['answers_form']['answers'];
  }

  public function addAnswer(array &$form, FormStateInterface $form_state) {
    $answer_count = $form_state->get('answer_count');
    $form_state->set('answer_count', $answer_count + 1);
    $form_state->setRebuild(TRUE);
  }

}
