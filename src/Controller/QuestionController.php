<?php

namespace Drupal\quiz_maker\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Link;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Url;
use Drupal\quiz_maker\QuestionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Question controller class.
 */
class QuestionController extends ControllerBase {

  /**
   * Constructs a QuestionController object.
   *
   * @param \Drupal\Core\Datetime\DateFormatterInterface $dateFormatter
   *   The date formatter service.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entityRepository
   *   The entity repository.
   */
  public function __construct(
    protected DateFormatterInterface $dateFormatter,
    protected RendererInterface $renderer,
    protected EntityRepositoryInterface $entityRepository
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('date.formatter'),
      $container->get('renderer'),
      $container->get('entity.repository')
    );
  }

  /**
   * Revision overview.
   *
   * @param \Drupal\quiz_maker\QuestionInterface $question
   *   The question.
   *
   * @return array
   *   The render array.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function revisionOverview(QuestionInterface $question) {
    $langcode = $question->language()->getId();
    $langname = $question->language()->getName();
    $languages = $question->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $question_storage = $this->entityTypeManager()->getStorage('question');

    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', [
      '@langname' => $langname,
      '%title' => $question->label()
    ]) : $this->t('Revisions for %title', ['%title' => $question->label()]);
    $header = [$this->t('Revision'), $this->t('Operations')];

    $rows = [];
    $default_revision = $question->getRevisionId();
    $current_revision_displayed = FALSE;

    foreach ($this->getRevisionIds($question, $question_storage) as $vid) {
      /** @var \Drupal\quiz_maker\QuestionInterface $revision */
      $revision = $question_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) &&
        $revision->getTranslation($langcode)->isRevisionTranslationAffected()
      ) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = $this->dateFormatter->format($revision->revision_timestamp->value, 'short');

        // We treat also the latest translation-affecting revision as current
        // revision, if it was the default revision, as its values for the
        // current language will be the same of the current default revision in
        // this case.
        $is_current_revision = $vid == $default_revision || (!$current_revision_displayed && $revision->wasDefaultRevision());
        if (!$is_current_revision) {
          $link = Link::fromTextAndUrl($date, new Url('entity.question.revision', [
            'question' => $question->id(),
            'question_revision' => $vid
          ]))->toString();
        }
        else {
          $link = $question->toLink($date)->toString();
          $current_revision_displayed = TRUE;
        }

        $row = [];
        $column = [
          'data' => [
            '#type' => 'inline_template',
            '#template' => '{% trans %}{{ date }} by {{ username }}{% endtrans %}{% if message %}<span class="revision-log">{{ message }}</span>{% endif %}',
            '#context' => [
              'date' => $link,
              'username' => $this->renderer->renderPlain($username),
              'message' => [
                '#markup' => $revision->revision_log->value,
                '#allowed_tags' => Xss::getHtmlTagList()
              ],
            ],
          ],
        ];

        $this->renderer->addCacheableDependency($column['data'], $username);
        $row[] = $column;

        if ($is_current_revision) {
          $row[] = [
            'data' => [
              '#prefix' => '<em>',
              '#markup' => $this->t('Current revision'),
              '#suffix' => '</em>',
            ],
          ];

          $rows[] = [
            'data' => $row,
            'class' => ['revision-current'],
          ];
        }
        else {
          $links = [];
          if ($revision->access('revert revision')) {
            $links['revert'] = [
              'title' => $vid < $question->getRevisionId() ? $this->t('Revert') : $this->t('Set as current revision'),
              'url' => $has_translations ?
              Url::fromRoute('question.revision_revert_translation_confirm', [
                'question' => $question->id(),
                'question_revision' => $vid,
                'langcode' => $langcode
              ]) :
              Url::fromRoute('question.revision_revert_confirm', [
                'question' => $question->id(),
                'question_revision' => $vid
              ]),
            ];
          }

          if ($revision->access('delete revision')) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('question.revision_delete_confirm', [
                'question' => $question->id(),
                'question_revision' => $vid
              ]),
            ];
          }

          $row[] = [
            'data' => [
              '#type' => 'operations',
              '#links' => $links,
            ],
          ];

          $rows[] = $row;
        }
      }
    }

    $build['question_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
      '#attributes' => [
        'class' => [
          'question-revision-table',
        ]
      ],
    ];

    $build['pager'] = ['#type' => 'pager'];

    return $build;
  }

  /**
   * Gets a list of question revision IDs for a specific question.
   *
   * @param \Drupal\quiz_maker\QuestionInterface $question
   *   The question entity.
   * @param \Drupal\Core\Entity\EntityStorageInterface $question_storage
   *   The question storage.
   *
   * @return int[]
   *   Question revision IDs (in descending order).
   */
  protected function getRevisionIds(QuestionInterface $question, EntityStorageInterface $question_storage) {
    $result = $question_storage->getQuery()
      ->accessCheck(TRUE)
      ->allRevisions()
      ->condition($question->getEntityType()->getKey('id'), $question->id())
      ->sort($question->getEntityType()->getKey('revision'), 'DESC')
      ->pager(50)
      ->execute();
    return array_keys($result);
  }

}
