<?php

namespace Drupal\quiz_maker\Plugin\QuizMaker\Answer;

use Drupal\Core\Form\FormStateInterface;
use Drupal\quiz_maker\Entity\QuestionAnswer;

/**
 * Plugin implementation of the question.
 *
 * @QuizMakerQuestionAnswer(
 *   id = "single_choice_answer",
 *   label = @Translation("Single choice answer"),
 *   description = @Translation("Single choice answer.")
 * )
 */
final class SingleChoiceAnswer extends QuestionAnswer {

}
