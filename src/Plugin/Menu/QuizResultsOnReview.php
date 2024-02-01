<?php

namespace Drupal\quiz_maker\Plugin\Menu;

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Query\QueryException;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Menu\MenuLinkDefault;
use Drupal\Core\Menu\StaticMenuLinkOverridesInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\quiz_maker\Entity\QuizResultType;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class of QuizResultsOnReview plugin.
 */
final class QuizResultsOnReview extends MenuLinkDefault implements ContainerFactoryPluginInterface {

  /**
   * Constructs a new QuizResultsOnReview instance.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    StaticMenuLinkOverridesInterface $static_override,
    protected EntityTypeManagerInterface $entityTypeManager,
    protected LoggerChannelFactoryInterface $loggerChannelFactory
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $static_override);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): self {
    return new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('menu_link.static.overrides'),
      $container->get('entity_type.manager'),
      $container->get('logger.factory'),
    );
  }

  /**
   * {@inheritDoc}
   */
  public function getTitle(): string {
    $title = (string) $this->pluginDefinition['title'];
    try {
      $count = count($this->entityTypeManager->getStorage('quiz_result')
        ->loadByProperties([
          'state' => QuizResultType::ON_REVIEW,
        ]));
    }
    catch (InvalidPluginDefinitionException | PluginNotFoundException | QueryException $e) {
      $this->loggerChannelFactory->get('quiz_maker')->error($e->getMessage());
      return $title;
    }

    return "$title ($count)";
  }

}
