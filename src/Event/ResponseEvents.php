<?php

namespace Drupal\quiz_maker\Event;

/**
 * ResponseEvents class.
 */
final class ResponseEvents {

  /**
   * Name of event when user answer on question and response was created.
   *
   * @Event
   *
   * @see \Drupal\quiz_maker\Event\ResponseEvent
   */
  const RESPONSE_CREATE = 'response.create';

  /**
   * Name of event when user answer on question and response was created.
   *
   * @Event
   *
   * @see \Drupal\quiz_maker\Event\ResponseEvent
   */
  const RESPONSE_UPDATE = 'response.update';

}
