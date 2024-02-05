<?php

namespace Drupal\quiz_maker_eca\Plugin\ECA\Event;

use Drupal\eca\Event\Tag;
use Drupal\eca\Plugin\ECA\Event\EventBase;
use Drupal\quiz_maker\Event\QuestionNavigationEvent;
use Drupal\quiz_maker\Event\QuizTakeEvents;
use Drupal\quiz_maker\Event\QuizTakeEvent;
use Drupal\quiz_maker\Event\ResponseEvent;
use Drupal\quiz_maker\Event\ResponseEvents;

/**
 * Plugin implementation of the ECA Events for quiz.
 *
 * @EcaEvent(
 *   id = "quiz_maker",
 *   deriver = "Drupal\quiz_maker_eca\Plugin\ECA\Event\QuizMakerEventDeriver"
 * )
 */
class QuizMakerEvent extends EventBase {

  /**
   * {@inheritDoc}
   */
  public static function definitions(): array {
    return [
      'quiz_start' => [
        'label' => 'User start a quiz',
        'event_name' => QuizTakeEvents::QUIZ_START,
        'event_class' => QuizTakeEvent::class,
        'tags' => Tag::WRITE | Tag::EPHEMERAL | Tag::AFTER,
      ],
      'quiz_update' => [
        'label' => 'User give an answers and update quiz result.',
        'event_name' => QuizTakeEvents::QUIZ_UPDATE,
        'event_class' => QuizTakeEvent::class,
        'tags' => Tag::WRITE | Tag::EPHEMERAL | Tag::AFTER,
      ],
      'quiz_finish' => [
        'label' => 'User finish a quiz',
        'event_name' => QuizTakeEvents::QUIZ_FINISH,
        'event_class' => QuizTakeEvent::class,
        'tags' => Tag::WRITE | Tag::EPHEMERAL | Tag::AFTER,
      ],
      'question_response_created' => [
        'label' => 'User answer a question',
        'event_name' => ResponseEvents::RESPONSE_CREATE,
        'event_class' => ResponseEvent::class,
        'tags' => Tag::WRITE | Tag::EPHEMERAL | Tag::AFTER,
      ],
      'question_response_updated' => [
        'label' => 'User update the answer a question',
        'event_name' => ResponseEvents::RESPONSE_UPDATE,
        'event_class' => ResponseEvent::class,
        'tags' => Tag::WRITE | Tag::EPHEMERAL | Tag::AFTER,
      ],
      'get_next_question' => [
        'label' => 'User get next question',
        'event_name' => QuizTakeEvents::NEXT_QUESTION,
        'event_class' => QuestionNavigationEvent::class,
        'tags' => Tag::WRITE | Tag::EPHEMERAL | Tag::AFTER,
      ],
      'get_previous_question' => [
        'label' => 'User get previous question',
        'event_name' => QuizTakeEvents::PREVIOUS_QUESTION,
        'event_class' => QuestionNavigationEvent::class,
        'tags' => Tag::WRITE | Tag::EPHEMERAL | Tag::AFTER,
      ],
      'navigate_to_question' => [
        'label' => 'User navigate to question',
        'event_name' => QuizTakeEvents::PREVIOUS_QUESTION,
        'event_class' => QuestionNavigationEvent::class,
        'tags' => Tag::WRITE | Tag::EPHEMERAL | Tag::AFTER,
      ],
    ];
  }

}